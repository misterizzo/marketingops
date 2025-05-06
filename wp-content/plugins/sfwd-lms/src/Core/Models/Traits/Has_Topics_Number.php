<?php
/**
 * Trait for models that can retrieve a number of child topics.
 *
 * @since 4.21.0
 *
 * @package LearnDash\Core
 */

namespace LearnDash\Core\Models\Traits;

use LDLMS_Post_Types;
use LearnDash\Core\Models\Course;
use LearnDash\Core\Models\Step;

/**
 * Trait for models that can retrieve a number of child topics.
 *
 * @since 4.21.0
 */
trait Has_Topics_Number {
	use Has_Steps;

	/**
	 * Returns the total number of topics associated with this model, including those nested multiple levels deep.
	 *
	 * @since 4.21.0
	 *
	 * @return int
	 */
	public function get_topics_number(): int {
		/**
		 * Filters topics number associated with this model.
		 *
		 * @since 4.21.0
		 *
		 * @param int         $number Number of topics.
		 * @param Course|Step $model  Model with topics.
		 *
		 * @return int Number of nested topics.
		 */
		return apply_filters(
			"learndash_model_{$this->get_post_type_key()}_topics_number",
			$this->get_steps_number(
				LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::TOPIC )
			),
			$this
		);
	}
}
