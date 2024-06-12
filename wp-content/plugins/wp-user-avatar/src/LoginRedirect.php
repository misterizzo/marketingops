<?php

namespace ProfilePress\Core;

use ProfilePress\Core\Admin\SettingsPages\AbstractSettingsPage;
use ProfilePress\Core\Membership\Repositories\PlanRepository;
use ProfilePress\Custom_Settings_Page_Api;

class LoginRedirect
{
    const DB_OPTION_NAME = 'ppress_login_redirect_rules';

    public function __construct()
    {
        add_filter('ppress_settings_page_submenus_tabs', [$this, 'header_sub_menu_tab']);

        add_action('ppress_admin_settings_submenu_page_general_login-redirect', [$this, 'login_redirect_fields_page']);

        add_action('ppress_register_menu_page_general_login-redirect', function () {

            add_filter('ppress_general_settings_admin_page_title', function () {
                return esc_html__('Login Redirect', 'wp-user-avatar');
            });

            add_action('admin_init', [$this, 'save_settings']);

            add_action('admin_enqueue_scripts', [$this, 'enqueue_script']);
            add_action('admin_footer', [$this, 'js_template']);
        });

        add_filter('login_redirect', function ($redirect_to, $requested_redirect_to, $user) {
            return $this->get_login_redirect_url($user, $redirect_to);
        }, PHP_INT_MAX - 1, 3);

        add_filter('ppress_login_redirect', function ($url, $login_form_id, $user) {
            return $this->get_login_redirect_url($user, $url);
        }, PHP_INT_MAX - 1, 3);
    }

    /**
     * @param \WP_User $user
     * @param string $default_url
     *
     * @return false|string|null
     */
    public function get_login_redirect_url($user, $default_url = '')
    {
        if (isset($user->ID)) {

            $user_id = $user->ID;

            $saved_rules = get_option(self::DB_OPTION_NAME, []);

            $membership_plan_rules = ppress_var($saved_rules, 'membership_plan', []);

            $user_role_rules = ppress_var($saved_rules, 'user_role', []);

            if (is_array($membership_plan_rules) && ! empty($membership_plan_rules)) {

                foreach ($membership_plan_rules as $plan_id => $redirect_url_slug) {

                    if (ppress_has_active_subscription($user_id, $plan_id)) {
                        return site_url($redirect_url_slug);
                    }
                }
            }

            if (is_array($user_role_rules) && ! empty($user_role_rules)) {

                $user_roles = $user instanceof \WP_User ? $user->roles : [];

                foreach ($user_role_rules as $user_role => $redirect_url_slug) {

                    if (in_array($user_role, $user_roles, true)) {
                        return site_url($redirect_url_slug);
                    }
                }
            }
        }

        return $default_url;
    }

    public function enqueue_script()
    {
        wp_enqueue_script(
            'ppress-login-redirect',
            PPRESS_ASSETS_URL . '/js/admin/login-redirect.js',
            ['jquery', 'wp-util']
        );
    }

    public function header_sub_menu_tab($tabs)
    {
        $tabs[60] = ['parent' => 'general', 'id' => 'login-redirect', 'label' => esc_html__('Login Redirect', 'wp-user-avatar')];

        return $tabs;
    }

    public function save_settings()
    {
        if (isset($_POST['ppress_login_redirect_save'])) {

            check_admin_referer('wp-csa-nonce', 'wp_csa_nonce');

            $payload = ['membership_plan' => [], 'user_role' => []];

            if (isset($_POST['ppress_login_redirect']['membership_plan'])) {
                $payload['membership_plan'] = $this->sanitize_payload($_POST['ppress_login_redirect']['membership_plan']);
            }

            if (isset($_POST['ppress_login_redirect']['user_role'])) {
                $payload['user_role'] = $this->sanitize_payload($_POST['ppress_login_redirect']['user_role']);
            }

            update_option(self::DB_OPTION_NAME, $payload);

            wp_safe_redirect(esc_url_raw(add_query_arg('settings-updated', 'true')));
            exit;
        }
    }

    private function sanitize_payload($bucket)
    {
        $val = [];

        foreach ($bucket as $k => $v) {
            $val[sanitize_text_field($k)] = sanitize_text_field($v);
        }

        return $val;
    }

    public function login_redirect_fields_page()
    {
        add_filter('wp_cspa_main_content_area', function () {
            return $this->settings_page_content();
        });

        add_action('wp_cspa_form_tag', function () {
            echo 'id="ppress-login-redirect-form"';
        });

        add_action('wp_cspa_before_post_body_content', function () {
            echo '<div class="ppress-submit-wrap">';
            printf('<input type="submit" name="submit" id="ppress-login-redirect-submit-btn" class="button button-primary" value="%s">', esc_html__('Save Changes', 'wp-user-avatar'));
            echo '</div>';
        });

        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name(self::DB_OPTION_NAME);
        $instance->page_header(esc_html__('Login Redirect', 'wp-user-avatar'));
        AbstractSettingsPage::register_core_settings($instance);
        $instance->build();
    }

    public static function field_addition_dropdown($saved_rules, $fieldGroup = 'plans')
    {
        $lookup = array_keys($saved_rules);

        $user_roles = ppress_get_editable_roles(false);

        $membership_plans = PlanRepository::init()->retrieveAll();

        echo '<select class="ppress-login-redirect-select-field">';

        printf('<option value="">%s</option>', esc_html__('Select...', 'wp-user-avatar'));

        if ($fieldGroup == 'user_roles' && ! empty($user_roles)) {
            foreach ($user_roles as $user_role_id => $user_role) {
                printf('<option value="%s"%s>%s</option>', $user_role_id, in_array($user_role_id, $lookup) ? ' disabled' : '', $user_role['name']);
            }
        } else {
            foreach ($membership_plans as $membership_plan) {
                printf('<option value="%s"%s>%s</option>', $membership_plan->get_id(), in_array($membership_plan->get_id(), $lookup) ? ' disabled' : '', $membership_plan->get_name());
            }
        }

        echo '</select>';
    }

    private function settings_page_content()
    {
        $user_roles = ppress_get_editable_roles(false);

        $saved_data = get_option(self::DB_OPTION_NAME, []);

        $membership_plan_saved_rules = ppress_var($saved_data, 'membership_plan', []);
        $user_role_saved_rules       = ppress_var($saved_data, 'user_role', []);

        // we are using some checkout field manager page classes to get the styling. hence the mention of checkout-* classes
        ob_start();
        ?>
        <div class="ppress-login-redirect-rules-wrap" data-redirect-type="membership_plan">
            <div class="wp-clearfix" id="widgets-right">
                <div class="widgets-holder-wrap">
                    <div class="widgets-sortables">
                        <div class="sidebar-name">
                            <h2><?php esc_html_e('Login Redirect Based on Membership Plan', 'wp-user-avatar') ?></h2>
                        </div>
                        <div class="sidebar-description">
                            <p class="description"><?php esc_html_e('Add or remove rules that redirect users to specific URLs after login based on their subscribed membership plans.', 'wp-user-avatar') ?></p>
                        </div>
                        <div class="ppress-checkout-fields ppress-login-redirect-items-wrap">
                            <?php foreach ($membership_plan_saved_rules as $plan_id => $redirect_url_slug) : ?>
                                <?php $this->redirect_rule_item(
                                    'membership_plan',
                                    $plan_id,
                                    $redirect_url_slug,
                                    ppress_get_plan($plan_id)->get_name()
                                ); ?>
                            <?php endforeach; ?>

                            <div class="ppress-no-login-redirect-rule-message">
                                <p><?php esc_html_e('No redirect rule found. Please add one.', 'wp-user-avatar'); ?></p>
                            </div>
                        </div>
                        <div class="ppress-checkout-add-field ppress-login-redirect-add-rule">
                            <p style="text-align:right">
                                <?php self::field_addition_dropdown($membership_plan_saved_rules); ?>
                                <button class="button"><?php esc_html_e('Add Rule', 'wp-user-avatar') ?></button>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 15px" class="ppress-login-redirect-rules-wrap" data-redirect-type="user_role">
            <div class="wp-clearfix" id="widgets-right">
                <div class="widgets-holder-wrap">
                    <div class="widgets-sortables">
                        <div class="sidebar-name">
                            <h2><?php esc_html_e('Login Redirect Based on User Role', 'wp-user-avatar') ?></h2>
                        </div>
                        <div class="sidebar-description">
                            <p class="description"><?php esc_html_e('Add or remove rules that redirect users to specific URLs after login based on their user roles.', 'wp-user-avatar') ?></p>
                        </div>
                        <div class="ppress-checkout-fields ppress-login-redirect-items-wrap">
                            <?php foreach ($user_role_saved_rules as $user_role_id => $redirect_url_slug) : ?>
                                <?php $this->redirect_rule_item(
                                    'user_role',
                                    $user_role_id,
                                    $redirect_url_slug,
                                    ppress_var(ppress_var($user_roles, $user_role_id, []), 'name', '')
                                ); ?>
                            <?php endforeach; ?>

                            <div class="ppress-no-login-redirect-rule-message">
                                <p><?php esc_html_e('No redirect rule found. Please add one.', 'wp-user-avatar'); ?></p>
                            </div>
                        </div>
                        <div class="ppress-checkout-add-field ppress-login-redirect-add-rule">
                            <p style="text-align:right">
                                <?php self::field_addition_dropdown($user_role_saved_rules, 'user_roles'); ?>
                                <button class="button"><?php esc_html_e('Add Rule', 'wp-user-avatar') ?></button>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="ppress_login_redirect_save" value="true">
        <?php

        return ob_get_clean();
    }

    public function redirect_rule_item($redirect_type, $redirect_target, $redirect_url_slug, $label)
    {
        ?>
        <div class="ppress-login-redirect--repeater-item" data-redirect-target="<?php echo esc_attr($redirect_target); ?>">
            <label class="ppress-login-redirect--field-label"><?php echo esc_attr($label); ?></label>
            <div class="ppress-login-redirect--field-control">
                <div class="ppress-url-prefix-suffix-field">
                    <div class="ppress-url-prefix-field"><code><?php echo site_url('/'); ?></code></div>
                    <input type="text" name="ppress_login_redirect[<?php echo esc_attr($redirect_type); ?>][<?php echo esc_attr($redirect_target); ?>]" value="<?php echo esc_attr($redirect_url_slug); ?>" placeholder="wp-admin/">
                    <div class="ppress-url-suffix-field">
                        <button type="button" class="ppress-login-redirect--remove-field">
                            <span class="dashicons dashicons-no-alt"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function js_template()
    {
        echo '<script type="text/html" id="tmpl-ppress-login-redirect-item">';
        $this->redirect_rule_item('{{ data.redirect_type }}', '{{ data.redirect_target }}', '', '{{ data.label }}');
        echo '</script>';
    }

    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}