<?php
/**
 * All property callbacks.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    makewebbetter-hubspot-for-woocommerce
 * @subpackage makewebbetter-hubspot-for-woocommerce/includes
 */

/**
 * Manage all property callbacks.
 *
 * Provide a list of functions to manage all the information
 * about contacts properties and there callback functions to
 * get value of that property.
 *
 * @package    makewebbetter-hubspot-for-woocommerce
 * @subpackage makewebbetter-hubspot-for-woocommerce/includes
 */
class HubwooEcommPropertyCallbacks {

	/**
	 * Object id.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $_object_id;

	/**
	 * Object type
	 *
	 * @since 1.0.0
	 * @var WP_User
	 */
	protected $_object;


	/**
	 * Properties and there callbacks.
	 *
	 * @since 1.0.0
	 * @var Associated_array
	 */
	protected $_property_callbacks = array(

		'email'                    => 'hubwoo_get_user_mail',
		'firstname'                => 'hubwoo_get_user_meta',
		'lastname'                 => 'hubwoo_get_user_meta',
		'address'                  => 'hubwoo_get_user_meta',
		'company'                  => 'hubwoo_get_user_meta',
		'city'                     => 'hubwoo_get_user_meta',
		'state'                    => 'hubwoo_get_user_meta',
		'zip'                      => 'hubwoo_get_user_meta',
		'country'                  => 'hubwoo_get_user_meta',
		'phone'                    => 'hubwoo_get_user_meta',
		'mobilephone'              => 'hubwoo_get_user_meta',

		'name'                     => 'hubwoo_ecomm_product_info',
		'hs_images'                => 'hubwoo_ecomm_product_info',
		'price'                    => 'hubwoo_ecomm_product_info',
		'pr_description'           => 'hubwoo_ecomm_product_info',
		'product_source_store'     => 'hubwoo_ecomm_product_info',
		'store_product_id'         => 'hubwoo_ecomm_product_info',
		'hs_sku'                   => 'hubwoo_ecomm_product_info',

		'dealstage'                => 'hubwoo_ecomm_deal_info',
		'dealname'                 => 'hubwoo_ecomm_deal_info',
		'closedate'                => 'hubwoo_ecomm_deal_info',
		'amount'                   => 'hubwoo_ecomm_deal_info',
		'order_abandoned_cart_url' => 'hubwoo_ecomm_deal_info',
		'discount_amount'          => 'hubwoo_ecomm_deal_info',
		'order_number'             => 'hubwoo_ecomm_deal_info',
		'shipment_ids'             => 'hubwoo_ecomm_deal_info',
		'tax_amount'               => 'hubwoo_ecomm_deal_info',
		'createdate'               => 'hubwoo_ecomm_deal_info',
		'description'              => 'hubwoo_ecomm_deal_info',
	);

	/**
	 * Constructor
	 *
	 * @param int    $object_id      object id to get property values of.
	 * @param string $object_type    object type to get property values of.
	 */
	public function __construct( $object_id, $object_type ) {

		$this->_object_id = $object_id;
		$this->_object    = $object_type;
	}

	/**
	 * Property value.
	 *
	 * @param  string $property_name    name of the object property.
	 * @since 1.0.0
	 */
	public function _get_object_property_value( $property_name ) {

		$value = '';

		if ( ! empty( $property_name ) ) {
			// get the callback.
			$callback_function = $this->_get_property_callback( $property_name );

			if ( ! empty( $callback_function ) ) {

				// get the value by calling respective callback.
				$value = $this->$callback_function( $property_name );
			}
		}

		$value = apply_filters( 'hubwoo-ecomm_contact_property_value', $value, $property_name, $this->_object_id, $this->_object );

		return $value;
	}

	/**
	 * Filter the property callback to get value of.
	 *
	 * @param  strig $property_name   name of the property.
	 * @return string/false             callback function name or false.
	 */
	private function _get_property_callback( $property_name ) {
		// check if the property name exists in the array.
		if ( array_key_exists( $property_name, $this->_property_callbacks ) ) {
			// if exists then get the callback name.
			$callback = $this->_property_callbacks[ $property_name ];

			return $callback;
		}

		return false;
	}

	/**
	 * User email
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_get_user_mail() {
		// get it from user object.
		$user = get_user_by( 'id', $this->_object_id );
		return $user->data->user_email;
	}

	/**
	 * Get customer meta
	 *
	 * @param string $key meta key.
	 * @return string    customer meta detail.
	 * @since 1.0.0
	 */
	public function hubwoo_get_user_meta( $key ) {

		$value = '';

		switch ( $key ) {

			case 'firstname':
				$value = get_user_meta( $this->_object_id, 'first_name', true );
				break;

			case 'lastname':
				$value = get_user_meta( $this->_object_id, 'last_name', true );
				break;

			case 'company':
				$value = get_user_meta( $this->_object_id, 'billing_company', true );
				break;

			case 'city':
				$value = get_user_meta( $this->_object_id, 'billing_city', true );
				break;

			case 'state':
				$value = get_user_meta( $this->_object_id, 'billing_state', true );
				break;

			case 'country':
				$value = get_user_meta( $this->_object_id, 'billing_country', true );
				break;

			case 'address':
				$address1 = get_user_meta( $this->_object_id, 'billing_address_1', true );
				$address2 = get_user_meta( $this->_object_id, 'billing_address_2', true );
				$address  = '';
				if ( ! empty( $address1 ) || ! empty( $address2 ) ) {
					$address = $address1 . ' ' . $address2;
				}
				$value = $address;
				break;

			case 'zip':
				$value = get_user_meta( $this->_object_id, 'billing_postcode', true );
				break;

			case 'mobilephone':
			case 'phone':
				$value = get_user_meta( $this->_object_id, 'billing_phone', true );
				break;
		}
		return $value;
	}

	/**
	 * Create user date.
	 *
	 * @since 1.0.0
	 * @return string user registered date
	 */
	public function hubwoo_create_date() {

		$create_date      = '';
		$customer         = new WP_User( $this->_object_id );
		$account_creation = isset( $customer->data->user_registered ) ? $customer->data->user_registered : '';
		if ( ! empty( $account_creation ) ) {
			$account_creation = strtotime( $account_creation );
		}
		if ( ! empty( $account_creation ) ) {
			$create_date = HubwooObjectProperties::get_instance()->hubwoo_set_utc_midnight( $account_creation );
		}
		return $create_date;
	}

	/**
	 * Get contact lifecycle stage
	 *
	 * @return string    customer lifecycle stage
	 * @since 1.0.0
	 */
	public function hubwoo_contact_stage() {

		$stage = '';

		$orders_count = 0;

		//hpos changes
		$query = new WC_Order_Query(array(
			'posts_per_page'      => -1,
			'post_status'         => array_keys( wc_get_order_statuses() ),
			'order'               => 'desc',
			'post_parent'         => 0,
			'customer_id'	  	  => $this->_object_id,
		));

		$customer_orders = $query->get_orders();

		if ( is_array( $customer_orders ) && count( $customer_orders ) ) {

			$orders_count = count( $customer_orders );
		}

		if ( $orders_count > 0 ) {

			$stage = 'customer';
		} else {

			$stage = 'lead';
		}

		return $stage;
	}

	/**
	 * Callback for products objects
	 *
	 * @param string $key meta key.
	 * @return string    product properties
	 * @since 1.0.0
	 */
	public function hubwoo_ecomm_product_info( $key ) {

		$product = wc_get_product( $this->_object_id );

		$value = '';

		if ( ! empty( $product ) && ! is_wp_error( $product ) ) {

			switch ( $key ) {

				case 'name':
					$value = HubwooObjectProperties::hubwoo_ecomm_product_name( $product );
					break;

				case 'hs_images':
					$attachment_src = wp_get_attachment_image_src( get_post_thumbnail_id( $this->_object_id ), 'single-post-thumbnail' );
					$image_url      = isset( $attachment_src[0] ) ? $attachment_src[0] : wc_placeholder_img_src();
					$value          = $image_url;
					break;

				case 'price':
					$value = $product->get_price();
					break;

				case 'pr_description':
					$value = $product->get_short_description();
					break;

				case 'store_product_id':
					$value = $this->_object_id;
					break;

				case 'product_source_store':
					$value = get_bloginfo( 'name' );
					break;

				case 'hs_sku':
					$value = $product->get_sku();
					break;
			}
		}

		return $value;
	}

	/**
	 * Callback for deal objects.
	 *
	 * @since 1.0.0
	 * @param string $key meta key.
	 * @return string   object properties
	 */
	public function hubwoo_ecomm_deal_info( $key ) {

		$order = wc_get_order( $this->_object_id );

		$value = '';

		if ( $order instanceof WC_Order ) {
			$status     = $order->get_status();
			$deal_name  = '#' . $order->get_order_number();
			$order_date = get_post_time( 'U', true, $this->_object_id );

			$create_date = $order_date;

			$user_info['first_name'] = $order->get_billing_first_name();
			$user_info['last_name']  = $order->get_billing_last_name();

			foreach ( $user_info as $value ) {
				if ( ! empty( $value ) ) {
					$deal_name .= ' ' . $value;
				}
			}
			
			$deal_stage = self::hubwoo_get_valid_deal_stage( 'wc-' . $status );

			if ( ! in_array( $deal_stage, self::hubwoo_ecomm_won_stages() ) ) {

				$order_date = get_post_time( 'U', true, $this->_object_id ) + ( get_option( 'hubwoo_ecomm_closedate_days', 5 ) * 24 * 60 * 60 );
			}

			if ( ! empty( $order ) && ! is_wp_error( $order ) ) {

				switch ( $key ) {

					case 'dealstage':
						$value = $deal_stage;
						break;

					case 'dealname':
						$value = $deal_name;
						break;

					case 'closedate':
						$value = HubwooObjectProperties::get_instance()->hubwoo_set_utc_midnight( $order_date );
						break;

					case 'createdate':
						$value = HubwooObjectProperties::get_instance()->hubwoo_set_utc_midnight( $create_date );
						break;

					case 'amount':
						$value = $order->get_total();
						break;

					case 'order_abandoned_cart_url':
						$value = $order->get_checkout_payment_url();
						break;

					case 'discount_amount':
						$value = $order->get_discount_total();
						break;

					case 'order_number':
						$value = $order->get_order_number();
						break;

					case 'shipment_ids':
						$value = $order->get_shipping_method();
						break;

					case 'tax_amount':
						$value = $order->get_total_tax();
						break;

					case 'description':
						$value = $order->get_customer_note();
						break;
				}
			}
		}

		return $value;
	}

	/**
	 * Format an array in hubspot accepted enumeration value.
	 *
	 * @param  array $properties  Array of values.
	 * @return string  formatted string.
	 * @since 1.0.0
	 */
	public static function hubwoo_ecomm_format_array( $properties ) {

		if ( is_array( $properties ) ) {

			$properties = array_unique( $properties );
			$properties = implode( ',', $properties );
		}

		return $properties;
	}

	/**
	 * Return a valid deal stage for the order status
	 *
	 * @since 1.0.0
	 * @param  string $order_key  order status key.
	 * @return string  hubspot deal stage
	 */
	public static function hubwoo_get_valid_deal_stage( $order_key ) {

		$saved_mappings = get_option( 'hubwoo_ecomm_final_mapping', array() );
		$key            = array_search( $order_key, array_column( $saved_mappings, 'status' ) );
		return false === $key ? 'checkout_completed' : $saved_mappings[ $key ]['deal_stage'];
	}

	/**
	 * Returns won deal stages.
	 *
	 * @since 1.0.0
	 * @return array won deal stages
	 */
	public static function hubwoo_ecomm_won_stages() {

		$won_stages = get_option( 'hubwoo_ecomm_won_stages', array() );

		if ( empty( $won_stages ) ) {

			$won_stages = array( 'processed', 'shipped' );
		}

		return $won_stages;
	}
}

