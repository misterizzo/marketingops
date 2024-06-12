<?php

namespace ProfilePress\Libsodium\CustomProfileFields;

use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Core\Classes\WPProfileFieldParserTrait;
use ProfilePress\Core\Membership\CheckoutFields;

class WPUserProfileCustomField
{
    use WPProfileFieldParserTrait;

    public function __construct()
    {
        add_action('show_user_profile', array($this, 'add_profile_fields_to_user_profile'), 2);
        add_action('edit_user_profile', array($this, 'add_profile_fields_to_user_profile'), 2);

        // profile update is handled by ProfileCustomFields in core.
    }

    public function add_profile_fields_to_user_profile($user)
    {
        $custom_fields = PROFILEPRESS_sql::get_profile_custom_fields();

        if ( ! $custom_fields || empty($custom_fields)) return;

        echo '<h3>' . apply_filters('ppress_custom_field_header', esc_html__('Other Information', 'profilepress-pro')) . '</h3>';

        echo '<table class="form-table">';

        foreach ($custom_fields as $extra_field) {

            $field_key   = $extra_field['field_key'];
            $label_name  = $extra_field['label_name'];
            $field_type  = $extra_field['type'];
            $options     = $extra_field['options'];
            $description = $extra_field['description'];

            if (in_array($field_key, array_keys(CheckoutFields::standard_billing_fields()))) continue;

            // skip woocommerce core billing / shipping fields added to wordpress profile admin page.
            if (in_array($field_key, ppress_woocommerce_billing_shipping_fields())) continue;

            if (in_array($field_key, $this->core_user_fields())) continue;

            $this->parse_custom_field($user, $label_name, $field_key, $field_type, $options, $description);
        }
        echo '</table>';
    }

    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}