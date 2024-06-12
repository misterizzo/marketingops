<?php
/**
 * CSV reading section of the plugin
 *
 * @link
 *
 * @package ImportExportSuite\Admin\Classes\Csvreader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_Csvreader Class.
 */
class Wt_Import_Export_For_Woo_Csvreader {

		/**
		 * Delimiter
		 *
		 * @var string
		 */
	public $delimiter = ',';
		/**
		 * Escape check
		 *
		 * @var boolean
		 */
	public $fgetcsv_esc_check = 0;
	/**
	 * Constructor.
	 *
	 * @param string $delimiter Delimiter.
	 * @since 1.0.0
	 */
	public function __construct( $delimiter = ',' ) {
		$this->delimiter = $delimiter;
		$this->delimiter = ( 'tab' == $this->delimiter ? "\t" : $this->delimiter );
				/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a post
		 * collection request.
		 *
		 * @since 1.0.0
		 *
		 * @param string           $enclosure    CSV enclosure.
		 */
		$this->enclosure = apply_filters( 'wt_csv_reader_enclosure', '"' );

		/* version 5.3.0 onwards 5th escaping argument introduced in `fgetcsv` function */
		$this->fgetcsv_esc_check = ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 );
	}

	/**
	 *   Taking sample data for mapping screen preparation
	 *   This function skip empty rows and take first two non empty rows
	 *
	 * @param string  $file Filename.
	 * @param boolean $grouping Grouping.
	 */
	public function get_sample_data( $file, $grouping = false ) {
		$enc = false;
		if ( function_exists( 'mb_detect_encoding' ) ) {
			$enc = mb_detect_encoding( $file, 'UTF-8, ISO-8859-1', true );
		}
		if ( $enc ) {
			setlocale( LC_ALL, 'en_US.' . $enc );
		}
		@ini_set( 'auto_detect_line_endings', true );

		$sample_data_key = array();
		$sample_data_val = array();
		$sample_data = array();
		$handle = @fopen( $file, 'r' ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
		if ( false !== ( $handle ) ) {
			$row_count = 0;
			while ( ( $row = ( $this->fgetcsv_esc_check ) ? fgetcsv( $handle, 0, $this->delimiter, $this->enclosure, '"' ) : fgetcsv( $handle, 0, $this->delimiter, $this->enclosure ) ) !== false ) {
				if ( count( array_filter( $row ) ) == 0 ) {
					continue;
				} else {
					$row_count++;
				}

				if ( 1 == $row_count ) {
					$sample_data_key = $row;
				} else // taking data row.
				{
					$sample_data_val = $row;
					break; // only single data row needed.
				}
			}

			foreach ( $sample_data_key as $k => $key ) {
				if ( ! $key ) {
					continue;
				}

				$val = ( isset( $sample_data_val[ $k ] ) ? $this->format_data_from_csv( $sample_data_val[ $k ], $enc ) : '' );

				/* removing BOM like non characters */
				$key = preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $key );
				if ( $grouping ) {
					if ( false !== strrpos( $key, ':' ) ) {
						$key_arr = explode( ':', $key );
						if ( count( $key_arr ) > 1 ) {
							$meta_key = $key_arr[0];
							if ( ! isset( $sample_data[ $meta_key ] ) || ! is_array( $sample_data[ $meta_key ] ) ) {
								$sample_data[ $meta_key ] = array();
							}
							$sample_data[ $meta_key ][ $key ] = $val;
						} else {
							$sample_data[ $key ] = $val;
						}
					} else {
						$sample_data[ $key ] = $val;
					}
				} else {
					$sample_data[ $key ] = $val;
				}
			}

			fclose( $handle );
		}

		return $sample_data;
	}

	/**
	 * Get data from CSV as batch
	 *
	 * @param string  $file Filename.
	 * @param integer $offset Offset.
	 * @param integer $batch_count Batch count.
	 * @param object  $module_obj Module.
	 * @param array   $form_data Form data.
	 * @return type
	 */
	public function get_data_as_batch( $file, $offset, $batch_count, $module_obj, $form_data ) {
		// Set locale.
		$enc = false;
		if ( function_exists( 'mb_detect_encoding' ) ) {
			$enc = mb_detect_encoding( $file, 'UTF-8, ISO-8859-1', true );
		}
		if ( $enc ) {
			setlocale( LC_ALL, 'en_US.' . $enc );
		}
		@ini_set( 'auto_detect_line_endings', true );

		$out = array(
			'response' => false,
			'offset' => $offset,
			'data_arr' => array(),
		);
		$handle = @fopen( $file, 'r' );// phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
		if ( false !== ( $handle ) ) {
			/*
			*   taking head
			*/
			$head_arr = array();
			while ( ( $row = ( $this->fgetcsv_esc_check ) ? fgetcsv( $handle, 0, $this->delimiter, $this->enclosure, '"' ) : fgetcsv( $handle, 0, $this->delimiter, $this->enclosure ) ) !== false ) {
				if ( count( array_filter( $row ) ) != 0 ) {
					$head_arr = $row;
					if ( 0 == $offset ) {
						$offset_after_head = ftell( $handle );
						fseek( $handle, $offset_after_head ); /* skipping head row */
					}
					break;
				}
			}

			$empty_head_columns = array();
			foreach ( $head_arr as $head_key => $head_val ) {
				if ( trim( $head_val ) == '' ) {
					$empty_head_columns[] = $head_key;
					unset( $head_arr[ $head_key ] );
				} else {
					/* removing BOM like non characters */
					$head_arr[ $head_key ] = preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $head_val );
				}
			}

			/* moving the pointer to corresponding batch. If not first batch */
			if ( 0 != $offset ) {
				fseek( $handle, $offset );
			}

			$out_arr = array();

			$row_count = 0;
			/* taking data */
			while ( ( $row = ( $this->fgetcsv_esc_check ) ? fgetcsv( $handle, 0, $this->delimiter, $this->enclosure, '"' ) : fgetcsv( $handle, 0, $this->delimiter, $this->enclosure ) ) !== false ) {
				$offset = ftell( $handle ); /* next offset */

				/*
				*   Skipping empty rows
				*/
				if ( count( array_filter( $row ) ) == 0 ) {
					continue;
				}

				/*
				*   Remove values of empty head
				*/
				foreach ( $empty_head_columns as $key ) {
					unset( $row[ $key ] );
				}

				/*
				*   Creating associative array with heading and data
				*/
				$row_column_count = count( $row );
				$head_column_count = count( $head_arr );
				if ( $row_column_count < $head_column_count ) {
					$empty_row = array_fill( $row_column_count, ( $head_column_count - $row_column_count ), '' );
					$row = array_merge( $row, $empty_row );
					$empty_row = null;
					unset( $empty_row );
				} elseif ( $row_column_count > $head_column_count ) {
					$row = array_slice( $row, 0, $head_column_count ); // IER-209.
					// continue;.
				}

				/* clearing temp variables */
				$row_column_count = null;
				$head_column_count = null;
				unset( $row_column_count, $head_column_count );

				$head_arr = array_map( 'trim', $head_arr ); // WUWCIEP-132.
				/* preparing associative array */
				$data_row = array_combine( $head_arr, $row );

				$out_arr[] = $module_obj->process_column_val( $data_row, $form_data );
				// $out_arr[]=$data_row;

				unset( $data_row );

				$row_count++;
				if ( $row_count == $batch_count ) {
					break;
				}
			}
			fclose( $handle );

			$out = array(
				'response' => true,
				'offset' => $offset,
				'rows_processed' => $row_count,
				'data_arr' => $out_arr,
			);

			$head_arr = null;
			$form_data = null;
			$row = null;
			$out_arr = null;
			unset( $head_arr, $form_data, $row, $out_arr );
		}

		return $out;
	}
	/**
	 * Format data from CSV
	 *
	 * @param array $data Data to format.
	 * @param type  $enc Enclosure.
	 * @return type
	 */
	protected function format_data_from_csv( $data, $enc ) {
		return ( ( 'UTF-8' == $enc ) ? trim( $data ) : utf8_encode( trim( $data ) ) );
	}
}
