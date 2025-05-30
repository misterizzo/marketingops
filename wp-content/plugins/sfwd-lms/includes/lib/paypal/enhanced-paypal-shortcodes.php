<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
Code included from the Enhanced Paypal Shortcodes plugin.
http://thewpwarrior.com/wordpress-plugin-enhanced-paypal-shortcodes/
Use shortcodes to easily embed a fully functional paypal button on your WordPress website.
Can be used for Buy Now and Subscription buttons. <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DXBKBP7Q5FSGC" target="_blank">Make a Donation</a>.

Designed with using iDevAffiliate or JROX Jam affiliate management programs which require additional code added to the button.
This plugin was inspired by Paypal Shortcodes by Pixline.

By Charly Leetham, version: 0.5a
http://askcharlyleetham.com

Copyright (C) Ask Charly Leetham (A Leetham Trust Project)
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

define( 'TWPW_NAME', 'Enhanced Paypal Shortcodes' );  // Name of the Plugin
define( 'TWPW_VERSION', '0.5a' );         // Current version of the Plugin
define( 'ALT_ADD', 'Add to cart (Paypal)' );   // alternate text for "Add to cart" image
define( 'ALT_VIEW', 'View Paypal cart' );      // alternate text for "View cart" image
define( 'ALT_SUBS', 'Subscribe Now (Paypal)' );   // alternate text for "Subscribe" image


/* Parameters for Shortcode for all Paypal buttons

type = paynow, subscribe, addtocart or hosted

For Hosted Buttons:
buttonid = the button id number from your paypal code

For All Button Types:
imageurl = The location of the image for the button. Use full web address for the image - e.g http://domainname.com/mybuynowbutton.jpg.
Default is https://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif

imagewidth = the width of the paypal image

For PayNow, Subscribe and Add To Cart Buttons:

email = the email address of the paypal account

itemno = A unique identifier for your product / service

name = Description of product / service

noshipping = Prompt for Shipping address
	  0 is prompt, but don't require
	  1 is don't prompt
	  2 is prompt and require the shipping address
	  defaults to 0

nonote = Prompt payers to include a note (Paynow buttons only)
	  0 is show the note box and prompt the user
	  1 is hide the note box and do not prompt the user
	  defaults to 0

currencycode = The currency for the transaction
	  Australian Dollar AUD
	  Canadian Dollar CAD
	  Czech Koruna CZK
	  Danish Krone DKK
	  Euro EUR
	  Hong Kong Dollar HKD
	  Hungarian Forint HUF
	  Israeli New Sheqel ILS
	  Japanese Yen JPY
	  Mexican Peso MXN
	  Norwegian Krone NOK
	  New Zealand Dollar NZD
	  Polish Zloty PLN
	  Pound Sterling GBP
	  Singapore Dollar SGD
	  Swedish Krona SEK
	  Swiss Franc CHF
	  U.S. Dollar USD
	  Default is USD

rm = The return method. This will only work if returnurl is also set. This variable is often required by membership type software
0 – all shopping cart transactions use the GET method
1 – the payer’s browser is redirected to the return URL by the GET method, and no transaction variables are sent
2 – the payer’s browser is redirected to the return URL by the POST method, and all transaction variables are also posted
The default is 0.

notifyurl = The URL to send payment advice too. Often required for IPN or other notifications
If this parameter is not used, no notifyurl value is added to the button

returnurl = The URL to which the payer’s browser is redirected after completing the payment; for example, a URL on your site that displays a “Thank you for your payment” page.
Default – The browser is redirected to a PayPal web page.

cancelurl = The URL to which the payer’s browser is redirected if the purchaser cancels the payment transaction before completing the process

scriptcode = the link to any script code that you may need to include.  e.g For Jrox JAM, some script code is added to the paypal buttons. Usage /foldername/scriptcode.php
If this parameter is not used, no notifyurl value is added to the button

pagestyle = The custom payment page style for checkout pages. Allowable values:
paypal – use the PayPal page style
primary – use the page style that you marked as primary in your account profile
page_style_name – use the custom payment page style from your account profile that has the specified name
The default is primary if you added a custom payment page style to your account profile. Otherwise, the default is paypal.

cbt = Sets the text for the Return to Merchant button on the PayPal Payment Complete page. For Business accounts, the return button displays your business name in place of the word “Merchant” by default. For Donate buttons, the text reads “Return to donations coordinator” by default.
NOTE: The returnurl variable must also be set.

cn = Label that appears above the note field on the Check Out page. This value is not saved and will not appear in any of your notifications. If omitted, the default label above the note field is "Add special instructions to merchant." The cn variable is not valid with Subscribe buttons or if you include nonote="1".

lc = Sets the payer’s language for the billing information/log-in page only.
The default is US. For allowable values visit: https://cms.paypal.com/au/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_countrycodes


Paynow & Add To Cart Button only parameters

amount = the amount to charge (for Paynow & Add To Cart buttons only)

shipping = the amount of shipping to charge per item

shipping2 = the amount of shipping to charge for each extra item purchased.

Subscribe Button only parameters

Trial Period 1:
a1 = The value to charge for the first trial period
p1 = The duration of the first trial.
t1 = The units of duration.

D for Days, allowable entries for p1: 1 to 90
W for Weeks, allowable entries for p1: 1 to 52
M for Months, allowable entries for p1: 1 to 24
Y for Years, allowable entries for p1: 1 to 5

Trial Period 2:
a2 = The value to charge for the second trial period
p2 = The duration of the second trial.
t2 = The units of duration.

D for Days, allowable entries for p2: 1 to 90
W for Weeks, allowable entries for p2: 1 to 52
M for Months, allowable entries for p2: 1 to 24
Y for Years, allowable entries for p2: 1 to 5

The full subscription Payment:
a3 = The value to charge
p3 = The duration between charging
t3 = The units of duration.

D for Days, allowable entries for p3: 1 to 90
W for Weeks, allowable entries for p3: 1 to 52
M for Months, allowable entries for p3: 1 to 24
Y for Years, allowable entries for p3: 1 to 5

src = Recurring payments. Subscription payments recur unless subscribers cancel their subscriptions before the end of the current billing cycle or you limit the number of times that payments recur with the value that you specify for srt.
Allowable values:
0 – subscription payments do not recur
1 – subscription payments recur
The default is 0.

srt = Recurring times. Number of times that subscription payments recur. Specify an integer above 1. Valid only if you specify src="1".
Allowable values:an integer above 1.

sra = Reattempt on failure. If a recurring payment fails, PayPal attempts to collect the payment two more times before canceling the subscription.
Allowable values:
0 – do not reattempt failed recurring payments
1 – reattempt failed recurring payments before canceling
The default is 0

modify - Modification behavior. Allowable values:
 0 – allows subscribers to only create new subscriptions
 1 – allows subscribers to modify their current subscriptions or sign up for new ones
 2 – allows subscribers to only modify their current subscriptions
The default value is 0

Add To Cart

display = Display the contents of the PayPal Shopping Cart to the buyer. If set, the shopping cart will be displayed after an item is added.  If not set, the item will be added to the cart only.

Formatting
The plugin will wrap the paypal button in a <div> tag.  The formatting options available are:
divwidth = the width of the div.  This should be at least the width of the image.
Default - 100%

textalign = the alignment of the image / text within the div
Allowable values:
left - text is left justified
right - text is right justified
center - text is centered
No default, taken from page format

float = position of the div on the page
left - the div 'floats' on the left
right - the div 'floats' on the right
Default - if this value is missing, the div is centered on the page

marginleft = the amount of space between the div and the text to the left of the div (particularly good to use when using float=right)
Default - if this value is missing, the page format is used

marginright = the amount of space between the div and the text to the right of the div
(particularly good to use when using float=left)
Default - if this value is missing, the page format is used

margintop = the amount of space to the line above the div
Default = 10px;

marginbottom = the amount of space to the line below the div
Default = 10px;

Button Formatting:

Image Classes:
The shortcode will add a 'placeholder' Paypal image that is 1px wide by 1px tall into the button. The code adds a class of "ppalholder" to this image.  This will allow site owners to add the class to their theme styles and remove any borders that cause the image to be 'visible'.

Class added to Buy Now, Add To Cart, Hosted or Subscribe button
The code will add the class "ppalbtn" to the actual image embedded on the page to allow for more formatting choices.

Sample Usage:

Buy Now Button:
[paypal type="paynow" amount="12.99" email="payments@arvoreentreasures.com" itemno="12345657" name="Description" noshipping="1" nonote="1" qty="1" shipping="4.00" shipping2="1.00" currencycode="USD" imageurl="https://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif" rm="2" notifyurl="http://notifyurl.com" returnurl="http://returnurl.com" scriptcode="scriptcode" imagewidth="100px" pagestyle="paypal" lc="AU" cbt="Complete Your Purchase"]

Subscribe Button with 2 trial periods and recurring Monthly payments.
[paypal type="subscribe" email="payments@arvoreentreasures.com" itemno="12345657" name="Description" noshipping="1" currencycode="USD" imageurl="https://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif" a1="1" p1="7" t1="D" a2="3" p2="1" t3="M" a3="47" p3="1" t3="M" rm="2" notifyurl="http://notifyurl.com" returnurl="http://returnurl.com" scriptcode="scriptcode" imagewidth="100px" pagestyle="paypal" lc="AU" cbt="Complete Your Purchase"]

Hosted Button
[paypal type="hosted" buttonid="1234456" imageurl="https://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif"]

Add To Cart Button
[paypal type="addtocart" amount="1.99" email="payments@arvoreentreasures.com" itemno="12345657" name="Description" noshipping="1" nonote="1" currencycode="USD" imageurl="https://www.paypalobjects.com/en_AU/i/btn/btn_cart_LG.gif" rm="2" notifyurl="http://notifyurl.com" returnurl="http://returnurl.com" scriptcode="scriptcode" cbt="Return to Me" cancelreturn="http://shoppingcartcancelurl.com" lc="AU" qty="4" shipping="3.00" shipping2="1.50" pagestyle="paypal"]

Adding formatting to Hosted Button
To use your own custom image hosted on your site, that is 200px wide, center the button in the line and leave 20px space above and 10px space below:
[paypal type="hosted" buttonid="1234456" imageurl="http://yourdomainname.com/images/buynow.jpg" imagewidth="200px" divwidth="200px" margintop="20px" marginbottom="10px"]

All formatting options work on three button types.
*/

if ( ! function_exists( 'enhanced_paypal_shortcode' ) ) {

	/**
	 * Builds the `paypal` shortcode output.
	 *
	 * @param array $atts An array of shortcode atributes.
	 *
	 * @return string The `paypal` shortcode output.
	 */
	function enhanced_paypal_shortcode( $atts ) {
		$registration_variation = learndash_registration_variation();
		$variation_classic      = \LearnDash_Theme_Register_LD30::$variation_classic;

		$atts = shortcode_atts(
			array(
				'type'                           => '',
				'textalign'                      => '',
				'divwidth'                       => '',
				'float'                          => '',
				'marginleft'                     => '',
				'marginright'                    => '',
				'margintop'                      => '',
				'marginbottom'                   => '',
				'sandbox'                        => '',
				'qty'                            => '1',
				'shipping'                       => '',
				'shipping2'                      => '',
				'imageurl'                       => '',
				'imagewidth'                     => '100px',
				'noshipping'                     => '1',
				'nonote'                         => '1',
				'rm'                             => '2',
				'lc'                             => '',
				'cbt'                            => esc_html__( 'Complete Your Purchase', 'learndash' ),
				'cn'                             => '',
				'pagestyle'                      => 'paypal',
				'notifyurl'                      => '',
				'notifyurl2'                     => '',
				'returnurl'                      => '',
				'cancelurl'                      => '',
				//      'scriptcode'    => 'scriptcode',
									'scriptcode' => '',  // Removed value as this was causing 404 errors
				'email'                          => '',
				'currencycode'                   => '',
				'itemno'                         => '',
				'name'                           => '',
				'amount'                         => '',
				'cancelreturn'                   => '',
				'a1'                             => '',
				'p1'                             => '',
				't1'                             => '',
				'a2'                             => '',
				'p2'                             => '',
				't2'                             => '',
				'a3'                             => '',
				'p3'                             => '',
				't3'                             => '',
				'src'                            => 1,
				'srt'                            => 0,
				'sra'                            => 1,
				'modify'                         => '',
				'custom'                         => '',
				'button_label'                   => __( 'Use PayPal', 'learndash' ),
				'button_aria_label'              => '',
			),
			$atts
		);

		$button_text = $atts['button_label'];
		$button_aria_label = empty( $atts['button_aria_label'] ) ? $button_text : $atts['button_aria_label'];

		switch ( $atts['type'] ) :
			case 'paynow':
				$code = '
        <div style="';
				if ( $atts['textalign'] ) {
					  $code .= 'text-align: ' . $atts['textalign'] . ';';
				}
				if ( $atts['divwidth'] > 0 ) {
					   $code .= 'width: ' . $atts['divwidth'] . ';';
				}
				if ( $atts['float'] ) {
					   $code .= 'float: ' . $atts['float'] . ';';
				}
				if ( $atts['marginleft'] > -1 ) {
					   $code .= 'margin-left: ' . $atts['marginleft'] . ';';
				}
				if ( $atts['marginright'] > -1 ) {
					   $code .= 'margin-right: ' . $atts['marginright'] . ';';
				}
				if ( $atts['margintop'] > -1 ) {
					   $code .= 'margin-top: ' . $atts['margintop'] . ';';
				}
				if ( $atts['marginbottom'] > -1 ) {
					   $code .= 'margin-bottom: ' . $atts['marginbottom'] . ';';
				}
				$paypalUrl = 'https://www.paypal.com/cgi-bin/webscr';
				$pixelUrl  = 'https://www.paypal.com/en_US/i/scr/pixel.gif';
				$buttonUrl = 'https://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif';
				if ( $atts['sandbox'] == 1 ) {
					$paypalUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
					$pixelUrl  = 'https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif';
					$buttonUrl = 'https://www.sandbox.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif';
				}

				if ( ! is_email( $atts['email'] ) ) {
					$atts['email'] = '';
				}
				//if ( strlen( $atts['currencycode'] ) > 3 )
				//	$atts['currencycode'] = substr( $atts['currencycode'], 0, 3 );
				if ( strlen( $atts['lc'] ) > 2 ) {
					$atts['lc'] = substr( $atts['lc'], 0, 2 );
				}
				if ( strlen( $atts['itemno'] ) ) {
					$atts['itemno'] = substr( $atts['itemno'], 0, 127 );
				}

				//      if ( ( isset( $atts['amount'] ) ) && ( !empty( $atts['amount'] ) ) ) {
				//          // format the Course price to be proper XXX.YY no leading dollar signs or other values.
				//          $course_price = preg_replace("/[^0-9.]/", '', $atts['amount'] );
				//          $atts['amount'] = number_format(floatval($course_price), 2, '.', '' );
				//      }

				$code .= '"><form class="learndash-payment-gateway-form-paypal" name="buynow" action="' . $paypalUrl . '" method="post">
        <input type="hidden" name="cmd" value="_xclick" />
		<input type="image" src="' . $pixelUrl . '" border="0" alt="" width="1" height="1" class="ppalholder">
		<input type="hidden" name="bn" value="PP-BuyNowBF" />
		<input type="hidden" name="business" value="' . $atts['email'] . '">
		<input type="hidden" name="currency_code" value="' . $atts['currencycode'] . '">
		<input type="hidden" name="item_number" value="' . $atts['itemno'] . '">
		<input type="hidden" name="item_name" value="' . $atts['name'] . '">
		<input type="hidden" name="amount" value="' . $atts['amount'] . '">
		<input type="hidden" name="custom" value="' . $atts['custom'] . '">';
				// Add Quantity
				if ( $atts['qty'] == 'ask' ) {
					$code .= '<input type="hidden" name="undefined_quantity" value="1">';
				} else {
					$code .= '<input type="hidden" name="quantity" value="' . $atts['qty'] . '">';
				}

				// Add Shipping
				if ( $atts['shipping'] ) {
					$code .= '<input type="hidden" name="shipping" value="' . $atts['shipping'] . '">';
				}

				// Add Shipping2 - additional items shipping
				if ( $atts['shipping2'] ) {
					$code .= '<input type="hidden" name="shipping2" value="' . $atts['shipping2'] . '">';
				}

				// Define Image to Use
				if ( $atts['imageurl'] ) {
					$code .= '<input type="hidden" src="' . $atts['imageurl'] . '" border="0" name="submit" alt="' . ALT_ADD . '"';
					if ( $atts['imagewidth'] ) {
						 $code .= ' width="' . $atts['imagewidth'] . '"';
					}
					$code .= ' class="ppalbtn">';
				} else {
					$code .= '<input type="hidden" src="' . $buttonUrl . '" border="0" name="submit" alt="' . ALT_ADD . '" class="ppalbtn">';
				}
				$code .= '<button aria-label="' . $button_aria_label . '" type="submit" class="' . Learndash_Payment_Button::map_button_class_name() . '" id="' . Learndash_Payment_Button::map_button_id() . '">';
				$code .= esc_html( $button_text );
				$code .= '</button>';

				if ( $atts['noshipping'] > -1 ) {
					$code .= '
			<input type="hidden" name="no_shipping" value="' . $atts['noshipping'] . '">';
				}

				if ( $atts['nonote'] > -1 ) {
					$code .= '
            <input type="hidden" name="no_note" value="' . $atts['nonote'] . '" />';
				}

				if ( $atts['rm'] > -1 ) {
					$code .= '
			<input type="hidden" name="rm" value="' . $atts['rm'] . '">';
				}

				// Add language code
				if ( $atts['lc'] ) {
					$code .= '<input type="hidden" name="lc" value="' . $atts['lc'] . '">';
				}

				/* Checkout Page Variables */

				// Add return to merchant text
				if ( $atts['cbt'] ) {
					$code .= '<input type="hidden" name="cbt" value="' . $atts['cbt'] . '">';
				}

				// Add Cancel Return URL
				if ( $atts['cancelreturn'] ) {
					$code .= '<input type="hidden" name="cancel_return" value="' . $atts['cancelreturn'] . '">';
				}

				// Add Special Instructions
				if ( $atts['cn'] ) {
					$code .= '<input type="hidden" name="cn" value="' . $atts['cn'] . '">';
				}

				// Add Page Style
				if ( $atts['pagestyle'] ) {
					$code .= '<input type="hidden" name="page_style" value="' . $atts['pagestyle'] . '">';
				}

				if ( $atts['notifyurl'] ) {
					$code .= '<input type="hidden" name="notify_url" value="' . $atts['notifyurl'] . '">';
				}

				if ( $atts['notifyurl2'] ) {
					$code .= '<input type="hidden" name="notify_url" value="' . $atts['notifyurl2'] . '">';
				}

				if ( $atts['returnurl'] ) {
					$code .= '<input type="hidden" name="return" value="' . $atts['returnurl'] . '">';
				}

				if ( $atts['cancelurl'] ) {
					$code .= '<input type="hidden" name="cancel_return" value="' . $atts['cancelurl'] . '">';
				}
				if ( $atts['custom'] ) {
					$code .= '<input type="hidden" name="custom" value="' . $atts['custom'] . '">';
				}
				if ( $atts['scriptcode'] ) {
					$code .= '<script src="' . $atts['scriptcode'] . '" type="text/javascript"></script>';
				}
				$code .= '</form>';

				$code .= '</div>';
				break;

			case 'subscribe':
				$code = '';
				/*
				$code .= <div style="';
				if ($atts['textalign']) {
				$code.='text-align: '.$atts['textalign'].';';
				}

				if ($atts['divwidth'] > 0) {
				$code.='width: '.$atts['divwidth'].';';
				}

				if ($atts['float']) {
				$code.='float: '.$atts['float'].';';
				}

				if ($atts['marginleft'] > -1 ) {
				$code.='margin-left: '.$atts['marginleft'].';';
				}

				if ($atts['marginright'] > -1 ) {
				$code.='margin-right: '.$atts['marginright'].';';
				}

				if ($atts['margintop'] > -1 ) {
				   $code.='margin-top: '.$atts['margintop'].';';
				}

				if ($atts['marginbottom'] > -1 ) {
				   $code.='margin-bottom: '.$atts['marginbottom'].';';
				}
				*/
				$paypalUrl = 'https://www.paypal.com/cgi-bin/webscr';
				if ( $atts['sandbox'] == 1 ) {
					$paypalUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
				}
				$code .= '<form class="learndash-payment-gateway-form-paypal" name="subscribewithpaypal" action="' . esc_url( $paypalUrl ) . '" method="post">
        <input type="hidden" name="cmd" value="_xclick-subscriptions" />

		<input type="image" src="https://www.paypal.com/en_US/i/scr/pixel.gif" border="0" alt="" width="1" height="1" class="ppalholder">';

				if ( $atts['imageurl'] ) {
					$code .= '<input type="hidden" src="' . $atts['imageurl'] . '" border="0" name="submit" alt="' . ALT_ADD . '"';
					if ( $atts['imagewidth'] ) {
						$code .= ' width="' . $atts['imagewidth'] . '"';
					}
					$code .= ' class="ppalbtn">';
				} else {
					$code .= '<input type="hidden" src="https://www.paypal.com/en_AU/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="' . esc_html__( 'PayPal - The safer, easier way to pay online.', 'learndash' ) . '" class="ppalbtn">';
				}

				$code .= '<button aria-label="' . $button_aria_label . '" type="submit" class="btn-join button button-primary button-large wp-element-button' . ( $registration_variation !== $variation_classic ? ' ld--ignore-inline-css' : '' ) . '" id="btn-join">';
				$code .= esc_html( $button_text );
				$code .= '</button>';

				if ( $atts['email'] ) {
					 $code .= '<input type="hidden" name="business" value="' . $atts['email'] . '">';
				}

				if ( $atts['currencycode'] ) {
					$code .= '<input type="hidden" name="currency_code" value="' . $atts['currencycode'] . '">';
				}

				if ( $atts['itemno'] ) {
					$code .= '<input type="hidden" name="item_number" value="' . $atts['itemno'] . '">';
				}

				if ( $atts['name'] ) {
					$code .= '<input type="hidden" name="item_name" value="' . $atts['name'] . '">';
				}

				if ( $atts['amount'] ) {
					$code .= '<input type="hidden" name="amount" value="' . $atts['amount'] . '">';
				}

				if ( $atts['custom'] ) {
					$code .= '<input type="hidden" name="custom" value="' . $atts['custom'] . '">';
				}

				if ( $atts['noshipping'] > -1 ) {
					$code .= '<input type="hidden" name="no_shipping" value="' . $atts['noshipping'] . '" />';
				}

				$code .= '<input type="hidden" name="no_note" value="1" />';

				/*Trial 1 settings */
				if ( $atts['a1'] > -1 ) {
					$code .= '<input type="hidden" name="a1" value="' . $atts['a1'] . '">';
				}

				if ( $atts['p1'] > 0 ) {
					$code .= '<input type="hidden" name="p1" value="' . $atts['p1'] . '">';
				}

				if ( $atts['t1'] ) {
					$code .= '<input type="hidden" name="t1" value="' . $atts['t1'] . '">';
				}

				/*Trial 2 settings */
				if ( $atts['a2'] > -1 ) {
					$code .= '<input type="hidden" name="a2" value="' . $atts['a2'] . '">';
				}

				if ( $atts['p2'] > 0 ) {
					$code .= '<input type="hidden" name="p2" value="' . $atts['p2'] . '">';
				}

				if ( $atts['t2'] ) {
					$code .= '<input type="hidden" name="t2" value="' . $atts['t2'] . '">';
				}

				/*Ongoing subscription*/
				if ( $atts['a3'] > 0 ) {
					$code .= '<input type="hidden" name="a3" value="' . $atts['a3'] . '">';
				}

				if ( $atts['p3'] > 0 ) {
					$code .= '<input type="hidden" name="p3" value="' . $atts['p3'] . '">';
				}

				if ( $atts['t3'] ) {
					$code .= '<input type="hidden" name="t3" value="' . $atts['t3'] . '">';
				}

				/* SRC - are payments recurring? 0 = No, 1 = Yes */
				if ( $atts['src'] == 0 ) {
					$code .= '<input type="hidden" name="src" value="0">';
				} else {
					$code .= '<input type="hidden" name="src" value="1">';
				}

				/* SRT - no of time payments recur?  */
				if ( $atts['srt'] > 1 ) {
					$code .= '<input type="hidden" name="srt" value="' . $atts['srt'] . '">';
				}

				/* SRA - re-attempt if fail?  0 = No, 1 = Yes */
				if ( $atts['sra'] == 0 ) {
					$code .= '<input type="hidden" name="sra" value="0">';
				} else {
					$code .= '<input type="hidden" name="sra" value="1">';
				}

				if ( $atts['rm'] > -1 ) {
					$code .= '<input type="hidden" name="rm" value="' . $atts['rm'] . '">';
				}

				// Add language code
				if ( $atts['lc'] ) {
					$code .= '<input type="hidden" name="lc" value="' . $atts['lc'] . '">';
				}

				// Add return to merchant text
				if ( $atts['cbt'] ) {
					$code .= '<input type="hidden" name="cbt" value="' . $atts['cbt'] . '">';
				}

				// Modify Subscriptions
				if ( $atts['modify'] ) {
					$code .= '<input type="hidden" name="modify" value="' . $atts['modify'] . '">';
				}

				// Add Cancel Return URL
				if ( $atts['cancelreturn'] ) {
					$code .= '<input type="hidden" name="cancel_return" value="' . $atts['cancelreturn'] . '">';
				}

				// Add Special Instructions
				if ( $atts['cn'] ) {
					$code .= '<input type="hidden" name="cn" value="' . $atts['cn'] . '">';
				}

				// Add Page Style
				if ( $atts['pagestyle'] ) {
					$code .= '<input type="hidden" name="page_style" value="' . $atts['pagestyle'] . '">';
				}
				if ( $atts['notifyurl'] ) {
					$code .= '<input type="hidden" name="notify_url" value="' . $atts['notifyurl'] . '">';
				}

				if ( $atts['notifyurl2'] ) {
					$code .= '<input type="hidden" name="notify_url" value="' . $atts['notifyurl2'] . '">';
				}

				if ( $atts['returnurl'] ) {
					$code .= '<input type="hidden" name="return" value="' . $atts['returnurl'] . '">';
				}

				if ( $atts['cancelurl'] ) {
					$code .= '<input type="hidden" name="cancel_return" value="' . $atts['cancelurl'] . '">';
				}

				if ( $atts['scriptcode'] ) {
					$code .= '<script src="' . $atts['scriptcode'] . '" type="text/javascript"></script>';
				}

				$code .= '</form>';
				//$code.='</div>';
				break;

			case 'hosted':
				$code = '<div style="';
				if ( $atts['textalign'] ) {
					$code .= 'text-align: ' . $atts['textalign'] . ';';
				}

				if ( $atts['divwidth'] > 0 ) {
					$code .= 'width: ' . $atts['divwidth'] . ';';
				}

				if ( $atts['float'] ) {
					$code .= 'float: ' . $atts['float'] . ';';
				}

				if ( $atts['marginleft'] > -1 ) {
					$code .= 'margin-left: ' . $atts['marginleft'] . ';';
				}

				if ( $atts['marginright'] > -1 ) {
					$code .= 'margin-right: ' . $atts['marginright'] . ';';
				}

				if ( $atts['margintop'] > -1 ) {
					$code .= 'margin-top: ' . $atts['margintop'] . ';';
				}

				if ( $atts['marginbottom'] > -1 ) {
					$code .= 'margin-bottom: ' . $atts['marginbottom'] . ';';
				}

				$code .= '"><form class="learndash-payment-gateway-form-paypal" name="" action="https://www.paypal.com/cgi-bin/webscr" method="post">
        <input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="' . $atts['buttonid'] . '">
		<input type="image" src="https://www.paypal.com/en_US/i/scr/pixel.gif" border="0" alt="" width="1" height="1">';

				if ( $atts['imageurl'] ) {
					$code .= '<input type="hidden" src="' . $atts['imageurl'] . '" border="0" name="submit" alt="' . ALT_ADD . '"';
					if ( $atts['imagewidth'] ) {
						$code .= ' width="' . $atts['imagewidth'] . '"';
					}
					$code .= ' class="ppalbtn">';
				} else {
					$code .= '<input type="hidden" src="https://www.paypal.com/en_AU/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="' . esc_html__( 'PayPal - The safer, easier way to pay online.', 'learndash' ) . '" class="ppalbtn">';
				}
				$code .= '<button aria-label="' . $button_aria_label . '" type="submit" class="btn-join' . ( $registration_variation !== $variation_classic ? ' ld--ignore-inline-css' : '' ) . '" id="btn-join">';
				$code .= esc_html( $button_text );
				$code .= '</button>';

				$code .= '<img alt="" border="0" src="https://www.paypal.com/en_AU/i/scr/pixel.gif" width="1" height="1" class="ppalholder">
       </form></div>';
				break;

			case 'addtocart':
				$code = '<div style="';
				if ( $atts['textalign'] ) {
					$code .= 'text-align: ' . $atts['textalign'] . ';';
				}

				if ( $atts['divwidth'] > 0 ) {
					$code .= 'width: ' . $atts['divwidth'] . ';';
				}

				if ( $atts['float'] ) {
					$code .= 'float: ' . $atts['float'] . ';';
				}

				if ( $atts['marginleft'] > -1 ) {
					$code .= 'margin-left: ' . $atts['marginleft'] . ';';
				}

				if ( $atts['marginright'] > -1 ) {
					$code .= 'margin-right: ' . $atts['marginright'] . ';';
				}

				if ( $atts['margintop'] > -1 ) {
					$code .= 'margin-top: ' . $atts['margintop'] . ';';
				}

				if ( $atts['marginbottom'] > -1 ) {
					$code .= 'margin-bottom: ' . $atts['marginbottom'] . ';';
				}
				$code .= '"><form class="learndash-payment-gateway-form-paypal" target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_cart">
		<input type="hidden" name="bn" value="PP-ShopCartBF:btn_cart_LG.gif:NonHosted">
		<input type="hidden" name="add" value="1">';
				if ( $atts['display'] == 1 ) {
					$code .= '<input type="hidden" name="display" value="1">';
				}
				$code .= '<input type="hidden" name="business" value="' . $atts['email'] . '">
		<input type="hidden" name="lc" value="' . $atts['lc'] . '">
		<input type="hidden" name="currency_code" value="' . $atts['currencycode'] . '">
		<input type="hidden" name="item_number" value="' . $atts['itemno'] . '">
		<input type="hidden" name="item_name" value="' . $atts['name'] . '">';
				if ( $atts['amount'] ) {
					$code .= '<input type="hidden" name="amount" value="' . $atts['amount'] . '">';
				}
				$code .= '<input type="hidden" name="button_subtype" value="products">';

				if ( $atts['noshipping'] > -1 ) {
					$code .= '<input type="hidden" name="no_shipping" value="' . $atts['noshipping'] . '">';
				}

				if ( $atts['nonote'] > -1 ) {
					$code .= '
            <input type="hidden" name="no_note" value="' . $atts['nonote'] . '" />';
				}

				if ( $atts['rm'] > -1 ) {
					$code .= '<input type="hidden" name="rm" value="' . $atts['rm'] . '">';
				}

				// Add Quantity
				if ( $atts['qty'] == 'ask' ) {
					$code .= '<input type="hidden" name="undefined_quantity" value="1">';
				} else {
					$code .= '<input type="hidden" name="quantity" value="' . $atts['qty'] . '">';
				}

				// Add Shipping
				if ( $atts['shipping'] ) {
					$code .= '<input type="hidden" name="shipping" value="' . $atts['shipping'] . '">';
				}

				// Add Shipping2 - additional items shipping
				if ( $atts['shipping2'] ) {
					$code .= '<input type="hidden" name="shipping2" value="' . $atts['shipping2'] . '">';
				}

				// Add return to merchant text
				if ( $atts['cbt'] ) {
					$code .= '<input type="hidden" name="cbt" value="' . $atts['cbt'] . '">';
				}

				// Add Cancel Return URL
				if ( $atts['cancelreturn'] ) {
					$code .= '<input type="hidden" name="cancel_return" value="' . $atts['cancelreturn'] . '">';
				}

				// Add Special Instructions
				if ( $atts['cn'] ) {
					$code .= '<input type="hidden" name="cn" value="' . $atts['cn'] . '">';
				}

				// Add Page Style
				if ( $atts['pagestyle'] ) {
					$code .= '<input type="hidden" name="page_style" value="' . $atts['pagestyle'] . '">';
				}
				if ( $atts['notifyurl'] ) {
					$code .= '<input type="hidden" name="notify_url" value="' . $atts['notifyurl'] . '">';
				}

				if ( $atts['notifyurl2'] ) {
					$code .= '<input type="hidden" name="notify_url" value="' . $atts['notifyurl2'] . '">';
				}

				if ( $atts['returnurl'] ) {
					$code .= '<input type="hidden" name="return" value="' . $atts['returnurl'] . '">';
				}

				if ( $atts['cancelurl'] ) {
					$code .= '<input type="hidden" name="cancel_return" value="' . $atts['cancelurl'] . '">';
				}

				if ( $atts['scriptcode'] ) {
					$code .= '<script src="' . $atts['scriptcode'] . '" type="text/javascript"></script>';
				}

				// Define Image to Use
				if ( $atts['imageurl'] ) {
					$code .= '<input type="hidden" src="' . $atts['imageurl'] . '" border="0" name="submit" alt="' . ALT_ADD . '"';
					if ( $atts['imagewidth'] ) {
						 $code .= ' width="' . $atts['imagewidth'] . '"';
					}
					$code .= ' class="ppalbtn">';
				} else {
					$code .= '<input type="hidden" src="https://www.paypalobjects.com/en_AU/i/btn/btn_cart_LG.gif" border="0" name="submit" alt="' . ALT_ADD . '" class="ppalbtn">';
				}
				$code .= '<button aria-label="' . $button_aria_label . '" type="submit" class="btn-join' . ( $registration_variation !== $variation_classic ? ' ld--ignore-inline-css' : '' ) . '" id="btn-join">';
				$code .= esc_html( $button_text );
				$code .= '</button>';

				$code .= '<img alt="" border="0" src="https://www.paypal.com/en_AU/i/scr/pixel.gif" width="1" height="1" class="ppalholder">
       </form></div>';

endswitch;
		/**
		 * Filters paypal payment button HTML output.
		 *
		 * @param string $paypal_button The HTML output of paypal button
		 * @param array  $button_data   An array of paypal payment button data like output and attributes.
		 */
		return apply_filters(
			'learndash_paypal_payment_button',
			$code,
			array(
				'code' => $code,
				'atts' => $atts,
			)
		);
	}
}

add_shortcode( 'paypal', 'enhanced_paypal_shortcode' );
