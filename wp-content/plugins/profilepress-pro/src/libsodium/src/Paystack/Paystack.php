<?php

namespace ProfilePress\Libsodium\Paystack;

use ProfilePress\Core\Membership\Controllers\CheckoutResponse;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionBillingFrequency;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\AbstractPaymentMethod;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;
use ProfilePress\Core\Membership\Repositories\OrderRepository;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePress\Core\RegisterScripts;
use ProfilePress\Libsodium\Paystack\WebhookHandlers\ChargeSuccess;
use ProfilePress\Libsodium\Paystack\WebhookHandlers\RefundProcessed;
use ProfilePress\Libsodium\Paystack\WebhookHandlers\SubscriptionCancelled;
use ProfilePress\Libsodium\Paystack\WebhookHandlers\SubscriptionRenewed;
use ProfilePressVendor\Carbon\CarbonImmutable;
use WP_Error;

class Paystack extends AbstractPaymentMethod
{
    public function __construct()
    {
        parent::__construct();

        $this->id          = 'paystack';
        $this->title       = 'Paystack';
        $this->description = esc_html__('Pay via Paystack', 'profilepress-pro');

        $this->method_title       = 'Paystack';
        $this->method_description = esc_html__('Accept payments via Paystack.', 'profilepress-pro');

        $this->icon = PPRESS_ASSETS_URL . '/images/paystack-icon.png';

        $this->supports = [self::SUBSCRIPTIONS, self::REFUNDS];
    }

    public function admin_settings()
    {
        $settings = parent::admin_settings();

        $settings['public_key'] = [
            'label' => esc_html__('Public Key', 'profilepress-pro'),
            'type'  => 'text',
        ];

        $settings['secret_key'] = [
            'label' => esc_html__('Secret Key', 'profilepress-pro'),
            'type'  => 'password',
        ];

        $settings['webhook_info'] = [
            'label' => esc_html__('Webhook Setup', 'profilepress-pro'),
            'type'  => 'custom_field_block',
            'data'  => sprintf(
                __('In order for Paystack to function, ensure the following URL %1$s is added as a webhook URL to %4$syour Paystack account%2$s. Learn more from our %3$sdocumentation%2$s', 'profilepress-pro'),
                '<code>' . esc_url($this->get_webhook_url()) . '</code>',
                '</a>',
                '<a target="_blank" href="https://profilepress.com/article/setting-up-paystack/">',
                '<a target="_blank" href="https://dashboard.paystack.com/#/settings/developer">'
            ),
        ];

        $settings['remove_billing_fields'] = [
            'label'          => esc_html__('Remove Billing Address', 'profilepress-pro'),
            'type'           => 'checkbox',
            'checkbox_label' => esc_html__('Check to remove billing address fields from the checkout page.', 'profilepress-pro'),
            'description'    => esc_html__('If you do not want the billing address fields displayed on the checkout page, use this setting to remove it.', 'profilepress-pro')
        ];

        return $settings;
    }

    protected function get_secret_key()
    {
        return $this->get_value('secret_key');
    }

    protected function get_public_key()
    {
        return $this->get_value('public_key');
    }

    /**
     * @throws \Exception
     */
    protected function getHttpclient()
    {
        return new APIClass($this->get_secret_key());
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
        if ($this->is_billing_fields_removed()) $val = false;

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
        if ( ! empty($transaction_id)) {

            if ( ! is_numeric($transaction_id)) {
                return sprintf('<a target="_blank" href="https://dashboard.paystack.com/#/search?model=transactions&query=%1$s">%1$s</a>', $transaction_id);
            }

            return sprintf('<a target="_blank" href="https://dashboard.paystack.com/#/transactions/%1$s/analytics">%1$s</a>', $transaction_id);
        }

        return $transaction_id;
    }

    public function link_profile_id($profile_id, $subscription)
    {
        if ( ! empty($profile_id)) {
            return sprintf('<a target="_blank" href="https://dashboard.paystack.com/#/subscriptions/%1$s">%1$s</a>', $profile_id);
        }

        return $profile_id;
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
        $sub_token = get_option(sprintf('ppress_paystack_email_token_%s', $subscription->id));

        if ( ! $sub_token || empty($sub_token)) return false;

        try {

            $response = $this->getHttpclient()->make_request('subscription/disable', [
                'code'  => $subscription->profile_id,
                'token' => $sub_token
            ]);

            if ( ! isset($response->status) || $response->status != true) {
                throw new \Exception(json_encode($response));
            }

            return true;

        } catch (\Exception $e) {

            $subscription->add_note(
                sprintf(
                    esc_html__('Unexpected error during subscription cancellation: %s.', 'profilepress-pro'),
                    wp_json_encode($e->getMessage())
                )
            );

            return false;
        }
    }

    /**
     * Cancels a subscription at period end.
     *
     * @param SubscriptionEntity $subscription
     *
     * @return bool
     */
    public function cancel($subscription)
    {
        return $this->cancel_immediately($subscription);
    }

    public function process_refund($order_id, $amount = null, $reason = '')
    {
        try {

            $order = OrderRepository::init()->retrieve($order_id);

            $api = $this->getHttpclient();

            $api->make_request('refund', ['transaction' => $order->transaction_id]);

            return ppress_is_http_code_success($api->last_response_code);

        } catch (\Exception $e) {
            ppress_log_error($e->getMessage() . '; OrderID:' . $order_id);

            return false;
        }
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

    public function enqueue_frontend_assets()
    {
        if ( ! function_exists('ppress_is_checkout')) return;

        if ( ! ppress_is_checkout()) return;

        if ( ! PaymentMethods::get_instance()->get_by_id('paystack')->is_enabled()) return;

        try {
            $this->getHttpclient();
        } catch (\Exception $e) {
            ppress_log_error(__METHOD__ . '(): ' . $e->getMessage());

            return;
        }

        $plan = ppress_get_plan(intval(ppressGET_var('plan')));

        if ( ! $plan->exists()) return;

        wp_register_script(
            'ppress-paystack-js-sdk',
            'https://js.paystack.co/v2/inline.js',
            [],
            null
        );

        $suffix = RegisterScripts::asset_suffix();

        wp_register_script(
            'ppress-paystack',
            PROFILEPRESS_PRO_LIBSODIUM_ASSETS_URL . "paystack/paystack-checkout{$suffix}.js",
            array(
                'ppress-paystack-js-sdk',
                'jquery',
                'ppress-frontend-script'
            ),
            PROFILEPRESS_PRO_VERSION_NUMBER,
            true
        );

        wp_enqueue_script('ppress-paystack-js-sdk');

        wp_enqueue_script('ppress-paystack');
    }

    /**
     * @throws \Exception
     */
    private function create_paystack_plan($args)
    {
        $api = $this->getHttpclient();

        $response = $api->make_request('plan', $args);

        if ( ! isset($response->data->plan_code)) {
            throw new \Exception(
                sprintf('Unexpected response when creating paystack plan: %s', json_encode($response)),
                $api->last_response_code
            );
        }

        return $response->data->plan_code;
    }

    /**
     * @param OrderEntity $order
     * @param SubscriptionEntity $subscription
     *
     * @return int
     * @throws \Exception
     */
    public function create_paystack_subscription(OrderEntity $order, SubscriptionEntity $subscription)
    {
        $plan = ppress_get_plan($subscription->plan_id);

        $create_plan_args = apply_filters('ppress_paystack_create_plan_args', [
            'name'     => $plan->get_name(),
            'interval' => $this->get_paystack_plan_interval($subscription),
            'amount'   => ppress_decimal_to_cent($subscription->get_recurring_amount()),
            'currency' => $order->currency
        ], $plan, $subscription);

        $pp_create_plan_id = md5(json_encode($create_plan_args));

        $paystack_plan_id = $plan->get_meta($pp_create_plan_id);

        if (empty($paystack_plan_id) || ! $this->is_paystack_plan_exist($paystack_plan_id)) {
            $paystack_plan_id = $this->create_paystack_plan($create_plan_args);
            $plan->update_meta($pp_create_plan_id, $paystack_plan_id);
        }

        $create_subscription_args = apply_filters('ppress_paystack_create_subscription_args', [
            'customer'   => $order->get_customer_email(),
            'plan'       => $paystack_plan_id,
            'start_date' => CarbonImmutable::parse($subscription->expiration_date)->toIso8601String(),
        ], $order, $subscription);

        $response = $this->getHttpclient()->make_request('subscription', $create_subscription_args);

        if ( ! isset($response->data->id)) {
            throw new \Exception(
                sprintf('Unexpected response when creating paystack subscription: %s', json_encode($response))
            );
        }

        update_option(sprintf('ppress_paystack_email_token_%s', $subscription->id), $response->data->email_token, false);

        return $response->data->subscription_code;
    }

    /**
     * @throws \Exception
     */
    private function is_paystack_plan_exist($id)
    {
        $response = $this->getHttpclient()->make_request('plan/' . $id, [], [], 'GET');

        return isset($response->data->plan_code) && $response->data->plan_code === $id;
    }

    private function get_paystack_plan_interval(SubscriptionEntity $subscription)
    {
        $interval = $subscription->billing_frequency;

        switch ($subscription->billing_frequency) {
            case SubscriptionBillingFrequency::QUARTERLY:
                $interval = 'quarterly';
                break;
            case SubscriptionBillingFrequency::EVERY_6_MONTHS:
                $interval = 'biannually';
                break;
            case SubscriptionBillingFrequency::YEARLY:
                $interval = 'annually';
                break;
        }

        return $interval;
    }

    public function process_payment($order_id, $subscription_id, $customer_id)
    {
        $order        = OrderFactory::fromId($order_id);
        $subscription = SubscriptionFactory::fromId($subscription_id);
        $customer     = CustomerFactory::fromId($customer_id);

        try {

            $meta_data                  = $this->get_order_metadata($order, $subscription);
            $meta_data['cancel_action'] = ppress_get_cancel_url($order->get_order_key());

            $zero_amount = '50.00';

            if (in_array($order->currency, ['GHS', 'ZAR', 'USD'])) {
                $zero_amount = '1.00';
            }

            // charging 100 for free or zero orders because checkout popup requires a non-zero amount
            $order_total = Calculator::init($order->get_total())->isNegativeOrZero() ? $zero_amount : $order->get_total();

            $gateway_response = [
                'public_key'   => $this->get_public_key(),
                'email'        => $customer->get_email(),
                'amount'       => ppress_decimal_to_cent($order_total),
                'ref'          => $order->get_order_key(),
                'currency'     => $order->currency,
                'metadata'     => $meta_data,
                'is_recurring' => $subscription->get_plan()->is_auto_renew() ? 'true' : 'false'
            ];

            return (new CheckoutResponse())
                ->set_is_success(true)
                ->set_gateway_response($gateway_response);

        } catch (\Exception $e) {

            ppress_log_error(__METHOD__ . '(): ' . $e->getMessage());

            $error_message = defined('W3GUY_LOCAL') ? $e->getMessage() : __('An error occurred while communicating with Paystack. Please try again.', 'profilepress-pro');

            return (new CheckoutResponse())
                ->set_is_success(false)
                ->set_error_message($error_message);
        }
    }

    /**
     * @see https://paystack.com/docs/payments/webhooks/#signature-validation
     *
     * @throws \Exception
     */
    private function verify_webhook()
    {
        if ((strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') || ! isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'])) {
            throw new \Exception('Invalid request method or missing signature header');
        }

        $input = @file_get_contents("php://input");

        if ($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] !== hash_hmac('sha512', $input, $this->get_secret_key())) {
            throw new \Exception('Invalid signature passed');
        }

        return json_decode($input);
    }

    private function valid_webhook_events()
    {
        return apply_filters('ppress_paystack_webhooks_whitelist', [
            'charge.success'         => new ChargeSuccess(),
            'refund.processed'       => new RefundProcessed(),
            'subscription.not_renew' => new SubscriptionCancelled(),
            'subscription.disable'   => new SubscriptionCancelled(),
            'invoice.create'         => new SubscriptionRenewed(),
            'invoice.update'         => new SubscriptionRenewed()
        ]);
    }

    /**
     * @return false|void
     */
    public function process_webhook()
    {
        try {

            $payload = $this->verify_webhook();

            $webhooks = $this->valid_webhook_events();

            if (isset($payload->event) && in_array($payload->event, array_keys($webhooks))) {
                /** @var WebhookHandlerInterface $callable */
                $callable = $webhooks[$payload->event];

                call_user_func([$callable, 'handle'], $payload->data);

                do_action('ppress_paystack_webhook_event', $payload->event, $payload);
            }

            http_response_code(200);

        } catch (\Exception $e) {
            http_response_code(400);
            exit();
        }
    }
}