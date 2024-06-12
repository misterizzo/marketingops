<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\ExportPage;

use ProfilePress\Core\Base;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePressVendor\Carbon\CarbonImmutable;

class SalesEarningsExport extends AbstractExport
{
    protected function headers()
    {
        return [
            __('Date', 'wp-user-avatar'),
            __('Sales', 'wp-user-avatar'),
            __('Earnings', 'wp-user-avatar')
        ];
    }

    public function get_data($page = 1, $limit = 9999)
    {
        global $wpdb;

        $start_date = $this->form['order-export-start'] ?? '';
        $end_date   = $this->form['order-export-end'] ?? '';

        $payment_method = $this->form['payment_method'] ?? '';

        $plan_id     = (int)$this->form['plan_id'];
        $customer_id = (int)$this->form['customer_id'];

        $table = Base::orders_db_table();

        $wp_timezone = wp_timezone();

        $replacements = [OrderStatus::COMPLETED];
        $sql          = "SELECT DATE(date_created) as date, COUNT(id) as sales, SUM(total) as total from $table WHERE status = %s";

        if ( ! empty($plan_id)) {
            $replacements[] = $plan_id;
            $sql            .= " AND plan_id = %d";
        }

        if ( ! empty($customer_id)) {
            $replacements[] = $customer_id;
            $sql            .= " AND customer_id = %d";
        }

        if ( ! empty($payment_method)) {
            $replacements[] = sanitize_text_field($payment_method);
            $sql            .= " AND payment_method = %s";
        }

        if ( ! empty($start_date)) {
            $replacements[] = CarbonImmutable::parse($start_date, $wp_timezone)->startOfDay()->utc()->toDateTimeString();
            $sql            .= " AND date_created >= %s";
        }

        if ( ! empty($end_date)) {
            $replacements[] = CarbonImmutable::parse($end_date, $wp_timezone)->endOfDay()->utc()->toDateTimeString();
            $sql            .= " AND date_created <= %s";
        }

        $sql .= " GROUP BY date";

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