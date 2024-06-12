<?php

namespace Leadin\admin\api;

use Leadin\api\Base_Api_Controller;
use Leadin\admin\Connection;
use Leadin\data\Portal_Options;

/**
 * Portal Api, used to clean portal id and domain from the WordPress options.
 */
class Portal_Api_Controller extends Base_Api_Controller {

	/**
	 * Class constructor, register route.
	 */
	public function __construct() {
		self::register_leadin_admin_route(
			'/business-unit',
			\WP_REST_Server::READABLE,
			array( $this, 'get_business_unit_option' )
		);
		self::register_leadin_admin_route(
			'/business-unit',
			\WP_REST_Server::EDITABLE,
			array( $this, 'set_business_unit_option' )
		);
	}

	/**
	 * Get business unit id option.
	 *
	 * @return string Business Unit Id
	 */
	public function get_business_unit_option() {
		return new \WP_REST_Response( Portal_Options::get_business_unit_id(), 200 );
	}

	/**
	 * Set business unit id option.
	 *
	 * @param number $request Request body.
	 *
	 * @return string OK response message.
	 */
	public function set_business_unit_option( $request ) {
		$data             = json_decode( $request->get_body(), true );
		$business_unit_id = $data['businessUnitId'];
		Portal_Options::set_business_unit_id( $business_unit_id );
		return new \WP_REST_Response( 'OK', 200 );
	}

}
