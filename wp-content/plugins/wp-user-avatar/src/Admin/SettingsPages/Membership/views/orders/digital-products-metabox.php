<?php

use ProfilePress\Core\Admin\SettingsPages\Membership\OrdersPage\OrderWPListTable;
use ProfilePress\Core\Membership\DigitalProducts\DownloadService;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;

/** @global OrderEntity[] $order_data */

$downloads = $order_data->get_plan()->get_downloads();

?>
<div id="ppress-submetabox-items">
    <div class="ppress-submetabox-items-table-wrapper">
        <table cellpadding="0" cellspacing="0" class="ppress-submetabox-items-table">
            <thead>
            <tr>
                <th class="ppress-submetabox-item-column-product"><?= esc_html__('Product', 'wp-user-avatar') ?></th>
                <th class="ppress-submetabox-item-column-downloads-remaining"><?= esc_html__('Downloads Remaining', 'wp-user-avatar') ?></th>
                <th class="ppress-submetabox-item-column-action"><?= esc_html__('Action', 'wp-user-avatar') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (isset($downloads['files']) && ! empty($downloads['files'])):
                $index = 0;
                foreach ($downloads['files'] as $file_url => $file_name) :
                    $download_url = DownloadService::init()->get_download_file_url($order_data->get_order_key(), $index, $downloads['download_expiry']); ?>
                    <tr class="ppress-submetabox-item-row">
                        <td class="ppress-submetabox-item-column-product"><?= $file_name ?></td>
                        <td class="ppress-submetabox-item-column-downloads-remaining"><?= DownloadService::init()->get_downloads_remaining($order_data->get_id(), $order_data->get_plan_id(), $file_url); ?></td>
                        <td class="ppress-submetabox-item-column-action">
                            <a class="button" target="_blank" href="<?= esc_url($download_url) ?>"><?php esc_html_e('Download', 'wp-user-avatar'); ?></a>
                        </td>
                    </tr>
                    <?php $index++; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>

        </table>

    </div>
</div>
