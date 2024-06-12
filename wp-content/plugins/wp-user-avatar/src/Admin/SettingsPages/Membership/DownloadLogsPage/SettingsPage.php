<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\DownloadLogsPage;

use ProfilePress\Core\Admin\SettingsPages\AbstractSettingsPage;
use ProfilePress\Custom_Settings_Page_Api;

class SettingsPage extends AbstractSettingsPage
{
    public $error_bucket;

    /** @var WPListTable */
    private $listTable;

    function __construct()
    {
        add_action('ppress_admin_settings_page_download-logs', [$this, 'settings_page_function']);
        add_filter('ppress_membership_dashboard_settings_page_title', [$this, 'admin_page_title']);
        add_action('ppress_membership_reports_settings_page_register', function ($hook) {
            add_action("load-$hook", array($this, 'add_options'));
        });

        if (ppressGET_var('page') == PPRESS_MEMBERSHIP_PLANS_SETTINGS_SLUG && ppressGET_var('view') == 'download-logs') {
            add_filter('set-screen-option', [__CLASS__, 'set_screen'], 10, 3);
            add_filter('set_screen_option_rules_per_page', [__CLASS__, 'set_screen'], 10, 3);
        }
    }

    public function admin_page_title($title = '')
    {
        if (ppressGET_var('view') == 'download-logs') {
            $title = esc_html__('Download Logs', 'wp-user-avatar');
        }

        return $title;
    }

    public static function set_screen($status, $option, $value)
    {
        return $value;
    }

    public function add_options()
    {
        if (ppressGET_var('view') == 'download-logs') {

            $args = [
                'label'   => esc_html__('Download Log', 'wp-user-avatar'),
                'default' => 10,
                'option'  => 'download_logs_per_page'
            ];

            add_screen_option('per_page', $args);

            $this->listTable = new WPListTable();
        }
    }

    public function admin_notices()
    {
        if ( ! isset($_GET['saved']) && ! isset($this->error_bucket)) return;

        $status  = 'updated';
        $message = '';

        if ( ! empty($this->error_bucket)) {
            $message = $this->error_bucket;
            $status  = 'error';
        }

        if (isset($_GET['saved'])) {
            $message = esc_html__('Changes saved.', 'wp-user-avatar');
        }

        printf('<div id="message" class="%s notice is-dismissible"><p>%s</strong></p></div>', $status, $message);
    }

    public function settings_page_function()
    {
        add_action('wp_cspa_main_content_area', [$this, 'admin_settings_page_callback'], 10, 2);

        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name('ppview'); // adds ppview css class to #poststuff
        $instance->page_header($this->admin_page_title());
        $instance->build(true);
    }

    public function admin_settings_page_callback()
    {
        $this->listTable->prepare_items(); // has to be here.

        echo '<form method="post">';
        $this->listTable->display();
        echo '</form>';
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