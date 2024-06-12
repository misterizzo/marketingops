<?php
/**
 * Order exclude columns
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
return apply_filters( 'hf_csv_order_exclude_meta_columns', array( 'wf_order_exported_status' ) );
