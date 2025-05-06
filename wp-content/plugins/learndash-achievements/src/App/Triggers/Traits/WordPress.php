<?php
/**
 * WordPress trigger trait file.
 *
 * @since 2.0.0
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements\Triggers\Traits;

/**
 * WordPress trigger trait.
 *
 * @since 2.0.0
 */
trait WordPress {
	/**
	 * Returns trigger category.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_category(): string {
		return 'WordPress';
	}
}
