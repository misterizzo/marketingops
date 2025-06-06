<?php
/**
 * The CSV Export class.
 *
 * @since      1.0
 * @package    RankMathPro
 * @subpackage RankMathPro\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMathPro\Redirections\CSV_Import_Export_Redirections;

use RankMath\Helper;
use RankMath\Redirections\DB;
use RankMath\Redirections\Cache;
use RankMathPro\Admin\CSV;

defined( 'ABSPATH' ) || exit;

/**
 * CSV Export.
 *
 * @codeCoverageIgnore
 */
class Exporter extends CSV {

	/**
	 * Settings array.
	 *
	 * @var array
	 */
	private $settings = [];

	/**
	 * Columns to export.
	 *
	 * @var array
	 */
	private $columns = [];

	/**
	 * Constructor.
	 *
	 * @param array $options Export options.
	 * @return void
	 */
	public function __construct( $options ) {
		$defaults        = [
			'include_deactivated' => true,
		];
		$this->settings  = wp_parse_args( $options, $defaults );
		$this->columns   = CSV_Import_Export_Redirections::get_columns();
		$this->columns[] = 'ignore';
	}

	/**
	 * Do export.
	 *
	 * @return void
	 */
	public function process_export() {
		$this->export(
			[
				'filename' => 'rank-math-redirections',
				'columns'  => $this->columns,
				'items'    => $this->get_items(),
			]
		);

		exit;
	}

	/**
	 * Get value for given column.
	 *
	 * @param string $column Column name.
	 * @param object $item WP_Post, WP_Term or WP_User.
	 *
	 * @return string
	 */
	public function get_column_value( $column, $item ) {
		$val = '';

		switch ( $column ) {
			case 'id':
				$val = $item->id;
				break;

			case 'source':
				$val = $item->source_processed;
				break;

			case 'matching':
				$val = $item->matching_processed;
				break;

			case 'destination':
				$val = $item->url_to;
				break;

			case 'type':
				$val = $item->header_code;
				break;

			case 'category':
				$val = $item->categories_processed;
				break;

			case 'status':
				$val = $item->status;
				break;

			case 'ignore':
				$val = $item->ignore;
				break;
		}
		return $this->escape_csv( apply_filters( "rank_math/admin/csv_export_redirections_column_{$column}", $val, $item ) );
	}

	/**
	 * Get all redirection IDs.
	 *
	 * @return array
	 */
	public function get_ids() {
		global $wpdb;
		$table    = $wpdb->prefix . 'rank_math_redirections';
		$statuses = [ 'active' ];
		if ( $this->settings['include_deactivated'] ) {
			$statuses[] = 'inactive';
		}
		$where    = 'status IN (\'' . join( '\',\'', $statuses ) . '\')';
		$post_ids = $wpdb->get_col( "SELECT ID FROM {$table} WHERE $where" ); // phpcs:ignore

		return $post_ids;
	}

	/**
	 * Export all redirections.
	 *
	 * @return array
	 */
	public function get_items() {
		global $wpdb;
		$items = [];
		$ids   = $this->get_ids();
		if ( ! $ids ) {
			return $items;
		}

		$primary_column = 'id';
		$table          = $wpdb->prefix . 'rank_math_redirections';
		$cols           = $this->columns;

		// Fetch 50 at a time rather than loading the entire table into memory.
		while ( $next_batch = array_splice( $ids, 0, 50 ) ) { // phpcs:ignore
			$where          = 'WHERE ' . $primary_column . ' IN (' . join( ',', $next_batch ) . ')';
			$objects        = $wpdb->get_results( "SELECT * FROM {$table} $where" ); // phpcs:ignore
			$current_object = 0;
			// Begin Loop.
			foreach ( $objects as $object ) {
				++$current_object;

				$this->process_categories( $object );
				$sources = maybe_unserialize( $object->sources, true );

				foreach ( $sources as $source ) {
					$single_source                     = $object;
					$single_source->source_processed   = $source['pattern'];
					$single_source->matching_processed = $source['comparison'];
					$single_source->ignore             = $source['ignore'];

					$columns = [];
					foreach ( $cols as $column ) {
						$columns[] = $this->get_column_value( $column, $single_source ); // phpcs:ignore
					}

					$items[] = $columns;
				}
			}
		}

		return $items;
	}

	/**
	 * Process sources & categories data for export.
	 *
	 * @param object $item Redirection row.
	 * @return void
	 */
	public function process_categories( &$item ) {
		$item->categories_processed = '';
		$terms                      = wp_get_object_terms( $item->id, 'rank_math_redirection_category' );
		if ( is_a( $terms, 'WP_Error' ) || ! is_array( $terms ) || empty( $terms ) ) {
			return;
		}
		$item->categories_processed = join( ', ', wp_list_pluck( $terms, 'slug' ) );
	}
}
