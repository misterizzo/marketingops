<?php

use ProfilePress\Core\Classes\ExtensionManager;
use ProfilePress\Core\Membership\Models\Group\GroupEntity;
use ProfilePress\Core\Membership\Models\Plan\PlanEntity;

/** @var GroupEntity $groupObj */
/** @var PlanEntity $plan */

if ( ! is_user_logged_in()) : ?>

    <div class="ppress-main-checkout-form__login_form_wrap" style="display:none">

        <div class="ppress-main-checkout-form__block__item ppress-co-half">
            <label for="ppmb_user_login">
                <?php esc_html_e('Username or Email', 'wp-user-avatar') ?>
            </label>
            <input name="ppmb_user_login" id="ppmb_user_login" type="text">
        </div>
        <div class="ppress-main-checkout-form__block__item ppress-co-half">
            <label for="ppmb_user_pass"><?php esc_html_e('Password', 'wp-user-avatar') ?></label>
            <input id="ppmb_user_pass" name="ppmb_user_pass" type="password">
            <span class="ppress-main-checkout-form__login_form__lostp">
            <a class="ppress-checkout__link" href="<?php echo wp_lostpassword_url() ?>"><?php esc_html_e('Forgot your password?', 'wp-user-avatar') ?></a>
        </span>
        </div>
        <div class="ppress-main-checkout-form__block__item ppress-login-submit-btn">
            <input name="ppmb_login_submit" type="submit" value="<?php esc_html_e('Log in', 'wp-user-avatar') ?>">
            <p><?php esc_html_e('Or continue with your order below.', 'wp-user-avatar') ?></p>
        </div>
    </div>

    <?php
    if (ExtensionManager::is_enabled(ExtensionManager::SOCIAL_LOGIN)) {
        $social_login_buttons = ppress_settings_by_key('checkout_social_login_buttons');
        if (is_array($social_login_buttons)) {
            $social_login_buttons = array_filter($social_login_buttons);
        }

        if ( ! empty($social_login_buttons)) {
            $redirect_to = ppress_get_current_url_query_string();
            echo '<div class="ppress-main-checkout-form__social_login_wrap">';
            foreach ($social_login_buttons as $social_login_button) {
                echo do_shortcode(sprintf('[pp-social-login type="%s" redirect="%s"]', $social_login_button, $redirect_to), true) . '&nbsp;';
            }
            echo '</div>';
        }
    }
    ?>

<?php else :
    $user = wp_get_current_user();
    if (is_object($groupObj) && $groupObj->exists()) {
        $logout_redirect_url = $groupObj->get_checkout_url();
    } else {
        $logout_redirect_url = ppress_plan_checkout_url($plan->id);
    }
    ?>

    <div class="ppress-main-checkout-form__logged_in_text_wrap">
        <div class="ppress-main-checkout-form__block__item">
            <p>
                <?php
                /* Translators: %s display name. */
                printf(esc_html__('Logged in as %s. Not you?', 'wp-user-avatar'), esc_html($user->display_name));
                ?>
                <a href="<?php echo esc_url(wp_logout_url($logout_redirect_url)); ?>">
                    <?php esc_html_e('log out', 'wp-user-avatar'); ?>
                </a>
            </p>
        </div>
    </div>

<?php endif; ?>
