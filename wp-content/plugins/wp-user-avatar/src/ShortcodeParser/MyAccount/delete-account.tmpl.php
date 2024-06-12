<?php

if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$current_user = get_user_by('id', get_current_user_id());

?>
    <div class="profilepress-myaccount-delete-account">

        <h2><?= esc_html__('Delete Account', 'wp-user-avatar') ?></h2>

        <p><?php esc_html_e('Are you sure you want to delete your account? This will erase all of your account data from the site. To delete your account enter your password below.', 'wp-user-avatar') ?></p>

        <form method="post" id="ppmyac-form-deleteAccount">

            <div class="profilepress-myaccount-form-wrap">

                <div class="profilepress-myaccount-form-field">
                    <label for="password_new"><?= esc_html__('Password', 'wp-user-avatar') ?></label>
                    <input type="password" name="password" id="password_new" required="required" class="profilepress-myaccount-form-control">
                </div>

                <div class="profilepress-myaccount-form-field">
                    <input name="submit-form" id="submit-form" type="submit" value="<?= esc_html__('Delete Account', 'wp-user-avatar') ?>">
                </div>
            </div>

            <input type="hidden" name="ppmyac_form_action" value="deleteAccount">
            <?= ppress_nonce_field(); ?>
        </form>

    </div>
<?php

do_action('ppress_myaccount_delete_Account');
