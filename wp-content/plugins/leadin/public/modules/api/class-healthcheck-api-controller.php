<?php

namespace Leadin\api;

use Leadin\api\Base_Api_Controller;

/**
 * Healthcheck deployment endpoint
 */
class Healthcheck_Api_Controller extends Base_Api_Controller {

	/**
	 * Class constructor, register route.
	 */
	public function __construct() {
		self::register_leadin_route(
			'/healthcheck',
			\WP_REST_Server::READABLE,
			array( $this, 'get_healthcheck_request' )
		);
	}

	/**
	 * Callback for healtcheck endpoint.
	 * leadin/v1/healthcheck Method:GET.
	 *
	 * @return string OK response message.
	 */
	public function get_healthcheck_request() {
		return new \WP_REST_Response( 'OK', 200 );
	}

}
