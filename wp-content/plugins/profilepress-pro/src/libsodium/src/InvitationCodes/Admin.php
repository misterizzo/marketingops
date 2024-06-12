<?php

namespace ProfilePress\Libsodium\InvitationCodes;

use ProfilePress\Core\Admin\SettingsPages\AbstractSettingsPage;
use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Custom_Settings_Page_Api;

class Admin
{
    /** @var WPListTable */
    private $myListTable;

    public function __construct()
    {
        add_filter('ppress_settings_page_screen_option', [$this, 'screen_options']);
        add_filter('ppress_settings_page_tabs', [$this, 'settings_tab']);
        add_filter('ppress_settings_page_submenus_tabs', [$this, 'settings_submenu_tab']);
        add_action('ppress_admin_settings_submenu_page_invite-codes_invite-codes', [$this, 'invite_codes_admin_page']);
        add_action('ppress_admin_settings_submenu_page_invite-codes_generate-codes', [$this, 'generate_codes_admin_page']);
        add_action('ppress_admin_settings_submenu_page_invite-codes_code-list', [$this, 'code_list_admin_page']);

        add_action('admin_init', [$this, 'form_handler']);

        add_filter('ppress_melange_available_shortcodes', [$this, 'add_available_shortcode_popup'], 10);
        add_filter('ppress_reg_edit_profile_available_shortcodes', function ($shortcodes, $type) {
            if ('reg' == $type) $shortcodes = $this->add_available_shortcode_popup($shortcodes);

            return $shortcodes;
        }, 10, 2);

        if (is_admin()) {
            add_action('pre_user_query', [$this, 'pre_user_query']);
        }
    }

    public function settings_tab($tabs)
    {
        $tabs[45] = ['id' => 'invite-codes', 'url' => add_query_arg('view', 'invite-codes', PPRESS_SETTINGS_SETTING_PAGE), 'label' => esc_html__('Invite Codes', 'profilepress-pro')];

        return $tabs;
    }

    public function settings_submenu_tab($tabs)
    {
        $tabs[151] = ['parent' => 'invite-codes', 'id' => 'invite-codes', 'label' => esc_html__('Invite Codes', 'profilepress-pro')];
        $tabs[152] = ['parent' => 'invite-codes', 'id' => 'generate-codes', 'label' => esc_html__('Auto Generate Codes', 'profilepress-pro')];
        $tabs[153] = ['parent' => 'invite-codes', 'id' => 'code-list', 'label' => esc_html__('Code List', 'profilepress-pro')];

        return $tabs;
    }

    public function screen_options()
    {
        if (isset($_GET['view']) && $_GET['view'] == 'invite-codes') {

            if (isset($_GET['post']) || isset($_GET['ppress_invite_code_action'])) {
                add_filter('screen_options_show_screen', '__return_false');
            }

            $this->myListTable = new WPListTable();
        }
    }

    public function invite_codes_admin_page()
    {
        add_action('wp_cspa_main_content_area', array($this, 'invite_codes_admin_page_callback'), 10, 2);
        add_action('wp_cspa_before_closing_header', [$this, 'add_new_button']);

        $is_exclude_sidebar = ! ppressGET_var('post') && ! ppressGET_var('ppress_invite_code_action');

        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name('ppview'); // adds ppview css class to #poststuff
        $instance->add_view_classes('pp-invite-codes');
        $instance->page_header(esc_html__('Invite Codes', 'profilepress-pro'));
        if ( ! $is_exclude_sidebar) {
            $instance->sidebar(AbstractSettingsPage::sidebar_args());
        }
        $instance->build($is_exclude_sidebar);
    }

    public function add_new_button()
    {
        if ( ! isset($_GET['post']) && ! isset($_GET['action'])) {
            $url = esc_url_raw(add_query_arg('post', 'new', PPRESS_INVITE_CODE_SETTINGS_PAGE));
            echo "<a class=\"add-new-h2\" href=\"$url\">" . esc_html__('Add Invite Code', 'profilepress-pro') . '</a>';
        }
    }

    public function invite_codes_admin_page_callback()
    {
        $this->myListTable->prepare_items(); // has to be here.

        if (isset($_GET['post']) || isset($_GET['ppress_invite_code_action'])) {
            require_once dirname(__FILE__) . '/views/add-edit-invite-code.php';

            return;
        }

        echo '<form method="post">';
        $this->myListTable->display();
        echo '</form>';

        do_action('ppress_invite_codes_wp_list_table_bottom');
    }

    public function generate_codes_admin_page()
    {
        add_action('wp_cspa_main_content_area', function () {
            require_once dirname(__FILE__) . '/views/generate-invite-code.php';
        }, 10, 2);

        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name('ppview'); // adds ppview css class to #poststuff
        $instance->page_header(esc_html__('Invite Codes', 'profilepress-pro'));
        $instance->sidebar(AbstractSettingsPage::sidebar_args());
        $instance->build();
    }

    public function code_list_admin_page()
    {
        add_action('wp_cspa_main_content_area', function () {
            require_once dirname(__FILE__) . '/views/raw-code-list.php';
        }, 10, 2);

        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name('ppview'); // adds ppview css class to #poststuff
        $instance->page_header(esc_html__('Invite Codes', 'profilepress-pro'));
        $instance->sidebar(AbstractSettingsPage::sidebar_args());
        $instance->build();
    }

    public function generate_random_invite_codes($code, $expiry_date = '', $usage_limit = '', $membership_plan = '')
    {
        if ((new InviteCodeEntity($code))->exists()) {
            $code .= rand(1, 99);

            return self::generate_random_invite_codes($code, $expiry_date, $usage_limit, $membership_plan);
        }

        $insert = PROFILEPRESS_sql::add_meta_data('invite_code', [
            'expiry_date'     => ppress_clean($expiry_date),
            'usage_limit'     => ppress_clean($usage_limit),
            'membership_plan' => ppress_clean($membership_plan)
        ], $code);

        if ($insert) return true;

        return false;
    }

    public function form_handler()
    {
        if (isset($_POST['save_invite_code'])) {

            if ( ! empty($_POST['invite_codes']) || ! empty($_POST['invite_code'])) {

                check_admin_referer('ppress_save_invite_code');

                if ( ! empty($_POST['invite_codes'])) {
                    $codes = array_map('trim', explode("\n", ppress_clean(ppressPOST_var('invite_codes', []))));

                    foreach ($codes as $code) {

                        if ( ! (new InviteCodeEntity($code))->exists()) {

                            PROFILEPRESS_sql::add_meta_data('invite_code', [
                                'expiry_date'     => ppress_clean(ppressPOST_var('expiry_date')),
                                'usage_limit'     => ppress_clean(ppressPOST_var('usage_limit')),
                                'membership_plan' => ppress_clean(ppressPOST_var('membership_plan'))
                            ], $code);
                        }
                    }
                }

                if ( ! empty($_POST['invite_code']) && ! empty($_GET['id'])) {

                    PROFILEPRESS_sql::update_meta_data(
                        intval($_GET['id']),
                        'invite_code',
                        [
                            'expiry_date'     => ppress_clean(ppressPOST_var('expiry_date', '')),
                            'usage_limit'     => ppress_clean(ppressPOST_var('usage_limit', '')),
                            'membership_plan' => ppress_clean(ppressPOST_var('membership_plan', ''))
                        ],
                        ppress_clean(ppressPOST_var('invite_code', ''))
                    );
                }

                wp_safe_redirect(add_query_arg('view', 'invite-codes', PPRESS_SETTINGS_SETTING_PAGE));
                exit;
            }
        }

        if (isset($_POST['generate_invite_code'])) {

            $prefix          = sanitize_text_field(ppressPOST_var('code_prefix', ''));
            $quantity        = absint(ppressPOST_var('code_quantity', 1));
            $usage_limit     = ! empty($_POST['usage_limit']) ? absint($_POST['usage_limit']) : '';
            $expiry_date     = sanitize_text_field(ppressPOST_var('expiry_date', ''));
            $membership_plan = ! empty($_POST['membership_plan']) ? absint($_POST['membership_plan']) : '';

            for ($i = 1; $i <= $quantity; $i++) {

                $invite_code = strtoupper(wp_generate_password(8, false));

                self::generate_random_invite_codes(
                    $prefix . $invite_code,
                    $expiry_date,
                    $usage_limit,
                    $membership_plan
                );
            }

            wp_safe_redirect(add_query_arg('view', 'invite-codes', PPRESS_SETTINGS_SETTING_PAGE));
            exit;
        }
    }

    public function add_available_shortcode_popup($shortcodes)
    {
        $shortcodes['pp-invite-code'] = [
            'description' => esc_html__('Allow only users with an invite code to register.', 'profilepress-pro'),
            'shortcode'   => 'pp-invite-code',
            'attributes'  => [
                'placeholder' => [
                    'label' => esc_html__('Placeholder', 'profilepress-pro'),
                    'field' => 'text'
                ],
                'class'       => [
                    'label' => esc_html__('CSS class', 'profilepress-pro'),
                    'field' => 'text'
                ]
            ]
        ];

        return $shortcodes;
    }

    /**
     * @param \WP_User_Query $query
     */
    public function pre_user_query($query)
    {
        if ('ppress_invite_code' === $query->query_vars['role'] && ! empty($_GET['code'])) {

            unset($query->query_vars['meta_query'][0]);

            $query->set('role', '');
            $query->set('meta_key', 'ppress_invite_code');
            $query->set('meta_value', sanitize_text_field($_GET['code']));
            $query->prepare_query();
        }
    }

    public static function get_role()
    {
        return isset($_REQUEST['role']) ? esc_html($_REQUEST['role']) : false;
    }
}