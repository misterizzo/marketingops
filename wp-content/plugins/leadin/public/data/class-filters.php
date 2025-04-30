<?php
namespace Leadin\data;

use Leadin\data\Portal_Options;
use Leadin\Proxy_Mappings;

/**
 * Class containing all the custom filters defined to be used instead of constants.
 */
class Filters {

	/**
	 * Return the current hublet.
	 */
	public static function apply_hublet_filters() {
		return apply_filters( LEADIN_PREFIX . '_hublet', Portal_Options::get_hublet() );
	}

	/**
	 * Return the prefix for UI urls.
	 */
	public static function apply_app_prefix_filters() {
		return self::resolve_hublet( apply_filters( LEADIN_PREFIX . '_app_prefix', 'app' ) );
	}

	/**
	 * Return the prefix for UI urls.
	 */
	public static function apply_js_prefix_filters() {
		return self::resolve_hublet( apply_filters( LEADIN_PREFIX . '_js_prefix', 'js' ) );
	}

	/**
	 * Return the prefix for API urls.
	 */
	public static function apply_api_prefix_filters() {
		return self::resolve_hublet( apply_filters( LEADIN_PREFIX . '_api_prefix', 'api' ) );
	}

	/**
	 * Return the Hubspot domain.
	 */
	public static function apply_hubspot_domain_filters() {
		return apply_filters( LEADIN_PREFIX . '_hubspot_domain', 'hubspot.com' );
	}

	/**
	 * Apply leadin_base_url filter.
	 *
	 * @param boolean $cross_hublet if false it's use non-hublet specific prefix. For example, "app" instead of "app-eu1".
	 */
	public static function apply_base_url_filters( $cross_hublet = true ) {
		$prefix = $cross_hublet ? self::apply_app_prefix_filters() : apply_filters( LEADIN_PREFIX . '_app_prefix', 'app' );
		$domain = self::apply_hubspot_domain_filters();
		return apply_filters( LEADIN_PREFIX . '_base_url', "https://$prefix.$domain" );
	}

	/**
	 * Apply leadin_js_base_url filter.
	 */
	public static function apply_js_base_url_filters() {
		$prefix = self::apply_js_prefix_filters();
		$domain = self::apply_hubspot_domain_filters();
		return apply_filters( LEADIN_PREFIX . '_js_base_url', "https://$prefix.$domain" );
	}

	/**
	 * Apply filter to get the base url for the HubSpot api.
	 *
	 * @param boolean $cross_hublet if true it's use non-hublet specific prefix. For example, "api" instead of "api-eu1".
	 */
	public static function apply_base_api_url_filters( $cross_hublet = false ) {
		$prefix = $cross_hublet ? 'api' : self::apply_api_prefix_filters();
		$domain = self::apply_hubspot_domain_filters();
		return apply_filters( LEADIN_PREFIX . '_base_api_url', "https://$prefix.$domain" );
	}

	/**
	 * Apply leadin_signup_base_url filter.
	 */
	public static function apply_signup_base_url_filters() {
		$domain = self::apply_hubspot_domain_filters();
		return apply_filters( LEADIN_PREFIX . '_signup_base_url', "https://app.$domain" );
	}

	/**
	 * Apply leadin_forms_script_url filter.
	 */
	public static function apply_forms_script_url_filters() {
		$hublet_domain = self::resolve_hublet( 'js' );
		return apply_filters( LEADIN_PREFIX . '_forms_script_url', "https://$hublet_domain.hsforms.net/forms/embed/v2.js" );
	}

	/**
	 * Apply leadin_forms_v4_script_url filter.
	 *
	 * @param string $portal_id The portal ID.
	 */
	public static function apply_forms_v4_script_url_filters( $portal_id ) {
		$hublet_domain = self::resolve_hublet( 'js' );
		return apply_filters( LEADIN_PREFIX . '_forms_v4_script_url', "https://$hublet_domain.hsforms.net/forms/embed/$portal_id.js" );
	}

	/**
	 * Apply leadin_meetings_script_url filter.
	 */
	public static function apply_meetings_script_url_filters() {
		return apply_filters( LEADIN_PREFIX . '_meetings_script_url', 'https://static.hsappstatic.net/MeetingsEmbed/ex/MeetingsEmbedCode.js' );
	}

	/**
	 * Apply leadin_script_loader_domain filter.
	 */
	public static function apply_script_loader_domain_filters() {
		$hublet_domain = self::resolve_hublet( 'js' );
		return apply_filters( LEADIN_PREFIX . '_script_loader_domain', "$hublet_domain.hs-scripts.com" );
	}

	/**
	 * Apply leadin_forms_payload filter.
	 */
	public static function apply_forms_payload_filters() {
		return apply_filters( LEADIN_PREFIX . '_forms_payload', '' );
	}

	/**
	 * Apply leadin_forms_v4_payload filter.
	 */
	public static function apply_forms_v4_payload_filters() {
		return apply_filters( LEADIN_PREFIX . '_forms_v4_payload', '' );
	}

	/**
	 * Apply leadin_forms_payload_url filter.
	 */
	public static function apply_page_content_type_filters() {
		if ( is_single() ) {
			$content_type = 'blog-post';
		} elseif ( is_archive() || is_search() ) {
			$content_type = 'listing-page';
		} else {
			$content_type = 'standard-page';
		}

		return apply_filters( LEADIN_PREFIX . '_page_content_type', $content_type );
	}

	/**
	 * Apply leadin_view_plugin_menu_capability filter.
	 */
	public static function apply_view_plugin_menu_capability_filters() {
		return apply_filters( LEADIN_PREFIX . '_view_plugin_menu_capability', 'edit_posts' );
	}

	/**
	 * Apply leadin_connect_plugin_capability filter.
	 */
	public static function apply_connect_plugin_capability_filters() {
		return apply_filters( LEADIN_PREFIX . '_connect_plugin_capability', 'manage_options' );
	}

	/**
	 * Apply leadin_impact_code filter.
	 */
	public static function apply_impact_code_filters() {
		return apply_filters( LEADIN_PREFIX . '_impact_code', null );
	}

	/**
	 * Apply leadin_query_params filter.
	 */
	public static function apply_query_params_filters() {
		return apply_filters( LEADIN_PREFIX . '_query_params', '' );
	}

	/**
	 * Add hublet to the prefix.
	 *
	 * @param String $prefix Prefix to add the hublet to.
	 */
	private static function resolve_hublet( $prefix ) {
		$hublet = self::apply_hublet_filters();
		$result = $prefix;
		if ( ! empty( $hublet ) && 'na1' !== $hublet ) {
			$result = "$prefix-$hublet";
		}
		return $result;
	}

	/**
	 * Add swimlane to the prefix when hublet is NA1.
	 *
	 * @param String $prefix Prefix to add the swimlane.
	 */
	private static function resolve_swimlane_for_na1( $prefix ) {
		$hublet = self::apply_hublet_filters();
		$result = $prefix;
		if ( ! empty( $hublet ) && 'na1' === $hublet ) {
			// phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.intdivFound
			$swimlane = intdiv( Portal_Options::get_portal_id(), 10 ) % 5;
			$result   = $prefix . $swimlane . '0';
		}
		return $result;
	}

	/**
	 * Apply filter to get the get all proxy mapping url.
	 */
	public static function apply_plugin_mappings_api_url() {
		$base_url = self::apply_base_api_url_filters( false );
		return apply_filters( LEADIN_PREFIX . '_plugin_mappings_api_url', "$base_url/cos-domains/v1/wp-plugin-mappings/get-all" );
	}


	/**
	 * Apply leadin_sites_proxy_cdn filter.
	 */
	public static function apply_sites_proxy_cdn_filters() {
		$portal_id = Portal_Options::get_portal_id();
		$domain    = self::resolve_swimlane_for_na1( self::resolve_hublet( 'sites-proxy.hscoscdn' ) );
		return apply_filters( LEADIN_PREFIX . '_sites_proxy_cdn', "https://$portal_id.$domain.net" );
	}

}
