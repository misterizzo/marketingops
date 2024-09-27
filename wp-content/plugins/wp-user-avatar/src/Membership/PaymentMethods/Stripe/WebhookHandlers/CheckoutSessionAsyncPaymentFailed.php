<?php

namespace ProfilePress\Core\Membership\PaymentMethods\Stripe\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;

class CheckoutSessionAsyncPaymentFailed implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        if ( ! in_array($event_data['mode'], ['subscription', 'payment'], true)) return;

        $order = OrderFactory::fromOrderKey($event_data['client_reference_id']);

        $order->fail_order();
    }
}
