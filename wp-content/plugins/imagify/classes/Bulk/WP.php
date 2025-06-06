<?php
namespace Imagify\Bulk;

use Imagify_DB;

/**
 * Class to use for bulk for WP attachments.
 *
 * @since 1.9
 */
class WP extends AbstractBulk {
	/**
	 * Context "short name".
	 *
	 * @var string
	 * @since  1.9
	 */
	protected $context = 'wp';

	/**
	 * Get all unoptimized media ids.
	 *
	 * @since 1.9
	 *
	 * @param int $optimization_level The optimization level.
	 *
	 * @return array A list of unoptimized media IDs.
	 */
	public function get_unoptimized_media_ids( $optimization_level ) {
		global $wpdb;

		$this->set_no_time_limit();

		$mime_types   = Imagify_DB::get_mime_types();
		$statuses     = Imagify_DB::get_post_statuses();
		$nodata_join  = Imagify_DB::get_required_wp_metadata_join_clause();
		$nodata_where = Imagify_DB::get_required_wp_metadata_where_clause(
			[
				'prepared' => true,
			]
		);
		$ids          = $wpdb->get_col(
			$wpdb->prepare( // WPCS: unprepared SQL ok.
				"
				SELECT DISTINCT p.ID
				FROM $wpdb->posts AS p
					$nodata_join
				LEFT JOIN $wpdb->postmeta AS mt1
					ON ( p.ID = mt1.post_id AND mt1.meta_key = '_imagify_status' )
				LEFT JOIN $wpdb->postmeta AS mt2
					ON ( p.ID = mt2.post_id AND mt2.meta_key = '_imagify_optimization_level' )
				WHERE
					p.post_mime_type IN ( $mime_types )
					AND (
						mt1.meta_value = 'error'
						OR
						mt2.meta_value != %d
						OR
						mt2.post_id IS NULL
					)
					AND p.post_type = 'attachment'
					AND p.post_status IN ( $statuses )
					$nodata_where
				ORDER BY
					CASE mt1.meta_value
						WHEN 'already_optimized' THEN 2
						ELSE 1
					END ASC,
					p.ID DESC
				LIMIT 0, %d",
				$optimization_level,
				imagify_get_unoptimized_attachment_limit()
			)
		);

		$wpdb->flush();
		unset( $mime_types );
		$ids = array_filter( array_map( 'absint', $ids ) );

		if ( ! $ids ) {
			return [];
		}

		$metas = Imagify_DB::get_metas(
			[
				// Get attachments filename.
				'filenames'           => '_wp_attached_file',
				// Get attachments data.
				'data'                => '_imagify_data',
				// Get attachments optimization level.
				'optimization_levels' => '_imagify_optimization_level',
				// Get attachments status.
				'statuses'            => '_imagify_status',
			],
			$ids
		);

		// First run.
		foreach ( $ids as $i => $id ) {
			$attachment_status             = isset( $metas['statuses'][ $id ] ) ? $metas['statuses'][ $id ] : false;
			$attachment_optimization_level = isset( $metas['optimization_levels'][ $id ] ) ? $metas['optimization_levels'][ $id ] : false;
			$attachment_error              = '';

			if ( isset( $metas['data'][ $id ]['sizes']['full']['error'] ) ) {
				$attachment_error = $metas['data'][ $id ]['sizes']['full']['error'];
			}

			// Don't try to re-optimize if the optimization level is still the same.
			if ( $optimization_level === $attachment_optimization_level && is_string( $attachment_error ) ) {
				unset( $ids[ $i ] );
				continue;
			}

			// Don't try to re-optimize images already compressed.
			if ( 'already_optimized' === $attachment_status && $attachment_optimization_level >= $optimization_level ) {
				unset( $ids[ $i ] );
				continue;
			}

			$attachment_error = trim( $attachment_error );

			// Don't try to re-optimize images with an empty error message.
			if ( 'error' === $attachment_status && empty( $attachment_error ) ) {
				unset( $ids[ $i ] );
			}
		}

		if ( ! $ids ) {
			return [];
		}

		$ids = array_values( $ids );

		/**
		 * Fires before testing for file existence.
		 *
		 * @since 1.6.7
		 *
		 * @param array $ids                An array of attachment IDs.
		 * @param array $metas              An array of the data fetched from the database.
		 * @param int   $optimization_level The optimization level that will be used for the optimization.
		 */
		do_action( 'imagify_bulk_optimize_before_file_existence_tests', $ids, $metas, $optimization_level );

		$data = [];

		foreach ( $ids as $i => $id ) {
			if ( empty( $metas['filenames'][ $id ] ) ) {
				// Problem.
				continue;
			}

			$file_path = get_imagify_attached_file( $metas['filenames'][ $id ] );

			if ( ! $file_path || ! $this->filesystem->exists( $file_path ) ) {
				continue;
			}

			$attachment_backup_path        = get_imagify_attachment_backup_path( $file_path );
			$attachment_status             = isset( $metas['statuses'][ $id ] ) ? $metas['statuses'][ $id ] : false;
			$attachment_optimization_level = isset( $metas['optimization_levels'][ $id ] ) ? $metas['optimization_levels'][ $id ] : false;

			// Don't try to re-optimize if there is no backup file.
			if ( 'success' === $attachment_status && $optimization_level !== $attachment_optimization_level && ! $this->filesystem->exists( $attachment_backup_path ) ) {
				continue;
			}

			$data[] = $id;
		}

		return $data;
	}

	/**
	 * Get ids of all optimized media without Next gen versions.
	 *
	 * @since 2.2
	 *
	 * @param string $format Format we are looking for. (webp|avif).
	 *
	 * @return array {
	 *     @type array $ids    A list of media IDs.
	 *     @type array $errors {
	 *         @type array $no_file_path A list of media IDs.
	 *         @type array $no_backup    A list of media IDs.
	 *     }
	 * }
	 */
	public function get_optimized_media_ids_without_format( $format ) {
		global $wpdb;

		$this->set_no_time_limit();

		$mime_types = Imagify_DB::get_mime_types( 'image' );

		// Remove single quotes and explode string into array.
		$mime_types_array = explode( ',', str_replace( "'", '', $mime_types ) );

		// Iterate over array and check if string contains input.
		foreach ( $mime_types_array as $item ) {
			if ( strpos( $item, $format ) !== false ) {
				$mime = $item;
				break;
			}
		}
		if ( ! isset( $mime ) && empty( $mime ) ) {
			$mime = 'image/webp';
		}
		$mime_types   = str_replace( ",'" . $mime . "'", '', $mime_types );
		$statuses     = Imagify_DB::get_post_statuses();
		$nodata_join  = '';
		$nodata_where = '';
		if ( ! imagify_has_attachments_without_required_metadata() ) {
			$nodata_join  = Imagify_DB::get_required_wp_metadata_join_clause();
			$nodata_where = Imagify_DB::get_required_wp_metadata_where_clause(
				[
					'prepared' => true,
				]
			);
		}

		$nextgen_suffix = constant( imagify_get_optimization_process_class_name( 'wp' ) . '::' . strtoupper( $format ) . '_SUFFIX' );

		$ids = $wpdb->get_col(
			$wpdb->prepare( // WPCS: unprepared SQL ok.
				"
			SELECT p.ID
			FROM $wpdb->posts AS p
				$nodata_join
			LEFT JOIN $wpdb->postmeta AS mt1
				ON ( p.ID = mt1.post_id AND mt1.meta_key = '_imagify_status' )
			LEFT JOIN $wpdb->postmeta AS mt2
					ON ( p.ID = mt2.post_id AND mt2.meta_key = '_imagify_data' )
			WHERE
				p.post_mime_type IN ( $mime_types )
				AND (mt1.meta_key IS NULL OR mt1.meta_value = 'success' OR mt1.meta_value = 'already_optimized' )
				AND mt2.meta_value NOT LIKE %s
				AND p.post_type = 'attachment'
				AND p.post_status IN ( $statuses )
				$nodata_where
			ORDER BY p.ID DESC
			LIMIT 0, %d",
				'%' . $wpdb->esc_like( $nextgen_suffix . '";a:4:{s:7:"success";b:1;' ) . '%',
				imagify_get_unoptimized_attachment_limit()
			)
		);

		$wpdb->flush();
		unset( $mime_types, $statuses, $nextgen_suffix, $mime );

		$ids = array_filter( array_map( 'absint', $ids ) );

		$data = [
			'ids'    => [],
			'errors' => [
				'no_file_path' => [],
				'no_backup'    => [],
			],
		];

		if ( ! $ids ) {
			return $data;
		}

		$metas = Imagify_DB::get_metas(
			[
				// Get attachments filename.
				'filenames' => '_wp_attached_file',
			],
			$ids
		);

		/**
		 * Fires before testing for file existence.
		 *
		 * @since 1.9
		 *
		 * @param array  $ids     An array of attachment IDs.
		 * @param array  $metas An array of the data fetched from the database.
		 * @param string $context The context.
		 */
		do_action( 'imagify_bulk_generate_nextgen_before_file_existence_tests', $ids, $metas, 'wp' );

		foreach ( $ids as $i => $id ) {
			if ( empty( $metas['filenames'][ $id ] ) ) {
				// Problem. Should not happen, thanks to the wpdb query.
				$data['errors']['no_file_path'][] = $id;
				continue;
			}

			$file_path = get_imagify_attached_file( $metas['filenames'][ $id ] );

			if ( ! $file_path ) {
				// Main file not found.
				$data['errors']['no_file_path'][] = $id;
				continue;
			}

			$backup_path = get_imagify_attachment_backup_path( $file_path );

			if ( ! $this->filesystem->exists( $backup_path ) ) {
				// No backup, no WebP.
				$data['errors']['no_backup'][] = $id;
				continue;
			}

			$data['ids'][] = $id;
		}

		return $data;
	}

	/**
	 * Get the context data.
	 *
	 * @since 1.9
	 *
	 * @return array {
	 *     The formated data.
	 *
	 *     @type string $count-optimized Number of media optimized.
	 *     @type string $count-errors    Number of media having an optimization error, with a link to the page listing the optimization errors.
	 *     @type string $optimized-size  Optimized filesize.
	 *     @type string $original-size   Original filesize.
	 * }
	 */
	public function get_context_data() {
		$total_saving_data = imagify_count_saving_data();
		$data              = [
			'count-optimized' => imagify_count_optimized_attachments(),
			'count-errors'    => imagify_count_error_attachments(),
			'optimized-size'  => $total_saving_data['optimized_size'],
			'original-size'   => $total_saving_data['original_size'],
			'errors_url'      => get_imagify_admin_url( 'folder-errors', $this->context ),
		];

		return $this->format_context_data( $data );
	}
}
