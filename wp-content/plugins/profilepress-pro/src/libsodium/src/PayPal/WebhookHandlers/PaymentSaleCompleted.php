<?php

namespace ProfilePress\Libsodium\PayPal\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;
use ProfilePressVendor\Carbon\CarbonImmutable;

class PaymentSaleCompleted implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        try {

            if (empty($event_data->resource->billing_agreement_id)) {
                throw new \Exception('Missing subscription ID from event.');
            }

            $subscription = SubscriptionFactory::fromProfileId($event_data->resource->billing_agreement_id);

            if ( ! $subscription || ! $subscription->exists()) {
                throw new \Exception(sprintf('Failed to locate ProfilePress subscription from PayPal ID %s', $event_data->resource->billing_agreement_id));
            }

            $parent_order        = OrderFactory::fromId($subscription->parent_order_id);
            $parent_order_date   = $parent_order->date_created;
            $paypal_payment_date = $event_data->resource->create_time;

            if (
                $this->is_initial_payment($paypal_payment_date, $parent_order_date) &&
                (empty($parent_order->transaction_id) || $parent_order->transaction_id === $event_data->resource->id)
            ) {

                // ensures order has a TXN ID
                $parent_order->update_transaction_id($event_data->resource->id);

                if ( ! $parent_order->is_completed()) {
                    $parent_order->complete_order($event_data->resource->id);
                }

                if ( ! $subscription->is_active()) {

                    if ($subscription->has_trial()) {
                        $subscription->enable_subscription_trial();
                    } else {
                        $subscription->activate_subscription();
                    }
                }

            } else {

                // This is a renewal charge
                $order_id = $subscription->add_renewal_order([
                    'transaction_id' => $event_data->resource->id,
                    'total_amount'   => $event_data->resource->amount->total,
                ]);

                if ( ! empty($order_id)) $subscription->renew();
            }

        } catch (\Exception $e) {
            ppress_log_error($e->getMessage());
        }
    }

    /**
     * Determines whether or not the PayPal transaction is the first payment in a subscription.
     * This is determined to be true if the timestamps are less than 6 hours apart.
     *
     * @param $paypal_payment_date
     * @param $parent_order_date
     *
     * @return bool
     */
    private function is_initial_payment($paypal_payment_date, $parent_order_date)
    {
        $diff = CarbonImmutable::parse($paypal_payment_date, 'UTC')->diffInHours(
            CarbonImmutable::parse($parent_order_date, 'UTC')
        );

        return $diff < 6;
    }
}
