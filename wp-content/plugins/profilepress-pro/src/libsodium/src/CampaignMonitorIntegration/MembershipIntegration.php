<?php

namespace ProfilePress\Libsodium\CampaignMonitorIntegration;

use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Plan\PlanFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Libsodium\CampaignMonitorIntegration\Admin\SettingsPage;

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

        add_action('ppress_campaign_monitor_sync', [$this, 'membership_sync']);
    }

    public function plans_admin_settings($settings)
    {
        $settings['campaign_monitor'] = [
            'tab_title' => esc_html__('Campaign Monitor', 'profilepress-pro'),
            [
                'id'          => 'campaign_monitor_list',
                'type'        => 'select',
                'options'     => SettingsPage::email_list_select_options(),
                'label'       => esc_html__('Select List', 'profilepress-pro'),
                'description' => esc_html__('Select the list to add users that purchase or subscribe to this plan.', 'profilepress-pro'),
                'priority'    => 5
            ]
        ];

        return $settings;
    }

    protected function get_list_by_id($id)
    {
        return PROFILEPRESS_sql::get_meta_value($id, 'cm_email_list');
    }

    public function membership_sync()
    {
        if ( ! apply_filters('ppress_campaign_monitor_membership_sync_is_enabled', true)) return;

        add_action('ppress_subscription_status_updated', function ($status, $old_status, SubscriptionEntity $sub) {

            $ppress_list_id = PlanFactory::fromId($sub->get_plan_id())->get_plan_extras('campaign_monitor_list');

            $customer = CustomerFactory::fromId($sub->customer_id);

            if (empty($ppress_list_id)) return;

            $ppress_list = $this->get_list_by_id($ppress_list_id);

            if ( ! is_array($ppress_list) || empty($ppress_list)) return;

            $cm_list_id = ppress_var($ppress_list, 'cm_email_list_select');

            if (empty($cm_list_id)) return;

            switch ($status) {

                case SubscriptionStatus::ACTIVE:
                case SubscriptionStatus::COMPLETED:
                case SubscriptionStatus::TRIALLING:
                    if ( ! $this->initInstance->is_user_subscribed($customer->get_wp_user()->user_email, $cm_list_id)) {
                        $this->initInstance->add_update_user_to_email_list($ppress_list_id, $customer->get_wp_user());
                    }
                    break;
                case SubscriptionStatus::CANCELLED:
                    if (($sub->is_lifetime() || ! $sub->is_active()) && $this->initInstance->is_user_subscribed($customer->get_wp_user()->user_email, $cm_list_id)) {
                        $this->initInstance->unsubscribe_user($customer->get_wp_user()->user_email, $cm_list_id);
                    }
                    break;

                case SubscriptionStatus::PENDING:
                case SubscriptionStatus::EXPIRED:
                    if ($this->initInstance->is_user_subscribed($customer->get_wp_user()->user_email, $cm_list_id)) {
                        $this->initInstance->unsubscribe_user($customer->get_wp_user()->user_email, $cm_list_id);
                    }
                    break;
            }
        }, 10, 3);
    }

    protected function is_checkbox_optin_enabled()
    {
        return ppress_settings_by_key('cm_checkout_checkbox', false) === 'true';
    }

    public function save_checkbox_state($order_id)
    {
        if ($this->is_checkbox_optin_enabled()) {

            OrderFactory::fromId($order_id)->update_meta(
                'ppress_campaign_monitor',
                ppressPOST_var('ppress-campaign-monitor') == 'on' ? 'yes' : 'no'
            );
        }
    }

    public function checkout_subscription_checkbox()
    {
        if ( ! $this->is_checkbox_optin_enabled()) return;

        $label = ppress_settings_by_key('cm_checkout_checkbox_label', esc_html__('Subscribe to our newsletter', 'profilepress-pro'));
        ?>
        <div class="ppress-checkout-form__before_button_wrap">

            <div class="ppress-checkout-form__before_button__checkbox_wrap">
                <label class="ppress-checkout-form__before_button__checkbox__label">
                    <input id="ppress-campaign-monitor" name="ppress-campaign-monitor" type="checkbox" class="ppress-checkout-field__input">
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

            $ppress_list_id = $order->get_plan()->get_plan_extras('campaign_monitor_list');

            if (intval($ppress_list_id) <= 0) return;

            if ($this->is_checkbox_optin_enabled() && $order->get_meta('ppress_campaign_monitor') !== 'yes') return;

            $this->initInstance->add_update_user_to_email_list(
                $ppress_list_id,
                $order->get_customer()->get_wp_user()
            );
        }
    }
}