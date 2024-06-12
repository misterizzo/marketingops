<?php

/**
 * Inventory Management
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
 * This class defines All Products Management system.
 *
 * @since      1.0.0
 * @package    Hubspot_Integration_Customization
 * @subpackage Hubspot_Integration_Customization/includes
 * @author     MakeWebBetter <support@makewebbetter.com>
 */
class Hubspot_Integration_Customization_Manage_Products {

	public function hubwoo_update_inventory($product_id) {

		$hs_product_id = get_post_meta($product_id, "hubwoo_hs_product_id", true);
		$product = $this->hubwoo_product_model($product_id);

		if( !empty( $hs_product_id ) ) {
			Hubspot_Integration_Customization_Rest::get_instance()->hubwoo_update_product_inventory( $hs_product_id, $product );
		} else {
			$this->hubwoo_process_products(Hubspot_Integration_Customization_Rest::get_instance()->hubwoo_create_product_inventory( $product ));
		}
	}

	public function hubwoo_get_all_products( $should_create = false, $limit = 50) {

		$query = new WP_Query();

		$products = $query->query(
			array(
				'post_type'           => array( 'product', 'product_variation' ),
				'posts_per_page'      => $limit,
				'post_status'         => array( 'publish' ),
				'orderby'             => 'date',
				'order'               => 'desc',
				'fields'              => 'ids',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
				'meta_query'		  => array(
					array(
						'key'	  => 'hubwoo_hs_product_id',
						'compare' => 'NOT EXISTS',
					)
				)
			)
		);

		return $should_create && !empty($products) ? $this->hubwoo_process_products( $this->hubwoo_prepare_products($products, $should_create ), 'batch') : $products;
	}


	public function hubwoo_prepare_products($product_ids, $should_create ) {

		if( empty( $product_ids ) ) { return; }

		// echo '<pre>'; print_r($product_ids); echo '</pre>';die('products!');

		$prepared_products = array();

		foreach ( $product_ids as $product_id ) {
			$pre_product = $this->hubwoo_product_model($product_id);
			if(!empty($pre_product)) {
				$prepared_products[] = $pre_product;
			}
		}

		// echo '<pre>'; print_r($prepared_products); echo '</pre>';die('prepared_products!');

		return $should_create && !empty($prepared_products) ?  Hubspot_Integration_Customization_Rest::get_instance()->hubwoo_create_batch_products($prepared_products) : $prepared_products;
	}

	public function hubwoo_product_model($product_id) {
		
		$product = array();
		$product['product_id'] = $product_id;

		$contains_tags = array( 'description', 'categories' );
		$db_keys = array( "_stock_status", "_stock", "_manage_stock", "_backorders" );
		$obj_keys = array( "name" => "get_name", "hs_sku" => 'get_sku' , 'price' => 'get_price', "description" => 'get_description', 'categories' => 'get_categories');

		foreach ( $db_keys as $key ) {
			$value = get_post_meta( $product_id, $key, true );
		
			if( !empty($value) ) {
				$product[Hubspot_Integration_Customization_Box_Office::hubwoo_clean_internal_name($key)] = $value;
			}
		}

		$wc_product = wc_get_product($product_id);
		foreach ( $obj_keys as $key => $method ) {
			if( !empty($wc_product->$method()) ) {
				if( in_array($key,$contains_tags)) {
					$product[$key] = strip_tags($wc_product->$method());
				} else {
					$product[$key] = $wc_product->$method();
				}
			}
		}

		return !empty($product) ? self::hubwoo_format_object($product): "";
	}

	public function hubwoo_process_products($response, $type = 'single') {

		if(200 != $response['status_code']) { return; }

		$parsed_response = json_decode($response['response'], true);

		if('single' == $type) {
			if(isset($parsed_response['properties']['product_id']['value'])) {
				update_post_meta( $parsed_response['properties']['product_id']['value'], 'hubwoo_hs_product_id', $parsed_response['objectId']);
			}	
		} else {

			foreach ( $parsed_response as $product_object ) {
				if(isset($product_object['properties']['product_id']['value'])) {
					update_post_meta( $product_object['properties']['product_id']['value'], 'hubwoo_hs_product_id', $product_object['objectId']);
				}
			}
		}
		return $response;
	}

	public static function hubwoo_format_object( $object ) {
		
		$hs_object = array();
		foreach ($object as $name => $value) {
			$hs_object[] = array( 
				'name'  => $name,
				'value' => $value  
			);
		}
		return $hs_object;
	}
}
