<?php

namespace ProfilePress\Libsodium\Recaptcha;

use ProfilePress\Core\Classes\FormRepository as FR;
use WP_Error;

class EditProfileForm extends Recaptcha
{
    public static function initialize()
    {
        add_filter('ppress_edit_profile_validation', array(__CLASS__, 'recaptcha_edit_profile_form'), 10, 2);
    }

    public static function recaptcha_edit_profile_form($validation_errors, $form_id)
    {
        if (self::is_recaptcha_found($form_id, FR::EDIT_PROFILE_TYPE)) {

            if ( ! self::captcha_verification()) {
                $validation_errors = new WP_Error('failed_captcha_verification', self::$error_message);
            }
        }

        return $validation_errors;
    }
}