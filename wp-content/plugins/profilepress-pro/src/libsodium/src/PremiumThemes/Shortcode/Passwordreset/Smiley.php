<?php

namespace ProfilePress\Libsodium\PremiumThemes\Shortcode\Passwordreset;

use ProfilePress\Core\ShortcodeParser\PasswordResetTag;
use ProfilePress\Core\Themes\Shortcode\PasswordResetThemeInterface;

class Smiley implements PasswordResetThemeInterface
{
    public function get_name()
    {
        return 'Smiley';
    }

    public function success_message()
    {
        return '<div class="pp-reset-success">Check your email for further instruction</div>';
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
            <span class="ppname">Reset Password</span>
        </div>
        <div class="ppprofdata">
            [user-login placeholder="Username"]
            <br/><br/>
        </div>
        <div class="pploginbutton">
            [reset-submit class="pplogbutt" value="Reset Password"]
            <br/>
            [link-login class="ppsoclog smi-links" label="Back to Login"]
            <br/><br/>
        </div>

    </div>
</div>
CODE;

    }

    public function password_reset_handler()
    {
        return PasswordResetTag::get_default_handler_form();
    }

    public function get_css()
    {
        $asset_url = PPRESS_ASSETS_URL . '/images/smiley';

        return <<<CSS
@import url('https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700&display=swap');
/* css class for the form generated errors */
.profilepress-reset-status {
  border-radius: 5px;
  font-size: 16px;
  line-height: 1.471;
  padding: 10px;
  background-color: #34495e;
  color: #ffffff;
  font-weight: normal;
  display: block;
  text-align: center;
  vertical-align: middle;
  margin: 5px auto;
  max-width: 360px;
}

.profilepress-reset-status a {
    color: #fff;
    text-decoration: underline;
}

.pp-reset-success {
  border-radius: 5px;
  font-size: 16px;
  line-height: 1.471;
  padding: 10px;
  background-color: #2ecc71;
  color: #ffffff;
  font-weight: normal;
  display: block;
  text-align: center;
  vertical-align: middle;
  margin: 5px auto;
  max-width: 360px;
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

.smiley1 .smi-links {
  font-size: 14px !important;
}

.smiley1 .ppboxb {
    font-family: 'Montserrat', sans-serif;
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
    font-family: 'Montserrat', sans-serif;
    font-size: 14px;
    padding: 11px;
    margin: 0;
    max-width: 230px;
    width: 100%;
    border-bottom: 1px dashed rgba(0, 0, 0, .05);
    margin-bottom:0;
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

}

.smiley1 .pploginbutton {
	text-align: center;
}

.smiley1 .pplogbutt {
    background: #f1f1f1;
    border: 1px solid rgba(0, 0, 0, .15);
    font-family: 'Montserrat', sans-serif;
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

.smiley1 .ppsocialmedia .ppsochead {
	font-weight: 500;
	font-size: 16px;
}

.ppsocialmedia img {
    max-width: 220px;
    width: 100%;
    padding-top: 10px;
}

.pp-gender {
  border: 0;
  background: #fff;
  max-width: 230px;
  width: 100%;
  font-size: 14px;
  font-family: 'Montserrat', sans-serif;
  color: #7c7c7c;
  font-weight: 500;
  border-radius: 0;border-bottom-right-radius:4px;
  padding: 12px;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  border-bottom-left-radius: 4px;
}

.pp-gender:focus {
	outline: none;
}
CSS;

    }
}