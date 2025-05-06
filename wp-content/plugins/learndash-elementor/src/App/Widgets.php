<?php
/**
 * Integration file for Elementor widgets-related stuff.
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor;

use Elementor\Widgets_Manager;

/**
 * Widgets integration class.
 *
 * @since 1.0.5
 */
class Widgets {
	/**
	 * Typography scheme key.
	 *
	 * Elementor typography scheme key used in the plugin. It accepts number string range from '1' - '4'.
	 *
	 * @see Elementor\Core\Kits\Manager::map_scheme_to_global()
	 *
	 * @since 1.0.7
	 *
	 * @var string
	 */
	public static $typography_scheme_key = '2';

	/**
	 * Register Elementor editor widgets.
	 *
	 * @since 1.0.5
	 *
	 * @param Widgets_Manager $widgets_manager Instance of Widgets_Manager class.
	 */
	public function register( $widgets_manager ) {
		if (
			! function_exists( 'learndash_is_active_theme' )
			|| ! learndash_is_active_theme( 'ld30' )
		) {
			return;
		}

		$template_type = learndash_elementor_get_template_type();

		if (
			! $template_type
			|| in_array( $template_type, learndash_get_post_types( 'course' ), true )
		) {
			if ( learndash_get_post_type_slug( 'quiz' ) !== $template_type ) {
				$widgets_manager->register( new Widgets\Course_Content() );
			} elseif ( learndash_get_post_type_slug( 'quiz' ) === $template_type ) {
				$widgets_manager->register( new Widgets\Quiz() );
			}

			$widgets_manager->register( new Widgets\Course_Infobar() );

			if (
				! $template_type
				|| learndash_get_post_type_slug( 'course' ) === $template_type
			) {
				$widgets_manager->register( new Widgets\Course_Certificate() );
			}
		}

		$widgets_manager->register( new Widgets\Login() );
		$widgets_manager->register( new Widgets\Profile() );
		$widgets_manager->register( new Widgets\Course_List() );
		$widgets_manager->register( new Widgets\Lesson_List() );
		$widgets_manager->register( new Widgets\Topic_List() );
		$widgets_manager->register( new Widgets\Quiz_List() );
	}

	/**
	 * Filter 'elementor/widget/render_content' hook to check for LearnDash
	 * completed step.
	 *
	 * @since 1.0.5
	 *
	 * @param string                 $content Original widget content.
	 * @param \Elementor\Widget_Base $widget  Widget base object.
	 *
	 * @return string Widget content.
	 */
	public function filter_render_content( $content, $widget ): string {
		$post = learndash_elementor_get_learndash_post();

		if ( $post ) {
			$checked_widgets = [
				'ld-course-certificate',
				'ld-course-content',
				'ld-course-infobar',
				'ld-course-list',
				'ld-course-progress',
				'ld-lesson-list',
				'ld-login',
				'ld-payment-buttons',
				'ld-profile',
				'ld-quiz-list',
				'ld-quiz',
				'ld-topic-list',
				'ld-video',
			];

			$allowed_widgets = apply_filters(
				'learndash_elementor_not_completed_step_allowed_widgets',
				array(
					'ld-course-infobar',
					'ld-course-content',
					'ld-course-list',
					'ld-lesson-list',
					'ld-login',
					'ld-payment-buttons',
					'ld-profile',
					'ld-quiz',
					'ld-quiz-list',
					'ld-topic-list',
				),
				$widget,
				$post
			);

			if (
				in_array( $widget->get_name(), $checked_widgets, true )
				&& ! learndash_elementor_is_previous_step_completed()
				&& ! in_array( $widget->get_name(), $allowed_widgets, true )
			) {
				$content = '';
			}
		}

		return $content;
	}
}
