<?php
/**
 * Functions for uninstall LearnDash LMS - Notifications
 *
 * @since 1.6.5
 *
 * @package LearnDash\Notifications
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'vendor-prefixed/autoload.php';

/**
 * Fires on plugin uninstall.
 *
 * @since 1.6.5
 *
 * @return void
 */
do_action( 'learndash_notifications_uninstall' );
