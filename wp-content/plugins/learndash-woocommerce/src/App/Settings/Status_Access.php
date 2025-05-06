<?php
/**
 * Status access settings class file.
 *
 * @since 2.0.0
 *
 * @package LearnDash\WooCommerce
 */

namespace LearnDash\WooCommerce\Settings;

/**
 * Status access settings class.
 *
 * The class requires to be used in a function hooked to the `learndash_loaded` action
 * at the earliest as it uses the LearnDash settings and sections API.
 *
 * @since 2.0.0
 */
class Status_Access {
	/**
	 * Get access denied order statuses specified in the settings.
	 *
	 * This method returns an array of order statuses in key label pairs.
	 * E.g. [
	 *   'cancelled' => 'Cancelled',
	 *   'failed' => 'Failed',
	 * ]
	 *
	 * @since 2.0.0
	 *
	 * @return array<string, string>
	 */
	public static function get_access_denied_order_statuses(): array {
		return Settings::get_statuses_by_access_status( 'order', false );
	}

	/**
	 * Get access granted subscription statuses specified in the settings.
	 *
	 * This method returns an array of subscription statuses in key label pairs.
	 * E.g. [
	 *   'active' => 'Active',
	 *   'on-hold' => 'On Hold',
	 * ]
	 *
	 * @since 2.0.0
	 *
	 * @return array<string, string>
	 */
	public static function get_access_granted_subscription_statuses(): array {
		return Settings::get_statuses_by_access_status( 'subscription', true );
	}

	/**
	 * Get access denied subscription statuses specified in the settings.
	 *
	 * This method returns an array of subscription statuses in key label pairs.
	 * E.g. [
	 *   'cancelled' => 'Cancelled',
	 *   'expired' => 'Expired',
	 * ]
	 *
	 * @since 2.0.0
	 *
	 * @return array<string, string>
	 */
	public static function get_access_denied_subscription_statuses(): array {
		return Settings::get_statuses_by_access_status( 'subscription', false );
	}

	/**
	 * Get access granted order statuses specified in the settings.
	 *
	 * This method returns an array of order statuses in key label pairs.
	 * E.g. [
	 *   'completed' => 'Completed',
	 *   'processing' => 'Processing',
	 * ]
	 *
	 * @since 2.0.0
	 *
	 * @return array<string, string>
	 */
	public static function get_access_granted_order_statuses(): array {
		return Settings::get_statuses_by_access_status( 'order', true );
	}
}
