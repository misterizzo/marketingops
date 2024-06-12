<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       makwebbetter.com
 * @since      1.0.0
 *
 * @package    Hubspot_Integration_Customization
 * @subpackage Hubspot_Integration_Customization/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Hubspot_Integration_Customization
 * @subpackage Hubspot_Integration_Customization/admin
 * @author     MakeWebBetter <support@makewebbetter.com>
 */
class Hubspot_Integration_Customization_Admin {
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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function hubwoo_update_ecomm_deal($order_id) {
		if ( empty( $order_id ) ) {
			return;
		}	
		$order       = wc_get_order( $order_id );

		if ( $order instanceof WC_Order ) {
			$customer_id = $order->get_customer_id();
			if ( ! empty( $customer_id ) ) {
				$user_info  = json_decode( wp_json_encode( get_userdata( $customer_id ) ), true );
				$user_email = $user_info['data']['user_email'];
				$contact    = $user_email;
				if ( empty( $contact ) ) {
					$contact = $customer_id;
				}
				$customers = array($customer_id);
				$user_data    = HubwooDataSync::get_sync_data( $customers );
				$contact_properties = $user_data[0]['properties'];
			} else {
				$contact = get_post_meta( $order_id, '_billing_email', true );
				$guest_orders = array($order_id);
				$user_data    = HubwooDataSync::get_guest_sync_data( $guest_orders );
				$contact_properties = $user_data[0]['properties'];
			}
		}

		if( ! empty( $contact_properties ) ) {
			$contact_properties = array( 'properties' => $contact_properties );
			HubWooConnectionMananager::get_instance()->create_or_update_single_contact( $contact_properties, $contact);
		}
		// $coupon_meta = $order->get_coupon_codes()[0];

		// $deal_id = get_post_meta( $order_id, "hubwoo_ecomm_deal_id", true );

		// if ( empty( $deal_id ) ) {		
		// 	return;
		// }

		// if( !empty( $coupon_meta ) ) {
		// 	$properties[] = array( 
		// 		'name'  => 'coupon_code', 
		// 		'value' => $coupon_meta,
		// 	);
		// }

		// if( !empty( $properties ) ) {	
		// 	$properties = array( "properties" => $properties );
		// 	HubWooConnectionMananager::get_instance()->update_existing_deal( $deal_id, $properties );
		// }
	}
 
	/**
	 * Function to extend properties of contact
	 *
	 */
	public function hubwoo_extend_contact_properties( $properties, $user_id ) {

	// 	if( ! empty( $user_id ) ) {
	// 		$customer    = new WC_Customer( $user_id );
	// 		$last_order  = $customer->get_last_order();
	// 		if( ! empty( $last_order ) ) {
	// 			$order_id    = $last_order->get_id(); 
	// 			$order       = wc_get_order( $order_id );
	// 			$coupon_meta = $order->get_coupon_codes();
	// 			if( ! empty( $coupon_meta ) ) {
	// 				$promo_code  = $coupon_meta[0];
	// 			}
	// 	 	}
	// 	}
		
	// 	if ( ! empty( $promo_code ) ) {
	// 		$properties[] = array(
	// 			'property' => 'code_promotionnel_champ_ouvert',
	// 			'value'    => $promo_code,
	// 		);
	// 	}

	 	return $properties;
	}

//end of file	
}	


