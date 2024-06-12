<?php
/**
 * Subscription reserved post columns
 *
 * @link
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

	$columns = array(
		'subscription_id'         => 'subscription_id',
		'subscription_status'     => 'subscription_status',
		'customer_id'             => 'customer_id',
		'customer_username'       => 'customer_username',
		'customer_email'          => 'customer_email',
		'date_created'            => 'date_created',
		'trial_end_date'          => 'trial_end_date',
		'next_payment_date'       => 'next_payment_date',
		'last_order_date_created' => 'last_order_date_created',
		'end_date'                => 'end_date',
		'post_parent'             => 'post_parent',
		'billing_period'          => 'billing_period',
		'billing_interval'        => 'billing_interval',
		'order_shipping'          => 'order_shipping',
		'order_shipping_tax'      => 'order_shipping_tax',
		'fee_total'               => 'fee_total',
		'fee_tax_total'           => 'fee_tax_total',
		'order_tax'               => 'order_tax',
		'cart_discount'           => 'cart_discount',
		'cart_discount_tax'       => 'cart_discount_tax',
		'order_total'             => 'order_total',
		'order_currency'          => 'order_currency',
		'payment_method'          => 'payment_method',
		'payment_method_title'    => 'payment_method_title',
		'shipping_method'         => 'shipping_method',
		'billing_first_name'      => 'billing_first_name',
		'billing_last_name'       => 'billing_last_name',
		'billing_email'           => 'billing_email',
		'billing_phone'           => 'billing_phone',
		'billing_address_1'       => 'billing_address_1',
		'billing_address_2'       => 'billing_address_2',
		'billing_postcode'        => 'billing_postcode',
		'billing_city'            => 'billing_city',
		'billing_state'           => 'billing_state',
		'billing_country'         => 'billing_country',
		'billing_company'         => 'billing_company',
		'shipping_first_name'     => 'shipping_first_name',
		'shipping_last_name'      => 'shipping_last_name',
		'shipping_address_1'      => 'shipping_address_1',
		'shipping_address_2'      => 'shipping_address_2',
		'shipping_postcode'       => 'shipping_postcode',
		'shipping_city'           => 'shipping_city',
		'shipping_state'          => 'shipping_state',
		'shipping_country'        => 'shipping_country',
		'shipping_company'        => 'shipping_company',
		'shipping_phone'          => 'shipping_phone',
		'customer_note'           => 'customer_note',
		'order_items'             => 'order_items',
		'order_notes'             => 'order_notes',
		'renewal_orders'          => 'renewal_orders',
		'shipping_items'          => 'shipping_items',
		'coupon_items'            => 'coupon_items',
		'fee_items'               => 'fee_items',
		'tax_items'               => 'tax_items',
		'download_permissions'    => 'download_permissions',
	);
	/**
	 * Reserved column names.
	 *
	 * Enables adding extra arguments or setting defaults for the request.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $columns    Import columns.
	 */
	return apply_filters( 'hf_csv_subscription_order_header_columns', $columns );
