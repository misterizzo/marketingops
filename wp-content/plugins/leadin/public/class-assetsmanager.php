<?php

namespace Leadin;

use Leadin\data\Filters;
use Leadin\admin\AdminConstants;
use Leadin\data\Portal_Options;

/**
 * Class responsible of managing all the plugin assets.
 */
class AssetsManager {
	const ADMIN_CSS  = 'leadin-css';
	const BRIDGE_CSS = 'leadin-bridge-css';
	const ADMIN_JS   = 'leadin-js';

	const FEEDBACK_CSS       = 'leadin-feedback-css';
	const FEEDBACK_JS        = 'leadin-feedback';
	const TRACKING_CODE      = 'leadin-script-loader-js';
	const GUTENBERG          = 'leadin-gutenberg';
	const MEETINGS_GUTENBERG = 'leadin-meetings-gutenberg';
	const FORMS_SCRIPT       = 'leadin-forms-v2';
	const MEETINGS_SCRIPT    = 'leadin-meeting';
	const LEADIN_CONFIG      = 'leadinConfig';
	const LEADIN_I18N        = 'leadinI18n';
	const REVIEW_BANNER      = 'leadin-review-banner';
	const ELEMENTOR          = 'leadin-elementor';
	const APP_ENTRY_CSS      = 'leadin-app-css';
	const APP_EMBEDDER       = 'leadin-app-embedder';

	/**
	 * Register and localize all assets.
	 */
	public static function register_assets() {
		wp_register_style( self::ADMIN_CSS, LEADIN_ASSETS_PATH . '/style/leadin.css', array(), LEADIN_PLUGIN_VERSION );
		wp_register_script( self::ADMIN_JS, LEADIN_JS_BASE_PATH . '/leadin.js', array( 'jquery', 'wp-element' ), LEADIN_PLUGIN_VERSION, true );

		wp_localize_script( self::ADMIN_JS, self::LEADIN_CONFIG, AdminConstants::get_leadin_config() );
		wp_register_script( self::FEEDBACK_JS, LEADIN_JS_BASE_PATH . '/feedback.js', array( 'jquery', 'thickbox' ), LEADIN_PLUGIN_VERSION, true );
		wp_localize_script( self::FEEDBACK_JS, self::LEADIN_CONFIG, AdminConstants::get_background_leadin_config() );
		wp_register_style( self::FEEDBACK_CSS, LEADIN_ASSETS_PATH . '/style/leadin-feedback.css', array(), LEADIN_PLUGIN_VERSION );
		wp_register_style( self::BRIDGE_CSS, LEADIN_ASSETS_PATH . '/style/leadin-bridge.css?', array(), LEADIN_PLUGIN_VERSION );
		wp_register_style( self::APP_ENTRY_CSS, LEADIN_JS_BASE_PATH . '/leadin.css', array(), LEADIN_PLUGIN_VERSION );
	}

	/**
	 * Enqueue the assets needed in the admin section.
	 */
	public static function enqueue_admin_assets() {
		wp_enqueue_style( self::ADMIN_CSS );
	}

	/**
	 * Enqueue the assets needed to render the deactivation feedback form.
	 */
	public static function enqueue_feedback_assets() {
		$embed_domain = Filters::apply_script_loader_domain_filters();
		wp_enqueue_script( self::APP_EMBEDDER, "$embed_domain/integrated-app-embedder/v1.js", array(), LEADIN_PLUGIN_VERSION, true );
		wp_enqueue_style( self::FEEDBACK_CSS );
		wp_enqueue_script( self::FEEDBACK_JS );
	}

	/**
	 * Enqueue the assets needed to correctly render the plugin's iframe.
	 */
	public static function enqueue_bridge_assets() {
		wp_enqueue_style( self::BRIDGE_CSS );
		wp_enqueue_script( self::ADMIN_JS );
		wp_enqueue_style( self::APP_ENTRY_CSS );
	}


	/**
	 * Enqueue the assets needed to correctly render the integrated forms app.
	 */
	public static function enqueue_integrated_app_assets() {
		$embed_domain = Filters::apply_js_base_url_filters();
		wp_enqueue_style( self::BRIDGE_CSS );
		wp_enqueue_script( self::ADMIN_JS );
		wp_enqueue_style( self::APP_ENTRY_CSS );
		wp_enqueue_script( self::APP_EMBEDDER, "$embed_domain/integrated-app-embedder/v1.js", array(), LEADIN_PLUGIN_VERSION, true );
	}

	/**
	 * Register and enqueue the HubSpot's script loader (aka tracking code), used to collect data from your visitors.
	 * https://knowledge.hubspot.com/account/how-does-hubspot-track-visitors
	 *
	 * @param Object $leadin_wordpress_info Object used to pass to the script loader.
	 */
	public static function enqueue_script_loader( $leadin_wordpress_info ) {
		$embed_domain     = Filters::apply_script_loader_domain_filters();
		$portal_id        = Portal_Options::get_portal_id();
		$business_unit_id = Portal_Options::get_business_unit_id();
		$embed_url        = "https://$embed_domain/$portal_id.js?integration=WordPress";

		if ( $business_unit_id && '' !== $business_unit_id ) {
			$embed_url = $embed_url . "&businessUnitId=$business_unit_id";
		}

		wp_register_script( self::TRACKING_CODE, $embed_url, array(), LEADIN_PLUGIN_VERSION, true );
		wp_localize_script( self::TRACKING_CODE, 'leadin_wordpress', $leadin_wordpress_info );
		wp_enqueue_script( self::TRACKING_CODE );
	}

	/**
	 * Register and enqueue forms script
	 */
	public static function enqueue_forms_script() {
		wp_enqueue_script(
			self::FORMS_SCRIPT,
			Filters::apply_forms_script_url_filters(),
			array(),
			LEADIN_PLUGIN_VERSION,
			true
		);
	}

	/**
	 * Register and enqueue meetings script
	 */
	public static function enqueue_meetings_script() {
		wp_enqueue_script(
			self::MEETINGS_SCRIPT,
			'https://static.hsappstatic.net/MeetingsEmbed/ex/MeetingsEmbedCode.js',
			array(),
			LEADIN_PLUGIN_VERSION,
			true
		);
	}

	/**
	 * Register and localize the Gutenberg scripts.
	 */
	public static function localize_gutenberg() {
		$embed_domain = Filters::apply_js_base_url_filters();
		wp_enqueue_script( self::APP_EMBEDDER, "$embed_domain/integrated-app-embedder/v1.js", array(), LEADIN_PLUGIN_VERSION, true );
		self::enqueue_forms_script();
		self::enqueue_meetings_script();
		wp_register_style( self::GUTENBERG, LEADIN_JS_BASE_PATH . '/gutenberg.css', array(), LEADIN_PLUGIN_VERSION );
		wp_enqueue_style( self::GUTENBERG );
		wp_register_script( self::GUTENBERG, LEADIN_JS_BASE_PATH . '/gutenberg.js', array( 'wp-blocks', 'wp-element', 'wp-i18n', self::APP_EMBEDDER, self::MEETINGS_SCRIPT, self::FORMS_SCRIPT ), LEADIN_PLUGIN_VERSION, true );
		wp_localize_script( self::GUTENBERG, self::LEADIN_CONFIG, AdminConstants::get_background_leadin_config() );
		wp_set_script_translations( self::GUTENBERG, 'leadin', __DIR__ . '/../languages' );
	}

	/**
	 * Register and enqueue a new script for tracking review banner events.
	 */
	public static function enqueue_review_banner_tracking_script() {
		wp_register_script( self::REVIEW_BANNER, LEADIN_JS_BASE_PATH . '/reviewBanner.js', array( 'jquery' ), LEADIN_PLUGIN_VERSION, true );
		wp_localize_script( self::REVIEW_BANNER, self::LEADIN_CONFIG, AdminConstants::get_background_leadin_config() );
		wp_enqueue_script( self::REVIEW_BANNER );
	}

	/**
	 * Register and enqueue a new script/style for elementor.
	 */
	public static function enqueue_elementor_script() {
		$embed_domain = Filters::apply_js_base_url_filters();
		wp_enqueue_script( self::APP_EMBEDDER, "$embed_domain/integrated-app-embedder/v1.js", array(), LEADIN_PLUGIN_VERSION, true );
		wp_register_style( self::ELEMENTOR, LEADIN_JS_BASE_PATH . '/elementor.css', array(), LEADIN_PLUGIN_VERSION );
		wp_enqueue_style( self::ELEMENTOR );
		wp_register_script( self::ELEMENTOR, LEADIN_JS_BASE_PATH . '/elementor.js', array( 'wp-element', 'wp-i18n', self::APP_EMBEDDER ), LEADIN_PLUGIN_VERSION, true );
		wp_localize_script( self::ELEMENTOR, self::LEADIN_CONFIG, AdminConstants::get_background_leadin_config() );
		wp_enqueue_script( self::ELEMENTOR );
		wp_set_script_translations( self::ELEMENTOR, 'leadin', __DIR__ . '/../languages' );
	}

}
