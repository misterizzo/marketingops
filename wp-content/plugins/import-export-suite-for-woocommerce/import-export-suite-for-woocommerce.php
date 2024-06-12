<?php
/**
 * Import export for WooCommerce
 *
 * @link              https://www.webtoffee.com/
 * @since             1.0.0
 * @package           ImportExportSuite
 *
 * @wordpress-plugin
 * Plugin Name:       Import Export Suite for WooCommerce
 * Plugin URI:        https://woocommerce.com/products/import-export-suite-for-woocommerce/
 * Description:       Import and export WooCommerce Products, Product reviews, Orders, Customers, Coupons and Subscriptions.
 * Version:           1.2.7
 * Author:            WebToffee
 * Author URI:        https://www.woocommerce.com
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       import-export-suite-for-woocommerce
 * Domain Path:       /languages
 *
 * Woo: 9125689:50e7e5db0c65d0ec3c066618574d8709
 * WC requires at least: 3.0.0
 * WC tested up to: 8.7.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WT_IEW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WT_IEW_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WT_IEW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WT_IEW_PLUGIN_FILENAME', __FILE__ );
define( 'WT_IEW_SETTINGS_FIELD', 'wt_import_export_for_woo' );
define( 'WT_IEW_ACTIVATION_ID', 'import-export-suite-for-woocommerce' );
define( 'WT_IEW_TEXT_DOMAIN', 'import-export-suite-for-woocommerce' );
define( 'WT_IEW_PLUGIN_ID', 'wt_import_export_for_woo' );
define( 'WT_IEW_PLUGIN_NAME', 'Import Export Suite for WooCommerce' );
define( 'WT_IEW_PLUGIN_DESCRIPTION', 'Import and Export From and To your WooCommerce Store.' );
define( 'WT_IEW_DEBUG_PRO_TROUBLESHOOT', 'https://www.webtoffee.com/finding-php-error-logs/' );

define( 'WT_IEW_DEBUG', false );

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WT_IEW_VERSION', '1.2.7' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wt-import-export-for-woo-activator.php
 */
function activate_wt_import_export_for_woo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wt-import-export-for-woo-activator.php';
	Wt_Import_Export_For_Woo_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wt-import-export-for-woo-deactivator.php
 */
function deactivate_wt_import_export_for_woo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wt-import-export-for-woo-deactivator.php';
	Wt_Import_Export_For_Woo_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wt_import_export_for_woo' );
register_deactivation_hook( __FILE__, 'deactivate_wt_import_export_for_woo' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wt-import-export-for-woo.php';

$advanced_settings = get_option( 'wt_iew_advanced_settings', array() );
$ier_get_max_execution_time = ( isset( $advanced_settings['wt_iew_maximum_execution_time'] ) && '' != $advanced_settings['wt_iew_maximum_execution_time'] ) ? $advanced_settings['wt_iew_maximum_execution_time'] : ini_get( 'max_execution_time' );
$ier_get_max_execution_time = (int) $ier_get_max_execution_time;
if ( false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) ) {
	$current_execution_time = ini_get( 'max_execution_time' );
	$current_execution_time = (int) $current_execution_time;
	if ( $current_execution_time < $ier_get_max_execution_time ) {
		set_time_limit( $ier_get_max_execution_time );
	}
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
function run_wt_import_export_for_woo() {

	$plugin = new Wt_Import_Export_For_Woo();
	$plugin->run();
}
if ( get_option( 'wt_iew_is_active' ) ) {
	run_wt_import_export_for_woo();
}
// HPOS compatibility decleration.
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);
