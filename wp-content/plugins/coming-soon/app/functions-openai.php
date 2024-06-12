<?php
/**
 * Make Open AI call to fetch result
 */
function seedprod_lite_call_open_ai() {
	if ( check_ajax_referer( 'seedprod_nonce' ) ) {

		if ( ! current_user_can( apply_filters( 'seedprod_lpage_capability', 'edit_others_posts' ) ) ) {
			wp_send_json_error();
		}

		$seedprod_api_key = seedprod_lite_get_api_key();
		$api_key          = $seedprod_api_key;
		$token            = get_option( 'seedprod_token' );
		$api_token        = get_option( 'seedprod_api_token' );

		$prompt = isset( $_REQUEST['prompt'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['prompt'] ) ) : '';
		$data   = array(
			'prompt'    => $prompt,
			'api_token' => $api_token,
			'api_key'   => $api_key,
			'token'     => $token,
		);

		$headers = array(
			'Accept'        => 'application/json',
			'Authorization' => 'Bearer ' . $api_token,
		);

		$url = SEEDPROD_WEB_API_URL . 'v4/openaigenerate';

		try {

			$response = wp_remote_post(
				$url,
				array(
					'body'      => wp_json_encode( $data ),
					'headers'   => $headers,
					'sslverify' => false,
					'timeout'   => 60,
				)
			);

			if ( is_wp_error( $response ) ) {

				$curl_error = $response->get_error_code();
				if ( 'http_request_failed' === $curl_error ) {
					$result = wp_json_encode( array( 'error' => __( 'cURL error:', 'coming-soon' ) . $response->get_error_message() ) );
				} else {
					$result = wp_json_encode( array( 'error' => $response->get_error_message() ) );
				}
			} else {
				$http_status = wp_remote_retrieve_response_code( $response );
				if ( 200 === $http_status ) {
					$response_body = wp_remote_retrieve_body( $response );
					$result_data   = json_decode( $response_body, true );

					if ( null === $result_data && json_last_error() !== JSON_ERROR_NONE ) {
						$result = wp_json_encode( array( 'error' => __( 'Invalid JSON response', 'coming-soon' ) ) );
					} else {
						$result = wp_json_encode( $result_data );
					}
				} else {
					// Request timeout error.
					$result = wp_json_encode( array( 'error' => __( 'Server error or request timeout. Try again later.', 'coming-soon' ) ) );
				}
			}
		} catch ( Exception $e ) {
			$result = wp_json_encode( array( 'error' => __( 'Server error or request timeout. Try again later.', 'coming-soon' ) ) );
		}

		echo wp_kses_post( $result );

		exit;

	}
}

/**
 * Make Open AI call to fetch result with instruction for model.
 */
function seedprod_lite_call_open_ai_edit() {
	if ( check_ajax_referer( 'seedprod_nonce' ) ) {

		if ( ! current_user_can( apply_filters( 'seedprod_lpage_capability', 'edit_others_posts' ) ) ) {
			wp_send_json_error();
		}

		$seedprod_api_key = seedprod_lite_get_api_key();
		$api_key          = $seedprod_api_key;
		$token            = get_option( 'seedprod_token' );
		$api_token        = get_option( 'seedprod_api_token' );

		$prompt      = isset( $_REQUEST['prompt'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['prompt'] ) ) : '';
		$instruction = isset( $_REQUEST['instruction'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['instruction'] ) ) : '';
		$data        = array(
			'prompt'      => $prompt,
			'instruction' => $instruction,
			'api_token'   => $api_token,
			'api_key'     => $api_key,
			'token'       => $token,
		);

		$headers = array(
			'Accept'        => 'application/json',
			'Authorization' => 'Bearer ' . $api_token,
		);

		$url = SEEDPROD_WEB_API_URL . 'v4/openaiedittext';

		try {
				$response = wp_remote_post(
					$url,
					array(
						'body'      => wp_json_encode( $data ),
						'headers'   => $headers,
						'sslverify' => false,
						'timeout'   => 60,
					)
				);

			if ( is_wp_error( $response ) ) {
				$curl_error = $response->get_error_code();
				if ( 'http_request_failed' === $curl_error ) {
					$result = wp_json_encode( array( 'error' => __( 'cURL error:', 'coming-soon' ) . $response->get_error_message() ) );
				} else {
					$result = wp_json_encode( array( 'error' => $response->get_error_message() ) );
				}
			} else {

				$http_status = wp_remote_retrieve_response_code( $response );

				if ( 200 === $http_status ) {
					$response_body = wp_remote_retrieve_body( $response );
					$result_data   = json_decode( $response_body, true );
					$result        = wp_json_encode( $result_data );
				} else {
					// Request timeout error.
					$result = wp_json_encode( array( 'error' => __( 'Server error or request timeout. Try again later.', 'coming-soon' ) ) );
				}
			}
		} catch ( Exception $e ) {
			$result = wp_json_encode( array( 'error' => __( 'Server error or request timeout. Try again later.', 'coming-soon' ) ) );
		}

		echo wp_kses_post( $result );

		exit;

	}
}



/**
 * Make Open AI call to fetch images result from prompt
 */
function seedprod_lite_generate_image_open_ai() {
	if ( check_ajax_referer( 'seedprod_nonce' ) ) {

		if ( ! current_user_can( apply_filters( 'seedprod_lpage_capability', 'edit_others_posts' ) ) ) {
			wp_send_json_error();
		}

		$seedprod_api_key = seedprod_lite_get_api_key();
		$api_key          = $seedprod_api_key;
		$token            = get_option( 'seedprod_token' );
		$api_token        = get_option( 'seedprod_api_token' );

		$prompt = isset( $_REQUEST['prompt'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['prompt'] ) ) : '';

		$headers = array(
			'Accept'        => 'application/json',
			'Authorization' => 'Bearer ' . $api_token,
		);

		$data = array(
			'prompt'    => $prompt,
			'api_token' => $api_token,
			'api_key'   => $api_key,
			'token'     => $token,
		);

		$url = SEEDPROD_WEB_API_URL . 'v4/openaiimagegenerate';

		try {
			$response = wp_remote_post(
				$url,
				array(
					'body'      => wp_json_encode( $data ),
					'headers'   => $headers,
					'sslverify' => false,
					'timeout'   => 120,
				)
			);

			if ( is_wp_error( $response ) ) {
				$curl_error = $response->get_error_code();
				if ( 'http_request_failed' === $curl_error ) {
					echo wp_json_encode( array( 'error' => __( 'cURL error:', 'coming-soon' ) . $response->get_error_message() ) );
				} else {
					echo wp_json_encode( array( 'error' => $response->get_error_message() ) );
				}
			} else {
				$http_status = wp_remote_retrieve_response_code( $response );
				if ( 200 === $http_status ) {
					$response_body = wp_remote_retrieve_body( $response );
					$result_data   = json_decode( $response_body, true );

					if ( null === $result_data && json_last_error() !== JSON_ERROR_NONE ) {
						echo wp_json_encode( array( 'error' => __( 'Invalid JSON response', 'coming-soon' ) ) );
					} else {
						echo wp_json_encode( $result_data );
					}
				} else {
					echo wp_json_encode( array( 'error' => __( 'Server error or request timeout. Try again later.', 'coming-soon' ) ) );
				}
			}
		} catch ( Exception $e ) {
			echo wp_json_encode( array( 'error' => __( 'Server error or request timeout. Try again later.', 'coming-soon' ) ) );
		}

		exit;

	}
}


/**
 * Make Open AI call to fetch images variations
 */
function seedprod_lite_generate_image_open_ai_variations() {
	if ( check_ajax_referer( 'seedprod_nonce' ) ) {

		if ( ! current_user_can( apply_filters( 'seedprod_lpage_capability', 'edit_others_posts' ) ) ) {
			wp_send_json_error();
		}

		$image_url = isset( $_REQUEST['image'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['image'] ) ) : '';
		// Convert the image URL to a local file path.
		$upload_dir      = wp_upload_dir();
		$upload_base_url = $upload_dir['baseurl'];
		$image_path      = str_replace( $upload_base_url, $upload_dir['basedir'], $image_url );

		$seedprod_api_key = seedprod_lite_get_api_key();
		$api_key          = $seedprod_api_key;
		$token            = get_option( 'seedprod_token' );
		$api_token        = get_option( 'seedprod_api_token' );

		if ( file_exists( $image_path ) ) {

			$headers_array = array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_token,
			);

			$data = array(
				'image'     => base64_encode( file_get_contents( $image_path ) ), // phpcs:ignore
				'api_token' => $api_token,
				'api_key'   => $api_key,
				'token'     => $token,
			);

			$url = SEEDPROD_WEB_API_URL . 'v4/openaiimagevariationsgenerate';

			try {
				$response = wp_remote_post(
					$url,
					array(
						'body'      => wp_json_encode( $data ),
						'headers'   => $headers_array,
						'sslverify' => false,
						'timeout'   => 120,
					)
				);

				if ( is_wp_error( $response ) ) {

					$curl_error = $response->get_error_code();
					if ( 'http_request_failed' === $curl_error ) {
						echo wp_json_encode( array( 'error' => __( 'cURL error:', 'coming-soon' ) . $response->get_error_message() ) );
					} else {
						echo wp_json_encode( array( 'error' => $response->get_error_message() ) );
					}
				} else {

					$http_status = wp_remote_retrieve_response_code( $response );
					if ( 200 === $http_status ) {
						$response_body = wp_remote_retrieve_body( $response );
						$result_data   = json_decode( $response_body, true );

						if ( null === $result_data && json_last_error() !== JSON_ERROR_NONE ) {
							echo wp_json_encode( array( 'error' => __( 'Invalid JSON response', 'coming-soon' ) ) );
						} else {
							echo wp_json_encode( $result_data );
						}
					} else {
						echo wp_json_encode( array( 'error' => __( 'Server error or request timeout. Try again later.', 'coming-soon' ) ) );
					}
				}
			} catch ( Exception $e ) {
				echo wp_json_encode( array( 'error' => __( 'Server error or request timeout. Try again later.', 'coming-soon' ) ) );
			}
		}

		exit;

	}
}



/**
 * Make Open AI call to get edit images data
 */
function seedprod_lite_generate_image_open_ai_edit_image() {

	if ( check_ajax_referer( 'seedprod_nonce' ) ) {

		if ( ! current_user_can( apply_filters( 'seedprod_lpage_capability', 'edit_others_posts' ) ) ) {
			wp_send_json_error();
		}

		$post_data = file_get_contents( 'php://input' );

		$data = json_decode( $post_data, true );

		$mask   = isset( $data['edit_image'] ) ? sanitize_text_field( wp_unslash( $data['edit_image'] ) ) : '';
		$prompt = isset( $data['prompt'] ) ? sanitize_text_field( wp_unslash( $data['prompt'] ) ) : '';

		$seedprod_api_key = seedprod_lite_get_api_key();
		$api_key          = $seedprod_api_key;
		$token            = get_option( 'seedprod_token' );
		$api_token        = get_option( 'seedprod_api_token' );

		$upload_dir        = wp_upload_dir();
		$edited_image_path = $upload_dir['path'] . '/edited-image.png';

		$image_data = base64_decode( preg_replace( '#^data:image/\w+;base64,#i', '', $mask ) ); // phpcs:ignore
		file_put_contents( $edited_image_path, $image_data );// phpcs:ignore

		$image_url = $data['image'];

		// Convert the image URL to a local file path.
		$upload_base_url = $upload_dir['baseurl'];
		$image_path      = str_replace( $upload_base_url, $upload_dir['basedir'], $image_url );

		if ( file_exists( $image_path ) ) {

			// Load the source PNG image.
			$source_image = imagecreatefrompng( $image_path );

			// Create a blank image in the 'RGBA' format.
			$rgba_image = imagecreatetruecolor( imagesx( $source_image ), imagesy( $source_image ) );

			// Create a transparent background.
			$transparent_color = imagecolorallocatealpha( $rgba_image, 0, 0, 0, 127 );
			imagefill( $rgba_image, 0, 0, $transparent_color );
			imagesavealpha( $rgba_image, true );

			// Merge the source PNG image into the 'RGBA' image.
			imagecopy( $rgba_image, $source_image, 0, 0, 0, 0, imagesx( $source_image ), imagesy( $source_image ) );

			// Save the 'RGBA' image as a temporary PNG file.
			$temp_image_path = $upload_dir['path'] . '/temp.png';
			imagepng( $rgba_image, $temp_image_path );

			// Clean up resources.
			imagedestroy( $source_image );
			imagedestroy( $rgba_image );

			$masked_image = imagecreatefrompng( $edited_image_path );

			// Create a blank image in the 'RGBA' format.
			$rgba_masked_image = imagecreatetruecolor( imagesx( $masked_image ), imagesy( $masked_image ) );

			// Create a transparent background.
			$transparent_color = imagecolorallocatealpha( $rgba_masked_image, 0, 0, 0, 127 );
			imagefill( $rgba_masked_image, 0, 0, $transparent_color );
			imagesavealpha( $rgba_masked_image, true );

			// Merge the source masked PNG image into the 'RGBA' image.
			imagecopy( $rgba_masked_image, $masked_image, 0, 0, 0, 0, imagesx( $masked_image ), imagesy( $masked_image ) );

			// Save the 'RGBA' masked image as a temporary PNG file.
			$masked_image_path = $upload_dir['path'] . '/masked_temp.png';
			imagepng( $rgba_masked_image, $masked_image_path );

			// Clean up resources.
			imagedestroy( $masked_image );
			imagedestroy( $rgba_masked_image );

			// Prepare the image for sending.
			$file = curl_file_create( $temp_image_path ); // phpcs:ignore
			$mask_file = curl_file_create( $masked_image_path );// phpcs:ignore

			// edit image code.
			$headers_array = array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_token,
			);

			$data = array(
				'image'     => base64_encode( file_get_contents( $image_path ) ),// phpcs:ignore
				'mask'      => base64_encode( file_get_contents( $masked_image_path ) ),// phpcs:ignore
				'prompt'    => $prompt,
				'api_token' => $api_token,
				'api_key'   => $api_key,
				'token'     => $token,
			);

			$url = SEEDPROD_WEB_API_URL . 'v4/openaieditimagegenerate';

			try {
				$response = wp_remote_post(
					$url,
					array(
						'body'      => wp_json_encode( $data ),
						'headers'   => $headers_array,
						'sslverify' => false,
						'timeout'   => 120,
					)
				);

				if ( is_wp_error( $response ) ) {
					$curl_error = $response->get_error_code();
					if ( 'http_request_failed' === $curl_error ) {
						echo wp_json_encode( array( 'error' => __( 'cURL error:', 'coming-soon' ) . $response->get_error_message() ) );
					} else {
						echo wp_json_encode( array( 'error' => $response->get_error_message() ) );
					}
				} else {

					$http_status = wp_remote_retrieve_response_code( $response );
					if ( 200 === $http_status ) {
						$response_body = wp_remote_retrieve_body( $response );
						$result_data   = json_decode( $response_body, true );

						if ( null === $result_data && json_last_error() !== JSON_ERROR_NONE ) {
							echo wp_json_encode( array( 'error' => __( 'Invalid JSON response', 'coming-soon' ) ) );
						} else {
							echo wp_json_encode( $result_data );
						}
					} else {
						echo wp_json_encode( array( 'error' => __( 'Server error or request timeout. Try again later.', 'coming-soon' ) ) );
					}
				}
			} catch ( Exception $e ) {
				echo wp_json_encode( array( 'error' => __( 'Server error or request timeout. Try again later.', 'coming-soon' ) ) );
			}

			exit;

		}

		exit;

	}
}

/**
 * Make Open AI call to fetch user credits available.
 */
function seedprod_lite_call_ai_credits() {
	if ( check_ajax_referer( 'seedprod_nonce' ) ) {

		if ( ! current_user_can( apply_filters( 'seedprod_lpage_capability', 'edit_others_posts' ) ) ) {
			wp_send_json_error();
		}

		$seedprod_api_key = seedprod_lite_get_api_key();
		$api_key          = $seedprod_api_key;
		$token            = get_option( 'seedprod_token' );
		$api_token        = get_option( 'seedprod_api_token' );

		$data = array(
			'api_token' => $api_token,
			'api_key'   => $api_key,
			'token'     => $token,
		);

		$headers = array(
			'Accept'        => 'application/json',
			'Authorization' => 'Bearer ' . $api_token,
		);

		$url = SEEDPROD_WEB_API_URL . 'v4/openaicredits';

		try {

			$response = wp_remote_post(
				$url,
				array(
					'body'      => wp_json_encode( $data ),
					'headers'   => $headers,
					'sslverify' => false,
					'timeout'   => 60,
				)
			);

			if ( is_wp_error( $response ) ) {

				$curl_error = $response->get_error_code();
				if ( 'http_request_failed' === $curl_error ) {
					$result = wp_json_encode( array( 'error' => __( 'cURL error:', 'coming-soon' ) . $response->get_error_message() ) );
				} else {
					$result = wp_json_encode( array( 'error' => $response->get_error_message() ) );
				}
			} else {
				$http_status = wp_remote_retrieve_response_code( $response );
				if ( 200 === $http_status ) {
					$response_body = wp_remote_retrieve_body( $response );
					$result_data   = json_decode( $response_body, true );

					if ( null === $result_data && json_last_error() !== JSON_ERROR_NONE ) {
						$result = wp_json_encode( array( 'error' => __( 'Invalid JSON response', 'coming-soon' ) ) );
					} else {
						$result = wp_json_encode( $result_data );
					}
				} else {
					// Request timeout error.
					$result = wp_json_encode( array( 'error' => __( 'Server error or request timeout. Try again later.', 'coming-soon' ) ) );
				}
			}
		} catch ( Exception $e ) {
			$result = wp_json_encode( array( 'error' => __( 'Server error or request timeout. Try again later.', 'coming-soon' ) ) );
		}

		echo wp_kses_post( $result );

		exit;

	}
}
