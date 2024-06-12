<?php

namespace ProfilePress\Libsodium\PremiumThemes\Shortcode\Login;


use ProfilePress\Core\Themes\Shortcode\LoginThemeInterface;

class Sukan implements LoginThemeInterface
{
    public function get_name()
    {
        return 'Sukan';
    }

    public function get_structure()
    {
        return <<<CODE
<div class="sukan-login">
	<h1>Let's get started.</h1>

	<p>This will be an amazing experience</p>

	<div class="sukan-input">

		<div class="sukan-blockinput">
			[login-username placeholder="Username"]
		</div>

		<div class="sukan-blockinput">
			[login-password placeholder="Password"]
		</div>
	</div>

	[login-submit value="Login"]

	<br/>

	[link-lost-password label="Forgot Password?"] | [link-registration label="Sign Up"]

</div>
CODE;

    }

    public function get_css()
    {
        return <<<CSS
	/*  css class for the form generated errors */
	.profilepress-login-status {
		background-color: #f1f2f4;
		color: #7b808a;
		border: medium none;
		border-radius: 4px;
		font-size: 15px;
		font-weight: normal;
		line-height: 1.4;
		margin: 2px auto 10px;
		padding: 15px 40px 20px 40px;
		width: 100%;
        max-width: 350px;
	}

	.profilepress-login-status a {
		color: #ea9629 !important;
	}

	.sukan-login a {
		text-decoration: none;
		color: #EC5C93;
	}

	.sukan-ribbon {
		background: rgba(200, 200, 200, .5);
		width: 50px;
		height: 70px;
		margin: 0 auto;
		position: relative;
		top: 19px;
		border: 1px solid rgba(255, 255, 255, .3);
		border-bottom: 0;
		border-radius: 5px 5px 0 0;
		box-shadow: 0 0 3px rgba(0, 0, 0, .7);
	}

	.sukan-login {
		background: #F1F2F4;
		border-bottom: 2px solid #C5C5C8;
		border-radius: 5px;
		text-align: center;
		color: #36383C;
		text-shadow: 0 1px 0 #FFF;
		width: 100%;
        max-width: 350px;
		margin: 0 auto;
		padding: 15px 40px 20px 40px;
		box-shadow: 0 0 3px #000;
	}

	.sukan-login h1 {
		font-size: 1.6em;
		margin-top: 30px;
		margin-bottom: 10px;
	}

	.sukan-login p {
		font-family: 'Helvetica Neue', Sans-serif;
		font-weight: 300;
		color: #7B808A;
		margin-top: 0;
		margin-bottom: 30px;
	}

	.sukan-input {
		text-align: right;
		background: #E5E7E9;
		border-radius: 5px;
		
		box-shadow: inset 0 0 3px #65686E;
		border-bottom: 1px solid #FFF;
	}

	.sukan-blockinput input {
		width: 90%;
		background: transparent;
		border: 0;
		line-height: 3.6em;
		box-sizing: border-box;
		color: #71747A;
		font-family: 'Helvetica Neue', Sans-serif;
		text-shadow: 0 1px 0 #FFF;
	}

	.sukan-blockinput > input:focus {
		outline: none;
	}

	.sukan-blockinput {
		border-bottom: 1px solid #BDBFC2;
		border-top: 1px solid #FFFFFF;
	}

	.sukan-blockinput:first-child {
		border-top: 0;
	}

	.sukan-blockinput:last-child {
		border-bottom: 0;
	}

	::-webkit-input-placeholder {
		color: #71747A;
		font-family: 'Helvetica Neue', Sans-serif;
		text-shadow: 0 1px 0 #FFF;
	}

	.sukan-login input[type="submit"] {
		margin-top: 20px;
		display: block;
		width: 100%;
		line-height: 2em;
		background: rgba(114, 212, 202, 1);
		border-radius: 5px;
		border: 0;
		border-top: 1px solid #B2ECE6;
		box-shadow: 0 0 0 1px #46A294, 0 2px 2px #808389;
		color: #FFFFFF;
		font-size: 1.5em;
		text-shadow: 0 1px 2px #21756A;
	}

	.sukan-login input[type="submit"]:hover {
		background: linear-gradient(to bottom, rgba(107, 198, 186, 1) 0%, rgba(57, 175, 154, 1) 100%);
	}

	.sukan-login input[type="submit"]:active {
		box-shadow: inset 0 0 5px #000;
		background: linear-gradient(to bottom, rgba(57, 175, 154, 1) 0%, rgba(107, 198, 186, 1) 100%);
	}
CSS;

    }
}