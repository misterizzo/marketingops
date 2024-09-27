<?php

namespace ProfilePress\Libsodium\PayPal\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;

class BillingSubscriptionExpired implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        $subscription = SubscriptionFactory::fromProfileId($event_data->resource->id);

        if ($subscription && $subscription->exists() && ! $subscription->is_completed() && ! $subscription->is_expired()) {
            $subscription->expire();
        }
    }
}
