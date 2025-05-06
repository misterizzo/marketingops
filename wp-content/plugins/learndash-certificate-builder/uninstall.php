<?php
/**
 * Functions for uninstall LearnDash LMS - Certificate Builder
 *
 * @since 1.1.0
 *
 * @package LearnDash\Certificate_Builder
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'vendor-prefixed/autoload.php';

/**
 * Fires on plugin uninstall.
 *
 * @since 1.1.0
 *
 * @return void
 */
do_action( 'learndash_certificate_builder_uninstall' );
