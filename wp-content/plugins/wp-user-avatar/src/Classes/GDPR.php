<?php

namespace ProfilePress\Core\Classes;

class GDPR
{
    public function __construct()
    {
        add_filter('wp_privacy_personal_data_exporters', [$this, 'wp_export_data']);
        add_filter('wp_privacy_personal_data_erasers', [$this, 'wp_erase_data']);
    }

    public function wp_erase_data($erasers)
    {
        $erasers['profilepress'] = [
            'eraser_friendly_name' => esc_html__('User Extra Information', 'wp-user-avatar'),
            'callback'             => [$this, 'erase_data']
        ];

        return $erasers;
    }

    public function erase_data($email_address)
    {
        $user          = get_user_by('email', $email_address);
        $user_id       = $user->ID;
        $custom_fields = ppress_custom_fields_key_value_pair(true);

        $items_removed  = false;
        $items_retained = false;

        if ( ! empty($custom_fields) && is_array($custom_fields)) {

            foreach ($custom_fields as $field_key => $field_label) {

                $get_meta_value = get_user_meta($user_id, $field_key);
                if (empty($get_meta_value)) continue;

                $deleted = delete_user_meta($user_id, $field_key);

                if ($deleted) {
                    $items_removed = true;
                } else {
                    $items_retained = true;
                }
            }
        }

        return [
            'items_removed'  => $items_removed,
            'items_retained' => $items_retained,
            'messages'       => [],
            'done'           => true,
        ];
    }

    public function wp_export_data($exporters)
    {
        $exporters[] = array(
            'exporter_friendly_name' => esc_html__('User Extra Information', 'wp-user-avatar'),
            'callback'               => function ($email_address) {

                $user    = get_user_by('email', $email_address);
                $user_id = $user->ID;

                $data_to_export = [];

                $lead_data_to_export = [];

                $x_custom_fields = ppress_custom_fields_key_value_pair(true);

                if ( ! empty($x_custom_fields) && is_array($x_custom_fields)) {

                    foreach ($x_custom_fields as $field_key => $field_label) {

                        $usermeta_value = get_user_meta($user_id, $field_key, true);

                        if ( ! empty($usermeta_value)) {
                            $lead_data_to_export[] = [
                                'name'  => $field_label,
                                'value' => get_user_meta($user_id, $field_key, true)
                            ];
                        }
                    }

                    $data_to_export[] = [
                        'group_id'    => 'profilepress',
                        'group_label' => esc_html__('User Extra Information', 'wp-user-avatar'),
                        'item_id'     => "profilepress-{$user_id}",
                        'data'        => $lead_data_to_export
                    ];
                }

                return [
                    'data' => $data_to_export,
                    'done' => true,
                ];
            }
        );

        return $exporters;
    }

    /**
     * @return GDPR
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