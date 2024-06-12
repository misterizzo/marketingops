<?php

namespace ProfilePress\Libsodium\Paystack\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePress\Libsodium\Paystack\Paystack;

class ChargeSuccess implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        $order = OrderFactory::fromId($event_data->metadata->order_id ?? 0);

        $subscription = SubscriptionFactory::fromId($order->subscription_id);

        if ( ! $order || ! $order->exists()) return;

        if ($order->get_id() != $subscription->parent_order_id) return;

        if ( ! $order->is_completed()) {

            $order->complete_order($event_data->id);

            if (
                Calculator::init($order->total)->isNegativeOrZero() &&
                Calculator::init(ppress_cent_to_decimal($event_data->amount))->isGreaterThanZero()
            ) {
                $this->refund_zero_amount_order($order);
            }
        }

        if ($subscription->exists() && ! $subscription->is_active()) {

            if ( ! $subscription->get_plan()->is_auto_renew()) {
                return $subscription->activate_subscription();
            }

            try {

                $profile_id = Paystack::get_instance()->create_paystack_subscription($order, $subscription);

                if ($subscription->has_trial()) {
                    return $subscription->enable_subscription_trial($profile_id);
                }

                $subscription->activate_subscription($profile_id);

            } catch (\Exception $e) {
                ppress_log_error($e->getMessage());
            }
        }
    }

    /**
     * @param OrderEntity $order
     *
     * @return void
     */
    private function refund_zero_amount_order(OrderEntity $order)
    {
        if ($order->exists()) {

            $payment_method = ppress_get_payment_method($order->payment_method);

            if (is_object($payment_method) && method_exists($payment_method, 'process_refund')) {

                $payment_method->process_refund($order->get_id(), $order->get_total());
            }
        }
    }
}
