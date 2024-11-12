<?php

/**
 * Plugin Name: ProfilePress Pro - Standard
 * Plugin URI: https://profilepress.com
 * Description: Extend and customize the functionality of your ProfilePress powered membership site and online business.
 * Version: 4.11.2
 * Author: ProfilePress Team
 * Author URI: https://profilepress.com
 * Text Domain: profilepress-pro
 * Domain Path: /languages
 */

use ProfilePress\Libsodium\Libsodium;
use ProfilePress\Libsodium\RequirementChecker;

require __DIR__ . '/vendor/autoload.php';

define('PROFILEPRESS_PRO_SYSTEM_FILE_PATH', __FILE__);
define('PROFILEPRESS_PRO_VERSION_NUMBER', '4.11.2');

add_action('init', function () {
    load_plugin_textdomain('profilepress-pro', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

$requirements = new RequirementChecker('ProfilePress Pro', array(
    'php'     => '5.6',
    'wp'      => '4.7',
    'plugins' => ['wp-user-avatar/wp-user-avatar.php' => ['name' => 'ProfilePress', 'version' => '3.0']],
));

if ( ! $requirements->satisfied()) {
    add_action('admin_notices', array($requirements, 'notice'));

    return;
}

register_activation_hook(PROFILEPRESS_PRO_SYSTEM_FILE_PATH, ['ProfilePress\Libsodium\Libsodium', 'run_install']);

// handles edge case where premium is activated before core.
add_action('admin_init', function () {
    if (ppress_is_admin_page() && get_option('ppress_pro_plugin_activated') != 'true') {
        Libsodium::on_activation();
    }
});

if (version_compare(get_bloginfo('version'), '5.1', '<')) {
    add_action('wpmu_new_blog', ['ProfilePress\Libsodium\Libsodium', 'multisite_new_blog_install']);
} else {
    add_action('wp_insert_site', function (\WP_Site $new_site) {
        ProfilePress\Libsodium\Libsodium::multisite_new_blog_install($new_site->blog_id);
    });
}

add_action('activate_blog', ['ProfilePress\Libsodium\Libsodium', 'multisite_new_blog_install']);

add_action('ppress_before_loaded', function () {
    ProfilePress\Libsodium\Libsodium::get_instance()->libsodium();
});