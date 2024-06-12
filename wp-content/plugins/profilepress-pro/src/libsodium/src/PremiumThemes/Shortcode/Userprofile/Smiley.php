<?php

namespace ProfilePress\Libsodium\PremiumThemes\Shortcode\Userprofile;

use ProfilePress\Core\Themes\Shortcode\ThemeInterface;

class Smiley implements ThemeInterface
{
    public function get_name()
    {
        return 'Smiley';
    }

    public function get_structure()
    {
        $asset_folder = PPRESS_ASSETS_URL . '/images/smiley';

        return <<<CODE
<div class="ppprofile1">
	<div class="ppboxa">
		<img src="[profile-avatar-url]" class="ppavatar"/>
	</div>
	<div class="ppboxb">
		<div class="ppuserdata">
			<ul class="ppsocial">
				<li>
					<a href="[profile-cpf key=facebook]"><img src="$asset_folder/facebook.png"/></a>
				</li>
				<li>
					<a href="[profile-cpf key=twitter]"><img src="$asset_folder/twitter.png"/></a>
				</li>
				<li>
					<a href="[profile-cpf key=linkedin]"><img src="$asset_folder/linkedin.png"/></a>
				</li>
			</ul>
			<span class="ppname">[profile-first-name] [profile-last-name]</span><br/>
			<span class="pptitle">[profile-cpf key="country"]</span>
		</div>
		<div class="ppprofdata">
			<ul>
				<li><strong>Email:</strong> <span class="pprof-val">[profile-email]</span></li>
				<li><strong>Gender:</strong> <span class="pprof-val">[profile-cpf key="gender"]</span></li>
				<li>
					<strong>Website:</strong> <span class="pprof-val"><a href="[profile-website]">[profile-website]</a></span>
				</li>
				<li><strong>Country:</strong> <span class="pprof-val">[profile-cpf key="country"]</span></li>
				<li>
					<strong>Bio:</strong> <span class="pprof-val">[profile-bio]</span>
				</li>
			</ul>
		</div>
		<div class="pp-jca">
			<span class="jcname">My Articles</span> [jcarousel-author-posts count="20"]
		</div>
	</div>
</div>
CODE;

    }

    public function get_css()
    {
        $asset_folder = PPRESS_ASSETS_URL . '/images/smiley';

        return <<<CSS
.ppprofile1 .ppboxa {
    background: transparent;
    max-width: 550px;
    width: 100%;
    height: 100px;
    margin: 0 auto;
    text-align: center;
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
}

.ppprofile1 .ppavatar {
    display: inline;
    border-radius: 120px;
    border: 6px solid #fff;
    margin-top: 05px;
    background: #f1f1f1;
    max-width: 175px;
    width: 100%;
    height: auto;
}

.ppprofile1 .ppboxb {
    background: url($asset_folder/bg1.png);
    max-width: 580px;
    width: 100%;
    margin: 0 auto;
    color: #fff;
    border-radius: 4px;
    padding-bottom: 4px;
}

.ppprofile1 .ppuserdata {
    text-align: center;
    padding-top: 110px;
    text-shadow: 0 1px 1px rgba(0, 0, 0, .25);
}

.ppprofile1 .pp-jca {
    text-align: center;
    padding: 20px 5px;
}

.ppprofile1 span.ppname {
    font-size: 32px;
}

.pp-jca span.jcname {
    font-size: 20px;
}

.ppprofile1 span.pptitle {
    text-transform: uppercase;
    font-size: 12px;
    color: rgba(255, 255, 255, .8);
}

.ppprofile1 .ppprofdata {
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
    width: 80%;
}

.ppprofile1 .ppprofdata ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.ppprofile1 .ppprofdata ul li {
    padding-bottom: 10px;
}

.ppprofile1 .ppprofdata ul li a {
    color: #F1F1F1;
    text-decoration: none;
}

.ppprofile1 .ppprofdata ul li a {
    color: #F1F1F1;
    text-decoration: none;
    border-bottom: 0 none;
}

.ppprofile1 .ppuserdata ul li img {
    display: inline;
}

.ppprofile1 .ppsocial {
    margin: 0;
    padding: 0;
    text-align: left;
    position: absolute;
    margin-top: -90px;
    margin-left: 20px;
    opacity: 0.2;
}

.ppprofile1 .ppsocial li {
    list-style: none;
    display: inline;
}
CSS;

    }
}