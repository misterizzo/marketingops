<?php
/**
 * Order reserved post columns
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
	'woocommerce_csv_order_reserved_fields_pair',
	array(
		'order_id'             => array(
			'title'       => 'Order ID ',
			'description' => 'Order ID ',
		),
		'order_number'         => array(
			'title'       => 'Order Number',
			'description' => 'Order Number',
		),
		'order_date'           => array(
			'title'       => 'Order Date',
			'description' => 'Order Date',
			'type'        => 'date',
		),
		'paid_date'            => array(
			'title'       => 'Paid Date',
			'description' => 'Paid Date',
			'type'        => 'date',
		),
		'status'               => array(
			'title'       => 'Order Status',
			'description' => 'Order Status ( processing , pending ...) ',
		),
		'shipping_total'       => array(
			'title'       => 'Shipping Total',
			'description' => 'Shipping Total amount',
		),
		'shipping_tax_total'   => array(
			'title'       => 'Shipping Tax Total',
			'description' => 'Shipping Tax Total',
		),
		'fee_total'            => array(
			'title'       => 'Total Fee',
			'description' => 'Total Fee',
		),
		'fee_tax_total'        => array(
			'title'       => 'Total Tax Fee',
			'description' => 'Total Tax Fee',
		),
		'tax_total'            => array(
			'title'       => 'Total Tax',
			'description' => 'Total Tax',
		),
		'cart_discount'        => array(
			'title'       => 'Cart Discount',
			'description' => 'Cart Discount',
		),
		'order_discount'       => array(
			'title'       => 'Order Discount',
			'description' => 'Order Discount',
		),
		'discount_total'       => array(
			'title'       => 'Discount Total',
			'description' => 'Discount Total',
		),
		'order_total'          => array(
			'title'       => 'Order Total',
			'description' => 'Order Total',
		),
		'order_currency'       => array(
			'title'       => 'order_currency',
			'description' => 'Order Currency',
		),
		'payment_method'       => array(
			'title'       => 'payment_method',
			'description' => 'Payment Method',
		),
		'payment_method_title' => array(
			'title'       => 'payment_method_title',
			'description' => 'Payment Method Title',
		),
		'transaction_id'       => array(
			'title'       => 'transaction_id',
			'description' => 'Payment transaction id',
		),
		'customer_ip_address'  => array(
			'title'       => 'customer_ip_address',
			'description' => 'Customer ip address',
		),
		'customer_user_agent'  => array(
			'title'       => 'customer_user_agent',
			'description' => 'Customer user agent',
		),
		'shipping_method'      => array(
			'title'       => 'shipping_method',
			'description' => 'Shipping Method',
		),
		'customer_email'       => array(
			'title'       => 'customer_email',
			'description' => 'Customer Email ( if not provided order will be created as Guest)',
		),
		'customer_user'        => array(
			'title'       => 'customer_user',
			'description' => 'Customer id ( if not provided order will be created as Guest)',
		),
		'billing_first_name'   => array(
			'title'       => 'billing_first_name',
			'description' => 'billing_first_name',
		),
		'billing_last_name'    => array(
			'title'       => 'billing_last_name',
			'description' => 'billing_last_name',
		),
		'billing_company'      => array(
			'title'       => 'billing_company',
			'description' => 'billing_company',
		),
		'billing_email'        => array(
			'title'       => 'billing_email',
			'description' => 'billing_email',
		),
		'billing_phone'        => array(
			'title'       => 'billing_phone',
			'description' => 'billing_phone',
		),
		'billing_address_1'    => array(
			'title'       => 'billing_address_1',
			'description' => 'billing_address_1',
		),
		'billing_address_2'    => array(
			'title'       => 'billing_address_2',
			'description' => 'billing_address_2',
		),
		'billing_postcode'     => array(
			'title'       => 'billing_postcode',
			'description' => 'billing_postcode',
		),
		'billing_city'         => array(
			'title'       => 'billing_city',
			'description' => 'billing_city',
		),
		'billing_state'        => array(
			'title'       => 'billing_state',
			'description' => 'billing_state',
		),
		'billing_country'      => array(
			'title'       => 'billing_country',
			'description' => 'billing_country',
		),
		'shipping_first_name'  => array(
			'title'       => 'shipping_first_name',
			'description' => 'shipping_first_name',
		),
		'shipping_last_name'   => array(
			'title'       => 'shipping_last_name',
			'description' => 'shipping_last_name',
		),
		'shipping_company'     => array(
			'title'       => 'shipping_company',
			'description' => 'shipping_company',
		),
		'shipping_phone'       => array(
			'title'       => 'shipping_phone',
			'description' => 'shipping_phone',
		),
		'shipping_address_1'   => array(
			'title'       => 'shipping_address_1',
			'description' => 'shipping_address_1',
		),
		'shipping_address_2'   => array(
			'title'       => 'shipping_address_2',
			'description' => 'shipping_address_2',
		),
		'shipping_postcode'    => array(
			'title'       => 'shipping_postcode',
			'description' => 'shipping_postcode',
		),
		'shipping_city'        => array(
			'title'       => 'shipping_city',
			'description' => 'shipping_city',
		),
		'shipping_state'       => array(
			'title'       => 'shipping_state',
			'description' => 'shipping_state',
		),
		'shipping_country'     => array(
			'title'       => 'shipping_country',
			'description' => 'shipping_country',
		),
		'customer_note'        => array(
			'title'       => 'customer_note',
			'description' => 'customer_note',
		),
		'wt_import_key'        => array(
			'title'       => 'wt_import_key',
			'description' => 'wt_import_key',
		),
		'tax_items'            => array(
			'title'       => 'tax_items',
			'description' => 'tax_items',
		),
		'shipping_items'       => array(
			'title'       => 'shipping_items',
			'description' => 'shipping_items',
		),
		'fee_items'            => array(
			'title'       => 'fee_items',
			'description' => 'fee_items',
		),
		'coupon_items'         => array(
			'title'       => 'coupon_items',
			'description' => 'coupons',
		),
		'refund_items'         => array(
			'title'       => 'refund_items',
			'description' => 'refund_items',
		),
		'order_notes'          => array(
			'title'       => 'order_notes',
			'description' => 'Order notes',
		),
		'line_item_'           => array(
			'title'       => 'line_item_',
			'description' => 'Line Items',
			'field_type'  => 'start_with',
		),
		'download_permissions' => array(
			'title'       => 'Downloadable Product Permissions ',
			'description' => 'Permissions for order items will automatically be granted when the order status changes to processing or completed.',
		),
	)
);
