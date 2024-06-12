<?php

namespace ProfilePress\Libsodium\InvitationCodes;

use ProfilePress\Core\Base;
use ProfilePress\Core\Classes\PROFILEPRESS_sql;

class WPListTable extends \WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => esc_html__('invite-code', 'profilepress-pro'),
            'plural'   => esc_html__('invite-codes', 'profilepress-pro'),
            'ajax'     => false
        ]);
    }

    public function no_items()
    {
        _e('No invite code is available.', 'profilepress-pro');
    }

    public function get_columns()
    {
        return [
            'cb'              => '<input type="checkbox" />',
            'invite_code'     => esc_html__('Invite Code', 'profilepress-pro'),
            'redemptions'     => esc_html__('Redemptions', 'profilepress-pro'),
            'membership_plan' => esc_html__('Membership Plan', 'profilepress-pro'),
            'expiry_date'     => esc_html__('Expiry Date', 'profilepress-pro'),
            'status'          => esc_html__('Status', 'profilepress-pro')
        ];
    }

    private function get_usage_count($invite_code)
    {
        return (new InviteCodeEntity($invite_code))->get_usage_count();
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="invite_code[]" value="%s" />', $item['id']);
    }

    public function column_invite_code($item)
    {
        $id = absint($item['id']);

        $edit_link   = add_query_arg(['ppress_invite_code_action' => 'edit', 'id' => $id], PPRESS_INVITE_CODE_SETTINGS_PAGE);
        $delete_link = add_query_arg(['ppress_invite_code_action' => 'delete', 'id' => $id, '_wpnonce' => wp_create_nonce('ppress_delete_invite_code')], PPRESS_INVITE_CODE_SETTINGS_PAGE);
        $actions     = [
            'edit'   => sprintf('<a href="%s">%s</a>', esc_url($edit_link), esc_html__('Edit', 'profilepress-pro')),
            'delete' => sprintf('<a class="pp-confirm-delete" href="%s">%s</a>', esc_url($delete_link), esc_html__('Delete', 'profilepress-pro')),
        ];

        $a = '<a href="' . esc_url($edit_link) . '">' . esc_html($item['flag']) . '</a>';

        return '<strong>' . $a . '</strong>' . $this->row_actions($actions);
    }

    public function column_redemptions($item)
    {
        $meta = maybe_unserialize($item['meta_value']);

        $usage_limit = absint(ppress_var($meta, 'usage_limit', 0));

        if (empty($usage_limit) || $usage_limit === 0) {
            $usage_limit = esc_html__('Unlimited', 'profilepress-pro');
        }

        return sprintf(
            '<a target="_blank" href="%s">%s</a>',
            add_query_arg(['role' => 'ppress_invite_code', 'code' => $item['flag']], admin_url('users.php')),
            $this->get_usage_count($item['flag']) . ' / ' . $usage_limit
        );
    }

    public function column_membership_plan($item)
    {
        $meta = maybe_unserialize($item['meta_value']);

        $plan = ppress_var($meta, 'membership_plan');

        if (empty($plan)) return '&mdash;&mdash;&mdash;';

        return ppress_get_plan($plan)->get_name();
    }

    public function column_expiry_date($item)
    {
        $meta = maybe_unserialize($item['meta_value']);

        $date = ppress_var($meta, 'expiry_date');

        if (empty($date)) return '&infin;';

        return esc_html($date);
    }

    public function column_status($item)
    {
        $status = '<span class="ppress-admin-status-badge ppress-admin-status-badge--completed">' . esc_html__('Active', 'profilepress-pro') . '</span>';

        $meta = maybe_unserialize($item['meta_value']);

        if (Init::is_date_expired(ppress_var($meta, 'expiry_date'))) {
            $status = '<span class="ppress-admin-status-badge ppress-admin-status-badge--failed">' . esc_html__('Expired', 'profilepress-pro') . '</span>';
        }

        return $status;
    }

    public function get_invite_codes($per_page, $current_page = 1)
    {
        global $wpdb;

        $replacement = ['invite_code', $per_page];

        $table = Base::meta_data_db_table();

        $sql = "SELECT * FROM $table WHERE meta_key = %s";
        $sql .= " ORDER BY id DESC";
        $sql .= " LIMIT %d";
        if ($current_page > 1) {
            $sql           .= "  OFFSET %d";
            $replacement[] = ($current_page - 1) * $per_page;
        }

        $result = $wpdb->get_results($wpdb->prepare($sql, $replacement), 'ARRAY_A');

        if (empty($result)) return [];

        return $result;
    }

    public function record_count()
    {
        global $wpdb;

        $table = Base::meta_data_db_table();

        return $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE meta_key = 'invite_code'");
    }

    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();

        $this->process_bulk_action();

        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $total_items  = $this->record_count();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page
        ]);

        $this->items = $this->get_invite_codes($per_page, $current_page);
    }

    public function get_bulk_actions()
    {
        return ['bulk-delete' => esc_html__('Delete', 'profilepress-pro')];
    }

    public function current_action()
    {
        if (isset($_REQUEST['filter_action']) && ! empty($_REQUEST['filter_action'])) {
            return false;
        }

        if (isset($_REQUEST['action']) && -1 != $_REQUEST['action']) {
            return $_REQUEST['action'];
        }

        if (isset($_REQUEST['ppress_invite_code_action']) && -1 != $_REQUEST['ppress_invite_code_action']) {
            return $_REQUEST['ppress_invite_code_action'];
        }

        return false;
    }

    public function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {

            if ( ! current_user_can('manage_options')) return;

            check_admin_referer('ppress_delete_invite_code');

            PROFILEPRESS_sql::delete_meta_data(absint($_GET['id']));
            wp_safe_redirect(PPRESS_INVITE_CODE_SETTINGS_PAGE);
            exit;
        }

        if ('bulk-delete' === $this->current_action()) {

            check_admin_referer('bulk-' . $this->_args['plural']);

            if ( ! current_user_can('manage_options')) return;

            $invite_codes = array_map('absint', $_POST['invite_code']);

            foreach ($invite_codes as $invite_code) {
                PROFILEPRESS_sql::delete_meta_data($invite_code);
            }
        }
    }

    /**
     * Add ppview to a list of css classes included in the table
     *
     * THis method overrides that of the parent class
     *
     * @return array List of CSS classes for the table tag.
     */
    public function get_table_classes()
    {
        return array('widefat', 'fixed', 'striped', 'invite_codes', 'ppview');
    }
}
