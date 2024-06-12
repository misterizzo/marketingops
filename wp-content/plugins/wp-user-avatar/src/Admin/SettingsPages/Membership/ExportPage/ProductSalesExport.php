<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\ExportPage;

use ProfilePress\Core\Base;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePressVendor\Carbon\CarbonImmutable;

class ProductSalesExport extends AbstractExport
{
    protected function headers()
    {
        return [
            __('Plan', 'wp-user-avatar'),
            __('User ID', 'wp-user-avatar'),
            __('Customer ID', 'wp-user-avatar'),
            __('Email', 'wp-user-avatar'),
            __('Name', 'wp-user-avatar'),
            __('Plan', 'wp-user-avatar'),
            __('Amount', 'wp-user-avatar'),
            __('Currency', 'wp-user-avatar'),
            __('Order ID', 'wp-user-avatar'),
            __('Date', 'wp-user-avatar')
        ];
    }

    public function get_data($page = 1, $limit = 9999)
    {
        global $wpdb;

        $start_date = $this->form['product-sales-export-start'] ?? '';
        $end_date   = $this->form['product-sales-export-end'] ?? '';

        $plan_id = (int)$this->form['plan_id'];

        $orders_table    = Base::orders_db_table();
        $customers_table = Base::customers_db_table();
        $plans_table     = Base::subscription_plans_db_table();
        $wp_user_table   = $wpdb->users;

        $wp_timezone = wp_timezone();

        $replacements = [OrderStatus::COMPLETED];
        $sql          = "
SELECT
	po.plan_id,
	pc.user_id,
	po.customer_id,
	wpu.user_email,
	wpu.display_name,
	pp.name AS plan_name,
	po.total AS order_total,
	po.currency,
	po.id AS order_id,
	po.date_created
FROM
	$orders_table AS po
	INNER JOIN $customers_table AS pc ON po.customer_id = pc.id
	INNER JOIN $wp_user_table AS wpu ON pc.user_id = wpu.ID
	INNER JOIN $plans_table AS pp ON po.plan_id = pp.id
WHERE
	po.status = %s";

        if ( ! empty($plan_id)) {
            $replacements[] = $plan_id;
            $sql            .= " AND po.plan_id = %d";
        }

        if ( ! empty($start_date)) {
            $replacements[] = CarbonImmutable::parse($start_date, $wp_timezone)->startOfDay()->utc()->toDateTimeString();
            $sql            .= " AND po.date_created >= %s";
        }

        if ( ! empty($end_date)) {
            $replacements[] = CarbonImmutable::parse($end_date, $wp_timezone)->endOfDay()->utc()->toDateTimeString();
            $sql            .= " AND po.date_created <= %s";
        }

        $page = max(1, intval($page));

        $offset = ($page - 1) * intval($limit);

        if ($limit > 0) {
            $sql            .= " LIMIT %d";
            $replacements[] = $limit;
        }

        if ($offset > 0) {
            $sql            .= "  OFFSET %d";
            $replacements[] = $offset;
        }

        return $wpdb->get_results($wpdb->prepare($sql, $replacements), ARRAY_A);
    }
}