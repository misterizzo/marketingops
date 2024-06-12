<?php

use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePress\Core\Membership\Services\TaxService;

/**
 * @var ProfilePress\Core\Membership\Models\Order\OrderEntity $order
 * @var $logo_url
 * @var $additional_info
 */

$date_paid = ppress_format_date(! empty($order->date_completed) ? $order->date_completed : $order->date_created);

$tax_label = TaxService::init()->get_tax_label($order->billing_country);

$business_name    = ppress_get_setting('business_name', '');
$business_address = ppress_get_setting('business_address', '');
$business_city    = ppress_get_setting('business_city', '');
$business_country = ppress_get_setting('business_country', '');
$business_state   = ppress_get_country_state_title(ppress_get_setting('business_state', ''), $business_country);

$business_country = ppress_get_country_title(ppress_get_setting('business_country', ''));

$business_tin = ppress_get_setting('business_tin', '');

$business_postal_code = ppress_get_setting('business_postal_code', '');

$business_address_extra = array_filter([$business_city, $business_state, $business_postal_code]);

$customer_address_1 = array_filter([$order->billing_city, $order->billing_postcode]);
$customer_country   = ppress_get_country_title($order->billing_country);
$customer_state     = ppress_get_country_state_title($order->billing_state, $order->billing_country);

?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr(get_bloginfo('language')); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <?php do_action('ppress_receipt_template_header', $order); ?>
</head>
<body>
<?php do_action('ppress_receipt_template_body_top', $order); ?>
<div id="receipt-wrap">
    <div style="margin: 40px 40px 0;overflow:hidden;min-height:700px;">
        <div class="invoice-top">
            <div>
                <div class="h1 mb20"><?= apply_filters('ppress_receipt_template_header_text', esc_html__('Receipt', 'profilepress-pro'), $order) ?></div>
                <div class="mb20">
                    <table cellpadding="0" cellspacing="0" class="metadata">
                        <tbody>
                        <tr>
                            <td><?= esc_html__('Order', 'profilepress-pro'); ?>:</td>
                            <td><?= $order->get_reduced_order_key(); ?></td>
                        </tr>
                        <tr>
                            <td><?= esc_html__('Date paid', 'profilepress-pro'); ?>:</td>
                            <td><?= $date_paid; ?></td>
                        </tr>
                        <tr>
                            <td><?= esc_html__('Payment method', 'profilepress-pro'); ?>:</td>
                            <td><?= $order->get_payment_method_title(); ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="brand-logo-container">
                <?php if ( ! empty($logo_url)) : ?>
                    <img class="logo-img brand-logo-img" src="<?= esc_url($logo_url) ?>">
                <?php else : ?>
                    <div class="h1 logo-gray"><?= ppress_site_title() ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex-horizontal  mb30 break-word">
            <div class="width-50">

                <?php do_action('ppress_receipt_company_column_start', $order); ?>

                <?php if ( ! empty($business_name)) : ?>
                    <div class="bold"><?= $business_name; ?></div>
                <?php endif; ?>

                <?php if ( ! empty($business_address)) : ?>
                    <div><?= $business_address; ?></div>
                <?php endif; ?>

                <?php if ( ! empty($business_address_extra)) : ?>
                    <div>
                        <?= implode(', ', $business_address_extra) ?>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty($business_country)) : ?>
                    <div><?= $business_country; ?></div>
                <?php endif; ?>

                <?php if ( ! empty($business_tin)) : ?>
                    <div><?php printf('%s: %s', $tax_label, $business_tin); ?></div>
                <?php endif; ?>

                <?php do_action('ppress_receipt_company_column_end', $order); ?>

            </div>
            <div class="width-50">
                <div class="width-100 pr15">

                    <div class="bold"><?php esc_html_e('Billed to', 'profilepress-pro') ?></div>

                    <div><?php echo $order->get_customer()->get_name(); ?></div>

                    <?php if ( ! empty($order->billing_address)) : ?>
                        <div><?= $order->billing_address; ?></div>
                    <?php endif; ?>

                    <?php if ( ! empty($customer_address_1)) : ?>
                        <div><?= implode(', ', $customer_address_1) ?></div>
                    <?php endif; ?>

                    <?php if ( ! empty($customer_state)) : ?>
                        <div><?= $customer_state; ?></div>
                    <?php endif; ?>

                    <?php if ( ! empty($customer_country)) : ?>
                        <div><?= $customer_country; ?></div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        <div class="mb30">
            <div class="h2">
                <?php printf(esc_html__('%s paid on %s', 'profilepress-pro'), ppress_display_amount($order->total, $order->currency), $date_paid); ?>
            </div>
        </div>
        <table cellpadding="0" cellspacing="0" class="line-item-table mb30">
            <tbody>
            <tr class="table-headers">
                <td class="width-50"><?php esc_html_e('Description', 'profilepress-pro'); ?></td>
                <td class="width-16point7"></td>
                <td class="width-16point7"></td>
                <td class="width-0"></td>
                <td class="width-16point7 align-right">
                    <div class="line-height-1point3"><?php esc_html_e('Price', 'profilepress-pro'); ?></div>
                </td>
            </tr>
            <tr class="line-item-row">
                <td>
                    <div style="word-break: break-word;padding-left: 0;">
                        <div class="bold"><?php echo $order->get_plan()->get_name(); ?></div>
                    </div>
                </td>
                <td>
                </td>
                <td></td>
                <td></td>
                <td><?php echo ppress_display_amount($order->subtotal, $order->currency) ?></td>
            </tr>
            <tr>
                <td colspan="5" height="15" style="border: 0;border-collapse: collapse;margin: 0;padding: 0;height: 15px;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;"></td>
            </tr>
            <tr class="summary-amount-border">
                <td></td>
                <td colspan="4"></td>
            </tr>

            <?php if (Calculator::init($order->get_discount())->isGreaterThanZero()) : ?>
                <tr class="summary-amount">
                    <td></td>
                    <td colspan="3">
                        <?php printf(
                            esc_html__('Discount %s', 'profilepress-pro'),
                            '&mdash; ' . $order->coupon_code
                        ); ?>
                    </td>
                    <td class="align-right">
                        &#45;<?php echo ppress_display_amount($order->get_discount(), $order->currency); ?>
                    </td>
                </tr>
            <?php endif; ?>

            <?php if (Calculator::init($order->tax)->isGreaterThanZero()) : ?>
                <tr class="summary-amount">
                    <td></td>
                    <td colspan="3"><?php printf('%s (%s%%):', $tax_label, $order->tax_rate); ?></td>
                    <td class="align-right"><?php echo ppress_display_amount($order->tax, $order->currency); ?></td>
                </tr>
            <?php endif; ?>
            <tr class="summary-amount-border">
                <td></td>
                <td colspan="4"></td>
            </tr>
            <tr class="summary-amount ">
                <td></td>
                <td colspan="3">
                    <?php esc_html_e('Total', 'profilepress-pro') ?>
                </td>
                <td class="align-right">
                    <?php echo ppress_display_amount($order->total, $order->currency); ?>
                </td>
            </tr>
            <tr class="summary-amount-border">
                <td></td>
                <td colspan="4"></td>
            </tr>
            <tr class="summary-amount bold">
                <td></td>
                <td colspan="3">
                    <?php esc_html_e('Amount paid', 'profilepress-pro') ?>
                </td>
                <td class="align-right">
                    <?php echo ppress_display_amount($order->total, $order->currency); ?>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="pb30 avoid-page-break"></div>
    </div>
    <div class="mt20 pt20 light-gray-border-top fs-13 line-height-140" style="padding-bottom: 40px;margin-left: 40px;margin-right: 40px;overflow:hidden;">
        <?php if ( ! empty($additional_info)) : ?>
            <div class="flex-horizontal">
                <div class="width-50"><?php echo wpautop($additional_info); ?></div>
            </div>
        <?php endif; ?>
        <div class="mt20">
            <div class="flex-horizontal">
                <div class="nowrap">
                    <?php printf(
                        esc_html__('%s %s %s paid on %s', 'profilepress-pro'),
                        $order->get_reduced_order_key(),
                        '&middot;',
                        ppress_display_amount($order->total, $order->currency),
                        $date_paid
                    ); ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>