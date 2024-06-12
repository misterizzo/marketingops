<?php

namespace ProfilePress\Libsodium\TWOFA;

abstract class AbstractClass
{
    const NOTICES_META_KEY = '_ppress_2fa_notices';

    const INVALID_AUTH_CODE = 1;
    const SECRET_KEY_FAILURE_CODE = 2;

    public function __construct()
    {
        static $cache = null;

        if (is_null($cache)) {
            $cache = true;
            add_action('wp', [$this, 'complete_otp_setup']);
            add_action('wp', [$this, 'disable_user_2fa_handler']);
        }
    }

    public static function twofa_setup_page_content($user_id = null)
    {
        $user_id = is_null($user_id) ? get_current_user_id() : $user_id;

        if ( ! Common::can_configure_2fa($user_id)) {
            do_action('ppress_can_configure_2fa_is_false', $user_id);

            return '';
        }
        ob_start();
        require dirname(__FILE__) . '/myacc-2fa-view.php';

        return ob_get_clean();
    }

    public function complete_otp_setup()
    {
        if (is_admin()) return;
        if ( ! isset($_POST['ppress_2fa_secret'])) return;
        if ( ! isset($_POST['ppmyac_2fa_authcode_submit'])) return;

        check_admin_referer('ppmyac_2fa_complete_setup', 'ppmyac_2fa_complete_setup_nonce');

        $secret_code          = sanitize_text_field($_POST['ppress_2fa_secret']);
        $activation_auth_code = (string)absint($_POST['ppmyac_2fa_authcode']);

        if ( ! Common::verify_auth_code($activation_auth_code, $secret_code)) {
            wp_safe_redirect(esc_url_raw(add_query_arg('2fa-status', self::INVALID_AUTH_CODE)));
            exit;
        }

        if ( ! Common::set_user_2fa_secret(get_current_user_id(), $secret_code)) {
            wp_safe_redirect(esc_url_raw(add_query_arg('2fa-status', self::SECRET_KEY_FAILURE_CODE)));
            exit;
        }

        wp_safe_redirect(
            esc_url_raw(
                remove_query_arg('2fa-status', ppress_get_current_url_query_string())
            )
        );
        exit;
    }

    public static function disable_user_2fa_handler()
    {
        if (is_admin()) return;

        if ( ! isset($_GET['ppress-2fa-reset'])) return;

        check_admin_referer('ppmyac_disable_2fa', 'ppress-2fa-reset');

        $user_id = get_current_user_id();

        Common::disable_2fa($user_id);

        wp_safe_redirect(esc_url_raw(remove_query_arg('ppress-2fa-reset')));
        exit;
    }

    public static function display_status_messages()
    {
        $type = $message = '';

        if ( ! isset($_GET['2fa-status'])) return;

        switch ($_GET['2fa-status']) {
            case self::INVALID_AUTH_CODE:
                $type    = 'danger';
                $message = esc_html__('Invalid Two-Factor Authentication code.', 'profilepress-pro');
                break;
            case self::SECRET_KEY_FAILURE_CODE:
                $type    = 'danger';
                $message = Common::unable_to_save_2fa_error();
                break;
        }

        if (empty($type) || empty($message)) return;

        ?>
        <div class="profilepress-myaccount-alert pp-alert-<?= $type ?>" role="alert">
            <?= $message ?>
        </div>
        <?php
    }

    public static function download_link($codes)
    {
        $data = 'data:application/text;charset=utf-8,' . "\n";
        $data .= rawurlencode(sprintf(esc_html__('Two-Factor Backup Codes for %s', 'profilepress-pro'), ppress_site_title())) . "\n\n";

        foreach ($codes as $code) {
            $data .= $code . "\n";
        }

        return $data;
    }

    public static function generate_recovery_codes_html($user_id = null)
    {
        if ( ! isset($_POST['ppmyac_2fa_generate_recovery_code'])) return;

        if (wp_doing_ajax()) {
            check_ajax_referer('ppmyac_2fa_generate_recovery_code', 'ppmyac_2fa_generate_recovery_code_nonce');
        } else {
            check_admin_referer('ppmyac_2fa_generate_recovery_code', 'ppmyac_2fa_generate_recovery_code_nonce');
        }

        $user_id = is_null($user_id) ? get_current_user_id() : $user_id;

        if ( ! current_user_can('edit_user', $user_id)) return;

        $codes = Common::generate_recovery_codes($user_id);
        echo '<p>';
        esc_html_e('Please save the following recovery codes in a safe place. You will use the recovery codes to access your account in the event you cannot receive two-factor authentication codes.', 'profilepress-pro');
        echo '</p>';

        printf('<p><strong>%s</strong></p>', esc_html__('Write these down! Once you navigate away from this page, you will not be able to view these codes again.', 'profilepress-pro'));

        echo '<div class="ppmyac-2fa-recovery-codes-wrap"><pre>';
        echo implode("\r\n", $codes);
        echo '</pre></div>';

        echo '<p>';
        printf(
            '<a href="%s" download="%s">%s</a>',
            self::download_link($codes),
            apply_filters('ppress_2fa_download_file_name', 'two-fa-recovery-codes', $user_id) . '.txt',
            esc_html__('Download Codes', 'profilepress-pro')
        );
        echo '</p>';
    }
}