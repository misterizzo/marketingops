<?php

use ProfilePress\Core\Membership\Models\Order\OrderEntity;

/** @var OrderEntity $order_data */

?>
<div class="ppress-order-subscription-wrap">
    <p>
        <span class="label">
            <span class="dashicons dashicons-download"></span>
            <a href="<?= esc_url(add_query_arg(['by_oid' => $order_data->get_id()], PPRESS_MEMBERSHIP_DOWNLOAD_LOGS_SETTINGS_PAGE)) ?>">
                <?php esc_html_e('View Order Download Logs', 'wp-user-avatar') ?>
            </a>
        </span>
    </p>
</div>