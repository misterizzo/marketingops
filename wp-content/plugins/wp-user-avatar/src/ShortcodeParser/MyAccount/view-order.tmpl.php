<?php

use ProfilePress\Core\Membership\CheckoutFields;
use ProfilePress\Core\Membership\CheckoutFields as CF;
use ProfilePress\Core\Membership\DigitalProducts\DownloadService;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePress\Core\Membership\Models\Order\OrderType;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePress\Core\Membership\Services\TaxService;
use ProfilePress\Core\ShortcodeParser\MyAccount\MyAccountTag;

if ( ! defined('ABSPATH')) {
    exit;
}

$order = OrderFactory::fromOrderKey(ppressGET_var('order_key'));

$customer_id = CustomerFactory::fromUserId(get_current_user_id())->id;

?>
    <div class="profilepress-myaccount-orders-subs">

        <?php if ( ! $order->exists() || (is_user_logged_in() && $order->customer_id !== $customer_id)) :

            printf('<p class="profilepress-myaccount-alert pp-alert-danger">%s</p>', esc_html__('Invalid order', 'wp-user-avatar'));

        else : $plan = ppress_get_plan($order->plan_id);
            $vat_number = $order->get_customer_tax_id();
            $plan_purchase_note = $order->get_plan_purchase_note();

            do_action('ppress_myaccount_view_order_before_order_details_table', $order, $customer_id);
            ?>

            <div class="profilepress-myaccount-order-details-wrap">
                <div class="ppress-details-table-wrap">
                    <table class="ppress-details-table">
                        <tbody>
                        <tr>
                            <td><?php esc_html_e('Order', 'wp-user-avatar'); ?></td>
                            <td><?= $order->get_reduced_order_key() ?> <?php do_action('ppress_myaccount_view_order_details_order_row', $order); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Date Placed', 'wp-user-avatar'); ?></td>
                            <td><?= ppress_format_date($order->date_created) ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Order Item', 'wp-user-avatar'); ?></td>
                            <td><?= $plan->name ?>
                                <span class="ppress-sub-info"><?= ppress_display_amount($plan->price) ?></span></td>
                        </tr>
                        <?php if ( ! empty($plan->description)) : ?>
                            <tr>
                                <td><?php esc_html_e('Item Description', 'wp-user-avatar'); ?></td>
                                <td><?= wpautop($plan->description) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($order->is_completed() && ! empty($plan_purchase_note)): ?>
                            <tr>
                                <td><?php esc_html_e('Purchase Note', 'wp-user-avatar'); ?></td>
                                <td><?= $plan_purchase_note ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td><?php esc_html_e('Order Status', 'wp-user-avatar'); ?></td>
                            <td>
                                <span class="order-status <?= $order->status ?>"><?= OrderStatus::get_label($order->status) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Order Type', 'wp-user-avatar'); ?></td>
                            <td>
                                <span class="order-type <?= $order->order_type ?>"><?= OrderType::get_label($order->order_type) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Payment Method', 'wp-user-avatar'); ?></td>
                            <td><?= PaymentMethods::get_instance()->get_by_id($order->payment_method)->title ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Billing Address', 'wp-user-avatar'); ?></td>
                            <td>
                                <?php

                                foreach (CheckoutFields::standard_billing_fields() as $field_id => $field) {

                                    $method_name = str_replace('ppress_', '', $field_id);

                                    $detail = $order->$method_name;

                                    if ( ! empty($detail)) {

                                        if ($field_id == CF::BILLING_COUNTRY) {
                                            $detail = ppress_array_of_world_countries($detail);
                                        }

                                        if ($field_id == CF::BILLING_STATE) {
                                            $state  = ! empty($order->billing_country) ? ppress_array_of_world_states($order->billing_country) : [];
                                            $detail = ppress_var($state, $detail, $detail, true);
                                        }

                                    }

                                    if (empty($detail)) continue;

                                    echo '<p>';
                                    printf('<span class="ppress-billing-title">%s:</span> %s', esc_html($field['label']), $detail);
                                    echo '</p>';
                                } ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Subtotal', 'wp-user-avatar'); ?></td>
                            <td><?= ppress_display_amount($order->subtotal, $order->currency) ?></td>
                        </tr>
                        <?php if ( ! Calculator::init($order->discount)->isNegativeOrZero()) : ?>
                            <tr>
                                <td><?php esc_html_e('Discount', 'wp-user-avatar'); ?></td>
                                <td>
                                    <?= ppress_display_amount($order->discount, $order->currency) ?>
                                    <span class="ppress-sub-info"><?= $order->coupon_code ?></span>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if (TaxService::init()->is_tax_enabled() || Calculator::init($order->tax)->isGreaterThanZero()) : ?>
                            <tr>
                                <td><?= TaxService::init()->get_tax_label($order->billing_country) ?></td>
                                <td><?= ppress_display_amount($order->tax, $order->currency) ?>
                                    <?php if (Calculator::init($order->tax_rate)->isGreaterThanZero()) : ?>
                                        <span class="ppress-sub-info"><?= $order->tax_rate ?>%</span>
                                    <?php elseif (TaxService::init()->is_reverse_charged($order->id)) : ?>
                                        <span class="ppress-sub-info"><?php esc_html_e('Reverse Charged', 'wp-user-avatar') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td><?php esc_html_e('Total', 'wp-user-avatar'); ?></td>
                            <td><?= ppress_display_amount($order->total, $order->currency) ?></td>
                        </tr>
                        <?php if ( ! empty($vat_number)): ?>
                            <tr>
                                <td><?php esc_html_e('VAT Number', 'wp-user-avatar'); ?></td>
                                <td><?= $vat_number ?></td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php $subscription = SubscriptionFactory::fromId($order->subscription_id); ?>

            <?php if ($subscription->exists()): $view_sub_url = add_query_arg(['sub_id' => $subscription->id], MyAccountTag::get_endpoint_url('list-subscriptions')) ?>

                <h2><?= esc_html__('Subscription', 'wp-user-avatar') ?></h2>

                <div class="profilepress-myaccount-sub-order-details-wrap">
                    <div class="profilepress-myaccount-sub-order-details-table-wrap">
                        <table class="ppress-details-table">
                            <thead>
                            <tr>
                                <th><?php esc_html_e('Subscription', 'wp-user-avatar') ?></th>
                                <th><?php esc_html_e('Terms', 'wp-user-avatar') ?></th>
                                <th><?php esc_html_e('Status', 'wp-user-avatar') ?></th>
                                <th><?php esc_html_e('Renewal Date', 'wp-user-avatar') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url($view_sub_url) ?>"><?= $subscription->get_plan()->get_name() ?></a>
                                </td>
                                <td><?= $subscription->get_subscription_terms() ?></td>
                                <td><?= $subscription->get_status_label() ?></td>
                                <td><?= $subscription->get_formatted_expiration_date() ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


            <?php endif; ?>

            <?php if ($order->is_completed() && SubscriptionFactory::fromId($order->subscription_id)->is_active()) : $downloads = $plan->get_downloads(); ?>

                <?php if (isset($downloads['files']) && is_array($downloads['files']) && ! empty($downloads['files'])) : ?>

                    <h2><?= esc_html__('Downloads', 'wp-user-avatar') ?></h2>

                    <div class="profilepress-myaccount-sub-order-details-wrap">
                        <div class="profilepress-myaccount-sub-order-details-table-wrap">
                            <table class="ppress-details-table">
                                <thead>
                                <tr>
                                    <th><?php esc_html_e('Product', 'wp-user-avatar') ?></th>
                                    <th><?php esc_html_e('Downloads Remaining', 'wp-user-avatar') ?></th>
                                    <th><?php esc_html_e('Action', 'wp-user-avatar') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $index = 0; ?>
                                <?php foreach ($downloads['files'] as $file_url => $file_name) : ?>
                                    <?php $download_link = DownloadService::init()->get_download_file_url(
                                        $order->order_key,
                                        $index,
                                        $downloads['download_expiry']
                                    ); ?>
                                    <tr>
                                        <td><?= $file_name ?></td>
                                        <td><?php echo DownloadService::init()->get_downloads_remaining(
                                                $order->get_id(),
                                                $plan->get_id(),
                                                $file_url
                                            ) ?></td>
                                        <td>
                                            <a class="ppress-myac-action" href="<?php echo esc_url($download_link) ?>"><?php esc_html_e('Download', 'wp-user-avatar'); ?></a>
                                        </td>
                                    </tr>
                                    <?php $index++; ?>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php

do_action('ppress_myaccount_view_order_details', $order);