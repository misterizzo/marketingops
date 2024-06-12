<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\ExportPage;

use ProfilePress\Core\Base;
use ProfilePressVendor\Carbon\CarbonImmutable;

class SubscriptionsExport extends AbstractExport
{
    protected function headers()
    {
        return [
            __('Subscription ID', 'wp-user-avatar'),
            __('Status', 'wp-user-avatar'),
            __('Customer ID', 'wp-user-avatar'),
            __('Email', 'wp-user-avatar'),
            __('Plan ID', 'wp-user-avatar'),
            __('Billing Frequency', 'wp-user-avatar'),
            __('Initial Amount', 'wp-user-avatar'),
            __('Initial Tax Rate', 'wp-user-avatar'),
            __('Initial Tax', 'wp-user-avatar'),
            __('Recurring Amount', 'wp-user-avatar'),
            __('Recurring Tax Rate', 'wp-user-avatar'),
            __('Recurring Tax', 'wp-user-avatar'),
            __('Total Payments', 'wp-user-avatar'),
            __('Trial Period', 'wp-user-avatar'),
            __('Start Date', 'wp-user-avatar'),
            __('Expiration Date', 'wp-user-avatar'),
            __('Gateway Profile ID', 'wp-user-avatar'),
            __('Parent Order ID', 'wp-user-avatar')
        ];
    }

    public function get_data($page = 1, $limit = 9999)
    {
        global $wpdb;

        $start_date = $this->form['subscriptions-export-start'] ?? '';
        $end_date   = $this->form['subscriptions-export-end'] ?? '';

        $subscription_status = $this->form['subscription_status'] ?? '';
        $plan_id             = (int)$this->form['plan_id'];

        $sub_table      = Base::subscriptions_db_table();
        $customer_table = Base::customers_db_table();
        $wp_user_table  = $wpdb->users;

        $wp_timezone = wp_timezone();

        $replacements = [1];
        $sql          = "
SELECT
	ps.id,
	ps.status,
	ps.customer_id,
	wpu.user_email,
	ps.plan_id,
	ps.billing_frequency,
	ps.initial_amount,
	ps.initial_tax_rate,
	ps.initial_tax,
	ps.recurring_amount,
	ps.recurring_tax_rate,
	ps.recurring_tax,
	ps.total_payments,
	ps.trial_period,
	ps.created_date,
	ps.expiration_date,
	ps.profile_id,
	ps.parent_order_id
FROM
	$sub_table AS ps
	INNER JOIN $customer_table AS pc ON ps.customer_id = pc.id
	INNER JOIN $wp_user_table AS wpu ON wpu.id = pc.user_id 
WHERE 1 = %d";

        if ( ! empty($plan_id)) {
            $replacements[] = $plan_id;
            $sql            .= " AND ps.plan_id = %d";
        }

        if ( ! empty($subscription_status)) {
            $replacements[] = sanitize_text_field($subscription_status);
            $sql            .= " AND ps.status = %s";
        }

        if ( ! empty($start_date)) {
            $replacements[] = CarbonImmutable::parse($start_date, $wp_timezone)->startOfDay()->utc()->toDateTimeString();
            $sql            .= " AND ps.created_date >= %s";
        }

        if ( ! empty($end_date)) {
            $replacements[] = CarbonImmutable::parse($end_date, $wp_timezone)->endOfDay()->utc()->toDateTimeString();
            $sql            .= " AND ps.created_date <= %s";
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