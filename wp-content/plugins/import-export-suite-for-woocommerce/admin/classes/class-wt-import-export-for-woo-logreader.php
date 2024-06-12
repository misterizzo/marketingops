<?php
/**
 * Log reader section of the plugin
 *
 * @link
 *
 * @package ImportExportSuite\Admin\Classes\Logreader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_Logreader Class.
 */
class Wt_Import_Export_For_Woo_Logreader {
		/**
		 * Log file path
		 *
		 * @var string
		 */
	private $file_path = '';
		/**
		 * Log file pointer
		 *
		 * @var string
		 */
	private $file_pointer = null;
		/**
		 * Log mode
		 *
		 * @var string
		 */
	private $mode = '';
	/**
	 *   Get log reader.
	 */
	public function __construct() {
	}
	/**
	 * Init logger
	 *
	 * @param string $file_path Log file path.
	 * @param string $mode Log mode.
	 */
	public function init( $file_path, $mode = 'r' ) {
		$this->file_path = $file_path;
		$this->mode = $mode;
		$this->file_pointer = @fopen( $file_path, 'r' );
	}
	/**
	 * Log file pointer close
	 */
	public function close_file_pointer() {
		if ( null != $this->file_pointer ) {
			fclose( $this->file_pointer );
		}
	}
	/**
	 * Get data
	 *
	 * @param string $file_path Log path.
	 * @return boolean
	 */
	public function get_full_data( $file_path ) {
		$out = array(
			'response' => false,
			'data_str' => '',
		);
		$this->init( $file_path );
		if ( ! is_resource( $this->file_pointer ) ) {
			return $out;
		}
		$data = fread( $this->file_pointer, filesize( $file_path ) );

		$this->close_file_pointer();

		$out = array(
			'response' => false,
			'data_str' => $data,
		);
		return $out;
	}

	/**
	 *   Read log file as batch
	 *
	 *   @param      string  $file_path     path of file to read.
	 *   @param      int     $offset  offset in bytes. default 0.
	 *   @param      integer $batch_count   total row in a batch. default 50.
	 *   @return     array       response, next offset, data array, finished or not flag
	 */
	public function get_data_as_batch( $file_path, $offset = 0, $batch_count = 50 ) {
		$out = array(
			'response' => false,
			'offset' => $offset,
			'data_arr' => array(),
			'finished' => false, // end of file reached or not.
		);
		$this->init( $file_path );
		if ( ! is_resource( $this->file_pointer ) ) {
			return $out;
		}

		fseek( $this->file_pointer, $offset );
		$row_count = 0;
		$next_offset = $offset;
		$finished = false;
		$data_arr = array();
		while ( ( $data = fgets( $this->file_pointer ) ) !== false ) {
			$data = maybe_unserialize( $data );
			if ( is_array( $data ) ) {
				$data_arr[] = $data;
				$row_count++;
				$next_offset = ftell( $this->file_pointer );
			}
			if ( $row_count == $batch_count ) {
				break;
			}
		}
		if ( filesize( $file_path ) == $next_offset ) {
			$finished = true;
		}
		$this->close_file_pointer();

		$out = array(
			'response' => true,
			'offset' => $next_offset,
			'data_arr' => $data_arr,
			'finished' => $finished,
		);
		return $out;
	}
}
