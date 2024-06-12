<?php

namespace ProfilePress\Core\Admin\SettingsPages;

use ProfilePress\Core\Classes\ExtensionManager;
use ProfilePress\Core\Classes\Installer\PluginSilentUpgrader;
use ProfilePress\Core\Classes\Installer\PluginSilentUpgraderSkin;
use ProfilePress\Custom_Settings_Page_Api;

class LicenseUpgrader
{
    public function __construct()
    {
        if ( ! ExtensionManager::is_premium()) {

            add_filter('ppress_settings_page_submenus_tabs', [$this, 'add_menu']);
            add_action('ppress_admin_settings_submenu_page_general_license', [$this, 'admin_page']);

            add_action('ppress_register_menu_page_general_license', function () {

                add_filter('ppress_general_settings_admin_page_title', function () {
                    return esc_html__('License', 'wp-user-avatar');
                });
            });

            add_action('admin_enqueue_scripts', [$this, 'settings_enqueues']);

            add_action('wp_ajax_ppress_connect_url', array($this, 'generate_url'));
        }

        add_action('wp_ajax_nopriv_ppress_connect_process', array($this, 'process'));
    }

    public function add_menu($tabs)
    {
        $tabs[-1] = ['parent' => 'general', 'id' => 'license', 'label' => esc_html__('License', 'wp-user-avatar')];

        return $tabs;
    }

    public function admin_page()
    {
        $settings = [
            [
                'section_title'         => '',
                'disable_submit_button' => true,
                'license_key'           => [
                    'type' => 'arbitrary',
                    'data' => $this->admin_settings_page_callback()
                ]
            ]
        ];

        $instance = Custom_Settings_Page_Api::instance($settings, 'ppress_license', esc_html__('License', 'wp-user-avatar'));
        $instance->remove_white_design();
        $instance->remove_h2_header();
        $instance->build(true);
    }

    public function admin_settings_page_callback()
    {
        $nonce = wp_create_nonce('ppress-connect-url');

        ob_start();

        ?>
        <style>
            .ppress-admin-wrap .wrap h2 {
                display: none;
            }

            .ppress-admin .remove_white_styling #post-body-content .form-table th {
                width: 200px !important;
            }

            .ppress-admin .remove_white_styling #post-body-content input[type=text] {
                width: 25em !important;
            }
        </style>

        <div class="ppress-lite-license-wrap">
            <p style="font-size: 110%;">
                <?php
                esc_html_e(
                    'You\'re using ProfilePress Lite - no license needed. Enjoy! 😊',
                    'wp-user-avatar'
                );
                ?>
            </p>

            <p class="description" style="margin-bottom: 8px;">
                <?php
                echo wp_kses_post(
                    sprintf(
                    /* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
                        __(
                            'Already purchased? Simply %1$sretrieve your license key%2$s and enter it below to connect with ProfilePress Pro.',
                            'wp-user-avatar'
                        ),
                        sprintf(
                            '<a href="%s" target="_blank" rel="noopener noreferrer">',
                            'https://profilepress.com/account/?utm_source=wp_dashboard&utm_medium=retrieve_license&utm_campaign=lite_license_page'
                        ),
                        '</a>'
                    )
                );
                ?>
            </p>

            <div class="ppress-license-field">
                <input
                        type="text"
                        id="ppress-connect-license-key"
                        name="ppress-license-key"
                        value=""
                        class="regular-text"
                        style="line-height: 1; font-size: 1.15rem; padding: 10px;"
                />

                <button
                        class="button button-secondary ppress-license-button"
                        id="ppress-connect-license-submit"
                        data-connecting="<?php esc_attr_e('Connecting...', 'wp-user-avatar'); ?>"
                        data-connect="<?php esc_attr_e('Unlock Pro Features Now', 'wp-user-avatar'); ?>"
                >
                    <?php esc_html_e('Unlock Pro Features Now', 'wp-user-avatar'); ?>
                </button>

                <input type="hidden" name="ppress-action" value="ppress-connect"/>
                <input type="hidden" id="ppress-connect-license-nonce" name="ppress-connect-license-nonce" value="<?php echo esc_attr($nonce); ?>"/>
            </div>

            <div id="ppress-connect-license-feedback" class="ppress-license-message"></div>

            <div class="ppress-settings-upgrade">
                <div class="ppress-settings-upgrade__inner">
                    <span class="dashicons dashicons-unlock" style="font-size: 40px; width: 40px; height: 50px;"></span>
                    <h3>
                        <?php esc_html_e('Unlock Powerful Pro Features', 'wp-user-avatar'); ?>
                    </h3>

                    <ul>
                        <li>
                            <div class="dashicons dashicons-yes"></div>
                            <?php esc_html_e('No extra 2% Stripe fee', 'wp-user-avatar'); ?>
                        </li>
                        <li>
                            <div class="dashicons dashicons-yes"></div>
                            <a href="https://profilepress.com/addons/paypal/?utm_source=wp_dashboard&utm_medium=retrieve_license&utm_campaign=lite_license_page" target="_blank" rel="noopener noreferrer">
                                <?php esc_html_e('Collect PayPal payments', 'wp-user-avatar'); ?>
                            </a>
                        </li>
                        <li>
                            <div class="dashicons dashicons-yes"></div>
                            <a href="https://profilepress.com/addons/?utm_source=wp_dashboard&utm_medium=retrieve_license&utm_campaign=lite_license_page" target="_blank" rel="noopener noreferrer">
                                <?php esc_html_e('Mollie & Razorpay gateways', 'wp-user-avatar'); ?>
                            </a>
                        </li>
                        <li>
                            <div class="dashicons dashicons-yes"></div>
                            <?php esc_html_e('Premium form & profile themes', 'wp-user-avatar'); ?>
                        </li>
                        <li>
                            <div class="dashicons dashicons-yes"></div>
                            <?php esc_html_e('Premium directory themes', 'wp-user-avatar'); ?>
                        </li>
                        <li>
                            <div class="dashicons dashicons-yes"></div>
                            <a href="https://profilepress.com/article/drag-drop-advanced-shortcode-builders/?utm_source=wp_dashboard&utm_medium=retrieve_license&utm_campaign=lite_license_page" target="_blank" rel="noopener noreferrer">
                                <?php esc_html_e('Advanced shortcode builder', 'wp-user-avatar'); ?>
                            </a>
                        </li>
                        <li>
                            <div class="dashicons dashicons-yes"></div>
                            <a href="https://profilepress.com/addons/custom-fields/?utm_source=wp_dashboard&utm_medium=retrieve_license&utm_campaign=lite_license_page" target="_blank" rel="noopener noreferrer">
                                <?php esc_html_e('Custom Fields', 'wp-user-avatar'); ?>
                            </a>
                        </li>
                        <li>
                            <div class="dashicons dashicons-yes"></div>
                            <a href="https://profilepress.com/addons/social-login/?utm_source=wp_dashboard&utm_medium=retrieve_license&utm_campaign=lite_license_page" target="_blank" rel="noopener noreferrer">
                                <?php esc_html_e('Social Login', 'wp-user-avatar'); ?>
                            </a>
                        </li>
                        <li>
                            <div class="dashicons dashicons-yes"></div>
                            <a href="https://profilepress.com/addons/metered-paywall/?utm_source=wp_dashboard&utm_medium=retrieve_license&utm_campaign=lite_license_page" target="_blank" rel="noopener noreferrer">
                                <?php esc_html_e('Metered Paywall', 'wp-user-avatar'); ?>
                            </a>
                        </li>
                        <li>
                            <div class="dashicons dashicons-yes"></div>
                            <a href="https://profilepress.com/addons/user-moderation/?utm_source=wp_dashboard&utm_medium=retrieve_license&utm_campaign=lite_license_page" target="_blank" rel="noopener noreferrer">
                                <?php esc_html_e('User Moderation', 'wp-user-avatar'); ?>
                            </a>
                        </li>
                        <li>
                            <div class="dashicons dashicons-yes"></div>
                            <a href="https://profilepress.com/addons/passwordless-login/?utm_source=wp_dashboard&utm_medium=retrieve_license&utm_campaign=lite_license_page" target="_blank" rel="noopener noreferrer">
                                <?php esc_html_e('Passwordless Login', 'wp-user-avatar'); ?>
                            </a>
                        </li>
                        <li>
                            <div class="dashicons dashicons-yes"></div>
                            <a href="https://profilepress.com/addons/?utm_source=wp_dashboard&utm_medium=retrieve_license&utm_campaign=lite_license_page" target="_blank" rel="noopener noreferrer">
                                <?php esc_html_e('And more addons', 'wp-user-avatar'); ?>
                            </a>
                        </li>
                    </ul>

                    <a href="https://profilepress.com/pricing/?discount=10PPOFF&utm_source=wp_dashboard&utm_medium=retrieve_license&utm_campaign=lite_license_page" class="button button-primary button-large ppress-upgrade-btn ppress-upgrade-btn-large" target="_blank" rel="noopener noreferrer">
                        <?php esc_html_e('Upgrade to ProfilePress Pro', 'wp-user-avatar'); ?>
                    </a>
                </div>

                <div class="ppress-upgrade-btn-subtext">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" role="img" aria-hidden="true" focusable="false">
                        <path d="M16.7 7.1l-6.3 8.5-3.3-2.5-.9 1.2 4.5 3.4L17.9 8z"></path>
                    </svg>

                    <?php
                    echo wp_kses(
                        sprintf(
                        /* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
                            __(
                                '<strong>Bonus</strong>: Loyal ProfilePress Lite users get <u>10%% off</u> regular price, automatically applied at checkout. %1$sUpgrade to Pro →%2$s',
                                'wp-user-avatar'
                            ),
                            sprintf(
                                '<a href="%s" rel="noopener noreferrer" target="_blank">',
                                'https://profilepress.com/pricing/?discount=10PPOFF&utm_source=wp_dashboard&utm_medium=retrieve_license&utm_campaign=lite_license_page'
                            ),
                            '</a>'
                        ),
                        array(
                            'a'      => array(
                                'href'   => true,
                                'rel'    => true,
                                'target' => true,
                            ),
                            'strong' => array(),
                            'u'      => array(),
                        )
                    );
                    ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function settings_enqueues()
    {
        wp_enqueue_script(
            'ppress-license-connect',
            PPRESS_ASSETS_URL . "/js/admin/license.js",
            ['jquery'],
            PPRESS_VERSION_NUMBER,
            true
        );
    }

    public function generate_url()
    {
        check_ajax_referer('ppress-connect-url', 'nonce');

        // Check for permissions.
        if ( ! current_user_can('install_plugins')) {
            wp_send_json_error(['message' => esc_html__('You are not allowed to install plugins.', 'wp-user-avatar')]);
        }

        $key = ! empty($_POST['key']) ? sanitize_text_field(wp_unslash($_POST['key'])) : '';

        if (empty($key)) {
            wp_send_json_error(['message' => esc_html__('Please enter your license key to connect.', 'wp-user-avatar')]);
        }

        if (ExtensionManager::is_premium()) {
            wp_send_json_error(['message' => esc_html__('Only the Lite version can be upgraded.', 'wp-user-avatar')]);
        }

        $active = activate_plugin('profilepress-pro/profilepress-pro.php', false, false, true);

        if ( ! is_wp_error($active)) {

            update_option('ppress_license_key', $key);

            wp_send_json_success([
                'message' => \esc_html__('You already have ProfilePress Pro installed! Activating it now', 'wp-user-avatar'),
                'reload'  => true,
            ]);
        }

        $oth = hash('sha512', wp_rand());

        update_option('ppress_connect_token', $oth);
        update_option('ppress_license_key', $key);

        $version  = PPRESS_VERSION_NUMBER;
        $endpoint = admin_url('admin-ajax.php');
        $redirect = PPRESS_SETTINGS_SETTING_GENERAL_PAGE;
        $url      = add_query_arg(
            [
                'key'      => $key,
                'oth'      => $oth,
                'endpoint' => $endpoint,
                'version'  => $version,
                'siteurl'  => \admin_url(),
                'homeurl'  => \home_url(),
                'redirect' => rawurldecode(base64_encode($redirect)), // phpcs:ignore
                'v'        => 1,
            ],
            'https://upgrade.profilepress.com'
        );

        wp_send_json_success(['url' => $url]);
    }

    public function process()
    {
        $error = wp_kses(
            sprintf(
            /* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
                __(
                    'Oops! We could not automatically install an upgrade. Please download the plugin from profilepress.com and install it manually.',
                    'wp-user-avatar'
                )
            ),
            [
                'a' => [
                    'target' => true,
                    'href'   => true,
                ],
            ]
        );

        $post_oth = ! empty($_REQUEST['oth']) ? sanitize_text_field($_REQUEST['oth']) : '';
        $post_url = ! empty($_REQUEST['file']) ? esc_url_raw($_REQUEST['file']) : '';

        $license = get_option('ppress_license_key', '');

        if (empty($post_oth) || empty($post_url)) {
            wp_send_json_error(['message' => $error, 'code_err' => '1']);
        }

        $oth = get_option('ppress_connect_token');

        if (empty($oth)) {
            wp_send_json_error(['message' => $error, 'code_err' => '2']);
        }

        if ( ! hash_equals($oth, $post_oth)) {
            wp_send_json_error(['message' => $error, 'code_err' => '3']);
        }

        delete_option('ppress_connect_token');

        // Set the current screen to avoid undefined notices.
        set_current_screen('profilepress_page_ppress-config');

        $url = PPRESS_SETTINGS_SETTING_GENERAL_PAGE;

        // Verify pro not activated.
        if (ExtensionManager::is_premium()) {
            wp_send_json_success(esc_html__('Plugin installed & activated.', 'wp-user-avatar'));
        }

        // Verify pro not installed.
        $active = activate_plugin('profilepress-pro/profilepress-pro.php', $url, false, true);

        if ( ! is_wp_error($active)) {

            wp_send_json_success([
                'message'  => esc_html__('Plugin installed & activated.', 'wp-user-avatar'),
                'code_err' => '3.5'
            ]);
        }

        $creds = request_filesystem_credentials($url, '', false, false, null);

        // Check for file system permissions.
        if (false === $creds || ! \WP_Filesystem($creds)) {
            wp_send_json_error(['message' => $error, 'code_err' => '4']);
        }

        /*
         * We do not need any extra credentials if we have gotten this far, so let's install the plugin.
         */

        // Do not allow WordPress to search/download translations, as this will break JS output.
        remove_action('upgrader_process_complete', ['Language_Pack_Upgrader', 'async_upgrade'], 20);

        // Create the plugin upgrader with our custom skin.
        $installer = new PluginSilentUpgrader(new PluginSilentUpgraderSkin());

        // Error check.
        if ( ! method_exists($installer, 'install')) {
            wp_send_json_error(['message' => $error, 'code_err' => '5']);
        }

        if (empty($license)) {
            wp_send_json_error([
                'message'  => esc_html__('You are not licensed.', 'wp-user-avatar'),
                'code_err' => '6'
            ]);
        }

        $installer->install($post_url);

        // Flush the cache and return the newly installed plugin basename.
        wp_cache_flush();

        $plugin_basename = $installer->plugin_info();

        if ($plugin_basename) {

            // Activate the plugin silently.
            $activated = activate_plugin($plugin_basename, '', false, true);

            if ( ! is_wp_error($activated)) {
                wp_send_json_success(esc_html__('Plugin installed & activated.', 'wp-user-avatar'));
            }
        }

        wp_send_json_error(['message' => $error, 'code_err' => '7']);
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
