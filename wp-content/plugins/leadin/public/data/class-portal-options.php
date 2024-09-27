<?php

namespace Leadin\data;

/**
 * Class that wraps the functions to access options related to the HubSpot account.
 */
class Portal_Options {
	const PORTAL_ID                 = LEADIN_PREFIX . '_portalId';
	const PORTAL_DOMAIN             = LEADIN_PREFIX . '_portal_domain';
	const ACCOUNT_NAME              = LEADIN_PREFIX . '_account_name';
	const HUBLET                    = LEADIN_PREFIX . '_hublet';
	const DISABLE_INTERNAL_TRACKING = LEADIN_PREFIX . '_disable_internal_tracking';
	const ACTIVATION_TIME           = LEADIN_PREFIX . '_activation_time';
	const REFRESH_TOKEN             = LEADIN_PREFIX . '_refresh_token';
	const BUSINESS_UNIT_ID          = LEADIN_PREFIX . '_business_unit_id';
	const LAST_AUTHORIZE_TIME       = LEADIN_PREFIX . '_last_authorize_time';
	const LAST_DEAUTHORIZE_TIME     = LEADIN_PREFIX . '_last_deauthorize_time';
	const LAST_DISCONNECT_TIME      = LEADIN_PREFIX . '_last_disconnect_time';
	/**
	 * Return portal id.
	 */
	public static function get_portal_id() {
		return get_option( self::PORTAL_ID );
	}

	/**
	 * Set portal id.
	 *
	 * @param Number $portal_id HubSpot portal id.
	 */
	public static function set_portal_id( $portal_id ) {
		return update_option( self::PORTAL_ID, $portal_id );
	}

	/**
	 * Delete portal id.
	 */
	public static function delete_portal_id() {
		return delete_option( self::PORTAL_ID );
	}

	/**
	 * Return portal's domain.
	 */
	public static function get_portal_domain() {
		return get_option( self::PORTAL_DOMAIN );
	}

	/**
	 * Set portal domain.
	 *
	 * @param string $domain domain.
	 */
	public static function set_portal_domain( $domain ) {
		return update_option( self::PORTAL_DOMAIN, $domain );
	}

	/**
	 * Delete portal domain.
	 */
	public static function delete_portal_domain() {
		return delete_option( self::PORTAL_DOMAIN );
	}

	/**
	 * Return account name.
	 */
	public static function get_account_name() {
		return get_option( self::ACCOUNT_NAME );
	}

	/**
	 * Set account name.
	 *
	 * @param string $name name.
	 */
	public static function set_account_name( $name ) {
		return update_option( self::ACCOUNT_NAME, $name );
	}

	/**
	 * Delete account name.
	 */
	public static function delete_account_name() {
		return delete_option( self::ACCOUNT_NAME );
	}

	/**
	 * Return option containing hublet info.
	 */
	public static function get_hublet() {
		return get_option( self::HUBLET );
	}

	/**
	 * Return option containing hublet info.
	 *
	 * @param string $hublet hublet.
	 */
	public static function set_hublet( $hublet ) {
		return update_option( self::HUBLET, $hublet );
	}

	/**
	 * Delete hublet
	 */
	public static function delete_hublet() {
		return delete_option( self::HUBLET );
	}

	/**
	 * Return option flag for disabling internal users to appear at HS analytics.
	 */
	public static function get_disable_internal_tracking() {
		return get_option( self::DISABLE_INTERNAL_TRACKING );
	}

	/**
	 * Set option containing flag for disabling internal users to appear at HS analytics.
	 *
	 * @param string $internal_tracking hublet.
	 */
	public static function set_disable_internal_tracking( $internal_tracking = '0' ) {
		return update_option( self::DISABLE_INTERNAL_TRACKING, $internal_tracking );
	}

	/**
	 * Delete option flag for disabling internal tracking
	 */
	public static function delete_disable_internal_tracking() {
		return delete_option( self::DISABLE_INTERNAL_TRACKING );
	}

	/**
	 * Return activation time.
	 */
	public static function get_activation_time() {
		return get_option( self::ACTIVATION_TIME );
	}

	/**
	 * Set activation time.
	 */
	public static function set_activation_time() {
		return update_option( self::ACTIVATION_TIME, time() );
	}

	/**
	 * Delete portal id.
	 */
	public static function delete_activation_time() {
		return delete_option( self::ACTIVATION_TIME );
	}

	/**
	 * Return refresh access token.
	 */
	public static function get_refresh_token() {
		return get_option( self::REFRESH_TOKEN );
	}

	/**
	 * Set refresh access token.
	 *
	 * @param string $refresh_token token.
	 */
	public static function set_refresh_token( $refresh_token ) {
		return update_option( self::REFRESH_TOKEN, $refresh_token );
	}

	/**
	 * Delete refresh access token.
	 */
	public static function delete_refresh_token() {
		return delete_option( self::REFRESH_TOKEN );
	}

	/**
	 * Return device id hash.
	 */
	public static function get_device_id() {
		$site_url = get_home_url();
		$user_id  = get_current_user_id();
		return hash( 'sha256', "$site_url:$user_id" );
	}

	/**
	 * Return business_unit_id for connected portal.
	 */
	public static function get_business_unit_id() {
		return get_option( self::BUSINESS_UNIT_ID );
	}

	/**
	 * Set business_unit_id for the connected portal.
	 *
	 * @param number $business_unit_id businessUnitId.
	 */
	public static function set_business_unit_id( $business_unit_id ) {
		return update_option( self::BUSINESS_UNIT_ID, $business_unit_id );
	}

	/**
	 * Delete business_unit_id.
	 */
	public static function delete_business_unit_id() {
		return delete_option( self::BUSINESS_UNIT_ID );
	}

	/**
	 * Set when last authorized.
	 */
	public static function set_last_authorize_time() {
		return update_option( self::LAST_AUTHORIZE_TIME, time() );
	}
	/**
	 * Set when last deauthorized.
	 */
	public static function set_last_deauthorize_time() {
		return update_option( self::LAST_DEAUTHORIZE_TIME, time() );
	}

	/**
	 * Set when last disconnect.
	 */
	public static function set_last_disconnect_time() {
		return update_option( self::LAST_DISCONNECT_TIME, time() );
	}

	/**
	 * Get the last time authorization was performed.
	 *
	 * @return mixed The last authorization time, or false if not set.
	 */
	public static function get_last_authorize_time() {
		return get_option( self::LAST_AUTHORIZE_TIME, false );
	}

	/**
	 * Get the last time deauthorization was performed.
	 *
	 * @return mixed The last deauthorization time, or false if not set.
	 */
	public static function get_last_deauthorize_time() {
		return get_option( self::LAST_DEAUTHORIZE_TIME, false );
	}

	/**
	 * Get the last time a disconnect was performed.
	 *
	 * @return mixed The last disconnect time, or false if not set.
	 */
	public static function get_last_disconnect_time() {
		return get_option( self::LAST_DISCONNECT_TIME, false );
	}
}
