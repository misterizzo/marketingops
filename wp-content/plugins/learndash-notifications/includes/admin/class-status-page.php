<?php

if ( class_exists( 'LearnDash_Settings_Page' ) ) :

class LearnDash_Notifications_Status_Page extends LearnDash_Settings_Page
{

	public function __construct() 
	{	
		$this->parent_menu_page_url		=	'edit.php?post_type=ld-notification';
		$this->menu_page_capability		=	LEARNDASH_ADMIN_CAPABILITY_CHECK;
		$this->settings_page_id 		= 	'ld-notifications-status';
		$this->settings_page_title 		= 	__( 'LearnDash Notifications Status', 'learndash-notifications' );
		$this->settings_tab_title		=	__( 'Status', 'learndash-notifications' );
		$this->settings_tab_priority	=	3;
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_tools_scripts' ) );
		$this->show_settings_page_function	=	array( $this, 'show_settings_page' );
			
		parent::__construct(); 
	}

	public function show_settings_page()
	{
		global $wpdb;
		$values = get_option( 'learndash_notifications_status', array() );
		include_once LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'templates/admin/status-page.php';
	}

	public function enqueue_tools_scripts() {
		$screen = get_current_screen();

		if ( $screen->id != 'admin_page_ld-notifications-status' ) {
			return;
		}

		wp_enqueue_script( 'learndash_notifications_tools_scripts', LEARNDASH_NOTIFICATIONS_PLUGIN_URL . 'assets/js/tools.js', array( 'jquery' ), LEARNDASH_NOTIFICATIONS_VERSION, false );

		wp_enqueue_style( 'learndash_notifications_tools_styles', LEARNDASH_NOTIFICATIONS_PLUGIN_URL . 'assets/css/tools.css', array(), LEARNDASH_NOTIFICATIONS_VERSION, 'all' );

		wp_localize_script( 'learndash_notifications_tools_scripts', 'LD_Notifications_Tools_Params', array(
			'text' => array(
				'status'             => __( 'Status', 'learndash-notifications' ),
				'complete'           => __( 'Complete', 'learndash-notifications' ),
				'keep_page_open'     => __( 'Please keep this page open until the process is complete.', 'learndash-notifications' ),
				'button' => __( 'Run', 'learndash-notifications' ),
				'confirm' => array(
					'empty_db_table' => __( 'Are you sure you want to empty scheduled notifications table? You can NOT undo this process. Please back up your database first!', 'learndash-notifications' )
				)
			),
			'nonce' => array(
				'fix_recipient' => wp_create_nonce( 'ld_notifications_fix_recipients' ),
			),
		) );
	}
}

add_action( 'learndash_settings_pages_init', function() {
	LearnDash_Notifications_Status_Page::add_page_instance();
} );

endif;