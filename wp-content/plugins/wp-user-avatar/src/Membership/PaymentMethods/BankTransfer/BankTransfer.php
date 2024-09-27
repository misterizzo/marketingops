<?php

namespace ProfilePress\Core\Membership\PaymentMethods\BankTransfer;

use ProfilePress\Core\Membership\Controllers\CheckoutResponse;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\PaymentMethods\AbstractPaymentMethod;

class BankTransfer extends AbstractPaymentMethod
{
    public function __construct()
    {
        parent::__construct();

        $this->id          = 'bank_transfer';
        $this->title       = esc_html__('Direct bank transfer', 'wp-user-avatar');
        $this->description = esc_html__('Make your payment to our bank account. Please use your Order ID as the payment reference. Your order will only be completed once the funds have cleared in our account.', 'wp-user-avatar');

        $this->method_title       = esc_html__('Direct bank transfer', 'wp-user-avatar');
        $this->method_description = esc_html__('Take payments in person via BACS. More commonly known as direct bank/wire transfer.', 'wp-user-avatar');

        $this->icon = '';

        $this->supports = [self::SUBSCRIPTIONS];

        add_action('ppress_myaccount_view_order_before_order_details_table', [$this, 'frontend_bank_account_details']);
    }

    protected function is_billing_fields_removed()
    {
        return $this->get_value('remove_billing_fields') == 'true';
    }

    public function admin_settings()
    {
        $settings = parent::admin_settings();

        $settings['description']['type'] = 'textarea';

        $default_bank_details = <<<BANK
<p>Below are our bank details.</p>
<ul>
<li>Account holder:</li>
<li>IBAN:</li>
<li>BIC/SWIFT:</li>
</ul>
<p>Please initiate the bank transfer to complete your order. Use your order number as the payment reference.</p>
BANK;

        $settings['account_details'] = [
            'label'       => esc_html__('Bank Account details', 'wp-user-avatar'),
            'type'        => 'wp_editor',
            'value'       => $default_bank_details,
            'description' => esc_html__('These account details will be displayed on the order confirmation page.', 'wp-user-avatar')
        ];

        $settings['remove_billing_fields'] = [
            'label'          => esc_html__('Remove Billing Address', 'wp-user-avatar'),
            'type'           => 'checkbox',
            'checkbox_label' => esc_html__('Check to remove billing address fields from the checkout page.', 'wp-user-avatar'),
            'description'    => esc_html__('If you do not want the billing address fields displayed on the checkout page, use this setting to remove it.', 'wp-user-avatar')
        ];

        return $settings;
    }

    /**
     * @return bool|\WP_Error
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

    protected function billing_address_form()
    {
        if ($this->is_billing_fields_removed()) return;

        parent::billing_address_form();
    }

    public function process_payment($order_id, $subscription_id, $customer_id)
    {
        return (new CheckoutResponse())->set_is_success(true);
    }

    /**
     * @param OrderEntity $order
     *
     * @return void
     */
    public function frontend_bank_account_details($order)
    {
        static $cache = false;

        if (false === $cache && $order->payment_method == $this->id && ! $order->is_completed()) {
            $cache   = true;
            $content = $this->get_value('account_details');
            echo '<div style="margin: 0 0 10px;">' . wpautop($content) . '</div>';
        }
    }

    public function process_webhook()
    {

    }
}