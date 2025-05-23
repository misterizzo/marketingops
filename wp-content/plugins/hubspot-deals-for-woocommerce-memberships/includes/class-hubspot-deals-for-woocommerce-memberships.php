<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://makewebbetter.com
 * @since      1.0.0
 *
 * @package    hubspot-deals-for-woocommerce-memberships
 * @subpackage hubspot-deals-for-woocommerce-memberships/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    hubspot-deals-for-woocommerce-memberships
 * @subpackage hubspot-deals-for-woocommerce-memberships/includes
 * @author     MakeWebBetter <webmaster@makewebbetter.com>
 */
class Hubspot_Deals_For_Woocommerce_Memberships {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Hubspot_Deals_For_Woocommerce_Memberships_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		if ( defined( 'HUBWOO_MS_DEAL_VERSION' ) ) {

			$this->version = HUBWOO_MS_DEAL_VERSION;
		}
		else {

			$this->version = '1.0.3';
		}

		$this->plugin_name = 'hubspot-deals-for-woocommerce-memberships';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Hubspot_Deals_For_Woocommerce_Memberships_Loader. Orchestrates the hooks of the plugin.
	 * - Hubspot_Deals_For_Woocommerce_Memberships_i18n. Defines internationalization functionality.
	 * - Hubspot_Deals_For_Woocommerce_Memberships_Admin. Defines all hooks for the admin area.
	 * - Hubspot_Deals_For_Woocommerce_Memberships_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubspot-deals-for-woocommerce-memberships-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubspot-deals-for-woocommerce-memberships-i18n.php';

		/**
		 * The class responsible for defining API connection functions
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubspot-deals-for-woocommerce-memberships-manager.php';


		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubspot-deals-for-woocommerce-memberships-callbacks.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-hubspot-deals-for-woocommerce-memberships-admin.php';

		$this->loader = new Hubspot_Deals_For_Woocommerce_Memberships_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Hubspot_Deals_For_Woocommerce_Memberships_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Hubspot_Deals_For_Woocommerce_Memberships_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Hubspot_Deals_For_Woocommerce_Memberships_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_ajax_hubwoo_ms_deals_check_oauth_access_token', $plugin_admin, 'hubwoo_ms_deals_check_oauth_access_token' );
		$this->loader->add_action( 'wp_ajax_hubwoo_ms_deals_get_pipeline', $plugin_admin, 'hubwoo_ms_deals_get_pipeline' );
		$this->loader->add_action( 'wp_ajax_hubwoo_ms_deals_create_pipeline', $plugin_admin, 'hubwoo_ms_deals_create_pipeline' );
		$this->loader->add_action( 'wp_ajax_hubwoo_ms_deals_get_groups', $plugin_admin, 'hubwoo_ms_deals_get_groups' );
		$this->loader->add_action( 'wp_ajax_hubwoo_ms_deals_create_group', $plugin_admin, 'hubwoo_ms_deals_create_group' );
		$this->loader->add_action( 'wp_ajax_hubwoo_ms_deals_get_group_properties', $plugin_admin, 'hubwoo_ms_deals_get_group_properties' );
		$this->loader->add_action( 'wp_ajax_hubwoo_ms_deals_create_property', $plugin_admin, 'hubwoo_ms_deals_create_property' );
		$this->loader->add_action( 'wp_ajax_hubwoo_ms_deals_setup_completed', $plugin_admin, 'hubwoo_ms_deals_setup_completed' );
		$this->loader->add_action( 'wp_ajax_hubwoo_ms_get_count', $plugin_admin, 'hubwoo_ms_get_count' );
		$this->loader->add_action( 'wp_ajax_hubwoo_ms_old_deals_sync', $plugin_admin, 'hubwoo_ms_old_deals_sync' );
		$this->loader->add_action( 'wp_ajax_hubwoo_ms_deals_validate_license_key', $plugin_admin, 'hubwoo_ms_deals_validate_license_key' );
		$this->loader->add_action( 'hubwoo_ms_deals_check_licence_daily', $plugin_admin, 'hubwoo_ms_deals_check_licence_daily' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'hubwoo_ms_deals_notice' );
		if ( "yes" == get_option( "hubwoo_ms_deals_settings_enable", "no" ) ) {
			$this->loader->add_action( 'wc_memberships_user_membership_saved', $plugin_admin, 'hubwoo_ms_create_deals', 20, 2 );
			$this->loader->add_action( 'wc_memberships_user_membership_created', $plugin_admin, 'hubwoo_ms_create_deals', 20, 2 );
			$this->loader->add_action( 'wc_memberships_user_membership_status_changed', $plugin_admin, 'hubwoo_ms_update_deals', 20, 1 );
			$this->loader->add_action( 'wc_memberships_user_membership_linked_to_subscription', $plugin_admin, 'hubwoo_ms_update_deals', 20, 1 );
			$this->loader->add_action( 'wc_memberships_user_membership_unlinked_from_subscription', $plugin_admin, 'hubwoo_ms_update_deals', 20, 1 );
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Hubspot_Deals_For_Woocommerce_Memberships_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	public static $hubwoo_ms_deals_lic_callback_function = 'hubwoo_ms_deals_key_validity';

	/*
		* check either the deal stages are mapped or not
	*/
	public static function hubwoo_ms_deals_check_mapped_stages () {

		if ( self::is_pipeline_setup_completed() ) {

			return true;
		}
		else {

			$mapped_stages = get_option( "hubwoo_ms_deal_mapped_stages", array() );

			if ( empty ( $mapped_stages ) ) {

				return false;
			}
			else {

				return true;
			}	
		}
	}

	/**
	 * check pipeline setup complete or not
	 *
	 * @since    1.0.0
	 */

	public static function is_pipeline_setup_completed() {

		return get_option( "hubwoo_ms_deals_pipeline_setup", false );
	}

	/**
	 * check deal whole setup complete or not
	 *
	 * @since    1.0.0
	 */

	public static function is_field_setup_completed() {

		return get_option( "hubwoo_ms_deals_setup", false );
	}

	/**
	 * displaying messages 
	 *
	 * @since    1.0.0
	 */

	public static function hubwoo_ms_deals_notice( $message, $type = 'error' ) {

		$classes = "notice ";

		switch ($type) {

			case 'update':
				$classes .= "updated";
				break;

			case 'update-nag':
				$classes .= "update-nag";
				break;
			case 'success':
				$classes .= "notice-success is-dismissible";
				break;

			default:
				$classes .= "error";
		} 

		$notice = '<div style="margin:10px" class="'. $classes .'">';
		$notice .= '<p>'. $message .'</p>';
		$notice .= '</div>';

		echo $notice;
	}

	/**
	 * check license is valid or not
	 *
	 * @since    1.0.0
	 */

	public static function hubwoo_ms_deals_key_validity() {

		return get_option( "hubwoo_ms_deals_valid_license", false );
	}

	/*
		* get deal stages for woocommerce membership pipeline
	*/

	public static function hubwoo_ms_probability_for_default() {

		$default_statuses_prob = array();

		$saved_mappings = get_option( "hubwoo_ms_deal_mapped_stages", array() );

		if( empty( $saved_mappings ) ) {

			$default_statuses_prob[] = array( 'status' => 'wcm-active', 'prob' => "1", 'label' => __( "Active", "hubwoo" ) );
			$default_statuses_prob[] = array( 'status' => 'wcm-free_trial', 'prob' => "1", 'label' => __( "Free Trial", "hubwoo" ) );
			$default_statuses_prob[] = array( 'status' => 'wcm-delayed', 'prob' => "0.5", 'label' => __( "Delayed", "hubwoo" ) );
			$default_statuses_prob[] = array( 'status' => 'wcm-complimentary', 'prob' => "1", 'label' => __( "Complimentary", "hubwoo" ) );
			$default_statuses_prob[] = array( 'status' => 'wcm-pending', 'prob' => "0.5", 'label' => __( "Pending", "hubwoo" ) );
			$default_statuses_prob[] = array( 'status' => 'wcm-paused', 'prob' => "0.5", 'label' => __( "Paused", "hubwoo" ) );
			$default_statuses_prob[] = array( 'status' => 'wcm-expired', 'prob' => "0.0", 'label' => __( "Expired", "hubwoo" ) );
			$default_statuses_prob[] = array( 'status' => 'wcm-cancelled', 'prob' => "0.0", 'label' => __( "Cancelled", "hubwoo" ) );
		} else {

			foreach ( $saved_mappings as $key => $single_deal_mapping ) {

				$default_statuses_prob[] = array( 'status' => $single_deal_mapping["stageId"], 'prob' => $single_deal_mapping["probability"], 'label' => $single_deal_mapping["label"] );
			}
		}

		return $default_statuses_prob;
	}

	/*
		* get the deal label for membership deal stages
	*/
	public static function get_the_deal_label( $key = '' ) {

		$default_prob = self::hubwoo_ms_probability_for_default();
		
		$deal_label = '';

		if ( !empty( $default_prob ) && !empty( $key ) ) {

			foreach ( $default_prob as $single_order_prob ) {

				if ( !empty( $single_order_prob["status"] ) && $key == $single_order_prob["status"] ) {

					$deal_label = $single_order_prob["label"];
				}
			}
		}

		return $deal_label;
	}


	/*
		get html for deal winning probabilities
	*/
	public static function hubwoo_ms_deals_selected_prob ( $deal_prob ) {

		$deal_probs = array( "0.1" => "10%", "0.2" => "20%", "0.3" => "30%", "0.4" => "40%", "0.5" => "50%", "0.6" => "60%", "0.7" => "70%", "0.8" => "80%", "0.9" => "90%", "1" => __( "Won", "hubwoo" ), "0.0" => __( "Lost", "hubwoo" ) );

		$html = '<select name="hubwoo_ms_deal_prob[][]">';

		foreach ( $deal_probs as $prob_key => $prob_label ) {

			if ( $prob_key == $deal_prob ) {

				$html .= '<option value="' . $prob_key . '" selected>' . $prob_label . '</option>';
			}
			else {

				$html .= '<option value="' . $prob_key . '">' . $prob_label . '</option>';
			}
		}

		$html .= '</select>';

		return $html;
	}

	/*
		* get appropriate select html for deal stage probability
	*/
	public static function get_the_deal_probability( $key = '' ) {

		$default_prob = self::hubwoo_ms_probability_for_default();

		$deal_prob = '';

		if ( !empty( $default_prob ) && !empty( $key ) ) {

			foreach ( $default_prob as $single_prob ) {

				if ( !empty( $single_prob["status"] ) && $key == $single_prob["status"] ) {

					$deal_prob = $single_prob["prob"];
				}
			}
		}

		if ( empty( $deal_prob ) ) {

			$deal_prob = "0.0";
		}

		$deal_prob_html = '';

		$deal_prob_html = self::hubwoo_ms_deals_selected_prob( $deal_prob );

		return $deal_prob_html;
	}

	/**
	 * checking whether basic setup of hubspot main extension is done or not
	 *
	 * @since 1.0.0
	 */

	public static function hubwoo_ms_check_basic_setup() {

		$hubwoo_basic_setup = false;

		if( in_array( 'hubspot-woocommerce-integration-pro/hubspot-woocommerce-integration-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			
			$hubwoo_basic_setup = get_option( 'hubwoo_pro_valid_client_ids_stored', false );
		}
		elseif( in_array( 'hubspot-woocommerce-integration-starter/hubspot-woocommerce-integration-starter.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

			$hubwoo_basic_setup = get_option( 'hubwoo_starter_valid_client_ids_stored', false );
		}
		elseif( in_array( 'hubspot-woocommerce-integration-complimentary/hubspot-woocommerce-integration-complimetary.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			
			$hubwoo_basic_setup = get_option( 'hubwoo_comp_valid_client_ids_stored', false );
		}
		elseif( in_array( 'hubwoo-integration/hubspot-woocommerce-integration.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			
			$hubwoo_basic_setup = get_option( 'hubwoo_valid_client_ids_stored', false );
		}
		elseif( in_array( 'makewebbetter-hubspot-for-woocommerce/makewebbetter-hubspot-for-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			
			$hubwoo_basic_setup = true;
		}
		elseif( in_array( 'hubspot-for-woocommerce/hubspot-for-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			
			$hubwoo_basic_setup = true;
		}
		return $hubwoo_basic_setup;
	}

	/**
	 * get pipeline id
	 *
	 * @since    1.0.0
	 */

	public static function hubwoo_ms_deals_get_pipeline_id() {

		return get_option( "hubwoo_ms_deals_pipeline_id", "" );
	}

	/**
	 * check whether the hubspot access token is valid or expired
	 *
	 * @since    1.0.0
	 */
	public static function is_access_token_expired () {

		$get_expiry = '';
	
		if( in_array( 'hubspot-woocommerce-integration-pro/hubspot-woocommerce-integration-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			
			$get_expiry = get_option( 'hubwoo_pro_token_expiry', false );
		}
		elseif( in_array( 'hubspot-woocommerce-integration-starter/hubspot-woocommerce-integration-starter.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

			$get_expiry = get_option( 'hubwoo_starter_token_expiry', false );
		}
		elseif( in_array( 'hubspot-woocommerce-integration-complimentary/hubspot-woocommerce-integration-complimetary.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			
			$get_expiry = get_option( 'hubwoo_comp_token_expiry', false );
		}
		elseif( in_array( 'hubwoo-integration/hubspot-woocommerce-integration.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			
			$get_expiry = get_option( 'hubwoo_token_expiry', false );
		}
		elseif( in_array( 'hubspot-for-woocommerce/hubspot-for-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			
			$get_expiry = get_option( 'hubwoo_pro_token_expiry', false );
		}
		elseif( in_array( 'makewebbetter-hubspot-for-woocommerce/makewebbetter-hubspot-for-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			
			$get_expiry = get_option( 'hubwoo_pro_token_expiry', false );
		}		
		if( $get_expiry ) {

			$current_time = time();

			if( ( $get_expiry > $current_time ) && ( $get_expiry - $current_time ) <= 50 ) {

				return true;
			}
			elseif ( ( $current_time > $get_expiry ) ) {

				return true;
			}
		}

		return false;
	}

	/**
	 * activate the license key.
	 *
	 * @since 1.0.0
	 */

	public static function activate_license( $params = array() ) {

		$query = esc_url_raw( add_query_arg( $params, HUBWOO_MS_DEAL_LICENSE_SERVER_URL ) );
		
        $response = wp_remote_get( $query, array( 'timeout' => 20, 'sslverify' => false ) );

        if ( is_wp_error( $response ) ) {

        	self::hubwoo_ms_deals_notice( __('An unexpected error occured. Please try again later', 'hubwoo' ), 'error' );
        }
        else {

	        $license_data = json_decode( wp_remote_retrieve_body( $response ) );

	        if( isset( $license_data->result ) && $license_data->result == 'success' ) {

	            update_option( 'hubwoo_ms_deals_valid_license', true ); 
	            update_option( 'hubwoo_ms_deals_license_key', $params['license_key'] );
	            echo json_encode( array( 'status' => true, 'msg' => __( 'Successfully Verified. Please Wait.', 'hubwoo' ) ) ); 
	        }
	        else {

				echo json_encode( array( 'status' => false, 'msg' => $license_data->message ) );
	        }
	    }

	    wp_die();
	}

	/**
	 * verify the license key.
	 *
	 * @since 1.0.0
	 */
	
	public static function verify_license( $api_params = array() ) {

		$query = esc_url_raw( add_query_arg( $api_params, HUBWOO_MS_DEAL_LICENSE_SERVER_URL ) );
		
        $response = wp_remote_get( $query, array( 'timeout' => 20, 'sslverify' => false ) );

        if ( is_wp_error( $response ) ) {

            return;
        }
        else {

	        $license_data = json_decode( wp_remote_retrieve_body( $response ) );
	      	
	        if( isset( $license_data->result ) &&  $license_data->result == 'success' ) {

	            if ( isset( $license_data->status ) &&  $license_data->status == 'active' ) {

	            	update_option( 'hubwoo_ms_deals_valid_license', true );
	            }
	            else {

	            	delete_option( 'hubwoo_ms_deals_valid_license' );
	            }
	        }
	        elseif( isset( $license_data->result ) && $license_data->result == 'error' && isset( $license_data->error_code ) && $license_data->error_code == 60 ) {

	        	delete_option( 'hubwoo_ms_deals_valid_license' );
	        }
	    }
	}
}