<?php

namespace ProfilePress\Libsodium\Receipt;

class Settings
{
    public function __construct()
    {
        add_filter('ppress_payment_admin_settings', [$this, 'settings_page']);
    }

    public function settings_page($settings)
    {
        $settings[] = [
            'section_title'              => esc_html__('Receipt Settings', 'profilepress-pro'),
            'receipt_disable_free_order' => [
                'label'          => esc_html__('Disable for Free Orders', 'profilepress-pro'),
                'checkbox_label' => esc_html__('Check to Disable', 'profilepress-pro'),
                'description'    => esc_html__("Optionally disable receipt generation for free orders.", 'profilepress-pro'),
                'type'           => 'checkbox'
            ],
            'receipt_logo_url'           => [
                'label'       => esc_html__('Logo URL', 'profilepress-pro'),
                'type'        => 'text',
                'description' => esc_html__('Enter your website Logo URL to display on the receipt. Default to your site title if empty.', 'profilepress-pro')
            ],
            'receipt_additional_info'    => [
                'label'       => esc_html__('Additional Information', 'profilepress-pro'),
                'description' => esc_html__('Any text entered here will appear at the bottom of each receipt', 'profilepress-pro'),
                'type'        => 'wp_editor',
                'settings'    => ['textarea_rows' => 5, 'wpautop' => false]
            ],
            'receipt_view_button_label'  => [
                'label' => esc_html__('Receipt Button Label', 'profilepress-pro'),
                'type'  => 'text',
                'value' => esc_html__('View Receipt', 'profilepress-pro')
            ],
        ];

        return $settings;
    }

    /**
     * @return self
     */
    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}