<?php

namespace ProfilePress\Libsodium\Recaptcha;

use ProfilePress\Core\Classes\FormRepository as FR;
use WP_Error;

class PasswordResetForm extends Recaptcha
{
    public static function initialize()
    {
        add_filter('ppress_password_reset_validation', array(__CLASS__, 'recaptcha_password_reset_form'), 10, 2);
    }

    public static function recaptcha_password_reset_form($errors, $form_id)
    {
        if (self::is_recaptcha_found($form_id, FR::PASSWORD_RESET_TYPE)) {
            if ( ! self::captcha_verification()) {
                $errors = new WP_Error('failed_captcha_verification', self::$error_message);
            }
        }

        return $errors;
    }
}