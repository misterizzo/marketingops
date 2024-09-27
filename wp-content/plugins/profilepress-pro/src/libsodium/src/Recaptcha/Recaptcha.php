<?php

namespace ProfilePress\Libsodium\Recaptcha;

use ProfilePress\Core\Classes\FormRepository as FR;

class Recaptcha
{
    static public $site_key;

    static public $secret_key;

    static public $type;

    static public $recaptcha_score;

    static public $theme;

    static public $language;

    static public $error_message;

    /** initialize class functions */
    public static function initialize()
    {
        self::$type = ppress_settings_by_key('recaptcha_type', 'v2', true);

        self::$site_key = ppress_settings_by_key('recaptcha_site_key');

        self::$secret_key = ppress_settings_by_key('recaptcha_secret_key');

        self::$theme = ppress_settings_by_key('recaptcha_theme', 'light', true);

        self::$language = ppress_settings_by_key('recaptcha_language');

        self::$error_message = ppress_settings_by_key('recaptcha_error_message');

        self::$recaptcha_score = ppress_settings_by_key('recaptcha_score', '0.5', true);

        add_filter('ppress_settings_page_args', [__CLASS__, 'settings_page']);

        add_action('ppress_enqueue_public_js', [__CLASS__, 'enqueue_script']);

        add_shortcode('pp-recaptcha', array(__CLASS__, 'recaptcha'));

        add_action('ppress_drag_drop_builder_field_init_after', function ($form_type) {

            if (in_array($form_type, [FR::LOGIN_TYPE, FR::REGISTRATION_TYPE, FR::EDIT_PROFILE_TYPE, FR::PASSWORD_RESET_TYPE])) {
                new RecaptchaDNDField();
            }
        });

        add_filter('ppress_global_available_shortcodes', [__CLASS__, 'shortcode_builder_shortcode_ui']);
    }

    /**
     * reCAPTCHA display
     *
     * @return string
     */
    public static function recaptcha($atts)
    {
        $atts = shortcode_atts([
            'theme' => 'light',
            'size'  => 'normal',
            'class' => ''
        ],
            $atts
        );

        return self::display_captcha($atts['theme'], $atts['size'], $atts['class']);
    }

    public static function shortcode_builder_shortcode_ui($shortcodes)
    {
        $shortcodes['pp-recaptcha'] = [
            'description' => esc_html__('Displays reCAPTCHA', 'profilepress-pro'),
            'shortcode'   => 'pp-recaptcha',
            'attributes'  => [
                'theme' => [
                    'label'   => esc_html__('Theme', 'profilepress-pro'),
                    'field'   => 'select',
                    'options' => ['light' => esc_html__('Light', 'profilepress-pro'), 'dark' => esc_html__('Dark', 'profilepress-pro')]
                ],
                'size'  => [
                    'label'   => esc_html__('Size', 'profilepress-pro'),
                    'field'   => 'select',
                    'options' => ['normal' => esc_html__('Normal', 'profilepress-pro'), 'compact' => esc_html__('Compact', 'profilepress-pro')]
                ],
                'class' => [
                    'label' => esc_html__('CSS class', 'profilepress-pro'),
                    'field' => 'text'
                ],
            ]
        ];

        return $shortcodes;
    }

    public static function settings_page($settings)
    {
        $settings['recaptcha'] = apply_filters('ppress_recaptcha_page', [
            'tab_title' => esc_html__('reCAPTCHA', 'profilepress-pro'),
            'dashicon'  => 'dashicons-shield-alt',
            [
                'section_title'           => esc_html__('reCAPTCHA Settings', 'profilepress-pro'),
                'recaptcha_type'          => [
                    'label' => __('Type', 'profilepress-pro'),
                    'type'  => 'custom_field_block',
                    'data'  => self::recaptcha_type_settings()
                ],
                'recaptcha_site_key'      => [
                    'type'        => 'text',
                    'label'       => esc_html__('Site Key', 'profilepress-pro'),
                    'description' => sprintf(__('Necessary for displaying the CAPTCHA. Grab it %shere%s', 'profilepress-pro'), '<a href="https://www.google.com/recaptcha/admin" target="_blank">', '</a>')
                ],
                'recaptcha_secret_key'    => [
                    'type'        => 'text',
                    'label'       => esc_html__('Secret Key', 'profilepress-pro'),
                    'description' => sprintf(__('Necessary for communication between your site and Google. Grab it %shere%s', 'profilepress-pro'), '<a href="https://www.google.com/recaptcha/admin" target="_blank">', '</a>')
                ],
                'recaptcha_score'         => [
                    'type'        => 'text',
                    'label'       => __('Score Threshold', 'profilepress-pro'),
                    'value'       => '0.5',
                    'description' => __('The score at which users will fail reCAPTCHA v3 verification. Scores can range from from 0.0 (very likely a bot) to 1.0 (very likely a human). Default is 0.5', 'profilepress-pro')
                ],
                'recaptcha_theme'         => [
                    'type'        => 'select',
                    'options'     => [
                        'light' => esc_html__('Light', 'profilepress-pro'),
                        'dark'  => esc_html__('Dark', 'profilepress-pro')
                    ],
                    'label'       => esc_html__('reCAPTCHA Theme', 'profilepress-pro'),
                    'description' => esc_html__('The theme colour of the captcha widget.', 'profilepress-pro')
                ],
                'recaptcha_language'      => [
                    'type'        => 'select',
                    'options'     => self::recaptcha_languages(),
                    'label'       => esc_html__('Language', 'profilepress-pro'),
                    'description' => esc_html__('Forces the widget to render in a specific language.', 'profilepress-pro')
                ],
                'recaptcha_error_message' => [
                    'type'        => 'text',
                    'value'       => '<strong>ERROR</strong>: Please retry CAPTCHA',
                    'label'       => esc_html__('Error Message', 'profilepress-pro'),
                    'description' => esc_html__('Message or text to display when CAPTCHA is ignored or the challenge is failed.', 'profilepress-pro')
                ],
            ]
        ]);

        return $settings;
    }

    protected static function recaptcha_type_settings()
    {
        $value = ppress_settings_by_key('recaptcha_type', 'v2');

        $html = sprintf(
            '<label><input class="pp-recaptcha-type" type="radio" name="' . PPRESS_SETTINGS_DB_OPTION_NAME . '[recaptcha_type]" value="v2" %s>%s</label>&nbsp;&nbsp;',
            checked($value, 'v2', false),
            __('reCAPTCHA v2', 'profilepress-pro')
        );

        $html .= sprintf(
            '<label><input class="pp-recaptcha-type" type="radio" name="' . PPRESS_SETTINGS_DB_OPTION_NAME . '[recaptcha_type]" value="v3" %s>%s</label>',
            checked($value, 'v3', false),
            __('reCAPTCHA v3', 'profilepress-pro')
        );

        $html .= '<script type="text/javascript">
jQuery(function($) {
$("input.pp-recaptcha-type").on("change", function() {
   var type = $("input[name=\'ppress_settings_data[recaptcha_type]\']:checked").val();
   if(type === "v3") {
       $("#recaptcha_score_row").show();
       $("#recaptcha_theme_row").hide();
       $("#recaptcha_language_row").hide();
}
   else {
       $("#recaptcha_score_row").hide();
       $("#recaptcha_theme_row").show();
       $("#recaptcha_language_row").show();
   }
}).trigger("change");
});
</script>';

        return $html;
    }

    protected static function recaptcha_languages()
    {
        return [
            ''      => esc_html__('Auto Detect', 'profilepress-pro'),
            'en'    => esc_html__('English (US)', 'profilepress-pro'),
            'en-GB' => esc_html__('English (UK)', 'profilepress-pro'),
            'ar'    => esc_html__('Arabic', 'profilepress-pro'),
            'af'    => esc_html__('Afrikaans', 'profilepress-pro'),
            'am'    => esc_html__('Amharic', 'profilepress-pro'),
            'hy'    => esc_html__('Armenian', 'profilepress-pro'),
            'az'    => esc_html__('Azerbaijani', 'profilepress-pro'),
            'eu'    => esc_html__('Basque', 'profilepress-pro'),
            'bn'    => esc_html__('Bengali', 'profilepress-pro'),
            'bg'    => esc_html__('Bulgarian', 'profilepress-pro'),
            'ca'    => esc_html__('Catalan', 'profilepress-pro'),
            'zh-HK' => esc_html__('Chinese (Hong Kong)', 'profilepress-pro'),
            'zh-CN' => esc_html__('Chinese (Simplified)', 'profilepress-pro'),
            'zh-TW' => esc_html__('Chinese (Traditional)', 'profilepress-pro'),
            'hr'    => esc_html__('Croatian', 'profilepress-pro'),
            'cs'    => esc_html__('Czech', 'profilepress-pro'),
            'da'    => esc_html__('Danish', 'profilepress-pro'),
            'nl'    => esc_html__('Dutch', 'profilepress-pro'),
            'et'    => esc_html__('Estonian', 'profilepress-pro'),
            'fil'   => esc_html__('Filipino', 'profilepress-pro'),
            'fi'    => esc_html__('Finnish', 'profilepress-pro'),
            'fr'    => esc_html__('French', 'profilepress-pro'),
            'gl'    => esc_html__('Galician', 'profilepress-pro'),
            'ka'    => esc_html__('Georgian', 'profilepress-pro'),
            'de'    => esc_html__('German', 'profilepress-pro'),
            'el'    => esc_html__('Greek', 'profilepress-pro'),
            'es'    => esc_html__('Spanish', 'profilepress-pro'),
            'fa'    => esc_html__('Persian', 'profilepress-pro'),
            'hi'    => esc_html__('Hindi', 'profilepress-pro'),
            'hu'    => esc_html__('Hungarian', 'profilepress-pro'),
            'id'    => esc_html__('Indonesian', 'profilepress-pro'),
            'it'    => esc_html__('Italian', 'profilepress-pro'),
            'iw'    => esc_html__('Hebrew', 'profilepress-pro'),
            'ja'    => esc_html__('Jananese', 'profilepress-pro'),
            'ko'    => esc_html__('Korean', 'profilepress-pro'),
            'lt'    => esc_html__('Lithuanian', 'profilepress-pro'),
            'lv'    => esc_html__('Latvian', 'profilepress-pro'),
            'no'    => esc_html__('Norwegian', 'profilepress-pro'),
            'pl'    => esc_html__('Polish', 'profilepress-pro'),
            'pt'    => esc_html__('Portuguese', 'profilepress-pro'),
            'ro'    => esc_html__('Romanian', 'profilepress-pro'),
            'ru'    => esc_html__('Russian', 'profilepress-pro'),
            'sk'    => esc_html__('Slovak', 'profilepress-pro'),
            'sl'    => esc_html__('Slovene', 'profilepress-pro'),
            'sr'    => esc_html__('Serbian', 'profilepress-pro'),
            'sv'    => esc_html__('Swedish', 'profilepress-pro'),
            'th'    => esc_html__('Thai', 'profilepress-pro'),
            'tr'    => esc_html__('Turkish', 'profilepress-pro'),
            'uk'    => esc_html__('Ukrainian', 'profilepress-pro'),
            'ur'    => esc_html__('Urdu', 'profilepress-pro'),
            'vi'    => esc_html__('Vietnamese', 'profilepress-pro'),
        ];
    }

    public static function is_recaptcha_found($form_id, $form_type)
    {
        $is_captcha_present = false;

        if (FR::is_drag_drop($form_id, $form_type)) {

            $result = wp_list_filter(
                FR::form_builder_fields_settings($form_id, $form_type),
                ['fieldType' => 'pp-recaptcha']
            );

            if ( ! empty($result)) $is_captcha_present = true;

        } else {

            $structure = FR::get_form_meta($form_id, $form_type, FR::FORM_STRUCTURE);

            $result = ppress_mb_function(['mb_strpos', 'strpos'], [$structure, 'pp-recaptcha']);

            if (false !== $result) $is_captcha_present = true;
        }

        return $is_captcha_present;
    }

    /**
     * @param bool $return_src
     *
     * @return string|void
     */
    public static function enqueue_script($return_src = false)
    {
        if (empty(self::$site_key) || empty(self::$secret_key)) return;

        $src = 'https://www.google.com/recaptcha/api.js?onload=ppFormRecaptchaLoadCallback&render=explicit';

        if ( ! empty(self::$language)) {
            $src = add_query_arg('hl', self::$language, $src);
        }

        if (self::$type === 'v3') {
            $src = 'https://www.google.com/recaptcha/api.js?onload=ppFormRecaptchaLoadCallback&render=' . self::$site_key;
        }

        if ($return_src) {
            return $src;
        }

        wp_enqueue_script('ppress-recaptcha-script', $src, ['ppress-frontend-script'], false, true);
    }

    public static function display_captcha($theme = '', $size = 'normal', $class = '')
    {
        $theme = empty($theme) ? self::$theme : $theme;

        if (empty(self::$site_key) || empty(self::$secret_key)) return esc_html__('reCAPTCHA has not been set up.', 'profilepress-pro');

        $site_key = self::$site_key;

        if (self::$type == 'v2') {
            return "<div style='margin: 2px 0' class=\"pp-g-recaptcha $class\" data-type=\"v2\" data-sitekey=\"$site_key\" data-theme='$theme' data-size='$size'></div>";
        }

        return "<div class=\"pp-g-recaptcha $class\" data-type=\"v3\" data-sitekey=\"$site_key\"></div>";
    }

    /**
     * Send a GET request to verify captcha challenge
     *
     * @return bool
     */
    public static function captcha_verification()
    {
        if (empty(self::$site_key) || empty(self::$secret_key)) return false;

        if ( ! isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) return false;

        $request = [
            'body' => [
                'secret'   => self::$secret_key,
                'response' => $_POST['g-recaptcha-response'],
                'remoteip' => ppress_get_ip_address(),
            ]
        ];

        $result        = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', $request);
        $response_code = wp_remote_retrieve_response_code($result);

        if (200 !== (int)$response_code) return false;

        $body = json_decode(wp_remote_retrieve_body($result), true);

        if ( ! isset($body['success']) || ! $body['success']) return false;

        if (self::$type == 'v3') {
            if ($body['score'] < self::$recaptcha_score) return false;
        }

        return true;
    }

}