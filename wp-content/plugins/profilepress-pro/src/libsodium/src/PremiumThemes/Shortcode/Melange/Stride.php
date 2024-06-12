<?php

namespace ProfilePress\Libsodium\PremiumThemes\Shortcode\Melange;

use ProfilePress\Core\Themes\Shortcode\MelangeThemeInterface;

class Stride implements MelangeThemeInterface
{
    public function get_name()
    {
        return 'Stride';
    }

    public function get_structure()
    {
        return <<<CODE
[pp-registration-form novalidate]
<div id="msform">
  <!-- progressbar -->
  <ul id="progressbar">
    <li class="active">Account Setup</li>
    <li>Social Profiles</li>
    <li>Personal Details</li>
  </ul>
  <!-- fieldsets -->
  <fieldset>
    <h2 class="fs-title">Create your account</h2>
    <h3 class="fs-subtitle">This is step 1</h3>
    [reg-username placeholder="Username"]
    [reg-email placeholder="Email"]
    [reg-password placeholder="Password"]
    <input type="button" name="next" class="next action-button" value="Next" />
  </fieldset>
  <fieldset>
    <h2 class="fs-title">Social Profiles</h2>
    <h3 class="fs-subtitle">Your presence on social networks</h3>
    [reg-cpf key="twitter" type="text" placeholder="Twitter"]
    [reg-cpf key="facebook" type="text" placeholder="Facebook"]
    [reg-cpf key="google" type="text" placeholder="Google Plus"]
    <input type="button" name="previous" class="previous action-button" value="Previous" />
    <input type="button" name="next" class="next action-button" value="Next" />
  </fieldset>
  <fieldset>
    <h2 class="fs-title">Personal Details</h2>
    <h3 class="fs-subtitle">We will never sell it</h3>
    [reg-first-name placeholder="First Name"]
    [reg-last-name placeholder="Last Name"]
    [reg-nickname placeholder="Nickname"]
    [reg-bio placeholder="Biography"]
    <input type="button" name="previous" class="previous action-button" value="Previous" />
    [reg-submit class="submit action-button" value="Submit"]
  </fieldset>
</div>
[/pp-registration-form]

<!-- jQuery easing plugin -->
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js" type="text/javascript" defer></script>

<script type="text/javascript">
//jQuery time
(function($) {
  var current_fs, next_fs, previous_fs;
  var left, opacity, scale;
  var animating;

  $(".next").on('click', function() {
    if (animating) return false;
    animating = true;

    current_fs = $(this).parent();
    next_fs = $(this).parent().next();

    //activate next step on progressbar using the index of next_fs
    $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");

    //show the next fieldset
    next_fs.show();
    //hide the current fieldset with style
    current_fs.animate({
      opacity: 0
    }, {
      step: function(now, mx) {
        //as the opacity of current_fs reduces to 0 - stored in "now"
        //1. scale current_fs down to 80%
        scale = 1 - (1 - now) * 0.2;
        //2. bring next_fs from the right(50%)
        left = (now * 50) + "%";
        //3. increase opacity of next_fs to 1 as it moves in
        opacity = 1 - now;
        current_fs.css({
          'transform': 'scale(' + scale + ')'
        });
        next_fs.css({
          'left': left,
          'opacity': opacity
        });
      },
      duration: 800,
      complete: function() {
        current_fs.hide();
        animating = false;
      },
      //this comes from the custom easing plugin
      easing: 'easeInOutBack'
    });
  });

  $(".previous").on('click', function() {
    if (animating) return false;
    animating = true;

    current_fs = $(this).parent();
    previous_fs = $(this).parent().prev();

    //de-activate current step on progressbar
    $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");

    //show the previous fieldset
    previous_fs.show();
    //hide the current fieldset with style
    current_fs.animate({
      opacity: 0
    }, {
      step: function(now, mx) {
        //as the opacity of current_fs reduces to 0 - stored in "now"
        //1. scale previous_fs from 80% to 100%
        scale = 0.8 + (1 - now) * 0.2;
        //2. take current_fs to the right(50%) - from 0%
        left = ((1 - now) * 50) + "%";
        //3. increase opacity of previous_fs to 1 as it moves in
        opacity = 1 - now;
        current_fs.css({
          'left': left
        });
        previous_fs.css({
          'transform': 'scale(' + scale + ')',
          'opacity': opacity
        });
      },
      duration: 800,
      complete: function() {
        current_fs.hide();
        animating = false;
      },
      //this comes from the custom easing plugin
      easing: 'easeInOutBack'
    });
  });
})(jQuery);
</script>
CODE;
    }

    public function get_css()
    {
        return <<<CSS
@import url(https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700&display=swap);

/* css class for the form generated errors */
.profilepress-reg-status {
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
  margin: 5px 0;
}

.stride-success {
 border-radius: 6px;
 font-size: 17px;
 line-height: 1.471;
 padding: 10px 19px;
 background-color: #2ecc71;
 color: #ffffff;
 font-weight: normal;
 display: block;
 text-align: center;
 vertical-align: middle;
 margin: 5px 0;
}

/*form styles*/

#msform {
  width: 400px;
  margin: 50px auto 550px;
  text-align: center;
  position: relative;
}

#msform fieldset {
  background: white;
  border: 0 none;
  border-radius: 3px;
  box-shadow: 0 0 15px 1px rgba(0, 0, 0, 0.4);
  padding: 20px 30px;
  box-sizing: border-box;
  width: 80%;
  margin: 0 10%;
  /*stacking fieldsets above each other*/
  
  position: absolute;
}

/*Hide all except first fieldset*/

#msform fieldset:not(:first-of-type) {
  display: none;
}

/*inputs*/
#msform input,
#msform textarea {
  padding: 15px;
  border: 1px solid #ccc;
  border-radius: 3px;
  margin-bottom: 10px;
  width: 100%;
  box-sizing: border-box;
  font-family: montserrat;
  color: #2C3E50;
  font-size: 13px;
}

/*buttons*/
#msform .action-button {
  width: 100px;
  background: #27AE60;
  font-weight: bold;
  color: white;
  border: 0 none;
  border-radius: 1px;
  cursor: pointer;
  padding: 10px 5px;
  margin: 10px 5px;
}

#msform .action-button:hover,
#msform .action-button:focus {
  box-shadow: 0 0 0 2px white, 0 0 0 3px #27AE60;
}

/*headings*/
.fs-title {
  font-size: 15px;
  text-transform: uppercase;
  color: #2C3E50;
  margin-bottom: 10px;
}

.fs-subtitle {
  font-weight: normal;
  font-size: 13px;
  color: #666;
  margin-bottom: 20px;
}

/*progressbar*/

#progressbar {
  margin-bottom: 30px;
  overflow: hidden;
  /*CSS counters to number the steps*/
  
  counter-reset: step;
}

#progressbar li {
  list-style-type: none;
  color: #616161;
  text-transform: uppercase;
  font-size: 9px;
  width: 33.33%;
  float: left;
  position: relative;
}

#progressbar li:before {
  content: counter(step);
  counter-increment: step;
  width: 20px;
  line-height: 20px;
  display: block;
  font-size: 15px;
  color: #333;
  background: white;
  border-radius: 3px;
  margin: 0 auto 5px auto;
}

#progressbar li:first-child:after {
  /*connector not needed before the first step*/
  
  content: none;
}
/*marking active/completed steps green*/
/*The number of the step and the connector before it = green*/

#progressbar li.active:before,
#progressbar li.active:after {
  background: #27AE60;
  color: white;
}
CSS;

    }

    public function registration_success_message()
    {
        return '<div class="stride-success">Registration Successful.</div>';
    }

    public function password_reset_success_message()
    {
        return '';
    }

    public function edit_profile_success_message()
    {
        return '';
    }
}