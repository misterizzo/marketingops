<?php
/**
 * Set up LearnDash Dependency Check
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @package LearnDash\Achievements
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

_deprecated_file(
	__FILE__,
	'2.0.0',
	esc_html(
		LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'src/deprecated/LearnDash_Dependency_Check_LD_Achievements.php'
	)
);

require_once LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'src/deprecated/LearnDash_Dependency_Check_LD_Achievements.php';
