<?php

namespace ProfilePress\Core\Membership\Models\Subscription;

use ProfilePress\Core\Base;
use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Core\Membership\Models\AbstractModel;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\ModelInterface;
use ProfilePress\Core\Membership\Models\Order\OrderEntity as OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePress\Core\Membership\Models\Plan\PlanEntity as PlanEntity;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods;
use ProfilePress\Core\Membership\Repositories\OrderRepository;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePress\Core\Membership\Services\OrderService;
use ProfilePressVendor\Carbon\CarbonImmutable;

/**
 * @property int $id
 * @property int $parent_order_id
 * @property int $plan_id
 * @property int $customer_id
 * @property string $billing_frequency
 * @property string $initial_amount
 * @property string $recurring_amount
 * @property string $initial_tax
 * @property string $initial_tax_rate
 * @property string $recurring_tax
 * @property string $recurring_tax_rate
 * @property int $total_payments
 * @property string $trial_period
 * @property string $profile_id
 * @property string $status
 * @property array $notes
 * @property string $created_date
 * @property string $expiration_date
 */
class SubscriptionEntity extends AbstractModel implements ModelInterface
{
    const DB_META_KEY = 'sbmeta';

    /**
     * Subscription ID
     *
     * @var int
     */
    protected $id = 0;

    /**
     * Subscription Parent order ID
     *
     * @var int
     */
    protected $parent_order_id = 0;

    /**
     * The Plan ID that this subscription is for.
     *
     * @var int
     */
    protected $plan_id = 0;

    /**
     * Customer ID with this subscription.
     *
     * @var int
     */
    protected $customer_id = 0;

    /**
     * Billing frequency
     *
     * @var string
     */
    protected $billing_frequency = SubscriptionBillingFrequency::MONTHLY;

    /**
     * @var string
     */
    protected $initial_amount = '0';

    /**
     * @var string
     */
    protected $recurring_amount = '0';

    /**
     * @var string
     */
    protected $initial_tax = '0';

    /**
     * @var string
     */
    protected $initial_tax_rate = '0';

    /**
     * @var string
     */
    protected $recurring_tax = '0';

    /**
     * @var string
     */
    protected $recurring_tax_rate = '0';

    /**
     * @var int
     */
    protected $total_payments = 0;

    /**
     * @var string
     */
    protected $trial_period = SubscriptionTrialPeriod::DISABLED;

    /**
     * @var string
     */
    protected $profile_id = '';

    /**
     * @var string
     */
    protected $status = SubscriptionStatus::PENDING;

    /**
     * @var string
     */
    protected $notes = [];

    /**
     * @var string
     */
    protected $created_date = '';

    /**
     * @var string
     */
    protected $expiration_date = '';

    public function __construct($data = [])
    {
        if (is_array($data) && ! empty($data)) {

            foreach ($data as $key => $value) {

                if ($key == 'notes' && ! is_array($value)) {
                    $value      = empty($value) ? '{}' : $value;
                    $this->$key = json_decode($value, true);
                } else {
                    $this->$key = $value;
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

    /**
     * Check if a subscription is active and not expired.
     *
     * @return bool
     */
    public function is_active()
    {
        $ret = false;

        $active_statuses = [
            SubscriptionStatus::ACTIVE,
            SubscriptionStatus::CANCELLED,
            // Cancelled is an active state because it just means it won't renew, but is also not yet expired.
            SubscriptionStatus::COMPLETED,
            SubscriptionStatus::TRIALLING,
        ];

        $last_order = $this->get_last_order();

        // one-time payments with lifetime expiration is considered not active if they are cancelled
        // unlike recurring sub which is only not active if expired.
        if ($this->is_lifetime() && $this->is_cancelled()) {
            $ret = false;
        }
        if (
            apply_filters('ppress_subscription_disable_active_status_on_refund', true) &&
            $this->is_cancelled() && $last_order instanceof OrderEntity && $last_order->is_refunded()
        ) {
            $ret = false;
        } elseif ( ! $this->is_expired() && in_array($this->status, $active_statuses, true)) {
            $ret = true;
        }

        return apply_filters('ppress_subscription_is_active', $ret, $this->id, $this);
    }

    public function is_expired()
    {
        $ret = false;

        if ($this->status == SubscriptionStatus::EXPIRED) {

            $ret = true;

        } elseif ( ! $this->is_lifetime() && in_array($this->status, [
                SubscriptionStatus::ACTIVE,
                SubscriptionStatus::CANCELLED,
                SubscriptionStatus::TRIALLING
            ])
        ) {

            $ret = false;

            $expiration_date = CarbonImmutable::parse($this->expiration_date, wp_timezone());
            $now             = CarbonImmutable::now(wp_timezone());

            if ($now->greaterThan($expiration_date) && $expiration_date->diffInDays($now) >= 2) {

                $ret = true;

                if (in_array($this->status, [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIALLING])) {
                    $this->expire();
                }
            }
        }

        return apply_filters('ppress_subscription_is_expired', $ret, $this->id, $this);
    }

    public function is_pending()
    {
        return $this->status == SubscriptionStatus::PENDING;
    }

    public function is_cancelled()
    {
        return $this->status == SubscriptionStatus::CANCELLED;
    }

    public function is_completed()
    {
        return $this->status == SubscriptionStatus::COMPLETED;
    }

    public function is_recurring()
    {
        return $this->billing_frequency != SubscriptionBillingFrequency::ONE_TIME;
    }

    public function is_lifetime()
    {
        return ! $this->is_recurring() || Calculator::init($this->recurring_amount)->isNegativeOrZero();
    }

    public function has_trial()
    {
        return $this->is_recurring() && $this->trial_period != SubscriptionTrialPeriod::DISABLED;
    }

    public function get_parent_order_id()
    {
        return absint($this->parent_order_id);
    }

    /**
     * @return PlanEntity
     */
    public function get_plan()
    {
        return ppress_get_plan($this->get_plan_id());
    }

    public function get_plan_id()
    {
        return absint($this->plan_id);
    }

    public function get_customer_id()
    {
        return absint($this->customer_id);
    }

    public function get_customer()
    {
        return CustomerFactory::fromId($this->get_customer_id());
    }

    /**
     * Total sub initial amount including tax.
     *
     * @return string
     */
    public function get_initial_amount()
    {
        return (string)$this->initial_amount;
    }

    /**
     * Total sub recurring amount including tax.
     *
     * @return string
     */
    public function get_recurring_amount()
    {
        return (string)$this->recurring_amount;
    }

    public function get_initial_tax()
    {
        return empty($this->initial_tax) ? '0' : (string)$this->initial_tax;
    }

    public function get_initial_tax_rate()
    {
        return empty($this->initial_tax_rate) ? '0' : (string)$this->initial_tax_rate;
    }

    public function get_recurring_tax()
    {
        return empty($this->recurring_tax) ? '0' : (string)$this->recurring_tax;
    }

    public function get_recurring_tax_rate()
    {
        return empty($this->recurring_tax_rate) ? '0' : (string)$this->recurring_tax_rate;
    }

    public function get_total_payments()
    {
        return absint($this->total_payments);
    }

    public function get_completed_order_count()
    {
        return OrderRepository::init()->retrieveBy([
            'subscription_id' => $this->id,
            'status'          => OrderStatus::COMPLETED
        ], true);
    }

    public function set_trial_period($period)
    {
        $valid_periods = (new \ReflectionClass(SubscriptionTrialPeriod::class))->getConstants();

        if (in_array($period, $valid_periods)) {
            $this->trial_period = $period;
        }
    }

    public function get_profile_id()
    {
        return apply_filters('ppress_subscription_profile_id', $this->profile_id, $this->get_id(), $this);
    }

    protected function set_status($status)
    {
        $valid_statuses = (new \ReflectionClass(SubscriptionStatus::class))->getConstants();

        if (in_array($status, $valid_statuses)) {
            $this->status = $status;
        }
    }

    public function get_status()
    {
        return apply_filters('ppress_subscription_status', $this->status, $this->get_id(), $this);
    }

    public function get_status_label()
    {
        return SubscriptionStatus::get_label($this->get_status());
    }

    public function get_payment_method()
    {
        return OrderFactory::fromId($this->parent_order_id)->payment_method;
    }

    public function get_formatted_expiration_date()
    {
        $date = esc_html__('Lifetime', 'wp-user-avatar');

        if ( ! $this->is_lifetime()) {
            $date = ppress_format_date($this->expiration_date);
        }

        return apply_filters('ppress_subscription_formatted_expiration_date', $date, $this);
    }

    public function get_notes()
    {
        return apply_filters('ppress_subscription_note', $this->notes, $this->get_id(), $this);
    }

    public function add_note($note)
    {
        $notes = $this->notes;

        $notes[] = ppress_format_date_time(time()) . '|' . $note;

        return SubscriptionRepository::init()->updateColumn($this->id, 'notes', json_encode($notes));
    }

    public function get_subscription_terms()
    {
        $price = $this->get_recurring_amount();

        if ($this->billing_frequency == SubscriptionBillingFrequency::ONE_TIME) {
            $price = $this->get_initial_amount();
        }

        $cycle = ppress_display_amount(
            $price,
            OrderFactory::fromId($this->parent_order_id)->currency
        );

        $cycle .= ' / ';

        $cycle .= SubscriptionBillingFrequency::get_label($this->billing_frequency);

        return $cycle;
    }

    /**
     * Retrieve all subscription related payments.
     *
     * @return OrderEntity[]|string
     */
    public function get_all_orders()
    {
        return OrderRepository::init()->retrieveBy([
            'subscription_id' => $this->id,
            'number'          => 0
        ]);
    }

    /**
     * @return OrderEntity|false
     */
    public function get_last_order()
    {
        $orders = OrderRepository::init()->retrieveBy([
            'subscription_id' => $this->id,
            'number'          => 1,
            'plan_id'         => $this->get_plan_id(),
            'order'           => 'DESC'
        ]);

        if ( ! empty($orders)) return $orders[0];

        return false;
    }

    public function add_plan_role_to_customer()
    {
        $customer = CustomerFactory::fromId($this->customer_id);
        $plan     = ppress_get_plan($this->plan_id);

        $user = $customer->get_wp_user();

        if ($user instanceof \WP_User) {
            $user->add_role($plan->user_role);
        }

        do_action('ppress_added_plan_role_to_customer', $this);
    }

    public function remove_plan_role_from_customer()
    {
        $customer = CustomerFactory::fromId($this->customer_id);
        $plan     = ppress_get_plan($this->plan_id);

        $user = $customer->get_wp_user();

        if ($user instanceof \WP_User) {
            $user->remove_role($plan->user_role);
        }

        do_action('ppress_removed_plan_role_from_customer', $this);
    }

    /**
     * @param $profile_id
     *
     * @return false|int
     */
    public function activate_subscription($profile_id = '')
    {
        if ($this->is_active()) return false;

        // important we don't use update_status() as it's mostly needed when sub moved from one status to another.
        $this->status = SubscriptionStatus::ACTIVE;

        if ( ! empty($profile_id)) {
            $this->profile_id = $profile_id;
        }

        $sub_id = $this->save();

        $this->id = $sub_id;

        $this->add_plan_role_to_customer();

        do_action('ppress_subscription_activated', $this);

        $this->maybe_complete_subscription();

        return $sub_id;
    }

    /**
     * @param $profile_id
     *
     * @return false|int
     */
    public function enable_subscription_trial($profile_id = '')
    {
        if ($this->is_active()) return false;

        // important we don't use update_status() as it's mostly needed when sub moved from one status to another.
        $this->status = SubscriptionStatus::TRIALLING;

        if ( ! empty($profile_id)) {
            $this->profile_id = $profile_id;
        }

        $sub_id = $this->save();

        $this->id = $sub_id;

        $this->add_plan_role_to_customer();

        do_action('ppress_subscription_enabled_trial', $this);

        $this->maybe_complete_subscription();

        return $sub_id;
    }

    public function update_profile_id($val)
    {
        return SubscriptionRepository::init()->updateColumn($this->id, 'profile_id', $val);
    }

    /**
     * @param $subscription_status
     *
     * @return false|int
     */
    public function update_status($subscription_status)
    {
        $old_status = $this->status;

        $this->status = $subscription_status;

        $response = $this->save();

        $user = is_user_logged_in() ? wp_get_current_user()->user_login : esc_html__('payment method', 'wp-user-avatar');

        $this->add_note(
            sprintf(__('Subscription changed from %s to %s by %s', 'wp-user-avatar'), $old_status, $this->status, $user)
        );

        do_action('ppress_subscription_status_updated', $subscription_status, $old_status, $this);

        return $response;
    }


    /**
     * Returns the number of times the subscription has been billed
     *
     * @return int
     */
    public function get_times_billed()
    {
        $orders = OrderRepository::init()->retrieveBy([
            'subscription_id' => $this->id,
            'status'          => [OrderStatus::COMPLETED],
            'number'          => 0
        ]);

        return count($orders);
    }

    /**
     * Determines if subscription can be cancelled
     *
     * This method is filtered by payment methods in order to return true on subscriptions
     * that can be cancelled with a profile ID through the merchant processor
     *
     * @return bool
     */
    public function can_cancel()
    {
        if ($this->is_lifetime()) return false;

        return apply_filters('ppress_subscription_can_cancel', false, $this);
    }

    public function has_cancellation_requested()
    {
        $val = PROFILEPRESS_sql::get_meta_data_by_key('subscription_cancellation_requested_' . $this->id);

        return is_array($val) && isset($val[0]['meta_value']) && 'true' == $val[0]['meta_value'];
    }

    public function add_cancellation_requested()
    {
        $this->delete_cancellation_requested(); // cleanup first
        PROFILEPRESS_sql::add_meta_data('subscription_cancellation_requested_' . $this->id, 'true');
    }

    public function delete_cancellation_requested()
    {
        PROFILEPRESS_sql::delete_meta_data_by_meta_key('subscription_cancellation_requested_' . $this->id);
    }

    /**
     * @param bool $gateway_cancel set to true to cancel sub in gateway too.
     *
     * @return false|void
     */
    public function cancel($gateway_cancel = false, $cancel_immediately = false)
    {
        if ($this->is_cancelled()) return false;

        $sub = clone $this; // gateway cancel() check sub status first before cancelling. hence we need a copy of the sub before it's cancelled below.

        $old_status = $this->status;

        $this->update_status(SubscriptionStatus::CANCELLED);

        if ($gateway_cancel === true && $sub->can_cancel()) {
            if ($cancel_immediately === true) {
                PaymentMethods::get_instance()->get_by_id($this->get_payment_method())->cancel_immediately($sub);
            } else {
                PaymentMethods::get_instance()->get_by_id($this->get_payment_method())->cancel($sub);
            }
        }

        if ($this->is_lifetime()) {
            $this->remove_plan_role_from_customer();
        }

        $this->add_cancellation_requested();

        do_action('ppress_subscription_cancelled', $this, $old_status);
    }

    /**
     * @return false|void
     */
    public function complete()
    {
        // Prevent setting a subscription as complete if it was previously set as cancelled.
        if ($this->is_cancelled()) return false;

        if ($this->update_status(SubscriptionStatus::COMPLETED)) {

            $this->add_plan_role_to_customer();

            do_action('ppress_subscription_completed', $this);
        }
    }

    /**
     * @param bool $check_expiration
     *
     * @return false|void
     */
    public function expire($check_expiration = false, $addBuffer = false)
    {
        if ($this->is_lifetime()) return false;

        $expiration_date_timestamp = ppress_strtotime_utc($this->expiration_date);

        if ($addBuffer && $this->billing_frequency == SubscriptionBillingFrequency::DAILY) {
            $addBuffer = false;
        }

        if (apply_filters('ppress_subscription_is_add_buffer', $addBuffer, $this)) {
            // added a day buffer to expiration date to give time for gateway to renew the sub
            $expiration_date_timestamp += DAY_IN_SECONDS;
        }

        if ($check_expiration && time() <= $expiration_date_timestamp) {
            return false; // Do not mark as expired since real expiration date is in the future
        }

        $this->update_status(SubscriptionStatus::EXPIRED);

        $this->remove_plan_role_from_customer();

        do_action('ppress_subscription_expired', $this);
    }

    /**
     * @param $change_expiry_date
     * @param int $expiration_date timestamp in UTC
     *
     * @return void
     */
    public function renew($change_expiry_date = true, $expiration_date = '')
    {
        if ( ! empty($expiration_date)) {
            $expiration = CarbonImmutable::createFromTimestampUTC($expiration_date);
        } else {

            $old_expiry_date = ppress_strtotime_utc($this->expiration_date);

            // Determine what date to use as the start for the new expiration calculation
            if ($old_expiry_date > time() && $this->is_active()) {
                $base_date = $old_expiry_date;
            } else {
                $base_date = time();
            }

            $cabonInstance = CarbonImmutable::createFromTimestampUTC($base_date);

            switch ($this->billing_frequency) {
                case SubscriptionBillingFrequency::DAILY :
                    $expiration = $cabonInstance->addDay();
                    break;
                case SubscriptionBillingFrequency::WEEKLY :
                    $expiration = $cabonInstance->addWeek();
                    break;
                case SubscriptionBillingFrequency::QUARTERLY :
                    $expiration = $cabonInstance->addMonths(3);
                    break;
                case SubscriptionBillingFrequency::EVERY_6_MONTHS :
                    $expiration = $cabonInstance->addMonths(6);
                    break;
                case SubscriptionBillingFrequency::YEARLY :
                    $expiration = $cabonInstance->addYear();
                    break;
                default:
                    $expiration = $cabonInstance->addMonth();
                    break;
            }
        }

        $expiration = apply_filters('ppress_subscription_renewal_expiration', $expiration->toDateTimeString(), $this->id, $this);

        do_action('ppress_subscription_pre_renew', $this->id, $expiration, $this);

        if ($change_expiry_date === true) {
            $this->expiration_date = $expiration;
        }

        $this->status = SubscriptionStatus::ACTIVE;
        $this->save();

        $this->add_plan_role_to_customer();

        $this->maybe_complete_subscription();

        do_action('ppress_subscription_post_renew', $this->id, $expiration, $this);
    }

    public function maybe_complete_subscription()
    {
        if ($this->is_completed()) return;

        $times_billed = $this->get_times_billed();

        // Complete subscription if applicable
        if ($this->total_payments > 0 && $times_billed >= $this->total_payments) {
            $this->complete();
            $this->add_plan_role_to_customer();
        }
    }

    /**
     * @param $args
     *
     * @return false|int
     */
    public function add_renewal_order($args)
    {
        $result = OrderService::init()->record_subscription_renewal_order(
            $args,
            $this
        );

        do_action('ppress_subscription_renewal_order_added', $args, $this);

        return $result;
    }

    /**
     * @return false|int
     */
    public function save()
    {
        if ($this->exists()) {

            $result = SubscriptionRepository::init()->update($this);

            do_action('ppress_membership_update_subscription', $result, $this);

            return $result;
        }

        $result = SubscriptionRepository::init()->add($this);

        do_action('ppress_membership_add_subscription', $result, $this);

        return $result;
    }

    public function get_meta_flag_id()
    {
        return sprintf('%s_%d', self::DB_META_KEY, $this->get_id());
    }

    /**
     * @param $meta_key
     * @param $meta_value
     *
     * @return int|false
     */
    public function update_meta($meta_key, $meta_value)
    {
        global $wpdb;

        $preflight = $this->get_meta($meta_key);

        if ( ! $preflight) {

            return $wpdb->insert(
                Base::meta_data_db_table(),
                ['flag' => $this->get_meta_flag_id(), 'meta_key' => $meta_key, 'meta_value' => $meta_value],
                ['%s', '%s', '%s']
            );

        } else {

            return $wpdb->update(
                Base::meta_data_db_table(),
                ['meta_value' => $meta_value],
                ['flag' => $this->get_meta_flag_id(), 'meta_key' => $meta_key],
                ['%s'],
                ['%s', '%s']
            );
        }
    }

    /**
     * @param $meta_key
     *
     * @return string|null
     */
    public function get_meta($meta_key)
    {
        global $wpdb;

        $table = Base::meta_data_db_table();

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT meta_value FROM $table WHERE flag = %s AND meta_key = %s",
                $this->get_meta_flag_id(), $meta_key
            )
        );
    }

    public function delete_meta($meta_key)
    {
        global $wpdb;

        $result = $wpdb->delete(
            Base::meta_data_db_table(),
            ['flag' => $this->get_meta_flag_id(), 'meta_key' => $meta_key],
            ['%s', '%s']
        );

        return $result !== false;
    }
}