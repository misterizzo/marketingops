<?php

namespace ProfilePress\Core\ShortcodeParser;

use ProfilePress\Core\Membership\Controllers\CheckoutSessionData;
use ProfilePress\Core\Membership\Models\Coupon\CouponFactory;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Group\GroupFactory;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Order\OrderType;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;

class MembershipShortcodes
{
    public function __construct()
    {
        add_shortcode('profilepress-checkout', [$this, 'checkout_page_wrapper']);
        add_shortcode('profilepress-receipt', [$this, 'success_page']);

        add_filter('the_content', [$this, 'filter_success_page_content'], 99999);

        // Avada theme incompatibility fix
        add_action('awb_remove_third_party_the_content_changes', function () {
            remove_filter('the_content', [$this, 'filter_success_page_content'], 99999);
        });

        add_action('awb_readd_third_party_the_content_changes', function () {
            add_filter('the_content', [$this, 'filter_success_page_content'], 99999);
        });
    }
    function filter_success_page_content($content)
    {
        if (isset($_GET['order_key'], $_GET['payment_method']) && ppress_is_success_page()) {
            $order = OrderFactory::fromOrderKey(sanitize_key($_GET['order_key']));
            if ($order->exists() && $order->is_pending()) {
                ob_start();
                ppress_render_view('order-processing', [
                    'order_success_page' => ppress_get_success_url($order->order_key)
                ]);

                $content = ob_get_clean();
            }
        }

        return $content;
    }

    public function checkout_page_wrapper()
    {
        if (ppress_is_redirect_to_referrer_after_checkout()) {

            $referrer = wp_get_referer();

            if ( ! empty($referrer)) {
                ppress_session()->set('ppress_checkout_referrer', esc_url_raw($referrer));
            }
        }

        ob_start();

        echo '<div class="ppress-checkout__form">';
        $this->checkout_page();
        echo '</div>';

        return ob_get_clean();
    }

    public function checkout_page()
    {
        if (
            ( ! isset($_GET['plan']) || ! is_numeric($_GET['plan'])) &&
            ( ! isset($_GET['group']) || ! is_numeric($_GET['group']))
        ) {

            do_action('ppress_membership_checkout_empty_cart');

            echo '<p>';
            printf(
                __('Your cart is currently empty. Click <a href="%s">here</a> to get started.', 'wp-user-avatar'),
                /** @todo add setting to set url to redirect to when cart is empty */
                apply_filters('ppress_membership_checkout_empty_cart_url', home_url())
            );
            echo '</p>';

            return;
        }

        $isGroupCheckout = ! empty($_GET['group']);

        $isChangePlanCheckout = ! empty($_GET['change_plan']);

        if ($isChangePlanCheckout) {

            if ( ! is_user_logged_in()) {
                echo '<p>';
                esc_html_e('You must be logged in to switch to another plan.', 'wp-user-avatar');
                echo '</p>';

                return;
            }

            $sub = SubscriptionFactory::fromId(absint($_GET['change_plan']));

            if ( ! $sub->exists() || ! ppress_get_plan($sub->plan_id)->get_group_id()) {

                echo '<p>';
                esc_html_e('You can not switch to another plan because this plan does not belong to any group.', 'wp-user-avatar');
                echo '</p>';

                return;
            }
        }

        if ($isGroupCheckout) {

            $group = GroupFactory::fromId(absint($_GET['group']));

            if ( ! $group->exists()) {
                esc_html_e('Invalid plan group.', 'wp-user-avatar');

                return;
            }

            if (empty($group->get_plan_ids())) {
                esc_html_e('Error: group has no membership plans.', 'wp-user-avatar');

                return;
            }
        }

        $planObj  = ppress_get_plan(absint(ppressGET_var('plan', 0)));
        $groupObj = GroupFactory::fromId(absint(ppressGET_var('group', 0)));

        if (
            is_user_logged_in() &&
            ! $isGroupCheckout &&
            ! $isChangePlanCheckout &&
            CustomerFactory::fromUserId(get_current_user_id())->has_active_subscription($planObj->id)) {
            echo '<p>';
            printf(
                esc_html__('You have an active subscription to this plan. Please go to %syour account%s to manage your subscription.', 'wp-user-avatar'),
                '<a href="' . ppress_my_account_url() . '">', '</a>'
            );
            echo '</p>';

            return;
        }

        if ( ! $planObj->is_active()) {
            do_action('ppress_membership_checkout_invalid_plan');
            echo '<p>' . esc_html__('Invalid membership plan.', 'wp-user-avatar') . '</p>';

            return;
        }

        add_filter('ppress_logout_url_enable_redirect_get_query', '__return_true');

        if ( ! empty($_GET['coupon'])) {

            $coupon = CouponFactory::fromCode(sanitize_text_field($_GET['coupon']));

            $order_type = CheckoutSessionData::get_order_type($planObj->get_id());

            if ( ! $order_type) $order_type = OrderType::NEW_ORDER;

            if ($coupon->exists() && $coupon->is_valid($planObj->get_id(), $order_type)) {

                ppress_session()->set(CheckoutSessionData::COUPON_CODE, [
                    'plan_id'     => $planObj->id,
                    'coupon_code' => $coupon->code,
                ]);
            }
        }

        ppress_render_view('checkout/form-checkout', [
            'planObj'         => $planObj,
            'groupObj'        => $groupObj,
            'changePlanSubId' => absint(ppressGET_var('change_plan', 0)),
        ]);
    }

    public function success_page()
    {
        ob_start();
        require apply_filters('ppress_order_receipt_template', dirname(__FILE__) . '/MyAccount/view-order.tmpl.php');

        return ob_get_clean();
    }

    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}
