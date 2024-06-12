<?php

namespace ProfilePress\Libsodium\TWOFA;


use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\Fields\Login\Userlogin;

class DNDLoginField extends Userlogin
{
    public function field_type()
    {
        return 'pp-2fa';
    }

    public static function field_icon()
    {
        return '<span class="dashicons dashicons-shield"></span>';
    }

    public function field_title()
    {
        return esc_html__('2FA Code', 'profilepress-pro');
    }

    public function field_bar_title()
    {
        return esc_html__('2FA Code', 'profilepress-pro');
    }
}