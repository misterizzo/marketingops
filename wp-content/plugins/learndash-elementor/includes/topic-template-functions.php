<?php
/**
 * Shows Course content table
 *
 * @param array $atts Array of shortcode attributes.
 */
function learndash_elementor_show_topic_content_listing( $atts = array() ) {
	global $course_pager_results;

	$atts_defaults = array(
		'course_id' => 0,
		'user_id'   => 0,
		'step_id'   => 0,

	);
	$atts = shortcode_atts( $atts_defaults, $atts );
	if ( empty( $atts['course_id'] ) ) {
		return '';
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

	if ( $lesson_progression_enabled ) {
		$lesson_post               = get_post( $lesson_id );
		$previous_topic_completed  = is_previous_complete( $topic_post );
		$previous_lesson_completed = is_previous_complete( $lesson_post );

		if ( ( learndash_is_admin_user( $user_id ) ) && ( $bypass_course_limits_admin_users ) ) {
			$previous_lesson_completed = true;
			$previous_topic_completed  = true;
			remove_filter( 'learndash_content', 'lesson_visible_after', 1, 2 );
		} else {

			/** This filter is documented in includes/class-ld-cpt-instance.php */
			$previous_topic_completed = apply_filters( 'learndash_previous_step_completed', is_previous_complete( $topic_post ), $topic_id, $user_id );
			/** This filter is documented in includes/class-ld-cpt-instance.php */
			$previous_lesson_completed = apply_filters( 'learndash_previous_step_completed', is_previous_complete( $lesson_post ), $lesson_post->ID, $user_id );

			if ( learndash_is_sample( $lesson_post ) ) {
				$previous_lesson_completed = true;
				$previous_topic_completed  = true;
			}
		}
		$show_content = ( $previous_topic_completed && $previous_lesson_completed );

	} else {
		$previous_topic_completed  = true;
		$previous_lesson_completed = true;
		$show_content              = true;
	}

	$quizzes = learndash_get_lesson_quiz_list( $topic_post, null, $course_id );
	$quizids = array();

	if ( ! empty( $quizzes ) ) {
		foreach ( $quizzes as $quiz ) {
			$quizids[ $quiz['post']->ID ] = $quiz['post']->ID;
		}
	}

	if ( $lesson_progression_enabled && ( ! $previous_topic_completed || ! $previous_lesson_completed ) ) {
		add_filter( 'comments_array', 'learndash_remove_comments', 1, 2 );
	}

	if ( ! empty( $quizids ) ) {
		$all_quizzes_completed = ! learndash_is_quiz_notcomplete( null, $quizids, false, $course_id );
	} else {
		$all_quizzes_completed = true;
	}

	$topics = learndash_topic_dots( $lesson_id, false, 'array', null, $course_id );

	/**
	 * Start from themes/ld30/templates/topic.php
	 */
	?><div class="<?php echo esc_attr( learndash_the_wrapper_class() ); ?>">
	<?php

	/**
	 * If the user needs to complete the previous lesson AND topic display an alert
	 */
	if ( $lesson_progression_enabled && ( ! $previous_topic_completed || ! $previous_lesson_completed ) ) :

		$previous_item = learndash_get_previous( $topic_post );

		if ( empty( $previous_item ) ) {
			$previous_item = learndash_get_previous( $lesson_post );
		}

		learndash_get_template_part(
			'modules/messages/lesson-progression.php',
			array(
				'previous_item' => $previous_item,
				'course_id'     => $course_id,
				'context'       => 'topic',
			),
			true
		);

	endif;

	if ( $show_content ) :

		if ( ! empty( $quizzes ) ) :

			learndash_get_template_part(
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

		endif;

		if ( lesson_hasassignments( $topic_post ) && ! empty( $user_id ) ) :

			learndash_get_template_part(
				'assignment/listing.php',
				array(
					'user_id'          => $user_id,
					'course_step_post' => $topic_post,
					'course_id'        => $course_id,
					'context'          => 'topic',
				),
				true
			);
		endif;
	endif; // $show_content

	$can_complete = false;

	if ( $all_quizzes_completed && $logged_in && ! empty( $course_id ) ) :
		/** This filter is documented in themes/ld30/templates/lesson.php */
		$can_complete = apply_filters( 'learndash-lesson-can-complete', true, $topic_id, $course_id, $user_id );
	endif;

	learndash_get_template_part(
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
