<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Printful_Integration
{
    const PF_API_CONNECT_STATUS = 'printful_api_connect_status';
    const PF_CONNECT_ERROR = 'printful_connect_error';

	public static $_instance;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		self::$_instance = $this;
	}

    /**
     * @return Printful_Client
     * @throws PrintfulException
     */
	public function get_client() {
		$isOauth = true;
		$key = $this->get_option( 'printful_oauth_key' );
		if (!$key) {
			$isOauth = false;
			$key = $this->get_option( 'printful_key' );
		}

		require_once 'class-printful-client.php';
		$client = new Printful_Client( $key, $this->get_option( 'disable_ssl' ) == 'yes', $isOauth );

		return $client;
	}

    /**
     * Check if the connection to Printful is working
     * @param bool $force
     * @return bool
     * @throws PrintfulException
     */
	public function is_connected( $force = false ) {
		$api_key = $this->get_option( 'printful_key' );
		$api_oauth_key = $this->get_option( 'printful_oauth_key' );
        
		//dont need to show error - the plugin is simply not setup
		if ( empty( $api_key ) && empty( $api_oauth_key ) ) {
			return false;
		}

		$invalid_api_key_error_message = sprintf(
            'Invalid API key. Please reconnect your store in <a href="%s">Printful plugin settings</a>',
            admin_url( 'admin.php?page=printful-dashboard&tab=settings' )
        );

		//validate length, show error
		if ( $api_key && strlen( $api_key ) != 36 ) {
			$this->set_connect_error( $invalid_api_key_error_message );

			return false;
		}

		//show connect status from cache
		if ( ! $force ) {
			$connected = get_transient( self::PF_API_CONNECT_STATUS );
			if ( $connected && $connected['status'] == 1 ) {
				$this->clear_connect_error();

				return true;
			} else if ( $connected && $connected['status'] == 0 ) {    //try again in a minute
				return false;
			}
		}

		$client   = $this->get_client();
		$response = false;

		//attempt to connect to printful to verify the API key
		try {
			$storeData = $client->get( 'store' );
			if ( ! empty( $storeData ) && $storeData['type'] == 'woocommerce') {
				$response = true;
				$this->clear_connect_error();
				set_transient( self::PF_API_CONNECT_STATUS, array( 'status' => 1 ) );  //no expiry
			}
		} catch ( Exception $e ) {

			if ( $e->getCode() == 401 ) {
				$this->set_connect_error( $invalid_api_key_error_message );

				set_transient( self::PF_API_CONNECT_STATUS, array( 'status' => 0 ), MINUTE_IN_SECONDS );  //try again in 1 minute
			} else {
				$this->set_connect_error( 'Could not connect to Printful API. Please try again later. (Error ' . $e->getCode() . ': ' . $e->getMessage() . ')' );
			}

			//do nothing
			set_transient( self::PF_API_CONNECT_STATUS, array( 'status' => 0 ), MINUTE_IN_SECONDS );  //try again in 1 minute
		}

		return $response;
	}

	/**
	 * Update connect error message
	 * @param string $error
	 */
	public function set_connect_error($error = '') {
		update_option( self::PF_CONNECT_ERROR, $error );
	}

	/**
	 * Get current connect error message
	 */
	public function get_connect_error() {
		return get_option( self::PF_CONNECT_ERROR, false );
	}

	/**
	 * Remove option used for storing current connect error
	 */
	public function clear_connect_error() {
		delete_option( self::PF_CONNECT_ERROR );
	}

    /**
     * AJAX call endpoint for connect status check
     * @throws PrintfulException
     */
	public static function ajax_force_check_connect_status() {

		Printful_Admin::validateAdminAccess();

        check_admin_referer( 'check_connect_status' );

		if ( Printful_Integration::instance()->is_connected( true ) ) {
			die( 'OK' );
		}

		die( 'FAIL' );
	}

	/**
	 * Wrapper method for getting an option
	 * @param $name
	 * @param array $default
	 * @return bool
	 */
	public function get_option( $name, $default = array() ) {
        $options = $this->get_settings( $default );
		if ( ! empty( $options[ $name ] ) ) {
			return $options[ $name ];
		}

		return false;
	}

    /**
     * Wrapper method for getting all the settings
     * @param array $default
     * @return array
     */
    public function get_settings( $default = array() ) {
        return get_option( 'woocommerce_printful_settings', $default );
    }

	/**
	 * Save the setting
	 * @param $settings
	 */
	public function update_settings( $settings ) {
		update_option( 'woocommerce_printful_settings', $settings );
	}
}