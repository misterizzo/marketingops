<?php

namespace ProfilePress\Libsodium\PayPal\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;
use ProfilePress\Core\Membership\Services\Calculator;

class PaymentSaleRefunded implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        $order = OrderFactory::fromTransactionId($event_data->resource->sale_id);

        if ( ! $order || ! $order->exists()) return;

        if ($order->is_refunded()) return;

        $order_amount    = $order->get_total();
        $refunded_amount = isset($event_data->resource->amount->total) ? $event_data->resource->amount->total : $order_amount;
        $currency        = isset($event_data->resource->amount->currency) ? $event_data->resource->amount->currency : $order->currency;

        /* Translators: %1$s - Amount refunded; %2$s - Original payment ID; %3$s - Refund transaction ID */
        $order_note = sprintf(
            esc_html__('Amount: %1$s; Order transaction ID: %2$s; Refund transaction ID: %3$s', 'profilepress-pro'),
            ppress_display_amount($refunded_amount, $currency),
            esc_html($order->transaction_id),
            esc_html($event_data->resource->id)
        );

        if (Calculator::init($refunded_amount)->isLessThan($order_amount)) {
            $order->add_note(esc_html__('Partial refund processed in PayPal.', 'profilepress-pro') . ' ' . $order_note);
        } else {
            $order->refund_order();
            // no need to cancel corresponding subscription as BILLING.SUBSCRIPTION.CANCELLED handles that.
        }
    }
}
