<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\ExportPage;

use ProfilePress\Core\Base;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;

class CustomersExport extends AbstractExport
{
    protected function headers()
    {
        return [
            __('Customer ID', 'wp-user-avatar'),
            __('User ID', 'wp-user-avatar'),
            __('Email', 'wp-user-avatar'),
            __('Name', 'wp-user-avatar'),
            __('Private Note', 'wp-user-avatar'),
            __('Total Spend', 'wp-user-avatar'),
            __('Purchase Count', 'wp-user-avatar'),
            __('Date Created', 'wp-user-avatar'),
            __('Order IDs', 'wp-user-avatar')
        ];
    }

    public function get_data($page = 1, $limit = 9999)
    {
        global $wpdb;

        $plan_id = $this->form['plan_id'] ?? '';

        $orders_table    = Base::orders_db_table();
        $customers_table = Base::customers_db_table();
        $wp_user_table   = $wpdb->users;

        $replacements = [OrderStatus::COMPLETED];
        $sql          = "
SELECT
	pc.id,
	pc.user_id,
	wpu.user_email,
	wpu.display_name,
	pc.private_note,
	pc.total_spend,
	pc.purchase_count,
	pc.date_created,
	(
			SELECT
				GROUP_CONCAT(po.id)
			FROM
				$orders_table AS po
			WHERE
				po.customer_id = pc.id
				AND po.status = %s) AS order_ids
		FROM
			$customers_table AS pc
			INNER JOIN $wp_user_table AS wpu ON pc.user_id = wpu.ID
			INNER JOIN $orders_table AS ppo ON ppo.customer_id = pc.id
";

        if ( ! empty($plan_id)) {
            $replacements[] = intval($plan_id);
            $sql            .= " AND ppo.plan_id = %d";
        }

        $sql .= " GROUP BY pc.id";

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