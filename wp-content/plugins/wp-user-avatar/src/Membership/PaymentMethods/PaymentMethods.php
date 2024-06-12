<?php

namespace ProfilePress\Core\Membership\PaymentMethods;

use ProfilePress\Core\Membership\PaymentMethods\BankTransfer\BankTransfer;
use ProfilePress\Core\Membership\PaymentMethods\Stripe\Stripe;

class PaymentMethods
{
    public function __construct()
    {
        $this->registered_methods();
    }

    /**
     * @return AbstractPaymentMethod[]
     */
    public function registered_methods()
    {
        $methods = [
            StoreGateway::get_instance(),
            Stripe::get_instance(),
            BankTransfer::get_instance()
        ];

        return apply_filters('ppress_payment_methods', $methods);
    }

    /**
     * @return PaymentMethodInterface[]
     */
    public function get_all($sort = false)
    {
        $bucket = [];

        if (count($this->registered_methods()) > 0) {

            foreach ($this->registered_methods() as $method) {
                $bucket[$method->id] = $method;
            }
        }

        if ($sort === true) {
            $sorted_vals = ppress_get_payment_method_setting('sorted_payment_methods', [], true);
            if (is_array($sorted_vals) && ! empty($sorted_vals)) {
                $sorted_bucket = [];
                $bucket_keys   = array_keys($bucket);

                foreach ($sorted_vals as $sorted_val) {
                    if (in_array($sorted_val, $bucket_keys)) {
                        $sorted_bucket[$sorted_val] = $bucket[$sorted_val];
                    }
                }

                // important to merge any gateway not saved in a pristine sorted gateway db value
                $bucket = array_merge($sorted_bucket, $bucket);
            }
        }

        return $bucket;
    }

    /**
     * Returns payment method ID and title.
     *
     * @return PaymentMethodInterface[]
     */
    public function get_enabled_methods($include_backend_only = false)
    {
        $bucket = [];

        foreach ($this->get_all(true) as $method) {
            if ($method->is_enabled()) {

                if ( ! $include_backend_only && $method->is_backend_only()) continue;

                $bucket[$method->id] = $method;
            }
        }

        return $bucket;
    }

    /**
     * @return false|string
     */
    public function get_default_method()
    {
        $default = array_key_first($this->get_enabled_methods());
        if ( ! $default) $default = '';

        return ppress_var(get_option(PPRESS_PAYMENT_METHODS_OPTION_NAME, []), 'default_payment_method', $default, true);
    }

    /**
     * @param $id
     *
     * @return AbstractPaymentMethod|false
     */
    public function get_by_id($id)
    {
        return ppress_var($this->get_all(), $id);
    }

    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}