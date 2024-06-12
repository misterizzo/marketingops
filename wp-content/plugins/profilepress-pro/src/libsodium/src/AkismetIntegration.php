<?php

namespace ProfilePress\Libsodium;

use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Membership\CheckoutFields;

class AkismetIntegration
{
    public static $instance_flag = false;

    public function __construct()
    {
        add_filter('ppress_registration_validation', array($this, 'detect_spam_registration'), 99, 3);

        add_filter('ppress_checkout_validation', array($this, 'detect_spam_checkout'), 999, 3);

        add_action('wp_cspa_field_before_text_field', array($this, 'api_key_activation_notice'));

        add_filter('ppress_settings_page_args', [$this, 'settings_page']);

        add_action('wp_cspa_after_persist_settings', [$this, 'update_api_key_status']);
    }

    public function settings_page($args)
    {
        $args['pp_aki_settings'] = [
            'tab_title'      => esc_html__('Akismet', 'profilepress-pro'),
            'section_title'  => esc_html__('Akismet Integration', 'profilepress-pro'),
            'dashicon'       => 'dashicons-shield',
            'aki_api_key'    => [
                'type'        => 'text',
                'label'       => esc_html__('API Key', 'profilepress-pro'),
                'description' => sprintf(
                    __('Enter your Akismet API key to start stopping spam registration. <a href="%s" target="_blank">Get your API key</a>', 'profilepress-pro'),
                    'https://akismet.com/get/'
                ),
            ],
            'aki_spam_error' => [
                'type'        => 'text',
                'label'       => esc_html__('Spam Registration Error', 'profilepress-pro'),
                'description' => esc_html__('Enter error message to display when a spam registration is detected by Akismet', 'profilepress-pro')
            ]
        ];

        return $args;
    }

    /**
     * Display error if API Key is invalid
     *
     * @param string $settings_key
     */
    public function api_key_activation_notice($id)
    {
        if ($id != 'aki_api_key') return;

        // if API key hasn't been saved, do not display any notice
        $api_key = $this->get_api_key();

        if (empty($api_key)) return;

        if ( ! $this->is_api_key_valid()) {
            echo '<div style="background: #FFE0E0; padding: 2px 5px; max-width:490px;width:100%">';
            _e('The key you entered is invalid. Please double-check it.', 'profilepress-pro');
            echo '</div>';
        } else {
            echo '<div style="background:#46b450; color: #fff; padding: 2px 5px; max-width:490px;width:100%">';
            _e('API key successfully validated.', 'profilepress-pro');
            echo '</div>';
        }
    }

    /**
     * Return Akismet API key.
     *
     * @return string
     */
    public function get_api_key()
    {
        return apply_filters('ppress_aki_get_api_key', ppress_settings_by_key('aki_api_key'));
    }

    /**
     * Is Akismet API key valid?
     *
     * @return bool
     */
    public function is_api_key_valid()
    {
        return get_option('ppress_aki_key_status', false) != 'invalid';
    }

    /**
     * Helper method to make HTTP POST requests.
     *
     * @param mixed $request_data
     * @param $path
     *
     * @return array
     */
    public function http_post($request_data, $path)
    {
        $pp_akismet_ua = sprintf('WordPress/%s | ProfilePress/%s', $GLOBALS['wp_version'], constant('PPRESS_VERSION_NUMBER'));
        $pp_akismet_ua = apply_filters('ppress_aki_akismet_ua', $pp_akismet_ua);

        $api_key = $this->get_api_key();
        $host    = 'rest.akismet.com';

        if ( ! empty($api_key)) $host = $api_key . '.' . $host;

        $http_host = $host;

        $http_args = array(
            'body'        => $request_data,
            'headers'     => array(
                'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option('blog_charset'),
                'Host'         => $host,
                'User-Agent'   => $pp_akismet_ua,
            ),
            'httpversion' => '1.0',
            'timeout'     => 15
        );

        $akismet_url = $http_akismet_url = "http://{$http_host}/1.1/{$path}";

        /**
         * Try SSL first; if that fails, try without it and don't try it again for a while.
         */

        $ssl = $ssl_failed = false;

        // Check if SSL requests were disabled fewer than X hours ago.
        $ssl_disabled = get_option('ppress_akismet_ssl_disabled');

        if ($ssl_disabled && $ssl_disabled < (time() - 60 * 60 * 24)) { // 24 hours
            $ssl_disabled = false;
            delete_option('ppress_akismet_ssl_disabled');
        } elseif ($ssl_disabled) {
            do_action('ppress_akismet_ssl_disabled');
        }

        if ( ! $ssl_disabled && function_exists('wp_http_supports') && ($ssl = wp_http_supports(array('ssl')))) {
            $akismet_url = set_url_scheme($akismet_url, 'https');
        }

        $response = wp_remote_post($akismet_url, $http_args);

        if ($ssl && is_wp_error($response)) {

            // Intermittent connection problems may cause the first HTTPS
            // request to fail and subsequent HTTP requests to succeed randomly.
            // Retry the HTTPS request once before disabling SSL for a time.
            $response = wp_remote_post($akismet_url, $http_args);

            if (is_wp_error($response)) {
                $ssl_failed = true;

                // Try the request again without SSL.
                $response = wp_remote_post($http_akismet_url, $http_args);
            }
        }

        if (is_wp_error($response)) {
            return array('', '');
        }

        if ($ssl_failed) {
            // The request failed when using SSL but succeeded without it. Disable SSL for future requests.
            update_option('ppress_akismet_ssl_disabled', time());
        }

        return array(wp_remote_retrieve_headers($response), wp_remote_retrieve_body($response));
    }

    /**
     * API request to check key status.
     *
     * @param string $key
     *
     * @return array
     */
    public function check_key_status($key)
    {
        return $this->http_post(array('key' => $key, 'blog' => get_option('home')), 'verify-key');
    }

    /**
     * API request to check for spam registration.
     *
     * @param array $user_data
     *
     * @return array
     */
    public function check_for_spam($user_data)
    {
        $request_data = array(
            'blog'                 => get_option('home'),
            'user_ip'              => ppress_get_ip_address(),
            'user_agent'           => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
            'referrer'             => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null,
            'permalink'            => ppress_site_url_without_scheme(),
            'comment_type'         => 'signup',
            'comment_author'       => $user_data['user_login'],
            'comment_author_email' => $user_data['user_email'],
            'comment_author_url'   => $user_data['user_url'],
            'blog_lang'            => get_locale(),
        );

        foreach ($_SERVER as $key => $value) {
            if ( ! is_string($value)) {
                continue;
            }

            if (preg_match("/^HTTP_COOKIE/", $key)) {
                continue;
            }

            // Send any potentially useful $_SERVER vars, but avoid sending junk we don't need.
            if (preg_match("/^(HTTP_|REMOTE_ADDR|REQUEST_URI|DOCUMENT_URI)/", $key)) {
                $request_data["$key"] = $value;
            }
        }

        return $this->http_post($request_data, 'comment-check');
    }

    /**
     * Verify Akismet API key validity.
     *
     * @param string $key
     *
     * @return string
     */
    public function verify_key($key)
    {
        $response = $this->check_key_status($key);

        if ($response[1] != 'valid' && $response[1] != 'invalid') {
            return 'failed';
        }

        return $response[1];
    }

    /**
     * API request to detect and combat spam registration.
     *
     * @param mixed $reg_errors
     * @param int $form_id
     * @param array $user_data
     *
     * @return mixed
     */
    public function detect_spam_registration($reg_errors, $form_id, $user_data)
    {
        if ($this->is_api_key_valid()) {

            $response = $this->check_for_spam($user_data);

            if ($response[1] == 'true') {

                $error_message = ppress_settings_by_key('aki_spam_error', __('Spam registration detected. Please try again.', 'profilepress-pro'), true);

                $reg_errors = new \WP_Error('akismet_spam_detected', apply_filters('ppress_aki_spam_registration_error', $error_message));
            }
        }

        return $reg_errors;
    }

    /**
     * API request to detect and combat spam checkout.
     *
     * @param \WP_Error $errors
     * @param int $plan_id
     * @param array $user_data
     *
     * @return mixed
     */
    public function detect_spam_checkout($errors, $plan_id, $user_data)
    {
        if ($this->is_api_key_valid()) {

            $response = $this->check_for_spam([
                'user_login' => ppress_var(
                    $user_data,
                    CheckoutFields::ACCOUNT_USERNAME,
                    sprintf(
                        '%s %s',
                        ppress_var($user_data, CheckoutFields::ACCOUNT_FIRST_NAME, ''),
                        ppress_var($user_data, CheckoutFields::ACCOUNT_LAST_NAME, '')
                    ),
                    true
                ),
                'user_email' => ppress_var($user_data, CheckoutFields::ACCOUNT_EMAIL_ADDRESS, ''),
                'user_url'   => ppress_var($user_data, CheckoutFields::ACCOUNT_WEBSITE, '')
            ]);

            if ($response[1] == 'true') {

                $error_message = ppress_settings_by_key('aki_spam_error', __('Spam registration detected. Please try again.', 'profilepress-pro'), true);

                $errors->add('akismet_spam_detected', apply_filters('ppress_aki_spam_checkout_error', $error_message));
            }
        }

        return $errors;
    }

    /**
     * Update Akismet API key status.
     *
     * @return string
     */
    public function update_api_key_status()
    {
        $key = $this->get_api_key();

        $status = $this->verify_key($key);

        update_option('ppress_aki_key_status', $status);
    }

    /**
     * @return self|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::AKISMET')) return;

        if ( ! EM::is_enabled(EM::AKISMET)) return;

        static $instance;

        if ( ! isset($instance)) {
            $instance = new self;
        }

        return $instance;
    }
}