<?php

/*
* plugin auto-update script
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( ! class_exists( 'Mwb_Hubwoo_Ftf_Update' ) ) {

	class Mwb_Hubwoo_Ftf_Update {

		public function __construct() {

			register_activation_hook( HUBWOO_FTF_BASE_FILE, array( $this, 'mwb_hubwoo_ftf_check_activation' ) );
			add_action( 'install_plugins_pre_plugin-information', array( $this, 'mwb_hubwoo_ftf_plugin_details' ) );
			add_action( 'mwb_hubwoo_ftf_check_event', array( $this, 'mwb_hubwoo_ftf_check_update'));
			add_filter( 'http_request_args', array( $this, 'mwb_hubwoo_ftf_updates_exclude' ), 5, 2 );
			register_deactivation_hook( HUBWOO_FTF_BASE_FILE, array( $this, 'mwb_hubwoo_ftf_check_deactivation' ) );
		}	

		//clearing update check event on plugin deactivation 
		public function mwb_hubwoo_ftf_check_deactivation() {

			wp_clear_scheduled_hook( 'mwb_hubwoo_ftf_check_event' );
		}

		//scheduling event for version check on server daily
		public function mwb_hubwoo_ftf_check_activation() {

			wp_schedule_event( time(), 'daily', 'mwb_hubwoo_ftf_check_event' );
		}

		// setting version details on new version update
		public function mwb_hubwoo_ftf_plugin_details() {

			global $tab;

			if( $tab == 'plugin-information' && $_REQUEST['plugin'] == 'hubspot-field-to-field-sync' ) {
				
				$url = 'https://makewebbetter.com/pluginupdates/hubspot-field-to-field-sync/hubspot-field-to-field-sync.json';

				$postdata = array(
					'action' => 'check_update',
					'license_code' => HUBWOO_FTF_LICENSE_KEY
				);
				
				$args = array(
					'method' => 'POST',
					'body' => $postdata,
				);
				
				$data = wp_remote_post( $url, $args );

				if ( is_wp_error( $data ) || empty( $data['body'] ) ) {

					return;

				}
				
				if( isset( $data['body'] ) ) {

					$all_data = json_decode( $data['body'], true );

					if( is_array( $all_data ) && !empty( $all_data ) ) {

						$this->mwb_hubwoo_ftf_create_html( $all_data );

					}
				}
			}
		}

		//checking for new version on server for the plugin
		public function mwb_hubwoo_ftf_check_update() {

			global $wp_version;
			global $hubwoo_ftf_update_check;
			
			$plugin_folder = plugin_basename( dirname( HUBWOO_FTF_BASE_FILE ) );
			$plugin_file = basename( ( HUBWOO_FTF_BASE_FILE ) );

			if ( defined( 'WP_INSTALLING' ) ) {

				return false;
			} 

			$postdata = array(
				'action' 		=> 'check_update',
				'license_key' 	=> HUBWOO_FTF_LICENSE_KEY
			);
			
			$args = array(
				'method' 		=> 'POST',
				'body' 			=> $postdata,
			);
			
			$response = wp_remote_post( $hubwoo_ftf_update_check, $args );

			if( is_wp_error( $response ) || !isset( $response['body'] ) || empty( $response['body'] ) ) {

				return false;
			}
			
			list( $version, $url ) = explode('~', $response['body']);
			
			if( empty( $version ) || ( $this->mwb_plugin_get("Version") >= $version ) ) {

				return false;
			}
			
			$plugin_transient = get_site_transient('update_plugins');

			$a = array(
				'slug' 			=> $plugin_folder,
				'new_version' 	=> $version,
				'url' 			=> $this->mwb_plugin_get("AuthorURI"),
				'package' 		=> $url
			);
			
			$o = (object) $a;
			$plugin_transient->response[$plugin_folder.'/'.$plugin_file] = $o;
			set_site_transient( 'update_plugins', $plugin_transient );
		}


		//creating html to show changelogs details on new version
		public function mwb_hubwoo_ftf_create_html( $all_data ) {
			?>
				<style>	
					#TB_window{
						top : 4% !important;
					}
					.mwb_hubwoo_ftf_banner {
						text-align: center;
					}
					.mwb_hubwoo_ftf_description > h4 {
						background-color: #3779B5;
						padding: 5px;
						color: #ffffff;
						border-radius: 5px;
					}
					.mwb_hubwoo_ftf_changelog_details > h4 {
						background-color: #3779B5;
						padding: 5px;
						color: #ffffff;
						border-radius: 5px;
					}
				</style>
				<div class="mwb_hubwoo_ftf_details_wrapper">
					<div class="mwb_hubwoo_ftf_name">
						<center><h1><?php echo $all_data['name'] ?></h1></center>
					</div>
					<div class="mwb_hubwoo_ftf_banner">
						<img src="<?php echo $all_data['banners']['low'];?>">	
					</div>
					<div class="mwb_hubwoo_ftf_description">
						<h4><?php _e('Plugin Description','hubwoo'); ?></h4>
						<span><?php echo $all_data['sections']['description']; ?></span>
					</div>
					<div class="mwb_hubwoo_ftf_changelog_details">
						<h4><?php _e('Plugin Change Log','hubwoo'); ?></h4>
						<span><?php echo $all_data['sections']['changelog']; ?></span>
					</div> 
				</div>
			<?php
		}

		//filtering plugins to exlude for updates
		public function mwb_hubwoo_ftf_updates_exclude( $r, $url ) {

			if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) ) {

				return $r; 
			}

			$plugins = unserialize( $r['body']['plugins'] );
			unset( $plugins->plugins[ plugin_basename( __FILE__ ) ] );
			if ( !empty( $plugins->active ) ) {
				unset( $plugins->active[ array_search( plugin_basename( __FILE__ ), $plugins->active ) ] );
			}
			$r['body']['plugins'] = serialize( $plugins );
			return $r;
		}

		//Returns current plugin info.
		public function mwb_plugin_get($i) {
			
			if ( ! function_exists( 'get_plugins' ) ) {

				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
				
			$plugin_folder = get_plugins( '/' . plugin_basename( dirname( HUBWOO_FTF_BASE_FILE ) ) );
			$plugin_file = basename( ( HUBWOO_FTF_BASE_FILE ) );
			return $plugin_folder[$plugin_file][$i];
		}
	}
	
	new Mwb_Hubwoo_Ftf_Update();
}		
?>