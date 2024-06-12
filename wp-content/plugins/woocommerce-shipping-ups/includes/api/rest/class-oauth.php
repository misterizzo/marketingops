<?php

namespace WooCommerce\UPS\API\REST;

defined( 'ABSPATH' ) || exit;

/**
 * Class UPS_OAuth
 *
 * Handles the OAuth authentication for the UPS REST API.
 *
 * @package WooCommerce\UPS
 */
class OAuth {

	/**
	 * @var
	 */
	private $client_id;
	/**
	 * @var
	 */
	private $client_secret;
	/**
	 * @var string
	 */
	private $endpoint = 'https://onlinetools.ups.com/security/v1/oauth/token';
	/**
	 * @var string
	 */
	private $transient_name = 'woocommerce_ups_oauth_access_token';

	/**
	 * @param string $client_id
	 * @param string $client_secret
	 */
	public function __construct( $client_id, $client_secret ) {
		$this->client_id     = $client_id;
		$this->client_secret = $client_secret;
	}

	/**
	 * Check if we've successfully authenticated.
	 *
	 * @return bool
	 */
	public function is_authenticated() {
		return (bool) $this->get_access_token();
	}

	/**
	 * Get an access token.
	 *
	 * @return string|bool
	 */
	public function get_access_token() {
		// If we don't have a client ID or secret, we can't authenticate.
		if ( empty( $this->client_id ) || empty( $this->client_secret ) ) {
			return false;
		}

		$access_token = get_transient( $this->transient_name );

		if ( false === $access_token ) {

			$response = $this->request_access_token();
			if ( $response && ! empty( $response->access_token ) && ! empty( $response->expires_in ) ) {
				set_transient( $this->transient_name, $response->access_token, $response->expires_in );

				$access_token = $response->access_token;
			}
		}

		return $access_token;
	}

	/**
	 * Request access token from the UPS OAuth API.
	 *
	 * @return object|bool
	 */
	private function request_access_token() {
		$headers = array(
			'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->client_secret ),
			'Content-Type'  => 'application/x-www-form-urlencoded',
		);

		$body = array(
			'grant_type' => 'client_credentials',
		);

		$response = wp_remote_post(
			$this->endpoint,
			array(
				'headers' => $headers,
				'body'    => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();

			$logger = wc_get_logger();
			$logger->error( "UPS_OAuth::request_access_token: The UPS OAuth endpoint returned the following error: $error_message" );

			return false;
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

}
