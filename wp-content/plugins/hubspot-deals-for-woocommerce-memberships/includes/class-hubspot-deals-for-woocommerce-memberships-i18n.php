<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://makewebbetter.com
 * @since      1.0.0
 *
 * @package    hubspot-deals-for-woocommerce-memberships
 * @subpackage hubspot-deals-for-woocommerce-memberships/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    hubspot-deals-for-woocommerce-memberships
 * @subpackage hubspot-deals-for-woocommerce-memberships/includes
 * @author     MakeWebBetter <webmaster@makewebbetter.com>
 */
class Hubspot_Deals_For_Woocommerce_Memberships_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'hubwoo',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}