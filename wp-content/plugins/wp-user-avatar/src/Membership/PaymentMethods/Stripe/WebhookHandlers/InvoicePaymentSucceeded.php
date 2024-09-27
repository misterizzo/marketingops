<?php

namespace ProfilePress\Core\Membership\PaymentMethods\Stripe\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\PaymentMethods\Stripe\APIClass;
use ProfilePress\Core\Membership\PaymentMethods\Stripe\PaymentHelpers;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;

class InvoicePaymentSucceeded implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        $subs = SubscriptionRepository::init()->retrieveBy([
            'profile_id' => $event_data['subscription'],
            'number'     => 1
        ]);

        if (empty($subs)) return;

        $subscription = $subs[0];

        if ($event_data['billing_reason'] == 'subscription_create') {

            $parent_order = OrderFactory::fromId($subscription->parent_order_id);

            if ($parent_order->exists() && ! $parent_order->is_completed()) {
                $parent_order->complete_order($event_data['payment_intent']);

                $this->set_customer_default_payment_method($parent_order, $event_data['customer']);
            }
        }

        if ($event_data['billing_reason'] == 'subscription_cycle') {

            // This is a renewal charge
            $order_id = $subscription->add_renewal_order([
                'transaction_id' => $event_data['payment_intent'],
                'total_amount'   => PaymentHelpers::stripe_amount_to_ppress_amount($event_data['amount_paid']),
            ]);

            if ( ! empty($order_id)) {
                // we are not changing the expiration date because it is always in sync with stripe via
                // CustomerSubscriptionUpdated event.
                $subscription->renew(false);
            }
        }
    }

    /**
     * @param OrderEntity $order
     * @param string $stripe_customer_id
     *
     * @return void
     */
    public function set_customer_default_payment_method($order, $stripe_customer_id)
    {
        try {

            $setup_intent_id = $order->get_meta('stripe_setup_intent');

            if ( ! empty($setup_intent_id) && ! empty($stripe_customer_id)) {

                $response = APIClass::stripeClient()->setupIntents->retrieve($setup_intent_id, []);

                if ( ! empty($response->payment_method)) {

                    APIClass::stripeClient()->customers->update(
                        $stripe_customer_id,
                        [
                            'invoice_settings' => [
                                'default_payment_method' => $response->payment_method
                            ]
                        ]
                    );
                }
            }

        } catch (\Exception $e) {
            // Ignore, not critical enough to cause an error
        }
    }
}
