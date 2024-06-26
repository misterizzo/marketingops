<?php
/**
 * Product post hidden columns
 *
 * @link
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exclude columns handled specifically.
 *
 * Enables adding extra arguments or setting defaults for the request.
 *
 * @since 1.0.0
 *
 * @param array   $columns    Import columns.
 */
return apply_filters(
	'wt_product_exclude_hidden_meta_columns',
	array(
		'_product_attributes',
		'_file_paths',
		'_woocommerce_gpf_data',
		'_price',
		'_default_attributes',
		'_edit_last',
		'_edit_lock',
		'_wp_old_slug',
		'_product_image_gallery',
		'_max_variation_price',
		'_max_variation_regular_price',
		'_max_variation_sale_price',
		'_min_variation_price',
		'_min_variation_regular_price',
		'_min_variation_sale_price',
	)
);
