<?php

namespace ProfilePress\Core\Classes;

class DisableConcurrentLogins
{
    public function __construct()
    {
        add_action('set_auth_cookie', [$this, 'disable_concurrent_logins'], 10, 6);
    }

    /**
     * @param string $logged_in_cookie The logged-in cookie value.
     * @param int $expire The time the login grace period expires as a UNIX timestamp.
     *                                 Default is 12 hours past the cookie's expiration time.
     * @param int $expiration The time when the logged-in authentication cookie expires as a UNIX timestamp.
     *                                 Default is 14 days from now.
     * @param int $user_id User ID.
     * @param string $scheme Authentication scheme. Default 'logged_in'.
     * @param string $token User's session token to use for this cookie.
     */
    public function disable_concurrent_logins($logged_in_cookie, $expire, $expiration, $user_id, $scheme, $token)
    {
        if (ppress_get_setting('disable_concurrent_logins') != 'true') return;

        /**
         * WP uses $_SERVER['REMOTE_ADDR'] to get user IP when logging in
         * @see WP_Session_Tokens::create()
         */
        if (empty($_SERVER['REMOTE_ADDR'])) return;

        $all_sessions = \WP_Session_Tokens::get_instance($user_id)->get_all();

        if (empty($all_sessions)) return;

        $different_ip_sessions = array_filter($all_sessions, function ($session) {
            return $session['ip'] !== $_SERVER['REMOTE_ADDR'];
        });

        // if login session from IP other than current user ip address exist, destroy other sessions
        if ( ! empty($different_ip_sessions) && ! empty($token)) {
            \WP_Session_Tokens::get_instance($user_id)->destroy_others($token);
        }
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