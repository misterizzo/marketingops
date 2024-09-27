<?php

namespace ProfilePress\Libsodium\PremiumThemes\Shortcode\Registration;

use ProfilePress\Core\Themes\Shortcode\RegistrationThemeInterface;

class Bash implements RegistrationThemeInterface
{
    public function get_name()
    {
        return 'Bash';
    }

    public function get_structure()
    {
        return <<<CODE
<div class="bash">
    <div id="bash-signup">
        <div class="bash-heading">Sign up with</div>

        <ul class="bash-socials">
            <li><a href="[facebook-login-url]" class="bash-facebook"></a></li>
            <li><a href="[twitter-login-url]" class="bash-twitter"></a></li>
		    <li><a href="[google-login-url]" class="bash-google"></a></li>
        </ul>

        Or via email
        <div class="bash-inputContainer">
            <label class="bash-label" for="bash-username">Username</label>
            [reg-username class="bash-inputField" id="bash-username" placeholder=""]
        </div>
        <div class="bash-inputContainer">
            <label class="bash-label" for="bash-email">Email</label>
            [reg-email class="bash-inputField" id="bash-email" placeholder=""]
        </div>
        <div class="bash-inputContainer">
            <label class="bash-label" for="bash-password">Password</label>
            [reg-password class="bash-inputField" id="bash-password" placeholder=""]
        </div>
        <div class="bash-inputContainer">
            <label class="bash-label" for="bash-fname">First Name</label>
            [reg-first-name class="bash-inputField" id="bash-fname" placeholder=""]
        </div>
        <div class="bash-inputContainer">
            <label class="bash-label" for="bash-lname">Last Name</label>
            [reg-last-name class="bash-inputField" id="bash-lname" placeholder=""]
        </div>

        <div class="bash-message">By signing up, you agree to our <a href="#">Terms of Service</a>, and
            <a href="#">Privacy Policy</a>.
        </div>

        <div style="overflow:hidden;">
            [reg-submit class="bash-submit" value="Sign Up"]
            <a class="bash-existing" href="[link-login raw]">Have an account?</a>
        </div>
    </div>
</div>
CODE;

    }

    public function get_css()
    {
        $asset_url = PPRESS_ASSETS_URL . '/images/bash';

        return <<<CSS
	/* css class for form generated errors */
	.profilepress-reg-status {
		border-radius: 5px;
		font-size: 16px;
		line-height: 1.471;
		padding: 10px;
		background-color: #2F9FFF;
		color: #ffffff;
		font-weight: normal;
		display: block;
		text-align: center;
		vertical-align: middle;
		margin: 5px auto;
        max-width: 400px;
        width: 100%;
	}

	.bash {
		margin-left: auto;
		margin-right: auto;
		font-family: helvetica, arial, sans-serif;
		overflow: hidden;
		position: relative;
		border: 1px solid #ddd;
		background: #fff;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
		color: #7C919E;
		max-width: 400px;
        width: 100%;
		padding: 40px;
		text-align: center;
		font-size: 14px;
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
		line-height: 24px;
		-webkit-font-smoothing: antialiased;
		-moz-font-smoothing: antialiased;
		font-smoothing: antialiased;
	}

	/* Links */
	.bash a {
		color: #2F9FFF;
		text-decoration: none;
	}

	.bash a:hover {
		text-decoration: underline;
	}

	/* Heading */

	.bash .bash-heading {
		font-size: 20px;
		line-height: 30px;
		font-weight: bold;
		color: #556673;
		text-align: center;
		display: block;
		margin-bottom: 10px;
	}

	/* Social buttons */

	.bash .bash-socials {
		list-style: none;
		clear: both;
		text-align: center;
		margin: 0;
		margin-bottom: 30px;
	}

	.bash .bash-socials li {
		display: inline;
	}

	.bash .bash-socials a {
		width: 64px;
		height: 64px;
		background-size: cover;
		background-position: center;
		display: inline-block;
		margin-left: 6px;
		margin-right: 6px;
		-webkit-transition: 0.2s;
		-moz-transition: 0.2s;
		transition: 0.2s;
	}

	.bash .bash-facebook {
		background-image: url($asset_url/bash-facebook.png);
	}

	.bash .bash-twitter {
		background-image: url($asset_url/x.svg);
	}

	.bash .bash-google {
		background-image: url($asset_url/../social-login/google.svg);
	}

	.bash .bash-socials a:hover {
		-webkit-transform: scale(1.05);
		transform: scale(1.05);
		-moz-transform: scale(1.05);
		-ms-transform: scale(1.05);
	}

	.bash a.bash-lost-password {
		cursor: pointer;
	}

	/* Form */
	.bash .bash-inputContainer {
		overflow: hidden;
		position: relative;
		display: block;
		margin-top: 20px;
	}

	.bash .bash-label {
		position: absolute;
		top: 8px;
		left: 10px;
		font-size: 13px;
		line-height: 23px;
		font-weight: bold;
	}

	.bash .bash-inputField {
		border: 1px solid #C3CAD4;
		outline: none;
		-webkit-appearance: none;
		-moz-appearance: none;
		font-size: 14px;
		line-height: 24px;
		font-family: helvetica, arial, sans-serif;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
		padding: 6px;
		padding-left: 85px;
		width: 100%;
		margin: 0;
		-webkit-font-smoothing: antialiased;
		-moz-font-smoothing: antialiased;
		font-smoothing: antialiased;
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
		-webkit-transition: 0.4s;
		-moz-transition: 0.4s;
		transition: 0.4s;
	}

	.bash .bash-inputField:focus {
		border-color: #2F9FFF;
	}

	/* Terms message */

	.bash .bash-message {
		font-weight: bold;
		color: #556673;
		font-size: 12px;
		line-height: 22px;
		margin-top: 20px;
		margin-bottom: 20px;
		display: block;
	}

	/* Buttons */

	.bash .bash-submit,
	.bash a.bash-existing, .bash a.bash-non-existing {
		cursor: pointer;
		border: 0;
		outline: none;
		-webkit-appearance: none;
		-moz-appearance: none;
		padding: 8px 0px;
		display: block;
		width: 48%;
		float: left;
		background: #2F9FFF;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
		color: #fff;
		font-size: 14px;
		text-shadow: none;
		line-height: 24px;
		font-family: helvetica, arial, sans-serif;
		font-weight: bold;
		border: 1px solid #2F9FFF;
		-webkit-font-smoothing: antialiased;
		-moz-font-smoothing: antialiased;
		font-smoothing: antialiased;
		-webkit-transition: 0.2s;
		-moz-transition: 0.2s;
		transition: 0.2s;
	}

	.bash .bash-submit:hover {
		background: #234970;
		border-color: #234970;
	}

	.bash a.bash-existing, .bash a.bash-non-existing {
		float: right;
		background: #fff;
		color: #2F9FFF;
		font-weight: normal;
		opacity: 0.5;
		-webkit-transition: 0.2s;
		-moz-transition: 0.2s;
		transition: 0.2s;
	}

	.bash a.bash-existing:hover, .bash a.bash-non-existing:hover {
		opacity: 1;
		text-decoration: none;
	}
CSS;

    }

    public function success_message()
    {
        return '<div class="profilepress-reg-status">Registration successful</div>';
    }
}