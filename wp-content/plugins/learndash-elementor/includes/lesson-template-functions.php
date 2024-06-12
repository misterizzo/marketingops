<?php
/**
 * Shows Course content table
 *
 * @param array $atts Array of shortcode attributes.
 */
function learndash_elementor_show_lesson_content_listing( $atts = array() ) {
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

	$lesson_id   = $atts['step_id'];
	$lesson_post = get_post( $atts['step_id'] );
	$user_id     = $atts['user_id'];
	$course_id   = $atts['course_id'];

	if ( ! empty( $user_id ) ) {
		$logged_in = true;
	} else {
		$logged_in = false;
	}

	$lesson_progression_enabled       = learndash_lesson_progression_enabled( $course_id );
	$bypass_course_limits_admin_users = learndash_elementor_bypass_course_limits( $user_id, $lesson_post->ID, $course_id );

	// For logged in users to allow an override filter.
	/** This filter is documented in themes/ld30/includes/helpers.php */
	$bypass_course_limits_admin_users = apply_filters( 'learndash_prerequities_bypass', $bypass_course_limits_admin_users, $user_id, $course_id, $lesson_post );

	/**
	 * Start from includes/class-ld-cpt-instance.php
	 */
	$show_content = false;
	if ( $lesson_progression_enabled ) {

		if ( ( learndash_is_admin_user( $user_id ) ) && ( $bypass_course_limits_admin_users ) ) {
			$previous_lesson_completed = true;
			remove_filter( 'learndash_content', 'lesson_visible_after', 1, 2 );
		} else {
			/**
			 * Filters whether the previous step for the course is completed or not.
			 *
			 * @param boolean $prevoius_complete Whether the previous state is completed or not.
			 * @param int     $lesson_id Post ID.
			 * @param int     $user_id User ID.
			 */
			$previous_lesson_completed = apply_filters( 'learndash_previous_step_completed', is_previous_complete( $lesson_post ), $lesson_id, $user_id );

			if ( learndash_is_sample( $lesson_post ) ) {
				$previous_lesson_completed = true;
			}
		}
		$show_content = $previous_lesson_completed;

	} else {
		$show_content              = true;
		$previous_lesson_completed = true;
	}

	$lesson_settings = learndash_get_setting( $lesson_post );
	$quizzes         = learndash_get_lesson_quiz_list( $lesson_post, null, $course_id );
	$quizids         = array();

	if ( ! empty( $quizzes ) ) {
		foreach ( $quizzes as $quiz ) {
			$quizids[ $quiz['post']->ID ] = $quiz['post']->ID;
		}
	}

	if ( $lesson_progression_enabled && ! $previous_lesson_completed ) {
		add_filter( 'comments_array', 'learndash_remove_comments', 1, 2 );
	}

	$topics = learndash_topic_dots( $lesson_id, false, 'array', null, $course_id );
	if ( ! empty( $topics ) ) {
		$topic_pager_args = array(
			'course_id' => $course_id,
			'lesson_id' => $lesson_id,
		);
		$topics           = learndash_process_lesson_topics_pager( $topics, $topic_pager_args );
	}

	if ( ! empty( $quizids ) ) {
		$all_quizzes_completed = ! learndash_is_quiz_notcomplete( null, $quizids, false, $course_id );
	} else {
		$all_quizzes_completed = true;
	}

	/**
	 * Start from themes/ld30/templates/lesson.php
	 */
	?><div class="<?php echo esc_attr( learndash_the_wrapper_class() ); ?>">
	<?php

	/**
	 * If the user needs to complete the previous lesson display an alert
	 */
	if ( $lesson_progression_enabled && ! $previous_lesson_completed ) :

		$previous_item = learndash_get_previous( $lesson_post );

		learndash_get_template_part(
			'modules/messages/lesson-progression.php',
			array(
				'previous_item' => $previous_item,
				'course_id'     => $course_id,
				'context'       => 'topic',
				'user_id'       => $user_id,
			),
			true
		);

	endif;

	if ( $show_content ) :
		/**
		 * Display Lesson Assignments
		 */
		$bypass_course_limits_admin_users = learndash_elementor_bypass_course_limits( $user_id, $lesson_post->ID, $course_id );

		if ( lesson_hasassignments( $lesson_post ) && ! empty( $user_id ) ) :
			if ( ( learndash_lesson_progression_enabled() && learndash_lesson_topics_completed( $lesson_id ) ) || ! learndash_lesson_progression_enabled() || $bypass_course_limits_admin_users ) :
				/** This filter is documented in themes/ld30/templates/lesson.php */
				do_action( 'learndash-lesson-assignment-before', $lesson_id, $course_id, $user_id );

				learndash_get_template_part(
					'assignment/listing.php',
					array(
						'course_step_post' => $lesson_post,
						'user_id'          => $user_id,
						'course_id'        => $course_id,
					),
					true
				);

				/** This filter is documented in themes/ld30/templates/lesson.php */
				do_action( 'learndash-lesson-assignment-after', $lesson_id, $course_id, $user_id );

			endif;
		endif;

		/**
		 * Lesson Topics or Quizzes
		 */
		if ( ! empty( $topics ) || ! empty( $quizzes ) ) :

			/** This filter is documented in themes/ld30/templates/lesson.php */
			do_action( 'learndash-lesson-content-list-before', $lesson_id, $course_id, $user_id );

			$lesson = array(
				'post' => $lesson_post,
			);
			?>

			<div class="ld-lesson-topic-list">
			<?php
			learndash_get_template_part(
				'lesson/listing.php',
				array(
					'course_id' => $course_id,
					'lesson'    => $lesson,
					'topics'    => $topics,
					'quizzes'   => $quizzes,
					'user_id'   => $user_id,
				),
				true
			);
			?>
			</div>

				<?php
				/** This filter is documented in themes/ld30/templates/lesson.php */
				do_action( 'learndash-lesson-content-list-after', $lesson_id, $course_id, $user_id );
		endif;

	endif; // end $show_content.

	/**
	 * Set a variable to switch the next button to complete button
	 *
	 * @var $can_complete [bool] - can the user complete this or not?
	 */
	$can_complete = false;

	if ( $all_quizzes_completed && $logged_in && ! empty( $course_id ) ) :
		/** This filter is documented in themes/ld30/templates/lesson.php */
		$can_complete = apply_filters( 'learndash-lesson-can-complete', true, $lesson_id, $course_id, $user_id );
	endif;

	learndash_get_template_part(
		'modules/course-steps.php',
		array(
			'course_id'        => $course_id,
			'course_step_post' => $lesson_post,
			'user_id'          => $user_id,
			'course_settings'  => isset( $course_settings ) ? $course_settings : array(),
			'can_complete'     => $can_complete,
			'context'          => 'lesson',
		),
		true
	);

	?>
	</div> <!--/.learndash-wrapper-->
	<?php
}
