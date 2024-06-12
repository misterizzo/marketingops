<?php

namespace ProfilePress\Libsodium\SocialLogin;

use ProfilePress\Core\Classes\RegistrationAuth;
use ProfilePress\Libsodium\UserModeration\UserModeration;

class ProcessSocialLogin
{
    public $profile_data;

    public $provider;

    /**
     * Constructor poop
     *
     * @param object $hybrid_profile_data
     */
    public function __construct($hybrid_profile_data, $provider)
    {
        $this->provider     = $provider;
        $this->profile_data = $hybrid_profile_data;

        /** @var string IDp identifier */
        $this->identifier = $hybrid_profile_data->identifier;

        $this->email = $this->construct_email($hybrid_profile_data);

        /** @var string username username */
        $this->username = $this->construct_username($hybrid_profile_data);

        $this->password   = wp_generate_password();
        $this->firstName  = ! empty($hybrid_profile_data->firstName) ? $hybrid_profile_data->firstName : '';
        $this->lastName   = ! empty($hybrid_profile_data->lastName) ? $hybrid_profile_data->lastName : '';
        $this->gender     = ! empty($hybrid_profile_data->gender) ? $hybrid_profile_data->gender : '';
        $this->bio        = ! empty($hybrid_profile_data->description) ? $hybrid_profile_data->description : '';
        $this->website    = ! empty($hybrid_profile_data->webSiteURL) ? $hybrid_profile_data->webSiteURL : '';
        $this->avatar_url = self::copy_social_avatar_locally($hybrid_profile_data->photoURL);

        // if user identifier already exist or email exist, login in the user otherwise create an account.
        if ($this->_identifier_exist($hybrid_profile_data->identifier)) {
            $login_response = $this->login_user($hybrid_profile_data->identifier);
        } elseif (email_exists($this->email)) {
            $login_response = $this->login_user();
        } else {
            $register = $this->register_and_login();
        }

        if (isset($login_response) && is_wp_error($login_response)) {
            wp_safe_redirect(add_query_arg('pp-sl-error', rawurlencode($login_response->get_error_message()), ppress_login_url()));
            exit;
        }

        if (isset($register) && is_wp_error($register)) {
            wp_safe_redirect(add_query_arg('pp-sl-error', rawurlencode($register->get_error_message()), ppress_login_url()));
            exit;
        }
    }

    /**
     * Get the username returned by the IDp or create a unique username prefixed with 'user' followed by a random numbers.
     *
     * @param $hybrid_profile_data
     *
     * @return string
     */
    public function construct_username($hybrid_profile_data)
    {
        if ( ! empty($hybrid_profile_data->displayName)) {
            $username = sanitize_key($hybrid_profile_data->displayName);
        } elseif ( ! empty($hybrid_profile_data->firstName) || ! empty($hybrid_profile_data->lastName)) {
            $username = ! empty($hybrid_profile_data->firstName) ? $hybrid_profile_data->firstName : $hybrid_profile_data->lastName;
        } else {
            $username = 'user' . mt_rand(1000, 9999);
        }

        // If Facebook social login, get the username from email address
        if (in_array($this->provider, ['linkedin', 'facebook'])) {
            $parts    = explode("@", $this->email);
            $username = trim($parts[0]);
        }

        // If GitHub social login, get the username from github profile url slug
        if ($this->provider == 'github') {
            preg_match('#.+.com/(.+)$#', $hybrid_profile_data->profileURL, $matches);
            $username = $matches[1];
        }

        $append     = 1;
        $o_username = $username;
        while (username_exists($username)) {
            $username = $o_username . $append;
            $append++;
        }

        return strtolower($username);
    }

    /**
     * Return the user's IDP email or create one depending on the IDP used.
     *
     * @param $hybrid_profile_data
     *
     * @return string
     */
    public function construct_email($hybrid_profile_data)
    {
        if (empty($hybrid_profile_data->emailVerified) && empty($hybrid_profile_data->email)) {
            $val = $this->username . '@' . $this->provider . '.com';
        } else {
            $val = ! empty($hybrid_profile_data->emailVerified) && is_string($hybrid_profile_data->emailVerified) ? $hybrid_profile_data->emailVerified : $hybrid_profile_data->email;
        }

        return strtolower($val);
    }


    /**
     * Copy the profile picture to local server with the name returned.
     *
     * @param string $avatar_url
     *
     * @return string
     */
    public function copy_social_avatar_locally($avatar_url)
    {
        if (empty($avatar_url)) return '';

        // use regex to spit the avatar url into http://xxxx and .jpg|png|gif
        preg_match('/.+\.(jpg|png|gif)/', $avatar_url, $matches);

        /* create an md5 image name and join the extension to the md5hash and copy to local server */
        // upload location + file
        $filename = md5(time() . rand());
        // file extension
        $file_ext = ! empty($matches[1]) ? $matches[1] : 'png';

        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer"      => false,
                "verify_peer_name" => false,
            ),
        );

        $fileLocationAndPath = PPRESS_AVATAR_UPLOAD_DIR . "$filename.$file_ext";
        if ( ! file_exists(PPRESS_AVATAR_UPLOAD_DIR)) {
            mkdir(PPRESS_AVATAR_UPLOAD_DIR);
        }

        // the copy or upload shebang
        $copy = copy($avatar_url, $fileLocationAndPath, stream_context_create($arrContextOptions));

        if ($copy) {
            return "$filename.$file_ext";
        }

        return '';
    }

    /**
     * Register the user to WordPress
     * @return int|\WP_Error
     */
    public function register_user()
    {
        $args = array(
            'user_login'  => $this->username,
            'user_email'  => $this->email,
            'user_url'    => $this->website,
            'user_pass'   => $this->password,
            'first_name'  => $this->firstName,
            'last_name'   => $this->lastName,
            'description' => $this->bio,
            'role'        => apply_filters('ppress_social_signup_role', get_option('default_role'))
        );

        $preflight_check = apply_filters('ppress_before_social_signup_init', false, $args, $this);

        if (is_wp_error($preflight_check)) return $preflight_check;

        do_action('ppress_before_social_signup', $args, $this->provider);

        $user_id = wp_insert_user(apply_filters('ppress_social_user_signup_args', $args));

        if (is_int($user_id)) {

            wp_new_user_notification($user_id, null, 'admin');

            add_user_meta($user_id, '_pp_signup_via', $this->provider);

            RegistrationAuth::send_welcome_email($user_id, $this->password);

            if (self::is_moderation_enabled()) {

                // if moderation is active, set new registered users as pending
                if (UserModeration::moderation_is_active()) {
                    UserModeration::make_pending($user_id);
                }
            }


            do_action('ppress_after_social_signup', $args, $user_id, $this->provider);
        }

        return $user_id;
    }


    /**
     * Login the user.
     *
     * @param string $identifier id unique identifier
     *
     * @param bool $registration_flag
     *
     * @return \WP_Error|null
     */
    public function login_user($identifier = '', $registration_flag = false)
    {
        if ( ! empty($identifier)) {
            $user_id = $this->get_user_id_by_meta_data('pp_social_identifier', $identifier);
        } else {
            $user_id = self::_get_user_id($this->email);
        }

        $preflight_check = apply_filters('ppress_before_social_login_init', false, $user_id, $this);

        if (is_wp_error($preflight_check)) return $preflight_check;

        $username = ppress_get_username_by_id($user_id);
        // check if the user is pending approval or blocked.
        if (UserModeration::moderation_is_active()) {

            $account_active = UserModeration::login_authentication('', $username, '');

            if (is_wp_error($account_active)) return $account_active;
        }

        do_action('ppress_before_social_login', $user_id, $this->profile_data);

        $secure_cookie = '';
        if (defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN === true) {
            $secure_cookie = true;
        }

        wp_set_auth_cookie($user_id, true, $secure_cookie);
        wp_set_current_user($user_id);

        $social_login_redirect = apply_filters('ppress_login_redirect', ppress_login_redirect(), 'ppress_social_login', get_userdata($user_id));

        /** @var string $social_login_redirect filter to specify custom url redirect for social login */
        $social_login_redirect = apply_filters('ppress_social_login_redirect', $social_login_redirect, $this->provider, $user_id, $registration_flag);

        $user = get_user_by('id', $user_id);

        do_action('wp_login', $user->user_login, $user);

        nocache_headers();

        wp_safe_redirect($social_login_redirect);
        exit;
    }

    /**
     * If no account exist for the user, register and log the user in.
     *
     * @return int|\WP_Error
     */
    public function register_and_login()
    {
        $registered_id = $this->register_user();

        // if user id is integer, means user was successfully logged in else return WP_Error.
        if (is_int($registered_id)) {
            //update gender meta
            update_user_meta($registered_id, 'gender', $this->gender);
            // update custom avatar
            update_user_meta($registered_id, 'pp_profile_avatar', $this->avatar_url);

            // idp unique identifier
            update_user_meta($registered_id, 'pp_social_identifier', $this->identifier);

            do_action('ppress_after_social_registration', $registered_id, $this->profile_data);

            $login_response = $this->login_user($this->identifier, true);

            // if login return WP_Error, return the error
            if (is_wp_error($login_response)) {
                return $login_response;
            }
        }

        if (is_wp_error($registered_id)) {
            return $registered_id;
        }
    }

    /**
     * Return the user's ID.
     *
     * @param $email
     *
     * @return int
     */
    private static function _get_user_id($email)
    {
        $user = get_user_by('email', $email);
        if (false !== $user) {
            return $user->ID;
        }
    }

    /**
     * Check if the identifier already exist in WordPress
     *
     * @param $identifier
     *
     * @return bool
     */
    private function _identifier_exist($identifier)
    {
        global $wpdb;
        $sql         = "SELECT $wpdb->usermeta.meta_value FROM $wpdb->usermeta WHERE $wpdb->usermeta.meta_key = 'pp_social_identifier'";
        $meta_values = $wpdb->get_col($sql);

        return in_array($identifier, $meta_values);
    }

    /**
     * Return the ID of a user base on the meta key and value.
     *
     * @param $meta_key
     * @param $meta_value
     *
     * @return mixed
     */
    private function get_user_id_by_meta_data($meta_key, $meta_value)
    {
        $user_query = new \WP_User_Query(
            array(
                'meta_key'   => $meta_key,
                'meta_value' => $meta_value,
            )
        );

        // return user ID
        return $user_query->results[0]->data->ID;
    }

    private static function is_moderation_enabled()
    {
        return apply_filters('ppress_enable_moderation_social_login', UserModeration::moderation_is_active());
    }
}