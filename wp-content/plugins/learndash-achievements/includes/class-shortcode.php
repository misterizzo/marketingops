<?php
/**
 * Shortcode class file.
 *
 * @since 1.0
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements;

use LearnDash\Achievements\Database;
use LearnDash\Achievements\Utilities\Assets;
use LearnDash\Core\Utilities\Cast;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode class.
 *
 * @since 1.0
 */
class Shortcode {
	/**
	 * Initial
	 */
	public static function init() {
		// Hooks.
		add_action( 'init', [ __CLASS__, 'register_shortcodes' ] );
	}

	/**
	 * Register the shortcodes.
	 */
	public static function register_shortcodes() {
		add_shortcode( 'ld_achievements_leaderboard', [ __CLASS__, 'ld_achievements_leaderboard_shortcode' ] );
		add_shortcode( 'ld_my_achievements', [ __CLASS__, 'ld_my_achievements_shortcode' ] );
		// add_shortcode( 'ld_achievements', array( __CLASS__, 'ld_achievements_shortcode' ) );
	}

	/**
	 * Output the leader board content.
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param string $content The content.
	 * @param string $shortcode_slug The shortcode slug. Default 'ld_achievements_leaderboard'.
	 *
	 * @return false|string
	 * @deprecated
	 */
	public static function ld_achievements_leaderboard_shortcode( $atts = [], $content = '', $shortcode_slug = 'ld_achievements_leaderboard' ) {
		$defaults = [
			'number'      => 10,
			'show_points' => false,
		];
		$atts     = shortcode_atts( $defaults, $atts );

		/**
		 * Filters shortcode attributes.
		 *
		 * @since 1.2
		 *
		 * @param array  $atts           An array of shortcode attributes.
		 * @param string $shortcode_slug The current shortcode slug.
		 */
		$atts = apply_filters( 'ld_achievements_leaderboard_shortcode_atts', $atts, $shortcode_slug );

		// Get leaders in points.
		$leaders = wp_cache_get( 'leaderboard_v2_' . $atts['number'], 'learndash_achievements' );

		if ( false === $leaders ) {
			global $wpdb;

			$table_name = Database::$table_name;

			$sql     = $wpdb->prepare(
				"SELECT COUNT(user_id) as total, user_id, GROUP_CONCAT(post_id SEPARATOR ',') as post_ids, SUM(points) as total_points
				FROM %i
				WHERE `post_id` IN (SELECT ID FROM {$wpdb->prefix}posts WHERE `post_status` = 'publish' AND `post_type` = 'ld-achievement')
				GROUP BY user_id
				ORDER BY total_points DESC
				LIMIT %d",
				$table_name,
				absint( $atts['number'] )
			);
			$leaders = $wpdb->get_results( $sql, ARRAY_A );
			// we got a list of.
			foreach ( $leaders as &$row ) {
				$post_ids = $row['post_ids'];
				$post_ids = explode( ',', $post_ids );
				$post_ids = array_unique( $post_ids );
				$post_ids = array_filter( $post_ids );
				foreach ( $post_ids as $post_id ) {
					$row['images'][] = Assets::achievement_icon_url( $post_id );
					$row['post'][]   = get_post( $post_id );
				}
			}

			wp_cache_set( 'leaderboard_v2_' . $atts['number'], $leaders, 'learndash_achievements', HOUR_IN_SECONDS );
		}
		// Display leaders in leaderboard.
		ob_start();
		include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'templates/shortcode-leaderboard.php';

		return ob_get_clean();
	}

	/**
	 * Output for the shortcode [ld_my_achievements]
	 *
	 * @param array  $atts    Attributes.
	 * @param string $content Content.
	 *
	 * @phpstan-param array{
	 *     'user_id'         ?: int,
	 *     'show_title'      ?: bool,
	 *     'show_points'     ?: bool,
	 *     'points_position' ?: string,
	 *     'points_label'    ?: string
	 * } $atts
	 *
	 * @return false|string
	 */
	public static function ld_my_achievements_shortcode( $atts, $content ) {
		// Treat string "false" as actual false for show_title and show_points parameters.

		$bool_atts = [
			'show_title',
			'show_points',
		];

		foreach ( $bool_atts as $key ) {
			if ( ! isset( $atts[ $key ] ) ) {
				continue;
			}

			$atts[ $key ] = filter_var( $atts[ $key ], FILTER_VALIDATE_BOOLEAN );
		}

		$atts = shortcode_atts(
			[
				'user_id'         => get_current_user_id(),
				'show_title'      => false,
				'show_points'     => false,
				'points_position' => 'after',
				'points_label'    => __( 'Points', 'learndash-achievements' ),
			],
			$atts,
			'ld_my_achievements'
		);

		$achievements = wp_cache_get( 'achievements_' . $atts['user_id'], 'learndash_achievements' );

		if ( false === $achievements ) {
			global $wpdb;

			$table_name = Database::$table_name;

			$sql = $wpdb->prepare(
				"SELECT `user_id`, `post_id`, `points`, COUNT(*) c FROM {$table_name} WHERE `user_id` = %d GROUP BY `post_id`",
				$atts['user_id']
			);

			$achievements = $wpdb->get_results( $sql );

			wp_cache_set( 'achievements_' . $atts['user_id'], $achievements, 'learndash_achievements', HOUR_IN_SECONDS );
		}

		ob_start();
		include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'templates/shortcode-achievements.php';

		return ob_get_clean();
	}

	/**
	 * Output the points of an user.
	 *
	 * @param array  $atts attributes.
	 * @param string $content Content.
	 *
	 * @return int|mixed|string
	 */
	public static function ld_achievements_shortcode( $atts, $content ) {
		global $wpdb, $post;

		$atts = shortcode_atts(
			[
				'post_id' => 0,
				'user_id' => 0,
				'show'    => '',
			],
			$atts,
			'ld_achievements'
		);

		if ( empty( $atts['post_id'] ) || empty( $atts['user_id'] ) ) {
			return '';
		}

		switch ( $atts['show'] ) {
			case 'points':
				$points = get_post_meta( $atts['post_id'], 'points', true );
				$result = ! empty( $points ) ? $points : 0;
				break;

			default:
				$result = '';
				break;
		}

		return $result;
	}
}

Shortcode::init();
