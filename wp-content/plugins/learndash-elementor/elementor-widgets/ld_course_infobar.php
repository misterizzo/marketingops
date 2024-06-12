<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * LearnDash Elementor Widget for ld_course_infobar shortcode.
 *
 * @since 1.0.0
 * @package LearnDash
 */
class LearnDash_Elementor_Widget_Course_Infobar extends LearnDash_Elementor_Widget_Base {

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
		$this->widget_slug  = 'ld-course-infobar';

		$template_type   = learndash_elementor_get_template_type();
		if ( learndash_get_post_type_slug( 'course' ) === $template_type ) {
			$this->widget_title = sprintf(
				// translators: placeholder: Course.
				esc_html_x( '%s Infobar', 'placeholder: Course', 'learndash-elementor' ),
				\LearnDash_Custom_Label::get_label( 'course' )
			);
		} elseif ( learndash_get_post_type_slug( 'lesson' ) === $template_type ) {
			$this->widget_title = sprintf(
				// translators: placeholder: Lesson.
				esc_html_x( '%s Infobar', 'placeholder: Lesson', 'learndash-elementor' ),
				\LearnDash_Custom_Label::get_label( 'lesson' )
			);
		} elseif ( learndash_get_post_type_slug( 'topic' ) === $template_type ) {
			$this->widget_title = sprintf(
				// translators: placeholder: Topic.
				esc_html_x( '%s Infobar', 'placeholder: Topic', 'learndash-elementor' ),
				\LearnDash_Custom_Label::get_label( 'topic' )
			);
		} elseif ( learndash_get_post_type_slug( 'quiz' ) === $template_type ) {
			$this->widget_title = sprintf(
				// translators: placeholder: Quiz.
				esc_html_x( '%s Infobar', 'placeholder: Quiz', 'learndash-elementor' ),
				\LearnDash_Custom_Label::get_label( 'quiz' )
			);
		}

		$this->widget_icon = 'eicon-form-vertical';
		$this->shortcode_slug   = 'ld_course_infobar';
		$this->shortcode_params = array(
			'course_id'         => 'course_id',
			'step_id'           => 'step_id',
			'has_access'        => 'has_access',
			'course_status'     => 'course_status',
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
				'label' => __( 'Preview Settings', 'learndash-elementor' ),
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

			$this->add_control(
				'course_status',
				array(
					'label'       => sprintf(
						// translators: placeholder: Course.
						esc_html_x( '%s Status', 'placeholder: Course', 'learndash-elementor' ),
						\LearnDash_Custom_Label::get_label( 'course' )
					),
					'type'        => \Elementor\Controls_Manager::SELECT,
					'label_block' => true,
					'options'     => array(
						'not-started' => __( 'Not Started', 'learndash-elementor' ),
						'in-progress' => __( 'In Progress', 'learndash-elementor' ),
						'completed'   => __( 'Completed', 'learndash-elementor' ),
					),
				)
			);

			$this->add_control(
				'has_access',
				array(
					'label'   => sprintf(
						// translators: placeholder: Course.
						esc_html_x( 'Has %s Access', 'placeholder: Course', 'learndash-elementor' ),
						\LearnDash_Custom_Label::get_label( 'course' )
					),
					'type'    => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
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
				'label'        => esc_html__( 'User', 'learndash-elementor' ),
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
		 * Begin STYLE tab
		 */
		if ( learndash_get_post_type_slug( 'course' ) === $template_type ) {
			$this->start_controls_section(
				'section_infobar_backgrounds',
				array(
					'label' => __( 'Backgrounds', 'learndash-elementor' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				)
			);

			$this->add_control(
				'header_infobar_backgrounds_not_enrolled',
				array(
					'label'     => __( 'NOT ENROLLED', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			/*
			$this->add_control(
				'control_infobar_backgrounds_not_enrolled_height',
				array(
					'label'     => __( 'Height', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::SLIDER,
					'default'   => array(
						'size' => 40,
					),
					'range'     => array(
						'px' => array(
							'min' => 1,
							'max' => 200,
						),
					),
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled' => 'height: {{SIZE}}{{UNIT}};',
					),
				)
			);
			*/

			$this->add_control(
				'control_infobar_backgrounds_not_enrolled_color',
				array(
					'label'     => __( 'Background Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#f0f3f6',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'header_infobar_backgrounds_enrolled',
				array(
					'label'     => __( 'ENROLLED', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			/*
			$this->add_control(
				'control_infobar_backgrounds_enrolled_height',
				array(
					'label'     => __( 'Height', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::SLIDER,
					'default'   => array(
						'size' => 40,
					),
					'range'     => array(
						'px' => array(
							'min' => 1,
							'max' => 200,
						),
					),
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-enrolled' => 'height: {{SIZE}}{{UNIT}};',
					),
				)
			);
			*/

			$this->add_control(
				'control_infobar_backgrounds_enrolled_color',
				array(
					'label'     => __( 'Background Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-enrolled' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_infobar_not_enrolled_current_status',
				array(
					'label' => __( 'Not Enrolled - Current Status Section', 'learndash-elementor' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				)
			);

			$this->add_control(
				'header_infobar_not_enrolled_current_status',
				array(
					'label'     => __( 'HEADER LABEL', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_infobar_not_enrolled_current_status_text',
					'label'    => __( 'Text', 'learndash-elementor' ),
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-status .ld-course-status-label',
					'exclude'  => array( 'line_height' ),
				)
			);

			$this->add_control(
				'control_infobar_not_enrolled_current_status_text_color',
				array(
					'label'     => __( 'Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-status .ld-course-status-label' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'header_infobar_not_enrolled_current_status_bubble',
				array(
					'label'     => __( 'STATUS LABEL', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_infobar_not_enrolled_current_status_bubble_text',
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-status .ld-course-status-content .ld-status.ld-status-waiting',
					'exclude'  => array( 'line_height' ),
				)
			);

			$this->add_control(
				'control_infobar_not_enrolled_current_status_bubble_text_color',
				array(
					'label'     => __( 'Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => 'rgba(0, 0, 0, 0.65)',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-status .ld-course-status-content .ld-status.ld-status-waiting' => 'color: {{VALUE}} !important;',
					),
				)
			);

			$this->add_control(
				'control_infobar_not_enrolled_current_status_bubble_text_background_color',
				array(
					'label'     => __( 'Background Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#ffd200',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-status .ld-course-status-content .ld-status.ld-status-waiting' => 'background-color: {{VALUE}}  !important;',
					),
				)
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_infobar_not_enrolled_price',
				array(
					'label' => __( 'Not Enrolled - Price Section', 'learndash-elementor' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				)
			);

			$this->add_control(
				'header_infobar_not_enrolled_price',
				array(
					'label'     => __( 'HEADER LABEL', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_infobar_not_enrolled_price_text',
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-price .ld-course-status-label',
					'exclude'  => array( 'line_height' ),
				)
			);

			$this->add_control(
				'control_infobar_not_enrolled_price_text_color',
				array(
					'label'     => __( 'Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-price .ld-course-status-label' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'header_infobar_not_enrolled_price_status',
				array(
					'label'     => __( 'STATUS LABEL', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_infobar_not_enrolled_price_status_text',
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-price .ld-course-status-price',
					'exclude'  => array( 'line_height' ),
				)
			);

			$this->add_control(
				'control_infobar_not_enrolled_price_status_text_color',
				array(
					'label'     => __( 'Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-price .ld-course-status-price' => 'color: {{VALUE}};',
					),
				)
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_infobar_not_enrolled_get_started',
				array(
					'label' => __( 'Not Enrolled - Get Started Section', 'learndash-elementor' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				)
			);

			$this->add_control(
				'header_infobar_not_enrolled_get_started_header',
				array(
					'label'     => __( 'HEADER LABEL', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_infobar_not_enrolled_get_started_header_text',
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-action .ld-course-status-label',
					'exclude'  => array( 'line_height' ),
				)
			);

			$this->add_control(
				'control_infobar_not_enrolled_get_started_header_text_color',
				array(
					'label'     => __( 'Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-action .ld-course-status-label' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'header_infobar_not_enrolled_get_started_status',
				array(
					'label'     => __( 'STATUS BUTTON', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_infobar_not_enrolled_get_started_status_button_text',
					'label'    => __( 'Text', 'learndash-elementor' ),
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					//'selector' => '{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-action .ld-course-status-action',
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-action .ld-course-status-action .learndash_join_button input.btn-join, .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-action .ld-course-status-action a.ld-button, .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-action .ld-course-status-action input#btn-join',
					'exclude'  => array( 'line_height' ),
				)
			);

			$this->add_control(
				'control_infobar_not_enrolled_get_started_status_button_text_color',
				array(
					'label'     => __( 'Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-action .ld-course-status-action .learndash_join_button input.btn-join' => 'color: {{VALUE}} !important;',
						'{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-action .ld-course-status-action a.ld-button' => 'color: {{VALUE}} !important;',
						'{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-action .ld-course-status-action input#btn-join' => 'color: {{VALUE}} !important;',
					),
				)
			);

			$this->add_control(
				'control_infobar_not_enrolled_get_started_status_button_text_background_color',
				array(
					'label'     => __( 'Background Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#f0f3f6',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-action .ld-course-status-action .learndash_join_button input.btn-join' => 'background-color: {{VALUE}} !important;',
						'{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-action .ld-course-status-action a.ld-button' => 'background-color: {{VALUE}} !important;',
						'{{WRAPPER}} .learndash-wrapper .ld-course-status.ld-course-status-not-enrolled .ld-course-status-segment.ld-course-status-seg-action .ld-course-status-action input#btn-join' => 'background-color: {{VALUE}} !important;',
					),
				)
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_infobar_enrolled_progress',
				array(
					'label' => __( 'Enrolled - Progress', 'learndash-elementor' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				)
			);

			$this->add_control(
				'header_infobar_enrolled_progress_indicator',
				array(
					'label'     => __( 'PROGRESS INDICATOR', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'control_infobar_enrolled_progress_indicator_primary_color',
				array(
					'label'     => __( 'Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => $this->learndash_get_template_color( 'secondary' ),
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-progress .ld-progress-bar .ld-progress-bar-percentage' => ' background: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'control_infobar_enrolled_progress_indicator_background_color',
				array(
					'label'     => __( 'Background Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#ffffff',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-progress .ld-progress-bar' => ' background: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'infobar_enrolled_progress_indicator_height',
				array(
					'label'     => __( 'Height', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::SLIDER,
					'default'   => array(
						'size' => 7,
					),
					'range'     => array(
						'px' => array(
							'min' => 1,
							'max' => 30,
						),
					),
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-progress .ld-progress-bar' => 'height: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .learndash-wrapper .ld-progress .ld-progress-bar .ld-progress-bar-percentage' => ' height: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$this->add_control(
				'header_infobar_enrolled_progress_percent_complete',
				array(
					'label'     => __( 'PERCENT COMPLETE', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_infobar_enrolled_progress_percent_complete_text',
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-progress .ld-progress-heading .ld-progress-stats .ld-progress-percentage',
					'exclude'  => array( 'line_height' ),
				)
			);

			$this->add_control(
				'control_infobar_enrolled_progress_percent_complete_text_color',
				array(
					'label'     => __( 'Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-progress .ld-progress-heading .ld-progress-stats .ld-progress-percentage' => 'color: {{VALUE}} !important;',
					),
				)
			);

			$this->add_control(
				'header_infobar_enrolled_progress_steps_complete',
				array(
					'label'     => __( 'STEPS COMPLETE TEXT', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_infobar_enrolled_progress_steps_complete_text',
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-progress .ld-progress-heading .ld-progress-stats .ld-progress-steps',
					'exclude'  => array( 'line_height' ),
				)
			);

			$this->add_control(
				'control_infobar_enrolled_progress_steps_complete_color',
				array(
					'label'     => __( 'Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-progress .ld-progress-heading .ld-progress-stats .ld-progress-steps' => 'color: {{VALUE}};',
					),
				)
			);

			$this->end_controls_section();

		} elseif ( ( ! empty( $template_type ) ) && ( in_array( $template_type, learndash_get_post_types( 'course' ), true ) ) ) {
			$this->start_controls_section(
				'infobar_style_section_step_breadcrumbs',
				array(
					'label' => __( 'Breadcrumbs', 'learndash-elementor' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'infobar_status_step_step_breadcrumbs_text_color',
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-breadcrumbs .ld-breadcrumbs-segments',
					'exclude'  => array( 'line_height' ),
				)
			);

			$this->add_control(
				'infobar_status_step_step_breadcrumbs_text_color',
				array(
					'label'     => __( 'Text Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => $this->learndash_get_template_color( 'primary' ),
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-breadcrumbs a' => 'color: {{VALUE}} !important;',
					),
				)
			);

			$this->add_control(
				'infobar_status_step_step_breadcrumbs_arrow_color',
				array(
					'label'     => __( 'Arrow Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => $this->learndash_get_template_color( 'primary' ),
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-breadcrumbs .ld-breadcrumbs-segments span::after' => 'color: {{VALUE}} !important;',
					),
				)
			);

			$this->add_control(
				'control_infobar_status_step_background_color',
				array(
					'label'     => __( 'Background Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#f0f3f6',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-breadcrumbs' => 'background-color: {{VALUE}} !important;',
						'{{WRAPPER}} .learndash-wrapper .ld-topic-status' => 'background-color: {{VALUE}} !important;',
					),
				)
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_infobar_step_status_bubble',
				array(
					'label' => __( 'Status', 'learndash-elementor' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'control_infobar_status_step_status_bubble_text',
					'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
					'selector' => '{{WRAPPER}} .learndash-wrapper .ld-breadcrumbs .ld-status',
					'exclude'  => array( 'line_height' ),
				)
			);

			$this->add_control(
				'control_infobar_status_step_status_bubble_text_color',
				array(
					'label'     => __( 'Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#f0f3f6',
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-course-status .ld-status.ld-status-complete' => 'color: {{VALUE}} !important;',
						'{{WRAPPER}} .learndash-wrapper .ld-lesson-status .ld-status' => 'color: {{VALUE}} !important;',
						'{{WRAPPER}} .learndash-wrapper .ld-breadcrumbs .ld-status.ld-status-complete' => 'color: {{VALUE}} !important;',
					),
				)
			);

			$this->add_control(
				'control_infobar_status_step_status_bubble_background_color',
				array(
					'label'     => __( 'Background Color', 'learndash-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => $this->learndash_get_template_color( 'primary' ),
					'selectors' => array(
						'{{WRAPPER}} .learndash-wrapper .ld-course-status .ld-status.ld-status-complete.ld-secondary-background' => 'background-color: {{VALUE}} !important;',
						'{{WRAPPER}} .learndash-wrapper .ld-lesson-status .ld-status' => 'background-color: {{VALUE}} !important;',
						'{{WRAPPER}} .learndash-wrapper .ld-breadcrumbs .ld-status.ld-status-complete' => 'background-color: {{VALUE}} !important;',
					),
				)
			);

			$this->end_controls_section();

			if ( ( ! empty( $template_type ) ) && ( in_array( $template_type, array( learndash_get_post_type_slug( 'topic' ) ), true ) ) ) {

				$this->start_controls_section(
					'section_infobar_lesson_progress',
					array(
						'label' => __( 'Lesson Progress', 'learndash-elementor' ),
						'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
					)
				);

				$this->add_control(
					'header_infobar_lesson_progress_header',
					array(
						'label'     => __( 'LESSON PROGRESS HEADER', 'learndash-elementor' ),
						'type'      => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					)
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					array(
						'name'     => 'control_infobar_lesson_progress_header_text',
						'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
						'selector' => '{{WRAPPER}} .learndash-wrapper .ld-topic-status .ld-progress .ld-progress-heading .ld-progress-label',
						'exclude'  => array( 'line_height' ),
					)
				);

				$this->add_control(
					'control_infobar_lesson_progress_header_text_color',
					array(
						'label'     => __( 'Color', 'learndash-elementor' ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'default'   => '',
						'selectors' => array(
							'{{WRAPPER}} .learndash-wrapper .ld-topic-status .ld-progress .ld-progress-heading .ld-progress-label' => 'color: {{VALUE}} !important;',
						),
					)
				);

				$this->add_control(
					'header_infobar_lesson_progress_percent_header',
					array(
						'label'     => __( 'LESSON PROGRESS PERCENT HEADER', 'learndash-elementor' ),
						'type'      => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					)
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					array(
						'name'     => 'infobar_lesson_progress_percent_header_text',
						'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
						'selector' => '{{WRAPPER}} .learndash-wrapper .ld-topic-status .ld-progress .ld-progress-heading .ld-progress-stats .ld-progress-percentage',
						'exclude'  => array( 'line_height' ),
					)
				);

				$this->add_control(
					'control_infobar_lesson_progress_percent_text_color',
					array(
						'label'     => __( 'Color', 'learndash-elementor' ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'default'   => '',
						'selectors' => array(
							'{{WRAPPER}} .learndash-wrapper .ld-topic-status .ld-progress .ld-progress-heading .ld-progress-stats .ld-progress-percentage' => 'color: {{VALUE}} !important;',
						),
					)
				);

				$this->add_control(
					'header_infobar_lesson_progress_indicator_header',
					array(
						'label'     => __( 'PROGRESS INDICATOR', 'learndash-elementor' ),
						'type'      => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					)
				);

				$this->add_control(
					'control_infobar_lesson_progress_indicator_header_primary_color',
					array(
						'label'     => __( 'Color', 'learndash-elementor' ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'default'   => $this->learndash_get_template_color( 'secondary' ),
						'selectors' => array(
							'{{WRAPPER}} .learndash-wrapper .ld-topic-status .ld-progress .ld-progress-bar .ld-progress-bar-percentage' => ' background: {{VALUE}} !important;',
							'{{WRAPPER}} .learndash-wrapper .ld-topic-status .ld-progress .ld-progress-bar .ld-progress-bar-percentage.ld-secondary-background' => ' background: {{VALUE}} !important;',
						),
					)
				);

				$this->add_control(
					'control_infobar_lesson_progress_indicator_header_background_color',
					array(
						'label'     => __( 'Background Color', 'learndash-elementor' ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'default'   => '#ffffff',
						'selectors' => array(
							'{{WRAPPER}} .learndash-wrapper .ld-topic-status .ld-progress .ld-progress-bar' => ' background: {{VALUE}};',
						),
					)
				);

				$this->add_control(
					'control_infobar_lesson_progress_indicator_header_background_height',
					array(
						'label'     => __( 'Height', 'learndash-elementor' ),
						'type'      => \Elementor\Controls_Manager::SLIDER,
						'default'   => array(
							'size' => 7,
						),
						'range'     => array(
							'px' => array(
								'min' => 1,
								'max' => 30,
							),
						),
						'selectors' => array(
							'{{WRAPPER}} .learndash-wrapper .ld-progress .ld-progress-bar' => 'height: {{SIZE}}{{UNIT}};',
							'{{WRAPPER}} .learndash-wrapper .ld-progress .ld-progress-bar .ld-progress-bar-percentage' => ' height: {{SIZE}}{{UNIT}};',
						),
					)
				);

				$this->end_controls_section();
			}
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
		$settings      = $this->get_settings_for_display();
		$template_type = learndash_elementor_get_template_type();

		$shortcode_pairs = array();

		foreach ( $this->shortcode_params as $key_ex => $key_in ) {
			$shortcode_pairs[ $key_in ] = '';
			if ( isset( $settings[ $key_ex ] ) ) {
				switch ( $key_ex ) {
					case 'course_id':
					case 'preview_course_id':
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

		if ( is_admin() ) {
			if ( ( ! isset( $shortcode_pairs['preview_course_id'] ) ) || ( empty( $shortcode_pairs['preview_course_id'] ) ) ) {
				$shortcode_pairs['preview_course_id'] = $this->learndash_get_preview_post_id( learndash_get_post_type_slug( 'course' ) );
			}

			if ( ( empty( $shortcode_pairs['course_id'] ) ) && ( ! empty( $shortcode_pairs['preview_course_id'] ) ) ) {
				$shortcode_pairs['course_id'] = absint( $shortcode_pairs['preview_course_id'] );
				unset( $shortcode_pairs['preview_course_id'] );
			}

			$template_type = learndash_elementor_get_template_type();
			if ( ( ! empty( $template_type ) ) && ( in_array( $template_type, learndash_get_post_types( 'course_steps' ), true ) ) ) {
				if ( ( ! isset( $shortcode_pairs['preview_step_id'] ) ) || ( empty( $shortcode_pairs['preview_step_id'] ) ) ) {
					if ( in_array( get_post_type( get_the_ID() ), learndash_get_post_types( 'course' ), true ) ) {
						$shortcode_pairs['preview_step_id'] = get_the_ID();
					} else {
						$shortcode_pairs['preview_step_id'] = $this->learndash_get_preview_post_id( learndash_get_post_type_slug( 'lesson' ) );
					}
				}

				if ( ( empty( $shortcode_pairs['step_id'] ) ) && ( ! empty( $shortcode_pairs['preview_step_id'] ) ) ) {
					$shortcode_pairs['step_id'] = absint( $shortcode_pairs['preview_step_id'] );
					unset( $shortcode_pairs['preview_step_id'] );
				}
			} else {
				$shortcode_pairs['step_id'] = $shortcode_pairs['course_id'];
				unset( $shortcode_pairs['preview_step_id'] );
			}

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

			$shortcode_pairs['course_status'] = learndash_course_status( $shortcode_pairs['course_id'], $shortcode_pairs['user_id'] );
			$shortcode_pairs['has_access']    = sfwd_lms_has_access( $shortcode_pairs['course_id'], $shortcode_pairs['user_id'] );
		}

		$context = '';
		if ( ! empty( $shortcode_pairs['course_id'] ) ) {
			$context = 'course';
		}

		if ( ! empty( $shortcode_pairs['step_id'] ) ) {
			$context        = '';
			$step_post_type = get_post_type( $shortcode_pairs['step_id'] );
			if ( ( ! empty( $step_post_type ) ) && ( in_array( $step_post_type, learndash_get_post_types( 'course' ), true ) ) ) {
				if ( learndash_get_post_type_slug( 'course' ) === $step_post_type ) {
					$context = 'course';
				} elseif ( learndash_get_post_type_slug( 'lesson' ) === $step_post_type ) {
					$context = 'lesson';
				} elseif ( learndash_get_post_type_slug( 'topic' ) === $step_post_type ) {
					$context = 'topic';
				} elseif ( learndash_get_post_type_slug( 'quiz' ) === $step_post_type ) {
					$context = 'quiz';
				}
			}

			if ( ! empty( $context ) ) {
				$course_id = $shortcode_pairs['course_id'];
				$user_id   = $shortcode_pairs['user_id'];

				if ( 'course' === $context ) {
					$course_post = get_post( $course_id );
					$settings    = learndash_get_setting( $course_id );

					$logged_in = ! empty( $user_id );

					$bypass_course_limits_admin_users = learndash_elementor_bypass_course_limits( $user_id, $course_post->ID, $course_id );

					// For logged in users to allow an override filter.
					/** This filter is documented in themes/ld30/includes/helpers.php */
					$bypass_course_limits_admin_users = apply_filters( 'learndash_prerequities_bypass', $bypass_course_limits_admin_users, $user_id, $course_id, $course_post );

					if ( ( $logged_in ) && ( ! learndash_is_course_prerequities_completed( $course_id ) ) && ( ! $bypass_course_limits_admin_users ) ) {
						$course_pre = learndash_get_course_prerequisites( $course_id );
						if ( ! empty( $course_pre ) ) {
							foreach ( $course_pre as $c_id => $c_status ) {
								break;
							}
							return;
						}
					} elseif ( ( $logged_in ) && ( ! learndash_check_user_course_points_access( $course_id, $user_id ) ) && ( ! $bypass_course_limits_admin_users ) ) {
						return;
					}
				}

				$infobar_html = learndash_get_template_part(
					'modules/infobar.php',
					array(
						'context'       => $context,
						'course_id'     => $shortcode_pairs['course_id'],
						'user_id'       => $shortcode_pairs['user_id'],
						'has_access'    => $shortcode_pairs['has_access'],
						'course_status' => $shortcode_pairs['course_status'],
						'post'          => get_post( $shortcode_pairs['step_id'] ),
					),
					false
				);

				if ( ! empty( $infobar_html ) ) {
					echo '<div class="' . esc_attr( learndash_get_wrapper_class( $shortcode_pairs['step_id'] ) ) . '">' . $infobar_html . '</div>';
				}
			}
		}
	}
}
