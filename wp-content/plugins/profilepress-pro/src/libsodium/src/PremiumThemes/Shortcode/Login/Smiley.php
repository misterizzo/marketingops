<?php

namespace ProfilePress\Libsodium\PremiumThemes\Shortcode\Login;

use ProfilePress\Core\Themes\Shortcode\LoginThemeInterface;

class Smiley implements LoginThemeInterface
{
    public function get_name()
    {
        return 'Smiley';
    }

    public function get_structure()
    {
        $asset_url = PPRESS_ASSETS_URL . '/images/smiley';

        return <<<CODE
<div class="smiley1">
    <div class="ppboxa">
        <img src="{$asset_url}/avatar.gif" class="ppavatar"/>
    </div>
    <div class="ppboxb">
        <div class="ppuserdata">
            <span class="ppname">Sign In</span>
        </div>
        <div class="ppprofdata">
            <form>
                [login-username class="smi-username" placeholder="Username"]
                [login-password class="smi-password" placeholder="Password"]
                <br style="clear:both"/><br/>
                <a id="ppsoclog" class="ppsoclog smi-links">Login with social media instead?</a>
                <br/><br/> [link-lost-password class="ppsoclog smi-links" label="Forgot your password?"]
                <br/><br/> [link-registration class="ppsoclog smi-links" label="Not registered yet?"]

        </div>
        <div class="ppsocialmedia">
            [pp-social-login type="facebook"]<br>
            [pp-social-login type="twitter"]<br>
            [pp-social-login type="google"]<br>
            [pp-social-login type="linkedin"]<br>
        </div>
        <div class="pploginbutton">
            [login-submit class="pplogbutt" value="Sign In"]
        </div>

    </div>
</div>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
				$("#ppsoclog").on('click', function () {
					$(".ppsocialmedia").toggle('slow');
				});
			}
		);
	})(jQuery);
</script>
CODE;

    }

    public function get_css()
    {
        $asset_url = PPRESS_ASSETS_URL . '/images/smiley';
        return <<<CSS
@import url('https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700&display=swap');

/*  css class for the form generated errors */
.profilepress-login-status {
	background-color: #34495e;
    color: #ffffff;
    border: medium none;
    border-radius: 4px;
    font-size: 15px;
    font-weight: normal;
    text-align: center;
    line-height: 1.4;
    padding: 8px 5px;
    margin: 5px auto;
    max-width: 360px;
}

.profilepress-login-status a {
    color: #ea9629 !important;
}

.smiley1 {
    font-family: 'Montserrat', sans-serif;
    font-size: 14px;
}

.smiley1 a.pp-button-social-login {
	font-weight:normal;
  	min-width: 250px;
  	font-size:14px;
}

.smiley1 .ppboxa {
	background: transparent;
	max-width: 350px;
	width: 100%;
	height: 80px;
	margin: 0 auto;
	text-align: center;
	border-top-left-radius: 4px;
	border-top-right-radius: 4px;
}

.smiley1 .ppavatar {
    display: inline;
    border-radius: 120px;
    border: 6px solid #fff;
    margin-top: 05px;
    background: #f1f1f1;
    max-width: 120px;
    width: 100%;
}

.smiley1 .smi-username {
  border-top-left-radius:4px;
  border-top-right-radius:4px;
}

.smiley1 .smi-password {
  border-bottom:0;
  border-bottom-left-radius:4px;
  border-bottom-right-radius:4px;
}

.smiley1 .smi-links {
  font-size: 14px !important;
}

.smiley1 .ppboxb {
	background: url({$asset_url}/bg1.png);
	max-width: 350px;
	width: 100%;
	/* height: 400px; */
	margin: 0 auto;
	color: #fff;
	border-radius: 4px;
	
}

.smiley1 .ppuserdata {
    text-align: center;
    padding-top: 80px;
    text-shadow: 0 1px 1px rgba(0, 0, 0, .25);
}

.smiley1 span.ppname {
    font-size: 32px;
}

.smiley1 span.pptitle {
    text-transform: uppercase;
    font-size: 12px;
    color: rgba(255, 255, 255, .8);
}

.smiley1 .ppprofdata {
    padding: 30px;
    background: rgba(0, 0, 0, .1);
    margin: 30px;
    border-radius: 4px;
    text-shadow: 0 1px 1px rgba(0, 0, 0, .25);
    text-align: center;
    padding-bottom: 5px;
}

.smiley1 .ppprofdata a {
  color: #fff !important;
}

.smiley1 .ppprofdata ul {
	list-style: none;
	margin: 0;
	padding: 0;
}

.smiley1 .ppprofdata ul li {
	padding-bottom: 10px;
}

.smiley1 .ppprofdata ul li a{
	color: #F1F1F1;
	text-decoration: none;
}

.smiley1 .ppprofdata ul li strong {
	padding-right: 6px;
	color: #D6EBFA;
}

.smiley1 .ppsocial {
	margin: 0;
	padding: 0;
	text-align: left;
    position: absolute;
    margin-top: -90px;
    margin-left: 20px;
    opacity: 0.2;
}

.smiley1 .ppsocial li {
	list-style: none;
	display: inline;
}

.smiley1 input[type=text], .smiley1 input[type=password] {
    border: 0;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    color: #7c7c7c;
    background-color: #fff;
    font-size: 14px;
    padding: 11px;
    max-width: 230px;
    width: 100%;
    margin: 0;
    border-bottom: 1px dashed rgba(0, 0, 0, .05);
    margin-bottom: 0;
}

.smiley1 input[type=text]:focus, .smiley1 input[type=password]:focus {
	outline: 0;
}

.smiley1 .ppsoclog {
	text-align: center;
	color: #F1F1F1;
	text-decoration: none;
	font-size: 12px;
    cursor: pointer;
    border: 0 none;
}

.smiley1 .pploginbutton {
	text-align: center;
}

.smiley1 .pplogbutt {
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
}

.smiley1 .ppsocialmedia {
    background: rgba(0, 0, 0, .2);
    margin-bottom: 30px;
    padding: 30px;
    text-align: center;
    display: none;
}

.ppsocialmedia img {
    max-width: 220px;
    width: 100%;
    padding-top: 10px;
     margin: auto;
}
CSS;

    }
}