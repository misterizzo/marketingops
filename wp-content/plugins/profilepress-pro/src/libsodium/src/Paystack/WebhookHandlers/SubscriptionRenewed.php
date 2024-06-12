<?php

namespace ProfilePress\Libsodium\Paystack\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;
use ProfilePressVendor\Carbon\CarbonImmutable;

class SubscriptionRenewed implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        $profile_id = $event_data->subscription->subscription_code ?? 0;

        $subscription = SubscriptionFactory::fromProfileId($profile_id);

        if ( ! $subscription || ! $subscription->exists()) return;

        if ( ! in_array($event_data->paid, [1, '1', 'true', true], true)) return;

        $order_id = $subscription->add_renewal_order([
            'transaction_id' => $event_data->transaction->reference,
            'total_amount'   => ppress_cent_to_decimal($event_data->transaction->amount),
        ]);

        if ( ! empty($order_id)) {

            $expiration_timestamp = CarbonImmutable::parse($event_data->subscription->next_payment_date, 'UTC')->getTimestamp();

            $subscription->renew(true, $expiration_timestamp);
        }
    }
}
