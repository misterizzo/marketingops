<?php

namespace ProfilePress\Libsodium\Razorpay;

use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Membership\Services\Calculator;

class Init
{
    public static $instance_flag = false;

    public function __construct()
    {
        add_filter('ppress_payment_methods', [$this, 'register_payment_method']);
    }

    public function register_payment_method($payment_methods)
    {
        $payment_methods[] = Razorpay::get_instance();

        return $payment_methods;
    }

    public static function process_amount($price)
    {
        return (int)Calculator::init($price)->toScale(2)->multipliedBy(100)->toScale(0)->val();
    }

    /**
     * @return self|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::RAZORPAY')) return;

        if ( ! EM::is_enabled(EM::RAZORPAY)) return;

        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}