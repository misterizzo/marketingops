<?php
/**
 * Plugin Name: LearnDash LMS - Notifications
 * Plugin URI: https://www.learndash.com/add-on/learndash-notifications/
 * Description:    Create and send notification emails to the users.
 * Version: 1.5.3
 * Author: LearnDash
 * Author URI: https://www.learndash.com/
 * Text Domain: learndash-notifications
 * Domain Path: languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// Check if class name already exists
if ( ! class_exists( 'LearnDash_Notifications' ) ) :

	/**
	 * Main class
	 *
	 * @since 1.0
	 */
	final class LearnDash_Notifications {

		/**
		 * The one and only true LearnDash_Notifications instance
		 *
		 * @since 1.0
		 * @access private
		 * @var object $instance
		 */
		private static $instance;

		/**
		 * Instantiate the main class
		 *
		 * This function instantiates the class, initialize all functions and return the object.
		 *
		 * @return object The one and only true LearnDash_Notifications instance.
		 * @since 1.0
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ( ! self::$instance instanceof LearnDash_Notifications ) ) {
				self::$instance = new LearnDash_Notifications();
				self::$instance->setup_constants();
				include_once LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'src/class-map.php';
				add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

				self::$instance->check_dependency();

				add_action(
					'plugins_loaded',
					function () {
						if (
						LearnDash_Dependency_Check_LD_Notifications::get_instance()->check_dependency_results()
						|| php_sapi_name() === 'cli'
						) {
							self::$instance->includes();
							self::$instance->includes_after_plugins_loaded();
						}
					}
				);
				add_action( 'init', array( self::$instance, 'init' ) );
			}

			return self::$instance;
		}

		/**
		 * Getting all registered notifications, and place the sniffing here
		 */
		public function init() {
			$triggers = array(
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
			);
			foreach ( $triggers as $trigger ) {
				$class = new $trigger();
				$class->listen();
			}
		}

		/**
		 * Function for setting up constants
		 *
		 * This function is used to set up constants used throughout the plugin.
		 *
		 * @since 1.0
		 */
		public function setup_constants() {

			// Plugin version.
			if ( ! defined( 'LEARNDASH_NOTIFICATIONS_VERSION' ) ) {
				define( 'LEARNDASH_NOTIFICATIONS_VERSION', '1.5.3' );
			}

			// Plugin file.
			if ( ! defined( 'LEARNDASH_NOTIFICATIONS_FILE' ) ) {
				define( 'LEARNDASH_NOTIFICATIONS_FILE', __FILE__ );
			}

			// Plugin folder path.
			if ( ! defined( 'LEARNDASH_NOTIFICATIONS_PLUGIN_PATH' ) ) {
				define( 'LEARNDASH_NOTIFICATIONS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
			}

			// Plugin folder URL.
			if ( ! defined( 'LEARNDASH_NOTIFICATIONS_PLUGIN_URL' ) ) {
				define( 'LEARNDASH_NOTIFICATIONS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}
		}

		/**
		 * Load text domain used for translation
		 *
		 * This function loads mo and po files used to translate text strings used throughout the
		 * plugin.
		 *
		 * @since 1.0
		 */
		public function load_textdomain() {

			// Set filter for plugin language directory.
			$lang_dir = dirname( plugin_basename( LEARNDASH_NOTIFICATIONS_FILE ) ) . '/languages/';
			$lang_dir = apply_filters( 'learndash_notifications_languages_directory', $lang_dir );

			// Load plugin translation file.
			load_plugin_textdomain( 'learndash-notifications', false, $lang_dir );

			// Include support for new LearnDash Translation logic in v2.5.5
			// This needs to load after LearnDash core because it depends on the LearnDash_Settings_Section and LearnDash_Translations classes.
			include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'includes/admin/class-ld-translations-notifications.php';
		}

		/**
		 * Check and set plugin dependencies
		 *
		 * @return void
		 */
		public function check_dependency() {
			include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'includes/class-dependency-check.php';

			LearnDash_Dependency_Check_LD_Notifications::get_instance()->set_dependencies(
				array(
					'sfwd-lms/sfwd_lms.php' => array(
						'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
						'class'       => 'SFWD_LMS',
						'min_version' => '3.2.0',
					),
				)
			);

			LearnDash_Dependency_Check_LD_Notifications::get_instance()->set_message(
				__( 'LearnDash LMS - Notifications Add-on requires the following plugin(s) to be active:', 'learndash-notifications' )
			);
		}

		/**
		 * Includes all necessary PHP files
		 *
		 * This function is responsible for including all necessary PHP files.
		 *
		 * @since  0.1
		 */
		public function includes() {
			include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'includes/functions.php';
			include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'includes/logger.php';
			include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'includes/cron.php';
			include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'includes/deactivation.php';
			include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'includes/database.php';
			include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'includes/meta-box.php';
			include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'includes/notification.php';
			include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'includes/post-type.php';
			include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'includes/shortcode.php';
			include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'includes/tools.php';
			include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'includes/update.php';
			include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'includes/user.php';
			include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'includes/subscription-manager.php';
		}

		public function includes_after_plugins_loaded() {
			if ( is_admin() ) {
				include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'includes/admin/class-settings.php';
				include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'includes/admin/class-status-page.php';
				include LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'includes/admin/class-logs-page.php';
			}
		}
	}

endif; // End if class exists check

/**
 * The main function for returning instance
 *
 * @return object The one and only true instance.
 * @since 1.0
 */
function learndash_notifications() {
	return LearnDash_Notifications::instance();
}

require dirname( __FILE__ ) . '/includes/activation.php';
// register it here as if we call it inside, it never ork
register_activation_hook( __FILE__, 'learndash_notifications_activate' );
register_deactivation_hook( __FILE__, 'learndash_notifications_deactivate' );
// Run plugin
learndash_notifications();
