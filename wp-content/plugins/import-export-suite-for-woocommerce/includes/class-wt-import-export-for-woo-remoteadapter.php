<?php
/**
 * Remote adapter section of the plugin
 *
 * @link
 *
 * @package ImportExportSuite\Includes\RemoteAdapter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_RemoteAdapter Class.
 */
abstract class Wt_Import_Export_For_Woo_RemoteAdapter {
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $id = '';
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $title = '';
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	}
	/**
	 * Upload
	 *
	 * @param string $local_file Local file.
	 * @param string $remote_file_name File name.
	 * @param array  $form_data Form data.
	 * @param array  $out Response.
	 */
	abstract public function upload( $local_file, $remote_file_name, $form_data, $out );
	/**
	 * Delete
	 */
	abstract public function delete();
}
