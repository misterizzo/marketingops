<?php

namespace ProfilePress\Libsodium\PayPal\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;
use ProfilePressVendor\Carbon\CarbonImmutable;

class BillingSubscriptionActivated implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        $subscription = SubscriptionFactory::fromProfileId($event_data->resource->id);

        if ($subscription && $subscription->exists()) {

            $order = OrderFactory::fromId($subscription->parent_order_id);

            if ( ! $subscription->is_active()) {

                $subscription->expiration_date = CarbonImmutable::parse($event_data->resource->billing_info->next_billing_time, 'UTC')->toDateTimeString();
                if ($subscription->has_trial()) {
                    $subscription->enable_subscription_trial();
                    if ( ! $order->is_completed()) $order->complete_order();
                } else {
                    $subscription->activate_subscription();
                }
            }
        }
    }
}
