<?php
/**
 * Logger class.
 *
 * A class to handle logging for the UPS shipping method.
 *
 * @package WC_Shipping_UPS
 */

namespace WooCommerce\UPS;

use WC_Logger;

/**
 * Logger class.
 */
class Logger {
	/**
	 * WC Log context.
	 *
	 * @var string
	 */
	const CONTEXT = 'woocommerce-shipping-ups';

	/**
	 * WC Logger
	 *
	 * @var WC_Logger
	 */
	private WC_Logger $logger;

	/**
	 * Is debug enabled.
	 *
	 * @var bool
	 */
	private bool $is_debug_enabled;

	/**
	 * Constructor.
	 */
	public function __construct( bool $is_debug_enabled ) {
		$this->is_debug_enabled = $is_debug_enabled;

		$this->logger = wc_get_logger();
	}

	/**
	 * Check if debug is enabled.
	 *
	 * @return bool
	 */
	private function is_debug_enabled(): bool {
		return $this->is_debug_enabled;
	}

	/**
	 * Add a debug log entry.
	 * Only logs if debug is enabled.
	 *
	 * @param string $message Message to display.
	 * @param array  $data    Additional contextual data to pass.
	 *
	 * @return void
	 */
	public function debug( string $message, array $data = array() ) {
		if ( ! $this->is_debug_enabled() ) {
			return;
		}

		$this->logger->debug( $message, $data );
	}
}
