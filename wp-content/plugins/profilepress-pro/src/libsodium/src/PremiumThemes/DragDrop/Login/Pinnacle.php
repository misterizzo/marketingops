<?php

namespace ProfilePress\Libsodium\PremiumThemes\DragDrop\Login;

use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\DragDropBuilder;
use ProfilePress\Core\Themes\DragDrop\AbstractTheme;
use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\Fields;

class Pinnacle extends AbstractTheme
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
            'id'       => 'pinnacle_login_headline',
            'type'     => 'text',
            'label'    => esc_html__('Headline', 'profilepress-pro'),
            'priority' => 20
        ];


        if ($this->is_show_social_login()) {

            $settings[] = [
                'id'       => 'pinnacle_login_social_buttons',
                'type'     => 'select2',
                'options'  => ppress_social_login_networks(),
                'label'    => esc_html__('Social Login Buttons', 'profilepress-pro'),
                'priority' => 30
            ];
        }

        $settings[] = [
            'id'       => 'pinnacle_login_signup_link_label',
            'type'     => 'text',
            'label'    => esc_html__('Signup Link Label', 'profilepress-pro'),
            'priority' => 40
        ];

        $settings[] = [
            'id'       => 'pinnacle_login_password_reset_link_label',
            'type'     => 'text',
            'label'    => esc_html__('Password Reset Link Label', 'profilepress-pro'),
            'priority' => 50
        ];

        return $settings;
    }

    public function social_login_buttons()
    {
        if ( ! $this->is_show_social_login()) return '';

        $active_social_logins = $this->get_meta('pinnacle_login_social_buttons');

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
        $data                                             = parent::default_metabox_settings();
        $data['pinnacle_login_headline']                  = esc_html__('Sign in to Your Account', 'profilepress-pro');
        $data['pinnacle_login_password_reset_link_label'] = esc_html__('Forgot your password?', 'profilepress-pro');
        $data['pinnacle_login_signup_link_label']         = esc_html__('Sign up now', 'profilepress-pro');
        $data['pinnacle_login_social_buttons']            = ['facebook', 'twitter', 'google', 'linkedin'];

        return $data;
    }

    public function form_structure()
    {
        $fields                    = $this->field_listing();
        $button                    = $this->form_submit_button();
        $headline                  = $this->get_meta('pinnacle_login_headline');
        $password_reset_link_label = $this->get_meta('pinnacle_login_password_reset_link_label');
        $signup_link_label         = $this->get_meta('pinnacle_login_signup_link_label');

        $social_login_buttons = $this->social_login_buttons();

        return <<<HTML
[pp-form-wrapper class="pp_pinnacle loginContainer"]
<div class="pp_pinnacle loginContent">
    <div class="pp_pinnacle loginTitle">$headline</div><br/>
    $social_login_buttons
</div>
<hr/>
<div class="pp_pinnacle loginContent">$fields</div>
<hr/>
	<div class="pp_pinnacle loginContent" style="background:#f0f0f0;">
		<div style="float:left;">
			<a class="pp_pinnacle" href="[link-lost-password raw]">$password_reset_link_label</a><br/>
			<a class="pp_pinnacle" href="[link-registration raw]">$signup_link_label</a>
		</div>
		<div style="float:right;">$button</div>
	</div>

[/pp-form-wrapper]
HTML;

    }

    public function form_css()
    {
        $form_id   = $this->form_id;
        $form_type = $this->form_type;

        return <<<CSS
/* css class for the form generated errors */
#pp-$form_type-$form_id-wrap .profilepress-login-status {
    border-radius: 5px;
    max-width: 380px;
    font-size: 16px;
    line-height: 1.471;
    padding: 10px;
    background-color: #e74c3c;
    color: #fff;
    font-weight: normal;
    transition: border 0.25s linear 0s, color 0.25s linear 0s, background-color 0.25s linear 0s;
    display: block;
    text-align: center;
    vertical-align: middle;
    margin: 5px auto;
}

#pp-$form_type-$form_id-wrap .profilepress-login-status a {
    color: #fff;
    text-decoration: underline;
}

div#pp-$form_type-$form_id.pp_pinnacle a.pp_pinnacle {
    font-weight: bold;
    color: #777;
    text-decoration: none;
}

div#pp-$form_type-$form_id.pp_pinnacle a.pp_pinnacle:hover {
    color: #000;
}

div#pp-$form_type-$form_id.pp_pinnacle.loginContainer {
    max-width: 380px;
    margin-left: auto;
    margin-right: auto;
    text-align: left;
    background: #ffffff;
    font-size: 14px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    box-shadow: 0px 2px 8px 0px #999;
    font-family: helvetica, arial, sans-serif;
}

div#pp-$form_type-$form_id.pp_pinnacle.loginContainer hr {
    border: 0;
    margin: 0;
    height: auto;
    border-top: 1px solid #ddd;
}

div#pp-$form_type-$form_id.pp_pinnacle .pp_pinnacle.loginContent {
    padding: 30px;
    overflow: hidden;
}

div#pp-$form_type-$form_id.pp_pinnacle .pp_pinnacle.loginTitle {
    font-size: 30px;
    line-height: 33px;
    font-weight: 900;
    color: #525252;
    text-align: center;
}

div#pp-$form_type-$form_id.pp_pinnacle input.pp-form-field {
    font-size: 16px;
    line-height: 30px;
    outline: none;
    -webkit-border-radius: 3px;
    -moz-border-radius: 3px;
    border-radius: 3px;
    padding: 10px 15px;
    padding-bottom: 8px;
    width: 100%;
    -webkit-box-sizing: border-box;
    background-color: #f2f2f2;
    border: 1px solid #ccc;
}

div#pp-$form_type-$form_id.pp_pinnacle input.pp-form-field:not([type=checkbox]):not([type=radio]) {
    -webkit-box-shadow: inset 0px 0px 4px 0px #ccc;
    -moz-box-shadow: inset 0px 0px 4px 0px #ccc;
    box-shadow: inset 0px 0px 4px 0px #ccc;
}

div#pp-$form_type-$form_id.pp_pinnacle .pp-form-field-wrap {
    margin-bottom: 10px;
}

div#pp-$form_type-$form_id.pp_pinnacle input.pp_pinnacle.loginInputField:focus {
    background-color: #fff;
}

div#pp-$form_type-$form_id.pp_pinnacle input.ppform-submit-button {
    margin-top: 5px;
    background: #5bb75b;
    border: 0;
    outline: none;
    display: block;
    padding: 10px 15px;
    font-size: 15px;
    font-weight: bold;
    color: #fff;
  	text-shadow:none;
    cursor: pointer;
    -webkit-border-radius: 4px;
    -moz-border-radius: 4px;
    border-radius: 4px;
    -webkit-font-smoothing: antialiased;
    -moz-font-smoothing: antialiased;
    font-smoothing: antialiased;
}

div#pp-$form_type-$form_id.pp_pinnacle a.pp-button-social-login {
    max-width: 100% !important;
    width: 100%;
}

div#pp-$form_type-$form_id.pp_pinnacle a.pp-button-social-login {
    display: block;
    height: 3em;
    line-height: 3em;
    text-decoration: none;
    margin-bottom: 10px;
}

div#pp-$form_type-$form_id.pp_pinnacle a.pp-button-social-login .ppsc {
    width: 3em;
    height: 3em;
}

div#pp-$form_type-$form_id.pp_pinnacle a.pp-button-social-login span.ppsc-text {
    margin-left: 50px;
}
CSS;

    }
}