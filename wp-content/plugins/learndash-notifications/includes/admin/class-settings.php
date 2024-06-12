<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * LearnDash_Notifications_Settings class
 *
 * This class is responsible for managing plugin settings.
 *
 * @since 1.0
 */
class LearnDash_Notifications_Settings {

	/**
	 * Plugin options
	 *
	 * @since 1.0
	 * @var array
	 */
	protected $options;

	/**
	 * Class __construct function
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->options = get_option( 'learndash_notifications_settings', array() );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'learndash_submenu', array( $this, 'submenu_item' ) );
		add_action( 'learndash_admin_tabs_set', array( $this, 'admin_tabs_set' ), 10, 2 );
	}

	/**
	 * Add zapier submenu in Learndash menu
	 *
	 * @param array $submenu Existing submenu
	 *
	 * @return array           New submenu
	 */
	public function submenu_item( $submenu ) {
		$menu = array(
			'ld-notifications' => array(
				'name' => __( 'Notifications', 'learndash-notifications' ),
				'cap'  => 'manage_options', // @TODO Need to confirm this capability on the menu.
				'link' => 'edit.php?post_type=ld-notification',
			)
		);

		array_splice( $submenu, 9, 0, $menu );

		return $submenu;
	}

	/**
	 * Add tabs to achievement pages
	 *
	 * @param string $current_screen_parent_file Current screen parent
	 * @param object $tabs Learndash_Admin_Menus_Tabs object
	 */
	public function admin_tabs_set( $current_screen_parent_file, $tabs ) {
		$screen = get_current_screen();

		if ( $current_screen_parent_file == 'learndash-lms' && $screen->post_type == 'ld-notification' ) {
			$tabs->add_admin_tab_item(
				$current_screen_parent_file,
				array(
					'link' => 'post-new.php?post_type=ld-notification',
					'name' => __( 'Add New', 'learndash-notifications' ),
					'id'   => 'ld-notification',
				),
				1
			);

			$tabs->add_admin_tab_item(
				$current_screen_parent_file,
				array(
					'link' => 'edit.php?post_type=ld-notification',
					'name' => __( 'Notifications', 'learndash-notifications' ),
					'id'   => 'edit-ld-notification',
				),
				2
			);

			$tabs->add_admin_tab_item(
				$current_screen_parent_file,
				array(
					'link' => 'admin.php?page=ld-notifications-status',
					'name' => __( 'Status', 'learndash-notifications' ),
					'id'   => 'ld-notifications-settings',
				),
				3
			);
//			$tabs->add_admin_tab_item(
//				$current_screen_parent_file,
//				array(
//					'link' => 'admin.php?page=ld-notifications-emails-queued',
//					'name' => __( 'Emails Queued', 'learndash-notifications' ),
//					'id'   => 'ld-notifications-settings',
//				),
//				4
//			);
			$tabs->add_admin_tab_item(
				$current_screen_parent_file,
				array(
					'link' => 'admin.php?page=ld-notifications-logs',
					'name' => __( 'Logs', 'learndash-notifications' ),
					'id'   => 'ld-notifications-settings',
				),
				5
			);

		} elseif ( $current_screen_parent_file == 'edit.php?post_type=ld-notification' ) {
			$tabs->add_admin_tab_item(
				$current_screen_parent_file,
				array(
					'link' => 'post-new.php?post_type=ld-notification',
					'name' => __( 'Add New', 'learndash-notifications' ),
					'id'   => 'ld-notification',
				),
				1
			);

			$tabs->add_admin_tab_item(
				$current_screen_parent_file,
				array(
					'link' => 'edit.php?post_type=ld-notification',
					'name' => __( 'Notifications', 'learndash-notifications' ),
					'id'   => 'edit-ld-notification',
				),
				2
			);
		}
	}

	/**
	 * Enqueue admin scripts and styles
	 */
	public function enqueue_scripts() {
		global $post_type;

		if ( ! is_admin() || 'ld-notification' != $post_type ) {
			return;
		}

		wp_enqueue_script( 'learndash_notifications_admin_scripts', LEARNDASH_NOTIFICATIONS_PLUGIN_URL . 'assets/js/admin-scripts.js', array( 'jquery' ), LEARNDASH_NOTIFICATIONS_VERSION, false );

		wp_enqueue_style( 'learndash_notifications_admin_styles', LEARNDASH_NOTIFICATIONS_PLUGIN_URL . 'assets/css/admin-styles.css', array(), LEARNDASH_NOTIFICATIONS_VERSION );

		wp_enqueue_style( 'learndash_style', plugins_url() . '/sfwd-lms/assets/css/style.css' );

		wp_enqueue_style( 'sfwd-module-style', plugins_url() . '/sfwd-lms/assets/css/sfwd_module.css' );

		wp_enqueue_script( 'sfwd-module-script', plugins_url() . '/sfwd-lms/assets/js/sfwd_module.js', array( 'jquery' ) );

		wp_localize_script( 'learndash_notifications_admin_scripts', 'LD_Notifications_String', array(
			'nonce'               => wp_create_nonce( 'ld_notifications_nonce' ),
			'select_lesson'       => __( '-- Select Lesson --', 'learndash-notifications' ),
			'select_topic'        => __( '-- Select Topic --', 'learndash-notifications' ),
			'select_quiz'         => __( '-- Select Quiz --', 'learndash-notifications' ),
			'select_course_first' => __( '-- Select Course First --', 'learndash-notifications' ),
			'select_lesson_first' => __( '-- Select Lesson First --', 'learndash-notifications' ),
			'select_topic_first'  => __( '-- Select Topic First --', 'learndash-notifications' ),
			'select_quiz_first'   => __( '-- Select Quiz First --', 'learndash-notifications' ),
			'all_lessons'         => __( 'Any Lesson', 'learndash-notifications' ),
			'all_topics'          => __( 'Any Topic', 'learndash-notifications' ),
			'all_quizzes'         => __( 'Any Quiz', 'learndash-notifications' ),
		) );
		wp_localize_script( 'sfwd-module-script', 'sfwd_data', array() );

		wp_dequeue_script( 'autosave' );
	}
}

new LearnDash_Notifications_Settings();