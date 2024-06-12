<?php

namespace LearnDash\Hub\Traits;

trait Permission {
	use License;

	/**
	 * Check if the current user have permission for execute an action
	 *
	 * @return bool
	 */
	public function check_permission(): bool {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$cap = is_multisite() ? 'manage_network_options' : 'manage_options';

		return current_user_can( $cap ) && $this->is_user_allowed();
	}

	/**
	 * Check if the current user has permission to access the hub.
	 *
	 * @param int $user_id The user id. If not set, it will use the current user id.
	 *
	 * @return bool
	 */
	public function is_user_allowed( $user_id = 0 ) {
		// we should pass the permission here in the future.

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return false;
		}

		if ( ! $this->is_signed_on() ) {
			return true; // If the site is not signed on, we will allow all the users.
		}

		$access_list = get_site_option( 'learndash_hub_access_list' );
		if ( ! is_array( $access_list ) ) {
			return false;
		}

		if ( isset( $access_list[ $user_id ] ) ) {
			return ! empty( $access_list[ $user_id ] );
		}

		return false;
	}

	/**
	 * A quick hand for verify the nonce.
	 *
	 * @param string $action
	 *
	 * @return bool
	 */
	public function verify_nonce( string $action ): bool {
		if ( ! isset( $_REQUEST['hubnonce'] ) ) {
			return false;
		}

		return wp_verify_nonce( $_REQUEST['hubnonce'], $action );
	}
}
