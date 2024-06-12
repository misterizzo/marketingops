<?php

namespace ProfilePress\Libsodium\CampaignMonitorIntegration;

use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Libsodium\CampaignMonitorIntegration\Admin\SettingsPage;
use ProfilePress\Libsodium\Traits;

class Init
{
    use Traits;

    public static $instance_flag = false;

    public function __construct()
    {
        SettingsPage::get_instance();
        new MembershipIntegration($this);
        new BatchSubscription($this);

        add_action('admin_enqueue_scripts', array($this, 'email_subscription_js'));
        add_action('admin_footer', function () {
            $this->admin_js_script('cm');
        });

        add_shortcode('pp-campaignmonitor', array($this, 'cm_checkbox'));

        add_filter('ppmyac_email_notification_endpoint_content', [$this, 'my_account_content']);

        add_action('ppress_after_registration', array($this, 'post_registration_action'), 10, 2);

        add_action('ppress_after_social_signup', array($this, 'subscribe_after_social_login'));

        add_action('wp', [$this, 'process_myaccount_list_update']);

        $this->campaign_monitor_sync();
    }

    public function campaign_monitor_sync()
    {
        if ('true' != ppress_settings_by_key('cm_auto_sync')) return;

        add_action('delete_user', function ($user_id) {

            $email = get_userdata($user_id)->user_email;

            foreach ($this->get_email_lists() as $email_list) {

                $cm_list_id = $email_list['meta_value']['cm_email_list_select'];

                if ($this->is_user_subscribed($email, $cm_list_id)) {

                    $this->unsubscribe_user($email, $cm_list_id);
                }
            }
        });

        add_action('profile_update', function ($user_id, \WP_User $old_user_data) {
            $user_data = get_userdata($user_id);
            $this->detect_update_and_sync($user_data, $old_user_data->user_email);
        }, 10, 2);

        add_filter('ppress_after_profile_update', function ($user_data, $form_id, \WP_User $old_user_data) {
            $this->detect_update_and_sync($user_data, $old_user_data->user_email);
        }, 10, 3);

        do_action('ppress_campaign_monitor_sync', $this);
    }

    /**
     * @return bool|APIClass
     * @throws \Exception
     */
    public static function api_instance()
    {
        static $instance = false;

        if ( ! $instance) {

            $apiKey = trim(ppress_settings_by_key('cm_api_key', ''));

            if (empty($apiKey)) {
                throw new \Exception('API key not found');
            }

            $instance = new APIClass($apiKey);
        }

        return $instance;
    }

    /**
     * @throws \Exception
     */
    public static function check_api_key()
    {
        $apiKey   = trim(ppress_settings_by_key('cm_api_key', ''));
        $clientID = trim(ppress_settings_by_key('cm_client_id', ''));

        $response = (new APIClass($apiKey))->make_request(sprintf('clients/%s/lists.json', $clientID));

        if ( ! ppress_is_http_code_success($response['status_code'])) {
            throw new \Exception('API key or Client ID is invalid');
        }

        return true;
    }

    public static function is_api_key_valid()
    {
        static $status = false;

        if ( ! $status) {

            try {

                self::check_api_key();

                $status = true;

            } catch (\Exception $e) {
                // clear cache if api key is invalid
                delete_transient('ppress_cm_get_email_list');
            }
        }

        return $status;
    }

    public function get_email_lists()
    {
        $meta_data = PROFILEPRESS_sql::get_meta_data_by_key('cm_email_list');

        if ( ! $meta_data) $meta_data = [];

        return array_filter($meta_data, function ($email_list) {
            return isset($email_list['meta_value']['cm_email_list_enable']) && $email_list['meta_value']['cm_email_list_enable'] == 'true';
        });
    }

    public static function get_list_custom_fields($cm_list_id)
    {
        $cache_key = 'ppress_cm_get_list_custom_fields_' . $cm_list_id;

        $list_fields = get_transient($cache_key);

        if (empty($list_fields) || false === $list_fields) {

            $list_fields = [
                'ppFirstName' => esc_html__('First Name', 'profilepress-pro'),
                'ppLastName'  => esc_html__('Last Name', 'profilepress-pro')
            ];

            try {

                $response = self::api_instance()->make_request(sprintf('lists/%s/customfields.json', $cm_list_id));

                if ( ! ppress_is_http_code_success($response['status_code'])) {
                    throw new \Exception(json_encode($response));
                }

                $fields = ppress_var($response, 'body', []);

                if ( ! empty($fields)) {
                    foreach ($fields as $field) {
                        $fieldKey               = str_replace(['[', ']'], '', $field['Key']);
                        $list_fields[$fieldKey] = $field['FieldName'];
                    }

                    set_transient($cache_key, $list_fields, HOUR_IN_SECONDS);
                }


            } catch (\Exception $e) {
                self::log_error($e->getMessage());
            }
        }

        return $list_fields;
    }

    public static function get_cm_list_data($cm_list_id, $field = false)
    {
        $email_list = Init::get_cm_email_list();

        $list_data = $field ? '' : [];

        if (is_array($email_list) && ! empty($email_list)) {

            $data = wp_list_filter(Init::get_cm_email_list(), ['id' => $cm_list_id]);

            if ( ! empty($data)) $list_data = array_values($data)[0];

            if ($field !== false && isset($list_data[$field])) {
                $list_data = $list_data[$field];
            }
        }

        return $list_data;
    }

    public static function is_automatic_add_user_email_list($wp_list_id)
    {
        $email_list_data = PROFILEPRESS_sql::get_meta_value($wp_list_id, 'cm_email_list');

        return ppress_var($email_list_data, 'cm_email_list_automatic_add_user') == 'true';
    }

    public function is_email_list_enabled($wp_list_id)
    {
        $email_list_data = PROFILEPRESS_sql::get_meta_value($wp_list_id, 'cm_email_list');

        return ppress_var($email_list_data, 'cm_email_list_enable') == 'true';
    }

    public static function get_list_subscribers_count($cm_list_id)
    {
        $count = 0;

        try {

            $response = self::api_instance()->make_request(sprintf('lists/%s/stats.json', $cm_list_id));

            if (ppress_is_http_code_success($response['status_code'])) {
                $count = ppress_var($response['body'], 'TotalActiveSubscribers', 0);
            }

        } catch (\Exception $e) {
            self::log_error($e->getMessage());
        }

        return $count;
    }

    public static function get_cm_email_list()
    {
        $cache_key = 'ppress_cm_get_email_list';

        $lists_array = get_transient($cache_key);

        if (empty($lists_array) || false === $lists_array) {

            $lists_array = [];

            try {

                $clientID = trim(ppress_settings_by_key('cm_client_id', ''));

                $response = self::api_instance()->make_request(sprintf('clients/%s/lists.json', $clientID));

                if (ppress_is_http_code_success($response['status_code'])) {

                    $lists = ppress_var($response, 'body', []);

                    if ( ! empty($lists)) {
                        foreach ($lists as $list) {
                            $lists_array[] = [
                                'id'           => $list['ListID'],
                                'name'         => $list['Name'],
                                'member_count' => self::get_list_subscribers_count($list['ListID'])
                            ];
                        }

                        set_transient($cache_key, $lists_array, HOUR_IN_SECONDS);
                    }
                } else {
                    self::log_error($response);
                }

            } catch (\Exception $e) {
                self::log_error($e->getMessage());
            }
        }

        return $lists_array;
    }

    /**
     * @param $cm_list_id
     * @param $email_address
     * @param array $custom_fields
     * @param bool|string $old_email_address
     *
     * @return bool|string
     */
    public function sync_to_list($cm_list_id, $email_address, $custom_fields = [], $old_email_address = false)
    {
        try {

            $name = ppress_var($custom_fields, 'ppFirstName', '') . ' ' . ppress_var($custom_fields, 'ppLastName', '');

            unset($custom_fields['ppFirstName']);
            unset($custom_fields['ppLastName']);

            if ( ! empty($custom_fields) && is_array($custom_fields)) {

                $custom_fields_payload = [];

                foreach ($custom_fields as $key => $value) {

                    if (is_array($value)) {
                        foreach ($value as $val) {
                            $custom_fields_payload[] = [
                                'Key'   => $key,
                                'Value' => $val,
                            ];
                        }

                        continue;
                    }

                    $custom_fields_payload[] = [
                        'Key'   => $key,
                        'Value' => $value,
                    ];
                }
            }

            $parameters = [
                'EmailAddress'                           => $email_address,
                'Name'                                   => trim($name),
                'CustomFields'                           => ppress_filter_empty_array($custom_fields_payload),
                'Resubscribe'                            => true,
                'ConsentToTrack'                         => 'Unchanged',
                'RestartSubscriptionBasedAutoresponders' => true
            ];

            $parameters = apply_filters('ppress_campaign_monitor_subscription_parameters', ppress_filter_empty_array($parameters), $this);

            if ( ! empty($old_email_address) && $email_address != $old_email_address) {

                $response = self::api_instance()->make_request(
                    sprintf('subscribers/%s.json?email=%s', $cm_list_id, $old_email_address),
                    $parameters,
                    'put'
                );
            } else {

                $response = self::api_instance()->make_request(
                    sprintf('subscribers/%s.json', $cm_list_id),
                    $parameters,
                    'post'
                );
            }

            $is_success = ppress_is_http_code_success($response['status_code']);

            if ( ! $is_success) {
                throw new \Exception(json_encode($response));
            }

            return $is_success;

        } catch (\Exception $e) {
            self::log_error($e->getMessage());

            return false;
        }
    }

    public function unsubscribe_user($email_address, $cm_list_id)
    {
        try {

            $response = self::api_instance()->make_request(
                sprintf('subscribers/%s/unsubscribe.json', $cm_list_id),
                ['EmailAddress' => $email_address],
                'post'
            );

            $is_success = ppress_is_http_code_success($response['status_code']);

            if ( ! $is_success) {
                throw new \Exception(json_encode($response));
            }

            return $is_success;

        } catch (\Exception $e) {
            self::log_error($e->getMessage());

            return false;
        }
    }

    public function get_add_update_user_to_email_list_params($wp_list_id, $user_data = [])
    {
        if (empty($user_data) && is_user_logged_in()) {
            $user_data = wp_get_current_user();
        }

        $email_list_data = PROFILEPRESS_sql::get_meta_value($wp_list_id, 'cm_email_list');

        if (ppress_var($email_list_data, 'cm_email_list_enable') != 'true') return false;

        $cm_list_id = $email_list_data['cm_email_list_select'];

        if (empty($cm_list_id)) return false;

        $custom_fields = [];

        $map_fields = ppress_var($email_list_data, 'cm_email_list_fields_map', []);

        if ( ! empty($map_fields)) {

            foreach ($map_fields as $merge_tag => $user_data_id) {

                $old_user_data_id = $user_data_id;
                if (strpos($user_data_id, 'ppdate_') !== false) {
                    $user_data_id = str_replace('ppdate_', '', $user_data_id);
                }

                if (is_object($user_data)) {
                    $field_value = ppress_var_obj($user_data, $user_data_id, '', true);
                } else {
                    $field_value = ppress_var($user_data, $user_data_id, '', true);
                }

                if (is_array($field_value)) {
                    $custom_fields[$merge_tag] = [];
                    foreach ($field_value as $val) {
                        $custom_fields[$merge_tag][] = esc_html($val);
                    }
                    continue;
                }

                if (strpos($old_user_data_id, 'ppdate_') !== false) {
                    $field_value = gmdate("Y/m/d", ppress_strtotime_utc($field_value));
                }

                $custom_fields[$merge_tag] = $field_value;
            }
        }

        $custom_fields = ppress_filter_empty_array($custom_fields);

        $email_address = is_object($user_data) ? $user_data->user_email : $user_data['user_email'];

        return [
            'custom_fields' => $custom_fields,
            'email_address' => $email_address,
            'cm_list_id'    => $cm_list_id,
        ];
    }

    public function add_update_user_to_email_list($wp_list_id, $user_data = [], $old_email_address = false)
    {
        $params = $this->get_add_update_user_to_email_list_params($wp_list_id, $user_data);

        if ( ! $params) return false;

        return $this->sync_to_list(
            $params['cm_list_id'],
            $params['email_address'],
            $params['custom_fields'],
            $old_email_address
        );
    }

    /**
     * Callback function to validate checkbox and add user to list if checked
     *
     * @param int $form_id
     * @param array $user_data
     */
    public function post_registration_action($form_id, $user_data)
    {
        foreach ($this->get_email_lists() as $email_list) {

            if (ppress_var($email_list['meta_value'], 'cm_email_list_automatic_add_user') == 'true') {

                $this->add_update_user_to_email_list($email_list['id'], $user_data);
            }
        }

        if ( ! isset($_POST['ppress_campaign_monitor']) || ! is_array($_POST['ppress_campaign_monitor'])) return;

        foreach ($_POST['ppress_campaign_monitor'] as $wp_list_id => $status) {

            if ('true' != $status) continue;

            $this->add_update_user_to_email_list($wp_list_id, $user_data);
        }
    }

    /**
     * Detect change in user profile and then update the user CM data.
     *
     * @param array $user_data
     * @param bool $old_email_address
     */
    public function detect_update_and_sync($user_data = [], $old_email_address = false)
    {
        foreach ($this->get_email_lists() as $email_list) {

            $wp_list_id = $email_list['id'];

            $user_email = wp_get_current_user()->user_email;

            $cm_list_id = $email_list['meta_value']['cm_email_list_select'];

            if ( ! $this->is_user_subscribed($user_email, $cm_list_id)) continue;

            $this->add_update_user_to_email_list($wp_list_id, $user_data, $old_email_address);
        }
    }

    /**
     * Add registering users via social login to email list.
     *
     * @param $user_data
     */
    public function subscribe_after_social_login($user_data)
    {
        foreach ($this->get_email_lists() as $email_list) {

            if (ppress_var($email_list['meta_value'], 'cm_email_list_automatic_add_user') == 'true') {

                $this->add_update_user_to_email_list($email_list['id'], $user_data);
            }
        }
    }

    /**
     * CHeck if an email has been subscribe to an email list.
     *
     * @param string $email
     * @param string $cm_list_id
     * @param bool $cache
     *
     * @return bool
     */
    public function is_user_subscribed($email, $cm_list_id, $cache = false)
    {
        $cache_key = "ppcm_is_user_subscribed_{$email}_{$cm_list_id}";

        $status = get_transient($cache_key);

        if ( ! $cache || ($cache === true && $status === false)) {

            $status = 'false';

            try {

                $response = self::api_instance()->make_request(sprintf('subscribers/%s.json?email=%s', $cm_list_id, $email));

                if (ppress_var($response['body'], 'State') == 'Active') {
                    $status = 'true';
                }

                if ($cache === true) set_transient($cache_key, $status, HOUR_IN_SECONDS);

            } catch (\Exception $e) {
                self::log_error($e->getMessage());
            }
        }

        return $status == 'true';
    }

    public function process_myaccount_list_update()
    {
        if ( ! isset($_POST['ppmyac_campaign_monitor_update']) || ! isset($_POST['ppress_campaign_monitor'])) return;

        if ( ! is_user_logged_in()) return;

        if ( ! ppress_verify_nonce()) return;

        foreach ($_POST['ppress_campaign_monitor'] as $wp_list_id => $status) {
            $user_email      = wp_get_current_user()->user_email;
            $email_list_data = PROFILEPRESS_sql::get_meta_value($wp_list_id, 'cm_email_list');
            $cm_list_id      = $email_list_data['cm_email_list_select'];

            delete_transient("ppcm_is_user_subscribed_{$user_email}_{$cm_list_id}");

            if ('yes' == $status) {

                if ($this->is_user_subscribed($user_email, $cm_list_id)) continue;

                $this->add_update_user_to_email_list($wp_list_id);
            }

            if ('no' == $status) {

                if ( ! $this->is_user_subscribed($user_email, $cm_list_id)) continue;

                $this->unsubscribe_user($user_email, $cm_list_id);
            }
        }

        delete_transient("ppcm_is_user_subscribed_{$user_email}_{$cm_list_id}");

        wp_safe_redirect(esc_url_raw(add_query_arg('edit', 'true')));
        exit;
    }

    /**
     * Shortcode checkbox.
     *
     * @param array $atts shortcodes attributes
     *
     * @return string
     */
    public function cm_checkbox($atts)
    {
        $atts = shortcode_atts([
            'class'         => '',
            'list_id'       => '',
            'checkbox_text' => '',
            'checked'       => 'false'
        ], $atts);

        if (empty($atts['list_id']) || absint($atts['list_id']) == 0) return esc_html__('List ID not found', 'profilepress-pro');

        $wp_list_id = absint($atts['list_id']);

        if ( ! $this->is_email_list_enabled($wp_list_id)) return '';

        if (self::is_automatic_add_user_email_list($wp_list_id)) return '';

        $checked = $atts['checked'] == 'true' ? 'checked=checked' : '';

        if (isset($_POST['ppress_campaign_monitor'][$wp_list_id])) {
            $checked = checked($_POST['ppress_campaign_monitor'][$wp_list_id], 'true', false);
        }

        $class = 'pp-campaignmonitor-checkbox ' . esc_attr($atts['class']);
        $label = ! empty($atts['checkbox_text']) ? $atts['checkbox_text'] : ppress_var(PROFILEPRESS_sql::get_meta_value($wp_list_id, 'cm_email_list'), 'cm_email_list_title');
        $label = htmlspecialchars_decode($label);

        $ish = '<div class="pp-checkbox-wrap pp-single-checkbox">';
        $ish .= sprintf('<input type="hidden" name="ppress_campaign_monitor[%1$s]" value="false" style="display: none">', $wp_list_id);
        $ish .= sprintf('<input type="checkbox" name="ppress_campaign_monitor[%1$s]" class="%2$s" id="ppress_campaign_monitor[%1$s]" value="true" %3$s>',
            $wp_list_id, $class, $checked
        );
        $ish .= sprintf('<label class="pp-form-label" for="ppress_campaign_monitor[%1$s]">%2$s</label>', $wp_list_id, $label);
        $ish .= '</div>';

        return $ish;
    }

    public function email_newsletters_form_content()
    {
        if (is_admin()) return '';

        $email_lists = $this->get_email_lists();
        $user_email  = wp_get_current_user()->user_email;

        if ( ! is_array($email_lists) || empty($email_lists)) return '';

        ob_start();

        echo '<form method="post" enctype="multipart/form-data">';

        echo ppress_nonce_field();

        foreach ($email_lists as $email_list) {

            $name       = sprintf('ppress_campaign_monitor[%s]', $email_list['id']);
            $cm_list_id = $email_list['meta_value']['cm_email_list_select']
            ?>
            <div class="profilepress-myaccount-form-field">
                <input type="hidden" name="<?= $name ?>" value="no">
                <input id="<?= $name ?>" type="checkbox" name="<?= $name ?>" value="yes" <?php checked($this->is_user_subscribed($user_email, $cm_list_id, true)) ?>>
                <label for="<?= $name ?>"><?= $email_list['meta_value']['cm_email_list_title'] ?></label>
            </div>
            <?php
        }
        ?>
        <div class="profilepress-myaccount-form-field">
            <input name="ppmyac_campaign_monitor_update" type="submit" value="<?= esc_html__('Save Changes', 'profilepress-pro') ?>">
        </div>
        <?php

        echo '</form>';

        return ob_get_clean();
    }

    public function my_account_content($contents)
    {
        if (self::is_api_key_valid()) {
            $contents[] = [
                'title'   => apply_filters('ppress_campaign_monitor_my_account_title', esc_html__('Newsletter Subscription', 'profilepress-pro')),
                'content' => $this->email_newsletters_form_content()
            ];
        }

        return $contents;
    }

    public static function log_error($error)
    {
        $error = is_array($error) || is_object($error) ? json_encode($error) : $error;
        ppress_log_error('Campaign Monitor extension: ' . $error);
    }

    /**
     * @return Init|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::CAMPAIGN_MONITOR')) return;

        if ( ! EM::is_enabled(EM::CAMPAIGN_MONITOR)) return;

        static $instance;

        if ( ! isset($instance)) {
            $instance = new self;
        }

        return $instance;
    }
}