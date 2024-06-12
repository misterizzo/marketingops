<?php

namespace ProfilePress\Libsodium\CustomProfileFields;

use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Custom_Settings_Page_Api;

class SettingsPage
{
    private $profile_fields_form_errors;

    /** @var WPListTable */
    private $myListTable;

    public function __construct()
    {
        add_filter('ppress_settings_page_screen_option', [$this, 'screen_options']);

        add_filter('ppress_settings_page_tabs', [$this, 'settings_tab']);
        add_filter('ppress_settings_page_submenus_tabs', [$this, 'settings_submenu_tab']);
        add_action('ppress_admin_settings_submenu_page_custom-fields_custom-fields', [$this, 'admin_page']);

        add_action('ppress_register_menu_page_custom-fields_custom-fields', function () {

            if (isset($_GET['post']) && $_GET['post'] == 'new') {
                $this->save_add_edit_profile_fields('add');
            }

            if (isset($_GET['action']) && $_GET['action'] == 'edit') {
                $this->save_add_edit_profile_fields('edit', absint($_GET['field']));
            }
        });

        add_action('ppress_register_menu_page_custom-fields_custom-fields', function () {

            add_filter('ppress_general_settings_admin_page_title', function () {
                return esc_html__('Custom Fields', 'profilepress-pro');
            });
        });
    }

    public function settings_tab($tabs)
    {
        $tabs[30] = ['id' => 'custom-fields', 'url' => add_query_arg('view', 'custom-fields', PPRESS_SETTINGS_SETTING_PAGE), 'label' => esc_html__('Custom Fields', 'profilepress-pro')];

        return $tabs;
    }

    public function settings_submenu_tab($tabs)
    {
        $tabs[30] = ['parent' => 'custom-fields', 'id' => 'custom-fields', 'label' => esc_html__('Custom Fields', 'profilepress-pro')];
        $tabs[31] = ['parent' => 'custom-fields', 'id' => 'contact-info', 'label' => esc_html__('Contact Info', 'profilepress-pro')];

        return $tabs;
    }

    public function admin_page_title()
    {
        $title = esc_html__('Custom Fields', 'profilepress-pro');

        if (isset($_GET['action']) && $_GET['action'] == 'edit') {
            $title = esc_html__('Edit Custom Field', 'profilepress-pro');
        }

        if (isset($_GET['post']) && $_GET['post'] == 'new') {
            $title = esc_html__('Add Custom Field', 'profilepress-pro');
        }

        return $title;
    }

    /**
     * Core contact info fields that can't be deleted.
     * @return array
     */
    public static function core_custom_fields()
    {
        return apply_filters('ppress_core_custom_fields', ['gender', 'country']);
    }

    public function screen_options()
    {
        if (isset($_GET['view']) && $_GET['view'] == 'custom-fields') {

            if (isset($_GET['post']) || isset($_GET['action']) || isset($_GET['type'])) {
                add_filter('screen_options_show_screen', '__return_false');
            }

            $this->myListTable = new WPListTable();
        }
    }

    // argument @operation determine if the method should save added field or edited field
    public function save_add_edit_profile_fields($operation, $id = '')
    {
        if ( ! isset($_POST['add_new_field']) && ! isset($_POST['edit_field'])) return;

        if ( ! check_admin_referer('pp_custom_profile_fields')) return;

        if ( ! current_user_can('manage_options')) return;

        $label_name  = stripslashes(esc_attr($_POST['cpf_label_name']));
        $key         = sanitize_text_field($_POST['cpf_key']);
        $description = stripslashes(sanitize_text_field($_POST['cpf_description']));
        $type        = sanitize_text_field($_POST['cpf_type']);
        $options     = stripslashes(sanitize_text_field($_POST['cpf_options']));
        if ($type == 'date') {
            $options = sanitize_text_field($_POST['date_format']);
        }

        $is_multi_selectable = ppressPOST_var('cpf_multi_select');

        if (empty($_POST['cpf_label_name'])) {
            return $this->profile_fields_form_errors = esc_html__('Field label is empty', 'profilepress-pro');
        }

        if (empty($_POST['cpf_key'])) {
            return $this->profile_fields_form_errors = esc_html__('Field Key is empty', 'profilepress-pro');
        }

        if (preg_match('/^[a-z0-9_]+$/', $_POST['cpf_key']) !== 1) {
            return $this->profile_fields_form_errors = esc_html__('Field key appears to be of an invalid format', 'profilepress-pro');
        }

        if (in_array($key, ppress_reserved_field_keys())) {
            return $this->profile_fields_form_errors = esc_html__('The Field Key you entered is a reserve word which is not permitted. Try another text.', 'profilepress-pro');
        }

        if (empty($_POST['cpf_type'])) {
            return $this->profile_fields_form_errors = esc_html__('Please choose a form type', 'profilepress-pro');
        }

        if ($_POST['cpf_type'] == 'select' && empty($_POST['cpf_options'])) {
            return $this->profile_fields_form_errors = esc_html__('Options field is required', 'profilepress-pro');
        }

        if ($_POST['cpf_type'] == 'date' && empty($_POST['date_format'])) {
            return $this->profile_fields_form_errors = esc_html__('Date/Time format is required', 'profilepress-pro');
        }

        if ($_POST['cpf_type'] == 'radio' && empty($_POST['cpf_options'])) {
            return $this->profile_fields_form_errors = esc_html__('Options field is required', 'profilepress-pro');
        }

        if (isset($_POST['add_new_field']) && $operation == 'add') {

            $new_custom_field_id = PROFILEPRESS_sql::add_profile_field($label_name, $key, $description, $type, $options);

            if ( ! is_int($new_custom_field_id)) {
                return $this->profile_fields_form_errors = esc_html__('Error adding custom field. Ensure it already does not exist.', 'profilepress-pro');
            }

            do_action('ppress_insert_custom_field_db', $new_custom_field_id, $_POST);

            if ($type == 'select' && $is_multi_selectable == 'yes') {
                PROFILEPRESS_sql::add_multi_selectable($key, $new_custom_field_id);
            }

            wp_safe_redirect(PPRESS_CUSTOM_FIELDS_SETTINGS_PAGE . '&field-added=true');
            exit;
        }

        if (isset($_POST['edit_field']) && $operation == 'edit') {
            $update = PROFILEPRESS_sql::update_profile_field($id, $label_name, $key, $description, $type, $options);

            if ($update !== false) do_action('ppress_update_custom_field_db', $id, $_POST);

            if ($type == 'select' && 'yes' == $is_multi_selectable) {
                PROFILEPRESS_sql::add_multi_selectable($key, $id);
            } else {
                PROFILEPRESS_sql::delete_multi_selectable($key);
            }

            wp_safe_redirect(esc_url_raw(add_query_arg('field-edited', 'true')));
            exit;
        }
    }

    public function admin_notices()
    {
        if ( ! isset($_GET['field-added']) && ! isset($_GET['field-edited']) && ! isset($this->profile_fields_form_errors)) return;

        $status = 'updated';
        if (isset($this->profile_fields_form_errors)) {
            $message = $this->profile_fields_form_errors;
            $status  = 'error';
        }

        if (isset($_GET['field-edited'])) {
            $message = esc_html__('Custom field edited', 'profilepress-pro');
        }

        if (isset($_GET['field-added'])) {
            $message = esc_html__('Custom field added', 'profilepress-pro');
        }

        printf('<div id="message" class="%s notice is-dismissible"><p>%s</strong></p></div>', $status, $message);
    }

    public static function custom_field_page_note()
    {
        echo '<div class="pp-custom-field-notice"><p>';
        printf(
            esc_html__('Contact Info are custom fields. The difference only is that they are added to the %sContact Info%s section of %sWordPress user profile%s while custom fields are added to %1$sOther information%2$s section.', 'profilepress-pro'),
            '<strong>', '</strong>',
            '<a href="' . admin_url('profile.php') . '" target="_blank">', '</a>'
        );
        echo '</p></div>';
    }

    public function admin_page()
    {
        add_action('wp_cspa_main_content_area', array($this, 'admin_page_callback'), 10, 2);
        add_action('wp_cspa_before_closing_header', [$this, 'add_new_button']);

        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name('ppview'); // adds ppview css class to #poststuff
        $instance->add_view_classes('pp-custom-fields');
        $instance->page_header($this->admin_page_title());
        $instance->remove_white_design();
        $instance->build(true);
    }

    public function add_new_button()
    {
        if ( ! isset($_GET['post']) && ! isset($_GET['action'])) {
            $url = esc_url_raw(add_query_arg('post', 'new'));
            echo "<a class=\"add-new-h2\" href=\"$url\">" . esc_html__('Add New', 'profilepress-pro') . '</a>';
        }
    }

    public function admin_page_callback()
    {
        $this->myListTable->prepare_items(); // has to be here.

        if (isset($_GET['post']) || isset($_GET['action'])) {
            $this->admin_notices();
            require_once dirname(__FILE__) . '/include.view.php';

            return;
        }

        self::custom_field_page_note();

        echo '<form method="post">';
        $this->myListTable->display();
        echo '</form>';

        do_action('ppress_custom_field_wp_list_table_bottom');
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