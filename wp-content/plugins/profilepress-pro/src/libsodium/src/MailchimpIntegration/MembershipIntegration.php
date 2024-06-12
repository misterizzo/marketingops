<?php

namespace ProfilePress\Libsodium\MailchimpIntegration;

use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Plan\PlanFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Libsodium\MailchimpIntegration\Admin\SettingsPage;

class MembershipIntegration
{
    protected $initInstance;

    /**
     * @param Init $init
     */
    public function __construct($init)
    {
        $this->initInstance = $init;

        add_filter('ppress_admin_membership_plan_metabox_settings', [$this, 'plans_admin_settings']);

        add_action('ppress_checkout_before_submit_button', [$this, 'checkout_subscription_checkbox']);

        add_action('ppress_process_checkout_after_order_subscription_creation', [$this, 'save_checkbox_state']);

        add_action('ppress_order_completed', [$this, 'subscribe_after_order_complete']);
        add_action('ppress_subscription_activated', [$this, 'subscribe_after_subscription_activation']);
        add_action('ppress_subscription_enabled_trial', [$this, 'subscribe_after_subscription_activation']);

        add_action('ppress_mailchimp_sync', [$this, 'membership_sync']);
    }

    public function plans_admin_settings($settings)
    {
        $settings['mailchimp'] = [
            'tab_title' => esc_html__('Mailchimp', 'profilepress-pro'),
            [
                'id'          => 'mailchimp_audience',
                'type'        => 'select',
                'options'     => SettingsPage::audience_select_options(),
                'label'       => esc_html__('Select Audience', 'profilepress-pro'),
                'description' => esc_html__('Select the audience to add users that purchase or subscribe to this plan.', 'profilepress-pro'),
                'priority'    => 5
            ]
        ];

        return $settings;
    }

    public function membership_sync()
    {
        if ( ! apply_filters('ppress_mailchimp_membership_sync_is_enabled', true)) return;

        add_action('ppress_subscription_status_updated', function ($status, $old_status, SubscriptionEntity $sub) {

            $ppress_audience_id = PlanFactory::fromId($sub->get_plan_id())->get_plan_extras('mailchimp_audience');

            $customer = CustomerFactory::fromId($sub->customer_id);

            if (empty($ppress_audience_id)) return;

            $ppress_audience = $this->initInstance->get_audience_by_id($ppress_audience_id);

            $ppress_audience_tags = ppress_var($ppress_audience, 'mc_audience_default_tags', [], true);

            if ( ! is_array($ppress_audience) || empty($ppress_audience)) return;

            $mc_list_id = ppress_var($ppress_audience, 'mc_audience_select');

            if (empty($mc_list_id)) return;

            switch ($status) {

                case SubscriptionStatus::ACTIVE:
                case SubscriptionStatus::COMPLETED:
                case SubscriptionStatus::TRIALLING:
                    $this->mc_sync_add_user($ppress_audience_id, $ppress_audience_tags, $customer->get_wp_user(), $mc_list_id);
                    break;
                case SubscriptionStatus::CANCELLED:
                    // $sub->is_active() is necessary to remove user when sub hasn't expired but last order was refunded.
                    if ($sub->is_lifetime() || ! $sub->is_active()) {
                        $this->mc_sync_remove_user($customer->get_email(), $mc_list_id, $ppress_audience_tags);
                    }
                    break;

                case SubscriptionStatus::PENDING:
                case SubscriptionStatus::EXPIRED:
                    $this->mc_sync_remove_user($customer->get_email(), $mc_list_id, $ppress_audience_tags);
                    break;
            }
        }, 10, 3);
    }

    protected function is_checkbox_optin_enabled()
    {
        return ppress_settings_by_key('mc_checkout_checkbox', false) === 'true';
    }

    private function is_tagging_segmentation()
    {
        return apply_filters('ppress_mailchimp_is_tagging_segmentation', false);
    }

    protected function mc_sync_add_user($ppress_audience_id, $ppress_audience_tags, \WP_User $customer_wp_user, $mc_list_id)
    {
        if ($this->is_tagging_segmentation()) {

            return $this->initInstance->add_remove_user_tags(
                $customer_wp_user->user_email,
                $mc_list_id,
                $ppress_audience_tags
            );
        }


        if ( ! $this->initInstance->is_user_subscribed($customer_wp_user->user_email, $mc_list_id)) {
            return $this->initInstance->add_update_user_to_audience($ppress_audience_id, $customer_wp_user);
        }

        return false;
    }

    protected function mc_sync_remove_user($customer_email, $mc_list_id, $ppress_audience_tags)
    {
        if ($this->is_tagging_segmentation()) {

            return $this->initInstance->add_remove_user_tags(
                $customer_email,
                $mc_list_id,
                $ppress_audience_tags,
                true
            );
        }

        if ($this->initInstance->is_user_subscribed($customer_email, $mc_list_id)) {
            return $this->initInstance->unsubscribe_user($customer_email, $mc_list_id);
        }

        return false;
    }

    public function save_checkbox_state($order_id)
    {
        if ($this->is_checkbox_optin_enabled()) {

            OrderFactory::fromId($order_id)->update_meta(
                'ppress_mailchimp',
                ppressPOST_var('ppress-mailchimp') == 'on' ? 'yes' : 'no'
            );
        }
    }

    public function checkout_subscription_checkbox()
    {
        if ( ! $this->is_checkbox_optin_enabled()) return;

        $label = ppress_settings_by_key('mc_checkout_checkbox_label', esc_html__('Subscribe to our newsletter', 'profilepress-pro'));
        ?>
        <div class="ppress-checkout-form__before_button_wrap">

            <div class="ppress-checkout-form__before_button__checkbox_wrap">
                <label class="ppress-checkout-form__before_button__checkbox__label">
                    <input id="ppress-mailchimp" name="ppress-mailchimp" type="checkbox" class="ppress-checkout-field__input">
                    <?= $label ?>
                </label>
            </div>
        </div>
        <?php
    }

    /**
     * @param SubscriptionEntity $sub
     *
     * @return void
     */
    public function subscribe_after_subscription_activation($sub)
    {
        $this->subscribe_after_order_complete(OrderFactory::fromId($sub->parent_order_id));
    }

    /**
     * @param OrderEntity $order
     *
     * @return void
     */
    public function subscribe_after_order_complete($order)
    {
        static $flag = false;

        if (false === $flag) {

            $flag = true;

            $ppress_audience_id = $order->get_plan()->get_plan_extras('mailchimp_audience');

            if (intval($ppress_audience_id) <= 0) return;

            if ($this->is_checkbox_optin_enabled() && $order->get_meta('ppress_mailchimp') !== 'yes') return;

            $this->initInstance->add_update_user_to_audience(
                $ppress_audience_id,
                $order->get_customer()->get_wp_user()
            );
        }
    }
}