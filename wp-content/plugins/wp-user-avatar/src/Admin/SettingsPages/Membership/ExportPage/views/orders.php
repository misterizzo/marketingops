<?php

use ProfilePress\Core\Membership\Models\Plan\PlanEntity;

/** @global array $order_statuses */
/** @global PlanEntity[] $membership_plans */

?>

<fieldset class="ppress-from-to-wrapper">
    <label for="ppress-orders-export-start" class="screen-reader-text"><?php esc_html_e('Set start date', 'wp-user-avatar') ?></label>
    <span id="ppress-orders-export-start-wrap"><input type="text" name="orders-export-start" id="ppress-orders-export-start" autocomplete="off" placeholder="<?php esc_html_e('From', 'wp-user-avatar') ?>" class="ppress-export-start ppress_datepicker"></span>
    <label for="ppress-orders-export-end" class="screen-reader-text"><?php esc_html_e('Set end date', 'wp-user-avatar') ?></label>
    <span id="ppress-orders-export-end-wrap"><input type="text" name="orders-export-end" id="ppress-orders-export-end" autocomplete="off" placeholder="<?php esc_html_e('To', 'wp-user-avatar') ?>" class="ppress-export-end ppress_datepicker"></span>
</fieldset>

<label for="ppress_orders_export_plan" class="screen-reader-text"><?php esc_html_e('Select Plan', 'wp-user-avatar') ?></label>
<select id="ppress_orders_export_plan" name="plan_id" class="ppress-select">
    <option value=""><?php esc_html_e('All Membership Plans', 'wp-user-avatar') ?></option>
    <?php foreach ($membership_plans as $plan) : ?>
        <option value="<?php echo $plan->get_id() ?>"><?php echo $plan->get_name() ?></option>
    <?php endforeach; ?>
</select>

<label for="ppress_orders_export_plan" class="screen-reader-text"><?php esc_html_e('Select Order Status', 'wp-user-avatar') ?></label>
<select id="ppress_orders_export_plan" name="order_status" class="ppress-select">
    <option value=""><?php esc_html_e('All Order Statuses', 'wp-user-avatar') ?></option>
    <?php foreach ($order_statuses as $id => $label) : ?>
        <option value="<?php echo $id ?>"><?php echo $label ?></option>
    <?php endforeach; ?>
</select>