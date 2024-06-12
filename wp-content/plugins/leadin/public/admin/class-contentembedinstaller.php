<?php

namespace Leadin\admin;

use Leadin\utils\QueryParameters;



/**
 * Class used to install the Content embed plugin
 */
class ContentEmbedInstaller {

	const CONTENT_EMBED_LINK = 'https://api.hubapi.com/content-embed/v1/plugin/download/content-hub-embed.zip';
	const INSTALL_ARG        = 'contentembed_install';
	const INSTALL_OPTION     = LEADIN_PREFIX . '_content_embed_ui_install';

	/**
	 * Class constructor, adds the necessary hooks.
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'wp_ajax_content_embed_install', array( $this, 'wp_ajax_install_content_embed' ) );
		}
	}


	/**
	 * AJAX to install Content embed plugin by downloading from URL in Content embed API.
	 * Modified from: https://github.com/WordPress/wordpress-develop/blob/trunk/src/wp-admin/includes/ajax-actions.php#L4444
	 */
	public function wp_ajax_install_content_embed() {
		$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( ( $_REQUEST['_wpnonce'] ) ) ) : '';

		if ( ! current_user_can( 'install_plugins' ) || ! wp_verify_nonce( $nonce, self::INSTALL_ARG ) ) {
			$status['errorCode']    = 'PERMISSIONS_ERROR';
			$status['errorMessage'] = 'User does not have permission to install or activate plugins.';
			return wp_send_json_error( $status, 403 );
		}

		$active_or_installed = self::is_content_embed_active_installed();
		$activated           = $active_or_installed['active'];
		$installed           = $active_or_installed['installed'];

		if ( $activated || $installed ) {
			$status['errorCode']    = $activated ? 'ALREADY_ACTIVATED' : 'ALREADY_INSTALLED';
			$status['errorMessage'] = 'Content embed already installed or activated';
			wp_send_json_error( $status );
		}

		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$skin     = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );
		$result   = $upgrader->install( self::CONTENT_EMBED_LINK );

		if ( is_wp_error( $result ) ) {
			$status['errorCode']    = 'GENERIC_ERROR';
			$status['errorMessage'] = $result->get_error_message();
			wp_send_json_error( $status );
		}

		if ( is_wp_error( $skin->result ) ) {
			$status['errorCode']    = 'GENERIC_ERROR';
			$status['errorMessage'] = $skin->result->get_error_message();
			wp_send_json_error( $status );
		}

		if ( $skin->get_errors()->has_errors() ) {
			$status['errorCode']    = 'GENERIC_ERROR';
			$status['errorMessage'] = $skin->get_error_messages();
			wp_send_json_error( $status );
		}

		if ( is_null( $result ) ) {
			global $wp_filesystem;
			$status['errorCode']    = 'FILESYSTEM_ERROR';
			$status['errorMessage'] = 'Unable to connect to the filesystem. Please confirm your credentials.';

			// Pass through the error from WP_Filesystem if one was raised.
			if ( $wp_filesystem instanceof \WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->has_errors() ) {
				$status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
			}
			wp_send_json_error( $status );
		}

		$plugin_info = $upgrader->plugin_info();
		if ( ! $plugin_info ) {
			return wp_send_json_error(
				array(
					'errorCode'    => 'INFO_FETCH_ERROR',
					'errorMessage' => 'Plugin installation failed, could not retrieve plugin info',
				),
				500
			);
		}

		$status = array(
			'message'     => 'Plugin installed and activated successfully',
			'plugin_info' => $plugin_info,
			'activated'   => false,
		);

		if ( current_user_can( 'activate_plugins' ) ) {
			$activation_response = activate_plugin( $plugin_info );
			if ( is_wp_error( $activation_response ) ) {
				$status['errorCode']    = 'ACTIVATION_ERROR';
				$status['errorMessage'] = $activation_response->get_error_message();
			} else {
				$status['activated'] = true;
			}
		}

		update_option( self::INSTALL_OPTION, true );
		return wp_send_json_success( $status );
	}

	/**
	 * Determine if there is a copy of Content embed installed, and if so, if it is active.
	 * Checks for path names with junk at the end to handle multiple installs
	 */
	public static function is_content_embed_active_installed() {
		$content_embed_regex = '/hubspot-content-embed((-.*)?)\/content-embed.php/';
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Filter based on regex in case there are multiple copies installed for some reason.
		$all_plugins         = array_keys( get_plugins() );
		$content_embed_paths = array_filter(
			$all_plugins,
			function ( $plugin_path ) use ( $content_embed_regex ) {
				return preg_match( $content_embed_regex, $plugin_path );
			}
		);

		$installed = ! empty( $content_embed_paths );
		$active    = ! empty(
			array_filter(
				$content_embed_paths,
				function ( $plugin_path ) {
					return is_plugin_active( $plugin_path );
				}
			)
		);

		return array(
			'installed' => $installed,
			'active'    => $active,
		);
	}
}
