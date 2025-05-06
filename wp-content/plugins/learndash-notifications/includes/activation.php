<?php
/**
 * Activation functions.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Notifications
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function learndash_notifications_activate() {
	if ( ! wp_next_scheduled( 'learndash_notifications_cron' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'learndash_notifications_cron' );
	}
	if ( ! wp_next_scheduled( 'leanrdash_notifications_send_delayed_email' ) ) {
		wp_schedule_single_event( time(), 'leanrdash_notifications_send_delayed_email' );
	}
	update_option( 'learndash_notifications_drips_check', true );
}
