<?php

namespace ProfilePress\Libsodium\PremiumThemes\DragDrop\PasswordReset;

use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\DragDropBuilder;
use ProfilePress\Core\Themes\DragDrop\AbstractTheme;
use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\Fields;

class Smiley extends AbstractTheme
{
    public static function default_field_listing()
    {
        Fields\Init::init();
        $standard_fields = DragDropBuilder::get_instance()->standard_fields();

        return [
            $standard_fields['user-login']
        ];
    }

    public function appearance_settings($settings)
    {
        $settings[] = [
            'id'       => 'smiley_password_reset_header_image',
            'type'     => 'upload',
            'label'    => esc_html__('Header Image', 'profilepress-pro'),
            'priority' => 10
        ];

        $settings[] = [
            'id'       => 'smiley_password_reset_headline',
            'type'     => 'text',
            'label'    => esc_html__('Headline', 'profilepress-pro'),
            'priority' => 20
        ];

        $settings[] = [
            'id'       => 'smiley_password_reset_login_label',
            'type'     => 'text',
            'label'    => esc_html__('Login Link Label', 'profilepress-pro'),
            'priority' => 30
        ];

        return $settings;
    }

    public function default_metabox_settings()
    {
        $data                                       = parent::default_metabox_settings();
        $data['smiley_password_reset_headline']     = esc_html__('Reset Password', 'profilepress-pro');
        $data['smiley_password_reset_login_label']  = esc_html__('Back to Login', 'profilepress-pro');
        $data['smiley_password_reset_header_image'] = $this->asset_image_url . "/smiley/avatar.gif";

        return $data;
    }

    public function form_structure()
    {
        $fields           = $this->field_listing();
        $submit_button    = $this->form_submit_button();
        $headline         = $this->get_meta('smiley_password_reset_headline');
        $login_link_label = $this->get_meta('smiley_password_reset_login_label');
        $header_image     = $this->get_meta('smiley_password_reset_header_image');

        if ( ! empty($header_image)) {
            $header_image = sprintf(
                '<div class="ppboxa"><img class="ppavatar" src="%s" alt="smiley"/></div>',
                $header_image
            );
        }

        return <<<HTML
[pp-form-wrapper class="smiley1"]
    $header_image
    <div class="ppboxb">
        <div class="ppuserdata"><span class="ppname">$headline</span></div>
        <div class="ppprofdata">
            $fields
            <br style="clear: both;"/>
            <div style="text-align:center">[link-login class="ppsoclog smi-links" label="$login_link_label"]</div>
        </div>
        <div class="pploginbutton">
            $submit_button
        </div>
    </div>
[/pp-form-wrapper]
HTML;

    }

    public function form_css()
    {
        $form_id   = $this->form_id;
        $form_type = $this->form_type;

        $asset_url   = $this->asset_image_url . '/smiley';
        $google_font = apply_filters('ppress_disable_google_fonts', false) ? '' :
            "@import url('https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700&display=swap');";

        return <<<CSS
$google_font

#pp-$form_type-$form_id-wrap .profilepress-reset-status {
  border-radius: 5px;
  font-size: 16px;
  line-height: 1.471;
  padding: 10px;
  background-color: #e74c3c;
  color: #ffffff;
  font-weight: normal;
  display: block;
  text-align: center;
  vertical-align: middle;
  margin: 5px auto;
  max-width: 350px;
}

#pp-$form_type-$form_id-wrap .profilepress-reset-status a {
    color: #fff;
    text-decoration: underline;
}

#pp-$form_type-$form_id-wrap .profilepress-reset-status.success {
  background-color: #2ecc71;
  color: #ffffff;
}

div#pp-$form_type-$form_id.smiley1 * {
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    font-family: 'Montserrat', sans-serif;
}

div#pp-$form_type-$form_id.smiley1 {
    max-width: 350px;
    width: 100%;
    margin: 0 auto;
    font-size: 14px;
}

div#pp-$form_type-$form_id.smiley1 .ppboxa {
	background: transparent;
	max-width: 350px;
	width: 100%;
	height: 80px;
	margin: 0 auto;
	text-align: center;
	border-top-left-radius: 4px;
	border-top-right-radius: 4px;
	line-height: normal;
}

div#pp-$form_type-$form_id.smiley1 .ppavatar {
    display: inline;
    border-radius: 120px;
    border: 6px solid #fff;
    margin-top: 05px;
    background: #f1f1f1;
    max-width: 120px;
    width: 100%;
}

div#pp-$form_type-$form_id.smiley1 .smi-links {
  font-size: 14px !important;
}

div#pp-$form_type-$form_id.smiley1 .ppboxb {
	background: url($asset_url/bg1.png);
	max-width: 350px;
	width: 100%;
	/* height: 400px; */
	margin: 0 auto;
	color: #fff;
	border-radius: 4px;
}

div#pp-$form_type-$form_id.smiley1 .ppuserdata {
    text-align: center;
    padding-top: 60px;
    text-shadow: 0 1px 1px rgba(0, 0, 0, .25);
}

div#pp-$form_type-$form_id.smiley1 span.ppname {
    font-size: 32px;
}

div#pp-$form_type-$form_id.smiley1 .ppprofdata {
    padding: 30px;
    background: rgba(0, 0, 0, .1);
    margin: 30px;
    border-radius: 4px;
    text-shadow: 0 1px 1px rgba(0, 0, 0, .25);
    padding-bottom: 5px;
}

div#pp-$form_type-$form_id.smiley1 input {
    border: 0;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    background-color: #fff;
    color: #7c7c7c;
    font-size: 14px;
    padding: 11px;
    margin: 0;
    max-width: 230px;
    border-radius: 0;
	width: 100%;
    border-bottom: 1px dashed rgba(0, 0, 0, .05);
}

div#pp-$form_type-$form_id.smiley1 input[type=text]:focus {
	outline: 0;
}

div#pp-$form_type-$form_id.smiley1 .ppsoclog {
	text-align: center;
	color: #F1F1F1;
	text-decoration: none;
	font-size: 12px;
    cursor: pointer;

}

div#pp-$form_type-$form_id.smiley1 .pploginbutton, div#pp-$form_type-$form_id.smiley1 a.ppsoclog {
	text-align: center;
	text-decoration: none;
    border: 0;
}

div#pp-$form_type-$form_id.smiley1 .ppform-submit-button {
    background: #f1f1f1;
    border: 1px solid rgba(0, 0, 0, .15);
    font-size: 15px;
    color: rgba(31, 31, 31, 0.5);
    text-transform: uppercase;
    max-width: 290px;
    width: 100%;
    padding: 8px;
    border-radius: 4px;
    box-shadow: 0 1px 1px rgba(0, 0, 0, .1);
    margin-bottom: 30px;
    text-align: center;
}
CSS;

    }
}