<?php
/**
 * Functions file for things related to LD topic template.
 *
 * @package LearnDash\Elementor
 */

/**
 * Shows Course content table.
 *
 * @param array $atts Array of shortcode attributes.
 *
 * @return void
 */
function learndash_elementor_show_topic_content_listing( $atts = array() ): void {
	$atts_defaults = array(
		'course_id' => 0,
		'user_id'   => 0,
		'step_id'   => 0,

	);

	$atts = shortcode_atts( $atts_defaults, $atts );

	if ( empty( $atts['course_id'] ) ) {
		return;
	}

	$topic_id   = $atts['step_id'];
	$topic_post = get_post( $atts['step_id'] );
	$user_id    = $atts['user_id'];
	$course_id  = $atts['course_id'];

	if ( ! empty( $user_id ) ) {
		$logged_in = true;
	} else {
		$logged_in = false;
	}

	$lesson_progression_enabled = learndash_lesson_progression_enabled( $course_id );

	$bypass_course_limits_admin_users = learndash_elementor_bypass_course_limits( $user_id, $topic_post->ID, $course_id );

	// For logged in users to allow an override filter.
	/** This filter is documented in themes/ld30/includes/helpers.php */
	$bypass_course_limits_admin_users = apply_filters( 'learndash_prerequities_bypass', $bypass_course_limits_admin_users, $user_id, $course_id, $topic_post );

	/**
	 * Start from includes/class-ld-cpt-instance.php
	 */
	$lesson_id = learndash_course_get_single_parent_step( $course_id, $topic_id );

	$lesson_post                 = get_post( $lesson_id );
	$previous_topic_completed    = learndash_is_previous_complete( $topic_post );
	$previous_lesson_completed   = learndash_is_previous_complete( $lesson_post );
	$previous_incomplete_step_id = learndash_user_progress_get_previous_incomplete_step( $user_id, $course_id, $topic_id );
	$previous_step_completed     = empty( $previous_incomplete_step_id ) || ( ! empty( $previous_incomplete_step_id ) && $previous_incomplete_step_id === $topic_id );

	if ( $bypass_course_limits_admin_users ) {
		$previous_lesson_completed = true;
		$previous_topic_completed  = true;
		// remove_filter( 'learndash_content', 'lesson_visible_after', 1, 2 );
		$show_content = ( $previous_topic_completed && $previous_lesson_completed );
	} else {
		if ( $lesson_progression_enabled ) {
			/** This filter is documented in includes/class-ld-cpt-instance.php */
			$previous_topic_completed = apply_filters( 'learndash_previous_step_completed', learndash_is_previous_complete( $topic_post ), $topic_id, $user_id );
			/** This filter is documented in includes/class-ld-cpt-instance.php */
			$previous_lesson_completed = apply_filters( 'learndash_previous_step_completed', learndash_is_previous_complete( $lesson_post ) && $previous_step_completed, $lesson_post->ID, $user_id );
		} elseif ( learndash_is_sample( $lesson_post ) ) {
			$previous_lesson_completed = true;
			$previous_topic_completed  = true;
		} else {
			$previous_lesson_completed = true;
			$previous_topic_completed  = true;
		}

		if ( ! learndash_elementor_user_step_access_state( 'show_content', $user_id, $topic_id, $course_id ) ) {
			$show_content = false;
		} else {
			$show_content = $previous_lesson_completed;
		}
	}

	$learndash_content = learndash_elementor_user_step_access_state( 'learndash_content', $user_id, $topic_id, $course_id );

	/**
	 * Start from themes/ld30/templates/topic.php
	 */
	?><div class="<?php echo esc_attr( learndash_the_wrapper_class() ); ?>">
	<?php

	$all_quizzes_completed = false;

	if ( $show_content ) {

		$quizzes  = learndash_get_lesson_quiz_list( $topic_post, null, $course_id );
		$quiz_ids = array();

		if ( ! empty( $quizzes ) ) {
			foreach ( $quizzes as $quiz ) {
				$quiz_ids[ $quiz['post']->ID ] = $quiz['post']->ID;
			}
		}

		if ( $lesson_progression_enabled && ( ! $previous_topic_completed || ! $previous_lesson_completed ) ) {
			add_filter( 'comments_array', 'learndash_remove_comments', 1, 2 );
		}

		if ( ! empty( $quiz_ids ) ) {
			$all_quizzes_completed = ! learndash_is_quiz_notcomplete( null, $quiz_ids, false, $course_id );
		} else {
			$all_quizzes_completed = true;
		}

		$topics = learndash_topic_dots( $lesson_id, false, 'array', null, $course_id );

		if ( ! empty( $quizzes ) ) {
			learndash_elementor_get_template_part(
				'quiz/listing.php',
				array(
					'user_id'   => $user_id,
					'course_id' => $course_id,
					'lesson_id' => $lesson_id,
					'quizzes'   => $quizzes,
					'context'   => 'topic',
				),
				true
			);

		}

		if ( learndash_lesson_hasassignments( $topic_post ) && ! empty( $user_id ) ) {

			learndash_elementor_get_template_part(
				'assignment/listing.php',
				array(
					'user_id'          => $user_id,
					'course_step_post' => $topic_post,
					'course_id'        => $course_id,
					'context'          => 'topic',
				),
				true
			);
		}
	} elseif ( $lesson_progression_enabled && ( ! $previous_topic_completed || ! $previous_lesson_completed ) ) {
		if ( ! empty( $previous_incomplete_step_id ) ) {
			$previous_item = get_post( $previous_incomplete_step_id );
		} else {
			$previous_item = learndash_get_previous( $topic_post );

			if ( empty( $previous_item ) ) {
				$previous_item = learndash_get_previous( $lesson_post );
			}
		}

		learndash_elementor_get_template_part(
			'modules/messages/lesson-progression.php',
			array(
				'previous_item' => $previous_item,
				'course_id'     => $course_id,
				'context'       => 'topic',
			),
			true
		);

	} elseif ( ! empty( $learndash_content ) ) {
		echo $learndash_content;
	}

	$can_complete = false;

	if ( $all_quizzes_completed && $logged_in && ! empty( $course_id ) ) :
		/** This filter is documented in themes/ld30/templates/lesson.php */
		$can_complete = apply_filters( 'learndash-lesson-can-complete', true, $topic_id, $course_id, $user_id );
	endif;

	learndash_elementor_get_template_part(
		'modules/course-steps.php',
		array(
			'course_id'             => $course_id,
			'course_step_post'      => $topic_post,
			'all_quizzes_completed' => $all_quizzes_completed,
			'user_id'               => $user_id,
			'course_settings'       => isset( $course_settings ) ? $course_settings : array(),
			'context'               => 'topic',
			'can_complete'          => $can_complete,
		),
		true
	);

	?>
	</div> <!--/.learndash-wrapper-->
	<?php

}
