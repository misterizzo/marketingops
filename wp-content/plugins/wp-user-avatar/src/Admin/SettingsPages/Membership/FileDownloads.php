<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership;

use ProfilePress\Core\Admin\SettingsPages\AbstractSettingsPage;
use ProfilePress\Custom_Settings_Page_Api;

class FileDownloads
{
    public function __construct()
    {
        add_filter('ppress_settings_page_submenus_tabs', [$this, 'header_sub_menu_tab']);

        add_action('ppress_admin_settings_submenu_page_payments_file-downloads', [$this, 'file_downloads_page']);

        add_action('ppress_register_menu_page_payments_file-downloads', function () {

            add_filter('ppress_general_settings_admin_page_title', function () {
                return esc_html__('File Downloads &lsaquo; Payments', 'wp-user-avatar');
            });
        });
    }

    public function header_sub_menu_tab($tabs)
    {
        $tabs[176] = ['parent' => 'payments', 'id' => 'file-downloads', 'label' => esc_html__('File Downloads', 'wp-user-avatar')];

        return $tabs;
    }

    public function file_downloads_page()
    {
        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name(PPRESS_FILE_DOWNLOADS_OPTION_NAME);
        $instance->page_header(esc_html__('File Downloads', 'wp-user-avatar'));

        $instance->add_view_classes('ppress-file-downloads');

        $settings = [
            [
                'download_method'             => [
                    'label'       => esc_html__('Download Method', 'wp-user-avatar'),
                    'type'        => 'select',
                    'options'     => [
                        'direct'    => esc_html__('Force Downloads', 'wp-user-redirect'),
                        'xsendfile' => esc_html__('X-Accel-Redirect/X-Sendfile', 'wp-user-redirect'),
                        'redirect'  => esc_html__('Redirect (Insecure)', 'wp-user-redirect')
                    ],
                    'description' => sprintf(
                        __("Select the file download method. If you are using X-Accel-Redirect/X-Sendfile download method (recommended, especially for delivery large files), make sure that you have applied settings as described in <a href='%s'>Downloadable Product Handling</a> guide.", 'wp-user-avatar'),
                        'https://profilepress.com/article/sell-downloads-wordpress-membership/#servers'
                    )
                ],
                'download_limit'              => [
                    'label'       => esc_html__('Download Limit', 'wp-user-avatar'),
                    'description' => esc_html__('The maximum number of times files can be downloaded (Leaving blank or using a value of 0 is unlimited). Can be overwritten for each membership plan.', 'wp-user-avatar'),
                    'value'       => '0',
                    'type'        => 'number'
                ],
                'download_expiry'             => [
                    'label'       => esc_html__('Download Expiry', 'wp-user-avatar'),
                    'description' => esc_html__('How long in days should download links be valid for? Default is 1 day from the time they are generated. Can be overwritten for each membership plan.', 'wp-user-avatar'),
                    'value'       => '1',
                    'type'        => 'number'
                ],
                'access_restriction'          => [
                    'label'          => esc_html__('Access restriction', 'wp-user-avatar'),
                    'checkbox_label' => esc_html__('Downloads require login', 'wp-user-avatar'),
                    'description'    => esc_html__('Enable to require users to be logged in to download files.', 'wp-user-avatar'),
                    'type'           => 'checkbox'
                ],
                'downloads_add_hash_filename' => [
                    'label'          => esc_html__('Filename', 'wp-user-avatar'),
                    'checkbox_label' => esc_html__('Append a unique string to filename for security', 'wp-user-avatar'),
                    'description'    => sprintf(
                        __("Not required if your download directory is protected. <a href='%s'>See this guide</a> for more details. Files already uploaded will not be affected.", 'woocommerce'),
                        'https://profilepress.com/article/sell-downloads-wordpress-membership/#unique-string'
                    ),
                    'type'           => 'checkbox'
                ]
            ]
        ];

        $instance->main_content($settings);
        $instance->remove_white_design();
        AbstractSettingsPage::register_core_settings($instance);
        $instance->build();
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