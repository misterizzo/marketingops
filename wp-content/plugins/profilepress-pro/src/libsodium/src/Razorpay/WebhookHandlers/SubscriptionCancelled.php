<?php

namespace ProfilePress\Libsodium\Razorpay\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;

class SubscriptionCancelled implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        $profile_id = $event_data->subscription->entity->id ?? 0;

        $subscription = SubscriptionFactory::fromProfileId($profile_id);

        if ( ! $subscription || ! $subscription->exists()) {

            // Attempt to get the order from the payment note. Populated by subscription order
            $subscription = SubscriptionFactory::fromId(
                $event_data->subscription->entity->notes->subscription_id ?? 0
            );

            if ( ! $subscription || ! $subscription->exists()) return;
        }

        $subscription->cancel();
    }
}
