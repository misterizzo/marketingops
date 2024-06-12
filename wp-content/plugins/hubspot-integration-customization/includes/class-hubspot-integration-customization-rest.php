<?php

/**
 * All REST call to be handled here
 *
 * @link       makwebbetter.com
 * @since      1.0.0
 *
 * @package    Hubspot_Integration_Customization
 * @subpackage Hubspot_Integration_Customization/includes
 */

/**
 * Manage REST calls.
 *
 * @since      1.0.0
 * @package    Hubspot_Integration_Customization
 * @subpackage Hubspot_Integration_Customization/includes
 * @author     MakeWebBetter <support@makewebbetter.com>
 */
class Hubspot_Integration_Customization_Rest {
	/**
	 * The single instance of the class.
	 *
	 * @since   1.0.0
	 * @var HubWooConnectionMananager   The single instance of the HubWooConnectionMananager
	 */
	protected static $_instance = null;

	/**
	 * Base url of hubspot api.
	 *
	 * @since 1.0.0
	 * @var string base url of API.
	 */
	private $base_url = 'https://api.hubapi.com';


	/**
	 * Main HubWooConnectionMananager Instance.
	 *
	 * Ensures only one instance of HubWooConnectionMananager is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return HubWooConnectionMananager - Main instance.
	 */
	public static function get_instance() {

		if ( is_null( self::$_instance ) ) {

			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Create log of requests.
	 *
	 * @param  string $message     hubspot log message.
	 * @param  string $url         hubspot acceptable url.
	 * @param  array  $response    hubspot response array.
	 * @since 1.0.0
	 */
	public function create_log( $message, $url, $response ) {

		if ( 400 == $response['status_code'] || 401 == $response['status_code'] ) {

			update_option( 'hubwoo_pro_alert_param_set', true );
			$error_apis = get_option( 'hubwoo-error-api-calls', 0 );
			$error_apis ++;
			update_option( 'hubwoo-error-api-calls', $error_apis );
		} elseif ( 200 == $response['status_code'] || 202 == $response['status_code'] || 201 == $response['status_code'] || 204 == $response['status_code'] ) {

			$success_apis = get_option( 'hubwoo-success-api-calls', 0 );
			$success_apis ++;
			update_option( 'hubwoo-success-api-calls', $success_apis );
			update_option( 'hubwoo_pro_alert_param_set', false );
		} else {

			update_option( 'hubwoo_pro_alert_param_set', false );
		}

		if ( 200 == $response['status_code'] ) {

			$final_response['status_code'] = 200;
		} elseif ( 202 == $response['status_code'] ) {

			$final_response['status_code'] = 202;
		} else {

			$final_response = $response;
		}

		$log_enable = get_option( 'hubwoo_pro_log_enable', 'yes' );

		if ( 'yes' == $log_enable ) {

			if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
				$server = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
			}

			$log_dir = WC_LOG_DIR . 'hubspot-for-woocommerce-logs.log';

			if ( ! is_dir( $log_dir ) ) {

				@fopen( WC_LOG_DIR . 'hubspot-for-woocommerce-logs.log', 'a' );
			}

			$log = 'Website: ' . $server . PHP_EOL .
					'Time: ' . current_time( 'F j, Y  g:i a' ) . PHP_EOL .
					'Process: ' . $message . PHP_EOL .
					'URL: ' . $url . PHP_EOL .
					'Response: ' . json_encode( $final_response ) . PHP_EOL .
					'-----------------------------------' . PHP_EOL;

			file_put_contents( $log_dir, $log, FILE_APPEND );
		}
	}

	private function hic_get( $endpoint, $headers ){

		$url = $this->base_url.$endpoint;

		$ch = @curl_init();
		@curl_setopt($ch, CURLOPT_POST, false);
		@curl_setopt($ch, CURLOPT_URL, $url);
		@curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		@curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$response = @curl_exec($ch);
		$status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errors = curl_error($ch);
		@curl_close($ch);

		return array( 'status_code' => $status_code, 'response' => $response, 'errors' => $curl_errors );
	}

	private function hic_post( $endpoint, $post_params, $headers ){
		
		$url = $this->base_url . $endpoint;

		$ch = @curl_init();
		@curl_setopt($ch, CURLOPT_POST, true);
		@curl_setopt($ch, CURLOPT_URL, $url);
		@curl_setopt($ch, CURLOPT_POSTFIELDS,  $post_params  );
		@curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		@curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$response = @curl_exec($ch);
		$status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errors = curl_error($ch);
		@curl_close($ch);

		return array( 'status_code' => $status_code, 'response' => $response, 'errors' => $curl_errors );
	}

	private function hic_put( $endpoint, $post_params, $headers ){
		
		$url = $this->base_url.$endpoint;
		$ch = @curl_init();
		@curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		@curl_setopt($ch, CURLOPT_URL, $url);
		@curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params );
		@curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		@curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$response = @curl_exec($ch);
		$status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errors = curl_error($ch);
		@curl_close($ch);
		return array( 'status_code' => $status_code, 'response' => $response, 'errors' => $curl_errors );
	}

	public function hubwoo_get_header_token() {
			
		if( class_exists('HubwooDealsManager')) {
			$access_token = HubwooDealsManager::hubwoo_get_access_token(); 
		} elseif(method_exists('HubWooConnectionMananager','hubwoo_get_access_token')) {
			$access_token = HubWooConnectionMananager::hubwoo_get_access_token();
		} else {
			$access_token = Hubwoo::hubwoo_get_access_token();
		}
		return $access_token;	
	}

	public function upload_file( $file, $order_id ) {

		$url = '/filemanager/api/v2/files';
		$access_token = $this->hubwoo_get_header_token();
		$headers = array(
			'Content-Type: multipart/form-data',
			'Authorization: Bearer '.$access_token
		);

		$filePath = WPI_ATTACHMENTS_DIR.'/'.$file;
		$fileName = array_pop( explode( '/', $file ) );
		$files = new CURLFILE(  $filePath );
		$pdf_details = array( "files" => $files, 'file_names' => '#'.$order_id.' - '.$fileName, 'folder_paths' => 'Invoices/' );
		$response = $this->hic_post( $url, $pdf_details, $headers );
		$message = __('Uploading file to HubSpot','hubwoo');
		$this->create_log( $message, $url, $response);
		return $response;
	}
	
	
	public function upload_woo_slip( $file, $order_id ) {

		$url = '/filemanager/api/v2/files';
		$access_token = $this->hubwoo_get_header_token();
		$headers = array(
			'Content-Type: multipart/form-data',
			'Authorization: Bearer '.$access_token
		);
		$wp_uploads = wp_upload_dir();
		$filePath = $wp_uploads['basedir'].'/wpo_wcpdf/attachments/'.$file.'.pdf';
		$fileName = array_pop( explode( '/', $file ) );
		$files = new CURLFILE(  $filePath );
		$pdf_details = array( "files" => $files, 'file_names' => '#'.$order_id.' - '.$fileName.'.pdf', 'folder_paths' => 'Invoices/' );
		$response = $this->hic_post( $url, $pdf_details, $headers );
		return $response;
	}
	
	public function create_attachment( $id, $att_id, $body_text = "Invoice" ) {

		$url = '/engagements/v1/engagements';
		$access_token = $this->hubwoo_get_header_token();
		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$access_token
		);
		$deal_ids[] = $id; 
		$engagement = array( "type" => "NOTE", "active" => "true" );
		$associations = array( "dealIds" => $deal_ids );
		$metadata = array( "body" => $body_text );
		$attachment[] = array( "id" => $att_id );
		$details["engagement"] = $engagement;
		$details["associations"] = $associations;
		$details["metadata"] = $metadata;
		$details["attachments"] = $attachment;
		$request = json_encode( $details );
		$response = $this->hic_post( $url, $request, $headers );
		$message = __('Creating Deal Engagement','hubwoo');
		$this->create_log( $message, $url, $response);
		return $response;
	}

	public function hubwoo_update_customer($vid, $contacts) {

		if( is_array( $contacts ) ) {
			$url = '/contacts/v1/contact/vid/'.$vid.'/profile';
			$access_token = $this->hubwoo_get_header_token();
			$headers = array(
				'Content-Type: application/json',
				'Authorization: Bearer '.$access_token
			);
	        $contacts = json_encode($contacts);
			$response = $this->hic_post( $url, $contacts, $headers );
			$message = __('Updating user data','hubwoo');
			$this->create_log( $message, $url, $response );
			return $response;
		}		
	}

	public function get_customer_by_user_data( $user_data ) {

		$vid = '';
		$url = '/contacts/v1/contact/email/'.$user_data['email'].'/profile';
		$access_token = $this->hubwoo_get_header_token();
		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$access_token
		);
		$response = $this->hic_get( $url, $headers );
		$message = __('Fetching Contact by email','hubwoo');
		$this->create_log( $message, $url, $response );
		$status_code = $response['status_code'];
		if( $status_code == 404 ) {
			$vid = self::create_customer_by_user_data( $user_data );
		}
		elseif( $status_code == 200 ) {
			$api_body = json_decode( $response['response'], true );
			if( isset( $api_body ) && isset( $api_body['vid'] ) ) {
				$vid = $api_body['vid'];
			}
			else {
				$vid = '';
			}
		}
		return $vid;
	}

	public function create_customer_by_user_data( $user_data ) {

		$url = '/contacts/v1/contact';
		$access_token = $this->hubwoo_get_header_token();
		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$access_token
		);
		$vid = '';
		$contact_properties = array();

		foreach ($user_data as $property => $value ) {
			$contact_properties[] = array( "property" => $property, "value" => $value );
		}
		$contact_details = array( "properties" => $contact_properties );
		$contact_details = json_encode( $contact_details );
		$response = $this->hic_post( $url, $contact_details, $headers );
		$status_code = !empty( $response['status_code'] ) ? $response['status_code'] : "";

		if( 200 == $status_code ) {

			$api_body = json_decode($response['response'], true );

			if( isset( $api_body['vid'] ) ) {

				$vid = $api_body['vid'];
			}
		}
		elseif ( 409 == $status_code ) {

			$api_body = json_decode($response['response'], true );

			if( isset( $api_body['identityProfile']['vid'] ) ) {

				$vid = $api_body['identityProfile']['vid'];
			}
		}
		else {

			$vid = '';
		}
				
		$message = __( 'Creating New Contact', 'hubwoo' );

		$this->create_log( $message, $url, $response );

		return $vid;
	}

	public function hubwoo_get_property($property_name) {

		$url = '/properties/v1/contacts/properties/named/'.$property_name;

		$access_token = $this->hubwoo_get_header_token();
		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$access_token
		);

		$response = $this->hic_get( $url, $headers );
		$message = __('Checking if the property exists','hubwoo');
		$this->create_log( $message, $url, $response );
		return $response;
	}	

	public function create_property( $prop_details ) {

		if( is_array( $prop_details ) ) {
			
			if( isset( $prop_details[ 'name' ] ) && isset( $prop_details[ 'groupName' ] ) ) {
				
				$url = '/properties/v1/contacts/properties';
				$access_token = $this->hubwoo_get_header_token();
				$headers = array(
					'Content-Type: application/json',
					'Authorization: Bearer '.$access_token
				);
		        $prop_details = json_encode($prop_details);
				$response = $this->hic_post( $url, $prop_details, $headers );
				$message = __('Creating property' ,'hubwoo');
				$this->create_log( $message, $url, $response );
				return $response;
			}
		}
	}	

	public function hubwoo_create_batch_products($products) {

		$url = '/crm-objects/v1/objects/products/batch-create';
		$access_token = $this->hubwoo_get_header_token();
		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$access_token
		);

        $products = json_encode($products);
		$response = $this->hic_post( $url, $products, $headers );
		$message = __('Creating Batch Products','hubwoo');
		$this->create_log( $message, $url, $response );
		return $response;
	}


	public function hubwoo_create_product_inventory($product) {

		$url = '/crm-objects/v1/objects/products/';
		$access_token = $this->hubwoo_get_header_token();
		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$access_token
		);

        $product = json_encode($product);
		$response = $this->hic_post( $url, $product, $headers );
		$message = __('Creating a Product','hubwoo');
		$this->create_log( $message, $url, $response );
		return $response;		
	}	

	public function hubwoo_update_product_inventory($product_id, $product) {

		$url = '/crm-objects/v1/objects/products/'.$product_id;
		$access_token = $this->hubwoo_get_header_token();
		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$access_token
		);

        $product = json_encode($product);
		$response = $this->hic_put( $url, $product, $headers );
		$message = __('Updating Product','hubwoo');
		$this->create_log( $message, $url, $response );
		return $response;		
	}
}
