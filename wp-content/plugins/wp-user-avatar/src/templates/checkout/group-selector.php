<?php

use ProfilePress\Core\Membership\Models\Group\GroupFactory;
use ProfilePress\Core\Membership\Models\Plan\PlanEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionBillingFrequency;

/** @global PlanEntity $plan */

$group = GroupFactory::fromId($plan->get_group_id());

echo '<div class="ppress-main-checkout-form__block__fieldset">';

if ($group->plans_display_field == 'select') {

    echo '<select name="group_selector">';
    printf('<option value="" disabled>%s</option>', esc_html__('Select membership plan', 'wp-user-avatar'));

    foreach ($group->get_plan_ids() as $plan_id) {

        $_plan             = ppress_get_plan($plan_id);
        $billing_frequency = $_plan->is_recurring() ? ' ' . SubscriptionBillingFrequency::get_label($_plan->billing_frequency) : '';

        printf(
            '<option value="%s" %s>%s &mdash; %s %s</option>',
            $plan_id,
            selected($plan_id, $plan->id, false),
            apply_filters('ppress_checkout_group_selector_title', $_plan->get_name(), $group, $_plan),
            ppress_display_amount($_plan->get_price()),
            $billing_frequency
        );
    }
    echo '</select>';

} else {

    foreach ($group->get_plan_ids() as $plan_id) {

        $_plan             = ppress_get_plan($plan_id);
        $billing_frequency = $_plan->is_recurring() ? ' ' . SubscriptionBillingFrequency::get_label($_plan->billing_frequency) : '';

        echo '<label class="ppress-main-checkout-form__block__group_selector_label">';
        printf('<input type="radio" name="group_selector" value="%s" %s>', $plan_id, checked($plan_id, $plan->id, false));
        echo '<span class="ppress-main-checkout-form__block__group_selector__span_wrap">';
        echo '<span class="ppress-main-checkout-form__block__group_selector__span">';
        printf(
            '<span class="ppress-main-checkout-form__block__group_selector__title">%s</span>',
            apply_filters('ppress_checkout_group_selector_title', $_plan->get_name(), $group, $_plan)
        );
        echo '</span>';
        echo '</span>';
        echo '<span class="ppress-main-checkout-form__block__group_selector__price_wrap">';
        printf('<span class="ppress-main-checkout-form__block__group_selector__price_amount">%s</span>', ppress_display_amount($_plan->get_price()));
        printf('<span class="ppress-main-checkout-form__block__group_selector__price_duration">%s</span>', $billing_frequency);
        echo '</span>';
        echo '</label>';
    }
}

echo '</div>';