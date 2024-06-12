<?php

namespace WooCommerce\UPS\API;

use WP_Error;

abstract class Abstract_Address_Validator {

	/**
	 * The notice group for address validation notices.
	 *
	 * @var string
	 */
	public static string $notice_group = 'address-validation';

	/**
	 * The address to validate.
	 *
	 * @var array
	 */
	protected $address_to_validate;
	/**
	 * The address validation response.
	 *
	 * @var mixed|WP_Error
	 */
	protected $response;
	/**
	 * Is the response cached?
	 *
	 * @var bool
	 */
	protected $is_response_cached = false;

	/**
	 * Abstract_Address_Validator constructor.
	 *
	 * @param $address_to_validate array The address to validate.
	 */
	public function __construct( $address_to_validate ) {
		$this->set_address_to_validate( $address_to_validate );
		$this->maybe_override_address_to_validate();
	}

	/**
	 * Set the address to validate.
	 *
	 * @param array $address_to_validate
	 *
	 * @return void
	 */
	private function set_address_to_validate( $address_to_validate ) {
		$this->address_to_validate = $address_to_validate;
	}

	/**
	 * Maybe override the address to validate.
	 *
	 * @return void
	 */
	private function maybe_override_address_to_validate() {
		// Set the state and country code for Puerto Rico.
		if ( ! empty( $this->address_to_validate['country'] ) && 'PR' === $this->address_to_validate['country'] ) {
			$this->address_to_validate['state']   = 'PR';
			$this->address_to_validate['country'] = 'US';
		}
	}

	/**
	 * Validate the address.
	 *
	 * @return void
	 */
	public function validate() {
		if ( ! $this->has_required_address_keys() ) {
			$this->response = new WP_Error( 'address_validation_error', __( 'The address you are trying to validate is missing required keys.', 'woocommerce-shipping-ups' ) );

			return;
		}

		$this->build_address_validation_request();

		$cached_response = $this->get_cached_response();

		if ( $this->is_cached_response_valid( $cached_response ) ) {
			$this->is_response_cached = true;
			$this->response           = $cached_response;

			return;
		}

		$response = $this->post_address_validation_request();

		$this->process_response( $response );
	}

	/**
	 * Check whether the address_to_validate array contains all the required keys.
	 *
	 * @return bool
	 */
	protected function has_required_address_keys(): bool {
		$address_to_validate   = $this->get_address_to_validate();
		$required_address_keys = $this->get_required_address_keys();

		foreach ( $required_address_keys as $key ) {
			if ( empty( $address_to_validate[ $key ] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get address to validate.
	 *
	 * @return array
	 */
	protected function get_address_to_validate(): array {
		return $this->address_to_validate;
	}

	/**
	 * Get the required address keys.
	 *
	 * @return array
	 */
	protected function get_required_address_keys(): array {
		return array(
			'address_1',
			'city',
			'state',
			'postcode',
			'country',
		);
	}

	/**
	 * Get the cached response if it exists.
	 *
	 * @return mixed
	 */
	public function get_cached_response() {
		return get_transient( $this->get_cached_response_key() );
	}

	/**
	 * Get the cached response key.
	 *
	 * @return string
	 */
	protected function get_cached_response_key(): string {
		return $this->get_cached_response_key_prefix() . md5( wp_json_encode( $this->get_address_to_validate() ) );
	}

	/**
	 * Get the cached response key prefix.
	 * This will be prepended to the address hash to create the cached response transient key.
	 *
	 * @return string
	 */
	abstract protected function get_cached_response_key_prefix(): string;

	/**
	 * Check if the cached response is valid.
	 *
	 * @param $cached_response
	 *
	 * @return bool
	 */
	protected function is_cached_response_valid( $cached_response ): bool {
		return ! empty( $cached_response ) && ( is_array( $cached_response ) || is_wp_error( $cached_response ) );
	}

	/**
	 * Post the address validation request.
	 *
	 * @return array|WP_Error
	 */
	abstract protected function post_address_validation_request();

	/**
	 * Process the address validation response.
	 *
	 * @param array|WP_Error $response The address validation response.
	 */
	abstract protected function process_response( $response );

	/**
	 * Are we using a cached response?
	 *
	 * @return bool
	 */
	public function is_using_cached_response(): bool {
		return $this->is_response_cached;
	}

	/**
	 * Get the address validation request.
	 */
	abstract public function get_request();

	/**
	 * Check if the address validation request found a valid match.
	 *
	 * @return bool
	 */
	abstract public function found_valid_match(): bool;

	/**
	 * Check if the address validation request returned a valid address classification.
	 * Valid classifications are:
	 * - Residential
	 * - Commercial
	 *
	 * @return bool
	 */
	abstract public function found_valid_classification(): bool;

	/**
	 * Get the address classification from the address validation response if it exists.
	 *
	 * @return string|null
	 */
	abstract public function get_address_classification();

	/**
	 * Get the first suggested address from the address validation response if it exists.
	 *
	 * @return array|false
	 */
	abstract public function get_first_suggested_address();

	/**
	 * Compare the address validation request address to the address validation response address.
	 * If the address validation response address is different from the address validation request
	 * address, then the address is invalid.
	 *
	 * @return bool
	 */
	public function found_exact_match(): bool {
		$address_to_validate = $this->get_address_to_validate();
		$address_to_validate = array_map( 'strtolower', $address_to_validate );
		$address_to_validate = array_map( 'trim', $address_to_validate );

		$address_to_validate = array(
			'address_1' => $address_to_validate['address_1'],
			'address_2' => $address_to_validate['address_2'],
			'city'      => $address_to_validate['city'],
			'state'     => $address_to_validate['state'],
			'postcode'  => $address_to_validate['postcode'],
			'country'   => $address_to_validate['country'],
		);

		$first_suggested_address = $this->get_first_suggested_address();

		if ( ! $first_suggested_address ) {
			return false;
		}

		$first_suggested_address = array_map( 'strtolower', $first_suggested_address );
		$first_suggested_address = array_map( 'trim', $first_suggested_address );

		$first_suggested_address = array(
			'address_1' => $first_suggested_address['address_1'],
			'address_2' => $first_suggested_address['address_2'],
			'city'      => $first_suggested_address['city'],
			'state'     => $first_suggested_address['state'],
			'postcode'  => $first_suggested_address['postcode'],
			'country'   => $first_suggested_address['country'],
		);

		return $address_to_validate === $first_suggested_address;
	}

	/**
	 * Check if address is a po box.
	 */
	public function is_po_box() {
		$address_to_validate = $this->get_address_to_validate();
		$address_1           = $address_to_validate['address_1'];
		$address_2           = isset( $address_to_validate['address_2'] ) ? $address_to_validate['address_2'] : '';

		// A regex pattern that matches "PO Box", "P.O. Box", "POB" or "Post Office Box".
		$po_box_pattern = '/^P?\.?O?\.?\s?B\.?(ox)?\s?\d+|Post\s?Office\s?Box\s?\d+/i';

		if ( preg_match( $po_box_pattern, $address_1 ) || preg_match( $po_box_pattern, $address_2 ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Maybe set the address validation cached response.
	 * Only set if the response code is 200.
	 * Set the cached response for 30 days.
	 *
	 * @param array|WP_Error $response
	 */
	protected function maybe_set_cached_response( $response ) {
		if ( is_wp_error( $response ) ) {
			return;
		}

		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			set_transient( $this->get_cached_response_key(), $this->get_response(), DAY_IN_SECONDS * 30 );
		}
	}

	/**
	 * Get the address validation response.
	 */
	abstract public function get_response();

	/**
	 * Set the address validation response.
	 *
	 * @param array|WP_Error $response
	 */
	abstract protected function set_response( $response );

	/**
	 * Get the endpoint URL.
	 *
	 * @return string
	 */
	abstract protected function get_endpoint();
}
