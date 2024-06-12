<?php
/**
 *  The sFTP adapter section. This adapter hook the FTP profile
 *
 * @link
 *
 * @package ImportExportSuite\Admin\Modules\Ftp\Sftp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require WP_CONTENT_DIR . '/wt-sftp-vendor/autoload.php';

/**
 * Wt_Import_Export_For_Woo_Sftp Class.
 */
class Wt_Import_Export_For_Woo_Sftp {

	/**
	 * The sFTP connection object
	 *
	 * @var resuorce
	 */
	private $link = false;

	/**
	 *   Test SFTP connection
	 *
	 *   @param array $profile Profile details.
	 *   @param array $out The output.
	 */
	public function test_sftp( $profile, $out ) {
		if ( $this->connect( $profile['server'], $profile['port'] ) ) {
			if ( $this->login( $profile['user_name'], $profile['password'] ) ) {
				$out['msg'] = __( 'Successfully tested.' );
				$out['status'] = true;
			} else {
				$out['msg'] = __( 'SFTP connection failed.' );
			}
		} else {
			$out['msg'] = __( 'Failed to establish SFTP connection.' );
		}
		return $out;
	}

	/**
	 * The file download function
	 *
	 * @param array  $profile Profile details.
	 * @param string $local_file Local filename.
	 * @param string $remote_file Remote filepath.
	 * @param array  $out The output.
	 * @return array
	 */
	public function download( $profile, $local_file, $remote_file, $out ) {
		$out['response'] = false;
		if ( $this->connect( $profile['server'], $profile['port'] ) ) {
			if ( $this->login( $profile['user_name'], $profile['password'] ) ) {
								$file_name_or_path = basename( $remote_file );
				if ( strpos( $file_name_or_path, '.' ) !== false ) {
					$is_folder = false;
				} else {
					$is_folder = true;
				}

				if ( $is_folder ) {
					/**
					* Filter the query arguments for a request.
					*
					* Enables adding extra arguments or setting defaults for the request.
					*
					* @since 1.0.0
					*
					* @param string   $extension    File extension.
					*/
					$ext = apply_filters( 'wt_iew_folder_import_file_extension', 'csv' );
					$file_list = $this->nlist( $remote_file, array( $ext ), false );

					$temp_file_data = '';
					/**
					 * Filter the query arguments for a request.
					 *
					 * Enables adding extra arguments or setting defaults for the request.
					 *
					 * @since 1.0.0
					 *
					 * @param array           $file_list    File reading adapters.
					 * @param object          $this    Current action.
					 * @param string          $remote_file    File reading adapter.
					 */
					$file_list = apply_filters( 'wt_iew_folder_import_fetched_files', $file_list, $this, $remote_file );

					$count = 0;
					$first_row_size = 0;
					foreach ( $file_list as $file_path ) {

						if ( 0 == $count ) {
							$temp_file_data .= trim( $this->get_contents( $remote_file . $file_path ) );
							$first_row = $this->get_first_row( $temp_file_data );
							$first_row_size = strlen( $first_row );

						} else {
							$temp_file_data .= ( $this->get_contents( $remote_file . $file_path, false, $first_row_size ) );
						}
						$count++;
					}
					$file_data = $temp_file_data;
				} else {

					$file_data = $this->get_contents( $remote_file );
				}
				if ( ! empty( $file_data ) ) {
					if ( @file_put_contents( $local_file, $file_data ) ) {
						$out['msg'] = __( 'Downloaded successfully.' );
						$out['response'] = true;
					} else {
						$out['msg'] = __( 'Unable to create temp file.' );
					}
				} else {
					$out['msg'] = __( 'Failed to download file.' );
				}
			} else {
				$out['msg'] = __( 'SFTP connection failed.' );
			}
		} else {
			$out['msg'] = __( 'Failed to establish SFTP connection.' );
		}
		return $out;
	}

	/**
	 * Get the first row from the file
	 *
	 * @param string $file The file data.
	 * @return type
	 */
	public function get_first_row( $file ) {

		$line = preg_split( '#\r?\n#', $file, 0 )[0];

		return $line;
	}

	/**
	 * Upload to the sFTP
	 *
	 * @param array  $profile Profile details.
	 * @param string $local_file Local filename.
	 * @param string $remote_file Remote filepath.
	 * @param array  $out The output.
	 * @return type
	 */
	public function upload( $profile, $local_file, $remote_file, $out ) {
		$out['response'] = false;
		if ( $this->connect( $profile['server'], $profile['port'] ) ) {
			if ( $this->login( $profile['user_name'], $profile['password'] ) ) {
				if ( $this->put_contents( $remote_file, $local_file ) ) {
					$out['msg'] = __( 'Uploaded successfully.' );
					$out['response'] = true;
				} else {
					$out['msg'] = __( 'Failed to upload file.' );
				}
			} else {
				$out['msg'] = __( 'SFTP login failed.' );
			}
		} else {
			$out['msg'] = __( 'Failed to establish SFTP connection.' );
		}
		return $out;
	}
	/**
	 * Login function.
	 *
	 * @param string $username Username.
	 * @param string $password Password.
	 * @return type
	 */
	private function login( $username, $password ) {
		return $this->link->login( $username, $password ) ? true : false;
	}
	/**
	 * Connect function.
	 *
	 * @param string  $hostname Host name.
	 * @param integer $port Port  number.
	 * @return type
	 */
	private function connect( $hostname, $port = 22 ) {
		$this->link = new \phpseclib3\Net\SFTP( $hostname, $port );
		return ( $this->link ? true : false );
	}
	/**
	 * Write file content.
	 *
	 * @param string $file File.
	 * @param string $local_file Local file.
	 * @return type
	 */
	private function put_contents( $file, $local_file ) {
		$ret = $this->link->put( $file, $local_file, \phpseclib3\Net\SFTP::SOURCE_LOCAL_FILE );
		return false !== $ret;
	}
	/**
	 * Change file permission.
	 *
	 * @param string         $file File.
	 * @param string/boolean $mode Mode.
	 * @param boolean        $recursive Is recursive.
	 * @return type
	 */
	private function chmod( $file, $mode = false, $recursive = false ) {
		return false === $mode ? false : $this->link->chmod( $mode, $file, $recursive );
	}
	/**
	 * Get file content.
	 *
	 * @param string  $file File.
	 * @param string  $local_file Local file.
	 * @param integer $offset Offset.
	 * @param integer $length Limit.
	 * @return type
	 */
	private function get_contents( $file, $local_file = false, $offset = 0, $length = -1 ) {
		return $this->link->get( $file, $local_file, $offset, $length );
	}
	/**
	 * Get file size.
	 *
	 * @param string $file File.
	 * @return type
	 */
	private function size( $file ) {
		$result = $this->link->stat( $file );
		return $result['size'];
	}
	/**
	 * Get file content as array.
	 *
	 * @param string $file File.
	 * @return string
	 */
	public function get_contents_array( $file ) {
		$lines = preg_split( '#(\r\n|\r|\n)#', $this->link->get( $file ), -1, PREG_SPLIT_DELIM_CAPTURE );
		$new_lines = array();
		$lines_count = count( $lines );
		for ( $i = 0; $i < $lines_count; $i += 2 ) {
			$new_lines[] = $lines[ $i ] . $lines[ $i + 1 ];
		}
		return $new_lines;
	}
	/**
	 * Delete file.
	 *
	 * @param string $file File.
	 * @return type
	 */
	public function delete_file( $file ) {
		return $this->link->delete( $file );
	}

	/**
	 * List files.
	 *
	 * @param string  $dir Directory.
	 * @param array   $file_types File types.
	 * @param boolean $recursive Is recursive.
	 * @return type
	 */
	public function nlist( $dir = '.', $file_types = array(), $recursive = false ) {
		$list = $this->link->nlist( $dir, $recursive );
		if ( empty( $file_types ) ) {
			return $list; // return all items if not specifying any file types.
		}
		$collection = array();
		foreach ( $list as $item => $value ) {

			$item_pathinfo = pathinfo( $dir . DIRECTORY_SEPARATOR . $value );

			$item_extension = isset( $item_pathinfo['extension'] ) ? $item_pathinfo['extension'] : '';

			if ( ! empty( $file_types ) && ! in_array( $item_extension, $file_types ) ) {
				continue;
			}

			$collection[ $item ] = $value;
		}
		return $collection;
	}

	/**
	 * Raw list files
	 *
	 * @param string  $dir Directory.
	 * @param array   $file_types File types.
	 * @param boolean $recursive Is recursive.
	 * @return type
	 */
	public function rawlist( $dir = '.', $file_types = array(), $recursive = false ) {
		$list = $this->link->rawlist( $dir, $recursive );
		if ( empty( $file_types ) ) {
			return $list; // return all items if not specifying any file types.
		}
		$collection = array();
		foreach ( $list as $item => $value ) {

			$item_pathinfo = pathinfo( $dir . DIRECTORY_SEPARATOR . $item );

			$item_extension = isset( $item_pathinfo['extension'] ) ? $item_pathinfo['extension'] : '';

			if ( ! empty( $file_types ) && ! in_array( $item_extension, $file_types ) ) {
				continue;
			}

			$collection[ $item ] = $value;
		}
		return $collection;
	}
}
