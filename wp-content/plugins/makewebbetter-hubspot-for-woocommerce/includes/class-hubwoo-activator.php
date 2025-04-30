<?php
/**
 * Fired during plugin activation
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    makewebbetter-hubspot-for-woocommerce
 * @subpackage makewebbetter-hubspot-for-woocommerce/includes
 */

if ( ! class_exists( 'Hubwoo_Activator' ) ) {

	/**
	 * Fired during plugin activation.
	 *
	 * This class defines all code necessary to run during the plugin's activation.
	 *
	 * @since      1.0.0
	 * @package    makewebbetter-hubspot-for-woocommerce
	 * @subpackage makewebbetter-hubspot-for-woocommerce/includes
	 */
	class Hubwoo_Activator {

		/**
		 * Schedule the realtime sync for HubSpot WooCommerce Integration
		 *
		 * Create a log file in the WooCommerce defined log directory
		 * and use the same for the logging purpose of our plugin.
		 *
		 * @since    1.0.0
		 */
		public static function activate() {

			update_option( 'hubwoo_plugin_activated_time', time() );

			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			
			global $wp_filesystem;
			
			if ( ! is_object( $wp_filesystem ) ) {
				WP_Filesystem();
			}
			
			$log_file = WC_LOG_DIR . 'hubspot-for-woocommerce-logs.log';
			
			if ( ! $wp_filesystem->exists( $log_file ) ) {
				$wp_filesystem->put_contents( $log_file, '', FS_CHMOD_FILE );
			}
			

			if ( ! as_next_scheduled_action( 'hubwoo_cron_schedule' ) ) {

				as_schedule_recurring_action( time(), 300, 'hubwoo_cron_schedule' );
			}

			if ( ! as_next_scheduled_action( 'hubwoo_deals_sync_check' ) ) {

				as_schedule_recurring_action( time(), 300, 'hubwoo_deals_sync_check' );
			}

			if ( ! as_next_scheduled_action( 'hubwoo_ecomm_deal_update' ) ) {

				as_schedule_recurring_action( time(), 180, 'hubwoo_ecomm_deal_update' );
			}

			if ( ! as_next_scheduled_action( 'hubwoo_products_sync_check' ) ) {

				as_schedule_recurring_action( time(), 300, 'hubwoo_products_sync_check' );
			}

			if ( ! as_next_scheduled_action( 'hubwoo_check_logs' ) ) {

				as_schedule_recurring_action( time(), 86400, 'hubwoo_check_logs' );
			}

			if ( ! as_next_scheduled_action( 'huwoo_abncart_clear_old_cart' ) ) {

				as_schedule_recurring_action( time(), 86400, 'huwoo_abncart_clear_old_cart' );
			}

			// Create log table in database.
			Hubwoo::hubwoo_create_log_table( Hubwoo::get_current_crm_name( 'slug' ) );
		}

	}
}
