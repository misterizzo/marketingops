<?php
/**
 * manages all api calls for deals
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 * @package    hubspot-deals-for-woocommerce-memberships
 * @subpackage hubspot-deals-for-woocommerce-memberships/includes
 * @author     MakeWebBetter <webmaster@makewebbetter.com>
 */

if( !class_exists( 'HubSpotMembershipsConnectionMananager' ) ) {

	if( in_array( 'hubspot-woocommerce-integration-pro/hubspot-woocommerce-integration-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

		if( file_exists( HUBWOO_MS_DEAL_PLUGINS_PATH . 'hubspot-woocommerce-integration-pro/includes/class-hubwoo-connection-manager.php' ) ) {

			require_once ( HUBWOO_MS_DEAL_PLUGINS_PATH . 'hubspot-woocommerce-integration-pro/includes/class-hubwoo-connection-manager.php' );
		}
	}
	elseif( in_array( 'hubspot-woocommerce-integration-starter/hubspot-woocommerce-integration-starter.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

		if( file_exists( HUBWOO_MS_DEAL_PLUGINS_PATH . 'hubspot-woocommerce-integration-starter/includes/class-hubwoo-connection-manager.php' ) ) {

			require_once ( HUBWOO_MS_DEAL_PLUGINS_PATH . 'hubspot-woocommerce-integration-starter/includes/class-hubwoo-connection-manager.php' );
		}
	}
	elseif( in_array( 'hubspot-woocommerce-integration-complimentary/hubspot-woocommerce-integration-complimetary.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

		if( file_exists( HUBWOO_MS_DEAL_PLUGINS_PATH . 'hubspot-woocommerce-integration-complimentary/includes/class-hubwoo-connection-manager.php' ) ) {
			
			require_once ( HUBWOO_MS_DEAL_PLUGINS_PATH . 'hubspot-woocommerce-integration-complimentary/includes/class-hubwoo-connection-manager.php' );
		}
	}
	elseif( in_array( 'hubwoo-integration/hubspot-woocommerce-integration.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

		if( file_exists( HUBWOO_MS_DEAL_PLUGINS_PATH . 'hubwoo-integration/includes/class-hubwoo-connection-manager.php' ) ) {

			require_once ( HUBWOO_MS_DEAL_PLUGINS_PATH . 'hubwoo-integration/includes/class-hubwoo-connection-manager.php' );
		}
	}
	elseif( in_array( 'hubspot-for-woocommerce/hubspot-for-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

		if( file_exists( HUBWOO_MS_DEAL_PLUGINS_PATH . 'hubspot-for-woocommerce/includes/class-hubwooconnectionmananager' ) ) {

			require_once ( HUBWOO_MS_DEAL_PLUGINS_PATH . 'hubspot-for-woocommerce/includes/class-hubwooconnectionmananager.php' );
		}
	}
	elseif( in_array( 'makewebbetter-hubspot-for-woocommerce/makewebbetter-hubspot-for-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

		if( file_exists( HUBWOO_MS_DEAL_PLUGINS_PATH . 'makewebbetter-hubspot-for-woocommerce/includes/class-hubwooconnectionmananager.php' ) ) {

			require_once ( HUBWOO_MS_DEAL_PLUGINS_PATH . 'makewebbetter-hubspot-for-woocommerce/includes/class-hubwooconnectionmananager.php' );
		}
	}
}

class HubSpotMembershipsConnectionMananager extends HubWooConnectionMananager {

	private $baseUrl  = "https://api.hubapi.com";

	/**
	 * post request
	 *
	 * @since    1.0.0
	 */

	private function _hubwoo_deals_post( $endpoint, $post_params, $headers ){
		
		$url = $this->baseUrl . $endpoint;

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

	/**
	 * get request
	 *
	 * @since    1.0.0
	 */
	private function _hubwoo_deals_get( $endpoint, $headers ){

		$url = $this->baseUrl . $endpoint;

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

	/**
	 * put request
	 *
	 * @since    1.0.0
	 */

	private function _hubwoo_deals_put( $endpoint, $post_params, $headers ){

		$url = $this->baseUrl . $endpoint;

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

	/**
	 * creating new wc pipeline
	 *
	 * @since    1.0.0
	 */
	
	public function create_deal_pipeline( $pipeline_details ){

		$url = '/deals/v1/pipelines';
		
		$access_token = self::hubwoo_get_access_token();

		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '. $access_token
		);

		$pipeline_details = json_encode( $pipeline_details );

		$response = self::_hubwoo_deals_post( $url, $pipeline_details, $headers );
				
		$message = __('Creating New Deal Pipeline','hubwoo');

		$this->create_log( $message, $url, $response );

		return $response;
	}

	/**
	 * get customer from HuBSpot bt email
	 *
	 * @since    1.0.0
	 */
	public function get_customer_by_email( $email, $fname = '', $lname = '' ) {

		$vid = '';

		$url = '/contacts/v1/contact/email/'.$email.'/profile';

		$access_token = self::hubwoo_get_access_token();

		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$access_token
		);

		$response = $this->_hubwoo_deals_get( $url, $headers );

		$message = __('Fetching Contact by email','hubwoo');

		$this->create_log( $message, $url, $response );
		
		$status_code = $response['status_code'];

		if( $status_code == 404 ) {

			$obj = new HubSpotMembershipsConnectionMananager();
			$vid = $obj->create_customer_by_email( $email, $fname, $lname );
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

	public function get_customer_vid ( $email ) {

		$vid = '';

		$url = '/contacts/v1/contact/email/'.$email.'/profile';

		$access_token = self::hubwoo_get_access_token();

		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$access_token
		);

		$response = $this->_hubwoo_deals_get( $url, $headers );

		$message = __( 'Fetching Contact VID by email', 'hubwoo' );

		$this->create_log( $message, $url, $response );
		
		$status_code = !empty( $response['status_code'] ) ? $response['status_code'] : "";

		if( !empty( $status_code ) &&  200 == $status_code ) {

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


	/**
	 * creating new contact on not found
	 *
	 * @since    1.0.0
	 */

	public function create_customer_by_email( $email, $fname, $lname ) {

		$url = '/contacts/v1/contact';
		$access_token = self::hubwoo_get_access_token();
		$vid = '';
		$contact_properties = array();
		$contact_properties[] = array( "property" => "email", "value" => $email );
		$contact_properties[] = array( "property" => "firstname", "value" => $fname );
		$contact_properties[] = array( "property" => "lastname", "value" => $lname );
		$contact_details = array( "properties" => $contact_properties );

		$contact_details = json_encode( $contact_details );
		
		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$access_token
		);

		$response = $this->_hubwoo_deals_post( $url, $contact_details, $headers );

		$status_code = $response['status_code'];

		if( $status_code == 200 ) {

			$api_body = json_decode($response['response'], true);

			if( isset( $api_body['vid'] ) ) {

				$vid = $api_body['vid'];
			}
			else {

				$vid = '';
			}
		}
		else {

			$vid = '';
		}
				
		$message = __( 'Creating New Contact', 'hubwoo' );

		$this->create_log( $message, $url, $response );

		return $vid;
	}

	/**
	 * creating deals on HubSpot
	 * @since 1.0.0
	 */

	public function create_new_deal( $deal_details ) {

		$url = '/deals/v1/deal/';
		
		$access_token = $this->hubwoo_get_access_token();

		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$access_token
		);

		$deal_details = json_encode( $deal_details );

		$response = $this->_hubwoo_deals_post( $url, $deal_details, $headers );
				
		$message = __('Creating New deal','hubwoo');

		$this->create_log( $message, $url, $response );

		return $response;
	}

	public function remove_deal_associations ( $deal_id, $vid ) {

		$url = '/crm-associations/v1/associations/delete';
		$access_token = $this->hubwoo_get_access_token();
		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$access_token
		);
		$request = array( "fromObjectId" => $vid, "toObjectId" => $deal_id, "category" => "HUBSPOT_DEFINED", "definitionId" => "4" );
		$request = json_encode( $request );
		$response = $this->_hubwoo_deals_put( $url, $request, $headers );
		$message = __('Removing Deal Association With Contact','hubwoo');
		$this->create_log( $message, $url, $response );
		return $response;
	}

	public function create_deal_associations ( $deal_id, $vid ) {

		$url = '/crm-associations/v1/associations';
		$access_token = $this->hubwoo_get_access_token();
		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$access_token
		);
		$request = array( "fromObjectId" => $vid, "toObjectId" => $deal_id, "category" => "HUBSPOT_DEFINED", "definitionId" => "4" );
		$request = json_encode( $request );
		$response = $this->_hubwoo_deals_put( $url, $request, $headers );
		$message = __('Creating Deal Association With Contact','hubwoo');
		$this->create_log( $message, $url, $response );
		return $response;
	}

	/**
	 *updating deals 
	 *
	 * @since    1.0.0
	 */

	public function update_existing_deal( $deal_id, $deal_details ) {

		$url = '/deals/v1/deal/'.$deal_id;
		
		$access_token = $this->hubwoo_get_access_token();

		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$access_token
		);

		$deal_details = json_encode( $deal_details );

		$response = $this->_hubwoo_deals_put( $url, $deal_details, $headers );
				
		$message = __('Updating HubSpot Deals','hubwoo');

		$this->create_log( $message, $url, $response );

		return $response;
	}

	/**
	 * return formatted time for HubSpot
	 * 
	 * @param  int 			$unix_timestamp
	 * @return string       formatted time.
	 * @since 1.0.0
	 */
	public static function hubwoo_deal_set_utc_midnight( $unix_timestamp ) {
		
		$string = gmdate("Y-m-d H:i:s", $unix_timestamp );
		$date = new DateTime( $string );
		$date->modify( 'midnight' );
		return $date->getTimestamp() * 1000; // in miliseconds
	}

	/**
	 * format an array in hubspot accepted enumeration value.
	 * 
	 * @param  array   $properties  Array of values
	 * @return string       formatted string.
	 * @since 1.0.0
	 */
	public static function hubwoo_deal_format_array( $properties ){

		if( is_array( $properties ) ){

			$properties = array_unique( $properties );

			$properties = implode( ';', $properties );
		}

		return $properties;
	}

	/**
	 * creating groups for deals on HubSpot
	 * @since 1.0.0
	 */
	public function create_deal_group( $deal_groups ) {

		$url = '/properties/v1/deals/groups/';
		
		$access_token = $this->hubwoo_get_access_token();

		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$access_token
		);

		if( is_array( $deal_groups ) && count( $deal_groups ) ) {

			$deal_details = json_encode( $deal_groups );

			$response = $this->_hubwoo_deals_post( $url, $deal_details, $headers );
					
			$message = __('Creating deal custom groups','hubwoo');

			$this->create_log( $message, $url, $response );

			return $response;
		}
	}

	/**
	 * creating properties for deals 
	 * @since 1.0.0
	 */
	public function create_deal_property( $prop_details ) {

		$url = '/properties/v1/deals/properties/';
		
		if( is_array( $prop_details ) ) {

			if( isset( $prop_details[ 'name' ] ) && isset( $prop_details[ 'groupName' ] ) ) {

				$url = '/properties/v1/deals/properties/';
				$access_token = $this->hubwoo_get_access_token();
				$headers = array(
					'Content-Type: application/json',
					'Authorization: Bearer '.$access_token
				);
		        $prop_details = json_encode( $prop_details );
				$response = $this->_hubwoo_deals_post( $url, $prop_details, $headers );
				
				$message = __('Creating deal custom properties','hubwoo');

				$this->create_log( $message, $url, $response );

				return $response;
			}
		}
	}

	/**
	 * updating deal properties 
	 *
	 * @since    1.0.0
	 */

	public function update_deal_property( $deal_property ){

		if( is_array( $deal_property ) ) {

			if( isset( $deal_property[ 'name' ] ) && isset( $deal_property[ 'groupName' ] ) ) {

				$url = '/properties/v1/deals/properties/named/'.$deal_property[ 'name' ];
				
				$access_token = $this->hubwoo_get_access_token();

				$headers = array(
					'Content-Type: application/json',
					'Authorization: Bearer '.$access_token
				);

				$deal_property = json_encode( $deal_property );

				$response = $this->_hubwoo_deals_put( $url, $deal_property, $headers );
						
				$message = __('Updating HubSpot Deal Properties','hubwoo');

				$this->create_log( $message, $url, $response );

				return $response;
			}
		}
	}

	/**
	 * upadting deal pipelines
	 *
	 * @since    1.0.0
	 */

	public function update_deal_pipeline( $pipeline_details, $pipeline_id ) {

		$url = '/crm/v3/pipelines/deals/' . $pipeline_id;
		
		$access_token = self::hubwoo_get_access_token();

		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$access_token
		);

		$pipeline_details = json_encode( $pipeline_details );

		$response = self::_hubwoo_deals_put( $url, $pipeline_details, $headers );
				
		$message = __('Updating Deal Pipeline','hubwoo');

		$this->create_log( $message, $url, $response );

		return $response;
	}

	/**
	 * fetching access token
	 *
	 * @since    1.0.0
	 */
	public static function hubwoo_get_access_token() {

		$hubwoo_token = '';

		if ( in_array( 'hubspot-woocommerce-integration-pro/hubspot-woocommerce-integration-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

			$hubwoo_token = get_option( "hubwoo_pro_access_token", false );
		}
		elseif( in_array( 'hubspot-woocommerce-integration-starter/hubspot-woocommerce-integration-starter.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

			$hubwoo_token = get_option( "hubwoo_starter_access_token", false );
		}
		elseif( in_array( 'hubspot-woocommerce-integration-complimentary/hubspot-woocommerce-integration-complimetary.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

			$hubwoo_token = get_option( "hubwoo_comp_access_token", false );
		}
		elseif( in_array( 'hubwoo-integration/hubspot-woocommerce-integration.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

			$hubwoo_token = get_option( "hubwoo_access_token", false );
		}
		elseif( in_array( 'hubspot-for-woocommerce/hubspot-for-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

			$hubwoo_token = get_option( "hubwoo_pro_access_token", false );
		}
		elseif( in_array( 'makewebbetter-hubspot-for-woocommerce/makewebbetter-hubspot-for-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

			$hubwoo_token = get_option( "hubwoo_pro_access_token", false );
		}		
		return $hubwoo_token;
	}
}
?>