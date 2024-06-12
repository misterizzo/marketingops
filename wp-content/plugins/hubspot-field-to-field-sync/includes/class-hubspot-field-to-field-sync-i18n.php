<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-field-to-field-sync
 * @subpackage hubspot-field-to-field-sync/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    hubspot-field-to-field-sync
 * @subpackage hubspot-field-to-field-sync/includes
 * @author     MakeWebBetter <webmaster@makewebbetter.com>
 */
class Hubspot_Field_To_Field_Sync_i18n {


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