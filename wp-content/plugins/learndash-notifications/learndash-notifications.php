<?php
/**
 * Plugin Name: LearnDash LMS - Notifications
 * Plugin URI: https://www.learndash.com/add-on/learndash-notifications/
 * Description: Create and send notification emails to the users.
 * Version: 1.6.5
 * Requires PHP: 7.4
 * Requires at least: 6.1
 * Tested up to: 6.7.2
 * Author: LearnDash
 * Author URI: https://www.learndash.com/
 * Text Domain: learndash-notifications
 * Domain Path: languages
 *
 * @package LearnDash\Notifications
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LEARNDASH_NOTIFICATIONS_VERSION', '1.6.5' );
define( 'LEARNDASH_NOTIFICATIONS_FILE', __FILE__ );
define( 'LEARNDASH_NOTIFICATIONS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'LEARNDASH_NOTIFICATIONS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LEARNDASH_NOTIFICATIONS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'vendor-prefixed/autoload.php';

use LearnDash\Core\Autoloader;
use LearnDash\Notifications\Provider;
use LearnDash\Notifications\Utilities\Dependency_Checker;

Dependency_Checker::get_instance()->set_dependencies(
	[
		'sfwd-lms/sfwd_lms.php' => [
			'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
			'class'       => 'SFWD_LMS',
			'min_version' => '4.7.0',
		],
	]
);

Dependency_Checker::get_instance()->set_message(
	esc_html__( 'LearnDash LMS - Notifications add-on requires the following plugin(s) be active:', 'learndash-notifications' )
);

add_action(
	'plugins_loaded',
	function () {
		if ( ! Dependency_Checker::get_instance()->check_dependency_results() ) {
			return;
		}

		learndash_notifications_extra_autoloading();

		learndash_register_provider( Provider::class );

		learndash_notifications_include();
	}
);

require_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'includes/activation.php';
require_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'includes/deactivation.php';

register_activation_hook( __FILE__, 'learndash_notifications_activate' );
register_deactivation_hook( __FILE__, 'learndash_notifications_deactivate' );

/**
 * Setup the autoloader for extra classes, which are not in the src/Notifications directory.
 *
 * @since 1.6.3
 * @since 1.6.4 Added namespaced classes support.
 *
 * @return void
 */
function learndash_notifications_extra_autoloading(): void {
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

	foreach ( $glob_recursive( LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'src/deprecated/*.php' ) as $file ) {
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

/**
 * Include necessary files for the plugin.
 *
 * @since 1.6.3
 *
 * @return void
 */
function learndash_notifications_include(): void {
	// Register autoloader and init triggers.

	require_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'src/class-map.php';

	$triggers = [
		\LearnDash_Notification\Trigger\Enroll_Group::class,
		\LearnDash_Notification\Trigger\Enroll_Course::class,
		\LearnDash_Notification\Trigger\Complete_Course::class,
		\LearnDash_Notification\Trigger\Complete_Lesson::class,
		\LearnDash_Notification\Trigger\Drip_Lesson_Available::class,
		\LearnDash_Notification\Trigger\Complete_Topic::class,
		\LearnDash_Notification\Trigger\Quiz_Passed::class,
		\LearnDash_Notification\Trigger\Quiz_Failed::class,
		\LearnDash_Notification\Trigger\Quiz_Submitted::class,
		\LearnDash_Notification\Trigger\Quiz_Completed::class,
		\LearnDash_Notification\Trigger\Essay_Submitted::class,
		\LearnDash_Notification\Trigger\Essay_Graded::class,
		\LearnDash_Notification\Trigger\Assignment_Uploaded::class,
		\LearnDash_Notification\Trigger\Assignment_Approved::class,
		\LearnDash_Notification\Trigger\User_Login_Track::class,
		\LearnDash_Notification\Trigger\Before_Course_Expire::class,
		\LearnDash_Notification\Trigger\After_Course_Expire::class,
	];

	foreach ( $triggers as $trigger ) {
		$class = new $trigger();
		$class->listen();
	}

	// Include general files.

	include_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'includes/functions.php';
	include_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'includes/logger.php';
	include_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'includes/cron.php';
	include_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'includes/database.php';
	include_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'includes/meta-box.php';
	include_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'includes/notification.php';
	include_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'includes/post-type.php';
	include_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'includes/shortcode.php';
	include_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'includes/tools.php';
	include_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'includes/update.php';
	include_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'includes/user.php';
	include_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'includes/subscription-manager.php';
	include_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'includes/ajax.php';

	if ( is_admin() ) {
		include_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'includes/admin/class-settings.php';
		include_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'includes/admin/class-status-page.php';
		include_once LEARNDASH_NOTIFICATIONS_PLUGIN_DIR . 'includes/admin/class-ld-translations-notifications.php';
	}
}
