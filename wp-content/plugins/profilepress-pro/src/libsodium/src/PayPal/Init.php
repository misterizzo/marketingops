<?php

namespace ProfilePress\Libsodium\PayPal;

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
        $payment_methods[] = PayPal::get_instance();

        return $payment_methods;
    }

    public static function process_amount($price, $currency = '')
    {
        if (empty($currency)) {
            $currency = ppress_get_currency();
        }

        $zero_decimals = ['TWD', 'JPY', 'HUF'];

        if (in_array($currency, $zero_decimals)) {
            return Calculator::init($price)->toScale(0)->val();
        }

        return Calculator::init($price)->toScale(2)->val();
    }

    /**
     * @return self|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::PAYPAL')) return;

        if ( ! EM::is_enabled(EM::PAYPAL)) return;

        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}