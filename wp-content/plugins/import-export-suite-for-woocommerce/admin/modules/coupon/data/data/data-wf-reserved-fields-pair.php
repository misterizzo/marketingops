<?php
/**
 * Coupon columns of the plugin
 *
 * @package   ImportExportSuite\Admin\Modules\Coupon
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
	'woocommerce_csv_product_import_reserved_fields_pair',
	array(
		'ID'                         => array(
			'title'       => 'Coupon ID',
			'description' => 'Coupon ID',
		),
		'post_title'                 => array(
			'title'       => 'Coupon Title',
			'description' => 'Name of the coupon ',
		),
		// 'post_name' => array('title'=>'Coupon Permalink','description'=>'Unique part of the coupon URL'),
		'post_status'                => array(
			'title'       => 'Coupon Status',
			'description' => 'Coupon Status ( published , draft ...)',
		),
		// 'post_content' => array('title'=>'Coupon Description','description'=>'Description about the Coupon'),
		'post_excerpt'               => array(
			'title'       => 'Coupon Short Description',
			'description' => 'Short description about the Coupon',
		),
		'post_date'                  => array(
			'title'       => 'Post Date',
			'description' => 'Coupon posted date',
			'type'        => 'date',
		),
		'discount_type'              => array(
			'title'       => 'Coupon Type',
			'description' => 'fixed_cart OR percent OR fixed_product OR percent_product',
		),
		'coupon_amount'              => array(
			'title'       => 'Coupon Amount',
			'description' => 'Numeric values',
		),
		'individual_use'             => array(
			'title'       => 'Individual Use?',
			'description' => 'yes or no',
		),
		'product_ids'                => array(
			'title'       => 'Associated Product Ids',
			'description' => 'With comma(,) Separator',
		),
		'exclude_product_ids'        => array(
			'title'       => 'Exclude Product Ids',
			'description' => 'With comma(,) Separator',
		),
		'usage_count'                => array(
			'title'       => 'No of times used',
			'description' => 'Numeric Values',
		),
		'usage_limit'                => array(
			'title'       => 'Usage Limit Per Coupon',
			'description' => 'Numeric Values',
		),
		'usage_limit_per_user'       => array(
			'title'       => 'Usage Limit Per User',
			'description' => 'Numeric Values',
		),
		'limit_usage_to_x_items'     => array(
			'title'       => 'Limit Usage To X Items',
			'description' => 'Maximum Number Of Individual Items This Coupon Can Apply',
		),
		'date_expires'               => array(
			'title'       => 'Expiry Date',
			'description' => 'YYYY-MM-DD',
			'type'        => 'date',
		),
		'free_shipping'              => array(
			'title'       => 'Is Free Shipping?',
			'description' => 'yes or no',
		),
		'exclude_sale_items'         => array(
			'title'       => 'Is Exclude Sale Items?',
			'description' => 'yes or no',
		),
		'product_categories'         => array(
			'title'       => 'Product Categories',
			'description' => 'with comma(,) Separator',
		),
		'exclude_product_categories' => array(
			'title'       => 'Exclude Product  Categories',
			'description' => 'With comma(,) Separator',
		),
		'minimum_amount'             => array(
			'title'       => 'Minimum Amount',
			'description' => 'Numeric',
		),
		'maximum_amount'             => array(
			'title'       => 'Maximum Amount',
			'description' => 'Numeric',
		),
		'customer_email'             => array(
			'title'       => 'Restricted Customers Email Ids',
			'description' => 'With comma(,) Separator',
		),
	)
);
