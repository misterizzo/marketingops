<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * LearnDash Elementor Widget for course_content shortcode.
 *
 * @since 1.0.0
 * @package LearnDash
 */
class LearnDash_Elementor_Widget_Course_Content extends LearnDash_Elementor_Widget_Base {

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
		$this->widget_slug = 'ld-course-content';

		$template_type = learndash_elementor_get_template_type();
		if ( learndash_get_post_type_slug( 'course' ) === $template_type ) {
			$this->widget_title = sprintf(
				// translators: placeholder: Course.
				esc_html_x( '%s Content', 'placeholder: Course', 'learndash-elementor' ),
				\LearnDash_Custom_Label::get_label( 'course' )
			);
		} elseif ( learndash_get_post_type_slug( 'lesson' ) === $template_type ) {
			$this->widget_title = sprintf(
				// translators: placeholder: Lesson.
				esc_html_x( '%s Content', 'placeholder: Lesson', 'learndash-elementor' ),
				\LearnDash_Custom_Label::get_label( 'lesson' )
			);
		} elseif ( learndash_get_post_type_slug( 'topic' ) === $template_type ) {
			$this->widget_title = sprintf(
				// translators: placeholder: Topic.
				esc_html_x( '%s Content', 'placeholder: Topic', 'learndash-elementor' ),
				\LearnDash_Custom_Label::get_label( 'topic' )
			);
		}

		$this->widget_icon      = 'eicon-table-of-contents';
		$this->shortcode_slug   = 'course_content';
		$this->shortcode_params = array(
			'course_id'         => 'course_id',
			'step_id'           => 'step_id',
			'user_id'           => 'user_id',
			'preview_course_id' => 'preview_course_id',
			'preview_step_id'   => 'preview_step_id',
			'preview_user_id'   => 'preview_user_id',
		);

		parent::__construct( $data, $args );
	}

	/** Documented in Elementor /includes/base/controls-stack.php */
	protected function _register_controls() {
		$template_type   = learndash_elementor_get_template_type();
		$preview_step_id = $this->learndash_get_preview_post_id( $template_type );

		$this->start_controls_section(
			'preview',
			array(
				'label' => __( 'Preview Setting', 'learndash-elementor' ),
			)
		);

		if ( in_array( $template_type, array( learndash_get_post_type_slug( 'course' ) ), true ) ) {

			$this->add_control(
				'preview_step_id',
				array(
					'label'        => sprintf(
						// translators: placeholder: Course.
						esc_html_x( '%s', 'placeholder: Course', 'learndash-elementor' ),
						\LearnDash_Custom_Label::get_label( 'course' )
					),
					'type'         => ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
					'options'      => array(),
					'default'      => $preview_step_id,
					'label_block'  => true,
					'autocomplete' => array(
						'object' => ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_POST,
						'query'  => array(
							'post_type' => array( learndash_get_post_type_slug( 'course' ) ),
						),
					),
				)
			);
		} elseif ( in_array( $template_type, array( learndash_get_post_type_slug( 'lesson' ) ), true ) ) {
			$this->add_control(
				'preview_step_id',
				array(
					'label'        => sprintf(
						// translators: placeholder: Lesson.
						esc_html_x( '%s', 'placeholder: Lesson', 'learndash-elementor' ),
						\LearnDash_Custom_Label::get_label( 'lesson' )
					),
					'type'         => ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
					'options'      => array(),
					'default'      => $preview_step_id,
					'label_block'  => true,
					'autocomplete' => array(
						'object' => ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_POST,
						'query'  => array(
							'post_type' => array( learndash_get_post_type_slug( 'lesson' ) ),
						),
					),
				)
			);
		} elseif ( in_array( $template_type, array( learndash_get_post_type_slug( 'topic' ) ), true ) ) {
			$this->add_control(
				'preview_step_id',
				array(
					'label'        => sprintf(
						// translators: placeholder: Topic.
						esc_html_x( '%s', 'placeholder: Topic', 'learndash-elementor' ),
						\LearnDash_Custom_Label::get_label( 'topic' )
					),
					'type'         => ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
					'options'      => array(),
					'default'      => $preview_step_id,
					'label_block'  => true,
					'autocomplete' => array(
						'object' => ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_POST,
						'query'  => array(
							'post_type' => array( learndash_get_post_type_slug( 'topic' ) ),
						),
					),
				)
			);
		}

		$this->add_control(
			'preview_user_id',
			array(
				'label'        => esc_html__( 'User ID', 'learndash-elementor' ),
				'type'         => ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'options'      => array(),
				'default'      => get_current_user_id(),
				'label_block'  => true,
				'autocomplete' => array(
					'object' => ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_USER,
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
				'label' => __( 'Header', 'learndash-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		if ( learndash_get_post_type_slug( 'course' ) === $template_type ) {
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_course_content_header_text',
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-section-heading h2',
					'exclude'  => array( 'line_height' ),
				)
			);
		} elseif ( learndash_get_post_type_slug( 'lesson' ) === $template_type ) {
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_course_content_header_text',
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-lesson-topic-list .ld-table-list .ld-table-list-header .ld-table-list-title',
					'exclude'  => array( 'line_height' ),
				)
			);
		} elseif ( learndash_get_post_type_slug( 'topic' ) === $template_type ) {
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_course_content_header_text',
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-table-list .ld-table-list-header .ld-table-list-title',
					'exclude'  => array( 'line_height' ),
				)
			);
		}

		$this->add_control(
			'control_course_content_header_text_color',
			array(
				'label'     => __( 'Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper .ld-section-heading > h2' => 'color: {{VALUE}};',
					'{{WRAPPER}} .learndash-wrapper .ld-table-list .ld-table-list-header' => 'color: {{VALUE}};',
				),
			)
		);

		if ( in_array( $template_type, array( learndash_get_post_type_slug( 'lesson' ), learndash_get_post_type_slug( 'topic' ) ), true ) ) {

			$this->add_control(
				'control_course_content_header_text_background_color',
				array(
					'label'     => __( 'Background Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => $this->learndash_get_template_color( 'primary' ),
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-table-list.ld-topic-list .ld-table-list-header' => 'background-color: {{VALUE}} !important;',
						'{{WRAPPER}} .learndash-wrapper .ld-table-list.ld-topic-list .ld-table-list-header.ld-primary-background' => 'background-color: {{VALUE}} !important;',
					),
				)
			);
		}

		if ( learndash_get_post_type_slug( 'course' ) === $template_type ) {
			$this->add_control(
				'control_course_content_header_expand_text_color',
				array(
					'label'     => __( 'Expand Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#ffffff',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-section-heading .ld-expand-button' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'control_course_content_header_expand_text_background_color',
				array(
					'label'     => __( 'Expand Background Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => $this->learndash_get_template_color( 'primary' ),
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-section-heading .ld-item-list-actions .ld-expand-button' => 'background-color: {{VALUE}} !important;',
					),
				)
			);
		}

		$this->end_controls_section();

		$this->start_controls_section(
			'section_course_content_row_item',
			array(
				'label' => __( 'Row Item', 'learndash-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'header_section_course_content_row_item_title',
			array(
				'label'     => __( 'TITLE', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		if ( learndash_get_post_type_slug( 'course' ) === $template_type ) {
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_course_content_row_item_title_text',
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-item-list .ld-item-list-item .ld-item-title',
					'exclude'  => array( 'line_height' ),
				)
			);
		} elseif ( learndash_get_post_type_slug( 'lesson' ) === $template_type ) {
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_course_content_row_item_title_text',
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-topic-list.ld-table-list .ld-table-list-items',
					'exclude'  => array( 'line_height' ),
				)
			);
		} elseif ( learndash_get_post_type_slug( 'topic' ) === $template_type ) {
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_course_content_row_item_title_text',
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-topic-list.ld-table-list .ld-table-list-items',
					'exclude'  => array( 'line_height' ),
				)
			);
		}

		$this->add_control(
			'control_course_content_row_item_title_color',
			array(
				'label'     => __( 'Title Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#495255',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper .ld-item-list .ld-item-list-item .ld-item-title' => 'color: {{VALUE}};',
					'{{WRAPPER}} .learndash-wrapper .ld-table-list-items .ld-table-list-item a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'control_course_content_row_item_background_color',
			array(
				'label'     => __( 'Background Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper .ld-item-list .ld-item-list-item' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .learndash-wrapper .ld-table-list-items' => 'background-color: {{VALUE}};',
				),
			)
		);

		if ( learndash_get_post_type_slug( 'course' ) === $template_type ) {
			$this->add_control(
				'header_section_course_content_row_item_expand',
				array(
					'label'     => __( 'EXPAND', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'control_course_content_row_item_expand_text_color',
				array(
					'label'     => __( 'Expand Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#ffffff',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-item-details .ld-expand-button' => 'color: {{VALUE}} !important;',
						'{{WRAPPER}} .learndash-wrapper .ld-item-details .ld-expand-button .ld-icon-arrow-down' => 'color: {{VALUE}} !important;',
						'{{WRAPPER}} .learndash-wrapper .ld-item-details .ld-expand-button .ld-text' => 'color: {{VALUE}} !important;',
					),
				)
			);

			$this->add_control(
				'control_course_content_row_item_expand_text_background_color',
				array(
					'label'     => __( 'Expand Background Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => $this->learndash_get_template_color( 'primary' ),
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-item-details .ld-expand-button .ld-icon-arrow-down' => 'background-color: {{VALUE}} !important;',
					),
				)
			);

			$this->add_control(
				'header_course_content_row_item_lesson_progress',
				array(
					'label'     => __( 'LESSON PROGRESS HEADER', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_course_content_row_item_lesson_progress_text',
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-lesson-list .ld-item-list-items .ld-item-list-item .ld-table-list-header',
					'exclude'  => array( 'line_height' ),
				)
			);

			$this->add_control(
				'control_course_content_row_item_lesson_progress_text_color',
				array(
					'label'     => __( 'Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-lesson-list .ld-item-list-items .ld-item-list-item .ld-table-list-header.ld-primary-background' => 'color: {{VALUE}} !important;',
						'{{WRAPPER}} .learndash-wrapper .ld-lesson-list .ld-item-list-items .ld-item-list-item .ld-table-list-header' => 'color: {{VALUE}} !important;',
					),
				)
			);

			$this->add_control(
				'control_course_content_row_item_lesson_progress_text_background_color',
				array(
					'label'     => __( 'Background Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => $this->learndash_get_template_color( 'primary' ),
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-lesson-list .ld-item-list-items .ld-item-list-item .ld-table-list-header.ld-primary-background' => 'background-color: {{VALUE}} !important;',
						'{{WRAPPER}} .learndash-wrapper .ld-lesson-list .ld-item-list-items .ld-item-list-item .ld-table-list-header' => 'background-color: {{VALUE}} !important;',
					),
				)
			);

		}

		$this->end_controls_section();

		if ( in_array( $template_type, array( learndash_get_post_type_slug( 'lesson' ), learndash_get_post_type_slug( 'topic' ) ), true ) ) {

			$this->start_controls_section(
				'section_course_content_footer',
				array(
					'label' => __( 'Footer', 'learndash-elementor' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				)
			);

			$this->add_control(
				'header_course_content_footer_links',
				array(
					'label'     => __( 'LINKS (Back to...)', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_course_content_footer_links_text',
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-content-actions a.ld-primary-color',
					'exclude'  => array( 'line_height' ),
				)
			);
			$this->add_control(
				'control_course_content_footer_links_color',
				array(
					'label'     => __( 'Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => $this->learndash_get_template_color( 'primary' ),
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-content-actions a.ld-primary-color' => 'color: {{VALUE}} !important;',
					),
				)
			);

			$this->add_control(
				'header_course_content_footer_navigation',
				array(
					'label'     => __( 'NAVIGATION', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_course_content_footer_navigation_text',
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-content-action a.ld-button',
					'exclude'  => array( 'line_height' ),
				)
			);
			$this->add_control(
				'control_course_content_footer_navigation_text_color',
				array(
					'label'     => __( 'Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#ffffff',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-content-action a.ld-button' => 'color: {{VALUE}} !important;',
					),
				)
			);

			$this->add_control(
				'control_course_content_footer_navigation_text_background_color',
				array(
					'label'     => __( 'Background Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => $this->learndash_get_template_color( 'primary' ),
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-content-action a.ld-button' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'header_course_content_footer_mark_complete',
				array(
					'label'     => __( 'MARK COMPLETE', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_course_content_footer_mark_complete_text',
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-content-action input.learndash_mark_complete_button',
					'exclude'  => array( 'line_height' ),
				)
			);
			$this->add_control(
				'control_course_content_footer_mark_complete_text_color',
				array(
					'label'     => __( 'Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#ffffff',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-content-action input.learndash_mark_complete_button' => 'color: {{VALUE}} !important;',
					),
				)
			);

			$this->add_control(
				'control_course_content_footer_mark_complete_text_background_color',
				array(
					'label'     => __( 'Background Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => $this->learndash_get_template_color( 'secondary' ),
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-content-action input.learndash_mark_complete_button' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->end_controls_section();
		}
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
					case 'step_id':
					case 'preview_step_id':
					case 'user_id':
					case 'preview_user_id':
						$shortcode_pairs[ $key_in ] = absint( $settings[ $key_ex ] );
						break;

					default:
						$shortcode_pairs[ $key_in ] = esc_attr( $settings[ $key_ex ] );
						break;
				}
			}
		}

		$template_type = learndash_elementor_get_template_type();
		if ( is_admin() ) {

			if ( ( isset( $shortcode_pairs['preview_step_id'] ) ) && ( ! empty( $shortcode_pairs['preview_step_id'] ) ) ) {
				if ( get_post_type( $shortcode_pairs['preview_step_id'] ) !== $template_type ) {
					$shortcode_pairs['preview_step_id'] = $this->learndash_get_preview_post_id( $template_type );
				}
			}

			if ( in_array( $template_type, learndash_get_post_types( 'course_steps' ), true ) ) {
				$shortcode_pairs['preview_course_id'] = learndash_get_course_id( $shortcode_pairs['preview_step_id'] );
			}
			$shortcode_pairs['step_id']   = absint( $shortcode_pairs['preview_step_id'] );
			$shortcode_pairs['course_id'] = absint( $shortcode_pairs['preview_course_id'] );

			unset( $shortcode_pairs['preview_course_id'] );
			unset( $shortcode_pairs['preview_step_id'] );

			if ( ( empty( $shortcode_pairs['user_id'] ) ) && ( ! empty( $shortcode_pairs['preview_user_id'] ) ) ) {
				$shortcode_pairs['user_id'] = absint( $shortcode_pairs['preview_user_id'] );
				unset( $shortcode_pairs['preview_user_id'] );
			}

			if ( ! empty( $shortcode_pairs['step_id'] ) ) {
				// Setup globals because the templates use them
				global $post;
				$post = get_post( $shortcode_pairs['step_id'] );
			}
		} else {
			unset( $shortcode_pairs['preview_course_id'] );
			unset( $shortcode_pairs['preview_step_id'] );
			unset( $shortcode_pairs['preview_user_id'] );

			if ( ( ! isset( $shortcode_pairs['course_id'] ) ) || ( empty( $shortcode_pairs['course_id'] ) ) ) {
				if ( in_array( get_post_type(), learndash_get_post_types(), true ) ) {
					$shortcode_pairs['course_id'] = learndash_get_course_id( get_the_id() );
				}
			}

			if ( ( ! isset( $shortcode_pairs['step_id'] ) ) || ( empty( $shortcode_pairs['step_id'] ) ) ) {
				if ( in_array( get_post_type(), learndash_get_post_types(), true ) ) {
					$shortcode_pairs['step_id'] = get_the_ID();
				}
			}

			if ( ( ! isset( $shortcode_pairs['user_id'] ) ) || ( empty( $shortcode_pairs['user_id'] ) ) ) {
				$shortcode_pairs['user_id'] = get_current_user_id();
			}
		}

		$context = '';
		if ( ! empty( $shortcode_pairs['course_id'] ) ) {
			$context = 'course';
		}

		if ( ( ! empty( $shortcode_pairs['step_id'] ) ) && ( $shortcode_pairs['step_id'] !== $shortcode_pairs['course_id'] ) ) {
			$step_post_type = get_post_type( $shortcode_pairs['step_id'] );
			if ( ( ! empty( $step_post_type ) ) && ( in_array( $step_post_type, learndash_get_post_types( 'course' ), true ) ) ) {
				if ( learndash_get_post_type_slug( 'lesson' ) === $step_post_type ) {
					$context = 'lesson';
				} elseif ( learndash_get_post_type_slug( 'topic' ) === $step_post_type ) {
					$context = 'topic';
				}
			}
		}

		if ( 'course' === $context ) {
			$course_post = get_post( $shortcode_pairs['course_id'] );
			$logged_in   = ! empty( $shortcode_pairs['user_id'] );

			$bypass_course_limits_admin_users = learndash_elementor_bypass_course_limits( $shortcode_pairs['user_id'], $shortcode_pairs['course_id'], $shortcode_pairs['course_id'] );

			if ( ( $logged_in ) && ( ! learndash_is_course_prerequities_completed( $shortcode_pairs['course_id'] ) ) && ( ! $bypass_course_limits_admin_users ) ) {
				return;
			}

			if ( ( $logged_in ) && ( ! learndash_check_user_course_points_access( $shortcode_pairs['course_id'], $shortcode_pairs['user_id'] ) ) && ( ! $bypass_course_limits_admin_users ) ) {
				return;
			}

			learndash_elementor_show_course_content_listing( $shortcode_pairs );
		} elseif ( 'lesson' === $context ) {
			learndash_elementor_show_lesson_content_listing( $shortcode_pairs );
		} elseif ( 'topic' === $context ) {
			learndash_elementor_show_topic_content_listing( $shortcode_pairs );
		}
	}
}
