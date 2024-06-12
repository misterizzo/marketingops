<?php

/**
 * WooCommerce Box Office Compatibility.
 *
 * @link       makwebbetter.com
 * @since      1.0.0
 *
 * @package    Hubspot_Integration_Customization
 * @subpackage Hubspot_Integration_Customization/includes
 */

/**
 * Fired during plugin activation.
 *
 * Compatibility with WooCommerce Box Office Plugin.
 *
 * @since      1.0.0
 * @package    Hubspot_Integration_Customization
 * @subpackage Hubspot_Integration_Customization/includes
 * @author     MakeWebBetter <support@makewebbetter.com>
 */
class Hubspot_Integration_Customization_Box_Office {
	
	public function hubwoo_create_ticket_contacts($order_id) {

		$product_ids = array();

		$order_object = wc_get_order($order_id);

		$product_items = $order_object->get_items();

		foreach ( $product_items as $product_item ) {
			$product_id = $product_item->get_product_id();
			$product_variation_id = $product_item->get_variation_id();			
			
			if($product_variation_id) {
				$product_ids[] = $product_variation_id;
			} else {

				$product_ids[] = $product_id;
			}

		}

		global $wpdb;

		$ticket_objects = $wpdb->get_results ("
			SELECT post_id 
			FROM  $wpdb->postmeta
			WHERE meta_key = '_order'
			AND meta_value = ".$order_id 
		);

		if(empty($ticket_objects)) { return; }

		$ticket_data_for_people = array();
		
		$user_data_set = array( "first_name", "last_name", "email", "phone", "country", "state", "zip", "company" );
		
		foreach ( $ticket_objects as $ticket_object ) {

			if( !empty( $ticket_object->post_id ) ) {
		   
				$ticket = new WC_Box_Office_Ticket((int)$ticket_object->post_id );
			
				$ticket_data = $ticket->fields;
				// echo '<pre>'; print_r($ticket_data); echo '</pre>';die('ticket data!');
				$data_set = array();
			  
				if( is_array ( $ticket_data ) && !empty( $ticket_data ) ) {
				  
					foreach ($ticket_data as $key => $ticket ) {  
					   
						if( $ticket['type'] == "checkbox" ) {
							
							if( !empty( $ticket['value'] ) ) {
								
								$data_set[$ticket["label"]] = "yes";
							} else {
							   
								$data_set[$ticket["label"]] = "no";
							}
						} else if( in_array( strtolower($ticket["type"]), $user_data_set ) || in_array( strtolower($ticket["label"]), $user_data_set ) ) {

							if( $ticket["type"] == "email" || $ticket["type"] == "first_name" || $ticket["type"] == "last_name" ) {
							
								$data_set[$ticket["type"]] = $ticket["value"];
							} else {
								$data_set[strtolower($ticket["label"])] = $ticket["value"];
							}
						} else {

							if( is_array( $ticket["value"] ) ) {
								$data_set[$ticket["label"]] = $ticket['value'][0];
							} else {
								$data_set[$ticket["label"]] = $ticket["value"];
							}                    
						}
					}
				}
				$ticket_data_for_people[] = $data_set;
			}
		}
		// echo '<pre>'; print_r($ticket_data_for_people); echo '</pre>';die('StopItRightHereAndDIEEEEEE!');
		if( !empty( $ticket_data_for_people ) ) {

			foreach ( $ticket_data_for_people as $key => $ticket_data ) {

				$properties = array();

				if( !empty( $ticket_data['email'] ) ) {

					foreach ($ticket_data as $ticket_field_name => $ticket_field_data ) {
						if(in_array($ticket_field_name, $user_data_set)) {

							if($ticket_field_name == 'first_name' || $ticket_field_name == 'last_name') {
								continue;
							}

							$user_data[$ticket_field_name] = $ticket_field_data;
						}
					}

					$user_data['firstname']   = $ticket_data["first_name"];
					$user_data['lastname'] 	  = $ticket_data["last_name"];
					$user_data['email'] 	  = $ticket_data["email"];
				   
					$user_vid = Hubspot_Integration_Customization_Rest::get_instance()->get_customer_by_user_data( $user_data );

					if( !empty( $user_vid ) ) {

						foreach ( $ticket_data as $key => $data ) {
							
							if( in_array( $data, $user_data ) ) {
								unset( $ticket_data[$key] );
							}
						}       

						// echo '<pre>'; print_r($ticket_data); echo(arg1) '</pre>';die('StopItRightHereAndDIEEEEEE!');
						
						$user_properties = array();
					   
						if( ! empty($ticket_data)) {

							foreach ( $ticket_data as $property => $property_value ) {

								if( !empty( $ticket_data[$property] ) ){

									$label = $property;
									$property = preg_replace('/[^A-Za-z0-9\_]/', '', $property);
									$property = strtolower($property);      
									if( !in_array($property, $user_data_set) ) {
								   		$internal_name = "wbo_".str_replace(" ","_", $property);
									} else {
										$internal_name = str_replace(" ","_", $property);
									}
								   
									$response = Hubspot_Integration_Customization_Rest::get_instance()->hubwoo_get_property( $internal_name );

									if( $response['status_code'] != 200 ) {
									
										$properties = array(
											"name"      => $internal_name,
											"label"     => $label,
											"type"      => "string",  
											"fieldType" => "textarea",  
											"groupName" => "woocommerce_box_office_tickets",  
										);    

										Hubspot_Integration_Customization_Rest::get_instance()->create_property( $properties );
									}

									$user_properties[] =  array(
										"property" => $internal_name,
										"value"    => $property_value
									);
								}
							}
						}
						// echo '<pre>'; print_r($user_properties); echo '</pre>';die('StopItRightHereAndDIEEEEEE!');
						$user_properties[] = array(
							"property" => "ticket_contact",
							"value"    => "yes"
						); 
						$uploaded_products = get_option("hubwoo_uploaded_products", "" );

						if( !empty( $uploaded_products ) ) {
							foreach ($product_ids as $product_id ) {
								if(array_key_exists($product_id, $uploaded_products)) {
									$value = $uploaded_products[$product_id]['value'];
									break;
								}
							}
						}

						$user_properties[] = array(
							"property" => "event_attending",
							"value"    => $value
						); 

						if(is_array($user_data) && !empty($user_data)) {
							foreach ($user_data as $key => $value) {
								$user_properties[] = array(
									"property" => $key,
									"value"    => $value
								);
							}
						}

						$final_properties = array("properties" => $user_properties);
						Hubspot_Integration_Customization_Rest::get_instance()->hubwoo_update_customer( $user_vid, $final_properties );
					}
				}
			}		
		}
	}

	public function hubwoo_update_ticket_products( $post_id ) {

		$ticket = get_post_meta( $post_id, "_ticket", true );

		if( empty( $ticket ) || 'no' == $ticket ) {
			return;
		}

		$uploaded_products = get_option("hubwoo_uploaded_products", array() );
		
		if( !empty( $uploaded_products ) ) {

			$options = array();
			
			$product = wc_get_product($post_id);

			if( "variable" == $product->get_type() ) {

				$all_variations = $product->get_children();

				foreach ( $all_variations as $variation_id ) {

					$product = wc_get_product($variation_id);
					$uploaded_products = $this->hubwoo_product_did_changed($variation_id, $product, $uploaded_products);
				}
			} else {
				$uploaded_products = $this->hubwoo_product_did_changed($post_id, $product, $uploaded_products);
			}	

			// echo '<pre>'; print_r($uploaded_products); echo '</pre>';die('StopItRightHereAndDIEEEEEE!');
			if( !empty( $uploaded_products ) ) {

				$properties = array(
					"name"      => "event_attending",
					"label"     => "Event Attending",
					"type"      => "enumeration",  
					"fieldType" => "radio",  
					"groupName" => "woocommerce_box_office_tickets", 
					"options"   => array_values($uploaded_products),
				);    

				$propertyResponse = HubWooConnectionMananager::get_instance()->update_property( $properties );				
				if( $propertyResponse['status_code'] == 200 ) {
					update_option("hubwoo_uploaded_products", $uploaded_products );				    
				}
				// echo '<pre>'; print_r($propertyResponse); echo '</pre>';die('StopItRightHereAndDIEEEEEE!');
			}
		}				
	}	

	public function upload_all_ticket_products() {

		$options = $prepared_products = array();

		$query = new WP_Query();

		$products =  $query->query( 
			array(
				'post_type'           => array( 'product' ),
				'posts_per_page'      => -1,
				'post_status'         => array( 'publish' , 'draft' ),
				'orderby'             => 'date',
				'order'               => 'desc',
				'fields'              => 'ids',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
				'meta_query'		  => array(
					array( 
						'key' => '_ticket',
						'compare' => '==',
						'value'	  => 'yes'						
					)
				) 
			)
		);

		// echo '<pre>'; print_r($products); echo '</pre>'; die('StopItRightHereAndDIEEEEEE!');

		foreach ( $products as $item_id ) {

			$all_variations = array();
			$product_data = array();
			$product = wc_get_product( $item_id );
			
			$product_type = $product->get_type();
			
			if( "variable" == $product_type ) {
				$all_variations = $product->get_children();
			}
			
			if( !empty( $all_variations )) {
			
				foreach ($all_variations as $var_id ) {
					unset($product_data);
					$var_product = wc_get_product($var_id );			
					$product_data = $this->hubwoo_prepare_ticket_products($var_product);
					$prepared_products[$var_id] = $product_data;
					$options[] = $product_data;
				}
			} else {
				$product_data = $this->hubwoo_prepare_ticket_products($product);
				$prepared_products[$item_id] = $product_data;
				$options[] = $product_data;
			}
		}

		// echo '<pre>'; print_r($prepared_products); echo '</pre>';die('StopItRightHereAndDIEEEEEE!');

		$properties = array(
			"name"      => "event_attending",
			"label"     => "Event Attending",
			"type"      => "enumeration",  
			"fieldType" => "radio",  
			"groupName" => "woocommerce_box_office_tickets", 
			"options"   => $options,
		); 

		// echo '<pre>'; print_r($prepared_products); echo '</pre>';die('StopItRightHereAndDIEEEEEE!');


		$propertyResponse = HubWooConnectionMananager::get_instance()->update_property( $properties );	


		if( $propertyResponse['status_code'] == 200 ) {
			update_option("hubwoo_uploaded_products", $prepared_products );				    
		}
		// echo '<pre>'; print_r($propertyResponse); echo '</pre>';die('StopItRightHereAndDIEEEEEE!');
	}	

	
	// Helpers 

	public function hubwoo_prepare_ticket_products($product) {
		
		$label = $product->get_name();
		$name = self::hubwoo_clean_internal_name($label);
		if(!empty($name)) {
			return array(
				"label" => $label,
				"value" => $name,
			);
		}		
	}

	public function hubwoo_product_did_changed($product_id, $product, $uploaded_products) {

		$name = $product->get_name();

		if( array_key_exists( $product_id, $uploaded_products ) ) {

			$cleaned_name = $uploaded_products[$product_id]['value'];
			$uploaded_products[$product_id] = array(
				"label"	  => $name,
				"value"   => $cleaned_name,
			);
		} else {
			$cleaned_name = self::hubwoo_clean_internal_name( $name );
			$uploaded_products[$product_id] = array(
				"label"	  => $name,
				"value"   => $cleaned_name,
			);
		}	
		return $uploaded_products;	
	}


	public static function hubwoo_clean_internal_name( $string ) {

		$firstChar=0;

		// looping the string and indexing the first alphabet   
		$length = strlen($string);
		for ($i=0; $i < $length; $i++) { 
			if(ctype_alpha($string[$i])) {
				$firstChar = $i;
				break;
			}
		}
		// if there is a numeric value before the very first character remove it
		for ($i=0; $i < $firstChar ; $i++) { 
			$string  = substr_replace( $string , "" , 0, $firstChar );
		}

		$string = str_replace('–', '', $string);
		$string = str_replace(' ', '_', $string);

		//removes all specials and setting it to lower case
		$string = preg_replace('/[^A-Za-z0-9\_]/', '', $string);
		$string = strtolower($string);
		return $string;		
	}

}
