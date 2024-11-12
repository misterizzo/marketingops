<?php

namespace ProfilePress\Core\Themes\DragDrop;

use ProfilePress\Core\Base;
use ProfilePress\Core\Classes\FormRepository as FR;
use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Core\Membership\CheckoutFields;

class ProfileFieldListing
{
    private $form_id;
    private $field_settings = [];

    private $has_field_data = false;

    private $item_wrap_start_tag = '';
    private $item_wrap_end_tag = '';
    private $title_start_tag = '';
    private $title_end_tag = '';
    private $info_start_tag = '';
    private $info_end_tag = '';

    private $output = '';

    private $defaults;

    public function __construct($form_id)
    {
        $this->form_id = $form_id;

        $this->field_settings = FR::form_builder_fields_settings($form_id, FR::USER_PROFILE_TYPE);
    }

    public function defaults($defaults)
    {
        $this->defaults = $defaults;

        return $this;
    }

    public function item_wrap_start_tag($tag)
    {
        $this->item_wrap_start_tag = $tag;

        return $this;
    }

    public function item_wrap_end_tag($tag)
    {
        $this->item_wrap_end_tag = $tag;

        return $this;
    }

    public function title_start_tag($tag)
    {
        $this->title_start_tag = $tag;

        return $this;
    }

    public function title_end_tag($tag)
    {
        $this->title_end_tag = $tag;

        return $this;
    }

    public function info_start_tag($tag)
    {
        $this->info_start_tag = $tag;

        return $this;
    }

    public function info_end_tag($tag)
    {
        $this->info_end_tag = $tag;

        return $this;
    }

    public function has_field_data()
    {
        return $this->has_field_data;
    }

    public function forge()
    {
        $output = '';

        foreach ($this->field_settings as $field_setting) {

            $field_type = $field_setting['fieldType'];

            if (isset($this->defaults, $this->defaults[$field_type])) {
                $field_setting = wp_parse_args($field_setting, $this->defaults[$field_type]);
            }

            $field_title = isset($field_setting['label']) ? $field_setting['label'] : '';

            $field_key = '';

            if ($field_type == 'profile-cpf') {

                if ( ! empty($field_setting['field_key']) && ! empty($field_setting['label'])) {
                    $field_key = $field_setting['field_key'];

                    $field_type = $field_type . ' key="' . $field_key . '"';

                } elseif ( ! empty($field_setting['custom_field'])) {

                    $field_key = $field_setting['custom_field'];

                    $custom_field_title = PROFILEPRESS_sql::get_field_label($field_key);

                    if ( ! $custom_field_title) {
                        $custom_field_title = PROFILEPRESS_sql::get_contact_info_field_label($field_key);
                    }

                    if ( ! empty($custom_field_title)) $field_title = $custom_field_title;

                    $field_type = $field_type . ' key="' . $field_key . '"';
                }
            }

            if ($field_type == 'profile-display-name') {

                if ( ! empty($field_setting['format'])) {

                    $field_type = $field_type . ' format="' . $field_setting['format'] . '"';
                }
            }

            if ($field_type == 'pp-custom-html') {

                if ( ! empty($field_setting['custom_html'])) {

                    $field_type = $field_type . ' custom_html="' . base64_encode($field_setting['custom_html']) . '"';
                }
            }

            $parsed_shortcode = apply_filters(
                'ppress_drag_drop_profile_listing_item',
                do_shortcode('[' . $field_type . ']', true),
                $field_type, $field_key, $this
            );

            if ( ! empty($parsed_shortcode)) {

                $this->has_field_data = true;

                if ($field_type == 'profile-website') {
                    $parsed_shortcode = sprintf('<a href="%1$s" rel="nofollow" target="_blank">%1$s</a>', $parsed_shortcode);
                }

                if ( ! empty($field_key) && strpos($field_type, 'profile-cpf') !== false) {

                    if (in_array($field_key, array_keys(ppress_social_network_fields()))) {
                        $parsed_shortcode = sprintf('<a href="%s">%s</a>', $parsed_shortcode, ppress_var(ppress_social_network_fields(), $field_key));
                    }

                    $custom_field_type = PROFILEPRESS_sql::get_field_type($field_key);

                    if ($field_key == CheckoutFields::BILLING_COUNTRY || $custom_field_type == 'country') {
                        $parsed_shortcode = ppress_get_country_title($parsed_shortcode);
                    }

                    if ($field_key == CheckoutFields::BILLING_STATE) {
                        $db_country       = do_shortcode('[profile-cpf key=' . CheckoutFields::BILLING_COUNTRY . ']', true);
                        $parsed_shortcode = ppress_get_country_state_title($parsed_shortcode, $db_country);
                    }
                }

                $output .= $this->item_wrap_start_tag;
                if ( ! empty($field_title)) {
                    $output .= $this->title_start_tag . $field_title . $this->title_end_tag;
                }
                $output .= $this->info_start_tag . $parsed_shortcode . $this->info_end_tag;
                $output .= $this->item_wrap_end_tag;
            }
        }

        $this->output = $output;

        return $this;
    }

    public function output()
    {
        return $this->output;
    }
}