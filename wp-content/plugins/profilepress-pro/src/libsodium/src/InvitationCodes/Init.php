<?php

namespace ProfilePress\Libsodium\InvitationCodes;

use ProfilePress\Core\Base;
use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Classes\FormRepository;
use ProfilePressVendor\Carbon\CarbonImmutable;

define('PPRESS_INVITE_CODE_SETTINGS_PAGE', add_query_arg('view', 'invite-codes', PPRESS_SETTINGS_SETTING_PAGE));

class Init
{
    public static $instance_flag = false;

    public function __construct()
    {
        new Admin();

        add_action('ppress_drag_drop_builder_field_init_after', function ($form_type) {
            if (FormRepository::REGISTRATION_TYPE == $form_type) {
                new InviteCodeDNDField();
            }
        });

        add_shortcode('pp-invite-code', [$this, 'field_shortcode_callback']);

        add_filter('ppress_registration_validation', [$this, 'validate_invite_code'], 999999, 3);

        add_action('ppress_after_registration', [$this, 'save_invite_code_used'], 10, 3);
    }

    public static function is_date_expired($date)
    {
        return ! empty($date) && CarbonImmutable::parse($date, wp_timezone())->isPast();
    }

    public static function get_all_invite_codes()
    {
        global $wpdb;

        $table = Base::meta_data_db_table();

        $sql = "SELECT flag FROM $table WHERE meta_key = %s";

        return $wpdb->get_col($wpdb->prepare($sql, 'invite_code'));
    }

    public function field_shortcode_callback($atts)
    {
        if (empty($atts)) $atts = [];

        if ( ! empty($_GET['reg_invite_code'])) {
            $atts['value'] = esc_attr($_GET['reg_invite_code']);
        }

        $array = $atts + ['placeholder' => esc_html__('Invite Code', 'profilepress-pro')];

        $attributes = array_reduce(array_keys($array), function ($carry, $key) use ($array) {
            return $carry . "$key=\"" . esc_attr($array[$key]) . '" ';
        }, '');

        $attributes = trim($attributes);

        return "<input name='reg_invite_code' type='text' $attributes>";
    }

    public function validate_invite_code($reg_errors, $form_id, $user_data)
    {
        if (ppress_form_has_field($form_id, FormRepository::REGISTRATION_TYPE, 'pp-invite-code')) {

            $submitted_code = trim(ppressPOST_var('reg_invite_code', ''));

            if (empty($submitted_code)) {

                $reg_errors->add(
                    'invite_code_empty',
                    apply_filters(
                        'ppress_invite_code_empty_error_message',
                        __('Invite code was not provided.', 'profilepress-pro'),
                        $user_data, $form_id
                    )
                );

                return $reg_errors;
            }

            $invite_code = new InviteCodeEntity($submitted_code);

            if ( ! $invite_code->exists()) {
                $reg_errors->add(
                    'invite_code_invalid',
                    apply_filters(
                        'ppress_invite_code_invalid_error_message',
                        __('Invalid invite code. Please try again.', 'profilepress-pro'),
                        $user_data, $form_id
                    )
                );

                return $reg_errors;
            }

            if ($invite_code->is_expired()) {
                $reg_errors->add(
                    'invite_code_expired',
                    apply_filters(
                        'ppress_invite_code_expired_error_message',
                        __('Invite code has expired. Please try again.', 'profilepress-pro'),
                        $user_data, $form_id
                    )
                );

                return $reg_errors;
            }

            if ($invite_code->is_exceeded_limit()) {
                $reg_errors->add(
                    'invite_code_exceeded_limit',
                    apply_filters(
                        'ppress_invite_code_exceeded_limit_error_message',
                        __('Invite code usage-limit exceeded. Please try again.', 'profilepress-pro'),
                        $user_data, $form_id
                    )
                );

                return $reg_errors;
            }
        }

        return $reg_errors;
    }

    public function save_invite_code_used($form_id, $user_data, $user_id)
    {
        $submitted_code = trim(ppressPOST_var('reg_invite_code', ''));

        if ( ! empty($submitted_code)) {
            \update_user_meta($user_id, 'ppress_invite_code', $submitted_code);
            (new InviteCodeEntity($submitted_code))->subscribe_to_membership($user_id);
        }
    }

    /**
     * @return self|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::INVITATION_CODES')) return;

        if ( ! EM::is_enabled(EM::INVITATION_CODES)) return;

        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}