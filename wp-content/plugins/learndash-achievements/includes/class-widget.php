<?php
/**
 * Widget class file.
 *
 * @since 1.0
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget class.
 *
 * @since 1.0
 */
class Widget {


	/**
	 * Init function
	 */
	public static function init() {
		self::includes();
		add_action( 'widgets_init', array( __CLASS__, 'register_widgets' ) );
	}

	/**
	 * Including files.
	 */
	public static function includes() {
		include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/widget/class-leaderboard-widget.php';
		include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'includes/widget/class-achievements-widget.php';
	}

	/**
	 * Register the widgets.
	 */
	public static function register_widgets() {
		register_widget( 'LearnDash\Achievements\Widget\Leaderboard' );
		register_widget( 'LearnDash\Achievements\Widget\Achievements' );
	}
}

Widget::init();
