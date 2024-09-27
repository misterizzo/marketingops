<?php

namespace ProfilePress\Libsodium\WooCommerceIntegration;

use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Plan\PlanFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity as SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\Membership\Repositories\PlanRepository;
use WC_Coupon;

class WooMemberships
{
    public function __construct()
    {
        add_filter('ppress_admin_membership_plan_metabox_settings', [$this, 'plan_edit_screen']);

        add_action('woocommerce_order_status_changed', function ($order_id, $old_status, $new_status) {
            $this->product_order_hook_callback($order_id, $new_status);
        }, 1, 3);

        add_action('woocommerce_order_status_completed', function ($order_id) {
            $this->product_order_hook_callback($order_id, 'completed');
        }, 1);

        add_action('woocommerce_order_status_processing', function ($order_id) {
            $this->product_order_hook_callback($order_id, 'processing');
        }, 1);

        add_action('woocommerce_subscription_status_updated', function ($subscription, $new_status, $old_status) {
            $this->product_subscription_hook_callback($subscription, $new_status);
        }, 99, 3);

        add_action('woocommerce_add_to_cart', [$this, 'auto_apply_discount']);
        add_filter('woocommerce_cart_totals_coupon_label', [$this, 'customize_coupon_label'], 99, 2);
        add_filter('woocommerce_coupon_message', [$this, 'customize_coupon_message'], 99, 3);
    }

    protected function get_current_member_coupon_code()
    {
        $current_user = wp_get_current_user();

        if ($current_user instanceof \WP_User && $current_user->exists()) {

            $cache_key = sprintf('ppress_woo_discount_%s', $current_user->ID);

            $coupon_code = wp_cache_get($cache_key);

            if (false === $coupon_code) {

                $active_subs = CustomerFactory::fromUserId($current_user->ID)->get_active_subscriptions();

                if (is_array($active_subs) && ! empty($active_subs)) {

                    foreach ($active_subs as $active_sub) {

                        $coupon_code = $active_sub->get_plan()->get_plan_extras('woocommerce_coupons');

                        if ( ! empty($coupon_code)) {

                            wp_cache_set($cache_key, $coupon_code, '', MINUTE_IN_SECONDS);

                            return $coupon_code;
                        }
                    }
                }
            }

            return $coupon_code;
        }

        return false;
    }

    public function auto_apply_discount()
    {
        $cart = WC()->cart;

        if (count($cart->get_applied_coupons()) > 0) return;

        $coupon_code = $this->get_current_member_coupon_code();

        if ( ! empty($coupon_code)) {

            try {

                $coupon = new WC_Coupon($coupon_code);

                $discounts = new \WC_Discounts($cart);

                $is_valid = $discounts->is_coupon_valid($coupon);

                if (true === $is_valid) {

                    if ( ! $cart->has_discount($coupon_code)) {

                        $cart->apply_coupon($coupon_code);
                    }
                }

            } catch (\Exception $e) {
            }
        }
    }

    /**
     * @param string $label
     * @param WC_Coupon $coupon
     *
     * @return string
     */
    public function customize_coupon_label($label, $coupon)
    {
        $coupon_code = $this->get_current_member_coupon_code();

        if ( ! empty($coupon_code) && $coupon->get_code() == $coupon_code) {
            $label = apply_filters('ppress_woocommerce_coupon_label', esc_html__('Membership discount', 'profilepress-pro'), $coupon_code, $coupon);
        }

        return $label;
    }

    /**
     * @param string $message
     * @param int $message_code
     * @param WC_Coupon $coupon
     *
     * @return string
     */
    public function customize_coupon_message($message, $message_code, $coupon)
    {
        $coupon_code = $this->get_current_member_coupon_code();

        if ( ! empty($coupon_code) && $coupon->get_code() == $coupon_code) {
            $message = apply_filters('ppress_woocommerce_coupon_message', esc_html__('Membership coupon applied', 'profilepress-pro'), $coupon_code, $coupon);
        }

        return $message;
    }

    /**
     * @return \Generator
     */
    private function woo_products_generator()
    {
        $offset     = 0;
        $batch_size = 100;

        while (true) {
            $args = [
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'posts_per_page' => $batch_size,
                'offset'         => $offset,
                'orderby'        => 'post_title',
                'order'          => 'ASC',
                'fields'         => 'ids'
            ];

            $product_ids = get_posts($args);

            if (empty($product_ids)) break;

            foreach ($product_ids as $product_id) {
                $product = wc_get_product($product_id);
                if ($product) {
                    yield $product_id => $product->get_name();
                }
                // Clean up to free memory
                unset($product);
            }

            $offset += $batch_size;

            // Optional: Add a small delay to reduce server load
            usleep(10000); // 10ms delay
        }
    }

    /**
     * @return \Generator
     */
    private function woo_coupon_generator()
    {
        $offset     = 0;
        $batch_size = 100;

        yield '' => '&mdash;&mdash;&mdash;';

        while (true) {

            $args = [
                'post_type'      => 'shop_coupon',
                'post_status'    => 'publish',
                'posts_per_page' => $batch_size,
                'offset'         => $offset,
                'fields'         => 'ids'
            ];

            $coupon_ids = get_posts($args);

            if (empty($coupon_ids)) break;

            foreach ($coupon_ids as $coupon_id) {
                $coupon = new WC_Coupon($coupon_id);

                yield $coupon->get_code() => $coupon->get_code();

                // Clean up to free memory
                unset($coupon);
            }

            $offset += $batch_size;

            // Optional: Add a small delay to reduce server load
            usleep(10000); // 10ms delay
        }
    }

    /**
     * @param $settings
     *
     * @return mixed
     */
    public function plan_edit_screen($settings)
    {
        $settings[EM::WOOCOMMERCE] = [
            'tab_title' => esc_html__('WooCommerce', 'profilepress-pro'),
            [
                'id'          => 'woocommerce_products',
                'type'        => 'select2',
                'options'     => $this->woo_products_generator(),
                'label'       => esc_html__('Select Products', 'profilepress-pro'),
                'description' => esc_attr__('Select the WooCommerce products that will subscribe users to this plan when purchased.', 'profilepress-pro'),
                'priority'    => 5
            ],
            [
                'id'          => 'woocommerce_coupons',
                'type'        => 'select',
                'options'     => $this->woo_coupon_generator(),
                'label'       => esc_html__('Select Coupon', 'profilepress-pro'),
                'description' => esc_attr__('Select the WooCommerce coupon to automatically apply to orders of users with active subscription to this plan.', 'profilepress-pro'),
                'priority'    => 10
            ]
        ];

        return $settings;
    }

    public function product_subscription_hook_callback($subscription, $status)
    {
        /** @var \WC_Subscription $subscription */
        if ( ! is_a($subscription, '\WC_Subscription')) return;

        static $cache = [];

        $cache_key = sprintf('%s:%s', $subscription->get_id(), $status);

        if (isset($cache[$cache_key])) return;

        $cache[$cache_key] = true;

        $sub_products = $subscription->get_items();

        if ( ! is_array($sub_products) || empty($sub_products)) return;

        $user_id = $subscription->get_user_id();

        try {

            if ('active' == $status) {
                $customer_id = $this->get_ppress_customer_id($user_id, $subscription->get_billing_email());
            }

            foreach ($sub_products as $sub_product) {

                $product_id = $sub_product->get_product_id();

                if ( ! $this->is_subscription_product($product_id)) continue;

                $plan_ids = $this->find_ppress_membership_plans($sub_product->get_product_id());

                if (empty($plan_ids)) continue;

                foreach ($plan_ids as $plan_id) {

                    if ('active' == $status) {
                        /** @var SubscriptionEntity $ppress_subscription */
                        $ppress_subscription = CustomerFactory::fromId($customer_id)->has_active_subscription($plan_id, true);

                        if ( ! $ppress_subscription) {

                            $response = ppress_subscribe_user_to_plan($plan_id, $customer_id);

                            if ( ! empty($response['subscription_id'])) {

                                $original_order    = $subscription->get_parent();
                                $next_payment_date = $subscription->get_time('next_payment');

                                $sub = SubscriptionFactory::fromId($response['subscription_id']);

                                if ($original_order) $sub->initial_amount = $original_order->get_total();

                                if ($next_payment_date) $sub->recurring_amount = $subscription->get_total();

                                $sub->save();
                            }

                        } else {

                            $ppress_subscription->status = SubscriptionStatus::ACTIVE;

                            $woo_expiration_date = $subscription->get_date('next_payment');

                            if ( ! empty($woo_expiration_date)) $ppress_subscription->expiration_date = $woo_expiration_date;

                            $ppress_subscription->save();
                        }
                    }

                    if (in_array($status, ['expired', 'cancelled'])) {

                        /** @var SubscriptionEntity $ppress_subscription */
                        $ppress_subscription = CustomerFactory::fromUserId($user_id)->has_active_subscription($plan_id, true);

                        if ('expired' == $status) $ppress_subscription->expire();

                        if ('cancelled' == $status) $ppress_subscription->cancel();
                    }
                }
            }

        } catch (\Exception $e) {
            ppress_log_error('WooCommerce ProfilePress Integration: ' . $e->getMessage());
        }
    }

    public function product_order_hook_callback($order_id, $order_status)
    {
        static $cache = [];

        $cache_key = sprintf('%s:%s', $order_id, $order_status);

        if (isset($cache[$cache_key])) return;

        $cache[$cache_key] = true;

        $order = wc_get_order($order_id);

        $user_id = $order->get_user_id('edit');

        $product_items = $order->get_items();

        foreach ($product_items as $product_item) {

            $product_id = $product_item->get_product_id();

            if ($this->is_subscription_product($product_id)) continue;

            $plan_ids = $this->find_ppress_membership_plans($product_id);

            if (empty($plan_ids)) continue;

            try {

                if (in_array($order_status, ['completed', 'processing'])) {
                    $customer_id = $this->get_ppress_customer_id($user_id, $order->get_billing_email());
                }

                foreach ($plan_ids as $plan_id) {

                    if (in_array($order_status, ['completed', 'processing'])) {

                        $ppress_subscription = CustomerFactory::fromId($customer_id)->has_active_subscription($plan_id, true);

                        if ( ! $ppress_subscription) {

                            $response = ppress_subscribe_user_to_plan($plan_id, $customer_id);

                            if ( ! empty($response['subscription_id'])) {

                                $sub                 = SubscriptionFactory::fromId($response['subscription_id']);
                                $sub->initial_amount = $order->get_total();
                                $sub->save();
                            }

                        } elseif ( ! $ppress_subscription->is_active()) {

                            $ppress_subscription->update_status(SubscriptionStatus::ACTIVE);

                        }
                    }

                    if (in_array($order_status, ['cancelled', 'refunded'])) {

                        $ppress_subscription = CustomerFactory::fromUserId($user_id)->has_active_subscription($plan_id, true);

                        if ($ppress_subscription) $ppress_subscription->expire();
                    }
                }

            } catch (\Exception $e) {
                ppress_log_error('WooCommerce ProfilePress Integration: ' . $e->getMessage());
            }
        }
    }

    private function find_ppress_membership_plans($product_id)
    {
        $product_id = (int)$product_id;

        $limit = 100;
        $page  = 1;

        $bucket = [];

        do {

            $plans = PlanRepository::init()->retrieveAll($limit, $page);

            if ( ! empty($plans)) {

                foreach ($plans as $plan) {

                    if ($plan->is_active()) {

                        $entities = $plan->get_plan_extras('woocommerce_products');

                        $entities = is_array($entities) ? array_map('absint', $entities) : [];

                        if (in_array($product_id, $entities, true)) {
                            $bucket[] = $plan->get_id();
                        }
                    }
                }
            }

            $page++;

        } while ( ! empty($plans));

        return $bucket;
    }

    /**
     * @throws \Exception
     */
    protected function get_ppress_customer_id($user_id, $user_email)
    {
        if (get_userdata($user_id) === false) {

            $user_id = wp_create_user(
                $user_email,
                wp_generate_password(),
                $user_email,
            );

            if (is_wp_error($user_id)) {

                throw new \Exception(
                    $user_id->get_error_message(),
                    $user_id->get_error_code()
                );
            }
        }

        return ppress_create_customer($user_id);
    }

    protected function is_subscription_product($product_id)
    {
        return class_exists('\WC_Subscriptions_Product') && \WC_Subscriptions_Product::is_subscription($product_id);
    }

    /**
     * @return self|void
     */
    public static function get_instance()
    {
        static $instance;

        if ( ! isset($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}
