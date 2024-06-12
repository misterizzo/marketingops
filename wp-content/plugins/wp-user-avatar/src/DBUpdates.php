<?php

namespace ProfilePress\Core;

use ProfilePress\Core\Classes\ExtensionManager;
use ProfilePress\Core\Membership\DigitalProducts\UploadHandler;
use ProfilePress\Libsodium\Licensing\Licensing;

class DBUpdates
{
    public static $instance;

    const DB_VER = 11;

    public function init_options()
    {
        add_option('ppress_db_ver', 0);
    }

    public function maybe_update()
    {
        $this->init_options();

        if (get_option('ppress_db_ver', 0) >= self::DB_VER) {
            return;
        }

        // update plugin
        $this->update();
    }

    public function update()
    {
        // no PHP timeout for running updates
        set_time_limit(0);

        // this is the current database schema version number
        $current_db_ver = get_option('ppress_db_ver', 0);

        // this is the target version that we need to reach
        $target_db_ver = self::DB_VER;

        // run update routines one by one until the current version number
        // reaches the target version number
        while ($current_db_ver < $target_db_ver) {
            // increment the current db_ver by one
            $current_db_ver++;

            // each db version will require a separate update function
            $update_method = "update_routine_{$current_db_ver}";

            if (method_exists($this, $update_method)) {
                call_user_func(array($this, $update_method));
            }
        }

        // update the option in the database, so that this process can always
        // pick up where it left off
        update_option('ppress_db_ver', $current_db_ver);
    }

    public function update_routine_1()
    {
        $a                           = get_option(ExtensionManager::DB_OPTION_NAME, []);
        $a[ExtensionManager::PAYPAL] = 'true';
        update_option(ExtensionManager::DB_OPTION_NAME, $a);
    }

    public function update_routine_2()
    {
        global $wpdb;

        $table1 = DBTables::orders_db_table();
        $table2 = DBTables::subscriptions_db_table();
        $table3 = DBTables::customers_db_table();

        $wpdb->query("ALTER TABLE $table1 CHANGE date_created date_created datetime NOT NULL;");
        $wpdb->query("ALTER TABLE $table2 CHANGE created_date created_date datetime NOT NULL;");
        $wpdb->query("ALTER TABLE $table3 CHANGE date_created date_created datetime NOT NULL;");
    }

    public function update_routine_3()
    {
        global $wpdb;

        $table  = DBTables::coupons_db_table();
        $table2 = DBTables::subscription_plans_db_table();

        $wpdb->query("ALTER TABLE $table CHANGE type coupon_type varchar(50) NULL;");
        $wpdb->query("ALTER TABLE $table2 ADD COLUMN order_note text NULL AFTER description;");
    }

    public function update_routine_4()
    {
        global $wpdb;

        $table = DBTables::subscription_plans_db_table();

        $wpdb->query("ALTER TABLE $table ADD COLUMN user_role varchar(50) NULL AFTER description;");
    }

    public function update_routine_5()
    {
        UploadHandler::get_instance()->create_protection_files(true);

        flush_rewrite_rules();

        if (class_exists('\ProfilePress\Libsodium\Licensing\Licensing')) {

            $response = Licensing::get_instance()->license_control_instance()->check_license();

            if (is_wp_error($response)) return false;

            if ( ! empty($response->license)) {
                if ($response->license == 'valid') {
                    update_option('ppress_license_status', 'valid');
                    update_option('ppress_license_expired_status', 'false');
                } else {
                    if (in_array($response->license, ['expired', 'disabled'])) {
                        update_option('ppress_license_expired_status', 'true');
                    }
                    update_option('ppress_license_status', 'invalid');
                }
            }
        }
    }

    public function update_routine_6()
    {
        $a                           = get_option(ExtensionManager::DB_OPTION_NAME);
        $a[ExtensionManager::MOLLIE] = 'true';
        update_option(ExtensionManager::DB_OPTION_NAME, $a);
    }

    public function update_routine_7()
    {
        ppress_update_settings('wordpresscom_button_label', esc_html__('Sign in with WordPress.com', 'wp-user-avatar'));
        ppress_update_settings('yahoo_button_label', esc_html__('Sign in with Yahoo', 'wp-user-avatar'));
        ppress_update_settings('microsoft_button_label', esc_html__('Sign in with Microsoft', 'wp-user-avatar'));
        ppress_update_settings('amazon_button_label', esc_html__('Sign in with Amazon', 'wp-user-avatar'));
    }

    public function update_routine_8()
    {
        $a                            = get_option(ExtensionManager::DB_OPTION_NAME);
        $a[ExtensionManager::RECEIPT] = 'true';
        update_option(ExtensionManager::DB_OPTION_NAME, $a);
    }

    public function update_routine_9()
    {
        global $wpdb;

        $table = DBTables::coupons_db_table();

        $wpdb->query("ALTER TABLE $table ADD COLUMN is_onetime_use enum('true','false') NOT NULL DEFAULT 'false' AFTER coupon_type;");
    }

    public function update_routine_10()
    {
        global $wpdb;

        $table = DBTables::profile_fields_db_table();

        $wpdb->query("ALTER TABLE $table CHANGE options options longtext NULL;");
    }

    public function update_routine_11()
    {
        $linkedin_api_version = ppress_get_setting('linkedin_api_version', '');

        if (empty($linkedin_api_version)) {
            ppress_update_settings('linkedin_api_version', 'deprecated');
        }
    }

    public static function get_instance()
    {
        if ( ! isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}