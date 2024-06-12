<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.cmsminds.com/
 * @since      1.0.0
 *
 * @package    Marketing_Ops_Core
 * @subpackage Marketing_Ops_Core/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Marketing_Ops_Core
 * @subpackage Marketing_Ops_Core/includes
 * @author     cmsMinds <info@cmsminds.com>
 */
class Marketing_Ops_Core_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'marketing-ops-core',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
