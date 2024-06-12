<?php

namespace ProfilePress\Libsodium\MeteredPaywall;

use ProfilePress\Core\Base;

class IPBlocker
{
    const DB_KEY = 'ppress_paywall_ips';

    protected $db_table;

    public function __construct()
    {
        $this->db_table = Base::meta_data_db_table();

        add_action('ppress_metered_paywall_exceeded_free_view_limit', [$this, 'save_block_ip']);

        add_filter('ppress_metered_paywall_is_exceeded_free_view_limit', [$this, 'ip_checker']);
    }

    public function save_block_ip()
    {
        if (DoRestriction::is_ip_blocker_enabled()) {
            $this->persist(ppress_get_ip_address());
        }
    }

    public function ip_checker($result)
    {
        if (DoRestriction::is_ip_blocker_enabled() && $this->exist(ppress_get_ip_address())) {
            $result = true;
        }

        return $result;
    }

    /**
     * @param $ip
     *
     * @return bool|int
     */
    protected function persist($ip)
    {
        global $wpdb;

        if ($this->exist($ip)) return false;

        return $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$this->db_table}
					(meta_key, meta_value, flag)
					VALUES (%s, %s, %d)",
                self::DB_KEY,
                bin2hex(inet_pton($ip)),
                filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? '6' : '4'
            )
        );
    }

    /**
     * @param string $ip
     *
     * @return bool
     */
    protected function exist($ip)
    {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT meta_value
					FROM {$this->db_table}
					WHERE meta_key = %s AND meta_value = %s",
            self::DB_KEY, bin2hex(inet_pton($ip))
        );

        return ! empty($wpdb->get_results($sql));
    }

    public function clear_log()
    {
        global $wpdb;

        return $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->db_table} WHERE meta_key = %s",
                self::DB_KEY
            )
        );
    }

    /**
     * @return array
     */
    public function last_100()
    {
        global $wpdb;

        $result = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_value, flag
			            FROM {$this->db_table}
			            WHERE meta_key = %s
			            ORDER BY id DESC
			            LIMIT 100",
                self::DB_KEY
            )
        );

        if ( ! $result || ! is_array($result) || empty($result)) $result = [];

        return $result;
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