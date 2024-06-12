<?php

namespace Leadin;

use Leadin\api\Healthcheck_Api_Controller;

use Leadin\admin\api\Internal_Tracking_Api_Controller;
use Leadin\admin\api\Hublet_Api_Controller;
use Leadin\admin\api\User_Meta_Api_Controller;
use Leadin\admin\api\Portal_Api_Controller;

add_action(
	'rest_api_init',
	'Leadin\\leadin_register_routes'
);

/**
 * Register api endpoints.
 */
function leadin_register_routes() {
	new Healthcheck_Api_Controller();
	// Admin routes.
	new Internal_Tracking_Api_Controller();
	new Hublet_Api_Controller();
	new User_Meta_Api_Controller();
	new Portal_Api_Controller();
}
