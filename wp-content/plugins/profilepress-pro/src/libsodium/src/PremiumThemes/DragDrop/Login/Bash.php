<?php

namespace ProfilePress\Libsodium\PremiumThemes\DragDrop\Login;

use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\DragDropBuilder;
use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\FieldBase;
use ProfilePress\Core\Themes\DragDrop\AbstractTheme;
use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\Fields;

class Bash extends AbstractTheme
{
    public function __construct($form_id, $form_type)
    {
        parent::__construct($form_id, $form_type);

        add_filter('ppress_form_builder_field_settings', [$this, 'add_field_properties'], 10, 2);
    }

    public static function default_field_listing()
    {
        Fields\Init::init();
        $standard_fields = DragDropBuilder::get_instance()->standard_fields();

        return [
            $standard_fields['login-username'],
            $standard_fields['login-password']
        ];
    }

    public function default_fields_settings()
    {
        $defaults = parent::default_fields_settings();

        $defaults['login-username']['placeholder'] = '';
        $defaults['login-username']['label']       = esc_html__('Username', 'profilepress-pro');

        $defaults['login-password']['placeholder'] = '';
        $defaults['login-password']['label']       = esc_html__('Password', 'profilepress-pro');

        return $defaults;
    }

    /**
     * @param $field
     * @param FieldBase $fieldBaseInstance
     *
     * @return mixed
     */
    public function add_field_properties($field, $fieldBaseInstance)
    {
        if ( ! in_array($fieldBaseInstance->field_type(), $this->disallowed_settings_fields())) {
            if (isset($field[FieldBase::GENERAL_TAB])) {
                $placeholder = null;
                if (isset($field[FieldBase::GENERAL_TAB]['placeholder'])) {
                    $placeholder = $field[FieldBase::GENERAL_TAB]['placeholder'];
                    unset($field[FieldBase::GENERAL_TAB]['placeholder']);
                }

                if ( ! isset($field[FieldBase::GENERAL_TAB]['label'])) {
                    $field[FieldBase::GENERAL_TAB]['label'] = [
                        'label' => esc_html__('Label', 'profilepress-pro'),
                        'field' => FieldBase::INPUT_FIELD,
                    ];
                }

                if ( ! is_null($placeholder)) {
                    $field[FieldBase::GENERAL_TAB]['placeholder'] = $placeholder;
                }
            }
        }

        return $field;
    }

    public function appearance_settings($settings)
    {
        $settings[] = [
            'id'       => 'bash_login_headline',
            'type'     => 'text',
            'label'    => esc_html__('Headline', 'profilepress-pro'),
            'priority' => 20
        ];

        $settings[] = [
            'id'       => 'bash_login_signup_link_label',
            'type'     => 'text',
            'label'    => esc_html__('Signup Link Label', 'profilepress-pro'),
            'priority' => 25
        ];

        $settings[] = [
            'id'       => 'bash_password_reset_link_label',
            'type'     => 'text',
            'label'    => esc_html__('Password Reset Link Label', 'profilepress-pro'),
            'priority' => 30
        ];


        if ($this->is_show_social_login()) {

            $settings[] = [
                'id'       => 'bash_login_social_buttons',
                'type'     => 'select2',
                'options'  => [
                    'facebook' => esc_html__('Facebook', 'profilepress-pro'),
                    'twitter'  => esc_html__('Twitter', 'profilepress-pro'),
                    'google'   => esc_html__('Google', 'profilepress-pro'),
                ],
                'label'    => esc_html__('Social Login Buttons', 'profilepress-pro'),
                'priority' => 40
            ];
        }

        return $settings;
    }

    public function social_login_buttons()
    {
        if ( ! $this->is_show_social_login()) return '';

        $active_social_logins = $this->get_meta('bash_login_social_buttons');

        $html = '<ul class="bash-socials">';
        foreach ($active_social_logins as $active_social_login) {
            $html .= sprintf('<li><a href="[%1$s-login-url]" class="bash-%1$s"></a></li>', $active_social_login);
        }
        $html .= '</ul>';

        return $html;
    }

    public function default_metabox_settings()
    {
        $data                                   = parent::default_metabox_settings();
        $data['bash_login_headline']            = esc_html__('Sign in to Your Account', 'profilepress-pro');
        $data['bash_login_signup_link_label']   = esc_html__('Have no account?', 'profilepress-pro');
        $data['bash_password_reset_link_label'] = esc_html__('Forgot your password?', 'profilepress-pro');
        $data['bash_login_social_buttons']      = ['facebook', 'twitter', 'google'];

        return $data;
    }

    public function form_structure()
    {
        $fields                    = $this->field_listing();
        $button                    = $this->form_submit_button();
        $headline                  = $this->get_meta('bash_login_headline');
        $signup_link_label         = $this->get_meta('bash_login_signup_link_label');
        $password_reset_link_label = $this->get_meta('bash_password_reset_link_label');

        $social_login_buttons = $this->social_login_buttons();

        return <<<HTML
[pp-form-wrapper class="bash"]
    <div class="bash-heading">$headline</div>
    $social_login_buttons
    $fields
    <div class="bash-message"><a href="[link-lost-password raw]" class="bash-lost-password">$password_reset_link_label</a></div>
    <div style="margin-top: 20px;">
        $button
        <a class="bash-existing" href="[link-login raw]">$signup_link_label</a>
    </div>
[/pp-form-wrapper]
HTML;

    }

    public function form_css()
    {
        $form_id   = $this->form_id;
        $form_type = $this->form_type;

        $asset_url = PPRESS_ASSETS_URL . '/images/bash';

        return <<<CSS
/* css class for form generated errors */
#pp-$form_type-$form_id-wrap .profilepress-login-status {
		border-radius: 5px;
		font-size: 16px;
		line-height: 1.471;
		padding: 10px;
		background-color: #2F9FFF;
		color: #ffffff;
		font-weight: normal;
		display: block;
		text-align: center;
		vertical-align: middle;
		margin: 5px auto;
        max-width: 400px;
        width: 100%;
	}

#pp-$form_type-$form_id-wrap .profilepress-login-status a {
    color: #fff !important;
    text-decoration: underline;
}

	div#pp-$form_type-$form_id.bash {
		margin-left: auto;
		margin-right: auto;
		font-family: helvetica, arial, sans-serif;
		position: relative;
		border: 1px solid #ddd;
		background: #fff;
		overflow: hidden;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
		color: #7C919E;
		max-width: 400px;
        width: 100%;
		padding: 40px;
		text-align: left;
		font-size: 14px;
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
		line-height: 24px;
		-webkit-font-smoothing: antialiased;
		-moz-font-smoothing: antialiased;
		font-smoothing: antialiased;
	}

	/* Links */
	div#pp-$form_type-$form_id.bash a {
		color: #2F9FFF;
		text-decoration: none;
	}

	div#pp-$form_type-$form_id.bash a:hover {
		text-decoration: underline;
	}

	/* Heading */

	div#pp-$form_type-$form_id.bash .bash-heading {
		font-size: 20px;
		line-height: 30px;
		font-weight: bold;
		color: #556673;
		text-align: center;
		display: block;
		margin-bottom: 10px;
	}

	/* Social buttons */

	div#pp-$form_type-$form_id.bash .bash-socials {
		list-style: none;
		clear: both;
		text-align: center;
		margin: 0;
		margin-bottom: 30px;
	}

	div#pp-$form_type-$form_id.bash .bash-socials li {
		display: inline;
	}

	div#pp-$form_type-$form_id.bash .bash-socials a {
		width: 64px;
		height: 64px;
		background-size: cover;
		background-position: center;
		display: inline-block;
		margin-left: 6px;
		margin-right: 6px;
		-webkit-transition: 0.2s;
		-moz-transition: 0.2s;
		transition: 0.2s;
	}

	div#pp-$form_type-$form_id.bash .bash-facebook {
		background-image: url($asset_url/bash-facebook.png);
	}

	div#pp-$form_type-$form_id.bash .bash-twitter {
		background-image: url($asset_url/bash-twitter.png);
	}

	div#pp-$form_type-$form_id.bash .bash-google {
		background-image: url($asset_url/../social-login/google.svg);
	}

	div#pp-$form_type-$form_id.bash .bash-socials a:hover {
		-webkit-transform: scale(1.05);
		transform: scale(1.05);
		-moz-transform: scale(1.05);
		-ms-transform: scale(1.05);
	}

	div#pp-$form_type-$form_id.bash a.bash-lost-password {
		cursor: pointer;
	}

	/* Form */
	div#pp-$form_type-$form_id.bash .pp-form-field-wrap {
		position: relative;
		display: block;
		margin-top: 20px;
	}

	div#pp-$form_type-$form_id.bash .pp-form-label-wrap {
		position: absolute;
		top: 8px;
		left: 10px;
	}

	div#pp-$form_type-$form_id.bash .pp-form-label-wrap .pp-form-label {
		font-size: 13px;
		line-height: 23px;
		font-weight: bold;
	}

	div#pp-$form_type-$form_id.bash .pp-form-field:not([type=checkbox]):not([type=radio]) {
		border: 1px solid #C3CAD4;
		outline: none;
		-webkit-appearance: none;
		-moz-appearance: none;
		font-size: 14px;
		line-height: 24px;
		font-family: helvetica, arial, sans-serif;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
		padding: 6px;
		padding-left: 85px;
		width: 100%;
		margin: 0;
		-webkit-font-smoothing: antialiased;
		-moz-font-smoothing: antialiased;
		font-smoothing: antialiased;
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
		-webkit-transition: 0.4s;
		-moz-transition: 0.4s;
		transition: 0.4s;
	}

	div#pp-$form_type-$form_id.bash ..pp-form-field:not([type=checkbox]):not([type=radio]):focus {
		border-color: #2F9FFF;
	}

	div#pp-$form_type-$form_id.bash .pp-form-field-wrap[class*=radio] .pp-form-label-wrap,
	div#pp-$form_type-$form_id.bash .pp-form-field-wrap[class*=checkbox] .pp-form-label-wrap,
	div#pp-$form_type-$form_id.bash .pp-form-field-wrap[class*=agreeable] .pp-form-label-wrap {
		position: static;
        top: auto;
        left: auto;
        text-align: left;
	}

	/* Buttons */

	div#pp-$form_type-$form_id.bash .ppform-submit-button,
	div#pp-$form_type-$form_id.bash a.bash-existing {
		cursor: pointer;
		outline: none;
		-webkit-appearance: none;
		-moz-appearance: none;
		padding: 8px 0px;
		display: block;
		width: 48%;
		float: left;
		background: #2F9FFF;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
		color: #fff;
		font-size: 14px;
		text-shadow: none;
		line-height: 24px;
		font-family: helvetica, arial, sans-serif;
		font-weight: bold;
		text-align: center;
		border: 1px solid #2F9FFF;
		-webkit-font-smoothing: antialiased;
		-moz-font-smoothing: antialiased;
		font-smoothing: antialiased;
		-webkit-transition: 0.2s;
		-moz-transition: 0.2s;
		transition: 0.2s;
	}
	
	div#pp-$form_type-$form_id.bash .bash-message {
		font-weight: bold;
		color: #556673;
		font-size: 12px;
		line-height: 22px;
		margin-top: 20px;
		margin-bottom: 20px;
		display: block;
	}

	div#pp-$form_type-$form_id.bash .ppform-submit-button:hover {
		background: #234970;
		border-color: #234970;
	}

	div#pp-$form_type-$form_id.bash a.bash-existing {
		float: right;
		background: #fff;
		color: #2F9FFF;
		font-weight: normal;
		text-align: center;
		opacity: 0.5;
		-webkit-transition: 0.2s;
		-moz-transition: 0.2s;
		transition: 0.2s;
	}

	div#pp-$form_type-$form_id.bash a.bash-existing:hover {
		opacity: 1;
		text-decoration: none;
	}

CSS;

    }
}