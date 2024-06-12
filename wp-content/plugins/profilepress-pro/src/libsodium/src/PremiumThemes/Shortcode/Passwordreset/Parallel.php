<?php

namespace ProfilePress\Libsodium\PremiumThemes\Shortcode\Passwordreset;

use ProfilePress\Core\ShortcodeParser\PasswordResetTag;
use ProfilePress\Core\Themes\Shortcode\PasswordResetThemeInterface;

class Parallel implements PasswordResetThemeInterface
{
    public function get_name()
    {
        return 'Parallel';
    }

    public function get_structure()
    {
        return <<<CODE
<div class="pp-parallel-container">
	<div class="pp-parallel">
		[user-login class="pp-parallel-input" placeholder="Username or Email"]
		[reset-submit class="pp-parallel-submit" value="Reset Password"]
		<div class="pp-parallel-or">Or</div>
		<ul class="pp-parallel-social">
			<li><a href="[facebook-login-url]" class="pp-parallel-socialIcon facebook"></a></li>
			<li><a href="[twitter-login-url]" class="pp-parallel-socialIcon twitter"></a></li>
			<div style="clear:both;"></div>
		</ul>
		<div class="pp-parallel-dots"></div>
		<ul class="pp-parallel-social-extras">
			<li><a href="[google-login-url]" class="pp-parallel-socialIcon google"></a></li>
			<li><a href="[linkedin-login-url]" class="pp-parallel-socialIcon linkedin"></a></li>
			<li><a href="[github-login-url]" class="pp-parallel-socialIcon github"></a></li>
			<div style="clear:both;"></div>
		</ul>
		<div style="clear:both;"></div>
	</div>
</div>
CODE;
    }

    public function password_reset_handler()
    {
        return PasswordResetTag::get_default_handler_form();
    }

    public function success_message()
    {
        return '<div class="pp-parallelSuccess">Check your email for further instruction</div>';
    }

    public function get_css()
    {
        $asset_url = PPRESS_ASSETS_URL . '/images/parallel';

        return <<<CSS
	/* css class for the form generated errors */
	.profilepress-reset-status {
		border-radius: 3px;
		font-size: 15px;
		padding: 5px;
		background-color: #e74c3c;
		color: #ffffff;
		font-weight: normal;
		display: block;
		text-align: center;
		vertical-align: middle;
		margin: 5px 0;
		max-width: 794px;
	}
	
	.profilepress-reset-status a {
        color: #fff;
        text-decoration: underline;
    }

	.pp-parallelSuccess {
		border-radius: 3px;
		font-size: 15px;
		padding: 5px;
		background-color: #2ecc71;
		color: #ffffff;
		font-weight: normal;
		display: block;
		text-align: center;
		vertical-align: middle;
		margin: 5px 0;
		max-width: 794px;
	}

	.pp-parallel-container, .pp-parallel-container * {
		margin: 0 auto;
		padding: 0;
	}

	.pp-parallel-container {
		overflow: hidden;
	}

	.pp-parallel-container .pp-parallel {
		padding: 10px;
		background: #7E8C8D;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
		width: auto;
		float: left;
		position: relative;
		max-width: 794px;
	}

	.pp-parallel-container input.pp-parallel-input {
		outline: none;
		border: 1px solid #AAAAAA;
		padding: 8px 12px;
		color: #777;
		font-size: 14px;
		line-height: 24px;
		font-family: 'helvetica', arial, sans-serif;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
		float: left;
		margin: 0;
		margin-right: 10px;
		max-width: 150px;
	}

	.pp-parallel-container input.pp-parallel-submit {
		outline: none;
		box-shadow: none;
		border: 1px solid #F6E556;
		padding: 9px 25px;
		padding-bottom: 8px;
		color: #3B493F;
		font-size: 12px;
		line-height: 22px;
		text-transform: uppercase;
		font-weight: bold;
		font-family: 'helvetica', arial, sans-serif;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
		cursor: pointer;
		background: #F6E556;
		float: left;
		margin-right: 10px;
		width: auto;
	}

	.pp-parallel-container .pp-parallel-or {
		padding: 10px;
		padding-right: 20px;
		border-right: 1px solid #aaa;
		color: #3B493F;
		font-size: 12px;
		line-height: 22px;
		text-transform: uppercase;
		font-weight: bold;
		font-family: 'helvetica', arial, sans-serif;
		display: block;
		float: left;
	}

	.pp-parallel-container .pp-parallel-social {
		margin-top: 5px;
		margin-left: 20px;
		float: left;
		list-style: none;
	}

	.pp-parallel-container .pp-parallel-social li {
		float: left;
		margin-right: 10px;
	}

	.pp-parallel-container .pp-parallel-social li:last-child {
		margin-right: 0;
	}

	.pp-parallel-container .pp-parallel-socialIcon {
		display: block;
		width: 32px;
		height: 32px;
		cursor: pointer;
		background-size: 32px 64px;
		background-position: top left;
	}

	.pp-parallel-container .pp-parallel-socialIcon:hover {
		background-position: bottom left;
	}

	.pp-parallel-container .facebook { background-image: url($asset_url/facebook.png); }
	.pp-parallel-container .twitter { background-image: url($asset_url/twitter.png); }
	.pp-parallel-container .google { background-image: url($asset_url/google.png); }
	.pp-parallel-container .linkedin { background-image: url($asset_url/linkedin.png); }
	.pp-parallel-container .github { background-image: url($asset_url/github.png); }

	.pp-parallel-container .pp-parallel-dots {
		display: block;
		float: left;
		margin-left: 10px;
		margin-right: 10px;
		width: 6px;
		height: 30px;
		background-image: url($asset_url/dots.png);
		background-size: 6px 30px;
		margin-top: 6px;
	}

	.pp-parallel-container .pp-parallel-social-extras {
		position: absolute;
		list-style: none;
		top: 0;
		bottom: 0;
		left: 100%;
		width: 150px;
		margin-left: -40px;
		padding: 15px;
		padding-right: 5px;
		overflow: hidden;
		background: #7E8C8D;
		-webkit-border-radius: 3px;
		-moz-border-radius: 6;
		border-radius: 3px;
		opacity: 0;
		-webkit-transition: 0.2s;
		-moz-transition: 0.2s;
		transition: 0.2s;
	}

	.pp-parallel-container .pp-parallel-social-extras li {
		float: left;
		margin: 0;
		margin-right: 10px;
	}

	.pp-parallel-container .pp-parallel-social-extras li:last-child {
		margin: 0;
		margin-right: 0;
	}

	.pp-parallel-container .pp-parallel:hover .pp-parallel-social-extras {
		opacity: 1;
		margin: 0;
		margin-left: 10px;
	}

	/* Mobile */

	@media only screen and (max-width: 800px) {
		.pp-parallel-container .pp-parallel-input,
		.pp-parallel-container .pp-parallel-submit {
			float: none;
			width: 100%;
			margin-bottom: 10px;
			-webkit-box-sizing: border-box;
			-moz-box-sizing: border-box;
			box-sizing: border-box;
		}

		.pp-parallel-container .pp-parallel-or {
			border: 0;
			padding: 0;
			width: 100%;
			text-align: center;
			float: none;
			margin-top: 10px;
			margin-bottom: 18px;
		}

		.pp-parallel-container .pp-parallel-dots {
			display: none;
		}

		.pp-parallel-container .pp-parallel-social,
		.pp-parallel-container .pp-parallel-social-extras {
			display: inline-block;
			float: none;
			position: relative;
			margin: 0;
			padding: 0;
			opacity: 1;
			left: 0;
			margin-bottom: 10px;
		}

		.pp-parallel-container .pp-parallel:hover .pp-parallel-social-extras {
			opacity: 1;
			margin-left: 0;
		}

		.pp-parallel-container .pp-parallel {
			text-align: center;
		}
	}
CSS;

    }
}