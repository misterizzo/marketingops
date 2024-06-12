<?php

namespace ProfilePress\Libsodium\Receipt;

use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;

class Generator
{
    public function __construct()
    {
        add_action('init', [$this, 'generate_receipt']);

        add_action('ppress_receipt_template_header', [$this, 'scripts_styles']);

        add_action('ppress_receipt_template_body_top', [$this, 'header_actions']);
    }

    public function generate_receipt()
    {
        if (ppressGET_var('ppress_action') == 'view_receipt') {
            $req_order_key  = ppressGET_var('order_key', '');
            $req_order_hash = ppressGET_var('receipt', '');

            if ( ! empty($req_order_key) && ! empty($req_order_hash)) {

                $order = OrderFactory::fromOrderKey($req_order_key);

                if ($order->exists() && $this->current_user_can_view_invoice($order, $req_order_hash)) {

                    if ( ! $order->is_completed()) {
                        wp_die(esc_html__('No receipt for this order because it has yet to be paid for.', 'profilepress-pro'), __('Error', 'profilepress-pro'), ['response' => 403]);
                    }

                    ppress_render_view(
                        'main',
                        [
                            'order'           => $order,
                            'logo_url'        => ppress_settings_by_key('receipt_logo_url', ''),
                            'additional_info' => ppress_settings_by_key('receipt_additional_info', '')
                        ],
                        dirname(__FILE__) . '/template/');
                    die();
                }

                wp_die(esc_html__('This order does not exist or you do not have the permission to view the order receipt.', 'profilepress-pro'), __('Error', 'profilepress-pro'), ['response' => 403]);
            }
        }
    }

    /**
     * @param OrderEntity $order
     *
     * @return void
     */
    public function header_actions($order)
    {
        ?>
        <div class="invoice-actions" data-html2canvas-ignore="">
            <a class="button home" href="<?= home_url() ?>"><?php esc_html_e('Home', 'profilepress-pro') ?></a>
            <button class="button print" onclick="window.print()"><?php esc_html_e('Print', 'profilepress-pro') ?></button>
            <button class="button pdf" data-name="<?= apply_filters('ppress_receipt_data_name', 'receipt-' . $order->get_reduced_order_key() . '.pdf', $order); ?>"><?php esc_html_e('Download as PDF', 'profilepress-pro') ?></button>
        </div>
        <?php
    }

    private function current_user_can_view_invoice($order, $req_order_hash)
    {
        $user_id = $order->get_customer()->user_id;

        if (is_user_logged_in()) {

            if ($user_id == get_current_user_id() || current_user_can('manage_options')) {
                // Current user is the owner of the order or an admin
                return true;
            }
        }

        // User is not logged in, but is using the link with the correct ID and hashed invoice key.
        if (
            ! is_user_logged_in() &&
            hash_equals(md5($order->id . $order->get_order_key() . $order->get_customer_email()), $req_order_hash)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param OrderEntity $order
     *
     * @return void
     */
    public function scripts_styles($order)
    {
        wp_register_style('ppress-receipt', PROFILEPRESS_PRO_LIBSODIUM_ASSETS_URL . 'receipt/receipt.css', [], PPRESS_VERSION_NUMBER, 'all');
        wp_register_script('html2pdf', PROFILEPRESS_PRO_LIBSODIUM_ASSETS_URL . "receipt/html2pdf.bundle.min.js", [], '0.9.3', true);
        wp_register_script('ppress-receipt-pdf', PROFILEPRESS_PRO_LIBSODIUM_ASSETS_URL . 'receipt/init.js', ['html2pdf'], PPRESS_VERSION_NUMBER, true);
        wp_print_scripts('ppress-receipt-pdf');
        wp_print_styles('ppress-receipt');

        printf(
            '<title>%s</title>',
            sprintf(
            /* translators: the invoice number */
                esc_html__('Order Receipt: %s', 'profilepress-pro'),
                esc_html($order->get_reduced_order_key())
            )
        );
    }

    /**
     * @return self|void
     */
    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}
