<?php

namespace ProfilePress\Core\Classes;

class BlockRegistrations
{
    public static function init()
    {
        add_filter('ppress_registration_validation', array(__CLASS__, 'do_action'), 999999, 3);
    }

    /**
     * @param string $user_email the user email
     * @param array $bucket the array of emails to check against
     *
     * @return bool
     */
    private static function is_email_matches($user_email, $bucket)
    {
        $explode = explode('@', $user_email);

        $email_domain = $explode[1];

        preg_match('/.+@[^.]+\.(.+)/', $user_email, $matches);
        $domain_pathinfo = pathinfo($email_domain);

        $email_tld = $matches[1] ?? $domain_pathinfo['extension'] ?? '';

        return in_array($user_email, $bucket) ||
               in_array('@' . $email_domain, $bucket) ||
               in_array('.' . $email_tld, $bucket);
    }

    public static function do_action($reg_errors, $form_id, $user_data)
    {
        if ( ! empty($user_data['user_email'])) {

            $user_email = $user_data['user_email'];

            $blocked_error_message = apply_filters(
                'ppress_blocked_user_email_error_message',
                __('The email address you are registering with is not supported.', 'wp-user-avatar'),
                $user_data, $form_id
            );

            $blocked_email_addresses_list = ppress_settings_by_key('blocked_email_addresses', '');

            $allowed_email_addresses_list = ppress_settings_by_key('allowed_email_addresses', '');

            if (empty($blocked_email_addresses_list) && ! empty($allowed_email_addresses_list)) {

                $allowed_email_addresses = array_map(
                    'trim',
                    explode("\n", $allowed_email_addresses_list)
                );


                if (is_array($allowed_email_addresses) && ! empty($allowed_email_addresses)) {

                    if ( ! self::is_email_matches($user_email, $allowed_email_addresses)) {
                        $reg_errors->add('blocked_email_address', $blocked_error_message);
                    }
                }
            }

            if ( ! empty($blocked_email_addresses_list) && ! empty($allowed_email_addresses_list)) {

                $allowed_email_addresses = array_map('trim', explode("\n", $allowed_email_addresses_list));

                if (is_array($allowed_email_addresses) && ! empty($allowed_email_addresses)) {

                    if (self::is_email_matches($user_email, $allowed_email_addresses)) {
                        return $reg_errors;
                    }
                }
            }

            if ( ! empty($blocked_email_addresses_list)) {

                $blocked_email_addresses = array_map('trim', explode("\n", $blocked_email_addresses_list));

                if (is_array($blocked_email_addresses) && ! empty($blocked_email_addresses)) {

                    if (self::is_email_matches($user_email, $blocked_email_addresses)) {
                        $reg_errors->add('blocked_email_address', $blocked_error_message);
                    }
                }
            }
        }

        return $reg_errors;
    }
}