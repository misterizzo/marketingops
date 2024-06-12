<?php
/**
 * WP OAuth Server Filters
 *
 * This file should contain all the filters used throughout the plugin that is not required for immediate use.
 *
 * @author Justin Greer  <justin@justin-greer.com>
 */

/**
 * Personal Data Eraser Class
 *
 * @since 3.7.0
 */
require_once dirname( __FILE__ ) . '/wo-personal-data-gpdr.php';

/**
 * WordPress OAuth Server Error Filter
 *
 * @deprecated Schedule for removal. The PHP server handles all these now.
 */
function wo_api_error_setup( $errors ) {
	$errors['invalid_access_token'] = 'The access token is invalid or has expired';
	$errors['invalid_refresh_token'] = 'The refresh token is invalid or has expired';
	$errors['invalid_credentials'] = 'Invalid user credentials';

	return $errors;
}

add_filter( 'WO_API_Errors', 'wo_api_error_setup', 1 );

/**
 * Default Method Filter for the resource server API calls
 *
 * @since 3.1.8 Endpoints now can accept public methods that bypass the token authorization
 */
function wo_default_endpoints() {
	$endpoints = array(
		'me' => array(
			'func' => 'wpoauth_method_me',
			'public' => false,
		),
		'destroy' => array(
			'func' => 'wpoauth_method_destroy',
			'public' => true,
		),
		'introspection' => array(
			'func' => 'wpoauth_method_introspection',
			'false' => true,
		),
	);

	return $endpoints;
}

add_filter( 'wo_endpoints', 'wo_default_endpoints', 1 );

/**
 * Token Introspection
 *
 * @url https://tools.ietf.org/html/rfc7662
 *
 * @since 4.0
 *
 * @param null $token
 */
function wpoauth_method_introspection( $token = null ) {
	/*
	 * Added action to give capability to add logging for abuse protection.
	 */
	do_action( 'wo_before_introspection_method', $token );

	// If the token parameter is null, the token is empty or invalid. Typically, an error would happen however this
	// introspection check needs to return a valid token.
	if ( is_null( $token ) ) {
		$response = new WPOAuth2\Response(
			array(
				'active' => false,
			)
		);
		$response->send();
		exit;
	}

	if ( ! isset( $token['user_id'] ) || $token['user_id'] == 0 ) {
		$response = new WPOAuth2\Response();
		$response->setError(
			400,
			'invalid_request',
			'Invalid token',
			'https://tools.ietf.org/html/draft-ietf-oauth-v2-31#section-7.2'
		);
		$response->send();
		exit;
	}

	$user = get_user_by( 'id', $token['user_id'] );
	$request = WPOAuth2\Request::createFromGlobals();

	if ( ! empty( $token['access_token'] ) ) {
		$access_token = $token['access_token'];
	} else {
		$access_token = esc_textarea( $request->request['access_token'] );
	}

	if ( strtolower( @$request->server['REQUEST_METHOD'] ) != 'post' ) {
		$response = new WPOAuth2\Response();
		$response->setError(
			405,
			'invalid_request',
			'The request method must be POST when calling the introspection endpoint.',
			'https://tools.ietf.org/html/rfc7662#section-2.1'
		);
		$response->addHttpHeaders( array( 'Allow' => 'POST' ) );
		$response->send();
		exit;
	}

	// Check if the token is valid
	$valid = wo_public_get_access_token( $access_token );

	if ( false == $valid ) {
		$response = new WPOAuth2\Response(
			array(
				'active' => false,
			)
		);
		$response->send();
		exit;
	}

	if ( $valid['user_id'] != 0 || ! is_null( $valid['user_id'] ) ) {
		$user = get_userdata( $valid['user_id'] );
		$username = $user->user_login;
	}
	$introspection = apply_filters(
		'wo_introspection_response',
		array(
			'active' => true,
			'scope' => $valid['scope'],
			'client_id' => $valid['client_id'],
		)
	);
	$response = new WPOAuth2\Response( $introspection );
	$response->send();

	exit;
}

/**
 * DEFAULT DESTROY METHOD
 * This method has been added to help secure installs that want to manually destroy sessions (valid access tokens).
 *
 * @since 3.1.5
 *
 * @param null $token
 */
function wpoauth_method_destroy() {
	$request = $_REQUEST;
	if ( isset( $request['access_token'] ) ) {
		$access_token = sanitize_text_field( $request['access_token'] );
	}

	// 10.30.17 Added basic session support for OpenID Connect
	// If there is not access token provided, lets destroy the cookie session.
	// http://openid.net/specs/openid-connect-session-1_0.html#toc

	/**
	 * If there is no access token, this will destroy the session.
	 * Since 4.4.0, the auto redirect has been removed to prevent abuse of the redirect. It has been replaced with a message
	 * and link to the redirect URL. This is to help with backward compatiability as well as protect the user from bad juju.
	 * 
	 * @todo It is very possible that we depercate the flow of allowing none access token requests to destroy the session.
	 */
	if ( empty( $access_token ) ) {
		$redirect_allowed = false;

		// To prevent abuse of the redirect, we will only redirect if the user was logged in but until we can check for
		// the redirect, we set a reference for later use after as clear the session.
		if ( is_user_logged_in() ) {
			$redirect_allowed = true;
		}

		wp_clear_auth_cookie();

		/**
		 * @since 4.4.0 Removed automated redirect to prevent abuse of the redirect. The user 
		 */
		if ( ! empty( $_REQUEST['post_logout_redirect_uri'] ) && $redirect_allowed ) {
			$redirect_url = esc_url( $_REQUEST['post_logout_redirect_uri'] );
			print 'You have been logged out. Either close this window or verify the link is valid before clicking to go back: <a href="' . $redirect_url . '">' . $redirect_url . '</a>';
			exit;
		}
	}

	// If there is an access token, remove it from the DB
	if ( ! empty( $access_token ) ) {

		global $wpdb;
		$stmt = $wpdb->delete( "{$wpdb->prefix}oauth_access_tokens", array( 'access_token' => $access_token ) );

		// Remove the refresh token as well
		if ( ! empty( $_REQUEST['refresh_token'] ) ) {
			$stmt = $wpdb->delete( "{$wpdb->prefix}oauth_refresh_tokens", array( 'refresh_token' => $_REQUEST['refresh_token'] ) );
		}
	}

	/**
	 * Clear any session that might be active
	 * @since 3.4.4
	 */
	wp_clear_auth_cookie();

	// prepare the return
	$response = new WPOAuth2\Response(
		array(
			'status' => true,
			'description' => 'Session destroyed successfully',
		)
	);
	$response->send();
	exit;
}

/**
 * DEFAULT ME METHOD - DO NOT REMOVE DIRECTLY
 * This is the default resource call "/oauth/me". Do not edit or remove.
 *
 * @param null $token
 */
function wpoauth_method_me( $token = null ) {

	if ( ! isset( $token['user_id'] ) || $token['user_id'] == 0 ) {
		$response = new WPOAuth2\Response();
		$response->setError(
			400,
			'invalid_request',
			'Invalid token',
			'https://tools.ietf.org/html/draft-ietf-oauth-v2-31#section-7.2'
		);
		$response->send();
		exit;
	}

	$user = get_user_by( 'id', $token['user_id'] );

	/*
	 * Typically, if a blank user returns, the token belongs to a client that used client credentials. Inform the user
	 * of this.
	 */
	if ( ! $user ) {
		$response = new WPOAuth2\Response();
		$response->setError(
			400,
			'invalid_request',
			'No user found for this token. Ensure it is not a client token.'
		);
		$response->send();
		exit;
	}
	$me_data = (array) $user->data;
	$me_data['user_roles'] = $user->roles;
	$me_data['capabilities'] = $user->allcaps;

	/*
	 * Map the user info if it needs to be.
	 *
	 * @since 4.2.0
	 */
	$user_info_mapping_settings = get_option( 'wp_oauth_server_mapping_settings' );
	if ( ! is_array( $user_info_mapping_settings ) ) {
		$user_info_mapping_settings = array();
	}
	$user_info_mapping_filter = apply_filters( 'wp_oauth_server_user_info_mapping', $user_info_mapping_settings );
	foreach ( $user_info_mapping_filter as $key => $value ) {

		if ( empty( $value ) ) {
			continue;
		}

		// The ID field is the only WP field that needs to be adjusted with the way the mapping tool is build
		if ( $key == 'id' ) {
			$key = 'ID';
		}

		// Remove the fields that are mapped
		$me_data[ $value ] = $me_data[ $key ];
		unset( $me_data[ $key ] );
	}

	unset( $me_data['user_pass'] );
	unset( $me_data['user_activation_key'] );
	unset( $me_data['user_url'] );

	/**
	 * 10.30.17 by Justin Greer <justin@dash10.digital>
	 *
	 * If openid is presented in the scope, we need to provide some more redundant information in a different variable.
	 */
	$scopes = explode( ' ', $token['scope'] );
	if ( in_array( 'openid', $scopes ) ) {
		$me_data['email'] = $me_data['user_email'];
		$me_data['sub'] = $me_data['ID'];
	}

	/**
	 * user information returned by the default me method is filtered
	 *
	 * @since  3.3.7
	 * @filter wo_me_resource_return
	 *
	 * @updated 4.0.4 Added the token to the filter for reference per a request. This will allow easier use of this filter.
	 */
	$me_data = apply_filters( 'wo_me_resource_return', $me_data, $token );

	$response = new WPOAuth2\Response( $me_data );
	$response->send();
	exit;
}

/**
 * Adds OAuth2 to the WP-JSON index
 *
 * @param $response_object
 *
 * @return mixed
 */
function wpoauth_server_register_routes( $response_object ) {
	if ( empty( $response_object->data['authentication'] ) ) {
		$response_object->data['authentication'] = array();
	}
	$response_object->data['authentication']['oauth2'] = array(
		'authorize' => site_url( 'oauth/authorize' ),
		'token' => site_url( 'oauth/token' ),
		'me' => site_url( 'oauth/me' ),
		'version' => '2.0',
		'software' => 'WP OAuth Server',
	);

	return $response_object;
}

add_filter( 'rest_index', 'wpoauth_server_register_routes' );