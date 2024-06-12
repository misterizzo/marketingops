<?php

namespace WooCommerce\UPS\API\REST;

use WooCommerce\UPS\API\Abstract_Address_Validator;
use WP_Error;

defined( 'ABSPATH' ) || exit;

require_once WC_SHIPPING_UPS_PLUGIN_DIR . '/includes/api/class-abstract-address-validator.php';

/**
 * Class Address_Validator
 */
class Address_Validator extends Abstract_Address_Validator {

	/**
	 * The UPS access token.
	 */
	private string $access_token;

	/**
	 * The address validation request.
	 *
	 * @var array
	 */
	private array $request = array();

	/**
	 * Address_Validator constructor.
	 *
	 * @param $address_to_validate array The address to validate.
	 * @param $access_token        string A UPS access token.
	 */
	public function __construct( $address_to_validate, string $access_token ) {
		parent::__construct( $address_to_validate );

		$this->access_token = $access_token;
	}

	/**
	 * Build the address validation request.
	 *
	 * @return void
	 */
	public function build_address_validation_request() {
		$address = $this->get_address_to_validate();

		$address_line = array( $address['address_1'] );

		if ( ! empty( $address['address_2'] ) ) {
			$address_line[] = $address['address_2'];
		}

		$this->request = array(
			'XAVRequest' => array(
				'AddressKeyFormat' => array(
					'AddressLine'        => $address_line,
					'PoliticalDivision2' => $address['city'],
					'PoliticalDivision1' => $address['state'],
					'PostcodePrimaryLow' => $address['postcode'],
					'CountryCode'        => $address['country'],
				),
			),
		);
	}

	/**
	 * Get the address validation request.
	 */
	public function get_request(): array {
		return $this->request;
	}

	/**
	 * Get the address validation response.
	 */
	public function get_response() {
		return $this->response;
	}

	/**
	 * Check if the address validation request found a valid match.
	 *
	 * @return bool
	 */
	public function found_valid_match(): bool {
		return is_array( $this->response ) && isset( $this->response['XAVResponse']['ValidAddressIndicator'] );
	}

	/**
	 * Check if the address validation request returned a valid address classification.
	 * Valid classifications are:
	 * - Residential
	 * - Commercial
	 *
	 * @return bool
	 */
	public function found_valid_classification(): bool {
		$classification = $this->get_address_classification();
		if ( empty( $classification ) ) {
			return false;
		}

		return in_array( $classification, array( 'Residential', 'Commercial' ), true );
	}

	/**
	 * Get the address classification from the address validation response if it exists.
	 *
	 * @return string|null
	 */
	public function get_address_classification(): ?string {
		if ( ! is_array( $this->response ) || ! isset( $this->response['XAVResponse']['AddressClassification']['Description'] ) ) {
			return null;
		}

		return $this->response['XAVResponse']['AddressClassification']['Description'];
	}

	/**
	 * @inheritDoc
	 */
	protected function get_cached_response_key_prefix(): string {
		return 'ups_rest_av_';
	}

	/**
	 * @inheritDoc
	 */
	protected function process_response( $response ) {
		$this->set_response( $response );
		$this->maybe_set_cached_response( $response );
	}

	protected function set_response( $response ) {
		if ( is_wp_error( $response ) ) {
			$this->response = $response;

			return;
		}

		$this->response = json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Post the address validation request.
	 *
	 * @return array|WP_Error
	 */
	protected function post_address_validation_request() {
		// Build the endpoint.
		$address_validation_endpoint = add_query_arg(
			array( 'maximumcandidatelistsize' => 1 ),
			$this->get_endpoint()
		);

		// Create the request headers.
		$headers = array(
			'Authorization' => 'Bearer ' . $this->access_token,
			'Content-Type'  => 'application/json',
		);

		/**
		 * Filter the address validation request body before sending it to the UPS API.
		 *
		 * @param array  $request The request body.
		 * @param string $class   The class name.
		 */
		$body = apply_filters( 'woocommerce_shipping_ups_address_validation_request', $this->request, get_class() );

		return wp_remote_post(
			$address_validation_endpoint,
			array(
				'headers' => $headers,
				'body'    => json_encode( $body ),
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	protected function get_endpoint(): string {
		return 'https://onlinetools.ups.com/api/addressvalidation/v1/3';
	}

	/**
	 * @inheritDoc
	 */
	public function get_first_suggested_address() {
		if ( ! $this->found_valid_match() ) {
			return false;
		}

		if ( ! isset( $this->response['XAVResponse']['Candidate']['AddressKeyFormat'] ) ) {
			return false;
		}

		$address = $this->response['XAVResponse']['Candidate']['AddressKeyFormat'];

		$address_line = is_array( $address['AddressLine'] ) ? $address['AddressLine'] : array( $address['AddressLine'] );

		return array(
			'address_1' => $address_line[0],
			'address_2' => $address_line[1] ?? '',
			'city'      => $address['PoliticalDivision2'],
			'state'     => $address['PoliticalDivision1'],
			'postcode'  => $address['PostcodePrimaryLow'],
			'country'   => $address['CountryCode'],
		);
	}
}
