<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\GroupsPage;

use ProfilePress\Core\Admin\SettingsPages\AbstractSettingsPage;
use ProfilePress\Core\Membership\Models\Group\GroupFactory;
use ProfilePress\Custom_Settings_Page_Api;

class SettingsPage extends AbstractSettingsPage
{
    public $error_bucket;

    /** @var GroupWPListTable */
    private $groupListTable;

    function __construct()
    {
        add_action('ppress_admin_settings_page_groups', [$this, 'settings_page_function']);
        add_filter('ppress_membership_plans_settings_page_title', [$this, 'admin_page_title']);
        add_action('ppress_membership_plans_settings_page_register', function ($hook) {
            add_action("load-$hook", array($this, 'add_options'));
        });

        if (ppressGET_var('page') == PPRESS_MEMBERSHIP_PLANS_SETTINGS_SLUG && ppressGET_var('view') == 'groups') {

            add_filter('set-screen-option', [__CLASS__, 'set_screen'], 10, 3);
            add_filter('set_screen_option_rules_per_page', [__CLASS__, 'set_screen'], 10, 3);

            add_action('admin_init', function () {
                $this->save_group();
            });
        }
    }

    /**
     * @return string|void
     */
    public function save_group()
    {
        if ( ! isset($_POST['ppress_save_group'])) return;

        check_admin_referer('wp-csa-nonce', 'wp_csa_nonce');

        if ( ! current_user_can('manage_options')) return;

        $required_fields = [
            'name'     => esc_html__('Group Name', 'wp-user-avatar'),
            'plan_ids' => esc_html__('Membership Plans', 'wp-user-avatar')
        ];

        foreach ($required_fields as $field_id => $field_name) {
            if (empty($_POST[$field_id])) {
                return $this->error_bucket = sprintf(esc_html__('%s cannot be empty.', 'wp-user-avatar'), $field_name);
            }
        }

        $group                      = GroupFactory::fromId(absint(ppressGET_var('id')));
        $group->name                = sanitize_textarea_field($_POST['name']);
        $group->plan_ids            = ppress_clean($_POST['plan_ids']);
        $group->plans_display_field = sanitize_text_field($_POST['plans_display_field']);

        $group_id = $group->save();

        // doing this because if no change is made and form saved, save() returns false.
        if ( ! $group_id && $group->exists()) {
            $group_id = absint(ppressGET_var('id'));
        }

        wp_safe_redirect(add_query_arg(['ppress_group_action' => 'edit', 'id' => $group_id, 'saved' => 'true'], PPRESS_MEMBERSHIP_GROUPS_SETTINGS_PAGE));
        exit;
    }

    public function admin_page_title($title = '')
    {
        if (ppressGET_var('view') == 'groups') {

            $title = esc_html__('Groups', 'wp-user-avatar');

            if (ppressGET_var('ppress_group_action') == 'new') {
                $title = esc_html__('Add a Group', 'wp-user-avatar');
            }

            if (ppressGET_var('ppress_group_action') == 'edit') {
                $title = esc_html__('Edit Group', 'wp-user-avatar');
            }
        }

        return $title;
    }

    public static function set_screen($status, $option, $value)
    {
        return $value;
    }

    public function add_options()
    {
        if (ppressGET_var('view') == 'groups') {

            $args = [
                'label'   => esc_html__('Groups', 'wp-user-avatar'),
                'default' => 10,
                'option'  => 'groups_per_page'
            ];

            add_screen_option('per_page', $args);

            $this->groupListTable = new GroupWPListTable();
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
        add_action('wp_cspa_before_closing_header', [$this, 'add_new_button']);

        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name('ppview'); // adds ppview css class to #poststuff
        $instance->page_header($this->admin_page_title());
        $instance->build(true);
    }

    public function add_new_button()
    {
        if ( ! isset($_GET['ppress_subp_action'])) {
            $url = esc_url_raw(add_query_arg('ppress_group_action', 'new', self::$parent_menu_url_map['groups']));
            echo "<a class=\"add-new-h2\" href=\"$url\">" . esc_html__('Add New Group', 'wp-user-avatar') . '</a>';
        }
    }

    public function admin_settings_page_callback()
    {
        if (in_array(ppressGET_var('ppress_group_action'), ['new', 'edit'])) {
            $this->admin_notices();
            require_once dirname(dirname(__FILE__)) . '/views/add-edit-plan-group.php';

            return;
        }

        $this->groupListTable->prepare_items(); // has to be here.

        self::group_info_note();

        echo '<form method="post">';
        $this->groupListTable->display();
        echo '</form>';

        do_action('ppress_group_wp_list_table_bottom');
    }

    public static function group_info_note()
    {
        echo '<div class="pp-custom-field-notice"><p>';
        esc_html_e('Groups let users select a membership plan from a list of plans to purchase during checkout. It also allows members to switch (upgrade & downgrade) between plans in the same group.', 'wp-user-avatar');
        echo '</p></div>';
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