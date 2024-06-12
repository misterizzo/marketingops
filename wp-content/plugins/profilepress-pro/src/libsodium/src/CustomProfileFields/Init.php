<?php

namespace ProfilePress\Libsodium\CustomProfileFields;

use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Libsodium\CustomProfileFields\SettingsPage as CustomProfileFieldsSettingsPage;
use ProfilePress\Libsodium\CustomProfileFields\ContactInfo\SettingsPage as ProfileContactInfoSettingsPage;

class Init
{
    public static $instance_flag = false;

    public static function init()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::CUSTOM_FIELDS')) return;

        if ( ! EM::is_enabled(EM::CUSTOM_FIELDS)) return;

        ProfileContactInfoSettingsPage::get_instance();
        CustomProfileFieldsSettingsPage::get_instance();
        WPUserProfileCustomField::get_instance();
    }
}