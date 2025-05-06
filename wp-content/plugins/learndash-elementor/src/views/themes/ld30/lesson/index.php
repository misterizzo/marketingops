<?php
/**
 * LearnDash Elementor custom LD30 Displays a lesson.
 *
 * Available Variables:
 *
 * $course_id                  : (int) ID of the course
 * $course                     : (object) Post object of the course
 * $course_settings            : (array) Settings specific to current course
 * $course_status              : Course Status
 * $has_access                 : User has access to course or is enrolled.
 *
 * $courses_options            : Options/Settings as configured on Course Options page
 * $lessons_options            : Options/Settings as configured on Lessons Options page
 * $quizzes_options            : Options/Settings as configured on Quiz Options page
 *
 * $user_id                    : (object) Current User ID
 * $logged_in                  : (true/false) User is logged in
 * $current_user               : (object) Currently logged in user object
 *
 * $quizzes                    : (array) Quizzes Array
 * $post                       : (object) The lesson post object
 * $topics                     : (array) Array of Topics in the current lesson
 * $all_quizzes_completed      : (true/false) User has completed all quizzes on the lesson Or, there are no quizzes.
 * $lesson_progression_enabled : (true/false)
 * $show_content               : (true/false) true if lesson progression is disabled or if previous lesson is completed.
 * $previous_lesson_completed  : (true/false) true if previous lesson is completed
 * $lesson_settings            : Settings specific to the current lesson.
 *
 * @since 1.0.5
 *
 * @version 1.0.5
 *
 * @package LearnDash\Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$in_focus_mode = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'focus_mode_enabled' );

add_filter( 'comments_array', 'learndash_remove_comments', 1, 2 ); ?>

<div class="<?php echo esc_attr( learndash_the_wrapper_class() ); ?>">
	<?php
	/**
	 * Fires before the lesson.
	 *
	 * @since 1.0.5
	 *
	 * @param int $post_id   Post ID.
	 * @param int $course_id Course ID.
	 * @param int $user_id   User ID.
	 */
	do_action( 'learndash-lesson-before', get_the_ID(), $course_id, $user_id );

	/**
	 * If the user needs to complete the previous lesson display an alert.
	 */
	if (
		isset( $lesson_progression_enabled )
		&& true === (bool) $lesson_progression_enabled
		&& isset( $previous_lesson_completed )
		&& true !== $previous_lesson_completed
	) {
		if (
			! learndash_is_sample( $post )
			|| (
				learndash_is_sample( $post )
				&& true === (bool) $has_access
			)
		) {
			$previous_item_id = learndash_user_progress_get_previous_incomplete_step( $user_id, $course_id, $post->ID );

			if ( ! empty( $previous_item_id ) ) {
				learndash_elementor_get_template_part(
					'modules/messages/lesson-progression.php',
					array(
						'previous_item' => get_post( $previous_item_id ),
						'course_id'     => $course_id,
						'context'       => 'lesson',
						'user_id'       => $user_id,
					),
					true
				);
			}
		}
	}

	if ( $show_content ) {
		/**
		 * Content and/or tabs.
		 */
		learndash_elementor_get_template_part(
			'modules/tabs.php',
			array(
				'course_id' => $course_id,
				'post_id'   => get_the_ID(),
				'user_id'   => $user_id,
				'content'   => $content,
				'materials' => $materials,
				'context'   => 'lesson',
			),
			true
		);
	}

	/**
	 * Fires after the lesson.
	 *
	 * @since 1.0.5
	 *
	 * @param int $post_id   Post ID.
	 * @param int $course_id Course ID.
	 * @param int $user_id   User ID.
	 */
	do_action( 'learndash-lesson-after', get_the_ID(), $course_id, $user_id );
	learndash_load_login_modal_html();
	?>

</div><!--/.learndash-wrapper-->
