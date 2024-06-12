<?php

namespace ProfilePress\Libsodium\PremiumThemes\DragDrop\UserProfile;

use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\DragDropBuilder;
use ProfilePress\Core\Base;
use ProfilePress\Core\Themes\DragDrop\AbstractTheme;
use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\Fields;

class Monochrome extends AbstractTheme
{
    public static function default_field_listing()
    {
        Fields\Init::init();
        $standard_fields = DragDropBuilder::get_instance()->standard_fields();

        return [
            $standard_fields['profile-username'],
            $standard_fields['profile-email'],
            $standard_fields['profile-first-name'],
            $standard_fields['profile-last-name'],
            $standard_fields['profile-website'],
            $standard_fields['profile-bio'],
        ];
    }

    public function appearance_settings($settings)
    {
        $settings[] = [
            'id'       => 'monochrome_profile_header_text',
            'type'     => 'select',
            'label'    => esc_html__('Header Text', 'profilepress-pro'),
            'options'  => ppress_standard_fields_key_value_pair(),
            'priority' => 10
        ];

        $settings[] = [
            'id'       => 'monochrome_profile_section_header_text',
            'type'     => 'text',
            'label'    => esc_html__('Profile Header Text', 'profilepress-pro'),
            'priority' => 15
        ];

        return $settings;
    }

    public function color_settings($settings)
    {
        $settings2 = [
            [
                'id'    => 'monochrome_profile_bg_color',
                'type'  => 'color',
                'label' => esc_html__('Background', 'profilepress-pro')
            ],
            [
                'id'    => 'monochrome_profile_header_bg_color',
                'type'  => 'color',
                'label' => esc_html__('Header Background', 'profilepress-pro')
            ],
            [
                'id'    => 'monochrome_profile_header_text_color',
                'type'  => 'color',
                'label' => esc_html__('Header Text', 'profilepress-pro')
            ],
            [
                'id'    => 'monochrome_profile_header_border_color',
                'type'  => 'color',
                'label' => esc_html__('Text Underline', 'profilepress-pro')
            ],
            [
                'id'    => 'monochrome_profile_avatar_border_color',
                'type'  => 'color',
                'label' => esc_html__('Avatar Border', 'profilepress-pro')
            ],
            [
                'id'    => 'monochrome_profile_title_text_color',
                'type'  => 'color',
                'label' => esc_html__('Profile Title Text', 'profilepress-pro')
            ],
            [
                'id'    => 'monochrome_profile_info_text_color',
                'type'  => 'color',
                'label' => esc_html__('Profile Info Text', 'profilepress-pro')
            ]
        ];

        return array_merge($settings, $settings2);
    }

    public function metabox_settings($settings, $form_type, $DragDropBuilderInstance)
    {
        $settings['monochrome_profile_social_links'] = [
            'tab_title' => esc_html__('Social Links', 'profilepress-pro'),
            [
                'id'             => 'monochrome_profile_hide_social_links',
                'type'           => 'checkbox',
                'label'          => esc_html__('Hide Social Links', 'profilepress-pro'),
                'checkbox_label' => esc_html__('Check to hide', 'profilepress-pro'),
            ]
        ];

        return $settings;
    }

    public function default_metabox_settings()
    {
        $data                                           = parent::default_metabox_settings();
        $data['monochrome_profile_header_text']         = 'username';
        $data['monochrome_profile_section_header_text'] = esc_html__('Profile Details', 'profilepress-pro');
        $data['monochrome_profile_hide_social_links']   = 'false';
        $data['monochrome_profile_bg_color']            = '#ffffff';
        $data['monochrome_profile_header_bg_color']     = '#222222';
        $data['monochrome_profile_header_text_color']   = '#ffffff';
        $data['monochrome_profile_avatar_border_color'] = '#ffffff';
        $data['monochrome_profile_header_border_color'] = '#dddddd';
        $data['monochrome_profile_title_text_color']    = '#333333';
        $data['monochrome_profile_info_text_color']     = '#888888';

        return $data;
    }

    protected function social_links_block()
    {
        if ($this->get_meta('monochrome_profile_hide_social_links') == 'true') return '';

        $facebook_field  = $this->get_profile_field(Base::cif_facebook, true);
        $twitter_field   = $this->get_profile_field(Base::cif_twitter, true);
        $youtube_field   = $this->get_profile_field(Base::cif_youtube, true);
        $instagram_field = $this->get_profile_field(Base::cif_instagram, true);
        $github_field    = $this->get_profile_field(Base::cif_github, true);

        ob_start();
        ?>
        <ul class="monochrome-social">
            <?php if ( ! empty($facebook_field)) : ?>
                <li>
                    <a class="monochrome-facebook" href="<?= $facebook_field ?>"></a>
                </li>
            <?php endif;
            if ( ! empty($twitter_field)) : ?>
                <li>
                    <a class="monochrome-twitter" href="<?= $twitter_field ?>"></a>
                </li>
            <?php endif; ?>
            <?php if ( ! empty($youtube_field)) : ?>
                <li>
                    <a class="monochrome-youtube" href="<?= $youtube_field ?>"></a>
                </li>
            <?php endif; ?>
            <?php if ( ! empty($instagram_field)) : ?>
                <li>
                    <a class="monochrome-instagram" href="<?= $instagram_field ?>"></a>
                </li>
            <?php endif; ?>
            <?php if ( ! empty($github_field)) : ?>
                <li>
                    <a class="monochrome-github" href="<?= $github_field ?>"></a>
                </li>
            <?php endif; ?>
        </ul>

        <?php
        return ob_get_clean();
    }

    public function form_structure()
    {
        $header_text = $this->get_profile_field($this->get_meta('monochrome_profile_header_text'));

        $section_header_text = $this->get_meta('monochrome_profile_section_header_text');

        $social_links_block = $this->social_links_block();

        $profile_listing = $this->profile_listing()
                                ->title_start_tag('<tr><td class="monochrome-label">')
                                ->title_end_tag('</td>')
                                ->info_start_tag('<td>')
                                ->info_end_tag('</td></tr>')
                                ->forge()
                                ->output();

        return <<<HTML
[pp-form-wrapper class="monochrome-profile"]
    <div class="monochrome-cover">
		<div class="monochrome-avatar"><img src="[profile-avatar-url]"/></div>
		<div class="monochrome-uname">$header_text</div>
	</div>
	<div class="monochrome-contentCont">
		<div class="monochrome-content">
			<div class="monochrome-sectionTitle">$section_header_text</div>
			<table class="monochrome-table">
				$profile_listing
			</table>
		</div>
	</div>
	$social_links_block
[/pp-form-wrapper]
HTML;

    }

    public function form_css()
    {
        $form_id   = $this->form_id;
        $form_type = $this->form_type;

        $bg_color            = $this->get_meta('monochrome_profile_bg_color');
        $header_bg_color     = $this->get_meta('monochrome_profile_header_bg_color');
        $header_text_color   = $this->get_meta('monochrome_profile_header_text_color');
        $avatar_border_color = $this->get_meta('monochrome_profile_avatar_border_color');
        $border_color        = $this->get_meta('monochrome_profile_header_border_color');
        $profile_title_text  = $this->get_meta('monochrome_profile_title_text_color');
        $profile_info_text   = $this->get_meta('monochrome_profile_info_text_color');

        $asset_url = $this->asset_image_url . '/monochrome';
        $google_font = apply_filters('ppress_disable_google_fonts', false) ? '' :
            "@import url('https://fonts.googleapis.com/css?family=Lato:300,400,600,700|Raleway:300,400,600,700&display=swap');";

        return <<<CSS
$google_font

div#pp-$form_type-$form_id.monochrome-wrapper * {
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}

div#pp-$form_type-$form_id.monochrome-profile {
    max-width: 580px;
    width: 100%;
    background: $bg_color;
    margin-left: auto;
    margin-right: auto;
    text-align: left;
    overflow: hidden;
    color: $profile_info_text;
    font-family: 'Helvetica Neue', helvetica, arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-font-smoothing: antialiased;
    font-smoothing: antialiased;
}

div#pp-$form_type-$form_id.monochrome-profile .monochrome-cover {
    background-color: $header_bg_color;
    padding: 30px;
    text-align: center;
    overflow: hidden;
}

div#pp-$form_type-$form_id.monochrome-profile .monochrome-avatar {
    display: block;
    float: left;
    padding: 5px;
    -webkit-border-radius: 50%;
    -moz-border-radius: 50%;
    border-radius: 50%;
    background: $avatar_border_color;
}

div#pp-$form_type-$form_id.monochrome-profile .monochrome-avatar img {
    width: 70px;
    height: 70px;
    display: block;
    -webkit-border-radius: 50%;
    -moz-border-radius: 50%;
    border-radius: 50%;
    overflow: hidden;
}

div#pp-$form_type-$form_id.monochrome-profile .monochrome-uname {
    display: block;
    text-align: center;
    color: #fff;
    font-size: 24px;
    line-height: 34px;
    font-weight: bold;
    margin-top: 23px;
    margin-left: 30px;
    float: left;
}

div#pp-$form_type-$form_id.monochrome-profile .monochrome-uname a {
    color: $header_text_color;
    text-decoration: none;
}

div#pp-$form_type-$form_id.monochrome-profile .monochrome-social {
    list-style: none;
    overflow: hidden;
    padding: 15px 25px;
    padding-bottom: 5px;
    border: 1px solid $border_color;
    border-top: 0;
    margin: -1px 0 0 !important;
}

div#pp-$form_type-$form_id.monochrome-profile .monochrome-social li {
    float: left;
    display: block;
    margin-right: 10px;
    margin-bottom: 10px;
}

div#pp-$form_type-$form_id.monochrome-profile .monochrome-social a {
    display: block;
    width: 32px;
    height: 32px;
    background-size: 32px 32px;
    opacity: 0.75;
    text-decoration: none;
    box-shadow: none;
}

div#pp-$form_type-$form_id.monochrome-profile .monochrome-social a:hover {
    opacity: 1;
    text-decoration: none;
    box-shadow: none;
}

div#pp-$form_type-$form_id.monochrome-profile a.monochrome-dribbble {
    background-image: url($asset_url/dribbble.png);
}

div#pp-$form_type-$form_id.monochrome-profile a.monochrome-facebook {
    background-image: url($asset_url/facebook.png);
}

div#pp-$form_type-$form_id.monochrome-profile a.monochrome-github {
    background-image: url($asset_url/github.png);
}

div#pp-$form_type-$form_id.monochrome-profile a.monochrome-instagram {
    background-image: url($asset_url/instagram.png);
}

div#pp-$form_type-$form_id.monochrome-profile a.monochrome-pinterest {
    background-image: url($asset_url/pinterest.png);
}

div#pp-$form_type-$form_id.monochrome-profile a.monochrome-soundcloud {
    background-image: url($asset_url/soundcloud.png);
}

div#pp-$form_type-$form_id.monochrome-profile a.monochrome-twitter {
    background-image: url($asset_url/twitter.png);
}

div#pp-$form_type-$form_id.monochrome-profile a.monochrome-youtube {
    background-image: url($asset_url/youtube.png);
}

div#pp-$form_type-$form_id.monochrome-profile .monochrome-contentCont {
    border: 1px solid $border_color;
    border-top: 0;
}

div#pp-$form_type-$form_id.monochrome-profile .monochrome-content {
    padding: 25px;
}

div#pp-$form_type-$form_id.monochrome-profile .monochrome-sectionTitle {
    display: block;
    color: $profile_title_text;
    font-size: 14px;
    line-height: 24px;
    padding-bottom: 10px;
    margin-bottom: 10px;
    border-bottom: 1px solid $border_color;
    font-weight: bold;
}

div#pp-$form_type-$form_id.monochrome-profile .monochrome-table {
    width: 100%;
    margin: 0;
    padding: 0;
    border: 0;
    border-collapse: collapse;
    font-size: 12px;
    line-height: 22px;
}

div#pp-$form_type-$form_id.monochrome-profile .monochrome-table td {
    padding: 8px 10px;
    width: 75%;
    border: none;
}

div#pp-$form_type-$form_id.monochrome-profile .monochrome-table td.monochrome-label {
    width: 25%;
    border: none;
}

div#pp-$form_type-$form_id.monochrome-profile .monochrome-label {
    font-weight: bold;
    color: $profile_title_text;
}
CSS;

    }
}