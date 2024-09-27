<?php

namespace ProfilePress\Core\Membership\Services;

use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionBillingFrequency;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;
use ProfilePress\Core\ShortcodeParser\MyAccount\MyAccountTag;
use ProfilePressVendor\Carbon\CarbonImmutable;

class SubscriptionService
{
    public function get_plan_expiration_datetime($plan_id)
    {
        $plan = ppress_get_plan($plan_id);

        if ( ! $plan->is_recurring()) return '';

        $carbon = CarbonImmutable::now(wp_timezone());

        if ($plan->has_free_trial()) {

            $duration = explode('_', $plan->get_free_trial());
            $period   = $carbon->add($duration[0], $duration[1]);

        } else {

            $period = $plan->billing_frequency;

            switch ($period) {
                case SubscriptionBillingFrequency::DAILY :
                    $period = $carbon->addDay();
                    break;
                case SubscriptionBillingFrequency::WEEKLY :
                    $period = $carbon->addWeek();
                    break;
                case SubscriptionBillingFrequency::QUARTERLY :
                    $period = $carbon->addMonths(3);
                    break;
                case SubscriptionBillingFrequency::EVERY_6_MONTHS :
                    $period = $carbon->addMonths(6);
                    break;
                case SubscriptionBillingFrequency::YEARLY :
                    $period = $carbon->addYear();
                    break;
                default:
                    $period = $carbon->addMonth();
                    break;
            }
        }

        return apply_filters('ppress_plan_expiration_datetime', $period->toDateTimeString(), $plan_id);
    }

    /**
     * @param $sub_id
     *
     * @return false|int
     */
    public function delete_subscription($sub_id)
    {
        $sub = SubscriptionFactory::fromId($sub_id);

        $sub->remove_plan_role_from_customer();

        $result = SubscriptionRepository::init()->delete($sub_id);

        if ($result) {
            PROFILEPRESS_sql::delete_meta_data_by_flag($sub->get_meta_flag_id());
        }

        do_action('ppress_subscription_deleted', $sub_id, $sub);

        return $result;
    }

    public function frontend_view_sub_url($subscription_id)
    {
        return add_query_arg(['sub_id' => $subscription_id], MyAccountTag::get_endpoint_url('list-subscriptions'));
    }

    public function admin_view_sub_url($subscription_id)
    {
        return add_query_arg(
            ['ppress_subscription_action' => 'edit', 'id' => $subscription_id],
            PPRESS_MEMBERSHIP_SUBSCRIPTIONS_SETTINGS_PAGE
        );
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