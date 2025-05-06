<?php
/**
 * Assets utility class file.
 *
 * @since 2.0.0
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements\Utilities;

/**
 * Assets utility class.
 *
 * @since 2.0.0
 */
class Assets {
	/**
	 * Retrieves an achievement icon URL.
	 *
	 * @since 2.0.0
	 *
	 * @param int|string $post_id Post ID.
	 *
	 * @return string
	 */
	public static function achievement_icon_url( $post_id ): string {
		$image_url = get_post_meta( (int) $post_id, 'image', true );

		if ( ! is_string( $image_url ) ) {
			return '';
		}

		$path = self::icon_path( $image_url );

		if ( empty( $path ) ) {
			return $image_url;
		}

		return self::icon_url( $path );
	}

	/**
	 * Retrieves icon URL.
	 *
	 * @since 2.0.0
	 *
	 * @param string $path Icon path.
	 *
	 * @return string
	 */
	private static function icon_url( string $path ): string {
		$dist_path  = 'dist/img/icons/' . $path;
		$image_file = LEARNDASH_ACHIEVEMENTS_DIR . $dist_path;

		if ( ! file_exists( $image_file ) ) {
			return '';
		}

		return LEARNDASH_ACHIEVEMENTS_URL . $dist_path;
	}

	/**
	 * Retrieves icon URL path from an existing image URL.
	 *
	 * @since 2.0.0
	 *
	 * @param string $url Icon URL.
	 *
	 * @return string
	 */
	private static function icon_path( string $url ): string {
		/**
		 * Supports both old `/assets/` and new `/dist/` paths.
		 *
		 * Old path: http://domain.com/wp-content/plugins/learndash-achievements/assets/img/icons/achievement-icon.png
		 * New path: http://domain.com/wp-content/plugins/learndash-achievements/dist/img/icons/achievement-icon.png
		 *
		 * Regex pattern:
		 *
		 * ^LEARNDASH_ACHIEVEMENTS_URL  # Starts with site URL, we store full URL including domain name.
		 * (?:                          # Non-capturing group.
		 *     dist|assets              # Either `dist` or `assets`.
		 * )
		 * /img/icons/                  # Hardcoded path exists in both old and new paths.
		 * (.*?)$                       # 1) Icon file relative path to the icons folder.
		 */
		preg_match( '#^' . LEARNDASH_ACHIEVEMENTS_URL . '(?:dist|assets)/img/icons/(.*?)$#', $url, $matches );

		return $matches[1] ?? '';
	}
}
