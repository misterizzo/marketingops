<?php
/**
 * WP OAuth Server Actions
 *
 * @author  Justin Greer <justin@justin-greer.com>
 * @package WordPress OAuth Server
 */

/**
 * Invalidate any token and refresh tokens during password reset
 *
 * @param object $user WP_User Object
 * @param String $new_pass New Password
 *
 * @return Void
 *
 * @since 3.1.8
 */
function wo_password_reset_action( $user, $new_pass ) {
	global $wpdb;
	$wpdb->delete( "{$wpdb->prefix}oauth_access_tokens", array( 'user_id' => $user->ID ) );
	$wpdb->delete( "{$wpdb->prefix}oauth_refresh_tokens", array( 'user_id' => $user->ID ) );
}

add_action( 'password_reset', 'wo_password_reset_action', 10, 2 );

/**
 * [wo_profile_update_action description]
 *
 * @param int $user_id WP User ID
 *
 * @return Void
 */
function wo_profile_update_action( $user_id ) {
	if ( ! isset( $_POST['pass1'] ) || '' == $_POST['pass1'] ) {
		return;
	}
	global $wpdb;
	$wpdb->delete( "{$wpdb->prefix}oauth_access_tokens", array( 'user_id' => $user_id ) );
	$wpdb->delete( "{$wpdb->prefix}oauth_refresh_tokens", array( 'user_id' => $user_id ) );
}

add_action( 'profile_update', 'wo_profile_update_action' );

/**
 * Only allow 1 acces_token at a time
 *
 * @param [type] $results [description]
 *
 * @return [type]          [description]
 */
function wo_only_allow_one_access_token( $object ) {
	if ( is_null( $object ) ) {
		return;
	}
	
	// Define the user ID
	$user_id = $object['user_id'];
	
	// Remove all other access tokens and refresh tokens from the system
	global $wpdb;
	$wpdb->delete( "{$wpdb->prefix}oauth_access_tokens", array( 'user_id' => $user_id ) );
	$wpdb->delete( "{$wpdb->prefix}oauth_refresh_tokens", array( 'user_id' => $user_id ) );
	
	return;
}

/**
 * Restrict users to only have a single access token
 *
 * @since 3.2.7
 */
$wo_restrict_single_access_token = apply_filters( 'wo_restrict_single_access_token', false );
if ( $wo_restrict_single_access_token ) {
	add_action( 'wo_set_access_token', 'wo_only_allow_one_access_token' );
}

/**
 * Define DOING_OAUTH if OAuth is present in the URL
 */
add_action( 'login_init', 'wp_oauth_setup_doing_oauth_login_page', 1 );
function wp_oauth_setup_doing_oauth_login_page() {
	/*
	* Check if there is a redirect_url parameter during the login page.
	*
	* If the script has made it this far for WP OAuth Server, there will be redirected URL exposed for the login redirect
	* required by WP OAuth Server. We can use this redirect as a flag to check for the path. If "oauth" is present, we
	* should assume that the request is an oauth request and should not be redirected.
	*/
	$redirect = isset( $_GET['redirect_to'] ) ? wp_sanitize_redirect( $_GET['redirect_to'] ) : '';
	$url      = wp_parse_url( $redirect );
	
	// Added index check before trying to use the index..
	if ( isset( $url['path'] ) && strpos( $url['path'], 'oauth' ) !== false ) {
		define( 'DOING_OAUTH', true );
	}
}

/**
 * Post Handler for regenerating certificates if needed and verify
 * that they have been creates.
 */
add_action( 'admin_post_wpoauth_regenerate_certificates', 'wp_oauth_regenerate_certificates', 10 );
function wp_oauth_regenerate_certificates() {
	/*
	* Security Check
	* This feature should only be left to the admin of WP and nothing else.
	*/
	if ( current_user_can( 'manage_options' ) && wp_verify_nonce( sanitize_text_field( $_REQUEST['wo_nonce'] ), 'wo_nonce' ) ) {
		wp_oauth_generate_server_keys( true );
	}
	
	wp_safe_redirect( admin_url( 'admin.php?page=wo_server_status' ) );
}

