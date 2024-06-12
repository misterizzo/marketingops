<?php
/**
 * FTP adapter section. This adapter hook the FTP profile
 *
 * @link
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_FtpAdapter Class.
 */
class Wt_Import_Export_For_Woo_FtpAdapter extends Wt_Import_Export_For_Woo_RemoteAdapter {


	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'ftp';
		$this->title = __( 'FTP' );
	}//end __construct()


	/**
	 *   Retrieve FTP profile id from formdata
	 *
	 * @param   array $form_data Form data.
	 * @since 1.0.0
	 * @return integer FTP Profile ID.
	 */
	private function get_ftp_profile_form_id( $form_data ) {
		return ( isset( $form_data['wt_iew_ftp_profile'] ) ? absint( $form_data['wt_iew_ftp_profile'] ) : 0 );
	}//end get_ftp_profile_form_id()


	/**
	 *   Retrive FTP server path from formadata/FTP profile
	 *
	 * @param   integer $file_name Export type.
	 * @param   string  $form_data Steps.
	 * @param   integer $ftp_profile Export method.
	 * @param   string  $action Selected template.
	 * @since 1.0.0
	 * @return string File path.
	 */
	private function prepare_remote_file( $file_name, $form_data, $ftp_profile, $action ) {
		$file_path = ( isset( $form_data[ 'wt_iew_' . $action . '_path' ] ) ? trim( Wt_Iew_Sh::sanitize_item( $form_data[ 'wt_iew_' . $action . '_path' ] ) ) : '' );
		$file_path = ( '' == $file_path ? $ftp_profile[ $action . '_path' ] : $file_path );

		return ( substr( $file_path, -1 ) != '/' ) ? ( $file_path . '/' . basename( $file_name ) ) : ( $file_path . basename( $file_name ) );
	}//end prepare_remote_file()


	/**
	 *    Test FTP connection. Via profile ID or Profile details
	 *
	 * @param   integer $profile_id Export method.
	 * @param   string  $ftp_profile Selected template.
	 * @since 1.0.0
	 * @return array
	 */
	public function test_ftp( $profile_id, $ftp_profile = array() ) {
		$out        = array(
			'status' => false,
			'msg'    => __( 'Error' ),
		);
		$profile_id = absint( $profile_id );
		if ( $profile_id > 0 ) {
			// check an existing profile.
			$ftp_profile = Wt_Import_Export_For_Woo_Ftp::get_ftp_data_by_id( $profile_id );
			if ( ! $ftp_profile ) {
				// no FTP profile found so return false.
				$out['msg'] = __( 'FTP profile not found.' );
				return $out;
			}
		}

		if ( isset( $ftp_profile['is_sftp'] ) && 1 == $ftp_profile['is_sftp'] ) {
			include_once 'class-wt-import-export-for-woo-sftp.php';
			$sftp = new Wt_Import_Export_For_Woo_Sftp();
			$out = $sftp->test_sftp( $ftp_profile, $out );
			return $out;
		} else {
			$ftp_conn = $this->connect_ftp( $ftp_profile );
			if ( 'implicit_ftp_connection' == $ftp_conn ) {
				$out['msg']    = __( 'Successfully tested.' );
				$out['status'] = true;
			} else if ( $ftp_conn && 'implicit_ftp_connection' != $ftp_conn ) {
				// successfully connected.
				   $login = @ftp_login( $ftp_conn, $ftp_profile['user_name'], $ftp_profile['password'] );
				if ( $login ) {
					// successfully logged in.
					if ( 1 == $ftp_profile['passive_mode'] ) {
						if ( ! @ftp_pasv( $ftp_conn, true ) ) {
							// failed to enable passive mode.
							$out['msg'] = __( 'Failed to enable passive mode.' );
							@ftp_close( $ftp_conn );
							return $out;
						} else {
							$out['msg']    = __( 'Successfully tested.' );
							$out['status'] = true;
						}
					} else {
						$out['msg']    = __( 'Success.' );
						$out['status'] = true;
					}
				} else {
								$out['msg'] = __( 'Connected to host but could not login. Server UserID or Password may be wrong or try again with/without FTPS.' );
				}//end if
			} else {
				$out['msg'] = __( 'Failed to establish FTP connection. Server host/IP or port specified may be wrong.' );
			}//end if

			@ftp_close( $ftp_conn );
			return $out;
		}
	}//end test_ftp()

	/**
	 *   Retrieve file.
	 *
	 * @param   string  $form_data Steps.
	 * @param   integer $out Export method.
	 * @param   string  $import_obj Selected template.
	 * @since 1.0.0
	 * @return string File path.
	 */
	public function download( $form_data, $out, $import_obj ) {
		$out['response'] = false;

		// checking file name.
		$remote_file_name = isset( $form_data['wt_iew_import_file'] ) ? trim( Wt_Iew_Sh::sanitize_item( $form_data['wt_iew_import_file'] ) ) : '';
		if ( '' == $remote_file_name ) {
			$out['msg'] = __( 'File not found.' );
			return $out;
		}

		// checking file extension.
				$ext_arr = explode( '.', $remote_file_name );
				$ext     = strtolower( end( $ext_arr ) );

		if ( ! isset( $import_obj->allowed_import_file_type[ $ext ] ) ) {
			// file type is in allowed list.
			$out['msg'] = __( 'File type not allowed.' );
			return $out;
		}

		$file_name = $import_obj->get_temp_file_name( $ext );
		$file_path = $import_obj->get_file_path( $file_name );
		if ( ! $file_path ) {
			$out['msg'] = __( 'Unable to create temp directory.' );
			return $out;
		}

		// retriving profile id from post data.
		$profile_id = $this->get_ftp_profile_form_id( $form_data );
		if ( 0 == $profile_id ) {
			// no FTP profile found so return false.
			$out['msg'] = __( 'FTP profile not found.' );
			return $out;
		}

		$ftp_profile = Wt_Import_Export_For_Woo_Ftp::get_ftp_data_by_id( $profile_id );
		if ( ! $ftp_profile ) {
			// no FTP profile found so return false.
			$out['msg'] = __( 'FTP profile not found.' );
			return $out;
		}

		$out['file_name'] = $file_name;

		if ( 1 == $ftp_profile['is_sftp'] ) {
			// handle sftp download.
			include_once 'class-wt-import-export-for-woo-sftp.php';
			$sftp = new Wt_Import_Export_For_Woo_Sftp();

			/* preparing remote file path */
			$remote_file = $this->prepare_remote_file( $remote_file_name, $form_data, $ftp_profile, 'import' );
			$out = $sftp->download( $ftp_profile, $file_path, $remote_file, $out );

			return $out;
		} else {
			$ftp_conn = $this->connect_ftp( $ftp_profile );
			if ( 'implicit_ftp_connection' == $ftp_conn ) {
				$download_status = $this->wt_implicit_ftp_file_download( $remote_file_name, $ftp_profile['server'], $ftp_profile['user_name'], $ftp_profile['password'], $file_path );
				if ( $download_status ) {
					  $out['msg'] = __( 'Downloaded successfully.' );
					$out['response']  = true;
				} else {
								 $out['msg'] = __( 'Failed to download file.' );
				}
			} else if ( 'implicit_ftp_connection' != $ftp_conn ) {
				// successfully connected.
						$login = @ftp_login( $ftp_conn, $ftp_profile['user_name'], $ftp_profile['password'] );
				if ( $login ) {
					// successfully logged in.
					if ( 1 == $ftp_profile['passive_mode'] ) {
						if ( ! @ftp_pasv( $ftp_conn, true ) ) {
							// failed to enable passive mode.
							$out['msg'] = __( 'Failed to enable passive mode.' );
							@ftp_close( $ftp_conn );
							return $out;
						}
					}

					// preparing remote file path.
					$remote_file = $this->prepare_remote_file( $remote_file_name, $form_data, $ftp_profile, 'import' );

					// downloading file from FTP server.
					if ( ! @ftp_get( $ftp_conn, $file_path, $remote_file, FTP_BINARY ) ) {
						$out['msg'] = __( 'Failed to download file.' );
					} else {
						$out['msg']      = __( 'Downloaded successfully.' );
						$out['response'] = true;
					}
				} else {
									  $out['msg'] = __( 'FTP login failed.' );
				}//end if
			} else {
				$out['msg'] = __( 'Failed to establish FTP connection.' );
			}//end if

			@ftp_close( $ftp_conn );
			return $out;
		}
	}//end download()

	/**
	 *   Retrieve first row.
	 *
	 * @param   string $file file data.
	 * @since 1.0.0
	 * @return string File path.
	 */
	public function get_first_row( $file ) {

		$line = preg_split( '#\r?\n#', $file, 0 )[0];

		return $line;
	}//end get_first_row()

	/**
	 *  Connect FTP.
	 *
	 * @param   string $ftp_profile FTP profile details.
	 * @since 1.0.0
	 * @return resource ftp.
	 */
	protected function connect_ftp( $ftp_profile ) {
		if ( 1 == $ftp_profile['ftps'] ) {
			// use ftps.
			$ftp_conn = @ftp_ssl_connect( $ftp_profile['server'], $ftp_profile['port'] );
		} else {
			$ftp_conn = @ftp_connect( $ftp_profile['server'], $ftp_profile['port'] );
		}

		if ( empty( $ftp_conn ) ) {
			 $curlhandle = curl_init();
			curl_reset( $curlhandle );
			curl_setopt( $curlhandle, CURLOPT_URL, 'ftps://' . $ftp_profile['server'] . '/' );
			curl_setopt( $curlhandle, CURLOPT_USERPWD, $ftp_profile['user_name'] . ':' . $ftp_profile['password'] );
			curl_setopt( $curlhandle, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $curlhandle, CURLOPT_SSL_VERIFYHOST, false );
			curl_setopt( $curlhandle, CURLOPT_FTP_SSL, CURLFTPSSL_TRY );
			curl_setopt( $curlhandle, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_TLS );
			curl_setopt( $curlhandle, CURLOPT_UPLOAD, 0 );
			curl_setopt( $curlhandle, CURLOPT_FTPLISTONLY, 1 );
			curl_setopt( $curlhandle, CURLOPT_RETURNTRANSFER, 1 );
			$result = curl_exec( $curlhandle );
			if ( curl_error( $curlhandle ) ) {
				$ftp_conn = '';
			} else {
				$ftp_conn = 'implicit_ftp_connection';
			}
		}

			return $ftp_conn;
	}//end connect_ftp()


	/**
	 * Upload file
	 *
	 * @param string $local_file Local file path.
	 * @param string $remote_file_name Remote file name.
	 * @param array  $form_data  Formdata of step that holds FTP related form fields.
	 * @param array  $out Output.
	 */
	public function upload( $local_file, $remote_file_name, $form_data, $out ) {
		// retriving profile id from post data.
		$profile_id = $this->get_ftp_profile_form_id( $form_data );

		$out['response'] = false;

		if ( 0 == $profile_id ) {
			// no FTP profile found so return false.
			$out['msg'] = __( 'FTP profile not found.' );
			return $out;
		}

		$ftp_profile = Wt_Import_Export_For_Woo_Ftp::get_ftp_data_by_id( $profile_id );
		if ( ! $ftp_profile ) {
			// no FTP profile found so return false.
			$out['msg'] = __( 'FTP profile not found.' );
			return $out;
		}
		if ( 1 == $ftp_profile['is_sftp'] ) {
			// Handle sftp upload.
			include_once 'class-wt-import-export-for-woo-sftp.php';
			$sftp = new Wt_Import_Export_For_Woo_Sftp();

			// Preparing remote file path.
			$remote_file = $this->prepare_remote_file( $remote_file_name, $form_data, $ftp_profile, 'export' );
			$out = $sftp->upload( $ftp_profile, $local_file, $remote_file, $out );

			return $out;
		} else {
			$ftp_conn = $this->connect_ftp( $ftp_profile );

			if ( 'implicit_ftp_connection' == $ftp_conn ) {
				$upload_status = $this->wt_implicit_ftp_file_upload( $local_file, $remote_file_name, $ftp_profile['server'], $ftp_profile['user_name'], $ftp_profile['password'] );

				if ( $upload_status ) {
					   $out['msg']  = __( 'Uploaded successfully.' );
					   $out['response'] = true;
				} else {
								 $out['msg'] = __( 'Failed to upload file.' );
				}
			} else if ( 'implicit_ftp_connection' != $ftp_conn ) {
				// successfully connected.
						$login = @ftp_login( $ftp_conn, $ftp_profile['user_name'], $ftp_profile['password'] );
				if ( $login ) {
					// successfully logged in.
					if ( 1 == $ftp_profile['passive_mode'] ) {
						if ( ! @ftp_pasv( $ftp_conn, true ) ) {
							// failed to enable passive mode.
							$out['msg'] = __( 'Failed to enable passive mode.' );
							ftp_close( $ftp_conn );
							return $out;
						}
					}

					// preparing remote file path.
					$remote_file = $this->prepare_remote_file( $remote_file_name, $form_data, $ftp_profile, 'export' );

					// uploading file to FTP server.
					if ( ! @ftp_put( $ftp_conn, $remote_file, $local_file, FTP_ASCII ) ) {
						$out['msg'] = __( 'Failed to upload file.' );
					} else {
						$out['msg']      = __( 'Uploaded successfully.' );
						$out['response'] = true;
					}
				} else {
									  $out['msg'] = __( 'FTP login failed.' );
				}//end if
			} else {
				$out['msg'] = __( 'Failed to establish FTP connection.' );
			}//end if

			@ftp_close( $ftp_conn );
			return $out;
		}
	}//end upload()

	/**
	 *   Delete file.
	 *
	 * @since 1.0.0
	 */
	public function delete() {
	}//end delete()

	/**
	 *   Upload file.
	 *
	 * @param   string  $local Steps.
	 * @param   integer $remote Export method.
	 * @param   string  $server Selected template.
	 * @param   integer $username Export method.
	 * @param   string  $password Selected template.
	 * @since 1.0.0
	 * @return bool
	 */
	private function wt_implicit_ftp_file_upload( $local, $remote, $server, $username, $password ) {

		$fp = fopen( $local, 'r' );
		if ( $fp ) { // @codingStandardsIgnoreLine.
			$ftp_server = 'ftps://' . $server . '/' . $remote;
			$ch         = curl_init();

			curl_setopt( $ch, CURLOPT_URL, $ftp_server );
			curl_setopt( $ch, CURLOPT_USERPWD, $username . ':' . $password );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
			curl_setopt( $ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY );
			curl_setopt( $ch, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_TLS );
			curl_setopt( $ch, CURLOPT_UPLOAD, 1 );
			curl_setopt( $ch, CURLOPT_INFILE, $fp );

			curl_exec( $ch );
			$err = curl_error( $ch );
			curl_close( $ch );

			return ! $err;
		}

		return false;
	}//end wt_implicit_ftp_file_upload()

	/**
	 *   Retrieve file.
	 *
	 * @param   string  $remote Steps.
	 * @param   integer $server Export method.
	 * @param   string  $username Selected template.
	 * @param   integer $password Export method.
	 * @param   string  $local Selected template.
	 * @since 1.0.0
	 * @return bool
	 */
	private function wt_implicit_ftp_file_download( $remote, $server, $username, $password, $local = null ) {
		if ( null === $local ) {
			$local = tempnam( '/tmp', 'implicit_ftp' );
		}
		$fp = fopen( $local, 'w' );
		if ( $fp ) { // @codingStandardsIgnoreLine.
			$ftp_server = 'ftps://' . $server . '/' . ltrim( $remote, '/' );
			$ch         = curl_init();

			curl_setopt( $ch, CURLOPT_URL, $ftp_server );
			curl_setopt( $ch, CURLOPT_USERPWD, $username . ':' . $password );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
			curl_setopt( $ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY );
			curl_setopt( $ch, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_TLS );
			curl_setopt( $ch, CURLOPT_UPLOAD, 0 );
			curl_setopt( $ch, CURLOPT_FILE, $fp );

			curl_exec( $ch );

			if ( curl_error( $ch ) ) {
				curl_close( $ch );
				return false;
			} else {
				curl_close( $ch );
				return $local;
			}
		}//end if

		return false;
	}//end wt_implicit_ftp_file_download()
}//end class

return new Wt_Import_Export_For_Woo_FtpAdapter();
