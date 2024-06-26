<?php

namespace ProfilePress\Core\Membership\Emails;

use ProfilePress\Core\Membership\Models\Order\OrderEntity;

class RenewalOrderReceipt extends AbstractMembershipEmail
{
    const ID = 'renewal_order_receipt';

    public function __construct()
    {
        add_action('ppress_order_completed', [$this, 'dispatch_email']);
    }

    /**
     * @param OrderEntity $order
     *
     * @return void
     */
    public function dispatch_email($order)
    {
        if (ppress_get_setting(self::ID . '_email_enabled', 'on') !== 'on') return;

        if ($order->is_renewal_order()) {

            $placeholders_values = $this->get_order_placeholders_values($order);

            $subject = apply_filters('ppress_' . self::ID . '_email_subject', $this->parse_placeholders(
                ppress_get_setting(self::ID . '_email_subject', esc_html__('Subscription Renewal Receipt', 'wp-user-avatar'), true),
                $placeholders_values,
	            $order
            ), $order);

            $message = apply_filters('ppress_' . self::ID . '_email_content', $this->parse_placeholders(
                ppress_get_setting(self::ID . '_email_content', $this->get_order_receipt_content(true), true),
                $placeholders_values,
	            $order
            ), $order);

            $recipient = apply_filters('ppress_' . self::ID . '_recipient', $order->get_customer_email(), $order);

            ppress_send_email($recipient, $subject, $message);
        }
    }
}