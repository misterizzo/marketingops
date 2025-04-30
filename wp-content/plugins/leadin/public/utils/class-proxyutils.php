<?php

namespace Leadin\utils;

use Leadin\data\Portal_Options;
use Leadin\data\Filters;
/**
 * Static class containing all the utility functions related to proxy mappings.
 */
class ProxyUtils {

	/**
	 * Info logger function to log messages.
	 *
	 * @param string $message The message to log.
	 */
	public static function info_log( $message ) {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound, WordPress.NamingConventions.ValidHookName.UseUnderscores
		do_action( 'qm/debug', $message );
	}

	/**
	 * Error logger function to log messages.
	 *
	 * @param string $message The message to log.
	 */
	public static function error_log( $message ) {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound, WordPress.NamingConventions.ValidHookName.UseUnderscores
		do_action( 'qm/error', $message );
	}

	/**
	 * Get the client IP address.
	 *
	 * @return string The client IP address.
	 */
	public static function get_client_ip() {
		$ip_keys = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);
		foreach ( $ip_keys as $key ) {
			if ( isset( $_SERVER[ $key ] ) && ! empty( $_SERVER[ $key ] ) ) {
				return sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
			}
		}
		return '';
	}

	/**
	 * Get the destination domain.
	 *
	 * @return string The destination domain.
	 */
	public static function get_destination_domain() {
		return isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
	}

	/**
	 * Get the proxy plugin mapping API base URL.
	 *
	 * @return string The API base URL.
	 */
	public static function get_plugin_mappings_api_url() {
		return Filters::apply_plugin_mappings_api_url();
	}

	/**
	 * Get the proxy base URL.
	 *
	 * @return string The proxy base URL.
	 */
	public static function get_proxy_base_url() {
		return Filters::apply_sites_proxy_cdn_filters();
	}
}
