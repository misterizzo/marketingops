<?php

namespace ProfilePress\Libsodium\PremiumThemes\Shortcode\Passwordreset;

use ProfilePress\Core\ShortcodeParser\PasswordResetTag;
use ProfilePress\Core\Themes\Shortcode\PasswordResetThemeInterface;

class Jakhu implements PasswordResetThemeInterface
{
    public function get_name()
    {
        return 'Jakhu';
    }

    public function success_message()
    {
        return '<div class="profilepress-reset-status">Check your email for further instruction</div>';
    }

    public function get_structure()
    {
        return <<<CODE
<div class="jakhu-login-form">

	<div class="jakhu-header">
		<h1>Forgot Password?</h1>
		<span>Please enter your username or email address. You will receive a link to create a new password via email.</span>
	</div>

	<div class="jakhu-content">

		[user-login id="login" placeholder="Username or E-mail" class="jakhu-input jakhu-username"]
	</div>

	<div class="jakhu-footer">
		[reset-submit value="Reset Password" class="jakhu-button"]

		<br/><br/>

		<div style="float: right; text-decoration: underline;">
			[link-login class="jakhu-login" label="Back to Login?"]
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
        return <<<CSS
@import url(https://fonts.googleapis.com/css?family=Bree+Serif:300,400,600,700&display=swap);

/* css class for the form generated errors */

.profilepress-reset-status {
    max-width: 300px;
    width: 100%;
	position: static;
	margin: 10px auto;
	padding: 5px;
	background: #f3f3f3;
	border: 1px solid #fff;
	border-radius: 5px;
	color: #6d7680;
	box-shadow: 0 1px 3px rgba(0,0,0,0.5);
	-moz-box-shadow: 0 1px 3px rgba(0,0,0,0.5);
	-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.5);
}

.profilepress-reset-status a {
    color: #6d7680;
    text-decoration: underline;
}


.jakhu-login-form .jakhu-header span::selection {
	color: #fff;
	background: #f676b2; /* Safari */
}

.jakhu-login-form .jakhu-header span::-moz-selection {
	color: #fff;
	background: #f676b2; /* Firefox */
}

.jakhu-login-form {
	max-width: 300px;
	width: 100%;
	margin: auto;
	position: static;
	background: #f3f3f3;
	border: 1px solid #fff;
	font-family: 'Bree Serif', serif;
	border-radius: 5px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.5);
	-moz-box-shadow: 0 1px 3px rgba(0,0,0,0.5);
	-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.5);
}

.jakhu-login-form .jakhu-header {
	padding: 40px 30px 30px 30px;
}

.jakhu-login-form .jakhu-header h1 {
	font-family: 'Bree Serif', serif;
	font-weight: 300;
	font-size: 28px;
	line-height:34px;
	color: #414848;
	text-shadow: 1px 1px 0 rgba(256,256,256,1.0);
	margin-bottom: 10px;
}

.jakhu-login-form .jakhu-header span {
	font-size: 13px;
	line-height: 16px;
	color: #678889;
	text-shadow: 1px 1px 0 rgba(256,256,256,1.0);
	font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
	font-weight:300;
}

.jakhu-login-form .jakhu-content {
	padding: 0 30px 25px 30px;
}

/* Input field */
.jakhu-login-form .jakhu-content .jakhu-input {
	width: 240px;
	padding: 15px 25px;
	font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
	font-weight: 400;
	font-size: 14px;
	color: #9d9e9e;
	text-shadow: 1px 1px 0 rgba(256,256,256,1.0);

	background: #fff;
	border: 1px solid #fff;
	border-radius: 5px;

	box-shadow: inset 0 1px 3px rgba(0,0,0,0.50);
	-moz-box-shadow: inset 0 1px 3px rgba(0,0,0,0.50);
	-webkit-box-shadow: inset 0 1px 3px rgba(0,0,0,0.50);
}

/* Second and Third input fourth fifth field */
.jakhu-login-form .jakhu-content .jakhu-password{
	margin-top: 25px;
}

.jakhu-login-form .jakhu-content .jakhu-input:hover {
	background: #dfe9ec;
	color: #414848;
}

.jakhu-login-form .jakhu-content .jakhu-input:focus {
	background: #dfe9ec;
	color: #414848;

	box-shadow: inset 0 1px 2px rgba(0,0,0,0.25);
	-moz-box-shadow: inset 0 1px 2px rgba(0,0,0,0.25);
	-webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,0.25);
}

/* Animation */
.jakhu-input, .jakhu-user-icon, .jakhu-email-icon, .jakhu-pass-icon, .jakhu-button, .jakhu-login {
	transition: all 0.5s;
	-moz-transition: all 0.5s;
	-webkit-transition: all 0.5s;
	-o-transition: all 0.5s;
	-ms-transition: all 0.5s;
}

.jakhu-login-form .jakhu-footer {
	padding: 25px 30px 40px 30px;
	overflow: auto;
	background: #d4dedf;
	border-top: 1px solid #fff;
	box-shadow: inset 0 1px 0 rgba(0,0,0,0.15);
	-moz-box-shadow: inset 0 1px 0 rgba(0,0,0,0.15);
	-webkit-box-shadow: inset 0 1px 0 rgba(0,0,0,0.15);
}

/* Login button */
.jakhu-login-form .jakhu-footer .jakhu-button {
	float:right;
	padding: 11px 25px;
	font-family: 'Bree Serif', serif;
	font-weight: 300;
	font-size: 18px;
	color: #fff;
	text-shadow: 0 1px 0 rgba(0,0,0,0.25);
	background: #56c2e1;
	border: 1px solid #46b3d3;
	border-radius: 5px;
	cursor: pointer;
	box-shadow: inset 0 0 2px rgba(256,256,256,0.75);
	-moz-box-shadow: inset 0 0 2px rgba(256,256,256,0.75);
	-webkit-box-shadow: inset 0 0 2px rgba(256,256,256,0.75);
}

.jakhu-login-form .jakhu-footer .jakhu-button:hover {
	background: #3f9db8;
	border: 1px solid rgba(256,256,256,0.75);
	box-shadow: inset 0 1px 3px rgba(0,0,0,0.5);
	-moz-box-shadow: inset 0 1px 3px rgba(0,0,0,0.5);
	-webkit-box-shadow: inset 0 1px 3px rgba(0,0,0,0.5);
}

.jakhu-login-form .jakhu-footer .jakhu-button:focus {
	bottom: -1px;
	background: #56c2e1;
	box-shadow: inset 0 1px 6px rgba(256,256,256,0.75);
	-moz-box-shadow: inset 0 1px 6px rgba(256,256,256,0.75);
	-webkit-box-shadow: inset 0 1px 6px rgba(256,256,256,0.75);
}

/* Registration link */
.jakhu-login-form .jakhu-footer .jakhu-login {
	display: block;
	float: right;
	padding: 10px;
	margin-right: 20px;
	text-decoration: none;
	background: none;
	border: none;
	cursor: pointer;
	font-family: 'Bree Serif', serif;
	font-weight: 300;
	font-size: 20px;
	color: #414848;
	text-shadow: 0 1px 0 rgba(256,256,256,0.5);
}

/* Back to login link */
.jakhu-login-form .jakhu-footer .jakhu-back-login {
	display: block;
	text-align:center
	padding: 10px;
	text-decoration: none;
	background: none;
	border: none;
	cursor: pointer;
	font-family: 'Bree Serif', serif;
	font-weight: 300;
	font-size: 18px;
	color: #414848;
	margin-top: 20px;
	text-shadow: 0 1px 0 rgba(256,256,256,0.5);
}


.jakhu-login a {
 text-decoration: none;
}

.jakhu-login-form .jakhu-footer .jakhu-login:hover {
	color: #3f9db8;
}

.jakhu-login-form .jakhu-footer .jakhu-login:focus {
	position: relative;
	bottom: -1px;
}

.jakhu-content input {
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

CSS;

    }
}