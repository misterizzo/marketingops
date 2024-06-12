<?php
/**
 * WordPress OAuth Main Functions File
 *
 * @version 3.2.0 (IMPORTANT)
 *
 * Modifying this file will cause the plugin to crash. This could also result in the the entire WordPress install
 * to become unstable. This file is considered sensitive and thus we have provided simple protection against file
 * manipulation.
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( file_exists( dirname( __FILE__ ) . '/hooks.php' ) ) {
	include_once dirname( __FILE__ ) . '/hooks.php';
}

// Hook into core filters
require_once dirname( __FILE__ ) . '/filters.php';

// Hook into core actions
require_once dirname( __FILE__ ) . '/actions.php';

add_action( 'init', 'wo_types' );
function wo_types() {
	$labels = array(
		'name' => _x( 'Client', 'post type general name', 'wp-oauth' ),
		'singular_name' => _x( 'Client', 'post type singular name', 'wp-oauth' ),
		'menu_name' => _x( 'Clients', 'admin menu', 'wp-oauth' ),
		'name_admin_bar' => _x( 'Client', 'add new on admin bar', 'wp-oauth' ),
		'add_new' => _x( 'Add New', 'Client', 'wp-oauth' ),
		'add_new_item' => __( 'Add New BoClientok', 'wp-oauth' ),
		'new_item' => __( 'New Client', 'wp-oauth' ),
		'edit_item' => __( 'Edit Client', 'wp-oauth' ),
		'view_item' => __( 'View Client', 'wp-oauth' ),
		'all_items' => __( 'All Clients', 'wp-oauth' ),
		'search_items' => __( 'Search Clients', 'wp-oauth' ),
		'parent_item_colon' => __( 'Parent Clients:', 'wp-oauth' ),
		'not_found' => __( 'No clients found.', 'wp-oauth' ),
		'not_found_in_trash' => __( 'No clients found in Trash.', 'wp-oauth' ),
	);

	$args = array(
		'labels' => $labels,
		'description' => __( 'Description.', 'wp-oauth' ),
		'public' => false,
		'publicly_queryable' => false,
		'show_ui' => true,
		'show_in_menu' => false,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'wo_client' ),
		'capability_type' => 'post',
		'has_archive' => true,
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array( 'title' ),
		'exclude_from_search' => true,
	);

	register_post_type( 'wo_client', $args );
}

/**
 * [wo_create_client description]
 *
 * @param [type] $user [description]
 *
 * @return [type]       [description]
 *
 * @todo Add role and permissions check
 */
function wo_insert_client( $client_data = null ) {
	// @todo Look into changing capabilities to create_clients after proper mapping has been done
	if ( ! current_user_can( 'manage_options' ) || is_null( $client_data ) ) {
		exit( 'Not Allowed' );

		return false;
	}

	do_action( 'wo_before_create_client', array( $client_data ) );

	// Generate the keys
	$client_id = wo_gen_key();
	$client_secret = wo_gen_key();

	// Sanitize inputs
	$grant_types = isset( $client_data['grant_types'] ) ? $client_data['grant_types'] : array();
	$grant_types = array_map( 'esc_attr', $grant_types );
	$user_id = intval( $client_data['user_id'] );
	$redirect_url = sanitize_text_field( $client_data['redirect_uri'] );
	$scopes = sanitize_text_field( $client_data['scope'] );

	if ( in_array( 'authorization_code', $grant_types ) ) {
		$grant_types = array( 'authorization_code', 'implicit' );
	}

	$client = array(
		'post_title' => wp_strip_all_tags( $client_data['name'] ),
		'post_content' => ' ',
		'post_status' => 'publish',
		'post_author' => get_current_user_id(),
		'post_type' => 'wo_client',
		'comment_status' => 'closed',
		'meta_input' => array(
			'client_id' => $client_id,
			'client_secret' => $client_secret,
			'grant_types' => $grant_types,
			'redirect_uri' => $redirect_url,
			'user_id' => $user_id,
			'scope' => $scopes,
		),

	);

	// Insert the post into the database
	$client_insert = wp_insert_post( $client );
	if ( is_wp_error( $client_insert ) ) {
		exit( sanitize_text_field( $client_insert->get_error_message() ) );
	}

	return $client_insert;
}

/**
 * Update a client
 *
 * @param null $client
 *
 * @return false|int|void
 */
function wo_update_client( $client = null ) {
	if ( is_null( $client ) ) {
		return;
	}

	$client_data = array(
		'ID' => intval( $client['edit_client'] ),
		'post_title' => wp_strip_all_tags( $client['name'] ),
	);
	wp_update_post( $client_data, true );

	$grant_types = isset( $client['grant_types'] ) ? $client['grant_types'] : array();
	$grant_types = array_map( 'esc_attr', $grant_types );
	$user_id = intval( $client['user_id'] );
	$redirect_url = sanitize_text_field( $client['redirect_uri'] );
	$scopes = sanitize_text_field( $client['scope'] );

	if ( in_array( 'authorization_code', $grant_types ) ) {
		$grant_types = array( 'authorization_code', 'implicit' );
	}

	if ( empty( $client['client_secret'] ) ) {
		$client['client_secret'] = get_post_meta( $client['edit_client'], 'client_secret', true );
	}

	update_post_meta( $client['edit_client'], 'client_id', sanitize_text_field( $client['client_id'] ) );
	update_post_meta( $client['edit_client'], 'client_secret', sanitize_text_field( $client['client_secret'] ) );
	update_post_meta( $client['edit_client'], 'grant_types', $grant_types );
	update_post_meta( $client['edit_client'], 'redirect_uri', $redirect_url );
	update_post_meta( $client['edit_client'], 'user_id', $user_id );
	update_post_meta( $client['edit_client'], 'scope', $scopes );
}

/**
 * Get a client by client ID
 *
 * @param $client_id
 */
function get_client_by_client_id( $client_id ) {
	$query = new \WP_Query();
	$clients = $query->query(
		array(
			'post_type' => 'wo_client',
			'post_status' => 'any',
			'meta_query' => array(
				array(
					'key' => 'client_id',
					'value' => $client_id,
				),
			),
		)
	);

	if ( $clients ) {
		$client = $clients[0];
		$client->client_secret = get_post_meta( $client->ID, 'client_secret', true );
		$client->redirect_uri = get_post_meta( $client->ID, 'redirect_uri', true );
		$client->grant_types = get_post_meta( $client->ID, 'grant_types', true );
		$client->user_id = get_post_meta( $client->ID, 'user_id', true );
		$client->scope = get_post_meta( $client->ID, 'scope', true );
		$client->meta = get_post_meta( $client->ID );

		return (array) $client;
	}
}

/**
 * Retrieve a client from the database
 *
 * @param null $id
 *
 * @return array|null|object|void
 */
function wo_get_client( $id = null ) {
	if ( is_null( $id ) ) {
		return;
	}

	global $wpdb;
	$client = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts WHERE ID = %s", array( $id ) ) );
	if ( ! $client ) {
		return false;
	}

	$client->grant_types = maybe_unserialize( get_post_meta( $client->ID, 'grant_types', true ) );
	$client->user_id = get_post_meta( $client->ID, 'user_id', true );

	return $client;
}

/**
 * Generates a 40 Character key is generated by default but should be adjustable in the admin
 *
 * @return [type] [description]
 *
 * @todo Allow more characters to be added to the character list to provide complex keys
 */
function wo_gen_key( $length = 40 ) {

	// Gather the settings
	$user_defined_length = wo_setting( 'token_length' );

	if ( $user_defined_length > 255 ) {
		$user_defined_length = 255;
	}

	// If user setting is larger than 0, then define it
	if ( $user_defined_length > 0 ) {
		$length = $user_defined_length;
	}

	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$randomString = '';

	for ( $i = 0; $i < $length; $i++ ) {
		$randomString .= $characters[ wp_rand( 0, strlen( $characters ) - 1 ) ];
	}

	return $randomString;
}

/**
 * Blowfish Encryptions
 *
 * @param [type]  $input  [description]
 * @param integer $rounds [description]
 *
 * @return [type]          [description]
 *
 * REQUIRES ATLEAST 5.3.x
 */
function wo_crypt( $input, $rounds = 7 ) {
	$salt = '';
	$salt_chars = array_merge( range( 'A', 'Z' ), range( 'a', 'z' ), range( 0, 9 ) );
	for ( $i = 0; $i < 22; $i++ ) {
		$salt .= $salt_chars[ array_rand( $salt_chars ) ];
	}

	return crypt( $input, sprintf( '$2a$%02d$', $rounds ) . $salt );
}

/**
 * Check if there is more than one client in the system
 *
 * @return boolean [description]
 */
function has_a_client() {
	$client = new \WP_Query(
		array(
			'post_type' => 'wo_client',
			'post_status' => 'any',
		)
	);

	if ( $client->have_posts() ) {
		return true;
	}

	return false;
}

/**
 * Get the client IP multiple ways since REMOTE_ADDR is not always the best way to do so
 *
 * @return [type] [description]
 */
function client_ip() {
	$ipaddress = '';
	if ( getenv( 'HTTP_CLIENT_IP' ) ) {
		$ipaddress = getenv( 'HTTP_CLIENT_IP' );
	} elseif ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
		$ipaddress = getenv( 'HTTP_X_FORWARDED_FOR' );
	} elseif ( getenv( 'HTTP_X_FORWARDED' ) ) {
		$ipaddress = getenv( 'HTTP_X_FORWARDED' );
	} elseif ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
		$ipaddress = getenv( 'HTTP_FORWARDED_FOR' );
	} elseif ( getenv( 'HTTP_FORWARDED' ) ) {
		$ipaddress = getenv( 'HTTP_FORWARDED' );
	} elseif ( getenv( 'REMOTE_ADDR' ) ) {
		$ipaddress = getenv( 'REMOTE_ADDR' );
	} else {
		$ipaddress = 'UNKNOWN';
	}

	return $ipaddress;
}

/**
 * Check if server is running windows
 *
 * @return boolean [description]
 */
function wo_os_is_win() {
	if ( strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' ) {
		return true;
	}

	return false;
}

/**
 * Retireve the server keys location
 *
 * @return array
 */
function wpoauth_get_server_certs() {
	$upload_dir = wp_get_upload_dir();
	$key_dir = $upload_dir['basedir'] . '/wo-keys/';

	$keys = apply_filters(
		'wo_server_keys',
		array(
			'public' => $key_dir . '/public_key.pem',
			'private' => $key_dir . '/private_key.pem',
		)
	);

	return $keys;
}

/**
 * Generate server
 *
 * @param bool $overwrite
 *
 * @return bool
 */
function wp_oauth_generate_server_keys( $overwrite = false ) {
	$upload_dir = wp_get_upload_dir();
	$key_dir = $upload_dir['basedir'] . '/wo-keys/';

	if ( ! file_exists( $key_dir ) ) {
		wp_mkdir_p( $key_dir );
	}

	file_put_contents( $key_dir . '/.htaccess', 'deny from all' );
	$cert_locs = wpoauth_get_server_certs();

	if ( ! file_exists( $cert_locs['private'] ) || $overwrite ) {
		$res = openssl_pkey_new(
			array(
				'private_key_bits' => 2048,
				'private_key_type' => OPENSSL_KEYTYPE_RSA,
			)
		);
		openssl_pkey_export( $res, $privKey );
		file_put_contents( $cert_locs['private'], $privKey );
	}

	if ( ! file_exists( $cert_locs['public'] ) || $overwrite ) {
		$pubKey = openssl_pkey_get_details( $res );
		$pubKey = $pubKey['key'];
		file_put_contents( $cert_locs['public'], $pubKey );
	}

	/*
	 * Moved here from the setup function in 4.0.2. This is used for the KID paramters for OpenID. The KID is a unique
	 * key per certificate so it makes sense that it is only ran and updated when the certificates are installed and or
	 * regenerated.
	 */
	update_option( 'wp_oauth_activation_time', time() );

	return true;
}

/**
 * Return the private key for signing
 *
 * @return [type] [description]
 * @since  3.0.5
 */
function wpoauth_get_private_server_key() {
	$keys = wpoauth_get_server_certs();

	return file_get_contents( $keys['private'] );
}

/**
 * Returns the public key
 *
 * @return [type] [description]
 * @since  3.1.0
 */
function wpoauth_get_public_server_key() {
	$keys = wpoauth_get_server_certs();

	return file_get_contents( $keys['public'] );
}

/**
 * Returns the set ALGO that is to be used for the server to encode
 *
 * @return String Type of algorithm used for encoding and decoding.
 * @since  3.1.93
 * @todo Possibly set this to be adjusted somewhere. The id_token calls for it to be set by each
 * client as a pref but we need to keep this simple.
 */
function wpoauth_get_jwt_algorithm() {
	return 'RS256';
}

/**
 * Check to see if there is certificates that have been generated
 *
 * @return boolean [description]
 */
function wp_oauth_has_certificates() {
	$keys = wpoauth_get_server_certs();

	if ( is_array( $keys ) ) {
		foreach ( $keys as $key ) {
			if ( ! file_exists( $key ) ) {
				return false;
			}
		}

		return true;
	} else {

		return false;
	}
}

/**
 * Returns the file sizes of the certificates in an array for display in the admin
 **/
function wpoauth_get_cetificate_filesizes() {
	if ( wp_oauth_has_certificates() ) {
		$keys = wpoauth_get_server_certs();

		$public_file_size = filesize( $keys['public'] );
		$private_file_size = filesize( $keys['private'] );
		$array_return = array(
			'public' => array(
				'size' => $public_file_size,
				'modified' => date_i18n( 'F d, Y H:i:s', filemtime( $keys['public'] ), true ),
			),
			'private' => array(
				'size' => $private_file_size,
				'modified' => date_i18n( 'F d, Y H:i:s', filemtime( $keys['private'] ), true ),
			),
		);

		return $array_return;
	}

	return false;
}

/**
 * Retrieves WP OAuth Server settings
 *
 * @param [type] $key [description]
 *
 * @return [type]      [description]
 */
function wo_setting( $key = null ) {
	$default_settings = _WO()->default_settings;
	$settings = get_option( 'wo_options' );
	$settings = array_merge(
		$default_settings,
		array_filter(
			$settings,
			function ($value) {
				return $value !== '';
			}
		)
	);

	// No key is provided, let return the entire options table
	if ( is_null( $key ) ) {
		return $settings;
	}

	if ( ! isset( $settings[ $key ] ) ) {
		return;
	}

	return $settings[ $key ];
}

/**
 * Returns if the core is valid
 *
 * @return [type] [description]
 */
function wo_is_core_valid() {
	if ( WOCHECKSUM != strtoupper( md5_file( __FILE__ ) ) ) {
		return false;
	}

	return true;
}

/**
 * Returns if the plugin is licensed
 *
 * @return Boolean True is valid
 */
function wo_is_licensed() {
	$options = get_option( 'wo_license_information' );

	return @$options['license'] == 'valid' ? true : false;
}

/**
 * Retrieve the license status
 *
 * @return String Valid|Invalid
 */
function license_status() {
	$options = get_option( 'wo_options' );
	$status = isset( $options['license_status'] ) ? $options['license_status'] : '';
	switch ( $status ) {
		case 'invalid':
			echo 'Invalid. Activate your license now.';
			break;
		case 'valid':
			echo 'Valid';
			break;
	}
}

/**
 * Retrieves the license information
 *
 * @return Array License Information
 */
function wo_license_information() {
	return get_option( 'wo_license_information' );
}

/**
 * Retrieves the license key
 *
 * @return [type] [description]
 */
function wo_license_key() {
	return get_option( 'wo_license_key' );
}

/**
 * Cheater watch
 *
 * @return [type] [description]
 */
function wo_cheater_watch() {
	$wo_license_key = get_option( 'wo_license_key', '' );
	if ( wo_is_licensed() && strlen( $wo_license_key ) > 0 ) {
		return;
	}

	$api_params = array(
		'edd_action' => 'activate_license',
		'license' => $wo_license_key,
		'item_name' => urlencode( 'WP OAuth Server' ),
		'url' => home_url(),
	);

	$response = wp_remote_get(
		add_query_arg( $api_params, 'https://wp-oauth.com' )
	);

	if ( ! is_wp_error( $response ) ) {
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		update_option( 'wo_license_key', $wo_license_key );
		update_option( 'wo_license_information', (array) $license_data );
	}
}

add_action( 'wo_daily_tasks_hook', 'wo_cheater_watch' );

/**
 * Determine is environment is development
 */
function wo_is_dev() {
	return add_filter( 'wo_development', '__return_false' );
}

/**
 * Check if the server is using a secure connection or not.
 *
 * @return bool
 */
function wo_is_protocol_secure() {
	$isSecure = false;
	if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) {
		$isSecure = true;
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || ! empty( $_SERVER['HTTP_X_FORWARDED_SSL'] ) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on' ) {
		$isSecure = true;
	}

	return $isSecure;
}

/**
 * Setup the admin tabs as needed
 *
 * @param $page
 * @param $tabs
 * @param $location
 * @param $default
 * @param null $current
 */
function wo_admin_setting_tabs( $page, $tabs, $location, $default, $current = null ) {
	if ( is_null( $current ) ) {
		$current = 'general';
	}

	if ( file_exists( $location . $current . '.php' ) ) {
		include_once $location . $current . '.php';
	}
}

function wo_display_settings_tabs() {
	$tabs = apply_filters(
		'wo_server_status_tabs',
		array(
			'general' => 'General Information'
		)
	);
	$settings_tab = 'wo_server_status';
	echo wo_admin_setting_tabs( $settings_tab, $tabs, dirname( __FILE__ ) . '/admin/tabs/', 'general', null );
}

function wp_oauth_server_debug_backtrace() {
	$friendly = array();
	$backtrace = debug_backtrace();
	foreach ( $backtrace as $file ) {
		$friendly[] = array(
			'file' => $file['file'],
			'function' => $file['function'],
			'line' => $file['line'],
		);
	}

	return $friendly;
}

// Public Functions.
require_once dirname( __FILE__ ) . '/public.php';