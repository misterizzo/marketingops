<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-field-to-field-sync
 * @subpackage hubspot-field-to-field-sync/includes
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
 * @package    hubspot-field-to-field-sync
 * @subpackage hubspot-field-to-field-sync/includes
 * @author     MakeWebBetter <webmaster@makewebbetter.com>
 */
class Hubspot_Field_To_Field_Sync {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Hubspot_Field_To_Field_Sync_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		
		if( defined( HUBWOO_FTF_VERSION ) ) {

			$this->version = HUBWOO_FTF_VERSION;
		}
		else {

			$this->version = '1.0.7';
		}
		
		$this->plugin_name = 'hubspot-field-to-field-sync';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Hubspot_Field_To_Field_Sync_Loader. Orchestrates the hooks of the plugin.
	 * - Hubspot_Field_To_Field_Sync_i18n. Defines internationalization functionality.
	 * - Hubspot_Field_To_Field_Sync_Admin. Defines all hooks for the admin area.
	 * - Hubspot_Field_To_Field_Sync_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubspot-field-to-field-sync-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubspot-field-to-field-sync-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-hubspot-field-to-field-sync-admin.php';

		$this->loader = new Hubspot_Field_To_Field_Sync_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Hubspot_Field_To_Field_Sync_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Hubspot_Field_To_Field_Sync_i18n();

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

		$plugin_admin = new Hubspot_Field_To_Field_Sync_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'wp_ajax_hubwoo_ftf_new_row', $plugin_admin, 'hubwoo_ftf_new_row' );
		$this->loader->add_action( 'hubwoo_ftf_check_licence_daily', $plugin_admin, 'hubwoo_ftf_check_licence_daily' );

		$this->loader->add_action( 'wp_ajax_hubwoo_ftf_validate_license_key', $plugin_admin, 'hubwoo_ftf_validate_license_key' ); 

		$hubwoo_ftf_callname_lic = self::$hubwoo_ftf_lic_callback_function;

		if( self::$hubwoo_ftf_callname_lic() ) {

			$this->loader->add_filter( 'hubwoo_map_new_properties', $plugin_admin, 'hubwoo_ftf_mapping_new_properties', 10, 2 );
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
	 * @return    Hubspot_Field_To_Field_Sync_Loader    Orchestrates the hooks of the plugin.
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

	public static $hubwoo_ftf_lic_callback_function = 'hubwoo_ftf_customer_key_validity';

	/**
	 * Retrieve all the user fields
	 *
	 * @since     1.0.0
	 * @return    string    unique user fields for mapping
	 */
	public function hubwoo_ftf_get_all_user_fields() {

		global $wpdb;

		$query = "SELECT DISTINCT `meta_key` FROM `{$wpdb->prefix}usermeta`";
		$row = $wpdb->get_results( $query );

		$user_meta = array();

		if ( !empty( $row ) ) {

			foreach ( $row as $single_row ) {

				if ( !empty( $single_row->meta_key ) ) {

					$user_meta[] = $single_row->meta_key;
				}
			}
		}

		return $user_meta;
    }

    /**
	 * checks for license validation
	 *
	 * @since     1.0.0
	 * @return    bool    true/false for license check
	 */

    public static function hubwoo_ftf_customer_key_validity() {

    	return get_option( "hubwoo_ftf_license_check", false );
    }

    /**
	 * displaying messages 
	 *
	 * @since    1.0.0
	 */

    public static function hubwoo_ftf_notice( $message, $type = 'error' ){

		$classes = "notice ";

		switch($type){

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
}