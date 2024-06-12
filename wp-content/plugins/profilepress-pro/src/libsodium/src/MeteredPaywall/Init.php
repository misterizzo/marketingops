<?php

namespace ProfilePress\Libsodium\MeteredPaywall;

use ProfilePress\Core\Classes\ExtensionManager as EM;

class Init
{
    public static $instance_flag = false;

    public function __construct()
    {
        new SettingsPage();
        new DoRestriction();
        new IPBlocker();
    }

    /**
     * @return self|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::METERED_PAYWALL')) return;

        if ( ! EM::is_enabled(EM::METERED_PAYWALL)) return;

        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}