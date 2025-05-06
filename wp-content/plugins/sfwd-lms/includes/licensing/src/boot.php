<?php

namespace LearnDash\Hub;

use LearnDash\Hub\Controller\CheckPluginsRequirements;
use LearnDash\Hub\Controller\Main_Controller;
use LearnDash\Hub\Controller\Projects_Controller;
use LearnDash\Hub\Controller\RemoteBanners;
use LearnDash\Hub\Controller\Settings_Controller;
use LearnDash\Hub\Controller\Signin_Controller;
use LearnDash\Hub\Traits\License;
use LearnDash\Hub\Traits\Permission;

defined( 'ABSPATH' ) || exit;

/**
 * Everything start from here
 *
 * Class Boot
 *
 * @package Hub
 */
class Boot {
	use Permission;
	use License;

	/**
	 * Run all the triggers in init runtime.
	 */
	public function start() {
		if ( $this->is_signed_on() ) {
			if ( ! $this->is_user_allowed() ) {
				return;
			}

			// later we will check the permissions for each modules.
			( new Main_Controller() );
			( new Projects_Controller() )->register_hooks();
			( new Settings_Controller() );
			( new CheckPluginsRequirements() )->register_hooks();
		} else {
			( new Signin_Controller() );
		}

		( new RemoteBanners() )->register_hooks();
	}

	/**
	 * Run the setup scripts when the plugin activating.
	 */
	public function install() {
	}

	/**
	 * Clear all the cache
	 */
	public function deactivate() {
		delete_site_option( 'learndash-hub-projects-api' );
		delete_site_option( 'learndash_hub_update_plugins_cache' );
		delete_site_option( $this->get_license_status_option_name() );
	}
}
