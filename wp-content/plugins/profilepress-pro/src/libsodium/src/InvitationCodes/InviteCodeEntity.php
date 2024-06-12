<?php

namespace ProfilePress\Libsodium\InvitationCodes;

use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Core\Membership\Models\AbstractModel;
use ProfilePress\Core\Membership\Models\ModelInterface;

/**
 * @property int $id
 * @property string $invite_code
 * @property string $usage_limit
 * @property string $membership_plan
 * @property string $expiry_date
 * @property string $status
 */
class InviteCodeEntity extends AbstractModel implements ModelInterface
{
    protected $id = 0;

    protected $invite_code = '';

    protected $usage_limit = '';

    protected $membership_plan = '';

    protected $expiry_date = '';

    public function __construct($id_or_code)
    {
        if (is_numeric($id_or_code)) {
            $data = PROFILEPRESS_sql::get_meta_data_by_id($id_or_code);
        } else {
            $data = PROFILEPRESS_sql::get_meta_data_by_flag($id_or_code);
        }

        if (isset($data[0]) && is_array($data[0]) && ! empty($data[0])) {

            $this->id = intval($data[0]['id']);

            $this->invite_code = $data[0]['flag'] ?? '';

            $this->usage_limit = $data[0]['meta_value']['usage_limit'] ?? '';

            $this->membership_plan = $data[0]['meta_value']['membership_plan'] ?? '';

            $this->expiry_date = $data[0]['meta_value']['expiry_date'] ?? '';
        }
    }

    /**
     * @return int
     *
     * Return how many times coupon has been used. update this after every successful order
     *
     */
    public function get_usage_count()
    {
        global $wpdb;

        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'ppress_invite_code' AND meta_value = %s",
                sanitize_text_field($this->invite_code)
            )
        );

        if ($result) return intval($result);

        return 0;
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return ! empty($this->id);
    }

    /**
     * @return bool
     */
    public function is_expired()
    {
        return Init::is_date_expired($this->expiry_date);
    }

    /**
     * @return bool
     */
    public function is_exceeded_limit()
    {
        if ( ! empty($this->usage_limit) && ($usage_limit = intval($this->usage_limit)) > 0) {
            return $this->get_usage_count() >= $usage_limit;
        }

        return false;
    }

    public function subscribe_to_membership($user_id)
    {
        if ( ! empty($this->membership_plan)) {
            $customer_id = ppress_create_customer($user_id);
            if ( ! is_wp_error($customer_id)) {
                ppress_subscribe_user_to_plan($this->membership_plan, $customer_id);
            }
        }
    }
}