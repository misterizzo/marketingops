<?php

namespace ProfilePress\Libsodium\PremiumThemes\DragDrop\UserProfile;

use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\DragDropBuilder;
use ProfilePress\Core\Base;
use ProfilePress\Core\Themes\DragDrop\AbstractTheme;
use ProfilePress\Core\Admin\SettingsPages\DragDropBuilder\Fields;

class Smiley extends AbstractTheme
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
            'id'       => 'smiley_profile_header_text',
            'type'     => 'select',
            'label'    => esc_html__('Header Text', 'profilepress-pro'),
            'options'  => ppress_standard_fields_key_value_pair(),
            'priority' => 10
        ];

        $settings[] = [
            'id'          => 'smiley_profile_byline',
            'type'        => 'select',
            'label'       => esc_html__('Byline', 'profilepress-pro'),
            'options'     => ppress_standard_custom_fields_key_value_pair(),
            'description' => esc_html__('Select custom field to use as the profile header byline.', 'profilepress-pro'),
            'priority'    => 15
        ];

        return $settings;
    }

    public function metabox_settings($settings, $form_type, $DragDropBuilderInstance)
    {
        $settings['smiley_profile_social_links'] = [
            'tab_title' => esc_html__('Social Links', 'profilepress-pro'),
            [
                'id'             => 'smiley_profile_hide_social_links',
                'type'           => 'checkbox',
                'label'          => esc_html__('Hide Social Links', 'profilepress-pro'),
                'checkbox_label' => esc_html__('Check to hide', 'profilepress-pro'),
            ]
        ];

        $settings['smiley_profile_carousel_post'] = [
            'tab_title' => esc_html__('Posts Carousel', 'profilepress-pro'),
            [
                'id'             => 'smiley_profile_hide_posts_carousel',
                'type'           => 'checkbox',
                'label'          => esc_html__('Hide Posts Carousel', 'profilepress-pro'),
                'checkbox_label' => esc_html__('Check to hide', 'profilepress-pro'),
            ],
            [
                'id'    => 'smiley_profile_carousel_post_headline',
                'type'  => 'text',
                'label' => esc_html__('Title', 'profilepress-pro'),
            ],
            [
                'id'    => 'smiley_profile_carousel_post_count',
                'type'  => 'number',
                'label' => esc_html__('Number of Posts', 'profilepress-pro'),
            ]
        ];

        return $settings;
    }

    public function default_metabox_settings()
    {
        $data                                          = parent::default_metabox_settings();
        $data['smiley_profile_header_text']            = 'last_first_names';
        $data['smiley_profile_byline']                 = '';
        $data['smiley_profile_hide_social_links']      = 'false';
        $data['smiley_profile_carousel_post_count']    = '20';
        $data['smiley_profile_carousel_post_headline'] = esc_html__('My Articles', 'profilepress-pro');
        $data['smiley_profile_hide_posts_carousel']    = 'false';

        return $data;
    }

    protected function social_links_block()
    {
        if ($this->get_meta('smiley_profile_hide_social_links') == 'true') return;

        $asset_url = $this->asset_image_url . '/smiley';

        $facebook_field = $this->get_profile_field(Base::cif_facebook, true);
        $twitter_field  = $this->get_profile_field(Base::cif_twitter, true);
        $linkedin_field = $this->get_profile_field(Base::cif_linkedin, true);

        ob_start();
        ?>
        <ul class="ppsocial">
            <?php if ( ! empty($facebook_field)) : ?>
                <li>
                    <a href="<?= $facebook_field ?>"><img src="<?= $asset_url ?>/facebook.png"/></a>
                </li>
            <?php endif; ?>
            <?php if ( ! empty($twitter_field)) : ?>
                <li>
                    <a href="<?= $twitter_field ?>"><img src="<?= $asset_url ?>/twitter.png"/></a>
                </li>
            <?php endif; ?>
            <?php if ( ! empty($linkedin_field)) : ?>
                <li>
                    <a href="<?= $linkedin_field ?>"><img src="<?= $asset_url ?>/linkedin.png"/></a>
                </li>
            <?php endif; ?>
        </ul>
        <?php
        return ob_get_clean();
    }

    protected function posts_carousel_block()
    {
        if ($this->get_meta('smiley_profile_hide_posts_carousel') == 'true') return;

        $headline = $this->get_meta('smiley_profile_carousel_post_headline');
        $count    = $this->get_meta('smiley_profile_carousel_post_count');

        ob_start();
        ?>
        <div class="pp-jca">
            <span class="jcname"><?= $headline ?></span>
            [jcarousel-author-posts count="<?= $count ?>"]
        </div>
        <?php
        return ob_get_clean();
    }

    protected function header_text()
    {
        $header_text = $this->get_meta('smiley_profile_header_text');
        if (empty($header_text)) return '';

        return apply_filters('ppress_smiley_profile_theme_header_text', sprintf('<span class="ppname">%s</span><br>', $this->get_profile_field($header_text)));
    }

    protected function byline()
    {
        $byline = $this->get_profile_field($this->get_meta('smiley_profile_byline'), true);

        if (empty($byline)) return '';

        return apply_filters('ppress_smiley_profile_theme_byline', sprintf('<span class="pptitle">%s</span>', $byline));
    }

    public function form_structure()
    {
        $header_text          = $this->header_text();
        $byline               = $this->byline();
        $social_links_block   = $this->social_links_block();
        $posts_carousel_block = $this->posts_carousel_block();

        $profile_listing = $this->profile_listing()
                                ->title_start_tag('<li><strong>')
                                ->title_end_tag(':</strong>')
                                ->info_start_tag(' <span class="pprof-val">')
                                ->info_end_tag('</span></li>')
                                ->forge()
                                ->output();

        return <<<HTML
[pp-form-wrapper class="smileyprofile1"]
<div class="ppprofile1">
	<div class="ppboxa">
		<img src="[profile-avatar-url]" class="ppavatar"/>
	</div>
	<div class="ppboxb">
		<div class="ppuserdata">
			$social_links_block
			$header_text
			$byline
		</div>
		<div class="ppprofdata">
			<ul>
			    $profile_listing
			</ul>
		</div>
		$posts_carousel_block
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

div#pp-$form_type-$form_id.smileyprofile1 * {
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}

div#pp-$form_type-$form_id.smileyprofile1 {
    font-family: 'Montserrat', sans-serif;
    max-width: 580px;
    width: 100%;
    margin: auto;
}

div#pp-$form_type-$form_id.smileyprofile1 .ppboxa {
    background: transparent;
    height: 100px;
    margin: 0 auto;
    text-align: center;
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
}

div#pp-$form_type-$form_id.smileyprofile1 .ppavatar {
    display: inline;
    border-radius: 120px;
    border: 6px solid #fff;
    margin-top: 05px;
    background: #f1f1f1;
    max-width: 175px;
    width: 100%;
}

div#pp-$form_type-$form_id.smileyprofile1 .ppboxb {
    background: url({$asset_url}/bg1.png);
    width: 100%;
    margin: 0 auto;
    color: #fff;
    border-radius: 4px;
    padding-bottom: 4px;
}

div#pp-$form_type-$form_id.smileyprofile1 .ppuserdata {
    text-align: center;
    padding-top: 110px;
    text-shadow: 0 1px 1px rgba(0, 0, 0, .25);
}

div#pp-$form_type-$form_id.smileyprofile1 .pp-jca {
    text-align: center;
    padding: 10px 30px;
}

div#pp-$form_type-$form_id.smileyprofile1 span.ppname {
    font-size: 36px;
}

.pp-jca span.jcname {
    font-size: 20px;
}

div#pp-$form_type-$form_id.smileyprofile1 span.pptitle {
    text-transform: uppercase;
    font-size: 12px;
    color: rgba(255, 255, 255, .8);
}

div#pp-$form_type-$form_id.smileyprofile1 .ppprofdata {
    padding: 30px;
    background: rgba(0, 0, 0, .1);
    margin-top: 50px;
    margin: 30px;
    font-size: 15px;
    border-radius: 4px;
    text-shadow: 0 1px 1px rgba(0, 0, 0, .25);
}

.ppprofdata .pprof-val {
    float: right;
    width: 70%;
}

div#pp-$form_type-$form_id.smileyprofile1 .ppprofdata ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

div#pp-$form_type-$form_id.smileyprofile1 .ppprofdata ul li {
    padding-bottom: 10px;
}

div#pp-$form_type-$form_id.smileyprofile1 .ppprofdata ul li a {
    color: #F1F1F1;
    text-decoration: none;
}

div#pp-$form_type-$form_id.smileyprofile1 .ppprofdata ul li a {
    color: #F1F1F1;
    text-decoration: none;
    border-bottom: 0 none;
}

div#pp-$form_type-$form_id.smileyprofile1 .ppuserdata ul li img {
    display: inline;
}

div#pp-$form_type-$form_id.smileyprofile1 .ppsocial {
    margin: 0;
    padding: 0;
    text-align: left;
    position: absolute;
    margin-top: -90px;
    margin-left: 20px;
    opacity: 0.2;
}

div#pp-$form_type-$form_id.smileyprofile1 .ppsocial li {
    list-style: none;
    display: inline;
}
CSS;

    }
}