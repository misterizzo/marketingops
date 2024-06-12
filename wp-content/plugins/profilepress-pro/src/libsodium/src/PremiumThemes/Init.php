<?php

namespace ProfilePress\Libsodium\PremiumThemes;

use ProfilePress\Core\Themes\DragDrop\ThemesRepository as DNDThemesRepository;
use ProfilePress\Core\Themes\Shortcode\ThemesRepository as ShortcodeThemesRepository;

class Init
{
    public function __construct()
    {
        add_filter('ppress_register_dnd_form_class', [$this, 'register_premium_dnd_themes_classes'], 10, 3);
        add_filter('ppress_register_shortcode_form_class', [$this, 'register_premium_shortcode_themes_classes'], 10, 3);
    }

    /**
     * @param $returned_class
     * @param $form_class
     * @param $form_type
     *
     * @return string
     */
    public function register_premium_dnd_themes_classes($returned_class, $form_class, $form_type)
    {
        foreach (DNDThemesRepository::premiumThemes() as $premium_theme) {

            $theme_type  = str_replace('-', '', ucwords($premium_theme['theme_type'], '-'));
            $theme_class = $premium_theme['theme_class'];

            if ($form_class === $theme_class && $form_type === $theme_type) {
                $returned_class = "\\ProfilePress\\Libsodium\\PremiumThemes\\DragDrop\\$theme_type\\$theme_class";
            }
        }

        return $returned_class;
    }

    /**
     * @param $returned_class
     * @param $form_class
     * @param $form_type
     *
     * @return string
     */
    public function register_premium_shortcode_themes_classes($returned_class, $form_class, $form_type)
    {
        foreach (ShortcodeThemesRepository::premiumThemes() as $premium_theme) {

            $theme_type  = str_replace('-', '', ucwords($premium_theme['theme_type'], '-'));
            $theme_class = $premium_theme['theme_class'];

            if ($form_class === $theme_class && $form_type === $theme_type) {
                $returned_class = "\\ProfilePress\\Libsodium\\PremiumThemes\\Shortcode\\$theme_type\\$theme_class";
            }
        }

        return $returned_class;
    }


    /**
     * @return self
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