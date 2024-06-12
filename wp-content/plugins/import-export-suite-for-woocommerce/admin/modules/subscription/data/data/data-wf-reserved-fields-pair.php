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

/**
 * Reserved column names.
 *
 * Enables adding extra arguments or setting defaults for the request.
 *
 * @since 1.0.0
 *
 * @param array   $columns    Import columns.
 */
return apply_filters(
	'hf_csv_subscription_alter_order_data_columns',
	array(
		'subscription_id'         => array(
			'title'       => 'Subscription ID',
			'description' => 'Subscription ID',
		),
		'subscription_status'     => array(
			'title'       => 'Subscription Status',
			'description' => 'Subscription Status',
		),
		'customer_id'             => array(
			'title'       => 'Customer ID',
			'description' => 'Customer ID',
		),
		'customer_username'       => array(
			'title'       => 'customer_username',
			'description' => '',
		),
		'customer_email'          => array(
			'title'       => 'customer_email',
			'description' => '',
		),
		'date_created'            => array(
			'title'       => 'Start Date',
			'description' => 'Start Date',
			'type'        => 'date',
		),
		'trial_end_date'          => array(
			'title'       => 'Trial End Date',
			'description' => 'Trial End Date',
			'type'        => 'date',
		),
		'next_payment_date'       => array(
			'title'       => 'Next Payment Date',
			'description' => 'Next Payment Date',
			'type'        => 'date',
		),
		'last_order_date_created' => array(
			'title'       => 'Last Payment Date',
			'description' => 'Last Payment Date',
			'type'        => 'date',
		),
		'end_date'                => array(
			'title'       => 'End Date',
			'description' => 'End Date',
			'type'        => 'date',
		),
		'post_parent'             => array(
			'title'       => 'Parent OrderID',
			'description' => 'Parent order id',
		),
		'billing_period'          => array(
			'title'       => 'Billing Period',
			'description' => 'Billing Period',
		),
		'billing_interval'        => array(
			'title'       => 'Billing Interval',
			'description' => 'Billing Interval',
		),
		'order_shipping'          => array(
			'title'       => 'Total Shipping',
			'description' => 'Total Shipping',
		),
		'order_shipping_tax'      => array(
			'title'       => 'Total Shipping Tax',
			'description' => 'Total Shipping Tax',
		),
		'fee_total'               => array(
			'title'       => 'Total Subscription Fees',
			'description' => 'Total Subscription Fees',
		),
		'fee_tax_total'           => array(
			'title'       => 'Total Fees Tax',
			'description' => 'Total Fees Tax',
		),
		'order_tax'               => array(
			'title'       => 'Subscription Total Tax',
			'description' => 'Subscription Total Tax',
		),
		'cart_discount'           => array(
			'title'       => 'Total Discount',
			'description' => 'Total Discount',
		),
		'cart_discount_tax'       => array(
			'title'       => 'Total Discount Tax',
			'description' => 'Total Discount Tax',
		),
		'order_total'             => array(
			'title'       => 'Subscription Total',
			'description' => 'Subscription Total',
		),
		'order_currency'          => array(
			'title'       => 'Subscription Currency',
			'description' => 'Subscription Currency',
		),
		'payment_method'          => array(
			'title'       => 'Payment Method',
			'description' => 'Payment Method',
		),
		'payment_method_title'    => array(
			'title'       => 'Payment Method Title',
			'description' => 'Payment Method Title',
		),
		'shipping_method'         => array(
			'title'       => 'Shipping Method',
			'description' => 'Shipping Method',
		),
		'billing_first_name'      => array(
			'title'       => 'Billing First Name',
			'description' => 'Billing First Name',
		),
		'billing_last_name'       => array(
			'title'       => 'Billing Last Name',
			'description' => 'Billing Last Name',
		),
		'billing_email'           => array(
			'title'       => 'Billing Email',
			'description' => 'Billing Email',
		),
		'billing_phone'           => array(
			'title'       => 'Billing Phone',
			'description' => 'Billing Phone',
		),
		'billing_address_1'       => array(
			'title'       => 'Billing Address 1',
			'description' => 'Billing Address 1',
		),
		'billing_address_2'       => array(
			'title'       => 'Billing Address 2',
			'description' => 'Billing Address 2',
		),
		'billing_postcode'        => array(
			'title'       => 'Billing Postcode',
			'description' => 'Billing Postcode',
		),
		'billing_city'            => array(
			'title'       => 'Billing City',
			'description' => 'Billing City',
		),
		'billing_state'           => array(
			'title'       => 'Billing State',
			'description' => 'Billing State',
		),
		'billing_country'         => array(
			'title'       => 'Billing Country',
			'description' => 'Billing Country',
		),
		'billing_company'         => array(
			'title'       => 'Billing Company',
			'description' => 'Billing Company',
		),
		'shipping_first_name'     => array(
			'title'       => 'Shipping First Name',
			'description' => 'Shipping First Name',
		),
		'shipping_last_name'      => array(
			'title'       => 'Shipping Last Name',
			'description' => 'Shipping Last Name',
		),
		'shipping_address_1'      => array(
			'title'       => 'Shipping Address 1',
			'description' => 'Shipping Address 1',
		),
		'shipping_address_2'      => array(
			'title'       => 'Shipping Address 2',
			'description' => 'Shipping Address 2',
		),
		'shipping_postcode'       => array(
			'title'       => 'Shipping Post code',
			'description' => 'Shipping Post code',
		),
		'shipping_city'           => array(
			'title'       => 'Shipping City',
			'description' => 'Shipping City',
		),
		'shipping_state'          => array(
			'title'       => 'Shipping State',
			'description' => 'Shipping State',
		),
		'shipping_country'        => array(
			'title'       => 'Shipping Country',
			'description' => 'Shipping Country',
		),
		'shipping_company'        => array(
			'title'       => 'Shipping Company',
			'description' => 'Shipping Company',
		),
		'shipping_phone'          => array(
			'title'       => 'Shipping Phone',
			'description' => 'Shipping Phone',
		),
		'customer_note'           => array(
			'title'       => 'Customer Note',
			'description' => 'Customer Note',
		),
		'order_items'             => array(
			'title'       => 'Subscription Items',
			'description' => 'Subscription Items',
		),
		'order_notes'             => array(
			'title'       => 'Subscription order notes',
			'description' => 'Subscription order notes',
		),
		'renewal_orders'          => array(
			'title'       => 'Renewal OrderIDs',
			'description' => 'Renewal order id',
		),
		'shipping_items'          => array(
			'title'       => 'Shipping Items',
			'description' => 'Shipping Items',
		),
		'coupon_items'            => array(
			'title'       => 'Coupons',
			'description' => 'Coupons',
		),
		'fee_items'               => array(
			'title'       => 'Fees',
			'description' => 'Fees',
		),
		'tax_items'               => array(
			'title'       => 'Taxes',
			'description' => 'Taxes',
		),
		'download_permissions'    => array(
			'title'       => 'Download Permissions Granted',
			'description' => 'Permissions for order items will automatically be granted when the order status changes to processing or completed.',
		),
	)
);
