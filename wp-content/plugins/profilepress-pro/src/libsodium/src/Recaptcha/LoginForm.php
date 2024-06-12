<?php

namespace ProfilePress\Libsodium\Recaptcha;

use ProfilePress\Core\Classes\FormRepository as FR;
use WP_Error;

class LoginForm extends Recaptcha
{
    public static function initialize()
    {
        /* The built in "pp_login_validation" filter was used instead of core "authentication"
         * Because the latter affects auto login when recaptcha is activated in registration form
         * That is, authenticate will try and validate the captcha of the registration form
         * Before the "auto login" class log in the user
         */
        add_filter('ppress_login_validation', array(__CLASS__, 'add_recaptcha_login_form'), 30, 2);
    }

    /**
     * Callback function to add the reCAPTCHA to the login form
     *
     * @param $login_errors
     * @param $form_id
     *
     * @return WP_Error
     */
    public static function add_recaptcha_login_form($login_errors, $form_id)
    {
        if (self::is_recaptcha_found($form_id, FR::LOGIN_TYPE)) {
            if ( ! self::captcha_verification()) {
                $login_errors = new WP_Error('failed_login_captcha_verification', self::$error_message);
            }
        }

        return $login_errors;
    }
}