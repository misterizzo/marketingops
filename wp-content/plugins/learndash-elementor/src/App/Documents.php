<?php
/**
 * Integration file for Elementor documents-related stuff.
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor;

use Elementor\Core\Documents_Manager;

/**
 * Element documents integration class.
 *
 * @since 1.0.5
 */
class Documents {
	/**
	 * Hook into the Elementor Document Manager to register our custom templates.
	 *
	 * @since 1.0.5
	 *
	 * @param Documents_Manager $documents_manager Instance of Documents_Manager.
	 *
	 * @return void
	 */
	public function register( Documents_Manager $documents_manager ): void {
		if (
			! function_exists( 'learndash_is_active_theme' )
			|| ! learndash_is_active_theme( 'ld30' )
		) {
			return;
		}

		$documents_manager->register_document_type( learndash_get_post_type_slug( 'course' ), Documents\Course_Single::get_class_full_name() );

		$documents_manager->register_document_type( learndash_get_post_type_slug( 'lesson' ), Documents\Lesson_Single::get_class_full_name() );

		$documents_manager->register_document_type( learndash_get_post_type_slug( 'topic' ), Documents\Topic_Single::get_class_full_name() );

		$documents_manager->register_document_type( learndash_get_post_type_slug( 'quiz' ), Documents\Quiz_Single::get_class_full_name() );
	}
}
