<?php

namespace ProfilePress\Libsodium\Licensing;

use ProfilePress\Core\Admin\SettingsPages\AbstractSettingsPage;
use ProfilePress\Custom_Settings_Page_Api;
use ProfilePressVendor\PAnD;

class Licensing
{
    const slug = 'pp-license';

    private $license_key;

    public function __construct()
    {
        $this->license_key = trim(get_option('ppress_license_key'));

        // unavailability of this class could potentially break all ajax requests.
        if (class_exists('ProfilePress\Libsodium\Licensing\LicenseControl')) {
            add_action('init', [$this, 'plugin_updater'], 0);
            add_action('admin_init', [$this, 'pp_plugin_check_license'], 0);
        }

        add_action('ppress_admin_notices', [$this, 'license_not_active_notice']);
        add_action('ppress_admin_notices', [$this, 'license_expired_notice']);

        add_filter('ppress_settings_page_tabs', [$this, 'license_tab']);
        add_action('ppress_admin_settings_page_license', [$this, 'admin_page']);

        add_filter('ppress_general_settings_admin_page_title', function ($title) {
            if (isset($_GET['view']) && $_GET['view'] == 'license') {
                $title = esc_html__('License', 'profilepress-pro');
            }

            return $title;
        });

        add_action('admin_init', function () {
            if (isset($_POST['pp_activate_license'])) {
                $this->activate_license();
            } elseif (isset($_POST['pp_deactivate_license'])) {
                $this->deactivate_license();
            } elseif (isset($_POST['ppress_save_license'])) {
                $this->save_license_key();
            }
        });
    }

    public function license_tab($tabs)
    {
        $tabs[999999999] = ['id' => 'license', 'url' => add_query_arg('view', 'license', PPRESS_SETTINGS_SETTING_PAGE), 'label' => esc_html__('License', 'profilepress-pro')];

        return $tabs;
    }

    /**
     * @return LicenseControl
     */
    public function license_control_instance()
    {
        return new LicenseControl(
            $this->license_key,
            PROFILEPRESS_PRO_SYSTEM_FILE_PATH,
            PROFILEPRESS_PRO_VERSION_NUMBER,
            PROFILEPRESS_PRO_ITEM_NAME,
            PROFILEPRESS_PRO_ITEM_ID
        );
    }

    /**
     * Check if the plugin license is active
     */
    public function pp_plugin_check_license()
    {
        if (false === get_transient('pp_license_check')) {

            $response = $this->license_control_instance()->check_license();

            if (is_wp_error($response)) {
                return false;
            }

            if ( ! empty($response->license)) {
                if ($response->license == 'valid') {
                    update_option('ppress_license_status', 'valid');
                    update_option('ppress_license_expired_status', 'false');
                } else {
                    if (in_array($response->license, ['expired', 'disabled'])) {
                        update_option('ppress_license_expired_status', 'true');
                    }
                    update_option('ppress_license_status', 'invalid');
                }
            }

            set_transient('pp_license_check', 'active', 24 * HOUR_IN_SECONDS);
        }
    }

    public function admin_page()
    {
        if (isset($_GET['license']) && $_GET['license'] == 'activated') {
            add_settings_error(self::slug, 'valid_license', esc_html__('License key activation successful.', 'profilepress-pro'), 'updated');
        } elseif (isset($_GET['license']) && $_GET['license'] == 'deactivated') {
            add_settings_error(self::slug, 'invalid_license', esc_html__('License key deactivation successful.', 'profilepress-pro'), 'updated');
        }

        add_filter('wp_cspa_main_content_area', [$this, 'license_page']);

        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name(self::slug);
        $instance->page_header(esc_html__('License', 'profilepress-pro'));
        AbstractSettingsPage::register_core_settings($instance);
        $instance->build();
    }

    /**
     * License settings page
     */
    public function license_page()
    {
        ob_start();
        $license = get_option('ppress_license_key');
        ?>

        <!--	Output Settings error	-->
        <?php settings_errors(self::slug); ?>
        <?php $this->license_banner(); ?>
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" valign="top">
                    <?php _e('License Key'); ?>
                </th>
                <td>
                    <input id="pp_license_key" name="pp_license_key" type="password" class="regular-text" value="<?php esc_attr_e($license); ?>"/>
                </td>
            </tr>
            <?php if (false !== $license) { ?>
                <tr valign="top" id="license_Activate_th">
                    <th scope="row" valign="top">
                        <?php _e('Activate License', 'profilepress-pro'); ?>
                    </th>
                    <td>
                        <?php if (LicenseControl::is_license_valid()) { ?>
                            <span style="color:green;"><?php _e('active'); ?></span>
                            <input type="submit" class="button-secondary" name="pp_deactivate_license" value="<?php _e('Deactivate License'); ?>"/>
                            <?php
                        } else {
                            ?>
                            <input type="submit" class="button-secondary" name="pp_activate_license" value="<?php _e('Activate License'); ?>"/>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php wp_nonce_field('pp_plugin_nonce', 'pp_plugin_nonce'); ?>
        <?php submit_button(null, 'primary', 'ppress_save_license'); ?>
        <script type="text/javascript">
            (function ($) {
                field = $('input#pp_license_key');
                var initial_value = field.val();
                var cache = $('tr#license_Activate_th');
                field.on('change', function () {
                    $(this).val() !== initial_value ? cache.hide() : cache.show();
                });
            })(jQuery);

        </script>
        <?php

        return ob_get_clean();
    }

    /**
     * Save License key to DB
     */
    public function save_license_key()
    {
        if ( ! check_admin_referer('pp_plugin_nonce', 'pp_plugin_nonce')) {
            return;
        }

        $old = $this->license_key;
        $new = esc_html($_POST['pp_license_key']);

        if ($old && $old != $new) {
            delete_option('ppress_license_status'); // new license has been entered, so must reactivate
        }

        update_option('ppress_license_key', $new);

        if ( ! empty($new)) {
            $this->activate_license($new, true);
        }

        wp_safe_redirect(esc_url_raw(add_query_arg('settings-updated', 'true', PPRESS_LICENSE_SETTINGS_PAGE)));
        exit;
    }

    /**
     * Activate License key
     */
    public function activate_license($license_key = '', $skp_csrf = false)
    {
        if ($skp_csrf === false) {
            if ( ! check_admin_referer('pp_plugin_nonce', 'pp_plugin_nonce')) return;
        }

        $response = $this->license_control_instance()->activate_license($license_key);

        if (is_wp_error($response)) {
            return add_settings_error(self::slug, 'activation_error', $response->get_error_message());
        }

        // $response->license will be either "valid" or "invalid"
        update_option('ppress_license_status', $response->license);

        if (in_array($response->license, ['expired', 'disabled'])) {
            update_option('ppress_license_expired_status', 'true');
        }

        if ($response->license == 'invalid') {
            return add_settings_error(self::slug, 'invalid_license', 'License key entered is invalid.');
        }

        if ($response->license == 'valid') {
            //first time activation
            update_option('ppress_license_expired_status', 'false');
            if ($skp_csrf === false) {
                wp_safe_redirect(add_query_arg('license', 'activated', PPRESS_LICENSE_SETTINGS_PAGE));
                exit;
            }
        }
    }

    /**
     * Plugin update method
     */
    public function plugin_updater()
    {
        $this->license_control_instance()->plugin_updater();
    }

    /**
     * Deactivate license
     */
    public function deactivate_license()
    {
        if ( ! check_admin_referer('pp_plugin_nonce', 'pp_plugin_nonce')) {
            return;
        }

        $response = $this->license_control_instance()->deactivate_license();

        if (is_wp_error($response)) {
            add_settings_error(self::slug, 'deactivation_error', $response->get_error_message());

            return;
        }

        if ($response->license == 'deactivated') {
            delete_option('ppress_license_status');
        }

        wp_safe_redirect(add_query_arg('license', 'deactivated', PPRESS_LICENSE_SETTINGS_PAGE));
        exit;
    }

    /**
     * License Banner
     */
    public function license_banner()
    {
        $message = '';

        if (LicenseControl::is_license_empty()) {
            $message = esc_html__('Enter a License Key', 'profilepress-pro');
        }

        if (LicenseControl::is_license_valid()) {
            $message = esc_html__('You have an active License', 'profilepress-pro');
        }

        if (LicenseControl::is_license_invalid()) {
            $message = esc_html__('License key is invalid or expired', 'profilepress-pro');
        }

        if ( ! empty($message)) {
            echo '<div class="ppress-banner">' . $message . '</div>';
        }
    }

    public function license_not_active_notice()
    {
        if ( ! is_super_admin()) return;

        if (LicenseControl::is_license_valid()) return;

        // bail if license is expired as this has its own admin notice.
        if (LicenseControl::is_license_expired()) return;

        echo '<div class="error notice"><p>' . sprintf(__('%sWelcome to ProfilePress Premium!%s Please %sactivate your license key%s or %srenew it%s to enable automatic updates.', 'profilepress-pro'),
                '<strong>',
                '</strong>',
                '<a href="' . add_query_arg('view', 'license', PPRESS_SETTINGS_SETTING_PAGE) . '">', '</a>',
                '<a target="_blank" href="https://profilepress.com/account/">', '</a>') . '</p></div>';
    }

    public function license_expired_notice()
    {
        if ( ! PAnD::is_admin_notice_active('ppress-license-expired-notice-7')) {
            return;
        }

        if ( ! is_super_admin()) return;

        // bail if license not expired.
        if ( ! LicenseControl::is_license_expired()) return;

        $licenseKey  = $this->license_key;
        $download_id = PROFILEPRESS_PRO_ITEM_ID;
        $renew_url   = ! empty($licenseKey) ? "https://profilepress.com/checkout/?edd_license_key={$licenseKey}&download_id={$download_id}" : 'https://profilepress.com/account/';

        echo '<div data-dismissible="ppress-license-expired-notice-7" class="error notice is-dismissible"><p>' .
             sprintf(
                 __('Your ProfilePress license has expired and all premium features disabled. %sClick here to renew your license%s to re-enable them.', 'profilepress-pro'),
                 "<a target='_blank' href=\"$renew_url\"><strong>", '</strong></a>'
             ) . '</p></div>';
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