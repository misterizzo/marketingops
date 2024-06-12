<?php

namespace ProfilePress\Libsodium\MailchimpIntegration;

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

        add_action('wp_ajax_pp_mailchimp_batch_subscribe', [$this, 'mc_batch_subscribe']);
    }

    /**
     * Batch subscribe a list of user to Mailchimp list.
     * @see https://apidocs.mailchimp.com/api/2.0/lists/batch-subscribe.php
     * @throws \Exception
     */
    public function mc_batch_subscribe()
    {
        set_time_limit(0);
        ignore_user_abort();

        check_ajax_referer('ppesp-sync-tool');

        try {

            $Batch = Init::api_instance()->new_batch();

            $wp_audience_id = absint($_POST['wp_list_id']);

            $limit  = 500;
            $offset = 0;
            $loop   = true;

            $selection = ppress_var($_POST, 'role', '', true);

            // is numeric if membership plan is selected.
            if (is_numeric($selection)) {

                while ($loop === true) {

                    $customers = CustomerRepository::init()->retrieveBySubscription($selection, [SubscriptionStatus::ACTIVE], [
                        'number' => $limit,
                        'offset' => $offset
                    ]);

                    if (is_array($customers) && ! empty($customers)) {

                        foreach ($customers as $key => $customer) {
                            $this->initInstance->add_to_batch_collection($Batch, $key, $wp_audience_id, $customer->get_wp_user());
                        }

                        $Batch->execute();

                        if (count($customers) < $limit) {
                            $loop = false;
                            wp_send_json_success();
                        }

                        $offset += $limit;

                    } else {
                        $loop = false;
                        wp_send_json_success();
                    }
                }

            } else {

                $args = [];

                if ('all' != $selection) $args['role__in'] = [$selection];

                $limit = 500;
                $page  = 1;
                $loop  = true;

                while ($loop === true) {

                    $args['number'] = $limit;
                    $args['paged']  = $page;

                    $users = get_users($args);

                    if (is_array($users) && ! empty($users)) {

                        foreach ($users as $key => $user_data) {
                            $this->initInstance->add_to_batch_collection($Batch, $key, $wp_audience_id, $user_data);
                        }

                        $Batch->execute();

                        if (count($users) < $limit) {
                            $loop = false;
                            wp_send_json_success();
                        }

                        $page++;

                    } else {
                        $loop = false;
                        wp_send_json_success();
                    }
                }
            }

            throw new \Exception(json_encode(Init::api_instance()->getLastResponse()));

        } catch (\Exception $e) {

            Init::log_error($e->getMessage());

            wp_send_json_error();
        }
    }
}