<?php

namespace ProfilePress\Libsodium\Razorpay\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;
use ProfilePressVendor\Carbon\CarbonImmutable;

class InvoicePaid implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        $profile_id = $event_data->invoice->subscription_id ?? 0;

        $subscription = SubscriptionFactory::fromProfileId($profile_id);

        if ( ! $subscription || ! $subscription->exists()) return;

        $parent_order          = OrderFactory::fromId($subscription->parent_order_id);
        $parent_order_date     = $parent_order->date_created;
        $razorpay_payment_date = $event_data->invoice->entity->paid_at;

        if ($this->is_initial_payment($razorpay_payment_date, $parent_order_date)) return;

        // This is a renewal charge
        $order_id = $subscription->add_renewal_order([
            'transaction_id' => $event_data->invoice->entity->order_id,
            'total_amount'   => ppress_cent_to_decimal($event_data->order->entity->amount),
        ]);

        if ( ! empty($order_id)) $subscription->renew();
    }

    /**
     * Determines whether or not the invoice payment is the first payment in a subscription.
     * This is determined to be true if the timestamps are less than 6 hours apart.
     *
     * @param $razorpay_payment_date
     * @param $parent_order_date
     *
     * @return bool
     */
    private function is_initial_payment($razorpay_payment_date, $parent_order_date)
    {
        $diff = CarbonImmutable::createFromTimestampUTC($razorpay_payment_date)->diffInHours(
            CarbonImmutable::parse($parent_order_date, 'UTC')
        );

        return $diff < 2;
    }
}
