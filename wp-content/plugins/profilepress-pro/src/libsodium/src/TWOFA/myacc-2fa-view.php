<?php

use ProfilePress\Libsodium\TWOFA\AbstractClass;
use ProfilePress\Libsodium\TWOFA\Common;

// $user_id is defined from the template call

$recovery_codes_count = Common::codes_remaining_for_user($user_id);
$generate_btn_label   = empty($recovery_codes_count) ? esc_html__('Generate Recovery Codes', 'profilepress-pro') : esc_html__('Generate New Recovery Codes', 'profilepress-pro');

if (Common::is_user_2fa_required($user_id) && ! Common::has_2fa_configured($user_id)) {

    $wrap_start = '<div class="profilepress-myaccount-alert pp-alert-danger">';
    if (is_admin()) {
        $wrap_start = '<div class="pp2fa-up-notice"><p>';
    }

    $wrap_end = '</div>';
    if (is_admin()) {
        $wrap_end = '</p></div>';
    }


    echo $wrap_start;
    echo ppress_settings_by_key(
        '2fa_enforce_user_roles_message',
        esc_html__('You are required to set up two-factor authentication to use this site.', 'profilepress-pro'),
        true
    );
    echo $wrap_end;
}
?>
<div class="ppress-2fa-setup-wrap">
    <?php AbstractClass::display_status_messages(); ?>
    <form method="post">
        <p><?php echo Common::twofa_description_text() ?></p>

        <?php if (Common::has_2fa_configured($user_id)) : ?>
            <div class="profilepress-myaccount-alert pp-alert-success" role="alert">
                <?php printf(
                    '%s <a onclick="return confirm(%s)" href="%s">%s</a>',
                    Common::twofa_enabled_text(),
                    "'" . esc_html__('Are you sure? You will have to re-scan the QR code on your authenticator application as the previous codes will stop working.', 'profilepress-pro') . "'",
                    esc_url(add_query_arg('ppress-2fa-reset', wp_create_nonce('ppmyac_disable_2fa'))),
                    Common::twofa_disable_button_text()
                );
                ?>
            </div>

        <?php if ( ! isset($_POST['ppmyac_2fa_generate_recovery_code'])) : ?>
            <p>
                <?php wp_nonce_field('ppmyac_2fa_generate_recovery_code', 'ppmyac_2fa_generate_recovery_code_nonce') ?>
                <input class="button" style="display:inline" type="submit" name="ppmyac_2fa_generate_recovery_code" value="<?= $generate_btn_label ?>">
                <?php if ( ! empty($recovery_codes_count)) : ?>
                    <span class="ppmyac-2fa-recovery-codes-count">
                    <?php echo esc_html(sprintf(_n('%s unused code remaining.', '%s unused codes remaining.', $recovery_codes_count, 'profilepress-pro'), $recovery_codes_count)) ?>
                </span>
                <?php endif; ?>
            </p>
        <?php endif; ?>

            <script type="text/javascript">
                (function ($) {
                    $('input[name="ppmyac_2fa_generate_recovery_code"]').on('click', function (e) {
                        e.preventDefault();

                        if (confirm('<?php esc_html_e('Are you sure?', 'profilepress-pro')?>')) {

                            var data,
                                btn = $(this),
                                btnText = btn.val(),
                                ajax_url = (typeof ajaxurl !== 'undefined') ? ajaxurl : '';

                            ajax_url = (typeof pp_ajax_form !== 'undefined') ? pp_ajax_form.ajaxurl : ajax_url;

                            btn.attr('disabled', 'disabled')
                                .val('<?php esc_html_e('Generating...', 'profilepress-pro'); ?>');

                            data = {
                                action: 'ppress_2fa_generate_recovery_codes',
                                ppmyac_2fa_generate_recovery_code: true,
                                user_id: <?php echo $user_id;?>,
                                ppmyac_2fa_generate_recovery_code_nonce: '<?= wp_create_nonce('ppmyac_2fa_generate_recovery_code') ?>'
                            };

                            $('.ppmyac-2fa-recovery-codes-count').hide();

                            $.post(ajax_url, data, function (response) {
                                if ('success' in response && response.success === true) {
                                    btn.parent().after(response.data)
                                }

                                btn.removeAttr('disabled');
                                btn.val(btnText);
                            });
                        }
                    });
                })(jQuery);
            </script>

        <?php else :

        $secret = Common::generate_secret();

        printf('<p>%s</p>', Common::scan_qrcode_text());
        printf('<p><img src="%s"></p>', Common::get_qrcode_image_url($secret, wp_get_current_user()));
        printf('<p><code>%s</code></p>', $secret);

        ?>
            <p>
                <input type="hidden" name="ppress_2fa_secret" value="<?= esc_attr($secret) ?>">
                <?php esc_html_e('Activation Code:', 'profilepress-pro') ?>
                <label for="ppmyac_2fa_authcode" style="display: inline-block">
                    <?php esc_html__('Authentication Code:', 'profilepress-pro'); ?>
                    <input style="width: 200px;" type="tel" name="ppmyac_2fa_authcode" id="ppmyac_2fa_authcode" class="input" size="20" pattern="[0-9]*">
                </label>
                <?php wp_nonce_field('ppmyac_2fa_complete_setup', 'ppmyac_2fa_complete_setup_nonce') ?>
                <input style="inline-block" type="submit" class="button" name="ppmyac_2fa_authcode_submit" value="<?php esc_html_e('Validate Code', 'profilepress-pro') ?>">
            </p>
        <?php endif; ?>
    </form>
</div>