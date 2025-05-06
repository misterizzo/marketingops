<?php
/**
 * Deprecated functions file.
 *
 * @package LearnDash\Achievements\Deprecated
 */

/**
 * Gets LearnDash Achievements plugin instance.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @return LearnDash_Achievements
 */
function learndash_achievements() {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	return LearnDash_Achievements::instance();
}
