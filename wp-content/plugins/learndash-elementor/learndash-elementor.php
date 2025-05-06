<?php
/**
 * Plugin Name: LearnDash LMS - Elementor
 * Plugin URI: http://www.learndash.com
 * Description: LearnDash LMS official add-on to integrate LearnDash LMS with Elementor widgets and templates.
 * Version: 1.0.10
 * Author: LearnDash
 * Author URI: http://www.learndash.com
 * Text Domain: learndash-elementor
 * Domain Path: /languages/
 *
 * @package LearnDash\Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LEARNDASH_ELEMENTOR_VERSION', '1.0.10' );
define( 'LEARNDASH_ELEMENTOR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LEARNDASH_ELEMENTOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LEARNDASH_ELEMENTOR_VIEWS_DIR', plugin_dir_path( __FILE__ ) . 'src/views/' );
define( 'LEARNDASH_ELEMENTOR_VIEWS_URL', plugin_dir_url( __FILE__ ) . 'src/views/' );

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'vendor-prefixed/autoload.php';

use LearnDash\Core\Autoloader;
use LearnDash\Elementor\Plugin;
use LearnDash\Elementor\Dependency_Checker;

$learndash_elementor_dependency_checker = new Dependency_Checker();

$learndash_elementor_dependency_checker->set_dependencies(
	array(
		'sfwd-lms/sfwd_lms.php'           => array(
			'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
			'class'       => 'SFWD_LMS',
			'min_version' => '4.7.0',
		),
		'elementor/elementor.php'         => array(
			'label'       => '<a href="https://elementor.com">Elementor</a>',
			'min_version' => '3.15.0',
		),
		'elementor-pro/elementor-pro.php' => array(
			'label'       => '<a href="https://elementor.com">Elementor Pro</a>',
			'min_version' => '3.15.0',
		),
	)
);

$learndash_elementor_dependency_checker->set_message(
	esc_html__( 'LearnDash LMS - Elementor add-on requires the following plugin(s) be active:', 'learndash-elementor' )
);

add_action(
	'learndash_init',
	function () use ( $learndash_elementor_dependency_checker ) {
		if (
			! $learndash_elementor_dependency_checker->check_dependency_results()
			|| ! learndash_is_active_theme( 'ld30' )
		) {
			return;
		}

		learndash_elementor_extra_autoloading();

		learndash_register_provider( Plugin::class );
	}
);

/**
 * Setup the autoloader for extra classes, which are not in the src/Elementor directory.
 *
 * @since 1.0.5
 * @since 1.0.8 Added namespaced classes support.
 *
 * @return void
 */
function learndash_elementor_extra_autoloading(): void {
	// From https://www.php.net/manual/en/function.glob.php#106595.
	$glob_recursive = function ( string $pattern, int $flags = 0 ) use ( &$glob_recursive ): array {
		$files = glob( $pattern, $flags );
		$files = $files === false ? array() : $files;

		$directories = glob(
			dirname( $pattern ) . '/*',
			GLOB_ONLYDIR | GLOB_NOSORT // cspell: disable-line -- GLOB_ONLYDIR and GLOB_NOSORT are constants.
		);

		if ( is_array( $directories ) ) {
			foreach ( $directories as $dir ) {
				$files = array_merge(
					$files,
					$glob_recursive( $dir . '/' . basename( $pattern ), $flags )
				);
			}
		}

		return $files;
	};

	$autoloader = Autoloader::instance();

	foreach ( $glob_recursive( LEARNDASH_ELEMENTOR_PLUGIN_DIR . 'src/deprecated/*.php' ) as $file ) {
		if ( ! strstr( $file, 'functions' ) ) {
			// Get the clean path to the file without the extension and the src/deprecated directory.
			$class_mapped_from_file = mb_substr( $file, mb_strpos( $file, 'src/deprecated/' ) + 15, -4 );

			// Convert directory separator to namespace separator.
			// If the class is in a subdirectory, add the root namespace.
			$class_mapped_from_file = strpos( $class_mapped_from_file, '/' )
				? str_replace( '/', '\\', 'LearnDash/' . $class_mapped_from_file )
				: $class_mapped_from_file;

			$autoloader->register_class( $class_mapped_from_file, (string) $file );
		} else {
			include_once $file;
		}
	}

	$autoloader->register_autoloader();
}
