<?php

use ElementorPro\Modules\ThemeBuilder\Documents\Single;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( class_exists( 'ElementorPro\Modules\ThemeBuilder\Documents\Single' ) ) && ( ! class_exists( 'LearnDash_Elementor_Document_Base' ) ) ) {
	/**
	 * Class for LearnDash_Elementor_Document_Base.
	 */
	class LearnDash_Elementor_Document_Base extends ElementorPro\Modules\ThemeBuilder\Documents\Single {

		/**
		 * Private var for post type used within this template.
		 *
		 * @var string $post_type_slug.
		 */
		protected static $post_type_slug = null;

		/**
		 * Class constructor.
		 *
		 * @param array $data Data.
		 */
		public function __construct( array $data = array() ) {
			// Hook into the LearnDash template logic and short-circut it if needed.
			add_filter( 'learndash_template_preprocess_filter', array( $this, 'learndash_template_preprocess_filter' ), 30, 2 );
	
			parent::__construct( $data );
		}

		/**
		 * Hook into the LearnDash template preprocess filter to see if we need to abort it.
		 *
		 * @since 1.0.0
		 * @param boolean $process_template True or False.
		 * @param integer $post_id Post ID being processed by LearnDash.
		 */
		public function learndash_template_preprocess_filter( $process_template, $post_id = 0 ) {
			if ( ( function_exists( 'learndash_is_active_theme' ) ) && ( learndash_is_active_theme( 'ld30' ) ) ) {
				if ( is_singular( self::$post_type_slug ) ) {
					if ( learndash_get_post_type_slug( 'course' ) === self::$post_type_slug ) {
						// If 'Course' we stop the normal 'the_content' filtering. 
						$process_template = false;
					} elseif ( in_array( self::$post_type_slug, learndash_get_post_types( 'course_steps' ), true ) ) {
						// If a course step, we stop the normal 'the_content' filtering only if not using Focus Mode. 
						if ( 'yes' !== LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'focus_mode_enabled' ) ) {
							$process_template = false;
						}
					}
				}
			}

			return $process_template;
		}

		/** Documented in Elementor core/base/document.php */
		protected static function get_editor_panel_categories() {

			$categories   = array();
			$widget_title = '';

			$template_type = learndash_elementor_get_template_type();
			if ( learndash_get_post_type_slug( 'course' ) === $template_type ) {
				$widget_title = sprintf(
					// translators: placeholder: Course.
					esc_html_x( 'LearnDash %s Elements', 'placeholder: Course', 'learndash-elementor' ),
					\LearnDash_Custom_Label::get_label( 'course' )
				);
			} elseif ( learndash_get_post_type_slug( 'lesson' ) === $template_type ) {
				$widget_title = sprintf(
					// translators: placeholder: Lesson.
					esc_html_x( 'LearnDash %s Elements', 'placeholder: Lesson', 'learndash-elementor' ),
					\LearnDash_Custom_Label::get_label( 'lesson' )
				);
			} elseif ( learndash_get_post_type_slug( 'topic' ) === $template_type ) {
				$widget_title = sprintf(
					// translators: placeholder: Topic.
					esc_html_x( 'LearnDash %s Elements', 'placeholder: Topic', 'learndash-elementor' ),
					\LearnDash_Custom_Label::get_label( 'topic' )
				);
			} elseif ( learndash_get_post_type_slug( 'quiz' ) === $template_type ) {
				$widget_title = sprintf(
					// translators: placeholder: Quiz.
					esc_html_x( 'LearnDash %s Elements', 'placeholder: Quiz', 'learndash-elementor' ),
					\LearnDash_Custom_Label::get_label( 'quiz' )
				);
			}

			if ( ! empty( $widget_title ) ) {
				// Move to top as active.
				$categories = array(
					'learndash-course-elements' => array(
						'title'  => $widget_title,
						'active' => true,
					),
				);
			}

			$other_categories = parent::get_editor_panel_categories();
			if ( isset( $other_categories['learndash-elements'] ) ) {
				$categories['learndash-elements'] = $other_categories['learndash-elements'];
				$categories['learndash-elements']['active'] = false;
				unset( $other_categories['learndash-elements'] );
			}

			// Since we are a LearnDash Post, we set the other sections non-active.
			if ( ! empty( $other_categories ) ) {
				foreach ( $other_categories as $key => $set ) {
					$set['active']      = false;
					$categories[ $key ] = $set;
				}
			}

			return $categories;
		}

		// End of functions.
	}
}
