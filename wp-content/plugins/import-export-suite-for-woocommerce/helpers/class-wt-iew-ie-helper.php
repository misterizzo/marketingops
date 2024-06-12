<?php
/**
 * Import Export Helper Library
 *
 * Includes helper functions for import, export, history modules
 *
 * @link
 *
 * @package ImportExportSuite\Helpers\IEHelper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wt_Iew_IE_Helper' ) ) {
	/**
	 * Wt_Iew_IE_Helper Class.
	 */
	class Wt_Iew_IE_Helper {

		/**
		 * Get CSV delimiter
		 *
		 * @return type
		 */
		public static function _get_csv_delimiters() {
			return array(
				'comma'      => array(
					'value' => __( 'Comma' ),
					'val' => ',',
				),
				'semicolon'  => array(
					'value' => __( 'Semicolon' ),
					'val' => ';',
				),
				'tab'        => array(
					'value' => __( 'Tab' ),
					'val' => "\t",
				),
				'space'      => array(
					'value' => __( 'Space' ),
					'val' => ' ',
				),
				'other'      => array(
					'value' => __( 'Other' ),
					'val' => '',
				),
			);
		}
		/**
		 * Get local file path
		 *
		 * @param string $file_url File path.
		 * @return boolean|string
		 */
		public static function _get_local_file_path( $file_url ) {
			$file_path = untrailingslashit( ABSPATH ) . str_replace( site_url(), '', $file_url );

			if ( file_exists( $file_path ) ) {
				return $file_path;
			} else {
				/* Retrying if the directory structure is different from wordpress default file structure */
				$url_parms = explode( '/', $file_url );

				$file_name = end( $url_parms );

				$file_dir_name = prev( $url_parms );

				$file_path = WP_CONTENT_DIR . '/' . $file_dir_name . '/' . $file_name;

				if ( file_exists( $file_path ) ) {
					return $file_path;
				} else {

					return false;
				}
			}
		}
		/**
		 * Get validation rules
		 *
		 * @param string $step Processing current step.
		 * @param array  $form_data Form data.
		 * @param object $module_obj Module Module.
		 * @return type
		 */
		public static function get_validation_rules( $step, $form_data, $module_obj ) {
			$method_name = 'get_' . $step . '_screen_fields';
			$out         = array();
			if ( method_exists( $module_obj, $method_name ) ) {
				$fields  = $module_obj->{$method_name}( $form_data );
				$out     = Wt_Import_Export_For_Woo_Common_Helper::extract_validation_rules( $fields );
			}
			$form_data   = null;
			$module_obj   = null;
			unset( $form_data, $module_obj );
			return $out;
		}
		/**
		 * Sanitize form data
		 *
		 * @param array  $form_data Form data.
		 * @param object $module_obj Module.
		 * @return type
		 */
		public static function sanitize_formdata( $form_data, $module_obj ) {
			$out = array();
			if ( isset( $form_data['post_type_form_data']['item_type'] ) ) {
				if ( 'export' == $module_obj->module_base ) {
					$module_obj->to_export = $form_data['post_type_form_data']['item_type'];
				} elseif ( 'import' == $module_obj->module_base ) {
					$module_obj->to_import = $form_data['post_type_form_data']['item_type'];
				}
			}
			foreach ( $module_obj->steps as $step => $step_data ) {
				if ( 'mapping' == $step ) { // custom rule needed for mapping fieds.

					/* general mapping fields section */
					if ( isset( $form_data['mapping_form_data'] ) && is_array( $form_data['mapping_form_data'] ) ) {
						$mapping_form_data = $form_data['mapping_form_data'];

						/* mapping fields. This is an internal purpose array */
						if ( isset( $mapping_form_data['mapping_fields'] ) && is_array( $mapping_form_data['mapping_fields'] ) ) {
							foreach ( $mapping_form_data['mapping_fields'] as $key => $value ) {
								$new_key                                         = sanitize_text_field( $key );
								$value                                           = array( sanitize_text_field( $value[0] ), absint( $value[1] ) );
								unset( $mapping_form_data['mapping_fields'][ $key ] );
								$mapping_form_data['mapping_fields'][ $new_key ]   = $value;
							}
						}

						/* mapping enabled meta items */
						if ( isset( $mapping_form_data['mapping_enabled_fields'] ) && is_array( $mapping_form_data['mapping_enabled_fields'] ) ) {
							$mapping_form_data['mapping_enabled_fields'] = Wt_Iew_Sh::sanitize_item( $mapping_form_data['mapping_enabled_fields'], 'text_arr' );
						}

						/* mapping fields. Selected fields only */
						if ( isset( $mapping_form_data['mapping_selected_fields'] ) && is_array( $mapping_form_data['mapping_selected_fields'] ) ) {
							foreach ( $mapping_form_data['mapping_selected_fields'] as $key => $value ) {
								$new_key                                                 = sanitize_text_field( $key );
								unset( $mapping_form_data['mapping_selected_fields'][ $key ] );
								$mapping_form_data['mapping_selected_fields'][ $new_key ]  = sanitize_text_field( $value );
							}
						}

						$out['mapping_form_data'] = $mapping_form_data;
					}

					/* meta mapping fields section */
					if ( isset( $form_data['meta_step_form_data'] ) && is_array( $form_data['meta_step_form_data'] ) ) {
						$meta_step_form_data = $form_data['meta_step_form_data'];
						/* mapping fields. This is an internal purpose array */
						if ( isset( $meta_step_form_data['mapping_fields'] ) && is_array( $meta_step_form_data['mapping_fields'] ) ) {
							foreach ( $meta_step_form_data['mapping_fields'] as $meta_key => $meta_value ) {
								foreach ( $meta_value as $key => $value ) {
									$new_key                 = sanitize_text_field( $key );
									$value                   = array( sanitize_text_field( $value[0] ), absint( $value[1] ) );
									unset( $meta_value[ $key ] );
									$meta_value[ $new_key ]  = $value;
								}
								$meta_step_form_data['mapping_fields'][ $meta_key ] = $meta_value;
							}
						}

						/* mapping fields. Selected fields only */
						if ( isset( $meta_step_form_data['mapping_selected_fields'] ) && is_array( $meta_step_form_data['mapping_selected_fields'] ) ) {
							foreach ( $meta_step_form_data['mapping_selected_fields'] as $meta_key => $meta_value ) {
								foreach ( $meta_value as $key => $value ) {
									$new_key = sanitize_text_field( $key );
									unset( $meta_value[ $key ] );
									$meta_value[ $new_key ]  = sanitize_text_field( $value );
								}
								$meta_step_form_data['mapping_selected_fields'][ $meta_key ] = $meta_value;
							}
						}

						$out['meta_step_form_data'] = $meta_step_form_data;
					}
				} else {
					$current_form_data_key   = $step . '_form_data';
					$current_form_data       = ( isset( $form_data[ $current_form_data_key ] ) ? $form_data[ $current_form_data_key ] : array() );
					if ( in_array( $step, $module_obj->step_need_validation_filter ) ) {
						$validation_rule = self::get_validation_rules( $step, $current_form_data, $module_obj );

						foreach ( $current_form_data as $key => $value ) {
							$no_prefix_key           = str_replace( 'wt_iew_', '', $key );
							$current_form_data[ $key ] = Wt_Iew_Sh::sanitize_data( $value, $no_prefix_key, $validation_rule );
						}
					} else {
						$validation_rule = ( isset( $module_obj->validation_rule[ $step ] ) ? $module_obj->validation_rule[ $step ] : array() );
						foreach ( $current_form_data as $key => $value ) {
							$current_form_data[ $key ] = Wt_Iew_Sh::sanitize_data( $value, $key, $validation_rule );
						}
					}
					$out[ $current_form_data_key ] = $current_form_data;
				}
			}
			$form_data = null;
			$current_form_data    = null;
			$mapping_form_data    = null;
			$meta_step_form_data = null;
			$module_obj            = null;
			unset( $form_data, $current_form_data, $mapping_form_data, $meta_step_form_data, $module_obj );
			return $out;
		}

		/**
		 * Debug panel
		 *
		 * @param string $module_base Module.
		 */
		public static function debug_panel( $module_base ) {
			if ( 'import' == $module_base || 'export' == $module_base ) {
				$debug_panel_btns = array(
					'refresh_step'       => array(
						'title'      => __( 'Refresh the step' ),
						'icon'       => 'dashicons dashicons-update',
						'onclick'    => 'wt_iew_' . $module_base . '.refresh_step();',
					),
					'console_form_data'  => array(
						'title'      => __( 'Console form data' ),
						'icon'       => 'dashicons dashicons-code-standards',
						'onclick'    => 'wt_iew_' . $module_base . '.console_formdata();',
					),
				);
			}
			/**
			 * Filter the query arguments for a request.
			 *
			 * Enables adding extra arguments or setting defaults for a post
			 * collection request.
		 *
		 * @since 1.0.0
		 *
			 * @param array           $debug_panel_btns    Panel buttons.
			 * @param string          $module_base    Current module.
			 */
			$debug_panel_btns = apply_filters( 'wt_iew_debug_panel_buttons', $debug_panel_btns, $module_base );
			if ( defined( 'WT_IEW_DEBUG' ) && WT_IEW_DEBUG && is_array( $debug_panel_btns ) && count( $debug_panel_btns ) > 0 ) {
				?>
				<div class="wt_iew_debug_panel" title="<?php esc_html_e( 'For debugging process' ); ?>">
					<div class="wt_iew_debug_panel_hd"><?php esc_html_e( 'Debug panel' ); ?></div>
					<div class="wt_iew_debug_panel_con">
				<?php
				foreach ( $debug_panel_btns as $btn ) {
					?>
							<a onclick="<?php echo wp_kses_post( $btn['onclick'] ); ?>" title="<?php echo wp_kses_post( $btn['title'] ); ?>">
								<span class="<?php echo wp_kses_post( $btn['icon'] ); ?>"></span>
							</a>
					<?php
				}
				?>
					</div>
				</div>
				<?php
			}
		}
		/**
		 * Get mime type
		 *
		 * @param string $filename File.
		 * @return string
		 */
		public static function wt_get_mime_content_type( $filename ) {
			$mime_types  = array(
				'txt'    => 'text/plain',
				'htm'    => 'text/html',
				'html'   => 'text/html',
				'php'    => 'text/html',
				'css'    => 'text/css',
				'js'     => 'application/javascript',
				'json'   => 'application/json',
				'xml'    => 'application/xml',
				'swf'    => 'application/x-shockwave-flash',
				'flv'    => 'video/x-flv',
				// images.
				'png'    => 'image/png',
				'jpe'    => 'image/jpeg',
				'jpeg'   => 'image/jpeg',
				'jpg'    => 'image/jpeg',
				'gif'    => 'image/gif',
				'bmp'    => 'image/bmp',
				'ico'    => 'image/vnd.microsoft.icon',
				'tiff'   => 'image/tiff',
				'tif'    => 'image/tiff',
				'svg'    => 'image/svg+xml',
				'svgz'   => 'image/svg+xml',
				// archives.
				'zip'    => 'application/zip',
				'rar'    => 'application/x-rar-compressed',
				'exe'    => 'application/x-msdownload',
				'msi'    => 'application/x-msdownload',
				'cab'    => 'application/vnd.ms-cab-compressed',
				// audio/video.
				'mp3'    => 'audio/mpeg',
				'qt'     => 'video/quicktime',
				'mov'    => 'video/quicktime',
				// adobe.
				'pdf'    => 'application/pdf',
				'psd'    => 'image/vnd.adobe.photoshop',
				'ai'     => 'application/postscript',
				'eps'    => 'application/postscript',
				'ps'     => 'application/postscript',
				// ms office.
				'doc'    => 'application/msword',
				'rtf'    => 'application/rtf',
				'xls'    => 'application/vnd.ms-excel',
				'ppt'    => 'application/vnd.ms-powerpoint',
				'docx'   => 'application/msword',
				'xlsx'   => 'application/vnd.ms-excel',
				'pptx'   => 'application/vnd.ms-powerpoint',
				// open office.
				'odt'    => 'application/vnd.oasis.opendocument.text',
				'ods'    => 'application/vnd.oasis.opendocument.spreadsheet',
			);
			$value       = explode( '.', $filename );
			$ext         = strtolower( array_pop( $value ) );
			if ( function_exists( 'mime_content_type' ) ) {
				$mimetype = mime_content_type( $filename );
				return $mimetype;
			} elseif ( function_exists( 'finfo_open' ) ) {
				$finfo       = finfo_open( FILEINFO_MIME );
				$mimetype    = finfo_file( $finfo, $filename );
				finfo_close( $finfo );
				return $mimetype;
			} elseif ( array_key_exists( $ext, $mime_types ) ) {
				return $mime_types[ $ext ];
			} else {
				return 'application/octet-stream';
			}
		}

		/**
		 * Get data from URL
		 *
		 * @param string $url URL.
		 * @param string $local_file Local file.
		 * @return boolean
		 */
		public static function get_data_from_url_method_1( $url, $local_file ) {
			$out = array(
				'status' => 0,
				'path' => '',
				'file_name' => '',
			);
			set_time_limit( 0 ); // avoiding time out issue.
			$arr_context_options = array(
				'ssl'    => array(
					'verify_peer'        => false,
					'verify_peer_name'   => false,
				),
				'http'   => array(
					'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36',
				),
			);

			if ( ini_get( 'allow_url_fopen' ) ) {

				set_error_handler(
					function ( $severity, $message, $file, $line ) {
						throw new ErrorException( wp_kses_post( $message ), absint( $severity ), absint( $severity ), esc_html( $file ), absint( $line ) );
					}
				);

				try {
					$file_contents = @file_get_contents( $url, false, stream_context_create( $arr_context_options ) );
				} catch ( Exception $e ) {
					$file_get_contents_error = $e->getMessage();
				}
				restore_error_handler();
			} else {
				echo '<p><strong>' . esc_html__( 'Sorry, allow_url_fopen not activated. Please setup in php.ini', 'wf_csv_import_export' ) . '</strong></p>';
				return false;
			}

			if ( empty( $file_contents ) ) {
				echo '<p><strong>' . esc_html( $file_get_contents_error ) . '</strong></p>';
				return false;
			}

			file_put_contents( $local_file, $file_contents );

			if ( file_exists( $local_file ) && filesize( $local_file ) > 0 ) {

				$out['status'] = 1;
				$out['path'] = $local_file;
				$out['file_name'] = basename( $local_file );
				return $out;
			}
			return false;
		}
		/**
		 * Get data from URL
		 *
		 * @param string $file_path File path.
		 * @param string $local_file Local file.
		 * @return boolean
		 */
		public static function get_data_from_url_method_2( $file_path, $local_file ) {

			if ( file_exists( $local_file ) ) {
				unlink( $local_file );
			}

			$file = @fopen( $file_path, 'rb' );

			if ( is_resource( $file ) ) {
				$fp = @fopen( $local_file, 'w' );
				while ( ! @feof( $file ) ) {
					$chunk = @fread( $file, 1024 );
					@fwrite( $fp, $chunk );
				}
				@fclose( $file );
				@fclose( $fp );
			}
			if ( file_exists( $local_file ) && filesize( $local_file ) > 0 ) {

				$out['status'] = 1;
				$out['path'] = $local_file;
				$out['file_name'] = basename( $local_file );
				return $out;
			}
			return false;
		}
		/**
		 * Get data from URL
		 *
		 * @param string $url URL.
		 * @param string $local_file Local file.
		 * @param string $cookies_in Cookies in.
		 * @return boolean
		 */
		public static function get_data_from_url_method_3( $url, $local_file, $cookies_in = '' ) {
			if ( file_exists( $local_file ) ) {
				unlink( $local_file );
			}
			$options = array(
				CURLOPT_RETURNTRANSFER   => true, // return web page.
				CURLOPT_HEADER           => true, // return headers in addition to content.
				CURLOPT_FOLLOWLOCATION   => true, // follow redirects.
				CURLOPT_ENCODING         => '', // handle all encodings.
				CURLOPT_AUTOREFERER      => true, // set referer on redirect.
				CURLOPT_CONNECTTIMEOUT   => 120, // timeout on connect.
				CURLOPT_TIMEOUT          => 120, // timeout on response.
				CURLOPT_MAXREDIRS        => 10, // stop after 10 redirects.
				CURLINFO_HEADER_OUT      => true,
				CURLOPT_SSL_VERIFYPEER   => true, // Validate SSL Cert.
				CURLOPT_HTTP_VERSION     => CURL_HTTP_VERSION_1_1,
				CURLOPT_COOKIE           => $cookies_in,
			);

			$ch              = curl_init( $url );
			curl_setopt_array( $ch, $options );
			$rough_content   = curl_exec( $ch );
			$err             = curl_errno( $ch );
			$errmsg          = curl_error( $ch );
			$header          = curl_getinfo( $ch );
			curl_close( $ch );

			$header_content  = substr( $rough_content, 0, $header['header_size'] );
			$body_content    = trim( str_replace( $header_content, '', $rough_content ) );
			$pattern         = '#Set-Cookie:\\s+(?<cookie>[^=]+=[^;]+)#m';
			preg_match_all( $pattern, $header_content, $matches );
			$cookies_out      = implode( '; ', $matches['cookie'] );

			$header['errno']   = $err;
			$header['errmsg']  = $errmsg;
			$header['headers']     = $header_content;
			$header['content']     = $body_content;
			$header['cookies']     = $cookies_out;

			if ( 200 == $header['http_code'] && in_array( $header['content_type'], array( 'application/xml', 'text/csv' ) ) ) {
				$fp = @fopen( $local_file, 'w' );
				fwrite( $fp, print_r( $header['content'], true ) );
				fclose( $fp );
			}
			if ( file_exists( $local_file ) && filesize( $local_file ) > 0 ) {
				$out['status'] = 1;
				$out['path'] = $local_file;
				$out['file_name'] = basename( $local_file );
				return $out;
			}
			return false;
		}
		/**
		 * Download file from URL
		 *
		 * @param string $file_url URL.
		 * @param string $local_file File.
		 * @return type
		 */
		public static function wt_wpie_download_file_from_url( $file_url = '', $local_file = '' ) {

			$file_url = self::process_url( $file_url );

			$file_data = self::download_file( $file_url, $local_file );

			if ( is_wp_error( $file_data ) ) {

				$error = $file_data->get_error_message();

				if ( 'Not Found' == $error ) {
					$error = __( 'Please check the URL.' );
				}
				return array(
					'status' => 0,
					'error' => $error,
				);
			}

			$filename = $file_data['name'] ? $file_data['name'] : '';

			$tempname = $file_data['tmp_name'] ? $file_data['tmp_name'] : '';

			if ( 0 === filesize( $tempname ) ) {
				if ( \file_exists( $tempname ) ) {
					unlink( $tempname );
				}
				return array(
					'status' => 0,
					'error' => __( 'Empty File' ),
				);
			} elseif ( ! preg_match( '%\W(xml|zip|csv|xls|xlsx|xml|ods|txt|json)$%i', trim( $filename ) ) ) {
				if ( \file_exists( $tempname ) ) {
					unlink( $tempname );
				}
				return array(
					'status' => 0,
					'error' => __( 'Invalid file extension.' ),
				);
			}

			return array(
				'status' => 1,
				'path' => $local_file,
				'file_name' => $filename,
			);
		}
		/**
		 * Process URL
		 *
		 * @param string $link Link.
		 * @param string $format Format.
		 * @return string
		 */
		public static function process_url( $link = '', $format = 'csv' ) {

			if ( empty( $link ) ) {
				return $link;
			}

			$link = str_replace( ' ', '%20', $link );

			preg_match( '/(?<=.com\/).*?(?=\/d)/', $link, $match );

			if ( isset( $match[0] ) && ! empty( $match[0] ) ) {
				$type = $match[0];
			} else {
				$type = null;
			}

			$parse   = parse_url( $link );
			$domain  = isset( $parse['host'] ) ? $parse['host'] : '';
			unset( $match, $parse );

			if ( preg_match( '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $match ) ) {
				$domain = isset( $match['domain'] ) ? $match['domain'] : '';
			}
			unset( $match );

			if ( ! empty( $domain ) ) {
				switch ( $domain ) {
					case 'dropbox.com':
						if ( substr( $link, -4 ) == 'dl=0' ) {
							$link = str_replace( 'dl=0', 'dl=1', $link );
						}
						break;
					case 'google.com':
						if ( ! empty( $type ) ) {
							switch ( $type ) {
								case 'file':
									$pattern = '/(?<=\/file\/d\/).*?(?=\/edit)/';
									preg_match( $pattern, $link, $match );
									$file_id = isset( $match[0] ) ? $match[0] : null;
									if ( ! empty( $file_id ) ) {
										$link = 'https://drive.google.com/uc?export=download&id=' . $file_id;
									}
									break;
								case 'spreadsheets':
									$pattern = '/(?<=\/spreadsheets\/d\/).*?(?=\/edit)/';
									preg_match( $pattern, $link, $match );
									$file_id = isset( $match[0] ) ? $match[0] : null;
									if ( ! empty( $file_id ) ) {
										$link = 'https://docs.google.com/spreadsheets/d/' . $file_id . '/export?format=' . $format;
									}
									break;
							}
						}
						break;
				}
			}
			return $link;
		}
		/**
		 * Download file
		 *
		 * @param string $file_url URL.
		 * @param string $local_file File.
		 * @return \WP_Error
		 */
		public static function download_file( $file_url = '', $local_file = '' ) {

			if ( empty( $file_url ) ) {
				return new \WP_Error( 'http_404', __( 'Empty File URL' ) );
			}

			$file = self::download_by_api( $file_url, $local_file );

			if ( is_wp_error( $file ) ) {

				$curl_file = self::download_by_curl( $file_url, $local_file );

				if ( is_wp_error( $curl_file ) ) {

					if ( file_exists( $local_file ) ) {
						unlink( $local_file );
					}

					return $file;
				} else {
					$file = $curl_file;
				}
			}

			return array(
				'name' => $file,
				'tmp_name' => $local_file,
			);
		}
		/**
		 * Download using CURL
		 *
		 * @param string $url URL.
		 * @param string $file File.
		 * @return \WP_Error
		 */
		public static function download_by_curl( $url = '', $file = '' ) {

			if ( ! function_exists( 'curl_version' ) ) {
				return new \WP_Error( 'download_error', __( "Can't access CURL" ) );
			}

			$ch = curl_init( $url );

			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

			curl_setopt( $ch, CURLOPT_HEADER, true );

			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );

			curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );

			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

			$url_data = parse_url( $url );

			if ( ! ( empty( $url_data['user'] ) || empty( $url_data['pass'] ) ) ) {

				curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY );

				curl_setopt( $ch, CURLOPT_USERPWD, $url_data['user'] . ':' . $url_data['pass'] );

				$url = $url_data['scheme'] . '://' . $url_data['host'];

				if ( ! empty( $url_data['port'] ) ) {
					$url .= ':' . $url_data['port'];
				}

				$url .= $url_data['path'];
				if ( ! empty( $url_data['query'] ) ) {
					$url .= '?' . $url_data['query'];
				}
				curl_setopt( $ch, CURLOPT_URL, $url );
			}

			$rawdata = curl_exec( $ch );

			$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

			$content_type = curl_getinfo( $ch, CURLINFO_CONTENT_TYPE );

			$header_size = curl_getinfo( $ch, CURLINFO_HEADER_SIZE );

			curl_close( $ch );

			$headers_data = ( $header_size > 0 && trim( $rawdata ) !== '' ) ? substr( $rawdata, 0, $header_size ) : '';

			$headers = self::header_to_array( $headers_data );

			$content_disposition = isset( $headers['content-disposition'] ) ? $headers['content-disposition'] : '';

			if ( 200 !== $http_code ) {
				return new \WP_Error( 'download_error', __( 'Invalid Status Code' ) );
			}
			if ( empty( $rawdata ) ) {
				return new \WP_Error( 'download_error', __( 'File is empty' ) );
			};

			if ( ! file_put_contents( $file, $rawdata ) ) {
				$fp = fopen( $file, 'w' );
				fwrite( $fp, $rawdata );
				fclose( $fp );
			}

			return self::get_filename_from_headers( $url, $file, $content_disposition, $content_type );
		}
		/**
		 * Header to array
		 *
		 * @param string $headers Headers.
		 * @return array
		 */
		public static function header_to_array( $headers = '' ) {

			$data = array();

			if ( empty( $headers ) ) {
				return $data;
			}

			$headers = str_replace( "\r\n", "\n", $headers );

			$headers = preg_replace( '/\n[ \t]/', ' ', $headers );

			$headers = explode( "\n", $headers );

			foreach ( $headers as $header ) {

				if ( empty( trim( $header ) ) ) {
					continue;
				}
				list($key, $value) = explode( ':', $header, 2 );

				if ( empty( trim( $key ) ) || empty( trim( $key ) ) ) {
					continue;
				}

				$value = trim( $value );

				$value = preg_replace( '#(\s+)#i', ' ', $value );

				$data[ $key ] = $value;
			}

			return $data;
		}
		/**
		 * Download by API
		 *
		 * @param string $url URL.
		 * @param string $file File.
		 * @return \WP_Error|string
		 */
		public static function download_by_api( $url = '', $file = '' ) {

			if ( '' === trim( $url ) ) {
				return '';
			}

			$response = wp_safe_remote_get(
				$url,
				array(
					'timeout' => 3000,
					'stream' => true,
					'filename' => $file,
				)
			);

			if ( is_wp_error( $response ) ) {

				return $response;
			}

			if ( 200 != wp_remote_retrieve_response_code( $response ) ) {

				return new \WP_Error( 'http_404', trim( wp_remote_retrieve_response_message( $response ) ) );
			}

			$content_md5 = wp_remote_retrieve_header( $response, 'content-md5' );

			if ( $content_md5 ) {

				$md5_check = verify_file_md5( $file, $content_md5 );

				if ( is_wp_error( $md5_check ) ) {

					return $md5_check;
				}

				unset( $md5_check );
			}
			$content_disposition = wp_remote_retrieve_header( $response, 'content-disposition' );

			$content_type = wp_remote_retrieve_header( $response, 'content-type' );

			return self::get_filename_from_headers( $url, $file, $content_disposition, $content_type );
		}
		/**
		 * Get file from remote
		 *
		 * @param string $url uRL.
		 * @param string $file File.
		 * @param string $content_disposition Content.
		 * @param string $content_type Content type.
		 * @return type
		 */
		public static function get_filename_from_headers( $url, $file, $content_disposition, $content_type ) {

			$filename = self::get_filename_from_content_disposition( $content_disposition );

			$ext = self::get_ext_from_content_type( $content_type );

			$url_data = parse_url( $url );

			$url_path = isset( $url_data['path'] ) ? $url_data['path'] : '';

			$path_info = ( '' !== trim( $url_path ) ) ? pathinfo( $url_path ) : pathinfo( $url );

			$new_ext = isset( $path_info['extension'] ) ? $path_info['extension'] : '';

			$new_filename = isset( $path_info['basename'] ) ? $path_info['basename'] : '';

			if ( '' !== trim( $ext ) && ( '' === trim( $new_ext ) || strtolower( trim( $new_ext ) ) !== strtolower( trim( $ext ) ) ) ) {
				$new_ext = strtolower( trim( $ext ) );
			}
			if ( '' !== trim( $filename ) && ( '' === trim( $new_filename ) || strtolower( trim( $new_filename ) ) !== strtolower( trim( $filename ) ) ) ) {
				$new_filename = $filename;
			}

			if ( '' === trim( $new_ext ) && '' !== trim( $new_filename ) ) {
				$new_ext = pathinfo( $new_filename, PATHINFO_EXTENSION );
			}

			if ( '' !== trim( $new_ext ) ) {

				$temp_ext = pathinfo( $new_filename, PATHINFO_EXTENSION );

				if ( strtolower( trim( $new_ext ) ) !== strtolower( trim( $temp_ext ) ) ) {
					$new_filename = pathinfo( $new_filename, PATHINFO_FILENAME ) . '.' . $new_ext;
				}
			}

			$new_filename = preg_replace( '/[^a-z0-9\_\-\.]/i', '', preg_replace( '#[ -]+#', '-', $new_filename ) );

			if ( '' === trim( $new_filename ) ) {
				$new_filename = pathinfo( $file, PATHINFO_FILENAME );
			}

			return $new_filename;
		}
		/**
		 * Get extension
		 *
		 * @param string $content_type Content type.
		 * @return string
		 */
		public static function get_ext_from_content_type( $content_type = '' ) {

			$content_type = strtolower( trim( $content_type ) );

			if ( '' === $content_type ) {
				return '';
			}

			$ext = '';

			if ( false !== strpos( $content_type, 'text/xml' ) || false !== strpos( $content_type, 'application/xml' ) ) {
				$ext = 'xml';
			} elseif ( false !== strpos( $content_type, 'text/plain' ) ) {
				$ext = 'txt';
			} elseif ( false !== strpos( $content_type, 'text/csv' ) ) {
				$ext = 'csv';
			} elseif ( false !== strpos( $content_type, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ) ) {
				$ext = 'xlsx';
			} elseif ( false !== strpos( $content_type, 'application/vnd.ms-excel' ) ) {
				$ext = 'xls';
			} elseif ( false !== strpos( $content_type, 'application/json' ) || false !== strpos( $content_type, 'json' ) ) {
				$ext = 'json';
			} elseif ( false !== strpos( $content_type, 'application/zip' ) ) {
				$ext = 'zip';
			} elseif ( false !== strpos( $content_type, 'application/vnd.oasis.opendocument.spreadsheet' ) ) {
				$ext = 'ods';
			}

			return $ext;
		}
		/**
		 * Get filename
		 *
		 * @param string $content_disposition  Content.
		 * @return string
		 */
		public static function get_filename_from_content_disposition( $content_disposition = '' ) {

			if ( empty( $content_disposition ) ) {
				return '';
			}

			$regex = '/.*?filename=(?<fn>[^\s]+|\x22[^\x22]+\x22)\x3B?.*$/m';

			$new_file_data = null;

			$original_name = '';

			if ( preg_match( $regex, $content_disposition, $new_file_data ) ) {

				if ( isset( $new_file_data['fn'] ) && ! empty( $new_file_data['fn'] ) ) {
					$wp_filetype = wp_check_filetype( $new_file_data['fn'] );
					if ( isset( $wp_filetype['ext'] ) && ( ! empty( $wp_filetype['ext'] ) ) && isset( $wp_filetype['type'] ) && ( ! empty( $wp_filetype['type'] ) ) ) {
						$original_name = $new_file_data['fn'];
					}
				}
			}

			if ( empty( $original_name ) ) {

				$regex = '/.*filename=([\'\"]?)([^\"]+)\1/';

				if ( preg_match( $regex, $content_disposition, $new_file_data ) ) {

					if ( isset( $new_file_data['2'] ) && ! empty( $new_file_data['2'] ) ) {
						$wp_filetype = wp_check_filetype( $new_file_data['2'] );
						if ( isset( $wp_filetype['ext'] ) && ( ! empty( $wp_filetype['ext'] ) ) && isset( $wp_filetype['type'] ) && ( ! empty( $wp_filetype['type'] ) ) ) {
							$original_name = $new_file_data['2'];
						}
					}
				}
			}

			return $original_name;
		}

		/**
		 * Compress HTML
		 *
		 * @param string $html HTML.
		 */
		public static function sanitize_and_minify_html( $html ) {

			$search = array(
				'/\>[^\S ]+/s', // strip whitespaces after tags, except space.
				'/[^\S ]+\</s', // strip whitespaces before tags, except space.
				'/(\s)+/s', // shorten multiple whitespace sequences.
				'/<!--(.|\s)*?-->/', // Remove HTML comments.
			);

			$replace = array(
				'>',
				'<',
				'\\1',
				'',
			);

			$html = preg_replace( $search, $replace, $html );

			return $html;
		}
	}

}
