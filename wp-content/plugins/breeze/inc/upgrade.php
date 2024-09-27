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
			update_option( 'breeze_version', BREEZE_VERSION, true );
			if ( ! class_exists( 'Breeze_ConfigCache' ) ) {
				//config to cache
				require_once( BREEZE_PLUGIN_DIR . 'inc/cache/config-cache.php' );
			}
			Breeze_ConfigCache::factory()->write();
			do_action( 'breeze_clear_all_cache' );
		}
	}
}

$breeze_upgrade = new Breeze_Upgrade();
$breeze_upgrade->init();
