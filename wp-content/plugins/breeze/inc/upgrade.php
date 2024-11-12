<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Breeze_Upgrade {

	public $breeze_version;

	public $version_upgrades = array();

	public function __construct() {}

	public function init() {

		$this->breeze_version = get_option( 'breeze_version' );

		if ( empty( $this->breeze_version ) || version_compare( BREEZE_VERSION, $this->breeze_version, '!=' ) ) {
			if ( ! class_exists( 'Breeze_ConfigCache' ) ) {
				// config to cache
				require_once BREEZE_PLUGIN_DIR . 'inc/cache/config-cache.php';
			}
			if ( $this->do_upgrades_run() ) {
				add_action( 'wp_loaded', array( $this, 'do_breeze_upgrade' ) );
			}
			update_option( 'breeze_version', BREEZE_VERSION, true );
			Breeze_ConfigCache::factory()->write();
			do_action( 'breeze_clear_all_cache' );
		}
	}

	public function do_upgrades_run() {

		$run_upgrades = false;

		if ( ( ! empty( $this->breeze_version ) && version_compare( $this->breeze_version, '2.1.18', '<' ) )
			|| ( empty( $this->breeze_version ) && ! empty( get_option( 'breeze_first_install' ) ) ) ) {
			$run_upgrades = true;
			array_push( $this->version_upgrades, 'v2118_upgrades' );
		}

		return $run_upgrades;
	}

	public function do_breeze_upgrade() {
		foreach ( $this->version_upgrades as $version_upgrade ) {
			call_user_func( array( $this, $version_upgrade ) );
		}
		do_action( 'breeze_after_existing_upgrade_routine', $this->breeze_version );
		update_option( 'breeze_version_upgraded_from', $this->breeze_version );
	}

	public function v2118_Upgrades() {

		if ( ! is_woocommerce_active() ) {
			return;
		}
		if ( ! class_exists( 'Breeze_Ecommerce_Cache' ) ) {
			require_once( BREEZE_PLUGIN_DIR . 'inc/cache/ecommerce-cache.php' );
		}

		if ( is_multisite() ) {
			$blogs = get_sites();
			if ( ! empty( $blogs ) ) {
				foreach ( $blogs as $blog_data ) {
					$blog_id = $blog_data->blog_id;
					switch_to_blog( $blog_id );
					$inherit_settings = get_blog_option( $blog_id, 'breeze_inherit_settings' );

					if( ! $inherit_settings ) {
						Breeze_ConfigCache::write_config_cache();
					}

					restore_current_blog();
				}
			}
			// Update the network config file.
			Breeze_ConfigCache::write_config_cache( true );
		} else {
			Breeze_ConfigCache::write_config_cache();
		}
	}
}

$breeze_upgrade = new Breeze_Upgrade();
$breeze_upgrade->init();
