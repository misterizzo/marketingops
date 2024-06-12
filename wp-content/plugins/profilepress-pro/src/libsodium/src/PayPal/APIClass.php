<?php

namespace ProfilePress\Libsodium\PayPal;

class APIClass
{
    private static $db_key = 'ppress_paypal_token';

    private $token_data;

    private $client_id;

    private $secret;

    private $access_token;

    private $api_url;

    /**
     * Response code from the last API request.
     *
     * @var int
     */
    public $last_response_code;

    /**
     * @throws \Exception
     */
    public function __construct($client_id, $secret)
    {
        $this->token_data = get_option(self::$db_key, []);

        $this->client_id = $client_id;
        $this->secret    = $secret;

        $this->api_url = ppress_is_test_mode() ? 'https://api-m.sandbox.paypal.com/' : 'https://api-m.paypal.com/';

        $this->access_token = $this->is_token_expired() ? $this->get_paypal_access_token() : $this->token_data['access_token'];
    }

    private function is_token_expired()
    {
        if (isset($this->token_data['expires_in'])) {
            $expire_time = (int)$this->token_data['expires_in'] - (10 * MINUTE_IN_SECONDS);
            if (time() < $expire_time) return false;
        }

        return true;
    }

    /**
     * @throws \Exception
     */
    private function get_paypal_access_token()
    {
        $response = wp_remote_post(
            $this->api_url . 'v1/oauth2/token',
            [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->client_id . ':' . $this->secret),
                    'Content-Type'  => 'application/x-www-form-urlencoded'
                ],
                'body'    => ['grant_type' => 'client_credentials'],
                'timeout' => 15,
            ]
        );

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ( ! ppress_is_http_code_success($response_code) && is_string($response_body)) {
            throw new \Exception($response_body, intval($response_code));
        }

        $token = json_decode($response_body);

        if (isset($token->access_token)) {

            update_option(self::$db_key, [
                'access_token' => $token->access_token,
                'app_id'       => $token->app_id,
                'expires_in'   => (int)time() + $token->expires_in
            ]);
        }

        return $token->access_token;
    }

    /**
     * Makes an API request.
     *
     * @param string $endpoint API endpoint.
     * @param array $body Array of data to send in the request.
     * @param array $headers Array of headers.
     * @param string $method HTTP method.
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function make_request($endpoint, $body = array(), $headers = array(), $method = 'POST')
    {
        $headers = wp_parse_args($headers, array(
            'Content-Type'                  => 'application/json',
            'Authorization'                 => sprintf('Bearer %s', $this->access_token),
            "PayPal-Partner-Attribution-Id" => 'ProfilePress_SP_PPCP',
        ));

        $request_args = array(
            'method'     => $method,
            'timeout'    => 15,
            'headers'    => $headers,
            'user-agent' => 'ProfilePress/' . PPRESS_VERSION_NUMBER . '; ' . get_bloginfo('name')
        );

        if ( ! empty($body)) {
            $request_args['body'] = json_encode($body);
        }

        // In a few rare cases, we may be providing a full URL to `$endpoint` instead of just the path.
        $api_url = ('https://' === substr($endpoint, 0, 8)) ? $endpoint : $this->api_url . $endpoint;

        $response = wp_remote_request($api_url, $request_args);

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $this->last_response_code = intval(wp_remote_retrieve_response_code($response));

        $data = json_decode(wp_remote_retrieve_body($response));

        if (isset($data->error) && $data->error == 'invalid_token') {
            $this->get_paypal_access_token(); // fetch a new access token
        }

        return $data;
    }
}