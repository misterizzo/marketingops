<?php

namespace ProfilePress\Libsodium;

use ProfilePress\Core\Classes\ExtensionManager as EM;

class PolylangIntegration
{
    public static $instance_flag = false;

    public function __construct()
    {
        add_action('pll_init', function () {
            add_filter('ppress_login_url', array($this, 'detect_language_login_url'), 999999999);
            add_filter('ppress_registration_url', array($this, 'detect_language_registration_url'), 999999999);
            add_filter('ppress_password_reset_url', array($this, 'detect_language_password_reset_url'), 999999999);
            add_filter('ppress_my_account_url', array($this, 'detect_language_my_account_url'), 999999999);
            add_filter('ppress_profile_url', array($this, 'detect_language_profile_url'), 999999999);
            add_filter('ppress_profile_page_id', array($this, 'change_profile_page_id'), 999999999);

            // actually needed to make things work.
            add_action('parse_query', function () {
                global $post;

                static $flag = false;

                if (isset($post->post_content) &&
                    false === $flag &&
                    false !== ppress_mb_function(['mb_strpos', 'strpos'], [$post->post_content, 'profilepress-user-profile'])
                ) {
                    flush_rewrite_rules();
                    $flag = true;
                }

            }, 10, 2);

            $this->rewrite_frontend_profile_url();

            add_filter('ppress_settings_page_args', array($this, 'settings_page'));
        });
    }

    /**
     * Rewrite the profile url.
     */
    public function rewrite_frontend_profile_url()
    {
        if (function_exists('pll_languages_list')) {
            $pll_languages_list = pll_languages_list();
            if ( ! empty($pll_languages_list)) {
                // regex addendum. E.g  (it|fr|en)
                $regex_prefix = implode('|', pll_languages_list());

                add_filter('ppress_profile_rewrite_regex_1', function ($regex, $profile_slug) use ($regex_prefix) {
                    // ?: isused to ignore the match i.e non-capturing group.
                    // ? is used to make the preceding token optional.
                    return "^(?:$regex_prefix)?/?{$profile_slug}/([^/]*)/?";
                }, 10, 2);

                add_filter('ppress_profile_rewrite_regex_2', function ($regex, $profile_slug) use ($regex_prefix) {
                    return "^(?:$regex_prefix/)?/?{$profile_slug}/?$";
                }, 10, 2);
            }
        }
    }

    public function detect_language_login_url($url)
    {
        $login_page_id = ppress_settings_by_key('set_login_url');

        if ( ! empty($login_page_id)) {

            $current_lang            = pll_current_language();
            $login_translation_pages = (array)pll_get_post_translations($login_page_id);

            if ( ! empty($login_translation_pages[$current_lang])) {
                $current_lang_login_page_id = $login_translation_pages[$current_lang];
                $url                        = get_permalink($current_lang_login_page_id);
            }
        }

        return $url;
    }

    public function detect_language_registration_url($url)
    {
        $registration_page_id = ppress_settings_by_key('set_registration_url');

        if ( ! empty($registration_page_id)) {
            $current_lang                   = pll_current_language();
            $registration_translation_pages = (array)pll_get_post_translations($registration_page_id);

            if ( ! empty($registration_translation_pages[$current_lang])) {
                $current_lang_registration_page_id = $registration_translation_pages[$current_lang];
                $url                               = get_permalink($current_lang_registration_page_id);
            }
        }

        return $url;
    }

    public function detect_language_password_reset_url($url)
    {
        $password_reset_page_id = ppress_settings_by_key('set_lost_password_url');

        if ( ! empty($password_reset_page_id)) {
            $current_lang                     = pll_current_language();
            $password_reset_translation_pages = (array)pll_get_post_translations($password_reset_page_id);
            if ( ! empty($password_reset_translation_pages[$current_lang])) {
                $current_lang_password_reset_page_id = $password_reset_translation_pages[$current_lang];
                $url                                 = get_permalink($current_lang_password_reset_page_id);
            }
        }

        return $url;
    }

    public function detect_language_my_account_url($url)
    {
        $my_account_page_id = ppress_settings_by_key('edit_user_profile_url');

        if (function_exists('pll_current_language') && ! empty($my_account_page_id)) {
            $current_lang                   = pll_current_language();
            $edit_profile_translation_pages = (array)pll_get_post_translations($my_account_page_id);

            if ( ! empty($edit_profile_translation_pages[$current_lang])) {
                $current_lang_edit_profile_page_id = $edit_profile_translation_pages[$current_lang];
                $url                               = get_permalink($current_lang_edit_profile_page_id);
            }
        }

        return $url;
    }

    public function detect_language_profile_url($url)
    {
        $pp_profile_page_id = ppress_settings_by_key('set_user_profile_shortcode');

        if ( ! empty($pp_profile_page_id)) {
            $current_lang              = pll_current_language();
            $profile_translation_pages = (array)pll_get_post_translations($pp_profile_page_id);

            if ( ! empty($profile_translation_pages[$current_lang])) {
                $current_lang_profile_page_id = $profile_translation_pages[$current_lang];
                $url                          = get_permalink($current_lang_profile_page_id);
            }
        }

        return $url;
    }

    public function change_profile_page_id($page_id)
    {
        $pp_profile_page_id = ppress_settings_by_key('set_user_profile_shortcode');

        if ( ! empty($pp_profile_page_id)) {
            $current_lang              = pll_current_language();
            $profile_translation_pages = (array)pll_get_post_translations($pp_profile_page_id);

            if ( ! empty($profile_translation_pages[$current_lang])) {
                $current_lang_profile_page_id = $profile_translation_pages[$current_lang];
                $page_id                      = $current_lang_profile_page_id;
            }
        }

        return $page_id;
    }

    /**
     * Plugin settings page.
     *
     * @param $args
     *
     * @return array
     */
    public function settings_page($args)
    {
        $args['pp_polylang_settings'] = [
            'tab_title'     => esc_html__('Polylang', 'profilepress-pro'),
            'section_title' => esc_html__('Polylang Integration', 'profilepress-pro'),
            'dashicon'      => 'dashicons-translation',
            'plg_activate'  => [
                'type'           => 'checkbox',
                'value'          => 'yes',
                'label'          => esc_html__('Activate Integration', 'profilepress-pro'),
                'checkbox_label' => esc_html__('Check to enable', 'profilepress-pro'),
                'description'    => esc_html__('This allows you to create different language versions of your login, registration, my account and password reset pages.', 'profilepress-pro')
            ]
        ];

        return $args;
    }

    /**
     * @return self|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::POLYLANG')) return;

        if ( ! EM::is_enabled(EM::POLYLANG)) return;

        static $instance;

        if ( ! isset($instance)) {
            $instance = new self;
        }

        return $instance;
    }
}