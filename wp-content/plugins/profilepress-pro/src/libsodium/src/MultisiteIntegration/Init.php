<?php

namespace ProfilePress\Libsodium\MultisiteIntegration;

use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Classes\FormRepository;
use ProfilePress\Core\ShortcodeParser\Builder\RegistrationFormBuilder;
use WP_Error;

class Init
{
    public static $instance_flag = false;

    public $ms_result;

    public $user_result;

    public function __construct()
    {
        if ( ! is_multisite()) return;

        add_filter('ppress_registration_validation', array($this, 'validate_ms_site_creation'), 10, 4);
        add_action('ppress_after_registration', array($this, 'create_new_site'), 10, 4);

        add_action('before_signup_header', array($this, 'wp_signup_redirection'));

        add_shortcode('reg-site-address', array($this, 'site_address_shortcode'));
        add_shortcode('reg-site-title', array($this, 'site_title_shortcode'));

        add_filter('ppress_settings_page_args', array($this, 'settings_page'));

        add_action('ppress_drag_drop_builder_field_init_after', function ($form_type) {
            if (FormRepository::REGISTRATION_TYPE == $form_type) {
                new SiteAddressField();
                new SiteTitleField();
            }
        });

        add_filter('ppress_reg_edit_profile_available_shortcodes', [$this, 'add_available_shortcode_popup'], 10, 2);

        // drag and drop ui
        add_filter('ppress_form_builder_meta_box_registration_settings', [$this, 'dnd_form_builder_settings']);
        add_filter('ppress_form_builder_metabox_field_as_form_meta', function ($meta_keys) {
            $meta_keys[] = 'ppm_enable_user_activation';

            return $meta_keys;
        });

        // shortcode builder
        add_filter('ppress_shortcode_builder_melange_meta', [$this, 'save_user_activation_meta_data']);
        add_filter('ppress_shortcode_builder_registration_meta', [$this, 'save_user_activation_meta_data']);
        add_action('ppress_shortcode_builder_registration_screen_after', function ($form_id) {
            $this->shortcode_form_builder_settings($form_id, FormRepository::REGISTRATION_TYPE);
        });
        add_action('ppress_shortcode_builder_melange_screen_after', function ($form_id) {
            $this->shortcode_form_builder_settings($form_id, FormRepository::MELANGE_TYPE);
        });
    }

    public function add_available_shortcode_popup($shortcodes, $type)
    {
        if ('reg' == $type) {

            $shortcodes['reg-site-address'] = [
                'description' => esc_html__('Site address field.', 'profilepress-pro'),
                'shortcode'   => 'reg-site-address',
                'attributes'  => [
                    'placeholder' => [
                        'label' => esc_html__('Placeholder', 'profilepress-pro'),
                        'field' => 'text'
                    ],
                    'id'          => [
                        'label' => esc_html__('ID', 'profilepress-pro'),
                        'field' => 'text'
                    ],
                    'class'       => [
                        'label' => esc_html__('CSS class', 'profilepress-pro'),
                        'field' => 'text'
                    ],
                ]
            ];

            $shortcodes['reg-site-title'] = [
                'description' => esc_html__('Site title field.', 'profilepress-pro'),
                'shortcode'   => 'reg-site-title',
                'attributes'  => [
                    'placeholder' => [
                        'label' => esc_html__('Placeholder', 'profilepress-pro'),
                        'field' => 'text'
                    ],
                    'id'          => [
                        'label' => esc_html__('ID', 'profilepress-pro'),
                        'field' => 'text'
                    ],
                    'class'       => [
                        'label' => esc_html__('CSS class', 'profilepress-pro'),
                        'field' => 'text'
                    ],
                ]
            ];
        }

        return $shortcodes;
    }

    public function save_user_activation_meta_data($metas)
    {
        $metas['ppm_enable_user_activation'] = sanitize_text_field($_POST['ppm_enable_user_activation']) == 'yes';

        return $metas;
    }

    public function dnd_form_builder_settings($settings)
    {
        $settings[] = [
            'id'             => 'ppm_enable_user_activation',
            'type'           => 'checkbox',
            'label'          => esc_html__('Multisite Email Activation', 'profilepress-pro'),
            'checkbox_label' => esc_html__('Enable activation of new sites', 'profilepress-pro'),
            'description'    => esc_html__('Send users an email with an activation link. Sites are only created after activation.', 'profilepress-pro'),
            'priority'       => 25
        ];

        return $settings;
    }

    public function shortcode_form_builder_settings($form_id, $form_type)
    {
        $db_data = FormRepository::get_form_meta($form_id, $form_type, 'ppm_enable_user_activation') ? 'yes' : 'no';
        ?>
        <tr>
            <th scope="row">
                <label for="ppm_enable_user_activation"><?php esc_attr_e('Multisite Email Activation', 'profilepress-pro'); ?></label>
            </th>
            <td>
                <input type="checkbox" name="ppm_enable_user_activation" id="ppm_enable_user_activation" value="yes" <?php checked('yes', $db_data); ?> />
                <label for="ppm_enable_user_activation"><strong><?php _e('Enable activation of new sites', 'profilepress-pro'); ?></strong></label>

                <p class="description">
                    <?php _e('Send users an email with an activation link. Sites are only created after activation.', 'profilepress-pro'); ?>
                </p>
            </td>
        </tr>
        <?php
    }

    /**
     * Redirect wp-signup.php to custom front-end multisite signup page.
     */
    public function wp_signup_redirection()
    {
        $page_id = ppress_settings_by_key('ms_multisite_signup_page', 'none');

        if (is_multisite() && $page_id != 'none') {
            nocache_headers();
            wp_safe_redirect(get_permalink($page_id));
            exit;
        }
    }

    public function is_email_activation_enabled($form_id, $is_melange = false)
    {
        $form_type = $is_melange === true ? FormRepository::MELANGE_TYPE : FormRepository::REGISTRATION_TYPE;
        $data      = FormRepository::get_form_meta($form_id, $form_type, 'ppm_enable_user_activation');

        return in_array($data, ['true', true], true);
    }

    /**
     * Is BuddyPress active?
     *
     * @return bool
     */
    public function is_bp_active()
    {
        return defined('BP_VERSION');
    }

    /**
     * Validate site creation on registration.
     *
     * @param WP_Error $reg_errors
     * @param int $form_id
     * @param array $user_data
     *
     * @return WP_Error
     */
    public function validate_ms_site_creation($reg_errors, $form_id, $user_data, $is_melange)
    {
        if ( ! apply_filters('ppress_ms_validate_ms_site_creation', true, $form_id, $is_melange)) return $reg_errors;

        $site_address = sanitize_text_field($_POST['reg_ms_site_address']);
        $site_title   = sanitize_text_field($_POST['reg_ms_site_title']);

        $user_result = wpmu_validate_user_signup($user_data['user_login'], $user_data['user_email']);

        /** @var WP_Error $user_errors */
        $user_errors = $user_result['errors'];

        $this->user_result = $user_result;

        if ($user_errors->get_error_code()) {
            $reg_errors->add('ms_user_validation_error', $user_errors->get_error_message());
        }

        $result2 = wpmu_validate_blog_signup($site_address, $site_title);
        /** @var WP_Error $errors */
        $errors          = $result2['errors'];
        $this->ms_result = $result2;

        if ($errors->get_error_code()) {
            $reg_errors->add('ms_site_create_error', $errors->get_error_message());
        }

        return $reg_errors;
    }


    /**
     * Create new MS site after registration is successful.
     *
     * @param $form_id
     * @param $user_data
     * @param $user_id
     * @param $is_melange
     *
     * @return void
     */
    public function create_new_site($form_id, $user_data, $user_id, $is_melange)
    {
        if ( ! apply_filters('ppress_ms_validate_ms_site_creation', true, $form_id, $is_melange)) return;

        $result     = $this->ms_result;
        $domain     = $result['domain'];
        $path       = $result['path'];
        $blogname   = $result['blogname'];
        $blog_title = $result['blog_title'];


        $user_result = $this->user_result;
        $user_name   = $user_result['user_name'];
        $user_email  = $user_result['user_email'];

        $site_meta = apply_filters('ppress_ms_registration_new_site_meta', array('public' => 1));

        $is_email_activation_enabled = $this->is_email_activation_enabled($form_id, $is_melange);

        if ($is_email_activation_enabled) {

            // buddpress will prevent wpmu_signup_blog sending the notification to activate site by returning false to the wpmu_signup_blog_notification filter
            // or sometimes it does but replaces the activate url with theirs.
            if ($this->is_bp_active()) {
                remove_filter('wpmu_signup_blog_notification', 'bp_core_activation_signup_blog_notification', 1);
            }

            return wpmu_signup_blog($domain, $path, $blog_title, $user_name, $user_email, $site_meta);
        }

        $blog_id = wpmu_create_blog($domain, $path, $blog_title, $user_id, $user_email, $site_meta);

        wpmu_welcome_notification($blog_id, $user_id, $user_data['user_pass'], $blog_title, ['public' => 1]);
    }

    /**
     * Normalize unamed shortcode
     *
     * @param array $atts
     *
     * @return mixed
     */
    public static function normalize_attributes($atts)
    {
        if (is_array($atts)) {
            foreach ($atts as $key => $value) {
                if (is_int($key)) {
                    $atts[$value] = true;
                    unset($atts[$key]);
                }
            }
        }

        return $atts;
    }


    /**
     * Shortcode callback for site address.
     *
     * @param $atts
     *
     * @return mixed|void
     */
    public function site_address_shortcode($atts)
    {
        $atts = self::normalize_attributes($atts);

        // grab unofficial attributes
        $other_atts_html = ppress_other_field_atts($atts);

        $atts = apply_filters('ppress_registration_ms_site_address_field_atts', $atts);

        $attributes = array_filter([
            'class'       => ppress_var($atts, 'class'),
            'id'          => ppress_var($atts, 'id'),
            'title'       => ppress_var($atts, 'title'),
            'placeholder' => ppress_var($atts, 'placeholder'),
        ]);

        $attributes_implode = '';
        foreach ($attributes as $key => $value) {
            $attributes_implode .= sprintf('%s="%s" ', $key, esc_attr($value));
        }

        $value    = esc_attr(isset($_POST['reg_ms_site_address']) ? $_POST['reg_ms_site_address'] : ppress_var($atts, 'value', ''));
        $required = RegistrationFormBuilder::is_field_required($atts) ? 'required="required"' : '';

        $html = "<input name='reg_ms_site_address' type='text' value='$value' $attributes_implode $other_atts_html $required>";

        return apply_filters('ppress_registration_ms_site_address_field', $html, $atts);
    }

    /**
     * Shortcode callback for site title.
     *
     * @param $atts
     *
     * @return mixed|void
     */
    public function site_title_shortcode($atts)
    {
        $atts = self::normalize_attributes($atts);

        // grab unofficial attributes
        $other_atts_html = ppress_other_field_atts($atts);

        $atts = apply_filters('ppress_registration_ms_site_title_field_atts', $atts);

        $attributes = array_filter([
            'class'       => ppress_var($atts, 'class'),
            'id'          => ppress_var($atts, 'id'),
            'title'       => ppress_var($atts, 'title'),
            'placeholder' => ppress_var($atts, 'placeholder'),
        ]);

        $attributes_implode = '';
        foreach ($attributes as $key => $value) {
            $attributes_implode .= sprintf('%s="%s" ', $key, esc_attr($value));
        }

        $value    = isset($_POST['reg_ms_site_title']) ? esc_attr($_POST['reg_ms_site_title']) : ppress_var($atts, 'value', '');
        $required = RegistrationFormBuilder::is_field_required($atts) ? 'required="required"' : '';

        $html = "<input name='reg_ms_site_title' type='text' value='$value' $attributes_implode $other_atts_html $required>";

        return apply_filters('ppress_registration_ms_site_title_field', $html, $atts);
    }

    /**
     * Return array of pages.
     *
     * @param int $limit
     *
     * @return array
     */
    public static function get_pages($limit = 99999)
    {
        global $wpdb;

        $table = $wpdb->posts;

        $sql = "SELECT ID, post_title FROM $table WHERE post_type = 'page' AND post_status = 'publish' LIMIT %d";

        $results = $wpdb->get_results(
            $wpdb->prepare($sql, absint(apply_filters('ppress_ms_page_limit', $limit))),
            'ARRAY_A'
        );

        $new_array = array('none' => __('Select...', 'profilepress-pro'));

        foreach ($results as $result) {
            $new_array[$result['ID']] = $result['post_title'];
        }

        return $new_array;
    }

    public function is_root_site()
    {
        global $current_blog, $current_site;

        return $current_site->blog_id == $current_blog->blog_id;
    }

    /**
     * @param array $settings
     *
     * @return array
     */
    public function settings_page($settings)
    {
        if ($this->is_root_site()) {
            $ec_settings = array(
                array(
                    'tab_title'                => esc_html__('Multisite Integration', 'profilepress-pro'),
                    'section_title'            => esc_html__('Multisite Settings', 'profilepress-pro'),
                    'dashicon'                 => 'dashicons-admin-multisite',
                    'ms_multisite_signup_page' => array(
                        'type'        => 'select',
                        'label'       => __('Multisite Signup Page', 'profilepress-pro'),
                        'options'     => self::get_pages(),
                        'description' => sprintf(
                            __('Select a page containing a ProfilePress registration shortcode that the default multisite signup page (%s) will be redirected to. ', 'profilepress-pro'),
                            network_site_url('wp-signup.php')
                        )
                    )
                )
            );

            $settings = array_merge($settings, $ec_settings);
        }

        return $settings;
    }

    /**
     * @return Init|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::MULTISITE')) return;

        if ( ! EM::is_enabled(EM::MULTISITE)) return;

        static $instance;
        if ( ! isset($instance)) {
            $instance = new self;
        }

        return $instance;
    }
}