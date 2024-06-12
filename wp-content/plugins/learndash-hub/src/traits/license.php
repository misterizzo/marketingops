<?php

namespace LearnDash\Hub\Traits;

trait License {
	/**
	 * The option name for license key
	 *
	 * @return string
	 */
	public function get_license_key_option_name() {
		return 'nss_plugin_license_sfwd_lms';
	}

	/**
	 * The option name for license email.
	 * @return string
	 */
	public function get_hub_email_option_name() {
		return 'nss_plugin_license_email_sfwd_lms';
	}

	/**
	 * Get the license key
	 *
	 * @return false|string
	 */
	public function get_license_key() {
		return get_site_option( $this->get_license_key_option_name() );
	}

	/**
	 * Get the register email.
	 *
	 * @return false|string
	 */
	public function get_hub_email() {
		return get_site_option( $this->get_hub_email_option_name() );
	}

	/**
	 * Return the headers that require for API side.
	 *
	 * @return array
	 */
	public function get_auth_headers(): array {
		return array(
			'Learndash-Site-Url'        => network_site_url(),
			'Learndash-Hub-License-Key' => $this->get_license_key(),
			'Learndash-Hub-Email'       => $this->get_hub_email(),
		);
	}

	/**
	 * Check if the current site is signed on.
	 *
	 * @return bool
	 */
	public function is_signed_on(): bool {
		if ( $this->get_hub_email() && $this->get_license_key() ) {
			return true;
		}

		return false;
	}

	/**
	 * Clear signed data.
	 */
	public function clear_auth() {
		delete_site_option( $this->get_license_key_option_name() );
		delete_site_option( $this->get_hub_email_option_name() );
		delete_site_option( 'learndash_hub_secret' );
	}
}
