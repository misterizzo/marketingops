<?php
/**
 * LearnDash Elementor custom LD30 Displays a topic.
 *
 * Available Variables:
 *
 * $course_id                 : (int) ID of the course
 * $course                    : (object) Post object of the course
 * $course_settings           : (array) Settings specific to current course
 * $course_status             : Course Status
 * $has_access                : User has access to course or is enrolled.
 *
 * $courses_options            : Options/Settings as configured on Course Options page
 * $lessons_options            : Options/Settings as configured on Lessons Options page
 * $quizzes_options            : Options/Settings as configured on Quiz Options page
 *
 * $user_id                    : (object) Current User ID
 * $logged_in                  : (true/false) User is logged in
 * $current_user               : (object) Currently logged in user object
 * $quizzes                    : (array) Quizzes Array
 * $post                       : (object) The topic post object
 * $lesson_post                : (object) Lesson post object in which the topic exists
 * $topics                     : (array) Array of Topics in the current lesson
 * $all_quizzes_completed      : (true/false) User has completed all quizzes on the lesson Or, there are no quizzes.
 * $lesson_progression_enabled : (true/false)
 * $show_content               : (true/false) true if lesson progression is disabled or if previous lesson and topic is completed.
 * $previous_lesson_completed  : (true/false) true if previous lesson is completed
 * $previous_topic_completed   : (true/false) true if previous topic is completed
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
?>

<div class="<?php echo esc_attr( learndash_the_wrapper_class() ); ?>">
	<?php
	if ( has_action( 'learndash-topic-before' ) ) {
		_deprecated_hook( 'learndash-topic-before', '1.0.5', 'learndash_topic_before' );

		/**
		 * Fires before the topic
		 *
		 * @deprecated 1.0.5
		 *
		 * @since 1.0.0
		 *
		 * @param int $course_id Course ID.
		 * @param int $user_id   User ID.
		 */
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Deprecated hook.
		do_action( 'learndash-topic-before', get_the_ID(), $course_id, $user_id );
	}

	/**
	 * Fires before the topic
	 *
	 * @since 1.0.5
	 *
	 * @param int $course_id Course ID.
	 * @param int $user_id   User ID.
	 */
	do_action( 'learndash_topic_before', get_the_ID(), $course_id, $user_id );

	/**
	 * If the user needs to complete the previous lesson AND topic display an alert.
	 */

	$sub_context = '';
	if (
		$lesson_progression_enabled
		&& ! learndash_user_progress_is_step_complete( $user_id, $course_id, $post->ID )
	) {
		$previous_item = learndash_get_previous( $post );

		if (
			! $previous_topic_completed
			|| empty( $previous_item )
		) {
			if (
				'on' === learndash_get_setting( $lesson_post->ID, 'lesson_video_enabled' )
				&& ! empty( learndash_get_setting( $lesson_post->ID, 'lesson_video_url' ) )
				&& 'BEFORE' === learndash_get_setting( $lesson_post->ID, 'lesson_video_shown' )
				&& ! learndash_video_complete_for_step( $lesson_post->ID, $course_id, $user_id )
			) {
				$sub_context = 'video_progression';
			}
		}
	}

	if (
		$lesson_progression_enabled
		&& (
			! empty( $sub_context )
			|| ! $previous_topic_completed
			|| ! $previous_lesson_completed
		)
		&& ! learndash_can_user_bypass()
	) {
		if (
			! learndash_is_sample( $post )
			|| (
				learndash_is_sample( $post )
				&& true === (bool) $has_access
			)
		) {
			if ( 'video_progression' === $sub_context ) {
				$previous_item = $lesson_post;
			} else {
				$previous_item_id = learndash_user_progress_get_previous_incomplete_step( $user_id, $course_id, $post->ID );

				if (
					! empty( $previous_item_id )
					&& $previous_item_id !== $post->ID
				) {
					$previous_item = get_post( $previous_item_id );
				}
			}

			if (
				isset( $previous_item )
				&& ! empty( $previous_item )
			) {
				$show_content = false;

				learndash_elementor_get_template_part(
					'modules/messages/lesson-progression.php',
					array(
						'previous_item' => $previous_item,
						'course_id'     => $course_id,
						'context'       => 'topic',
						'sub_context'   => $sub_context,
					),
					true
				);
			}
		}
	}

	if ( $show_content ) {
		learndash_elementor_get_template_part(
			'modules/tabs.php',
			array(
				'course_id' => $course_id,
				'post_id'   => get_the_ID(),
				'user_id'   => $user_id,
				'content'   => $content,
				'materials' => $materials,
				'context'   => 'topic',
			),
			true
		);
	}

	if ( has_action( 'learndash-topic-after' ) ) {
		_deprecated_hook( 'learndash-topic-after', '1.0.5', 'learndash_topic_after' );

		/**
		 * Fires after the topic.
		 *
		 * @deprecated 1.0.5
		 *
		 * @since 1.0.0
		 *
		 * @param int $post_id   Current Post ID.
		 * @param int $course_id Course ID.
		 * @param int $user_id   User ID.
		 */
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Deprecated hook.
		do_action( 'learndash-topic-after', get_the_ID(), $course_id, $user_id );
	}

	/**
	 * Fires after the topic.
	 *
	 * @since 1.0.5
	 *
	 * @param int $post_id   Current Post ID.
	 * @param int $course_id Course ID.
	 * @param int $user_id   User ID.
	 */
	do_action( 'learndash_topic_after', get_the_ID(), $course_id, $user_id );

	learndash_load_login_modal_html();
	?>
</div><!--/.learndash-wrapper-->
