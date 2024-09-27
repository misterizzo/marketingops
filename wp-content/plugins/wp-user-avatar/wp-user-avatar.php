<?php
/**
 * Plugin Name: ProfilePress
 * Plugin URI: https://profilepress.com
 * Description: The modern WordPress membership and user profile plugin.
 * Version: 4.15.14
 * Author: ProfilePress Membership Team
 * Author URI: https://profilepress.com
 * Text Domain: wp-user-avatar
 * Domain Path: /languages
 */

defined('ABSPATH') or die("No script kiddies please!");

define('PROFILEPRESS_SYSTEM_FILE_PATH', __FILE__);
define('PPRESS_VERSION_NUMBER', '4.15.14');

if ( ! defined('PPRESS_STRIPE_API_VERSION')) {
    define('PPRESS_STRIPE_API_VERSION', '2023-10-16');
}

require __DIR__ . '/autoloader.php';
require __DIR__ . '/third-party/vendor/autoload.php';

add_action('init', function () {
    load_plugin_textdomain('wp-user-avatar', false, dirname(plugin_basename(PROFILEPRESS_SYSTEM_FILE_PATH)) . '/languages');
});

ProfilePress\Core\Base::get_instance();