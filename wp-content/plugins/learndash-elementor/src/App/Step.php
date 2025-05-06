<?php
/**
 * LearnDash step-related integration class.
 *
 * @since 1.0.5
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor;

/**
 * LearnDash step-related integration class.
 *
 * @since 1.0.5
 */
class Step {
	/**
	 * Filter 'learndash_previous_step_completed' hook.
	 *
	 * @since 1.0.5
	 *
	 * @param bool $completed Original step completed value.
	 * @param int  $object_id Step object ID.
	 * @param int  $user_id   WP_User ID.
	 *
	 * @return bool Step completed value.
	 */
	public function filter_previous_step_completed( $completed, $object_id, $user_id ): bool {
		if ( learndash_can_user_bypass( $user_id ) ) {
			$completed = true;
		}

		return $completed;
	}
}
