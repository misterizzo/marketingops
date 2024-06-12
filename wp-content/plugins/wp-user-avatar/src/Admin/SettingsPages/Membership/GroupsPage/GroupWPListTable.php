<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\GroupsPage;

use ProfilePress\Core\Admin\SettingsPages\Membership\PlansPage\PlanWPListTable;
use ProfilePress\Core\Membership\Models\Group\GroupEntity;
use ProfilePress\Core\Membership\Repositories\GroupRepository;

class GroupWPListTable extends \WP_List_Table
{
    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'ppress-group-code',
            'plural'   => 'ppress-group-codes',
            'ajax'     => false
        ));
    }

    public function no_items()
    {
        _e('No group found.', 'wp-user-avatar');
    }

    public function get_columns()
    {
        return [
            'cb'           => '<input type="checkbox" />',
            'group_name'   => esc_html__('Group Name', 'wp-user-avatar'),
            'group_plans'  => esc_html__('Plans in Group', 'wp-user-avatar'),
            'checkout_url' => esc_html__('Checkout URL', 'wp-user-avatar')
        ];
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param GroupEntity $item
     *
     * @return string
     */
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="group_id[]" value="%s" />', $item->id);
    }

    public function column_group_name(GroupEntity $item)
    {
        $group_id = absint($item->id);

        $edit_link   = esc_url(add_query_arg(['ppress_group_action' => 'edit', 'id' => $group_id], PPRESS_MEMBERSHIP_GROUPS_SETTINGS_PAGE));
        $delete_link = self::delete_group_url($group_id);

        $actions = [
            'edit' => sprintf('<a href="%s">%s</a>', $edit_link, esc_html__('Edit', 'wp-user-avatar')),
        ];

        $actions['delete'] = sprintf('<a class="pp-confirm-delete" href="%s">%s</a>', $delete_link, esc_html__('Delete', 'wp-user-avatar'));

        $a = '<a href="' . $edit_link . '">' . esc_html($item->name) . '</a>';

        return '<strong>' . $a . '</strong>' . $this->row_actions($actions);
    }

    public function column_group_plans(GroupEntity $item)
    {
        $plans = $item->plan_ids;

        if (empty($plans)) return '&mdash;&mdash;&mdash;';

        $plans = array_map(function ($plan_id) {

            $plan = ppress_get_plan($plan_id);

            return sprintf(
                '<a target="_blank" href="%s">%s</a>',
                $plan->get_edit_plan_url(),
                $plan->get_name()
            );

        }, $plans);

        return sprintf('%s', implode(', ', $plans));
    }

    public function column_checkout_url(GroupEntity $item)
    {
        $url = $item->get_checkout_url();

        if ( ! $url) return esc_html__('Checkout page not found', 'wp-user-avatar');

        return '<input type="text" onfocus="this.select();" readonly="readonly" value="' . esc_url($url) . '" style="width:100%;max-width:80%" />';
    }

    public static function delete_group_url($group_id)
    {
        $nonce_delete = wp_create_nonce('pp_group_delete_rule');

        return add_query_arg(['action' => 'delete', 'id' => $group_id, '_wpnonce' => $nonce_delete], PPRESS_MEMBERSHIP_GROUPS_SETTINGS_PAGE);
    }

    public function get_groups($per_page, $current_page = 1)
    {
        return GroupRepository::init()->retrieveAll($per_page, $current_page);
    }

    public function record_count()
    {
        return GroupRepository::init()->retrieveAll(0, 1, 'DESC', true);
    }

    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();

        $this->process_bulk_action();

        $per_page = $this->get_items_per_page('groups_per_page', 10);
        $current_page = $this->get_pagenum();
        $total_items  = $this->record_count();

        $this->set_pagination_args([
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page //WE have to determine how many items to show on a page
        ]);

        $this->items = $this->get_groups($per_page, $current_page);
    }

    public function current_action()
    {
        if (isset($_REQUEST['filter_action']) && ! empty($_REQUEST['filter_action'])) {
            return false;
        }

        if (isset($_REQUEST['action']) && -1 != $_REQUEST['action']) {
            return $_REQUEST['action'];
        }

        if (isset($_REQUEST['ppress_group_action']) && -1 != $_REQUEST['ppress_group_action']) {
            return $_REQUEST['ppress_group_action'];
        }

        return false;
    }

    public function get_bulk_actions()
    {
        $actions = [
            'bulk-delete' => esc_html__('Delete', 'wp-user-avatar')
        ];

        return $actions;
    }

    public function process_bulk_action()
    {
        $group_id = absint(ppress_var($_GET, 'id', 0));

        // Bail if user is not an admin or without admin privileges.
        if ( ! current_user_can('manage_options')) return;

        if ('delete' === $this->current_action()) {

            check_admin_referer('pp_group_delete_rule');

            if ( ! current_user_can('manage_options')) return;

            GroupRepository::init()->delete($group_id);
        }

        if ('bulk-delete' === $this->current_action()) {

            check_admin_referer('bulk-' . $this->_args['plural']);

            if ( ! current_user_can('manage_options')) return;

            $group_ids = array_map('absint', $_POST['group_id']);

            foreach ($group_ids as $group_id) {
                GroupRepository::init()->delete($group_id);
            }
        }

        if ($this->current_action() !== false) {
            wp_safe_redirect(PPRESS_MEMBERSHIP_GROUPS_SETTINGS_PAGE);
            exit;
        }
    }

    /**
     * @return array List of CSS classes for the table tag.
     */
    public function get_table_classes()
    {
        return array('widefat', 'fixed', 'striped', 'group', 'ppview');
    }
}
