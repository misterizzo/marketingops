<?php
/**
 * Functions for uninstall LearnDash LMS - Elementor
 *
 * @since 1.0.9
 *
 * @package LearnDash\Elementor
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'vendor-prefixed/autoload.php';

/**
 * Fires on plugin uninstall.
 *
 * @since 1.0.9
 *
 * @return void
 */
do_action( 'learndash_elementor_uninstall' );
