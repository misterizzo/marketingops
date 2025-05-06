<?php
/**
 * Entry file for composer files autoloader.
 *
 * @package LearnDash\Elementor
 */

use LearnDash\Elementor\Dependency_Checker;

if ( ! defined( 'ABSPATH' ) ) {
	return; // Can't use exit as it will break CI and composer vendor scripts.
}

add_action(
	'plugins_loaded',
	function() {
		if ( ! Dependency_Checker::get_instance()->check_dependency_results() ) {
			return;
		}

		require_once LEARNDASH_ELEMENTOR_PLUGIN_DIR . 'src/functions/general.php';
		require_once LEARNDASH_ELEMENTOR_PLUGIN_DIR . 'src/functions/course.php';
		require_once LEARNDASH_ELEMENTOR_PLUGIN_DIR . 'src/functions/lesson.php';
		require_once LEARNDASH_ELEMENTOR_PLUGIN_DIR . 'src/functions/topic.php';
	}
);
