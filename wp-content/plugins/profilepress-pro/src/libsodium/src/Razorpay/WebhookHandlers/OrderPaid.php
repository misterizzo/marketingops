<?php

namespace ProfilePress\Libsodium\Razorpay\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;

class OrderPaid implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        $txn_id = $event_data->order->entity->id ?? 0;

        $order = OrderFactory::fromTransactionId($txn_id);

        if ( ! $order || ! $order->exists()) {

            // Attempt to get the order from the payment note. Populated by subscription order
            $order = OrderFactory::fromId(
                $event_data->payment->entity->notes->order_id ?? 0
            );

            if ( ! $order || ! $order->exists()) return;
        }

        if ( ! $order->is_completed()) {
            $order->complete_order($txn_id);
        }

        $subscription = SubscriptionFactory::fromId($order->subscription_id);

        if ($subscription->exists() && ! $subscription->get_plan()->is_auto_renew()) {
            $subscription->activate_subscription();
        }
    }
}
