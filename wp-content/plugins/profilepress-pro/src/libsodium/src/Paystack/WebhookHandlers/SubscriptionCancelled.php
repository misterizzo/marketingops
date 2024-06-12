<?php

namespace ProfilePress\Libsodium\Paystack\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;

class SubscriptionCancelled implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        $subscription = SubscriptionFactory::fromProfileId($event_data->subscription_code);

        if ($subscription && $subscription->exists()) {
            $subscription->cancel();
        }
    }
}
