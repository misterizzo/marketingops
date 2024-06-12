<?php

use ProfilePress\Core\Membership\Models\Plan\PlanEntity;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethodInterface;

/** @global PlanEntity[] $membership_plans */
/** @global PaymentMethodInterface[] $payment_methods */

?>

<fieldset class="ppress-from-to-wrapper">
    <label for="ppress-product-sales-export-start" class="screen-reader-text"><?php esc_html_e('Set start date', 'wp-user-avatar') ?></label>
    <span id="ppress-product-sales-export-start-wrap"><input type="text" name="product-sales-export-start" id="ppress-product-sales-export-start" autocomplete="off" placeholder="<?php esc_html_e('From', 'wp-user-avatar') ?>" class="ppress-export-start ppress_datepicker"></span>
    <label for="ppress-product-sales-export-end" class="screen-reader-text"><?php esc_html_e('Set end date', 'wp-user-avatar') ?></label>
    <span id="ppress-product-sales-export-end-wrap"><input type="text" name="product-sales-export-end" id="ppress-product-sales-export-end" autocomplete="off" placeholder="<?php esc_html_e('To', 'wp-user-avatar') ?>" class="ppress-export-end ppress_datepicker"></span>
</fieldset>

<label for="ppress_product_sales_export_plan" class="screen-reader-text"><?php esc_html_e('Select Plan', 'wp-user-avatar') ?></label>
<select id="ppress_product_sales_export_plan" name="plan_id" class="ppress-select">
    <option value=""><?php esc_html_e('All Membership Plans', 'wp-user-avatar') ?></option>
    <?php foreach ($membership_plans as $plan) : ?>
        <option value="<?php echo $plan->get_id() ?>"><?php echo $plan->get_name() ?></option>
    <?php endforeach; ?>
</select>