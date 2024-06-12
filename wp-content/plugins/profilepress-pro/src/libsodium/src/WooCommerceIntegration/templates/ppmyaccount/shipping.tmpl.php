<?php

use ProfilePress\Core\Classes\EditUserProfile;
use ProfilePress\Libsodium\WooCommerceIntegration\Init;

if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$current_user_id = get_current_user_id();

$success_message = EditUserProfile::get_success_message();

$shipping_fields = [
    'shipping_first_name' => esc_html__('First Name', 'profilepress-pro'),
    'shipping_last_name'  => esc_html__('Last Name', 'profilepress-pro'),
    'shipping_company'    => esc_html__('Company', 'profilepress-pro'),
    'shipping_address_1'  => esc_html__('Address 1', 'profilepress-pro'),
    'shipping_address_2'  => esc_html__('Address 2', 'profilepress-pro'),
    'shipping_city'       => esc_html__('City', 'profilepress-pro'),
    'shipping_postcode'   => esc_html__('Postcode / ZIP', 'profilepress-pro'),
    'shipping_country'    => esc_html__('Country / Region', 'profilepress-pro'),
    'shipping_state'      => esc_html__('State / County', 'profilepress-pro'),
];
?>
<?php ob_start(); ?>
    [pp-edit-profile-form]

    <div class="profilepress-myaccount-form-wrap">

        <?php foreach ($shipping_fields as $field_key => $label) : ?>
            <?php $field_type = 'shipping_country' == $field_key ? 'country' : 'text'; ?>
            <div class="profilepress-myaccount-form-field">
                <label for="<?= $field_key ?>"><?= $label ?></label>
                <?= do_shortcode(sprintf(
                        '[edit-profile-cpf id="%1$s" key="%1$s" type="%2$s" class="profilepress-myaccount-form-control"%3$s]',
                        $field_key, $field_type, $field_type == 'text' ? ' value="' . Init::get_user_meta($current_user_id, $field_key) . '"' : ''
                    )
                ); ?>
            </div>
        <?php endforeach; ?>

        <div class="profilepress-myaccount-form-field">
            <?= do_shortcode('[edit-profile-submit]'); ?>
        </div>
    </div>

    <input type="hidden" name="ppmyac_form_action" value="updateProfile">

    [/pp-edit-profile-form]

<?php

echo do_shortcode(ob_get_clean());

do_action('ppress_myaccount_woocommerce_billing');