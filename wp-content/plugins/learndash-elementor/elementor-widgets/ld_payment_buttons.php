<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * LearnDash Elementor Widget for learndash_payment_buttons shortcode.
 *
 * @since 1.0.0
 * @package LearnDash
 */
class LearnDash_Elementor_Widget_Payment_Buttons extends LearnDash_Elementor_Widget_Base {

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
		$this->widget_slug  = 'ld-payment-buttons';
		$this->widget_title = sprintf(
			// translators: placeholder: Course.
			esc_html_x( '%s Payment Buttons', 'placeholder: Course', 'learndash-elementor' ),
			\LearnDash_Custom_Label::get_label( 'course' )
		);
		$this->widget_icon = 'eicon-cart';

		$this->shortcode_slug   = 'learndash_payment_buttons';
		$this->shortcode_params = array(
			'course_id' => 'course_id',
		);

		parent::__construct( $data, $args );
	}

	/** Documented in Elementor /includes/base/controls-stack.php */
	protected function _register_controls() {

		$this->start_controls_section(
			'login_section',
			array(
				'label' => __( 'Settings', 'learndash-elementor' ),
			)
		);

		$this->add_control(
			'course_id',
			array(
				'label'       => sprintf(
					// translators: placeholder: Course.
					esc_html_x( '%s ID', 'placeholder: Course', 'learndash-elementor' ),
					\LearnDash_Custom_Label::get_label( 'course' )
				),
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
			$shortcode_content    = do_shortcode( $shortcode_params_str );
			$shortcode_content    = str_replace( array( '<br />', '<br>', "\n", "\r" ), ' ', $shortcode_content );
			echo $shortcode_content;
		}
	}
}
