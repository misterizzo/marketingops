<?php

namespace Leadin;

use Leadin\data\Filters;
use Leadin\data\Portal_Options;
use Leadin\utils\ProxyUtils;

/**
 * Class responsible for proxy mappings.
 */
class Proxy_Mappings {

	const PROXY_MAPS_CACHE_TTL_FILTER = 'proxy_maps_cache_ttl';
	const PROXY_MAPS_CACHE_TTL        = 1800;
	const PREDEFINED_PATH_PATTERNS    = array(
		'~^/_hcms/.*$~',
		'~^/hs/.*$~',
		'~^/hubfs/.*$~',
		'~^/hs-fs/.*$~',
		'~^/cs/c/.*$~',
		'~^/e3t/.*$~',
	);

	/**
	 * Proxy_Mappings constructor, register callback for template redirect and scheduler.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_custom_schedule' ) );
		add_action( 'template_redirect', array( $this, 'proxy_requests' ) );
		add_action( 'wp', array( $this, 'schedule_and_fetch_mapping_update' ) );
		add_action( 'leadin_update_proxy_mappings', array( $this, 'fetch_and_cache_mappings' ) );
		add_action( 'leadin_reset_wp_mappings_cache', array( $this, 'refetch_proxy_mapping' ) );
	}

	/**
	 * Registers the custom cron schedule which schedules and fetches the mapping update
	 *
	 * @return void
	 */
	public function register_custom_schedule() {
			add_filter(
				'cron_schedules',
				function( $schedules ) {
					$schedules[ self::PROXY_MAPS_CACHE_TTL_FILTER ] = array(
						'interval' => 1800,
						'display'  => __( 'Fetch Proxy Maps Schedule', 'leadin' ),
					);
					return $schedules;
				}
			);
	}

	/**
	 * Fetches proxy mappings from a remote API and caches them.
	 *
	 * This function retrieves the portal ID and uses it to fetch proxy mappings
	 * from a specified API endpoint. The fetched mappings are then cached for
	 * a predefined duration. If the portal ID is empty or an error occurs during
	 * the fetch process, appropriate error messages are logged.
	 *
	 * @return void
	 */
	public function fetch_and_cache_mappings() {
		if ( empty( Portal_Options::get_portal_id() ) ) {
			ProxyUtils::error_log( 'Portal ID is empty. Skipping fetching mappings.' );
			return;
		}

		$json_url = ProxyUtils::get_plugin_mappings_api_url();
		ProxyUtils::info_log( "Fetching mappings from: $json_url" );

		$response = wp_remote_get(
			$json_url,
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'Accept'       => 'application/json',
				),
				'body'    => array( 'portalId' => Portal_Options::get_portal_id() ),
			)
		);

		if ( is_wp_error( $response ) ) {
			ProxyUtils::error_log( 'Error fetching JSON mappings: ' . $response->get_error_message() );
			return;
		}

		$mappings = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( is_array( $mappings ) ) {
			set_transient( 'proxy_mappings', $mappings, self::PROXY_MAPS_CACHE_TTL );
			ProxyUtils::info_log( 'Mappings cached successfully.' );
		} else {
			ProxyUtils::error_log( 'Invalid JSON format for proxy mappings.' );
		}
	}

	/**
	 * Refetches the proxy mappings.
	 *
	 * This function is responsible for refetching the proxy mappings. It is
	 * called when the mappings need to be updated, such as when the mappings
	 * are disabled or when the mappings are reset.
	 *
	 * @return void
	 */
	public function refetch_proxy_mapping() {
		$this->schedule_and_fetch_mapping_update( true );
	}

	/**
	 * Schedules and fetches the mapping update.
	 *
	 * This function is responsible for scheduling and fetching the mapping
	 * update. It is called when the mappings need to be updated, such as when
	 * the mappings are disabled or when the mappings are reset.
	 *
	 * @param bool $force_fetch Whether to force the fetch.
	 *
	 * @return void
	 */
	public function schedule_and_fetch_mapping_update( $force_fetch = false ) {
		if ( ! Portal_Options::get_proxy_mappings_enabled() ) {
			return;
		}
		if ( $force_fetch || ! wp_next_scheduled( 'leadin_update_proxy_mappings' ) ) {
			$this->fetch_and_cache_mappings();
			wp_schedule_event( time() + self::PROXY_MAPS_CACHE_TTL, self::PROXY_MAPS_CACHE_TTL_FILTER, 'leadin_update_proxy_mappings' );
			ProxyUtils::info_log( 'Scheduled mapping update event.' );
		}
	}

	/**
	 * Proxies the requests.
	 *
	 * This function is responsible for proxying the requests. It retrieves the
	 * HTTP host and request URI from the server, and then uses these values to
	 * determine the proxy path. If a proxy path is found, the request is proxied
	 * to the target URL. If no proxy path is found, a message is logged.
	 *
	 * @return void
	 */
	public function proxy_requests() {
		if ( ! Portal_Options::get_proxy_mappings_enabled() ) {
			ProxyUtils::info_log( 'Proxy is not enabled.' );
			return;
		}

		$http_host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		$proxy_path = $this->get_proxy_path( $http_host, $request_uri );
		if ( is_null( $proxy_path ) ) {
			ProxyUtils::info_log( "No hubspot mapping found for the url: $request_uri" );
			return;
		}

			$target_url = ProxyUtils::get_proxy_base_url() . $proxy_path;

			ProxyUtils::info_log( "Proxying request to: $target_url" );

			$remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

			$args = array(
				'headers' => array(
					'X-HS-Public-Host'              => ProxyUtils::get_destination_domain(),
					'X-Forwarded-For'               => ( ! empty( ProxyUtils::get_client_ip() ) ? ProxyUtils::get_client_ip() . ', ' : '' ) . $remote_addr,
					'X-HubSpot-Trust-Forwarded-For' => 'true',
				),
			);

			$response = wp_remote_get( $target_url, $args );

			if ( is_wp_error( $response ) ) {
				ProxyUtils::error_log( 'Error retrieving content: ' . $response->get_error_message() );
				wp_die( 'Error retrieving content.' );
			}

			$body      = wp_remote_retrieve_body( $response );
			$http_code = wp_remote_retrieve_response_code( $response );

			status_header( $http_code );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $body;
			exit;
	}

	/**
	 * Gets the proxy path.
	 *
	 * This function is responsible for getting the proxy path. It retrieves the
	 * mappings from the cache and then iterates over the mappings to find the
	 * matching domain and path. If a match is found, the new path is returned.
	 *
	 * @param string $current_domain The current domain.
	 * @param string $request_uri    The request URI.
	 *
	 * @return string|null The new path.
	 */
	private function get_proxy_path( $current_domain, $request_uri ) {
		if ( $this->is_predefined_path( $request_uri ) === true ) {
			ProxyUtils::info_log( 'Predefined path: ' . $request_uri );
			return $request_uri;
		}

		$mappings = $this->get_cached_mappings();

		if ( is_array( $mappings ) && ! empty( $mappings ) ) {
			foreach ( $mappings as $mapping ) {
				ProxyUtils::info_log( 'Mapping: ' . json_encode( $mapping ) );
				$wp_path = rtrim( $mapping['wp_path'], '/' );
				$hs_path = rtrim( $mapping['hs_path'], '/' );
				$domain  = $mapping['domain'];

				if ( $current_domain !== $domain ) {
					continue;
				}

				$pattern = $this->get_wp_path_pattern( $wp_path );
				if ( ! is_null( $pattern ) && preg_match( $pattern, rtrim( $request_uri, '/' ), $matches ) ) {
					return $this->get_new_hs_path( $hs_path, $matches, $request_uri );
				}
			}
		}
		return null;
	}

	/**
	 * Gets the WordPress path pattern.
	 *
	 * This function is responsible for getting the WordPress path pattern. It
	 * retrieves the WordPress path and then constructs a pattern based on the
	 * path. If the path contains a wildcard, the pattern is modified to include
	 * the wildcard.
	 *
	 * @param string $wp_path The WordPress path.
	 *
	 * @return string|null The pattern.
	 */
	private function get_wp_path_pattern( $wp_path ) {
		if ( substr( $wp_path, -1 ) === '*' ) {
			if ( substr_count( $wp_path, '*' ) > 1 ) {
				ProxyUtils::error_log( "Invalid mapping: Multiple wildcards in wpPath $wp_path" );
				return null;
			}
			// Remove the trailing '*' and any trailing slash
			// e.g. '/test-path/*' becomes '/test-path'.
			$base = rtrim( substr( $wp_path, 0, -1 ), '/' );

			// Build a regex with two branches:
			// Branch 1: Exactly the base path (followed by a query string or end-of-string)
			// Branch 2: The base path followed by a slash and then at least one character (i.e. extra path data),
			// followed by a query string or end-of-string.
			$pattern = '~^(?:'
				. preg_quote( $base, '~' ) . '(?:\?.*|$)'  // Branch 1.
				. '|'
				. preg_quote( $base . '/', '~' ) . '([^?]+)(?:\?.*|$)'  // Branch 2.
				. ')$~';
			return $pattern;
		}
		// When no wildcard is present, match exactly.
		return '~^' . preg_quote( $wp_path, '~' ) . '$~';
	}

	/**
	 * Gets the new HubSpot path.
	 *
	 * This function is responsible for getting the new HubSpot path. It retrieves
	 * the HubSpot path, matches, and original request URI, and then constructs
	 * a new path based on these values.
	 *
	 * @param string $hs_path            The HubSpot path.
	 * @param array  $matches            The matches.
	 * @param string $original_request_uri The original request URI.
	 *
	 * @return string The new path.
	 */
	private function get_new_hs_path( $hs_path, $matches, $original_request_uri ) {
		// If the HubSpot path contains a wildcard '*' then replace it.
		if ( strpos( $hs_path, '*' ) !== false ) {
			// If there's a captured value, use it; otherwise use an empty string.
			$replacement = ( isset( $matches[1] ) && ! empty( $matches[1] ) )
				? parse_url( $matches[1], PHP_URL_PATH )
				: '';

			// Replace '*' with the captured value (or empty string).
			$new_path = str_replace( '*', $replacement, $hs_path );

			// If the replacement is empty, remove any trailing slash.
			if ( empty( $replacement ) ) {
				$new_path = rtrim( $new_path, '/' );
			}

			// Append the query string from the original URI, if present.
			$query_string = wp_parse_url( $original_request_uri, PHP_URL_QUERY );
			if ( $query_string ) {
				$new_path .= '?' . $query_string;
			}
			return $new_path;
		}
		// If there's no wildcard in the HubSpot path, return it as-is.
		return $hs_path;
	}

	/**
	 * Gets the cached mappings.
	 *
	 * @return array The mappings.
	 */
	private function get_cached_mappings() {
		return get_transient( 'proxy_mappings' );
	}

	/**
	 * Checks if the path is predefined.
	 *
	 * @param string $path url path.
	 *
	 * @return bool Whether the path is predefined.
	 */
	private function is_predefined_path( $path ) {
		foreach ( self::PREDEFINED_PATH_PATTERNS as $pattern ) {
			if ( preg_match( $pattern, $path ) ) {
				return true;
			}
		}
		return false;
	}
}
