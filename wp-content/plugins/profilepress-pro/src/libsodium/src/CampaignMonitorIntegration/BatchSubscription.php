<?php

namespace ProfilePress\Libsodium\CampaignMonitorIntegration;

use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\Membership\Repositories\CustomerRepository;

class BatchSubscription
{
    protected $initInstance;

    /**
     * @param Init $init
     */
    public function __construct($init)
    {
        $this->initInstance = $init;

        add_action('wp_ajax_pp_campaign_monitor_batch_subscribe', array($this, 'bulk_import'));
    }

    /**
     * Batch subscribe a list of user to CM list.
     * @throws \Exception
     */
    public function bulk_import()
    {
        set_time_limit(0);
        ignore_user_abort();

        check_ajax_referer('ppesp-sync-tool');

        try {

            $wp_list_id = absint($_POST['wp_list_id']);

            $selection = ppress_var($_POST, 'role', '', true);

            $payload = [
                'Resubscribe'                            => true,
                'QueueSubscriptionBasedAutoResponders'   => true,
                'RestartSubscriptionBasedAutoresponders' => false,
                'Subscribers'                            => []
            ];

            // is numeric if membership plan is selected.
            if (is_numeric($selection)) {

                $customers = CustomerRepository::init()->retrieveBySubscription($selection, [SubscriptionStatus::ACTIVE]);

                if ( ! is_array($customers) || empty($customers)) wp_send_json_success();

                $cm_list_id = false;

                foreach ($customers as $customer) {

                    $params = $this->initInstance->get_add_update_user_to_email_list_params($wp_list_id, $customer->get_wp_user());

                    $cm_list_id = $params['cm_list_id'];

                    if ( ! $params) continue;

                    $custom_fields = $params['custom_fields'];

                    $name = ppress_var($custom_fields, 'ppFirstName', '') . ' ' . ppress_var($custom_fields, 'ppLastName', '');

                    unset($custom_fields['ppFirstName']);
                    unset($custom_fields['ppLastName']);

                    $custom_fields_payload = [];

                    if ( ! empty($custom_fields) && is_array($custom_fields)) {

                        foreach ($custom_fields as $field_key => $value) {

                            if (is_array($value)) {
                                foreach ($value as $val) {
                                    $custom_fields_payload[] = [
                                        'Key'   => $field_key,
                                        'Value' => $val,
                                    ];
                                }

                                continue;
                            }

                            $custom_fields_payload[] = [
                                'Key'   => $field_key,
                                'Value' => $value
                            ];
                        }
                    }

                    $payload['Subscribers'][] = [
                        'EmailAddress'   => $params['email_address'],
                        'Name'           => trim($name),
                        'CustomFields'   => $custom_fields_payload,
                        'ConsentToTrack' => 'Unchanged'
                    ];
                }

                $payload = apply_filters('ppress_campaign_monitor_bulk_import_payload', ppress_filter_empty_array($payload), $this);

                $response = Init::api_instance()->make_request(
                    sprintf('subscribers/%s/import.json', $cm_list_id),
                    $payload,
                    'post'
                );

                $is_success = ppress_is_http_code_success($response['status_code']);

                if ( ! $is_success) {
                    throw new \Exception(json_encode($response));
                }

                wp_send_json_success();

            } else {

                $args = [];

                if ('all' != $selection) {
                    $args['role__in'] = [$selection];
                }

                $users = get_users($args);

                $user_count = count($users);

                if ($user_count < 1) wp_send_json_success();

                $cm_list_id = false;

                foreach ($users as $key => $user_data) {

                    $params = $this->initInstance->get_add_update_user_to_email_list_params($wp_list_id, $user_data);

                    $cm_list_id = $params['cm_list_id'];

                    if ( ! $params) continue;

                    $custom_fields = $params['custom_fields'];

                    $name = ppress_var($custom_fields, 'ppFirstName', '') . ' ' . ppress_var($custom_fields, 'ppLastName', '');

                    unset($custom_fields['ppFirstName']);
                    unset($custom_fields['ppLastName']);

                    $custom_fields_payload = [];

                    if ( ! empty($custom_fields) && is_array($custom_fields)) {

                        foreach ($custom_fields as $field_key => $value) {

                            if (is_array($value)) {
                                foreach ($value as $val) {
                                    $custom_fields_payload[] = [
                                        'Key'   => $field_key,
                                        'Value' => $val,
                                    ];
                                }

                                continue;
                            }

                            $custom_fields_payload[] = [
                                'Key'   => $field_key,
                                'Value' => $value
                            ];
                        }
                    }

                    $payload['Subscribers'][] = [
                        'EmailAddress'   => $params['email_address'],
                        'Name'           => trim($name),
                        'CustomFields'   => $custom_fields_payload,
                        'ConsentToTrack' => 'Unchanged'
                    ];
                }

                $payload = apply_filters('ppress_campaign_monitor_bulk_import_payload', ppress_filter_empty_array($payload), $this);

                $response = Init::api_instance()->make_request(
                    sprintf('subscribers/%s/import.json', $cm_list_id),
                    $payload,
                    'post'
                );

                $is_success = ppress_is_http_code_success($response['status_code']);

                if ( ! $is_success) {
                    throw new \Exception(json_encode($response));
                }

                wp_send_json_success();
            }

        } catch (\Exception $e) {

            Init::log_error($e->getMessage());

            wp_send_json_success();
        }
    }
}