<?php
/**
 * This class provides the easy way to operate a lesson.
 *
 * @since 4.6.0
 *
 * @package LearnDash\Core
 */

namespace LearnDash\Core\Models;

use LDLMS_Post_Types;

/**
 * Lesson model class.
 *
 * @since 4.6.0
 */
class Lesson extends Step {
	use Traits\Has_Materials;
	use Traits\Has_Quizzes;
	use Traits\Has_Steps;
	use Traits\Has_Topics_Number;

	/**
	 * Returns allowed post types.
	 *
	 * @since 4.6.0
	 *
	 * @return string[]
	 */
	public static function get_allowed_post_types(): array {
		return array(
			LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::LESSON ),
		);
	}

	/**
	 * Returns topics that are a direct child of this model.
	 *
	 * @since 4.21.0
	 *
	 * @param int $limit  Optional. Limit. Default 0.
	 * @param int $offset Optional. Offset. Default 0.
	 *
	 * @return Topic[]
	 */
	public function get_topics( int $limit = 0, int $offset = 0 ): array {
		/**
		 * Topics
		 *
		 * @var Topic[] $topics
		 */
		$topics = $this->get_steps(
			LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::TOPIC ),
			$limit,
			$offset
		);

		$course = $this->get_course();

		foreach ( $topics as $topic ) {
			$topic->set_course( $course ); // This is used to optimize subsequent calls to $topic->get_course().
		}

		/**
		 * Filters direct child topics.
		 *
		 * @since 4.21.0
		 *
		 * @param Topic[] $topics Topics.
		 * @param int     $limit  Limit. Default 0.
		 * @param int     $offset Offset. Default 0.
		 * @param Lesson  $lesson Lesson model.
		 *
		 * @return Topic[] Topics.
		 */
		return apply_filters(
			'learndash_model_lesson_topics',
			$topics,
			$limit,
			$offset,
			$this
		);
	}

	/**
	 * Returns true if a lesson has steps, otherwise false.
	 *
	 * @since 4.21.0
	 *
	 * @return bool
	 */
	public function has_steps(): bool {
		/**
		 * Filters whether a lesson has steps.
		 *
		 * @since 4.21.0
		 *
		 * @param bool   $has_steps Whether a lesson has steps.
		 * @param Lesson $lesson    Lesson model.
		 *
		 * @return bool Whether a lesson has steps.
		 */
		return apply_filters(
			'learndash_model_lesson_has_steps',
			$this->get_topics_number() > 0 || $this->get_quizzes_number() > 0,
			$this
		);
	}
}
