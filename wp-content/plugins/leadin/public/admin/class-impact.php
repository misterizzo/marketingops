<?php

namespace Leadin\admin;

use Leadin\data\Filters;

const IR_CLICK_ID = 'irclickid';
const MPID        = 'mpid';

/**
 * Class containing the logic to get Impact affiliate information when necessary
 */
class Impact {
	/**
	 * Apply leadin_impact_code filter.
	 */
	public static function get_affiliate_link() {
		return Filters::apply_impact_code_filters();
	}

	/**
	 * Get impact properties from query parameters.
	 */
	public static function get_params() {
		$params = array();

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['leadin_irclickid'] ) ) {
			$params[ IR_CLICK_ID ] = sanitize_text_field( \wp_unslash( $_GET['leadin_irclickid'] ) );
		}

		if ( isset( $_GET['leadin_mpid'] ) ) {
			$params[ MPID ] = sanitize_text_field( \wp_unslash( $_GET['leadin_mpid'] ) );
		}
		// phpcs:enable

		return $params;
	}

	/**
	 * Return true if the function `get_params` returns both irclickid and mpid.
	 */
	public static function has_params() {
		return 2 === \count( self::get_params() );
	}
}
