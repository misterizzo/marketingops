<?php

namespace ProfilePress\Libsodium\PremiumThemes\DragDrop\Login;

use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\DragDropBuilder;
use ProfilePress\Core\Themes\DragDrop\AbstractTheme;
use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\Fields;

class PerfectoPro extends AbstractTheme
{
    public static function default_field_listing()
    {
        Fields\Init::init();
        $standard_fields = DragDropBuilder::get_instance()->standard_fields();

        return [
            $standard_fields['login-username'],
            $standard_fields['login-password'],
            $standard_fields['login-remember'],
        ];
    }

    public function appearance_settings($settings)
    {
        $settings[] = [
            'id'       => 'perfectopro_login_headline',
            'type'     => 'text',
            'label'    => esc_html__('Headline', 'profilepress-pro'),
            'priority' => 20
        ];

        if ($this->is_show_social_login()) {

            $settings[] = [
                'id'       => 'perfectopro_login_social_buttons',
                'type'     => 'select2',
                'options'  => ppress_social_login_networks(),
                'label'    => esc_html__('Social Login Buttons', 'profilepress-pro'),
                'priority' => 30
            ];
        }

        return $settings;
    }

    public function color_settings($settings)
    {
        $settings2 = [
            [
                'id'    => 'perfectopro_login_bg_color',
                'type'  => 'color',
                'label' => esc_html__('Background', 'profilepress-pro')
            ],
            [
                'id'    => 'perfectopro_login_border',
                'type'  => 'color',
                'label' => esc_html__('Form Border', 'profilepress-pro')
            ],
            [
                'id'    => 'perfectopro_login_text_color',
                'type'  => 'color',
                'label' => esc_html__('Text', 'profilepress-pro')
            ],
            [
                'id'    => 'perfectopro_login_placeholder_color',
                'type'  => 'color',
                'label' => esc_html__('Field Placeholder', 'profilepress-pro')
            ],
            [
                'id'    => 'perfectopro_login_button_bg_color',
                'type'  => 'color',
                'label' => esc_html__('Button Background', 'profilepress-pro')
            ],
            [
                'id'    => 'perfectopro_login_button_text_color',
                'type'  => 'color',
                'label' => esc_html__('Button Text', 'profilepress-pro')
            ]
        ];

        return array_merge($settings, $settings2);
    }

    public function social_login_buttons()
    {
        if ( ! $this->is_show_social_login()) return '';

        $active_social_logins = $this->get_meta('perfectopro_login_social_buttons');

        $html = '';

        if (is_array($active_social_logins)) {

            $active_social_logins = array_filter($active_social_logins);

            foreach ($active_social_logins as $active_social_login) {
                $html .= "[pp-social-button type=$active_social_login]";
            }
        }

        return $html;
    }

    public function default_metabox_settings()
    {
        $data                                        = parent::default_metabox_settings();
        $data['perfectopro_login_headline']          = esc_html__('Sign in to Your Account', 'profilepress-pro');
        $data['perfectopro_login_bg_color']          = '#f0f0f0';
        $data['perfectopro_login_border']            = '#f0f0f0';
        $data['perfectopro_login_text_color']        = '#555555';
        $data['perfectopro_login_placeholder_color'] = '#555555';
        $data['perfectopro_login_button_bg_color']   = '#196cd8';
        $data['perfectopro_login_button_text_color'] = '#ffffff';
        $data['perfectopro_login_social_buttons']    = ['facebook', 'twitter', 'google', 'linkedin'];

        return $data;
    }

    public function form_structure()
    {
        $fields   = $this->field_listing();
        $button   = $this->form_submit_button();
        $headline = $this->get_meta('perfectopro_login_headline');

        $social_login_buttons = $this->social_login_buttons();

        return <<<HTML
[pp-form-wrapper class="perfecto"]
    <div class="perfecto-heading">$headline</div>
    $social_login_buttons
    <div class="perfecto-or">
    <div class="perfecto-orText">or</div>
    <div class="perfecto-orStroke"></div>
    </div>
    $fields
	$button
[/pp-form-wrapper]
HTML;

    }

    public function form_css()
    {
        $form_id   = $this->form_id;
        $form_type = $this->form_type;

        $bg_color          = $this->get_meta('perfectopro_login_bg_color');
        $border_color      = $this->get_meta('perfectopro_login_border');
        $text_color        = $this->get_meta('perfectopro_login_text_color');
        $placeholder_color = $this->get_meta('perfectopro_login_placeholder_color');
        $button_bg_color   = $this->get_meta('perfectopro_login_button_bg_color');
        $button_text_color = $this->get_meta('perfectopro_login_button_text_color');

        return <<<CSS
/*  css class for the form generated errors */
#pp-$form_type-$form_id-wrap .profilepress-login-status {
    background-color: #484c51;
    color: #ffffff;
    border-radius: 2px;
    font-size: 16px;
    font-weight: normal;
    line-height: 1.4;
    text-align: center;
    padding: 8px 5px;
    max-width: 400px;
    margin: 5px auto;
}
#pp-$form_type-$form_id-wrap .profilepress-login-status a {
    color: #ea9629 !important;
    text-decoration: underline;
}

div#pp-$form_type-$form_id.perfecto * {
    color: $text_color;
}

div#pp-$form_type-$form_id.perfecto ::placeholder{
  color: $placeholder_color;
}

div#pp-$form_type-$form_id.perfecto ::-webkit-input-placeholder{
  color: $placeholder_color;
}

div#pp-$form_type-$form_id.perfecto ::-moz-placeholder{
  color: $placeholder_color;
}

div#pp-$form_type-$form_id.perfecto :-moz-placeholder{
  color: $placeholder_color;
}

div#pp-$form_type-$form_id.perfecto :-ms-input-placeholder{
  color: $placeholder_color;
}

div#pp-$form_type-$form_id.perfecto {
    margin: 5px auto;
    border: 2px solid $border_color;
    background: $bg_color;
    max-width: 400px;
    padding: 40px;
    font-family: helvetica, arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-font-smoothing: antialiased;
    font-smoothing: antialiased;
    font-size: 14px;
    line-height: 24px;
}
div#pp-$form_type-$form_id.perfecto .perfecto-heading {
    font-size: 24px;
    line-height: 34px;
    margin-bottom: 20px;
    display: block;
    font-weight: 100;
    color: #555;
    text-align: center;
}
/* Social Buttons */

div#pp-$form_type-$form_id.perfecto a.pp-button-social-login {
    display: block;
    color: #fff;
    height: 3em;
    line-height: 3em;
    text-decoration: none;
    margin-bottom: 10px;
    -webkit-transition: 0.4s;
    -moz-transition: 0.4s;
    transition: 0.4s;
}

div#pp-$form_type-$form_id.perfecto a.pp-button-social-login .ppsc {
    width: 3em;
    height: 3em;
}

div#pp-$form_type-$form_id.perfecto a.pp-button-social-login span.ppsc-text {
    margin-left: 45px;
}

/* Or */
div#pp-$form_type-$form_id.perfecto .perfecto-or {
    display: block;
    
    position: relative;
    height: 14px;
    margin: 40px 0;
}

div#pp-$form_type-$form_id.perfecto .perfecto-orText {
    position: absolute;
    top: 0;
    left: 50%;
    margin-left: -20px;
    width: 40px;
    height: 14px;
    font-size: 14px;
    line-height: 14px;
    background: $bg_color;
    font-family: georgia, times, serif;
    font-style: italic;
    font-weight: bold;
    text-align: center;
    z-index: 2;
}

div#pp-$form_type-$form_id.perfecto .perfecto-orStroke {
    position: absolute;
    top: 7px;
    left: 0;
    right: 0;
    height: 1px;
    z-index: 1;
    background: #ddd;
}
/* Inputs */

div#pp-$form_type-$form_id.perfecto .pp-form-field-wrap input.pp-form-field {
    background-color: #fff;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.125);
    padding: 12px 18px;
    width: 100%;
    box-sizing: border-box;
    outline: none;
    color: #555;
    font-size: 12px;
    line-height: 22px;
    font-family: helvetica, arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-font-smoothing: antialiased;
    font-smoothing: antialiased;
    border: 1px solid #ddd;
    -webkit-border-radius: 2px;
    -moz-border-radius: 2px;
    border-radius: 2px;
    margin: 0;
    margin-bottom: 10px;
    -webkit-transition: 0.4s;
    -moz-transition: 0.4s;
    transition: 0.4s;
}

div#pp-$form_type-$form_id.perfecto .pp-form-field-wrap input.pp-form-field:focus {
    border-color: #ccc;
    background: #fafafa;
    -webkit-box-shadow: inset 0px 1px 5px 0px #f0f0f0;
    -moz-box-shadow: inset 0px 1px 5px 0px #f0f0f0;
    box-shadow: inset 0px 1px 5px 0px #f0f0f0;
}

div#pp-$form_type-$form_id.perfecto input.ppform-submit-button {
    padding: 12px 10px;
    width: 100%;
    box-sizing: border-box;
    border: 0;
    outline: none;
    color: $button_text_color;
    background: $button_bg_color;
    font-size: 12px;
    line-height: 20px;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: bold;
    font-family: helvetica, arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-font-smoothing: antialiased;
    font-smoothing: antialiased;
    -webkit-border-radius: 2px;
    -moz-border-radius: 2px;
    border-radius: 2px;
    margin: 0;
    margin-top: 20px;
    margin-bottom: 10px;
    -webkit-transition: 0.4s;
    -moz-transition: 0.4s;
    transition: 0.4s;
}
CSS;

    }
}