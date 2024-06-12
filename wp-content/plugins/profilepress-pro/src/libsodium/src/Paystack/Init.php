<?php

namespace ProfilePress\Libsodium\Paystack;

use ProfilePress\Core\Classes\ExtensionManager as EM;

class Init
{
    public static $instance_flag = false;

    public function __construct()
    {
        add_filter('ppress_payment_methods', [$this, 'register_payment_method']);
    }

    public function register_payment_method($payment_methods)
    {
        $payment_methods[] = Paystack::get_instance();

        return $payment_methods;
    }

    /**
     * @return self|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::PAYSTACK')) return;

        if ( ! EM::is_enabled(EM::PAYSTACK)) return;

        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}