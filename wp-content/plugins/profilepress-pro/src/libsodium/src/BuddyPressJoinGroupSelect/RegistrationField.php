<?php

namespace ProfilePress\Libsodium\BuddyPressJoinGroupSelect;

use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\FieldBase;

class RegistrationField extends FieldBase
{
    public function field_type()
    {
        return 'pp-buddypress-groups';
    }

    public static function field_icon()
    {
        return '<span class="dashicons dashicons-buddicons-buddypress-logo"></span>';
    }

    public function field_title()
    {
        return esc_html__('BuddyPress Groups', 'profilepress-pro');
    }

    public function field_settings()
    {
        return [
            parent::GENERAL_TAB => [
                'label' => [
                    'label' => esc_html__('Label', 'profilepress-pro'),
                    'field' => self::INPUT_FIELD,
                ],
                'type' => [
                    'label' => esc_html__('Field Type', 'profilepress-pro'),
                    'field' => self::SELECT_FIELD,
                    'options' => [
                        'checkbox' => esc_html__('Checkboxes', 'profilepress-pro'),
                        'select'   => esc_html__('Select Dropdown', 'profilepress-pro')
                    ]
                ]
            ],
            parent::STYLE_TAB   => [
                'class' => [
                    'label'       => esc_html__('CSS Classes', 'profilepress-pro'),
                    'field'       => self::INPUT_FIELD,
                    'description' => esc_html__('Enter the CSS class names you would like to add to this field.', 'profilepress-pro')
                ]
            ],
        ];
    }
}