<?php

namespace ProfilePress\Libsodium\PremiumThemes\DragDrop\EditProfile;

use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\DragDropBuilder;
use ProfilePress\Core\Themes\DragDrop\AbstractBuildScratch;
use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\Fields;

class Daffodil extends AbstractBuildScratch
{
    public static function default_field_listing()
    {
        Fields\Init::init();
        $standard_fields = DragDropBuilder::get_instance()->standard_fields();

        return [
            array_merge(
                $standard_fields['edit-profile-username'],
                ['icon' => 'face']
            ),
            array_merge(
                $standard_fields['edit-profile-email'],
                ['icon' => 'email']
            ),
            array_merge(
                $standard_fields['edit-profile-password'],
                ['password_visibility_icon' => true]
            ),
            array_merge(
                $standard_fields['edit-profile-first-name'],
                ['icon' => 'perm_identity']
            ),
            array_merge(
                $standard_fields['edit-profile-last-name'],
                ['icon' => 'perm_identity']
            )
        ];
    }

    public function default_metabox_settings()
    {
        $data                                      = parent::default_metabox_settings();
        $data['buildscratch_field_layout']         = 'material';
        $data['buildscratch_label_field_size']     = 'small';
        $data['buildscratch_submit_button_layout'] = 'round';
        $data['buildscratch_submit_button_width']  = 'wide';

        return $data;
    }
}