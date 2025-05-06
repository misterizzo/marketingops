<?php
/**
 * Plugin Name: LearnDash LMS - WooCommerce Integration
 * Plugin URI: https://www.learndash.com/integrations/woocommerce/
 * Description: LearnDash LMS addon plugin to integrate LearnDash LMS with WooCommerce.
 * Version: 2.0.1
 * Author: LearnDash
 * Author URI: https://www.learndash.com
 * Domain Path: /languages/
 * Text Domain: learndash-woocommerce
 * WC requires at least: 3.0.0
 * WC tested up to: 8.6.1
 *
 * @package LearnDash\WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LEARNDASH_WOOCOMMERCE_VERSION', '2.0.1' );
define( 'LEARNDASH_WOOCOMMERCE_FILE', __FILE__ );
define( 'LEARNDASH_WOOCOMMERCE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'LEARNDASH_WOOCOMMERCE_DIR', plugin_dir_path( __FILE__ ) );
define( 'LEARNDASH_WOOCOMMERCE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LEARNDASH_WOOCOMMERCE_VIEWS_PATH', plugin_dir_path( __FILE__ ) . 'src/views/' );
define( 'LEARNDASH_WOOCOMMERCE_VIEWS_URL', plugin_dir_url( __FILE__ ) . 'src/views/' );
define( 'LEARNDASH_WOOCOMMERCE_ADMIN_VIEWS_PATH', plugin_dir_path( __FILE__ ) . 'src/admin-views/' );
define( 'LEARNDASH_WOOCOMMERCE_ADMIN_VIEWS_URL', plugin_dir_url( __FILE__ ) . 'src/admin-views/' );

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'vendor-prefixed/autoload.php';

use LearnDash\Core\Autoloader;
use LearnDash\WooCommerce\Dependency_Checker;
use LearnDash\WooCommerce\Plugin;

add_action(
	'plugins_loaded',
	function () {
		load_plugin_textdomain(
			'learndash-woocommerce',
			false,
			plugin_basename(
				__DIR__
			) . '/languages'
		);
	}
);

$learndash_woocommerce_dependency_checker = new Dependency_Checker();

$learndash_woocommerce_dependency_checker->set_dependencies(
	[
		'sfwd-lms/sfwd_lms.php'       => [
			'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
			'class'       => 'SFWD_LMS',
			'min_version' => '4.7.0',
		],
		'woocommerce/woocommerce.php' => [
			'label'       => '<a href="https://woocommerce.com/">WooCommerce</a>',
			'class'       => 'WooCommerce',
			'min_version' => '4.5.0',
		],
	]
);

$learndash_woocommerce_dependency_checker->set_message(
	esc_html__( 'LearnDash LMS - WooCommerce add-on requires the following plugin(s) be active:', 'learndash-woocommerce' )
);

/**
 * The initialization of the plugin requires `plugins_loaded` hook because some required WooCommerce hooks
 * are not available in `learndash_init` or `init` hook. We set the priority to 50 to ensure that all plugins
 * have been loaded before we check the dependencies.
 */
add_action(
	'plugins_loaded',
	function () use ( $learndash_woocommerce_dependency_checker ) {
		if ( ! $learndash_woocommerce_dependency_checker->check_dependency_results() ) {
			return;
		}

		learndash_woocommerce_extra_autoloading();

		learndash_register_provider( Plugin::class );

		require_once LEARNDASH_WOOCOMMERCE_PLUGIN_PATH . 'includes/class-learndash-woocommerce.php';
		new Learndash_WooCommerce();
	},
	50
);

/**
 * Sets up the autoloader for extra classes, which are not in the src/WooCommerce directory.
 *
 * @since 2.0.0
 *
 * @return void
 */
function learndash_woocommerce_extra_autoloading(): void {
	$autoloader = Autoloader::instance();

	foreach ( (array) glob( LEARNDASH_WOOCOMMERCE_PLUGIN_PATH . 'src/deprecated/*.php' ) as $file ) {
		$autoloader->register_class( basename( (string) $file, '.php' ), (string) $file );
	}

	$autoloader->register_autoloader();
}
