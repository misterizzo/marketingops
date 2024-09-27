<?php

namespace ProfilePress\Libsodium\PayPal;

use ProfilePress\Core\Membership\Controllers\CheckoutResponse;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\CartEntity;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Order\OrderMode;
use ProfilePress\Core\Membership\Models\Plan\PlanEntity;
use ProfilePress\Core\Membership\Models\Plan\PlanFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionBillingFrequency;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionTrialPeriod;
use ProfilePress\Core\Membership\PaymentMethods\AbstractPaymentMethod;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;
use ProfilePress\Core\Membership\Repositories\OrderRepository;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePress\Core\Membership\Services\OrderService;
use ProfilePress\Core\RegisterScripts;
use WP_Error;

class PayPal extends AbstractPaymentMethod
{
    public function __construct()
    {
        parent::__construct();

        $this->id          = 'paypal';
        $this->title       = 'PayPal';
        $this->description = esc_html__('Pay via PayPal', 'profilepress-pro');

        $this->method_title       = 'PayPal';
        $this->method_description = esc_html__('Accept payments via PayPal including Credit Card, Venmo, Discover, iDEAL, American Express, Bancontact, BLIK, Giropay, MyBank, Przelewy24.', 'profilepress-pro');

        $this->icon = PPRESS_ASSETS_URL . '/images/paypal-icon.svg';

        $this->supports = [
            self::SUBSCRIPTIONS,
            self::REFUNDS,
        ];

        add_filter('ppress_render_view_output', [$this, 'replace_checkout_button'], 999999, 2);

        add_action('wp_ajax_ppress_paypal_process_checkout', [$this, 'process_paypal_checkout']);
        add_action('wp_ajax_nopriv_ppress_paypal_process_checkout', [$this, 'process_paypal_checkout']);

        add_action('wp_ajax_ppress_capture_paypal_order', [$this, 'capture_order']);
        add_action('wp_ajax_nopriv_ppress_capture_paypal_order', [$this, 'capture_order']);
    }

    public function admin_settings()
    {
        $settings = parent::admin_settings();

        $settings['client_id'] = [
            'label' => esc_html__('Client ID', 'profilepress-pro'),
            'type'  => 'text',
        ];

        $settings['secret'] = [
            'label' => esc_html__('Secret', 'profilepress-pro'),
            'type'  => 'password',
        ];

        $settings['webhook_info'] = [
            'label' => esc_html__('Webhook Setup', 'profilepress-pro'),
            'type'  => 'custom_field_block',
            'data'  => sprintf(
                __('In order for PayPal to function well, ensure the webhook endpoint %1$s is added to the list of webhooks in your PayPal application. Learn more from our %3$sdocumentation%2$s', 'profilepress-pro'),
                '<code>' . esc_url($this->get_webhook_url()) . '</code>',
                '</a>',
                '<a target="_blank" href="https://profilepress.com/article/setting-up-paypal/">'
            ),
        ];

        $settings['webhook_id'] = [
            'label' => esc_html__('Webhook ID', 'profilepress-pro'),
            'type'  => 'text',
        ];

        $settings['remove_billing_fields'] = [
            'label'          => esc_html__('Remove Billing Address', 'profilepress-pro'),
            'type'           => 'checkbox',
            'checkbox_label' => esc_html__('Check to remove billing address fields from the checkout page.', 'profilepress-pro'),
            'description'    => esc_html__('If you do not want the billing address fields displayed on the checkout page, use this setting to remove it.', 'profilepress-pro')
        ];

        return $settings;
    }

    protected function get_client_id()
    {
        return $this->get_value('client_id');
    }

    protected function get_secret()
    {
        return $this->get_value('secret');
    }

    protected function get_webhook_id()
    {
        return $this->get_value('webhook_id');
    }

    /**
     * @throws \Exception
     */
    protected function getHttpclient()
    {
        return new APIClass(
            $this->get_client_id(),
            $this->get_secret()
        );
    }

    public function replace_checkout_button($output, $template)
    {
        /** @global $cart_vars CartEntity */
        global $cart_vars;

        if (
            'checkout/form-checkout-submit-btn' == $template &&
            ppressPOST_var('ppress_payment_method') == 'paypal' &&
            ! OrderService::init()->is_free_checkout($cart_vars)
        ) {
            $output = '<div class="ppress-checkout-form__place_order_wrap ppress-checkout-submit"><div id="ppress-paypal-button-element"></div></div>';
        }

        return $output;

    }

    public function process_paypal_checkout()
    {
        do_action('wp_ajax_ppress_process_checkout');
        do_action('wp_ajax_nopriv_ppress_process_checkout');
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
        if ( ! empty($transaction_id)) {

            if ($order->exists()) {

                $subdomain       = (OrderMode::TEST === $order->mode) ? 'sandbox.' : '';
                $transaction_url = 'https://' . urlencode($subdomain) . 'paypal.com/activity/payment/' . urlencode($transaction_id);

                $transaction_id = '<a href="' . esc_url($transaction_url) . '" target="_blank">' . esc_html($transaction_id) . '</a>';
            }
        }

        return $transaction_id;
    }

    /**
     * @param $profile_id
     * @param SubscriptionEntity $subscription
     *
     * @return string
     */
    public function link_profile_id($profile_id, $subscription)
    {
        if (empty($profile_id)) return $profile_id;

        $subdomain = ppress_is_test_mode() ? 'sandbox.' : '';
        $order     = OrderFactory::fromId($subscription->parent_order_id);

        if ($order->exists()) {
            $subdomain = (empty($order->mode) || OrderMode::LIVE === $order->mode) ? '' : 'sandbox.';
        }

        $url = 'https://' . $subdomain . 'paypal.com/billing/subscriptions/' . urlencode($profile_id);

        return '<a href="' . esc_url($url) . '" target="_blank">' . esc_html($profile_id) . '</a>';
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

            $api = $this->getHttpclient();

            $api->make_request('v1/billing/subscriptions/' . urlencode($subscription->profile_id) . '/cancel', [
                'reason' => esc_html__('Customer requested cancellation.', 'profilepress-pro')
            ]);

            if ( ! ppress_is_http_code_success($api->last_response_code)) {
                throw new \Exception(sprintf('Unexpected HTTP response code: %d', $api->last_response_code));
            }

            return true;

        } catch (\Exception $e) {

            $subscription->add_note(sprintf(
                __('Failed to cancel subscription in PayPal. Message: %s', 'profilepress-pro'),
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

    /**
     * Get enabled funding sources.
     *
     * @return array
     */
    protected function get_enabled_funding_sources()
    {
        /** @see https://developer.paypal.com/sdk/js/configuration/#enable-funding */
        /** @see https://developer.paypal.com/docs/checkout/apm/ */
        /** @see https://developer.paypal.com/beta/apm-beta/ */
        $enabled_funding = [
            'venmo'    => 'Venmo',
            'paylater' => 'Pay Later',
        ];

        return apply_filters('ppress_paypal_enable_funding', $enabled_funding);
    }

    /**
     * Get disabled funding sources.
     *
     * @return array
     */
    protected function get_disable_funding()
    {
        return apply_filters('ppress_paypal_disable_funding', []);
    }

    public function enqueue_frontend_assets()
    {
        if ( ! function_exists('ppress_is_checkout')) return;

        if ( ! ppress_is_checkout()) return;

        if ( ! PaymentMethods::get_instance()->get_by_id('paypal')->is_enabled()) return;

        $plan = ppress_get_plan(intval(ppressGET_var('plan')));

        if ( ! $plan->exists()) return;

        $sdk_query_args = apply_filters('ppress_paypal_js_sdk_query_args', [
            'client-id' => urlencode($this->get_client_id()),
            'currency'  => urlencode(strtoupper(ppress_get_currency())),
            'intent'    => 'capture',
        ]);

        $enabled_funding_sources = $this->get_enabled_funding_sources();

        if ( ! empty($enabled_funding_sources)) {
            $sdk_query_args['enable-funding'] = implode(',', array_keys($enabled_funding_sources));
        }

        $disabled_funding = $this->get_disable_funding();

        if ( ! empty($disabled_funding)) {
            $sdk_query_args['disable-funding'] = implode(',', array_keys($disabled_funding));
        }

        if ($plan->is_auto_renew()) {
            $sdk_query_args['intent'] = 'subscription';
            $sdk_query_args['vault']  = 'true';
        }

        wp_register_script(
            'ppress-paypal-js-sdk',
            esc_url_raw(add_query_arg(array_filter($sdk_query_args), 'https://www.paypal.com/sdk/js')),
            [],
            null
        );

        add_filter('script_loader_tag', function ($script_tag, $handle, $src) {

            if ('ppress-paypal-js-sdk' == $handle) {

                $data_attributes = sprintf('data-%s="%s"', 'partner-attribution-id', 'ProfilePress_SP_PPCP');

                $script_tag = str_replace(' src', ' ' . $data_attributes . ' src', $script_tag);
            }

            return $script_tag;

        }, 10, 3);

        $suffix = RegisterScripts::asset_suffix();
        wp_register_script(
            'ppress-paypal',
            PROFILEPRESS_PRO_LIBSODIUM_ASSETS_URL . "paypal/paypal-checkout{$suffix}.js",
            array(
                'ppress-paypal-js-sdk',
                'jquery',
                'ppress-frontend-script'
            ),
            PROFILEPRESS_PRO_VERSION_NUMBER,
            true
        );

        wp_enqueue_script('ppress-paypal-js-sdk');

        wp_enqueue_script('ppress-paypal');

        $paypal_script_vars = [
            'style'        => [
                'layout' => 'vertical',
                'shape'  => 'rect',
                'color'  => 'gold',
                'label'  => 'paypal'
            ],
            'defaultError' => esc_html__('An unexpected error occurred. Please refresh the page and try again.', 'profilepress-pro'),
            'intent'       => ! empty($sdk_query_args['intent']) ? $sdk_query_args['intent'] : 'capture'
        ];

        wp_localize_script('ppress-paypal', 'ppressPayPalVars', $paypal_script_vars);
    }

    /**
     * Determines whether or not a product exists.
     *
     * @param string $product_id PayPal product ID.
     *
     * @return bool
     */
    protected function is_paypal_product_exists($product_id)
    {

        try {

            $api = $this->getHttpclient();

            $api->make_request('v1/catalogs/products/' . urlencode($product_id), [], [], 'GET');

            return ppress_is_http_code_success($api->last_response_code);

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param PlanEntity $plan
     *
     * @return mixed
     * @throws \Exception
     */
    protected function get_paypal_product_id($plan)
    {
        $meta_key = '_paypal_product_id';
        if (ppress_is_test_mode()) {
            $meta_key .= '_sandbox';
        }

        $paypal_product_id = $plan->get_meta($meta_key);

        if ( ! empty($paypal_product_id) && $this->is_paypal_product_exists($paypal_product_id)) {
            return $paypal_product_id;
        }

        $product_args = array_filter([
            'name' => substr($plan->get_name(), 0, 127),
            'type' => 'DIGITAL'
        ]);

        $product_args = apply_filters('ppress_paypal_create_product_args', $product_args, $plan);

        $api = $this->getHttpclient();

        $response = $api->make_request('v1/catalogs/products', $product_args);

        if ( ! ppress_is_http_code_success($api->last_response_code)) {
            throw new \Exception(sprintf(
                'Unexpected response code from PayPal: %d. Response: %s',
                $api->last_response_code,
                json_encode($response)
            ));
        }

        if (empty($response->id)) {
            throw new \Exception(sprintf(
                'PayPal product creation response missing product ID. Response: %s',
                json_encode($response)
            ));
        }

        $plan->update_meta($meta_key, $response->id);

        return $response->id;
    }

    /**
     * @param PlanEntity $plan
     *
     * @return array|bool
     */
    protected function subscription_trial_to_paypal_args($plan)
    {
        switch ($plan->free_trial) {
            case SubscriptionTrialPeriod::THREE_DAYS:
                $unit  = 'DAY';
                $count = 3;
                break;
            case SubscriptionTrialPeriod::FIVE_DAYS:
                $unit  = 'DAY';
                $count = 5;
                break;
            case SubscriptionTrialPeriod::ONE_WEEK:
                $unit  = 'WEEK';
                $count = 1;
                break;
            case SubscriptionTrialPeriod::TWO_WEEKS:
                $unit  = 'WEEK';
                $count = 2;
                break;
            case SubscriptionTrialPeriod::THREE_WEEKS:
                $unit  = 'WEEK';
                $count = 3;
                break;
            case SubscriptionTrialPeriod::ONE_MONTH:
                $unit  = 'MONTH';
                $count = 1;
                break;
            default:
                return false;
        }

        return array(
            'interval_unit'  => $unit,
            'interval_count' => $count
        );
    }

    /**
     * @param PlanEntity $plan
     *
     * @return array
     */
    protected function subscription_frequency_to_paypal_args($plan)
    {
        switch ($plan->billing_frequency) {
            case SubscriptionBillingFrequency::DAILY:
                $unit  = 'DAY';
                $count = 1;
                break;
            case SubscriptionBillingFrequency::WEEKLY:
                $unit  = 'WEEK';
                $count = 1;
                break;
            case SubscriptionBillingFrequency::QUARTERLY:
                $unit  = 'MONTH';
                $count = 3;
                break;
            case SubscriptionBillingFrequency::EVERY_6_MONTHS:
                $unit  = 'MONTH';
                $count = 6;
                break;
            case SubscriptionBillingFrequency::YEARLY:
                $unit  = 'YEAR';
                $count = 1;
                break;
            default:
                $unit  = 'MONTH';
                $count = 1;
        }

        return array(
            'interval_unit'  => $unit,
            'interval_count' => $count
        );
    }

    /**
     * Determines whether or not a plan exists.
     *
     * @param string $plan_id
     *
     * @return bool
     *
     */
    protected function paypal_plan_exists($plan_id)
    {
        try {
            $api = $this->getHttpclient();
            $api->make_request('v1/billing/plans/' . urlencode($plan_id), [], [], 'GET');

            return ppress_is_http_code_success($api->last_response_code);

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param PlanEntity $plan
     * @param SubscriptionEntity $subscription
     *
     * @return mixed|null
     * @throws \Exception
     */
    protected function get_paypal_plan_id($plan, $subscription)
    {
        $args = array(
            'product_id'          => $this->get_paypal_product_id($plan),
            'name'                => substr($plan->name, 0, 127),
            'status'              => 'ACTIVE',
            'payment_preferences' => array(
                'auto_bill_outstanding'     => true,
                'setup_fee_failure_action'  => 'CANCEL',
                'payment_failure_threshold' => 3
            ),
        );

        $total_payment_sub = 0;

        $current_sequence = 1;

        if (
            $plan->has_free_trial() ||
            ! Calculator::init($subscription->initial_amount)->isEqualTo($subscription->recurring_amount)
        ) {

            $total_payment_sub = 1;

            $frequency = $plan->has_free_trial() ? $this->subscription_trial_to_paypal_args($plan) : $this->subscription_frequency_to_paypal_args($plan);

            $args['billing_cycles'][] = [
                'frequency'      => $frequency,
                'tenure_type'    => 'TRIAL',
                'sequence'       => $current_sequence,
                'pricing_scheme' => [
                    'fixed_price' => [
                        'currency_code' => ppress_get_currency(),
                        'value'         => Init::process_amount($subscription->initial_amount)
                    ]
                ],
                'total_cycles'   => 1
            ];

            $current_sequence++;
        }

        $args['billing_cycles'][] = array(
            'frequency'      => self::subscription_frequency_to_paypal_args($plan),
            'tenure_type'    => 'REGULAR',
            'sequence'       => $current_sequence,
            'pricing_scheme' => array(
                'fixed_price' => [
                    'currency_code' => ppress_get_currency(),
                    'value'         => Init::process_amount($subscription->recurring_amount)
                ]
            ),
            'total_cycles'   => $plan->total_payments > 0 ? ($plan->total_payments - $total_payment_sub) : 0
        );

        /* Add tax rate. We only send PayPal the percentage, inclusive. */
        if (Calculator::init($subscription->recurring_tax_rate)->isGreaterThanZero()) {
            $args['taxes'] = array(
                'percentage' => (string)$subscription->recurring_tax_rate,
                'inclusive'  => true
            );
        }

        $args = apply_filters('ppress_paypal_create_plan_args', $args, $plan, $subscription);

        $plan_cache_key = ppress_md5(wp_json_encode($args));

        $existing_plans = get_option('ppress_paypal_plans', []);

        if ( ! empty($existing_plans)) {
            $existing_plans = json_decode($existing_plans, true);
        } else {
            $existing_plans = array();
        }

        if (is_array($existing_plans) && ! empty($existing_plans[$plan_cache_key]) && $this->paypal_plan_exists($existing_plans[$plan_cache_key])) {
            return $existing_plans[$plan_cache_key];
        }

        $api = $this->getHttpclient();

        $response = $api->make_request('v1/billing/plans', $args);

        if ( ! ppress_is_http_code_success($api->last_response_code)) {
            throw new \Exception(sprintf(
                'Unexpected HTTP response code: %d; Response: %s',
                $api->last_response_code,
                json_encode($response)
            ));
        }

        if (empty($response->id)) {
            throw new \Exception(sprintf('Missing plan ID from PayPal response. Response: %s', json_encode($response)));
        }

        $existing_plans[$plan_cache_key] = sanitize_text_field($response->id);
        update_option('ppress_paypal_plans', json_encode($existing_plans));

        return $response->id;
    }

    /**
     * @param OrderEntity $order
     * @param PlanEntity $plan
     * @param SubscriptionEntity $subscription
     *
     * @return mixed
     * @throws \Exception
     */
    protected function create_paypal_subscription($order, $plan, $subscription)
    {
        $subscription_args = array(
            'plan_id'             => $this->get_paypal_plan_id($plan, $subscription),
            'custom_id'           => $order->get_order_key(),
            'application_context' => array(
                'shipping_preference' => 'NO_SHIPPING',
                'user_action'         => 'SUBSCRIBE_NOW',
                /*
                 * These are commented out for now because PayPal seems to have odd/strict standards when
                 * validating these and will reject the entire API request for some local URLs. Instead
                 * of breaking local testing, we're omitting these parameters until we can figure out
                 * a possible way to pre-validate the values ourselves.
                 */
                //'return_url'          => edd_get_success_page_uri(),
                //'cancel_url'          => edd_get_failed_transaction_uri(),
            )
        );

        $subscription_args = apply_filters(
            'ppress_paypal_create_subscription_args',
            $subscription_args,
            $order,
            $plan,
            $subscription,
            $this
        );

        $api = $this->getHttpclient();

        $response = $api->make_request('v1/billing/subscriptions', $subscription_args);

        if ( ! ppress_is_http_code_success($api->last_response_code)) {
            throw new \Exception(sprintf(
                'Unexpected HTTP response code: %d; Response: %s',
                $api->last_response_code,
                json_encode($response)
            ));
        }

        if (empty($response->id)) {
            throw new \Exception(sprintf('Missing subscription ID from response. Response: %s', json_encode($response)));
        }

        return $response->id;
    }

    /**
     * Retrieves the subscription details from PayPal directly.
     *
     * @param SubscriptionEntity $subscription
     *
     * @return mixed
     * @throws \Exception
     */
    protected function get_subscription_details($subscription)
    {
        $details = [
            'status'     => '',
            'expiration' => ''
        ];

        if (empty($subscription->profile_id)) {

            throw new \Exception(__('Missing profile ID.', 'profilepress-pro'));
        }

        $api = $this->getHttpclient();

        $response = $api->make_request('v1/billing/subscriptions/' . urlencode($subscription->profile_id), [], [], 'GET');

        if ( ! ppress_is_http_code_success($api->last_response_code)) {
            throw new \Exception(sprintf(
            /* Translators: %d - the HTTP response code */
                __('Unexpected HTTP response code: %d.', 'profilepress-pro'),
                $api->last_response_code
            ));
        }

        if (empty($response->id)) {

            throw new \Exception(sprintf(
            /* Translators: %s - response from PayPal */
                __('PayPal response missing subscription ID. Response: %s', 'profilepress-pro'),
                json_encode($response)
            ));
        }

        $details['status'] = $response->status;

        $details['expiration'] = isset($response->billing_info->next_billing_time) ? date('Y-m-d H:i:s', ppress_strtotime_utc($response->billing_info->next_billing_time)) : '';

        // Let's add all the other details too.
        $response = (array)json_decode(json_encode($response), true);
        $details  = wp_parse_args($details, $response);

        return $details;
    }

    private function limitStringLength($string)
    {
        // Trim the string to remove any leading or trailing whitespace
        $trimmedString = trim($string);

        // Check if the string length exceeds 120 characters
        if (strlen($trimmedString) > 120) {
            // If the string exceeds 120 characters, truncate it to 120 characters
            $trimmedString = substr($trimmedString, 0, 120);
        }

        // Return the trimmed string
        return $trimmedString;
    }

    public function process_payment($order_id, $subscription_id, $customer_id)
    {
        $order        = OrderFactory::fromId($order_id);
        $subscription = SubscriptionFactory::fromId($subscription_id);

        $plan = PlanFactory::fromId($order->plan_id);

        // https://developer.paypal.com/api/rest/reference/currency-codes/
        // https://developer.paypal.com/reference/currency-codes/
        $supported_currencies = apply_filters('ppress_paypal_supported_currencies', [
            'USD',
            'EUR',
            'GBP',
            'AUD',
            'BRL',
            'CAD',
            'SGD',
            'CHF',
            'CNY',
            'CZK',
            'DKK',
            'HKD',
            'HUF',
            'ILS',
            'JPY',
            'MYR',
            'MXN',
            'TWD',
            'NZD',
            'NOK',
            'PHP',
            'RUB',
            'PLN',
            'SEK',
            'THB'
        ]);

        try {

            if ( ! in_array(ppress_get_currency(), $supported_currencies)) {
                throw new \Exception(
                    sprintf('Unsupported paypal currency %s set in ProfilePress', ppress_get_currency()),
                    999888231
                );
            }

            if ($plan->is_auto_renew()) {

                $paypal_subscription_id = $this->create_paypal_subscription($order, $plan, $subscription);

                $subscription->update_profile_id($paypal_subscription_id);

                return (new CheckoutResponse())
                    ->set_is_success(true)
                    ->set_gateway_response($paypal_subscription_id);
            }

            $currency = ppress_get_currency();
            $order_total = (string)Init::process_amount($order->get_total());

            $order_data = [
                'intent'         => 'CAPTURE',
                'purchase_units' => [
                    [
                        'description'          => $this->limitStringLength($plan->name),
                        'reference_id'         => $order->get_order_key(),
                        'items'                => [
                            [
                                'name'        => $this->limitStringLength($plan->name),
                                'quantity'    => '1',
                                'unit_amount' => [
                                    'currency_code' => $currency,
                                    'value'         => $order_total
                                ]
                            ]
                        ],
                        'amount'               => [
                            'currency_code' => $currency,
                            'value'         => $order_total,
                            'breakdown'     => [
                                'item_total' => [
                                    'currency_code' => $currency,
                                    'value'         => $order_total
                                ]
                            ]
                        ],
                        'custom_id'            => $order->get_id(),
                        'invoice_id'           => $order->get_reduced_order_key(),
                        'payment_instructions' => [
                            'disbursement_mode' => 'INSTANT'
                        ]
                    ]
                ],
                'payment_source' => [
                    'paypal' => [
                        'experience_context' => [
                            'shipping_preference' => 'NO_SHIPPING',
                            'user_action'         => 'PAY_NOW',
                            'return_url'          => ppress_plan_checkout_url($plan->get_id()),
                            'cancel_url'          => ppress_get_cancel_url($order->get_order_key())
                        ],
                        'email_address'      => $subscription->get_customer()->get_email(),
                        'name'               => [
                            'given_name' => $subscription->get_customer()->get_first_name(),
                            'surname'    => $subscription->get_customer()->get_last_name()
                        ]
                    ]
                ]
            ];

            $order_data = apply_filters('ppress_paypal_create_order_args', $order_data, $order, $plan, $subscription);

            $api = $this->getHttpclient();

            $response = $api->make_request('v2/checkout/orders', $order_data);

            if ( ! isset($response->id)) {
                throw new \Exception(
                    sprintf('Unexpected response when creating order: %s', json_encode($response)),
                    $api->last_response_code
                );
            }

            return (new CheckoutResponse())
                ->set_is_success(true)
                ->set_gateway_response($response->id);

        } catch (\Exception $e) {

            ppress_log_error(__METHOD__ . '(): ' . $e->getMessage());

            $error_message = defined('W3GUY_LOCAL') || $e->getCode() === 999888231 ? $e->getMessage() : __('An error occurred while communicating with PayPal. Please try again.', 'profilepress-pro');

            return (new CheckoutResponse())
                ->set_is_success(false)
                ->set_error_message($error_message);
        }
    }

    /**
     * Captures the order in PayPal
     *
     * @throws \Exception
     */
    public function capture_order()
    {
        $is_checkout_autologin = ppress_settings_by_key('enable_checkout_autologin') == 'true';

        if ( ! $is_checkout_autologin) {

            $nonce_check = check_ajax_referer('ppress_process_checkout', 'ppress_checkout_nonce', false);

            if (false === $nonce_check) {
                throw new \Exception(esc_html__('Error processing checkout. Nonce failed', 'profilepress-pro'));
            }
        }

        $retry = false;

        try {

            if (empty($_POST['paypal_order_id']) && empty($_POST['paypal_subscription_id'])) {

                throw new \Exception(
                    __('Missing PayPal order or subscription ID on approval.', 'profilepress-pro')
                );
            }

            $api = $this->getHttpclient();

            $is_checkout_subscription = ppressPOST_var('ppress_is_subscription') == 'true';

            if ( ! $is_checkout_subscription && ! empty($_POST['paypal_order_id'])) {

                $response = $api->make_request('v2/checkout/orders/' . urlencode(sanitize_text_field($_POST['paypal_order_id'])) . '/capture');

                if ( ! ppress_is_http_code_success($api->last_response_code)) {
                    /*
                     * If capture failed due to funding source, we want to send a `restart` back to PayPal.
                     * @link https://developer.paypal.com/docs/checkout/standard/customize/handle-funding-failures/
                     */
                    if ( ! empty($response->details) && is_array($response->details)) {
                        if (isset($response->details[0]->issue) && 'INSTRUMENT_DECLINED' === $response->details[0]->issue) {
                            $retry = true;
                        }
                    }

                    throw new \Exception(
                        sprintf('Order capture failure. PayPal response: %s', json_encode($response))
                    );
                }

                if (isset($response->purchase_units) && is_array($response->purchase_units)) {

                    $purchase_unit = $response->purchase_units[0];

                    if ( ! empty($purchase_unit->reference_id)) {

                        $order = OrderRepository::init()->retrieveByOrderKey($purchase_unit->reference_id);

                        $capture = $purchase_unit->payments->captures[0];

                        $transaction_id = isset($capture->id) ? $capture->id : false;

                        if ($order->exists() && false !== $transaction_id) {

                            if ('COMPLETED' === strtoupper($capture->status)) {

                                $order->complete_order($transaction_id);

                                $subscription = SubscriptionRepository::init()->retrieve($order->get_subscription_id());

                                if ($subscription->exists()) {
                                    $subscription->activate_subscription();
                                }

                            } elseif ('DECLINED' === strtoupper($capture->status)) {
                                $order->fail_order();
                            }

                            wp_send_json_success([
                                'redirect_url' => ppress_get_success_url(
                                    $order->get_order_key(),
                                    $order->payment_method
                                )
                            ]);
                        }
                    }
                }

                throw new \Exception(sprintf('Order not found. Data: %s', json_encode($response)));

            } else {

                $paypal_subscription_id = sanitize_text_field($_POST['paypal_subscription_id']);

                $subscription = SubscriptionRepository::init()->retrieveBy([
                    'profile_id' => $paypal_subscription_id
                ]);

                if ( ! empty($subscription)) {

                    $subscription = $subscription[0];

                    $order = OrderFactory::fromId($subscription->parent_order_id);

                    // Get the subscription details in PayPal... let's make sure it's active.
                    $paypal_sub = $this->get_subscription_details($subscription);

                    if (empty($paypal_sub['status']) || 'active' !== strtolower($paypal_sub['status'])) {
                        throw new \Exception(
                            sprintf('Unexpected status in PayPal subscription. Data: %s', json_encode($paypal_sub))
                        );
                    }

                    if ( ! $subscription->is_active()) {

                        $subscription->expiration_date = $paypal_sub['expiration'];

                        if ($subscription->has_trial()) {
                            $subscription->enable_subscription_trial();
                            if ( ! $order->is_completed()) $order->complete_order();
                        } else {
                            $subscription->activate_subscription();
                        }
                    }

                    wp_send_json_success([
                        'redirect_url' => ppress_get_success_url(
                            $order->get_order_key(),
                            $order->payment_method
                        )
                    ]);
                }

                throw new \Exception(sprintf('Subscription ID:%s not found.', $paypal_subscription_id));
            }

        } catch (\Exception $e) {

            ppress_log_error(__METHOD__ . '(): ' . $e->getMessage());

            $error_message = defined('W3GUY_LOCAL') ? $e->getMessage() : __('An unexpected error occurred. Please try again.', 'profilepress-pro');

            wp_send_json_error([
                'message' => $error_message,
                'retry'   => $retry === true
            ]);
        }
    }

    public function process_refund($order_id, $amount = null, $reason = '')
    {
        try {

            $order = OrderRepository::init()->retrieve($order_id);

            $api      = $this->getHttpclient();
            $response = $api->make_request(
                'v2/payments/captures/' . $order->transaction_id . '/refund',
                ['invoice_id' => $order->get_reduced_order_key(), 'custom_id' => $order->get_id()]
            );

            switch ($response->status) {
                case 'COMPLETED':
                    return true;
                case 'PENDING':
                    $order->add_note(esc_html__('Refund request is pending', 'profilepress-pro'));
                    break;
                default:
                    $order->add_note(esc_html__('Refund request failed', 'profilepress-pro'));
                    break;
            }

            return false;

        } catch (\Exception $e) {
            ppress_log_error($e->getMessage() . '; OrderID:' . $order_id);

            return false;
        }
    }

    public function process_webhook()
    {
        try {

            $event = json_decode(file_get_contents('php://input'));

            WebhookHelpers::validate_webhook($this->getHttpclient(), $event, $this->get_webhook_id());

            $webhooks = WebhookHelpers::valid_events();

            if (in_array($event->event_type, array_keys($webhooks))) {
                /** @var WebhookHandlerInterface $callable */
                $callable = $webhooks[$event->event_type];

                call_user_func([$callable, 'handle'], $event);

                do_action('ppress_paypal_webhook_event', $event->event_type, $event);
            }

            http_response_code(200);

        } catch (\Exception $e) {
            ppress_log_error($e->getMessage());

            return false;
        }
    }
}