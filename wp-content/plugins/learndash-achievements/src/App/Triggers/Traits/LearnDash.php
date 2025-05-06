<?php
/**
 * LearnDash trigger trait file.
 *
 * @since 2.0.0
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements\Triggers\Traits;

/**
 * LearnDash trigger trait.
 *
 * @since 2.0.0
 */
trait LearnDash {
	/**
	 * Returns trigger category.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_category(): string {
		return 'LearnDash';
	}
}
