<?php

namespace ProfilePress\Libsodium\Recaptcha;

use ProfilePress\Core\Classes\FormRepository as FR;
use WP_Error;

class CheckoutForm extends Recaptcha
{
    public static function initialize()
    {
        if (ppress_settings_by_key('checkout_recaptcha') == 'true') {

            add_action('ppress_checkout_before_submit_button', [__CLASS__, 'checkout_recaptcha_display']);

            add_filter('ppress_checkout_validation', [__CLASS__, 'checkout_validation'], 99);
        }
    }

    public static function checkout_recaptcha_display()
    {
        echo self::display_captcha();
    }

    /**
     * @param $checkout_errors
     *
     * @return WP_Error
     */
    public static function checkout_validation($checkout_errors)
    {
        if ( ! self::captcha_verification()) {
            $checkout_errors = new WP_Error('ppress_checkout_captcha_verification_error', self::$error_message);
        }

        return $checkout_errors;
    }
}