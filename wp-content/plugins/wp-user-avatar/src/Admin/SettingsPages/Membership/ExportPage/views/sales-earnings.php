<?php

use ProfilePress\Core\Membership\Models\Plan\PlanEntity;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethodInterface;

/** @global PlanEntity[] $membership_plans */
/** @global PaymentMethodInterface[] $payment_methods */

?>

<fieldset class="ppress-from-to-wrapper">
    <label for="ppress-order-export-start" class="screen-reader-text"><?php esc_html_e('Set start date', 'wp-user-avatar') ?></label>
    <span id="ppress-order-export-start-wrap"><input type="text" name="order-export-start" id="ppress-order-export-start" autocomplete="off" placeholder="<?php esc_html_e('From', 'wp-user-avatar') ?>" class="ppress-export-start ppress_datepicker"></span>
    <label for="ppress-order-export-end" class="screen-reader-text"><?php esc_html_e('Set end date', 'wp-user-avatar') ?></label>
    <span id="ppress-order-export-end-wrap"><input type="text" name="order-export-end" id="ppress-order-export-end" autocomplete="off" placeholder="<?php esc_html_e('To', 'wp-user-avatar') ?>" class="ppress-export-end ppress_datepicker"></span>
</fieldset>

<label for="ppress_sales_earnings_export_plan" class="screen-reader-text"><?php esc_html_e('Select Plan', 'wp-user-avatar') ?></label>
<select id="ppress_sales_earnings_export_plan" name="plan_id" class="ppress-select">
    <option value=""><?php esc_html_e('All Membership Plans', 'wp-user-avatar') ?></option>
    <?php foreach ($membership_plans as $plan) : ?>
        <option value="<?php echo $plan->get_id() ?>"><?php echo $plan->get_name() ?></option>
    <?php endforeach; ?>
</select>

<label for="ppress_order_export_customer" class="screen-reader-text"><?php esc_html_e('Select Customer', 'wp-user-avatar') ?></label>
<select id="ppress_order_export_customer" name="customer_id" class="ppress-select2-field customer_user">
    <option value="all"><?= esc_html__('All Customers', 'wp-user-avatar') ?></option>
</select>

<label for="ppress_sales_earnings_export_payment_method" class="screen-reader-text"><?php esc_html_e('Select Payment Method', 'wp-user-avatar') ?></label>
<select id="ppress_sales_earnings_export_payment_method" name="payment_method" class="ppress-select">
    <option value=""><?php esc_html_e('All Payment Method', 'wp-user-avatar') ?></option>
    <?php foreach ($payment_methods as $payment_method) : ?>
        <option value="<?php echo $payment_method->get_id() ?>"><?php echo $payment_method->get_method_title() ?></option>
    <?php endforeach; ?>
</select>