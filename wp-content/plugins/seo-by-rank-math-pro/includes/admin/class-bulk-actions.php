<?php
/**
 * Bulk actions for the manage posts screen.
 *
 * @since      1.0
 * @package    RankMathPro
 * @subpackage RankMathPro\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMathPro\Admin;

use RankMath\Helper;
use RankMath\Helpers\Param;
use RankMath\Traits\Hooker;
use RankMath\Admin\Admin_Helper;
use RankMathPro\Admin\Admin_Helper as ProAdminHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Bulk actions class.
 *
 * @codeCoverageIgnore
 */
class Bulk_Actions {

	use Hooker;

	/**
	 * Register hooks.
	 */
	public function __construct() {
		$this->action( 'current_screen', 'init' );
		$this->action( 'save_post', 'save_post_primary_term' );
	}

	/**
	 * Intialize.
	 */
	public function init() {
		$post_types = Helper::get_accessible_post_types();
		foreach ( $post_types as $post_type ) {
			$this->filter( "handle_bulk_actions-edit-{$post_type}", 'handle_post_bulk_actions', 10, 3 );
		}

		$taxonomies = Helper::get_accessible_taxonomies();
		unset( $taxonomies['post_format'] );
		$taxonomies = wp_list_pluck( $taxonomies, 'label', 'name' );
		foreach ( $taxonomies as $taxonomy => $label ) {
			$this->filter( "handle_bulk_actions-edit-{$taxonomy}", 'handle_tax_bulk_actions', 10, 3 );
		}
	}

	/**
	 * Handle bulk actions for applicable posts, pages, CPTs.
	 *
	 * @param  string $redirect   Redirect URL.
	 * @param  string $doaction   Performed action.
	 * @param  array  $object_ids Post IDs.
	 *
	 * @return string             New redirect URL.
	 */
	public function handle_post_bulk_actions( $redirect, $doaction, $object_ids ) {
		$redirect = remove_query_arg(
			[
				'rank_math_bulk_robots_noindex',
				'rank_math_bulk_robots_index',
				'rank_math_bulk_robots_nofollow',
				'rank_math_bulk_robots_follow',
				'rank_math_bulk_stop_redirect',
				'rank_math_bulk_schema_none',
				'rank_math_bulk_schema_default',
				'rank_math_bulk_remove_canonical',
				'rank_math_bulk_determine_search_intent',
			],
			$redirect
		);

		$notification_type = 'success';
		$edited            = 0;
		$message           = '';
		$post_type_object  = false;

		switch ( $doaction ) {
			case 'rank_math_bulk_robots_noindex':
			case 'rank_math_bulk_robots_index':
			case 'rank_math_bulk_robots_nofollow':
			case 'rank_math_bulk_robots_follow':
				foreach ( $object_ids as $post_id ) {
					if ( ! $post_type_object ) {
						$post_type_object = get_post_type_object( get_post_type( $post_id ) );
					}
					$action = str_replace( 'rank_math_bulk_robots_', '', $doaction );
					$robots = (array) get_post_meta( $post_id, 'rank_math_robots', true );
					$robots = array_filter( $robots );

					// Remove "opposite" robots meta.
					$opposite = 'no' . $action;
					if ( substr( $action, 0, 2 ) === 'no' ) {
						$opposite = substr( $action, 2 );
					}
					if ( ( $key = array_search( $opposite, $robots ) ) !== false ) { // @codingStandardsIgnoreLine
						unset( $robots[ $key ] );
					}

					// Add new robots meta.
					if ( ! in_array( $action, $robots, true ) ) {
						$robots[] = $action;
					}
					$robots = array_unique( $robots );

					update_post_meta( $post_id, 'rank_math_robots', $robots );
					++$edited;

					if ( 'index' === $action || 'noindex' === $action ) {
						$this->do_action( 'sitemap/invalidate_object_type', 'post', $post_id );
					}
				}
				// Translators: 1 The number of posts edited. 2 The post type name.
				$message = sprintf( __( 'Robots meta edited for %1$d %2$s.', 'rank-math-pro' ), $edited, ( $edited > 1 ? $post_type_object->labels->name : $post_type_object->labels->singular_name ) );
				break;

			case 'rank_math_bulk_redirect':
				$redirect = Helper::get_admin_url( 'redirections' );
				$i        = 0;
				foreach ( $object_ids as $post_id ) {
					$post_url = get_permalink( $post_id );
					$redirect = add_query_arg( "urls[{$i}]", $post_url, $redirect );
					++$i;
				}
				break;

			case 'rank_math_bulk_stop_redirect':
				foreach ( $object_ids as $post_id ) {
					$redirection = \RankMath\Redirections\Cache::get_by_object_id( $post_id, 'post' );
					if ( $redirection ) {
						\RankMath\Redirections\DB::change_status( $redirection->redirection_id, 'trashed' );
						++$edited;
					}
				}
				// Translators: placeholder is the number of redirections deleted.
				$message = sprintf( _n( '%d redirection moved to Trash.', '%d redirections moved to Trash.', $edited, 'rank-math-pro' ), $edited );
				break;

			case 'rank_math_bulk_schema_none':
				foreach ( $object_ids as $post_id ) {
					if ( ! $post_type_object ) {
						$post_type_object = get_post_type_object( get_post_type( $post_id ) );
					}
					update_post_meta( $post_id, 'rank_math_rich_snippet', 'off' );
					$this->delete_schema( $post_id );

					++$edited;
				}
				// Translators: 1 The number of posts edited. 2 The post type name.
				$message = sprintf( __( 'Schema edited for %1$d %2$s.', 'rank-math-pro' ), $edited, ( $edited > 1 ? $post_type_object->labels->name : $post_type_object->labels->singular_name ) );
				break;

			case 'rank_math_bulk_schema_default':
				foreach ( $object_ids as $post_id ) {
					if ( ! $post_type_object ) {
						$post_type_object = get_post_type_object( get_post_type( $post_id ) );
					}
					delete_post_meta( $post_id, 'rank_math_rich_snippet' );
					$this->delete_schema( $post_id );

					++$edited;
				}
				// Translators: 1 The number of posts edited. 2 The post type name.
				$message = sprintf( __( 'Schema edited for %1$d %2$s.', 'rank-math-pro' ), $edited, ( $edited > 1 ? $post_type_object->labels->name : $post_type_object->labels->singular_name ) );
				break;

			case 'rank_math_bulk_remove_canonical':
				foreach ( $object_ids as $post_id ) {
					if ( ! $post_type_object ) {
						$post_type_object = get_post_type_object( get_post_type( $post_id ) );
					}

					if ( get_post_meta( $post_id, 'rank_math_canonical_url', true ) ) {
						delete_post_meta( $post_id, 'rank_math_canonical_url' );
						++$edited;
					}
				}
				// Translators: 1 The number of posts edited. 2 The post type name.
				$message = sprintf( __( 'Custom Canonical URL removed from %1$d %2$s.', 'rank-math-pro' ), $edited, ( $edited > 1 ? $post_type_object->labels->name : $post_type_object->labels->singular_name ) );
				break;
			case 'rank_math_bulk_determine_search_intent':
				$focus_keywords = [];
				foreach ( $object_ids as $object_id ) {
					$keyword = get_post_meta( $object_id, 'rank_math_focus_keyword', true );
					if ( $keyword ) {
						$keywords         = explode( ',', $keyword );
						$focus_keywords[] = strtolower( current( $keywords ) );
					}
				}

				if ( empty( $focus_keywords ) ) {
					$message = esc_html__( 'Focus keywords are not added for the selected posts. Please add the Focus keywords and try again.', 'rank-math-pro' );
				} else {
					$response = ProAdminHelper::determine_search_intent(
						[
							'focus_keywords' => $focus_keywords,
						],
						'bulk_keyword_intent'
					);

					if ( isset( $response['error'] ) ) {
						$message           = $response['error'];
						$notification_type = 'error';
					} else {
						$response       = json_decode( $response, true );
						$search_intents = [];
						foreach ( $response as $data ) {
							$keyword                    = strtolower( $data['focus_keyword'] );
							$search_intents[ $keyword ] = $data['intent'];
						}

						ProAdminHelper::get_search_intent( $search_intents );

						$count = count( $response );
						// Translators: placeholder is the number of Keyword intent.
						$message = sprintf( _n( 'Search Intent updated for %d Focus Keyword.', 'Search Intent updated for %d Focus Keywords.', $count, 'rank-math-pro' ), $count );
					}
				}
				break;
		}

		if ( $message ) {
			Helper::add_notification( $message, [ 'type' => $notification_type ] );
		}

		return $redirect;
	}

	/**
	 * Delete ALL existing Schema data for a post.
	 *
	 * @param int $post_id Post id.
	 */
	public function delete_schema( $post_id ) {
		global $wpdb;

		$where = $wpdb->prepare( 'WHERE post_id = %d AND meta_key LIKE %s', $post_id, $wpdb->esc_like( 'rank_math_schema_' ) . '%' );
		$wpdb->query( "DELETE FROM {$wpdb->postmeta} {$where}" ); // phpcs:ignore
	}

	/**
	 * Handle bulk actions for applicable posts, pages, CPTs.
	 *
	 * @param  string $redirect   Redirect URL.
	 * @param  string $doaction   Performed action.
	 * @param  array  $object_ids Post IDs.
	 *
	 * @return string             New redirect URL.
	 */
	public function handle_tax_bulk_actions( $redirect, $doaction, $object_ids ) {
		$redirect = remove_query_arg(
			[
				'rank_math_bulk_robots_noindex',
				'rank_math_bulk_robots_index',
				'rank_math_bulk_robots_nofollow',
				'rank_math_bulk_robots_follow',
				'rank_math_bulk_stop_redirect',
				'rank_math_bulk_schema_none',
				'rank_math_bulk_schema_default',
				'rank_math_bulk_remove_canonical',
			],
			$redirect
		);

		$edited  = 0;
		$message = '';

		$tax_object = false;
		switch ( $doaction ) {
			case 'rank_math_bulk_robots_noindex':
			case 'rank_math_bulk_robots_index':
			case 'rank_math_bulk_robots_nofollow':
			case 'rank_math_bulk_robots_follow':
				foreach ( $object_ids as $term_id ) {
					if ( ! $tax_object ) {
						$tax_object = get_taxonomy( get_term( $term_id )->taxonomy );
					}
					$action = str_replace( 'rank_math_bulk_robots_', '', $doaction );
					$robots = (array) get_term_meta( $term_id, 'rank_math_robots', true );
					$robots = array_filter( $robots );

					// Remove "opposite" robots meta.
					$opposite = 'no' . $action;
					if ( substr( $action, 0, 2 ) === 'no' ) {
						$opposite = substr( $action, 2 );
					}
					if ( ( $key = array_search( $opposite, $robots ) ) !== false ) { // @codingStandardsIgnoreLine
						unset( $robots[ $key ] );
					}

					// Add new robots meta.
					if ( ! in_array( $action, $robots, true ) ) {
						$robots[] = $action;
					}
					$robots = array_unique( $robots );

					update_term_meta( $term_id, 'rank_math_robots', $robots );
					++$edited;

					if ( 'index' === $action || 'noindex' === $action ) {
						$this->do_action( 'sitemap/invalidate_object_type', 'term', $term_id );
					}
				}
				// Translators: 1 The number of terms edited. 2 The term taxonomy name.
				$message = sprintf( __( 'Robots meta edited for %1$d %2$s.', 'rank-math-pro' ), $edited, ( $edited > 1 ? $tax_object->labels->name : $tax_object->labels->singular_name ) );
				break;

			case 'rank_math_bulk_redirect':
				$redirect = Helper::get_admin_url( 'redirections' );
				$i        = 0;
				foreach ( $object_ids as $term_id ) {
					$term_url = get_term_link( (int) $term_id );
					if ( is_wp_error( $term_url ) ) {
						continue;
					}

					$redirect = add_query_arg( "urls[{$i}]", $term_url, $redirect );
					++$i;
				}
				break;

			case 'rank_math_bulk_stop_redirect':
				foreach ( $object_ids as $term_id ) {
					$redirection = \RankMath\Redirections\Cache::get_by_object_id( $term_id, 'term' );
					if ( $redirection ) {
						\RankMath\Redirections\DB::change_status( $redirection->redirection_id, 'trashed' );
						++$edited;
					}
				}
				// Translators: placeholder is the number of redirections deleted.
				$message = sprintf( _n( '%d redirection moved to Trash.', '%d redirections moved to Trash.', $edited, 'rank-math-pro' ), $edited );
				break;
		}

		if ( $message ) {
			Helper::add_notification( $message );
		}

		return $redirect;
	}

	/**
	 * Save primary term bulk edit. This handles the action performed when the
	 * user selects one or more posts with the checkbox and then selects "Edit"
	 * in the Bulk Edit dropdown.
	 *
	 * @param  int $post_id Post ID.
	 *
	 * @return void
	 */
	public function save_post_primary_term( $post_id ) {
		if ( Param::get( 'rank_math_quick_edit_nonce' ) ) {
			return;
		}
		if ( ! wp_verify_nonce( Param::get( 'rank_math_bulk_edit_primary_term' ), 'rank-math-edit-primary-term' ) ) {
			return;
		}

		$taxonomy = ProAdminHelper::get_primary_taxonomy( $post_id );
		$input    = absint( Param::get( 'rank_math_primary_' . $taxonomy['name'] ) );
		if ( ! $input ) {
			return;
		}

		if ( ! has_term( $input, $taxonomy['name'], $post_id ) ) {
			return;
		}

		update_post_meta( $post_id, 'rank_math_primary_' . $taxonomy['name'], absint( $input ) );
	}
}
