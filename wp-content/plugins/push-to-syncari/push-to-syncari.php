<?php
/**
 * The plugin bootstrap file.
 *
 * @link              https://github.com/vermadarsh/
 * @since             1.0.0
 * @package           Core_Functions
 *
 * @wordpress-plugin
 * Plugin Name:       Push to Syncari 
 * Plugin URI:        https://github.com/vermadarsh/
 * Description:       This plugin is responsible pushing the data to custom table to push the data to Syncari.
 * Version:           1.0.0
 * Author:            Adarsh Verma
 * Author URI:        https://github.com/vermadarsh/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       push-to-syncari
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// error_reporting(E_ALL);
// ini_set('display_errors', '1');

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PTS_PLUGIN_VERSION', '1.0.0' );

// Plugin path.
if ( ! defined( 'PTS_PLUGIN_PATH' ) ) {
	define( 'PTS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

// Plugin URL.
if ( ! defined( 'PTS_PLUGIN_URL' ) ) {
	define( 'PTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_push_to_syncari() {
	require_once 'includes/pts-push-to-syncari-functions.php';

	// The core plugin class that is used to define admin-specific hooks.
	require_once 'includes/class-pts-push-to-syncari-admin.php';
	new Pts_Push_To_Syncari_Admin();
}

/**
 * This initiates the plugin.
 * Checks for the required plugins to be installed and active.
 */
function pts_plugins_loaded_callback() {
	run_push_to_syncari();
}

add_action( 'plugins_loaded', 'pts_plugins_loaded_callback' );

/**
 * Debugger function which shall be removed in production.
 */
if ( ! function_exists( 'debug' ) ) {
	/**
	 * Debug function definition.
	 *
	 * @param string $params Holds the variable name.
	 */
	function debug( $params ) {
		echo '<pre>';
		print_r( $params );
		echo '</pre>';
	}
}