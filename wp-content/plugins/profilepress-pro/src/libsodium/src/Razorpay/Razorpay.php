<?php

namespace ProfilePress\Libsodium\Razorpay;

use ProfilePress\Core\Membership\Controllers\CheckoutResponse;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Plan\PlanFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionBillingFrequency;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\AbstractPaymentMethod;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePress\Core\RegisterScripts;
use ProfilePress\Libsodium\Razorpay\WebhookHandlers\InvoicePaid;
use ProfilePress\Libsodium\Razorpay\WebhookHandlers\OrderPaid;
use ProfilePress\Libsodium\Razorpay\WebhookHandlers\RefundProcessed;
use ProfilePress\Libsodium\Razorpay\WebhookHandlers\SubscriptionAuthenticated;
use ProfilePress\Libsodium\Razorpay\WebhookHandlers\SubscriptionCancelled;
use ProfilePressVendor\Carbon\CarbonImmutable;
use WP_Error;

class Razorpay extends AbstractPaymentMethod
{
    public function __construct()
    {
        parent::__construct();

        $this->id          = 'razorpay';
        $this->title       = 'Razorpay';
        $this->description = esc_html__('Pay via Razorpay', 'profilepress-pro');

        $this->method_title       = 'Razorpay';
        $this->method_description = esc_html__('Accept payments via Razorpay.', 'profilepress-pro');

        $this->icon = PPRESS_ASSETS_URL . '/images/razorpay-icon.svg';

        $this->supports = [self::SUBSCRIPTIONS];
    }

    public function admin_settings()
    {
        $settings = parent::admin_settings();

        $settings['key_id'] = [
            'label' => esc_html__('Key ID', 'profilepress-pro'),
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
                __('In order for Razorpay to function, ensure the following URL %1$s is added as a webhook URL to %4$syour Razorpay account%2$s. Learn more from our %3$sdocumentation%2$s', 'profilepress-pro'),
                '<code>' . esc_url($this->get_webhook_url()) . '</code>',
                '</a>',
                '<a target="_blank" href="https://profilepress.com/article/setting-up-razorpay/">',
                '<a target="_blank" href="https://dashboard.razorpay.com/app/webhooks">'
            ),
        ];

        $settings['webhook_secret'] = [
            'label' => esc_html__('Webhook Secret', 'profilepress-pro'),
            'type'  => 'password',
        ];

        $settings['theme_color'] = [
            'label'       => esc_html__('Popup Theme Color', 'profilepress-pro'),
            'type'        => 'color',
            'description' => esc_html__('Use this setting to set the theme color of the Razorpay popup', 'profilepress-pro')
        ];

        $settings['popup_image_url'] = [
            'label'       => esc_html__('Popup Image URL', 'profilepress-pro'),
            'type'        => 'text',
            'description' => esc_html__('Use this setting to set the image URL displayed in the Razorpay popup.', 'profilepress-pro')
        ];

        $settings['remove_billing_fields'] = [
            'label'          => esc_html__('Remove Billing Address', 'profilepress-pro'),
            'type'           => 'checkbox',
            'checkbox_label' => esc_html__('Check to remove billing address fields from the checkout page.', 'profilepress-pro'),
            'description'    => esc_html__('If you do not want the billing address fields displayed on the checkout page, use this setting to remove it.', 'profilepress-pro')
        ];

        return $settings;
    }

    protected function get_key_id()
    {
        return $this->get_value('key_id');
    }

    protected function get_secret_key()
    {
        return $this->get_value('secret_key');
    }

    /**
     * @throws \Exception
     */
    protected function getHttpclient()
    {
        return new APIClass($this->get_key_id(), $this->get_secret_key());
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
            return sprintf('<a target="_blank" href="https://dashboard.razorpay.com/app/orders/%1$s">%1$s</a>', $transaction_id);
        }

        return $transaction_id;
    }

    public function link_profile_id($profile_id, $subscription)
    {
        if ( ! empty($profile_id)) {
            return sprintf('<a target="_blank" href="https://dashboard.razorpay.com/app/subscriptions/%1$s">%1$s</a>', $profile_id);
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
        try {

            $response = $this->getHttpclient()->make_request(
                sprintf('subscriptions/%s/cancel', $subscription->profile_id),
                []
            );

            if ( ! isset($response->id)) {
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

        if ( ! PaymentMethods::get_instance()->get_by_id('razorpay')->is_enabled()) return;

        try {
            $this->getHttpclient();
        } catch (\Exception $e) {
            ppress_log_error(__METHOD__ . '(): ' . $e->getMessage());

            return;
        }

        $plan = ppress_get_plan(intval(ppressGET_var('plan')));

        if ( ! $plan->exists()) return;

        wp_register_script(
            'ppress-razorpay-js-sdk',
            'https://checkout.razorpay.com/v1/checkout.js',
            [],
            null
        );

        $suffix = RegisterScripts::asset_suffix();

        wp_register_script(
            'ppress-razorpay',
            PROFILEPRESS_PRO_LIBSODIUM_ASSETS_URL . "razorpay/razorpay-checkout{$suffix}.js",
            array(
                'ppress-razorpay-js-sdk',
                'jquery',
                'ppress-frontend-script'
            ),
            PROFILEPRESS_PRO_VERSION_NUMBER,
            true
        );

        wp_enqueue_script('ppress-razorpay-js-sdk');

        wp_enqueue_script('ppress-razorpay');
    }

    private function get_razorpay_billing_frequency($billing_frequency)
    {
        $period = $billing_frequency;

        $interval = 1;

        switch ($billing_frequency) {
            case SubscriptionBillingFrequency::QUARTERLY:
                $interval = 3;
                $period   = 'monthly';
                break;
            case SubscriptionBillingFrequency::EVERY_6_MONTHS:
                $interval = 6;
                $period   = 'monthly';
                break;
            case SubscriptionBillingFrequency::YEARLY:
                $interval = 1;
                $period   = 'yearly';
                break;
        }

        return [$period, $interval];
    }

    /**
     * @throws \Exception
     */
    private function create_razorpay_plan($args)
    {
        $api = $this->getHttpclient();

        $response = $api->make_request('plans', $args);

        if ( ! isset($response->id)) {
            throw new \Exception(
                sprintf('Unexpected response when creating razorpay plan: %s', json_encode($response)),
                $api->last_response_code
            );
        }

        return $response->id;
    }

    /**
     * @throws \Exception
     */
    private function create_razorpay_subscription($plan_id, OrderEntity $order, SubscriptionEntity $subscription)
    {
        /**
         * Currently, you can only charge a Subscription for up to 10 years. We now using this instead of the
         * create subscription endpoint imposed limits defined by the error message states in each cases below.
         */

        // Defaults to Monthly total count - maximum is 1200 else this error "Exceeds the maximum total_count (1200) allowed for the given period and interval"
        $total_count = 120;

        // according to https://razorpay.com/docs/api/payments/subscriptions/#error-response-parameters-6 you can only charge a Subscription for up to 10 years.
        switch ($subscription->billing_frequency) {
            case SubscriptionBillingFrequency::QUARTERLY:
                // error message states 400 is maximum - {"error":{"code":"BAD_REQUEST_ERROR","description":"Exceeds the maximum total_count (400) allowed for the given period and interval"}}
                $total_count = 40;
                break;
            case SubscriptionBillingFrequency::EVERY_6_MONTHS:
                // error message states 400 is maximum - {"error":{"code":"BAD_REQUEST_ERROR","description":"Exceeds the maximum total_count (200) allowed for the given period and interval"}}
                $total_count = 20;
                break;
            case SubscriptionBillingFrequency::WEEKLY:
                // error message states 400 is maximum - {"error":{"code":"BAD_REQUEST_ERROR","description":"Exceeds the maximum total_count (5200) allowed for the given period and interval"}}
                $total_count = 520;
                break;
            case SubscriptionBillingFrequency::YEARLY:
                // error message states 400 is maximum - {"error":{"code":"BAD_REQUEST_ERROR","description":"Exceeds the maximum total_count (100) allowed for the given period and interval"}}
                $total_count = 10;
                break;
        }

        if ($subscription->get_total_payments() > 0) {
            $total_count = $subscription->get_total_payments();
        }

        $payload = [
            'plan_id'     => $plan_id,
            'total_count' => $total_count,
            'start_at'    => CarbonImmutable::parse($subscription->expiration_date, 'UTC')->getTimestamp(),
            'notes'       => $this->get_order_metadata($order, $subscription)
        ];

        if (Calculator::init($order->get_total())->isGreaterThanZero()) {
            $payload['addons'][] = [
                'item' => [
                    'name'     => apply_filters('ppress_razorpay_addon_payment_name', esc_html__('Setup Fee', 'profilepress-pro')),
                    'amount'   => Init::process_amount($order->get_total()),
                    'currency' => $order->currency
                ]
            ];
        }

        $response = $this->getHttpclient()->make_request('subscriptions', $payload);

        if ( ! isset($response->id)) {
            throw new \Exception(
                sprintf('Unexpected response when creating razorpay subscription: %s', json_encode($response))
            );
        }

        $subscription->update_profile_id($response->id);

        return $response->id;
    }

    /**
     * @throws \Exception
     */
    private function is_razorpay_plan_exist($id)
    {
        $response = $this->getHttpclient()->make_request('plans/' . $id, [], [], 'GET');

        return isset($response->id) && $response->id === $id;
    }

    public function process_payment($order_id, $subscription_id, $customer_id)
    {
        $order        = OrderFactory::fromId($order_id);
        $subscription = SubscriptionFactory::fromId($subscription_id);
        $customer     = CustomerFactory::fromId($customer_id);

        $plan = PlanFactory::fromId($order->plan_id);

        $business_name = ppress_business_name();
        if (empty($business_name)) $business_name = ucwords(ppress_site_title());

        $gateway_response = [
            'key_id'         => $this->get_key_id(),
            'business_name'  => $business_name,
            'description'    => $plan->get_name(),
            'theme_color'    => $this->get_value('theme_color'),
            'image'          => $this->get_value('popup_image_url'),
            'customer_name'  => $customer->get_name(),
            'customer_email' => $customer->get_email(),
            'customer_phone' => $order->billing_phone,
            'notes'          => $this->get_order_metadata($order, $subscription)
        ];

        try {

            if ($plan->is_auto_renew()) {

                $razorpay_frequency = $this->get_razorpay_billing_frequency($subscription->billing_frequency);

                $create_plan_args = [
                    'period'   => $razorpay_frequency[0],
                    'interval' => $razorpay_frequency[1],
                    'item'     => [
                        'name'     => $plan->name,
                        'amount'   => Init::process_amount($subscription->recurring_amount),
                        'currency' => $order->currency,
                    ]
                ];

                $pp_create_plan_id = md5(json_encode($create_plan_args));

                $razorpay_plan_id = $plan->get_meta($pp_create_plan_id);

                if (empty($razorpay_plan_id) || ! $this->is_razorpay_plan_exist($razorpay_plan_id)) {
                    $razorpay_plan_id = $this->create_razorpay_plan($create_plan_args);
                    $plan->update_meta($pp_create_plan_id, $razorpay_plan_id);
                }

                // https://razorpay.com/docs/payments/subscriptions/test/
                $gateway_response['subscription_id'] = $this->create_razorpay_subscription($razorpay_plan_id, $order, $subscription);

            } else {

                $order_data = [
                    'receipt'  => $order->get_order_key(),
                    'amount'   => Init::process_amount($order->get_total()),
                    'currency' => $order->currency,
                    'notes'    => $this->get_order_metadata($order, $subscription)
                ];

                $order_data = apply_filters('ppress_razorpay_create_order_args', $order_data, $order, $plan, $subscription);

                $api = $this->getHttpclient();

                // https://razorpay.com/docs/payments/payment-gateway/web-integration/standard/build-integration
                $response = $api->make_request('orders', $order_data);

                if ( ! isset($response->id)) {
                    throw new \Exception(
                        sprintf('Unexpected response when creating razorpay payment: %s', json_encode($response)),
                        $api->last_response_code
                    );
                }

                $order->update_transaction_id($response->id);

                $gateway_response['order_id'] = $response->id;
                $gateway_response['amount']   = $response->amount;
                $gateway_response['currency'] = $response->currency;
            }

            return (new CheckoutResponse())
                ->set_is_success(true)
                ->set_gateway_response($gateway_response);

        } catch (\Exception $e) {

            ppress_log_error(__METHOD__ . '(): ' . $e->getMessage());

            $error_message = defined('W3GUY_LOCAL') ? $e->getMessage() : __('An error occurred while communicating with Razorpay. Please try again.', 'profilepress-pro');

            return (new CheckoutResponse())
                ->set_is_success(false)
                ->set_error_message($error_message);
        }
    }

    /**
     * @throws \Exception
     */
    private function verify_webhook($payload)
    {
        if ( ! isset($_SERVER['HTTP_X_RAZORPAY_SIGNATURE'])) return;

        $webhook_secret = $this->get_value('webhook_secret');
        $realSignature  = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'];

        $calculatedSignature = hash_hmac('sha256', $payload, $webhook_secret);

        $verified = hash_equals($calculatedSignature, $realSignature);

        if ($verified === false) {
            throw new \Exception('Invalid signature passed');
        }

        return json_decode($payload);
    }

    private function valid_webhook_events()
    {
        return apply_filters('ppress_razorpay_webhooks_whitelist', [
            'order.paid'                 => new OrderPaid(),
            'invoice.paid'               => new InvoicePaid(),
            'subscription.authenticated' => new SubscriptionAuthenticated(),
            'subscription.cancelled'     => new SubscriptionCancelled(),
            'refund.processed'           => new RefundProcessed()
        ]);
    }

    /**
     * @return false|void
     */
    public function process_webhook()
    {
        $payload = @file_get_contents('php://input');

        try {

            $payload = $this->verify_webhook($payload);

            $webhooks = $this->valid_webhook_events();

            if (isset($payload->event) && in_array($payload->event, array_keys($webhooks))) {
                /** @var WebhookHandlerInterface $callable */
                $callable = $webhooks[$payload->event];

                call_user_func([$callable, 'handle'], $payload->payload);

                do_action('ppress_razorpay_webhook_event', $payload->event, $payload);
            }

            http_response_code(200);

        } catch (\Exception $e) {
            http_response_code(400);
            exit();
        }
    }
}