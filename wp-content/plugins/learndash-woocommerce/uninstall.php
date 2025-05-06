<?php
/**
 * Functions for uninstalling LearnDash LMS - WooCommerce
 *
 * @since 2.0.0
 *
 * @package LearnDash\WooCommerce
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'vendor-prefixed/autoload.php';

/**
 * Fires on plugin uninstall.
 *
 * @since 2.0.0
 *
 * @return void
 */
do_action( 'learndash_woocommerce_uninstall' );
