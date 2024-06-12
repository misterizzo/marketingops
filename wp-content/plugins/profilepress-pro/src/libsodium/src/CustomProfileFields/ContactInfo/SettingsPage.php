<?php

namespace ProfilePress\Libsodium\CustomProfileFields\ContactInfo;

use ProfilePress\Core\Classes\WPProfileFieldParserTrait;
use ProfilePress\Core\Membership\CheckoutFields;
use ProfilePress\Libsodium\CustomProfileFields\SettingsPage as CustomProfileFieldsSettingsPage;
use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Custom_Settings_Page_Api;

class SettingsPage
{
    use WPProfileFieldParserTrait;

    private $contact_info_errors;

    public function __construct()
    {
        add_action('ppress_register_menu_page_custom-fields_contact-info', function () {

            add_filter('ppress_general_settings_admin_page_title', function () {
                return esc_html__('Contact Info &lsaquo; Custom Fields', 'profilepress-pro');
            });

            if (isset($_GET['delete']) && is_string($_GET['delete'])) {
                $this->delete_contact_info(ppress_sanitize_key($_GET['delete']));
            }

            $this->save_contact_info();
        });

        add_action('ppress_admin_settings_submenu_page_custom-fields_contact-info', [$this, 'admin_page']);

        add_filter('user_contactmethods', [$this, 'modify_user_contact_methods'], 99);
    }

    /**
     * modify WordPress contact information in user profile.
     *
     * @param $user_contact
     *
     * @return mixed
     */
    public function modify_user_contact_methods($user_contact)
    {
        $db_contact_info = PROFILEPRESS_sql::get_contact_info_fields();

        if ( ! empty($db_contact_info)) {

            foreach ($db_contact_info as $key => $label) {

                if (in_array($key, array_keys(CheckoutFields::standard_billing_fields()))) continue;

                $user_contact[$key] = __($label);
            }
        }

        return $user_contact;
    }

    /**
     * save added contact info to db
     *
     * @return void
     */
    function save_contact_info()
    {
        if ( ! isset($_POST['save_contact_info'])) return;

        check_admin_referer('pp-save-contact-info');

        if ( ! current_user_can('manage_options')) return;

        $ci_name = sanitize_text_field($_POST['ci_label_name']);

        $ci_key = isset($_POST['ci_key']) ? ppress_sanitize_key($_POST['ci_key']) : '';

        if (empty($_POST['ci_label_name'])) {
            $this->contact_info_errors = esc_html__('Field Label is empty', 'profilepress-pro');
        }

        if (empty($_POST['ci_key']) && empty($_GET['edit'])) {
            $this->contact_info_errors = esc_html__('Field Key is empty', 'profilepress-pro');
        }

        if ( ! empty($_POST['ci_key']) && preg_match('/^[a-z0-9_]+$/', $_POST['ci_key']) !== 1) {
            $this->contact_info_errors = esc_html__('Field key appears to be of an invalid format', 'profilepress-pro');
        }

        if ( ! empty($ci_key) && in_array($ci_key, ppress_reserved_field_keys())) {
            $this->contact_info_errors = esc_html__('The Field Key you entered is a reserve word which is not permitted. Try another text.', 'profilepress-pro');
        }

        if (isset($this->contact_info_errors)) return;

        $old_value = PROFILEPRESS_sql::get_contact_info_fields();

        if (isset($_GET['edit']) && ! empty($_GET['edit'])) {
            $old_value[ppress_sanitize_key($_GET['edit'])] = $ci_name;
            $merge_data                                    = $old_value;
        } else {
            $new_value  = [$ci_key => $ci_name];
            $merge_data = array_merge($old_value, $new_value);
        }

        if (update_option(PPRESS_CONTACT_INFO_OPTION_NAME, $merge_data)) {
            wp_safe_redirect(PPRESS_CONTACT_INFO_SETTINGS_PAGE . '&updated-contact-info=saved');
            exit;
        }
    }

    public function admin_notices()
    {
        if (isset($_GET['updated-contact-info']) && $_GET['updated-contact-info'] == 'saved') {
            $message = esc_html__('Contact information field saved.', 'profilepress-pro');
        }

        $status = 'updated';

        if (isset($this->contact_info_errors)) {
            $status  = 'error';
            $message = $this->contact_info_errors;
        }

        if (empty($message)) return;

        echo '<div class="' . $status . ' notice is-dismissible"><p><strong>' . $message . '</strong></p></div>';
    }

    public function delete_contact_info($key_to_delete)
    {
        check_admin_referer('pp-delete-contact-info');

        if (current_user_can('manage_options')) {

            $old_value = get_option(PPRESS_CONTACT_INFO_OPTION_NAME, []);

            if ( ! in_array($key_to_delete, ppress_social_network_fields())) {

                foreach ($old_value as $key => $value) {
                    if ($key == $key_to_delete) {
                        unset($old_value[$key_to_delete]);
                    }
                }

                update_option(PPRESS_CONTACT_INFO_OPTION_NAME, $old_value);
            }
        }

        wp_safe_redirect(PPRESS_CONTACT_INFO_SETTINGS_PAGE);
        exit;
    }

    public function admin_page_title()
    {
        if (isset($_GET['contact-info']) && $_GET['contact-info'] == 'new') {
            return esc_html__('Add Contact Information', 'profilepress-pro');
        }

        if (isset($_GET['edit'])) {
            return esc_html__('Edit Contact Information', 'profilepress-pro');
        }

        return esc_html__('Contact Information', 'profilepress-pro');
    }


    public function admin_page()
    {
        add_action('wp_cspa_main_content_area', array($this, 'admin_page_callback'), 10, 2);
        add_action('wp_cspa_before_closing_header', [$this, 'add_new_button']);
        add_action('wp_cspa_settings_after_tab', [$this, 'after_tabs_header']);

        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name('ppview'); // adds ppview css class to #poststuff
        $instance->add_view_classes('pp-custom-fields');
        $instance->page_header($this->admin_page_title());
        $instance->build(true);
    }

    public function after_tabs_header()
    {
        $this->admin_notices();

        if ( ! isset($_GET['contact-info']) && ! isset($_GET['edit'])) {

            return printf('<ul class="subsubsub"><li><a href="%s">%s</a> |</li><li><a href="%s"  class="current">%s</a></li></ul>',
                PPRESS_CUSTOM_FIELDS_SETTINGS_PAGE, esc_html__('Custom Fields', 'profilepress-pro'), PPRESS_CONTACT_INFO_SETTINGS_PAGE, esc_html__('Contact Info', 'profilepress-pro')
            );
        }

        printf('<br><a class="button-secondary" href="%s" title="%2$s">%2$s</a>',
            PPRESS_CONTACT_INFO_SETTINGS_PAGE, esc_html__('<< Back to Contact Info', 'profilepress-pro')
        );
    }

    public function add_new_button()
    {
        if ( ! isset($_GET['contact-info']) && ! isset($_GET['edit'])) {
            $url = esc_url_raw(add_query_arg('contact-info', 'new', PPRESS_CONTACT_INFO_SETTINGS_PAGE));
            echo "<a class=\"add-new-h2\" href=\"$url\">" . esc_html__('Add New', 'profilepress-pro') . '</a>';
        }
    }

    public function admin_page_callback()
    {
        if ((isset($_GET['contact-info']) && $_GET['contact-info'] == 'new') || ! empty($_GET['edit'])) {
            require_once dirname(__FILE__) . '/include.add-edit-view.php';

            return;
        }

        CustomProfileFieldsSettingsPage::custom_field_page_note();

        require dirname(__FILE__) . '/include.catalog-view.php';
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