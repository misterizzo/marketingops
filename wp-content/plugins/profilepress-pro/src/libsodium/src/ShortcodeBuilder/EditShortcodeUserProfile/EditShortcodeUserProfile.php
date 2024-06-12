<?php

namespace ProfilePress\Libsodium\ShortcodeBuilder\EditShortcodeUserProfile;

use ProfilePress\Libsodium\ShortcodeBuilder\ShortcodeInserterTrait;
use ProfilePress\Core\Classes\FormRepository;

class EditShortcodeUserProfile
{
    use ShortcodeInserterTrait;

    private $builder_errors;

    public function __construct()
    {
        add_action('admin_init', [$this, 'save_edit']);
        add_action('ppress_admin_notices', [$this, 'admin_notices']);
    }

    public function available_shortcodes()
    {
        return apply_filters('ppress_user_profile_available_shortcodes', [
                'profile-username'         => [
                    'description' => esc_html__('Username of user', 'profilepress-pro'),
                    'shortcode'   => 'profile-username',
                ],
                'profile-email'            => [
                    'description' => esc_html__('Email address of user', 'profilepress-pro'),
                    'shortcode'   => 'profile-email',
                ],
                'profile-website'          => [
                    'description' => esc_html__('Website URL of user', 'profilepress-pro'),
                    'shortcode'   => 'profile-website',
                ],
                'profile-nickname'         => [
                    'description' => esc_html__('Nickname of user', 'profilepress-pro'),
                    'shortcode'   => 'profile-nickname',
                ],
                'profile-display-name'     => [
                    'description' => esc_html__('Display name of user', 'profilepress-pro'),
                    'shortcode'   => 'profile-display-name',
                ],
                'profile-first-name'       => [
                    'description' => esc_html__('First name of user', 'profilepress-pro'),
                    'shortcode'   => 'profile-first-name',
                ],
                'profile-last-name'        => [
                    'description' => esc_html__('Last name of user', 'profilepress-pro'),
                    'shortcode'   => 'profile-last-name',
                ],
                'profile-bio'              => [
                    'description' => esc_html__('Biographical info of user', 'profilepress-pro'),
                    'shortcode'   => 'profile-bio',
                ],
                'profile-cpf'              => [
                    'description' => esc_html__('Custom field information', 'profilepress-pro'),
                    'shortcode'   => 'profile-cpf',
                    'attributes'  => [
                        'key' => [
                            'label' => esc_html__('Field key', 'profilepress-pro'),
                            'field' => 'text'
                        ]
                    ]
                ],
                'profile-file'             => [
                    'description' => esc_html__('Link to uploaded file', 'profilepress-pro'),
                    'shortcode'   => 'profile-file',
                    'attributes'  => [
                        'key' => [
                            'label' => esc_html__('Field key', 'profilepress-pro'),
                            'field' => 'text'
                        ],
                        'raw' => [
                            'label' => esc_html__('Check to return URL', 'profilepress-pro'),
                            'field' => 'checkbox'
                        ],
                    ]
                ],
                'profile-avatar-url'       => [
                    'description' => esc_html__('URL of User profile picture or avatar', 'profilepress-pro'),
                    'shortcode'   => 'profile-avatar-url'
                ],
                'profile-date-registered'  => [
                    'description' => esc_html__('Date of user registration', 'profilepress-pro'),
                    'shortcode'   => 'profile-date-registered'
                ],
                'profile-post-count'       => [
                    'description' => esc_html__('Number of posts published by user', 'profilepress-pro'),
                    'shortcode'   => 'profile-post-count'
                ],
                'profile-comment-count'    => [
                    'description' => esc_html__('Number of comments submitted by user', 'profilepress-pro'),
                    'shortcode'   => 'profile-comment-count'
                ],
                'profile-post-list'        => [
                    'description' => esc_html__('List of posts authored by user', 'profilepress-pro'),
                    'shortcode'   => 'profile-post-list',
                    'attributes'  => [
                        'limit' => [
                            'label' => esc_html__('Limit', 'profilepress-pro'),
                            'field' => 'number'
                        ]
                    ]
                ],
                'profile-comment-list'        => [
                    'description' => esc_html__('List of comments by user', 'profilepress-pro'),
                    'shortcode'   => 'profile-comment-list',
                    'attributes'  => [
                        'limit' => [
                            'label' => esc_html__('Limit', 'profilepress-pro'),
                            'field' => 'number'
                        ]
                    ]
                ],
                'profile-author-posts-url' => [
                    'description' => esc_html__('URL to author posts page', 'profilepress-pro'),
                    'shortcode'   => 'profile-author-posts-url'
                ],
                'profile-hide-empty-data'  => [
                    'description' => esc_html__('Hide content if profile information is empty. Do not forget to close the shortcode with [/profile-hide-empty-data]', 'profilepress-pro'),
                    'shortcode'   => 'profile-hide-empty-data',
                    'attributes'  => [
                        'field' => [
                            'label' => esc_html__('Field ID', 'profilepress-pro'),
                            'field' => 'text'
                        ]
                    ]
                ],
            ]) + self::global_shortcodes();
    }

    public function admin_notices()
    {
        if (isset($this->builder_errors)) {
            echo '<div id="message" class="error notice is-dismissible"><p><strong>' . $this->builder_errors . '</strong></p></div>';

            return;
        }

        if (ppressGET_var('view') == 'edit-shortcode-user-profile' && ppressGET_var('form-edited')) {
            echo '<div id="message" class="updated notice is-dismissible"><p><strong>' . esc_html__('Changes saved.', 'profilepress-pro') . '</strong></p></div>';
        }
    }

    public function edit_screen()
    {
        $this->traitInit($this->available_shortcodes());
        require dirname(__FILE__) . '/edit_screen.php';
    }

    public function save_edit()
    {
        if ( ! current_user_can('manage_options') || ! isset($_POST['edit_user_profile_page']) || ! ppress_verify_nonce()) return;

        $id        = absint($_GET['id']);
        $title     = @sanitize_text_field($_POST['fep_title']);
        $structure = @stripslashes($_POST['fep_structure']);
        $css       = @stripslashes($_POST['fep_css']);


        if (empty($title)) {
            $this->builder_errors = esc_html__('Title is empty', 'profilepress-pro');
        } elseif (empty($structure)) {
            $this->builder_errors = esc_html__('Structure is missing', 'profilepress-pro');
        }

        if (isset($this->builder_errors)) return;

        FormRepository::update_form(
            $id,
            FormRepository::USER_PROFILE_TYPE,
            $title,
            apply_filters('ppress_shortcode_builder_registration_meta', [
                FormRepository::FORM_STRUCTURE => $structure,
                FormRepository::FORM_CSS       => $css,
            ])
        );

        wp_safe_redirect(esc_url_raw(add_query_arg('form-edited', 'true')));
        exit;
    }

    /**
     * @return EditShortcodeUserProfile
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