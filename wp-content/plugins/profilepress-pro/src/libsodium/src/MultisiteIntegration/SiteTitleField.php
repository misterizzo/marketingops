<?php

namespace ProfilePress\Libsodium\MultisiteIntegration;

use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\FieldBase;

class SiteTitleField extends FieldBase
{
    public function field_type()
    {
        return 'reg-site-title';
    }

    public static function field_icon()
    {
        return '<span class="dashicons dashicons-admin-comments"></span>';
    }

    public function field_title()
    {
        return esc_html__('Site Title', 'profilepress-pro');
    }

    public function field_settings()
    {
        return [
            parent::GENERAL_TAB  => [
                'placeholder' => [
                    'label' => esc_html__('Placeholder', 'profilepress-pro'),
                    'field' => self::INPUT_FIELD,

                ]
            ],
            parent::SETTINGS_TAB => [
                'required' => [
                    'type'        => 'checkbox',
                    'label'       => esc_html__('Required', 'profilepress-pro'),
                    'description' => esc_html__('Force users to fill out this field, otherwise it will be optional.', 'profilepress-pro'),
                    'field'       => self::INPUT_FIELD,
                ]
            ],
            parent::STYLE_TAB    => [
                'class' => [
                    'label'       => esc_html__('CSS Classes', 'profilepress-pro'),
                    'field'       => self::INPUT_FIELD,
                    'description' => esc_html__('Enter the CSS class names you would like to add to this field.', 'profilepress-pro')
                ]
            ],
        ];
    }
}