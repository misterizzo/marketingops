<?php
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Scheme_Typography;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Widget_Base;

use Elementor\TemplateLibrary\Source_Local;
use ElementorPro\Core\Utils;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ( class_exists( 'Elementor\Widget_Base' ) ) && ( ! class_exists( 'LearnDash_Elementor_Widget_Base' ) ) ) {
	/**
	 * LearnDash Elementor Widget Base
	 *
	 * @since 1.0.0
	 * @package LearnDash
	 */
	class LearnDash_Elementor_Widget_Base extends Elementor\Widget_Base {

		/**
		 * Widget Slug.
		 *
		 * @var string $widget_slug.
		 */
		protected $widget_slug;

		/**
		 * Widget Label.
		 *
		 * @var string $widget_label.
		 */
		protected $widget_title;

		/**
		 * Widget Icon.
		 *
		 * @var string $widget_icon.
		 */
		protected $widget_icon;

		/**
		 * Shortcode Slug.
		 *
		 * @var string $shortcode_slug.
		 */
		protected $shortcode_slug;

		/**
		 * Private array to map external to internal
		 * shortcode params.
		 *
		 * @var array $shortcode_params.
		 */
		protected $shortcode_params = array();

		/** Documented in Elementor includes/widgets/menu-anchor.php */
		public function get_name() {
			return $this->widget_slug;
		}

		/** Documented in Elementor includes/widgets/menu-anchor.php */
		public function get_title() {
			return $this->widget_title;
		}

		/** Documented in Elementor includes/widgets/menu-anchor.php */
		public function get_icon() {
			return $this->widget_icon;
		}

		/** Documented in Elementor includes/widgets/menu-anchor.php */
		public function get_categories() {
			if ( in_array(
				get_class( $this ),
				array(
					'LearnDash_Elementor_Widget_Course_Content',
					'LearnDash_Elementor_Widget_Quiz',
					'LearnDash_Elementor_Widget_Course_Certificate',
					'LearnDash_Elementor_Widget_Course_Infobar',
					'LearnDash_Elementor_Widget_Course_Progress',
				)
			) ) {
				return array( 'learndash-course-elements' );
			} else {
				return array( 'learndash-elements' );
			}
		}

		/** Documented in Elementor includes/widgets/menu-anchor.php */
		public function get_keywords() {
			return array( 'learndash' );
		}

		/**
		 * Get preview post ID.
		 *
		 * This is used by widgets having a preview section.
		 *
		 * @since 1.0.0
		 * @param string $post_type Post Type Slug to retreive.
		 * @return integer $post_id Post ID.
		 */
		public function learndash_get_preview_post_id( $post_type = '' ) {
			static $preview_post_types = array();

			$post_id = 0;

			if ( ! empty( $post_type ) ) {
				if ( isset( $preview_post_types[ $post_type ] ) ) {
					$post_id = $preview_post_types[ $post_type ];
				} else {
					$post_id = apply_filters( 'learndash_elementor_widget_preview_id', $post_id, 'post_id', $post_type, $this->widget_slug );
					$post_id = absint( $post_id );
					if ( ! empty( $post_id ) ) {
						$_post = get_post( $post_id );
						if ( ( ! $_post ) || ( ! is_a( $_post, 'WP_Post' ) ) || ( $post_type !== $_post->post_type ) ) {
							$post_id = 0;
						}
					}

					if ( empty( $post_id ) ) {
						$post_id = learndash_get_single_post( $post_type );
					}

					if ( ! empty( $post_id ) ) {
						$preview_post_types[ $post_type ] = $post_id;
					}
				}
			}

			return $post_id;
		}

		/**
		 * Get the color scheme from the LD30 template logic.
		 *
		 * @param string $color_key Key for color to retreive. default 'primary'.
		 * @return string color hex code.
		 */
		protected function learndash_get_template_color( $color_key = 'primary' ) {
			$returned_color = '';

			$colors = apply_filters(
				'learndash_30_custom_colors',
				array(
					'primary'   => LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'color_primary' ),
					'secondary' => LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'color_secondary' ),
					'tertiary'  => LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'color_tertiary' ),
				)
			);

			switch ( $color_key ) {
				case 'secondary':
					$returned_color = LD_30_COLOR_SECONDARY;
					if ( ( isset( $colors['secondary'] ) ) && ( ! empty( $colors['secondary'] ) ) && ( LD_30_COLOR_SECONDARY !== $colors['secondary'] ) ) {
						$returned_color = $colors['secondary'];
					}
					break;

				case 'tertiary':
					$returned_color = LD_30_COLOR_TERTIARY;
					if ( ( isset( $colors['tertiary'] ) ) && ( ! empty( $colors['tertiary'] ) ) && ( LD_30_COLOR_TERTIARY !== $colors['tertiary'] ) ) {
						$returned_color = $colors['tertiary'];
					}
					break;

				case 'primary':
				default:
					$returned_color = LD_30_COLOR_PRIMARY;
					if ( ( isset( $colors['primary'] ) ) && ( ! empty( $colors['primary'] ) ) && ( LD_30_COLOR_PRIMARY !== $colors['primary'] ) ) {
						$returned_color = $colors['primary'];
					}
					break;
			}

			return $returned_color;
		}

		/**
		 * When the post is save we don't want out widget content saved raw to the post_content.
		 */
		public function render_plain_content() {
			return;
		}

		// End of functions.
	}
}
