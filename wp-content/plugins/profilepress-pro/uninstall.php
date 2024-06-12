<?php

if ( ! defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

// Load ProfilePress file
$lite_file_path = dirname(dirname(__FILE__)) . '/wp-user-avatar/wp-user-avatar.php';
if (file_exists($lite_file_path)) {
    include_once($lite_file_path);
}

include_once(dirname(__FILE__) . '/profilepress-pro.php');

function ppress_pro_mo_uninstall_function()
{
    if (function_exists('ppress_get_setting') && ppress_get_setting('remove_plugin_data') == 'yes' || ! function_exists('ppress_get_setting')) {

        delete_option('ppress_license_key');
        delete_option('ppress_license_status');
        delete_option('ppress_license_expired_status');

        delete_option('ppress_pro_plugin_activated');

        global $wpdb;

        $drop_tables   = [];
        $drop_tables[] = $wpdb->prefix . 'ppress_passwordless';
        $drop_tables[] = $wpdb->prefix . 'ppress_profile_fields';

        $drop_tables = apply_filters('ppress_pro_drop_database_tables', $drop_tables);

        foreach ($drop_tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }

        flush_rewrite_rules();
        // Clear any cached data that has been removed.
        wp_cache_flush();
    }
}

if ( ! is_multisite()) {
    ppress_pro_mo_uninstall_function();
} else {

    if ( ! wp_is_large_network()) {
        $site_ids = get_sites(['fields' => 'ids', 'number' => 0]);

        foreach ($site_ids as $site_id) {
            switch_to_blog($site_id);
            ppress_pro_mo_uninstall_function();
            restore_current_blog();
        }
    }
}