<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * LearnDash Elementor Widget for ld_video shortcode.
 *
 * @since 1.0.0
 * @package LearnDash
 */
class LearnDash_Elementor_Widget_Video extends LearnDash_Elementor_Widget_Base {

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
		$this->widget_slug  = 'ld-video';
		$this->widget_title = esc_html__( 'Video Progress', 'learndash-elementor' );
		$this->widget_icon = 'fas fa-file-video';

		$this->shortcode_slug   = 'ld_video';
		$this->shortcode_params = array();

		parent::__construct( $data, $args );
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

		$shortcode_params_str = '[' . $this->shortcode_slug . ']';
		$shortcode_content = do_shortcode( $shortcode_params_str );
		echo $shortcode_content;
	}
}
