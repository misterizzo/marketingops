<?php

namespace ProfilePress\Libsodium\PayPal\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;
use ProfilePress\Core\Membership\Repositories\OrderRepository;

class PaymentCaptureCompleted implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        $orders = OrderRepository::init()->retrieveBy([
            'transaction_id' => $event_data->resource->id
        ]);

        if (empty($orders)) {
            $order = OrderFactory::fromId($event_data->resource->custom_id);
        } else {
            $order = $orders[0];
        }

        if ( ! $order->exists()) return;
        // ensures order has a TXN ID
        $order->update_transaction_id($event_data->resource->id);

        if ($order->is_completed()) return;

        $order->complete_order($event_data->resource->id);

        $sub = SubscriptionFactory::fromId($order->subscription_id);

        if ($sub->exists() && ! $sub->is_active() && ! $sub->get_plan()->is_auto_renew()) {
            $sub->activate_subscription();
        }
    }
}
