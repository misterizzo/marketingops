<?php

namespace ProfilePress\Core\Membership\PaymentMethods\Stripe\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;
use ProfilePress\Core\Membership\Repositories\OrderRepository;

class PaymentIntentSucceeded implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        $order = OrderFactory::fromId(
            $event_data['metadata']['order_id'] ?? 0
        );

        $payment_intent_id = $event_data['id'];

        if ( ! $order->exists()) {
            $orders = OrderRepository::init()->retrieveBy(['transaction_id' => $payment_intent_id, 'number' => 1]);
            if ( ! empty($orders)) $order = $orders[0];
        }

        if ($order->exists() && ! $order->is_completed()) {
            $order->complete_order($payment_intent_id);
        }

        $subscription = SubscriptionFactory::fromId($order->subscription_id);

        if ($subscription->exists() && ! $order->get_plan()->is_auto_renew()) {
            $subscription->activate_subscription();
        }
    }
}
