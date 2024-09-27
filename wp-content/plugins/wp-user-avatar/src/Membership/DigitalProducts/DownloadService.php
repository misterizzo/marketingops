<?php

namespace ProfilePress\Core\Membership\DigitalProducts;

use ProfilePress\Core\Base;
use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;

class DownloadService
{
    public function get_download_file_url($order_key, $file_index, $expiry = 0)
    {
        if ($expiry == '' || intval($expiry) === 0) {
            $ttl = 2147472000;
        } else {
            $ttl = time() + (intval($expiry) * DAY_IN_SECONDS);
        }

        $order = OrderFactory::fromOrderKey($order_key);

        if ( ! $order->exists()) return false;

        $args = array_fill_keys($this->get_url_token_parameters(), '');

        $args['ppress_file'] = rawurlencode(sprintf('%d:%d:%d', $order->id, $order->plan_id, $file_index));
        $args['ttl']         = rawurlencode($ttl);

        $args['token'] = $this->get_download_token(add_query_arg(array_filter($args), untrailingslashit(site_url())));

        return add_query_arg(array_filter($args), site_url('index.php'));
    }

    /**
     * Generates a token for a given URL.
     *
     * @param string $url URL to generate a token for.
     *
     * @return string Token for the URL.
     */
    function get_download_token($url = '')
    {
        $secret = hash('sha256', wp_salt());

        $url   = add_query_arg(['secret' => $secret], $url);
        $parts = wp_parse_url($url);

        // In the event there isn't a path, set an empty one so we can MD5 the token
        if ( ! isset($parts['path'])) $parts['path'] = '';

        parse_str($parts['query'], $result);

        $query_parts = [
            'ppress_file' => ppress_var($result, 'ppress_file', ''),
            'ttl'         => ppress_var($result, 'ttl', '')
        ];

        return hash_hmac('sha256', $parts['path'] . '?' . implode('&', $query_parts), wp_salt('ppress_file_download_link'));
    }

    public function get_url_token_parameters()
    {
        return ['ppress_file', 'ttl', 'token'];
    }

    /**
     * Generate a token for a URL and match it against the existing token to make
     * sure the URL hasn't been tampered with.
     *
     * @param string $url URL to test.
     *
     * @return bool
     */
    function validate_url_token($url = '')
    {
        $parts      = parse_url($url);
        $query_args = [];

        if (isset($parts['query'])) {

            parse_str($parts['query'], $query_args);

            // If the TTL is in the past, die out before we go any further.
            if (isset($query_args['ttl']) && time() > $query_args['ttl']) {
                wp_die(apply_filters('ppress_download_link_expired_text', esc_html__('Sorry but your download link has expired.', 'wp-user-avatar')), esc_html__('Error', 'wp-user-avatar'), ['response' => 403]);
            }

            // Collect the allowed tags in proper order, remove all tags, and re-add only the allowed ones.
            $validated_query_args = [];

            foreach ($this->get_url_token_parameters() as $key) {
                if (true === array_key_exists($key, $query_args)) {
                    $validated_query_args[$key] = $query_args[$key];
                }
            }

            // strtok allows a quick clearing of existing query string parameters, so we can re-add the allowed ones.
            $url = add_query_arg($validated_query_args, strtok($url, '?'));

            if (isset($query_args['token']) && hash_equals($query_args['token'], $this->get_download_token($url))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $args
     *
     * @return bool|int
     */
    public function add_download_log($args)
    {
        $args = wp_parse_args($args, [
            'plan_id'  => '',
            'file_url' => '',
            'order_id' => ''
        ]);

        return PROFILEPRESS_sql::add_meta_data(
            sprintf('fdl_%s', intval($args['order_id'])),
            [
                'plan_id'  => intval($args['plan_id']),
                'file_url' => sanitize_text_field($args['file_url']),
                'order_id' => intval($args['order_id']),
                'ip'       => ppress_get_ip_address(),
                'date'     => current_time('mysql', true)
            ],
            'ppress_download_logs'
        );
    }

    public function get_download_file_name($plan_id, $file_url)
    {
        $downloads = ppress_get_plan($plan_id)->get_downloads();

        if (
            isset($downloads['files']) &&
            ! empty($downloads['files']) &&
            ! empty($downloads['files'][$file_url])
        ) {
            return $downloads['files'][$file_url];
        }

        return false;
    }

    public function get_download_log_count($order_id = 0)
    {
        global $wpdb;

        $table = Base::meta_data_db_table();

        $replacement = ['ppress_download_logs'];
        $sql         = "SELECT COUNT(*) FROM $table WHERE flag = %s";
        if ($order_id > 0) {
            $sql           .= " AND meta_key = %s";
            $replacement[] = sprintf('fdl_%d', $order_id);
        }

        return $wpdb->get_var($wpdb->prepare($sql, $replacement));
    }

    public function get_download_log($limit = 0, $current_page = 1, $order_id = 0)
    {
        global $wpdb;

        $table = Base::meta_data_db_table();

        $order_id = (int)$order_id;

        $replacement = ['ppress_download_logs'];
        $sql         = "SELECT * FROM $table WHERE flag = %s";

        if ($order_id > 0) {
            $sql           .= " AND meta_key = %s";
            $replacement[] = sprintf('fdl_%d', $order_id);
        }

        $sql .= " ORDER BY id DESC";

        if ($limit > 0) {
            $sql           .= " LIMIT %d";
            $replacement[] = $limit;
        }

        if ($current_page > 1) {
            $sql           .= "  OFFSET %d";
            $replacement[] = ($current_page - 1) * $limit;
        }

        $result = $wpdb->get_results($wpdb->prepare($sql, $replacement), 'ARRAY_A');

        if (empty($result)) return false;

        $output = [];
        foreach ($result as $meta) {
            $output[$meta['id']] = ['id' => $meta['id']] + unserialize($meta['meta_value'], ['allowed_classes' => false]);
        }

        return $output;
    }

    /**
     * @param $order_id
     * @param $plan_id
     * @param $file_url
     *
     * @return false|int
     */
    public function get_downloads_count($order_id, $plan_id, $file_url)
    {
        $logs = $this->get_download_log(0, 1, $order_id);

        if (is_array($logs) && ! empty($logs)) {

            $download_count = count(wp_list_filter($logs, [
                    'file_url' => $file_url,
                    'order_id' => intval($order_id),
                    'plan_id'  => intval($plan_id)
                ])
            );

            return $download_count;
        }

        return false;
    }

    /**
     * Checks if a file is at its download limit
     *
     * This limit refers to the maximum number of times files connected to a plan can be downloaded.
     *
     * @param int $plan_id
     * @param int $order_id Order ID.
     * @param string $file_url
     *
     * @return bool
     *
     */
    public function is_file_at_download_limit($plan_id, $order_id, $file_url)
    {
        $plan_downloads = ppress_get_plan($plan_id)->get_downloads();

        $download_limit = $plan_downloads['download_limit'];

        if ( ! empty($file_url) && absint($download_limit) > 0) {

            $download_count = $this->get_downloads_count($order_id, $plan_id, $file_url);

            if (false !== $download_count && $download_count >= $download_limit) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $order_id
     * @param $plan_id
     * @param $file_url
     *
     * @return mixed|string
     */
    public function get_downloads_remaining($order_id, $plan_id, $file_url)
    {
        $plan_downloads = ppress_get_plan($plan_id)->get_downloads();

        $download_limit = $plan_downloads['download_limit'];

        if ( ! empty($file_url) && absint($download_limit) > 0) {
            $download_count = $this->get_downloads_count($order_id, $plan_id, $file_url);

            // max ensures lowest result is 0 and no negative integer.
            return max($download_limit - $download_count, 0);
        }

        return '&infin;';
    }

    /**
     * @return self
     */
    public static function init()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}