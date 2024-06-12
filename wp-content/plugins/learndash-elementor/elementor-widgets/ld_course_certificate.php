<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * LearnDash Elementor Widget for ld_course_certificate shortcode.
 *
 * @since 1.0.0
 * @package LearnDash
 */
class LearnDash_Elementor_Widget_Course_Certificate extends LearnDash_Elementor_Widget_Base {

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
		$this->widget_slug  = 'ld-course-certificate';
		$this->widget_title = sprintf(
			// translators: placeholder: Course.
			esc_html_x( '%s Certificate', 'placeholder: Course', 'learndash-elementor' ),
			\LearnDash_Custom_Label::get_label( 'course' )
		);
		$this->widget_icon = 'eicon-call-to-action';

		$this->shortcode_slug   = 'ld_course_certificate';
		$this->shortcode_params = array(
			'course_id'         => 'course_id',
			'user_id'           => 'user_id',
			'label'             => 'label',
			'message'           => 'message',
			'preview_course_id' => 'preview_course_id',
			'preview_user_id'   => 'preview_user_id',
		);

		parent::__construct( $data, $args );
	}

	/** Documented in Elementor /includes/base/controls-stack.php */
	protected function _register_controls() {
		$preview_course_id = 0;
		if ( in_array( get_post_type(), learndash_get_post_types(), true ) ) {
			$preview_course_id = learndash_get_course_id( get_the_id() );
		} else {
			$preview_course_id       = $this->learndash_get_preview_post_id( learndash_get_post_type_slug( 'course' ) );
			$course_lessons_per_page = learndash_get_course_lessons_per_page( $preview_course_id );
		}

		$this->start_controls_section(
			'settings',
			array(
				'label' => __( 'Settings', 'learndash-elementor' ),
			)
		);

		$this->add_control(
			'label',
			array(
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label'       => esc_html__( 'Button Label', 'learndash-elementor' ),
				'label_block' => true,
				'placeholder' => __( 'Download Certificate', 'learndash-elementor' ),
				'default'     => __( 'Download Certificate', 'learndash-elementor' ),
			)
		);

		$this->add_control(
			'message',
			array(
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'label'       => esc_html__( 'Message', 'learndash-elementor' ),
				'label_block' => true,
				'placeholder' => __( 'You\'ve earned a certificate!', 'learndash-elementor' ),
				'default'     => __( 'You\'ve earned a certificate!', 'learndash-elementor' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'preview',
			array(
				'label' => __( 'Preview', 'learndash-elementor' ),
			)
		);

		$this->add_control(
			'preview_course_id',
			array(
				'label'        => sprintf(
					// translators: placeholder: Course.
					esc_html_x( '%s', 'placeholder: Course', 'learndash-elementor' ),
					\LearnDash_Custom_Label::get_label( 'course' )
				),
				'type'         => ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'options'      => array(),
				'default'      => $preview_course_id,
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
		 * Start of Style tab.
		 */
		$this->start_controls_section(
			'section_course_cert_background_border',
			array(
				'label' => __( 'Background & Border', 'learndash-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'control_course_cert_background_color',
			array(
				'label'     => __( 'Background Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper .ld-alert.ld-alert-success.ld-alert-certificate' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'control_course_cert_border_color',
			array(
				'label'     => __( 'Border Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $this->learndash_get_template_color( 'secondary' ),
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper .ld-alert.ld-alert-success.ld-alert-certificate' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'control_course_cert_border_width',
			array(
				'label'     => __( 'Border Width', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 2,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 20,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper .ld-alert.ld-alert-success.ld-alert-certificate' => 'border-width: {{SIZE}}{{UNIT}}; border-style: solid;',
				),
			)
		);

		$this->add_control(
			'control_course_cert_border_radius',
			array(
				'label'     => __( 'Border Radius', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 6,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 20,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper .ld-alert.ld-alert-success.ld-alert-certificate' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_course_cert_title',
			array(
				'label' => __( 'Title', 'learndash-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'control_course_cert_title_text_color',
			array(
				'label'     => __( 'Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper .ld-alert.ld-alert-success.ld-alert-certificate .ld-alert-messages' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'control_course_cert_title_text',
				'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
				'selector' => '{{WRAPPER}} .learndash-wrapper .ld-alert.ld-alert-success.ld-alert-certificate .ld-alert-messages',
				'exclude'  => array( 'line_height' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_course_cert_icon',
			array(
				'label' => __( 'Icon', 'learndash-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'control_course_cert_title_icon_color',
			array(
				'label'     => __( 'Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper .ld-alert.ld-alert-success.ld-alert-certificate .ld-icon-certificate' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'control_course_cert_title_icon_background_color',
			array(
				'label'     => __( 'Background Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $this->learndash_get_template_color( 'secondary' ),
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper .ld-alert.ld-alert-success.ld-alert-certificate .ld-icon-certificate' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_course_cert_download',
			array(
				'label' => __( 'Download Button', 'learndash-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'control_course_cert_download_color',
			array(
				'label'     => __( 'Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper .ld-alert.ld-alert-success.ld-alert-certificate .ld-button' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'control_course_cert_download_background_color',
			array(
				'label'     => __( 'Background Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $this->learndash_get_template_color( 'primary' ),
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper .ld-alert.ld-alert-success.ld-alert-certificate .ld-button' => 'background-color: {{VALUE}} !important;',
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
					case 'course_id':
					case 'preview_course_id':
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

		if ( empty( $shortcode_pairs['label'] ) ) {
			$shortcode_pairs['label'] = __( 'Download Certificate', 'learndash-elementor' );
		}
		if ( empty( $shortcode_pairs['message'] ) ) {
			$shortcode_pairs['message'] = __( 'You\'ve earned a certificate!', 'learndash-elementor' );
		}

		if ( is_admin() ) {
			if ( ( ! isset( $shortcode_pairs['preview_course_id'] ) ) || ( empty( $shortcode_pairs['preview_course_id'] ) ) ) {
				$shortcode_pairs['preview_course_id'] = $this->learndash_get_preview_post_id( learndash_get_post_type_slug( 'course' ) );
			}

			if ( ( empty( $shortcode_pairs['course_id'] ) ) && ( ! empty( $shortcode_pairs['preview_course_id'] ) ) ) {
				$shortcode_pairs['course_id'] = absint( $shortcode_pairs['preview_course_id'] );
				unset( $shortcode_pairs['preview_course_id'] );
			}

			if ( ( empty( $shortcode_pairs['user_id'] ) ) && ( ! empty( $shortcode_pairs['preview_user_id'] ) ) ) {
				$shortcode_pairs['user_id'] = absint( $shortcode_pairs['preview_user_id'] );
				unset( $shortcode_pairs['preview_user_id'] );
			}

			$course_certficate_link = '#';
		} else {
			unset( $shortcode_pairs['preview_course_id'] );
			unset( $shortcode_pairs['preview_user_id'] );

			if ( ( ! isset( $shortcode_pairs['course_id'] ) ) || ( empty( $shortcode_pairs['course_id'] ) ) ) {
				if ( in_array( get_post_type(), learndash_get_post_types(), true ) ) {
					$shortcode_pairs['course_id'] = learndash_get_course_id( get_the_id() );
				}
			}

			if ( ( ! isset( $shortcode_pairs['user_id'] ) ) || ( empty( $shortcode_pairs['user_id'] ) ) ) {
				$shortcode_pairs['user_id'] = get_current_user_id();
			}

			if ( ( ! empty( $shortcode_pairs['user_id'] ) ) && ( ! empty( $shortcode_pairs['course_id'] ) ) ) {
				$course_certficate_link = learndash_get_course_certificate_link( $shortcode_pairs['course_id'], $shortcode_pairs['user_id'] );
			}
		}

		if ( ! empty( $course_certficate_link ) ) {
			$certificate_alert = learndash_get_template_part(
				'modules/alert.php',
				array(
					'type'    => 'success ld-alert-certificate',
					'icon'    => 'certificate',
					'message' => $shortcode_pairs['message'],
					'button'  => array(
						'url'    => $course_certficate_link,
						'icon'   => 'download',
						'label'  => $shortcode_pairs['label'],
						'target' => '_new',
					),
				),
				false
			);
			if ( ! empty( $certificate_alert ) ) {
				echo '<div class="' . esc_attr( learndash_get_wrapper_class( $shortcode_pairs['course_id'] ) ) . '">' . $certificate_alert . '</div>';
			}
		}
	}
}
