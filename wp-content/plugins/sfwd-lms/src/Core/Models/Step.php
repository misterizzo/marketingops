<?php
/**
 * This base class provides the easy way to interact with a Course Step.
 *
 * @since 4.21.0
 *
 * @package LearnDash\Core
 */

namespace LearnDash\Core\Models;

use LDLMS_Post_Types;
use LearnDash_Settings_Section;
use WP_User;

/**
 * Step model base class.
 *
 * @since 4.21.0
 */
abstract class Step extends Post {
	/**
	 * Returns a course step permalink.
	 *
	 * @since 4.21.0
	 *
	 * @return string
	 */
	public function get_permalink(): string {
		$nested_urls_enabled = 'yes' === LearnDash_Settings_Section::get_section_setting(
			'LearnDash_Settings_Section_Permalinks',
			'nested_urls'
		);

		if ( $nested_urls_enabled ) {
			$course = $this->get_course();

			if ( $course ) {
				return (string) learndash_get_step_permalink( $this->get_id(), $course->get_id() );
			}
		}

		return (string) get_permalink( $this->get_id() );
	}

	/**
	 * Returns the related course of the step or null if the step is not associated with a course.
	 *
	 * @since 4.21.0
	 *
	 * @return Course|null
	 */
	public function get_course(): ?Course {
		$cached_course = $this->getAttribute( LDLMS_Post_Types::COURSE, false );

		if (
			$cached_course instanceof Course
			|| is_null( $cached_course )
		) {
			return $cached_course;
		}

		$course = Course::find(
			(int) learndash_get_course_id( $this->get_id() )
		);

		$this->set_course( $course );

		/**
		 * Filters a course step's course.
		 *
		 * @since 4.21.0
		 *
		 * @param Course|null $course Course model.
		 * @param Step        $step   Course step model.
		 *
		 * @return Course|null Course model or null if not found.
		 */
		return apply_filters(
			"learndash_model_{$this->get_post_type_key()}_course",
			$course,
			$this
		);
	}

	/**
	 * Sets the related course of the step.
	 *
	 * @since 4.21.0
	 *
	 * @param Course|null $course Course model or null.
	 *
	 * @return void
	 */
	public function set_course( ?Course $course ): void {
		$this->setAttribute( LDLMS_Post_Types::COURSE, $course );
	}

	/**
	 * Returns a flag whether a step is completed.
	 *
	 * @since 4.21.0
	 *
	 * @param WP_User|int|null $user The user ID or WP_User. If null or empty, the current user is used.
	 *
	 * @return bool
	 */
	public function is_complete( $user = null ): bool {
		$user    = $this->map_user( $user );
		$user_id = $user instanceof WP_User ? $user->ID : $user;

		$course = $this->get_course();

		$is_complete = $course && learndash_user_progress_is_step_complete(
			$user_id,
			$course->get_id(),
			$this->get_id(),
		);

		/**
		 * Filters whether the step is completed.
		 *
		 * @since 4.21.0
		 *
		 * @param bool        $is_complete Whether the step is completed.
		 * @param Step        $step        Step model.
		 * @param WP_User|int $user        The WP_User by default or the user ID if a user ID was passed explicitly to the filter's caller.
		 *
		 * @return bool Whether the step is completed.
		 */
		return apply_filters(
			"learndash_model_{$this->get_post_type_key()}_is_complete",
			$is_complete,
			$this,
			$user
		);
	}

	/**
	 * Returns whether the step takes place in an external setting.
	 *
	 * @since 4.21.0
	 *
	 * @return bool
	 */
	public function is_external(): bool {
		$is_external = learndash_course_steps_is_external( $this->get_id() );

		/**
		 * Filters whether the step takes place in an external setting.
		 *
		 * @since 4.21.0
		 *
		 * @param bool $is_external Whether the step takes place in an external setting.
		 * @param Step $step        Step model.
		 *
		 * @return bool Whether the step takes place in an external setting.
		 */
		return apply_filters(
			"learndash_model_{$this->get_post_type_key()}_is_external",
			$is_external,
			$this
		);
	}

	/**
	 * Returns whether the step is offered virtually.
	 *
	 * @since 4.21.0
	 *
	 * @return bool
	 */
	public function is_virtual(): bool {
		$external_type = strtolower( learndash_course_steps_get_external_type( $this->get_id() ) );

		$is_virtual = $external_type === 'virtual';

		/**
		 * Filters whether the step is offered virtually.
		 *
		 * @since 4.21.0
		 *
		 * @param bool $is_attendance_required Whether the step is offered virtually.
		 * @param Step $step                   Step model.
		 *
		 * @return bool Whether the step is offered virtually.
		 */
		return apply_filters(
			"learndash_model_{$this->get_post_type_key()}_is_virtual",
			$is_virtual,
			$this
		);
	}

	/**
	 * Returns whether the step is offered in-person.
	 *
	 * @since 4.21.0
	 *
	 * @return bool
	 */
	public function is_in_person(): bool {
		$external_type = strtolower( learndash_course_steps_get_external_type( $this->get_id() ) );

		$is_in_person = $external_type === 'in-person';

		/**
		 * Filters whether the step is offered in-person.
		 *
		 * @since 4.21.0
		 *
		 * @param bool $is_attendance_required Whether the step is offered in-person.
		 * @param Step $step                   Step model.
		 *
		 * @return bool Whether the step is offered in-person.
		 */
		return apply_filters(
			"learndash_model_{$this->get_post_type_key()}_is_in_person",
			$is_in_person,
			$this
		);
	}

	/**
	 * Returns whether the step requires attendance.
	 *
	 * @since 4.21.0
	 *
	 * @return bool
	 */
	public function is_attendance_required(): bool {
		$is_attendance_required = learndash_course_steps_is_external_attendance_required( $this->get_id() );

		/**
		 * Filters whether attendance is required.
		 *
		 * @since 4.21.0
		 *
		 * @param bool $is_attendance_required Whether attendance is required.
		 * @param Step $step                   Step model.
		 *
		 * @return bool Whether attendance is required.
		 */
		return apply_filters(
			"learndash_model_{$this->get_post_type_key()}_is_attendance_required",
			$is_attendance_required,
			$this
		);
	}

	/**
	 * Returns the timestamp when the step is available.
	 *
	 * @since 4.21.0
	 *
	 * @param WP_User|int|null $user The user ID or WP_User. If null or empty, the current user is used.
	 *
	 * @return int|null Unix timestamp or null if the step is always available.
	 */
	public function get_available_on_date( $user = null ): ?int {
		$user    = $this->map_user( $user );
		$user_id = $user instanceof WP_User ? $user->ID : $user;

		$available_on = null;

		$course = $this->get_course();

		if (
			$course
			&& ! learndash_can_user_bypass(
				$user_id,
				'learndash_course_lesson_not_available',
				[
					'step_id' => $this->get_id(),
					'step'    => $this->get_post(),
				]
			)
		) {
			// Get all parent step IDs.
			// We need to reverse the array to start from the immediate parent step to the root step.

			$step_ids = array_reverse( learndash_course_get_all_parent_step_ids( $course->get_id(), $this->get_id() ) );
			$step_ids = array_merge( [ $this->get_id() ], $step_ids );

			// Loop through all parent steps and the current step to find the first step with a defined availability date.

			foreach ( $step_ids as $step_id ) {
				$available_on = (int) ld_lesson_access_from( $step_id, $user_id, $course->get_id() );

				if ( $available_on > 0 ) {
					break;
				}
			}

			// If the step is always available, set the availability date to null.

			if ( $available_on <= 0 ) {
				$available_on = null;
			}
		}

		/**
		 * Filters the timestamp when the step is available.
		 *
		 * @since 4.21.0
		 *
		 * @param int|null    $available_on Unix timestamp when the step is available or null if the step is always available.
		 * @param Step        $step         Step model.
		 * @param WP_User|int $user         The WP_User by default or the user ID if a user ID was passed explicitly to the filter's caller.
		 *
		 * @return int|null Unix timestamp when the step is available or null if the step is always available.
		 */
		return apply_filters(
			"learndash_model_{$this->get_post_type_key()}_available_on_date",
			$available_on,
			$this,
			$user
		);
	}

	/**
	 * Returns a flag whether a step is a sample.
	 *
	 * @since 4.21.0
	 *
	 * @return bool
	 */
	public function is_sample(): bool {
		/**
		 * Filters whether the step is a sample.
		 *
		 * @since 4.21.0
		 *
		 * @param bool $is_sample Whether the step is a sample.
		 * @param Step $step      Step model.
		 *
		 * @return bool Whether the step is a sample.
		 */
		return apply_filters(
			"learndash_model_{$this->get_post_type_key()}_is_sample",
			learndash_is_sample( $this->get_id() ),
			$this
		);
	}
}
