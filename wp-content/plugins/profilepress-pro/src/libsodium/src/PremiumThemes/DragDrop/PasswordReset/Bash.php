<?php

namespace ProfilePress\Libsodium\PremiumThemes\DragDrop\PasswordReset;

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
            $standard_fields['user-login']
        ];
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
            'id'       => 'bash_password_reset_headline',
            'type'     => 'text',
            'label'    => esc_html__('Headline', 'profilepress-pro'),
            'priority' => 20
        ];

        $settings[] = [
            'id'       => 'bash_password_reset_signup_link_label',
            'type'     => 'text',
            'label'    => esc_html__('Signup Link Label', 'profilepress-pro'),
            'priority' => 25
        ];

        return $settings;
    }

    public function default_metabox_settings()
    {
        $data                                          = parent::default_metabox_settings();
        $data['bash_password_reset_headline']          = esc_html__('Reset Password', 'profilepress-pro');
        $data['bash_password_reset_signup_link_label'] = esc_html__('Have no account?', 'profilepress-pro');

        return $data;
    }

    public function form_structure()
    {
        $fields            = $this->field_listing();
        $button            = $this->form_submit_button();
        $headline          = $this->get_meta('bash_password_reset_headline');
        $signup_link_label = $this->get_meta('bash_password_reset_signup_link_label');

        return <<<HTML
[pp-form-wrapper class="bash"]
    <div class="bash-heading">$headline</div>
    $fields
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
#pp-$form_type-$form_id-wrap .profilepress-reset-status {
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
	
	#pp-$form_type-$form_id-wrap .profilepress-reset-status a {
        color: #fff;
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

	/* Buttons */

	div#pp-$form_type-$form_id.bash .ppform-submit-button,
	div#pp-$form_type-$form_id.bash a.bash-existing {
		cursor: pointer;
		outline: none;
		-webkit-appearance: none;
		-moz-appearance: none;
		padding: 8px 0;
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
		border: 1px solid #2F9FFF;
		-webkit-font-smoothing: antialiased;
		-moz-font-smoothing: antialiased;
		font-smoothing: antialiased;
		-webkit-transition: 0.2s;
		-moz-transition: 0.2s;
		transition: 0.2s;
		text-align: center;
	}
	
	div#pp-$form_type-$form_id.bash a.bash-existing {
		float: right;
		background: #fff;
		color: #2F9FFF;
		font-weight: normal;
		opacity: 0.5;
		-webkit-transition: 0.2s;
		-moz-transition: 0.2s;
		transition: 0.2s;
		text-align: center;
	}

	div#pp-$form_type-$form_id.bash a.bash-existing:hover {
		opacity: 1;
		text-decoration: none;
	}

	div#pp-$form_type-$form_id.bash .ppform-submit-button:hover {
		background: #234970;
		border-color: #234970;
	}

CSS;

    }
}