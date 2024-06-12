<?php

use ProfilePress\Core\Membership\Models\Plan\PlanEntity;

/** @global array $subscription_statuses */
/** @global PlanEntity[] $membership_plans */

?>

<fieldset class="ppress-from-to-wrapper">
    <label for="ppress-subscriptions-export-start" class="screen-reader-text"><?php esc_html_e('Set start date', 'wp-user-avatar') ?></label>
    <span id="ppress-subscriptions-export-start-wrap"><input type="text" name="subscriptions-export-start" id="ppress-subscriptions-export-start" autocomplete="off" placeholder="<?php esc_html_e('From', 'wp-user-avatar') ?>" class="ppress-export-start ppress_datepicker"></span>
    <label for="ppress-subscriptions-export-end" class="screen-reader-text"><?php esc_html_e('Set end date', 'wp-user-avatar') ?></label>
    <span id="ppress-subscriptions-export-end-wrap"><input type="text" name="subscriptions-export-end" id="ppress-subscriptions-export-end" autocomplete="off" placeholder="<?php esc_html_e('To', 'wp-user-avatar') ?>" class="ppress-export-end ppress_datepicker"></span>
</fieldset>

<label for="ppress_subscriptions_export_plan" class="screen-reader-text"><?php esc_html_e('Select Plan', 'wp-user-avatar') ?></label>
<select id="ppress_subscriptions_export_plan" name="plan_id" class="ppress-select">
    <option value=""><?php esc_html_e('All Membership Plans', 'wp-user-avatar') ?></option>
    <?php foreach ($membership_plans as $plan) : ?>
        <option value="<?php echo $plan->get_id() ?>"><?php echo $plan->get_name() ?></option>
    <?php endforeach; ?>
</select>

<label for="ppress_subscriptions_export_plan" class="screen-reader-text"><?php esc_html_e('Select Subscription Status', 'wp-user-avatar') ?></label>
<select id="ppress_subscriptions_export_plan" name="subscription_status" class="ppress-select">
    <option value=""><?php esc_html_e('All Subscription Statuses', 'wp-user-avatar') ?></option>
    <?php foreach ($subscription_statuses as $id => $label) : ?>
        <option value="<?php echo $id ?>"><?php echo $label ?></option>
    <?php endforeach; ?>
</select>