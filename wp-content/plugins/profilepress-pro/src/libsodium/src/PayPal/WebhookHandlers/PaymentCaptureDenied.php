<?php

namespace ProfilePress\Libsodium\PayPal\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;

class PaymentCaptureDenied implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        $order = OrderFactory::fromId($event_data->resource->custom_id);

        if ( ! $order->exists()) return;

        $order->fail_order();

        SubscriptionFactory::fromId($order->subscription_id)->cancel();
    }
}