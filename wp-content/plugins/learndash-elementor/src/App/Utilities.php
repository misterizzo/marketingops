<?php
/**
 * Integration file for Elementor utilities-related stuff.
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor;

/**
 * Utilities integration class.
 *
 * @since 1.0.5
 */
class Utilities {
	/**
	 * Include the LearnDash Post Types to show when adding new Elementor Templates.
	 *
	 * Requires Elementor Pro 2.3.0 or higher.
	 *
	 * @since 1.0.5
	 *
	 * @param array $post_types array of post type slugs and labels to show.
	 *
	 * @return array Array of post types.
	 */
	public function get_public_post_types( $post_types ): array {
		if (
			function_exists( 'learndash_is_active_theme' )
			&& learndash_is_active_theme( 'ld30' )
			&& function_exists( 'learndash_get_post_type_slug' )
		) {
			$ld_post_types = [
				learndash_get_post_type_slug( 'course' ) => learndash_get_custom_label( 'courses' ),
				learndash_get_post_type_slug( 'lesson' ) => learndash_get_custom_label( 'lessons' ),
				learndash_get_post_type_slug( 'topic' )  => learndash_get_custom_label( 'topics' ),
				learndash_get_post_type_slug( 'quiz' )   => learndash_get_custom_label( 'quizzes' ),
			];

			foreach ( $ld_post_types as $ld_post_type_slug => $ld_post_type_label ) {
				if ( ! isset( $post_types[ $ld_post_type_slug ] ) ) {
					$post_types[ $ld_post_type_slug ] = $ld_post_type_label;
				}
			}
		}

		return $post_types;
	}
}
