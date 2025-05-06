<?php
/**
 * Base LearnDash Elementor Widget base class file.
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor\Widgets;

use Elementor\Widget_Base;
use LearnDash_Settings_Section;

/**
 * LearnDash Elementor Widget Base class.
 *
 * @since 1.0.5
 */
class Base extends Widget_Base {
	/**
	 * Widget Slug.
	 *
	 * @since 1.0.5
	 *
	 * @var string
	 */
	protected $widget_slug;

	/**
	 * Widget Label.
	 *
	 * @since 1.0.5
	 *
	 * @var string
	 */
	protected $widget_title;

	/**
	 * Widget Icon.
	 *
	 * @since 1.0.5
	 *
	 * @var string
	 */
	protected $widget_icon;

	/**
	 * Shortcode Slug.
	 *
	 * @since 1.0.5
	 *
	 * @var string
	 */
	protected $shortcode_slug;

	/**
	 * Private array to map external to internal
	 * shortcode params.
	 *
	 * @since 1.0.5
	 *
	 * @var array
	 */
	protected $shortcode_params = [];

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
		return [ 'learndash' ];
	}

	/** Documented in Elementor includes/widgets/menu-anchor.php */
	public function get_keywords() {
		return [ 'learndash' ];
	}

	/**
	 * Get preview post ID.
	 *
	 * This is used by widgets having a preview section.
	 *
	 * @since 1.0.5
	 *
	 * @param string $post_type Post Type Slug to retrieve.
	 *
	 * @return int $post_id Post ID.
	 */
	public function learndash_get_preview_post_id( $post_type = '' ): int {
		static $preview_post_types = [];

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

		return intval( $post_id );
	}

	/**
	 * Get the color scheme from the LD30 template logic.
	 *
	 * @since 1.0.5
	 *
	 * @param string $color_key Key for color to retrieve. Default 'primary'.
	 *
	 * @return string Color hex code.
	 */
	protected function learndash_get_template_color( $color_key = 'primary' ): string {
		$returned_color = '';

		$colors = apply_filters(
			'learndash_30_custom_colors',
			[
				'primary'   => LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'color_primary' ),
				'secondary' => LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'color_secondary' ),
				'tertiary'  => LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'color_tertiary' ),
			]
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
	 * When the post is saved we don't want out widget content saved raw to the post_content.
	 *
	 * @since 1.0.5
	 *
	 * @return void
	 */
	public function render_plain_content(): void {
		return;
	}
}
