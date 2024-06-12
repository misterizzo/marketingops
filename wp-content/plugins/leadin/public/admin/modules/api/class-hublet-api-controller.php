<?php

namespace Leadin\admin\api;

use Leadin\api\Base_Api_Controller;
use Leadin\data\Portal_Options;

/**
 * Hublet Api. Used to fetch portal's hublet and update in case of region migration
 */
class Hublet_Api_Controller extends Base_Api_Controller {

	/**
	 * Class constructor, register route.
	 */
	public function __construct() {
		self::register_leadin_admin_route(
			'/hublet',
			\WP_REST_Server::EDITABLE,
			array( $this, 'update_hublet' )
		);
	}

	/**
	 * Get's correct hublet and updates it in Options
	 *
	 * @param array $request Request body.
	 */
	public function update_hublet( $request ) {
		$data   = json_decode( $request->get_body(), true );
		$hublet = $data['hublet'];

		if ( ! $hublet ) {
			return new \WP_REST_Response( 'Hublet is required', 400 );
		}
		Portal_Options::set_hublet( $hublet );
		return new \WP_REST_Response( $hublet, 200 );
	}

}
