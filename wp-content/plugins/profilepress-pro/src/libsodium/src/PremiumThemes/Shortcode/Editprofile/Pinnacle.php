<?php

namespace ProfilePress\Libsodium\PremiumThemes\Shortcode\Editprofile;

use ProfilePress\Core\Themes\Shortcode\EditProfileThemeInterface;

class Pinnacle implements EditProfileThemeInterface
{
    public function get_name()
    {
        return 'Pinnacle';
    }

    public function get_structure()
    {
        return <<<CODE
<div class="pp_pinnacle loginContainer">
    <div class="pp_pinnacle loginContent">
    	<div class="pp_pinnacle loginTitle">Edit Your Profile</div>
	</div>
    <hr>
	<div class="pp_pinnacle loginContent">
		[edit-profile-username class="pp_pinnacle loginInputField" title="Username" placeholder="Username"]
		[edit-profile-email class="pp_pinnacle loginInputField" title="Email Address" placeholder="Email Address"]
		[edit-profile-password class="pp_pinnacle loginInputField" title="Password" placeholder="Password"]
		[edit-profile-first-name class="pp_pinnacle loginInputField" title="First Name" placeholder="First Name"]
		[edit-profile-last-name class="pp_pinnacle loginInputField" title="Last Name" placeholder="Last Name"]
	</div>
	<hr/>
	<div class="pp_pinnacle loginContent" style="background:#f0f0f0;">
		<div style="float:right;">
			[edit-profile-submit class="pp_pinnacle loginInputSubmit" value="Save Changes"]
		</div>
	</div>
</div>
CODE;
    }

    public function success_message()
    {
        return '<div class="pinnacle-success">Changes saved</div>';
    }

    public function get_css()
    {
        return <<<CSS
/* css class for the form generated errors */
.profilepress-edit-profile-status {
    border-radius: 5px;
    max-width: 380px;
    font-size: 16px;
    line-height: 1.471;
    padding: 10px;
    background-color: #e74c3c;
    color: #fff;
    font-weight: normal;
    transition: border 0.25s linear 0s, color 0.25s linear 0s, background-color 0.25s linear 0s;
    display: block;
    text-align: center;
    vertical-align: middle;
    margin: 5px auto;
}

.pinnacle-success {
    border-radius: 5px;
    max-width: 380px;
    font-size: 16px;
    line-height: 1.471;
    padding: 10px;
    background-color: #34495e;
    color: #fff;
    font-weight: normal;
    transition: border 0.25s linear 0s, color 0.25s linear 0s, background-color 0.25s linear 0s;
    display: block;
    text-align: center;
    vertical-align: middle;
    margin: 5px auto;
}

a.pp_pinnacle {
    font-weight: bold;
    color: #777;
    text-decoration: none;
}

a.pp_pinnacle:hover {
    color: #000;
}

.pp_pinnacle.loginContainer {
    max-width: 380px;
    margin-left: auto;
    margin-right: auto;
    text-align: left;
    background: #fff;
    font-size: 16px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    box-shadow: 0px 2px 8px 0px #999;
}

.pp_pinnacle.loginContainer hr {
    border: 0;
    margin: 0;
    height: auto;
    border-top: 1px solid #ddd;
}

.pp_pinnacle.loginContent {
    padding: 30px;
    overflow: hidden;
}

.pp_pinnacle.loginTitle {
    font-size: 30px;
    line-height: 33px;
    font-weight: 900;
    color: #525252;
    text-align: center;
}

input.pp_pinnacle.loginInputField {
    font-family: helvetica, arial, sans-serif;
    font-size: 16px;
    line-height: 30px;
    outline: none;
    -webkit-border-radius: 3px;
    -moz-border-radius: 3px;
    border-radius: 3px;
    padding: 10px 15px;
    padding-bottom: 8px;
    width: 100%;
    -webkit-box-sizing: border-box;
    background-color: #f2f2f2;
    margin-bottom: 10px;
    -webkit-box-shadow: inset 0px 0px 4px 0px #ccc;
    -moz-box-shadow: inset 0px 0px 4px 0px #ccc;
    box-shadow: inset 0px 0px 4px 0px #ccc;
    border: 1px solid #ccc;
}

input.pp_pinnacle.loginInputField:focus {
    background-color: #fff;
}

.pp_pinnacle input.loginInputField[type="text"]:disabled {
  background: #ccc;
}

input.pp_pinnacle.loginInputSubmit {
    margin-top: 6px;
    background: #5bb75b;
    border: 0;
    outline: none;
    display: block;
    padding: 10px 15px;
    font-size: 15px;
    font-weight: bold;
    color: #fff;
  	text-shadow:none;
    cursor: pointer;
    -webkit-border-radius: 4px;
    -moz-border-radius: 4px;
    border-radius: 4px;
    -webkit-font-smoothing: antialiased;
    -moz-font-smoothing: antialiased;
    font-smoothing: antialiased;
    font-family: helvetica, arial, sans-serif;
}

a.pp-button-social-login {
    max-width: 100% !important;
    width: 100%;
}

a.pp-button-social-login {
    display: block;
    height: 3em;
    line-height: 3em;
    text-decoration: none;
    margin-bottom: 10px;
}

a.pp-button-social-login .ppsc {
    width: 3em;
    height: 3em;
}

a.pp-button-social-login span.ppsc-text {
    margin-left: 50px;
}
CSS;
    }

}