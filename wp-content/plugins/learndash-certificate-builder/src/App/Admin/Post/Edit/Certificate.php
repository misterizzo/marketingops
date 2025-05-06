<?php
/**
 * Certificate post edit class file.
 *
 * @since 1.1.3
 *
 * @package LearnDash\Certificate_Builder
 */

namespace LearnDash\Certificate_Builder\Admin\Post\Edit;

use LDLMS_Post_Types;
use WP_Post;
use WP_Screen;

/**
 * Certificate post edit class.
 *
 * @since 1.1.3
 */
class Certificate {
	/**
	 * Update the view permalink for the certificate post type.
	 *
	 * @since 1.1.3
	 *
	 * @param string  $link The post type link.
	 * @param WP_Post $post The post object.
	 *
	 * @return string
	 */
	public function update_view_permalink( $link, $post ) {
		if (
			! is_admin()
			|| learndash_get_post_type_slug( LDLMS_Post_Types::CERTIFICATE ) !== $post->post_type
		) {
			return $link;
		}

		$screen = get_current_screen();
		if (
			! $screen instanceof WP_Screen
			|| ! $screen->is_block_editor()
		) {
			return $link;
		}

		return add_query_arg(
			[
				'preview' => 'true',
			],
			$link
		);
	}
}
