<?php
/**
 * WooCommerce Payment Gateway Framework
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the plugin to newer
 * versions in the future. If you wish to customize the plugin for your
 * needs please refer to http://www.skyverge.com
 *
 * @package   SkyVerge/WooCommerce/Payment-Gateway/Classes
 * @author    SkyVerge
 * @copyright Copyright (c) 2013-2024, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace SkyVerge\WooCommerce\PluginFramework\v5_15_8;

use SkyVerge\WooCommerce\PluginFramework\v5_15_8\Blocks\Blocks_Handler;
use SkyVerge\WooCommerce\PluginFramework\v5_15_8\Enums\PaymentFormContext;
use SkyVerge\WooCommerce\PluginFramework\v5_15_8\Payment_Gateway\Blocks\Gateway_Checkout_Block_Integration;

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( '\\SkyVerge\\WooCommerce\\PluginFramework\\v5_15_8\\SV_WC_Payment_Gateway_Payment_Form' ) ) :


/**
 * Payment Form Class
 *
 * Handles rendering the payment form for both credit card and eCheck gateways
 *
 * @since 4.0.0
 */
#[\AllowDynamicProperties]
class SV_WC_Payment_Gateway_Payment_Form extends Handlers\Script_Handler {


	/** @var \SV_WC_Payment_Gateway gateway for this payment form */
	protected $gateway;

	protected PaymentFormContextChecker $paymentFormContextChecker;

	/** @var array of SV_WC_Payment_Gateway_Payment_Tokens, keyed by token ID */
	protected $tokens;

	/** @var bool default to show new payment method form */
	protected $default_new_payment_method = true;

	/** @var string JS handler base class name, without the FW version */
	protected $js_handler_base_class_name = 'SV_WC_Payment_Form_Handler';

	/** @var bool memoization to account whether the payment JS has been rendered for a gateway */
	protected $payment_form_js_rendered = [];


	/**
	 * Sets up class.
	 *
	 * @since 4.0.0
	 *
	 * @param SV_WC_Payment_Gateway|SV_WC_Payment_Gateway_Direct $gateway gateway for form
	 */
	public function __construct( $gateway ) {

		$this->gateway = $gateway;
		$this->paymentFormContextChecker = new PaymentFormContextChecker($this->gateway->get_id());

		parent::__construct();
	}


	/**
	 * Adds hooks for rendering the payment form.
	 *
	 * @see SV_WC_Payment_Gateway_Payment_Form::render()
	 *
	 * @since 4.0.0
	 */
	protected function add_hooks() {

		parent::add_hooks();

		$gateway_id = $this->get_gateway()->get_id();

		// payment form description
		add_action( "wc_{$gateway_id}_payment_form_start", array( $this, 'render_payment_form_description' ), 15 );

		// saved payment methods
		add_action( "wc_{$gateway_id}_payment_form_start", array( $this, 'render_saved_payment_methods' ), 20 );

		// sample eCheck image (if eCheck gateway)
		add_action( "wc_{$gateway_id}_payment_form_start", array( $this, 'render_sample_check' ), 25 );

		// fieldset start
		add_action( "wc_{$gateway_id}_payment_form_start", array( $this, 'render_fieldset_start' ), 30 );

		// payment fields
		add_action( "wc_{$gateway_id}_payment_form",       array( $this, 'render_payment_fields' ), 0 );

		// fieldset end
		add_action( "wc_{$gateway_id}_payment_form_end",   array( $this, 'render_fieldset_end' ), 5 );

		// payment form JS
		add_action( "wc_{$gateway_id}_payment_form_end", [ $this, 'render_js' ], 5 );
		add_action( 'wp_footer', [ $this, 'maybe_render_js' ] );
	}


	/**
	 * Gets the script ID.
	 *
	 * @since 5.7.0
	 *
	 * @return string
	 */
	public function get_id() {

		return $this->get_gateway()->get_id() . '_payment_form';
	}


	/**
	 * Gets the script ID, dasherized.
	 *
	 * @since 5.7.0
	 *
	 * @return string
	 */
	public function get_id_dasherized() {

		return $this->get_gateway()->get_id_dasherized() . '-payment-form';
	}


	/**
	 * Returns the active tokens for the current user/gateway
	 *
	 * @since 4.0.0
	 * @return array of SV_WC_Payment_Gateway_Payment_Tokens, keyed by token ID
	 */
	protected function get_tokens() {

		if ( ! empty( $this->tokens ) ) {
			return $this->tokens;
		}

		$tokens = array();

		if ( $this->tokenization_allowed() && is_user_logged_in() ) {

			foreach ( $this->get_gateway()->get_payment_tokens_handler()->get_tokens( get_current_user_id() ) as $token ) {

				// some gateways return all tokens for each gateway, so ensure the token type matches the gateway type
				if ( ( $this->get_gateway()->is_credit_card_gateway() && $token->is_echeck() ) || ( $this->get_gateway()->is_echeck_gateway() && $token->is_credit_card() ) ) {
					continue;
				}

				// set token
				$tokens[ $token->get_id() ] = $token;

				// don't force new payment method if an existing token is default
				if ( $token->is_default() ) {
					$this->default_new_payment_method = false;
				}
			}
		}

		return $this->tokens = $tokens;
	}


	/**
	 * Returns the gateway for this form.
	 *
	 * @since 4.0.0
	 *
	 * @return SV_WC_Payment_Gateway|SV_WC_Payment_Gateway_Direct
	 */
	public function get_gateway() {

		return $this->gateway;
	}


	/**
	 * Return true if the current user has active tokens to display
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	public function has_tokens() {
		return ! empty( $this->tokens );
	}


	/**
	 * Returns true if tokenization is allowed for the payment form. This is true
	 * if both the gateway supports tokenization and it is enabled.
	 *
	 * Note that tokenization is not allowed on the pay page for guest customers,
	 * as there is no way to create an account there.
	 *
	 * @since 4.0.0
	 * @return bool true if tokenization is allowed
	 */
	public function tokenization_allowed() {

		// tokenization is allowed if tokenization is enabled on the gateway
		$tokenization_allowed = $this->get_gateway()->supports_tokenization() && $this->get_gateway()->tokenization_enabled();

		if ( $tokenization_allowed && ! is_user_logged_in() ) {

			if ( is_checkout_pay_page() ) {

				// on the pay page there is no way of creating an account, so disallow tokenization for guest customers
				$tokenization_allowed = false;

			} else if ( is_checkout() ) {

				// on the checkout page, only allow if account creation during checkout is enabled
				$tokenization_allowed = WC()->checkout()->is_registration_enabled();

			}
		}

		/**
		 * Payment Gateway Payment Form Tokenization Allowed.
		 *
		 * Filters whether tokenization is allowed for the payment form.
		 *
		 * @since 4.0.0
		 *
		 * @param bool $tokenization_allowed
		 * @param SV_WC_Payment_Gateway_Payment_Form $this payment form instance
		 */
		return apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_payment_form_tokenization_allowed', $tokenization_allowed, $this );
	}


	/**
	 * Returns true if tokenization is forced for the payment form. This is generally
	 * true when a subscription or pre-order is in the cart and the payment
	 * method must be tokenized.
	 *
	 * Note that only direct gateways support forced tokenization
	 *
	 * @since 4.0.0
	 * @return bool true if tokenization is forced
	 */
	public function tokenization_forced() {

		$tokenization_forced = $this->get_gateway()->is_direct_gateway() && $this->get_gateway()->supports_tokenization() && $this->get_gateway()->get_payment_tokens_handler()->tokenization_forced();

		// tokenization always "forced" on the add new payment method page
		if ( $this->get_gateway()->is_direct_gateway() && $this->get_gateway()->supports_add_payment_method() && is_add_payment_method_page() ) {
			$tokenization_forced = true;
		}

		/**
		 * Payment Gateway Payment Form Tokenization Forced.
		 *
		 * Filters whether tokenization is forced for the payment form.
		 *
		 * @since 4.0.0
		 *
		 * @param bool $tokenization_forced
		 * @param SV_WC_Payment_Gateway_Payment_Form $this payment form instance
		 */
		return apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_payment_form_tokenization_forced', $tokenization_forced, $this );
	}


	/**
	 * Return true if the payment form should default to showing the new payment
	 * method form
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	public function default_new_payment_method() {

		return $this->default_new_payment_method;
	}


	/**
	 * Gets the payment form fields.
	 *
	 * @since 4.0.0
	 *
	 * @return array<string, mixed> payment fields in format suitable for {@see woocommerce_form_field()}
	 */
	protected function get_payment_fields() : array {

		$gateway = $this->get_gateway();

		switch ( $gateway->get_payment_type() ) {

			case 'credit-card':
				$fields = $this->get_credit_card_fields();
			break;

			case 'echeck':
				$fields = $this->get_echeck_fields();
			break;

			default:
				$fields = [];
			break;
		}

		/**
		 * Overrideable hidden field to post a flag that the payment originated from the legacy shortcode checkout form.
		 * This is helpful in some gateways that need different handling between blocks checkout and legacy shortcode checkout.
		 * @see Gateway_Checkout_Block_Integration::prepare_payment_data()
		 */
		$fields = wp_parse_args( $fields, [
			'context' => [
				'type'  => 'hidden',
				'id'    => 'wc-' . $gateway->get_id_dasherized() . '-context',
				'name'  => 'wc-' . $gateway->get_id_dasherized() . '-context',
				'value' => $gateway::PROCESSING_CONTEXT_SHORTCODE,
			],
		] );

		/**
		 * Payment Gateway Payment Form Default Payment Fields filter.
		 *
		 * Filters the default field data for a gateway:
		 * @see SV_WC_Payment_Gateway_Payment_Form::get_credit_card_fields()
		 * @see SV_WC_Payment_Gateway_Payment_Form::get_echeck_fields()
		 *
		 * This filter can be used to return payment fields for a non-standard payment type (like PayPal Express).
		 *
		 * @since 4.0.0
		 *
		 * @param array<string, mixed> $fields in the format supported by {@see woocommerce_form_field()}
		 * @param SV_WC_Payment_Gateway_Payment_Form $payment_form payment form instance
		 */
		return (array) apply_filters( 'wc_' . $gateway->get_id() . '_payment_form_default_payment_form_fields', $fields, $this );
	}


	/**
	 * Get default credit card form fields, note this pulls default values
	 * from the associated gateway
	 *
	 * for an explanation of autocomplete attribute values, see:
	 * @link https://html.spec.whatwg.org/multipage/forms.html#autofill
	 *
	 * @since 4.0.0
	 * @return array credit card form fields
	 */
	protected function get_credit_card_fields() {

		$defaults = $this->get_gateway()->get_payment_method_defaults();

		$fields = [
			'card-number' => [
				'type'              => 'tel',
				'label'             => esc_html__( 'Card Number', 'woocommerce-plugin-framework' ),
				'id'                => 'wc-' . $this->get_gateway()->get_id_dasherized() . '-account-number',
				'name'              => 'wc-' . $this->get_gateway()->get_id_dasherized() . '-account-number',
				'placeholder'       => '•••• •••• •••• ••••',
				'required'          => true,
				'class'             => [ 'form-row-wide' ],
				'input_class'       => [ 'js-sv-wc-payment-gateway-credit-card-form-input js-sv-wc-payment-gateway-credit-card-form-account-number' ],
				'maxlength'         => 20,
				'custom_attributes' => [
					'autocomplete'   => 'cc-number',
					'autocorrect'    => 'no',
					'autocapitalize' => 'no',
					'spellcheck'     => 'no',
				],
				'value' => $defaults['account-number'],
			],
			'card-expiry' => [
				'type'              => 'tel',
				'label'             => esc_html__( 'Expiration (MM/YY)', 'woocommerce-plugin-framework' ),
				'id'                => 'wc-' . $this->get_gateway()->get_id_dasherized() . '-expiry',
				'name'              => 'wc-' . $this->get_gateway()->get_id_dasherized() . '-expiry',
				'placeholder'       => esc_html__( 'MM / YY', 'woocommerce-plugin-framework' ),
				'required'          => true,
				'class'             => [ 'form-row-first' ],
				'input_class'       => [ 'js-sv-wc-payment-gateway-credit-card-form-input js-sv-wc-payment-gateway-credit-card-form-expiry' ],
				'custom_attributes' => [
					'autocomplete'   => 'cc-exp',
					'autocorrect'    => 'no',
					'autocapitalize' => 'no',
					'spellcheck'     => 'no',
					'maxlength'      => 7, // the spaces before and after the slash are counted
				],
				'value' => $defaults['expiry'],
			],
		];

		if ( $this->get_gateway()->csc_enabled() ) {

			$fields['card-csc'] = [
				'type'              => 'tel',
				'label'             => esc_html__( 'Card Security Code', 'woocommerce-plugin-framework' ),
				'id'                => 'wc-' . $this->get_gateway()->get_id_dasherized() . '-csc',
				'name'              => 'wc-' . $this->get_gateway()->get_id_dasherized() . '-csc',
				'placeholder'       => esc_html__( 'CSC', 'woocommerce-plugin-framework' ),
				'required'          => true,
				'class'             => [ 'form-row-last' ],
				'input_class'       => [ 'js-sv-wc-payment-gateway-credit-card-form-input js-sv-wc-payment-gateway-credit-card-form-csc' ],
				'maxlength'         => 4,
				'custom_attributes' => [
					'autocomplete'   => 'off',
					'autocorrect'    => 'no',
					'autocapitalize' => 'no',
					'spellcheck'     => 'no',
				],
				'value' => $defaults['csc'],
			];
		}

		/**
		 * Payment Gateway Payment Form Default Credit Card Fields.
		 *
		 * Filters the default field data for credit card gateways.
		 *
		 * @since 4.0.0
		 *
		 * @param array $fields in the format supported by woocommerce_form_fields()
		 * @param SV_WC_Payment_Gateway_Payment_Form $this payment form instance
		 */
		return apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_payment_form_default_credit_card_fields', $fields, $this );
	}


	/**
	 * Get default eCheck form fields, note this pulls default values
	 * from the associated gateway
	 *
	 * @since 4.0.0
	 * @return array eCheck form fields
	 */
	protected function get_echeck_fields() {

		$defaults = $this->get_gateway()->get_payment_method_defaults();

		$check_hint = sprintf( '<img title="%s" alt="%s" class="js-sv-wc-payment-gateway-echeck-form-check-hint" src="%s" width="16" height="16" />', esc_attr__( 'Where do I find this?', 'woocommerce-plugin-framework' ), esc_attr__( 'Where do I find this?', 'woocommerce-plugin-framework' ), esc_url( WC()->plugin_url() . '/assets/images/help.png' ) );

		$fields = array(
			'routing-number' => array(
				'type'              => 'tel',
				/* translators: e-check routing number, HTML form field label, https://en.wikipedia.org/wiki/Routing_transit_number */
				'label'             => esc_html__( 'Routing Number', 'woocommerce-plugin-framework' ) . $check_hint,
				'id'                => 'wc-' . $this->get_gateway()->get_id_dasherized() . '-routing-number',
				'name'              => 'wc-' . $this->get_gateway()->get_id_dasherized() . '-routing-number',
				'placeholder'       => '•••••••••',
				'required'          => true,
				'class'             => array( 'form-row-first' ),
				'input_class'       => array( 'js-sv-wc-payment-gateway-echeck-form-input js-sv-wc-payment-gateway-echeck-form-routing-number' ),
				'maxlength'         => 9,
				'custom_attributes' => array(
					'autocomplete'   => 'off',
					'autocorrect'    => 'no',
					'autocapitalize' => 'no',
					'spellcheck'     => 'no',
				),
				'value'             => $defaults['routing-number'],
			),
			'account-number' => array(
				'type'              => 'tel',
				/* translators: e-check account number, HTML form field label */
				'label'             => esc_html__( 'Account Number', 'woocommerce-plugin-framework' ) . $check_hint,
				'id'                => 'wc-' . $this->get_gateway()->get_id_dasherized() . '-account-number',
				'name'              => 'wc-' . $this->get_gateway()->get_id_dasherized() . '-account-number',
				'required'          => true,
				'class'             => array( 'form-row-last' ),
				'input_class'       => array( 'js-sv-wc-payment-gateway-echeck-form-input js-sv-wc-payment-gateway-echeck-form-account-number' ),
				'maxlength'         => 17,
				'custom_attributes' => array(
					'autocomplete'   => 'off',
					'autocorrect'    => 'no',
					'autocapitalize' => 'no',
					'spellcheck'     => 'no',
				),
				'value'             => $defaults['account-number'],
			),
			'account-type'   => array(
				'type'              => 'select',
				/* translators: e-check account type, HTML form field label */
				'label'             => esc_html__( 'Account Type', 'woocommerce-plugin-framework' ),
				'id'                => 'wc-' . $this->get_gateway()->get_id_dasherized() . '-account-type',
				'name'              => 'wc-' . $this->get_gateway()->get_id_dasherized() . '-account-type',
				'required'          => true,
				'class'             => array( 'form-row-wide' ),
				'input_class'       => array( 'js-sv-wc-payment-gateway-echeck-form-input js-sv-wc-payment-gateway-echeck-form-account-type' ),
				'options'           => array(
					/* translators: http://www.investopedia.com/terms/c/checkingaccount.asp  */
					'checking' => esc_html_x( 'Checking', 'account type', 'woocommerce-plugin-framework' ),
					/* translators: http://www.investopedia.com/terms/s/savingsaccount.asp  */
					'savings'  => esc_html_x( 'Savings',  'account type', 'woocommerce-plugin-framework' ),
				),
				'custom_attributes' => array(),
				'value'             => 'checking',
			),
		);

		/**
		 * Payment Gateway Payment Form Default eCheck Fields.
		 *
		 * Filters the default field data for eCheck gateways.
		 *
		 * @since 4.0.0
		 *
		 * @param array $fields in the format supported by woocommerce_form_fields()
		 * @param SV_WC_Payment_Gateway_Payment_Form $this payment form instance
		 */
		return apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_payment_form_default_echeck_fields', $fields, $this );
	}


	/**
	 * Get the payment form description HTML, generally set by the admin in
	 * the gateway settings
	 *
	 * @since 4.0.0
	 * @return string payment form description HTML
	 */
	public function get_payment_form_description_html() {

		$description = '';

		if ( $this->get_gateway()->get_description() ) {
			$description .= '<p>' . wp_kses_post( $this->get_gateway()->get_description() ) . '</p>';
		}

		if ( $this->get_gateway()->is_test_environment() ) {
			/* translators: Test mode refers to the current software environment */
			echo '<p>' . esc_html__( 'TEST MODE ENABLED', 'woocommerce-plugin-framework' ) . '</p>';
		}

		/**
		 * Payment Gateway Payment Form Description.
		 *
		 * Filters the HTML rendered for payment form description.
		 *
		 * @since 4.0.0
		 *
		 * @param string $html
		 * @param SV_WC_Payment_Gateway_Payment_Form $this payment form instance
		 */
		return apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_payment_form_description', $description, $this );
	}


	/**
	 * Get the sample check image HTML
	 *
	 * @since 4.0.0
	 * @return string sample check image HTML
	 */
	protected function get_sample_check_html() {

		$image_url = \WC_HTTPS::force_https_url( $this->get_gateway()->get_plugin()->get_payment_gateway_framework_assets_url() . '/images/sample-check.png' );

		$html = sprintf( '<div class="js-sv-wc-payment-gateway-echeck-form-sample-check" style="display: none;"><img width="541" height="270" src="%s" alt="%s" /></div>', esc_url( $image_url ), esc_attr_x( 'Sample Check', 'Bank check (noun)', 'woocommerce-plugin-framework' ) );

		/**
		 * Payment Gateway Payment Form Sample eCheck HTML.
		 *
		 * Filters the HTML rendered for the same eCheck image.
		 *
		 * @since 4.0.0
		 *
		 * @param string $html
		 * @param SV_WC_Payment_Gateway_Payment_Form $this payment form instance
		 */
		return apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_payment_form_sample_check_html', $html, $this );
	}


	/**
	 * Get the saved payment methods HTML, this section includes the
	 * "Manage Payment Method" button, the radio inputs for selecting an existing saved
	 * payment method, and the radio input for using a new saved payment method
	 *
	 * @since 4.0.0
	 * @return string saved payment methods HTML
	 */
	protected function get_saved_payment_methods_html() {

		$html = '<p class="form-row form-row-wide">';

		$html .= $this->get_manage_payment_methods_button_html();

		foreach ( $this->get_tokens() as $token ) {

			$html .= $this->get_saved_payment_method_html( $token );
		}

		$html .= $this->get_use_new_payment_method_input_html();

		$html .= '</p><div class="clear"></div>';

		/**
		 * Payment Gateway Payment Form Saved Payment Methods HTML.
		 *
		 * Filters the HTML rendered for the entire saved payment methods section.
		 *
		 * @since 4.0.0
		 *
		 * @param string $html
		 * @param SV_WC_Payment_Gateway_Payment_Form $this payment form instance
		 */
		return apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_payment_form_saved_payment_methods_html', $html, $this );
	}


	/**
	 * Get the "Manage Payment Methods" button HTML
	 *
	 * @since 4.0.0
	 * @return string manage payment methods button html
	 */
	protected function get_manage_payment_methods_button_html() {

		$url = wc_get_account_endpoint_url( 'payment-methods' );

		/**
		 * Payment Form Manage Payment Methods Button Text Filter.
		 *
		 * Allow actors to modify the "manage payment methods" button text rendered
		 * on the checkout page.
		 *
		 * @since 4.0.0
		 * @param string $button_text button text
		 */
		$html = sprintf( '<a class="button sv-wc-payment-gateway-payment-form-manage-payment-methods" href="%s">%s</a>',
			esc_url( $url ),
			/* translators: Payment method as in a specific credit card, eCheck or bank account */
			wp_kses_post( apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_manage_payment_methods_text', esc_html__( 'Manage Payment Methods', 'woocommerce-plugin-framework' ) ) )
		);

		/**
		 * Payment Gateway Payment Form Manage Payment Methods Button HTML.
		 *
		 * Filters the HTML rendered for the "Manage Payment Methods" button.
		 *
		 * @since 4.0.0
		 *
		 * @param string $html
		 * @param SV_WC_Payment_Gateway_Payment_Form $this payment form instance
		 */
		return apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_payment_form_manage_payment_methods_button_html', $html, $this );
	}


	/**
	 * Get the saved payment method HTML for an given token, renders an input + label like:
	 *
	 * o <Amex logo> American Express ending in 6666 (expires 10/20)
	 *
	 * @since 4.0.0
	 * @param SV_WC_Payment_Gateway_Payment_Token $token payment token
	 * @return string saved payment method HTML
	 */
	protected function get_saved_payment_method_html( $token ) {

		// input
		$html = sprintf( '<input type="radio" id="wc-%1$s-payment-token-%2$s" name="wc-%1$s-payment-token" class="js-sv-wc-payment-gateway-payment-token js-wc-%1$s-payment-token" style="width:auto; margin-right:.5em;" value="%2$s" %3$s/>',
			esc_attr( $this->get_gateway()->get_id_dasherized() ),
			esc_attr( $token->get_id() ),
			checked( $token->is_default(), true, false )
		);

		// label
		$html .= sprintf( '<label class="sv-wc-payment-gateway-payment-form-saved-payment-method" for="wc-%s-payment-token-%s">', esc_attr( $this->get_gateway()->get_id_dasherized() ), esc_attr( $token->get_id() ) );

		// title
		$html .= $this->get_saved_payment_method_title( $token );

		$html .= '</label><br />';

		/**
		 * Payment Gateway Payment Form Payment Method Title HTML.
		 *
		 * Filters the HTML rendered for a saved payment method, like "Amex ending in 6666".
		 *
		 * @since 4.0.0
		 *
		 * @param string $html
		 * @param SV_WC_Payment_Gateway_Payment_Form $this payment form instance
		 */
		return apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_payment_form_payment_method_html', $html, $token, $this );
	}


	/**
	 * Get the title for a saved payment method, like
	 *
	 * <Amex logo> American Express ending in 6666 (expires 10/20)
	 *
	 * @since 4.0.0
	 * @param SV_WC_Payment_Gateway_Payment_Token $token payment token
	 * @return string saved payment method title
	 */
	protected function get_saved_payment_method_title( $token ) {

		$image_url = $token->get_image_url();
		$last_four = $token->get_last_four();
		$type      = $token->get_type_full();

		$title = '<span class="title">';

		if ( $token->get_nickname() ) {
			$title .= '<span class="nickname">' . $token->get_nickname() . '</span>';
		}

		if ( $image_url ) {

			// format like "<Amex logo image> American Express"
			$title .= sprintf( '<img src="%1$s" alt="%2$s" title="%2$s" width="30" height="20" style="width: 30px; height: 20px;" />', esc_url( $image_url ), esc_attr( $type ) );

		} else {

			// missing payment method image, format like "American Express"
			$title .= esc_html( $type );
		}

		// add "ending in XXXX" if available
		if ( $last_four ) {
			$title .= '&bull; &bull; &bull; ' . esc_html( $last_four );
		}

		// add "(expires MM/YY)" if available
		if ( $token->get_exp_month() && $token->get_exp_year() ) {

			/* translators: Placeholders: %s - expiry date */
			$title .= ' ' . sprintf( esc_html__( '(expires %s)', 'woocommerce-plugin-framework' ), $token->get_exp_date() );
		}

		$title .= '</span>';

		/**
		 * Payment Gateway Payment Form Payment Method Title.
		 *
		 * Filters the text/HTML rendered for a saved payment method, like "Amex ending in 6666".
		 *
		 * @since 4.0.0
		 *
		 * @param string $title
		 * @param SV_WC_Payment_Gateway_Payment_Token $token
		 * @param SV_WC_Payment_Gateway_Payment_Form $this payment form instance
		 */
		return apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_payment_form_payment_method_title', $title, $token, $this );
	}


	/**
	 * Get the "Use new payment method" radio input HTML, like
	 *
	 * o Use new <card>|<bank account>
	 *
	 * @since 4.0.0
	 * @return string saved payment method title
	 */
	protected function get_use_new_payment_method_input_html() {

		// input
		$html = sprintf( '<input type="radio" id="wc-%1$s-use-new-payment-method" name="wc-%1$s-payment-token" class="js-sv-wc-payment-token js-wc-%1$s-payment-token" style="width:auto; margin-right: .5em;" value="" %2$s />',
			esc_attr( $this->get_gateway()->get_id_dasherized() ),
			checked( $this->default_new_payment_method(), true, false )
		);

		// label
		$html .= sprintf( '<label style="display:inline;" for="wc-%s-use-new-payment-method">%s</label>',
			esc_attr( $this->get_gateway()->get_id_dasherized() ),
			$this->get_gateway()->is_credit_card_gateway() ? esc_html__( 'Use a new card', 'woocommerce-plugin-framework' ) : esc_html__( 'Use a new bank account', 'woocommerce-plugin-framework' )
		);

		/**
		 * Payment Gateway Payment Form New Payment Method Input HTML.
		 *
		 * Filters the HTML rendered for the "Use a new card" radio button.
		 *
		 * @since 4.0.0
		 * @param string $html
		 * @param SV_WC_Payment_Gateway_Payment_Form $this payment form instance
		 */
		return apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_payment_form_new_payment_method_input_html', $html, $this );
	}


	/**
	 * Get saved payment method checkbox HTML, like:
	 *
	 * [] Securely Save to Account
	 *
	 * @since 4.0.0
	 * @return string save payment method checkbox HTML
	 */
	protected function get_save_payment_method_checkbox_html() {

		$html = '';

		if ( $this->tokenization_allowed() || $this->tokenization_forced() ) {

			if ( $this->tokenization_forced() ) {

				$html .= sprintf( '<input name="wc-%1$s-tokenize-payment-method" id="wc-%1$s-tokenize-payment-method" type="hidden" value="true" />', $this->get_gateway()->get_id_dasherized() );

			} else {

				$html .= '<p class="form-row">';

				/**
				 * Payment Form Default Tokenize Payment Method Checkbox to Checked Filter.
				 *
				 * Allow actors to default the tokenize payment method checkbox state to checked.
				 *
				 * @since 4.2.0
				 *
				 * @param bool $checked default false, set to true to change the checkbox state to checked
				 * @param SV_WC_Payment_Gateway_Payment_Form $this payment form instance
				 */
				$checked = apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_default_tokenize_payment_method_checkbox_to_checked', false, $this );

				$html .= sprintf( '<input name="wc-%1$s-tokenize-payment-method" id="wc-%1$s-tokenize-payment-method" class="js-sv-wc-tokenize-payment method js-wc-%1$s-tokenize-payment-method" type="checkbox" value="true" style="width:auto;" %2$s/>', $this->get_gateway()->get_id_dasherized(), $checked ? 'checked="checked" ' : '' );

				/**
				 * Payment Form Tokenize Payment Method Checkbox Text Filter.
				 *
				 * Allow actors to modify the "securely save to account" checkbox
				 * text rendered on the payment form on the checkout page.
				 *
				 * @since 4.0.0
				 *
				 * @param string $checkbox_text checkbox text
				 */
				/* translators: account as in customer's account on the eCommerce site */
				$html .= sprintf( '<label for="wc-%s-tokenize-payment-method" style="display:inline;">%s</label>', $this->get_gateway()->get_id_dasherized(), apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_tokenize_payment_method_text', esc_html__( 'Securely Save to Account', 'woocommerce-plugin-framework' ) ) );
				$html .= '</p><div class="clear"></div>';
			}
		}

		/**
		 * Payment Gateway Payment Form Save Payment Method Checkbox HTML.
		 *
		 * Filters the HTML rendered for the "save payment method" checkbox.
		 *
		 * @since 4.0.0
		 *
		 * @param string $html
		 * @param SV_WC_Payment_Gateway_Payment_Form $this payment form instance
		 */
		return apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_payment_form_save_payment_method_checkbox_html', $html, $this );
	}


	/** Rendering methods *****************************************************/


	/**
	 * Renders the payment form
	 *
	 * @since 4.0.0
	 */
	public function render() {

		// maybe load tokens
		$this->get_tokens();

		/**
		 * Payment Gateway Payment Form Start Action.
		 *
		 * Triggered before the payment fields are rendered.
		 *
		 * @hooked SV_WC_Payment_Gateway_Payment_Form::render_payment_form_description() - 15 (outputs payment form description HTML)
		 * @hooked SV_WC_Payment_Gateway_Payment_Form::render_saved_payment_methods() - 20 (outputs saved payment method fields)
		 * @hooked SV_WC_Payment_Gateway_Payment_Form::render_sample_check() - 25 (outputs sample check div if eCheck gateway)
		 * @hooked SV_WC_Payment_Gateway_Payment_Form::render_fieldset_start() - 30 (outputs opening fieldset tag and starting payment fields div)
		 *
		 * @since 4.0.0
		 *
		 * @param SV_WC_Payment_Gateway_Payment_Form $this payment form instance
		 */
		do_action( 'wc_' . $this->get_gateway()->get_id() . '_payment_form_start', $this );


		/**
		 * Payment Gateway Payment Form Action.
		 *
		 * Triggered for the payment fields.
		 *
		 * @hooked SV_WC_Payment_Gateway_Payment_Form::render_payment_fields() - 0 (outputs payment fields like account number, expiry, etc)
		 *
		 * @since 4.0.0
		 *
		 * @param SV_WC_Payment_Gateway_Payment_Form $this payment form instance
		 */
		do_action( 'wc_' . $this->get_gateway()->get_id() . '_payment_form', $this );


		/**
		 * Payment Gateway Payment Form End Action.
		 *
		 * Triggered after the payment form fields are rendered.
		 *
		 * @hooked SV_WC_Payment_Gateway_Payment_Form::render_fieldset_end() - 5 (outputs clear div, save payment method checkbox, and closing fieldset tag)
		 * @hooked SV_WC_Payment_Gateway_Payment_Form::render_js() - 5 (outputs JS for instantiating payment form JS class)
		 *
		 * @since 4.0.0
		 *
		 * @param SV_WC_Payment_Gateway_Payment_Form $this payment form instance
		 */
		do_action( 'wc_' . $this->get_gateway()->get_id() . '_payment_form_end', $this );
	}


	/**
	 * Render the payment form description
	 *
	 * @hooked wc_{gateway ID}_payment_form_start @ priority 15
	 *
	 * @since 4.0.0
	 */
	public function render_payment_form_description() {

		echo $this->get_payment_form_description_html();
	}


	/**
	 * Render the saved payment methods
	 *
	 * @hooked wc_{gateway ID}_payment_form_start @ priority 20
	 *
	 * @since 4.0.0
	 */
	public function render_saved_payment_methods() {

		$is_add_new_payment_method_page = $this->get_gateway()->supports_add_payment_method() && is_add_payment_method_page();

		// tokenization forced check to prevent rendering this on the "add new payment method" screen
		if ( $this->has_tokens() && ! $is_add_new_payment_method_page ) {
			echo $this->get_saved_payment_methods_html();
		}
	}


	/**
	 * Render the sample check image if gateway is eCheck
	 *
	 * @hooked wc_{gateway ID}_payment_form_start @ priority 25
	 *
	 * @since 4.0.0
	 */
	public function render_sample_check() {

		if ( $this->get_gateway()->is_echeck_gateway() ) {
			echo $this->get_sample_check_html();
		}
	}


	/**
	 * Render the payment form opening fieldset tag and div
	 *
	 * @hooked wc_{gateway ID}_payment_form_start @ priority 30
	 *
	 * @since 4.0.0
	 */
	public function render_fieldset_start() {

		printf( '<fieldset id="wc-%s-%s-form" aria-label="%s"><legend style="display:none;">%s</legend>', esc_attr( $this->get_gateway()->get_id_dasherized() ), esc_attr( $this->get_gateway()->get_payment_type() ), esc_attr__( 'Payment Info', 'woocommerce-plugin-framework' ), esc_attr__( 'Payment Info', 'woocommerce-plugin-framework' ) );

		printf( '<div class="wc-%1$s-new-payment-method-form js-wc-%1$s-new-payment-method-form">', esc_attr( $this->get_gateway()->get_id_dasherized() ) );
	}


	/**
	 * Render the payment fields (e.g. account number, expiry, etc)
	 *
	 * @hooked wc_{gateway ID}_payment_form_start @ priority 0
	 *
	 * @since 4.0.0
	 */
	public function render_payment_fields() {

		foreach ( $this->get_payment_fields() as $field ) {
			$this->render_payment_field( $field );
		}

		// set the context for the checkout form in case gateways need to reference this in their validation
		$this->paymentFormContextChecker->maybeSetContext();
	}


	/**
	 * Render the payment, a simple wrapper around woocommerce_form_field() to
	 * make it more convenient for concrete gateways to override form output
	 *
	 * @since 4.1.2
	 * @param array $field
	 */
	protected function render_payment_field( $field ) {

		woocommerce_form_field(
			isset( $field['name'] ) ? $field['name'] : null,
			$field,
			isset( $field['value'] ) ? $field['value'] : null
		);
	}


	/**
	 * Render the payment form closing fieldset tag, clearing div, and "save
	 * payment method" checkbox
	 *
	 * @hooked wc_{gateway ID}_payment_form_end @ priority 5
	 *
	 * @since 4.0.0
	 */
	public function render_fieldset_end() {

		// clear
		echo '<div class="clear"></div>';

		echo $this->get_save_payment_method_checkbox_html() . '</div><!-- ./new-payment-method-form-div -->';

		echo '</fieldset>';
	}


	/**
	 * Maybe renders the payment gateway JS on checkout or pay pages.
	 *
	 * This is hooking directly into `wp_footer` in case the `wc_{$gateway_id}_payment_form_end` didn't trigger already.
	 *
	 * @since 5.10.8
	 */
	public function maybe_render_js() {

		if ( ! is_order_received_page() && ( is_checkout_pay_page() || is_add_payment_method_page() || $this->should_render_js_on_checkout_page() ) ) {
			$this->render_js();
		}
	}


	/**
	 * Determines if it should render the payment form JavaScript in the checkout page.
	 *
	 * @since 5.12.0
	 *
	 * @return bool
	 */
	protected function should_render_js_on_checkout_page() : bool {
		global $post;

		if ( is_checkout() && ! is_checkout_pay_page() && Blocks_Handler::is_checkout_block_in_use() && ( $post && ! Blocks_Handler::page_contains_checkout_shortcode( $post ) ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Renders the payment form JS.
	 *
	 * This is normally hooked to `wc_{$gateway_id}_payment_form_end` with priority 5.
	 * However, in the circumstance this doesn't trigger, {@see SV_WC_Payment_Gateway_Payment_Form::maybe_render_js()} hooked to footer.
	 * This may happen when the customer reaches checkout with a $0 value order.
	 *
	 * @see SV_WC_Payment_Gateway_Payment_Form::get_safe_handler_js()
	 *
	 * @since 4.0.0
	 */
	public function render_js() {

		$gateway_id = $this->get_gateway()->get_id();

		// bail if the payment form JS was already rendered for the current gateway
		if ( in_array( $gateway_id, $this->payment_form_js_rendered, true ) ) {
			return;
		}

		switch ( current_action() ) :
			case 'wp_footer' :
				$this->renderScriptDependencies();
				$this->payment_form_js_rendered[] = $gateway_id;
				?><script type="text/javascript">jQuery(function($){<?php echo $this->get_safe_handler_js(); ?>});</script><?php
			break;
			case "wc_{$gateway_id}_payment_form_end" :
				$this->payment_form_js_rendered[] = $gateway_id;
				wc_enqueue_js( $this->get_safe_handler_js() );
			break;
		endswitch;
	}

	/**
	 * Renders the payment form JS dependencies.
	 *
	 * @see SV_WC_Payment_Gateway_Payment_Form::render_js()
	 *
	 * @since 4.12.6
	 */
	protected function renderScriptDependencies() : void
	{
		if (! $dependencies = $this->getFormScriptDependencies()) {
			return;
		}

		wp_print_scripts($dependencies);
	}

	/**
	 * Gets the payment form JS list of dependencies handles.
	 *
	 * @see SV_WC_Payment_Gateway_Payment_Form::renderScriptDependencies()
	 *
	 * @since 4.12.6
	 *
	 * @return string[]
	 */
	protected function getFormScriptDependencies() : array
	{
		return ['jquery'];
	}

	/**
	 * Gets the handler instantiation JS.
	 *
	 * @since 5.7.0
	 *
	 * @param array $additional_args additional handler arguments, if any
	 * @param string $handler_name handler name, if different from self::get_js_handler_class_name()
	 * @param string $object_name object name, if different from self::get_js_handler_object_name()
	 * @return string
	 */
	protected function get_handler_js( array $additional_args = [], $handler_name = '', $object_name = '' ) {

		$js = parent::get_handler_js( $additional_args, $handler_name, $object_name );

		$js .= 'window.jQuery( document.body ).trigger( "update_checkout" );';

		return $js;
	}


	/**
	 * Gets the JS args for the payment form handler.
	 *
	 * Payment gateways can overwrite this method to define specific args.
	 * render_js() will apply filters to the returned array of args.
	 *
	 * @since 5.7.0
	 *
	 * @return array
	 */
	protected function get_js_handler_args() {

		$args = [
			'plugin_id'               => $this->get_gateway()->get_plugin()->get_id(),
			'id'                      => $this->get_gateway()->get_id(),
			'id_dasherized'           => $this->get_gateway()->get_id_dasherized(),
			'type'                    => $this->get_gateway()->get_payment_type(),
			'csc_required'            => $this->get_gateway()->csc_enabled(),
			'csc_required_for_tokens' => $this->get_gateway()->csc_enabled_for_tokens(),
		];

		if ( $this->get_gateway()->supports_card_types() ) {

			$card_types = $this->get_gateway()->get_card_types();

			if ( is_array( $card_types ) && ! empty( $card_types ) ) {

				$args['enabled_card_types'] = array_map( [ 'SkyVerge\WooCommerce\PluginFramework\v5_15_8\SV_WC_Payment_Gateway_Helper', 'normalize_card_type' ], $card_types );
			}
		}

		return $args;
	}


	/**
	 * Adds a log entry.
	 *
	 * @since 5.7.0
	 *
	 * @param string $message message to log
	 */
	protected function log_event( $message ) {

		$this->get_gateway()->add_debug_message( $message );
	}


	/**
	 * Determines whether logging is enabled.
	 *
	 * @since 5.7.0
	 *
	 * @return bool
	 */
	protected function is_logging_enabled() {

		return $this->get_gateway()->debug_log();
	}


}


endif;
