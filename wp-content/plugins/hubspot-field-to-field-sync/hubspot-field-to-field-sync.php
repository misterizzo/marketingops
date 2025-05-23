<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://makewebbetter.com/
 * @since             1.0.0
 * @package           hubspot-field-to-field-sync
 *
 * @wordpress-plugin
 * Plugin Name:       HubSpot Field to Field Sync
 * Plugin URI:        https://makewebbetter.com/hubspot-field-to-field-sync
 * Description:       Automatically creates a field to field sync between HubSpot contact properties and WordPress user's fields and updates the data over HubSpot.
 * Version:           1.0.8
 * Requires at least: 	4.4.0
 * Tested up to: 		6.1.1
 * WC requires at least:	3.0.0
 * WC tested up to: 		7.1.0
 * Author:            MakeWebBetter
 * Author URI:        https://makewebbetter.com/
 * License:           MakeWebBetter License
 * License URI:       https://makewebbetter.com/license-agreement.txt
 * Text Domain:       hubwoo
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {

	die;
}

$hubwoo_ftf_activated = false;

/**
 * Checking if HubSpot WooCommerce Integration Pro or free version is active
 **/

if ( in_array( 'hubspot-woocommerce-integration-pro/hubspot-woocommerce-integration-pro.php', apply_filters('active_plugins', get_option('active_plugins') ) ) || in_array( 'hubwoo-integration/hubspot-woocommerce-integration.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || in_array( 'hubspot-woocommerce-integration-starter/hubspot-woocommerce-integration-starter.php', apply_filters('active_plugins', get_option('active_plugins') ) ) || in_array( 'hubspot-woocommerce-integration-complimentary/hubspot-woocommerce-integration-complimetary.php', apply_filters('active_plugins', get_option('active_plugins') ) )|| in_array( 'makewebbetter-hubspot-for-woocommerce/makewebbetter-hubspot-for-woocommerce.php', apply_filters('active_plugins', get_option('active_plugins') ) )|| in_array( 'hubspot-for-woocommerce/hubspot-for-woocommerce.php', apply_filters('active_plugins', get_option('active_plugins') ) ) ) {

	$hubwoo_ftf_activated = true;
}


if ( $hubwoo_ftf_activated ) {

	/**
	 * The code that runs during plugin activation.
	 */
	function activate_hubspot_field_to_field_sync() {

		if (! wp_next_scheduled ( 'hubwoo_ftf_check_licence_daily' ) ) {

            wp_schedule_event(time(), 'daily', 'hubwoo_ftf_check_licence_daily' );
        }
	}

	register_activation_hook( __FILE__, 'activate_hubspot_field_to_field_sync' );

	/**
	 * The code that runs during plugin deactivation.
	 */

	function deactivate_hubspot_field_to_field_sync() {

		wp_clear_scheduled_hook( 'hubwoo_ftf_check_licence_daily' );
	}

	register_deactivation_hook( __FILE__, 'deactivate_hubspot_field_to_field_sync' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-hubspot-field-to-field-sync.php';

	/**
	 * define hubwoo field to field constants.
	 *
	 * @since 1.0.0
	*/
	function hubwoo_ftf_define_constants() {

		hubwoo_ftf_define( 'HUBWOO_FTF_VERSION', '1.0.8' );
		hubwoo_ftf_define( 'HUBWOO_FTF_PLUGINS_PATH', plugin_dir_path( __DIR__ ) );
		hubwoo_ftf_define( 'HUBWOO_FTF_SPECIAL_SECRET_KEY', '59f32ad2f20102.74284991' );
		hubwoo_ftf_define( 'HUBWOO_FTF_LICENSE_SERVER_URL', 'https://makewebbetter.com' );
		hubwoo_ftf_define( 'HUBWOO_FTF_ITEM_REFERENCE', 'HubSpot Field to Field Sync' );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param  string $name
	 * @param  string|bool $value
	 * @since 1.0.0
	*/
	function hubwoo_ftf_define( $name, $value ) {

		if ( ! defined( $name ) ) {

			define( $name, $value );
		}
	}

	/**
	 * Setting Page Link
	 * @since    1.0.0
	 * @author  MakeWebBetter
	 * @link  https://makewebbetter.com/
	 */

	function hubwoo_ftf_admin_settings( $actions, $plugin_file ) {

		static $plugin;

		if ( !isset ( $plugin ) ) {
	
			$plugin = plugin_basename ( __FILE__ );
		}

		if ( $plugin == $plugin_file ) {

			$settings = array (
				'settings' => '<a href="' . admin_url ( 'admin.php' ).'?page=hubwoo_field_to_field'. '">' . __ ( 'Settings', 'hubwoo' ) . '</a>',
			);

			$actions = array_merge ( $settings, $actions );
		}

		return $actions;
	}
	
	//add link for settings
	add_filter ( 'plugin_action_links','hubwoo_ftf_admin_settings', 10, 5 );

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_hubspot_field_to_field_sync() {

		hubwoo_ftf_define_constants();
		$hubwoo_ftf = new Hubspot_Field_To_Field_Sync();
		$hubwoo_ftf->run();
		$GLOBALS['hubwoo_ftf'] = $hubwoo_ftf;

	}
	run_hubspot_field_to_field_sync();
}
else {

	/**
	 * Show warning message if HubSpot WooCommerce Integration Pro or free version is not activated
	 * @since 1.0.0
	 * @author MakeWebBetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */

	function hubwoo_ftf_plugin_error_notice() {

 		?>
 		 <div class="error notice is-dismissible">
			<p><?php _e( 'Oops! You tried activating HubSpot Field to Field Sync without installing and activating the main extension of HubSpot WooCommerce Integration. Please activate HubSpot WooCommerce Integration and then try again.', 'hubwoo' ); ?></p>
   		</div>
   		<style>
   		#message{display:none;}
   		</style>
   		<?php 
 	}
 	 
 	add_action( 'admin_init', 'hubwoo_ftf_plugin_deactivate' );  
 
 	
 	/**
 	 * Call Admin notices
 	 * 
 	 * @author MakeWebBetter<webmaster@makewebbetter.com>
 	 * @link https://www.makewebbetter.com/
 	 */ 	
  	function hubwoo_ftf_plugin_deactivate() {

	   deactivate_plugins( plugin_basename( __FILE__ ) );
	   add_action( 'admin_notices', 'hubwoo_ftf_plugin_error_notice' );
	}
}

$hubwoo_ftf_license_key = get_option( "hubwoo_ftf_license_key", "" );
define( 'HUBWOO_FTF_LICENSE_KEY', $hubwoo_ftf_license_key );
define( 'HUBWOO_FTF_BASE_FILE', __FILE__ );
$hubwoo_ftf_update_check = "https://makewebbetter.com/pluginupdates/hubspot-field-to-field-sync/update.php";
require_once('mwb-hubwoo-ftf-update.php');


// Declare support for HPOS features
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );