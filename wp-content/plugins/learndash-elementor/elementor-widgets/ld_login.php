<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * LearnDash Elementor Widget for ld_login shortcode.
 *
 * @since 1.0.0
 * @package LearnDash
 */
class LearnDash_Elementor_Widget_Login extends LearnDash_Elementor_Widget_Base {

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
		$this->widget_slug  = 'ld-login';
		$this->widget_title = esc_html__( 'LearnDash Login', 'learndash-elementor' );
		$this->widget_icon  = 'eicon-lock-user';

		$this->shortcode_slug   = 'learndash_login';
		$this->shortcode_params = array(
			'preview_action'   => 'preview_action',

			'login_url'        => 'login_url',
			'login_label'      => 'login_label',
			'login_placement'  => 'login_placement',
			'login_button'     => 'login_button',

			'logout_url'       => 'logout_url',
			'logout_label'     => 'logout_label',
			'logout_placement' => 'logout_placement',
			'logout_button'    => 'logout_button',

		);

		parent::__construct( $data, $args );
	}

	/** Documented in Elementor /includes/base/controls-stack.php */
	protected function _register_controls() {

		$this->start_controls_section(
			'preview_section',
			array(
				'label' => __( 'Preview Settings', 'learndash-elementor' ),
			)
		);

		$this->add_control(
			'preview_action',
			array(
				'label'       => esc_html__( 'Preview Login/Logout Status', 'learndash-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => array(
					'login'  => __( 'Login', 'learndash-elementor' ),
					'logout' => __( 'Logout', 'learndash-elementor' ),
				),
				'default'     => 'login',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'login_section',
			array(
				'label' => __( 'Login Settings', 'learndash-elementor' ),
			)
		);

		$this->add_control(
			'login_url',
			array(
				'label'       => esc_html__( 'Login URL', 'learndash-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'description' => esc_html__( 'Override default login URL', 'learndash-elementor' ),
			)
		);

		$this->add_control(
			'login_label',
			array(
				'label'       => esc_html__( 'Login Label', 'learndash-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'description' => esc_html__( 'Override default label "Login"', 'learndash-elementor' ),
				'placeholder' => esc_html__( 'Login', 'learndash-elementor' ),
			)
		);

		$this->add_control(
			'login_placement',
			array(
				'label'       => esc_html__( 'Login Icon Placement', 'learndash-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => array(
					''      => __( 'Left - To left of label', 'learndash-elementor' ),
					'right' => __( 'Right - To right of label', 'learndash-elementor' ),
					'none'  => __( 'None - No icon', 'learndash-elementor' ),
				),
			)
		);

		$this->add_control(
			'login_button',
			array(
				'label'       => esc_html__( 'Login Displayed as', 'learndash-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'description' => esc_html__( 'Display as Button or link', 'learndash-elementor' ),
				'label_block' => true,
				'options'     => array(
					''     => __( 'Button', 'learndash-elementor' ),
					'link' => __( 'Link', 'learndash-elementor' ),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'logout_section',
			array(
				'label' => __( 'Logout Settings', 'learndash-elementor' ),
			)
		);

		$this->add_control(
			'logout_url',
			array(
				'label'       => esc_html__( 'Logout URL', 'learndash-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'description' => esc_html__( 'Override default logout URL', 'learndash-elementor' ),
			)
		);

		$this->add_control(
			'logout_label',
			array(
				'label'       => esc_html__( 'Logout Label', 'learndash-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'description' => esc_html__( 'Override default label "Logout"', 'learndash-elementor' ),
				'placeholder' => esc_html__( 'Logout', 'learndash-elementor' ),
			)
		);

		$this->add_control(
			'logout_placement',
			array(
				'label'       => esc_html__( 'Logout Icon Placement', 'learndash-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => array(
					'left'  => __( 'Left - To left of label', 'learndash-elementor' ),
					''      => __( 'Right - To right of label', 'learndash-elementor' ),
					'none'  => __( 'None - No icon', 'learndash-elementor' ),
				),
			)
		);

		$this->add_control(
			'logout_button',
			array(
				'label'       => esc_html__( 'Logout Displayed as', 'learndash-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'description' => esc_html__( 'Display as Button or link', 'learndash-elementor' ),
				'label_block' => true,
				'options'     => array(
					''     => __( 'Button', 'learndash-elementor' ),
					'link' => __( 'Link', 'learndash-elementor' ),
				),
			)
		);

		$this->end_controls_section();

		/**
		 * Start of Style tab.
		 */
		$this->start_controls_section(
			'section_login',
			array(
				'label' => __( 'Login', 'learndash-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'control_login_text',
				'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
				'selector' => '{{WRAPPER}} .learndash-wrapper .ld-login.ld-button',
				'exclude'  => array( 'line_height' ),
			)
		);

		$this->add_control(
			'control_login_text_color',
			array(
				'label'     => __( 'Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper .ld-login.ld-button' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'control_login_text_background_color',
			array(
				'label'     => __( 'Background Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $this->learndash_get_template_color( 'primary' ),
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper .ld-login.ld-button' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_logout',
			array(
				'label' => __( 'Logout', 'learndash-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'control_logout_text',
				'scheme'   => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2,
				'selector' => '{{WRAPPER}} .learndash-wrapper .ld-logout.ld-button',
				'exclude'  => array( 'line_height' ),
			)
		);

		$this->add_control(
			'control_logout_text_color',
			array(
				'label'     => __( 'Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper .ld-logout.ld-button' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'control_logout_text_background_color',
			array(
				'label'     => __( 'Background Color', 'learndash-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $this->learndash_get_template_color( 'primary' ),
				'selectors' => array(
					'{{WRAPPER}} .learndash-wrapper .ld-logout.ld-button' => 'background-color: {{VALUE}} !important;',
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

		// Remove the preview state when rending for the front-end.
		if ( ! is_admin() ) {
			unset( $shortcode_pairs['preview_action'] );
		}

		// These default values really should be determined within the shortcode.
		if ( empty( $shortcode_pairs['login_label'] ) ) {
			$shortcode_pairs['login_label'] = esc_html__( 'Login', 'learndash-elementor' );
		}
		if ( empty( $shortcode_pairs['logout_label'] ) ) {
			$shortcode_pairs['logout_label'] = esc_html__( 'Logout', 'learndash-elementor' );
		}

		$shortcode_params_str = '';
		foreach ( $shortcode_pairs as $key => $val ) {
			if ( '' !== $val ) {
				$shortcode_params_str .= ' ' . $key . '="' . $val . '"';
			}
		}
		if ( ! empty( $shortcode_params_str ) ) {
			$shortcode_params_str = '[' . $this->shortcode_slug . $shortcode_params_str . ']';
			echo do_shortcode( $shortcode_params_str );
		}
	}
}
