<?php
/**
 * Extend the quick edit functionality on the manage posts screen.
 *
 * @since      1.0
 * @package    RankMathPro
 * @subpackage RankMathPro\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMathPro\Admin;

use RankMath\Helper;
use RankMath\Helpers\Param;
use RankMath\Helpers\Arr;
use RankMath\Traits\Hooker;
use RankMath\Admin\Admin_Helper;
use RankMathPro\Admin\Admin_Helper as ProAdminHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Quick edit class.
 *
 * @codeCoverageIgnore
 */
class Quick_Edit {

	use Hooker;

	/**
	 * Register hooks.
	 */
	public function __construct() {
		$this->action( 'admin_enqueue_scripts', 'admin_scripts', 20 );
		$this->action( 'rank_math/post/column/seo_details', 'quick_edit_hidden_fields' );
		$this->action( 'quick_edit_custom_box', 'quick_edit' );
		$this->action( 'bulk_edit_custom_box', 'bulk_edit' );
		$this->action( 'save_post', 'save_post' );
		$this->action( 'load-edit.php', 'maybe_save_bulk_edit', 20 );

		$this->filter( 'rank_math/post/column/seo_details/focus_keyword', 'determine_search_intent' );

		$taxonomies = Helper::get_accessible_taxonomies();
		unset( $taxonomies['post_format'] );
		$taxonomies = wp_list_pluck( $taxonomies, 'label', 'name' );
		foreach ( $taxonomies as $taxonomy => $label ) {
			$this->filter( "manage_edit-{$taxonomy}_columns", 'add_tax_seo_column' );
			$this->filter( "manage_{$taxonomy}_custom_column", 'tax_seo_column_content', 10, 3 );
			$this->filter( "edited_{$taxonomy}", 'save_tax' );
		}
	}

	/**
	 * Add hidden column for SEO details for the quick edit.
	 *
	 * @param string[] $columns Original columns array.
	 *
	 * @return string[]         New columns array.
	 */
	public function add_tax_seo_column( $columns ) {
		$columns['rank_math_tax_seo_details'] = __( 'SEO Details', 'rank-math-pro' );
		return $columns;
	}

	/**
	 * Add the hidden fields in the SEO Details column of the terms listing screen.
	 *
	 * @param  string $content     Current content.
	 * @param  string $column_name Column name.
	 * @param  int    $term_id     Term ID.
	 *
	 * @return string              New content.
	 */
	public function tax_seo_column_content( $content, $column_name, $term_id ) {
		if ( 'rank_math_tax_seo_details' !== $column_name ) {
			return $content;
		}

		ob_start();
		$this->quick_edit_hidden_fields( $term_id, 'term' );
		return ob_get_clean();
	}

	/**
	 * Output hidden fields in the `seo_details` column on the posts and the
	 * terms screen, to use the data in the quick edit form.
	 *
	 * @param  int    $object_id   Post/term ID.
	 * @param  object $object_type Object type: post or term.
	 *
	 * @return void
	 */
	public function quick_edit_hidden_fields( $object_id, $object_type = 'post' ) {
		if ( ! in_array( $object_type, [ 'post', 'term' ], true ) ) {
			return;
		}

		if ( 'post' === $object_type && ! $this->can_bulk_edit() ) {
			return;
		}

		$robots = array_filter( (array) get_metadata( $object_type, $object_id, 'rank_math_robots', true ) );

		if ( empty( $robots ) ) {
			$robots = Helper::get_robots_defaults();
		}

		// Maybe product with hidden visibility!
		if ( Helper::is_woocommerce_active() && 'product' === get_post_type( $object_id ) && Helper::get_settings( 'general.noindex_hidden_products' ) ) {
			$product = \wc_get_product( $object_id );

			if ( $product && $product->get_catalog_visibility() === 'hidden' ) {
				// Preserve other robots values.
				$robots = array_filter(
					$robots,
					function ( $robot ) {
						return 'index' !== $robot;
					}
				);

				$robots = array_merge( $robots, [ 'noindex' ] );
			}
		}

		$title = get_metadata( $object_type, $object_id, 'rank_math_title', true );
		if ( ! $title ) {
			if ( 'post' === $object_type ) {
				$post_type = get_post_type( $object_id );
				$title     = Helper::get_settings( "titles.pt_{$post_type}_title" );
			} elseif ( 'term' === $object_type ) {
				$term     = get_term( $object_id );
				$taxonomy = $term->taxonomy;
				$title    = Helper::get_settings( "titles.tax_{$taxonomy}_title" );
			}
		}

		$description = get_metadata( $object_type, $object_id, 'rank_math_description', true );
		if ( ! $description ) {
			if ( 'post' === $object_type ) {
				$post_type   = get_post_type( $object_id );
				$description = Helper::get_settings( "titles.pt_{$post_type}_description" );
			} elseif ( 'term' === $object_type ) {
				$term        = get_term( $object_id );
				$taxonomy    = $term->taxonomy;
				$description = Helper::get_settings( "titles.tax_{$taxonomy}_description" );
			}
		}

		$canonical       = get_metadata( $object_type, $object_id, 'rank_math_canonical_url', true );
		$focus_keywords  = Arr::from_string( get_metadata( $object_type, $object_id, 'rank_math_focus_keyword', true ) );
		$primary_keyword = ! empty( $focus_keywords ) ? $focus_keywords[0] : '';

		$canonical_placeholder = '';
		if ( 'post' === $object_type ) {
			$canonical_placeholder = get_permalink( $object_id );
		} elseif ( 'term' === $object_type ) {
			$canonical_placeholder = get_term_link( $object_id );
		}
		?>

		<input type="hidden" class="rank-math-title-value" id="rank-math-title-<?php echo esc_attr( $object_id ); ?>" value="<?php echo esc_attr( $title ); ?>">
		<input type="hidden" class="rank-math-description-value" id="rank-math-description-<?php echo esc_attr( $object_id ); ?>" value="<?php echo esc_attr( $description ); ?>">
		<input type="hidden" class="rank-math-robots-meta-value" id="rank-math-robots-meta-<?php echo esc_attr( $object_id ); ?>" value="<?php echo esc_attr( wp_json_encode( $robots ) ); ?>">
		<input type="hidden" class="rank-math-canonical-url-value" id="rank-math-canonical-url-<?php echo esc_attr( $object_id ); ?>" value="<?php echo esc_attr( $canonical ); ?>">
		<input type="hidden" class="rank-math-canonical-placeholder-value" id="rank-math-canonical-placeholder-<?php echo esc_attr( $object_id ); ?>" value="<?php echo esc_attr( $canonical_placeholder ); ?>">
		<input type="hidden" class="rank-math-focus-keywords-value" id="rank-math-focus-keywords-<?php echo esc_attr( $object_id ); ?>" value="<?php echo esc_attr( $primary_keyword ); ?>">
		<?php if ( 'post' === $object_type ) : ?>
			<input type="hidden" class="rank-math-primary-term-value" id="rank-math-primary-term-<?php echo esc_attr( $object_id ); ?>" value="<?php echo esc_attr( ProAdminHelper::get_primary_term_id( $object_id ) ); ?>">
		<?php endif; ?>
		<?php
	}

	/**
	 * Enqueue styles and scripts.
	 */
	public function admin_scripts() {
		global $pagenow;
		if ( Admin_Helper::is_post_list() ) {
			wp_enqueue_script( 'rank-math-pro-post-list', RANK_MATH_PRO_URL . 'assets/admin/js/post-list.js', [], RANK_MATH_PRO_VERSION, true );
			wp_enqueue_style( 'rank-math-pro-post-list', RANK_MATH_PRO_URL . 'assets/admin/css/post-list.css', [ 'rank-math-common' ], RANK_MATH_PRO_VERSION );
		} elseif ( 'edit-tags.php' === $pagenow ) {
			wp_enqueue_script( 'rank-math-pro-term-list', RANK_MATH_PRO_URL . 'assets/admin/js/term-list.js', [], RANK_MATH_PRO_VERSION, true );
			wp_enqueue_style( 'rank-math-pro-term-list', RANK_MATH_PRO_URL . 'assets/admin/css/term-list.css', [], RANK_MATH_PRO_VERSION );
		}
	}

	/**
	 * Display our custom content on the quick-edit interface. No values can be
	 * pre-populated (all done in JS).
	 *
	 * @param  string $column    Column name.
	 * @param  bool   $bulk_edit Is bulk edit row.
	 * @return void
	 */
	public function quick_edit( $column, $bulk_edit = false ) {
		if ( ! $this->can_bulk_edit() ) {
			return;
		}

		global $post_type;
		$ptype = $post_type;
		if ( is_a( $ptype, 'WP_Post_Type' ) ) {
			$ptype = $ptype->name;
		}

		global $pagenow;
		if ( 'edit-tags.php' === $pagenow ) {
			global $taxonomy;
			$ptype = $taxonomy;
		}

		$columns = get_column_headers( 'edit-' . $ptype );
		if ( ! isset( $columns['rank_math_seo_details'] ) && ! isset( $columns['rank_math_tax_seo_details'] ) ) {
			return;
		}

		$robots = [
			'index'        => __( 'Index', 'rank-math-pro' ),
			'noindex'      => __( 'No Index', 'rank-math-pro' ),
			'nofollow'     => __( 'No Follow', 'rank-math-pro' ),
			'noarchive'    => __( 'No Archive', 'rank-math-pro' ),
			'noimageindex' => __( 'No Image Index', 'rank-math-pro' ),
			'nosnippet'    => __( 'No Snippet', 'rank-math-pro' ),
		];

		switch ( $column ) {
			case 'rank_math_seo_details':
				wp_nonce_field( 'rank-math-quick-edit', 'rank_math_quick_edit_nonce' );
				?>
				<div class="rank-math-quick-edit wp-clearfix">
					<fieldset class="inline-edit-col-left clear">
						<legend class="inline-edit-legend">
							<?php esc_html_e( 'SEO Settings', 'rank-math-pro' ); ?>
						</legend>
						<div class="inline-edit-col wp-clearfix">
				<?php
				break;

			case 'rank_math_title':
				?>
				<label>
					<span class="title"><?php esc_html_e( 'SEO Title', 'rank-math-pro' ); ?></span>
					<span class="input-text-wrap rank-math-quick-edit-text-wrap">
						<input type="text" name="rank_math_title" id="rank_math_title" value="">
					</span>
				</label>
				<?php
				break;

			case 'rank_math_description':
				?>
				<label class="inline-edit-seo-description">
					<span class="title"><?php esc_html_e( 'SEO Description', 'rank-math-pro' ); ?></span>
					<textarea name="rank_math_description" id="rank_math_description"></textarea>
				</label>
				</div></fieldset>

				<fieldset class="inline-edit-col-center inline-edit-robots">
					<div class="inline-edit-col">
						<span class="title inline-edit-robots-label"><?php esc_html_e( 'Robots Meta', 'rank-math-pro' ); ?></span>
						<ul class="cat-checklist category-checklist rank-math-robots-checklist">
							<?php foreach ( $robots as $val => $option ) : ?>
								<li id="rank_math_robots_<?php echo esc_attr( $val ); ?>" class="rank_math_robots">
									<label class="selectit">
										<input type="checkbox" name="rank_math_robots[]" id="rank_math_robots_<?php echo esc_attr( $val ); ?>_input" value="<?php echo esc_attr( $val ); ?>" checked="">
										<?php echo esc_html( $option ); ?>
									</label>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</fieldset>

				<fieldset class="inline-edit-col-right">
					<div class="inline-edit-col">
						<?php if ( ! $bulk_edit ) { ?>
							<label>
								<span class="title"><?php esc_html_e( 'Primary Focus Keyword', 'rank-math-pro' ); ?></span>
								<span class="input-text-wrap rank-math-quick-edit-text-wrap">
									<input type="text" name="rank_math_focus_keyword" id="rank_math_focus_keyword" value="">
								</span>
							</label>
							<label>
								<span class="title"><?php esc_html_e( 'Canonical URL', 'rank-math-pro' ); ?></span>
								<span class="input-text-wrap rank-math-quick-edit-text-wrap">
									<input type="text" name="rank_math_canonical_url" id="rank_math_canonical_url" value="">
								</span>
							</label>
							<?php
							if ( false === $this->do_filter( 'admin/disable_primary_term', false ) ) :
								$taxonomy_obj = ProAdminHelper::get_primary_taxonomy();
								if ( false !== $taxonomy_obj ) :
									?>
									<fieldset class="inline-edit-rank-math-primary-term inline-edit-categories">
										<?php wp_nonce_field( 'rank-math-edit-primary-term', 'rank_math_bulk_edit_primary_term' ); ?>
										<label class="rank-math-primary-term">
											<span class="title">
												<?php // Translators: placeholder is taxonomy name, e.g. "Category". ?>
												<?php echo esc_html( sprintf( __( 'Primary %s', 'rank-math-pro' ), $taxonomy_obj['singularLabel'] ) ); ?>
											</span>
											<span class="input-text-wrap rank-math-quick-edit-text-wrap">
											<?php
											wp_dropdown_categories(
												[
													'name' => 'rank_math_primary_term',
													'id'   => 'rank_math_primary_term',
													'class' => '',
													'selected' => '0',
													'orderby' => 'name',
													'taxonomy' => $taxonomy_obj['name'],
													'hide_empty' => false,
													'show_option_all' => false,
													'show_option_none' => __( '&mdash; Not Selected &mdash;', 'rank-math-pro' ),
													'option_none_value' => '0',
												]
											);
											?>
											</span>
										</label>
									</fieldset>
									<?php
								endif;
							endif;
							?>
						<?php } ?>
					</div>
				</fieldset>
				</div>
				<?php
				break;

			case 'rank_math_tax_seo_details':
				wp_nonce_field( 'rank-math-quick-edit', 'rank_math_quick_edit_nonce' );
				?>
				<div class="rank-math-quick-edit wp-clearfix inline-edit-row">
					<fieldset>
						<legend class="inline-edit-legend">
							<?php esc_html_e( 'SEO Settings', 'rank-math-pro' ); ?>
						</legend>
						<div class="inline-edit-col">
							<label>
								<span class="title"><?php esc_html_e( 'SEO Title', 'rank-math-pro' ); ?></span>
								<span class="input-text-wrap rank-math-quick-edit-text-wrap">
									<input type="text" name="rank_math_title" id="rank_math_title" value="">
								</span>
							</label>
							<label class="inline-edit-seo-description">
								<div><?php esc_html_e( 'SEO Description', 'rank-math-pro' ); ?></div>
								<textarea name="rank_math_description" id="rank_math_description"></textarea>
							</label>

							<div class="inline-edit-robots-label"><?php esc_html_e( 'Robots Meta', 'rank-math-pro' ); ?></div>
							<ul class="cat-checklist category-checklist rank-math-robots-checklist">
								<?php foreach ( $robots as $val => $option ) : ?>
									<li id="rank_math_robots_<?php echo esc_attr( $val ); ?>" class="rank_math_robots">
										<label class="selectit">
											<input type="checkbox" name="rank_math_robots[]" id="rank_math_robots_<?php echo esc_attr( $val ); ?>_input" value="<?php echo esc_attr( $val ); ?>" checked="">
											<?php echo esc_html( $option ); ?>
										</label>
									</li>
								<?php endforeach; ?>
							</ul>

							<label class="clear">
								<div class="title"><?php esc_html_e( 'Primary Focus Keyword', 'rank-math-pro' ); ?></div>
								<div class="rank-math-quick-edit-text-wrap">
									<input type="text" name="rank_math_focus_keyword" id="rank_math_focus_keyword" value="">
								</div>
							</label>
							<label class="clear">
								<div class="title"><?php esc_html_e( 'Canonical URL', 'rank-math-pro' ); ?></div>
								<div class="input-text-wrap rank-math-quick-edit-text-wrap">
									<input type="text" name="rank_math_canonical_url" id="rank_math_canonical_url" value="">
								</div>
							</label>
						</div>
					</fieldset>
				</div>
				<?php
				break;
		}
	}

	/**
	 * Add fields for the bulk edit row.
	 * Just a wrapper for the quick_edit() method, since the fields are mostly the same.
	 *
	 * @param string $column Column name.
	 * @return void
	 */
	public function bulk_edit( $column ) {
		$this->quick_edit( $column, true );
	}

	/**
	 * Save bulk edit data if needed.
	 *
	 * @return void
	 */
	public function maybe_save_bulk_edit() {
		if ( ! Param::request( 'bulk_edit' ) ) {
			return;
		}

		$this->save_bulk_edit( $_REQUEST ); // phpcs:ignore
	}

	/**
	 * Save bulk edit data.
	 *
	 * @param array $post_data Post data input.
	 * @return void
	 */
	public function save_bulk_edit( $post_data ) {
		if ( empty( $post_data ) ) {
			$post_data = &$_POST; // phpcs:ignore
		}

		if ( isset( $post_data['post_type'] ) ) {
			$ptype = get_post_type_object( $post_data['post_type'] );
		} else {
			$ptype = get_post_type_object( 'post' );
		}

		if ( ! current_user_can( $ptype->cap->edit_posts ) ) {
			return;
		}

		if ( ! Helper::has_cap( 'onpage_general' ) ) {
			return;
		}

		if ( ! $this->can_bulk_edit( $ptype ) ) {
			return;
		}

		$save_fields = [
			'title',
			'description',
			'robots',
			'primary_term',
		];

		$post_ids = array_map( 'intval', (array) $post_data['post'] );
		foreach ( $post_ids as $post_id ) {
			foreach ( $save_fields as $field ) {
				$field_name  = 'rank_math_' . $field;
				$field_value = isset( $post_data[ $field_name ] ) ? $post_data[ $field_name ] : '';
				if ( is_string( $field_value ) ) {
					$field_value = trim( $field_value );
				}

				if ( empty( $field_value ) ) {
					// Skip if not set.
					continue;
				}

				if ( 'robots' === $field ) {
					$field_value = (array) $field_value;
				} elseif ( 'primary_term' === $field ) {
					$taxonomy   = ProAdminHelper::get_primary_taxonomy( $post_id );
					$field_name = 'rank_math_primary_' . $taxonomy['name'];
				}

				update_post_meta( $post_id, $field_name, $field_value );
			}
		}
	}

	/**
	 * Save post quick edit.
	 *
	 * @param  int $post_id Post ID.
	 * @return mixed
	 */
	public function save_post( $post_id ) {
		if ( wp_is_post_revision( $post_id ) || ! wp_verify_nonce( Param::post( 'rank_math_quick_edit_nonce' ), 'rank-math-quick-edit' ) ) {
			return;
		}

		if ( ! Helper::has_cap( 'onpage_general' ) ) {
			return;
		}

		$post_type = get_post_type( $post_id );
		if ( ! $this->can_bulk_edit( $post_type ) ) {
			return;
		}

		$taxonomy = ProAdminHelper::get_primary_taxonomy( $post_id );

		$save_fields = [
			'title',
			'description',
			'robots',
			'focus_keyword',
			'canonical_url',
			'primary_term',
		];

		foreach ( $save_fields as $field ) {
			$field_name = 'rank_math_' . $field;
			$flag       = [];
			if ( 'robots' === $field ) {
				$flag = FILTER_REQUIRE_ARRAY;
			}

			$field_value = Param::post( $field_name, false, FILTER_DEFAULT, $flag );
			if ( false === $field_value ) {
				continue;
			}

			$default_value = '';
			if ( $post_type ) {
				$default_value = Helper::get_settings( 'titles.pt_' . $post_type . '_' . $field );
			}

			if ( 'robots' === $field ) {
				$field_value = array_filter( $field_value );
				$field_value = array_unique( $field_value );
				$field_value = array_intersect( $field_value, [ 'index', 'noindex', 'nofollow', 'noarchive', 'noimageindex', 'nosnippet' ] );
			} elseif ( 'canonical_url' === $field ) {
				$field_value = esc_url_raw( $field_value );
			} elseif ( 'focus_keyword' === $field ) {
				$current_value = get_post_meta( $post_id, $field_name, true );
				$current       = Arr::from_string( $current_value );
				$keywords      = Arr::from_string( $field_value );
				$current[0]    = ! empty( $keywords ) ? $keywords[0] : '';
				if ( '' === $current[0] ) {
					array_shift( $current );
				}
				$field_value = join( ', ', $current );
			} elseif ( 'primary_term' === $field ) {
				if ( ! $field_value ) {
					delete_post_meta( $post_id, $field_name );
					continue;
				}

				if ( ! has_term( absint( $field_value ), $taxonomy['name'], $post_id ) ) {
					continue;
				}

				$field_name = 'rank_math_primary_' . $taxonomy['name'];
			}

			if ( empty( $field_value ) || $field_value === $default_value ) {
				delete_post_meta( $post_id, $field_name );
				continue;
			}

			update_post_meta( $post_id, $field_name, $field_value );
		}
	}

	/**
	 * Save taxonomy term quick edit.
	 *
	 * @param  int $term_id Term ID.
	 *
	 * @return void
	 */
	public function save_tax( $term_id ) {
		$term_id = Param::post( 'tax_ID' );

		if ( ! $term_id ) {
			return;
		}

		if ( ! wp_verify_nonce( Param::post( 'rank_math_quick_edit_nonce' ), 'rank-math-quick-edit' ) ) {
			return;
		}

		if ( ! Helper::has_cap( 'onpage_general' ) ) {
			return;
		}

		$save_fields = [
			'title',
			'description',
			'robots',
			'focus_keyword',
			'canonical_url',
		];

		foreach ( $save_fields as $field ) {
			$field_name = 'rank_math_' . $field;
			$flag       = [];
			if ( 'robots' === $field ) {
				$flag = FILTER_REQUIRE_ARRAY;
			}

			$field_value = Param::post( $field_name, false, FILTER_DEFAULT, $flag );
			if ( false === $field_value ) {
				continue;
			}

			if ( 'robots' === $field ) {
				$field_value = array_filter( $field_value );
				$field_value = array_unique( $field_value );
				$field_value = array_intersect( $field_value, [ 'index', 'noindex', 'nofollow', 'noarchive', 'noimageindex', 'nosnippet' ] );
			} elseif ( 'canonical_url' === $field ) {
				$field_value = esc_url_raw( $field_value );
			} elseif ( 'focus_keyword' === $field ) {
				$current    = get_term_meta( $term_id, $field_name, true );
				$current    = Arr::from_string( $current );
				$keywords   = Arr::from_string( $field_value );
				$current[0] = ! empty( $keywords ) ? $keywords[0] : '';
				if ( '' === $current[0] ) {
					array_shift( $current );
				}
				$field_value = join( ', ', $current );
			}
			update_term_meta( $term_id, $field_name, $field_value );
		}
	}

	/**
	 * Check if bulk editing is enabled for the current post type.
	 *
	 * @param  string $ptype Post type name.
	 *
	 * @return boolean
	 */
	public function can_bulk_edit( $ptype = null ) {
		global $post_type;
		if ( ! $ptype ) {
			$ptype = $post_type;
		}

		if ( is_a( $ptype, 'WP_Post_Type' ) ) {
			$ptype = $ptype->name;
		}

		$allow_editing = Helper::get_settings( 'titles.pt_' . $ptype . '_bulk_editing', true );
		if ( ! $allow_editing || 'readonly' === $allow_editing ) {
			return false;
		}

		return true;
	}

	/**
	 * Add Keyword Intent.
	 *
	 * @param  string $keyword Focus Keyword.
	 *
	 * @return string
	 */
	public function determine_search_intent( $keyword ) {
		if ( ! Helper::should_determine_search_intent() ) {
			return $keyword;
		}

		$keyword_lower  = strtolower( $keyword );
		$search_intent  = ProAdminHelper::get_search_intent();
		$keyword_intent = ! empty( $search_intent[ $keyword_lower ] ) ? strtolower( $search_intent[ $keyword_lower ] ) : '';
		$intent_types   = [
			'commercial'    => esc_html__( 'Commercial', 'rank-math-pro' ),
			'informational' => esc_html__( 'Informational', 'rank-math-pro' ),
			'navigational'  => esc_html__( 'Navigational', 'rank-math-pro' ),
			'transactional' => esc_html__( 'Transactional', 'rank-math-pro' ),
		];

		if ( ! isset( $intent_types[ $keyword_intent ] ) ) {
			return $keyword;
		}

		return "
			<span class='rank-math-tooltip'>
				{$keyword}
				<span class='intent'>{$intent_types[ $keyword_intent ]}</span>
			</span>";
	}
}
