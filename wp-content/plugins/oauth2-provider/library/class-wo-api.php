<?php
/**
 * WP OAuth Server API
 *
 * @author Justin Greer <justin@justn-greer.com>
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

do_action( 'wo_before_api', array( $_REQUEST ) );

require_once dirname( __FILE__ ) . '/WPOAuth2/Autoloader.php';
WPOAuth2\Autoloader::register();

$settings = wo_setting();

$default_settings = _WO()->default_settings;

// Shutdown the API if it is not enabled
if ( 0 == wo_setting( 'enabled' ) ) {
	do_action( 'wo_before_unavailable_error' );
	$response = new WPOAuth2\Response();
	$response->setError( 503, 'error', __( 'temporarily unavailable', 'wp-oauth' ) );
	$response->send();
	exit;
}

$wo_strict_api_lockdown = apply_filters( 'wo_strict_api_lockdown', false );
if ( $wo_strict_api_lockdown && ! wo_is_core_valid() && ! wo_is_dev() ) {
	$response = new WPOAuth2\Response();
	$response->setError( 403, 'security_risk', __( 'plugin core is not authentic', 'wp-oauth' ) );
	$response->send();
	exit;
}

global $wp_query;
$method = $wp_query->get( 'oauth' );
$well_known = $wp_query->get( 'well-known' );
$storage = new WPOAuth2\Storage\Wordpressdb();
$config = array(
	'use_crypto_tokens' => false,
	'store_encrypted_token_string' => wo_setting( 'jwt_bearer_enabled' ),
	'use_openid_connect' => false,
	'issuer' => home_url( null, 'https' ),
	'id_lifetime' => 3600,
	'access_lifetime' => 3600,
	'refresh_token_lifetime' => 86400,
	'www_realm' => apply_filters( 'wo_www_realm', 'Service' ),
	'token_param_name' => apply_filters( 'wo_token_param_name', 'access_token' ),
	'token_bearer_header_name' => apply_filters( 'wo_token_bearer_header_name', 'Bearer' ),
	'enforce_state' => wo_setting( 'enforce_state' ),
	'require_exact_redirect_uri' => wo_setting( 'require_exact_redirect_uri' ),
	'allow_implicit' => wo_setting( 'implicit_enabled' ),
	'allow_credentials_in_request_body' => apply_filters( 'wo_allow_credentials_in_request_body', true ),
	'allow_public_clients' => apply_filters( 'wo_allow_public_clients', true ),
	'always_issue_new_refresh_token' => apply_filters( 'wo_always_issue_new_refresh_token', true ),
	'unset_refresh_token_after_use' => apply_filters( 'wo_unset_refresh_token_after_use', false ),
	'redirect_status_code' => apply_filters( 'wo_redirect_status_code', 302 ),
	'use_jwt_access_tokens' => wo_setting( 'jwt_bearer_enabled' ),
	'code_challenge_method' => apply_filters( 'wo_pcke_challenge_method', 'plain' ),
);

$server = new WPOAuth2\Server( $storage, $config );

/*
|--------------------------------------------------------------------------
| SUPPORTED GRANT TYPES
|--------------------------------------------------------------------------
|
| Authorization Code will always be on. This may be a bug or a f@#$ up on
| my end. None the less, these are controlled in the server settings page.
|
 */
$support_grant_types = array();

/*
if ( '1' == wo_setting( 'auth_code_enabled' ) ) {
	$server->addGrantType( new WPOAuth2\GrantType\AuthorizationCode( $storage ) );
}

if ( '1' == wo_setting( 'client_creds_enabled' ) ) {
	$server->addGrantType( new WPOAuth2\GrantType\ClientCredentials( $storage ) );
}

if ( '1' == wo_setting( 'user_creds_enabled' ) ) {
	$server->addGrantType( new WPOAuth2\GrantType\UserCredentials( $storage ) );
}

if ( '1' == wo_setting( 'refresh_tokens_enabled' ) ) {
	$server->addGrantType( new WPOAuth2\GrantType\RefreshToken( $storage, $config ) );
}

if ( '1' == wo_setting( 'use_openid_connect' ) ) {
	$server->addGrantType( new WPOAuth2\OpenID\GrantType\AuthorizationCode( $storage, $config ) );
}
*/

/**
 * JWT Bearer Support
 */
if ( '1' == wo_setting( 'jwt_bearer_enabled' ) ) {
	// $server->addGrantType( new WPOAuth2\GrantType\JwtBearer( $storage, site_url() ) );
}

/*
|--------------------------------------------------------------------------
| DEFAULT SCOPES
|--------------------------------------------------------------------------
|
| Supported scopes can be added to the plugin by modifying the wo_scopes.
| Until further notice, the default scope is 'basic'. Plans are in place to
| allow this scope to be adjusted.
|
 */
$default_scope = apply_filters( 'wo_default_scope', 'basic' );

$supported_scopes = apply_filters(
	'wo_scopes',
	array(
		'openid',
		'profile',
		'email',
		'basic',
	)
);

$scope_util = new WPOAuth2\Scope(
	array(
		'default_scope' => $default_scope,
		'supported_scopes' => $supported_scopes,
	)
);

$server->setScopeUtil( $scope_util );

/*
|--------------------------------------------------------------------------
| TOKEN CATCH
|--------------------------------------------------------------------------
|
| The following code is ran when a request is made to the server using the
| Authorization Code (implicit) Grant Type as well as request tokens
|
 */
if ( 'token' == $method ) {
	do_action( 'wo_before_token_method', array( $_REQUEST ) );
	$server->handleTokenRequest( WPOAuth2\Request::createFromGlobals() )->send();
	exit;
}

/**
 * TOKEN REVOKE
 *
 * The following will revoke a given token. This endpoint accepts code hinting as well.
 *
 * @url https://tools.ietf.org/html/rfc7009
 */
if ( 'revoke' == $method ) {

	do_action( 'wo_before_token_revoke', array( $_REQUEST ) );
	$request = WPOAuth2\Request::createFromGlobals();
	$response = new WPOAuth2\Response();

	$server->handleRevokeRequest( $request, $response );
	$response->send();
	exit;
}

/*
|--------------------------------------------------------------------------
| AUTHORIZATION CODE CATCH
|--------------------------------------------------------------------------
|
| The following code is ran when a request is made to the server using the
| Authorization Code (not implicit) Grant Type.
|
| 1. Check if the user is logged in (redirect if not)
| 2. Validate the request (client_id, redirect_uri)
| 3. Create the authorization request using the authentication user's user_id
|
*/
if ( 'authorize' == $method ) {

	do_action( 'wo_before_authorize_method', array( $_REQUEST ) );
	$request = WPOAuth2\Request::createFromGlobals();
	$response = new WPOAuth2\Response();

	if ( ! $server->validateAuthorizeRequest( $request, $response ) ) {
		$response->send();
		exit;
	}

	// For backward compatibility. If grant request is enabled, it will be overridden during the grant request.
	$is_authorized = true;
	$prompt = '';

	/**
	 * Check to see if prompt is enabled and if so, lets handle the request.
	 *
	 * The parameter for prompt is intended to be used for OpenID Connect but will will change it up. For example,
	 * Google uses ?prompt on their OAuth 2.0 flow. This should be a good addition but should be kept for off for
	 * backward compatibility.
	 *
	 * @link http://openid.net/specs/openid-connect-core-1_0.html#rfc.section.3.1.2.1
	 *
	 * @since 3.4.0
	 */
	if ( ! isset( $_REQUEST['ignore_prompt'] ) ) {
		if ( isset( $_REQUEST['prompt'] ) ) {
			$prompt = isset( $_REQUEST['prompt'] ) ? sanitize_text_field( $_REQUEST['prompt'] ) : 'consent';
			if ( $prompt == 'none' ) {
				$is_authorized = false;
			} elseif ( $prompt == 'login' ) {
				wp_logout();
				if ( $settings['home_url_modify'] ) {
					wp_redirect( home_url( add_query_arg( array( 'ignore_prompt' => '' ) ) ) );
					exit;
				}
				wp_redirect( site_url( add_query_arg( array( 'ignore_prompt' => '' ) ) ) );
				exit;
			}
		}
	}

	/*
	 * Redirect the user to the login screen if they are not logged in.
	 * This check takes into account the home url modifier setting that changes what site url function is used from WP.
	 */
	if ( ! is_user_logged_in() ) {
		if ( $settings['home_url_modify'] ) {
			wp_redirect( wp_login_url( add_query_arg( $_GET, home_url( 'oauth/authorize' ) ) ) );
			exit;
		}
		wp_redirect( wp_login_url( add_query_arg( $_GET, site_url( 'oauth/authorize' ) ) ) );
		exit;
	}

	/**
	 * Check and see if we should include a grant request or not to the user.
	 * For backward compatibility, this is disabled by default but can be enabled.
	 *
	 * @todo If the user clicks deny, then the application does not return to the app and simply loads the request again.
	 * we need to look into a to allow the app the report that it is not authorized while
	 *
	 * @example add_filter('wo_use_grant_request', '__return_true');
	 *
	 * @since 3.5.0
	 */
	if ( apply_filters( 'wo_use_grant_request', false ) ) {

		$current_user = get_current_user_id();
		$grant_status = get_user_meta( $current_user, 'wo_grant_' . $_REQUEST['client_id'], true );

		if ( $grant_status == '' || $prompt == 'consent' ) {

			// @todo Add documentation for this feature
			$request_template = dirname( __FILE__ ) . '/templates/grant-request.php';
			if ( file_exists( get_stylesheet_directory() . '/wp-oauth-server/templates/grant-request.php' ) ) {
				$request_template = get_stylesheet_directory() . '/wp-oauth-server/templates/grant-request.php';
			}

			include $request_template;
			exit;
		} elseif ( $grant_status == 'allow' ) {

			$is_authorized = true;

		} elseif ( $grant_status == 'deny' ) {

			$is_authorized = false;

		}
	}

	$user_id = get_current_user_id();
	do_action( 'wo_authorization_code_authorize', array( $user_id ) );

	$user = get_user_by( 'ID', $user_id );

	$server->handleAuthorizeRequest( $request, $response, $is_authorized, $user_id );
	$response->send();
	exit;
}

/**
 * Well Know Keys
 * This responds with a JWKS response.
 *
 * @link https://tools.ietf.org/html/rfc7517
 */
if ( 'keys' == $well_known ) {
	$keys = wpoauth_get_server_certs();
	$public_key = openssl_pkey_get_public( file_get_contents( $keys['public'] ) );
	$public_key = openssl_pkey_get_details( $public_key );
	$response = new WPOAuth2\Response(
		array(
			'keys' => array(
				array(
					'kty' => 'RSA',
					'alg' => 'RS256',
					'kid' => get_option( 'wp_oauth_activation_time' ),
					'use' => 'sig',
					'n' => rtrim( strtr( base64_encode( $public_key['rsa']['n'] ), '+/', '-_' ), '=' ),
					'e' => base64_encode( $public_key['rsa']['e'] ),
				),
			),
		)
	);
	$response->send();
	exit;
}

/*
|--------------------------------------------------------------------------
| OpenID Discovery
|--------------------------------------------------------------------------
|
*/
/*
 * @todo add a dynamic registration endpoint and function to the plugin.
 * @todo add option for service_documentation endpoint in the settings
 *
 * @todo Depercate the destroy endpoint and move it to the proper revoke endpoint.
 *
 * @link https://tools.ietf.org/html/draft-ietf-oauth-discovery-08
 */
if ( 'openid-configuration' == $well_known || 'oauth-authorization-server' == $well_known ) {
	$openid_discovery_values = apply_filters(
		'wpo_well_known_openid_configuration',
		array(
			'issuer' => home_url( null, 'https' ),
			'authorization_endpoint' => home_url( '/oauth/authorize/' ),
			'token_endpoint' => home_url( '/oauth/token/' ),
			'userinfo_endpoint' => home_url( '/oauth/me/' ),
			'end_session_endpoint' => home_url( '/oauth/destroy/' ),
			'jwks_uri' => home_url( '/.well-known/keys/' ),
			'revocation_endpoint' => home_url( '/oauth/revoke/' ),
			'introspection_endpoint' => home_url( '/oauth/introspection/' ),
			'registration_endpoint' => null,
			'grant_types_supported' => array(
				'authorization_code',
				'client_credentials',
				'refresh_token',
				'uswo_me_resource_returner_credentials',
				'implicit',
			),
			'service_documentation' => null,
			'scopes_supported' => array(
				'openid',
				'profile',
				'email',
				'basic',
			),
			'response_types_supported' => array(
				'code',
				'id_token',
				'token id_token',
				'code id_token',
			),
			'subject_types_supported' => array(
				'public',
			),
			'id_token_signing_alg_values_supported' => array(
				'RS256',
			),
			'token_endpoint_auth_methods_supported' => array(
				'client_secret_basic',
			),
		)
	);

	$openid_discovery_configuration = apply_filters( 'wo_openid_discovery', $openid_discovery_values );

	$response = new WPOAuth2\Response( $openid_discovery_configuration );
	$response->send();
	exit;
}

/*
|--------------------------------------------------------------------------
| EXTENDABLE RESOURCE SERVER METHODS
|--------------------------------------------------------------------------
|
| Below this line is part of the developer API. Do not edit directly.
| Refer to the developer documentation for extending the WordPress OAuth
| Server plugin core functionality.
|
| @todo Document and tighten up error messages. All error messages will soon be
| controlled through apply_filters so start planning for a filter error list to
| allow for developers to customize error messages.
|
*/
$resource_server_methods = apply_filters( 'wo_endpoints', null );

// Check to see if the method exists in the filter.
if ( array_key_exists( $method, $resource_server_methods ) ) {

	// If the method is set to public, lets just run the method without.
	if ( isset( $resource_server_methods[ $method ]['public'] ) && $resource_server_methods[ $method ]['public'] ) {
		call_user_func( $resource_server_methods[ $method ]['func'] );
		exit;
	}

	/**
	 * Check if the user is logged in.
	 *
	 * @since 3.3.2
	 */
	$current_user = apply_filters( 'determine_current_user', null );
	if ( is_null( $current_user ) || empty( $current_user ) ) {
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

	$token = $server->getAccessTokenData( WPOAuth2\Request::createFromGlobals() );
	if ( is_null( $token ) ) {
		$server->getResponse()->send();
		exit;
	}

	call_user_func_array( $resource_server_methods[ $method ]['func'], array( $token ) );

	exit;
}

/*
 * Server error response. End of line
 *
 * @since 3.1.0
 */
$response = new WPOAuth2\Response();
$response->setError( 400, 'invalid_request', __( 'unknown request', 'wp-oauth' ) );
$response->send();
exit;