<?php
/**
 * Store_API_Extension class.
 *
 * A class to extend the store public API with UPS shipping functionality.
 *
 * @package WC_Shipping_UPS
 */

namespace WooCommerce\UPS;

use Automattic\WooCommerce\StoreApi\StoreApi;
use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;

/**
 * Store API Extension.
 */
class Store_API_Extension {
	/**
	 * Stores Rest Extending instance.
	 *
	 * @var ExtendSchema
	 */
	private static $extend;

	/**
	 * Plugin Identifier, unique to each plugin.
	 *
	 * @var string
	 */
	const IDENTIFIER = 'wc_shipping_ups';

	/**
	 * Bootstraps the class and hooks required data.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		self::$extend = StoreApi::container()->get( ExtendSchema::class );
		self::extend_store();
	}

	/**
	 * Registers the data into each endpoint.
	 */
	public static function extend_store() {

		self::$extend->register_endpoint_data(
			array(
				'endpoint'        => CartSchema::IDENTIFIER,
				'namespace'       => self::IDENTIFIER,
				'data_callback'   => array( static::class, 'data' ),
				'schema_callback' => array( static::class, 'schema' ),
				'schema_type'     => ARRAY_A,
			)
		);
	}

	/**
	 * Store API extension data callback.
	 *
	 * @return array
	 */
	public static function data() {
		$notices = Notifier::get_notices();

		Notifier::clear_notices();

		$html_formatter = self::$extend->get_formatter( 'html' );

		foreach ( $notices as $type => $type_notices ) {
			foreach ( $type_notices as $index => $notice ) {
				$notices[ $type ][ $index ]['message'] = $html_formatter->format( $notice['message'] );
			}
		}

		return array(
			'debug_notices'   => ! empty( $notices['notice'] ) ? $notices['notice'] : array(),
			'success_notices' => ! empty( $notices['success'] ) ? $notices['success'] : array(),
			'error_notices'   => ! empty( $notices['error'] ) ? $notices['error'] : array(),
		);
	}

	/**
	 * Store API extension schema callback.
	 *
	 * @return array Registered schema.
	 */
	public static function schema() {
		return array(
			'debug_notices'   => array(
				'description' => __( 'UPS debug notices', 'woocommerce-shipping-ups' ),
				'type'        => array( 'array' ),
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
			'success_notices' => array(
				'description' => __( 'UPS success notices', 'woocommerce-shipping-ups' ),
				'type'        => array( 'array' ),
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
			'error_notices'   => array(
				'description' => __( 'UPS error notices', 'woocommerce-shipping-ups' ),
				'type'        => array( 'array' ),
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
		);
	}
}
