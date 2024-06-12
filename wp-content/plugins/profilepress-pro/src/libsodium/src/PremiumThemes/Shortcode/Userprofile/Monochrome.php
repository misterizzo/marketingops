<?php

namespace ProfilePress\Libsodium\PremiumThemes\Shortcode\Userprofile;

use ProfilePress\Core\Themes\Shortcode\ThemeInterface;

class Monochrome implements ThemeInterface
{
    public function get_name()
    {
        return 'Monochrome';
    }

    public function get_structure()
    {
        return <<<CODE
<div class="monochrome-profile">
	<div class="monochrome-cover">
		<div class="monochrome-avatar"><img src="[profile-avatar-url]"/></div>
		<div class="monochrome-uname">[profile-username]</div>
	</div>
	<div class="monochrome-contentCont">
		<div class="monochrome-content">
			<div class="monochrome-sectionTitle">Profile Details</div>
			<table class="monochrome-table">
				<tr>
					<td class="monochrome-label">First name</td>
					<td>[profile-first-name]</td>
				</tr>
				<tr>
					<td class="monochrome-label">Last name</td>
					<td>[profile-last-name]</td>
				</tr>
				<tr>
					<td class="monochrome-label">Biography</td>
					<td>[profile-bio]</td>
				</tr>
				<tr>
					<td class="monochrome-label">Gender</td>
					<td>[profile-cpf key="gender"]</td>
				</tr>
				<tr>
					<td class="monochrome-label">Country</td>
					<td>[profile-cpf key="country"]</td>
				</tr>
			</table>
		</div>
	</div>
	<ul class="monochrome-social">
		<li><a class="monochrome-dribbble" href="[profile-cpf key=dribbble]"></a></li>
		<li><a class="monochrome-facebook" href="[profile-cpf key=facebook]"></a></li>
		<li><a class="monochrome-flickr" href="[profile-cpf key=flickr]"></a></li>
		<li><a class="monochrome-github" href="[profile-cpf key=github]"></a></li>
		<li><a class="monochrome-instagram" href="[profile-cpf key=instagram]"></a></li>
		<li><a class="monochrome-pinterest" href="[profile-cpf key=pinterest]"></a></li>
		<li><a class="monochrome-soundcloud" href="[profile-cpf key=soundcloud]"></a></li>
		<li><a class="monochrome-spotify" href="[profile-cpf key=spotify]"></a></li>
		<li><a class="monochrome-twitter" href="[profile-cpf key=twitter]"></a></li>
		<li><a class="monochrome-youtube" href="[profile-cpf key=youtube]"></a></li>
	</ul>
</div>
CODE;

    }

    public function get_css()
    {
        $asset_url = PPRESS_ASSETS_URL;

        return <<<CSS
.monochrome-profile {
    max-width: 580px;
    margin-left: auto;
    margin-right: auto;
    text-align: left;
    overflow: hidden;
    color: #888;
    font-family: 'Helvetica Neue', helvetica, arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-font-smoothing: antialiased;
    font-smoothing: antialiased;
}

.monochrome-profile p {
    margin: 0 0 5px 0 !important;
    padding: 0 !important;
}

.monochrome-cover {
    background-color: #222;
    padding: 30px;
    text-align: center;
    overflow: hidden;
}

.monochrome-avatar {
    display: block;
    float: left;
    padding: 5px;
    -webkit-border-radius: 50%;
    -moz-border-radius: 50%;
    border-radius: 50%;
    background: #fff;
}

.monochrome-avatar img {
    width: 70px;
    height: 70px;
    display: block;
    -webkit-border-radius: 50%;
    -moz-border-radius: 50%;
    border-radius: 50%;
    overflow: hidden;
}

.monochrome-uname {
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

.monochrome-uname a {
    color: #fff;
    text-decoration: none;
}

.monochrome-social {
    list-style: none;
    overflow: hidden;
    padding: 15px 25px;
    padding-bottom: 5px;
    border: 1px solid #ddd;
    border-top: 0;
    margin: -1px 0 0 !important;
}

.monochrome-social li {
    float: left;
    display: block;
    margin-right: 10px;
    margin-bottom: 10px;
}

.monochrome-social a {
    display: block;
    width: 32px;
    height: 32px;
    background-size: 32px 32px;
    opacity: 0.75;
    text-decoration: none;
    box-shadow: none;
}

.monochrome-social a:hover {
    opacity: 1;
    text-decoration: none;
    box-shadow: none;
}

a.monochrome-dribbble {
    background-image: url($asset_url/images/monochrome/dribbble.png);
}

a.monochrome-facebook {
    background-image: url($asset_url/images/monochrome/facebook.png);
}

a.monochrome-flickr {
    background-image: url($asset_url/images/monochrome/flickr.png);
}

a.monochrome-github {
    background-image: url($asset_url/images/monochrome/github.png);
}

a.monochrome-instagram {
    background-image: url($asset_url/images/monochrome/instagram.png);
}

a.monochrome-pinterest {
    background-image: url($asset_url/images/monochrome/pinterest.png);
}

a.monochrome-rss {
    background-image: url($asset_url/images/monochrome/rss.png);
}

a.monochrome-soundcloud {
    background-image: url($asset_url/images/monochrome/soundcloud.png);
}

a.monochrome-spotify {
    background-image: url($asset_url/images/monochrome/spotify.png);
}

a.monochrome-twitter {
    background-image: url($asset_url/images/monochrome/twitter.png);
}

a.monochrome-youtube {
    background-image: url($asset_url/images/monochrome/youtube.png);
}

.monochrome-contentCont {
    border: 1px solid #ddd;
    border-top: 0;
}

.monochrome-content {
    padding: 25px;
}

.monochrome-sectionTitle {
    display: block;
    color: #333;
    font-size: 14px;
    line-height: 24px;
    padding-bottom: 10px;
    margin-bottom: 10px;
    border-bottom: 1px solid #ddd;
    font-weight: bold;
}

.monochrome-table {
    width: 100%;
    margin: 0;
    padding: 0;
    border: 0;
    border-collapse: collapse;
    font-size: 12px;
    line-height: 22px;
}

.monochrome-table td {
    padding: 8px 10px;
    width: 75%;
    border: none;
}

.monochrome-table td.monochrome-label {
    width: 25%;
    border: none;
}

.monochrome-label {
    font-weight: bold;
    color: #333;
}
CSS;

    }
}