<?php
/**
 * WP OAuth Server Cron Jobs
 *
 * @author  Justin Greer <justin@dash10.digital>
 * @package WP OAuth Server
 */

add_action( 'wpo_global_cleanup', 'wpo_global_cleanup_functionality' );

/**
 * Cleans up the database and clutter that may be caused by many connections calling tokens and codes
 * This is a simple cleanup script that can be ran whenever needed but is designed for the WPO Cron Cleanup every hour.
 *
 * This function will loop through the largest DB tables known to build up and clean them
 * There are actions in place to allow for hooking outside of the plugin core
 *
 * Actions Introduced
 *
 * - wpo_global_cleanup_after_auth_code_cleanup (passes number of record affected)
 * - wpo_global_cleanup_after_access_token_cleanup (passes number of record affected)
 * - wpo_global_cleanup_after_refresh_token_cleanup (passes number of record affected) *
 *
 * @updated Aug 7, 2023 - Added prepare statement and reviewed code suggestions
 */
function wpo_global_cleanup_functionality() {
	global $wpdb;
	
	// This MUST return the MYSQL Date Time format, NOT a UNIX timestamp.
	$current_time = current_time( 'mysql' );
	
	// Delete All Auth Codes that are expired up until now.
	$expired_auth_codes = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}oauth_authorization_codes WHERE expires <= %s", array( $current_time ) ) );
	do_action( 'wpo_global_cleanup_after_auth_code_cleanup', $expired_auth_codes );
	
	// Handle expired access that are expired up until now.
	$expired_access_tokens = $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->prefix}oauth_access_tokens WHERE expires <= %s", array($current_time) ) );
	do_action( 'wpo_global_cleanup_after_access_token_cleanup', $expired_access_tokens );
}
