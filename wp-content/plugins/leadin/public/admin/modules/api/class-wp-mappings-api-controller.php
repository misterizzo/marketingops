<?php

namespace Leadin\admin\api;

use Leadin\api\Base_Api_Controller;
use Leadin\data\Portal_Options;

/**
 * Updates the Cache for the Mappings
 */
class WP_Mappings_Api_Controller extends Base_Api_Controller {

	/**
	 * Class constructor, register route.
	 */
	public function __construct() {
		self::register_leadin_admin_route(
			'/wp-mappings-cache-reset',
			\WP_REST_Server::CREATABLE,
			array( $this, 'reset_wp_mappings_cache' )
		);
		self::register_leadin_admin_route(
			'/wp-mappings-proxy-enabled',
			\WP_REST_Server::EDITABLE,
			array( $this, 'set_wp_mappings_proxy_enabled' )
		);
		self::register_leadin_admin_route(
			'/wp-mappings-proxy-enabled',
			\WP_REST_Server::READABLE,
			array( $this, 'get_wp_mappings_proxy_enabled' )
		);
	}

	/**
	 * Resets the WP cache for the Mappings
	 *
	 * @return string OK response message.
	 */
	public function reset_wp_mappings_cache() {
		do_action( 'leadin_reset_wp_mappings_cache' );
		return new \WP_REST_Response( 'OK', 200 );
	}

	/**
	 * Get proxy mappings enabled option.
	 *
	 * @return bool Proxy mappings enabled option value.
	 */
	public function get_wp_mappings_proxy_enabled() {
		return new \WP_REST_Response( Portal_Options::get_proxy_mappings_enabled(), 200 );
	}

	/**
	 * Set the proxy mappings enabled option.
	 *
	 * @param bool $request option value.
	 *
	 * @return string OK response message.
	 */
	public function set_wp_mappings_proxy_enabled( $request ) {
		Portal_Options::set_proxy_mappings_enabled( json_decode( $request->get_body(), true ) );
		return new \WP_REST_Response( 'OK', 200 );
	}

}
