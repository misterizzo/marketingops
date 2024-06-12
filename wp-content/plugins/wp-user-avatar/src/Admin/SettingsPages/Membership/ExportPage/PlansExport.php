<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\ExportPage;

use ProfilePress\Core\Base;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;

class PlansExport extends AbstractExport
{
    protected function headers()
    {
        return [
            __('Plan ID', 'wp-user-avatar'),
            __('Name', 'wp-user-avatar'),
            __('Description', 'wp-user-avatar'),
            __('User Role', 'wp-user-avatar'),
            __('Order Note', 'wp-user-avatar'),
            __('Price', 'wp-user-avatar'),
            __('Billing Frequency', 'wp-user-avatar'),
            __('Subscription Length', 'wp-user-avatar'),
            __('Total Payments', 'wp-user-avatar'),
            __('Signup Fee', 'wp-user-avatar'),
            __('Free Trial', 'wp-user-avatar'),
            __('Active', 'wp-user-avatar'),
            __('Sales', 'wp-user-avatar'),
            __('Earnings', 'wp-user-avatar')
        ];
    }

    public function get_data($page = 1, $limit = 9999)
    {
        global $wpdb;

        $plan_id = $this->form['plan_id'] ?? '';

        $orders_table = Base::orders_db_table();
        $plans_table  = Base::subscription_plans_db_table();

        $replacements = [OrderStatus::COMPLETED, OrderStatus::COMPLETED, 1];
        $sql          = "
SELECT
	pp.id,
	pp.name,
	pp.description,
	pp.user_role,
	pp.order_note,
	pp.price,
	pp.billing_frequency,
	pp.subscription_length,
	pp.total_payments,
	pp.signup_fee,
	pp.free_trial,
	pp.status AS is_active,
	(
		SELECT
			count(id)
		FROM
			$orders_table AS po
		WHERE
			po.plan_id = pp.id
			AND po.status = %s) AS sales, (
			SELECT
				SUM(ppo.total)
			FROM
				$orders_table AS ppo
			WHERE
				ppo.plan_id = pp.id
				AND ppo.status = %s) AS earnings
		FROM
			$plans_table AS pp
        WHERE 1 = %d
";

        if ( ! empty($plan_id)) {
            $replacements[] = intval($plan_id);
            $sql            .= " AND pp.id = %d";
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