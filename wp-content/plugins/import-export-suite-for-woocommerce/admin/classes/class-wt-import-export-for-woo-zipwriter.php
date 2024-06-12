<?php
/**
 * Zip writing section of the plugin
 *
 * @link
 *
 * @package ImportExportSuite\Admin\Classes\Zipwriter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_Zipwriter Class.
 */
class Wt_Import_Export_For_Woo_Zipwriter {


	/**
	 * Create Zip
	 *
	 * @param string  $file_path File path.
	 * @param array   $file_arr Data array.
	 * @param integer $offset Offset.
	 */
	public static function write_to_file( $file_path, $file_arr, $offset ) {
		if ( is_array( $file_arr ) ) {
			$zip = new ZipArchive();
			if ( 0 == $offset ) {
				$zip->open( $file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE );
			} else {
				$zip->open( $file_path );
			}

			foreach ( $file_arr as $file_url ) {
				$local_file_path = Wt_Iew_IE_Helper::_get_local_file_path( $file_url );
				if ( $local_file_path ) {
					$added = $zip->addFile( $local_file_path, basename( $local_file_path ) );
				}
			}
			$zip->close();
		}
	}
}
