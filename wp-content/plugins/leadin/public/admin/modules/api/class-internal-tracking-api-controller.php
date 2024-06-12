<?php

namespace Leadin\admin\api;

use Leadin\api\Base_Api_Controller;
use Leadin\data\Portal_Options;

/**
 * Disable Internal Tracking Api. Used to exclude internal users to appear in HS analytics
 */
class Internal_Tracking_Api_Controller extends Base_Api_Controller {

	/**
	 * Class constructor, register route.
	 */
	public function __construct() {
		self::register_leadin_admin_route(
			'/internal-tracking',
			\WP_REST_Server::READABLE,
			array( $this, 'get_internal_tracking_option' )
		);
		self::register_leadin_admin_route(
			'/internal-tracking',
			\WP_REST_Server::EDITABLE,
			array( $this, 'set_internal_tracking_option' )
		);
	}

	/**
	 * Get the disable internal tracking option.
	 *
	 * @return bool Internal tracking option value.
	 */
	public function get_internal_tracking_option() {
		return new \WP_REST_Response( Portal_Options::get_disable_internal_tracking(), 200 );
	}

	/**
	 * Set the disable internal tracking option.
	 *
	 * @param array $request Request body.
	 *
	 * @return string OK response message.
	 */
	public function set_internal_tracking_option( $request ) {
		Portal_Options::set_disable_internal_tracking( json_decode( $request->get_body(), true ) );
		return new \WP_REST_Response( 'OK', 200 );
	}

}
