<?php
/**
 * Log xmlreader section of the plugin
 *
 * @link
 *
 * @package ImportExportSuite\Admin\Classes\Xmlreader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_Xmlreader Class.
 */
class Wt_Import_Export_For_Woo_Xmlreader {

		/**
		 * Log file path
		 *
		 * @var string
		 */
	public $csv_writer = null;
			/**
			 * Log file path
			 *
			 * @var string
			 */
	public $module_obj = null;
			/**
			 * Log file path
			 *
			 * @var string
			 */
	public $form_data = array();
	/**
	 *   Taking sample data for mapping screen preparation
	 *
	 * @param string  $file Filename.
	 * @param boolean $grouping Grouping.
	 */
	public function get_sample_data( $file, $grouping = false ) {
		$sample_data = $this->get_xml_data( $file, 0, 1 );

		$sample_data = ( is_array( $sample_data ) && isset( $sample_data[0] ) ? $sample_data[0] : array() );

		if ( $grouping ) {
			$out = array();
			foreach ( $sample_data as $key => $val ) {

				if ( false !== strrpos( $key, ':' ) ) {
					$key_arr = explode( ':', $key );
					if ( count( $key_arr ) > 1 ) {
							$meta_key = $key_arr[0];
						if ( ! isset( $out[ $meta_key ] ) ) {
							$out[ $meta_key ] = array();
						}
							$out[ $meta_key ][ $key ] = $val;
					} else {
							$out[ $key ] = $val;
					}
				} else {
						$out[ $key ] = $val;
				}
			}
			$sample_data = $out;
		}

		return $sample_data;
	}

	/**
	 *   Conver XML file to CSV.
	 *   To avoid multiple looping we are calling CSV reader sub functions here also
	 *
	 * @param string  $xml_file File name.
	 * @param string  $csv_file File name.
	 * @param integer $offset Offset.
	 * @param integer $batch Batch.
	 */
	public function xml_to_csv( $xml_file, $csv_file, $offset = 0, $batch = 30000 ) {
		$out = array(
			'response' => false,
			'msg' => '',
			'finished' => 0,
		);

		$csv_writer_file = WT_IEW_PLUGIN_PATH . 'admin/classes/class-wt-import-export-for-woo-csvwriter.php';
		if ( file_exists( $csv_writer_file ) ) {
			include_once $csv_writer_file;
			$this->csv_writer = new Wt_Import_Export_For_Woo_Csvwriter( $csv_file, $offset );

			/* this method will write data to CSV */
			return $this->get_xml_data( $xml_file, $offset, $batch, 'xml_to_csv' );
		} else {
			return $out;
		}
	}
	/**
	 * Process to CSV
	 *
	 * @param string $val Value.
	 * @return type
	 */
	protected function _process_to_csv_data( $val ) {
		return $this->csv_writer->format_data( $val );
	}
	/**
	 * Write CSV
	 *
	 * @param array   $row_data Row.
	 * @param integer $offset Offset.
	 * @param boolean $is_last_offset Is last offset.
	 */
	protected function _write_csv_row( $row_data, $offset, $is_last_offset ) {
		$this->csv_writer->write_row( $row_data, $offset, $is_last_offset );
	}

	/**
	 * Get XML data
	 *
	 * @param string  $xml_file XML.
	 * @param integer $offset Offset.
	 * @param integer $batch Batch.
	 * @param string  $action Action.
	 * @return boolean
	 */
	public function get_xml_data( $xml_file, $offset = 0, $batch = 30000, $action = '' ) {
		$out = array();
		$node_name = '';
		$node_val = '';
		$p = 0;
		$row_count = 0;
		$node_type = 'nodeType';

		$reader = new XMLReader();
		$reader->open( $xml_file );
		while ( $reader->read() ) {
			if ( XMLReader::ELEMENT == $reader->{$node_type} ) {
				$depth_1_name = $reader->name;
				while ( $reader->read() ) {
					if ( XMLReader::ELEMENT == $reader->{$node_type} ) {
						$depth_2_name = $reader->name;
						$temp_arr = array();
						while ( $reader->read() ) {
							if ( $p < $offset ) {
								$reader->next( $depth_2_name ); // closing node.
								$reader->next( $depth_2_name ); // opening node.
								$p++;
								continue;
							} else {
								break;
							}
						}

						while ( $reader->read() ) {
							if ( XMLReader::ELEMENT == $reader->{$node_type} ) {
								$node_name = $reader->name;

							} elseif ( XMLReader::TEXT == $reader->{$node_type} || XMLReader::CDATA == $reader->{$node_type} || XMLReader::WHITESPACE == $reader->{$node_type} || XMLReader::SIGNIFICANT_WHITESPACE == $reader->{$node_type} ) {
								$node_val = trim( $reader->value );

							} elseif ( XMLReader::END_ELEMENT == $reader->{$node_type} ) {
								if ( $reader->name == $depth_2_name ) {
									break;

								} else {
									if ( 'xml_to_csv' == $action ) {
										$node_val = $this->_process_to_csv_data( $node_val );
									} else {
										$node_val = sanitize_text_field( $node_val );
									}
									$temp_arr[ $node_name ] = $node_val;
									$node_name = '';
									$node_val = '';
								}
							}
						}
						if ( 'xml_to_csv' == $action ) {
							$this->_write_csv_row( $temp_arr, ( $offset + $row_count ), false );
						} elseif ( 'get_data_as_batch' == $action ) {
							$out[] = $this->_process_column_val( $temp_arr );
						} else {
							$out[] = $temp_arr;
						}

						$row_count++;
						if ( $row_count == $batch ) {
							if ( 'xml_to_csv' == $action ) {
								/* just close the file pointer */
								$this->_write_csv_row( array(), $offset, true );
							}
							break 2;
						}
					} elseif ( XMLReader::END_ELEMENT == $reader->{$node_type} && $depth_1_name == $reader->name ) {
						break;
					}
				}
			}
		}
		if ( 'xml_to_csv' == $action ) {
			$finished = 0;
			if ( isset( $depth_2_name ) && '' != $depth_2_name ) {
				if ( ! $reader->next( $depth_2_name ) ) {
					$finished = 1;
				}
			}
			$new_offset = $offset + $batch;
			$out = array(
				'response' => true,
				'msg' => '',
				'rows_processed' => $row_count,
				'finished' => $finished,
				'new_offset' => $new_offset,
			);
		}
		$reader->close();
		return $out;
	}

	/**
	 *   Get data from XML as batch.
	 *   This method is not using. But keeping here for a backup
	 *
	 * @param string  $file Filename.
	 * @param integer $offset Offset.
	 * @param integer $batch_count Batch.
	 * @param object  $module_obj Module.
	 * @param array   $form_data Form data.
	 */
	public function get_data_as_batch( $file, $offset, $batch_count, $module_obj, $form_data ) {
		$out = array(
			'response' => false,
			'offset' => $offset,
			'data_arr' => array(),
		);

		$this->module_obj = $module_obj;
		$this->form_data = $form_data;

		$out['response'] = true;
		$out['data_arr'] = $this->get_xml_data( $file, $offset, $batch_count, 'get_data_as_batch' );

		return $out;
	}
	/**
	 * Process column
	 *
	 * @param array $data_row Row.
	 * @return type
	 */
	protected function _process_column_val( $data_row ) {
		return $this->module_obj->process_column_val( $data_row, $this->form_data );
	}
}
