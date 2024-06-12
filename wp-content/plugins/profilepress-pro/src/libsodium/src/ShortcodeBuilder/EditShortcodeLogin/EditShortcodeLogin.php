<?php

namespace ProfilePress\Libsodium\ShortcodeBuilder\EditShortcodeLogin;


use ProfilePress\Libsodium\ShortcodeBuilder\ShortcodeInserterTrait;
use ProfilePress\Core\Classes\FormRepository as FR;

class EditShortcodeLogin
{
    use ShortcodeInserterTrait;

    private $login_builder_errors;

    public function __construct()
    {
        add_action('admin_init', [$this, 'save_edit']);
        add_action('ppress_admin_notices', [$this, 'admin_notices']);
    }

    public function available_shortcodes()
    {
        return apply_filters('ppress_login_available_shortcodes', [
                'login-username' => [
                    'description' => esc_html__('Username / Email address field', 'profilepress-pro'),
                    'shortcode'   => 'login-username',
                    'attributes'  => self::popular_attributes()
                ],
                'login-password' => [
                    'description' => esc_html__('Password field', 'profilepress-pro'),
                    'shortcode'   => 'login-password',
                    'attributes'  => self::popular_attributes()
                ],
                'login-remember' => [
                    'description' => esc_html__('Remeber Me checkbox', 'profilepress-pro'),
                    'shortcode'   => 'login-remember',
                    'attributes'  => [
                        'id'    => [
                            'label' => esc_html__('ID', 'profilepress-pro'),
                            'field' => 'text'
                        ],
                        'class' => [
                            'label' => esc_html__('CSS class', 'profilepress-pro'),
                            'field' => 'text'
                        ]
                    ]
                ],
                'login-submit'   => [
                    'description' => esc_html__('Form submit button', 'profilepress-pro'),
                    'shortcode'   => 'login-submit',
                    'attributes'  => [
                        'value'            => [
                            'label' => esc_html__('Button label', 'profilepress-pro'),
                            'field' => 'text'
                        ],
                        'processing_label' => [
                            'label' => esc_html__('Processing label', 'profilepress-pro'),
                            'field' => 'text'
                        ],
                        'id'               => [
                            'label' => esc_html__('ID', 'profilepress-pro'),
                            'field' => 'text'
                        ],
                        'class'            => [
                            'label' => esc_html__('CSS class', 'profilepress-pro'),
                            'field' => 'text'
                        ],
                    ]
                ],
            ]) + self::global_shortcodes();
    }

    public function admin_notices()
    {
        if (isset($this->login_builder_errors)) {
            echo '<div id="message" class="error notice is-dismissible"><p><strong>' . $this->login_builder_errors . '</strong></p></div>';

            return;
        }

        if (ppressGET_var('view') == 'edit-shortcode-login' && ppressGET_var('form-edited')) {
            echo '<div id="message" class="updated notice is-dismissible"><p><strong>' . esc_html__('Form updated', 'profilepress-pro') . '</strong></p></div>';
        }
    }

    public function edit_screen()
    {
        $this->traitInit($this->available_shortcodes());
        require dirname(__FILE__) . '/edit_screen.php';
    }

    public function save_edit()
    {
        if ( ! current_user_can('manage_options') || ! isset($_POST['edit_login']) || ! ppress_verify_nonce()) return;

        $id                = absint($_GET['id']);
        $title             = @sanitize_text_field($_POST['lfb_title']);
        $structure         = @stripslashes($_POST['lfb_structure']);
        $css               = @stripslashes($_POST['lfb_css']);
        $make_passwordless = @sanitize_text_field($_POST['lfb_make_passwordless']);

        if (empty($title)) {
            $this->login_builder_errors = esc_html__('Title is empty', 'profilepress-pro');
        } elseif (empty($structure)) {
            $this->login_builder_errors = esc_html__('Structure is missing', 'profilepress-pro');
        }

        if (isset($this->login_builder_errors)) return;

        FR::update_form(
            $id,
            FR::LOGIN_TYPE,
            $title,
            apply_filters('ppress_shortcode_builder_registration_meta', [
                FR::FORM_STRUCTURE     => $structure,
                FR::FORM_CSS           => $css,
                FR::PROCESSING_LABEL   => sanitize_text_field($_POST['processing_label']),
                FR::PASSWORDLESS_LOGIN => ! empty($make_passwordless)
            ])
        );

        wp_safe_redirect(esc_url_raw(add_query_arg('form-edited', 'true')));
        exit;
    }

    /**
     * @return EditShortcodeLogin
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