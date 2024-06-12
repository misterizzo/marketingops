<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       makwebbetter.com
 * @since      1.0.0
 *
 * @package    Hubspot_Integration_Customization
 * @subpackage Hubspot_Integration_Customization/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Hubspot_Integration_Customization
 * @subpackage Hubspot_Integration_Customization/public
 * @author     MakeWebBetter <support@makewebbetter.com>
 */
class Hubspot_Integration_Customization_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function hubwoo_add_abncart_products() {

		// if( isset($_GET['hubwoo-abncart-retrieve']) ) {

		// 	$product_string = $_GET['hubwoo-abncart-retrieve'];
		// 	$seperated_products = explode(',', $product_string);
		// 	if( empty($seperated_products)) { return; } 

		// 	global $woocommerce;
		// 	$woocommerce->cart->empty_cart();

		// 	foreach ($seperated_products as $product ) {
		// 		$pro_qty = array();
		// 		$pro_qty = explode( ':' ,$product);
		// 		$woocommerce->cart->add_to_cart($pro_qty[0], $pro_qty[1] );
		// 	}	
		// }
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_register_script( 'hubwoo_custom_public_script', plugin_dir_url( __FILE__ ) . 'js/class-hubspot-integration-customization-public.js', array('jquery'), $this->version . time(), true );
		wp_localize_script(
			'hubwoo_custom_public_script',
			'hubwooi18n',
			array(
				'ajaxUrl'               => admin_url( 'admin-ajax.php' ),
				'hubwooSecurity'        => wp_create_nonce( 'hubwoo_security' ),
			)
		);
		wp_enqueue_script('hubwoo_custom_public_script');

	}
}
