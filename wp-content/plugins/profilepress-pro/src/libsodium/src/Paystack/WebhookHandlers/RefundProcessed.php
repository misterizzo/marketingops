<?php

namespace ProfilePress\Libsodium\Paystack\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;
use ProfilePress\Core\Membership\Services\Calculator;

class RefundProcessed implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        $order = OrderFactory::fromOrderKey($event_data->transaction_reference ?? 0);

        if ( ! $order || ! $order->exists()) return;

        if ($order->is_refunded()) return;

        $order_amount    = $order->get_total();
        $refunded_amount = isset($event_data->amount) ? ppress_cent_to_decimal($event_data->amount) : $order_amount;
        $currency        = isset($event_data->currency) ? $event_data->currency : $order->currency;

        /* Translators: %1$s - Amount refunded; %2$s - Original payment ID; %3$s - Refund transaction ID */
        $order_note = sprintf(
            esc_html__('Amount: %1$s; Order transaction ID: %2$s; Refund transaction ID: %3$s', 'profilepress-pro'),
            ppress_display_amount($refunded_amount, $currency),
            esc_html($order->transaction_id),
            esc_html($event_data->id)
        );

        // Partial refund.
        if (Calculator::init($refunded_amount)->isLessThan($order_amount)) {
            $order->add_note(esc_html__('Partial refund processed in Paystack.', 'profilepress-pro') . ' ' . $order_note);
        } else {

            $order->refund_order();

            $subscription = SubscriptionFactory::fromId($order->subscription_id);

            if ( ! $subscription->is_recurring()) {
                $subscription->cancel();
            }
        }
    }
}
