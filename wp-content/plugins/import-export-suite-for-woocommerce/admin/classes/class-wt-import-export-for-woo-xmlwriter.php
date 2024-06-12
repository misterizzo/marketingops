<?php
/**
 * Log xmlwriter section of the plugin
 *
 * @link
 *
 * @package ImportExportSuite\Admin\Classes\Xmlwriter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_Xmlwriter Class.
 */
class Wt_Import_Export_For_Woo_Xmlwriter extends XMLWriter {
		/**
		 * Log file path
		 *
		 * @var string
		 */
	public $file_path = '';
		/**
		 * Log file path
		 *
		 * @var string
		 */
	public $data_ar = '';
	/**
	 *   Get xml writer
	 *
	 * @param string $file_path File path.
	 */
	public function __construct( $file_path ) {
		$this->file_path = $file_path;
	}
	/**
	 * Write to file
	 *
	 * @param array   $export_data Export data.
	 * @param integer $offset Offset.
	 * @param boolean $is_last_offset Is last offset.
	 * @param string  $to_export To export.
	 */
	public function write_to_file( $export_data, $offset, $is_last_offset, $to_export ) {
		$this->export_data = $export_data;
		$this->head_data = $export_data['head_data'];
		$file_path = $this->file_path;

		$this->openMemory();
		$this->setIndent( true );
		$xml_version = '1.0';
		$xml_encoding = 'UTF-8';
		// $xml_standalone = 'no';

		/* write array data to xml */
		$this->array_to_xml( $this, ucfirst( $to_export ), $export_data['body_data'], null );

		if ( $is_last_offset ) {
			$prev_body_xml_data = '';
			$body_xml_data = $this->outputMemory(); // taking current offset data.
			$this->endDocument();

			/* need this checking because, if only single batch exists */
			if ( file_exists( $file_path ) ) {
				$fpr = fopen( $file_path, 'r' );
				$prev_body_xml_data = fread( $fpr, filesize( $file_path ) ); // reading previous offset data.
			}

			/* create xml starting tag */
			$this->startDocument( $xml_version, $xml_encoding /*, $xml_standalone*/ );
			$doc_xml_data = $this->outputMemory(); // taking xml starting data.
			$this->endDocument();

			/* creating xml doc data */
			$xml_data = $doc_xml_data . '<' . ucfirst( $to_export ) . 's>' . $prev_body_xml_data . $body_xml_data . '</' . ucfirst( $to_export ) . 's>';

			$fp = fopen( $file_path, 'w' );  // writing the full xml data to file.
			fwrite( $fp, $xml_data );
			fclose( $fp );

		} else // append data to file.
		{
			$xml_data = $this->outputMemory(); // taking xml starting data.
			$this->endDocument();
			if ( 0 == $offset ) {
				$fp = fopen( $file_path, 'w' );
			} else {
				$fp = fopen( $file_path, 'a+' );
			}
			fwrite( $fp, $xml_data );
			fclose( $fp );
		}
	}
	/**
	 * Start attribute
	 *
	 * @param object $xml_writer XML writer.
	 * @param string $key Key.
	 */
	public function start_attr( &$xml_writer, $key ) {
		$key = ( isset( $this->head_data[ $key ] ) ? $this->head_data[ $key ] : $key );
		$xml_writer->startAttribute( $key );
	}
	/**
	 * Start Element
	 *
	 * @param object $xml_writer XML writer.
	 * @param string $key Key.
	 */
	public function start_elm( &$xml_writer, $key ) {
		$key = ( isset( $this->head_data[ $key ] ) ? $this->head_data[ $key ] : $key );
		$xml_writer->startElement( $key );
	}
	/**
	 * Write element
	 *
	 * @param object $xml_writer XML writer.
	 * @param string $key Key.
	 * @param string $value Value.
	 */
	public function write_elm( &$xml_writer, $key, $value ) {
		$key = ( isset( $this->head_data[ $key ] ) ? $this->head_data[ $key ] : $key );
		$xml_writer->writeElement( sanitize_title( $key ), $value );
	}
	/**
	 * Array to XML
	 *
	 * @param object $xml_writer XML writer.
	 * @param string $element_key Key.
	 * @param string $element_value Value.
	 * @param string $xmlnsurl XMLNS.
	 * @return type
	 */
	public function array_to_xml( $xml_writer, $element_key, $element_value = array(), $xmlnsurl = null ) {
		if ( ! empty( $xmlnsurl ) ) {
			$my_root_tag = $element_key;
			$xml_writer->startElementNS( null, $element_key, $xmlnsurl );
		} else {
			$my_root_tag = '';
		}

		if ( is_array( $element_value ) ) {
			// handle attributes.
			if ( '@attributes' === $element_key ) {
				foreach ( $element_value as $attribute_key => $attribute_value ) {
					$this->start_attr( $xml_writer, $attribute_key );
					$xml_writer->text( $attribute_value );
					$xml_writer->endAttribute();
				}
				return;
			}

			// handle order elements.
			if ( is_numeric( key( $element_value ) ) ) {
				foreach ( $element_value as $child_element_key => $child_element_value ) {
					if ( $element_key !== $my_root_tag ) {
						$this->start_elm( $xml_writer, $element_key );
					}
					foreach ( $child_element_value as $sibling_element_key => $sibling_element_value ) {
						$this->array_to_xml( $xml_writer, $sibling_element_key, $sibling_element_value );
					}
					$xml_writer->endElement();
				}
			} else {
						/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a post
		 * collection request.
		 *
		 * @since 1.0.0
		 *
		 * @param string           $element_key    XML element_key.
		 */
				$element_key = apply_filters( 'wt_ier_alter_export_xml_tags', $element_key );
				if ( $element_key !== $my_root_tag ) {
					$this->start_elm( $xml_writer, $element_key );
				}
				foreach ( $element_value as $child_element_key => $child_element_value ) {
					$this->array_to_xml( $xml_writer, $child_element_key, $child_element_value );
				}
				$xml_writer->endElement();
			}
		} else {
			// handle single elements.
			if ( '@value' == $element_key ) {
				$xml_writer->text( $element_value );
				// Wrap element in CDATA tag if it contain illegal characters.
			} elseif ( false !== strpos( $element_value, '<' ) || false !== strpos( $element_value, '>' ) ) {
					$arr = explode( ':', $element_key );
				if ( isset( $arr[1] ) ) {
					$xml_writer->startElementNS( $arr[0], $arr[1], $arr[0] );
				} else {
					$this->start_elm( $xml_writer, $element_key );
				}
					$xml_writer->writeCdata( $element_value );
					$xml_writer->endElement();

			} else {
				// Write full namespaced element tag using xmlns.
				$arr = explode( ':', $element_key );
				if ( count( $arr ) > 1 ) {
					$xml_writer->writeElementNS( $arr[0], sanitize_title( $arr[1] ), $arr[0], $element_value );
				} else {
					$this->write_elm( $xml_writer, $element_key, $element_value );
				}
			}

			return;
		}
	}
}
