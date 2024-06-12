<?php

namespace ProfilePress\Libsodium\Recaptcha;


use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\FieldBase;

class RecaptchaDNDField extends FieldBase
{
    public function field_type()
    {
        return 'pp-recaptcha';
    }

    public static function field_icon()
    {
        return '<span class="dashicons dashicons-shield-alt"></span>';
    }

    public function field_title()
    {
        return esc_html__('reCAPTCHA', 'profilepress-pro');
    }

    public function field_bar_title()
    {
        return esc_html__('reCAPTCHA', 'profilepress-pro');
    }

    public function field_settings()
    {
        $general_settings = [];
        if (Recaptcha::$type == 'v2') {
            $general_settings['theme'] = [
                'label'   => esc_html__('Theme', 'profilepress-pro'),
                'field'   => self::SELECT_FIELD,
                'options' => [
                    'light' => esc_html__('Light', 'profilepress-pro'),
                    'dark'  => esc_html__('Dark', 'profilepress-pro'),
                ]
            ];

            $general_settings['size'] = [
                'label'   => esc_html__('Size', 'profilepress-pro'),
                'field'   => self::SELECT_FIELD,
                'options' => [
                    'normal'  => esc_html__('Normal', 'profilepress-pro'),
                    'compact' => esc_html__('Compact', 'profilepress-pro'),
                ]
            ];
        }

        if ( ! empty($general_settings)) {
            $settings[parent::GENERAL_TAB] = $general_settings;
        }

        $settings[parent::STYLE_TAB] = [
            'class' => [
                'label'       => esc_html__('CSS Classes', 'profilepress-pro'),
                'field'       => self::INPUT_FIELD,
                'description' => esc_html__('Enter the CSS class names you would like to add to this field.', 'profilepress-pro')
            ]
        ];

        return apply_filters('ppress_form_builder_recaptcha_login_field_settings', $settings, $this);
    }
}