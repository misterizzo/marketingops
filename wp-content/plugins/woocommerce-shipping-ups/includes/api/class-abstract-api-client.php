<?php

namespace WooCommerce\UPS\API;

use WC_Logger;
use WC_Product;
use WC_Shipping_UPS;
use WP_Error;

abstract class Abstract_API_Client {

	/**
	 * The notice group for rate request notices.
	 *
	 * @var string
	 */
	public static string $notice_group = 'rate-request';

	/**
	 * Endpoints for the API.
	 *
	 * @var array
	 */
	protected static array $endpoints;
	/**
	 * @var WC_Shipping_UPS
	 */
	protected WC_Shipping_UPS $shipping_method;
	/**
	 * @var WC_Logger|null
	 */
	protected $logger;
	/**
	 * @var array
	 */
	protected array $package_requests;
	/**
	 * @var array
	 */
	protected array $package;
	/**
	 * The address validator.
	 *
	 * @var Abstract_Address_Validator
	 */
	protected Abstract_Address_Validator $address_validator;

	/**
	 * @param WC_Shipping_UPS $ups_shipping_method The UPS shipping method object.
	 */
	public function __construct( $ups_shipping_method ) {
		$this->shipping_method = $ups_shipping_method;

		$this->logger = wc_get_logger();
	}

	/**
	 * Set the package requests to be used for the rate request.
	 *
	 * @param $package_requests
	 *
	 * @return void
	 */
	public function set_package_requests( $package_requests ) {
		$this->package_requests = $package_requests;
	}

	/**
	 * Set the package to be used for the rate request.
	 *
	 * @param $package
	 *
	 * @return void
	 */
	public function set_package( $package ) {
		$this->package = $package;
	}

	/**
	 * Determine if we can use Simple Rate for this package.
	 *
	 * @param $total_packages_count
	 *
	 * @return bool
	 */
	public function is_package_eligible_for_simple_rate( $total_packages_count ) {
		return $this->shipping_method->is_domestic_us_shipping() && ( 1 === $total_packages_count ) && $this->shipping_method->is_simple_rate_enabled() && $this->shipping_method->simple_rate_services_enabled();
	}

	/**
	 * Convert the product dimensions from the store dimensions unit to the shipping method's dimension unit.
	 * Format the dimensions for sending in the rate request.
	 * Sort the dimensions in ascending order.
	 *
	 * @param WC_Product $product The product.
	 *
	 * @return array
	 */
	public function get_processed_product_dimensions( WC_Product $product ): array {
		$dimensions = array(
			$this->shipping_method->get_formatted_measurement( $this->shipping_method->get_converted_dimension( $product->get_length() ) ),
			$this->shipping_method->get_formatted_measurement( $this->shipping_method->get_converted_dimension( $product->get_width() ) ),
			$this->shipping_method->get_formatted_measurement( $this->shipping_method->get_converted_dimension( $product->get_height() ) ),
		);

		sort( $dimensions );

		return array(
			'length' => $dimensions[2],
			'width'  => $dimensions[1],
			'height' => $dimensions[0],
		);
	}

	/**
	 * Build a package element for a Box Packer packed box which will be added to the rate request.
	 *
	 * @param $packed_box
	 * @param $packed_boxes_count
	 */
	abstract public function build_packed_box_package_for_rate_request( $packed_box, $packed_boxes_count );

	/**
	 * Build a package element for an individually packed product which will be added to the rate request.
	 *
	 * @param $cart_item
	 */
	abstract public function build_individually_packed_package_for_rate_request( $cart_item );

	/**
	 * POST all rate requests and return the parsed results.
	 *
	 * @return false|array
	 */
	abstract public function get_rates();

	/**
	 * Handle destination address validation.
	 *
	 * @param array $destination_address
	 *
	 * @return void
	 */
	abstract public function validate_destination_address( $destination_address );

	/**
	 * @param Abstract_Address_Validator $address_validator
	 *
	 * @return void
	 */
	public function set_is_valid_destination_address( Abstract_Address_Validator $address_validator ) {
		if ( ! $address_validator->found_valid_match() ) {
			$this->shipping_method->set_is_valid_destination_address( false );

			return;
		}

		if ( ! $address_validator->found_exact_match() ) {
			$this->shipping_method->set_is_valid_destination_address( false );

			return;
		}

		/**
		 * If the address is a PO Box, we need to flag the destination address as invalid.
		 *
		 * According to UPS:
		 * We only ship to a valid street address, and do not deliver to P.O. boxes.
		 * If a shipper uses a P.O. Box address, the recipient’s phone number must be included on the label.
		 *
		 * @link https://www.ups.com/us/en/support/shipping-support.page
		 */
		if ( $address_validator->is_po_box() ) {
			$this->shipping_method->set_is_valid_destination_address( false );

			return;
		}

		$this->shipping_method->set_is_valid_destination_address( true );
	}

	/**
	 * Get the rate cost for the service.
	 *
	 * @param $shipment
	 * @param $code
	 *
	 * @return float
	 */
	public function get_rate_cost( $shipment, $code ) {
		if ( $this->shipping_method->is_negotiated_rates_enabled() && isset( $shipment->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue ) ) {
			$rate_cost = (float) $shipment->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue;
		} else {
			$rate_cost = (float) $shipment->TotalCharges->MonetaryValue;
		}

		// Cost adjustment %.
		if ( ! empty( $this->shipping_method->get_custom_services()[ $code ]['adjustment_percent'] ) ) {
			$rate_cost = $rate_cost + ( $rate_cost * ( floatval( $this->shipping_method->get_custom_services()[ $code ]['adjustment_percent'] ) / 100 ) );
		}
		// Cost adjustment.
		if ( ! empty( $this->shipping_method->get_custom_services()[ $code ]['adjustment'] ) ) {
			$rate_cost = $rate_cost + floatval( $this->shipping_method->get_custom_services()[ $code ]['adjustment'] );
		}

		return $rate_cost;
	}

	/**
	 * Get the rate name for the service.
	 *
	 * @param $code
	 *
	 * @return mixed|string
	 */
	public function get_rate_name( $code ) {
		$service_name = $this->shipping_method->get_services()[ $code ];

		$rate_name = $service_name . ' (' . $this->shipping_method->title . ')';

		// Name adjustment.
		if ( ! empty( $this->shipping_method->get_custom_services()[ $code ]['name'] ) ) {
			$rate_name = $this->shipping_method->get_custom_services()[ $code ]['name'];
		}

		return $rate_name;
	}

	/**
	 * Gets the default position of a service code within the services array.
	 *
	 * @param string $code The service code to find.
	 *
	 * @return int The position of the service code (0-indexed), or 999 if not found.
	 */
	public function get_default_order( $code ) {
		$services = $this->shipping_method->get_services();
		$position = array_search( $code, array_keys( $services ), true );

		return false !== $position ? $position : 999;
	}

	/**
	 * Gets the sort order for a service, prioritizing custom settings if available.
	 *
	 * @param string $code The service code.
	 *
	 * @return int The sort order.
	 */
	public function get_sort_order( string $code ): int {
		$custom_services = $this->shipping_method->get_custom_services();

		return empty( $custom_services[ $code ]['order'] ) ? $this->get_default_order( $code ) : (int) $custom_services[ $code ]['order'];
	}

	/**
	 * Make a post request to the rate endpoint.
	 *
	 * @param $request
	 * @param $endpoint
	 *
	 * @return false|void|WP_Error
	 */
	abstract protected function post_rate_request( $request, $endpoint );

	/**
	 * @param $request_object
	 *
	 * @return bool
	 */
	protected function is_valid_package_request_object( $request_object ) {
		return empty( $request_object->Dimensions->Length ) || empty( $request_object->Dimensions->Width ) || empty( $request_object->Dimensions->Height ) || empty( $request_object->Dimensions->UnitOfMeasurement->Code ) || empty( $request_object->PackageWeight->Weight ) || empty( $request_object->PackageWeight->UnitOfMeasurement->Code );
	}

	/**
	 * Extract the packed box dimensions and weights if available and return in an array
	 *
	 * @return array|false
	 */
	abstract protected function maybe_get_packed_box_details();

	/**
	 * Maybe add the packed box dimensions and weights to the metadata.
	 *
	 * @param array  $meta_data
	 * @param object $request_object
	 * @param int    $index
	 *
	 * @return array
	 */
	protected function maybe_get_packed_box_details_meta( $meta_data, $request_object, $index ) {

		// Make sure we have length, width, height, weight, and measurement units, or don't add to the metadata.
		if ( $this->is_valid_package_request_object( $request_object ) ) {
			return array();
		}

		$package_number = sprintf( __( 'Package %1$s', 'woocommerce-shipping-ups' ), $index );

		// Combine length, width, height into a string.
		$dimensions = implode(
			' x ',
			array(
				$request_object->Dimensions->Length,
				$request_object->Dimensions->Width,
				$request_object->Dimensions->Height,
			)
		);

		$meta_data[ $package_number ] = sprintf( __( '%1$s (%2$s) %3$s%4$s', 'woocommerce-shipping-ups' ), $dimensions, strtolower( $request_object->Dimensions->UnitOfMeasurement->Code ), $request_object->PackageWeight->Weight, strtolower( $request_object->PackageWeight->UnitOfMeasurement->Code ) );

		return $meta_data;
	}

	/**
	 * Maybe get the rate.
	 *
	 * @param $response
	 * @param $code
	 * @param $shipment
	 *
	 * @return array|false
	 */
	protected function maybe_get_rate( $response, $code, $shipment ) {
		$rate             = array();
		$rate['id']       = $this->shipping_method->get_rate_id( $code );
		$rate['currency'] = (string) $shipment->TotalCharges->CurrencyCode;

		// Get the rate name.
		$rate['name'] = $this->get_rate_name( $code );

		// Ensure the store currency matches the rate currency.
		if ( ! $this->is_store_currency_equal_to_rate_currency( $response, $rate['name'], $rate['currency'] ) ) {
			return false;
		}

		// Get the rate cost.
		$rate['cost'] = $this->get_rate_cost( $shipment, $code );

		// Get the sort order.
		$rate['sort'] = $this->get_sort_order( $code );

		return $rate;
	}

	/**
	 *  Does the store currency match the currency of the rate response?
	 *
	 * @param object $rate_response
	 * @param string $rate_name
	 *
	 * @return bool
	 */
	protected function is_store_currency_equal_to_rate_currency( $rate_response, $rate_name, $currency ) {
		$store_currency = get_woocommerce_currency();

		/**
		 * Allow 3rd parties to skip the check against the store currency.
		 * This check is irrelevant in multi-currency scenarios.
		 */
		if ( apply_filters( 'woocommerce_shipping_ups_check_store_currency', true, $currency, $rate_response, $this ) && ( $store_currency !== $currency ) ) {
			/* translators: 1) UPS service name 2) currency for the rate 3) store's currency */
			$this->shipping_method->debug( sprintf( __( '[UPS] Rate for %1$s is in %2$s but store currency is %3$s.', 'woocommerce-shipping-ups' ), $rate_name, $currency, $store_currency ) );

			return false;
		}

		return true;
	}

	/**
	 * Set the address validator.
	 *
	 * @param Abstract_Address_Validator $address_validator The address validator.
	 */
	public function set_address_validator( Abstract_Address_Validator $address_validator ) {
		$this->address_validator = $address_validator;
	}

	/**
	 * Get the address validator.
	 *
	 * @return Abstract_Address_Validator
	 */
	public function get_address_validator(): Abstract_Address_Validator {
		return $this->address_validator;
	}
}
