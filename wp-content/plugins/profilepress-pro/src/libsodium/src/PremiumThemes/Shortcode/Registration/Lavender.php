<?php

namespace ProfilePress\Libsodium\PremiumThemes\Shortcode\Registration;

use ProfilePress\Core\Themes\Shortcode\RegistrationThemeInterface;

class Lavender implements RegistrationThemeInterface
{
    public function get_name()
    {
        return 'Lavender';
    }

    public function get_structure()
    {
        return <<<CODE
<div class="pp-lavender-form">
    <div class="pp-lavender-form">
        <div class="pp-lavender-style">
            <div class="pp-lavender-form-heading">Need an Account? sign up below!</div>
            <div class="pp-lavender-form-heading-talks">Dear Website Name,</div>
        </div>
    </div>
    <div class="pp-lavender-collect">
        I'd love to join your website. my email address is
        <li class="pp-lavender-after-line">[reg-email class="pp-lavender-field" placeholder=""]</li>
        please make my username
        <li class="pp-lavender-after-line">[reg-username class="pp-lavender-field" placeholder=""]</li>
        and my password
        <li class="pp-lavender-after-line">[reg-password class="pp-lavender-field" placeholder=""]</li>
        incidentally, i am definitely not a robot. I'll Prove it by clicking this box.!
        <div class="pp-lavender-end-section">
            [reg-submit class="pp-lavender-button" type="submit" label="SIGN UP"]
        </div>
    </div>
</div>
CODE;
    }

    public function success_message()
    {
        return '<div class="profilepress-reg-status">Registration Successful</div>';
    }

    public function get_css()
    {
        return <<<CSS
    @import url('https://fonts.googleapis.com/css?family=Raleway:300,400,600,700|Roboto:300,400,600,700&display=swap');

     /* css class for the form generated errors */
    .profilepress-reg-status {
        max-width: 600px;
        width: 100%;
        border-radius: 4px;
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
    }
    
    .pp-lavender-form {
        max-width: 600px;
        width: 100%;
        margin: 0 auto;
    }

    .pp-lavender-form .pp-lavender-form-heading {
        text-align: center;
        font-family: 'Roboto', sans-serif;
        font-weight: 900;
        padding: 20px 0px 20px 0px;
        font-size: 16px;
    }

    .pp-lavender-form .pp-lavender-form-heading-talks {
        text-align: center;
        font-size: 30px !important;
        font-family: 'Raleway', sans-serif;
        font-weight: 900;
        padding: 0px 0px 10px 0px;
        text-transform: uppercase;
    }

    .pp-lavender-form .pp-lavender-collect {
        padding: 20px !important;
        font-size: 15px !important;
        font-family: 'Roboto', sans-serif;
        line-height: 1.9;
        font-weight: 300;
    }

    .pp-lavender-form input.pp-lavender-field {
        color: #a3a3a3;
        border: 0px;
        padding: 2px;
        max-width: 145px;
    }
    
    .pp-lavender-form .pp-lavender-after-line input:focus {
        outline: 0;
        border: 0;
        box-shadow: none;
    }

    .pp-lavender-form input.input.pp-lavender-field:focus {
        outline: none !important;
    }

    .pp-lavender-form input.pp-lavender-button {
        font-family: 'Raleway', sans-serif;
        font-size: 13px;
        background: none !important;
        border: 2px solid black;
        padding: 9px !important;
        font-weight: 900;
        width: initial;
    }

    .pp-lavender-form .pp-lavender-after-line::after {
        display: block;
        content: "";
        width: 145px;
        height: 2px;
        background: none repeat scroll 0 0 #dd3333 !important;
    }

    .pp-lavender-form .pp-lavender-after-line {
        display: inline-block;
        list-style: none;
    }

    .pp-lavender-form .the-recaptcha-form-space {
        float: left;
    }

    .pp-lavender-form .pp-lavender-end-section {
        text-align: center;
        padding-top: 20px;
    }
CSS;

    }
}