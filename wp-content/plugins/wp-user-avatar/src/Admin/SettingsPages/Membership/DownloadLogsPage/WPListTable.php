<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\DownloadLogsPage;

use ProfilePress\Core\Admin\SettingsPages\Membership\CustomersPage\CustomerWPListTable;
use ProfilePress\Core\Admin\SettingsPages\Membership\OrdersPage\OrderWPListTable;
use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Core\Membership\DigitalProducts\DownloadService;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;

class WPListTable extends \WP_List_Table
{
    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'ppress-download-log',
            'plural'   => 'ppress-download-logs',
            'ajax'     => false
        ));
    }

    public function no_items()
    {
        _e('No download log found.', 'wp-user-avatar');
    }

    public function get_columns()
    {
        $columns = [
            'cb'              => '<input type="checkbox" />',
            'membership_plan' => esc_html__('Membership Plan', 'wp-user-avatar'),
            'customer'        => esc_html__('Customer', 'wp-user-avatar'),
            'order_number'    => esc_html__('Order Number', 'wp-user-avatar'),
            'file'            => esc_html__('File', 'wp-user-avatar'),
            'ip_address'      => esc_html__('IP Address', 'wp-user-avatar'),
            'date'            => esc_html__('Date', 'wp-user-avatar'),
        ];

        return $columns;
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param $item
     *
     * @return string
     */
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="ppress_download_log_id[]" value="%s" />', $item['id']);
    }

    public function column_membership_plan($item)
    {
        $plan_id   = absint($item['plan_id']);
        $plan_name = ppress_get_plan($plan_id)->get_name();

        $url = add_query_arg(['ppress_subp_action' => 'edit', 'id' => $plan_id], PPRESS_MEMBERSHIP_SUBSCRIPTION_PLANS_SETTINGS_PAGE);

        return '<a href="' . $url . '">' . sanitize_text_field($plan_name) . '</a>';
    }

    public function column_customer($item)
    {
        $order_id = absint($item['order_id']);

        $customer = OrderFactory::fromId($order_id)->get_customer();

        $name = $customer->get_name();

        $url = CustomerWPListTable::view_customer_url($customer->get_id());

        return '<a href="' . esc_url($url) . '">' . sanitize_text_field($name) . '</a>';
    }

    public function column_order_number($item)
    {
        $order_id = absint($item['order_id']);

        $url = OrderWPListTable::view_edit_order_url($order_id);

        return '<a href="' . esc_url($url) . '">' . $order_id . '</a>';
    }

    public function column_default($item, $column_name)
    {
        $output = '';

        switch ($column_name) {
            case 'file':
                $output = DownloadService::init()->get_download_file_name(
                    $item['plan_id'],
                    $item['file_url']
                );
                break;
            case 'ip_address':
                $output = '<a href="' . esc_url('https://ipinfo.io/' . esc_attr($item['ip'])) . '" target="_blank" rel="noopener noreferrer">' . esc_html($item['ip']) . '</a>';
                break;
            case 'date':
                $output = ppress_format_date_time($item['date'], 'Y-m-d H:i:s');
                break;
        }

        return $output;
    }

    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();

        $this->process_bulk_action();

        $by_order_id = ppress_var($_GET, 'by_oid', 0, true);

        $per_page = $this->get_items_per_page('download_logs_per_page', 10);
        $current_page = $this->get_pagenum();
        $total_items  = DownloadService::init()->get_download_log_count($by_order_id);

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page
        ]);

        $this->items = DownloadService::init()->get_download_log($per_page, $current_page, $by_order_id);
    }

    public function get_bulk_actions()
    {
        return ['bulk-delete' => esc_html__('Delete', 'wp-user-avatar')];
    }

    public function process_bulk_action()
    {
        if ('bulk-delete' === $this->current_action()) {

            if ( ! current_user_can('manage_options')) return;

            check_admin_referer('bulk-' . $this->_args['plural']);

            if ( ! current_user_can('manage_options')) return;

            $log_ids = array_map('absint', $_POST['ppress_download_log_id']);

            foreach ($log_ids as $log_id) {
                PROFILEPRESS_sql::delete_meta_data($log_id);
            }

            wp_safe_redirect(PPRESS_MEMBERSHIP_DOWNLOAD_LOGS_SETTINGS_PAGE);
            exit;
        }
    }
}
