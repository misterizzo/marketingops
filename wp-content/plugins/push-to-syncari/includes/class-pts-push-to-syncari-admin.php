<?php
/**
 * The file that defines the core plugin class.
 *
 * A class definition that holds all the hooks regarding all the custom functionalities.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Pts_Push_To_Syncari
 * @subpackage Pts_Push_To_Syncari/includes
 */

/**
 * The core plugin class.
 *
 * A class definition that holds all the hooks regarding all the custom functionalities.
 *
 * @since      1.0.0
 * @package    Pts_Push_To_Syncari
 * @author     Adarsh Verma <adarsh.srmcem@gmail.com>
 */
class Pts_Push_To_Syncari_Admin {
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Load all the admin hooks here.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'pts_admin_enqueue_scripts_callback' ) );
		add_action( 'admin_head-users.php', array( $this, 'pts_admin_head_users_php_callback' ) );
		add_action( 'wp_ajax_push_users_to_syncari', array( $this, 'pts_push_users_to_syncari_callback' ) );
		// add_action( 'admin_init', array( $this, 'pts_admin_init_callback' ) );
	}

	/**
	 * Enqueue scripts for admin end.
	 */
	public function pts_admin_enqueue_scripts_callback() {
		// Custom admin script.
		wp_enqueue_script(
			'push-to-syncari-admin-script',
			PTS_PLUGIN_URL . 'assets/admin/js/push-to-syncari-admin.js',
			array( 'jquery' ),
			filemtime( PTS_PLUGIN_PATH . 'assets/admin/js/push-to-syncari-admin.js' ),
			true
		);

		// Custom admin style.
		wp_enqueue_script(
			'push-to-syncari-admin-style',
			PTS_PLUGIN_URL . 'assets/admin/css/push-to-syncari-admin.css',
			'',
			filemtime( PTS_PLUGIN_PATH . 'assets/admin/css/push-to-syncari-admin.css' ),
		);

		// Localize admin script.
		wp_localize_script(
			'push-to-syncari-admin-script',
			'PTS_Admin_JS_Obj',
			array(
				'ajaxurl'           => admin_url( 'admin-ajax.php' ),
				'push_data_confirm' => __( 'Are you sure to push the users info to syncari data table. This action won\'t be reverted.', 'push-to-syncari' ),
				'start_pushing'     => __( 'Please wait while we start pushing users information.', 'push-to-syncari' ),
			)
		);
	}

	/**
	 * Add custom call to actions on the users page.
	 */
	public function pts_admin_head_users_php_callback() {
		ob_start();
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function( $ ) {
				jQuery( '<a href="javascript:void(0);" id="push_users_to_syncari" class="page-title-action"><?php esc_html_e( 'Push to Syncari', 'push-to-syncari' ); ?></a>' ).insertAfter( '.wrap .page-title-action' );
			} );
		</script>
		<?php

		echo ob_get_clean();
	}

	/**
	 * Ajax to push the users data to syncari table.
	 *
	 * @since 1.0.0
	 */
	public function pts_push_users_to_syncari_callback() {
		set_time_limit(0); // Set a holdback so the query doesn't crash.
		$page        = (int) filter_input( INPUT_POST, 'page', FILTER_SANITIZE_NUMBER_INT );
		$per_page    = (int) filter_input( INPUT_POST, 'per_page', FILTER_SANITIZE_NUMBER_INT );
		$users_query = pts_get_users( $page, $per_page );
		$user_ids    = $users_query->get_results();

		// Get the total users count.
		$all_users_query = pts_get_users();
		$all_user_ids    = $all_users_query->get_results();

		// Complete the push task, if empty response received.
		if ( empty( $user_ids ) ) {
			$response = array(
				'code'               => 'users-sync-completed',
				'progress_note_text' => sprintf( __( '%1$d users are pushed successfully successfully. Reload to start fresh sync.', 'push-to-syncari' ), count( $all_user_ids ) ),
			);
			wp_send_json_success( $response );
			wp_die();
		}

		// loop through the user ids to push them to the syncari table.
		foreach ( $user_ids as $user_id ) {
			pts_push_user( $user_id );
		}

		// Send the final response.
		$response = array(
			'code'               => 'users-sync-in-process',
			'progress_note_text' => sprintf( __( '%1$d users out of %2$d pushed.. Rest users are in process...', 'push-to-syncari' ), ( $per_page * $page ), count( $all_user_ids ) ),
		);
		wp_send_json_success( $response );
		wp_die();
	}

	/**
	 * Do some action.
	 */
	public function pts_admin_init_callback() {
		// Return if the user is not from the following IP.
		if ( '183.82.161.194' !== $_SERVER['REMOTE_ADDR'] ) {
			return;
		}

		// $this->pts_remove_unexisting_users_from_db();
		$this->pts_check_user_data();
	}

	private function pts_check_user_data() {
		$args        = array(
			'search' => 'developer@marketingops.com',
			'fields' => 'ids',
		);
		$users_query = new WP_User_Query( $args );
		$user_ids    = $users_query->get_results();

		// Loop through the user IDs.
		if ( ! empty( $user_ids ) && is_array( $user_ids ) ) {
			foreach ( $user_ids as $user_id ) {
				$user_certificates = pts_get_selected_certifications( $user_id );

				echo maybe_serialize( $user_certificates );
				die;

				debug( $user_certificates );
				die;
			}
		}
	}

	/**
	 * Remove the non-existing users from the syncari table.
	 */
	private function pts_remove_unexisting_users_from_db() {
		global $wpdb;

		// Prepare the SQL query for fetching the information from the syncari table.
		$syncari_table   = $wpdb->prefix . 'syncari_data';
		$syncari_db_data = $wpdb->get_results( "SELECT `user_ID` FROM `{$syncari_table}`", ARRAY_A );

		// Return, if there is no data.
		if ( empty( $syncari_db_data ) ) {
			return;
		}

		// Loop through the syncari data.
		foreach ( $syncari_db_data as $syncaridata ) {
			// If the user ID is available and the user data is available.
			if ( ! empty( $syncaridata['user_ID'] ) ) {

				if ( false === get_userdata( $syncaridata['user_ID'] ) ) {
					$wpdb->delete( $syncari_table, array( 'user_ID' => $syncaridata['user_ID'] ) );
				}
			}
		}
	}
}