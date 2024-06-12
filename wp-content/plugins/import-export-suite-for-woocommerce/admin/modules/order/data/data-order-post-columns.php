<?php
/**
 * Order post columns
 *
 * @link
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Filter the query arguments for a request.
 *
 * Enables adding extra arguments or setting defaults for the request.
 *
 * @since 1.0.0
 *
 * @param array   $columns    Import columns.
 */
return apply_filters(
	'hf_csv_order_post_columns',
	array(
		'order_id'             => 'order_id',
		'order_number'         => 'order_number',
		'order_date'           => 'order_date',
		'paid_date'            => 'paid_date',
		'status'               => 'status',
		'shipping_total'       => 'shipping_total',
		'shipping_tax_total'   => 'shipping_tax_total',
		'fee_total'            => 'fee_total',
		'fee_tax_total'        => 'fee_tax_total',
		'tax_total'            => 'tax_total',
		'cart_discount'        => 'cart_discount',
		'order_discount'       => 'order_discount',
		'discount_total'       => 'discount_total',
		'order_total'          => 'order_total',
		'order_subtotal'       => 'order_subtotal',
		'order_currency'       => 'order_currency',
		'payment_method'       => 'payment_method',
		'payment_method_title' => 'payment_method_title',
		'transaction_id'       => 'transaction_id',
		'customer_ip_address'  => 'customer_ip_address',
		'customer_user_agent'  => 'customer_user_agent',
		'shipping_method'      => 'shipping_method',
		'customer_id'          => 'customer_id',
		'customer_user'        => 'customer_user',
		'customer_email'       => 'customer_email',
		'billing_first_name'   => 'billing_first_name',
		'billing_last_name'    => 'billing_last_name',
		'billing_company'      => 'billing_company',
		'billing_email'        => 'billing_email',
		'billing_phone'        => 'billing_phone',
		'billing_address_1'    => 'billing_address_1',
		'billing_address_2'    => 'billing_address_2',
		'billing_postcode'     => 'billing_postcode',
		'billing_city'         => 'billing_city',
		'billing_state'        => 'billing_state',
		'billing_country'      => 'billing_country',
		'shipping_first_name'  => 'shipping_first_name',
		'shipping_last_name'   => 'shipping_last_name',
		'shipping_company'     => 'shipping_company',
		'shipping_phone'       => 'shipping_phone',
		'shipping_address_1'   => 'shipping_address_1',
		'shipping_address_2'   => 'shipping_address_2',
		'shipping_postcode'    => 'shipping_postcode',
		'shipping_city'        => 'shipping_city',
		'shipping_state'       => 'shipping_state',
		'shipping_country'     => 'shipping_country',
		'customer_note'        => 'customer_note',
		'wt_import_key'        => 'wt_import_key',
		'shipping_items'       => 'shipping_items',
		'fee_items'            => 'fee_items',
		'tax_items'            => 'tax_items',
		'coupon_items'         => 'coupon_items',
		'refund_items'         => 'refund_items',
		'order_notes'          => 'order_notes',
		'download_permissions' => 'download_permissions',
	)
);
