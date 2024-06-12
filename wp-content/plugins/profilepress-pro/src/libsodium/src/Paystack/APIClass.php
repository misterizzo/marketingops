<?php

namespace ProfilePress\Libsodium\Paystack;

class APIClass
{
    private $key_secret;

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
    public function __construct($key_secret)
    {
        $this->key_secret = $key_secret;

        $this->api_url = 'https://api.paystack.co/';
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
        $headers = wp_parse_args($headers, [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => sprintf('Bearer %s', $this->key_secret)
        ]);

        $request_args = [
            'method'     => $method,
            'timeout'    => 30,
            'headers'    => $headers,
            'user-agent' => 'ProfilePress/' . PPRESS_VERSION_NUMBER . '; ' . get_bloginfo('name')
        ];

        if ( ! empty($body) && 'GET' !== $method) {
            $request_args['body'] = json_encode($body);
        }

        // In a few rare cases, we may be providing a full URL to `$endpoint` instead of just the path.
        $api_url = ('https://' === substr($endpoint, 0, 8)) ? $endpoint : $this->api_url . $endpoint;

        $response = wp_remote_request($api_url, $request_args);

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $this->last_response_code = intval(wp_remote_retrieve_response_code($response));

        return json_decode(wp_remote_retrieve_body($response));
    }
}