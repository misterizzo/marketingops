<?php

namespace ProfilePress\Libsodium\WooCommerceIntegration;

use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Classes\FormRepository as FR;
use ProfilePress\Core\Classes\PPRESS_Session;
use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Core\ShortcodeParser\Builder\FieldsShortcodeCallback;
use ProfilePress\Core\ShortcodeParser\MyAccount\MyAccountTag;

class Init
{
    public static $instance_flag = false;

    public function __construct()
    {
        add_action('woocommerce_loaded', function () {

            WooMemberships::get_instance();

            add_action('admin_enqueue_scripts', array($this, 'import_js'));
            add_filter('ppress_custom_fields_extra_tablenav', array($this, 'add_import_button'));

            add_action('wp_ajax_pp_import_wc_fields', array($this, 'ajax_handler'));

            add_filter('wc_get_template', array($this, 'replace_wc_edit_profile_form'), 10, 2);
            add_filter('wc_get_template', array($this, 'replace_wc_login_form'), 10, 2);
            add_filter('wc_get_template', array($this, 'replace_wc_my_account_login_form'), 10, 2);

            add_action('ppress_my_account_shortcode_callback', function () {
                self::wc_myaccount_url_rewrites();
            });
            add_filter('ppress_myaccount_tabs', [$this, 'myaccount_tabs']);
            add_filter('ppress_my_account_settings_sub_menus', [$this, 'myaccount_sub_menus']);
            add_action('woocommerce_after_checkout_registration_form', [$this, 'checkout_fields']);
            add_action('woocommerce_after_checkout_validation', [$this, 'validate_checkout_fields'], 10, 2);
            add_action('woocommerce_checkout_update_user_meta', [$this, 'save_registration_data']);
            add_action('wp_enqueue_scripts', [$this, 'enqueue_flatpickr']);

            add_action('ppress_myaccount_edit_profile_submenu_content', [$this, 'submenu_content']);

            add_filter('ppress_settings_page_args', array($this, 'settings_page'));

            add_filter('ppress_login_url', function ($url) {

                if (is_checkout()) $url = add_query_arg('ppwc_sl', 'true', $url);

                return $url;
            }, 999999999);

            add_action('ppress_before_social_login_init', function () {
                if (isset($_REQUEST['ppwc_sl'], $_REQUEST['pp_current_url'])) {
                    PPRESS_Session::get_instance()->set('pp_social_login_redirect_to', rawurlencode($_REQUEST['pp_current_url']));
                }
            });

            add_filter('ppress_social_login_redirect', function ($url) {

                $session_saved_redirect_to = PPRESS_Session::get_instance()->get('pp_social_login_redirect_to');

                if ( ! empty($session_saved_redirect_to)) {
                    $url = rawurldecode($session_saved_redirect_to);
                }

                return $url;

            }, 999999);
        });
    }

    /**
     * Doing this so we fallback to default core values if WC has nothing set.
     *
     * @param $user_id
     * @param $key
     *
     * @return mixed|string
     * @see WC_Admin_Profile::get_user_meta()
     *
     */
    public static function get_user_meta($user_id, $key)
    {
        $value           = get_user_meta($user_id, $key, true);
        $existing_fields = array('billing_first_name', 'billing_last_name');
        if ( ! $value && in_array($key, $existing_fields)) {
            $value = get_user_meta($user_id, str_replace('billing_', '', $key), true);
        } elseif ( ! $value && ('billing_email' === $key)) {
            $user  = get_userdata($user_id);
            $value = $user->user_email;
        }

        return $value;
    }

    public function myaccount_tabs($tabs)
    {
        $tabs_to_show = ppress_settings_by_key('wci_my_account_tabs', []);

        if (in_array('orders', $tabs_to_show)) {
            $tabs['wc-orders'] = [
                'title'    => esc_html__('Orders', 'profilepress-pro'),
                'endpoint' => 'wc-orders',
                'priority' => 50,
                'icon'     => 'shopping_cart',
                'callback' => [__CLASS__, 'wc_orders_myac_page']
            ];
        }

        if (in_array('downloads', $tabs_to_show)) {
            $tabs['wc-downloads'] = [
                'title'    => esc_html__('Downloads', 'profilepress-pro'),
                'endpoint' => 'wc-downloads',
                'priority' => 51,
                'icon'     => 'file_download',
                'callback' => [__CLASS__, 'wc_downloads_myac_page']
            ];
        }

        if (in_array('subscriptions', $tabs_to_show)) {
            $tabs['wc-subscriptions'] = [
                'title'    => esc_html__('Subscriptions', 'profilepress-pro'),
                'endpoint' => 'wc-subscriptions',
                'priority' => 52,
                'icon'     => 'receipt',
                'callback' => [__CLASS__, 'wc_subscriptions_myac_page']
            ];
        }

        if (in_array('memberships', $tabs_to_show)) {
            $tabs['wc-memberships'] = [
                'title'    => esc_html__('Memberships', 'profilepress-pro'),
                'endpoint' => 'wc-memberships',
                'priority' => 53,
                'icon'     => 'card_membership',
                'callback' => [__CLASS__, 'wc_memberships_myac_page']
            ];
        }

        return apply_filters('ppress_my_account_woocommerce_tabs', $tabs);
    }

    public function myaccount_sub_menus($sub_menus)
    {
        $tabs_to_show = ppress_settings_by_key('wci_my_account_tabs', []);

        if (in_array('billing', $tabs_to_show)) {
            $sub_menus['wc-billing'] = esc_html__('Billing Address', 'profilepress-pro');
        }

        if (in_array('shipping', $tabs_to_show)) {
            $sub_menus['wc-shipping'] = esc_html__('Shipping Address', 'profilepress-pro');
        }

        return apply_filters('ppress_my_account_woocommerce_submenus', $sub_menus);
    }

    public function define_wc_file_upload_field()
    {
        add_filter('woocommerce_form_field_multi_checkbox', function ($field, $key, $args, $value) {

            if ($args['required']) {
                $args['class'][] = 'validate-required';
                $required        = '&nbsp;<abbr class="required" title="' . esc_attr__('required', 'profilepress-pro') . '">*</abbr>';
            } else {
                $required = '&nbsp;<span class="optional">(' . esc_html__('optional', 'profilepress-pro') . ')</span>';
            }

            $field_html = '';

            if ($args['label']) {
                $field_html .= '<label class="' . esc_attr(implode(' ', $args['label_class'])) . '">' . wp_kses_post($args['label']) . $required . '</label>';
            }

            if ( ! empty($args['options'])) {

                foreach ($args['options'] as $option_key => $option_text) {
                    $selected   = is_array($value) && in_array($key, $value) ? ' checked=checked' : '';
                    $field_html .= '<span class="woocommerce-input-wrapper input-checkbox">';
                    $field_html .= '<input type="checkbox" class="input-checkbox ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($key) . '[]" id="' . esc_attr('multis_' . $option_key) . '" value="' . $option_key . '"' . $selected . '/>';
                    $field_html .= '<label style="display:inline" for="' . esc_attr('multis_' . $option_key) . '">' . wp_kses_post($option_text) . '</label>';
                    $field_html .= '</span>';
                }
            }

            if ($args['description']) {
                $field_html .= '<span style="display:block" class="description" id="' . esc_attr($args['id']) . '-description" aria-hidden="true">' . wp_kses_post($args['description']) . '</span>';
            }

            return sprintf(
                '<p class="form-row %1$s">%3$s</p>',
                esc_attr(implode(' ', $args['class'])),
                esc_attr($args['id']) . '_field',
                $field_html
            );
        }, 10, 4);

        add_filter('woocommerce_form_field_date', function ($field, $key) {

            $config = FieldsShortcodeCallback::date_picker_config(str_replace('ppress_wci_', '', $key));

            $field .= sprintf(
                '<script type="text/javascript">jQuery(function() {jQuery( ".ppress-wci-fields-wrap input[name=%s]" ).flatpickr(%s);});</script>',
                $key, json_encode($config)
            );

            return $field;
        }, 10, 2);
    }

    public function enqueue_flatpickr()
    {
        if (is_checkout()) {

            $cf_date_fields = array_reduce(
                wp_list_filter(PROFILEPRESS_sql::get_profile_custom_fields(), ['type' => 'date']),
                function ($carry, $item) {
                    $carry[] = $item['field_key'];

                    return $carry;
                }, []
            );

            $checkout_fields = array_filter(ppress_settings_by_key('wci_checkout_fields', [], true));

            if ( ! empty(array_intersect($cf_date_fields, $checkout_fields))) {
                wp_enqueue_script('ppress-flatpickr-wc', PPRESS_ASSETS_URL . '/flatpickr/flatpickr.min.js', array('jquery'), PPRESS_VERSION_NUMBER, true);
                wp_enqueue_style('ppress-flatpickr-wc', PPRESS_ASSETS_URL . '/flatpickr/flatpickr.min.css', false, PPRESS_VERSION_NUMBER);
            }
        }
    }

    public function checkout_fields()
    {
        if ( ! EM::is_enabled(EM::CUSTOM_FIELDS)) return;

        $this->define_wc_file_upload_field();

        $checkout_fields = array_filter(ppress_settings_by_key('wci_checkout_fields', [], true));

        if (empty($checkout_fields)) return;

        $checkout_required_fields = array_filter(ppress_settings_by_key('wci_checkout_fields_required', [], true));

        $contact_info  = PROFILEPRESS_sql::get_contact_info_fields();
        $custom_fields = PROFILEPRESS_sql::get_profile_custom_fields();

        $custom_field_keys = [];
        if ( ! empty($custom_fields) && is_array($checkout_fields)) {
            $custom_field_keys = wp_list_pluck($custom_fields, 'field_key');
        }

        echo '<div class="create-account ppress-wci-fields-wrap">';

        foreach ($checkout_fields as $checkout_field) {

            $field_id = 'ppress_wci_' . $checkout_field;

            $value = ppress_clean(ppressPOST_var($field_id, null));

            if (in_array($checkout_field, array_keys($contact_info))) {

                $args = ['label' => $contact_info[$checkout_field]];

                if (in_array($checkout_field, $checkout_required_fields)) {
                    $args['required'] = true;
                }

                woocommerce_form_field('ppress_wci_' . $checkout_field, $args, $value);
            }

            if (in_array($checkout_field, $custom_field_keys) && ! in_array($checkout_field, ppress_woocommerce_billing_shipping_fields())) {

                $custom_field_data = ppress_var(array_values(
                    wp_list_filter($custom_fields, ['field_key' => $checkout_field])
                ), 0);

                $args = [
                    'type'  => $custom_field_data['type'],
                    'label' => $custom_field_data['label_name']
                ];

                if (in_array($custom_field_data['type'], ['select', 'radio', 'checkbox'])) {

                    $options = array_map('trim', explode(',', $custom_field_data['options']));
                    $options = array_reduce($options, function ($carry, $item) {
                        $carry[$item] = $item;

                        return $carry;
                    }, []);

                    $args['options'] = $options;
                }

                if ('select' == $custom_field_data['type'] && ppress_is_select_field_multi_selectable($checkout_field)) {
                    $args['type'] = 'multi_checkbox';
                }

                if ('checkbox' == $custom_field_data['type']) {
                    $args['type'] = 'multi_checkbox';
                }

                if ('agreeable' == $custom_field_data['type']) {
                    $args['type'] = 'checkbox';
                }

                if (in_array($checkout_field, $checkout_required_fields)) {
                    $args['required'] = true;
                }

                if ( ! empty($custom_field_data['description'])) {
                    $args['description'] = $custom_field_data['description'];
                }

                switch ($checkout_field) {
                    case 'country':
                        $args['type']    = 'select';
                        $args['options'] = ['' => '––––––––––'] + ppress_array_of_world_countries();
                        break;
                }

                woocommerce_form_field('ppress_wci_' . $checkout_field, $args, $value);
            }
        }

        echo '</div>';
    }

    /**
     * @param $data
     * @param \WP_Error $errors
     *
     * @return mixed|void
     */
    public function validate_checkout_fields($data, $errors)
    {
        if ( ! EM::is_enabled(EM::CUSTOM_FIELDS)) return;

        if ( ! WC()->checkout()->is_registration_required() && empty($_POST['createaccount'])) {
            return $data;
        }

        $checkout_required_fields = array_filter(ppress_settings_by_key('wci_checkout_fields_required', [], true));

        foreach ($checkout_required_fields as $field) {

            $field_key = 'ppress_wci_' . $field;

            if ( ! isset($_POST[$field_key]) || empty($_POST[$field_key])) {
                $errors->add(
                    'ppress_wci_required_field',
                    apply_filters('ppress_wci_required_field_error', esc_html__('Please complete all required fields.', 'profilepress-pro'))
                );
                break;
            }
        }
    }

    /**
     * @param $customer_id
     *
     * @return mixed|void
     */
    public function save_registration_data($customer_id)
    {
        if ( ! EM::is_enabled(EM::CUSTOM_FIELDS)) return;

        if ( ! WC()->checkout()->is_registration_required() && empty($_POST['createaccount'])) return;

        foreach ($_POST as $key => $value) {

            if (strpos($key, 'ppress_wci_') === false) continue;

            update_user_meta($customer_id, str_replace('ppress_wci_', '', $key), ppress_clean($value));
        }
    }

    public function submenu_content($menu_id)
    {
        switch ($menu_id) {
            case 'wc-billing':
                require apply_filters('ppress_my_account_woocommerce_billing_template', plugin_dir_path(__FILE__) . wp_normalize_path("templates/ppmyaccount/billing.tmpl.php"));
                break;
            case 'wc-shipping':
                require apply_filters('ppress_my_account_woocommerce_shipping_template', plugin_dir_path(__FILE__) . wp_normalize_path("templates/ppmyaccount/shipping.tmpl.php"));
                break;
        }
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function settings_page($args)
    {
        $custom_fields = array_filter(ppress_custom_fields_key_value_pair(true), function ($field_key) {
            return ! in_array($field_key, ppress_woocommerce_billing_shipping_fields());
        }, ARRAY_FILTER_USE_KEY);

        $required_checkout_field_values = array_reduce(
            ppress_settings_by_key('wci_checkout_fields', [], true),
            function ($carry, $item) use ($custom_fields) {

                if ( ! empty($item)) {
                    $carry[$item] = ppress_var($custom_fields, $item);
                }

                return $carry;
            }, []);

        $args['pp_wi_settings'] = [
            'tab_title'                          => esc_html__('WooCommerce', 'profilepress-pro'),
            'section_title'                      => esc_html__('WooCommerce Settings', 'profilepress-pro'),
            'dashicon'                           => 'dashicons-cart',
            'replace_wc_checkout_login'          => array(
                'type'        => 'select',
                'options'     => $this->get_form_list(FR::LOGIN_TYPE, true),
                'label'       => __('Checkout Login Form', 'profilepress-pro'),
                'description' => __('Select the form that will replace the login form on WooCommerce checkout page.', 'profilepress-pro'),
            ),
            'replace_wc_my_account_login'        => array(
                'type'        => 'select',
                'options'     => $this->get_form_list(FR::LOGIN_TYPE, true),
                'label'       => __('My Account Login Form', 'profilepress-pro'),
                'description' => __('Select the form that will replace login form in WooCommerce My Account page.', 'profilepress-pro'),
            ),
            'replace_wc_my_account_signup'       => array(
                'type'        => 'select',
                'options'     => $this->get_form_list(FR::REGISTRATION_TYPE, true),
                'label'       => __('My Account Registration Form', 'profilepress-pro'),
                'description' => __('Select the form that will replace the registration form in WooCommerce My Account page.', 'profilepress-pro'),
            ),
            'replace_wc_my_account_edit_profile' => array(
                'type'        => 'select',
                'options'     => $this->get_form_list(FR::EDIT_PROFILE_TYPE),
                'label'       => __('WooCommerce Edit Account', 'profilepress-pro'),
                'description' => __('Select the form that will replace the Edit Account form in WooCommerce My Account page.', 'profilepress-pro'),
            ),
            'wci_my_account_tabs'                => array(
                'type'        => 'select2',
                'options'     => [
                    'billing'   => esc_html__('Billing Address', 'profilepress-pro'),
                    'shipping'  => esc_html__('Shipping Address', 'profilepress-pro'),
                    'orders'    => esc_html__('Orders', 'profilepress-pro'),
                    'downloads' => esc_html__('Downloads', 'profilepress-pro')
                ],
                'label'       => __('My Account Tabs', 'profilepress-pro'),
                'description' => __('Select the tabs to show on My Account page. Note that billing and shipping addresses are shown in the "Account Details" section.', 'profilepress-pro'),
            ),
            'wci_checkout_fields'                => array(
                'type'        => 'select2',
                'options'     => $custom_fields,
                'label'       => __('Checkout Registration Fields', 'profilepress-pro'),
                'description' => __('Select custom fields to add to WooCommerce checkout registration. Note that "File Upload" field is not supported.', 'profilepress-pro'),
            ),
            'wci_checkout_fields_required'       => array(
                'type'        => 'select2',
                'options'     => $required_checkout_field_values,
                'label'       => __('Required Registration Fields', 'profilepress-pro'),
                'description' => __('Select custom fields that must be filled before the checkout is processed.', 'profilepress-pro'),
            ),
        ];

        if (class_exists('\WC_Subscriptions')) {
            $args['pp_wi_settings']['wci_my_account_tabs']['options']['subscriptions'] = esc_html__('Subscriptions', 'profilepress-pro');
        }

        if (class_exists('\WC_Subscriptions')) {
            $args['pp_wi_settings']['wci_my_account_tabs']['options']['subscriptions'] = esc_html__('Subscriptions', 'profilepress-pro');
        }

        if (function_exists('wc_memberships')) {
            $args['pp_wi_settings']['wci_my_account_tabs']['options']['memberships'] = esc_html__('Memberships', 'profilepress-pro');
        }

        return $args;
    }

    public function get_forms($form_type, $initial_select = true, $prefix = false)
    {
        $forms = FR::get_forms($form_type);

        $initial = $initial_select ? ['' => __('Select...', 'profilepress-pro')] : [];

        return array_reduce($forms, function ($carry, $item) use ($prefix) {
            $id = $prefix ? $prefix . $item['form_id'] : $item['form_id'];

            $carry[$id] = $item['name'];

            return $carry;

        }, $initial);
    }

    /**
     * @param string $form_type
     *
     * @return mixed
     */
    public function get_form_list($form_type, $include_melange = false)
    {
        if ( ! $include_melange || ! in_array($form_type, [FR::LOGIN_TYPE, FR::REGISTRATION_TYPE])) {
            return $this->get_forms($form_type);
        }

        $melange_forms = $this->get_forms(FR::MELANGE_TYPE, false, 'melange_');

        if (empty($melange_forms)) return $this->get_forms($form_type);

        $label = $form_type == FR::REGISTRATION_TYPE ? esc_html__('Registration Forms', 'profilepress-pro') : esc_html__('Login Forms', 'profilepress-pro');

        $melange_label = esc_html__('Melange Forms', 'profilepress-pro');

        $forms = ['' => __('Select...', 'profilepress-pro')];

        $forms[$label]   = $this->get_forms($form_type, false);
        $forms[$melange_label] = $melange_forms;

        return $forms;
    }

    /**
     * JS file
     */
    public function import_js()
    {
        wp_enqueue_script('pp_wci_import', PROFILEPRESS_PRO_LIBSODIUM_ASSETS_URL . 'wci/import.js', array('jquery'), false, true);
        wp_localize_script('pp_wci_import', 'pp_wci_var', [
            'nonce' => wp_create_nonce('pp-import-woocommerce-fields')
        ]);

        if (ppressGET_var('page') == PPRESS_SETTINGS_SLUG) {
            wp_enqueue_script('ppress_wci_admin', PROFILEPRESS_PRO_LIBSODIUM_ASSETS_URL . 'wci/admin.js', array('jquery'), false, true);
            wp_localize_script('ppress_wci_admin', 'ppress_wci_admin_var', [
                'custom_fields' => ppress_custom_fields_key_value_pair(true)
            ]);
        }
    }

    /**
     * Add profile field import button to custom field wp_list admin page.
     */
    public function add_import_button()
    {
        ?>
        <div style="float:left;">
            <input class="button-secondary wc_import_fields" type="button" value="<?php _e('Import WooCommerce Fields', 'profilepress-pro'); ?>"/>
            <span style="display:none" class="spinner is-active"></span>
            <span style="display:none;vertical-align:middle" class="dashicons dashicons-yes"></span>
        </div>
        <?php
    }

    /**
     * Ajax handler / callback => Import WooCommerce billing and shipping fields.
     */
    public function ajax_handler()
    {
        check_ajax_referer('pp-import-woocommerce-fields', 'ajax_nonce');
        if ( ! current_user_can('administrator')) return;
        $this->import_wc_fields('billing');
        $this->import_wc_fields('shipping');
        wp_die();
    }

    /**
     * Handles billing field importation to ProfilePress custom fields.
     *
     * @param string $type
     */
    public function import_wc_fields($type = 'billing')
    {
        $billing_fields_array = WC()->countries->get_address_fields(WC()->countries->get_base_country(), "{$type}_");
        $supported_fields     = array('date', 'select', 'multiselect', 'textarea', 'country', 'radio', 'checkbox', 'text');

        foreach ($billing_fields_array as $key => $value) {

            if (PROFILEPRESS_sql::is_profile_field_key_exist($key)) {
                continue;
            }

            $label_name  = ppress_var($value, 'label', '');
            $description = sprintf(__('WooCommerce %s field', 'profilepress-pro'), $type);
            $options     = is_array($value['options']) && ! empty($value['options']) ? implode(',', $value['options']) : '';
            $field_type  = in_array($value['type'], $supported_fields) ? $value['type'] : 'text';

            // if label is missing, generate it from the field key.
            // turns billing_address_2 into "Billing Address 2"
            if (empty($label_name)) {
                $label_name = ucwords(preg_replace('/[_\W]/', ' ', $key));
            }

            if ($field_type == 'multiselect') {
                $field_type = 'select';
            }

            $field_id = PROFILEPRESS_sql::add_profile_field($label_name, $key, $description, $field_type, $options);

            if ($field_type == 'multiselect') {
                // this ensure ProfilePress sees the select as multi selectable.
                PROFILEPRESS_sql::add_multi_selectable($key, $field_id);
            }
        }
    }

    public static function wc_myaccount_url_rewrites()
    {
        $tabs_to_show = ppress_settings_by_key('wci_my_account_tabs', []);

        if (in_array('orders', $tabs_to_show)) {

            add_filter('woocommerce_get_view_order_url', function ($url, $order) {
                /** @var \WC_Order $order */
                /** using view-order as query arg resulted in a 404 issue */
                return esc_url_raw(add_query_arg('vorder', $order->get_id(), MyAccountTag::get_endpoint_url('wc-orders')));
            }, 10, 2);

            add_filter('woocommerce_get_endpoint_url', function ($url, $endpoint, $value) {

                if ('orders' == $endpoint && is_numeric($value)) {
                    $url = esc_url_raw(add_query_arg('spage', $value));
                }

                return $url;
            }, 10, 3);
        }

        if (in_array('subscriptions', $tabs_to_show)) {

            add_filter('wcs_get_view_subscription_url', function ($url, $id) {
                return esc_url_raw(add_query_arg('subid', $id, MyAccountTag::get_endpoint_url('wc-subscriptions')));
            }, 10, 2);

            add_filter('woocommerce_get_endpoint_url', function ($url, $endpoint, $value) {

                if ('subscriptions' == $endpoint && is_numeric($value)) {
                    $url = esc_url_raw(add_query_arg('spage', $value));
                }

                return $url;
            }, 10, 3);
        }
    }

    public static function wc_orders_myac_page()
    {
        echo '<div class="profilepress-myaccount-wc-orders woocommerce">';
        echo '<h2>' . ppress_var(MyAccountTag::myaccount_tabs(), 'wc-orders')['title'] . '</h2>';
        if ( ! empty($_GET['vorder'])) {
            woocommerce_account_view_order(absint($_GET['vorder']));
        } else {
            woocommerce_account_orders(absint(ppressGET_var('spage', 1)));
        }
        echo '</div>';
    }

    public static function wc_downloads_myac_page()
    {
        echo '<div class="profilepress-myaccount-wc-downloads woocommerce">';
        echo '<h2>' . ppress_var(MyAccountTag::myaccount_tabs(), 'wc-downloads')['title'] . '</h2>';
        woocommerce_account_downloads();
        echo '</div>';
    }

    public static function wc_subscriptions_myac_page()
    {
        echo '<div class="profilepress-myaccount-wc-subscriptions woocommerce">';
        echo '<h2>' . ppress_var(MyAccountTag::myaccount_tabs(), 'wc-subscriptions')['title'] . '</h2>';
        if ( ! empty($_GET['subid'])) {
            \WCS_Template_Loader::get_view_subscription_template(absint($_GET['subid']));
        } else {
            \WC_Subscriptions::get_my_subscriptions_template(absint(ppressGET_var('spage', 1)));
        }
        echo '</div>';
    }

    /**
     * Action links url rewrite not possible.
     * Might take user ot woo myaccount page for some actions
     */
    public static function wc_memberships_myac_page()
    {
        /** shim to make @see Members_Area::is_members_area() true */
        set_query_var('members-area', true);
        $_GET['members-area'] = true;

        echo '<div class="profilepress-myaccount-wc-memberships woocommerce">';
        echo '<h2>' . ppress_var(MyAccountTag::myaccount_tabs(), 'wc-memberships')['title'] . '</h2>';
        wc_memberships()->get_frontend_instance()->get_my_account_instance()->get_members_area_instance()->output_members_area();
        echo '</div>';
    }

    /**
     * Replace WooCommerce edit profile with that of ProfilePress
     *
     * @param string $located
     * @param string $template_name
     *
     * @return string
     */
    public function replace_wc_edit_profile_form($located, $template_name)
    {
        if ( ! empty(ppress_settings_by_key('replace_wc_my_account_edit_profile'))) {

            if ($template_name == 'myaccount/form-edit-account.php') {
                $located = plugin_dir_path(__FILE__) . wp_normalize_path("templates/edit-profile-form.php");
            }
        }

        return $located;
    }

    /**
     * Replace WooCommerce login form with that of ProfilePress.
     *
     * @param string $located
     * @param string $template_name
     *
     * @return string
     */
    public function replace_wc_login_form($located, $template_name)
    {
        if ( ! empty(ppress_settings_by_key('replace_wc_checkout_login'))) {

            if ($template_name == 'global/form-login.php') {
                $located = plugin_dir_path(__FILE__) . wp_normalize_path("templates/login-form.php");
            }
        }

        return $located;
    }

    /**
     * Replace WooCommerce 'My Account" login form with that of ProfilePress.
     *
     * @param string $located
     * @param string $template_name
     *
     * @return string
     */
    public function replace_wc_my_account_login_form($located, $template_name)
    {
        if ( ! empty(ppress_settings_by_key('replace_wc_my_account_login')) || ! empty(ppress_settings_by_key('replace_wc_my_account_signup'))) {

            if ($template_name == 'myaccount/form-login.php') {
                $located = plugin_dir_path(__FILE__) . wp_normalize_path("templates/my-account-login-form.php");
            }
        }

        return $located;
    }

    /**
     * @return self|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::WOOCOMMERCE')) return;

        if ( ! EM::is_enabled(EM::WOOCOMMERCE)) return;

        static $instance;

        if ( ! isset($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}
