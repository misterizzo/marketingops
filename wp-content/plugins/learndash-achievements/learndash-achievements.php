<?php
/**
 * Plugin Name: LearnDash LMS - Achievements
 * Plugin URI: http://www.learndash.com/
 * Description: Award badges and points to users for the successful completion of LearnDash and WordPress activities.
 * Version: 2.0.3
 * Author: LearnDash
 * Author URI: http://www.learndash.com/
 * Text Domain: learndash-achievements
 * Domain Path: languages
 *
 * @package LearnDash\Achievements
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'vendor-prefixed/autoload.php';

use LearnDash\Achievements\Dependency_Checker;
use LearnDash\Achievements\Plugin;
use LearnDash\Achievements\StellarWP\DB;
use LearnDash\Core\Autoloader;

define( 'LEARNDASH_ACHIEVEMENTS_VERSION', '2.0.3' );
define( 'LEARNDASH_ACHIEVEMENTS_DIR', plugin_dir_path( __FILE__ ) );
define( 'LEARNDASH_ACHIEVEMENTS_URL', plugins_url( '/', __FILE__ ) );
define( 'LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'LEARNDASH_ACHIEVEMENTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LEARNDASH_ACHIEVEMENTS_FILE', __FILE__ );

add_action(
	'plugins_loaded',
	function () {
		/**
		 * Filters the plugin language directory.
		 *
		 * @since 1.0.0
		 *
		 * @param string $lang_dir The plugin language directory.
		 *
		 * @return string
		 */
		$lang_dir = apply_filters(
			'learndash_achievements_languages_directory',
			plugin_basename( __DIR__ ) . '/languages'
		);

		load_plugin_textdomain(
			'learndash-achievements',
			false,
			$lang_dir
		);
	}
);

$learndash_achievements_dependency_checker = new Dependency_Checker();

$learndash_achievements_dependency_checker->set_dependencies(
	[
		'sfwd-lms/sfwd_lms.php' => [
			'label'            => '<a href="https://www.learndash.com" target="_blank">' . __( 'LearnDash LMS', 'learndash-achievements' ) . '</a>',
			'class'            => 'SFWD_LMS',
			'version_constant' => 'LEARNDASH_VERSION',
			'min_version'      => '4.7.0',
		],
	]
);

$learndash_achievements_dependency_checker->set_message(
	esc_html__( 'LearnDash LMS - Achievements requires the following plugin(s) to be active:', 'learndash-achievements' )
);

add_action(
	'learndash_init',
	function () use ( $learndash_achievements_dependency_checker ) {
		// If plugin requirements aren't met, don't run anything else to prevent possible fatal errors.
		if ( ! $learndash_achievements_dependency_checker->check_dependency_results() || php_sapi_name() === 'cli' ) {
			return;
		}

		DB\Config::setHookPrefix( 'learndash_achievements' );
		DB\DB::init();

		learndash_achievements_extra_includes();
		learndash_achievements_extra_autoloading();

		learndash_register_provider( Plugin::class );
	}
);

/**
 * Includes all necessary PHP files.
 *
 * @since 2.0.0
 *
 * @return void
 */
function learndash_achievements_extra_includes(): void {
	if ( is_admin() ) {
		include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/admin/class-settings-page.php';
		include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/admin/class-settings-popup-section.php';
		include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/admin/class-settings-badge-section.php';
		include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/admin/class-settings-submit-section.php';
		include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/admin/class-settings-page-shortcodes.php';
		include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/class-user-profile.php';
		include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/admin/class-students-achievements.php';
		include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/admin/class-settings-course-price-metabox.php';
	}

	include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/class-settings.php';
	include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/class-database.php';
	include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/class-post-type.php';
	include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/class-meta-box.php';
	include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/class-achievement.php';
	include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/class-shortcode.php';
	include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/class-widget.php';
	include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/class-trigger.php';
	include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'templates/class-general-template.php';
	include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/class-course-point.php';
	include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/gutenberg/index.php';
}

/**
 * Sets up the autoloader for extra classes, which are not in the src/App directory.
 *
 * @since 2.0.0
 *
 * @return void
 */
function learndash_achievements_extra_autoloading(): void {
	// From https://www.php.net/manual/en/function.glob.php#106595.
	$glob_recursive = function ( string $pattern, int $flags = 0 ) use ( &$glob_recursive ): array {
		$files = glob( $pattern, $flags );
		$files = $files === false ? [] : $files;

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

	foreach ( $glob_recursive( LEARNDASH_ACHIEVEMENTS_DIR . 'src/deprecated/*.php' ) as $file ) {
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
