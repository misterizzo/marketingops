<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\ExportPage;

use ProfilePress\Core\Base;
use ProfilePressVendor\Carbon\CarbonImmutable;

class OrdersExport extends AbstractExport
{
    protected function headers()
    {
        return [
            __('Order ID', 'wp-user-avatar'),
            __('Order Key', 'wp-user-avatar'),
            __('Plan ID', 'wp-user-avatar'),
            __('Plan', 'wp-user-avatar'),
            __('Email', 'wp-user-avatar'),
            __('Customer ID', 'wp-user-avatar'),
            __('Customer Name', 'wp-user-avatar'),
            __('Subscription ID', 'wp-user-avatar'),
            __('Billing Address', 'wp-user-avatar'),
            __('Billing City', 'wp-user-avatar'),
            __('Billing State', 'wp-user-avatar'),
            __('Billing Country', 'wp-user-avatar'),
            __('Billing Postcode', 'wp-user-avatar'),
            __('Billing Phone', 'wp-user-avatar'),
            __('Order Status', 'wp-user-avatar'),
            __('Order Type', 'wp-user-avatar'),
            __('Order Mode', 'wp-user-avatar'),
            __('Currency', 'wp-user-avatar'),
            __('Coupon Code', 'wp-user-avatar'),
            __('Subtotal', 'wp-user-avatar'),
            __('Tax', 'wp-user-avatar'),
            __('Tax Rate', 'wp-user-avatar'),
            __('Discount', 'wp-user-avatar'),
            __('Order Total', 'wp-user-avatar'),
            __('Payment Method', 'wp-user-avatar'),
            __('Transaction ID', 'wp-user-avatar'),
            __('IP Address', 'wp-user-avatar'),
            __('Date Created', 'wp-user-avatar'),
            __('Date Completed', 'wp-user-avatar')
        ];
    }

    public function get_data($page = 1, $limit = 9999)
    {
        global $wpdb;

        $start_date = $this->form['orders-export-start'] ?? '';
        $end_date   = $this->form['orders-export-end'] ?? '';

        $order_status = $this->form['order_status'] ?? '';
        $plan_id      = (int)$this->form['plan_id'] ?? '';

        $orders_table    = Base::orders_db_table();
        $customers_table = Base::customers_db_table();
        $plans_table     = Base::subscription_plans_db_table();
        $wp_user_table   = $wpdb->users;

        $wp_timezone = wp_timezone();

        $replacements = [1];
        $sql          = "
SELECT
	po.id as order_id,
	po.order_key,
	po.plan_id,
	pp.name AS plan_name,
	wpu.user_email,
	po.customer_id,
	wpu.display_name,
	po.subscription_id,
	po.billing_address,
	po.billing_city,
	po.billing_state,
	po.billing_country,
	po.billing_postcode,
	po.billing_phone,
	po.status,
	po.order_type,
	po.mode,
	po.currency,
	po.coupon_code,
	po.subtotal,
	po.tax,
	po.tax_rate,
	po.discount,
	po.total,
	po.payment_method,
	po.transaction_id,
	po.ip_address,
	po.date_created,
	po.date_completed
FROM
	$orders_table AS po
	INNER JOIN $customers_table AS pc ON po.customer_id = pc.id
	INNER JOIN $wp_user_table AS wpu ON pc.user_id = wpu.ID
	INNER JOIN $plans_table AS pp ON po.plan_id = pp.id
WHERE
	1 = %d";

        if ( ! empty($plan_id)) {
            $replacements[] = $plan_id;
            $sql            .= " AND po.plan_id = %s";
        }

        if ( ! empty($order_status)) {
            $replacements[] = sanitize_text_field($order_status);
            $sql            .= " AND po.status = %s";
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