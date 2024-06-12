<?php

use ProfilePress\Core\Membership\Models\Plan\PlanEntity;

/** @global PlanEntity[] $membership_plans */

?>

<label for="ppress_plans_export_plan" class="screen-reader-text"><?php esc_html_e('Select Plan', 'wp-user-avatar') ?></label>
<select id="ppress_plans_export_plan" name="plan_id" class="ppress-select">
    <option value=""><?php esc_html_e('All Membership Plans', 'wp-user-avatar') ?></option>
    <?php foreach ($membership_plans as $plan) : ?>
        <option value="<?php echo $plan->get_id() ?>"><?php echo $plan->get_name() ?></option>
    <?php endforeach; ?>
</select>