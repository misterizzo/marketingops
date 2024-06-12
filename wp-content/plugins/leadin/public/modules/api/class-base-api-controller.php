<?php

namespace Leadin\api;

use Leadin\data\Filters;

/**
 * Base class to set a rest endpoint
 */
class Base_Api_Controller {

	/**
	 * Register a route with given parameters
	 *
	 * @param string $path The path for the route to register the service on. Route gets namespaced with leadin/v1.
	 * @param string $methods Comma seperated list of methods allowed for this route.
	 * @param array  $callback Method to execute when this endpoint is requested.
	 */
	public function register_leadin_route( $path, $methods, $callback ) {
		register_rest_route(
			'leadin/v1',
			$path,
			array(
				'methods'             => $methods,
				'callback'            => $callback,
				'permission_callback' => array( $this, 'verify_permissions' ),
			)
		);
	}

	/**
	 * Register an admin route with given parameters
	 *
	 * @param string $path The path for the route to register the service on. Route gets namespaced with leadin/v1.
	 * @param string $methods Comma seperated list of methods allowed for this route.
	 * @param array  $callback Method to execute when this endpoint is requested.
	 */
	public function register_leadin_admin_route( $path, $methods, $callback ) {
		register_rest_route(
			'leadin/v1',
			$path,
			array(
				'methods'             => $methods,
				'callback'            => $callback,
				'permission_callback' => array( $this, 'verify_admin_permissions' ),
			)
		);
	}


	/**
	 * Permissions required by user to execute the request. User permissions are already
	 * verified by nonce 'wp_rest' automatically.
	 *
	 * @return bool true if the user has adequate permissions for this endpoint.
	 */
	public function verify_permissions() {
		return current_user_can( Filters::apply_view_plugin_menu_capability_filters() );
	}

	/**
	 * Permissions required by user to execute the request. User permissions are already
	 * verified by nonce 'wp_rest' automatically.
	 *
	 * @return bool true if the user has adequate admin permissions for this endpoint.
	 */
	public function verify_admin_permissions() {
		return current_user_can( Filters::apply_connect_plugin_capability_filters() );
	}

}
