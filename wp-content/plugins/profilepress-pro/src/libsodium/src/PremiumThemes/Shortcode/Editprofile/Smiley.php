<?php

namespace ProfilePress\Libsodium\PremiumThemes\Shortcode\Editprofile;

use ProfilePress\Core\Themes\Shortcode\EditProfileThemeInterface;

class Smiley implements EditProfileThemeInterface
{
    public function get_name()
    {
        return 'Smiley';
    }

    public function success_message()
    {
        return '<div class="pp-edit-success">Changes saved.</div>';
    }

    public function get_structure()
    {
        return <<<CODE
<div class="smiley1">
	<div class="ppboxa">
		[pp-user-avatar class="ppavatar"]
		<div class="pp-edia">
			[pp-remove-avatar-button class="pp-del-pix custom-file-upload" label="Delete Picture"]
		</div>
	</div>

	<div class="ppboxb">
		<div class="ppuserdata"><span class="ppname">Edit Profile</span></div>
		<div class="ppprofdata">
			<div class="heading">Personal Information</div>
			[edit-profile-username class="pp-top" placeholder="Username"]
			<br/> [edit-profile-email title="Email Address" placeholder="Email Address"]
			<br/> [edit-profile-password title="Password" placeholder="Password"]
			<br/> [edit-profile-website title="Website" placeholder="Website"]
			<br/> [edit-profile-nickname title="Nickname" placeholder="Nickname"]
			<br/> [edit-profile-first-name title="First Name" placeholder="First Name"]
			<br/> [edit-profile-last-name title="Last Name" placeholder="Last Name"]
			<br/> [edit-profile-cpf key="country" type="text" title="Country" placeholder="Country"]
			<br/>

			<div class="pp-smiley-file">
				<label for="pp-file-upload" class="custom-file-upload">Choose file</label> [edit-profile-avatar id="pp-file-upload"]
				<span id="pp-file-upload-value">upload avatar</span>
			</div>
			[edit-profile-bio title="Bio" placeholder="Bio"] [edit-profile-display-name title="Display Name" class="pp-no-radius" placeholder="Display Name"] [edit-profile-cpf key="gender" type="select" title="Gender" class="pp-gender"]
			<br style="clear:both"/> <br style="clear:both"/> <br/>

			<div class="heading">Social Media Profile URLs</div>
			[edit-profile-cpf key="facebook" type="text" class="pp-top" title="Facebook profile URL" placeholder="Facebook profile"]
			<br/> [edit-profile-cpf key="twitter" type="text" title="Twitter Profile URL" placeholder="Twitter URL"]
			<br/> [edit-profile-cpf key="linkedin" class="pp-bottom" type="text" title="LinkedIn Profile URL" placeholder="LinkedIn Profile"]
			<br style="clear:both"/> <br/> <br/>
		</div>
		<div class="pploginbutton">
			[edit-profile-submit class="pplogbutt"]
		</div>
	</div>
</div>

<script type="text/javascript">
	(function ($) {
		$("input:file[id=pp-file-upload]").change(function () {
			$("#pp-file-upload-value").html($(this).val());
		});
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
.profilepress-edit-profile-status {
    border-radius: 5px;
    font-size: 16px;
    line-height: 1.471;
    padding: 10px;
    background-color: #e74c3c;
    color: #ffffff;
    font-weight: normal;
    display: block;
    text-align: center;
    vertical-align: middle;
    margin: 5px auto;
    max-width: 400px;
}

.pp-edit-success {
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
    max-width: 400px;
}

.smiley1 {
    font-family: 'Montserrat', sans-serif;
    font-size: 16px;
}

.smiley1 input[type="file"] {
    display: none;
}

#pp-file-upload-value {
    padding: 5px 10px 1px;
    text-shadow: 0 1px 1px rgba(0, 0, 0, 0);
}

.smiley1 .pp-top {
    border-top-left-radius:4px !important;
    border-top-right-radius:4px !important;
}

.smiley1 .pp-bottom {
    border-bottom-left-radius:4px !important;
    border-bottom-right-radius:4px !important;
    border-bottom:0;
}

.smiley1 .custom-file-upload {
    font-size: 15px;
}

.smiley1 .custom-file-upload {
    margin: 0 !important;
    background: #BABABA;
    border: 1px solid #BABABA;
    border-top: 1px solid #ccc;
    border-left: 1px solid #ccc;
    -moz-border-radius: 3px;
    -webkit-border-radius: 3px;
    border-radius: 3px;
    color: #fff;
    display: inline-block;
    text-decoration: none;
    cursor: pointer;
    line-height: normal;
    padding: 5px;
}

.pp-del-pix {
    font-size: 10px !important;
    background-color: rgb(221, 51, 51) !important;
    border: 0 none !important;
}

.smiley1 .ppboxa {
    background: transparent;
    max-width: 400px;
    width: 100%;
    height: 80px;
    margin: 0 auto;
    text-align: center;
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
    position: relative;
}

.smiley1 .ppavatar {
    border-radius: 120px;
    border: 6px solid #fff;
    margin: auto;
    background: #f1f1f1;
    max-width: 120px;
    width: 100%;
    height: auto;
    position: relative;
}
.smiley1 .ppboxb {
    background: url({$asset_url}/bg1.png);
    max-width: 400px;
    width: 100%;
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
    padding-bottom: 5px;
}

.smiley1 .ppprofdata div.heading {
    text-align:center;
}
.smiley1 .ppprofdata ul {
    list-style: none;
    margin: 0;
    padding: 0;
}
.smiley1 .ppprofdata ul li {
    padding-bottom: 10px;
}
.smiley1 .ppprofdata ul li a {
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
.pp-smiley-file, .smiley1 input[type=text], .smiley1 input[type=password], .smiley1 textarea {
    border: 0;
    margin: 0;
    border-radius: 0;
    box-sizing: border-box;
    color: #2b2b2b;
    font-weight: 500;
    background-color:#fff;
    font-family: 'Montserrat', sans-serif;
    font-size: 14px;
    padding: 11px;
    max-width: 280px;
    width: 100%;
    border-bottom: 1px dashed rgba(0, 0, 0, .05);
    text-align: left;
    margin-bottom:0;
}

.ppprofdata textarea {
  height: 140px;
}

.pp-smiley-file, .smiley1 input[type=text]:focus, .smiley1 input[type=password]:focus, .smiley1 input[type=file]:focus, .smiley1 textarea:focus {
    outline: 0;
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
    max-width: 340px;
    width: 100%;
    padding: 8px;
    border-radius: 4px;
    box-shadow: 0 1px 1px rgba(0, 0, 0, .1);
    margin-bottom: 30px;
}
.smiley1 .ppsocialmedia {
    background: rgba(0, 0, 0, .2);
    height: 165px;
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
    max-width: 280px;
    width: 100%;
    font-size: 14px;
    font-family: 'Montserrat', sans-serif;
    color: rgba(0, 0, 0, .5);
    font-weight: 500;
    border-radius: 0;
    border-bottom-left-radius: 4px;
    border-bottom-right-radius:4px;
    padding: 12px;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

 .pp-no-radius {
    border: 0;
    background: #fff;
    max-width: 280px;
    width: 100%;
    font-size: 14px;
    font-family: 'Montserrat', sans-serif;
    color: rgba(0, 0, 0, .5);
    font-weight: 500;
    border-radius: 0;
    padding: 12px;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}


.pp-gender:focus {
    outline: none;
}

.pp-edia {
	  position: absolute;
	  right: 11px;
	  top: -5px;
	  margin-top: 75px;
	  padding: 6px;
	  border-bottom-right-radius: 3px;
	  padding-left: 8px;
	  font-size: 12px;
}

.pp-edia a {
	text-decoration: none;
	color: #1f1f1f;
}
CSS;

    }
}