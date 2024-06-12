<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * LearnDash Elementor Widget for ld_profile shortcode.
 *
 * @since 1.0.0
 * @package LearnDash
 */
class LearnDash_Elementor_Widget_Profile extends LearnDash_Elementor_Widget_Base {

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
		$this->widget_slug  = 'ld-profile';
		$this->widget_title = esc_html__( 'Profile', 'learndash-elementor' );
		$this->widget_icon  = 'eicon-person';

		$this->shortcode_slug   = 'ld_profile';
		$this->shortcode_params = array(
			'per_page'           => 'per_page',
			'order'              => 'order',
			'orderby'            => 'orderby',
			'show_search'        => 'show_search',
			'show_header'        => 'show_header',
			'course_points_user' => 'course_points_user',
			'profile_link'       => 'profile_link',
			'show_quizzes'       => 'show_quizzes',
			'expand_all'         => 'expand_all',
		);

		parent::__construct( $data, $args );
	}

	/** Documented in Elementor /includes/base/controls-stack.php */
	protected function _register_controls() {

		$this->start_controls_section(
			'section_profile',
			array(
				'label' => __( 'Profile Settings', 'learndash-elementor' ),
			)
		);

		$this->add_control(
			'per_page',
			array(
				'label'       => sprintf(
					// translators: placeholder: Courses.
					esc_html_x( '%s per page', 'placeholder: Courses', 'learndash-elementor' ),
					\LearnDash_Custom_Label::get_label( 'courses' )
				),
				'type'        => \Elementor\Controls_Manager::NUMBER,
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'       => esc_html__( 'Order by', 'learndash-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => array(
					'ID'         => __( 'ID - Order by post id. (default)', 'learndash-elementor' ),
					'title'      => __( 'Title - Order by post title', 'learndash-elementor' ),
					'date'       => __( 'Date - Order by post date', 'learndash-elementor' ),
					'menu_order' => __( 'Menu - Order by Page Order Value', 'learndash-elementor' ),
				),
				'default'     => 'ID',
			)
		);

		$this->add_control(
			'order',
			array(
				'label'       => esc_html__( 'Order', 'learndash-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => array(
					'DESC' => __( 'DESC - highest to lowest values (default)', 'learndash-elementor' ),
					'ASC'  => __( 'ASC - lowest to highest values', 'learndash-elementor' ),
				),
				'default'     => 'DESC',
			)
		);

		$this->add_control(
			'show_search',
			array(
				'label'       => esc_html__( 'Show Search', 'learndash-elementor' ),
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'description' => esc_html__( 'LD30 template only', 'learndash-elementor' ),
				'default'     => 'yes',
			)
		);

		$this->add_control(
			'show_header',
			array(
				'label'   => esc_html__( 'Show Profile Header', 'learndash-elementor' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'course_points_user',
			array(
				// translators: placeholder: Cours.
				'label'   => sprintf( esc_html_x( 'Show Earned %s Points', 'placeholder: Course', 'learndash-elementor' ), \LearnDash_Custom_Label::get_label( 'course' ) ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'profile_link',
			array(
				'label'   => esc_html__( 'Show Profile Link', 'learndash-elementor' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_quizzes',
			array(
				// translators: placeholder: Quiz.
				'label'   => sprintf( esc_html_x( 'Show User %s Attempts', 'placeholder: Quiz', 'learndash-elementor' ), \LearnDash_Custom_Label::get_label( 'quiz' ) ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'expand_all',
			array(
				// translators: placeholder: Cours.
				'label'   => sprintf( esc_html_x( 'Expand All %s Sections', 'placeholder: Course', 'learndash-elementor' ), \LearnDash_Custom_Label::get_label( 'course' ) ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->end_controls_section();

		/**
		 * Start of Style tab.
		 */
		$this->start_controls_section(
			'section_profile_header',
			array(
				'label' => __( 'Header', 'learndash-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'control_profile_header_text_background_color',
			array(
				'label'     => __( 'Background Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'header_profile_header_avatar',
			array(
				'label'     => __( 'AVATAR', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'control_profile_header_avatar_size_height',
			array(
				'label'     => __( 'Avatar Size', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 150,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 500,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-profile-summary .ld-profile-card .ld-profile-avatar' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_control(
			'header_profile_header_summary',
			array(
				'label'     => __( 'SUMMARY', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'control_profile_header_summary_header_text',
				'label'    => __( 'Header Typography', 'learndash-elementor' ),
				'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
				'selector' => '{{WRAPPER}} .learndash-wrapper #ld-profile .ld-profile-summary .ld-profile-stats .ld-profile-stat > strong',
				'exclude'  => array( 'line_height' ),
			)
		);

		$this->add_control(
			'control_profile_header_summary_header_text_color',
			array(
				'label'     => __( 'Header Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-profile-summary .ld-profile-stats .ld-profile-stat > strong' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'control_profile_header_summary_label_text',
				'label'    => __( 'Label Typography', 'learndash-elementor' ),
				'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
				'selector' => '{{WRAPPER}} .learndash-wrapper #ld-profile .ld-profile-summary .ld-profile-stats .ld-profile-stat span',
				'exclude'  => array( 'line_height' ),
			)
		);

		$this->add_control(
			'control_profile_header_summary_label_text_color',
			array(
				'label'     => __( 'Label Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-profile-summary .ld-profile-stats .ld-profile-stat span' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'header_profile_header_mycourses',
			array(
				'label'     => sprintf(
					// translators: placeholder: Courses.
					esc_html_x( 'YOUR %s', 'placeholder: Courses', 'learndash-elementor' ),
					strtoupper( \LearnDash_Custom_Label::get_label( 'courses' ) )
				),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'control_profile_header_yourcourses_text',
				'label'    => __( 'Label Typography', 'learndash-elementor' ),
				'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
				'selector' => '{{WRAPPER}} .learndash-wrapper #ld-profile .ld-section-heading h3',
				'exclude'  => array( 'line_height' ),
			)
		);

		$this->add_control(
			'control_profile_header_yourcourses_text_color',
			array(
				'label'     => __( 'Label Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-section-heading h3' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'header_profile_header_expand_all',
			array(
				'label'     => __( 'EXPAND ALL', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'control_course_content_header_expand_text_color',
			array(
				'label'     => __( 'Expand Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-section-heading .ld-expand-button' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'control_course_content_header_expand_text_background_color',
			array(
				'label'     => __( 'Expand Background Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-section-heading .ld-expand-button' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_profile_row_item',
			array(
				'label' => __( 'Row Item', 'learndash-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'control_profile_row_item_title_background_color',
			array(
				'label'     => __( 'Background Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-item-list .ld-item-list-item' => 'background-color: {{VALUE}};',
				),
			)
		);


		$this->add_control(
			'header_section_profile_row_item_title',
			array(
				'label'     => __( 'TITLE', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'control_profile_row_item_title_text',
				'label'    => __( 'Title Text', 'learndash-elementor' ),
				'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
				'selector' => '{{WRAPPER}} .learndash-wrapper #ld-profile .ld-course-title',
				'exclude'  => array( 'line_height' ),
			)
		);

		$this->add_control(
			'control_profile_row_item_title_text_color',
			array(
				'label'     => __( 'Title Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-course-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'header_section_profile_row_item_expand',
			array(
				'label'     => __( 'EXPAND', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'control_profile_row_item_expand_text_color',
			array(
				'label'     => __( 'Expand Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-item-details .ld-expand-button' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-item-details .ld-expand-button .ld-icon-arrow-down' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .learndash-wrapper #ld-profile.ld-item-details .ld-expand-button .ld-text' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'control_profile_row_item_expand_text_background_color',
			array(
				'label'     => __( 'Expand Background Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-item-details .ld-expand-button .ld-icon-arrow-down' => 'background-color: {{VALUE}} !important;',
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-item-details .ld-expand-button' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_profile_row_item_progress',
			array(
				'label' => __( 'Row Item Details', 'learndash-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'control_profile_row_item_background_color',
			array(
				'label'     => __( 'Background Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-item-list .ld-item-list-item .ld-item-list-item-expanded .ld-progress' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'header_section_profile_row_item_course_progress',
			array(
				'label'     => sprintf(
					// translators: placeholder: Courses.
					esc_html_x( '%s PROGRESS LABEL', 'placeholder: Courses', 'learndash-elementor' ),
					strtoupper( \LearnDash_Custom_Label::get_label( 'courses' ) )
				),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'control_profile_row_item_course_progress_text',
				'label'    => __( 'Title Text', 'learndash-elementor' ),
				'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
				'selector' => '{{WRAPPER}} .learndash-wrapper #ld-profile .ld-progress .ld-progress-heading .ld-progress-label',
				'exclude'  => array( 'line_height' ),
			)
		);

		$this->add_control(
			'control_profile_row_item_course_progress_text_color',
			array(
				'label'     => __( 'Title Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-progress .ld-progress-heading .ld-progress-label' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'header_section_profile_row_item_course_progress_percent',
			array(
				'label'     => sprintf(
					// translators: placeholder: Courses.
					esc_html_x( '%s PROGRESS PERCENT LABEL', 'placeholder: Courses', 'learndash-elementor' ),
					strtoupper( \LearnDash_Custom_Label::get_label( 'courses' ) )
				),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'control_profile_row_item_course_progress_percent_text',
				'label'    => __( 'Title Text', 'learndash-elementor' ),
				'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
				'selector' => '{{WRAPPER}} .learndash-wrapper #ld-profile .ld-progress .ld-progress-heading .ld-progress-percentage',
				'exclude'  => array( 'line_height' ),
			)
		);

		$this->add_control(
			'control_profile_row_item_course_progress_percent_text_color',
			array(
				'label'     => __( 'Percent Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-progress .ld-progress-heading .ld-progress-percentage' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'header_section_profile_row_item_course_progress_steps',
			array(
				'label'     => sprintf(
					// translators: placeholder: Courses.
					esc_html_x( '%s PROGRESS STEPS LABEL', 'placeholder: Courses', 'learndash-elementor' ),
					strtoupper( \LearnDash_Custom_Label::get_label( 'courses' ) )
				),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'control_profile_row_item_course_progress_steps_text',
				'label'    => __( 'Title Text', 'learndash-elementor' ),
				'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
				'selector' => '{{WRAPPER}} .learndash-wrapper #ld-profile .ld-progress .ld-progress-heading .ld-progress-steps',
				'exclude'  => array( 'line_height' ),
			)
		);

		$this->add_control(
			'control_profile_row_item_course_progress_steps_text_color',
			array(
				'label'     => __( 'Percent Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-progress .ld-progress-heading .ld-progress-steps' => 'color: {{VALUE}} !important;',
				),
			)
		);


		$this->add_control(
			'header_section_profile_row_item_course_progress_bar',
			array(
				'label'     => esc_html__( 'PROGRESS BAR', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'control_profile_row_item_course_progress_bar_color',
			array(
				'label'     => __( 'Progress Bar Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-progress .ld-progress-bar .ld-progress-bar-percentage' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'control_profile_row_item_course_progress_bar_color_backgrouns',
			array(
				'label'     => __( 'Progress Bar Background', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-progress .ld-progress-bar' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_profile_row_item_progress_quiz',
			array(
				'label' => sprintf(
					// translators: placeholder: Quizzes.
					esc_html_x( 'Row Item %s', 'placeholder: Quizzes', 'learndash-elementor' ),
					\LearnDash_Custom_Label::get_label( 'quizzes' )
				),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);


		$this->add_control(
			'header_section_profile_row_item_progress_quiz_header',
			array(
				'label'     => esc_html__( 'HEADER', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'control_profile_row_item_progress_quiz_header_text',
				'label'    => __( 'Text', 'learndash-elementor' ),
				'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
				'selector' => '{{WRAPPER}} .learndash-wrapper #ld-profile .ld-table-list.ld-quiz-list .ld-table-list-header',
				'exclude'  => array( 'line_height' ),
			)
		);

		$this->add_control(
			'control_profile_row_item_progress_quiz_header_text_color',
			array(
				'label'     => __( 'Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-table-list.ld-quiz-list .ld-table-list-header' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'control_profile_row_item_progress_quiz_header_text_background_color',
			array(
				'label'     => __( 'Background Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-table-list.ld-quiz-list .ld-table-list-header' => 'background-color: {{VALUE}} !important;',
				),
			)
		);


		$this->add_control(
			'header_section_profile_row_item_progress_quiz_row',
			array(
				'label'     => esc_html__( 'ROW ITEM', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'control_profile_row_item_progress_quiz_row_text',
				'label'    => __( 'Text', 'learndash-elementor' ),
				'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
				'selector' => '{{WRAPPER}} .learndash-wrapper #ld-profile .ld-table-list.ld-quiz-list .ld-table-list-item',
				'exclude'  => array( 'line_height' ),
			)
		);

		$this->add_control(
			'control_profile_row_item_progress_quiz_row_text_color',
			array(
				'label'     => __( 'Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-table-list.ld-quiz-list .ld-table-list-item' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-table-list.ld-quiz-list .ld-table-list-item .ld-table-list-title a' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-table-list.ld-quiz-list .ld-table-list-item .user_statistic .ld-icon' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'control_profile_row_item_progress_quiz_row_text_background_color',
			array(
				'label'     => __( 'Background Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-table-list.ld-quiz-list .ld-table-list-items' => 'background-color: {{VALUE}} !important;',
				),
			)
		);


		$this->end_controls_section();


		$this->start_controls_section(
			'section_profile_row_item_progress_assignments',
			array(
				'label' => esc_html__( 'Row Item Assignments', 'learndash-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'header_section_profile_row_item_progress_assignments_header',
			array(
				'label'     => esc_html__( 'HEADER', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'control_profile_row_item_progress_assignments_header_text',
				'label'    => __( 'Text', 'learndash-elementor' ),
				'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
				'selector' => '{{WRAPPER}} .learndash-wrapper #ld-profile .ld-assignment-list .ld-table-list-header',
				'exclude'  => array( 'line_height' ),
			)
		);

		$this->add_control(
			'control_profile_row_item_progress_assignments_header_text_color',
			array(
				'label'     => __( 'Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-assignment-list .ld-table-list-header' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'control_profile_row_item_progress_assignments_header_text_background_color',
			array(
				'label'     => __( 'Background Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-assignment-list .ld-table-list-header' => 'background-color: {{VALUE}} !important;',
				),
			)
		);


		$this->add_control(
			'header_section_profile_row_item_progress_assignments_row',
			array(
				'label'     => esc_html__( 'ROW ITEM', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'control_profile_row_item_progress_assignments_row_text',
				'label'    => __( 'Text', 'learndash-elementor' ),
				'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
				'selector' => '{{WRAPPER}} .learndash-wrapper #ld-profile .ld-assignment-list .ld-table-list-item',
				'exclude'  => array( 'line_height' ),
			)
		);

		$this->add_control(
			'control_profile_row_item_progress_assignments_row_text_color',
			array(
				'label'     => __( 'Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-assignment-list .ld-table-list-item' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-assignment-list .ld-table-list-item .ld-table-list-title a' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-assignment-list .ld-table-list-item .user_statistic .ld-icon' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-assignment-list .ld-table-list-item .ld-comments-column a' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'control_profile_row_item_progress_assignments_row_text_background_color',
			array(
				'label'     => __( 'Background Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-assignment-list .ld-table-list-items' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'header_section_profile_row_item_progress_assignments_row_comments',
			array(
				'label'     => esc_html__( 'STATUS', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'control_profile_row_item_progress_assignments_row_comments_text',
				'label'    => __( 'Text', 'learndash-elementor' ),
				'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
				'selector' => '{{WRAPPER}} .learndash-wrapper #ld-profile .ld-assignment-list .ld-table-list-item .ld-status-column .ld-status',
				'exclude'  => array( 'line_height' ),
			)
		);

		$this->add_control(
			'control_profile_row_item_progress_assignments_row_comments_text_color',
			array(
				'label'     => __( 'Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-assignment-list .ld-table-list-item .ld-status-column .ld-status' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'control_profile_row_item_progress_assignments_row_comments_text_background_color',
			array(
				'label'     => __( 'Background Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper #ld-profile .ld-assignment-list .ld-table-list-item .ld-status-column .ld-status' => 'background-color: {{VALUE}} !important;',
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
			if ( isset( $settings[ $key_ex ] ) ) {
				$shortcode_pairs[ $key_in ] = esc_attr( $settings[ $key_ex ] );
			}
		}

		$shortcode_params_str = '';
		foreach ( $shortcode_pairs as $key => $val ) {
			$skip_param = false;
			switch ( $key ) {
				case 'show_search':
				case 'show_header':
				case 'course_points_user':
				case 'profile_link':
				case 'show_quizzes':
				case 'expand_all':
					if ( '' === $val ) {
						$val = 'no';
					}
					break;

				default:
					if ( '' === $val ) {
						$skip_param = true;
					}
					break;
			}

			if ( true !== $skip_param ) {
				$shortcode_params_str .= ' ' . $key . '="' . $val . '"';
			}
		}
		if ( ! empty( $shortcode_params_str ) ) {
			$shortcode_params_str = '[' . $this->shortcode_slug . $shortcode_params_str . ']';
			echo do_shortcode( $shortcode_params_str );
		}
	}

	// End of functions.
}
