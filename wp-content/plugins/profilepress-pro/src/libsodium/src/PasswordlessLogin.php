<?php

namespace ProfilePress\Libsodium;

use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Classes\FormRepository as FR;
use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use WP_User;
use WP_Error;

class PasswordlessLogin
{
    public static $instance_flag = false;

    /** @var WP_User user object */
    public static $user_obj;

    public static $username;

    public static $user_email;

    public static $user_id;

    protected $form_id;

    protected $form_type;

    public function __construct()
    {
        add_action('init', array($this, 'validate_one_time_login_url'));

        add_filter('ppress_login_settings_page', [$this, 'settings_page']);

        add_filter('ppress_email_notifications', [$this, 'email_notifications']);

        if ( ! apply_filters('ppress_disable_login_passwordless', false)) {
            add_filter('authenticate', array($this, 'validate_user_login'), 999999999, 3);
        }

        add_filter('user_row_actions', array($this, 'resend_passwordless_login_link'), 10, 2);
        add_action('load-users.php', array($this, 'act_on_send_passworldess_request'));
        add_action('ppress_admin_notices', array($this, 'admin_notices'));
    }

    public function settings_page($settings)
    {
        $settings[] = [
            'section_title'                => esc_html__('One-time Passwordless Login Settings', 'profilepress-pro'),
            'passwordless_disable_admin'   => [
                'type'        => 'checkbox',
                'value'       => 'active',
                'label'       => esc_html__('Disable for Admins', 'profilepress-pro'),
                'description' => esc_html__('Check to disable passwordless login for administrators.', 'profilepress-pro')
            ],
            'passwordless_expiration'      => [
                'type'        => 'number',
                'label'       => esc_html__('Expiration', 'profilepress-pro'),
                'description' => esc_html__('Time in minutes the one-time login URL will expire if it isn\'t use by the user. Default to 10mins if this field is empty.', 'profilepress-pro')
            ],
            'passwordless_error'           => [
                'type'        => 'textarea',
                'rows'        => '2',
                'label'       => esc_html__('Error Message', 'profilepress-pro'),
                'description' => esc_html__('Error displayed when the one-time login URL is invalid or has expired.', 'profilepress-pro')
            ],
            'passwordless_success_message' => [
                'type'        => 'textarea',
                'rows'        => '2',
                'label'       => esc_html__('Success Message', 'profilepress-pro'),
                'description' => esc_html__('Message displayed when passwordless login email is sent to a user email address.', 'profilepress-pro')
            ],
            'passwordless_email_notice'    => [
                'type'        => 'arbitrary',
                'data'        => '',
                'description' => sprintf(
                    '<div class="ppress-settings-page-notice">' . esc_html__('To customize the one-time passwordless email that is sent to login users, go to the %semail settings%s.', 'profilepress-pro'),
                    '<a target="_blank" href="' . add_query_arg('type', 'passwordless_login', PPRESS_SETTINGS_EMAIL_SETTING_PAGE) . '">', '</a>'
                )
            ]
        ];

        return $settings;
    }

    public function email_notifications($emails)
    {
        $emails[] = [
            'type'         => 'account',
            'key'          => 'passwordless_login',
            'title'        => esc_html__('One-time Passwordless Login Email', 'profilepress-pro'),
            'subject'      => sprintf(__('One-time login to %s', 'profilepress-pro'), ppress_site_title()),
            'message'      => ppress_passwordless_login_message_default(),
            'description'  => esc_html__('Email that is sent to the user upon one-time passwordless login request.', 'profilepress-pro'),
            'recipient'    => esc_html__('Users', 'profilepress-pro'),
            'placeholders' => [
                '{{username}}'          => esc_html__('Username of user.', 'profilepress-pro'),
                '{{first_name}}'        => esc_html__('First name of user.', 'profilepress-pro'),
                '{{last_name}}'         => esc_html__('Last name of user.', 'profilepress-pro'),
                '{{passwordless_link}}' => esc_html__('The one-time login URL.', 'profilepress-pro')
            ]
        ];

        return $emails;
    }

    public function is_passwordless_login_active($form_id, $form_type)
    {
        if ( ! empty($form_id) && ! empty($form_type)) {
            return FR::get_form_meta($form_id, $form_type, FR::PASSWORDLESS_LOGIN) == 'true';
        }

        return true;
    }

    /**
     * Callback function for authenticate hook.
     *
     * Validate the user supplied info.
     *
     * @param string $user
     * @param string $username
     * @param string $password
     *
     * @return WP_User|WP_Error|string
     */
    public function validate_user_login($user = '', $username = '', $password = '')
    {
        $this->form_id   = ppressPOST_var('login_form_id');
        $this->form_type = FR::LOGIN_TYPE;

        if ( ! empty($_POST['melange_form_id'])) {
            $this->form_id   = ppressPOST_var('melange_form_id');
            $this->form_type = FR::MELANGE_TYPE;
        }

        if ( ! $this->is_passwordless_login_active($this->form_id, $this->form_type)) return $user;

        do_action('ppress_before_passwordless_login_validation', $user, $username, $password);

        $no_user_error_msg = apply_filters('ppress_passwordless_no_user', esc_html__('Sorry, no user was found with that username or email.', 'profilepress-pro'));

        if (is_email($username)) {
            $user = get_user_by('email', $username);
        } else {
            $user = get_user_by('login', $username);
        }

        if (false !== $user) {

            self::$user_obj = $user;

            self::$user_id = $user->ID;

            self::$user_email = $user->user_email;

            self::$username = $user->user_login;

            // if "pp_skip_passwordless_login" filter return true, passwordless login is skipped.
            if (($this->is_disable_for_admin() && is_super_admin(self::$user_id))
                || apply_filters('ppress_skip_passwordless_login', false, $username, $password)
            ) {
                return wp_authenticate_username_password(null, $username, $password);
            }

            // send the one-time password login url
            $send_mail = $this->send_otp();

            if (is_wp_error($send_mail)) {
                return $send_mail;
            }

            return new WP_Error('otp_sent', $this->_get_success_message());
        }

        return new WP_Error('user_not_found', $no_user_error_msg);
    }

    /**
     * Generate the one-time login url
     *
     * @return string|WP_Error
     */
    private function _generate_ot_url()
    {
        $expiration = time() + 60 * ppress_get_setting('passwordless_expiration', 10, true);

        $token_length = apply_filters('ppress_passwordless_token_length', 20);
        $token        = wp_generate_password($token_length, false);

        $insert_data = PROFILEPRESS_sql::passwordless_insert_record(self::$user_id, $token, $expiration);

        if ($insert_data === false) {
            return new WP_Error('db_insert_failed', esc_html__('Unexpected error. Please try again', 'profilepress-pro'));
        }

        $data = [
            'uid'   => self::$user_id,
            'token' => $token,
        ];

        if ( ! empty($this->form_id)) {
            $data['form_id']   = $this->form_id;
            $data['form_type'] = $this->form_type;
        }

        return add_query_arg($data, wp_login_url());
    }

    public function parse_placeholders($content, $ot_url = '')
    {
        $search = apply_filters('ppress_passwordless_login_placeholder_search', array(
            '{{username}}',
            '{{passwordless_link}}',
            '{{first_name}}',
            '{{last_name}}',
        ));

        $replace = apply_filters('ppress_passwordless_login_placeholder_replace', array(
            self::$username,
            $ot_url,
            self::$user_obj->first_name,
            self::$user_obj->last_name,
        ));

        return str_replace($search, $replace, $content);
    }

    /**
     * Send the one-time login email
     *
     * @return bool|string|WP_Error
     */
    public function send_otp()
    {
        if ( ! apply_filters('ppress_passwordless_should_send_otp', true, self::$user_obj)) return false;

        if (ppress_get_setting('passwordless_login_email_enabled', 'on') != 'on') return;

        $ot_url = $this->_generate_ot_url();

        if (is_wp_error($ot_url)) return $ot_url;

        $default_mail_subject = sprintf(esc_html__("One-time login to %s", 'profilepress-pro'), ppress_site_title());

        $mail_subject = apply_filters(
            'ppress_passwordless_subject',
            $this->parse_placeholders(ppress_get_setting('passwordless_login_email_subject', $default_mail_subject, true))
        );

        $mail_content = apply_filters('ppress_passwordless_message', $this->message_content($ot_url));

        return ppress_send_email(self::$user_email, $mail_subject, $mail_content);
    }

    /**
     * Passwordless email content/message.
     *
     * @param string $ot_url one-time password
     *
     * @return string
     */
    public function message_content($ot_url)
    {
        $message = ppress_get_setting('passwordless_login_email_content', ppress_passwordless_login_message_default(), true);

        return apply_filters(
            'ppress_passwordless_login_message',
            $this->parse_placeholders($message, $ot_url),
            self::$user_obj
        );
    }

    private function _get_success_message()
    {
        $default = esc_html__('One-time login URL sent successfully to your email', 'profilepress-pro');

        return ppress_get_setting('passwordless_success_message', $default, true);
    }

    /**
     * Validate one-time login url
     */
    public function validate_one_time_login_url()
    {
        if (isset($_GET['token']) && isset($_GET['uid'])) {

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

                $uid       = sanitize_key($_GET['uid']);
                $token     = esc_attr($_GET['token']);
                $form_id   = isset($_GET['form_id']) ? absint(ppressGET_var('form_id')) : '';
                $form_type = sanitize_text_field(ppressGET_var('form_type', ''));

                $time       = time();
                $db_token   = PROFILEPRESS_sql::passwordless_get_user_token($uid);
                $db_expires = (int)PROFILEPRESS_sql::passwordless_get_expiration($uid);

                if (ppress_user_id_exist($uid) && $token == $db_token && $time < $db_expires) {

                    $secure_cookie = '';
                    // If the user wants ssl but the session is not ssl, force a secure cookie.
                    if ( ! force_ssl_admin()) {
                        if (get_user_option('use_ssl', $uid)) {
                            $secure_cookie = true;
                            force_ssl_admin(true);
                        }
                    }

                    /**
                     * Filter to enable remember me for passwordless login.
                     */
                    $remember_me = apply_filters('ppress_passwordless_login_remember', false);

                    wp_set_auth_cookie($uid, $remember_me, $secure_cookie);
                    wp_set_current_user($uid);

                    PROFILEPRESS_sql::passwordless_delete_record($uid);

                    $user = get_user_by('id', $uid);

                    do_action('wp_login', $user->user_login, $user);
                    nocache_headers();
                    wp_safe_redirect(
                        apply_filters('ppress_login_redirect', ppress_login_redirect(), 'ppress_passwordless_login', $user, $form_id, $form_type)
                    );
                    exit;
                }

                $default = esc_html__('One-time login has expired or is invalid.', 'profilepress-pro');

                wp_die(ppress_get_setting('passwordless_error', $default, true));
            }
        }
    }

    /**
     * User action link to resend passwordless login.
     *
     * @param array $actions
     * @param object $user_object
     *
     * @return mixed
     */
    public function resend_passwordless_login_link($actions, $user_object)
    {
        $current_user = wp_get_current_user();

        // do not display button for admin
        if ($current_user->ID != $user_object->ID) {

            // the unblock button
            $actions['send_passwordless'] = sprintf('<a href="%1$s">%2$s</a>',
                esc_url(
                    add_query_arg(
                        array(
                            'action'   => 'send_passwordless',
                            'user'     => $user_object->ID,
                            '_wpnonce' => wp_create_nonce('send-passwordless'),
                        ),
                        admin_url('users.php')
                    )
                ),
                esc_html__('Send passwordless login', 'profilepress-pro')
            );
        }

        return $actions;
    }

    /**
     * Check if passwordless login has been disabled for administrators.
     *
     * @return bool
     */
    public function is_disable_for_admin()
    {
        return ppress_get_setting('passwordless_disable_admin') == 'active';
    }

    public function act_on_send_passworldess_request()
    {
        $user_id = isset($_GET['user']) ? absint($_GET['user']) : '';

        if (isset($_GET['action'])) {

            if ('send_passwordless' == $_GET['action'] && check_admin_referer('send-passwordless')) {

                if ( ! current_user_can('manage_options')) return;

                $username = ppress_get_username_by_id($user_id);

                $this->validate_user_login(null, $username, null);

                wp_safe_redirect(add_query_arg('update', 'send_passwordless', admin_url('users.php')));
                exit;
            }

        }
    }

    public function admin_notices()
    {
        if (isset($_GET['update'])) {

            if ($_GET['update'] == 'send_passwordless') {
                echo '<div class="updated notice is-dismissible">';
                echo '<p>';
                _e('One-time passwordless login sent to user.', 'profilepress-pro');
                echo '</p>';
                echo '</div>';
            }
        }
    }

    /**
     * @return self|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::PASSWORDLESS_LOGIN')) return;

        if ( ! EM::is_enabled(EM::PASSWORDLESS_LOGIN)) return;

        static $instance;

        if ( ! isset($instance)) {
            $instance = new self;
        }

        return $instance;
    }
}