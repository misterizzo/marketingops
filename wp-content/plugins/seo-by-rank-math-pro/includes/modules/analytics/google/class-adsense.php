<?php
/**
 *  Google AdSense.
 *
 * @since      1.0.34
 * @package    RankMathPro
 * @subpackage RankMathPro\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMathPro\Google;

use RankMath\Google\Api;
use RankMath\Helpers\Security;
use RankMath\Analytics\Workflow\Base;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * AdSense class.
 */
class Adsense {

	/**
	 * Get adsense accounts.
	 *
	 * @return array
	 */
	public static function get_adsense_accounts() {
		$accounts = [];
		$response = Api::get()->http_get( 'https://adsense.googleapis.com/v2/accounts' );
		if (
			! Api::get()->is_success() ||
			isset( $response->error ) ||
			! isset( $response['accounts'] ) ||
			! is_array( $response['accounts'] )
		) {
			return $accounts;
		}

		foreach ( $response['accounts'] as $account ) {
			$accounts[ $account['name'] ] = [
				'name' => $account['displayName'],
			];
		}

		return $accounts;
	}

	/**
	 * Query adsense data from google client api.
	 *
	 * @param array $options Options to pass to the API.
	 *
	 * @return array
	 */
	public static function get_adsense( $options = [] ) {
		$account_id = isset( $options['account_id'] ) ? $options['account_id'] : self::get_adsense_id();
		$start_date = isset( $options['start_date'] ) ? $options['start_date'] : '';
		$end_date   = isset( $options['end_date'] ) ? $options['end_date'] : '';

		if ( ! $account_id || ! $start_date || ! $end_date ) {
			return false;
		}

		$request = Security::add_query_arg_raw(
			[
				'startDate.year'  => gmdate( 'Y', strtotime( $start_date ) ),
				'startDate.month' => gmdate( 'n', strtotime( $start_date ) ),
				'startDate.day'   => gmdate( 'j', strtotime( $start_date ) ),
				'endDate.year'    => gmdate( 'Y', strtotime( $end_date ) ),
				'endDate.month'   => gmdate( 'n', strtotime( $end_date ) ),
				'endDate.day'     => gmdate( 'j', strtotime( $end_date ) ),
				'dimensions'      => 'DATE',
				'currencyCode'    => 'USD',
				'metrics'         => 'ESTIMATED_EARNINGS',
			],
			'https://adsense.googleapis.com/v2/' . $account_id . '/reports:generate'
		);

		$workflow = 'adsense';
		Api::get()->set_workflow( $workflow );

		$response = Api::get()->http_get( $request );

		Api::get()->log_failed_request( $response, $workflow, $start_date, func_get_args() );

		if ( ! Api::get()->is_success() ) {
			return new WP_Error( 'adsense_api_fail', 'Google AdSense API request failed.' );
		}

		if ( ! isset( $response['rows'] ) ) {
			return false;
		}

		return $response['rows'];
	}

	/**
	 * Get adsense id.
	 *
	 * @return string
	 */
	public static function get_adsense_id() {
		static $rank_math_adsense_id;

		if ( is_null( $rank_math_adsense_id ) ) {
			$options              = get_option( 'rank_math_google_analytic_options' );
			$rank_math_adsense_id = ! empty( $options['adsense_id'] ) ? $options['adsense_id'] : false;
		}

		return $rank_math_adsense_id;
	}

	/**
	 * Is adsense connected.
	 *
	 * @return boolean
	 */
	public static function is_adsense_connected() {
		$account = wp_parse_args(
			get_option( 'rank_math_google_analytic_options' ),
			[ 'adsense_id' => '' ]
		);

		return ! empty( $account['adsense_id'] );
	}
}
