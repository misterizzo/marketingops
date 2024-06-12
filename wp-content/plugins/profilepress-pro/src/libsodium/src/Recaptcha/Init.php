<?php

namespace ProfilePress\Libsodium\Recaptcha;

use ProfilePress\Core\Classes\ExtensionManager as EM;

class Init
{
    public static $instance_flag = false;

    public static function init()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::RECAPTCHA')) return;

        if ( ! EM::is_enabled(EM::RECAPTCHA)) return;

        Recaptcha::initialize();
        EditProfileForm::initialize();
        LoginForm::initialize();
        PasswordResetForm::initialize();
        RegistrationForm::initialize();
        CheckoutForm::initialize();
    }
}