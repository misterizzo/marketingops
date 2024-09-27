<?php

namespace ProfilePress\Libsodium\TWOFA;

use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Classes\FormRepository as FR;
use ProfilePress\Core\ShortcodeParser\MyAccount\MyAccountTag;
use ProfilePress\Libsodium\ShortcodeBuilder\ShortcodeInserterTrait;
use WP_Error;
use WP_User;

class Init
{
    use ShortcodeInserterTrait;

    public static $instance_flag = false;

    public function __construct()
    {
        MyAccount::get_instance();
        UserProfile::get_instance();
        Shortcode::get_instance();

        add_filter('wp_authenticate_user', [$this, 'login_authentication'], PHP_INT_MAX - 1);

        add_action('wp', [$this, 'enforce_user_2fa_frontend'], 1);
        add_action('current_screen', [$this, 'enforce_user_2fa_admin'], 1);

        add_action('wp_ajax_ppress_2fa_reset_user', [$this, 'reset_user_ajax_handler']);
        add_action('wp_ajax_ppress_2fa_generate_recovery_codes', [$this, 'generate_recovery_codes_ajax_handler']);

        add_shortcode('pp-2fa', array($this, 'twofa_field_shortcode'));

        add_action('ppress_drag_drop_builder_field_init_after', function ($form_type) {

            if ($form_type == FR::LOGIN_TYPE) {
                new DNDLoginField();
            }
        });

        add_filter('ppress_form_default_fields_settings', function ($defaults) {

            $defaults['pp-2fa'] = [
                'placeholder' => esc_html__('Authentication Code', 'profilepress-pro')
            ];

            return $defaults;
        });

        add_filter('ppress_global_available_shortcodes', [$this, 'shortcode_builder_shortcode_ui']);
        add_filter('manage_users_columns', array(__CLASS__, 'filter_manage_users_columns'));
        add_filter('wpmu_users_columns', array(__CLASS__, 'filter_manage_users_columns'));
        add_filter('manage_users_custom_column', array(__CLASS__, 'manage_users_custom_column'), 10, 3);
        add_filter('ppress_settings_page_args', [$this, 'settings_page']);
    }

    public static function filter_manage_users_columns(array $columns)
    {
        $columns['ppress-2fa'] = __('2-Factor', 'two-factor');

        return $columns;
    }

    public static function manage_users_custom_column($output, $column_name, $user_id)
    {

        if ('ppress-2fa' == $column_name) {

            $output = sprintf('<span class="dashicons-before dashicons-no-alt">%s</span>', esc_html__('Disabled', 'profilepress-pro'));

            if (Common::has_2fa_configured($user_id)) {
                $output = sprintf('<span class="dashicons-before dashicons-yes">%s</span>', esc_html__('Enabled', 'profilepress-pro'));
            }
        }

        return $output;
    }

    public function settings_page($args)
    {
        $args['pp_2fa_settings'] = [
            'tab_title'                      => esc_html__('Two-Factor Authentication', 'profilepress-pro'),
            'section_title'                  => esc_html__('Two-Factor Authentication (2FA)', 'profilepress-pro'),
            'dashicon'                       => 'dashicons-shield-alt',
            '2fa_user_roles'                 => [
                'type'        => 'select2',
                'options'     => ppress_wp_roles_key_value(false),
                'label'       => esc_html__('User Roles', 'profilepress-pro'),
                'description' => sprintf(
                    esc_html__('Select the user roles that can set up 2FA. Leave empty for all users. %sLearn more%s', 'profilepress-pro'),
                    '<a target="_blank" href="https://profilepress.com/article/wordpress-two-factor-authentication-setup/">', '</a>'
                )
            ],
            '2fa_enforce_user_roles'         => [
                'type'        => 'select2',
                'options'     => ppress_wp_roles_key_value(false),
                'label'       => esc_html__('Enforce 2FA', 'profilepress-pro'),
                'description' => sprintf(
                    esc_html__('By default, 2FA is optional for users. This setting forces users of selected roles to activate two-factor before they can use the site. %sLearn more%s', 'profilepress-pro'),
                    '<a target="_blank" href="https://profilepress.com/article/wordpress-two-factor-authentication-setup/">', '</a>'
                ),
            ],
            '2fa_enforce_user_roles_message' => [
                'type'        => 'textarea',
                'value'       => esc_html__('You are required to set up two-factor authentication to use this site.', 'profilepress-pro'),
                'label'       => esc_html__('Enforce 2FA Message', 'profilepress-pro'),
                'description' => esc_html__('Message shown to users that are required to set up 2FA before they can use the site.', 'profilepress-pro')
            ],
            '2fa_page_url'                   => [
                'type'        => 'text',
                'label'       => esc_html__('Custom 2FA Page URL', 'profilepress-pro'),
                'description' => sprintf(
                    esc_html__('If using a %scustom 2FA page%s, enter the page URL to redirect users to. If empty, it defaults to the My Account page or WordPress user profile page if it does not exist. %sLearn more%s', 'profilepress-pro'),
                    '<a target="_blank" href="https://profilepress.com/article/custom-2fa-configuration-page/">', '</a>',
                    '<a target="_blank" href="https://profilepress.com/article/wordpress-two-factor-authentication-setup/">', '</a>'
                ),
            ]
        ];

        return $args;
    }

    public function twofa_field_shortcode($atts)
    {
        $other_atts_html = ppress_other_field_atts($atts);

        $atts = shortcode_atts(
            array(
                'class'       => '',
                'id'          => '',
                'value'       => '',
                'placeholder' => esc_html__('Authentication Code', 'profilepress-pro')
            ),
            $atts
        );

        $atts = apply_filters('ppress_login_2fa_field_atts', $atts);

        $class       = ! empty($atts['class']) ? 'class="' . $atts['class'] . '"' : null;
        $placeholder = ! empty($atts['placeholder']) ? 'placeholder="' . $atts['placeholder'] . '"' : null;
        $id          = ! empty($atts['id']) ? 'id="' . $atts['id'] . '"' : null;

        $html = "<input name=\"ppress_2fa_code\" autocomplete=\"one-time-code\" type=\"text\" $class $placeholder $id $other_atts_html>";

        return apply_filters('ppress_login_2fa_field', $html, $atts);
    }

    /**
     * @param WP_User $user
     *
     * @return mixed|WP_Error
     */
    public static function login_authentication($user)
    {
        if ( ! apply_filters('ppress_2fa_is_disabled', false, $user)) {

            if (Common::has_2fa_configured($user->ID)) {

                $secret = Common::get_secret_code($user->ID);

                if (empty($_POST['ppress_2fa_code'])) {
                    return new WP_Error('pp2fa_auth_code_invalid', esc_html__('Enter a authenticator app code or a recovery code.', 'profilepress-pro'));
                }

                $auth_code = sanitize_text_field($_POST['ppress_2fa_code']);

                if (
                    ! Common::verify_auth_code($auth_code, $secret)
                    && ! Common::verify_backup_code($user->ID, $auth_code)
                ) {
                    return new WP_Error('pp2fa_auth_code_invalid', esc_html__('Invalid authentication code. Please try again.', 'profilepress-pro'));
                }
            }
        }

        return $user;
    }

    public function enforceable_redirect_rule($user_id)
    {
        static $cache = false;

        if ($cache === false) {

            $redirect_url = get_edit_profile_url($user_id) . '#ppress-2fa';
            $state        = 'admin';

            $page_id = ppress_settings_by_key('edit_user_profile_url');

            if (
                ! empty($page_id) &&
                get_post_status($page_id) &&
                has_shortcode(get_post($page_id)->post_content, 'profilepress-my-account')
            ) {
                $redirect_url = MyAccountTag::get_endpoint_url('account-settings');
                $state        = 'myaccount';
            }

            $custom_2fa_page_url = ppress_settings_by_key('2fa_page_url');

            if ( ! empty($custom_2fa_page_url)) {
                $redirect_url = $custom_2fa_page_url;
                $state        = 'frontend';
            }

            $cache = ['state' => $state, 'url' => $redirect_url];
        }

        return $cache;
    }

    public function enforce_user_2fa_admin()
    {
        if ( ! is_admin() || wp_doing_ajax()) return;

        $user_id = get_current_user_id();

        if ( ! $user_id) return;

        if ( ! Common::is_user_2fa_enforced($user_id)) return;

        $screen = get_current_screen();

        // skip for main dashboard and profilepress admin pages
        if (current_user_can('manage_options')) {
            if ('dashboard' == $screen->id) return;
            if (ppress_is_admin_page()) return;
        }

        if ('profile' !== $screen->id) {
            wp_safe_redirect(get_edit_profile_url($user_id) . '#ppress-2fa');
            exit;
        }
    }

    public function enforce_user_2fa_frontend()
    {
        if (is_admin()) return;

        $user_id = get_current_user_id();

        if ( ! $user_id) return;

        if (wp_doing_ajax()) return;

        if ( ! Common::is_user_2fa_enforced($user_id)) return;

        $redirect_rule = $this->enforceable_redirect_rule($user_id);

        $url   = $redirect_rule['url'];
        $state = $redirect_rule['state'];

        $page_id = ppress_settings_by_key('edit_user_profile_url');

        if ($state == 'myaccount' && ! empty($page_id) && ! is_page($page_id)) {
            wp_safe_redirect($url);
            exit;
        }

        if ($state == 'frontend') {
            $current_url = ppress_get_current_url_query_string();
            /** strtok() remove all query strings and trailing slash. @see https://stackoverflow.com/a/6975045/2648410 */
            $comp_redirect_url = untrailingslashit(strtok($url, '?'));

            if (strpos($current_url, $comp_redirect_url) === false) {
                wp_safe_redirect($url);
                exit;
            }
        }

        if ($state == 'admin') {
            wp_safe_redirect($url);
            exit;
        }
    }

    public function shortcode_builder_shortcode_ui($shortcodes)
    {
        $shortcodes['pp-2fa'] = [
            'description' => esc_html__('Displays 2FA auth code field', 'profilepress-pro'),
            'shortcode'   => 'pp-2fa',
            'attributes'  => self::popular_attributes()
        ];

        return $shortcodes;
    }

    public function reset_user_ajax_handler()
    {
        check_ajax_referer('ppress_2fa_reset_user', 'pp2fa_nonce');

        $user_id = absint($_POST['user_id']);

        if (current_user_can('edit_user', $user_id)) {
            Common::disable_2fa($user_id);
            wp_send_json_success(
                esc_html__('Two-Factor authentication disabled successfully.', 'profilepress-pro')
            );
        }

        wp_send_json_error();
    }

    public function generate_recovery_codes_ajax_handler()
    {
        ob_start();
        AbstractClass::generate_recovery_codes_html(absint($_POST['user_id']));
        wp_send_json_success(ob_get_clean());
    }

    /**
     * @return Init|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::TWOFA')) return;

        if ( ! EM::is_enabled(EM::TWOFA)) return;

        static $instance;
        if ( ! isset($instance)) {
            $instance = new self;
        }

        return $instance;
    }
}