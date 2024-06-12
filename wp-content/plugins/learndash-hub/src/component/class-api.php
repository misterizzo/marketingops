<?php

declare( strict_types=1 );

namespace LearnDash\Hub\Component;

use LearnDash\Hub\Framework\Base;
use LearnDash\Hub\Traits\Formats;
use LearnDash\Hub\Traits\License;

/**
 * This class handle all stuffs relate to API.
 */
class API extends Base {
	use License;
	use Formats;

	/**
	 * The API base URL.
	 *
	 * @var string
	 */
	public $base = LICENSING_SITE . '/wp-json/' . BASE_REST;

	/**
	 * Trigger a license verification.
	 *
	 * @param string $email       The email that registered with LearnDash.
	 * @param string $license_key The license key provided when registered.
	 *
	 * @return \WP_Error|bool
	 */
	public function verify_license( string $email, string $license_key ) {
		$response = $this->do_api_request(
			'/site/auth',
			'POST',
			array(
				'site_url'    => site_url(),
				'license_key' => $license_key,
				'email'       => $email,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		update_site_option( $this->get_license_key_option_name(), $license_key );
		update_site_option( $this->get_hub_email_option_name(), $email );

		return true;
	}

	/**
	 * Return all the projects, and cache it.
	 */
	public function get_projects() {
		if ( defined( 'LEARNDASH_HUB_FETCH_ERROR' ) ) {
			return new \WP_Error( 'License Error', LEARNDASH_HUB_FETCH_ERROR );
		}
		$cached = get_site_option( 'learndash-hub-projects-api' );
		if ( is_array( $cached )
		     && isset( $cached['last_check'] )
		     && strtotime( '+1 hour', $cached['last_check'] ) < time() ) {
			$cached = array();
		}

		if ( ! is_array( $cached ) || empty( $cached['projects'] ) || is_wp_error( $cached['projects'] ) ) {
			delete_site_option( 'learndash_hub_fetch_projects' );
			delete_site_option( 'learndash_hub_update_plugins_cache' );
			$projects = $this->do_api_request( '/repo/plugins' );
			if ( is_wp_error( $projects ) ) {
				// pageload cache.
				define( 'LEARNDASH_HUB_FETCH_ERROR', $projects->get_error_message() );
			}
			$cached = array(
				'projects'   => $projects,
				'last_check' => time(),
			);

			update_site_option( 'learndash-hub-projects-api', $cached );
		}

		return $cached['projects'];
	}

	/**
	 * Remove the domain from API side
	 *
	 * @return array|\WP_Error
	 */
	public function remove_domain() {
		return $this->do_api_request(
			'/site/domain',
			'DELETE'
		);
	}
}
