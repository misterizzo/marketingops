<?php
/**
 * Deprecated LearnDash_Achievements class file.
 *
 * @since 1.0
 * @deprecated 2.0.0
 *
 * @package LearnDash\Achievements\Deprecated
 */

use LearnDash_Dependency_Check_LD_Achievements;

_deprecated_file( __FILE__, '2.0.0' );

if ( ! class_exists( 'LearnDash_Achievements' ) ) {
	/**
	 * Deprecated LearnDash_Achievements.
	 *
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	class LearnDash_Achievements {
		/**
		 * Plugin instance.
		 *
		 * @since 1.0
		 * @deprecated 2.0.0
		 *
		 * @var self
		 */
		private static $instance;

		/**
		 * Get plugin instance.
		 *
		 * @since 1.0
		 * @deprecated 2.0.0
		 *
		 * @return self
		 */
		public static function instance() {
			_deprecated_function( __METHOD__, '2.0.0' );

			if ( ! isset( self::$instance ) || ! ( self::$instance instanceof LearnDash_Achievements ) ) {
				self::$instance = new LearnDash_Achievements();
				self::$instance->define_constants();

				add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

				self::$instance->check_dependency();

				// Big priority required so that LearnDash is loaded first.
				add_action(
					'plugins_loaded',
					function () {
						if ( LearnDash_Dependency_Check_LD_Achievements::get_instance()->check_dependency_results() ) {
							self::$instance->includes();
						}
					},
					100
				);
			}

			return self::$instance;
		}

		/**
		 * Function for setting up constants
		 *
		 * This function is used to set up constants used throughout the plugin.
		 *
		 * @since 1.0
		 * @deprecated 2.0.0
		 */
		public function define_constants() {
			_deprecated_function( __METHOD__, '2.0.0' );

			// Plugin version.
			if ( ! defined( 'LEARNDASH_ACHIEVEMENTS_VERSION' ) ) {
				define( 'LEARNDASH_ACHIEVEMENTS_VERSION', '1.2' );
			}

			// Plugin file.
			if ( ! defined( 'LEARNDASH_ACHIEVEMENTS_FILE' ) ) {
				define( 'LEARNDASH_ACHIEVEMENTS_FILE', __FILE__ );
			}

			// Plugin folder path.
			if ( ! defined( 'LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH' ) ) {
				define( 'LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
			}

			// Plugin folder URL.
			if ( ! defined( 'LEARNDASH_ACHIEVEMENTS_PLUGIN_URL' ) ) {
				define( 'LEARNDASH_ACHIEVEMENTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}
		}

		/**
		 * Check plugin dependencies
		 *
		 * @since 1.0
		 * @deprecated 2.0.0
		 *
		 * @return void
		 */
		public function check_dependency() {
			_deprecated_function( __METHOD__, '2.0.0' );

			include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/class-dependency-check.php';

			LearnDash_Dependency_Check_LD_Achievements::get_instance()->set_dependencies(
				array(
					'sfwd-lms/sfwd_lms.php' => array(
						'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
						'class'       => 'SFWD_LMS',
						'min_version' => '3.0.0',
					),
				)
			);

			LearnDash_Dependency_Check_LD_Achievements::get_instance()->set_message(
				__( 'LearnDash LMS - Achievements Add-on requires the following plugin(s) to be active:', 'learndash-achievements' )
			);
		}

		/**
		 * Load text domain used for translation
		 *
		 * This function loads mo and po files used to translate text strings used throughout the
		 * plugin.
		 *
		 * @since 1.0
		 * @deprecated 2.0.0
		 */
		public function load_textdomain() {
			_deprecated_function( __METHOD__, '2.0.0' );

			// Set filter for plugin language directory.
			$lang_dir = dirname( plugin_basename( LEARNDASH_ACHIEVEMENTS_FILE ) ) . '/languages/';
			$lang_dir = apply_filters( 'learndash_achievements_languages_directory', $lang_dir );

			// Load plugin translation file.
			load_plugin_textdomain( 'learndash-achievements', false, $lang_dir );
		}

		/**
		 * Includes all necessary PHP files
		 *
		 * This function is responsible for including all necessary PHP files.
		 *
		 * @since 1.0
		 * @deprecated 2.0.0
		 */
		public function includes() {
			_deprecated_function( __METHOD__, '2.0.0' );

			if ( is_admin() ) {
				include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/admin/class-settings-page.php';
				include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/admin/class-settings-general-section.php';
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
	}
}
