<?php

namespace ProfilePress\Core\Membership\Models\Plan;

use ProfilePress\Core\Membership\Models\AbstractModel;
use ProfilePress\Core\Membership\Models\ModelInterface;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionBillingFrequency;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionTrialPeriod;
use ProfilePress\Core\Membership\Repositories\GroupRepository;
use ProfilePress\Core\Membership\Repositories\PlanRepository;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePress\Core\Membership\Services\OrderService;

/**
 * @property int $id
 * @property string $name
 * @property string $user_role
 * @property string $order_note
 * @property string $description
 * @property string $price
 * @property string $billing_frequency
 * @property string $subscription_length
 * @property int $total_payments
 * @property string $signup_fee
 * @property string $free_trial
 */
class PlanEntity extends AbstractModel implements ModelInterface
{
    const PLAN_EXTRAS = 'plan_extras';

    protected $id = 0;

    protected $name = '';

    protected $description = '';

    protected $user_role = '';

    protected $order_note = '';

    protected $price = '0';

    protected $billing_frequency = SubscriptionBillingFrequency::MONTHLY;

    protected $subscription_length = 'renew_indefinitely';

    // 0 indicates renew indefinitely.
    protected $total_payments = 0;

    protected $signup_fee = '0';

    protected $free_trial = SubscriptionTrialPeriod::DISABLED;

    protected $status = 'false';

    protected $meta_data = [];

    public function __construct($data = [])
    {
        if (is_array($data) && ! empty($data)) {

            foreach ($data as $key => $value) {
                $this->$key = $value;

                if ($key == 'meta_data') {
                    $this->meta_data = ! empty($value) && ppress_is_json($value) ? \json_decode($value, true) : [];
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return ! empty($this->id);
    }

    public function get_id()
    {
        return absint($this->id);
    }

    public function get_name()
    {
        return $this->name;
    }

    public function is_active()
    {
        return $this->status == 'true';
    }

    public function is_recurring()
    {
        return ! empty($this->billing_frequency) && $this->billing_frequency != SubscriptionBillingFrequency::LIFETIME;
    }

    /**
     * If subscription plan, do we want to setup payment gateway subscription for automatic renewal / recurring payments?
     *
     * @return bool
     */
    public function is_auto_renew(): bool
    {
        $result = $this->is_recurring() && ppress_settings_by_key('disable_auto_renew') != 'true';

        return apply_filters('ppress_subscription_is_auto_renew', $result, $this);
    }

    public function is_lifetime()
    {
        return ! empty($this->billing_frequency) && $this->billing_frequency == 'lifetime';
    }

    public function has_free_trial()
    {
        return $this->is_recurring() &&
               $this->free_trial != SubscriptionTrialPeriod::DISABLED &&
               ! OrderService::init()->customer_has_trialled($this->id);
    }

    public function has_signup_fee()
    {
        return ! Calculator::init($this->signup_fee)->isNegativeOrZero();
    }

    public function get_description()
    {
        return apply_filters('ppress_subscription_plan_description', wpautop($this->description), $this->get_id());
    }

    /**
     * @return string
     */
    public function get_price()
    {
        return ppress_sanitize_amount($this->price);
    }

    public function get_billing_frequency()
    {
        return $this->billing_frequency;
    }

    public function get_subscription_length()
    {
        return $this->subscription_length;
    }

    public function get_total_payments()
    {
        return absint($this->total_payments);
    }

    /**
     * @return string
     */
    public function get_signup_fee()
    {
        return ppress_sanitize_amount($this->signup_fee);
    }

    public function get_free_trial()
    {
        return sanitize_text_field($this->free_trial);
    }

    public function get_edit_plan_url()
    {
        return add_query_arg(['ppress_subp_action' => 'edit', 'id' => $this->id], PPRESS_MEMBERSHIP_SUBSCRIPTION_PLANS_SETTINGS_PAGE);
    }

    /**
     * @return false|int
     */
    public function save()
    {
        if ($this->id > 0) {

            $result = PlanRepository::init()->update($this);

            do_action('ppress_membership_update_plan', $result, $this);

            return $result;
        }

        $result = PlanRepository::init()->add($this);

        do_action('ppress_membership_add_plan', $result, $this);

        return $result;
    }

    /**
     * @return false|string
     */
    public function get_checkout_url()
    {
        return ppress_plan_checkout_url($this->get_id());
    }

    /**
     * @return bool
     */
    public function has_downloads()
    {
        $val = $this->get_downloads();

        return isset($val['files']) && is_array($val['files']) && ! empty($val['files']);
    }

    public function get_downloads()
    {
        $cache_key = sprintf('ppress_plan_%d_downloads', $this->get_id());

        $ret = wp_cache_get($cache_key);

        if (false === $ret) {

            $ret = [];

            $extras = $this->get_plan_extras();

            $file_names = ppress_var($extras, 'df_names');
            $file_urls  = ppress_var($extras, 'df_urls');

            if ( ! is_array($file_urls) || empty($file_urls)) return false;

            foreach ($file_urls as $index => $file_url) {
                if ( ! empty($file_url)) {
                    $ret['files'][$file_url] = ppress_var($file_names, $index, pathinfo($file_url)['filename']);
                }
            }

            $ret['download_limit'] = ppress_is_boolean($extras['df_download_limit']) || ! empty($extras['df_download_limit']) ?
                absint($extras['df_download_limit']) :
                absint(ppress_get_file_downloads_setting('download_limit', 0, true));

            $ret['download_expiry'] = ppress_is_boolean($extras['df_download_expiry']) || ! empty($extras['df_download_expiry']) ?
                absint($extras['df_download_expiry']) :
                absint(ppress_get_file_downloads_setting('download_expiry', 0, true));

            wp_cache_set($cache_key, $ret, '', MINUTE_IN_SECONDS);
        }

        return $ret;
    }

    /**
     * @param string $extra_key
     *
     * @return false|mixed
     */
    public function get_plan_extras($extra_key = '')
    {
        $extras = $this->get_meta(self::PLAN_EXTRAS);

        if ( ! empty($extra_key)) {
            return ppress_var($extras, $extra_key, '');
        }

        return $extras;
    }

    public function update_meta($meta_key, $meta_value)
    {
        $this->meta_data[$meta_key] = $meta_value;

        return PlanRepository::init()->updateColumn(
            $this->get_id(),
            'meta_data',
            \wp_json_encode($this->meta_data)
        );
    }

    /**
     * @param $meta_key
     *
     * @return false|mixed
     */
    public function get_meta($meta_key)
    {
        return ppress_var($this->meta_data, $meta_key);
    }

    /**
     * @param $meta_key
     *
     * @return false|int
     */
    public function delete_meta($meta_key)
    {
        unset($this->meta_data[$meta_key]);

        return PlanRepository::init()->updateColumn(
            $this->get_id(),
            'meta_data',
            \wp_json_encode($this->meta_data)
        );
    }

    /**
     * @return false|int
     */
    public function activate()
    {
        return PlanRepository::init()->updateColumn($this->id, 'status', 'true');
    }

    /**
     * @return false|int
     */
    public function deactivate()
    {
        return PlanRepository::init()->updateColumn($this->id, 'status', 'false');
    }

    /**
     * @return int|false
     */
    public function get_group_id()
    {
        $groups = GroupRepository::init()->retrieveAll(0, 1, 'ASC');

        foreach ($groups as $group) {
            if (in_array($this->get_id(), $group->get_plan_ids(), true)) {
                return $group->get_id();
            }
        }

        return false;
    }
}