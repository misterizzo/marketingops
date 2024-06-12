<?php

namespace ProfilePress\Libsodium\ShortcodeBuilder\EditShortcodePasswordReset;


use ProfilePress\Libsodium\ShortcodeBuilder\ShortcodeInserterTrait;
use ProfilePress\Core\Classes\FormRepository;

class EditShortcodePasswordReset
{
    use ShortcodeInserterTrait;

    private $password_reset_builder_errors;

    public function __construct()
    {
        add_action('admin_init', [$this, 'save_edit']);
        add_action('ppress_admin_notices', [$this, 'admin_notices']);
    }

    public function available_shortcodes()
    {
        return apply_filters('ppress_password_reset_available_shortcodes', [
                'user-login'            => [
                    'description' => esc_html__('Username / Email field - Password reset form', 'profilepress-pro'),
                    'shortcode'   => 'user-login',
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
                        ]
                    ]
                ],
                'reset-submit'          => [
                    'description' => esc_html__('Submit button - Password reset form', 'profilepress-pro'),
                    'shortcode'   => 'reset-submit',
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
                'enter-password'        => [
                    'description' => esc_html__('Password field  - Password reset handler form', 'profilepress-pro'),
                    'shortcode'   => 'enter-password',
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
                        ]
                    ]
                ],
                're-enter-password'     => [
                    'description' => esc_html__('Confirm password field  - Password reset handler form', 'profilepress-pro'),
                    'shortcode'   => 're-enter-password',
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
                        ]
                    ]
                ],
                'reset-password-meter'  => [
                    'description' => esc_html__('Password strength meter  - Password reset handler form', 'profilepress-pro'),
                    'shortcode'   => 'reset-password-meter',
                    'attributes'  => [
                        'enforce' => [
                            'label'   => esc_html__('Enforce strong password', 'profilepress-pro'),
                            'field'   => 'select',
                            'options' => ['true' => esc_html__('Yes', 'profilepress-pro'), 'false' => esc_html__('No', 'profilepress-pro')]
                        ],
                        'class'   => [
                            'label' => esc_html__('CSS class', 'profilepress-pro'),
                            'field' => 'text'
                        ]
                    ]
                ],
                'password-reset-submit' => [
                    'description' => esc_html__('Submit button - Password reset handler form', 'profilepress-pro'),
                    'shortcode'   => 'password-reset-submit',
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
        if (isset($this->password_reset_builder_errors)) {
            echo '<div id="message" class="error notice is-dismissible"><p><strong>' . $this->password_reset_builder_errors . '</strong></p></div>';

            return;
        }

        if (ppressGET_var('view') == 'edit-shortcode-password-reset' && ppressGET_var('form-edited')) {
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
        if ( ! current_user_can('manage_options') || ! isset($_POST['edit_password_reset']) || ! ppress_verify_nonce()) return;

        $id = absint($_GET['id']);

        $title                  = @sanitize_text_field($_POST['prb_title']);
        $structure              = @stripslashes($_POST['prb_structure']);
        $handler_structure      = @stripslashes($_POST['prb_handler_structure']);
        $css                    = @stripslashes($_POST['prb_css']);
        $success_password_reset = @stripslashes($_POST['prb_success_password_reset']);

        // catch and save form generated errors in property @password_reset_builder_errors
        if (empty($title)) {
            $this->password_reset_builder_errors = esc_html__('Title is empty', 'profilepress-pro');
        } elseif (empty($structure)) {
            $this->password_reset_builder_errors = esc_html__('Structure is missing', 'profilepress-pro');
        } elseif (empty($handler_structure)) {
            $this->password_reset_builder_errors = esc_html__('Password Reset Handler Form Structure is missing', 'profilepress-pro');
        }

        if (isset($this->password_reset_builder_errors)) return;

        FormRepository::update_form(
            $id,
            FormRepository::PASSWORD_RESET_TYPE,
            $title,
            apply_filters('ppress_shortcode_builder_registration_meta', [
                FormRepository::FORM_STRUCTURE         => $structure,
                FormRepository::FORM_CSS               => $css,
                FormRepository::PROCESSING_LABEL       => sanitize_text_field($_POST['processing_label']),
                FormRepository::SUCCESS_MESSAGE        => $success_password_reset,
                FormRepository::PASSWORD_RESET_HANDLER => $handler_structure
            ])
        );

        wp_safe_redirect(esc_url_raw(add_query_arg('form-edited', 'true')));
        exit;
    }

    /**
     * @return EditShortcodePasswordReset
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