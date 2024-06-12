<?php

namespace ProfilePress\Libsodium\PremiumThemes\DragDrop\EditProfile;

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
            $standard_fields['edit-profile-username'],
            $standard_fields['edit-profile-email'],
            $standard_fields['edit-profile-first-name'],
            $standard_fields['edit-profile-last-name'],
            $standard_fields['edit-profile-website'],
            $standard_fields['edit-profile-bio'],
        ];
    }

    public function appearance_settings($settings)
    {
        $settings[] = [
            'id'       => 'smiley_edit_profile_header_image',
            'type'     => 'upload',
            'label'    => esc_html__('Header Image', 'profilepress-pro'),
            'priority' => 10
        ];

        $settings[] = [
            'id'       => 'smiley_edit_profile_headline',
            'type'     => 'text',
            'label'    => esc_html__('Headline', 'profilepress-pro'),
            'priority' => 20
        ];

        return $settings;
    }

    public function default_metabox_settings()
    {
        $data                                     = parent::default_metabox_settings();
        $data['smiley_edit_profile_headline']     = esc_html__('Edit Profile', 'profilepress-pro');
        $data['smiley_edit_profile_header_image'] = $this->asset_image_url . "/smiley/avatar.gif";

        return $data;
    }

    public function form_structure()
    {
        $fields        = $this->field_listing();
        $signup_button = $this->form_submit_button();
        $headline      = $this->get_meta('smiley_edit_profile_headline');
        $header_image  = $this->get_meta('smiley_edit_profile_header_image');

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
        </div>
        <div class="pploginbutton">
            $signup_button
        </div>
    </div>
[/pp-form-wrapper]
HTML;

    }

    public function form_css()
    {
        $form_id   = $this->form_id;
        $form_type = $this->form_type;

        $asset_url = $this->asset_image_url . '/smiley';

        $google_font = apply_filters('ppress_disable_google_fonts', false) ? '' :
            "@import url('https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700&display=swap');";

        return <<<CSS
$google_font
/* css class for the form generated errors */
#pp-$form_type-$form_id-wrap .profilepress-edit-profile-status {
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
    max-width: 400px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}

#pp-$form_type-$form_id-wrap .profilepress-edit-profile-status.success {
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
    max-width: 400px;
    width: 100%;
    font-size: 14px;
    margin: 0 auto;
}

div#pp-$form_type-$form_id.smiley1 .pp-top {
    border-top-left-radius:4px !important;
    border-top-right-radius:4px !important;
}

div#pp-$form_type-$form_id.smiley1 input[type="text"]:disabled {
  background: #f4f4f4;
}

div#pp-$form_type-$form_id.smiley1 .pp-bottom {
    border-bottom-left-radius:4px !important;
    border-bottom-right-radius:4px !important;
    border-bottom:0;
}

div#pp-$form_type-$form_id.smiley1 .pp-del-pix {
    font-size: 10px !important;
    background-color: rgb(221, 51, 51) !important;
    border: 0 none !important;
}

div#pp-$form_type-$form_id.smiley1 .ppboxa {
    background: transparent;
    width: 100%;
    height: 80px;
    margin: 0 auto;
    text-align: center;
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
    position: relative;
}

div#pp-$form_type-$form_id.smiley1 .ppavatar {
    border-radius: 120px;
    border: 6px solid #fff;
    margin: auto;
    background: #f1f1f1;
    max-width: 140px;
    width: 100%;
    height: auto;
    position: relative;
}

div#pp-$form_type-$form_id.smiley1 .ppboxb {
    background: url({$asset_url}/bg1.png);
    width: 100%;
    margin: 0 auto;
    color: #fff;
    border-radius: 4px;
}

div#pp-$form_type-$form_id.smiley1 .ppuserdata {
    text-align: center;
    padding-top: 80px;
    text-shadow: 0 1px 1px rgba(0, 0, 0, .25);
}
div#pp-$form_type-$form_id.smiley1 span.ppname {
    font-size: 32px;
}
div#pp-$form_type-$form_id.smiley1 span.pptitle {
    text-transform: uppercase;
    font-size: 12px;
    color: rgba(255, 255, 255, .8);
}
div#pp-$form_type-$form_id.smiley1 .ppprofdata {
    padding: 30px;
    background: rgba(0, 0, 0, .1);
    margin: 30px;
    border-radius: 4px;
    text-shadow: 0 1px 1px rgba(0, 0, 0, .25);
}

div#pp-$form_type-$form_id.smiley1 .ppprofdata div.heading {
    text-align:center;
}
div#pp-$form_type-$form_id.smiley1 .ppprofdata ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

div#pp-$form_type-$form_id.smiley1 .ppprofdata ul li a {
    color: #F1F1F1;
    text-decoration: none;
}
div#pp-$form_type-$form_id.smiley1 .ppprofdata ul li strong {
    padding-right: 6px;
    color: #D6EBFA;
}

div#pp-$form_type-$form_id.smiley1 .ppsocial {
    margin: 0;
    padding: 0;
    text-align: left;
    position: absolute;
    margin-top: -90px;
    margin-left: 20px;
    opacity: 0.2;
}

div#pp-$form_type-$form_id.smiley1 .ppsocial li {
    list-style: none;
    display: inline;
}

div#pp-$form_type-$form_id.smiley1 select, 
div#pp-$form_type-$form_id.smiley1 input, 
div#pp-$form_type-$form_id.smiley1 textarea {
    border: 0;
    margin: 0;
    border-radius: 0;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    color: #2b2b2b;
    font-weight: 500;
    background-color:#fff;
    font-size: 14px;
    padding: 11px;
    max-width: 280px;
    width: 100%;
    border-bottom: 1px dashed rgba(0, 0, 0, .05);
    text-align: left;
    margin-bottom:0;
}

div#pp-$form_type-$form_id.smiley1 .ppprofdata textarea {
  height: 140px;
}

div#pp-$form_type-$form_id.smiley1 input[type=text]:focus,
div#pp-$form_type-$form_id.smiley1 input[type=password]:focus,
div#pp-$form_type-$form_id.smiley1 input[type=file]:focus, .smiley1 textarea:focus {
    outline: 0;
}

div#pp-$form_type-$form_id.smiley1 .pploginbutton {
    text-align: center;
}

div#pp-$form_type-$form_id.smiley1 .ppform-submit-button {
    background: #f1f1f1;
    border: 1px solid rgba(0, 0, 0, .15);
    font-size: 15px;
    color: rgba(31, 31, 31, 0.5);
    text-transform: uppercase;
    max-width: 340px;
    width: 100%;
    padding: 8px;
    border-radius: 4px;
    box-shadow: 0 1px 1px rgba(0, 0, 0, .1);
    margin-bottom: 30px;
    text-align: center;
}

div#pp-$form_type-$form_id.smiley1 .pp-form-field-wrap .pp-form-label-wrap {
    margin-top: 10px;
    font-size: 16px;
}

div#pp-$form_type-$form_id.smiley1 .pp-form-field-wrap .pp-form-label-wrap label {
    font-size: 16px;
}

div#pp-$form_type-$form_id.smiley1 .select2-container--default .select2-selection--multiple .select2-selection__choice {
    color: initial;
}
CSS;

    }
}