<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * LearnDash Elementor Widget for quiz shortcode.
 *
 * @since 1.0.0
 * @package LearnDash
 */
class LearnDash_Elementor_Widget_Quiz extends LearnDash_Elementor_Widget_Base {

	/**
	 * Widget base constructor.
	 *
	 * Initializing the widget base class.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @throws \Exception If arguments are missing when initializing a full widget
	 *                   instance.
	 *
	 * @param array      $data Widget data. Default is an empty array.
	 * @param array|null $args Optional. Widget default arguments. Default is null.
	 */
	public function __construct( $data = array(), $args = null ) {
		$this->widget_slug  = 'ld-quiz';
		$this->widget_title = sprintf(
			// translators: placeholder: Quiz.
			esc_html_x( '%s Content', 'placeholder: Quiz', 'learndash-elementor' ),
			\LearnDash_Custom_Label::get_label( 'quiz' )
		);
		$this->widget_icon = 'eicon-checkbox';

		$this->shortcode_slug   = 'ld_quiz';
		$this->shortcode_params = array(
			'quiz_id'           => 'quiz_id',
			//'course_id'         => 'course_id',
			'preview_quiz_id'   => 'preview_quiz_id',
			'preview_course_id' => 'preview_course_id',
		);

		parent::__construct( $data, $args );
	}

	/** Documented in Elementor /includes/base/controls-stack.php */
	protected function _register_controls() {

		$preview_quiz_id = 0;
		if ( get_post_type() === learndash_get_post_type_slug( 'quiz' ) ) {
			$preview_quiz_id = get_the_id();
		} else {
			$preview_quiz_id = $this->learndash_get_preview_post_id( learndash_get_post_type_slug( 'quiz' ) );
		}

		$preview_course_id = 0;
		$quiz_courses      = learndash_get_courses_for_step( $preview_quiz_id, true );
		if ( ! empty( $quiz_courses ) ) {
			$preview_course_id = array_keys( $quiz_courses )[0];
		}
/*
		$this->start_controls_section(
			'settings',
			array(
				'label' => __( 'Settings', 'learndash-elementor' ),
			)
		);

		$this->add_control(
			'quiz_id',
			array(
				// translators: placeholder: Quiz.
				'label'       => sprintf( esc_html_x( '%s', 'placeholder: Quiz', 'learndash-elementor' ), \LearnDash_Custom_Label::get_label( 'quiz' ) ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 0,
				'description' => sprintf(
					// translators: placeholder: Quiz, Quiz.
					esc_html_x( 'Enter single %1$s ID. Leave blank if used within a %2$s.', 'placeholders: Quiz, Quiz', 'learndash-elementor' ),
					\LearnDash_Custom_Label::get_label( 'quiz' ),
					\LearnDash_Custom_Label::get_label( 'quiz' )
				),
			)
		);

		$this->add_control(
			'course_id',
			array(
				// translators: placeholder: Course.
				'label'       => sprintf( esc_html_x( '%s', 'placeholder: Course', 'learndash-elementor' ), \LearnDash_Custom_Label::get_label( 'course' ) ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 0,
				'description' => sprintf(
					// translators: placeholder: Course, Course.
					esc_html_x( 'Enter single %1$s ID. Leave blank if used within a %2$s.', 'placeholders: Course, Course', 'learndash-elementor' ),
					\LearnDash_Custom_Label::get_label( 'course' ),
					\LearnDash_Custom_Label::get_label( 'course' )
				),
			)
		);

		$this->end_controls_section();
*/
		$this->start_controls_section(
			'preview',
			array(
				'label' => __( 'Preview Setting', 'learndash-elementor' ),
			)
		);

		$this->add_control(
			'preview_quiz_id',
			array(
				'label'        => sprintf(
					// translators: placeholder: Quiz.
					esc_html_x( '%s', 'placeholder: Quiz', 'learndash-elementor' ),
					\LearnDash_Custom_Label::get_label( 'Quiz' )
				),
				'type'         => ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'options'      => array(),
				'default'      => 0,
				'label_block'  => true,
				'autocomplete' => array(
					'object' => ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_POST,
					'query'  => array(
						'post_type' => array( learndash_get_post_type_slug( 'quiz' ) ),
					),
				),
			)
		);

		$this->end_controls_section();

		/**
		 * Start of Style tab.
		 */
		$this->start_controls_section(
			'section_course_content_header',
			array(
				'label' => __( 'Start', 'learndash-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'control_quiz_start_button_text',
				'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
				'selector' => '{{WRAPPER}} .learndash-wrapper input.wpProQuiz_button[name="startQuiz"]',
				'exclude'  => array( 'line_height' ),
			)
		);

		$this->add_control(
			'control_quiz_start_button_color',
			array(
				'label'     => __( 'Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper input.wpProQuiz_button[name="startQuiz"]' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'control_quiz_start_button_backgroundcolor',
			array(
				'label'     => __( 'Background Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper input.wpProQuiz_button[name="startQuiz"]' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$shortcode_pairs = array();

		foreach ( $this->shortcode_params as $key_ex => $key_in ) {
			$shortcode_pairs[ $key_in ] = '';
			if ( isset( $settings[ $key_ex ] ) ) {
				switch ( $key_ex ) {
					//case 'course_id':
					case 'preview_course_id':
					case 'quiz_id':
					case 'preview_quiz_id':
						$shortcode_pairs[ $key_in ] = absint( $settings[ $key_ex ] );
						break;

					default:
						$shortcode_pairs[ $key_in ] = esc_attr( $settings[ $key_ex ] );
						break;
				}
			}
		}

		if ( is_admin() ) {
			if ( ( ! isset( $shortcode_pairs['preview_quiz_id'] ) ) || ( empty( $shortcode_pairs['preview_quiz_id'] ) ) ) {
				if ( get_post_type() === learndash_get_post_type_slug( 'quiz' ) ) {
					$shortcode_pairs['preview_quiz_id'] = get_the_id();
				} else {
					$shortcode_pairs['preview_quiz_id'] = $this->learndash_get_preview_post_id( learndash_get_post_type_slug( 'quiz' ) );
				}
			}

			if ( ( empty( $shortcode_pairs['quiz_id'] ) ) && ( ! empty( $shortcode_pairs['preview_quiz_id'] ) ) ) {
				$shortcode_pairs['quiz_id'] = absint( $shortcode_pairs['preview_quiz_id'] );
				unset( $shortcode_pairs['preview_quiz_id'] );
			}

			if ( ( ! isset( $shortcode_pairs['preview_course_id'] ) ) || ( empty( $shortcode_pairs['preview_course_id'] ) ) ) {
				if ( ! empty( $shortcode_pairs['quiz_id'] ) ) {
					$quiz_courses = learndash_get_courses_for_step( $shortcode_pairs['quiz_id'], true );
					if ( ! empty( $quiz_courses ) ) {
						$preview_course_id = array_keys( $quiz_courses )[0];
					}
				} else {
					$shortcode_pairs['preview_course_id'] = $this->learndash_get_preview_post_id( learndash_get_post_type_slug( 'course' ) );
				}
			}

			//if ( ( empty( $shortcode_pairs['course_id'] ) ) && ( ! empty( $shortcode_pairs['preview_course_id'] ) ) ) {
			//	$shortcode_pairs['course_id'] = absint( $shortcode_pairs['preview_course_id'] );
			//	unset( $shortcode_pairs['preview_course_id'] );
			//}

			$quiz_content = '<div class="wpProQuiz_content"><div class="wpProQuiz_text"><div><input class="wpProQuiz_button" type="button" value="Start Quiz" name="startQuiz"></div></div></div>';
			if ( ! empty( $quiz_content ) ) {
				echo '<div class="' . esc_attr( learndash_get_wrapper_class( $shortcode_pairs['quiz_id'] ) ) . '">' . $quiz_content . '</div>';
			}

			return;
		} else {
			unset( $shortcode_pairs['preview_course_id'] );
			unset( $shortcode_pairs['preview_quiz_id'] );

			if ( ( ! isset( $shortcode_pairs['quiz_id'] ) ) || ( empty( $shortcode_pairs['quiz_id'] ) ) ) {
				if ( get_post_type() === learndash_get_post_type_slug( 'quiz' ) ) {
					$shortcode_pairs['quiz_id'] = get_the_ID();
				}
			}

			if ( ( ! isset( $shortcode_pairs['course_id'] ) ) || ( empty( $shortcode_pairs['course_id'] ) ) ) {
				if ( ! empty( $shortcode_pairs['quiz_id'] ) ) {
					$shortcode_pairs['course_id'] = learndash_get_course_id( $shortcode_pairs['quiz_id'] );
				}
			}
		}

		if ( ! empty( $shortcode_pairs['quiz_id'] ) ) {
			$shortcode_pairs['quiz_pro_id'] = get_post_meta( $shortcode_pairs['quiz_id'], 'quiz_pro_id', true );
			$shortcode_pairs['quiz_pro_id'] = absint( $shortcode_pairs['quiz_pro_id'] );

			if ( empty( $shortcode_pairs['quiz_pro_id'] ) ) {
				$shortcode_pairs['quiz_id'] = 0;
			}
		}

		if ( ! empty( $shortcode_pairs['quiz_id'] ) ) {
			$atts = $shortcode_pairs;

			$lesson_progression_enabled = false;
			if ( ! empty( $atts['course_id'] ) ) {
				$lesson_progression_enabled = learndash_lesson_progression_enabled( $atts['course_id'] );
			}

			$has_access = '';

			$user_id = get_current_user_id();

			$quiz_post = get_post( $atts['quiz_id'] );
			if ( is_a( $quiz_post, 'WP_Post' ) ) {
				$quiz_settings = learndash_get_setting( $atts['quiz_id'] );
				$meta          = \SFWD_CPT_Instance::$instances['sfwd-quiz']->get_settings_values( 'sfwd-quiz' );

				$show_content   = ! ( ! empty( $lesson_progression_enabled ) && ! is_quiz_accessable( $user_id, $quiz_post, false, $atts['course_id'] ) );
				$attempts_count = 0;
				$repeats        = ( isset( $quiz_settings['repeats'] ) ) ? trim( $quiz_settings['repeats'] ) : '';
				if ( '' === $repeats ) {
					if ( ! empty( $quiz_settings['quiz_pro'] ) ) {
						$quiz_mapper   = new \WpProQuiz_Model_QuizMapper();
						$pro_quiz_edit = $quiz_mapper->fetch( $quiz_settings['quiz_pro'] );
						if ( ( $pro_quiz_edit ) && ( is_a( $pro_quiz_edit, 'WpProQuiz_Model_Quiz' ) ) ) {
							if ( ( isset( $atts['quiz_id'] ) ) && ( ! empty( $atts['quiz_id'] ) ) ) {
								$pro_quiz_edit->setPostId( $atts['quiz_id'] );
							}

							if ( $pro_quiz_edit->isQuizRunOnce() ) {
								$repeats = 0;
								// Update for later.
								learndash_update_setting( $quiz_post, 'repeats', $repeats );
							}
						}
					}
				}

				if ( '' !== $repeats ) {

					if ( $user_id ) {
						$usermeta = get_user_meta( $user_id, '_sfwd-quizzes', true );
						$usermeta = maybe_unserialize( $usermeta );

						if ( ! is_array( $usermeta ) ) {
							$usermeta = array();
						}

						if ( ! empty( $usermeta ) ) {
							foreach ( $usermeta as $k => $v ) {
								if ( ( intval( $v['quiz'] ) === $atts['quiz_id'] ) ) {
									if ( ! empty( $atts['course_id'] ) ) {
										if ( ( isset( $v['course'] ) ) && ( ! empty( $v['course'] ) ) && ( absint( $v['course'] ) === absint( $atts['course_id'] ) ) ) {
											// Count the number of time the student has taken the quiz where the course_id matches.
											$attempts_count++;
										}
									} elseif ( empty( $atts['course_id'] ) ) {
										if ( ( isset( $v['course'] ) ) && ( empty( $v['course'] ) ) && ( absint( $v['course'] ) === absint( $atts['course_id'] ) ) ) {
											// Count the number of time the student has taken the quiz where the course_id is zero.
											$attempts_count++;
										}
									}
								}
							}
						}
					}
				}

				$attempts_left = ( ( '' === $repeats ) || ( absint( $repeats ) >= absint( $attempts_count ) ) );

				$bypass_course_limits_admin_users = learndash_elementor_bypass_course_limits( $user_id, $quiz_post->ID, $atts['course_id'] );

				// For logged in users to allow an override filter.
				/** This filter is documented in themes/ld30/includes/helpers.php */
				$bypass_course_limits_admin_users = apply_filters( 'learndash_prerequities_bypass', $bypass_course_limits_admin_users, $user_id, $atts['course_id'], $quiz_post );
				if ( ( true === $bypass_course_limits_admin_users ) && ( ! $attempts_left ) ) {
					$attempts_left = 1;
				}

				/**
				 * Filters the quiz attempts left for a user.
				 *
				 * See example https://bitbucket.org/snippets/learndash/Gjygja
				 *
				 * @since 3.1.0
				 *
				 * @param boolean $attempts_left  Whether any quiz attempts left for a user or not.
				 * @param int     $attempts_count Number of Quiz attemplts already taken.
				 * @param int     $user_id        ID of User taking Quiz.
				 * @param int     $quiz_id        ID of Quiz being taken.
				 */
				$attempts_left = apply_filters( 'learndash_quiz_attempts', $attempts_left, absint( $attempts_count ), absint( $user_id ), absint( $quiz_post->ID ) );
				$attempts_left = absint( $attempts_left );

				if ( ! empty( $lesson_progression_enabled ) && ! is_quiz_accessable( $user_id, $quiz_post, false, $atts['course_id'] ) ) {
					add_filter( 'comments_array', 'learndash_remove_comments', 1, 2 );
				}

				if ( ! empty( $lesson_progression_enabled ) ) :

					$last_incomplete_step = is_quiz_accessable( null, $quiz_post, true, $atts['course_id'] );
					if ( learndash_is_sample( $quiz_post ) ) {
						$last_incomplete_step = null;
					}

					if ( is_a( $last_incomplete_step, 'WP_Post' ) ) {
						/** This filter is documented in themes/ld30/templates/quiz.php */
						do_action( 'learndash-quiz-progression-before', $quiz_post->ID, $atts['course_id'], $user_id );

						$progression_message = learndash_get_template_part(
							'modules/messages/lesson-progression.php',
							array(
								'previous_item' => $last_incomplete_step,
								'course_id'     => $atts['course_id'],
								'user_id'       => $user_id,
								'context'       => 'quiz',
							),
							false
						);

						if ( ! empty( $progression_message ) ) {
							echo '<div class="' . esc_attr( learndash_get_wrapper_class( $shortcode_pairs['step_id'] ) ) . '">' . $progression_message . '</div>';
						}

						/** This filter is documented in themes/ld30/templates/quiz.php */
						do_action( 'learndash-quiz-progression-after', $quiz_post->ID, $course_id, $user_id );
						return;
					}
				endif;

				if ( $attempts_left ) {

					/**
					 * Filters quiz shortcode content access message.
					 *
					 * If not null, message display instead of quiz content.
					 *
					 * @since 2.1.0
					 *
					 * @param string $message The content access message.
					 * @param WP_Post $quiz_post    Quiz WP_Post object.
					 */
					$access_message = apply_filters( 'learndash_content_access', null, $quiz_post );
					if ( ! is_null( $access_message ) ) {
						$quiz_content = $access_message;
					} else {

						$quiz_content = '';
						if ( ! empty( $quiz_settings['quiz_pro'] ) ) {
							$quiz_settings['lesson'] = 0;
							$quiz_settings['topic']  = 0;

							if ( ( ! empty( $course_id ) ) && ( ! empty( $quiz_id ) ) ) {
								$quiz_settings['topic'] = learndash_course_get_single_parent_step( $atts['course_id'], $quiz_id, learndash_get_post_type_slug( 'topic' ) );
								$quiz_settings['topic'] = absint( $quiz_settings['topic'] );

								$quiz_settings['lesson'] = learndash_course_get_single_parent_step( $atts['course_id'], $quiz_id, learndash_get_post_type_slug( 'lesson' ) );
								$quiz_settings['lesson'] = absint( $quiz_settings['lesson'] );
							}

							/*
							$quiz_content = wptexturize(
								do_shortcode( '[LDAdvQuiz ' . $quiz_settings['quiz_pro'] . ' quiz_pro_id="' . $quiz_settings['quiz_pro'] . '" quiz_id="' . $quiz_post->ID . '" course_id="' . $atts['course_id'] . '" lesson_id="' . $quiz_settings['lesson'] . '" topic_id="' . $quiz_settings['topic'] . '"]' )
							);
							*/

							$quiz_content = wptexturize(
								do_shortcode( '[LDAdvQuiz ' . $quiz_settings['quiz_pro'] . ' quiz_pro_id="' . $quiz_settings['quiz_pro'] . '" quiz_id="' . $quiz_post->ID . '" course_id="' . $atts['course_id'] . '" lesson_id="' . $quiz_settings['lesson'] . '" topic_id="' . $quiz_settings['topic'] . '"]' )
							);

							if ( substr( $quiz_content, 0, strlen( '[LDAdvQuiz' ) ) == '[LDAdvQuiz' ) {
								$quiz_content = '<div class="wpProQuiz_content"><div class="wpProQuiz_text"><div><input class="wpProQuiz_button" type="button" value="Start Quiz" name="startQuiz"></div></div></div>';
							}
						}

						/**
						 * Filters `ld_quiz` shortcode content.
						 *
						 * @since 2.1.0
						 *
						 * @param string  $quiz_content ld_quiz shortcode content.
						 * @param WP_Post $quiz_post    Quiz WP_Post object.
						 */
						$quiz_content = apply_filters( 'learndash_quiz_content', $quiz_content, $quiz_post );
						if ( ! empty( $quiz_content ) ) {
							echo '<div class="' . esc_attr( learndash_get_wrapper_class( $shortcode_pairs['quiz_id'] ) ) . '">' . $quiz_content . '</div>';
						}
					}
				} else {
					/**
					 * Display an alert
					 */

					echo '<div class="' . esc_attr( learndash_get_wrapper_class( $shortcode_pairs['quiz_id'] ) ) . '">';

					learndash_get_template_part(
						'modules/alert.php',
						array(
							'type'    => 'warning',
							'icon'    => 'alert',
							'message' => sprintf(
								// translators: placeholders: quiz, attempts count.
								esc_html_x( 'You have already taken this %1$s %2$d time(s) and may not take it again.', 'placeholders: quiz, attempts count', 'learndash' ),
								learndash_get_custom_label_lower( 'quiz' ),
								$attempts_count
							),
						),
						true
					);

					echo '</div>';
				}
			}
		}
	}
}
