<?php

namespace ProfilePress\Libsodium\Receipt;

use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Services\Calculator;

class Init
{
    public static $instance_flag = false;

    public function __construct()
    {
        Settings::get_instance();
        Generator::get_instance();

        add_filter('ppress_orders_table_column_order_actions', [$this, 'admin_orders_listing_table'], 10, 2);

        add_action('ppress_order_admin_page_sidebar', [$this, 'admin_order_metabox']);

        add_filter('ppress_settings_custom_sanitize', [$this, 'validate_fields']);

        add_action('ppress_myaccount_order_header_actions', [$this, 'myaccount_orders_view']);
        add_action('ppress_myaccount_view_order_details_order_row', [$this, 'myaccount_single_order_receipt_link']);

        add_filter('ppress_email_order_placeholder_definitions', [$this, 'mail_tag_definition']);
        add_filter('ppress_order_placeholders_values', [$this, 'mail_tag_transform'], 10, 2);
    }

    public function validate_fields($rules)
    {
        $rules['receipt_additional_info'] = function ($val) {
            return wp_kses_post($val);
        };

        return $rules;
    }

    public function mail_tag_definition($placeholders)
    {
        $placeholders['{{receipt_url}}'] = esc_html__("URL to view order receipt or invoice.", 'profilepress-pro');

        return $placeholders;
    }

    public function mail_tag_transform($tags, $order)
    {
        $tags['{{receipt_url}}'] = self::get_receipt_url($order);

        return $tags;
    }

    public function myaccount_single_order_receipt_link(OrderEntity $order)
    {
        if ( ! $this->is_free_order_disabled($order) && $order->is_completed()) {

            printf(
                '&nbsp;&nbsp;<a target="_blank" href="%s">%s &rarr;</a>',
                self::get_receipt_url($order),
                self::get_view_receipt_label()
            );
        }
    }

    public function myaccount_orders_view(OrderEntity $order)
    {
        if ($order->is_completed() && ! $this->is_free_order_disabled($order)) {

            printf(
                '<a target="_blank" href="%s" class="ppress-my-account-order-sub-header--actions--link"><span>%s</span></a>',
                self::get_receipt_url($order),
                self::get_view_receipt_label()
            );
        }
    }

    public function admin_orders_listing_table($actions, OrderEntity $order)
    {
        if ($order->is_completed() && ! $this->is_free_order_disabled($order)) {
            $actions['view_receipt'] = sprintf('<a href="%s">%s</a>', self::get_receipt_url($order), self::get_view_receipt_label());
        }

        return $actions;
    }

    public function admin_order_metabox(OrderEntity $order_data)
    {
        if ($order_data->is_completed() && ! $this->is_free_order_disabled($order_data)) {
            add_meta_box(
                'ppress-membership-order-receipt',
                __('Invoice / Receipt', 'profilepress-pro'),
                function () use ($order_data) {
                    ?>
                    <div class="ppress-order-receipt-wrap">
                        <p>
                            <a target="_blank" class="button" href="<?= esc_url(self::get_receipt_url($order_data)) ?>">
                                <?php echo self::get_view_receipt_label() ?>
                            </a>
                        </p>
                    </div>
                    <?php
                },
                'ppmembershiporder',
                'sidebar'
            );
        }
    }

    /**
     * Returns true if Disable receipt for free order is enabled and order is free or $0.
     *
     * @param OrderEntity $order_data
     *
     * @return bool
     */
    private function is_free_order_disabled(OrderEntity $order_data)
    {
        return ppress_settings_by_key('receipt_disable_free_order') == 'true' &&
               Calculator::init($order_data->get_total())->isNegativeOrZero();
    }

    public static function get_view_receipt_label()
    {
        return ppress_settings_by_key('receipt_view_button_label', esc_html__('View Receipt', 'profilepress-pro'), true);
    }

    public static function get_receipt_url(OrderEntity $order)
    {
        $args = [
            'ppress_action' => 'view_receipt',
            'order_key'     => urlencode($order->get_order_key()),
            'receipt'       => urlencode(md5($order->id . $order->get_order_key() . $order->get_customer_email())),
        ];

        return add_query_arg($args, trailingslashit(home_url()));
    }

    /**
     * @return self|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::RECEIPT')) return;

        if ( ! EM::is_enabled(EM::RECEIPT)) return;

        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}