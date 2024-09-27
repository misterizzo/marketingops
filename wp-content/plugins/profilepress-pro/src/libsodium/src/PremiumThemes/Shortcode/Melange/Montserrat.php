<?php

namespace ProfilePress\Libsodium\PremiumThemes\Shortcode\Melange;

use ProfilePress\Core\Themes\Shortcode\MelangeThemeInterface;

class Montserrat implements MelangeThemeInterface
{
    public function get_name()
    {
        return 'Montserrat';
    }

    public function get_structure()
    {
        return <<<CODE
<div class="montseLoginContainer">
	<div class="montseLoginCol1">
		<div class="montseLoginCol1Wrap">
			<h1 id="montse-title">Sign up to continue</h1>

			<div id="montse-checkbox" style="overflow:hidden;margin-bottom:20px;">
				<div style="float:left">
					<input type="radio" name="montse-status" class="loginInputRadio" id="montse-new-user" checked/>
					<label for="montse-new-user">New user</label>
				</div>
				<div style="float:left;margin-left:40px">
					<input type="radio" name="montse-status" class="loginInputRadio" id="montse-existing-user"/>
					<label for="montse-existing-user">Existing user</label>
				</div>
			</div>

			[pp-registration-form]
			<div id="montse-signup-form">
				[reg-username class="montseLoginInputField" placeholder="Username"]
				[reg-email class="montseLoginInputField" placeholder="Email Address"]
				[reg-password class="montseLoginInputField" placeholder="Password"]
				[reg-first-name class="montseLoginInputField" placeholder="First Name"]
				[reg-last-name class="montseLoginInputField" placeholder="Last Name"]
				[reg-submit class="montseLoginInputSubmit" value="Sign Up"]
			</div>
			[/pp-registration-form]

			[pp-login-form]
			<div id="montse-login-form" style="display: none">
				[login-username class="montseLoginInputField" placeholder="Username"]
				[login-password class="montseLoginInputField" placeholder="Password"]

				<div style="overflow:hidden">
					<div style="float:left">
						[login-remember class="loginInputCheckbox" id="rememberMe"]
						<label for="rememberMe">Remember me</label>
					</div>
					<div style="float:right">
						<a id="montse-lostp">Forgot password</a>
					</div>
				</div>
				[login-submit class="montseLoginInputSubmit" value="Log In"]
			</div>
			[/pp-login-form]


			[pp-password-reset-form]
			<div id="montse-reset-form" style="display: none">
				<p class="montseNote">Enter your account username or email address to receive password reset instructions.</p>
				[user-login class="montseLoginInputField" placeholder="Username or Email"]

				<div style="overflow:hidden">
					<div style="float:left">
						<a id="montse-back-login">Back to Sign In</a>
					</div>
				</div>
				[reset-submit class="montseLoginInputSubmit" value="Get New Password"]
			</div>
		</div>
	</div>
	[/pp-password-reset-form]
	<div class="montseSeparator"><span>Or</span></div>
	<div class="montseLoginCol2">
		<div class="montseLoginCol2Wrap">
			<div style="margin-bottom:40px">Click below to sign in using your social account:</div>
            [pp-social-button type=twitter]
            [pp-social-button type=facebook]
            [pp-social-button type=google]
            [pp-social-button type=linkedin]
            [pp-social-button type=github]
            [pp-social-button type=vk]
		</div>
	</div>
</div>

<script type="text/javascript">
        (function ($) {
            $(document).ready(function () {
                function montse_existing_user() {
                    $('div#montse-signup-form').hide();
                    $('div#montse-reset-form').hide();
                    $('h1#montse-title').text('Sign in to continue');
                    $('div#montse-login-form').show();
                }

                function montse_new_user() {
                    $('div#montse-login-form').hide();
                    $('div#montse-reset-form').hide();
                    $('h1#montse-title').text('Sign up to continue');
                    $('div#montse-signup-form').show();
                }

                function montse_lostp() {
                    $('div#montse-login-form').hide();
                    $('div#montse-signup-form').hide();
                    $('div#montse-checkbox').hide();
                    $('h1#montse-title').text('Forgot password?');
                    $('div#montse-reset-form').show();
                }

                function montse_back_login() {
                    $('input#montse-existing-user').prop('checked', true);
                    $('div#montse-signup-form').hide();
                    $('div#montse-reset-form').hide();
                    $('h1#montse-title').text('Sign in to continue');
                    $('div#montse-checkbox').show();
                    $('div#montse-login-form').show();
                }

                /** Hash setup **/
                var hash = window.location.hash;
                if (hash == "#montse-existing-user") {
                    $('input#montse-existing-user').prop('checked', true);
                    montse_existing_user();
                }
                if (hash == "#montse-new-user") {
                    $('input#montse-new-user').prop('checked', true);
                    montse_new_user();
                }
                if (hash == "#montse-lostp") {
                    montse_lostp();
                }
                if (hash == "#montse-back-login") {
                    montse_back_login();
                }
                /**  Hash setup ends **/

                $('input[type=radio]').on('change', function () {
                    if ($('input#montse-existing-user').is(':checked')) {
                        montse_existing_user();
                    }
                    else if ($('input#montse-new-user').is(':checked')) {
                        montse_new_user();
                    }
                });

                $('a#montse-lostp').on('click', function () {
                    montse_lostp();
                });

                $('a#montse-back-login').on('click', function () {
                    $('input#montse-existing-user').prop('checked', true);
                    montse_back_login();
                });
            });
        })(jQuery);
    </script>
CODE;

    }

    public function get_css()
    {
        return <<<CSS
	/* css class for login form generated errors */
	.profilepress-login-status {
		border-radius: 5px;
		font-size: 14px;
		line-height: 1.471;
		padding: 10px;
		background-color: #e74c3c;
		color: #ffffff;
		font-weight: normal;
		display: block;
		text-align: center;
		vertical-align: middle;
		margin: 5px 0;
	}

	.profilepress-login-status a {
		text-decoration: underline;
        color: #fff;
	}

	/* css class for registration form generated errors */
	.profilepress-reg-status {
		border-radius: 5px;
		font-size: 14px;
		line-height: 1.471;
		padding: 10px;
		background-color: #e74c3c;
		color: #ffffff;
		font-weight: normal;
		display: block;
		text-align: center;
		vertical-align: middle;
		margin: 5px 0;
	}

	/* css class for password reset form generated errors */
	.profilepress-reset-status {
		border-radius: 5px;
		font-size: 14px;
		line-height: 1.471;
		padding: 10px;
		background-color: #e74c3c;
		color: #ffffff;
		font-weight: normal;
		display: block;
		text-align: center;
		vertical-align: middle;
		margin: 5px 0;
	}

    .profilepress-reset-status a {
        color: #fff;
        text-decoration: underline;
    }

	.montseSuccess {
		border-radius: 5px;
		font-size: 14px;
		line-height: 1.471;
		padding: 10px;
		background-color: #2ecc71;
		color: #ffffff;
		font-weight: normal;
		display: block;
		text-align: center;
		vertical-align: middle;
		margin: 5px 0;
	}

	.montseLoginContainer p.montseNote {
		line-height: 1.428571429;
	}

	.montseLoginContainer {
		max-width: 960px;
		margin-left: auto;
		margin-right: auto;
		text-align: left;
		overflow: hidden;
		padding: 50px;
        font-size: 16px;
		color: #7f8c8d;
		position: relative;
		font-family: 'Roboto', helvetica, arial, sans-serif;
	}

	.montseLoginContainer .montseSeparator {
		position: absolute;
		top: 50px;
		bottom: 50px;
		left: 50%;
		border-left: 2px solid #dee1e3;
		text-transform: uppercase;
		font-size: 22px;
		color: #2c3e50;
	}

	.montseLoginContainer .montseSeparator span {
		display: block;
		width: 40px;
		text-align: center;
		top: 50%;
		margin-top: -24px;
		font-weight: 500;
		left: -22px;
		padding: 10px 0px;
		position: absolute;
		background: #fff;
	}

	.montseLoginContainer a {
		color: #00bc9c;
		text-decoration: none;
		cursor: pointer;
	}

	#montse-signup-form input[type="submit"] {
		margin-top: 10px;
	}

	.montseLoginContainer h1 {
		font-weight: 300;
		font-size: 28px;
		line-height: 28px;
		color: #2c3e50;
	}

	.montseLoginContainer .montseLoginCol1 {
		float: left;
		width: 50%;
	}

	.montseLoginContainer .montseLoginCol2 {
		float: right;
		width: 50%;
	}

	.montseLoginContainer .montseLoginCol1Wrap {
		padding-right: 120px;
	}

	.montseLoginContainer .montseLoginCol2Wrap {
		padding-left: 120px;
	}

	.montseLoginContainer input.montseLoginInputField {
		-webkit-box-sizing: border-box;
		font-family: helvetica, arial, sans-serif;
		color: #34495e;
		font-size: 18px;
		padding: 7px 14px;
		border: 2px solid #ecf0f1;
		border-radius: 5px;
		outline: 0;
		-webkit-transition: all .30s ease-in-out;
		-moz-transition: all .30s ease-in-out;
		-ms-transition: all .30s ease-in-out;
		-o-transition: all .30s ease-in-out;
		width: 100%;
		height: auto;
		line-height: normal;
		margin: 0;
		margin-bottom: 20px;
	}

	.montseLoginContainer input.montseLoginInputField:focus {
		border-color: #00bc9c;
	}

	.montseLoginContainer input.montseLoginInputSubmit {
		background: #00bc9c;
		display: block;
		width: 100%;
		-webkit-box-sizing: border-box;
		padding: 14px 15px;
		font-size: 14px;
		font-weight: normal;
		text-align: center;
		border: 0;
		color: #fff;
		cursor: pointer;
		-webkit-border-radius: 4px;
		-moz-border-radius: 4px;
		border-radius: 4px;
		text-transform: uppercase;
		margin-bottom: 25px;
        box-shadow: none;
		font-family: helvetica, arial, sans-serif;
        box-shadow: none;
    	text-shadow: none;
	}

	.montseLoginContainer input.montseLoginInputSubmit {
		margin-top: 30px;
	}


.montseLoginContainer a.pp-button-social-login {
    max-width: 100% !important;
    width: 100%;
}

.montseLoginContainer a.pp-button-social-login {
    display: block;
    height: 3em;
    line-height: 3em;
    text-decoration: none;
    margin-bottom: 10px;
}

.montseLoginContainer a.pp-button-social-login .ppsc {
    width: 3em;
    height: 3em;
}

.montseLoginContainer a.pp-button-social-login span.ppsc-text {
    margin-left: 50px;
}

	@media only screen and (max-width: 900px) {

		.montseLoginContainer .montseLoginCol1,
		.montseLoginContainer .montseLoginCol2 {
			float: none;
			width: 100%;
		}

		.montseLoginContainer .montseLoginCol2 {
			margin-top: 75px;
			border-top: 2px solid #dee1e3;
			padding-top: 75px;
		}

		.montseLoginContainer .montseLoginCol1Wrap,
		.montseLoginContainer .montseLoginCol2Wrap {
			padding: 0;
		}

		.montseLoginContainer .montseSeparator {
			display: none;
		}
	}
CSS;

    }

    public function registration_success_message()
    {
        return '<div class="montseSuccess">Registration Successful.</div>';
    }

    public function password_reset_success_message()
    {
        return '<div class="montseSuccess">Check your e-mail for further instruction.</div>';
    }

    public function edit_profile_success_message()
    {
        return '<div class="montseSuccess">Profile successfully updated.</div>';
    }
}