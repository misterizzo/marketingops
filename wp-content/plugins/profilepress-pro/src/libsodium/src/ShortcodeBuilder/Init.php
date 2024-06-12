<?php

namespace ProfilePress\Libsodium\ShortcodeBuilder;

use ProfilePress\Core\Admin\SettingsPages\Forms;

use ProfilePress\Libsodium\ShortcodeBuilder\EditShortcodeEditProfile\EditShortcodeEditProfile;
use ProfilePress\Libsodium\ShortcodeBuilder\EditShortcodeLogin\EditShortcodeLogin;
use ProfilePress\Libsodium\ShortcodeBuilder\EditShortcodePasswordReset\EditShortcodePasswordReset;
use ProfilePress\Libsodium\ShortcodeBuilder\EditShortcodeMelange\EditShortcodeMelange;
use ProfilePress\Libsodium\ShortcodeBuilder\EditShortcodeRegistration\EditShortcodeRegistration;
use ProfilePress\Libsodium\ShortcodeBuilder\EditShortcodeUserProfile\EditShortcodeUserProfile;
use ProfilePress\Core\Classes\FormRepository as FR;

class Init
{
    protected $EditShortcodeLoginInstance;
    protected $EditShortcodeRegistrationInstance;
    protected $EditShortcodePasswordResetInstance;
    protected $EditShortcodeMelangeInstance;
    protected $EditShortcodeEditProfileInstance;
    protected $EditShortcodeUserProfileInstance;

    public function __construct()
    {
        add_action('wp_ajax_pp-builder-preview', [$this, 'builder_preview_handler']);

        add_action('ppress_admin_forms_class_constructor', [$this, 'load_shortcode_builder_classes']);

        add_filter('ppress_forms_settings_admin_page_short_circuit', [$this, 'shortcode_builder_admin_page']);
    }

    public function load_shortcode_builder_classes()
    {
        $this->EditShortcodeLoginInstance         = EditShortcodeLogin::get_instance();
        $this->EditShortcodeRegistrationInstance  = EditShortcodeRegistration::get_instance();
        $this->EditShortcodePasswordResetInstance = EditShortcodePasswordReset::get_instance();
        $this->EditShortcodeEditProfileInstance   = EditShortcodeEditProfile::get_instance();
        $this->EditShortcodeMelangeInstance       = EditShortcodeMelange::get_instance();
        $this->EditShortcodeUserProfileInstance   = EditShortcodeUserProfile::get_instance();
    }

    public function shortcode_builder_admin_page()
    {
        if ( ! empty($_GET['view'])) {

            $form_id = absint($_GET['id']);

            $page_header = Forms::get_instance()->admin_page_title();

            $shortcode_builder_page_header = sprintf(
                '<div class="wrap ppSCB"><h2>%s %s</h2><form method="post">%s',
                $page_header,
                Forms::get_instance()->live_form_preview_btn(false),
                ppress_nonce_field()
            );

            if ($_GET['view'] == 'edit-shortcode-login') {
                Forms::get_instance()->no_form_exist_redirect($form_id, FR::LOGIN_TYPE);
                echo $shortcode_builder_page_header;
                $this->EditShortcodeLoginInstance->edit_screen();
                echo '</form></div>';

                return true;
            }

            if ($_GET['view'] == 'edit-shortcode-registration') {
                Forms::get_instance()->no_form_exist_redirect($form_id, FR::REGISTRATION_TYPE);
                echo $shortcode_builder_page_header;
                $this->EditShortcodeRegistrationInstance->edit_screen();
                echo '</form></div>';

                return true;
            }

            if ($_GET['view'] == 'edit-shortcode-password-reset') {
                Forms::get_instance()->no_form_exist_redirect($form_id, FR::PASSWORD_RESET_TYPE);
                echo $shortcode_builder_page_header;
                $this->EditShortcodePasswordResetInstance->edit_screen();
                echo '</form></div>';

                return true;
            }

            if ($_GET['view'] == 'edit-shortcode-melange') {
                Forms::get_instance()->no_form_exist_redirect($form_id, FR::MELANGE_TYPE);
                echo $shortcode_builder_page_header;
                $this->EditShortcodeMelangeInstance->edit_screen();
                echo '</form></div>';

                return true;
            }

            if ($_GET['view'] == 'edit-shortcode-edit-profile') {
                Forms::get_instance()->no_form_exist_redirect($form_id, FR::EDIT_PROFILE_TYPE);
                echo $shortcode_builder_page_header;
                $this->EditShortcodeEditProfileInstance->edit_screen();
                echo '</form></div>';

                return true;
            }

            if ($_GET['view'] == 'edit-shortcode-user-profile') {
                Forms::get_instance()->no_form_exist_redirect($form_id, FR::USER_PROFILE_TYPE);
                echo $shortcode_builder_page_header;
                $this->EditShortcodeUserProfileInstance->edit_screen();
                echo '</form></div>';

                return true;
            }
        }

        return false;
    }

    public function builder_preview_handler()
    {
        check_ajax_referer('ppress-admin-nonce');

        if (current_user_can('manage_options')) {
            // iframe preview url content
            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'pp-builder-preview') {
                include dirname(__FILE__) . '/builder-preview.php';
            }
        }

        // IMPORTANT: don't forget to "exit"
        wp_die();
    }

    /**
     * @return self|void
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