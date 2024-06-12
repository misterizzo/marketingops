<?php

$pp_login_form_id  = ppress_settings_by_key('replace_wc_my_account_login');
$pp_signup_form_id = ppress_settings_by_key('replace_wc_my_account_signup');

$login_shortcode  = sprintf('profilepress-login id="%s"', $pp_login_form_id);
$signup_shortcode = sprintf('profilepress-registration id="%s"', $pp_signup_form_id);

if (strpos($pp_login_form_id, 'melange_') !== false) {
    $login_shortcode = sprintf('profilepress-melange id="%s"', str_replace('melange_', '', $pp_login_form_id));
}

if (strpos($pp_signup_form_id, 'melange_') !== false) {
    $signup_shortcode = sprintf('profilepress-melange id="%s"', str_replace('melange_', '', $signup_shortcode));
}

if ( ! empty($pp_login_form_id) && empty($pp_signup_form_id)) {
    echo do_shortcode(sprintf('[%s redirect="%s"]', $login_shortcode, ppress_get_current_url_query_string()));
}

if ( ! empty($pp_signup_form_id) && empty($pp_login_form_id)) {
    echo do_shortcode(sprintf('[%s]', $signup_shortcode));
}

if ( ! empty($pp_login_form_id) && ! empty($pp_signup_form_id)) :

    echo ppress_minify_css('<style>.pp-form-container{margin-left:0!important;margin-right:0!important;}</style>');

    do_action('woocommerce_before_customer_login_form'); ?>

    <?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>

    <div class="u-columns col2-set" id="customer_login">

    <div class="u-column1 col-1">

<?php endif; ?>

    <h2><?php esc_html_e('Login', 'woocommerce'); ?></h2>

    <?php echo do_shortcode(sprintf('[%s redirect="%s"]', $login_shortcode, ppress_get_current_url_query_string())); ?>

    <?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>

    </div>

    <div class="u-column2 col-2">

        <h2><?php esc_html_e('Register', 'woocommerce'); ?></h2>

        <?php echo do_shortcode(sprintf('[%s]', $signup_shortcode)); ?>

    </div>

    </div>
<?php endif; ?>

    <?php do_action('woocommerce_after_customer_login_form'); ?>

<?php endif; ?>
