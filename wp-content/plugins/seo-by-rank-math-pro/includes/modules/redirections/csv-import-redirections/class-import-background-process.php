<?php
/**
 * The CSV Import class.
 *
 * @since      1.0
 * @package    RankMathPro
 * @subpackage RankMathPro\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMathPro\Redirections\CSV_Import_Export_Redirections;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * CSV Import Export class.
 *
 * @codeCoverageIgnore
 */
class Import_Background_Process extends \WP_Background_Process {

	/**
	 * Prefix.
	 *
	 * (default value: 'wp')
	 *
	 * @var string
	 * @access protected
	 */
	protected $prefix = 'rank_math';

	/**
	 * Action.
	 *
	 * @var string
	 */
	protected $action = 'csv_import_redirections';

	/**
	 * Importer instance.
	 *
	 * @var Importer
	 */
	private $importer;

	/**
	 * Main instance.
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Import_Background_Process
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) || ! ( $instance instanceof Import_Background_Process ) ) {
			$instance = new Import_Background_Process();
		}

		return $instance;
	}

	/**
	 * Start creating batches.
	 *
	 * @param int $lines_number The line number to process.
	 */
	public function start( $lines_number ) {
		$chunks = array_chunk( range( 0, $lines_number ), apply_filters( 'rank_math/admin/csv_import_redirections_chunk_size', 100 ) );
		foreach ( $chunks as $chunk ) {
			$this->push_to_queue( $chunk );
		}

		$this->save()->dispatch();
	}

	/**
	 * Task.
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
		try {
			$this->importer = new Importer();
			foreach ( $item as $row ) {
				$this->importer->import_line( $row );
			}
			$this->importer->batch_done( $item );
			return false;
		} catch ( \Exception $error ) {
			return true;
		}
	}

	/**
	 * Import complete. Clear options & add notification.
	 *
	 * @return void
	 */
	protected function complete() {
		wp_delete_file( get_option( 'rank_math_csv_import_redirections' ) );
		delete_option( 'rank_math_csv_import_redirections' );
		delete_option( 'rank_math_csv_import_redirections_total' );
		delete_option( 'rank_math_csv_import_redirections_settings' );

		$status = (array) get_option( 'rank_math_csv_import_redirections_status', [] );

		$notification_args = [
			'type'    => 'success',
			'classes' => 'is-dismissible',
		];

		if ( ! empty( $status['errors'] ) ) {
			$notification_args = [
				'type'    => 'error',
				'classes' => 'is-dismissible',
			];
		}

		Helper::add_notification(
			CSV_Import_Export_Redirections::get_import_complete_message(),
			$notification_args
		);
		parent::clear_scheduled_event();
		do_action( $this->identifier. '_completed' ); // phpcs:ignore
	}

	/**
	 * Count remaining items in batch.
	 *
	 * @return int
	 */
	public function count_remaining_items() {
		if ( $this->is_queue_empty() ) {
			// This fixes an issue where get_batch() runs too early and results in a PHP notice.
			return get_option( 'rank_math_csv_import_redirections_total' );
		}
		$batch = $this->get_batch();
		$count = 0;
		if ( ! empty( $batch->data ) && is_array( $batch->data ) ) {
			foreach ( $batch->data as $items ) {
				$count += count( $items );
			}
		}

		return $count;
	}

	/**
	 * Has the process been cancelled?
	 *
	 * @return bool
	 */
	public function is_cancelled() {
		// Fixes bug in parent is_cancelled()!
		// where get_site_option( 'rank_math_csv_import_redirections_status' ) is not yet set when is_cancelled is called for the first time.
		return is_multisite() ? parent::is_cancelled() : ! get_site_option( $this->identifier );
	}
}
