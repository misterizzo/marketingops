<?php

use ProfilePress\Core\Membership\Controllers\CheckoutSessionData;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Group\GroupEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\Services\OrderService;
use ProfilePress\Core\Membership\Models\Plan\PlanEntity;

/** @var PlanEntity $planObj */
/** @var GroupEntity $groupObj */
/** @var int $changePlanSubId */

$changePlanId = SubscriptionFactory::fromId($changePlanSubId)->get_plan_id();

/** @var bool true if we on change plan checkout and selected plan is same as plan to switch from */
$isChangePlanIdSelected = $changePlanId == $planObj->id;

$cart_vars = OrderService::init()->checkout_order_calculation([
    'plan_id'            => $planObj->id,
    'coupon_code'        => CheckoutSessionData::get_coupon_code($planObj->id),
    'tax_rate'           => CheckoutSessionData::get_tax_rate($planObj->id),
    'change_plan_sub_id' => $changePlanSubId
]);

?>

<div id="ppress_checkout_summary" class="ppress-checkout-section ppress-checkout_side_section">
    <?php
    ppress_render_view('checkout/form-checkout-sidebar', [
            'plan'                   => $planObj,
            'cart_vars'              => $cart_vars,
            'isChangePlanIdSelected' => $isChangePlanIdSelected
        ]
    ); ?>
</div>

<div id="ppress_checkout_main_form" class="ppress-checkout-section ppress-checkout_main">

    <form method="post" id="ppress_mb_checkout_form" enctype="multipart/form-data">
        <input id="ppress-checkout-plan-id" type="hidden" name="plan_id" value="<?= $planObj->id ?>">
        <input id="ppress-checkout-group-id" type="hidden" name="group_id" value="<?= $groupObj->get_id() ?>">
        <input id="ppress-checkout-change-plan-id" type="hidden" name="change_plan_sub_id" value="<?= $changePlanSubId ?>">
        <?php wp_nonce_field('ppress_process_checkout', 'ppress_checkout_nonce'); ?>

        <div class="ppress-main-checkout-form__block">

            <?php if ($changePlanId > 0 || $groupObj->exists()) : ?>
                <?php ppress_render_view('checkout/group-selector', ['plan' => $planObj]); ?>
            <?php endif; ?>

            <?php if (CustomerFactory::fromUserId(get_current_user_id())->has_active_subscription($planObj->id)) : ?>
                <div class="ppress-alert ppress-error">
                    <p><?php esc_html_e('You have an active subscription to this plan.', 'wp-user-avatar') ?></p>
                </div>
            <?php else: ?>

                <?php if ( ! $isChangePlanIdSelected) : ?>

                    <div class="ppress-main-checkout-form__block__fieldset">
                        <fieldset id="ppress_checkout_account_info">
                            <legend>
                                <span><?php esc_html_e('Account Information', 'wp-user-avatar') ?></span>
                                <?php if ( ! is_user_logged_in()): ?>
                                    <a class="ppress-checkout-show-login-form" href="#">
                                        <?php esc_html_e('Already have an account?', 'wp-user-avatar') ?>
                                    </a>
                                <?php endif; ?>
                            </legend>

                            <?php ppress_render_view('checkout/form-login', ['plan' => $planObj, 'groupObj' => $groupObj]); ?>

                        </fieldset>
                    </div>

                    <?php ppress_render_view('checkout/form-account-info-fields'); ?>

                    <?php ppress_render_view('checkout/form-payment-methods', [
                        'plan'      => $planObj,
                        'cart_vars' => $cart_vars
                    ]); ?>

                    <?php ppress_render_view('checkout/form-terms'); ?>

                    <?php do_action('ppress_checkout_before_submit_button', $cart_vars, $planObj); ?>

                    <label style="display: none !important;">
                        <input style="display:none !important" type="text" name="_ppress_honeypot" value="" tabindex="-1" autocomplete="off"/>
                    </label>
                    <input type="hidden" name="_ppress_timestamp" value="<?= time() ?>"/>

                    <?php ppress_render_view('checkout/form-checkout-submit-btn', [
                        'order_total' => $cart_vars->total,
                        'plan'        => $planObj
                    ]); ?>
                <?php endif; ?>

            <?php endif; ?>
        </div>

    </form>

</div>