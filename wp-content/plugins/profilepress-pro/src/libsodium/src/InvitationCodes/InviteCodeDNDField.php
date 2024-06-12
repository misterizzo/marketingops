<?php

namespace ProfilePress\Libsodium\InvitationCodes;

use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\FieldBase;

class InviteCodeDNDField extends FieldBase
{
    public function field_type()
    {
        return 'pp-invite-code';
    }

    public static function field_icon()
    {
        return '<img src="' . PROFILEPRESS_PRO_LIBSODIUM_ASSETS_URL . 'invite-code.svg' . '">';
    }

    public function field_title()
    {
        return esc_html__('Invite Code', 'profilepress-pro');
    }

    public function field_settings()
    {
        return [
            parent::GENERAL_TAB => [
                'placeholder' => [
                    'label' => esc_html__('Placeholder', 'profilepress-pro'),
                    'field' => self::INPUT_FIELD,

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