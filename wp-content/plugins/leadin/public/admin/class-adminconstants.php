<?php

namespace Leadin\admin;

use Leadin\data\Filters;
use Leadin\admin\Links;
use Leadin\admin\Routing;
use Leadin\auth\OAuth;
use Leadin\utils\Versions;
use Leadin\data\User;
use Leadin\data\Portal_Options;
use Leadin\admin\Connection;
use Leadin\admin\Impact;
use Leadin\data\User_Metadata;
use Leadin\auth\OAuthCryptoError;

/**
 * Class containing all the constants used for admin script localization.
 */
class AdminConstants {



	/**
	 * Return utm_campaign to add to the signup link.
	 */
	private static function get_utm_campaign() {
		$wpe_template = get_option( 'wpe_template' );
		if ( 'hubspot' === $wpe_template ) {
			return 'wp-engine-site-template';
		}
	}

	/**
	 * Return an array with the utm parameters for signup
	 */
	private static function get_utm_query_params_array() {
		$utm_params = array(
			'utm_source' => 'wordpress-plugin',
			'utm_medium' => 'marketplaces',
		);

		$utm_campaign = self::get_utm_campaign();
		if ( ! empty( $utm_campaign ) ) {
			$utm_params['utm_campaign'] = $utm_campaign;
		}
		return $utm_params;
	}

	/**
	 * Return a nonce used on the connection class
	 */
	private static function get_connection_nonce() {
		return wp_create_nonce( 'hubspot-nonce' );
	}

	/**
	 * Return an array with the user's pre-fill info for signup
	 */
	private static function get_signup_prefill_params_array() {
		$wp_user   = wp_get_current_user();
		$user_info = array(
			'firstName' => $wp_user->user_firstname,
			'lastName'  => $wp_user->user_lastname,
			'email'     => $wp_user->user_email,
			'company'   => get_bloginfo( 'name' ),
		);

		return $user_info;
	}

	/**
	 * Return an array of properties to be included in the signup search string
	 */
	public static function get_signup_query_params_array() {
		$signup_params                        = array();
		$signup_params['leadinPluginVersion'] = constant( 'LEADIN_PLUGIN_VERSION' );
		$signup_params['trackConsent']        = User_Metadata::get_track_consent();
		$user_prefill_params                  = self::get_signup_prefill_params_array();
		$signup_params                        = array_merge( $signup_params, $user_prefill_params );
		return $signup_params;
	}

	/**
	 * Return query params array for the iframe.
	 */
	public static function get_hubspot_query_params_array() {
		$wp_user        = wp_get_current_user();
		$hubspot_config = array(
			'l'            => get_locale(),
			'php'          => Versions::get_php_version(),
			'v'            => LEADIN_PLUGIN_VERSION,
			'wp'           => Versions::get_wp_version(),
			'theme'        => get_option( 'stylesheet' ),
			'adminUrl'     => admin_url(),
			'websiteName'  => get_bloginfo( 'name' ),
			'domain'       => wp_parse_url( get_site_url(), PHP_URL_HOST ),
			'wp_user'      => $wp_user->first_name ? $wp_user->first_name : $wp_user->user_nicename,
			'nonce'        => self::get_connection_nonce(),
			'accountName'  => Portal_Options::get_account_name(),
			'hsdio'        => Portal_Options::get_device_id(),
			'portalDomain' => Portal_Options::get_portal_domain(),
		);

		$utm_params     = self::get_utm_query_params_array();
		$hubspot_config = array_merge( $hubspot_config, $utm_params );

		if ( User::is_admin() ) {
			$hubspot_config['admin'] = '1';
		}

		if ( Routing::has_just_connected_with_oauth() ) {
			$hubspot_config['justConnected'] = true;
		}
		if ( Routing::is_new_portal_with_oauth() ) {
			$hubspot_config['isNewPortal'] = true;
		}

		if ( ! Connection::is_connected() ) {
			$signup_params  = self::get_signup_query_params_array();
			$hubspot_config = array_merge( $hubspot_config, $signup_params, Impact::get_params() );
		}

		return $hubspot_config;
	}

	/**
	 * Returns information about Content embed plugin necessary for user guide to determine if/how to install & activate
	 */
	public static function get_content_embed_config() {
		$content_embed_config = array_merge(
			array(
				'userCanInstall'  => current_user_can( 'install_plugins' ),
				'userCanActivate' => current_user_can( 'activate_plugins' ),
				'nonce'           => wp_create_nonce( ContentEmbedInstaller::INSTALL_ARG ),
			),
			ContentEmbedInstaller::is_content_embed_active_installed()
		);

		return $content_embed_config;
	}

	/**
	 * Returns a minimal version of leadinConfig, containing the data needed by the background iframe.
	 */
	public static function get_background_leadin_config() {
		$wp_user_id    = get_current_user_id();
		$portal_id     = Portal_Options::get_portal_id();
		$refresh_token = OAuth::get_refresh_token();
		$is_connected  = ! empty( $portal_id ) && ! empty( $refresh_token );

		$background_config = array(
			'adminUrl'                  => admin_url(),
			'activationTime'            => Portal_Options::get_activation_time(),
			'deviceId'                  => Portal_Options::get_device_id(),
			'formsScript'               => Filters::apply_forms_script_url_filters(),
			'formsScriptPayload'        => Filters::apply_forms_payload_filters(),
			'meetingsScript'            => Filters::apply_meetings_script_url_filters(),
			'hublet'                    => Filters::apply_hublet_filters(),
			'hubspotBaseUrl'            => Filters::apply_base_url_filters( $is_connected ),
			'leadinPluginVersion'       => constant( 'LEADIN_PLUGIN_VERSION' ),
			'locale'                    => get_locale(),
			'restUrl'                   => get_rest_url(),
			'restNonce'                 => wp_create_nonce( 'wp_rest' ),
			'redirectNonce'             => wp_create_nonce( Routing::REDIRECT_NONCE ),
			'phpVersion'                => Versions::get_php_version(),
			'pluginPath'                => constant( 'LEADIN_PATH' ),
			'plugins'                   => get_plugins(),
			'portalId'                  => $portal_id,
			'accountName'               => Portal_Options::get_account_name(),
			'portalDomain'              => Portal_Options::get_portal_domain(),
			'portalEmail'               => get_user_meta( $wp_user_id, 'leadin_email', true ),
			'reviewSkippedDate'         => User_Metadata::get_skip_review(),
			'theme'                     => get_option( 'stylesheet' ),
			'wpVersion'                 => Versions::get_wp_version(),
			'leadinQueryParams'         => self::get_hubspot_query_params_array(),
			'connectionStatus'          => $is_connected ? 'Connected' : 'NotConnected',
			'contentEmbed'              => self::get_content_embed_config(),
			'requiresContentEmbedScope' => is_plugin_active( 'hubspot-content-embed/content-embed.php' ) ? '1' : '0',
			'lastAuthorizeTime'         => Portal_Options::get_last_authorize_time(),
			'lastDeauthorizeTime'       => Portal_Options::get_last_deauthorize_time(),
			'lastDisconnectTime'        => Portal_Options::get_last_disconnect_time(),
		);

		if ( false === $refresh_token ) {
				$background_config['decryptError'] = OAuthCryptoError::DECRYPT_FAILED;
		} else {
				$background_config['refreshToken'] = $refresh_token;
		}

		return $background_config;
	}

	/**
	 * Returns leadinConfig, containing all the data needed by the leadin javascript.
	 */
	public static function get_leadin_config() {
		$leadin_config = self::get_background_leadin_config();

		if ( 'NotConnected' === $leadin_config['connectionStatus'] ) {
			if ( ! Impact::has_params() ) {
				$impact_link = Impact::get_affiliate_link();
				if ( ! empty( $impact_link ) ) {
					$leadin_config['impactLink'] = Impact::get_affiliate_link();
				}
			}
		}

		return $leadin_config;
	}
}
