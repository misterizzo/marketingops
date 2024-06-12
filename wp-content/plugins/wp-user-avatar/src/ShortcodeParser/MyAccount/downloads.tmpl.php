<?php

use ProfilePress\Core\Membership\DigitalProducts\DownloadService;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;

if ( ! defined('ABSPATH')) {
    exit;
}

if ( ! is_user_logged_in()) return;

$current_user_id = get_current_user_id();

$customer = CustomerFactory::fromUserId($current_user_id);

if ( ! $customer->exists()) return;

$subs = $customer->get_active_subscriptions();

$found = [];

foreach ($subs as $sub) {

    if ($sub->get_plan()->has_downloads()) {
        $found[] = $sub;
    }
}

echo '<div class="profilepress-myaccount-orders-subs">';
printf('<h2>%s</h2>', esc_html__('Downloads', 'wp-user-avatar'));
if (empty($found)) {
    printf('<p class="profilepress-myaccount-alert pp-alert-danger">%s</p>', __('You have no downloads.', 'wp-user-avatar'));
} else { ?>

    <div class="profilepress-myaccount-sub-order-details-table-wrap">
        <table class="ppress-details-table">
            <thead>
            <tr>
                <th><?php esc_html_e('Plan', 'wp-user-avatar') ?></th>
                <th><?php esc_html_e('Product', 'wp-user-avatar') ?></th>
                <th><?php esc_html_e('Downloads Remaining', 'wp-user-avatar') ?></th>
                <th><?php esc_html_e('Action', 'wp-user-avatar') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($found as $sub) : ?>
                <?php $downloads = $sub->get_plan()->get_downloads(); ?>
                <?php $plan = $sub->get_plan(); ?>
                <?php $order = OrderFactory::fromId($sub->parent_order_id); ?>
                <?php if (is_array($downloads['files']) && ! empty($downloads['files'])) : ?>
                    <?php $index = 0; ?>
                    <?php foreach ($downloads['files'] as $file_url => $file_name) : ?>
                        <?php $download_url = DownloadService::init()->get_download_file_url(
                            $order->get_order_key(), $index, $downloads['download_expiry']
                        ); ?>
                        <tr>
                            <td><?php echo $plan->get_name() ?></td>
                            <td><?php echo $file_name ?></td>
                            <td><?php echo DownloadService::init()->get_downloads_remaining($order->get_id(), $plan->get_id(), $file_url) ?></td>
                            <td>
                                <a class="ppress-myac-action" href="<?php echo esc_url($download_url) ?>"><?php esc_html_e('Download', 'wp-user-avatar'); ?></a>
                            </td>
                        </tr>
                        <?php $index++; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
echo '</div>';