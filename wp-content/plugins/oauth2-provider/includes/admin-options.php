<?php

/**
 * WPOauth_Admin Class
 * Add admin functionkaity to the backend of WordPress
 */
class WPOAuth_Admin {
	
	
	/**
	 * WO Options Name
	 *
	 * @var string
	 */
	protected $option_name = 'wo_options';
	
	/**
	 * WP OAuth Server Admin Setup
	 *
	 * @return [type] [description]
	 */
	public static function init() {
		add_action( 'admin_init', array( new self(), 'admin_init' ) );
		add_action( 'admin_menu', array( new self(), 'add_page' ), 1 );
	}
	
	/**
	 * [admin_init description]
	 *
	 * @return [type] [description]
	 */
	public function admin_init() {
		register_setting( 'wo-options-group', $this->option_name );
		
		// New Pages Layout
		include_once dirname( __FILE__ ) . '/admin/pages/add-new-client.php';
		include_once dirname( __FILE__ ) . '/admin/pages/manage-clients.php';
		include_once dirname( __FILE__ ) . '/admin/pages/edit-client.php';
		
		include_once dirname( __FILE__ ) . '/admin/page-server-options.php';
		include_once dirname( __FILE__ ) . '/admin/page-server-status.php';
	}
	
	/**
	 * Updated the add_submenu_page to remove null and add bogus strings for deprecated on later PHP and WP versions
	 * @updated 4.3.4
	 */
	public function add_page() {
		add_menu_page( 'OAuth Server', 'OAuth Server', 'manage_options', 'wo_manage_clients', 'wo_admin_manage_clients_page', 'dashicons-groups', 70 );
		add_submenu_page( 'wo_manage_clients', 'Clients', __( 'Clients', 'wp-oauth' ), 'manage_options', 'wo_manage_clients', 'wo_admin_manage_clients_page' );
		add_submenu_page( 'wo_manage_clients', 'Settings', __( 'Settings', 'wp-oauth' ), 'manage_options', 'wo_settings', 'wo_server_options_page' );
		add_submenu_page( 'wo_manage_clients', 'Status', __( 'Status', 'wp-oauth' ), 'manage_options', 'wo_server_status', 'wo_server_status_page' );
		
		add_submenu_page( '--', 'Add Client', 'Add Client', 'manage_options', 'wo_add_client', 'wo_add_client_page' );
		add_submenu_page( '--', 'Edit Client', 'Edit Clients', 'manage_options', 'wo_edit_client', 'wo_admin_edit_client_page' );
	}
	
	/**
	 * WO options validation
	 *
	 * @param [type] $input [description]
	 *
	 * @return [type]        [description]
	 */
	public function validate_options( $input ) {
		return $input;
	}
}

WPOAuth_Admin::init();
