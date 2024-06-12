<?php

namespace ProfilePress\Libsodium;

use ProfilePress\Core\Classes\Autologin;
use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Classes\RegistrationAuth;
use WP_Error, WP_User;

class EmailConfirmation
{
    public $login_form_notice;

    public static $instance_flag = false;

    public function __construct()
    {
        add_filter('ppress_settings_page_args', array($this, 'settings_page'), 1);

        add_filter('ppress_email_notifications', [$this, 'email_listing_page_addition']);

        add_filter('manage_users_columns', array($this, 'add_email_verified_column'));
        add_action('manage_users_custom_column', array($this, 'show_email_verified_status'), 10, 3);

        add_filter('user_row_actions', array(__CLASS__, 'confirm_email_link'), 10, 2);
        add_action('load-users.php', array($this, 'act_on_confirmation_order'));
        add_action('admin_notices', array(__CLASS__, 'admin_notices'));

        // disable welcome email
        add_filter('ppress_activate_send_welcome_email', '__return_false', PHP_INT_MAX - 1);

        add_shortcode('pp-resend-email-confirmation', array($this, 'resend_email_confirmation_shortcode'));
        add_action('wp', array($this, 'act_on_shortcode_confirmation_resend'));

        add_action('wp', [$this, 'validate_activation_code']);
        add_action('wp', [$this, 'act_on_resend_activation']);
        add_action('wp', array($this, 'delete_unconfirmed_users'));

        add_filter('ppress_login_error_output', array($this, 'display_custom_error_notices'));

        add_action('ppress_after_registration', array($this, 'send_email_confirmation'), 10, 3);

        add_filter('authenticate', array($this, 'check_if_user_is_activated'), 999999999, 3);
    }

    public static function default_email_message()
    {
        return <<<MESSAGE
<p>Hi {{username}}!</p>
<p>To confirm your email address and activate your account, click the button below.</p>
<div style="padding: 10px 0 50px 0; text-align: center;"><a style="background: #555555; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 3px; letter-spacing: 0.3px;" href="{{email_confirmation_link}}">Confirm Email Address</a></div>
MESSAGE;
    }

    public function email_listing_page_addition($emails)
    {
        $ec_email = [
            'type'         => 'account',
            'key'          => 'email_confirmation',
            'title'        => esc_html__('Email Address Confirmation', 'profilepress-pro'),
            'subject'      => esc_html__('Confirm Your Email Address to Activate Your Account', 'profilepress-pro'),
            'message'      => self::default_email_message(),
            'description'  => esc_html__('Email sent to new users for them to confirm their email addresses before they can log in to your site.', 'profilepress-pro'),
            'recipient'    => esc_html__('Users', 'profilepress-pro'),
            'placeholders' => [
                '{{username}}'                => esc_html__('Username of the registered user.', 'profilepress-pro'),
                '{{email_confirmation_link}}' => esc_html__('The email address confirmation URL.', 'profilepress-pro'),
            ]
        ];

        array_unshift($emails, $ec_email);

        return $emails;
    }

    /**
     * Is user email confirmed?
     *
     * @return bool
     */
    public static function is_user_confirm($user_id)
    {
        $status = get_user_meta($user_id, 'pp_email_verified', 'true');
        // old users wouldn't have their email verified thus, make them verified
        if (empty($status)) return true;

        if ( ! empty($status) && 'true' == $status) return true;

        return false;
    }

    /**
     * Is email confirmation / account activation module active?
     *
     * @return bool
     */
    public function is_autologin_after_confirmation_active()
    {
        return ppress_settings_by_key('uec_autologin_after_confirmation') == 'true';
    }

    /**
     * Callback to output text and link to resend email confirmation.
     *
     * @param array $atts
     *
     * @return string|void
     */
    public function resend_email_confirmation_shortcode($atts)
    {
        if ( ! is_user_logged_in()) return;

        $user_id = get_current_user_id();

        if (self::is_user_confirm($user_id)) return;

        $atts = shortcode_atts([
            'text' => __("Apparently you haven't confirmed your email address", 'profilepress-pro'),
            'link' => __('Do that now!', 'profilepress-pro')
        ],
            $atts
        );

        $text = esc_html($atts['text']);
        $link = esc_url($atts['link']);

        $url = add_query_arg(array('pp-frontend-resend' => 'true', 'resend_activation' => 'true'));

        if ( ! empty($this->resend_activation_status)) {
            return $this->resend_activation_status;
        }

        return $text . ' ' . sprintf("<a href=\"$url\">%s</a>", $link);
    }

    /**
     * Act on email confirmation resent done via shortcode.
     */
    public function act_on_shortcode_confirmation_resend()
    {
        if (isset($_GET['pp-frontend-resend']) && $_GET['pp-frontend-resend'] === 'true') {
            $this->resend_activation_status = $this->resend_activation(get_current_user_id());
        }
    }

    /**
     * Delete unconfirmed users.
     */
    public function delete_unconfirmed_users()
    {
        global $wpdb;

        $activated = ppress_settings_by_key('uac_activate_delete_unconfirmed_users') == 'true';

        if ( ! $activated) return;

        if (false === get_transient('pp_ec_delete_unconfirmed_users')) {
            // get IDs of unconfirmed users
            $unconfirmed_ids = $wpdb->get_col(
                $wpdb->prepare("SELECT DISTINCT user_id FROM $wpdb->usermeta WHERE meta_key = %s OR meta_key = %s",
                    'pp_activation_created',
                    'pp_activation_expiration'
                )
            );

            $expiration_day_in_seconds = ppress_settings_by_key('uec_unconfirmed_age', 10, true);
            $expiration_day_in_seconds = (int)$expiration_day_in_seconds * DAY_IN_SECONDS;

            foreach ($unconfirmed_ids as $user_id) {

                $last_sent_confirmation_date = get_user_meta($user_id, 'pp_activation_created', true);

                // calculate the difference between current time and time the activation was last sent.
                // the result is how long (in seconds) the confirmation has been left dormant.
                $difference = time() - absint($last_sent_confirmation_date);

                do_action('ppress_ec_before_delete_unconfirmed_users', $user_id);

                if ( ! self::is_user_confirm($user_id) && $difference > $expiration_day_in_seconds) {
                    if ( ! function_exists('wp_delete_user')) {
                        require_once(ABSPATH . 'wp-admin/includes/user.php');
                    }

                    wp_delete_user($user_id);
                }

                do_action('ppress_ec_after_delete_unconfirmed_users', $user_id);
            }

            // filter to specify how often to delete unconfirmed users
            $delete_interval = absint(apply_filters('ppress_ec_delete_user', 24));

            set_transient('pp_ec_delete_unconfirmed_users', 'active', $delete_interval * HOUR_IN_SECONDS);
        }
    }


    /**
     * Check if a user is already activated.
     *
     * @param WP_User $user
     * @param string $username
     * @param string $password
     *
     * @return WP_Error|WP_User
     */
    public function check_if_user_is_activated($user, $username, $password)
    {
        if ( ! apply_filters('ppress_bypass_check_if_user_is_activated', false)) {
            if (is_email($username)) {
                $user_obj = get_user_by('email', $username);
            } else {
                $user_obj = get_user_by('login', $username);
            }

            if (false !== $user_obj) {
                if ( ! self::is_user_confirm($user_obj->ID)) {
                    return new WP_Error('uec_error', $this->pending_activation_error($user_obj->ID));
                }
            }
        }

        return $user;
    }


    /**
     * Send email confirmation / account activation message to a user.
     *
     * @param int $form_id
     * @param mixed $user_data
     * @param int $user_id
     */
    public function send_email_confirmation($form_id, $user_data, $user_id)
    {
        if (ppress_get_setting('email_confirmation_email_enabled', 'on') != 'on') return;

        $token_length    = apply_filters('ppress_email_confirmation_length', 10);
        $activation_code = wp_generate_password($token_length, false);
        $expiration      = time() + (60 * ppress_settings_by_key('uec_expiration', 30, true));

        update_user_meta($user_id, 'pp_activation_code', $activation_code);
        update_user_meta($user_id, 'pp_activation_created', time());
        update_user_meta($user_id, 'pp_activation_expiration', $expiration);

        // set the user email verified status to false
        update_user_meta($user_id, 'pp_email_verified', 'false');

        $subject = ppress_get_setting('email_confirmation_email_subject', esc_html__('Confirm Your Email Address to Activate Your Account', 'profilepress-pro'), true);
        $subject = apply_filters(
            'ppress_ec_mail_subject',
            $this->parse_shortcode($subject, $user_id, $activation_code),
            $user_id, $activation_code
        );

        $email_body = apply_filters(
            'ppress_ec_mail_content',
            $this->parse_shortcode(
                ppress_get_setting('email_confirmation_email_content', self::default_email_message(), true), $user_id, $activation_code
            ),
            $user_id, $activation_code
        );

        ppress_send_email($this->user_email($user_id), wp_specialchars_decode($subject), $email_body);
    }


    /**
     * Helper function to confirm user email.
     *
     * @param int $user_id
     */
    public static function confirm_user_email($user_id)
    {
        // delete activation meta key
        delete_user_meta($user_id, 'pp_activation_code');

        // delete activation expiration meta key
        delete_user_meta($user_id, 'pp_activation_expiration');
        // delete activation created meta key
        delete_user_meta($user_id, 'pp_activation_created');

        // set to active once user have their email verified
        update_user_meta($user_id, 'pp_email_verified', 'true');
    }

    /**
     * Validate account activation / email confirmation
     *
     * @return void
     */
    public function validate_activation_code()
    {
        if (isset($_GET['activation_code']) && isset($_GET['user_id'])) {

            // skip for bot such as outlook safe-links that uses HEAD request to validate URLs
            // https://github.com/nhost/hasura-auth/pull/191/files
            if (empty($_SERVER['HTTP_USER_AGENT']) || isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'head') {
                http_response_code(200);
                exit;
            }

            if ( ! isset($_GET['ppressSafe'])) {

                echo "<script>
                    var currentUrl = window.location.href;
                    // Check if the URL already contains a query string
                    var separator = currentUrl.indexOf('?') !== -1 ? '&' : '?';
                    // Construct the new URL with the added query string
                    var newUrl = currentUrl + separator + 'ppressSafe=true';
                    // Update the window location with the new URL
                    window.location.href = newUrl;
                    </script>";

                exit;
            }

            if (isset($_GET['ppressSafe'])) {

                $user_id = absint($_GET['user_id']);

                $activation_code = esc_attr($_GET['activation_code']);

                $time = time();

                $db_activation_code = get_user_meta($user_id, 'pp_activation_code', true);

                $db_expires = absint(get_user_meta($user_id, 'pp_activation_expiration', true));

                if (self::is_user_confirm($user_id)) return;

                if (ppress_user_id_exist($user_id) && $activation_code == $db_activation_code && $time < $db_expires) {
                    self::confirm_user_email($user_id);
                    remove_filter('ppress_activate_send_welcome_email', '__return_false', PHP_INT_MAX - 1);
                    RegistrationAuth::send_welcome_email($user_id);
                    // fire after confirmation.
                    do_action('ppress_ec_after_confirmation', $user_id);

                    if ($this->is_autologin_after_confirmation_active()) {
                        Autologin::initialize($user_id, '', apply_filters('ppress_ec_autologin_redirect_url', ''));
                    }

                    $this->login_form_notice = $this->activation_success_message();

                    return;
                }

                $this->login_form_notice = $this->fail_email_confirmation_message($user_id);
            }
        }
    }


    /**
     * Resend activation / email confirmation mail.
     *
     * @param $user_id
     *
     * @return string
     */
    public function resend_activation($user_id)
    {
        if ( ! ppress_user_id_exist($user_id)) return '';

        if (self::is_user_confirm($user_id)) return $this->already_confirm_notice();

        $this->send_email_confirmation(null, null, $user_id);

        return $this->confirmation_resent_notice();
    }

    public function act_on_resend_activation()
    {
        if (isset($_GET['resend_activation']) && ! empty($_GET['resend_activation'])) {

            $user_id = absint($_GET['resend_activation']);

            if ( ! ppress_user_id_exist($user_id)) return;

            if (self::is_user_confirm($user_id)) {
                $this->login_form_notice = $this->already_confirm_notice();

                return;
            }

            $this->send_email_confirmation(null, null, $user_id);

            $this->login_form_notice = $this->confirmation_resent_notice();
        }
    }

    public function display_custom_error_notices($notice)
    {
        if (isset($this->login_form_notice) && ! empty($this->login_form_notice)) {
            $notice = $this->login_form_notice;
        }

        return $notice;
    }

    /**
     * Get user email by ID.
     *
     * @param $user_id
     *
     * @return string
     */
    public function user_email($user_id)
    {
        return get_user_by('id', $user_id)->user_email;
    }

    /**
     * Mail content.
     *
     * @param int $user_id
     * @param string $activation_code
     *
     * @return string
     */
    public function parse_shortcode($content, $user_id, $activation_code)
    {
        $username = ppress_get_username_by_id($user_id);

        return str_replace([
            '{{email_confirmation_link}}',
            '{{username}}'
        ],
            [
                $this->email_confirmation_url($user_id, $activation_code),
                $username
            ],
            $content
        );
    }

    /**
     * Return the email confirmation / activation url
     *
     * @param int $user_id
     * @param string $activation_code
     *
     * @return string
     */
    public function email_confirmation_url($user_id, $activation_code)
    {
        $url = add_query_arg(
            array(
                'user_id'         => $user_id,
                'activation_code' => $activation_code
            ),
            ppress_login_url()
        );

        return $url;
    }


    /**
     * URL to resend activation / email confirmation mail.
     *
     * @param int $user_id
     *
     * @return string
     */
    public function resend_activation_url($user_id)
    {
        $url = add_query_arg(
            array(
                'resend_activation' => $user_id
            ),
            ppress_login_url()
        );

        return $url;
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function settings_page($args)
    {
        $args['pp_ec_settings'] = [
            'tab_title'                                 => esc_html__('Email Confirmation', 'profilepress-pro'),
            'section_title'                             => esc_html__('Email Confirmation Settings', 'profilepress-pro'),
            'dashicon'                                  => 'dashicons-email',
            'customize_email_confirmation_email_notice' => [
                'type'        => 'arbitrary',
                'data'        => '',
                'description' => sprintf(
                    '<div class="ppress-settings-page-notice">' . esc_html__('To customize the email that is sent to users for them to confirm or verify their email addresses, go to the %semail settings%s.', 'profilepress-pro'),
                    '<a target="_blank" href="' . add_query_arg('type', 'email_confirmation', PPRESS_SETTINGS_EMAIL_SETTING_PAGE) . '">', '</a>'
                )
            ],
            'uec_expiration'                            => array(
                'type'        => 'number',
                'label'       => __('Link Expiration', 'profilepress-pro'),
                'description' => __('Time in minutes the email confirmation URL will expire if unused. Default to 30mins if this field is left empty.', 'profilepress-pro')
            ),
            'uec_unactivated_error'                     => array(
                'type'        => 'textarea',
                'rows'        => 3,
                'label'       => __('Error Message I', 'profilepress-pro'),
                'description' => __('Error message displayed when users that haven\'t verified their email try to log in.', 'profilepress-pro') . '<br/>
					<p class="description">' . __('The following placeholder is available for use', 'profilepress-pro') . ': <br/>
						<strong>{{resend_email_confirmation_link}}</strong>&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;' . __('URL to resend email confirmation link.', 'profilepress-pro') . '<br/>'
            ),
            'uec_invalid_error'                         => array(
                'type'        => 'textarea',
                'rows'        => 3,
                'label'       => __('Error Message II', 'profilepress-pro'),
                'description' => __('Error message displayed when an email confirmation link is invalid or has expired.', 'profilepress-pro') . '<br/>
					<p class="description">' . __('The following placeholder is available for use', 'profilepress-pro') . ': <br/>
						<strong>{{resend_email_confirmation_link}}</strong>&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;' . __('URL to resend email confirmation link.', 'profilepress-pro') . '<br/></p>'
            ),
            'uec_autologin_after_confirmation'          => array(
                'type'        => 'checkbox',
                'label'       => __('Autologin After Confirmation', 'profilepress-pro'),
                'description' => __('Check this for users to automatically be logged in after successful email confirmation.', 'profilepress-pro')
            ),
            'uec_success_message'                       => array(
                'type'        => 'textarea',
                'rows'        => 3,
                'label'       => __('Confirmation Successful', 'profilepress-pro'),
                'description' => __('Message displayed when a user successfully verify their email addresses.', 'profilepress-pro')
            ),
            'uec_activation_resent'                     => array(
                'type'        => 'textarea',
                'rows'        => 3,
                'label'       => __('Email Confirmation Resent', 'profilepress-pro'),
                'description' => __('Message displayed when an account activation or email confirmation message is resent to a user.', 'profilepress-pro')
            ),
            'uec_already_confirm_message'               => array(
                'type'        => 'textarea',
                'rows'        => 3,
                'label'       => __('Email Already Confirmed', 'profilepress-pro'),
                'description' => __('Message displayed when an attempt to resend an confirmation email to users that have previously confirmed their email.', 'profilepress-pro')
            ),
            'uac_activate_delete_unconfirmed_users'     => array(
                'type'        => 'checkbox',
                'label'       => __('Delete Unconfirmed Users', 'profilepress-pro'),
                'description' => __('Check to delete users who haven\'t confirmed their email for a period of time.', 'profilepress-pro')
            ),
            'uec_unconfirmed_age'                       => array(
                'type'        => 'number',
                'label'       => __('Age of Unconfirmed Account', 'profilepress-pro'),
                'description' => __('The age of unconfirmed user accounts before they are deleted. Defaults to 10 days if field is empty.', 'profilepress-pro')
            ),
        ];

        return $args;
    }

    //--------------- Error messages -------------------------------------//

    /**
     * Error message when a user pending activation tries to log in.
     *
     * @param int $user_id
     *
     * @return string
     */
    public function pending_activation_error($user_id)
    {
        $default_error = __('Account is pending email confirmation.', 'profilepress-pro') . ' <a href="{{resend_email_confirmation_link}}">' . __('Click to resend', 'profilepress-pro') . '</a>';

        $err_msg = ppress_settings_by_key('uec_unactivated_error', $default_error, true);

        return str_replace('{{resend_email_confirmation_link}}', $this->resend_activation_url($user_id), $err_msg);
    }

    /**
     * Error message when an invalid or expired activation or email confirmation link is clicked.
     *
     * @param int $user_id
     *
     * @return string
     */
    public function fail_email_confirmation_message($user_id)
    {
        $default_error = '<div class="profilepress-login-status">' . __('Confirmation link is invalid or has expired.', 'profilepress-pro') . ' <a href="{{resend_email_confirmation_link}}">' . __('Click to resend.', 'profilepress-pro') . '</a></div>';

        $err_msg = ppress_settings_by_key('uec_invalid_error', $default_error, true);

        return str_replace('{{resend_email_confirmation_link}}', $this->resend_activation_url($user_id), $err_msg);
    }

    /**
     * Message when a user successfully verified their email thus activating their account.
     *
     * @return string
     */
    public function activation_success_message()
    {
        $default_error = '<div class="profilepress-login-status">' . __('Email successfully verified. You can now log in.', 'profilepress-pro') . '</div>';

        return ppress_settings_by_key('uec_success_message', $default_error, true);
    }


    /**
     * Message when a user successfully verified their email thus activating their account.
     *
     * @return string
     */
    public function confirmation_resent_notice()
    {
        $default_error = '<div class="profilepress-login-status">' . __('Confirmation email successfully resent', 'profilepress-pro') . '.</div>';

        return ppress_settings_by_key('uec_activation_resent', $default_error, true);
    }

    /**
     * Message when a user successfully verified their email thus activating their account.
     *
     * @return string
     */
    public function already_confirm_notice()
    {
        $default_error = '<div class="profilepress-login-status">' . __('Email address for this user is already verified.', 'profilepress-pro') . '.</div>';

        return ppress_settings_by_key('uec_already_confirm_message', $default_error, true);
    }

    // --------- Custom email verified admin column --- //


    /**
     * Add "email verified" Custom Column
     *
     * @param array $columns
     *
     * @return array $columns (with the new column appended)
     */
    public function add_email_verified_column($columns)
    {
        $columns['pp_email_verify'] = apply_filters('ppress_email_confirm_column', __('Verified', 'profilepress-pro'));

        return $columns;
    }


    /**
     * Show the status of email i.e verified or not.
     *
     * @param string $empty
     * @param string $column_name
     * @param int $user_id
     *
     * @return string
     */
    public function show_email_verified_status($empty, $column_name, $user_id)
    {
        if ('pp_email_verify' == $column_name) {
            if (self::is_user_confirm($user_id)) {
                $status = '<div class="pp_circle_green"></div>';
            } else {
                $status = '<div class="pp_circle_red"></div>';
            }

            return $status;
        }

        return $empty;
    }

    /**
     * Add email confirmation helper links to user_row_actions
     *
     * @param $actions
     * @param $user_object
     *
     * @return mixed
     */
    public static function confirm_email_link($actions, $user_object)
    {
        $current_user = wp_get_current_user();

        // do not display link for admin
        if ($current_user->ID != $user_object->ID) {

            if ( ! self::is_user_confirm($user_object->ID)) {

                // confirm user action link
                $actions['email_confirm'] = sprintf(
                    '<a href="%1$s">%2$s</a>',
                    esc_url(add_query_arg(
                            array(
                                'action'   => 'email_confirm',
                                'user'     => $user_object->ID,
                                '_wpnonce' => wp_create_nonce('email-confirm')
                            )
                        )
                    ),
                    apply_filters('ppress_email_confirm_row_actions', __('Confirm Email', 'profilepress-pro'))
                );

                // resend confirmation action link
                $actions['resend_email_confirm'] = sprintf(
                    '<a href="%1$s">%2$s</a>',
                    esc_url(add_query_arg(
                            array(
                                'action'   => 'resend_email_confirm',
                                'user'     => $user_object->ID,
                                '_wpnonce' => wp_create_nonce('resend-email-confirm')
                            )
                        )
                    ),
                    apply_filters('ppress_resend_email_confirm_row_actions', __('Resend Email Confirmation', 'profilepress-pro'))
                );
            }
        }

        return $actions;
    }

    public function act_on_confirmation_order()
    {
        if (isset($_GET['action'])) {

            $user_id = isset($_GET['user']) ? absint($_GET['user']) : '';

            if ('email_confirm' == $_GET['action'] && check_admin_referer('email-confirm')) {
                self::confirm_user_email($user_id);
                wp_safe_redirect(add_query_arg('email-confirmed', 'true', admin_url('users.php')));
                exit;
            }

            if ('resend_email_confirm' == $_GET['action'] && check_admin_referer('resend-email-confirm')) {
                $this->send_email_confirmation(null, null, $user_id);
                wp_safe_redirect(add_query_arg('resend-email-confirm', 'true', admin_url('users.php')));
                exit;
            }
        }
    }

    public static function admin_notices()
    {
        if (isset($_GET['email-confirmed'])) {

            if ($_GET['email-confirmed'] == 'true') {
                echo '<div class="updated"><p>';
                echo apply_filters('ppress_email_confirmation_success', __('Email successfully confirmed.', 'profilepress-pro'));
                echo '</p></div>';
            }
        }

        if (isset($_GET['resend-email-confirm'])) {

            if ($_GET['resend-email-confirm'] == 'true') {
                echo '<div class="updated"><p>';
                echo apply_filters('ppress_resend_email_confirmation_success', __('Email successfully confirmed.', 'profilepress-pro'));
                echo '</p></div>';
            }
        }
    }

    /**
     * Singleton instance.
     *
     * @return self|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::EMAIL_CONFIRMATION')) return;

        if ( ! EM::is_enabled(EM::EMAIL_CONFIRMATION)) return;

        static $instance;

        if ( ! isset($instance)) {
            $instance = new self;
        }

        return $instance;
    }
}