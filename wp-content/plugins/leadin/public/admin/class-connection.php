<?php

namespace Leadin\admin;

use Leadin\data\Portal_Options;
use Leadin\data\User;
use Leadin\utils\QueryParameters;
use Leadin\auth\OAuth;

/**
 * Handles portal connection to the plugin.
 */
class Connection {

	const CONNECT_KEYS = array(
		'refresh_token',
		'expires_in',
		'portal_id',
		'portal_domain',
		'portal_name',
		'hublet',
	);

	const CONNECT_NONCE_ARG    = 'leadin_connect';
	const DISCONNECT_NONCE_ARG = 'leadin_disconnect';

	/**
	 * Returns true if a portal has been connected to the plugin
	 */
	public static function is_connected() {
		return ! empty( Portal_Options::get_portal_id() ) && ! empty( OAuth::get_refresh_token() );
	}

	/**
	 * Returns true if the current request is for the plugin to connect to a portal
	 */
	public static function is_connection_requested() {
		$maybe_leadin_connect = QueryParameters::get_param( self::CONNECT_NONCE_ARG, 'hubspot-nonce', self::CONNECT_NONCE_ARG );
		$maybe_refresh_token  = QueryParameters::get_param( 'refresh_token', 'hubspot-nonce', self::CONNECT_NONCE_ARG );

		return isset( $maybe_leadin_connect ) && isset( $maybe_refresh_token );
	}

	/**
	 * Returns true if the current request is for the plugin to connect to a portal
	 */
	public static function is_new_portal() {
		$maybe_is_new_portal = QueryParameters::get_param( 'is_new_portal', 'hubspot-nonce', self::CONNECT_NONCE_ARG );

		return isset( $maybe_is_new_portal );
	}

	/**
	 * Returns true if the current request is to disconnect the plugin from the portal
	 */
	public static function is_disconnection_requested() {
		$maybe_leadin_disconnect = QueryParameters::get_param( self::DISCONNECT_NONCE_ARG, 'hubspot-nonce', self::DISCONNECT_NONCE_ARG );
		return isset( $maybe_leadin_disconnect );
	}
	/**
	 * Retrieves user ID and create new metadata
	 *
	 * @param Array $user_meta array of pairs metadata - value.
	 */
	private static function add_metadata( $user_meta ) {
		$wp_user    = wp_get_current_user();
		$wp_user_id = $wp_user->ID;
		foreach ( $user_meta as $key => $value ) {
			add_user_meta( $wp_user_id, $key, $value );
		}
	}

	/**
	 * Retrieves user ID and deletes a piece of the users meta data.
	 *
	 * @param String $meta_key is the key of the data you want to delete.
	 */
	private static function delete_metadata( $meta_key ) {
		$wp_user    = wp_get_current_user();
		$wp_user_id = $wp_user->ID;
		delete_user_meta( $wp_user_id, $meta_key );
	}

	/**
	 * Connect portal id, domain, name to WordPress options and HubSpot email to user meta data.
	 *
	 * @param Number $portal_id     HubSpot account id.
	 * @param String $portal_name   HubSpot account name.
	 * @param String $portal_domain HubSpot account domain.
	 * @param String $hs_user_email HubSpot user email.
	 * @param String $hublet        HubSpot account's hublet.
	 */
	public static function connect( $portal_id, $portal_name, $portal_domain, $hs_user_email, $hublet ) {
		self::disconnect();
		self::store_portal_info( $portal_id, $portal_name, $portal_domain, $hublet );
		self::add_metadata( array( 'leadin_email' => $hs_user_email ) );
	}

	/**
	 * Connect the plugin with OAuthorization. Storing OAuth tokens and metadata for the connected portal.
	 */
	public static function oauth_connect() {
		$connect_params = QueryParameters::get_parameters( self::CONNECT_KEYS, 'hubspot-nonce', self::CONNECT_NONCE_ARG );

		self::disconnect();
		self::store_portal_info(
			$connect_params['portal_id'],
			$connect_params['portal_name'],
			$connect_params['portal_domain'],
			$connect_params['hublet']
		);

		OAuth::authorize( $connect_params['refresh_token'] );
	}

	/**
	 * Removes portal id and domain from the WordPress options.
	 */
	public static function disconnect() {
		Portal_Options::set_last_disconnect_time();

		self::delete_portal_info();

		$users = get_users(
			array( 'role__in' => array( 'administrator', 'editor' ) ),
			array( 'fields' => array( 'ID' ) )
		);
		foreach ( $users as $user ) {
			delete_user_meta( $user->ID, 'leadin_email' );
			delete_user_meta( $user->ID, 'leadin_skip_review' );
			delete_user_meta( $user->ID, 'leadin_review_banner_last_call' );
			delete_user_meta( $user->ID, 'leadin_has_min_contacts' );
			delete_user_meta( $user->ID, 'leadin_track_consent' );
		}

		OAuth::deauthorize();
	}

	/**
	 * Store the portal metadata for connecting the plugin in the options table
	 *
	 * @param String $portal_id ID for connecting portal.
	 * @param String $portal_name Name of the connecting portal.
	 * @param String $portal_domain Domain for the connecting portal.
	 * @param String $hublet Hublet for the connecting portal.
	 */
	public static function store_portal_info( $portal_id, $portal_name, $portal_domain, $hublet ) {
		Portal_Options::set_portal_id( $portal_id );
		Portal_Options::set_account_name( $portal_name );
		Portal_Options::set_portal_domain( $portal_domain );
		Portal_Options::set_hublet( $hublet );
		Portal_Options::set_disable_internal_tracking();
	}

	/**
	 * Delete stored portal metadata for disconnecting the plugin from the options table
	 */
	private static function delete_portal_info() {
		Portal_Options::delete_portal_id();
		Portal_Options::delete_account_name();
		Portal_Options::delete_portal_domain();
		Portal_Options::delete_hublet();
		Portal_Options::delete_disable_internal_tracking();
		Portal_Options::delete_business_unit_id();
	}
}
