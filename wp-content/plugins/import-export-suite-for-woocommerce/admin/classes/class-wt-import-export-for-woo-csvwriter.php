<?php
/**
 * CSV writing section of the plugin
 *
 * @link
 *
 * @package ImportExportSuite\Admin\Classes\Csvwriter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_Csvwriter Class.
 */
class Wt_Import_Export_For_Woo_Csvwriter {
		/**
		 * File path
		 *
		 * @var string
		 */
	public $file_path = '';
		/**
		 * Data array
		 *
		 * @var array
		 */
	public $data_ar = '';
		/**
		 * Delimiter
		 *
		 * @var string
		 */
	public $csv_delimiter = '';
		/**
		 * File pointer
		 *
		 * @var resource
		 */
	public $file_pointer;
		/**
		 * Export data
		 *
		 * @var array
		 */
	public $export_data = array();
	/**
	 * Constructor.
	 *
	 * @param string  $file_path File path.
	 * @param integer $offset Offset.
	 * @param string  $csv_delimiter Delimiter.
	 * @since 1.0.0
	 */
	public function __construct( $file_path, $offset, $csv_delimiter = ',' ) {
		$this->csv_delimiter = $csv_delimiter;
		$this->file_path = $file_path;
		$this->get_file_pointer( $offset );
	}

	/**
	 * This is used in XML to CSV converting
	 *
	 * @param array   $row_data Row array.
	 * @param integer $offset Offset.
	 * @param boolean $is_last_offset Is last offset.
	 */
	public function write_row( $row_data, $offset = 0, $is_last_offset = false ) {
		if ( $is_last_offset ) {
			$this->close_file_pointer();
		} else {
			if ( 0 == $offset ) {
				$this->fput_csv( $this->file_pointer, array_keys( $row_data ), $this->csv_delimiter );
			}
			$this->fput_csv( $this->file_pointer, $row_data, $this->csv_delimiter );
		}
	}

	/**
	 * Create CSV
	 *
	 * @param array   $export_data Export row.
	 * @param integer $offset Offset.
	 * @param boolean $is_last_offset Is last offset.
	 * @param string  $to_export Export type.
	 */
	public function write_to_file( $export_data, $offset, $is_last_offset, $to_export ) {
		$this->export_data = $export_data;
		$this->set_head( $export_data, $offset, $this->csv_delimiter );
		$this->set_content( $export_data, $this->csv_delimiter );
		$this->close_file_pointer();
	}
	/**
	 * Get file pointer
	 *
	 * @param integer $offset Offset.
	 */
	private function get_file_pointer( $offset ) {
		if ( 0 == $offset ) {
			$this->file_pointer = fopen( $this->file_path, 'w' );
		} else {
			$this->file_pointer = fopen( $this->file_path, 'a+' );
		}
	}
	/**
	 * Close file pointer
	 */
	private function close_file_pointer() {
		if ( null != $this->file_pointer ) {
			fclose( $this->file_pointer );
		}
	}
	/**
	 * Escape a string to be used in a CSV context
	 *
	 * Malicious input can inject formulas into CSV files, opening up the possibility
	 * for phishing attacks and disclosure of sensitive information.
	 *
	 * Additionally, Excel exposes the ability to launch arbitrary commands through
	 * the DDE protocol.
	 *
	 * @see http://www.contextis.com/resources/blog/comma-separated-vulnerabilities/
	 *
	 * @param string $data CSV field to escape.
	 * @return string
	 */
	public function escape_data( $data ) {
		$active_content_triggers = array( '=', '+', '-', '@' );

		if ( in_array( mb_substr( $data, 0, 1 ), $active_content_triggers, true ) ) {
			$data = "'" . $data;
		}

		return $data;
	}
	/**
	 * Format data
	 *
	 * @param string $data CSV data.
	 * @return type
	 */
	public function format_data( $data ) {
		if ( ! is_scalar( $data ) ) {
			if ( is_a( $data, 'WC_Datetime' ) ) {
				$data = $data->date( 'Y-m-d G:i:s' );
			} else {
				$data = ''; // Not supported.
			}
		} elseif ( is_bool( $data ) ) {
			$data = $data ? 1 : 0;
		}

		$use_mb = function_exists( 'mb_convert_encoding' );
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables processing non-english languages.
		 *
		 * @since 1.1.1
		 *
		 * @param boolean           $keep_encoding    Keep encoding as is.
		 */
		$keep_encoding = apply_filters( 'wt_iew_importer_keep_encoding', true );
		if ( $use_mb && $keep_encoding ) {
			$encoding = mb_detect_encoding( $data, 'UTF-8, ISO-8859-1', true );
			$data     = 'UTF-8' === $encoding ? $data : utf8_encode( $data );
		}

		return $this->escape_data( $data );
	}
	/**
	 * Export content
	 *
	 * @param array  $export_data Export data.
	 * @param string $delm Export delimiter.
	 */
	private function set_content( $export_data, $delm = ',' ) {
		if ( isset( $export_data ) && isset( $export_data['body_data'] ) && count( $export_data['body_data'] ) > 0 ) {
			$row_datas = array_values( $export_data['body_data'] );
			foreach ( $row_datas as $row_data ) {
				foreach ( $row_data as $key => $value ) {
					$row_data[ $key ] = $this->format_data( $value );
				}
				$this->fput_csv( $this->file_pointer, $row_data, $delm );
			}
		}
	}
	/**
	 * Set data header
	 *
	 * @param array   $export_data Export data.
	 * @param integer $offset Offset.
	 * @param string  $delm Delimiter.
	 */
	private function set_head( $export_data, $offset, $delm = ',' ) {
		if ( 0 == $offset && isset( $export_data ) && isset( $export_data['head_data'] ) && count( $export_data['head_data'] ) > 0 ) {
			foreach ( $export_data['head_data'] as $key => $value ) {
				$export_data['head_data'][ $key ] = $this->format_data( $value );
			}
			$this->fput_csv( $this->file_pointer, $export_data['head_data'], $delm );
		}
	}
	/**
	 * Write to file
	 *
	 * @param resource $fp File pointer.
	 * @param array    $row Row data.
	 * @param string   $delm Delimiter.
	 * @param string   $encloser Enclosure.
	 */
	private function fput_csv( $fp, $row, $delm = ',', $encloser = '"' ) {
		fputcsv( $fp, $row, $delm, $encloser );
	}
}
