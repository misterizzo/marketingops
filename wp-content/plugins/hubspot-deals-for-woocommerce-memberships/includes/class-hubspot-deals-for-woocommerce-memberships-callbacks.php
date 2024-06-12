<?php

class HubwooMembershipsCallbacks {

	/**
	 * The single instance of the class.
	 *
	 * @since 	1.0.0
	 * @access 	protected 
	 * @var HubwooMembershipsCallbacks 	The single instance of the HubwooMembershipsCallbacks
	 */
	protected static $_instance = null;

	/**
	 * Main HubwooMembershipsCallbacks Instance.
	 *
	 * Ensures only one instance of HubwooMembershipsCallbacks is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return HubwooMembershipsCallbacks - Main instance.
	 */

	public static function get_instance() {

		if ( is_null( self::$_instance ) ) {

			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public static function create_membership_deal ( $membership_id ) {

		if ( !empty( $membership_id ) ) {

			$user_membership = wc_memberships_get_user_membership( $membership_id );
			$user_id = $user_membership->get_user_id();
			$user = get_userdata( $user_id );
			$properties = array();
			$email = !empty( $user->user_email ) ? $user->user_email : '';
			if ( !empty( $email ) ) {

				$fname = get_user_meta( $user_id, "first_name", true );
				$lname = get_user_meta( $user_id, "last_name", true );
				$statuses = wc_memberships_get_user_membership_statuses();
				if ( $plan = $user_membership->get_plan() ) {
					$plan_name = $plan->get_name();
				}
				else {
					$plan_name = "";
				}
				$status   = 'wcm-' . $user_membership->get_status();
				$status_label = !empty( $statuses[ $status ]['label'] ) ? $statuses[ $status ]['label'] : "";
				$since_time = get_post_meta( $membership_id, '_start_date', true );
				if ( ! empty( $since_time ) ) {
					$since_time = DateTime::createFromFormat( "Y-m-d H:i:s", $since_time );
					if ( $since_time instanceof DateTime ) {
						$since_time->modify( 'midnight' );
						$since_time = $since_time->getTimestamp() * 1000;
						$properties[] = array( "name" => "membership_since", "value" => $since_time );
					}
				}
				$end_time = get_post_meta( $membership_id, '_end_date', true );
				if ( ! empty( $end_time ) ) {
					$end_time = DateTime::createFromFormat( "Y-m-d H:i:s", $end_time );
					if ( $end_time instanceof DateTime ) {
						$end_time->modify( 'midnight' );
						$end_time = $end_time->getTimestamp() * 1000;
						$properties[] = array( "name" => "membership_expires", "value" => $end_time );
					}
				}

				if( $status_label == "Cancelled" ) {
					// $cancelled_time = get_post_meta( $membership_id, '_cancelled_date', true );
					$cancelled_time = date("Y-m-d H:i:s");
					if ( ! empty( $cancelled_time ) ) {
						$cancelled_time = DateTime::createFromFormat( "Y-m-d H:i:s", $cancelled_time );
						if ( $cancelled_time instanceof DateTime ) {
							$cancelled_time->modify( 'midnight' );
							$cancelled_time = $cancelled_time->getTimestamp() * 1000;
							$properties[] = array( "name" => "membership_cancelled", "value" => $cancelled_time );

						}
					}
				}else{
					$properties[] = array( "name" => "membership_cancelled", "value" => "" );
				}

				if( $status_label == "Paused" ) {
					// $paused_time = get_post_meta( $membership_id, '_paused_date', true );
					$paused_time = date("Y-m-d H:i:s");
					if ( ! empty( $paused_time ) ) {
						$paused_time = DateTime::createFromFormat( "Y-m-d H:i:s", $paused_time );
						if ( $paused_time instanceof DateTime ) {
							$paused_time->modify( 'midnight' );
							$paused_time = $paused_time->getTimestamp() * 1000;
							$properties[] = array( "name" => "membership_paused", "value" => $paused_time );
						}
					}
				} else{
					$properties[] = array( "name" => "membership_paused", "value" => "" );
				}

				$deal_name = __( "Membership: ", "hubwoo" ) . $plan_name;
				$properties[] = array( "name" => "dealname", "value" => $deal_name );
				$properties[] = array( "name" => "dealstage", "value" => $status );
				$properties[] = array( "name" => "pipeline", "value" => Hubspot_Deals_For_Woocommerce_Memberships::hubwoo_ms_deals_get_pipeline_id() );
				$properties[] = array( "name" => "membership_status", "value" => $status_label );
				$properties[] = array( "name" => "membership_type", "value" => $user_membership->get_type() );
				$properties[] = array( "name" => "membership_plan", "value" => $plan_name );
				$order_id = $user_membership->get_order_id();
				if ( $order_id ) {
					$properties[] = array( "name" => "membership_shop_order_number", "value" => $order_id );
					$order_date = get_post_time( 'U', true, $order_id );
					$order_date = self::hubwoo_ms_deal_set_utc_midnight( $order_date );
					$properties[] = array( "name" => "membership_shop_order_date", "value" => $order_date );
					$order = wc_get_order( $order_id );
					if ( $order ) {
						$properties[] = array( "name" => "membership_shop_order_total", "value" => $order->get_total() );
						$order_items = $order->get_items();
						$products_bought = array();
						foreach( $order_items as $item_id_1 => $WC_Order_Item_Product ) {
							if( !empty( $WC_Order_Item_Product ) && is_object( $WC_Order_Item_Product ) ) {
								$item_id = $WC_Order_Item_Product->get_product_id();
								$item_var_id = $WC_Order_Item_Product->get_variation_id();
								if ( $item_var_id ) {
									$item_id = $item_var_id;
								}
								if( get_post_status( $item_id ) == "trash" || get_post_status( $item_id ) == false ) {
									continue;
								}
								$post = get_post( $item_id );
								$product_uni_name = isset( $post->post_name ) ? $post->post_name : "";
								if( !empty( $product_uni_name ) ) {
									$products_bought[] = $product_uni_name;
								}
							}
						}
						if ( count( $products_bought ) ) {
							$properties[] = array( "name" => "membership_products", "value" => implode( ";", $products_bought ) );
						}
					}
				}
				$user_membership_subs_id = get_post_meta( $membership_id, "_subscription_id", true );
				if ( $user_membership_subs_id ) {
					$properties[] = array( "name" => "membership_subscription_order_number", "value" => $user_membership_subs_id );
				}

				$deal_id = get_post_meta( $membership_id, 'hubwoo_ms_deal_id', true );
				
				if ( empty( $deal_id ) ) {
					$contact_vid = self::hubwoo_deals_get_contact_by_email( $email, $fname, $lname );
					$vids = array();
					$vids[] = $contact_vid;
					$associated_vids = array( "associatedVids" => $vids );
					$deal = array( "properties" => $properties, "associations" => $associated_vids );
					$response = self::hubwoo_create_deals_for_memberships( $deal );
					if( isset( $response['status_code'] ) && $response['status_code'] == 200 ) {
						if( isset( $response['response'] ) ) {
							$store_response = json_decode( $response['response'] );
							if( isset( $store_response->dealId ) ) {
								update_post_meta( $membership_id, "hubwoo_ms_deal_id", $store_response->dealId );
							}
						}
					}
				}
				else {
					$properties = array( "properties" => $properties );
					$response = self::hubwoo_update_deals( $properties, $deal_id );
					if( isset( $response['status_code'] ) && $response['status_code'] == 200 ) {
						if( isset( $response['response'] ) ) {
							$store_response = json_decode( $response['response'] );
							if( isset( $store_response->dealId ) ) {
								update_post_meta( $membership_id, "hubwoo_ms_deal_id", $store_response->dealId );
							}
						}
					}
				}

				return $response;
			}
		}
	}

	/**
	 * checking contacts on HubSpot by email
	 *
	 * @since    1.0.0
	 */

	public static function hubwoo_deals_get_contact_by_email( $email = '', $fname = '', $lname = '' ) {
		
		$contact_vid = '';

		if( Hubspot_Deals_For_Woocommerce_Memberships::hubwoo_ms_check_basic_setup() ) {

			$flag = true;
			
			if( Hubspot_Deals_For_Woocommerce_Memberships::is_access_token_expired() ) {
		
				$hapikey = HUBWOO_MS_DEAL_CLIENTID;
				$hseckey = HUBWOO_MS_DEAL_SECRETID;
				$status =  HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey );

				if( !$status ) {

					$flag = false;
				}
			}

			if( $flag ) {

				$deals_manager = new HubSpotMembershipsConnectionMananager();
				$contact_vid = $deals_manager->get_customer_by_email( $email, $fname, $lname );
			}
		}

		return $contact_vid;
	}

	/**
	 * new deals on HubSpot
	 *
	 * @since    1.0.0
	 */

	public static function hubwoo_create_deals_for_memberships( $deal_details ) {

		if( Hubspot_Deals_For_Woocommerce_Memberships::hubwoo_ms_check_basic_setup() ) {

			$flag = true;
		
			if( Hubspot_Deals_For_Woocommerce_Memberships::is_access_token_expired() ) {
		
				$hapikey = HUBWOO_MS_DEAL_CLIENTID;
				$hseckey = HUBWOO_MS_DEAL_SECRETID;
				$status =  HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey);

				if( !$status ) {

					$flag = false;
				}
			}

			if( $flag ) {

				$deals_manager = new HubSpotMembershipsConnectionMananager();
				$response = $deals_manager->create_new_deal( $deal_details );
				return $response;
			}
		}
	}

	/**
	 * updating deals on HubSpot
	 *
	 * @since    1.0.0
	 */

	public static function hubwoo_update_deals( $deal_details, $deal_id ) {
		
		if( Hubspot_Deals_For_Woocommerce_Memberships::hubwoo_ms_check_basic_setup() ) {

			$flag = true;
			
			if( Hubspot_Deals_For_Woocommerce_Memberships::is_access_token_expired() ) {
		
				$hapikey = HUBWOO_MS_DEAL_CLIENTID;
				$hseckey = HUBWOO_MS_DEAL_SECRETID;
				$status =  HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey);

				if( !$status ) {
					$flag = false;
				}
			}

			if( $flag ) {
				
				$deals_manager = new HubSpotMembershipsConnectionMananager();
				$response = $deals_manager->update_existing_deal( $deal_id, $deal_details );
				return $response;
			}
		}
	}

	/**
	 * return formatted time for HubSpot
	 * 
	 * @param  int 			$unix_timestamp
	 * @return string       formatted time.
	 * @since 1.0.0
	 */
	public static function hubwoo_ms_deal_set_utc_midnight( $unix_timestamp ) {
		
		$string = gmdate("Y-m-d H:i:s", $unix_timestamp );
		$date = new DateTime( $string );
		$date->modify( 'midnight' );
		return $date->getTimestamp() * 1000; // in miliseconds
	}
}
?>