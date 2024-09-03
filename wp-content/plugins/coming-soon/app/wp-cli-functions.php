<?php
if ( defined( 'WP_CLI' ) && WP_CLI ) {

	/**
	 * Import a theme from a URL using WP-CLI.
	 *
	 * ## OPTIONS
	 *
	 * <theme-url>
	 * : The URL of the theme to import.
	 *
	 * ## EXAMPLES
	 *
	 *     wp seedprod_import_theme_from_url http://example.com/theme.zip
	 *
	 * @param array $args       Command arguments.
	 * @param array $assoc_args Command associative arguments.
	 */
	function seedprod_lite_import_theme_from_url( $args, $assoc_args ) {
		list( $theme_url ) = $args;

		$nonce = wp_create_nonce( 'seedprod_import_theme_by_url' );
		try {

			$response = wp_remote_head( $theme_url );

			if ( is_wp_error( $response ) ) {
				WP_CLI::error( 'An error occurred while checking the URL: ' . $response->get_error_message() );
			}

			$response_code = wp_remote_retrieve_response_code( $response );

			if ( 200 !== $response_code ) {
				WP_CLI::error( 'The ZIP file does not exist at the provided URL.' );
			}

			$result = seedprod_lite_import_theme_by_url_cli( $theme_url, $nonce );

			if ( true === $result ) {
				WP_CLI::success( 'Theme imported successfully.' );
			} else {
				WP_CLI::error( $result );
			}
		} catch ( Exception $e ) {
			WP_CLI::error( 'An error occurred: ' . $e->getMessage() );
		}

	}
	WP_CLI::add_command( 'seedprod_import_theme_from_url', 'seedprod_lite_import_theme_from_url' );

	/**
	 * Export a theme using WP-CLI.
	 *
	 * @param string $theme_url The URL of the theme to import.
	 * @param string $nonce     The nonce value for security verification.
	 * @return mixed|array|string|array[] Depending on the outcome, may return various data or an error message.
	 */
	function seedprod_lite_import_theme_by_url_cli( $theme_url, $nonce ) {

		if ( ! wp_verify_nonce( $nonce, 'seedprod_import_theme_by_url' ) ) {
			return 'Invalid request. Please provide a valid nonce.';
		}

		$is_ajax_request = false;
		if ( null === $theme_url ) {
			$is_ajax_request = check_ajax_referer( 'seedprod_lite_import_theme_by_url' );
		}

		if ( $is_ajax_request || ! empty( $theme_url ) ) {

			$url   = wp_nonce_url( 'admin.php?page=seedprod_lite_import_theme_by_url', 'seedprod_import_theme_files' );
			$creds = request_filesystem_credentials( $url, '', false, false, null );
			if ( false === $creds ) {
				return array( 'error' => 'Failed to obtain filesystem credentials.' );
			}

			if ( ! WP_Filesystem( $creds ) ) {
				request_filesystem_credentials( $url, '', true, false, null );
				return array( 'error' => 'Failed to initialize filesystem.' );
			}

			$source = isset( $_REQUEST['seedprod_theme_url'] ) ? wp_kses_post( wp_unslash( $_REQUEST['seedprod_theme_url'] ) ) : '';

			if ( ! empty( $theme_url ) ) {
				$source = $theme_url;
			}

			$file_import_url_json = wp_remote_get( $source, array( 'sslverify' => false ) );
			if ( is_wp_error( $file_import_url_json ) ) {
				$error_code    = wp_remote_retrieve_response_code( $file_import_url_json );
				$error_message = wp_remote_retrieve_response_message( $file_import_url_json );
				return array( 'error' => $error_message );
			}
			preg_match( '/zip/', $file_import_url_json['headers']['content-type'], $match );
			if ( is_array( $match ) && count( $match ) <= 0 ) {
				return array( 'error' => 'Invalid file format. Please upload a .zip file.' );
			}

			if ( '' !== $source && $file_import_url_json['body'] ) {

				$url_data = pathinfo( $source );

				$filename = $url_data['basename'];
				$type     = $url_data['extension'];

				$filename = substr( $filename, 0, strpos( $filename, '.zip' ) + 4 );

				$name           = explode( '.', $filename );
				$accepted_types = array( 'application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed' );
				foreach ( $accepted_types as $mime_type ) {
					if ( $mime_type === $type ) {
						$okay = true;
						break;
					}
				}

				$continue = strtolower( $name[1] ) === 'zip' ? true : false;
				if ( ! $continue ) {
					return array( 'error' => 'The file you are trying to upload is not a .zip file.' );
				}

				$filename_import = 'seedprod-themes-imports';

				global $wp_filesystem;
				$upload_dir = wp_upload_dir();
				$path       = trailingslashit( $upload_dir['basedir'] );
				$webpath    = trailingslashit( $upload_dir['baseurl'] );

				$filenoext = basename( $filename_import, '.zip' );  // absolute path to the directory where zipper.php is in (lowercase).
				$filenoext = basename( $filenoext, '.ZIP' );  // absolute path to the directory where zipper.php is in (when uppercase).

				$targetdir    = $path . $filenoext; // target directory.
				$targetzip    = $path . $filename; // target zip file.
				$webtargetdir = $webpath . $filenoext;

				if ( is_dir( $targetdir ) ) {
					recursive_rmdir( $targetdir );
				}
				mkdir( $targetdir, 0777 );
				if ( file_put_contents( $targetzip, $file_import_url_json['body'] ) ) {

					$zip = new ZipArchive();
					$x   = $zip->open( $targetzip );
					if ( true === $x ) {
						$zip->extractTo( $targetdir );
						$zip->close();

						unlink( $targetzip );
					}
					$theme_json_data     = $targetdir . '/export_theme.json';
					$web_theme_json_data = $webtargetdir . '/export_theme.json';

					if ( file_exists( $theme_json_data ) ) {
						$file_theme_json = wp_remote_get( $web_theme_json_data, array( 'sslverify' => false ) );
						if ( is_wp_error( $file_theme_json ) ) {
							$error_code    = wp_remote_retrieve_response_code( $file_theme_json );
							$error_message = wp_remote_retrieve_response_message( $file_theme_json );
							return array( 'error' => $error_message );
						}
						$data = json_decode( $file_theme_json['body'] );
						if ( ! empty( $data->type ) && 'theme-builder' !== $data->type ) {
							return array( 'error' => 'This does not appear to be a SeedProd theme.' );
						}
						seedprod_lite_theme_import_json( $data );
						// remove the json file for security.
						wp_delete_file( $theme_json_data );

						return true;
					}
				} else {
					return array( 'error' => 'There was a problem with the upload. Please try again.' );
				}
			} else {
				return array( 'error' => 'There was a problem with the upload. Please try again.' );
			}
		}
	}



	/**
	 * Import a landing page from a URL using WP-CLI.
	 *
	 * ## OPTIONS
	 *
	 * <landing-page-url>
	 * : The URL of the landing page to import.
	 *
	 * ## EXAMPLES
	 *
	 *     wp seedprod_import_landing_page_from_url http://example.com/landing-page.zip
	 *
	 * @param array $args       Command arguments.
	 * @param array $assoc_args Command associative arguments.
	 */
	function seedprod_lite_import_landing_page_from_url( $args, $assoc_args ) {
		list( $theme_url ) = $args;

		$nonce = wp_create_nonce( 'seedprod_lite_import_landing_pages' );
		try {

			$response = wp_remote_head( $theme_url );

			if ( is_wp_error( $response ) ) {
				WP_CLI::error( 'An error occurred while checking the URL: ' . $response->get_error_message() );
			}

			$response_code = wp_remote_retrieve_response_code( $response );

			if ( 200 !== $response_code ) {
				WP_CLI::error( 'The ZIP file does not exist at the provided URL.' );
			}

			$imported_pages = seedprod_lite_import_landing_page_cli( $theme_url, $nonce );

			if ( is_wp_error( $imported_pages ) ) {
				WP_CLI::error( 'Failed to import landing pages: ' . $imported_pages->get_error_message() );
			}

			// Output imported page IDs and titles.
			foreach ( $imported_pages as $page ) {
				WP_CLI::success( 'Imported Page ID: ' . $page['id'] );
			}

			//WP_CLI::success( 'Landing pages imported successfully.' );

		} catch ( Exception $e ) {
			WP_CLI::error( 'An error occurred: ' . $e->getMessage() );
		}

	}
	WP_CLI::add_command( 'seedprod_import_landing_page_from_url', 'seedprod_lite_import_landing_page_from_url' );

	/**
	 * Export a landing page using WP-CLI.
	 *
	 * @param string $theme_url The URL of the theme to import.
	 * @param string $nonce     The nonce value for security verification.
	 * @return mixed|array|string|array[] Depending on the outcome, may return various data or an error message.
	 */
	function seedprod_lite_import_landing_page_cli( $theme_url, $nonce ) {

		if ( ! wp_verify_nonce( $nonce, 'seedprod_lite_import_landing_pages' ) ) {
			return 'Invalid request. Please provide a valid nonce.';
		}

		$is_ajax_request = false;
		if ( null === $theme_url ) {
			$is_ajax_request = check_ajax_referer( 'seedprod_lite_import_landing_pages' );
		}

		if ( $is_ajax_request || ! empty( $theme_url ) ) {

			$url   = wp_nonce_url( 'admin.php?page=seedprod_lite_import_landing_pages', 'seedprod_import_landing_pages' );
			$creds = request_filesystem_credentials( $url, '', false, false, null );
			if ( false === $creds ) {
				return array( 'error' => 'Failed to obtain filesystem credentials.' );
			}

			if ( ! WP_Filesystem( $creds ) ) {
				request_filesystem_credentials( $url, '', true, false, null );
				return array( 'error' => 'Failed to initialize filesystem.' );
			}

			$source = isset( $_REQUEST['seedprod_landing_url'] ) ? wp_kses_post( wp_unslash( $_REQUEST['seedprod_landing_url'] ) ) : '';

			if ( ! empty( $theme_url ) ) {
				$source = $theme_url;
			}

			$file_import_url_json = wp_remote_get( $source, array( 'sslverify' => false ) );
			if ( is_wp_error( $file_import_url_json ) ) {
				$error_code    = wp_remote_retrieve_response_code( $file_import_url_json );
				$error_message = wp_remote_retrieve_response_message( $file_import_url_json );
				return array( 'error' => $error_message );
			}

			preg_match( '/zip/', $file_import_url_json['headers']['content-type'], $match );
			if ( is_array( $match ) && count( $match ) <= 0 ) {
				return array( 'error' => 'Invalid file format. Please upload a .zip file.' );
			}

			if ( '' !== $source && $file_import_url_json['body'] ) {
				$url_data = pathinfo( $source );

				$filename = $url_data['basename'];
				$type     = $url_data['extension'];

				$filename = substr( $filename, 0, strpos( $filename, '.zip' ) + 4 );

				$name           = explode( '.', $filename );
				$accepted_types = array( 'application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed' );
				foreach ( $accepted_types as $mime_type ) {
					if ( $mime_type === $type ) {
						$okay = true;
						break;
					}
				}

				$continue = strtolower( $name[1] ) === 'zip' ? true : false;
				if ( ! $continue ) {
					return array( 'error' => 'The file you are trying to upload is not a .zip file.' );
				}

				$filename_import = 'seedprod-themes-imports';

				global $wp_filesystem;
				$upload_dir   = wp_upload_dir();
				$path         = trailingslashit( $upload_dir['basedir'] );
				$path_baseurl = trailingslashit( $upload_dir['baseurl'] );

				$filenoext = basename( $filename_import, '.zip' ); // absolute path to the directory where zipper.php is in (lowercase).
				$filenoext = basename( $filenoext, '.ZIP' ); // absolute path to the directory where zipper.php is in (when uppercase).

				$targetdir  = $path . $filenoext; // target directory.
				$targetzip  = $path . $filename; // target zip file.
				$target_url = $path_baseurl . $filenoext;

				if ( is_dir( $targetdir ) ) {
					recursive_rmdir( $targetdir );
				}
				mkdir( $targetdir, 0777 );

				if ( file_put_contents( $targetzip, $file_import_url_json['body'] ) ) {
					$zip = new ZipArchive();
					$x   = $zip->open( $targetzip );
					if ( true === $x ) {
						$zip->extractTo( $targetdir );
						$zip->close();

						unlink( $targetzip );
					}

					$theme_json_data     = $targetdir . '/export_page.json';
					$theme_json_data_url = $target_url . '/export_page.json';

					if ( file_exists( $theme_json_data ) ) {
						$file_theme_json = wp_remote_get( $theme_json_data_url, array( 'sslverify' => false ) );
						if ( is_wp_error( $file_theme_json ) ) {
							$error_code    = wp_remote_retrieve_response_code( $file_theme_json );
							$error_message = wp_remote_retrieve_response_message( $file_theme_json );
							return array( 'error' => $error_message );
						}
						$data = json_decode( $file_theme_json['body'] );
						if ( ! empty( $data->type ) && 'landing-page' !== $data->type ) {
							return array( 'error' => 'This does not appear to be a SeedProd landing page.' );
						}
						$imported_pages = seedprod_lite_landing_import_json( $data );
						// remove the json file for security.
						wp_delete_file( $theme_json_data );

						return $imported_pages;
					}
				} else {
					return array( 'error' => 'There was a problem with the upload. Please try again.' );
				}
			} else {
				return array( 'error' => 'There was a problem with the upload. Please try again.' );
			}
		}
	}


	/**
	 * Activate a license using WP-CLI.
	 *
	 * ## OPTIONS
	 *
	 * <license-key>
	 * : The license key to activate.
	 *
	 * ## EXAMPLES
	 *
	 *     wp seedprod_activate_license ABCDEFG1234567
	 *
	 * @param array $args Command arguments.
	 */
	function seedprod_lite_activate_license( $args ) {
		list( $license_key ) = $args;

		// Generate a nonce.
		$nonce = wp_create_nonce( 'seedprod_nonce' );

		try {
			// Call the function to activate the license with nonce.
			$result = seedprod_lite_save_api_key_cli( $license_key, $nonce );

			if ( 'true' === $result['status'] ) {
				WP_CLI::success( 'License activated successfully.' );
			} else {
				WP_CLI::error( 'Failed to activate license: ' . $result['msg'] );
			}
		} catch ( Exception $e ) {
			WP_CLI::error( 'An error occurred: ' . $e->getMessage() );
		}
	}
	WP_CLI::add_command( 'seedprod_activate_license', 'seedprod_lite_activate_license' );

	/**
	 * Save API Key
	 *
	 * @param string $api_key API key for activation.
	 * @param string $nonce The nonce for verification.
	 * @return array Return result array.
	 */
	function seedprod_lite_save_api_key_cli( $api_key = null, $nonce = null ) {
		// Verify the nonce.
		if ( ! wp_verify_nonce( $nonce, 'seedprod_nonce' ) ) {
			return array(
				'status' => 'false',
				'msg'    => 'Invalid nonce. Please try again.',
			);
		}

		if ( empty( $api_key ) ) {
			$api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : null;
		}

		if ( defined( 'SEEDPROD_LOCAL_JS' ) ) {
			$slug = 'seedprod-coming-soon-pro-5/seedprod-coming-soon-pro-5.php';
		} else {
			$slug = SEEDPROD_SLUG;
		}

		// Get token and generate one if one does not exist.
		$token = get_option( 'seedprod_token' );
		if ( empty( $token ) ) {
			$token = strtolower( wp_generate_password( 32, false, false ) );
			update_option( 'seedprod_token', $token );
		}

		// Validate the API key.
		$data = array(
			'action'            => 'info',
			'license_key'       => $api_key,
			'token'             => $token,
			'wp_version'        => get_bloginfo( 'version' ),
			'domain'            => home_url(),
			'installed_version' => SEEDPROD_VERSION,
			'slug'              => $slug,
		);

		if ( empty( $data['license_key'] ) ) {
			return array(
				'status' => 'false',
				'msg'    => __( 'License Key is required.', 'coming-soon' ),
			);
		}

		$headers = array(
			'Accept' => 'application/json',
		);

		$url      = SEEDPROD_API_URL . 'update';
		$response = wp_remote_post(
			$url,
			array(
				'body'    => $data,
				'headers' => $headers,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'status' => 'false',
				'msg'    => $response->get_error_message(),
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $status_code ) {
			return array(
				'status' => 'false',
				'msg'    => wp_remote_retrieve_response_message( $response ),
			);
		}

		$body = wp_remote_retrieve_body( $response );

		if ( ! empty( $body ) ) {
			$body = json_decode( $body );
		}

		if ( ! empty( $body->valid ) && true === $body->valid ) {
			// Store API key.
			update_option( 'seedprod_user_id', $body->user_id );
			update_option( 'seedprod_api_token', $body->api_token );
			update_option( 'seedprod_api_key', $data['license_key'] );
			update_option( 'seedprod_api_message', $body->message );
			update_option( 'seedprod_license_name', $body->license_name );
			update_option( 'seedprod_a', true );
			update_option( 'seedprod_per', $body->per );

			return array(
				'status'       => 'true',
				// Translators: %s is the license name.
				'license_name' => sprintf( __( 'You currently have the <strong>%s</strong> license.', 'coming-soon' ), $body->license_name ),
				'msg'          => $body->message,
				'body'         => $body,
			);
		} else {
			$api_msg = __( 'Invalid License Key.', 'coming-soon' );
			if ( 'Unauthenticated.' !== $body->message ) {
				$api_msg = $body->message;
			}
			update_option( 'seedprod_license_name', '' );
			update_option( 'seedprod_api_token', '' );
			update_option( 'seedprod_api_key', '' );
			update_option( 'seedprod_api_message', $api_msg );
			update_option( 'seedprod_a', false );
			update_option( 'seedprod_per', '' );

			return array(
				'status' => 'false',
				'msg'    => $api_msg,
				'body'   => $body,
			);
		}
	}


	/**
	 * Enable or disable a theme using WP-CLI.
	 *
	 * ## OPTIONS
	 *
	 * <true|false>
	 * : Whether to enable or disable the theme.
	 *
	 * ## EXAMPLES
	 *
	 *     wp seedprod_enable_theme true
	 *
	 * @param array $args Command arguments.
	 */
	function seedprod_lite_enable_theme( $args ) {
		list( $enable ) = $args;

		$enable = filter_var( $enable, FILTER_VALIDATE_BOOLEAN );

		// Generate a nonce.
		$nonce = wp_create_nonce( 'seedprod_lite_update_seedprod_theme_enabled' );

		try {
			// Call the function to enable/disable the theme with nonce.
			$result = seedprod_lite_update_seedprod_theme_enabled_cli( $enable, $nonce );

			if ( true === $result || 'disabled' === $result ) {
				WP_CLI::success( 'Theme ' . ( $enable ? 'enabled' : 'disabled' ) . ' successfully.' );
			} else {
				WP_CLI::error( 'Failed to ' . ( $enable ? 'enable' : 'disable' ) . ' theme.' );
			}
		} catch ( Exception $e ) {
			WP_CLI::error( 'An error occurred: ' . $e->getMessage() );
		}
	}
	WP_CLI::add_command( 'seedprod_enable_theme', 'seedprod_lite_enable_theme' );

	/**
	 * Enable or disable the theme.
	 *
	 * @param bool   $enable Whether to enable or disable the theme.
	 * @param string $nonce The nonce for verification.
	 * @return bool True on success, false on failure.
	 */
	function seedprod_lite_update_seedprod_theme_enabled_cli( $enable = null, $nonce = null ) {
		// Verify the nonce.
		if ( ! wp_verify_nonce( $nonce, 'seedprod_lite_update_seedprod_theme_enabled' ) ) {
			return false;
		}

		$seedprod_theme_enabled_update = false;
		if ( true === $enable ) {
			$seedprod_theme_enabled_update = true;
		}

		update_option( 'seedprod_theme_enabled', $seedprod_theme_enabled_update );

		if ( true === $seedprod_theme_enabled_update ) {
			$result = seedprod_lite_create_blog_and_home_for_theme_cli( $nonce );
			return true;
		} else {
			return 'disabled';
		}
	}

	/**
	 * Create blog and home pages if they don't exist, and set them as the front and posts pages.
	 *
	 * @param string $nonce The nonce for verification.
	 * @return bool True on success, false on failure.
	 */
	function seedprod_lite_create_blog_and_home_for_theme_cli( $nonce ) {

			// Verify the nonce.
		if ( ! wp_verify_nonce( $nonce, 'seedprod_lite_update_seedprod_theme_enabled' ) ) {
			return false;
		}

			// create front page and blog page.
			$posts_page_id = get_page_by_path( 'blog' );
			// Check if the page already exists.
		if ( empty( $posts_page_id ) ) {
			$posts_page_id = wp_insert_post(
				array(
					'comment_status' => 'close',
					'ping_status'    => 'close',
					'post_author'    => 1,
					'post_title'     => 'Blog',
					'post_name'      => 'blog',
					'post_status'    => 'publish',
					'post_content'   => '',
					'post_type'      => 'page',
				)
			);
		} else {
			$posts_page_id = $posts_page_id->ID;
		}

			$front_page_id = get_page_by_path( 'home' );
			// Check if the page already exists.
		if ( empty( $front_page_id ) ) {
			$front_page_id = wp_insert_post(
				array(
					'comment_status' => 'close',
					'ping_status'    => 'close',
					'post_author'    => 1,
					'post_title'     => 'Home',
					'post_name'      => 'home',
					'post_status'    => 'publish',
					'post_content'   => '',
					'post_type'      => 'page',
				)
			);
		} else {
			$front_page_id = $front_page_id->ID;
		}

			update_option( 'show_on_front', 'page' );
			update_option( 'page_for_posts', $posts_page_id );
			update_option( 'page_on_front', $front_page_id );

			return true;
	}

	/**
	 * Enable or disable the coming soon page using WP-CLI.
	 *
	 * ## OPTIONS
	 *
	 * <true|false>
	 * : Whether to enable or disable the coming soon page.
	 *
	 * [--page_id=<id>]
	 * : The ID of the page to use for coming soon.
	 *
	 * ## EXAMPLES
	 *
	 * wp seedprod_enable_coming_soon_page true --page_id=123
	 *
	 * @param array $args Command arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	function seedprod_lite_enable_coming_soon_page( $args, $assoc_args ) {
		list( $enable ) = $args;
		$enable         = filter_var( $enable, FILTER_VALIDATE_BOOLEAN );

		$page_id = isset( $assoc_args['page_id'] ) ? intval( $assoc_args['page_id'] ) : null;

		// Generate a nonce.
		$nonce = wp_create_nonce( 'seedprod_enable_coming_soon_page' );

		try {
			// Call the function to enable/disable the coming soon page with nonce.
			$result = seedprod_enable_coming_soon_page_function_cli( $enable, $nonce, $page_id );

			if ( false !== $result ) {
				$action = $enable ? 'enabled' : 'disabled';
				WP_CLI::success( "Coming soon page $action successfully." );
			} else {
				$action = $enable ? 'enable' : 'disable';
				WP_CLI::error( "Failed to $action coming soon page." );
			}
		} catch ( Exception $e ) {
			WP_CLI::error( 'An error occurred: ' . $e->getMessage() );
		}
	}
	WP_CLI::add_command( 'seedprod_enable_coming_soon_page', 'seedprod_lite_enable_coming_soon_page' );

	/**
	 * Enable or disable the coming soon page.
	 *
	 * @param bool     $enable Whether to enable or disable the coming soon page.
	 * @param string   $nonce The nonce for verification.
	 * @param int|null $page_id The ID of the page to use for coming soon.
	 * @return bool True on success, false on failure.
	 */
	function seedprod_enable_coming_soon_page_function_cli( $enable, $nonce, $page_id = null ) {
		// Verify the nonce.
		if ( ! wp_verify_nonce( $nonce, 'seedprod_enable_coming_soon_page' ) ) {
			return false;
		}

		$settings = get_option( 'seedprod_settings', array() );

		if ( ! is_array( $settings ) ) {
			// If settings are not an array, initialize it as an array.
			$settings = array();
		}

		// Update the settings with the new coming soon page status.
		$settings['enable_coming_soon_mode'] = $enable ? true : false;

		if ( $page_id ) {
			update_option( 'seedprod_coming_soon_page_id', $page_id );
		}

		// Update the option in the database.
		update_option( 'seedprod_settings', wp_json_encode( $settings ) );

		return true;
	}
}
