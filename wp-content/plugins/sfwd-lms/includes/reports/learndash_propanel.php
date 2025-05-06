<?php
/**
 * Plugin Name: LearnDash LMS - ProPanel
 * Plugin URI: http://www.learndash.com
 * Description: Easily manage and view your LearnDash LMS activity.
 * Version: 2.2.2
 * Author: LearnDash
 * Author URI: http://www.learndash.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: ld_propanel
 * Domain Path: /languages
 *
 * @package LearnDash
 * @version 2.2.0
 */

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

/**
 * Setup Constants
 */

define( 'LD_PP_VERSION', '2.2.2' );

if ( ! defined( 'LD_PP_PLUGIN_DIR' ) ) {
	define( 'LD_PP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'LD_PP_PLUGIN_URL' ) ) {
	define( 'LD_PP_PLUGIN_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );
}

$learndash_shortcode_used = false;

/**
 * Load ProPanel
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ld-propanel.php';


/**
 * Support for Gutenberg Editor
 */
add_action(
	'plugins_loaded',
	function () {
		// @phpstan-ignore-next-line -- Should be checked later.
		if ( defined( 'LEARNDASH_VERSION' ) && version_compare( LEARNDASH_VERSION, '4.8.0', '>=' ) ) {
			require_once __DIR__ . '/includes/gutenberg/index.php';
		}
	}
);


add_action(
	'plugins_loaded',
	function () {
		LearnDash_ProPanel::get_instance();
	}
);

function LD_ProPanel() {
	LearnDash_ProPanel::get_instance();
}

/**
 * Activation logic.
 *
 * @since 4.17.0
 * @deprecated 4.17.0 - This logic has been moved.
 *
 * @return void
 */
function activate_learndash_propanel() {
	_deprecated_function( __FUNCTION__, '4.17.0', 'LearnDash\Core\Modules\Reports\Capabilities::add()' );
}

/**
 * Deactivation logic.
 *
 * @since 4.17.0
 * @deprecated 4.17.0 - This function never did anything.
 *
 * @return void
 */
function deactivate_learndash_propanel() {
	_deprecated_function( __FUNCTION__, '4.17.0' );
}

/**
 * Shows the old ProPanel license menu in the LD admin area.
 *
 * @since 4.17.0
 * @deprecated 4.17.0. This function was used to show the old ProPanel license menu and it is no longer used.
 *
 * @param mixed $admin_tabs The admin tabs.
 *
 * @return void
 */
function learndash_propanel_admin_tabs( $admin_tabs ) {
	_deprecated_function( __FUNCTION__, '4.17.0' );
}

/**
 * Shows the old ProPanel license menu in the LD admin area.
 *
 * @since 4.17.0
 * @deprecated 4.17.0. This function was used to show the old ProPanel license menu and it is no longer used.
 *
 * @param mixed $admin_tabs_on_page The admin tabs on page.
 * @param mixed $admin_tabs         The admin tabs.
 * @param mixed $current_page_id    The current page ID.
 *
 * @return void
 */
function learndash_propanel_learndash_admin_tabs_on_page( $admin_tabs_on_page, $admin_tabs, $current_page_id ) {
	_deprecated_function( __FUNCTION__, '4.17.0' );
}
