<?php

namespace ProfilePress\Libsodium\Recaptcha;

use ProfilePress\Core\Classes\FormRepository as FR;
use WP_Error;

class RegistrationForm extends Recaptcha
{
    public static function initialize()
    {
        add_filter('ppress_registration_validation', array(__CLASS__, 'add_recaptcha_registration_form'), 10, 2);
    }

    public static function add_recaptcha_registration_form($reg_errors, $form_id)
    {
        if (self::is_recaptcha_found($form_id, FR::REGISTRATION_TYPE)) {

            if ( ! self::captcha_verification()) {
                $reg_errors = new WP_Error('failed_registration_captcha_verification', self::$error_message);
            }
        }

        return $reg_errors;
    }
}