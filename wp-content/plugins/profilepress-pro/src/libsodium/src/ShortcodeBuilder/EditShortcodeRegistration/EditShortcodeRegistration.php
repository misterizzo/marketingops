<?php

namespace ProfilePress\Libsodium\ShortcodeBuilder\EditShortcodeRegistration;


use ProfilePress\Libsodium\ShortcodeBuilder\ShortcodeInserterTrait;
use ProfilePress\Core\Classes\FormRepository;

class EditShortcodeRegistration
{
    use ShortcodeInserterTrait;

    private $registration_builder_errors;

    public function __construct()
    {
        add_action('admin_init', [$this, 'save_edit']);
        add_action('ppress_admin_notices', [$this, 'admin_notices']);
    }

    public function available_shortcodes()
    {
        return self::reg_edit_profile_available_shortcodes();
    }

    public function admin_notices()
    {
        if (isset($this->registration_builder_errors)) {
            echo '<div id="message" class="error notice is-dismissible"><p><strong>' . $this->registration_builder_errors . '</strong></p></div>';

            return;
        }

        if (ppressGET_var('view') == 'edit-shortcode-registration' && ppressGET_var('form-edited')) {
            echo '<div id="message" class="updated notice is-dismissible"><p><strong>' . esc_html__('Form updated', 'profilepress-pro') . '</strong></p></div>';
        }
    }

    public function edit_screen()
    {
        // we're using here because this method is only called when the view is called for the form type.
        $this->traitInit($this->available_shortcodes());

        require dirname(__FILE__) . '/edit_screen.php';
    }

    public function save_edit()
    {
        if ( ! current_user_can('manage_options') || ! isset($_POST['edit_registration']) || ! ppress_verify_nonce()) return;

        $id                           = absint($_GET['id']);
        $title                        = @sanitize_text_field($_POST['rfb_title']);
        $structure                    = @stripslashes($_POST['rfb_structure']);
        $css                          = @stripslashes($_POST['rfb_css']);
        $success_registration         = @stripslashes($_POST['rfb_success_registration']);
        $user_role                    = esc_attr($_POST['rfb_new_user_role']);
        $disable_username_requirement = esc_attr(@$_POST['rfb_disable_username_requirement']);

        // catch and save form generated errors in property @registration_builder_errors
        if (empty($title)) {
            $this->registration_builder_errors = esc_html__('Title is empty', 'profilepress-pro');
        } elseif (empty($structure)) {
            $this->registration_builder_errors = esc_html__('Structure is missing', 'profilepress-pro');
        }

        if (isset($this->registration_builder_errors)) return;

        FormRepository::update_form(
            $id,
            FormRepository::REGISTRATION_TYPE,
            $title,
            apply_filters('ppress_shortcode_builder_registration_meta', [
                FormRepository::FORM_STRUCTURE               => $structure,
                FormRepository::FORM_CSS                     => $css,
                FormRepository::PROCESSING_LABEL             => sanitize_text_field($_POST['processing_label']),
                FormRepository::SUCCESS_MESSAGE              => $success_registration,
                FormRepository::REGISTRATION_USER_ROLE       => $user_role,
                FormRepository::DISABLE_USERNAME_REQUIREMENT => $disable_username_requirement == 'yes'
            ])
        );

        wp_safe_redirect(esc_url_raw(add_query_arg('form-edited', 'true')));
        exit;
    }

    /**
     * @return EditShortcodeRegistration
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