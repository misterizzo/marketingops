<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages upgrade routines for the Breeze plugin.
 *
 * This class handles version-specific upgrade tasks for the Breeze plugin. It ensures that
 * stored plugin version information is updated, appropriate hooks are triggered, caches are cleared,
 * configuration files are refreshed, and compatibility adjustments are made for different WordPress setups
 * (e.g., single-site or multisite installations).
 */
class Breeze_Upgrade {

	public $breeze_version;

	public function __construct() {}

	public function init() {
		$this->breeze_version = get_option( 'breeze_version' );

		if ( empty( $this->breeze_version ) || version_compare( BREEZE_VERSION, $this->breeze_version, '!=' ) ) {

			add_action( 'wp_loaded', array( $this, 'do_breeze_upgrade' ) );
			update_option( 'breeze_version', BREEZE_VERSION, true );
			$this->do_breeze_clear_cache();
			$this->do_breeze_config_refresh();

		}
	}

	/**
	 * Clears all cached data managed by the Breeze plugin.
	 *
	 * This method checks for the existence of the Breeze_Admin class,
	 * loads the Breeze_MinificationCache class if not already loaded,
	 * and executes the cache-clearing functionality provided by the Breeze plugin.
	 *
	 * @return void
	 */
	private function do_breeze_clear_cache() {
		if ( class_exists( 'Breeze_Admin' ) ) {
			if ( ! class_exists( 'Breeze_MinificationCache' ) ) {
				require_once BREEZE_PLUGIN_DIR . 'inc/minification/breeze-minification-cache.php';
			}

			$admin = new Breeze_Admin();
			$admin->breeze_clear_all_cache();
		}

	}

	/**
	 * Refreshes the configuration files for the Breeze plugin.
	 *
	 * This method ensures that the plugin's configuration files
	 * are updated by invoking the refresh_config_files method.
	 *
	 * @return void
	 */
	private function do_breeze_config_refresh() {
			self::refresh_config_files();
	}

	/**
	 * Handles the upgrade routines for different versions of the Breeze plugin.
	 *
	 * This method performs conditional upgrade tasks based on the current version
	 * of the Breeze plugin. It ensures that specific update routines from older
	 * versions are executed and applies necessary changes for newer versions.
	 * Also triggers a custom action after the upgrade routines and updates
	 * the stored version value accordingly.
	 *
	 * @return void
	 */
	public function do_breeze_upgrade() {
		$is_older_than_v2118 = false;

		// Version 2.1.18 updates.
		if ( ( ! empty( $this->breeze_version ) && version_compare( $this->breeze_version, '2.1.18', '<' ) )
			|| ( empty( $this->breeze_version ) && ! empty( get_option( 'breeze_first_install' ) ) ) ) {
			$is_older_than_v2118 = true;
			$this->v2118_upgrades();
		}
		// Version 2.1.19 updates.
		if ( $is_older_than_v2118 || version_compare( $this->breeze_version, '2.1.19', '<' ) ) {
			$this->v2119_upgrades();
		}

		// Making sure that "Purge Cache After" value is set in Basic tab option.
		if ( $is_older_than_v2118 || version_compare( $this->breeze_version, '2.2.8', '<' ) ) {
			$this->v228_upgrades();
		}

		do_action( 'breeze_after_existing_upgrade_routine', $this->breeze_version );
		update_option( 'breeze_version_upgraded_from', $this->breeze_version );
	}

	/**
	 * Performs the necessary upgrades for version 2.2.8.
	 *
	 * This function updates the "Purge Cache After" setting in Basic tab.
	 * Ensures that the 'breeze-b-ttl' key is present in both multisite and single-site configurations.
	 * For multisite setups, it handles both network options and individual blog options.
	 *
	 * @return void
	 */
	public function v228_upgrades() {

		if ( is_multisite() ) {
			// Handle network options.
			$breeze_basic_network = get_site_option( 'breeze_basic_settings', array() );
			if ( ! array_key_exists( 'breeze-b-ttl', $breeze_basic_network ) ) {
				$breeze_basic_network['breeze-b-ttl'] = 1440;
				update_site_option( 'breeze_basic_settings', $breeze_basic_network );
			}

			// Handle check and update for multisite blogs.
			$blogs = get_sites(
				array(
					'number' => 0,
				)
			);

			foreach ( $blogs as $blog ) {
				$basic = get_blog_option( (int) $blog->blog_id, 'breeze_basic_settings', array() );
				if ( ! array_key_exists( 'breeze-b-ttl', $basic ) ) {
					$basic['breeze-b-ttl'] = 1440;
					update_blog_option( (int) $blog->blog_id, 'breeze_basic_settings', $basic );
				}
			}
		} else {
			// Handle check for single site.
			$basic = breeze_get_option( 'basic_settings', true );
			if ( ! array_key_exists( 'breeze-b-ttl', $basic ) ) {
				$basic['breeze-b-ttl'] = 1440;
				breeze_update_option( 'basic_settings', $basic, true );
			}
		}
	}

	/**
	 * Handles upgrade routines for version 2.1.19 by unscheduling specific cron events.
	 *
	 * This method checks the status of WP-Cron and required functions. If valid cron jobs exist,
	 * it unschedules the 'breeze_after_update_scheduled_hook' events to prevent conflicts or redundancy.
	 *
	 * @return void
	 */
	public function v2119_upgrades() {
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			return;
		}

		if ( ! function_exists( '_get_cron_array' ) || ! function_exists( 'wp_unschedule_event' ) ) {
			return;
		}
		$crons = _get_cron_array();
		if ( empty( $crons ) ) {
			return;
		}

		$hook = 'breeze_after_update_scheduled_hook';

		foreach ( $crons as $timestamp => $cron ) {
			if ( isset( $cron[ $hook ] ) ) {
				foreach ( $cron[ $hook ] as $instance ) {
					wp_unschedule_event( $timestamp, $hook, $instance['args'], false );
				}
			}
		}
	}

	/**
	 * Handles the upgrades introduced in version 2.11.8 of the plugin.
	 *
	 * This method verifies if WooCommerce is active, ensures the Breeze_Ecommerce_Cache class is loaded,
	 * and performs configuration updates for each site within a multisite network or for a single site setup.
	 * Specifically, it writes the configuration cache depending on the settings inheritance status
	 * for multisite setups or directly for single-site installations.
	 *
	 * @return void
	 */
	public function v2118_Upgrades() {

		if ( ! is_woocommerce_active() ) {
			return;
		}
		if ( ! class_exists( 'Breeze_Ecommerce_Cache' ) ) {
			require_once BREEZE_PLUGIN_DIR . 'inc/cache/ecommerce-cache.php';
		}

		if ( is_multisite() ) {
			$blogs = get_sites();
			if ( ! empty( $blogs ) ) {
				foreach ( $blogs as $blog_data ) {
					$blog_id = $blog_data->blog_id;
					switch_to_blog( $blog_id );
					$inherit_settings = get_blog_option( $blog_id, 'breeze_inherit_settings' );

					if ( ! $inherit_settings ) {
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


	/**
	 * This function is used to refresh config files and is called from other parts of plugin too.
	 */
	public static function refresh_config_files() {

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		// import these file in front-end when required.
		if ( ! class_exists( 'Breeze_Ecommerce_Cache' ) ) {
			// cache when ecommerce installed
			require_once BREEZE_PLUGIN_DIR . 'inc/cache/ecommerce-cache.php';
		}

		// import these file in front-end when required.
		if ( ! class_exists( 'Breeze_ConfigCache' ) ) {
			// config to cache
			require_once BREEZE_PLUGIN_DIR . 'inc/cache/config-cache.php';
		}

		if ( is_multisite() ) {
			// For multi-site we need to also reset the root config-file.
			Breeze_ConfigCache::factory()->write_config_cache( true );

			$blogs = get_sites();
			if ( ! empty( $blogs ) ) {
				foreach ( $blogs as $blog_data ) {
					$blog_id = $blog_data->blog_id;
					switch_to_blog( $blog_id );

					// if the settings are inherited, then we do not need to refresh the config file.
					$inherit_option = get_option( 'breeze_inherit_settings' );
					$inherit_option = filter_var( $inherit_option, FILTER_VALIDATE_BOOLEAN );

					// If the settings are not inherited from parent blog, then refresh the config file.
					if ( false === $inherit_option ) {
						// Refresh breeze-cache.php file
						Breeze_ConfigCache::factory()->write_config_cache();
					}

					restore_current_blog();
				}
			}
		} else {
			// For single site.
			// Refresh breeze-cache.php file
			Breeze_ConfigCache::factory()->write_config_cache();
		}
		Breeze_ConfigCache::factory()->write();
	}
}

$breeze_upgrade = new Breeze_Upgrade();
$breeze_upgrade->init();
