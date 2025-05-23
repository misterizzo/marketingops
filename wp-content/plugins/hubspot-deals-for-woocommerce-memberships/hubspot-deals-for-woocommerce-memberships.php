<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://makewebbetter.com
 * @since             1.0.0
 * @package           hubspot-deals-for-woocommerce-memberships
 *
 * @wordpress-plugin
 * Plugin Name:       HubSpot Deals for WooCommerce Memberships
 * Plugin URI:        https://makewebbetter.com/products/hubspot-deals-for-woocommerce-memberships
 * Description:       Auto creates a new Deal on HubSpot for every new Membership on your woocommerce store.
 * Version:           1.0.3
 * Author:            MakeWebBetter
 * Author URI:        https://makewebbetter.com
 * License:           MakeWebBetter License
 * License URI:       https://makewebbetter.com/license-agreement.txt
 * Text Domain:       hubwoo
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$membership_deals_activated = true;
$hubwoo_ms_plugin = "";


if ( !in_array( 'woocommerce-memberships/woocommerce-memberships.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	$membership_deals_activated = false;
	$hubwoo_ms_plugin = "membership";	
}

/**
 * Checking if HubSpot WooCommerce Integration is active
 **/

elseif ( !( in_array( 'hubspot-woocommerce-integration-pro/hubspot-woocommerce-integration-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || in_array( 'hubspot-woocommerce-integration-starter/hubspot-woocommerce-integration-starter.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || in_array( 'hubspot-woocommerce-integration-complimentary/hubspot-woocommerce-integration-complimetary.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || in_array( 'hubwoo-integration/hubspot-woocommerce-integration.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || in_array( 'hubspot-for-woocommerce/hubspot-for-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || in_array( 'makewebbetter-hubspot-for-woocommerce/makewebbetter-hubspot-for-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) ) {

	$membership_deals_activated = false;
	$hubwoo_ms_plugin = "hubwoo";
}

if ( $membership_deals_activated ) {

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-hubspot-deals-for-woocommerce-memberships-activator.php
	 */
	function activate_hubspot_deals_for_woocommerce_memberships() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-hubspot-deals-for-woocommerce-memberships-activator.php';
		Hubspot_Deals_For_Woocommerce_Memberships_Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-hubspot-deals-for-woocommerce-memberships-deactivator.php
	 */
	function deactivate_hubspot_deals_for_woocommerce_memberships() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-hubspot-deals-for-woocommerce-memberships-deactivator.php';
		Hubspot_Deals_For_Woocommerce_Memberships_Deactivator::deactivate();
	}

	register_activation_hook( __FILE__, 'activate_hubspot_deals_for_woocommerce_memberships' );
	register_deactivation_hook( __FILE__, 'deactivate_hubspot_deals_for_woocommerce_memberships' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-hubspot-deals-for-woocommerce-memberships.php';

	/**
	 * define hubwoo_deals constants.
	 *
	 * @since 1.0.0
	*/
	function hubwoo_ms_deal_define_constants() {

		hubwoo_ms_deal_define( 'HUBWOO_MS_DEAL_URL', plugin_dir_url( __FILE__ ) );

		hubwoo_ms_deal_define( 'HUBWOO_MS_DEAL_PLUGINS_PATH', plugin_dir_path( __DIR__ ) );

		hubwoo_ms_deal_define( 'HUBWOO_MS_DEAL_VERSION', '1.0.3' );

		hubwoo_ms_deal_define( 'HUBWOO_MS_DEAL_CLIENTID', '769fa3e6-79b1-412d-b69c-6b8242b2c62a' );
			
		hubwoo_ms_deal_define( 'HUBWOO_MS_DEAL_SECRETID', '2893dd41-017e-4208-962b-12f7495d16b0' );

		hubwoo_ms_deal_define( 'HUBWOO_MS_DEAL_LICENSE_SERVER_URL', 'https://makewebbetter.com' );

		hubwoo_ms_deal_define( 'HUBWOO_MS_DEAL_ACTIVATION_SECRET_KEY', '59f32ad2f20102.74284991' );

		hubwoo_ms_deal_define( 'HUBWOO_MS_DEAL_ITEM_REFERENCE', 'HubSpot Deals for WooCommerce Memberships' );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param  string $name
	 * @param  string|bool $value
	 * @since 1.0.0
	*/
	function hubwoo_ms_deal_define( $name, $value ) {
		
		if ( ! defined( $name ) ) {
			
			define( $name, $value );
		}
	}

	/**
	 * Setting Page Link
	 * @since    1.0.0
	 * @link  https://makewebbetter.com/ 
	 */

	function hubwoo_ms_deal_admin_settings( $actions, $plugin_file ) {

		static $plugin;

		if ( !isset( $plugin ) ) {
	
			$plugin = plugin_basename ( __FILE__ );
		}

		if ( $plugin == $plugin_file ) {

			$settings = array (

				'settings' => '<a href="' . admin_url ( 'admin.php' ) . '?page=hubwoo_ms_deal' . '">' . __ ( 'Settings', 'hubwoo' ) . '</a>',
			);

			$actions = array_merge ( $settings, $actions );
		}

		return $actions;
	}
	
	//add link for settings
	add_filter ( 'plugin_action_links','hubwoo_ms_deal_admin_settings', 10, 2 );

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_hubspot_deals_for_woocommerce_memberships() {

		hubwoo_ms_deal_define_constants();
		$plugin = new Hubspot_Deals_For_Woocommerce_Memberships();
		$plugin->run();
	}
	run_hubspot_deals_for_woocommerce_memberships();
}
elseif ( !$membership_deals_activated && "hubwoo" == $hubwoo_ms_plugin ) {

	/**
	 * Show warning message if HubSpot WooCommerce Integration is not install
	 * @since 1.0.0
	 * @link https://www.makewebbetter.com/
	 */

	function hubwoo_ms_deal_plugin_error_notice() {
		
	?>
 		<div class="error notice is-dismissible">
			<p><?php _e( 'Oops! You tried activating the HubSpot Deals for WooCommerce Memberships without installing and activating the main extension of HubSpot WooCommerce Integration. Please activate HubSpot WooCommerce Integration and then try again.', 'hubwoo' ); ?></p>
   		</div>
   		<style>
   			#message{display:none;}
   		</style>
   	<?php 
 	}

 	add_action( 'admin_init', 'hubwoo_ms_deal_plugin_deactivate' );  
 
 	
 	/**
 	 * Call Admin notices
 	 * 
 	 * @link https://www.makewebbetter.com/
 	 */ 	
  	function hubwoo_ms_deal_plugin_deactivate() {

	   deactivate_plugins( plugin_basename( __FILE__ ) );
	   add_action( 'admin_notices', 'hubwoo_ms_deal_plugin_error_notice' );
	}
}
elseif ( !$membership_deals_activated && "membership" == $hubwoo_ms_plugin ) {

	/**
	 * Show warning message if WooCommerce Membership is not install
	 * @since 1.0.0
	 * @link https://www.makewebbetter.com/
	 */

	function hubwoo_ms_deal_plugin_error_notice() {
		
	?>
 		<div class="error notice is-dismissible">
			<p><?php _e( 'Oops! You tried activating the HubSpot Deals for WooCommerce Memberships without installing and activating the WooCommerce Memberships. Please activate WooCommerce Memberships and then try again.', 'hubwoo' ); ?></p>
   		</div>
   		<style>
   			#message{display:none;}
   		</style>
   	<?php 
 	}

 	add_action( 'admin_init', 'hubwoo_ms_deal_plugin_deactivate' );  
 
 	
 	/**
 	 * Call Admin notices
 	 * 
 	 * @link https://www.makewebbetter.com/
 	 */ 	
  	function hubwoo_ms_deal_plugin_deactivate() {

	   deactivate_plugins( plugin_basename( __FILE__ ) );
	   add_action( 'admin_notices', 'hubwoo_ms_deal_plugin_error_notice' );
	}
}

$hubwoo_ms_deals_license_key = get_option( "hubwoo_ms_deals_license_key", "" );
define( 'HUBWOO_MS_LICENSE_KEY', $hubwoo_ms_deals_license_key );
define( 'HUBWOO_MS_BASE_FILE', __FILE__ );
$hubwoo_ms_deal_update_check = "https://makewebbetter.com/pluginupdates/hubspot-deals-for-woocommerce-memberships/update.php";
require_once( 'mwb-hubwoo-ms-deals-update.php' );