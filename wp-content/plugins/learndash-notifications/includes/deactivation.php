<?php
/**
 * Deactivation functions.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Notifications
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

function learndash_notifications_deactivate() {
	wp_unschedule_hook( 'learndash_notifications_cron' );
	wp_unschedule_hook( 'learndash_notifications_cron_hourly' );
	wp_unschedule_hook( 'learndash_notifications_drip_lesson' );
	wp_unschedule_hook( 'leanrdash_notifications_send_delayed_email' );
}
