<?php

namespace WooCommerce\UPS\API\REST;

defined( 'ABSPATH' ) || exit;

require_once WC_SHIPPING_UPS_PLUGIN_DIR . '/includes/api/class-abstract-api-client.php';
require_once WC_SHIPPING_UPS_PLUGIN_DIR . '/includes/api/rest/class-address-validator.php';

use WC_Product;
use WooCommerce\UPS\API\Abstract_API_Client;
use WooCommerce\UPS\Notifier;
use WP_Error;

class API_Client extends Abstract_API_Client {

	/**
	 * Endpoint for the UPS Rating API.
	 *
	 * @var array
	 */
	protected static array $endpoints = array(
		'shop' => 'https://onlinetools.ups.com/api/rating/v1/Shop',
		'rate' => 'https://onlinetools.ups.com/api/rating/v1/Rate',
	);

	/**
	 * @param $request
	 * @param $endpoint
	 *
	 * @inheritDoc
	 */
	protected function post_rate_request( $request, $endpoint ) {
		$access_token = $this->shipping_method->get_ups_oauth()->get_access_token();

		// If we don't have an access token, return an error.
		if ( ! $access_token ) {
			return new WP_Error( 'post_rate_request_error', __( 'UPS OAuth authentication failed.', 'woocommerce-shipping-ups' ) );
		}

		// Create the request headers.
		$headers = array(
			'Authorization'  => 'Bearer ' . $access_token,
			'Content-Type'   => 'application/json',
			'transId'        => 'WooCommerce UPS plugin',
			'transactionSrc' => 'WooCommerce UPS plugin',
			'additionalinfo' => 'timeintransit',
		);

		/**
		 * Filter the request body before sending it to the UPS API.
		 *
		 * @param array  $request          The request body.
		 * @param array  $package_requests The package requests.
		 * @param array  $package          The package.
		 * @param string $class            The class name.
		 * @param string $endpoint         The request endpoint.
		 */
		$body = apply_filters( 'woocommerce_shipping_ups_request', $request, $this->package_requests, $this->package, get_class(), $endpoint );

		return wp_remote_post(
			$endpoint,
			array(
				'headers' => $headers,
				'body'    => json_encode( $body ),
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function build_packed_box_package_for_rate_request( $packed_box, $packed_boxes_count ): array {
		// The dimensions are currently in the UPS instance's dimension unit.
		$dimensions = array(
			'length' => $packed_box->length,
			'width'  => $packed_box->width,
			'height' => $packed_box->height,
		);

		// The weight is currently in the UPS instance's weight unit.
		$weight = $packed_box->weight;

		// Create the request array.
		$request = array();

		// Add the package packaging type.
		$this->add_package_packaging_type_element( $request );

		// Add the package dimensions.
		$this->add_package_dimensions_element( $request, $dimensions['length'], $dimensions['width'], $dimensions['height'] );

		// Add the package weight.
		$this->add_package_weight_element( $request, $weight );

		// Maybe add the package SimpleRate element.
		$use_simple_rate = $this->maybe_add_package_simple_rate_element(
			$request,
			$dimensions['length'],
			$dimensions['width'],
			$dimensions['height'],
			$weight,
			$packed_boxes_count
		);

		/**
		 * Change package code to 01 UPS Letter to get lower rates if the items fit into the
		 * UPS Letter envelope and if Simple Rate isn't being used
		 */
		if ( 'UPS Letter' === $packed_box->id && ! $use_simple_rate ) {
			$request['PackagingType']['Code']        = '01';
			$request['PackagingType']['Description'] = 'UPS Letter';
		}

		// Package service options.
		if ( $this->shipping_method->has_package_service_options( $this->package['destination']['country'] ) ) {

			// Maybe add the package insured value element.
			$this->maybe_add_package_insured_value_element( $request, $packed_box->value );

			// Maybe add the package delivery confirmation element.
			$this->maybe_add_package_delivery_confirmation_element( $request, $this->package['destination']['country'] );
		}

		return $request;
	}

	/**
	 * @inheritDoc
	 */
	public function build_individually_packed_package_for_rate_request( $cart_item ): array {

		/**
		 * @var WC_Product $product Product instance.
		 */
		$product = $cart_item['data'];

		// Get formatted, converted, sorted product dimensions. Dimensions are in the UPS instance's dimension unit.
		$dimensions = $this->get_processed_product_dimensions( $product );

		$product_has_dimensions = ! empty( $dimensions['length'] ) && ! empty( $dimensions['width'] ) && ! empty( $dimensions['height'] );

		// Convert and format the weight. Weight is in the UPS instance's weight unit.
		$weight = $this->shipping_method->get_formatted_measurement( $this->shipping_method->get_converted_weight( $product->get_weight() ) );

		// Create the request array.
		$request = array();

		// Add the package packaging type.
		$this->add_package_packaging_type_element( $request );

		// Maybe add the package dimensions.
		if ( $product_has_dimensions ) {
			$this->add_package_dimensions_element( $request, $dimensions['length'], $dimensions['width'], $dimensions['height'] );
		}

		// Add the package weight.
		$this->add_package_weight_element( $request, $weight );

		// Maybe add the package SimpleRate element.
		if ( $product_has_dimensions ) {
			$this->maybe_add_package_simple_rate_element( $request, $dimensions['length'], $dimensions['width'], $dimensions['height'], $weight, $cart_item['quantity'] );
		}

		// Package Service Options.
		if ( $this->shipping_method->has_package_service_options( $this->package['destination']['country'] ) ) {

			// Maybe add package insured value.
			$this->maybe_add_package_insured_value_element( $request, $product->get_price() );

			// Maybe add package delivery confirmation.
			$this->maybe_add_package_delivery_confirmation_element( $request, $this->package['destination']['country'] );
		}

		return $request;
	}

	/**
	 * @inheritDoc
	 */
	public function get_rates() {
		$notice_group = self::$notice_group;

		Notifier::clear_notices( $notice_group );

		$rate_requests = $this->get_rate_requests_array();

		// If SurePost services are enabled, we need to post separate requests for them.
		$enabled_surepost_service_codes = $this->shipping_method->get_enabled_surepost_service_codes();
		if ( ! empty( $enabled_surepost_service_codes ) ) {
			foreach ( $enabled_surepost_service_codes as $service_code ) {
				$rate_requests = array_merge( $rate_requests, $this->get_rate_requests_array( $service_code ) );
			}
		}

		// If there are no rate requests, return early.
		if ( empty( $rate_requests ) ) {
			return array();
		}

		$rates = array();
		foreach ( $rate_requests as $request ) {
			/**
			 * Try to get a cached response before sending a new request.
			 */
			$transient       = 'ups_quote_' . md5( serialize( $request ) );
			$cached_response = get_transient( $transient );

			if ( false === $cached_response ) {
				$response = $this->post_rate_request( $request['body'], $request['endpoint'] );

				if ( is_wp_error( $response ) ) {
					$this->shipping_method->debug( __( 'Cannot retrieve rate: ', 'woocommerce-shipping-ups' ) . $response->get_error_message(), 'error', array(), $notice_group );

					// If there is an error, continue to the next rate request.
					continue;
				}

				set_transient( $transient, $response['body'], DAY_IN_SECONDS * 30 );
				$response = $response['body'];
			} else {
				$response = $cached_response;
			}

			$request_type = ! empty( $request['body']['RateRequest']['Shipment']['Service']['Code'] ) ? 'Service Code: ' . $request['body']['RateRequest']['Shipment']['Service']['Code'] : 'Shop';
			$this->shipping_method->debug(
				'UPS REQUEST: ' . $request_type,
				'notice',
				$request['body'],
				$notice_group
			);
			$this->shipping_method->debug(
				'UPS RESPONSE: ' . $request_type,
				'notice',
				json_decode( $response, true ),
				$notice_group
			);

			// Parse the response.
			$response = json_decode( $response );

			// The response code must be equal to 1, otherwise continue to the next rate request.
			if ( empty( $response->RateResponse->Response->ResponseStatus->Code ) || 1 !== (int) $response->RateResponse->Response->ResponseStatus->Code ) {
				continue;
			}

			// If there are no rated shipments, continue to the next rate request.
			if ( empty( $response->RateResponse->RatedShipment ) ) {
				continue;
			}

			$rate_response = $response->RateResponse;
			$ups_services  = (array) $rate_response->RatedShipment;

			/**
			 * For "Rate" requests, the response is an object, not an array.
			 * We need to convert it to an array to make it consistent with the "Shop" requests.
			 */
			if ( ! isset( $ups_services[0] ) ) {
				$ups_services = array( $ups_services );
			}

			foreach ( $ups_services as $service ) {
				$shipment = (object) $service;
				$code     = $shipment->Service->Code;

				// Check if the service is enabled.
				$enabled_service_codes = $this->shipping_method->get_enabled_service_codes();
				if ( empty( $enabled_service_codes ) || ! in_array( $code, $enabled_service_codes ) ) {
					continue;
				}

				$rate_id  = $this->shipping_method->get_rate_id( $code );
				$currency = (string) $shipment->TotalCharges->CurrencyCode;

				// Get the rate name.
				$rate_name = $this->get_rate_name( $code );

				// Ensure the store currency matches the rate currency.
				if ( ! $this->is_store_currency_equal_to_rate_currency( $rate_response, $rate_name, $currency ) ) {
					continue;
				}

				// Get the rate cost.
				$rate_cost = $this->get_rate_cost( $shipment, $code );

				// Get the sort order.
				$sort = $this->get_sort_order( $code );

				// If the rate already exists, we can just add the cost to it.
				if ( empty( $rates[ $rate_id ] ) ) {
					/**
					 * Allow 3rd parties to process the rates returned by UPS. This will
					 * allow to convert them to the active currency. The original currency
					 * from the rates, the XML and the shipping method instance are passed
					 * as well, so that 3rd parties can fetch any additional information
					 * they might require
					 */
					$rates[ $rate_id ] = apply_filters(
						'woocommerce_shipping_ups_rate',
						array(
							'id'        => $rate_id,
							'label'     => $rate_name,
							'cost'      => $rate_cost,
							'sort'      => $sort,
							'meta_data' => $this->maybe_get_packed_box_details(),
						),
						$currency,
						$shipment,
						$this
					);
				} else {
					$rates[ $rate_id ]['cost'] += $rate_cost;
				}
			}
		}

		return $rates;
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
		if ( $this->shipping_method->is_negotiated_rates_enabled() && isset( $shipment->NegotiatedRateCharges->TotalCharge->MonetaryValue ) ) {
			$rate_cost = (float) $shipment->NegotiatedRateCharges->TotalCharge->MonetaryValue;
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
	 * @inheritDoc
	 */
	protected function maybe_get_packed_box_details() {
		$meta_data = array();
		foreach ( $this->package_requests as $index => $request ) {

			$request_object = json_decode( json_encode( $request ), false );

			$meta_data = $this->maybe_get_packed_box_details_meta( $meta_data, $request_object, ( $index + 1 ) );
		}

		return ! empty( $meta_data ) ? $meta_data : false;
	}

	/**
	 * Add PackagingType element to package.
	 *
	 * @param array $request The request array.
	 *
	 * @return void
	 */
	private function add_package_packaging_type_element( array &$request ) {
		$request['PackagingType'] = array(
			'Code'        => '02',
			'Description' => 'Package/customer supplied',
		);
	}

	/**
	 * Add PackageDimensions element to package.
	 *
	 * @param array $request The request array.
	 * @param mixed $length  The length in the UPS instance's dimension unit.
	 * @param mixed $width   The width in the UPS instance's dimension unit.
	 * @param mixed $height  The height in the UPS instance's dimension unit.
	 *
	 * @return void
	 */
	private function add_package_dimensions_element( array &$request, $length, $width, $height ) {
		$request['Dimensions'] = array(
			'UnitOfMeasurement' => array(
				'Code' => $this->shipping_method->get_dimension_unit(),
			),
			'Length'            => (string) round( $length ),
			'Width'             => (string) round( $width ),
			'Height'            => (string) round( $height ),
		);
	}

	/**
	 * Add PackageWeight element to package.
	 *
	 * @param $request
	 * @param $weight
	 *
	 * @return void
	 */
	private function add_package_weight_element( &$request, $weight ) {
		$request['PackageWeight'] = array(
			'UnitOfMeasurement' => array(
				'Code' => $this->shipping_method->get_weight_unit(),
			),
			'Weight'            => (string) $weight,
		);
	}

	/**
	 * Maybe add SimpleRate element to package.
	 *
	 * Dimensions will be converted from WooCommerce's set units to inches.
	 * Weight will be converted from WooCommerce's set units to pounds.
	 *
	 * @param array $request              The request array.
	 * @param mixed $length               The length in the UPS instance's dimension unit.
	 * @param mixed $width                The width in the UPS instance's dimension unit.
	 * @param mixed $height               The height in the UPS instance's dimension unit.
	 * @param mixed $weight               The weight in the UPS instance's weight unit.
	 * @param int   $total_packages_count The total number of packages.
	 *
	 * @return bool
	 */
	private function maybe_add_package_simple_rate_element( array &$request, $length, $width, $height, $weight, int $total_packages_count ): bool {

		// UPS Simple Rate is only available for domestic US shipments.
		if ( ! $this->is_package_eligible_for_simple_rate( $total_packages_count ) ) {
			return false;
		}

		// Make sure the dimensions/weight are not empty.
		if ( empty( $length ) || empty( $width ) || empty( $height ) || empty( $weight ) ) {
			return false;
		}

		$length = round( $this->shipping_method->get_converted_dimension( $length, $this->shipping_method->get_dimension_unit(), 'in' ) );
		$width  = round( $this->shipping_method->get_converted_dimension( $width, $this->shipping_method->get_dimension_unit(), 'in' ) );
		$height = round( $this->shipping_method->get_converted_dimension( $height, $this->shipping_method->get_dimension_unit(), 'in' ) );
		$weight = $this->shipping_method->get_converted_weight( $weight, $this->shipping_method->get_weight_unit(), 'lbs' );

		$code = $this->shipping_method->maybe_get_simple_rate_code( $length, $width, $height, $weight );

		if ( empty( $code ) ) {
			return false;
		}

		$request['SimpleRate'] = array(
			'Description' => 'UPS Simple Rate',
			'Code'        => $code,
		);

		return true;
	}

	/**
	 * Maybe add InsuredValue element to package.
	 *
	 * @param $request
	 * @param $value
	 *
	 * @return void
	 */
	private function maybe_add_package_insured_value_element( &$request, $value ) {
		if ( $this->shipping_method->is_insured_value_enabled() ) {
			$request['PackageServiceOptions']['DeclaredValue'] = array(
				'CurrencyCode'  => get_woocommerce_currency(),
				'MonetaryValue' => (string) $value,
			);
		}
	}

	/**
	 * Maybe add DeliveryConfirmation element to package.
	 *
	 * @param $request
	 * @param $country
	 *
	 * @return void
	 */
	private function maybe_add_package_delivery_confirmation_element( &$request, $country ) {
		if ( $this->shipping_method->needs_delivery_confirmation() && 'package' === $this->shipping_method->delivery_confirmation_level( $country ) ) {
			$request['PackageServiceOptions']['DeliveryConfirmation'] = array(
				'DCISType' => ( 'regular' === $this->shipping_method->get_signature() ? '2' : '3' ),
			);
		}
	}

	/**
	 * Retrieves an array of rate requests.
	 *
	 * @param string|null $service_code A specific service code to get rates for.
	 *                                  Changes the request option and endpoint to 'Rate'
	 *                                  and adds the service code to the request.
	 *
	 * @return array
	 */
	private function get_rate_requests_array( string $service_code = null ): array {
		if ( empty( $this->package_requests ) || ! is_array( $this->package_requests ) ) {
			return array();
		}

		if ( empty( $service_code ) ) {
			$requests = $this->get_grouped_requests();
		} elseif ( in_array( $service_code, $this->shipping_method->get_surepost_service_codes(), true ) ) {
			$requests = $this->get_surepost_requests( $service_code );
		} else {
			$requests = $this->get_grouped_requests( $service_code );
		}

		return $requests;
	}

	/**
	 * Build the rate request.
	 *
	 * @param array $package_requests The package requests.
	 * @param null  $service_code     A specific service code to get rates for.
	 *
	 * @return array|array[]
	 */
	private function build_rate_request( array $package_requests, $service_code = null ): array {
		$request_option = ! empty( $service_code ) ? 'Rate' : 'Shop';

		$request = array(
			'RateRequest' => array(
				'Request'  => array(
					'RequestOption'        => $request_option,
					'TransactionReference' => array(
						'CustomerContext' => 'Rating and Service',
					),
				),
				'Shipment' => array(
					'Shipper'     => array(
						'ShipperNumber' => $this->shipping_method->get_shipper_number(),
						'Address'       => array(
							'City'        => $this->shipping_method->get_origin_city(),
							'PostalCode'  => $this->shipping_method->get_origin_postcode(),
							'CountryCode' => $this->shipping_method->get_origin_country(),
						),
					),
					'ShipTo'      => array(
						'Address' => array(
							'AddressLine'       => $this->package['destination']['address_1'],
							'City'              => $this->package['destination']['city'],
							'StateProvinceCode' => $this->package['destination']['state'],
							'PostalCode'        => $this->package['destination']['postcode'],
							'CountryCode'       => $this->package['destination']['country'],
						),
					),
					'ShipFrom'    => array(
						'Address' => array(
							'City'        => $this->shipping_method->get_origin_city(),
							'PostalCode'  => $this->shipping_method->get_origin_postcode(),
							'CountryCode' => $this->shipping_method->get_origin_country(),
						),
					),
					'NumOfPieces' => (string) count( $package_requests ),
					'Package'     => $package_requests,
				),
			),
		);

		// RateRequest adjustments.
		$this->maybe_add_customer_classification_code_element( $request['RateRequest'] );

		// RateRequest > Shipment adjustments.
		$this->maybe_add_shipment_rating_options( $request['RateRequest']['Shipment'] );
		$this->maybe_add_shipment_service_options( $request['RateRequest']['Shipment'] );
		$this->maybe_add_shipment_total_weight_element( $request['RateRequest']['Shipment'], $package_requests );
		$this->maybe_add_shipment_service_element( $request['RateRequest']['Shipment'], $service_code );

		// RateRequest > Shipment > Shipper adjustments.
		$this->maybe_add_shipper_street_address_element( $request['RateRequest']['Shipment']['Shipper'] );

		// RateRequest > Shipment > ShipTo > Address adjustments.
		$this->maybe_add_ship_to_address_residential_indicator_element( $request['RateRequest']['Shipment']['ShipTo']['Address'] );
		$this->maybe_adjust_ship_to_country_code( $request['RateRequest']['Shipment']['ShipTo']['Address'] );

		// RateRequest > Shipment > ShipFrom > Address adjustments.
		$this->maybe_add_ship_from_street_address_element( $request['RateRequest']['Shipment']['ShipFrom']['Address'] );
		$this->maybe_add_ship_from_address_state_province_code_element( $request['RateRequest']['Shipment']['ShipFrom']['Address'] );

		return $request;
	}

	/**
	 * Maybe add the customer classification code to the request.
	 *
	 * @param $rate_request
	 *
	 * @return void
	 */
	public function maybe_add_customer_classification_code_element( &$rate_request ) {
		if ( ! empty( $this->shipping_method->get_customer_classification_code() ) ) {
			$rate_request['CustomerClassification'] = array(
				'Code'        => $this->shipping_method->get_customer_classification_code(),
				'Description' => $this->shipping_method->get_customer_classifications()[ $this->shipping_method->get_customer_classification_code() ],
			);
		}
	}

	/**
	 * Maybe add the AddressLine element to the Shipper Address element.
	 *
	 * @param $shipper
	 *
	 * @return void
	 */
	private function maybe_add_shipper_street_address_element( &$shipper ) {
		if ( empty( $this->shipping_method->get_origin_addressline() ) ) {
			return;
		}

		$shipper['Address']['AddressLine'] = $this->shipping_method->get_origin_addressline();
	}

	/**
	 * Maybe add the ship to address residential indicator element.
	 * This setting is located in the shipping zone's shipping method settings.
	 *
	 * @param $ship_to_address
	 *
	 * @return void
	 */
	public function maybe_add_ship_to_address_residential_indicator_element( &$ship_to_address ) {
		if ( $this->shipping_method->is_residential() ) {
			$ship_to_address['ResidentialAddressIndicator'] = '1';
		}
	}

	/**
	 * Handle conditions where the ship to country code needs to be adjusted.
	 *
	 * @param $ship_to_address
	 *
	 * @return void
	 */
	public function maybe_adjust_ship_to_country_code( &$ship_to_address ) {
		// If Country / State is 'Puerto Rico', set it to be the country.
		if ( ( 'PR' === $this->package['destination']['state'] ) && ( 'US' === $this->package['destination']['country'] ) ) {
			$ship_to_address['CountryCode'] = 'PR';
		}
	}

	/**
	 * Maybe add the AddressLine element to the ship from address.
	 *
	 * @param $ship_from_address
	 *
	 * @return void
	 */
	public function maybe_add_ship_from_street_address_element( &$ship_from_address ) {
		if ( $this->shipping_method->get_origin_addressline() ) {
			$ship_from_address['AddressLine'] = $this->shipping_method->get_origin_addressline();
		}
	}

	/**
	 * Maybe add the StateProvinceCode element to the ship from address.
	 *
	 * @param $ship_from_address
	 *
	 * @return void
	 */
	private function maybe_add_ship_from_address_state_province_code_element( &$ship_from_address ) {
		if ( $this->shipping_method->is_negotiated_rates_enabled() && $this->shipping_method->get_origin_state() ) {
			$ship_from_address['StateProvinceCode'] = $this->shipping_method->get_origin_state();
		}
	}

	/**
	 * Maybe add shipment rating options to the request shipment element.
	 *
	 * @param $shipment
	 *
	 * @return void
	 */
	public function maybe_add_shipment_rating_options( &$shipment ) {
		// Negotiated rates indicator.
		if ( $this->shipping_method->is_negotiated_rates_enabled() ) {
			$shipment['ShipmentRatingOptions']['NegotiatedRatesIndicator'] = '1';
		}
	}

	/**
	 * Maybe add shipment service options to the request shipment element.
	 *
	 * @param $shipment
	 *
	 * @return void
	 */
	public function maybe_add_shipment_service_options( &$shipment ) {
		// Delivery confirmation.
		if ( $this->shipping_method->needs_delivery_confirmation() && 'shipment' === $this->shipping_method->delivery_confirmation_level( $this->package['destination']['country'] ) ) {
			$shipment['ShipmentServiceOptions']['DeliveryConfirmation']['DCISType'] = ( 'regular' === $this->shipping_method->get_signature() ) ? '1' : '2';
		}
	}

	/**
	 * Maybe add ShipmentTotalWeight element to the request Shipment element.
	 *
	 * This element is only needed if there are multiple packages.
	 *
	 * @param array $shipment
	 * @param array $package_requests
	 *
	 * @return void
	 */
	private function maybe_add_shipment_total_weight_element( &$shipment, $package_requests ) {
		if ( 2 > count( $package_requests ) ) {
			return;
		}

		// Get total weight of all packages
		$weight = $this->get_total_weight_of_all_packages( $package_requests );

		$shipment['ShipmentTotalWeight'] = array(
			'UnitOfMeasurement' => array(
				'Code' => $this->shipping_method->get_weight_unit(),
			),
			'Weight'            => (string) $weight,
		);
	}

	/**
	 * @param array $package_requests
	 *
	 * @return int|float
	 */
	private function get_total_weight_of_all_packages( $package_requests ) {
		$total_weight = 0;
		foreach ( $package_requests as $package_request ) {
			if ( empty( $package_request['PackageWeight']['Weight'] ) || ! is_numeric( $package_request['PackageWeight']['Weight'] ) ) {
				continue;
			}

			$total_weight += $package_request['PackageWeight']['Weight'];
		}

		return $total_weight;
	}

	/**
	 * @inheritDoc
	 */
	public function validate_destination_address( $destination_address ) {

		$access_token = $this->shipping_method->get_ups_oauth()->get_access_token();

		// If we don't have an access token, return an error.
		if ( ! $access_token ) {
			$this->shipping_method->debug( __( 'UPS OAuth authentication failed.', 'woocommerce-shipping-ups' ), 'error' );
		}

		$this->shipping_method->set_is_valid_destination_address( false );

		// Validate the address.
		$this->set_address_validator( new Address_Validator( $destination_address, $access_token ) );
		$this->get_address_validator()->validate();

		$notice_group = $this->get_address_validator()::$notice_group;

		Notifier::clear_notices( $notice_group );

		// Print the request.
		$this->shipping_method->debug( __( 'Destination Address Validation Request: ', 'woocommerce-shipping-ups' ), 'notice', array( $this->get_address_validator()->get_request() ), $notice_group );

		// Print the response.
		$validation_response = $this->get_address_validator()->get_response();
		if ( is_wp_error( $validation_response ) ) {
			$this->shipping_method->debug( __( 'Destination Address Validation Error: ', 'woocommerce-shipping-ups' ), 'error', array( $validation_response->get_error_message() ), $notice_group );
			$this->shipping_method->set_is_valid_destination_address( false );

			return;
		}

		$this->shipping_method->debug( __( 'Destination Address Validation Response: ', 'woocommerce-shipping-ups' ), 'notice', $validation_response, $notice_group );

		// Set whether the destination address is valid.
		$this->set_is_valid_destination_address( $this->get_address_validator() );
	}

	/**
	 * Split the package requests into groups with a limited number of requests. If the service code is provided, the
	 * requests will be grouped in groups of 200, otherwise in groups of 50.
	 *
	 *  According to the UPS documentation:
	 *  "When using the Rate option, there is a 200-package maximum allowed for each API request.
	 *  Each package container in the request can contain 1 package.
	 *  When using the Shop option, there is a 50-package maximum limit allowed for each API request."
	 *
	 * @param string|null $service_code A specific service code to get rates for.
	 *
	 * @return array
	 */
	public function get_grouped_requests( string $service_code = null ): array {
		$limit          = ! empty( $service_code ) ? 200 : 50;
		$endpoint       = ! empty( $service_code ) ? self::$endpoints['rate'] : self::$endpoints['shop'];
		$requests       = array();
		$group          = array();
		$requests_count = count( $this->package_requests );

		foreach ( $this->package_requests as $idx => $package_request ) {
			$group[] = $package_request;

			if ( ( $idx + 1 ) % $limit === 0 && $idx > 0 ) {
				$requests[] = array(
					'body'     => $this->build_rate_request( $group, $service_code ),
					'endpoint' => $endpoint,
				);
				$group      = array();

				continue;
			}

			if ( $idx === $requests_count - 1 ) {
				$requests[] = array(
					'body'     => $this->build_rate_request( $group, $service_code ),
					'endpoint' => $endpoint,
				);
			}
		}

		return $requests;
	}

	/**
	 * If the debug mode is enabled, log a surepost debug message.
	 *
	 * @param string $service_code The service code.
	 *
	 * @return void
	 */
	public function maybe_log_surepost_debug_message( string $service_code ) {

		if ( ! $this->shipping_method->is_debug_mode_enabled() ) {
			return;
		}

		$this->logger->debug(
			/* translators: %s: service code */
			sprintf( esc_html__( 'Unable to offer SurePost service: %s.', 'woocommerce-shipping-ups' ), $service_code ),
			array(
				'reason' => sprintf(
					/* translators: %s: service code */
					esc_html__( 'At least one package did not qualify for SurePost service %s', 'woocommerce-shipping-ups' ),
					$service_code
				),
			)
		);
	}

	/**
	 * Get SurePost requests.
	 *
	 * SurePost services require a single package request.
	 * Some SurePost services cannot be offered together.
	 *
	 * @param string $service_code A specific service code to get rates for.
	 *
	 * @return array
	 */
	public function get_surepost_requests( string $service_code ): array {
		$requests = array();
		$endpoint = self::$endpoints['rate'];

		foreach ( $this->package_requests as $package_request ) {

			if ( '92' === $service_code ) {
				// SurePost 92 requires the weight to be less than 1 lb.
				if ( ! $this->package_qualifies_for_surepost_92( $package_request ) ) {
					$this->maybe_log_surepost_debug_message( $service_code );

					return array();
				}

				// SurePost 92 (Less than 1 lb) requires the weight to be in ounces.
				$this->convert_package_weight_to_ounces( $package_request );

			} elseif ( '93' === $service_code ) {
				if ( ! $this->package_qualifies_for_surepost_93( $package_request ) ) {
					$this->maybe_log_surepost_debug_message( $service_code );

					return array();
				}
			} elseif ( in_array( $service_code, array( '94', '95' ), true ) ) {
				// SurePost 94 and 95 require the total package dimension to be no more than 108 inches.
				if ( $this->get_total_package_dimension( $package_request ) > 108 ) {
					$this->maybe_log_surepost_debug_message( $service_code );

					return array();
				}
			}

			$requests[] = array(
				'body'     => $this->build_rate_request( array( $package_request ), $service_code ),
				'endpoint' => $endpoint,
			);
		}

		return $requests;
	}

	/**
	 * Check if the package qualifies for SurePost 92.
	 *
	 * @param array $package_request The package request.
	 *
	 * @return bool
	 */
	public function package_qualifies_for_surepost_92( array $package_request ): bool {
		$weight_unit = $package_request['PackageWeight']['UnitOfMeasurement']['Code'];
		$weight      = $package_request['PackageWeight']['Weight'];

		// The weight must be less than 1 lb.
		switch ( $weight_unit ) {
			case 'LBS':
				if ( $weight >= 1 ) {
					return false;
				}
				break;
			case 'KGS':
				if ( $weight >= 0.453592 ) {
					return false;
				}
				break;
			case 'OZS':
				if ( $weight >= 16 ) {
					return false;
				}
				break;
		}

		// The total package dimension must be less than or equal to 130 inches.
		if ( $this->get_total_package_dimension( $package_request ) > 130 ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the package qualifies for SurePost 93.
	 *
	 * @param array $package_request The package request.
	 *
	 * @return bool
	 */
	public function package_qualifies_for_surepost_93( array $package_request ): bool {
		$weight_unit = $package_request['PackageWeight']['UnitOfMeasurement']['Code'];
		$weight      = $package_request['PackageWeight']['Weight'];

		// The weight must be between 1 and 70 lbs.
		switch ( $weight_unit ) {
			case 'LBS':
				if ( $weight < 1 || $weight > 70 ) {
					return false;
				}
				break;
			case 'KGS':
				if ( $weight < 0.453592 || $weight > 31.7515 ) {
					return false;
				}
				break;
			case 'OZS':
				if ( $weight < 16 || $weight > 1120 ) {
					return false;
				}
				break;
		}

		// The total package dimension must be less than or equal to 130 inches.
		if ( $this->get_total_package_dimension( $package_request ) > 130 ) {
			return false;
		}

		return true;

	}

	/**
	 * Convert the package weight to ounces.
	 *
	 * @param array $package_request The package request.
	 *
	 * @return void
	 */
	public function convert_package_weight_to_ounces( array &$package_request ) {
		$weight_unit = $package_request['PackageWeight']['UnitOfMeasurement']['Code'];
		$weight      = $package_request['PackageWeight']['Weight'];

		switch ( $weight_unit ) {
			case 'LBS':
				$weight_in_ozs                                                 = $weight * 16;
				$package_request['PackageWeight']['Weight']                    = (string) $weight_in_ozs;
				$package_request['PackageWeight']['UnitOfMeasurement']['Code'] = 'OZS';
				break;
			case 'KGS':
				$weight_in_ozs                                                 = $weight * 35.274;
				$package_request['PackageWeight']['Weight']                    = (string) $weight_in_ozs;
				$package_request['PackageWeight']['UnitOfMeasurement']['Code'] = 'OZS';
				break;
		}
	}

	/**
	 * Get the total package dimension.
	 * The formula is: Length + 2 * Width + 2 * Height.
	 *
	 * @param array $package_request The package request.
	 *
	 * @return float
	 */
	public function get_total_package_dimension( array $package_request ): float {
		$dimension = 0;
		$dimension += (float) $package_request['Dimensions']['Length'];
		$dimension += ( 2 * (float) $package_request['Dimensions']['Width'] );
		$dimension += ( 2 * (float) $package_request['Dimensions']['Height'] );

		return (float) $dimension;
	}

	/**
	 * Maybe add the shipment service element to the shipment.
	 * This is used to get rates for a specific service code.
	 *
	 * @param array       $shipment     The shipment.
	 * @param string|null $service_code The service code.
	 *
	 * @return void
	 */
	public function maybe_add_shipment_service_element( array &$shipment, ?string $service_code ) {
		if ( empty( $service_code ) ) {
			return;
		}

		$shipment['Service'] = array(
			'Code' => $service_code,
		);
	}
}
