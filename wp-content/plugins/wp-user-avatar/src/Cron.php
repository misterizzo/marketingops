<?php

namespace ProfilePress\Core;

use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;
use ProfilePressVendor\Carbon\CarbonImmutable;

class Cron
{
    public function __construct()
    {
        add_action('init', [$this, 'create_recurring_schedule']);

        add_action('ppress_daily_recurring_job', [$this, 'check_for_expired_subscriptions']);
    }

    public function create_recurring_schedule()
    {
        if ( ! wp_next_scheduled('ppress_daily_recurring_job')) {
            wp_schedule_event(time(), 'daily', 'ppress_daily_recurring_job');
        }
    }

    /**
     * Check for expired subscriptions once per day and mark them as expired
     */
    public function check_for_expired_subscriptions()
    {
        $subs = SubscriptionRepository::init()->retrieveBy([
            'status'          => [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIALLING, SubscriptionStatus::CANCELLED],
            'expiration_date' => CarbonImmutable::now('UTC')->endOfDay()->toDateTimeString(),
            'date_compare'    => '<=',
            'number'          => 100
        ]);

        if ( ! empty($subs)) {

            foreach ($subs as $sub) {
                $sub->expire(true, true);
            }
        }
    }

    /**
     * @return self
     */
    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}