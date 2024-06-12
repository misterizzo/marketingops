<?php
use Elementor\TemplateLibrary\Source_Local;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shows Course content table
 *
 * @param array $atts Array of shortcode attributes.
 */
function learndash_elementor_show_course_content_listing( $atts = array() ) {
	global $course_pager_results;

	$atts_defaults = array(
		'course_id' => 0,
		'user_id'   => 0,
	);
	$atts          = shortcode_atts( $atts_defaults, $atts );
	if ( empty( $atts['course_id'] ) ) {
		return '';
	}

	$course_id = $atts['course_id'];
	$user_id   = $atts['user_id'];

	/**
	 * Start from includes/class-ld-cpt-instance.php
	 */

	$has_access    = sfwd_lms_has_access( $course_id, $user_id );
	$course_status = learndash_course_status( $course_id, null );
	$course_meta   = get_post_meta( $course_id, '_sfwd-courses', true );
	if ( ( ! $course_meta ) || ( ! is_array( $course_meta ) ) ) {
		$course_meta = array();
	}
	if ( ! isset( $course_meta['sfwd-courses_course_disable_content_table'] ) ) {
		$course_meta['sfwd-courses_course_disable_content_table'] = false;
	}

	$lesson_progression_enabled = false;
	if ( ! empty( $course_id ) ) {
		$lesson_progression_enabled = learndash_lesson_progression_enabled( $course_id );
	}

	$lessons = learndash_get_course_lessons_list( $course_id );

	// For now no paginiation on the course quizzes. Can't think of a scenario where there will be more
	// than the pager count.
	$quizzes = learndash_get_course_quiz_list( $course_id );

	$has_course_content = ( ! empty( $lessons ) || ! empty( $quizzes ) );

	$lesson_topics = array();

	$has_topics = false;

	if ( ! empty( $lessons ) ) {
		foreach ( $lessons as $lesson ) {
			$lesson_topics[ $lesson['post']->ID ] = learndash_topic_dots( $lesson['post']->ID, false, 'array', null, $course_id );
			if ( ! empty( $lesson_topics[ $lesson['post']->ID ] ) ) {
				$has_topics = true;

				$topic_pager_args                     = array(
					'course_id' => $course_id,
					'lesson_id' => $lesson['post']->ID,
				);
				$lesson_topics[ $lesson['post']->ID ] = learndash_process_lesson_topics_pager( $lesson_topics[ $lesson['post']->ID ], $topic_pager_args );
			}
		}
	}

	/**
	 * Start from themes/ld30/templates/course.php
	 */

	$has_lesson_quizzes = learndash_30_has_lesson_quizzes( $course_id, $lessons );
	?><div class="<?php echo esc_attr( learndash_the_wrapper_class() ); ?>">
	<?php

	global $course_pager_results;

	/**
	 * Identify if we should show the course content listing
	 *
	 * @var $show_course_content [bool]
	 */
	$show_course_content = ( ! $has_access && 'on' === $course_meta['sfwd-courses_course_disable_content_table'] ? false : true );

	if ( $has_course_content && $show_course_content ) :
		?>
		<div class="ld-item-list ld-lesson-list">
			<div class="ld-section-heading">

			<?php
			/** This filter is documented in themes/ld30/templates/course.php */
			do_action( 'learndash-course-heading-before', $course_id, $user_id );
			?>
				<h2>
			<?php
			printf(
				// translators: placeholder: Course.
				esc_html_x( '%s Content', 'placeholder: Course', 'learndash' ),
				LearnDash_Custom_Label::get_label( 'course' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Method escapes output
			);
			?>
				</h2>

				<?php
				/** This filter is documented in themes/ld30/templates/course.php */
				do_action( 'learndash-course-heading-after', $course_id, $user_id );
				?>

				<div class="ld-item-list-actions" data-ld-expand-list="true">

				<?php
				/**
				 * Fires before the course expand.
				 *
				 * @since 3.0.0
				 *
				 * @param int $course_id Course ID.
				 * @param int $user_id   User ID.
				 */
				do_action( 'learndash-course-expand-before', $course_id, $user_id );
				?>

				<?php
				// Only display if there is something to expand
				if ( $has_topics || $has_lesson_quizzes ) :
					?>
						<div class="ld-expand-button ld-primary-background" id="<?php echo esc_attr( 'ld-expand-button-' . $course_id ); ?>" data-ld-expands="<?php echo esc_attr( 'ld-item-list-' . $course_id ); ?>" data-ld-expand-text="<?php echo esc_attr_e( 'Expand All', 'learndash' ); ?>" data-ld-collapse-text="<?php echo esc_attr_e( 'Collapse All', 'learndash' ); ?>">
							<span class="ld-icon-arrow-down ld-icon"></span>
							<span class="ld-text"><?php echo esc_html_e( 'Expand All', 'learndash' ); ?></span>
						</div> <!--/.ld-expand-button-->
						<?php
						/** This filter is documented in themes/ld30/templates/course.php */
						if ( apply_filters( 'learndash_course_steps_expand_all', false, $course_id, 'course_lessons_listing_main' ) ) {
							?>
							<script>
								jQuery(document).ready(function(){
									setTimeout(function(){
										jQuery("<?php echo esc_attr( '#ld-expand-button-' . $course_id ); ?>").trigger('click');
									}, 1000);
								});
							</script>
							<?php
						}
					endif;

				/** This filter is documented in themes/ld30/templates/course.php */
				do_action( 'learndash-course-expand-after', $course_id, $user_id );
				?>

				</div> <!--/.ld-item-list-actions-->
			</div> <!--/.ld-section-heading-->

			<?php
			/** This filter is documented in themes/ld30/templates/course.php */
			do_action( 'learndash-course-content-list-before', $course_id, $user_id );

			/**
			 * Content content listing
			 *
			 * @since 3.0
			 *
			 * ('listing.php');
			 */
			learndash_get_template_part(
				'course/listing.php',
				array(
					'course_id'                  => $course_id,
					'user_id'                    => $user_id,
					'lessons'                    => $lessons,
					'lesson_topics'              => ! empty( $lesson_topics ) ? $lesson_topics : array(),
					'quizzes'                    => $quizzes,
					'has_access'                 => $has_access,
					'course_pager_results'       => $course_pager_results,
					'lesson_progression_enabled' => $lesson_progression_enabled,
				),
				true
			);

			/** This filter is documented in themes/ld30/templates/course.php */
			do_action( 'learndash-course-content-list-after', $course_id, $user_id );
			?>

		</div> <!--/.ld-item-list-->

		<?php
	endif;

	learndash_load_login_modal_html();
	?>
	</div>
	<?php
}

/**
 * Can user bypass course limits.
 *
 * @param integer $user_id   User ID.
 * @param integer $step_id   Lesson, Topic, Quiz ID.
 * @param integer $course_id Related Course ID.
 */
function learndash_elementor_bypass_course_limits( $user_id = 0, $step_id = 0, $course_id = 0 ) {
	$bypass_course_limits_admin_users = false;

	if ( ( ! empty( $user_id ) ) && ( ! empty( $step_id ) ) && ( ! empty( $course_id ) ) ) {
		$user_id   = absint( $user_id );
		$step_id   = absint( $step_id );
		$course_id = absint( $course_id );

		if ( in_array( get_post_type( $step_id ), learndash_get_post_types( 'course' ), true ) ) {
			$step_post = get_post( $step_id );

			if ( learndash_is_admin_user( $user_id ) ) {
				$bypass_course_limits_admin_users = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_General_Admin_User', 'bypass_course_limits_admin_users' );
				if ( 'yes' === $bypass_course_limits_admin_users ) {
					$bypass_course_limits_admin_users = true;
				} else {
					$bypass_course_limits_admin_users = false;
				}
			} else {
				$bypass_course_limits_admin_users = false;
			}

			// For logged in users to allow an override filter.
			/** This filter is documented in themes/ld30/includes/helpers.php */
			$bypass_course_limits_admin_users = apply_filters( 'learndash_prerequities_bypass', $bypass_course_limits_admin_users, $user_id, $course_id, get_post( $step_id ) );
		}
	}

	return $bypass_course_limits_admin_users;
}



/**
 * Logs the Step activity into the LearnDash Activity Database
 *
 * @param integer $user_id   User ID.
 * @param integer $step_id   Lesson, Topic, Quiz ID.
 * @param integer $course_id Related Course ID.
 */
function learndash_elementor_activity_start_step( $user_id = 0, $step_id = 0, $course_id = 0 ) {
	if ( ( ! empty( $user_id ) ) && ( ! empty( $step_id ) ) && ( ! empty( $course_id ) ) ) {
		$user_id   = absint( $user_id );
		$step_id   = absint( $step_id );
		$course_id = absint( $course_id );

		if ( in_array( get_post_type( $step_id ), learndash_get_post_types( 'course' ), true ) ) {
			$step_type = learndash_get_post_type_key( get_post_type( $step_id ) );

			// We insert the Course started record before the Lesson.
			$course_args = array(
				'course_id'        => $course_id,
				'user_id'          => $user_id,
				'post_id'          => $course_id,
				'activity_type'    => 'course',
				'activity_status'  => false,
				'activity_started' => time(),
				'activity_meta'    => array(
					'steps_total'     => learndash_get_course_steps_count( $course_id ),
					'steps_completed' => learndash_course_get_completed_steps( $user_id, $course_id ),
					'steps_last_id'   => $step_id,
				),
			);

			$course_activity = learndash_get_user_activity( $course_args );
			if ( ( ! $course_activity ) || ( empty( $course_activity->activity_started ) ) ) {
				learndash_update_user_activity( $course_args );
			}

			// Now log the Course Step (Lesson, Topic, Quiz).
			$step_args = array(
				'course_id'        => $course_id,
				'user_id'          => $user_id,
				'post_id'          => $step_id,
				'activity_type'    => $step_type,
				'activity_status'  => false,
				'activity_started' => time(),
				'activity_meta'    => array(
					'steps_total'     => learndash_get_course_steps_count( $course_id ),
					'steps_completed' => learndash_course_get_completed_steps( $user_id, $course_id ),
				),
			);

			$step_activity = learndash_get_user_activity( $step_args );
			if ( ( ! $step_activity ) || ( empty( $step_activity->activity_started ) ) ) {
				learndash_update_user_activity( $step_args );
			}
		}
	}
}

/**
 * Add the Course Step Material to the content.
 *
 * @param string  $content     Original Post content.
 * @param string  $select_type Material process type. tabs, append, or none.
 * @param integer $user_id     User ID.
 * @param integer $step_id     Lesson, Topic, Quiz ID.
 * @param integer $course_id   Course ID.
 *
 * @return string Materials content.
 */
function learndash_elementor_add_step_material_content( $content = '', $select_type = '', $user_id = 0, $step_id = 0, $course_id = 0 ) {
	if ( /* ( ! empty( $user_id ) ) && */ ( ! empty( $step_id ) ) ) {
		$select_type = esc_attr( $select_type );
		$user_id     = absint( $user_id );
		$course_id   = absint( $course_id );
		$step_id     = absint( $step_id );

		if ( in_array( get_post_type( $step_id ), learndash_get_post_types( 'course' ), true ) ) {
			$step_type = learndash_get_post_type_key( get_post_type( $step_id ) );

			$materials = learndash_elementor_get_step_material( $user_id, $step_id );
			if ( ! empty( $materials ) ) {
				if ( 'tabs' === $select_type ) {
					$material = learndash_get_template_part(
						'modules/tabs.php',
						array(
							'course_id' => $course_id,
							'post_id'   => $step_id,
							'user_id'   => $user_id,
							'content'   => $content,
							'materials' => $materials,
							'context'   => $step_type,
						),
						false
					);

					if ( ! empty( $material ) ) {
						$content = '<div class="' . esc_attr( learndash_get_wrapper_class( $step_id ) ) . '">' . $material . '</div>';
					}
				} elseif ( 'append' === $select_type ) {
					$content .= '<div class="' . esc_attr( learndash_get_wrapper_class( $step_id ) ) . '">' . $materials . '</div>';
				}
			}
		}
	}

	return $content;
}


/**
 * Retreived the Course Step Material content.
 *
 * @param integer $user_id   User ID.
 * @param integer $step_id   Lesson, Topic, Quiz ID.
 *
 * @return string Materials content.
 */
function learndash_elementor_get_step_material( $user_id = 0, $step_id = 0 ) {
	$materials = '';

	if ( /* ( ! empty( $user_id ) ) && */ ( ! empty( $step_id ) ) ) {
		$user_id = absint( $user_id );
		$step_id = absint( $step_id );

		if ( in_array( get_post_type( $step_id ), learndash_get_post_types( 'course' ), true ) ) {
			$step_type = learndash_get_post_type_key( get_post_type( $step_id ) );
			$settings  = learndash_get_setting( $step_id );

			if ( ! isset( $settings[ $step_type . '_materials_enabled' ] ) ) {
				$settings[ $step_type . '_materials_enabled' ] = '';
				if ( ( isset( $settings[ $step_type . '_materials' ] ) ) && ( ! empty( $settings[ $step_type . '_materials' ] ) ) ) {
					$settings[ $step_type . '_materials_enabled' ] = 'on';
				}
			}

			if ( ( 'on' === $settings[ $step_type . '_materials_enabled' ] ) && ( ! empty( $settings[ $step_type . '_materials' ] ) ) ) {
				$materials = wp_specialchars_decode( $settings[ $step_type . '_materials' ], ENT_QUOTES );
				if ( ! empty( $materials ) ) {
					$materials = do_shortcode( $materials );
					$materials = wpautop( $materials );
				}
			}
		}
	}

	return $materials;
}

/**
 * Get USer access state
 *
 * @param string  $state     State token. 'show_content', 'previous_lesson_completed', 'previous_topic_completed'.
 * @param integer $user_id   User ID.
 * @param integer $step_id   Lesson, Topic, Quiz ID.
 * @param integer $course_id Related Course ID.
 */
function learndash_elementor_user_step_access_state( $state = '', $user_id = 0, $step_id = 0, $course_id = 0 ) {
	static $user_course_access_states = array();

	$state_value = '';

	if ( ( ! empty( $state ) ) /* && ( ! empty( $user_id ) ) */ && ( ! empty( $step_id ) ) && ( ! empty( $course_id ) ) ) {
		$state     = esc_attr( $state );
		$user_id   = absint( $user_id );
		$step_id   = absint( $step_id );
		$course_id = absint( $course_id );

		if ( in_array( get_post_type( $step_id ), learndash_get_post_types( 'course' ), true ) ) {
			if ( isset( $user_course_access_states[ $user_id ][ $course_id ][ $step_id ][ $state ] ) ) {
				$state_value = $user_course_access_states[ $user_id ][ $course_id ][ $step_id ][ $state ];
			} else {
				$step_post = get_post( $step_id );

				if ( ! isset( $user_course_access_states[ $user_id ] ) ) {
					$user_course_access_states[ $user_id ] = array();
				}
				if ( ! isset( $user_course_access_states[ $user_id ][ $course_id ] ) ) {
					$user_course_access_states[ $user_id ][ $course_id ] = array();
				}

				$bypass_course_limits = learndash_elementor_bypass_course_limits( $user_id, $step_id, $course_id );
				$is_sample            = learndash_is_sample( $step_id );

				$step_state                 = array();
				$lesson_progression_enabled = learndash_lesson_progression_enabled( $course_id );
				if ( $lesson_progression_enabled ) {
					switch ( get_post_type( $step_id ) ) {
						case learndash_get_post_type_slug( 'topic' ):
							if ( ( ! $bypass_course_limits ) && ( ! $is_sample ) ) {
								$lesson_id   = learndash_course_get_single_parent_step( $course_id, $step_id );
								$lesson_post = get_post( $lesson_id );

								/** This filter is documented in includes/class-ld-cpt-instance.php */
								$step_state['previous_topic_completed'] = apply_filters( 'learndash_previous_step_completed', is_previous_complete( $step_post ), $step_id, $user_id );

								/** This filter is documented in includes/class-ld-cpt-instance.php */
								$step_state['previous_lesson_completed'] = apply_filters( 'learndash_previous_step_completed', is_previous_complete( $lesson_post ), $lesson_post->ID, $user_id );
							} else {
								$step_state['previous_topic_completed']  = true;
								$step_state['previous_lesson_completed'] = true;
							}
							$step_state['show_content'] = ( $step_state['previous_topic_completed'] && $step_state['previous_lesson_completed'] );
							break;

						case learndash_get_post_type_slug( 'lesson' ):
							if ( ( ! $bypass_course_limits ) && ( ! $is_sample ) ) {
								$step_state['previous_lesson_completed'] = apply_filters( 'learndash_previous_step_completed', is_previous_complete( $step_post ), $step_id, $user_id );
								$step_state['show_content']              = $step_state['previous_lesson_completed'];
							} else {
								$step_state['previous_lesson_completed'] = true;
								$step_state['show_content']              = $step_state['previous_lesson_completed'];
							}

							break;

						case learndash_get_post_type_slug( 'quiz' ):
							$last_incomplete_step = is_quiz_accessable( null, $step_post, true, $course_id );
							if ( ( is_a( $last_incomplete_step, 'WP_Post' ) ) && ( ! $is_sample ) ) {
								$step_state['show_content'] = false;
							} else {
								$step_state['show_content'] = true;
							}
							break;

						default:
							break;
					}
				} else {
					$step_state['previous_topic_completed']  = true;
					$step_state['previous_lesson_completed'] = true;
					$step_state['show_content']              = ( $step_state['previous_topic_completed'] && $step_state['previous_lesson_completed'] );
				}
				$user_course_access_states[ $user_id ][ $course_id ][ $step_id ] = $step_state;

				if ( isset( $user_course_access_states[ $user_id ][ $course_id ][ $step_id ][ $state ] ) ) {
					$state_value = $user_course_access_states[ $user_id ][ $course_id ][ $step_id ][ $state ];
				}
			}
		}
	}

	return $state_value;
}

/**
 * Add the Course Step Video to the content.
 *
 * @param string  $content   Original Post content.
 * @param integer $user_id   User ID.
 * @param integer $step_id   Lesson, Topic, Quiz ID.
 * @param integer $course_id Course ID.
 *
 * @return string Materials content.
 */
function learndash_elementor_add_step_video_content( $content = '', $user_id = 0, $step_id = 0, $course_id = 0 ) {
	if ( ( defined( 'LEARNDASH_LESSON_VIDEO' ) ) && ( true === LEARNDASH_LESSON_VIDEO ) ) {
		if ( ( ! empty( $user_id ) ) && ( ! empty( $step_id ) ) ) {
			$user_id   = absint( $user_id );
			$course_id = absint( $course_id );
			$step_id   = absint( $step_id );

			if ( in_array( get_post_type( $step_id ), array( learndash_get_post_type_slug( 'topic' ), learndash_get_post_type_slug( 'lesson' ) ), true ) ) {
				$settings         = learndash_get_setting( $step_id );
				$ld_course_videos = Learndash_Course_Video::get_instance();
				$content          = $ld_course_videos->add_video_to_content( $content, get_post( $step_id ), $settings );
			}
		}
	}

	return $content;
}


/**
 * Determine template type frm environment if possible.
 *
 * @since 1.0.0
 */
function learndash_elementor_get_template_type() {
	$template_type = '';

	if ( isset( $_POST['editor_post_id'] ) ) {
		$editor_post_id = absint( $_POST['editor_post_id'] );
	} elseif ( isset( $_GET['post'] ) ) {
		$editor_post_id = absint( $_GET['post'] );
	} else {
		$editor_post_id = get_the_ID();
	}

	if ( ! empty( $editor_post_id ) ) {
		$post_type = get_post_type( $editor_post_id );
		if ( 'elementor_library' === $post_type ) {
			$template_type = Source_Local::get_template_type( $editor_post_id );
		} else {
			$template_type = $post_type;
		}
	} else {
		$template_type = '';
	}

	return $template_type;
}
