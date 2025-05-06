<?php
/**
 * Plugin Name: LearnDash LMS - Certificate Builder
 * Plugin URI: https://www.learndash.com/ld-add-ons/learndash-certificate-builder/
 * Description: LearnDash certificate builder allows you build certificates for your courses using the Gutenberg WordPress block editor.
 * Version: 1.1.3.1
 * Author: LearnDash
 * Author URI: https://www.learndash.com
 * Text Domain: learndash-certificate-builder
 * Domain Path: /languages
 *
 * @package LearnDash\Certificate_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'vendor-prefixed/autoload.php';

// Legacy includes.

require_once plugin_dir_path( __FILE__ ) . 'src/classmap.php';
require_once plugin_dir_path( __FILE__ ) . 'src/constants.php';
require_once plugin_dir_path( __FILE__ ) . 'src/functions.php';

use LearnDash\Certificate_Builder\Dependency_Checker;
use LearnDash\Certificate_Builder\Plugin;
use LearnDash\Core\App;
use LearnDash_Certificate_Builder\Controller\Certificate_Builder;

define( 'LEARNDASH_CERTIFICATE_BUILDER_VERSION', '1.1.3.1' );
define( 'LEARNDASH_CERTIFICATE_BUILDER_DIR', plugin_dir_path( __FILE__ ) );
define( 'LEARNDASH_CERTIFICATE_BUILDER_URL', plugins_url( '/', __FILE__ ) );
define( 'LEARNDASH_CERTIFICATE_BUILDER_FILE', __FILE__ );

add_action(
	'plugins_loaded',
	function () {
		load_plugin_textdomain(
			'learndash-certificate-builder',
			false,
			plugin_basename(
				__DIR__
			) . '/languages'
		);
	}
);

$learndash_certificate_builder_dependency_checker = new Dependency_Checker();

$learndash_certificate_builder_dependency_checker->set_dependencies(
	[
		'sfwd-lms/sfwd_lms.php' => [
			'label'            => '<a href="https://www.learndash.com" target="_blank">' . __( 'LearnDash LMS', 'learndash-certificate-builder' ) . '</a>',
			'class'            => 'SFWD_LMS',
			'version_constant' => 'LEARNDASH_VERSION',
			'min_version'      => '4.6.0',
		],
	]
);

$learndash_certificate_builder_dependency_checker->set_message(
	esc_html__( 'LearnDash LMS - Certificate Builder requires the following plugin(s) to be active:', 'learndash-certificate-builder' )
);

add_action(
	'learndash_init',
	function () use ( $learndash_certificate_builder_dependency_checker ) {
		// If plugin requirements aren't met, don't run anything else to prevent possible fatal errors.
		if (
			! $learndash_certificate_builder_dependency_checker->check_dependency_results()
			|| php_sapi_name() === 'cli'
		) {
			return;
		}

		learndash_register_provider( Plugin::class );

		// Instantiate the plugin.
		// The legacy way of initializing the plugin is to create an object of the Certificate_Builder class.
		// Here we get the singleton instance of the class which is registered in the App container.
		App::get( Certificate_Builder::class );
	}
);
