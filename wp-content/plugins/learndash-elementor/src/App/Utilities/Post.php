<?php
/**
 * Utility class for post-related stuff.
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor\Utilities;

/**
 * Post utility class.
 *
 * @since 1.0.5
 */
class Post {
	/**
	 * Check if a post use Elementor edit mode.
	 *
	 * @since 1.0.5
	 *
	 * @param int $post_id WP post ID.
	 *
	 * @return boolean
	 */
	public static function is_elementor( int $post_id = 0 ): bool {
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		if ( $post_id === false ) {
			return false;
		}

		return (bool) get_post_meta( $post_id, '_elementor_edit_mode', true );
	}

	/**
	 * Extract elements from document data.
	 *
	 * @since 1.0.5
	 *
	 * @param array<array<string, mixed>> $elements Elementor document elements data.
	 *
	 * @return array<array<string, mixed>>
	 */
	public static function extract_elements( array $elements ): array {
		$returned_elements = [];

		foreach ( $elements as $element ) {
			array_push( $returned_elements, $element );

			if ( is_array( $element['elements'] ) && ! empty( $element['elements'] ) ) {
				$temp_elements = self::extract_elements( $element['elements'] );

				$returned_elements = array_merge( $returned_elements, $temp_elements );
			}
		}

		$returned_elements = array_map(
			function( $element ) {
				if ( isset( $element['elements'] ) ) {
					  unset( $element['elements'] );
				}

				return $element;
			},
			$returned_elements
		);

		return $returned_elements;
	}
}
