<?php
/**
 * Log writing section of the plugin
 *
 * @link
 *
 * @package ImportExportSuite\Admin\Classes\Log
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_Log Class.
 */
class Wt_Import_Export_For_Woo_Log {
		/**
		 * Log directory
		 *
		 * @var string
		 */
	public static $log_dir = WP_CONTENT_DIR . '/webtoffee_iew_log';
		/**
		 * History ID
		 *
		 * @var integer
		 */
	public static $history_id = '';
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	}

	/**
	 *   Get given temp file path.
	 *   If file name is empty then file path will return
	 *
	 * @param string $file_name File name.
	 */
	public static function get_file_path( $file_name = '' ) {
		if ( ! is_dir( self::$log_dir ) ) {
			if ( ! mkdir( self::$log_dir, 0700 ) ) {
				return false;
			} else {
				$files_to_create = array(
					'.htaccess' => 'deny from all',
					'index.php' => '<?php // Silence is golden',
				);
				foreach ( $files_to_create as $file => $file_content ) {
					if ( ! file_exists( self::$log_dir . '/' . $file ) ) {
						$fh = @fopen( self::$log_dir . '/' . $file, 'w' );
						if ( is_resource( $fh ) ) {
							fwrite( $fh, $file_content );
							fclose( $fh );
						}
					}
				}
			}
		}
		return self::$log_dir . '/' . $file_name;
	}

	/**
	 *   Checks a log file created for the history entry for current day
	 *
	 *   @param   int $history_id id of history entry.
	 *   @return  string/bool if file found returns file name otherwise false.
	 */
	public static function check_log_exists_for_entry( $history_id ) {
		$log_dir = self::get_file_path();
		$exp = '~^' . $history_id . '.*\.log$~';
		$files = preg_grep( $exp, scandir( $log_dir ) );

		if ( count( $files ) > 0 ) {
			foreach ( $files as $key => $value ) {
				$file_name = pathinfo( $value, PATHINFO_FILENAME );
				$file_name_arr = explode( '_', $file_name );
				$file_date_time = end( $file_name_arr );
				$file_date_time_arr = explode( ' ', $file_date_time );
				$file_time = strtotime( $file_date_time_arr[0] );
				if ( $file_time ) {
					$today = strtotime( gmdate( 'Y-m-d' ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					$file_time = strtotime( gmdate( 'Y-m-d', $file_time ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					if ( $today == $file_time ) {
						return $value;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Generate log file name
	 *
	 * @param string  $post_type Post type.
	 * @param string  $action_type Action type.
	 * @param integer $history_id History ID.
	 * @return type
	 */
	public static function generate_file_name( $post_type = '', $action_type = '', $history_id = '' ) {
		$arr = array( $history_id, $post_type );
		if ( defined( 'WT_IEW_CRON' ) ) {
			$arr[] = 'schedule';
		}
		$arr[] = $action_type;
		$arr[] = gmdate( 'Y-m-d h i s A' ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		// * if changing this format please consider `check_log_exists_for_entry` method */

		$arr = array_filter( $arr );
		return implode( '_', $arr ) . '.log';
	}
}
