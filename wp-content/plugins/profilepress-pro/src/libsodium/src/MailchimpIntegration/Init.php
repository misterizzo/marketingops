<?php

namespace ProfilePress\Libsodium\MailchimpIntegration;

use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Libsodium\MailchimpIntegration\Admin\SettingsPage;
use ProfilePress\Libsodium\MailchimpIntegration\APIClass\Batch;
use ProfilePress\Libsodium\MailchimpIntegration\APIClass\MailChimp;
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

        add_action('admin_enqueue_scripts', [$this, 'email_subscription_js']);
        add_action('admin_footer', function () {
            $this->admin_js_script('mc');
        });

        add_shortcode('pp-mailchimp', array($this, 'mc_checkbox'));

        add_filter('ppmyac_email_notification_endpoint_content', [$this, 'my_account_content']);

        add_action('ppress_after_registration', array($this, 'post_registration_action'), 10, 2);
        add_action('ppress_after_social_signup', array($this, 'subscribe_after_social_login'));

        add_action('wp', [$this, 'process_myaccount_list_update']);

        $this->mailchimp_sync();
    }

    public function mailchimp_sync()
    {
        if ('true' != ppress_settings_by_key('mc_auto_sync')) return;

        if ( ! apply_filters('ppress_mailchimp_user_sync_is_enabled', true)) return;

        add_action('delete_user', function ($user_id) {

            $email = get_userdata($user_id)->user_email;

            foreach ($this->get_audiences() as $audience) {

                $mc_list_id = $audience['meta_value']['mc_audience_select'];

                if ($this->is_user_subscribed($email, $mc_list_id)) {

                    $this->unsubscribe_user($email, $mc_list_id);
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

        do_action('ppress_mailchimp_sync', $this);
    }

    /**
     * @return MailChimp
     * @throws \Exception
     */
    public static function api_instance()
    {
        static $instance = false;

        if ( ! $instance) {

            $apiKey = trim(ppress_settings_by_key('mc_api_key', ''));

            if (empty($apiKey)) {
                throw new \Exception('API key not found');
            }

            $instance = new MailChimp($apiKey);
        }

        return $instance;
    }

    /**
     * @throws \Exception
     */
    public static function check_api_key()
    {
        $instance = new MailChimp(trim(ppress_settings_by_key('mc_api_key', '')));

        $instance->get('');

        if ( ! $instance->success()) {
            throw new \Exception('API key is invalid');
        }
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
                delete_transient('ppress_mc_get_email_list');
            }
        }

        return $status;
    }

    public function get_audiences()
    {
        $meta_data = PROFILEPRESS_sql::get_meta_data_by_key('mc_audience');

        if ( ! $meta_data) $meta_data = [];

        return array_filter($meta_data, function ($audience) {
            return isset($audience['meta_value']['mc_audience_enable']) && $audience['meta_value']['mc_audience_enable'] == 'true';
        });
    }

    public function get_audience_by_id($id)
    {
        return PROFILEPRESS_sql::get_meta_value($id, 'mc_audience');
    }

    public static function get_list_merge_fields($list_id)
    {
        $cache_key = 'ppress_mc_get_list_merge_fields_' . $list_id;

        $list_fields = get_transient($cache_key);

        if (empty($list_fields) || false === $list_fields) {

            $list_fields = [];

            try {

                $fields = self::api_instance()->get("lists/$list_id/merge-fields", ['count' => 1000, 'fields' => 'merge_fields.tag,merge_fields.name,merge_fields.type,merge_fields.options']);

                if ( ! self::api_instance()->success()) {
                    throw new \Exception(json_encode(self::api_instance()->getLastResponse()));
                }

                $fields = ppress_var($fields, 'merge_fields', []);

                if ( ! empty($fields)) {
                    foreach ($fields as $field) {
                        $list_fields[$field['tag']] = $field['name'];
                    }

                    set_transient($cache_key, $list_fields, MINUTE_IN_SECONDS);
                }


            } catch (\Exception $e) {
                self::log_error($e->getMessage());
            }
        }

        return $list_fields;
    }

    public static function get_list_tags($list_id)
    {
        $cache_key = 'ppress_mc_get_list_tags_' . $list_id;

        $list_tags = get_transient($cache_key);

        if (empty($list_tags) || false === $list_tags) {

            $list_tags = [];

            try {

                $offset = 0;
                $loop   = true;
                $limit  = 900;

                while ($loop === true) {

                    $tags = self::api_instance()->get("lists/$list_id/segments", [
                        'type'   => 'static',
                        'offset' => $offset,
                        'count'  => $limit
                    ]);

                    if (self::api_instance()->success()) {

                        $tags = ppress_var($tags, 'segments', []);

                        if (is_array($tags) && ! empty($tags)) {

                            foreach ($tags as $tag) {
                                $list_tags[$tag['name']] = $tag['name'];
                            }

                            set_transient($cache_key, $list_tags, MINUTE_IN_SECONDS);

                            if (count($tags) < $limit) $loop = false;

                            $offset += $limit;

                        } else {
                            $loop = false;
                        }

                    } else {
                        $loop = false;
                        self::log_error(self::api_instance()->getLastResponse());
                    }
                }

            } catch (\Exception $e) {
                self::log_error($e->getMessage());
            }
        }

        return $list_tags;
    }

    public static function get_mc_audience_data($audience_id, $field = false)
    {
        $email_list = Init::get_email_list();

        $list_data = $field ? '' : [];

        if (is_array($email_list) && ! empty($email_list)) {

            $data = wp_list_filter(Init::get_email_list(), ['id' => $audience_id]);

            if ( ! empty($data)) $list_data = array_values($data)[0];

            if ($field !== false && isset($list_data[$field])) {
                $list_data = $list_data[$field];
            }
        }

        return $list_data;
    }

    public static function is_automatic_add_user_audience($audience_id)
    {
        $audience_data = PROFILEPRESS_sql::get_meta_value($audience_id, 'mc_audience');

        return ppress_var($audience_data, 'mc_audience_automatic_add_user') == 'true';
    }

    public function is_audience_enabled($audience_id)
    {
        $audience_data = PROFILEPRESS_sql::get_meta_value($audience_id, 'mc_audience');

        return ppress_var($audience_data, 'mc_audience_enable') == 'true';
    }

    public static function get_email_list()
    {
        $cache_key = 'ppress_mc_get_email_list';

        $lists_array = get_transient($cache_key);

        if (empty($lists_array) || false === $lists_array) {

            $lists_array = [];

            try {

                $lists = self::api_instance()->get('/lists', ['count' => 1000]);

                if (self::api_instance()->success()) {

                    $lists = ppress_var($lists, 'lists', []);

                    if ( ! empty($lists)) {
                        foreach ($lists as $list) {
                            $lists_array[] = [
                                'id'           => $list['id'],
                                'name'         => $list['name'],
                                'member_count' => isset($list['stats']['member_count']) ? $list['stats']['member_count'] : 0
                            ];
                        }

                        set_transient($cache_key, $lists_array, MINUTE_IN_SECONDS);
                    }
                } else {
                    self::log_error(self::api_instance()->getLastResponse());
                }

            } catch (\Exception $e) {
                self::log_error($e->getMessage());
            }
        }

        return $lists_array;
    }

    /**
     * @param $list_id
     * @param $email_address
     * @param array $merge_fields
     * @param array $tags
     * @param bool $is_double_optin
     * @param bool|string $old_email_address
     *
     * @return bool|string
     */
    public function sync_to_list($list_id, $email_address, $merge_fields = [], $tags = [], $is_double_optin = false, $old_email_address = false)
    {
        try {

            $parameters = [
                'email_address' => $email_address,
                'merge_fields'  => ppress_filter_empty_array($merge_fields),
                'status_if_new' => $is_double_optin ? 'pending' : 'subscribed',
                'status'        => 'subscribed',
                'ip_signup'     => ppress_get_ip_address()
            ];

            if ( ! empty($tags)) {
                $parameters['tags'] = array_values(array_filter(array_map('trim', $tags)));
            }

            $parameters = apply_filters('ppress_mailchimp_subscription_parameters', ppress_filter_empty_array($parameters), $this);

            $response = self::api_instance()->put(
                sprintf('lists/%s/members/%s', $list_id, MailChimp::subscriberHash(! empty($old_email_address) ? $old_email_address : $email_address)),
                $parameters, 30
            );

            if ( ! self::api_instance()->success()) {
                throw new \Exception(json_encode(self::api_instance()->getLastResponse()));
            }

            return in_array($response['status'], ['subscribed', 'pending']);

        } catch (\Exception $e) {
            self::log_error($e->getMessage());

            return false;
        }
    }

    public function unsubscribe_user($email_address, $mc_list_id)
    {
        try {

            self::api_instance()->put(
                sprintf('lists/%s/members/%s', $mc_list_id, MailChimp::subscriberHash($email_address)),
                ['status' => 'unsubscribed'], 30
            );

            if ( ! self::api_instance()->success()) {
                throw new \Exception(json_encode(self::api_instance()->getLastResponse()));
            }

            return self::api_instance()->success();

        } catch (\Exception $e) {
            self::log_error($e->getMessage());

            return false;
        }
    }

    /**
     * @param $email_address
     * @param $mc_list_id
     * @param $tags
     * @param $remove
     *
     * @return bool
     */
    public function add_remove_user_tags($email_address, $mc_list_id, $tags = [], $remove = false)
    {
        try {

            $tags = array_values(array_filter($tags));

            if (empty($tags)) return false;

            $tags = array_map(function ($val) use ($remove) {
                return [
                    'name'   => $val,
                    'status' => (false === $remove) ? 'active' : 'inactive'
                ];
            }, $tags);

            self::api_instance()->post(
                sprintf('lists/%s/members/%s/tags', $mc_list_id, MailChimp::subscriberHash($email_address)),
                ['tags' => $tags]
            );

            if ( ! self::api_instance()->success()) {
                throw new \Exception(json_encode(self::api_instance()->getLastResponse()));
            }

            return self::api_instance()->success();

        } catch (\Exception $e) {
            self::log_error($e->getMessage());

            return false;
        }
    }

    public function get_add_update_user_to_audience_params($wp_audience_id, $user_data = [])
    {
        if (empty($user_data) && is_user_logged_in()) {
            $user_data = wp_get_current_user();
        }

        $audience_data = PROFILEPRESS_sql::get_meta_value($wp_audience_id, 'mc_audience');

        if (ppress_var($audience_data, 'mc_audience_enable') != 'true') return false;

        $list_id = $audience_data['mc_audience_select'];

        if (empty($list_id)) return false;

        $merge_fields = [];

        $map_fields = ppress_var($audience_data, 'mc_audience_fields_map', []);

        if ( ! empty($map_fields)) {

            foreach ($map_fields as $merge_tag => $user_data_id) {
                if (is_object($user_data)) {
                    $field_value = ppress_var_obj($user_data, $user_data_id, '', true);
                } else {
                    $field_value = ppress_var($user_data, $user_data_id, '', true);
                }

                $merge_fields[$merge_tag] = is_array($field_value) ? implode(', ', $field_value) : $field_value;
            }
        }

        $merge_fields = ppress_filter_empty_array($merge_fields);

        $is_double_optin = ppress_var($audience_data, 'mc_audience_double_optin') == 'true';

        $tags = ppress_var($audience_data, 'mc_audience_default_tags', [], true);

        $email_address = is_object($user_data) ? $user_data->user_email : $user_data['user_email'];

        return [
            'merge_fields'    => $merge_fields,
            'is_double_optin' => $is_double_optin,
            'tags'            => array_values(array_filter($tags)),
            'email_address'   => $email_address,
            'list_id'         => $list_id,
        ];
    }

    public function add_update_user_to_audience($audience_id, $user_data = [], $old_email_address = false)
    {
        $params = $this->get_add_update_user_to_audience_params($audience_id, $user_data);

        if ( ! $params) return false;

        return $this->sync_to_list(
            $params['list_id'],
            $params['email_address'],
            $params['merge_fields'],
            $params['tags'],
            $params['is_double_optin'],
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
        foreach ($this->get_audiences() as $audience) {

            if (ppress_var($audience['meta_value'], 'mc_audience_automatic_add_user') == 'true') {

                $this->add_update_user_to_audience($audience['id'], $user_data);
            }
        }

        if ( ! isset($_POST['ppress_mailchimp']) || ! is_array($_POST['ppress_mailchimp'])) return;

        foreach ($_POST['ppress_mailchimp'] as $audience_id => $status) {

            if ('true' != $status) continue;

            $this->add_update_user_to_audience($audience_id, $user_data);
        }
    }

    /**
     * Detect change in user profile and then update the user mailchimp data.
     *
     * @param array $user_data
     */
    public function detect_update_and_sync($user_data = [], $old_email_address = false)
    {
        foreach ($this->get_audiences() as $audience) {

            $audience_id = $audience['id'];

            $user_email = wp_get_current_user()->user_email;

            $mc_list_id = $audience['meta_value']['mc_audience_select'];

            if ( ! $this->is_user_subscribed($user_email, $mc_list_id)) continue;

            $this->add_update_user_to_audience($audience_id, $user_data, $old_email_address);
        }
    }

    /**
     * Add registering users via social login to email list.
     */
    public function subscribe_after_social_login($user_data)
    {
        foreach ($this->get_audiences() as $audience) {

            if (ppress_var($audience['meta_value'], 'mc_audience_automatic_add_user') == 'true') {

                $this->add_update_user_to_audience($audience['id'], $user_data);
            }
        }
    }

    /**
     * @param Batch $Batch
     * @param $key
     * @param $wp_audience_id
     * @param $user_data
     */
    public function add_to_batch_collection($Batch, $key, $wp_audience_id, $user_data)
    {
        $params = $this->get_add_update_user_to_audience_params($wp_audience_id, $user_data);

        $email_address = $params['email_address'];

        $tags = $params['tags'];

        $parameters = [
            'email_address' => $email_address,
            'merge_fields'  => ppress_filter_empty_array($params['merge_fields']),
            'status_if_new' => $params['is_double_optin'] ? 'pending' : 'subscribed',
            'status'        => 'subscribed',
            'ip_signup'     => parse_url('http://' . ppress_get_ip_address(), PHP_URL_HOST) // strip ports and stuff
        ];

        if ( ! empty($tags)) {
            $parameters['tags'] = array_map('trim', $tags);
        }

        $parameters = ppress_filter_empty_array($parameters);

        $Batch->put(
            "batchSubscribe$key",
            sprintf("lists/%s/members/%s", $params['list_id'], MailChimp::subscriberHash($email_address)),
            $parameters
        );
    }

    /**
     * CHeck if an email has been subscribe to an email list.
     *
     * @param string $email
     * @param string $list_id
     *
     * @param bool $cache
     *
     * @return bool
     */
    public function is_user_subscribed($email, $list_id, $cache = false)
    {
        $cache_key = "ppmc_is_user_subscribed_{$email}_{$list_id}";

        $status = get_transient($cache_key);

        if ( ! $cache || ($cache === true && $status === false)) {

            $status = 'false';

            try {

                $subscriber_hash = self::api_instance()->subscriberHash($email);

                $result = self::api_instance()->get("lists/$list_id/members/$subscriber_hash");

                $status = self::api_instance()->success() && $result['status'] == 'subscribed' ? 'true' : 'false';

                if ($cache === true) set_transient($cache_key, $status, MINUTE_IN_SECONDS);

            } catch (\Exception $e) {
                self::log_error($e->getMessage());
            }
        }

        return $status == 'true';
    }

    public function process_myaccount_list_update()
    {
        if ( ! isset($_POST['ppmyac_mailchimp_update']) || ! isset($_POST['ppress_mailchimp'])) return;

        if ( ! is_user_logged_in()) return;

        if ( ! ppress_verify_nonce()) return;

        foreach ($_POST['ppress_mailchimp'] as $audience_id => $status) {
            $user_email    = wp_get_current_user()->user_email;
            $audience_data = PROFILEPRESS_sql::get_meta_value($audience_id, 'mc_audience');
            $mc_list_id    = $audience_data['mc_audience_select'];

            delete_transient("ppmc_is_user_subscribed_{$user_email}_{$mc_list_id}");

            if ('yes' == $status) {

                if ($this->is_user_subscribed($user_email, $mc_list_id)) continue;

                $this->add_update_user_to_audience($audience_id);
            }

            if ('no' == $status) {

                if ( ! $this->is_user_subscribed($user_email, $mc_list_id)) continue;

                $this->unsubscribe_user($user_email, $mc_list_id);
            }
        }

        delete_transient("ppmc_is_user_subscribed_{$user_email}_{$mc_list_id}");

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
    public function mc_checkbox($atts)
    {
        $atts = shortcode_atts([
            'class'         => '',
            'audience_id'   => '',
            'checkbox_text' => '',
            'checked'       => 'false'
        ], $atts);

        if (empty($atts['audience_id']) || absint($atts['audience_id']) == 0) return esc_html__('Audience ID not found', 'profilepress-pro');

        $audience_id = absint($atts['audience_id']);

        if ( ! $this->is_audience_enabled($audience_id)) return '';

        if (self::is_automatic_add_user_audience($audience_id)) return '';

        $checked = $atts['checked'] == 'true' ? 'checked=checked' : '';

        if (isset($_POST['ppress_mailchimp'][$audience_id])) {
            $checked = checked($_POST['ppress_mailchimp'][$audience_id], 'true', false);
        }

        $class = 'pp-mailchimp-checkbox ' . esc_attr($atts['class']);
        $label = ! empty($atts['checkbox_text']) ? $atts['checkbox_text'] : ppress_var(PROFILEPRESS_sql::get_meta_value($audience_id, 'mc_audience'), 'mc_audience_title');
        $label = htmlspecialchars_decode($label);

        $ish = '<div class="pp-checkbox-wrap pp-single-checkbox">';
        $ish .= sprintf('<input type="hidden" name="ppress_mailchimp[%1$s]" value="false" style="display: none">', $audience_id);
        $ish .= sprintf('<input type="checkbox" name="ppress_mailchimp[%1$s]" class="%2$s" id="ppress_mailchimp[%1$s]" value="true" %3$s>',
            $audience_id, $class, $checked
        );
        $ish .= sprintf('<label class="pp-form-label" for="ppress_mailchimp[%1$s]">%2$s</label>', $audience_id, $label);
        $ish .= '</div>';

        return $ish;
    }

    public function email_newsletters_form_content()
    {
        if (is_admin()) return '';

        $audiences  = $this->get_audiences();
        $user_email = wp_get_current_user()->user_email;

        if ( ! is_array($audiences) || empty($audiences)) return '';

        ob_start();

        echo '<form method="post" enctype="multipart/form-data">';

        echo ppress_nonce_field();

        foreach ($audiences as $audience) {

            $name       = sprintf('ppress_mailchimp[%s]', $audience['id']);
            $mc_list_id = $audience['meta_value']['mc_audience_select']
            ?>
            <div class="profilepress-myaccount-form-field">
                <input type="hidden" name="<?= $name ?>" value="no">
                <input id="<?= $name ?>" type="checkbox" name="<?= $name ?>" value="yes" <?php checked($this->is_user_subscribed($user_email, $mc_list_id, true)) ?>>
                <label for="<?= $name ?>"><?= $audience['meta_value']['mc_audience_title'] ?></label>
            </div>
            <?php
        }
        ?>
        <div class="profilepress-myaccount-form-field">
            <input name="ppmyac_mailchimp_update" type="submit" value="<?= esc_html__('Save Changes', 'profilepress-pro') ?>">
        </div>
        <?php

        echo '</form>';

        return ob_get_clean();
    }

    public function my_account_content($contents)
    {
        if (self::is_api_key_valid()) {
            $contents[] = [
                'title'   => apply_filters('ppress_mailchimp_my_account_title', esc_html__('Newsletter Subscription', 'profilepress-pro')),
                'content' => $this->email_newsletters_form_content()
            ];
        }

        return $contents;
    }

    public static function log_error($error)
    {
        $error = is_array($error) || is_object($error) ? json_encode($error) : $error;
        ppress_log_error('Mailchimp extension: ' . $error);
    }

    /**
     * @return self|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::MAILCHIMP')) return;

        if ( ! EM::is_enabled(EM::MAILCHIMP)) return;

        static $instance;

        if ( ! isset($instance)) {
            $instance = new self;
        }

        return $instance;
    }
}
