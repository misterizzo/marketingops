<?php

namespace ProfilePress\Libsodium\Mollie;

use ProfilePress\Core\Membership\Controllers\CheckoutResponse;
use ProfilePress\Core\Membership\Models\Customer\CustomerEntity;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePress\Core\Membership\Models\Plan\PlanFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionBillingFrequency;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\Membership\PaymentMethods\AbstractPaymentMethod;
use ProfilePress\Core\Membership\Repositories\OrderRepository;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePressVendor\Carbon\CarbonImmutable;
use WP_Error;

class Mollie extends AbstractPaymentMethod
{
    static $MOLLIE_CUSTOMER_ID = 'mollie_customer_id';

    public function __construct()
    {
        parent::__construct();

        $this->id          = 'mollie';
        $this->title       = 'Mollie';
        $this->description = esc_html__('Pay via Mollie', 'profilepress-pro');

        $this->method_title       = 'Mollie';
        $this->method_description = esc_html__('Accept payments via iDEAL, Credit Card, Apple Pay, PayPal, SEPA Direct Debit, Bancontact, Bank transfer and more with Mollie.', 'profilepress-pro');

        $this->icon = PPRESS_ASSETS_URL . '/images/mollie-icon.svg';

        if ( ! ppress_is_test_mode()) {
            self::$MOLLIE_CUSTOMER_ID .= '_live';
        }

        $this->supports = [
            self::SUBSCRIPTIONS,
            self::REFUNDS,
        ];
    }

    public function admin_settings()
    {
        $settings = parent::admin_settings();

        $settings['api_key'] = [
            'label' => esc_html__('API Key', 'profilepress-pro'),
            'type'  => 'password',
        ];

        $settings['remove_billing_fields'] = [
            'label'          => esc_html__('Remove Billing Address', 'profilepress-pro'),
            'type'           => 'checkbox',
            'checkbox_label' => esc_html__('Check to remove billing address fields from the checkout page.', 'profilepress-pro'),
            'description'    => esc_html__('If you do not want the billing address fields displayed on the checkout page, use this setting to remove it.', 'profilepress-pro')
        ];

        return $settings;
    }

    protected function get_api_key()
    {
        return $this->get_value('api_key');
    }

    /**
     * @throws \Exception
     */
    protected function getHttpclient()
    {
        return new APIClass($this->get_api_key());
    }

    /**
     * @return bool|WP_Error
     */
    public function validate_fields()
    {
        return true;
    }

    /**
     * Disable billing validation.
     *
     * @param $val
     *
     * @return bool
     */
    public function should_validate_billing_details($val)
    {
        if ($this->is_billing_fields_removed()) {
            $val = false;
        }

        return $val;
    }

    protected function is_billing_fields_removed()
    {
        return $this->get_value('remove_billing_fields') == 'true';
    }

    protected function billing_address_form()
    {
        if ($this->is_billing_fields_removed()) return;

        parent::billing_address_form();
    }

    /**
     * @param $transaction_id
     * @param OrderEntity $order
     *
     * @return string
     */
    public function link_transaction_id($transaction_id, $order)
    {
        $url = $order->get_meta('mollie_dashboard_url');

        if ($order->exists() && ! empty($url)) {
            $transaction_id = '<a href="' . esc_url($url) . '" target="_blank">' . esc_html($transaction_id) . '</a>';
        }

        return $transaction_id;
    }

    /**
     * Determines if the subscription can be cancelled
     *
     * @param $ret
     *
     * @param SubscriptionEntity $subscription
     *
     * @return      bool
     */
    public function can_cancel($ret, $subscription)
    {
        if ($subscription->get_payment_method() === $this->id && ! empty($subscription->profile_id) && in_array($subscription->status, $this->get_cancellable_statuses())) {
            return true;
        }

        return $ret;
    }

    /**
     * Cancels a subscription immediately.
     *
     * @param SubscriptionEntity $subscription
     *
     * @return bool
     */
    public function cancel_immediately($subscription)
    {
        try {

            if (empty($subscription->profile_id)) {
                throw new \Exception(__('Missing profile ID.', 'profilepress-pro'));
            }

            if ($subscription->is_cancelled()) return true;

            $mollie_customer_id = $subscription->get_customer()->get_meta(self::$MOLLIE_CUSTOMER_ID);

            $api = $this->getHttpclient();

            $response = $api->make_request(sprintf('customers/%s/subscriptions/%s', $mollie_customer_id, $subscription->profile_id), [], [], 'DELETE');

            if ('canceled' !== $response->status) {
                throw new \Exception(sprintf('Unexpected error during subscription cancellation: %s', json_encode($response)));
            }

            return true;

        } catch (\Exception $e) {

            $subscription->add_note(sprintf(
                __('Failed to cancel subscription in Mollie. Message: %s', 'profilepress-pro'),
                esc_html($e->getMessage())
            ));

            return false;
        }
    }

    /**
     * @param SubscriptionEntity $subscription
     *
     * @return bool
     */
    public function cancel($subscription)
    {
        return $this->cancel_immediately($subscription);
    }

    private function get_order_metadata($order, $subscription)
    {
        return [
            'order_id'        => $order->id,
            'order_key'       => $order->order_key,
            'customer_id'     => $order->customer_id,
            'subscription_id' => $subscription->id,
            'caller'          => __CLASS__ . '|' . __METHOD__ . '|' . __LINE__ . '|' . PPRESS_VERSION_NUMBER,
        ];
    }

    /**
     * @param CustomerEntity $customer
     *
     * @return string
     * @throws \Exception
     */
    private function get_mollie_customer_id($customer)
    {
        $customer_id = $customer->get_meta(self::$MOLLIE_CUSTOMER_ID);

        if ( ! empty($customer_id)) return $customer_id;

        $api = $this->getHttpclient();

        $customer_data = [
            'name'  => $customer->get_name(),
            'email' => $customer->get_email()
        ];

        $response = $api->make_request('customers', $customer_data);

        if (isset($response->id)) {
            $customer->update_meta(self::$MOLLIE_CUSTOMER_ID, $response->id);

            return $response->id;
        }

        throw new \Exception(
            sprintf('Unexpected response when creating customer: %s', json_encode($response)),
            $api->last_response_code
        );
    }

    public function process_payment($order_id, $subscription_id, $customer_id)
    {
        $order        = OrderFactory::fromId($order_id);
        $subscription = SubscriptionFactory::fromId($subscription_id);
        $customer     = CustomerFactory::fromId($customer_id);

        $plan = PlanFactory::fromId($order->plan_id);

        try {

            $payment_data = [
                'amount'      => [
                    'currency' => $order->currency,
                    'value'    => Init::process_amount($order->get_total()),
                ],
                'description' => $plan->get_name(),
                'redirectUrl' => $this->get_success_url($order->order_key),
                'webhookUrl'  => add_query_arg(['order_key' => $order->get_order_key()], $this->get_webhook_url()),
                'metadata'    => $this->get_order_metadata($order, $subscription)
            ];

            if ($plan->is_auto_renew()) {

                $payment_data['customerId']   = $this->get_mollie_customer_id($customer);
                $payment_data['sequenceType'] = 'first';

                $payment_data = apply_filters('ppress_mollie_recurring_create_payment_args', $payment_data, $order, $plan, $subscription);

            } else {

                $payment_data = apply_filters('ppress_mollie_create_payment_args', $payment_data, $order, $plan, $subscription);
            }

            $api = $this->getHttpclient();

            $response = $api->make_request('payments', $payment_data);

            if ( ! isset($response->_links->checkout->href)) {
                throw new \Exception(
                    sprintf('Unexpected response when creating mollie payment: %s', json_encode($response)),
                    $api->last_response_code
                );
            }

            return (new CheckoutResponse())
                ->set_is_success(true)
                ->set_redirect_url($response->_links->checkout->href);

        } catch (\Exception $e) {

            ppress_log_error(__METHOD__ . '(): ' . $e->getMessage());

            $error_message = defined('W3GUY_LOCAL') ? $e->getMessage() : __('An error occurred while communicating with Mollie. Please try again.', 'profilepress-pro');

            return (new CheckoutResponse())
                ->set_is_success(false)
                ->set_error_message($error_message);
        }
    }

    public function process_refund($order_id, $amount = null, $reason = '')
    {
        try {

            $order = OrderRepository::init()->retrieve($order_id);

            $api      = $this->getHttpclient();
            $response = $api->make_request('payments/' . $order->transaction_id . '/refunds', [
                'amount' => [
                    'currency' => $order->currency,
                    'value'    => Init::process_amount($order->get_total()),
                ],
            ]);

            if ( ! ppress_is_http_code_success($api->last_response_code)) {
                throw new \Exception(
                    sprintf('Invalid response code: %d. Response: %s', $api->last_response_code, json_encode($response))
                );
            }

            return true;

        } catch (\Exception $e) {
            ppress_log_error($e->getMessage() . '; OrderID:' . $order_id);

            return false;
        }
    }

    private function get_webhook_transaction_id()
    {
        $transaction_id = ppressPOST_var('id', '', true);

        if (empty($transaction_id)) {

            $body = file_get_contents('php://input');

            if (false === $body) return false;

            if (0 !== strpos($body, 'id=')) return false;

            $transaction_id = str_replace('id=', '', $body);
        }

        return ('' !== $transaction_id) ? $transaction_id : false;
    }

    /**
     * @param int $payment_id
     *
     * @return mixed
     * @throws \Exception
     */
    private function get_mollie_payment($payment_id)
    {
        return $this->getHttpclient()->make_request("payments/$payment_id", [], [], 'GET');
    }

    /**
     * @param $mollie_customer_id
     *
     * @return mixed
     * @throws \Exception
     */
    private function has_valid_customer_mandate($mollie_customer_id)
    {
        $mandates = $this->getHttpclient()->make_request("customers/$mollie_customer_id/mandates", [], [], 'GET');

        if (isset($mandates->_embedded->mandates)) {

            foreach ($mandates->_embedded->mandates as $mandate) {
                if (in_array($mandate->status, ['valid', 'pending'], true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param int $mollie_customer_id
     * @param OrderEntity $order
     * @param SubscriptionEntity $subscription
     *
     * @throws \Exception
     */
    private function create_mollie_subscription($mollie_customer_id, $order, $subscription)
    {
        $total_payments = $subscription->get_total_payments();

        if ($total_payments > 0) { // is total payments defined? 0 means disabled hence this check

            if ($total_payments <= 1 || ($total_payments - 1) <= 0) {
                return false;
            }
        }

        switch ($subscription->billing_frequency) {
            case SubscriptionBillingFrequency::DAILY:
                $interval = '1 days';
                break;
            case SubscriptionBillingFrequency::WEEKLY:
                $interval = '1 weeks';
                break;
            case SubscriptionBillingFrequency::QUARTERLY:
                $interval = '3 months';
                break;
            case SubscriptionBillingFrequency::EVERY_6_MONTHS:
                $interval = '6 months';
                break;
            case SubscriptionBillingFrequency::YEARLY:
                $interval = '12 months';
                break;
            default:
                $interval = '1 months';
        }

        $sub_data = [
            'amount'      => [
                'currency' => $order->currency,
                'value'    => Init::process_amount($subscription->get_recurring_amount()),
            ],
            'description' => sprintf('%s (#%s)', $order->get_plan()->get_name(), $subscription->get_id()),
            'interval'    => $interval,
            'startDate'   => CarbonImmutable::parse($subscription->expiration_date, 'UTC')->toDateString(),
            'webhookUrl'  => add_query_arg(['sub_id' => $subscription->get_id()], $this->get_webhook_url()),
            'metadata'    => $this->get_order_metadata($order, $subscription)
        ];

        $total_payments = $subscription->get_total_payments();

        if ($total_payments > 0 && (--$total_payments) > 0) {
            $sub_data['times'] = $subscription->get_total_payments() - 1;
        }

        return $this->getHttpclient()->make_request("customers/$mollie_customer_id/subscriptions", $sub_data);
    }

    /**
     * @throws \Exception
     */
    private function renew_subscription_action($mollie_payment)
    {
        if ('paid' !== $mollie_payment->status) return;

        $subscription = SubscriptionFactory::fromProfileId($mollie_payment->subscriptionId);

        // If subscription is not available.
        if ( ! $subscription || ! $subscription->exists()) {
            throw new \Exception(__('Subscription is missing.', 'profilepress-pro'));
        }

        $order = OrderFactory::fromTransactionId($mollie_payment->id);

        // if renewal is not found.
        if ( ! $order || ! $order->exists()) {

            // This is a renewal charge
            $order_id = $subscription->add_renewal_order([
                'transaction_id' => $mollie_payment->id,
                'total_amount'   => ppress_sanitize_amount($mollie_payment->amount->value),
            ]);

            if ( ! empty($order_id)) $subscription->renew();

        } else {

            // ensure renewal order is completed
            if ( ! $order->is_completed()) $order->update_status(OrderStatus::COMPLETED);

            // ensure sub is active
            if ( ! $subscription->is_active()) {
                $subscription->update_status(SubscriptionStatus::ACTIVE);
            }
        }
    }

    private function refund_payment_action($mollie_payment)
    {
        $order = OrderFactory::fromTransactionId($mollie_payment->id);

        if ( ! $order || ! $order->exists() || $order->is_refunded()) return;

        $subscription = $order->get_subscription();

        $order_amount    = $order->get_total();
        $refunded_amount = $mollie_payment->amountRefunded->value ?? $order_amount;
        $currency        = $mollie_payment->amountRefunded->currency ?? $order->currency;

        /* Translators: %1$s - Amount refunded; %2$s - Original payment ID */
        $order_note = sprintf(
            esc_html__('Amount: %1$s; Order transaction ID: %2$s', 'profilepress-pro'),
            ppress_display_amount($refunded_amount, $currency),
            esc_html($order->transaction_id)
        );

        if (Calculator::init($refunded_amount)->isLessThan($order_amount)) {

            $order->add_note(esc_html__('Partial refund processed in Mollie.', 'profilepress-pro') . ' ' . $order_note);

        } else {

            $order->refund_order();

            $subscription->cancel(true);
        }
    }

    /**
     * @return false|void
     */
    public function process_webhook()
    {
        try {

            $transaction_id = $this->get_webhook_transaction_id();

            $payment = $this->get_mollie_payment($transaction_id);

            if (isset($payment->status)) {

                // detect refunded payment webhook
                if ( ! empty($payment->_links->refunds)) {

                    $this->refund_payment_action($payment);

                } elseif (isset($_GET['sub_id']) && ! empty($_GET['sub_id'])) {

                    $this->renew_subscription_action($payment);

                } else {

                    if (isset($payment->metadata->order_key)) {

                        $order = OrderFactory::fromOrderKey($payment->metadata->order_key);

                        if ($order->exists()) {

                            // ensures order has a TXN ID
                            $order->update_transaction_id($payment->id);

                            $sub = SubscriptionFactory::fromId($order->subscription_id);

                            if ( ! empty($payment->paidAt)) {

                                $order->update_meta('mollie_dashboard_url', $payment->_links->dashboard->href);
                                if ( ! $order->is_completed()) {
                                    $order->complete_order($payment->id);
                                }
                                if ($sub->exists() && ! $sub->is_active()) {
                                    $sub->activate_subscription();
                                }

                                if ($sub->get_plan()->is_auto_renew()) {

                                    $mollie_customer_id = $sub->get_customer()->get_meta(self::$MOLLIE_CUSTOMER_ID);

                                    if ($this->has_valid_customer_mandate($mollie_customer_id)) {

                                        $result = $this->create_mollie_subscription(
                                            $mollie_customer_id,
                                            $order,
                                            SubscriptionFactory::fromId($sub->id) // reinitializing to get updated renewal date.
                                        );

                                        if (isset($result->id)) {
                                            $sub->update_profile_id($result->id);
                                        }
                                    }
                                }
                            }

                            if ($payment->status == 'failed') $order->fail_order();
                        }
                    }
                }
            }

            http_response_code(200);

        } catch (\Exception $e) {
            ppress_log_error($e->getMessage());

            return false;
        }
    }
}