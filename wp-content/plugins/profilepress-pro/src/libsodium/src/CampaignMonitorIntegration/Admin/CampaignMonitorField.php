<?php

namespace ProfilePress\Libsodium\CampaignMonitorIntegration\Admin;

use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\FieldBase;

class CampaignMonitorField extends FieldBase
{
    public function field_type()
    {
        return 'pp-campaignmonitor';
    }

    public static function field_icon()
    {
        return '<span class="dashicons dashicons-email-alt2"></span>';
    }

    public function field_title()
    {
        return esc_html__('Campaign Monitor', 'profilepress-pro');
    }

    public function field_settings()
    {
        return [
            parent::GENERAL_TAB => [
                'list_id'   => [
                    'label'   => esc_html__('Select List', 'profilepress-pro'),
                    'field'   => self::SELECT_FIELD,
                    'options' => SettingsPage::email_list_select_options(),
                ],
                'checkbox_text' => [
                    'label'       => esc_html__('Checkbox Text', 'profilepress-pro'),
                    'description' => esc_html__('Text that is shown beside the checkbox. Defaults to the list title if empty.', 'profilepress-pro'),
                    'field'       => self::INPUT_FIELD
                ],
                'checked'       => [
                    'label'  => esc_html__('Checked by Default', 'profilepress-pro'),
                    'options' => [
                        'false' => esc_html__('False', 'profilepress-pro'),
                        'true'  => esc_html__('True', 'profilepress-pro'),
                    ],
                    'field'  => self::SELECT_FIELD
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