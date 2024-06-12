<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://makewebbetter.com
 * @since      1.0.0
 *
 * @package    hubspot-deals-for-woocommerce-memberships
 * @subpackage hubspot-deals-for-woocommerce-memberships/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    hubspot-deals-for-woocommerce-memberships
 * @subpackage hubspot-deals-for-woocommerce-memberships/admin
 * @author     MakeWebBetter <webmaster@makewebbetter.com>
 */
class Hubspot_Deals_For_Woocommerce_Memberships_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->hubwoo_ms_deal_admin_actions();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Hubspot_Deals_For_Woocommerce_Memberships_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Hubspot_Deals_For_Woocommerce_Memberships_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$screen = get_current_screen();

        if( isset( $screen->id ) && $screen->id == 'woocommerce_page_hubwoo_ms_deal' ) {

			wp_enqueue_style( "hubwoo-ms-deal-admin-style", plugin_dir_url( __FILE__ ) . 'css/hubspot-deals-for-woocommerce-memberships-admin.css', array(), $this->version, 'all' );
			wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
			wp_enqueue_style( 'hubwoo-ms-deals-jquery-ui', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css', array(), '1.0.0' );
			wp_enqueue_style( 'woocommerce_admin_styles' );
			wp_enqueue_style( 'woocommerce_admin_menu_styles' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Hubspot_Deals_For_Woocommerce_Memberships_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Hubspot_Deals_For_Woocommerce_Memberships_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$screen = get_current_screen();
		
        if( isset( $screen->id ) && $screen->id == 'woocommerce_page_hubwoo_ms_deal' ) {

        	wp_register_script( 'woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip', 'wc-enhanced-select' ), WC_VERSION );
			$locale  = localeconv();
			$decimal = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';
			$params = array(
				'i18n_decimal_error'                => sprintf( __( 'Please enter in decimal (%s) format without thousand separators.', 'hubwoo' ), $decimal ),
				'i18n_mon_decimal_error'            => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'hubwoo' ), wc_get_price_decimal_separator() ),
				'i18n_country_iso_error'            => __( 'Please enter in country code with two capital letters.', 'hubwoo' ),
				'i18_sale_less_than_regular_error'  => __( 'Please enter in a value less than the regular price.', 'hubwoo' ),
				'decimal_point'                     => $decimal,
				'mon_decimal_point'                 => wc_get_price_decimal_separator(),
				'strings' => array(
					'import_products' => __( 'Import', 'hubwoo' ),
					'export_products' => __( 'Export', 'hubwoo' ),
				),
				'urls' => array(
					'import_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_importer' ) ),
					'export_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_exporter' ) ),
				),
			);
			wp_localize_script( 'woocommerce_admin', 'woocommerce_admin', $params );
			wp_enqueue_script( 'woocommerce_admin' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
        	wp_enqueue_script( "hubwoo-ms-deal-admin-sript", plugin_dir_url( __FILE__ ) . 'js/hubspot-deals-for-woocommerce-memberships-admin.js', array( 'jquery' ), $this->version, false );
        	wp_localize_script( 'hubwoo-ms-deal-admin-sript', 'hubwooi18n',
        		array( 
        			'ajaxUrl' 							=> admin_url( 'admin-ajax.php' ),
					'hubwooSecurity' 					=> wp_create_nonce( 'hubwoo_security' ), 
					'hubwooWentWrong' 					=> __( 'Something went wrong, please try again later! or check logs', 'hubwoo' ), 
					'hubwooDealsPipelineSetupCompleted' => __( 'Pipeline Setup Completed. One more step to Go.', 'hubwoo'),
					'hubwooCreatingPipeline' 			=> __( 'New Pipeline created for Memberships', 'hubwoo' ),
					'hubwooCreatingGroup' 				=> __( 'Creating Group', 'hubwoo' ),
					'hubwooCreatingProperty' 			=> __( 'Creating Property', 'hubwoo' ),
					'hubwooSetupCompleted' 				=> __( 'Deals Setup Completed', 'hubwoo' ),
					'hubwooPipelineUpdated' 			=> __( 'Pipeline Updated Successfully', 'hubwoo' ),
					'hubwooPipelineUpdateFailed' 		=> __( 'Something went wrong in Deal Pipeline Update. Please check the logs and try again.', 'hubwoo' ),
					'hubwooLicenseUpgrade' 				=> __( 'This will redirect you to License Activation Panel and your current license will be deleted. For license change or upgrade, use the new license key received after the full purchase of the extension.', 'hubwoo' ),
					'hubwooDealsSyncComplete' 			=> __( 'Deals Sync completed!', 'hubwoo' ),
					'hubwooDealSynced' 					=> __( 'Membership synced successfully', 'hubwoo' ),
					'hubwooNoObjectFound' 				=> __( 'No memberships found for the selected date or status. Please try again.', 'hubwoo' ),
				)
			);
        }
	}

	/**
	 * all admin actions.
	 * 
	 * @since 1.0.0
	 */
	public function hubwoo_ms_deal_admin_actions() {

		// add submenu hubspot coupons in woocommerce top menu.
		add_action( 'admin_menu', array( &$this, 'add_hubwoo_ms_deal_submenu' ) );
	}

	/**
	 * add hubspot coupons submenu in woocommerce menu.
	 *
	 * @since 1.0.0
	 */
	public function add_hubwoo_ms_deal_submenu() {

		add_submenu_page( 'woocommerce', __( 'HubSpot Membership Deals', 'hubwoo' ), __( 'HubSpot Membership Deals', 'hubwoo' ), 'manage_woocommerce', 'hubwoo_ms_deal', array( &$this, 'hubwoo_ms_deal_configuration' ) );
	}

	/**
	 * adding hubspot deal menu display for admin
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_ms_deal_configuration() {

		$callname_lic = Hubspot_Deals_For_Woocommerce_Memberships::$hubwoo_ms_deals_lic_callback_function;

		if( Hubspot_Deals_For_Woocommerce_Memberships::$callname_lic() ) {

			include_once 'partials/hubspot-deals-for-woocommerce-memberships-admin-display.php';
		}
		else {

			include_once 'partials/hubspot-deals-for-woocommerce-memberships-license.php';
		}
	}

	/**
	 * general settings for deals admin page
	 *
	 * @since 1.0.0
	 */

	public static function hubwoo_ms_deals_settings() {

		$settings = array();

		$settings[] = array(
			'title' => __( 'Create HubSpot Deal on every new Membership', 'hubwoo'),  
			'id'	=> 'hubwoo_ms_deals_settings_title', 
			'type'	=> 'title'	
		);

		$settings[] = array(
			'title' => __( 'Enable/Disable', 'hubwoo' ),
			'id'	=> 'hubwoo_ms_deals_settings_enable', 
			'desc'	=> __( 'Turn on/off the feature', 'hubwoo' ),
			'type'	=> 'checkbox'
		);

		$settings[] = array(
			'type' 	=> 'sectionend',
	        'id' 	=> 'hubwoo_ms_deals_settings_end'
		);

		return $settings;
	}

	/**
	 * checking access token validity
	 * @since 1.0.0
	 */
	public function hubwoo_ms_deals_check_oauth_access_token() {

		$response = array( 'status' => true, 'message' => __('Success', 'hubwoo') );
		check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

		if ( Hubspot_Deals_For_Woocommerce_Memberships::is_access_token_expired() ) {
			$hapikey = HUBWOO_MS_DEAL_CLIENTID;
			$hseckey = HUBWOO_MS_DEAL_SECRETID;
			$deals_manager = new HubSpotMembershipsConnectionMananager();
			$status =  $deals_manager->hubwoo_refresh_token( $hapikey, $hseckey );
			if( !$status ) {
				$response['status'] = false;
				$response['message'] = __( 'Something went wrong, please check your API Keys', 'hubwoo' );
			}
		}
		
		echo json_encode( $response );
		wp_die();
	}

	/*	
		* get membership pipeline details
	*/
	public function hubwoo_ms_deals_get_pipeline () {

		$pipelines = array();

		$mapped_stages = get_option( "hubwoo_ms_deal_mapped_stages", array() );

		if ( !empty( $mapped_stages ) ) {

			$pipelines[] = array(
				'label' 		=> __( 'WooCommerce Memberships', 'hubwoo' ),
				'displayOrder' 	=> 2,
				'stages' 		=> $mapped_stages,
			);

			$pipelines = apply_filters( 'hubwoo_ms_deals_pipelines', $pipelines );
			echo json_encode( $pipelines );
		}
		wp_die();
	}

	/*
		* create new deal pipeline
	*/
	public function hubwoo_ms_deals_create_pipeline () {

		if( isset( $_POST['pipelineDetails'] ) ) {

			$pipeline_details = $_POST['pipelineDetails'];
			$deals_manager = new HubSpotMembershipsConnectionMananager();
			$response = $deals_manager->create_deal_pipeline( $pipeline_details );
			if( isset( $response['status_code'] ) && $response['status_code'] == 200 ) {
				if( isset( $response['response'] ) && !empty( $response['response'] ) ) {
					$reponse_formatted = json_decode( $response['response'] );
					$pipeline_id = !empty( $reponse_formatted->pipelineId ) ? $reponse_formatted->pipelineId : "";
					update_option( "hubwoo_ms_deals_pipeline_id", $pipeline_id );
					update_option( "hubwoo_ms_deals_pipeline_setup", true );
					update_option( "hubwoo_ms_pipeline_version", HUBWOO_MS_DEAL_VERSION );
				}
			}

			echo json_encode( $response );
		}

		wp_die();
	}

	/*	
		* get custom groups for deals
	*/
	public function hubwoo_ms_deals_get_groups () {

		// check the nonce security.
		check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

		$values[] = array( 'name' => 'membership_details', 'displayName' => __( 'Membership Details', 'hubwoo' ) );
		$values[] = array( 'name' => 'membership_billing_details', 'displayName' =>  __( 'Membership Billing Details', 'hubwoo' ) );
		$values = apply_filters( "hubwoo_ms_deals_groups", $values );
		echo json_encode( $values );
		wp_die();
	}

	/*	
		* create new groups
	*/
	public function hubwoo_ms_deals_create_group () {

		check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );
		
		if( isset( $_POST[ 'createNow' ] ) && isset( $_POST[ 'groupDetails' ] ) ) {
			$createNow = $_POST[ 'createNow' ];
			if( $createNow == "group" ){
				$groupDetails = $_POST[ 'groupDetails' ];
				$deals_manager = new HubSpotMembershipsConnectionMananager();
				echo json_encode( $deals_manager->create_deal_group( $groupDetails ) );
				wp_die();
			}
		}
	}

	/*	
		* get group properties for deal
	*/
	public function hubwoo_ms_deals_get_group_properties () {

		check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

		if( isset( $_POST[ 'groupName' ] ) ) {
			$groupName = $_POST[ 'groupName' ];
			echo json_encode( $this->hubwoo_ms_deals_properties( $groupName ) );
		}

		wp_die();
	}

	/**
	 * preparing deal properties
	 *
	 * @since 1.0.0
	 */

	public function hubwoo_ms_deals_properties( $group ) {

		$properties = array();

		if ( 'membership_details' == $group ) {
			$properties[] = array(
				"name" 					=> "membership_plan",
				"label" 				=> __( 'Membership Plan', 'hubwoo' ),
				"type" 					=> "string",
				"fieldType" 			=> "text",
				"formfield"				=> false,
			);
			$properties[] = array(
				"name" 					=> "membership_status",
				"label" 				=> __( 'Membership Status', 'hubwoo' ),
				"type" 					=> "string",
				"fieldType" 			=> "text",
				"formfield" 			=> false,
			);
			$properties[] = array(
				"name" 					=> "membership_since",
				"label" 				=> __( 'Member Since', 'hubwoo' ),
				"type" 					=> "date",
				"fieldType" 			=> "date",
				"formfield" 			=> false,
			);
			$properties[] = array(
				"name" 					=> "membership_expires",
				"label" 				=> __( 'Expires On', 'hubwoo' ),
				"type" 					=> "date",
				"fieldType" 			=> "date",
				"formfield" 			=> false,
			);
			$properties[] = array(
				"name" 					=> "membership_cancelled",
				"label" 				=> __( 'Membership Cancelled On', 'hubwoo' ),
				"type" 					=> "date",
				"fieldType" 			=> "date",
				"formfield" 			=> false,
			);
			$properties[] = array(
				"name" 					=> "membership_paused",
				"label" 				=> __( 'Membership Paused On', 'hubwoo' ),
				"type" 					=> "date",
				"fieldType" 			=> "date",
				"formfield" 			=> false,
			);
			$properties[] = array(
				"name" 					=> "membership_type",
				"label" 				=> __( 'Membership Type', 'hubwoo' ),
				"type" 					=> "string",
				"fieldType" 			=> "text",
				"formfield" 			=> false,
			);
		}
		elseif ( 'membership_billing_details' == $group ) {
			$properties[] = array(
				"name" 					=> "membership_shop_order_number",
				"label" 				=> __( 'Membership Shop Order Number', 'hubwoo' ),
				"type" 					=> "string",
				"fieldType" 			=> "text",
				"formfield" 			=> false,
			);
			$properties[] = array(
				"name" 					=> "membership_shop_order_date",
				"label" 				=> __( 'Membership Shop Order Date', 'hubwoo' ),
				"type" 					=> "date",
				"fieldType" 			=> "date",
				"formfield" 			=> false,
			);
			$properties[] = array(
				"name" 					=> "membership_shop_order_total",
				"label" 				=> __( 'Membership Shop Order Total', 'hubwoo' ),
				"type" 					=> "number",
				"fieldType" 			=> "number",
				"showCurrencySymbol" 	=> true,
				"formfield" 			=> false,
			);
			$properties[] = array(
				"name" 					=> "membership_subscription_order_number",
				"label" 				=> __( 'Membership Subscription Order Number', 'hubwoo' ),
				"type" 					=> "string",
				"fieldType" 			=> "text",
				"formfield" 			=> false,
			);
			$properties[] = array(
				"name" 					=> "membership_products",
				"label" 				=> __( 'Membership Products', 'hubwoo' ),
				"type" 					=> "string",
				"fieldType" 			=> "textarea",
				"formfield" 			=> false,
			);
		}
		$properties = apply_filters( "hubwoo_deals_properties", $properties );
		return $properties;
	}

	/*	
		* creating new custom properties for deal
	*/
	public function hubwoo_ms_deals_create_property () {

		check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );
		if ( isset( $_POST[ 'groupName' ] ) && isset( $_POST[ 'propertyDetails' ] ) ) {
			$propertyDetails = $_POST[ 'propertyDetails' ];
			$propertyDetails[ 'groupName' ] = $_POST[ 'groupName' ];
			$deals_manager = new HubSpotMembershipsConnectionMananager();
			echo json_encode( $deals_manager->create_deal_property(  $propertyDetails ) );
			wp_die();
		}
	}

	/*
		* mark the deal setup as completed
	*/
	public function hubwoo_ms_deals_setup_completed () {

		update_option( "hubwoo_ms_deals_setup", true );
		update_option( "hubwoo_ms_deals_version" , HUBWOO_MS_DEAL_VERSION );
		return true;
		wp_die();
	}

	public static function hubwoo_ms_count_for_deal () {

		$since_date = get_option( "hubwoo-ms-since-date", date('d-m-Y') );
		$upto_date = get_option( "hubwoo-ms-upto-date", date('d-m-Y') );
		$selected_status = get_option( "hubwoo-ms-sync-status", 'wcm-active' );
		$old_memberships = get_posts(
			array(
		        'numberposts' => -1,
		        'post_type'   => 'wc_user_membership',
		        'post_status' => $selected_status,
		        'meta_query'  => array(
		        	array(
						'key' 		=> 'hubwoo_ms_deal_id',
						'compare' 	=> 'NOT EXISTS',
					)
				),
		        'date_query' => array(
		            array(
						'after' 	=> date( 'd-m-Y', strtotime( $since_date ) ),
						'before'	=> date( 'd-m-Y', strtotime( $upto_date . ' +1 day' ) ),
						'inclusive'	=> true,
					)
		        ),
	    	)
    	);

	    $memberships_count = 0;

	    if ( is_array( $old_memberships ) && !empty( $old_memberships ) ) {

	    	$memberships_count = count( $old_memberships );
	    }

	    return $memberships_count;
	}

	public function hubwoo_ms_get_count () {

		$memberships = self::hubwoo_ms_count_for_deal();
		echo json_encode( $memberships );
		wp_die();
	}

	public function hubwoo_ms_old_deals_sync () {

		$since_date = get_option( "hubwoo-ms-since-date", date('d-m-Y') );
		$upto_date = get_option( "hubwoo-ms-upto-date", date('d-m-Y') );
		$selected_status = get_option( "hubwoo-ms-sync-status", 'wcm-active' );
		$old_memberships = get_posts(
			array(
		        'numberposts' => 1,
		        'post_type'   => 'wc_user_membership',
		        'post_status' => $selected_status,
		        'meta_query'  => array(
		        	array(
						'key' 		=> 'hubwoo_ms_deal_id',
						'compare' 	=> 'NOT EXISTS',
					)
				),
		        'date_query' => array(
		            array(
						'after' 	=> date( 'd-m-Y', strtotime( $since_date ) ),
						'before'	=> date( 'd-m-Y', strtotime( $upto_date . ' +1 day' ) ),
						'inclusive'	=> true,
					)
		        ),
	    	)
    	);

    	$response = array( "status_code" => 400, "response" => __( "Details missing", "hubwoo" ) );

    	if ( !empty( $old_memberships ) ) {

    		foreach( $old_memberships as $key => $single_membership ) {

	    		if( !isset( $single_membership->ID ) || empty( $single_membership->ID ) ) {

	    			continue;
	    		}
	    		else {

	    			$membership_id = $single_membership->ID;
	    			if ( empty( $membership_id ) ) {
	    				continue;
	    			}
	    			$response = HubwooMembershipsCallbacks::get_instance()->create_membership_deal( $membership_id );
	    		}
	    	}
    	}

    	echo json_encode( $response );
		wp_die();
	}

	public function hubwoo_ms_create_deals ( $plan, $args ) {

		if ( !empty( $args['user_membership_id'] ) ) {

			HubwooMembershipsCallbacks::get_instance()->create_membership_deal( $args['user_membership_id'] );
		}
	}

	public function hubwoo_ms_update_deals ( $user_membership ) {

		if ( $user_membership ) {
			$membership_id = $user_membership->get_id();
			HubwooMembershipsCallbacks::get_instance()->create_membership_deal( $membership_id );
		}
	}

	public function hubwoo_ms_deals_notice () {

		if ( !empty( $_GET["page"] ) && ( "hubwoo_ms_deal" == $_GET["page"] ) )  {

			if( !Hubspot_Deals_For_Woocommerce_Memberships::hubwoo_ms_check_basic_setup() ) {

				$hubwoo_url = '<a href="' . admin_url( 'admin.php' ) . '?page=hubwoo">' . __( 'Here', 'hubwoo' ) . '</a>';
				$message = sprintf( __( 'You have not completed the basic authorization for main extension. Please complete it from %s to proceed with the deals add-on.', 'hubwoo' ), $hubwoo_url );

				Hubspot_Deals_For_Woocommerce_Memberships::hubwoo_ms_deals_notice( $message );
			}
		}
	}

	/**
	 * validating license key 
	 *
	 * @since    1.0.0
	 */

	public function hubwoo_ms_deals_validate_license_key() {

		$purchase_code = sanitize_text_field( $_POST["purchase_code"] );

	 	$api_params = array(
            'slm_action' 		=> 'slm_activate',
            'secret_key' 		=> HUBWOO_MS_DEAL_ACTIVATION_SECRET_KEY,
            'license_key' 		=> $purchase_code,
            'registered_domain' => $_SERVER['SERVER_NAME'],
            'item_reference' 	=> urlencode( HUBWOO_MS_DEAL_ITEM_REFERENCE ),
            'product_reference' => 'MWBPK-19341',
		);

		Hubspot_Deals_For_Woocommerce_Memberships::activate_license( $api_params );
	}

	/**
	 * checking license key
	 *
	 * @since    1.0.0
	 */

	public function hubwoo_ms_deals_check_licence_daily() {
		
		$license_key = get_option( 'hubwoo_ms_deals_license_key', "" );
		
		$api_params = array(

            'slm_action' 		=> 'slm_check',
            'secret_key' 		=> HUBWOO_MS_DEAL_ACTIVATION_SECRET_KEY,
            'license_key' 		=> $license_key,
            'registered_domain' => $_SERVER['SERVER_NAME'],
            'item_reference' 	=> urlencode(HUBWOO_MS_DEAL_ITEM_REFERENCE),
            'product_reference' => 'MWBPK-19341',
        );

		Hubspot_Deals_For_Woocommerce_Memberships::verify_license( $api_params );
	}
}