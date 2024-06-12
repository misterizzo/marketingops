<?php

namespace ProfilePress\Libsodium\TWOFA;

use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\TwoFactorAuth;
use WP_User;

class Common
{
    const SECRET_META_KEY = '_ppress_2fa_totp_key';
    const BACKUP_CODES_META_KEY = '_ppress_2fa_backup_codes';

    public static function invalid_2fa_code_error()
    {
        return apply_filters('ppress_invalid_2fa_code_error',
            esc_html__('Invalid Two-Factor Authentication code.', 'profilepress-pro')
        );
    }

    public static function unable_to_save_2fa_error()
    {
        return apply_filters('ppress_unable_to_save_2fa_error',
            esc_html__('Unable to save 2FA code. Re-scan the QR code and enter the code provided by your application.', 'profilepress-pro')
        );
    }

    public static function scan_qrcode_text()
    {
        return apply_filters('ppress_twofa_scan_qrcode_text',
            esc_html__('Scan the QR code or manually enter the key. Then enter the code from your authenticator app to complete setup.', 'profilepress-pro')
        );
    }

    public static function twofa_enabled_text()
    {
        return apply_filters('ppress_twofa_enabled_text', esc_html__('Two factor authentication is enabled.', 'profilepress-pro'));
    }

    public static function twofa_description_text()
    {
        return apply_filters(
            'ppress_twofa_description_text',
            esc_html__('This adds an additional layer of security to your account by requiring more than just a password to log in.', 'profilepress-pro')
        );
    }

    public static function twofa_disable_button_text()
    {
        return apply_filters('ppress_twofa_disable_button_text',
            esc_html__('Click here to disable.', 'profilepress-pro')
        );
    }

    /**
     * @return false|TwoFactorAuth
     * @throws \RobThree\Auth\TwoFactorAuthException
     */
    public static function tfa_instance()
    {
        static $cache = false;

        if (false === $cache) {

            $qrCodeProvider = new BaconQrCodeProvider(
                0,
                '#ffffff',
                '#000000',
                'svg'
            );

            $cache = new TwoFactorAuth(
                ppress_site_title(),
                6,
                30,
                'sha1',
                $qrCodeProvider
            );
        }

        return $cache;
    }

    public static function generate_secret()
    {
        try {

            return self::tfa_instance()->createSecret(80, false);

        } catch (\Exception $e) {
            ppress_log_error($e->getMessage());

            return false;
        }
    }

    /**
     * @param $secret
     * @param WP_User $user
     *
     * @return false|string
     */
    public static function get_qrcode_image_url($secret, $user)
    {
        try {

            return self::tfa_instance()->getQRCodeImageAsDataUri(
                ppress_site_url_without_scheme(),
                $secret
            );

        } catch (\Exception $e) {
            ppress_log_error($e->getMessage());

            return false;
        }
    }

    public static function verify_auth_code($code, $secret)
    {
        try {

            return self::tfa_instance()->verifyCode($secret, $code);

        } catch (\Exception $e) {
            ppress_log_error($e->getMessage());

            return false;
        }
    }

    public static function verify_backup_code($user_id, $code)
    {
        $backup_codes = get_user_meta($user_id, self::BACKUP_CODES_META_KEY, true);

        if (is_array($backup_codes) && ! empty($backup_codes)) {

            foreach ($backup_codes as $code_index => $code_hashed) {

                if (wp_check_password($code, $code_hashed, $user_id)) {

                    $backup_codes = array_flip($backup_codes);
                    unset($backup_codes[$code_hashed]);
                    $backup_codes = array_values(array_flip($backup_codes));
                    update_user_meta($user_id, self::BACKUP_CODES_META_KEY, $backup_codes);

                    return true;
                }
            }
        }

        return false;
    }

    public static function generate_code()
    {
        return implode('-', str_split(substr(strtoupper(md5(time() . rand(1000, 9999))), 0, 15), 5));
    }

    public static function generate_recovery_codes($user_id)
    {
        $codes        = [];
        $codes_hashed = [];

        for ($i = 0; $i < 8; $i++) {
            $code           = self::generate_code();
            $codes_hashed[] = wp_hash_password($code);
            $codes[]        = $code;
            unset($code);
        }

        update_user_meta($user_id, self::BACKUP_CODES_META_KEY, $codes_hashed);

        return $codes;
    }

    public static function get_secret_code($user_id)
    {
        return get_user_meta($user_id, self::SECRET_META_KEY, true);
    }

    public static function get_recovery_codes($user_id)
    {
        return get_user_meta($user_id, self::BACKUP_CODES_META_KEY, true);
    }

    public static function codes_remaining_for_user($user_id)
    {
        $backup_codes = self::get_recovery_codes($user_id);

        if (is_array($backup_codes) && ! empty($backup_codes)) {
            return count($backup_codes);
        }

        return 0;
    }

    public static function set_user_2fa_secret($user_id, $key)
    {
        return update_user_meta($user_id, self::SECRET_META_KEY, $key);
    }

    public static function can_configure_2fa($user_id = null)
    {
        $user_id = is_null($user_id) ? get_current_user_id() : $user_id;

        $roles = array_filter(ppress_settings_by_key('2fa_user_roles', []));

        $user = get_user_by('id', $user_id);

        if ( ! $user) return false;

        return empty($roles) || ! empty(array_intersect($roles, $user->roles));
    }

    public static function is_user_2fa_required($user_id)
    {
        $roles = array_filter(ppress_settings_by_key('2fa_enforce_user_roles', []));

        $user = get_user_by('id', $user_id);

        if ( ! $user) return false;

        return ! empty(array_intersect($roles, $user->roles));
    }

    public static function has_2fa_configured($user_id)
    {
        $secret = self::get_secret_code($user_id);

        return ! empty($secret);
    }

    public static function is_user_2fa_enforced($user_id)
    {
        return Common::can_configure_2fa($user_id) &&
               ! Common::has_2fa_configured($user_id) &&
               Common::is_user_2fa_required($user_id);
    }

    public static function disable_2fa($user_id)
    {
        delete_user_meta($user_id, self::BACKUP_CODES_META_KEY);

        return delete_user_meta($user_id, self::SECRET_META_KEY);
    }
}