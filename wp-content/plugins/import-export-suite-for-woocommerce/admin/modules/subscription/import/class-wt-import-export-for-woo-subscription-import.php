<?php
/**
 * Handles the subscription import.
 *
 * @package   ImportExportSuite\Admin\Modules\Subscription\Import
 * @version   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_Subscription_Import Class.
 */
class Wt_Import_Export_For_Woo_Subscription_Import {

	/**
	 * Post type
	 *
	 * @var string
	 */
	public $post_type = 'shop_subscription';
	/**
	 * Parent module
	 *
	 * @var object
	 */
	public $parent_module = null;
	/**
	 * Subscription data
	 *
	 * @var array
	 */
	public $parsed_data = array();
	/**
	 * Subscription data
	 *
	 * @var array
	 */
	public $import_columns = array();
	/**
	 * Whether to update existing?
	 *
	 * @var boolean
	 */
	public $merge;
	/**
	 * Whether to skip new?
	 *
	 * @var boolean
	 */
	public $skip_new;
	/**
	 * Whether to read empty columns?
	 *
	 * @var boolean
	 */
	public $merge_empty_cells;

	/**
	 * Whether to delete existing subscription from site that are not in the input file?
	 *
	 * @var boolean
	 */
	public $delete_existing;
	/**
	 * Key that connects order and subscription on a migration
	 *
	 * @var string/integer
	 */
	public $link_wt_import_key;
	/**
	 * Whether to link the line items using SKU or ID?
	 *
	 * @var boolean
	 */
	public $link_product_using_sku;
	/**
	 * Processed rows
	 *
	 * @var arrray
	 */
	public $import_results = array();
	/**
	 * Whether the subscription with same ID exist?
	 *
	 * @var boolean
	 */
	public $is_order_exist = false;
	/**
	 * Membership plans of importing subscriptions if applicable
	 *
	 * @var string
	 */
	public static $membership_plans = null;
	/**
	 * Determines whether shipping requires
	 *
	 * @var boolean
	 */
	public static $all_virtual = true;
	/**
	 * User meat dields
	 *
	 * @var array
	 */
	public static $user_meta_fields = array(
		'_billing_first_name', // Billing Address Info.
		'_billing_last_name',
		'_billing_company',
		'_billing_address_1',
		'_billing_address_2',
		'_billing_city',
		'_billing_state',
		'_billing_postcode',
		'_billing_country',
		'_billing_email',
		'_billing_phone',
		'_shipping_first_name', // Shipping Address Info.
		'_shipping_last_name',
		'_shipping_company',
		'_shipping_phone',
		'_shipping_address_1',
		'_shipping_address_2',
		'_shipping_city',
		'_shipping_state',
		'_shipping_postcode',
		'_shipping_country',
	);
	/**
	 * Order table is in sync.
	 *
	 * @var bool
	 */
	public static $is_sync;
	/**
	 * Order table name.
	 *
	 * @var string
	 */
	public static $table_name;
	/**
	 * Is HPOS enabled.
	 *
	 * @var bool
	 */
	public static $is_hpos_enabled;
	/**
	 * Order meta field array.
	 *
	 * @var array
	 */
	public $order_meta_fields = array();
	/**
	 * Id conflict.
	 *
	 * @var string
	 */
	public $id_conflict;

	/**
	 * Constructor.
	 *
	 * @param object $parent_object Parent module object.
	 * @since 1.0.0
	 */
	public function __construct( $parent_object ) {

		$this->parent_module = $parent_object;
		$hpos_data = Wt_Import_Export_For_Woo_Common_Helper::is_hpos_enabled();
		self::$table_name = $hpos_data['table_name'];
		self::$is_sync = $hpos_data['sync'];
		if ( false !== strpos( $hpos_data['table_name'], 'wc_orders' ) || $hpos_data['sync'] ) {
			self::$is_hpos_enabled = true;
		}

		$this->order_meta_fields = array(
			'subscription_status',
			'billing_period',
			'billing_interval',
			'order_shipping',
			'order_shipping_tax',
			'order_tax',
			'cart_discount',
			'cart_discount_tax',
			'order_total',
			'order_currency',
			'payment_method',
			'payment_method_title',
			'billing_first_name',
			'billing_last_name',
			'billing_email',
			'billing_phone',
			'billing_address_1',
			'billing_address_2',
			'billing_postcode',
			'billing_city',
			'billing_state',
			'billing_country',
			'billing_company',
			'shipping_first_name',
			'shipping_last_name',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_postcode',
			'shipping_city',
			'shipping_state',
			'shipping_country',
			'shipping_company',
			'shipping_phone',
			'download_permissions',
		);
	}//end __construct()


	/**
	 * WC object based import
	 *
	 * @param array   $import_data Import data.
	 * @param array   $form_data Form data.
	 * @param integer $batch_offset Offset.
	 * @param bool    $is_last_batch Is last offset.
	 * @return int
	 */
	public function prepare_data_to_import( $import_data, $form_data, $batch_offset, $is_last_batch ) {
		$this->merge = isset( $form_data['advanced_form_data']['wt_iew_merge'] ) ? $form_data['advanced_form_data']['wt_iew_merge'] : 0;
		// wt_iew_wtcreateuser  wt_iew_status_mail wt_iew_ord_link_using_sku.
		$this->skip_new    = isset( $form_data['advanced_form_data']['wt_iew_skip_new'] ) ? $form_data['advanced_form_data']['wt_iew_skip_new'] : 0;
		$this->id_conflict = ! empty( $form_data['advanced_form_data']['wt_iew_id_conflict'] ) ? $form_data['advanced_form_data']['wt_iew_id_conflict'] : 'skip';

		$this->delete_existing = isset( $form_data['advanced_form_data']['wt_iew_delete_existing'] ) ? $form_data['advanced_form_data']['wt_iew_delete_existing'] : 0;

		$this->link_wt_import_key     = isset( $form_data['advanced_form_data']['wt_iew_link_wt_import_key'] ) ? $form_data['advanced_form_data']['wt_iew_link_wt_import_key'] : 0;
		$this->link_product_using_sku = isset( $form_data['advanced_form_data']['wt_iew_link_product_using_skur'] ) ? $form_data['advanced_form_data']['wt_iew_link_product_using_skur'] : 0;

		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );
		wp_suspend_cache_invalidation( true );

		Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', 'Preparing for import.' );

		$success = 0;
		$failed  = 0;
		$msg     = 'Subscription order imported successfully.';
		foreach ( $import_data as $key => $data ) {
			$row = ( $batch_offset + $key + 1 );
			Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', "Row :$row - Parsing item." );
			$parsed_data = $this->parse_subscription_orders( $data, $this->merge );
			if ( ! is_wp_error( $parsed_data ) ) {
				Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', "Row :$row - Processing item." );
				$result = $this->process_subscription_orders( $parsed_data );
				if ( ! is_wp_error( $result ) ) {
					if ( $this->is_order_exist ) {
						$msg = 'Subscription Order updated successfully.';
					}

					$this->import_results[ $row ] = array(
						'row'     => $row,
						'message' => $msg,
						'status'  => true,
						'post_id' => $result['id'],
					);
					Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', "Row :$row - " . $msg );
					$success++;
				} else {
					$this->import_results[ $row ] = array(
						'row'     => $row,
						'message' => $result->get_error_message(),
						'status'  => false,
						'post_id' => '',
					);
					Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', "Row :$row - Processing failed. Reason: " . $result->get_error_message() );
					$failed++;
				}//end if
			} else {
				$this->import_results[ $row ] = array(
					'row'     => $row,
					'message' => $parsed_data->get_error_message(),
					'status'  => false,
					'post_id' => '',
				);
				Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', "Row :$row - Parsing failed. Reason: " . $parsed_data->get_error_message() );
				$failed++;
			}//end if

			unset( $data, $parsed_data );
		}//end foreach

		wp_suspend_cache_invalidation( false );
		wp_defer_term_counting( false );
		wp_defer_comment_counting( false );

		if ( $is_last_batch && $this->delete_existing ) {
			$this->delete_existing();
		}

		$import_response = array(
			'total_success' => $success,
			'total_failed'  => $failed,
			'log_data'      => $this->import_results,
		);

		return $import_response;
	}//end prepare_data_to_import()


	/**
	 * Parse orders
	 *
	 * @param  array   $item Order data.
	 * @param  integer $merge Is merge.
	 * @return array
	 */
	public function parse_subscription_orders( $item, $merge ) {
		try {
			global $wpdb;
			$data = $item['mapping_fields'];
			foreach ( $item['meta_mapping_fields'] as $value ) {
				$data = array_merge( $data, $value );
			}

			/**
			 * Filter the query arguments for a request.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param array      $data  Subscription CSV data.
			 */
			$data = apply_filters( 'wt_subscription_order_importer_pre_parse_data', $data );
			if ( 'import' == $this->id_conflict ) {
				unset( $data['last_order_date_created'] );
			}
			$post_meta = array();
			$result = array();
			$merging = false;
			$billing_and_shipping_addr = array();

			$result['customer_id'] = $data['customer_id'];
			$result['subscription_id'] = ! empty( $data['subscription_id'] ) ? $data['subscription_id'] : 0;
			$result['customer_username'] = $data['customer_username'];
			$result['customer_email'] = $data['customer_email'];
			$result['payment_method'] = $data['payment_method'];
			$result['payment_method'] = ( ! empty( $data['payment_method'] ) ) ? strtolower( $data['payment_method'] ) : '';
			$result['payment_method_title'] = ( ! empty( $data['payment_method_title'] ) ) ? $data['payment_method_title'] : $result['payment_method'];
			$result['order_total'] = ! empty( $data['order_total'] ) ? $data['order_total'] : 0;
			$result['billing_email'] = ( ! empty( $data['billing_email'] ) ) ? $data['billing_email'] : '';

			$this->is_order_exist = false;
			$subscription_id = $result['subscription_id'];

			if ( $subscription_id ) {
				$this->is_order_exist = $this->subscription_order_exists( $subscription_id );
			}
			if ( ! $merge && $this->is_order_exist ) {
				$usr_msg = 'Subscription with same ID already exists.';
				$this->hf_log_data_change( 'hf-subscription-csv-import', /* translators:%s: subscription order number */ sprintf( __( '> &#8220;%s&#8221; Subscription with same ID already exists.' ), esc_html( $subscription_id ) ), true );
				unset( $data );
				return new WP_Error( 'parse-error', /* translators:%s: subscription order number */ sprintf( __( '> &#8220;%s&#8221; Subscription with same ID already exists.' ), esc_html( $subscription_id ) ) );
			}
			if ( ! $this->is_order_exist && $this->skip_new ) {
				$this->hf_log_data_change( 'review-csv-import', '> > Skipping new item.' );
				return new WP_Error( 'parse-error', 'Skipping new item on merge.' );
			}
			if ( $this->is_order_exist ) {
				$merging = true;
			}

			if ( self::$is_hpos_enabled && 'skip' == $this->id_conflict && $subscription_id ) {
				$result_query = $wpdb->query( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}wc_orders WHERE type NOT IN ('shop_subscription','hf_shop_subscription') AND id = %d", $subscription_id ) );
				if ( $result_query ) {
					$this->hf_log_data_change( 'hf-subscription-csv-import'/* translators:%s: subscription order number */, __( '> &#8220;%s&#8221; Importing subscription(ID) conflicts with an existing post.' ), esc_html( $subscription_id ), true );
					unset( $data );
					return new WP_Error( 'parse-error', /* translators:%s: subscription order number */sprintf( __( '> &#8220;%s&#8221;Importing subscription(ID) conflicts with an existing post.' ), esc_html( $subscription_id ) ) );
				}
			} else if ( 'skip' == $this->id_conflict && $subscription_id && is_string( get_post_status( $subscription_id ) ) && ( 'shop_subscription' !== get_post_type( $subscription_id ) ) && ( 'hf_shop_subscription' !== get_post_type( $subscription_id ) ) && ( 'shop_order_placehold' ) !== get_post_type( $subscription_id ) ) {
					$this->hf_log_data_change( 'hf-subscription-csv-import', /* translators:%s: subscription order number */ sprintf( __( '> &#8220;%s&#8221; Importing subscription(ID) conflicts with an existing post.' ), esc_html( $subscription_id ) ), true );
					unset( $data );
					return new WP_Error( 'parse-error', /* translators:%s: subscription order number */sprintf( __( '> &#8220;%s&#8221; Importing subscription(ID) conflicts with an existing post.' ), esc_html( $subscription_id ) ) );
			}

			$missing_shipping_addresses = array();
			$missing_billing_addresses = array();

			$tax_rates = array();

			foreach ( $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates" ) as $_row ) {
				$tax_rates[ $_row->tax_rate_id ] = $_row;
			}

			foreach ( $this->order_meta_fields as $column ) {
				switch ( $column ) {
					case 'cart_discount':
					case 'cart_discount_tax':
					case 'order_shipping':
					case 'order_shipping_tax':
					case 'order_total':
						$value = ( ! empty( $data[ $column ] ) ) ? $data[ $column ] : 0;
						$post_meta[] = array(
							'key' => '_' . $column,
							'value' => $value,
						);
						break;

					case 'payment_method':
						$payment_method = ( ! empty( $data[ $column ] ) ) ? strtolower( $data[ $column ] ) : '';
						$title = ( ! empty( $data['payment_method_title'] ) ) ? $data['payment_method_title'] : $payment_method;

						if ( ! empty( $payment_method ) && 'manual' != $payment_method ) {
							$post_meta[] = array(
								'key' => '_' . $column,
								'value' => $payment_method,
							);
							$post_meta[] = array(
								'key' => '_payment_method_title',
								'value' => $title,
							);
						}
						break;

					case 'shipping_address_1':
					case 'shipping_city':
					case 'shipping_postcode':
					case 'shipping_state':
					case 'shipping_country':
					case 'shipping_phone':
					case 'billing_address_1':
					case 'billing_city':
					case 'billing_postcode':
					case 'billing_state':
					case 'billing_country':
					case 'billing_phone':
					case 'billing_company':
					case 'billing_email':
						$value = ( ! empty( $data[ $column ] ) ) ? $data[ $column ] : '';
						if ( 'billing_phone' == $column || 'shipping_phone' == $column ) {
							$value = trim( $value, '\'' );
						}
						if ( empty( $value ) ) {
							if ( 0 === strpos( $column, 'billing_' ) ) {
								$missing_billing_addresses[] = $column;
							} else {
								$missing_shipping_addresses[] = $column;
							}
						}

						$post_meta[] = array(
							'key' => '_' . $column,
							'value' => $value,
						);
						$billing_and_shipping_addr[ $column ] = $value;
						break;
					case 'billing_first_name':
					case 'billing_last_name':
					case 'billing_address_2':
					case 'shipping_first_name':
					case 'shipping_last_name':
					case 'shipping_address_2':
					case 'shipping_company':
						$value = ( ! empty( $data[ $column ] ) ) ? $data[ $column ] : '';
						$post_meta[] = array(
							'key' => '_' . $column,
							'value' => $value,
						);
						$billing_and_shipping_addr[ $column ] = $value;
						break;

					default:
						$value = ( ! empty( $data[ $column ] ) ) ? $data[ $column ] : '';
						$post_meta[] = array(
							'key' => '_' . $column,
							'value' => $value,
						);
				}
			}

			// Get any custom meta fields.
			foreach ( $data as $key => $value ) {
				if ( ! isset( $value ) ) {
					continue;
				}
				// Handle meta: columns - import as custom fields.
				if ( strstr( $key, 'meta:' ) ) {
					$meta_key = trim( str_replace( 'meta:', '', $key ) );
					// Add to postmeta array.
					$post_meta[] = array(
						'key' => esc_attr( $meta_key ),
						'value' => $value,
					);
				}
			}

			if ( empty( $data['subscription_status'] ) ) {
				$status = 'pending';
				$this->hf_log_data_change( 'hf-subscription-csv-import', __( 'No subscription status was specified. The subscription will be created with the status "pending". ' ) );
			} else {
				$status = $data['subscription_status'];
			}
			$result['subscription_status'] = $status;
			$dates_to_update = array( 'start' => ( ! empty( $data['date_created'] ) ) ? gmdate( 'Y-m-d H:i:s', strtotime( $data['date_created'] ) ) : '' );
			foreach ( array( 'last_order_date_created', 'trial_end_date', 'next_payment_date', 'end_date' ) as $date_type ) {
				$dates_to_update[ $date_type ] = ( ! empty( $data[ $date_type ] ) ) ? gmdate( 'Y-m-d H:i:s', strtotime( $data[ $date_type ] ) ) : '';
				$result[ $date_type ] = $dates_to_update[ $date_type ];
			}
			foreach ( $dates_to_update as $date_type => $datetime ) {
				if ( empty( $datetime ) ) {
					continue;
				}
				switch ( $date_type ) {
					case 'end_date':
						if ( ! empty( $dates_to_update['last_order_date_created'] ) && strtotime( $datetime ) <= strtotime( $dates_to_update['last_order_date_created'] ) ) {
							$this->hf_log_data_change( 'hf-subscription-csv-import', /* translators:%s: Date type ( start, end...) */ sprintf( __( 'The %s date must occur after the last payment date.' ), $date_type ) );
							unset( $data );
							return new WP_Error( 'parse-error', /* translators:%s: Date type ( start, end...) */sprintf( __( 'The %s date must occur after the last payment date.' ), $date_type ) );
						}
						if ( ! empty( $dates_to_update['next_payment_date'] ) && strtotime( $datetime ) <= strtotime( $dates_to_update['next_payment_date'] ) ) {
							$this->hf_log_data_change( 'hf-subscription-csv-import', /* translators:%s: Date type ( start, end...) */sprintf( __( 'The %s date must occur after the next payment date.' ), $date_type ) );
							unset( $data );
								return new WP_Error( 'parse-error', /* translators:%s: Date type ( start, end...) */sprintf( __( 'The %s date must occur after the next payment date.' ), $date_type ) );
						}
							// no break.
					case 'next_payment_date':
						if ( ! empty( $dates_to_update['trial_end_date'] ) && strtotime( $datetime ) < strtotime( $dates_to_update['trial_end_date'] ) ) {
							$this->hf_log_data_change( 'hf-subscription-csv-import', /* translators:%s: Date type ( start, end...) */sprintf( __( 'The %s date must occur after the trial end date.' ), $date_type ) );
							unset( $data );
							return new WP_Error( 'parse-error', /* translators:%s: Date type ( start, end...) */sprintf( __( 'The %s date must occur after the trial end date.' ), $date_type ) );
						}
						// no break.
					case 'trial_end_date':
						if ( strtotime( $datetime ) <= strtotime( $dates_to_update['start'] ) ) {
							$this->hf_log_data_change( 'hf-subscription-csv-import', /* translators:%s: Date type ( start, end...) */sprintf( __( 'The %s must occur after the start date.' ), $date_type ) );
							unset( $data );
							return new WP_Error( 'parse-error', /* translators:%s: Date type ( start, end...) */sprintf( __( 'The %s must occur after the start date.' ), $date_type ) );
						}
						// no break.
				}//end switch
			}//end foreach
			$result['start_date'] = $dates_to_update['start'];
			$result['dates_to_update'] = $dates_to_update;
			$result['post_parent'] = isset( $data['post_parent'] ) ? $data['post_parent'] : 0;
			$result['billing_interval'] = ( ! empty( $data['billing_interval'] ) ) ? $data['billing_interval'] : 1;
			$result['billing_period'] = ( ! empty( $data['billing_period'] ) ) ? $data['billing_period'] : '';
			$result['created_via'] = 'importer';
			$result['customer_note'] = ( ! empty( $data['customer_note'] ) ) ? $data['customer_note'] : '';
			$result['currency'] = ( ! empty( $data['order_currency'] ) ) ? $data['order_currency'] : '';
			$result['post_meta'] = $post_meta;
			$result['billing_and_shipping_addr'] = $billing_and_shipping_addr;

			if ( ! empty( $data['order_notes'] ) ) {

				$order_notes = array();
				if ( ! empty( $data['order_notes'] ) ) {
					$order_notes = explode( '||', $data['order_notes'] );
				}
				$result['order_notes'] = $order_notes;
			}

			if ( ! empty( $data['renewal_orders'] ) ) {
				$result['renewal_orders'] = $data['renewal_orders'];
			}

			if ( ! empty( $data['coupon_items'] ) ) {
				$result['coupon_items'] = $data['coupon_items'];
			}

			if ( ! empty( $data['tax_items'] ) ) {
				$tax_item = explode( ';', $data['tax_items'] );
				$tax_items = array();
				foreach ( $tax_item as $tax ) {

					$tax_item_data = array();
					// turn "label: Tax | tax_amount: 10" into an associative array.
					foreach ( explode( '|', $tax ) as $piece ) {
						list( $name, $value ) = array_pad( explode( ':', $piece ), 2, null );
						if ( isset( $name ) ) {
							$tax_item_data[ trim( $name ) ] = trim( $value );
						}
					}
					// default rate id to 0 if not set.
					if ( ! isset( $tax_item_data['rate_id'] ) ) {
						$tax_item_data['rate_id'] = 0;
					}
					$old_rate_id = $tax_item_data['rate_id'];
					if ( ! isset( $tax_item_data['rate_percent'] ) ) {
						$tax_item_data['rate_percent'] = '';
					}
					// default rate id to 0 if rate id is not present in $tax_rates.
					if ( ( $tax_item_data['rate_id'] && ! isset( $tax_rates[ $tax_item_data['rate_id'] ] ) ) || 0 == $tax_item_data['rate_id'] ) {
						$tax_item_data['rate_id'] = 0;
					} else if ( isset( $tax_item_data['label'] ) && ( ( 0 !== strcasecmp( $tax_rates[ $tax_item_data['rate_id'] ]->tax_rate_name, $tax_item_data['label'] ) ) || ( $tax_rates[ $tax_item_data['rate_id'] ]->tax_rate != $tax_item_data['rate_percent'] ) ) ) {
						// label or rate percent not match.
						$tax_item_data['rate_id'] = 0;
					}
					// have a tax amount or shipping tax amount.
					if ( ( isset( $tax_item_data['total'] ) || isset( $tax_item_data['shipping_tax_amount'] ) ) && wc_tax_enabled() ) {
						// try and look up rate id by label if needed.
						if ( isset( $tax_item_data['label'] ) && $tax_item_data['label'] && ! $tax_item_data['rate_id'] ) {
							foreach ( $tax_rates as $tax_rate ) {
								// if label match check rate id is matching.
								if ( 0 === strcasecmp( $tax_rate->tax_rate_name, $tax_item_data['label'] ) && $tax_rate->tax_rate == $tax_item_data['rate_percent'] ) {
									// found the tax by label.
									$tax_item_data['rate_id'] = $tax_rate->tax_rate_id;
									break;
								}
							}
						}
						// default label of 'Tax' if not provided.
						if ( ! isset( $tax_item_data['label'] ) || ! $tax_item_data['label'] ) {
							$tax_item_data['label'] = 'Tax';
						}

						// default tax amounts to 0 if not set.
						if ( ! isset( $tax_item_data['total'] ) ) {
							$tax_item_data['total'] = 0;
						}
						if ( ! isset( $tax_item_data['shipping_tax_amount'] ) ) {
							$tax_item_data['shipping_tax_amount'] = 0;
						}

						// handle compound flag by using the defined tax rate value (if any).
						if ( ! isset( $tax_item_data['tax_rate_compound'] ) ) {
							$tax_item_data['tax_rate_compound'] = '';
							if ( $tax_item_data['rate_id'] ) {
								$tax_item_data['tax_rate_compound'] = $tax_rates[ $tax_item_data['rate_id'] ]->tax_rate_compound;
							}
						}

						$tax_items[ $old_rate_id ] = array(
							'title' => $tax_item_data['code'],
							'rate_id' => $tax_item_data['rate_id'],
							'label' => $tax_item_data['label'],
							'compound' => $tax_item_data['tax_rate_compound'],
							'tax_amount' => $tax_item_data['total'],
							'shipping_tax_amount' => $tax_item_data['shipping_tax_amount'],
							'rate_percent' => $tax_item_data['rate_percent'],
						);
					}
				}

				$result['tax_items'] = $tax_items;
			}

			if ( ! empty( $data['order_items'] ) ) {
				$_order_items = explode( '||', $data['order_items'] );
				foreach ( $_order_items as $item ) {
					if ( ! empty( $item ) ) {
						/**
						 * Filter the query arguments for a request.
						 *
						 * Enables adding extra arguments or setting defaults for the request.
						 *
						 * @since 1.0.0
						 *
						 * @param array      $separator  Line item separator.
						 */
						$_item_meta = explode( apply_filters( 'wt_subscription_change_item_separator', '|' ), $item );
					}

					// get any additional item meta.
					$item_meta = array();
					foreach ( $_item_meta as $pair ) {

						// replace any escaped pipes.
						$pair = str_replace( '\|', '|', $pair );

						// find the first ':' and split into name-value.
						$split = strpos( $pair, ':' );

						$name = substr( $pair, 0, $split );

						$value = substr( $pair, $split + 1 );

						switch ( $name ) {
							case 'name':
								$product_name = $value;
								break;
							case 'product_id':
								$product_id = $value;
								break;
							case 'sku':
								$sku = $value;
								break;
							case 'quantity':
								$qty = $value;
								break;
							case 'total':
								$total = $value;
								break;
							case 'sub_total':
								$sub_total = $value;
								break;
							case 'tax':
								$tax = $value;
								break;
							case 'tax_data':
								$tax_data = $value;
								break;
							default:
								$item_meta[ $name ] = $value;
						}
					}
					$tax_data = maybe_unserialize( $tax_data );
					if ( ! empty( $tax_data ) ) {
						$tax_data = maybe_unserialize( $tax_data );
						$new_tax_data = array();
						if ( isset( $tax_data['total'] ) ) {
							foreach ( $tax_data['total'] as $t_key => $t_value ) {
								if ( isset( $result['tax_items'][ $t_key ] ) ) {
									$new_tax_data ['total'][ $result['tax_items'][ $t_key ]['rate_id'] ] = $t_value;
								} else {
									$new_tax_data ['total'][ $t_key ] = $t_value;
								}
							}
						}
						if ( isset( $tax_data['subtotal'] ) ) {
							foreach ( $tax_data['subtotal'] as $st_key => $st_value ) {
								if ( isset( $result['tax_items'][ $st_key ] ) ) {
									$new_tax_data ['subtotal'][ $result['tax_items'][ $st_key ]['rate_id'] ] = $st_value;
								} else {
									$new_tax_data ['subtotal'][ $st_key ] = $st_value;
								}
							}
						}
					}
					$tax_data = maybe_serialize( $new_tax_data );
					$order_items[] = array(
						'product_id' => $product_id,
						'sku' => $sku,
						'qty' => $qty,
						'total' => $total,
						'sub_total' => $sub_total,
						'tax' => isset( $tax ) ? $tax : '',
						'tax_data' => $tax_data,
						'meta' => $item_meta,
						'name' => $product_name,
					);
				}

				$result['order_items'] = $order_items;
			}

			if ( ! empty( $data['order_currency'] ) ) {
				$result['order_currency'] = $data['order_currency'];
			}

			if ( ! empty( $data['fee_items'] ) ) {
				$result['fee_items'] = $data['fee_items'];
			}

			if ( ! empty( $data['shipping_method'] ) ) {
				$result['shipping_method'] = $data['shipping_method'];
			}

			$shipping_items = array();
			$shipping_line_items = array();
			if ( ! empty( $data['shipping_items'] ) ) {
				$shipping_line_items = explode( ';', $data['shipping_items'] );
				$shipping_item_data = array();
				foreach ( $shipping_line_items as $shipping_line_item ) {
					foreach ( explode( '|', $shipping_line_item ) as $piece ) {
						list( $name, $value ) = explode( ':', $piece );
						$shipping_item_data[ trim( $name ) ] = trim( $value );
					}
					if ( ! isset( $shipping_item_data['item'] ) ) {
						$shipping_item_data['item'] = '';
					}
					if ( ! isset( $shipping_item_data['value'] ) ) {
						$shipping_item_data['value'] = 0;
					}
					$shipping_items[] = array(
						'item' => $shipping_item_data['item'],
						'value' => $shipping_item_data['value'],
					);
				}
				$result['shipping_items'] = $shipping_items;
			}

			$result['merging'] = $merging;
			return $result;
		} catch ( Exception $e ) {
			return new WP_Error( 'woocommerce_product_importer_error', $e->getMessage(), array( 'status' => $e->getCode() ) );
		}//end try
	}//end parse_subscription_orders()

	/**
	 * Get currency formater
	 *
	 * @param float $price Price.
	 * @return type
	 */
	public function hf_currency_formatter( $price ) {
		$decimal_seperator = wc_get_price_decimal_separator();
		// @codingStandardsIgnoreStart
		return preg_replace( "[^0-9\\'.$decimal_seperator.']", '', $price );
		// @codingStandardsIgnoreEnd
	}//end hf_currency_formatter()


	/**
	 * Create new posts based on import information
	 *
	 * @param array $data Order data.
	 */
	private function process_subscription_orders( $data ) {
		try {
			/**
			 * Filter the query arguments for a request.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param array      $data  Subscription data.
			 */
			do_action( 'wt_subscription_order_import_before_process_item', $data );
			global $wpdb;
			$merge          = ! empty( $data['merging'] );
			$is_order_exist = $this->is_order_exist;

			$add_memberships = false; /* ( isset( $_POST['add_memberships'] ) ) ? sanitize_text_field( wp_unslash( $_POST['add_memberships'] ) ) : false; */
			$this->hf_log_data_change( 'hf-subscription-csv-import', __( 'Process start..' ) );
			$this->hf_log_data_change( 'hf-subscription-csv-import', __( 'Processing subscriptions...' ) );
			$email_customer = false;
			// set this as settings for choosing weather to mail details for newly created customers.
			$user_id = $this->hf_check_customer( $data, $email_customer );
			$data['customer_id'] = $user_id;
			if ( is_wp_error( $user_id ) ) {
				$this->hf_log_data_change( 'hf-subscription-csv-import', $user_id->get_error_message() );
				unset( $data );
				return new WP_Error( 'parse-error', $user_id->get_error_message() );
			} else if ( empty( $user_id ) ) {
				unset( $data );
				return new WP_Error( 'parse-error', __( 'An error occurred with the customer information provided.' ) );
			}

			// check whether download permissions need to be granted.
			$add_download_permissions = false;
			// Check if post exists when importing.
			$new_added = false;
			if ( ( ! empty( $data['post_parent'] ) ) && $this->link_wt_import_key ) {
				// Check whether post_parent (Parent order ID) is an order or not.
				$data['post_parent'] = self::wt_get_order_with_import_key( $data['post_parent'] );
			} else {
				// Check whether post_parent (Parent order ID) is an order or not.
				$temp_parent_order_exist = wc_get_order( $data['post_parent'] );
				$data['post_parent']     = ( $temp_parent_order_exist && $temp_parent_order_exist->get_type() == 'shop_order' ) ? $data['post_parent'] : '';
			}

			if ( $is_order_exist ) {

				$subscription = $this->hf_create_subscription( $data, true );

				$new_added = false;
				if ( is_wp_error( $subscription ) ) {
					$this->errored++;
					$new_added = false;
					$order_number = $data['order_number'];
					unset( $data );
					return new WP_Error( 'parse-error', /* translators: 1: order number. 2: error message */ sprintf( __( '> Error inserting %1$s: %2$s' ), $order_number, $subscription->get_error_message() ) );
				}
			} else {
				if ( class_exists( 'HF_Subscription' ) ) {
					$subscription_data['status'] = $data['subscription_status'];
				}

				$subscription = $this->hf_create_subscription( $data );
				$new_added = true;
				if ( is_wp_error( $subscription ) ) {
					$this->errored++;
					$new_added = false;
					unset( $data );
					return new WP_Error( 'parse-error', $subscription->get_error_message() );
				}
			}//end if
			if ( self::$is_hpos_enabled && ! class_exists( 'HF_Subscription' ) ) {
				$billing_address = array(
					'first_name' => $data['billing_and_shipping_addr']['billing_first_name'],
					'last_name' => $data['billing_and_shipping_addr']['billing_last_name'],
					'company' => $data['billing_and_shipping_addr']['billing_company'],
					'address_1' => $data['billing_and_shipping_addr']['billing_address_1'],
					'address_2' => $data['billing_and_shipping_addr']['billing_address_2'],
					'city' => $data['billing_and_shipping_addr']['billing_city'],
					'state' => $data['billing_and_shipping_addr']['billing_state'],
					'postcode' => $data['billing_and_shipping_addr']['billing_postcode'],
					'country' => $data['billing_and_shipping_addr']['billing_country'],
					'email' => $data['billing_and_shipping_addr']['billing_email'],
					'phone' => $data['billing_and_shipping_addr']['billing_phone'],
				);
				$subscription->set_billing_address( $billing_address );
				$shipping_address = array(
					'first_name' => $data['billing_and_shipping_addr']['shipping_first_name'],
					'last_name' => $data['billing_and_shipping_addr']['shipping_last_name'],
					'company' => $data['billing_and_shipping_addr']['shipping_company'],
					'address_1' => $data['billing_and_shipping_addr']['shipping_address_1'],
					'address_2' => $data['billing_and_shipping_addr']['shipping_address_2'],
					'city' => $data['billing_and_shipping_addr']['shipping_city'],
					'state' => $data['billing_and_shipping_addr']['shipping_state'],
					'postcode' => $data['billing_and_shipping_addr']['shipping_postcode'],
					'country' => $data['billing_and_shipping_addr']['shipping_country'],
					'phone' => $data['billing_and_shipping_addr']['shipping_phone'],
				);

				$subscription->set_shipping_address( $shipping_address );
			}
			$subscription->update_meta_data( '_billing_address_index', implode( ' ', $subscription->get_address( 'billing' ) ) );
			$subscription->update_meta_data( '_shipping_address_index', implode( ' ', $subscription->get_address( 'shipping' ) ) );
			$current_order_ids = array();
			if ( ! empty( $data['renewal_orders'] ) ) {
				$renewal_orders = explode( '|', $data['renewal_orders'] );
				if ( $this->link_wt_import_key ) {
					foreach ( $renewal_orders as $order_id ) {
						$current_order_ids[] = self::wt_get_order_with_import_key( $order_id );
					}
				} else {
					foreach ( $renewal_orders as $order_id ) {
						$order = WC()->order_factory->get_order( $order_id );
						if ( is_object( $order ) ) {
							$current_order_ids[] = $order_id;
						}
					}
				}

				$current_order_ids = array_filter( $current_order_ids );
				if ( ! empty( $current_order_ids ) && ! class_exists( 'HF_Subscription' ) ) {
					update_option( '_transient_wcs-related-orders-to-' . ( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id ), $current_order_ids );
				}
				foreach ( $current_order_ids as $id ) {
					if ( self::$is_hpos_enabled ) {
						$order = WC()->order_factory->get_order( $id );
						$order->update_meta_data( '_subscription_renewal', ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id );
					}
					if ( false !== strpos( self::$table_name, 'posts' ) || self::$is_sync ) {
						update_post_meta( $id, '_subscription_renewal', ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id );
					}
				}
			}//end if

			foreach ( $data['post_meta'] as $meta_data ) {
				switch ( $meta_data['key'] ) {
					case '_billing_email':
						$_billing_email = $meta_data['value'];
						// keep _billing_email for update _billing_email after update $subscription->update_dates and $subscription->update_status.
					case '_coupon_items':
						break;
					case '_download_permissions':
						$add_download_permissions     = true;
						$data['download_permissions'] = $meta_data['value'];
						if ( self::$is_hpos_enabled ) {
							$subscription->update_meta_data( '_download_permissions_granted', $meta_data['value'] );
						}
						if ( false !== strpos( self::$table_name, 'posts' ) || self::$is_sync ) {
							update_post_meta( ( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id ), '_download_permissions_granted', $meta_data['value'] );
						}
						break;
					case '_requires_manual_renewal':
						if ( 0 == $meta_data['value'] ) {
							$meta_data['value'] = false;
						}
						$subscription->set_requires_manual_renewal( $meta_data['value'] );
						break;
					default:
						if ( self::$is_hpos_enabled ) {
							$meta_function = 'set_' . ltrim( $meta_data['key'], '_' );
							if ( is_callable( array( $subscription, $meta_function ) ) ) {
								$subscription->{$meta_function}( $meta_data['value'] );
							} else {
								$subscription->update_meta_data( $meta_data['key'], $meta_data['value'] );
							}
						}
						if ( false !== strpos( self::$table_name, 'posts' ) || self::$is_sync ) {
							update_post_meta( ( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id ), $meta_data['key'], $meta_data['value'] );
						}
				}
			}
			if ( self::$is_hpos_enabled ) {
				$order_operational_data = array();
				foreach ( $data['post_meta'] as $meta_data ) {
					switch ( $meta_data['key'] ) {
						case '_order_shipping_tax':
							$order_operational_data['shipping_tax_amount'] = $meta_data['value'];
							break;
						case '_order_shipping':
							$order_operational_data['shipping_total_amount'] = $meta_data['value'];
							break;
						case '_cart_discount_tax':
							$order_operational_data['discount_tax_amount'] = $meta_data['value'];
							break;
						case '_cart_discount':
							$order_operational_data['discount_total_amount'] = $meta_data['value'];
							break;
						default:
							break;
					}
				}
				if ( ! empty( $order_operational_data ) ) {
					$operational_data = array();
					$operational_data[] = $order_operational_data['shipping_tax_amount'] ? $order_operational_data['shipping_tax_amount'] : 0;
					$operational_data[] = $order_operational_data['shipping_total_amount'] ? $order_operational_data['shipping_total_amount'] : 0;
					$operational_data[] = $order_operational_data['discount_tax_amount'] ? $order_operational_data['discount_tax_amount'] : 0;
					$operational_data[] = $order_operational_data['discount_total_amount'] ? $order_operational_data['discount_total_amount'] : 0;
					$operational_data['order_id'] = ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id;
					$wpdb->query( $wpdb->prepare( " UPDATE {$wpdb->prefix}wc_order_operational_data SET shipping_tax_amount= %f,shipping_total_amount=%f,discount_tax_amount=%f,discount_total_amount=%f WHERE order_id=%d", $operational_data ) );
				}
			}

			try {
				$subscription->update_dates( $data['dates_to_update'] );
				$subscription->save();
			} catch ( Exception $e ) {
				unset( $data );
				return new WP_Error( 'parse-error', $e->getMessage() );
			}

			$result['items'] = isset( $result['items'] ) ? $result['items'] : '';
			if ( ! empty( $data['order_items'] ) ) {
				$wpdb->query( $wpdb->prepare( "DELETE items,itemmeta FROM {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items ON itemmeta.order_item_id = items.order_item_id WHERE items.order_id = %d and items.order_item_type = 'line_item'", ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id ) );
				if ( is_numeric( $data['order_items'] ) ) {
					$product_id      = absint( $data['order_items'] );
					$result['items'] = self::add_product( $data, $subscription, array( 'product_id' => $product_id ), $this->link_product_using_sku );
					if ( $add_memberships ) {
						self::maybe_add_memberships( $user_id, ( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id ), $product_id );
					}
				} else {
					foreach ( $data['order_items'] as $order_item ) {
						$result['items'] .= self::add_product( $data, $subscription, $order_item, $this->link_product_using_sku ) . '<br/>';

						if ( $add_memberships ) {
							self::maybe_add_memberships( $user_id, ( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id ), $item_data['product_id'] );
						}
					}
				}
			}//end if

			if ( ! empty( $data['shipping_method'] ) ) {
				$wpdb->query( $wpdb->prepare( "DELETE items,itemmeta FROM {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items ON itemmeta.order_item_id = items.order_item_id WHERE items.order_id = %d and items.order_item_type = 'shipping'", ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id ) );
				$shipping_item = explode( '|', $data['shipping_method'] );
				$method_id     = array_shift( $shipping_item );
				$method_id     = substr( $method_id, ( strpos( $method_id, ':' ) + 1 ) );
				$method_title  = array_shift( $shipping_item );
				$method_title  = substr( $method_title, ( strpos( $method_title, ':' ) + 1 ) );
				$total         = array_shift( $shipping_item );
				$total         = substr( $total, ( strpos( $total, ':' ) + 1 ) );
				$total_tax     = array_shift( $shipping_item );
				$total_tax     = substr( $total_tax, ( strpos( $total_tax, ':' ) + 1 ) );
				$taxes         = array_shift( $shipping_item );
				$taxes         = substr( $taxes, ( strpos( $taxes, ':' ) + 1 ) );
				$tax_data = maybe_unserialize( $taxes );
				if ( isset( $tax_data['total'] ) ) {
					$new_tax_data = array();
					foreach ( $tax_data['total'] as $t_key => $t_value ) {
						if ( isset( $data['tax_items'][ $t_key ] ) ) {
							$new_tax_data ['total'][ $data['tax_items'][ $t_key ]['rate_id'] ] = $t_value;
						} else {
							$new_tax_data ['total'][ $t_key ] = $t_value;
						}
					}
				} else {
					$new_tax_data = $tax_data;
				}
				$shipping_order_item = array(
					'order_item_name' => ( $method_title ) ? $method_title : $method_id,
					'order_item_type' => 'shipping',
				);

				$shipping_order_item_id = wc_add_order_item( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id, $shipping_order_item );

				if ( $shipping_order_item_id ) {
					wc_add_order_item_meta( $shipping_order_item_id, 'method_id', $method_id );
					wc_add_order_item_meta( $shipping_order_item_id, 'cost', $total );
					wc_add_order_item_meta( $shipping_order_item_id, 'total_tax', $total_tax );
					wc_add_order_item_meta( $shipping_order_item_id, 'taxes', maybe_serialize( $new_tax_data ) );

				}
			}//end if

			if ( ! empty( $data['shipping_items'] ) && ! empty( $data['shipping_method'] ) ) {
				foreach ( $data['shipping_items'] as $shipping_item ) {
					if ( $shipping_order_item_id ) {
						wc_add_order_item_meta( $shipping_order_item_id, $shipping_item['item'], $shipping_item['value'] );
					} else {
						$shipping_order_item_id = wc_add_order_item( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id, $shipping_order_item );
						wc_add_order_item_meta( $shipping_order_item_id, $shipping_item['item'], $shipping_item['value'] );
					}
				}
			}

			if ( ! empty( $data['fee_items'] ) ) {
				$fee_str = 'fee';
				$wpdb->query( $wpdb->prepare( "DELETE items,itemmeta FROM {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items WHERE itemmeta.order_item_id = items.order_item_id and items.order_id = %d and items.order_item_type = %s", ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id, $fee_str ) );
				$fee_items = explode( ';', $data['fee_items'] );
				foreach ( $fee_items as $item ) {
					$fee_item       = explode( '|', $item );
					$name           = array_shift( $fee_item );
					$name           = substr( $name, ( strpos( $name, ':' ) + 1 ) );
					$total          = array_shift( $fee_item );
					$total          = substr( $total, ( strpos( $total, ':' ) + 1 ) );
					$tax            = array_shift( $fee_item );
					$tax            = substr( $tax, ( strpos( $tax, ':' ) + 1 ) );
					$tax_data       = array_shift( $fee_item );
					$tax_data       = substr( $tax_data, ( strpos( $tax_data, ':' ) + 1 ) );
					$tax_class      = array_shift( $fee_item );
					$tax_class      = substr( $tax_class, ( strpos( $tax_class, ':' ) + 1 ) );
					$tax_data = maybe_unserialize( $tax_data );
					if ( isset( $tax_data['total'] ) ) {
						foreach ( $tax_data['total'] as $t_key => $t_value ) {
							if ( isset( $data['tax_items'][ $t_key ] ) ) {
								$new_tax_data ['total'][ $data['tax_items'][ $t_key ]['rate_id'] ] = $t_value;
							} else {
								$new_tax_data ['total'][ $t_key ] = $t_value;
							}
						}
					} else {
						$new_tax_data = $tax_data;
					}
					$tax_data = maybe_serialize( $new_tax_data );
					$fee_order_item = array(
						'order_item_name' => $name ? $name : '',
						'order_item_type' => 'fee',
					);
					$fee_order_item_id = wc_add_order_item( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id, $fee_order_item );
					if ( $fee_order_item_id ) {
						wc_add_order_item_meta( $fee_order_item_id, '_line_total', $total );
						wc_add_order_item_meta( $fee_order_item_id, '_line_tax', $tax );
						wc_add_order_item_meta( $fee_order_item_id, '_line_tax_data', $tax_data );
						wc_add_order_item_meta( $fee_order_item_id, '_tax_class', $tax_class );
					}
				}//end foreach
			}//end if

			$chosen_tax_rate_id = 0;
			if ( ! empty( $data['tax_items'] ) ) {
				$tax_str = 'tax';
				$wpdb->query( $wpdb->prepare( "DELETE items,itemmeta FROM {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items WHERE itemmeta.order_item_id = items.order_item_id and items.order_id = %d and items.order_item_type = %s", ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id, $tax_str ) );
				foreach ( $data['tax_items'] as $tax_item ) {
					$tax_order_item    = array(
						'order_item_name' => $tax_item['title'],
						'order_item_type' => 'tax',
					);
					$tax_order_item_id = wc_add_order_item( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id, $tax_order_item );
					if ( $tax_order_item_id ) {
						wc_add_order_item_meta( $tax_order_item_id, 'rate_id', $tax_item['rate_id'] );
						wc_add_order_item_meta( $tax_order_item_id, 'label', $tax_item['label'] );
						wc_add_order_item_meta( $tax_order_item_id, 'compound', $tax_item['compound'] );
						wc_add_order_item_meta( $tax_order_item_id, 'tax_amount', $tax_item['tax_amount'] );
						wc_add_order_item_meta( $tax_order_item_id, 'shipping_tax_amount', $tax_item['shipping_tax_amount'] );
						wc_add_order_item_meta( $tax_order_item_id, 'rate_percent', $tax_item['rate_percent'] );
					}
				}
			}//end if

			if ( ! empty( $data['coupon_items'] ) ) {
				if ( $merge && $is_order_exist ) {
					$applied_coupons = $subscription->get_used_coupons();
					if ( ! empty( $applied_coupons ) ) {
						foreach ( $applied_coupons as $coupon ) {
							$subscription->remove_coupon( $coupon );
						}
					}
				}

				self::add_coupons( $subscription, $data );
			}

			// add order notes.
			if ( ! empty( $data['order_notes'] ) ) {
				add_filter( 'woocommerce_email_enabled_customer_note', '__return_false' );
					$wpdb->query( $wpdb->prepare( "DELETE comments,meta FROM {$wpdb->prefix}comments comments LEFT JOIN {$wpdb->prefix}commentmeta meta ON comments.comment_ID = meta.comment_id WHERE comments.comment_post_ID = %d", ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id ) );
				foreach ( $data['order_notes'] as $order_note ) {
					$note     = explode( '|', $order_note );
					$con      = array_shift( $note );
					$con      = substr( $con, ( strpos( $con, ':' ) + 1 ) );
					$date     = array_shift( $note );
					$date     = substr( $date, ( strpos( $date, ':' ) + 1 ) );
					$cus      = array_shift( $note );
					$cus      = substr( $cus, ( strpos( $cus, ':' ) + 1 ) );
					$system   = array_shift( $note );
					$added_by = substr( $system, ( strpos( $system, ':' ) + 1 ) );
					if ( 'system' == $added_by ) {
						$added_by_user = false;
					} else {
						$added_by_user = true;
					}

					if ( '1' == $cus ) {
						$comment_id = $subscription->add_order_note( $con, 1, 1 );
					} else {
						$comment_id = $subscription->add_order_note( $con, 0, $added_by_user );
					}

					wp_update_comment(
						array(
							'comment_ID' => $comment_id,
							'comment_date' => $date,
						)
					);
				}//end foreach
			}//end if

			// only show the following warnings on the import when the subscription requires shipping.
			if ( ! self::$all_virtual ) {
				if ( ! empty( $missing_shipping_addresses ) ) {
					/* translators:%s: Shipping addess field */
					$result['warning'][] = sprintf( __( 'The following shipping address fields have been left empty: %s' ), rtrim( implode( ', ', $missing_shipping_addresses ), ',' ) . '. ' );
				}

				if ( ! empty( $missing_billing_addresses ) ) {
					/* translators:%s: Billing addess field */
					$result['warning'][] = sprintf( __( 'The following billing address fields have been left empty: %s' ), rtrim( implode( ', ', $missing_billing_addresses ), ',' ) . '. ' );
				}

				if ( empty( $shipping_method ) ) {
					$result['warning'][] = esc_html__( 'Shipping method and title for the subscription have been left as empty. ' );
				}
			}

			if ( $merge && ! $new_added ) {
				$out_msg = 'Subscription updated successfully';
			} else {
				$out_msg = 'Subscription Imported Successfully.';
			}

			/* translators: 1: subscription number. 2: success message */
			$this->hf_log_data_change( 'hf-subscription-csv-import', sprintf( __( '> &#8220;%1$s&#8221; %2$s' ), esc_html( $data['subscription_id'] ), $out_msg ), true );
			// $this->imported++;
			/* translators:%s: subscription number */
			$this->hf_log_data_change( 'hf-subscription-csv-import', sprintf( __( '> Finished importing subscription %s' ), $data['subscription_id'] ) );
			$this->hf_log_data_change( 'hf-subscription-csv-import', __( 'Finished processing subscription.' ) );
			$data['is_subscription_exist'] = $this->is_order_exist;
			/**
			 * Filter the query arguments for a request.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.6
			 *
			 * @param object $subscription    Subscription object.
			 * @param array  $data   Subscription created.
			 */
			do_action( 'wt_woocommerce_subscription_import_inserted_subscription_object', $subscription, $data );
			unset( $data );

			if ( $this->delete_existing ) {
				update_post_meta( ( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id ), '_wt_delete_existing', 1 );
			}

			return array( 'id' => ( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id ) );
		} catch ( Exception $e ) {
			return new WP_Error( 'woocommerce_product_importer_error', $e->getMessage(), array( 'status' => $e->getCode() ) );
		}//end try
	}//end process_subscription_orders()

	/**
	 * Subscription exist
	 *
	 * @global type $wpdb
	 * @param intger $orderid Order id.
	 * @return boolean
	 */
	public function subscription_order_exists( $orderid ) {
		global $wpdb;
		if ( class_exists( 'HF_Subscription' ) ) {
			$args = 'hf_shop_subscription';
		} else {
			$args = 'shop_subscription';
		}
		if ( self::$is_hpos_enabled ) {
			$posts_are_exist = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM  {$wpdb->prefix}wc_orders WHERE type = %s AND status IN ( 'wc-pending-cancel','wc-expired','wc-switched','wc-cancelled','wc-on-hold','wc-active','wc-pending') AND id=%d ", $args, $orderid ) );
		} else {
			$posts_are_exist = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN ( 'wc-pending-cancel','wc-expired','wc-switched','wc-cancelled','wc-on-hold','wc-active','wc-pending') AND ID=%d ", $args, $orderid ) );
		}
		if ( $posts_are_exist ) {
			foreach ( $posts_are_exist as $exist_id ) {
				$found = false;
				if ( $exist_id == $orderid ) {
					$found = true;
				}

				if ( $found ) {
					return true;
				}
			}
		} else {
			return false;
		}
	}//end subscription_order_exists()

	/**
	 * Check customer exist
	 *
	 * @param array   $data Customer data.
	 * @param boolean $email_customer Send mail.
	 * @return \WP_Error
	 */
	public function hf_check_customer( $data, $email_customer = false ) {
		$customer_email = ( ! empty( $data['customer_email'] ) ) ? $data['customer_email'] : '';
		$username       = ( ! empty( $data['customer_username'] ) ) ? $data['customer_username'] : '';
		$customer_id    = ( ! empty( $data['customer_id'] ) ) ? $data['customer_id'] : '';
		if ( ! empty( $data['_customer_password'] ) ) {
			$password           = $data['_customer_password'];
			$password_generated = false;
		} else {
			$password           = wp_generate_password( 12, true );
			$password_generated = true;
		}

		$found_customer = false;
		$order_exist    = $this->subscription_order_exists( $data['subscription_id'] );
		if ( $order_exist && empty( $customer_email ) && 1 == $this->merge ) {
			$order          = new WC_Order( $data['subscription_id'] );
			$customer_id    = $order->get_customer_id();
			$user_info      = get_userdata( $customer_id );
			$customer_email = $user_info->user_email;
		}

		if ( ! empty( $customer_email ) ) {
			if ( is_email( $customer_email ) && false !== email_exists( $customer_email ) ) {
				$found_customer = email_exists( $customer_email );
			} else if ( ! empty( $username ) && false !== username_exists( $username ) ) {
				$found_customer = username_exists( $username );
			} else if ( is_email( $customer_email ) ) {
				// Not in test mode, create a user account for this email.
				if ( empty( $username ) ) {
					$maybe_username = explode( '@', $customer_email );
					$maybe_username = sanitize_user( $maybe_username[0] );
					$counter        = 1;
					$username       = $maybe_username;
					while ( username_exists( $username ) ) {
						$username = $maybe_username . $counter;
						$counter++;
					}
				}

				$found_customer = wp_create_user( $username, $password, $customer_email );
				if ( ! is_wp_error( $found_customer ) ) {
					// update user meta data.
					foreach ( self::$user_meta_fields as $key ) {
						switch ( $key ) {
							case '_billing_email':
								// user billing email if set in csv otherwise use the user's account email.
								$meta_value = ( ! empty( $data['post_meta'][ $key ] ) ) ? $data['post_meta'][ $key ] : $customer_email;
								$key        = substr( $key, 1 );
								update_user_meta( $found_customer, $key, $meta_value );
								break;
							case '_billing_first_name':
								$meta_value = ( ! empty( $data['post_meta'][ $key ] ) ) ? $data['post_meta'][ $key ] : $username;
								$key        = substr( $key, 1 );
								update_user_meta( $found_customer, $key, $meta_value );
								update_user_meta( $found_customer, 'first_name', $meta_value );
								break;
							case '_billing_last_name':
								$meta_value = ( ! empty( $data['post_meta'][ $key ] ) ) ? $data['post_meta'][ $key ] : '';
								$key        = substr( $key, 1 );
								update_user_meta( $found_customer, $key, $meta_value );
								update_user_meta( $found_customer, 'last_name', $meta_value );
								break;
							case '_shipping_first_name':
							case '_shipping_last_name':
							case '_shipping_address_1':
							case '_shipping_address_2':
							case '_shipping_city':
							case '_shipping_postcode':
							case '_shipping_state':
							case '_shipping_country':
								// Set the shipping address fields to match the billing fields if not specified in CSV.
								$meta_value = ( ! empty( $data['post_meta'][ $key ] ) ) ? $data['post_meta'][ $key ] : '';

								if ( empty( $meta_value ) ) {
									$n_key      = str_replace( 'shipping', 'billing', $key );
									$meta_value = ( ! empty( $data['post_meta'][ $n_key ] ) ) ? $data['post_meta'][ $n_key ] : '';
								}

								$key = substr( $key, 1 );
								update_user_meta( $found_customer, $key, $meta_value );
								break;

							default:
								$meta_value = ( ! empty( $data['post_meta'][ $key ] ) ) ? $data['post_meta'][ $key ] : '';
								$key        = substr( $key, 1 );
								update_user_meta( $found_customer, $key, $meta_value );
						}//end switch
					}//end foreach

					$this->hf_make_user_active( $found_customer );
					// send user registration email if admin as chosen to do so.
					if ( $email_customer && function_exists( 'wp_new_user_notification' ) ) {
						$previous_option = get_option( 'woocommerce_registration_generate_password' );
						// force the option value so that the password will appear in the email.
						update_option( 'woocommerce_registration_generate_password', 'yes' );
						/**
						 * Filter the query arguments for a request.
						 *
						 * Enables adding extra arguments or setting defaults for the request.
						 *
						 * @since 1.0.0
						 *
						 * @param int      $found_customer  User id.
						 * @param array    $data    User data.
						 * @param boolean  $created   After user created.
						 */
						do_action( 'woocommerce_created_customer', $found_customer, array( 'user_pass' => $password ), true );

						update_option( 'woocommerce_registration_generate_password', $previous_option );
					}
				}//end if
			}//end if
		} else {
			$found_customer = new WP_Error( 'hf_invalid_customer', sprintf( __( 'User could not be created without Email.' ), $customer_id ) );
		}//end if

		return $found_customer;
	}//end hf_check_customer()

	/**
	 * Mark user active
	 *
	 * @param integer $user_id User id.
	 */
	public function hf_make_user_active( $user_id ) {
		$this->hf_update_users_role( $user_id, 'default_subscriber_role' );
	}//end hf_make_user_active()


	/**
	 * Update a user's role to a special subscription's role
	 *
	 * @param  int    $user_id  The ID of a user.
	 * @param  string $role_new The special name assigned to the role by Subscriptions,
	 *                          one of 'default_subscriber_role', 'default_inactive_role' or 'default_cancelled_role'.
	 * @return WP_User The user with the new role.
	 * @since  2.0
	 */
	public function hf_update_users_role( $user_id, $role_new ) {
		$user = new WP_User( $user_id );
		// Never change an admin's role to avoid locking out admins testing the plugin.
		if ( ! empty( $user->roles ) && in_array( 'administrator', $user->roles ) ) {
			return;
		}

		/**
		 * Allow plugins to prevent Subscriptions from handling roles.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param boolean    $allow  Allows to change the user role.
		 * @param object     $user   User object.
		 * @param string     $role_new   New role.
		 */
		if ( ! apply_filters( 'woocommerce_subscriptions_update_users_role', true, $user, $role_new ) ) {
			return;
		}

		$roles    = $this->hf_get_new_user_role_names( $role_new );
		$role_new = $roles['new'];
		$role_old = $roles['old'];
		if ( ! empty( $role_old ) ) {
			$user->remove_role( $role_old );
		}

		$user->add_role( $role_new );
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param string    $role_new  User role new.
		 * @param object    $user    User data.
		 * @param string    $role_old  User role old.
		 */
		do_action( 'woocommerce_subscriptions_updated_users_role', $role_new, $user, $role_old );
		return $user;
	}//end hf_update_users_role()


	/**
	 * Gets default new and old role names if the new role is 'default_subscriber_role'. Otherwise returns role_new and an
	 * empty string.
	 *
	 * @param  string $role_new string the new role of the user.
	 * @return array with keys 'old' and 'new'.
	 */
	public function hf_get_new_user_role_names( $role_new ) {
		$default_subscriber_role = get_option( WC_Subscriptions_Admin::$option_prefix . '_subscriber_role' );
		$default_cancelled_role  = get_option( WC_Subscriptions_Admin::$option_prefix . '_cancelled_role' );
		$role_old = '';
		if ( 'default_subscriber_role' == $role_new ) {
			$role_old = $default_cancelled_role;
			$role_new = $default_subscriber_role;
		} else if ( in_array( $role_new, array( 'default_inactive_role', 'default_cancelled_role' ) ) ) {
			$role_old = $default_subscriber_role;
			$role_new = $default_cancelled_role;
		}

		return array(
			'new' => $role_new,
			'old' => $role_old,
		);
	}//end hf_get_new_user_role_names()


	/**
	 * Create a new subscription
	 *
	 * Returns a new WC_Subscription object on success which can then be used to add additional data.
	 *
	 * @param array   $args Arguments.
	 * @param boolean $subscription_exist Is exist sub.
	 * @throws Exception Unable to insert data into db.
	 * @return WC_Subscription | WP_Error A WC_Subscription on success or WP_Error object on failure
	 * @since  2.0
	 */
	public function hf_create_subscription( $args = array(), $subscription_exist = false ) {
		global $wpdb;
		$this->data_validation( $args, $subscription_exist );
		$subscription_status = $args['subscription_status'];
		$subscription_currency = $args['currency'];
		$subscription_total_amount = $args['order_total'];
		$subscription_customer_id = $args['customer_id'];
		$subscription_billing_email = $args['billing_email'];
		$subscription_date_created_gmt = $args['start_date'];
		$subscription_parent_order_id = $args['post_parent'];
		$subscription_payment_method = $args['payment_method'];
		$subscription_payment_method_title = $args['payment_method_title'];
		$subscription_customer_note = $args['customer_note'];
		$subscription_billing_interval = $args['billing_interval'];
		$subscription_billing_period = $args['billing_period'];
		if ( $subscription_exist ) {
			$subscription_id = $args['subscription_id'];
			if ( class_exists( 'HF_Subscription' ) ) {
				$subscription = new HF_Subscription( $subscription_id );
			} else {
				$subscription = new WC_Subscription( $subscription_id );
			}
		} elseif ( isset( $args['subscription_id'] ) && ! empty( $args['subscription_id'] ) && is_numeric( $args['subscription_id'] ) && $args['subscription_id'] > 0 ) {
			$import_id = $args['subscription_id'];
			$current_date = current_time( 'Y-m-d H:i:s' );
			if ( class_exists( 'HF_Subscription' ) ) {
				$post_type = 'hf_shop_subscription';
			} else {
				$post_type = 'shop_subscription';
			}
			$table_name = self::$table_name;
			if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
				$order_table_name = $wpdb->prefix . 'wc_orders';
				$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wc_orders'" );

				if ( $table_exists === $order_table_name ) {
					$check_order_table = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}wc_orders WHERE type= %s AND id=%d", $post_type, $args['subscription_id'] ) );
				}
			}
			$check_post_table = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type= %s AND ID=%d", $post_type, $args['subscription_id'] ) );

			$result_order_table = false;
			$result_post_table = false;
			if ( isset( $check_order_table ) && ! $check_post_table ) {
				if ( self::$is_hpos_enabled && ( self::$is_sync || class_exists( 'HF_Subscription' ) ) ) {
					$result_post_table = $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->posts} (ID, post_status, post_date, post_date_gmt, post_type) VALUES (%d, 'wc-pending', %s, %s, %s)", $import_id, $current_date, $current_date, $post_type ) );
				} else if ( self::$is_hpos_enabled && ! self::$is_sync ) {
					$result_post_table = $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->posts} (ID, post_status, post_date, post_date_gmt, post_type) VALUES (%d, 'wc-pending', %s, %s, %s)", $import_id, $current_date, $current_date, 'shop_order_placehold' ) );
				}
				$result_order_table = (int) 1;
			} else if ( ! isset( $check_order_table ) && $check_post_table ) {
				if ( self::$is_hpos_enabled ) {
					$result_order_table = $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wc_orders (id, status, date_created_gmt, type) VALUES (%d, 'wc-pending', %s, %s)", $import_id, $current_date, $post_type ) );
				}
				$result_post_table = (int) 1;
			} else if ( ! isset( $check_order_table ) && ! $check_post_table ) {
				if ( self::$is_hpos_enabled && ( self::$is_sync || class_exists( 'HF_Subscription' ) ) ) {
					$result_post_table = $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->posts} (ID, post_status, post_date, post_date_gmt, post_type) VALUES (%d, 'wc-pending', %s, %s, %s)", $import_id, $current_date, $current_date, $post_type ) );
				} else if ( self::$is_hpos_enabled && ! self::$is_sync ) {
					$result_post_table = $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->posts} (ID, post_status, post_date, post_date_gmt, post_type) VALUES (%d, 'draft', %s, %s, %s)", $import_id, $current_date, $current_date, $post_type ) );
				} else {
					$result_post_table = $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->posts} (ID, post_status, post_date, post_date_gmt, post_type) VALUES (%d, 'wc-pending', %s, %s, %s)", $import_id, $current_date, $current_date, $post_type ) );
				}
				if ( self::$is_hpos_enabled ) {
					$result_order_table = $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wc_orders (id, status, date_created_gmt, type) VALUES (%d, 'wc-pending', %s, %s)", $import_id, $current_date, $post_type ) );
				}
			}

			if ( ( $result_order_table && $result_post_table ) || ( ! self::$is_hpos_enabled && $result_post_table ) ) {
				if ( class_exists( 'HF_Subscription' ) ) {
					$subscription = new HF_Subscription( $import_id );
				} else {
					$subscription = new WC_Subscription( $import_id );
				}
			} elseif ( class_exists( 'HF_Subscription' ) ) {
					$subscription = new HF_Subscription();
			} else {
				$subscription = new WC_Subscription();
			}
		} else {
			$subscription = new WC_Subscription();
			if ( class_exists( 'HF_Subscription' ) ) {
				$post_type = 'hf_shop_subscription';
				$current_date = current_time( 'Y-m-d H:i:s' );
				$result_post_table = $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->posts} ( post_status, post_date, post_date_gmt, post_type) VALUES ( %s, %s, %s, %s);", 'wc-pending', $current_date, $current_date, $post_type ) );
				if ( $result_post_table ) {
					$inserted_post_id = $wpdb->insert_id;
					$result_order_table = $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wc_orders (id, status, date_created_gmt, type) VALUES (%d, %s, %s, %s);", $inserted_post_id, 'wc-pending', $current_date, $post_type ) );
					if ( $result_order_table ) {
						$subscription = new HF_Subscription( $inserted_post_id );
					} else {
						throw new Exception( esc_html__( 'Unable to insert data into post table.' ) );
					}
				} else {
					throw new Exception( esc_html__( 'Unable to insert data into order table' ) );
				}
			} else {
				$subscription = new WC_Subscription();
			}
		}
		$subscription->set_status( $subscription_status );
		$subscription->set_currency( $subscription_currency );
		$subscription->set_total( $subscription_total_amount );
		$subscription->set_customer_id( $subscription_customer_id );
		$subscription->set_billing_email( $subscription_billing_email );
		$subscription->set_date_created( $subscription_date_created_gmt );
		$subscription->set_parent_id( $subscription_parent_order_id );
		$subscription->set_payment_method( $subscription_payment_method );
		$subscription->set_payment_method_title( $subscription_payment_method_title );
		$subscription->set_customer_note( $subscription_customer_note );
		$subscription->set_billing_interval( $subscription_billing_interval );
		$subscription->set_billing_period( $subscription_billing_period );
		$subscription_id = $subscription->save();
		if ( class_exists( 'HF_Subscription' ) ) {
			if ( function_exists( 'hf_custom_orders_table_usage_is_enabled' ) && hf_custom_orders_table_usage_is_enabled() && ( false === get_option( 'woocommerce_custom_orders_table_data_sync_enabled' ) || 'no' === get_option( 'woocommerce_custom_orders_table_data_sync_enabled' ) ) ) {
				$value = $wpdb->get_col( $wpdb->prepare( "SELECT status FROM {$wpdb->prefix}wc_orders WHERE  id = %d", $subscription->get_id() ) );
				if ( ! empty( $value ) && isset( $value[0] ) ) {
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_status = %s WHERE ID = %d", array( $value[0], $subscription->get_id() ) ) );
				}
			}
			$subscription = new HF_Subscription( $subscription_id );
		} else {
			$subscription = new WC_Subscription( $subscription_id );
		}
		return $subscription;
	}//end hf_create_subscription()


	/**
	 * Return an array statuses used to describe when a subscriptions has been marked as ending or has ended.
	 *
	 * @return array
	 * @since  2.0
	 */
	public function hf_get_subscription_ended_statuses() {
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array     $subscription_ended_status  Subscription ended statuses.
		 */
		return apply_filters( 'hf_subscription_ended_statuses', array( 'cancelled', 'trash', 'expired', 'switched', 'pending-cancel' ) );
	}//end hf_get_subscription_ended_statuses()


	/**
	 * Add membership plans to imported subscriptions if applicable
	 *
	 * @since 1.0
	 * @param int $user_id User id.
	 * @param int $subscription_id Subscription id.
	 * @param int $product_id Product id.
	 */
	public static function maybe_add_memberships( $user_id, $subscription_id, $product_id ) {
		if ( function_exists( 'wc_memberships_get_membership_plans' ) ) {
			if ( ! self::$membership_plans ) {
				self::$membership_plans = wc_memberships_get_membership_plans();
			}

			foreach ( self::$membership_plans as $plan ) {
				if ( $plan->has_product( $product_id ) ) {
					$plan->grant_access_from_purchase( $user_id, $product_id, $subscription_id );
				}
			}
		}
	}//end maybe_add_memberships()


	/**
	 * Adds the line item to the subscription
	 *
	 * @since  1.0
	 * @param array           $details Product details.
	 * @param  WC_Subscription $subscription Subscription.
	 * @param  array           $data Product data.
	 * @param boolean         $link_product_using_sku Link using sku.
	 * @throws Exception Product not found.
	 * @return string
	 */
	public static function add_product( $details, $subscription, $data, $link_product_using_sku ) {
		$item_args        = array();
		$item_args['qty'] = isset( $data['qty'] ) ? $data['qty'] : 1;
		if ( $link_product_using_sku || empty( $data['product_id'] ) ) {
			$product_id         = wc_get_product_id_by_sku( $data['sku'] );
			$data['product_id'] = $product_id;
		}

		if ( ! isset( $data['product_id'] ) ) {
			throw new Exception( esc_html__( 'The product is not found.' ) );
		}

		$_product = wc_get_product( $data['product_id'] );
		if ( ! $_product ) {
			$order_item       = array(
				'order_item_name' => ( ! empty( $data['name'] ) ) ? $data['name'] : __( 'Unknown Product' ),
				'order_item_type' => 'line_item',
			);
			$_order_item_meta = array(
				'_qty'               => $item_args['qty'],
				'_tax_class'         => '',
				// Tax class (adjusted by filters).
					'_product_id'        => '',
				'_variation_id'      => '',
				'_line_subtotal'     => ! empty( $data['total'] ) ? $data['total'] : 0,
				// Line subtotal (before discounts).
					'_line_subtotal_tax' => 0,
				// Line tax (before discounts).
					'_line_total'        => ! empty( $data['sub_total'] ) ? $data['sub_total'] : 0,
				// Line total (after discounts).
					'_line_tax'          => 0,
			// Line Tax (after discounts).
			);

			if ( isset( $data['meta'] ) && ! empty( $data['meta'] ) ) {
				$_order_item_meta = array_merge( $_order_item_meta, $data['meta'] );
			}

			$line_item_name = ( ! empty( $data['name'] ) ) ? $data['name'] : __( 'Unknown Product' );
			$product_string = $line_item_name;
		} else {
			$line_item_name = ( ! empty( $data['name'] ) ) ? $data['name'] : $_product->get_title();

			$product_id = ( WC()->version >= '2.7.0' ) ? $_product->get_id() : $_product->id;
			// solve issue with the hyperlink when the variation product present in the subscription and linked using the Link using the SKU option.
			if ( get_post_type( $product_id ) == 'product_variation' ) {
				$product_id         = wp_get_post_parent_id( $product_id );
				$data['product_id'] = $product_id;
				// parent id added.
			}

			$product_string = sprintf( '<a href="%s">%s</a>', get_edit_post_link( ( WC()->version >= '2.7.0' ) ? $product_id : $_product->id ), $line_item_name );

			$order_item = array(
				'order_item_name' => $line_item_name,
				'order_item_type' => 'line_item',
			);
			$var_id     = 0;
			if ( WC()->version < '2.7.0' ) {
				$var_id = ( 'variation' === $_product->product_type ) ? $_product->variation_id : 0;
			} else {
				$var_id = $_product->is_type( 'variation' ) ? $_product->get_id() : 0;
			}

			$_order_item_meta = array(
				'_qty'               => $item_args['qty'],
				'_tax_class'         => '',
				// Tax class (adjusted by filters).
					'_product_id'        => $data['product_id'],
				'_variation_id'      => $var_id,
				'_line_subtotal'     => ! empty( $data['total'] ) ? $data['total'] : 0,
				// Line subtotal (before discounts).
					'_line_subtotal_tax' => ! empty( $data['tax'] ) ? $data['tax'] : 0,
				// Line tax (before discounts).
					'_line_total'        => ! empty( $data['sub_total'] ) ? $data['sub_total'] : 0,
				// Line total (after discounts).
					'_line_tax'          => ! empty( $data['tax'] ) ? $data['tax'] : 0,
				// Line Tax (after discounts).
					'_line_tax_data'     => $data['tax_data'],
			);

			if ( self::$all_virtual && ! $_product->is_virtual() ) {
				self::$all_virtual = false;
			}

			if ( isset( $data['meta'] ) && ! empty( $data['meta'] ) ) {
				$_order_item_meta = array_merge( $_order_item_meta, $data['meta'] );
			}

			if ( ! empty( $details['download_permissions'] ) && ( 'true' == $details['download_permissions'] || 1 == (int) $details['download_permissions'] ) ) {
				self::save_download_permissions( $subscription, $_product, $item_args['qty'] );
			}
		}//end if

		$order_item_id = wc_add_order_item( ( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id ), $order_item );

		if ( $order_item_id ) {
			foreach ( $_order_item_meta as $meta_key => $meta_value ) {
				wc_add_order_item_meta( $order_item_id, $meta_key, maybe_unserialize( $meta_value ) );
			}
		}

		return $product_string;
	}//end add_product()


	/**
	 * Save download permission to the subscription.
	 *
	 * @since 1.0
	 * @param WC_Subscription $subscription Subscription.
	 * @param WC_Product      $product Product.
	 * @param int             $quantity Quantity.
	 */
	public static function save_download_permissions( $subscription, $product, $quantity = 1 ) {
		if ( $product && $product->exists() && $product->is_downloadable() ) {
			$downloads  = $product->get_downloads();
			$product_id = isset( $product->variation_id ) ? $product->variation_id : ( ( WC()->version >= '2.7.0' ) ? $product->get_id() : $product->id );
			foreach ( array_keys( $downloads ) as $download_id ) {
				wc_downloadable_file_permission( $download_id, $product_id, $subscription, $quantity );
			}
		}
	}//end save_download_permissions()


	/**
	 * Add coupon line item to the subscription. The discount amount used is based on priority list.
	 *
	 * @since 1.0
	 * @param WC_Subscription $subscription Subscription.
	 * @throws Exception Coupon not found.
	 * @param array           $data Coupon data.
	 */
	public static function add_coupons( $subscription, $data ) {
		$coupon_items = explode( ';', $data['coupon_items'] );
		if ( ! empty( $coupon_items ) ) {
			foreach ( $coupon_items as $coupon_item ) {
				$coupon_data = array();
				foreach ( explode( '|', $coupon_item ) as $item ) {
					list( $name, $value )     = explode( ':', $item );
					$coupon_data[ trim( $name ) ] = trim( $value );
				}

				$coupon_code = isset( $coupon_data['code'] ) ? $coupon_data['code'] : '';
				$coupon      = new WC_Coupon( $coupon_code );
				if ( ! $coupon ) {
					/* translators:%s: coupon code */
					throw new Exception( sprintf( esc_html__( 'Could not find coupon with code "%s" in your store.' ), esc_html( $coupon_code ) ) );
				} else if ( isset( $coupon_data['amount'] ) ) {
					$discount_amount = floatval( $coupon_data['amount'] );
				} else {
					$discount_amount = ( WC()->version >= '2.7.0' ) ? $coupon->get_amount() : $coupon->discount_amount;
				}

				if ( WC()->version >= '2.7.0' ) {
					$cpn = new WC_Order_Item_Coupon();
					$cpn->set_code( $coupon_code );
					$cpn->set_discount( $discount_amount );
					$cpn->save();
					$subscription->add_item( $cpn );
					$coupon_id = $cpn->get_id();
				} else {
					$coupon_id = $subscription->add_coupon( $coupon_code, $discount_amount );
				}

				if ( ! $coupon_id ) {
					/* translators:%s: coupon code */
					throw new Exception( sprintf( esc_html__( 'Coupon "%s" could not be added to subscription.' ), esc_html( $coupon_code ) ) );
				}
			}//end foreach
		}//end if
	}//end add_coupons()


	/**
	 * PHP on Windows does not have strptime function. Therefore this is what we're using to check
	 * whether the given time is of a specific format.
	 *
	 * @param  string $time the mysql time string.
	 * @return boolean      true if it matches our mysql pattern of YYYY-MM-DD HH:MM:SS
	 */
	public function hf_is_datetime_mysql_format( $time ) {
		if ( ! is_string( $time ) ) {
			return false;
		}

		// @codingStandardsIgnoreStart
		if ( function_exists( 'DateTime::createFromFormat' ) ) {
			$date_format = 'Y-m-d H:i:s';
			$valid_time = false;
			$match = false;

			$datetime = DateTime::createFromFormat( $date_format, $time );

			if ( $datetime instanceof DateTime ) {
				$valid_time = true;
				$match = $datetime->format( $date_format ) === $time;
			}
		} else {
			// parses for the pattern of YYYY-MM-DD HH:MM:SS, but won't check whether it's a valid timedate.
			$match = preg_match( '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $time );
			// parses time, returns false for invalid dates.
			$valid_time = strtotime( $time );
		}
		// @codingStandardsIgnoreEnd
		// magic number -2209078800 is strtotime( '1900-01-00 00:00:00' ). Needed to achieve parity with strptime.
		return ( $match && false !== $valid_time && -2209078800 <= $valid_time ) ? true : false;
	}//end hf_is_datetime_mysql_format()


	/**
	 * Return translated associative array of all possible subscription periods.
	 *
	 * @param int    $number (optional) An interval in the range 1-6.
	 * @param string $period (optional) One of day, week, month or year. If empty, all subscription ranges are returned.
	 */
	public function hf_get_subscription_period_strings( $number = 1, $period = '' ) {
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array     $subscription_periods  Subscription periods.
		 */
		$translated_periods = apply_filters(
			'woocommerce_subscription_periods',
			array(
				// translators: placeholder is number of days. (e.g. "Bill this every day / 4 days").
					'day'   => sprintf( _x( '%s days', 'Subscription billing period.', 'woocommerce-subscriptions' ), $number ),
				// translators: placeholder is number of weeks. (e.g. "Bill this every week / 4 weeks").
					'week'  => sprintf( _x( '%s weeks', 'Subscription billing period.', 'woocommerce-subscriptions' ), $number ),
				// translators: placeholder is number of months. (e.g. "Bill this every month / 4 months").
					'month' => sprintf( _x( '%s months', 'Subscription billing period.', 'woocommerce-subscriptions' ), $number ),
				// translators: placeholder is number of years. (e.g. "Bill this every year / 4 years").
					'year'  => sprintf( _x( '%s years', 'Subscription billing period.', 'woocommerce-subscriptions' ), $number ),
			)
		);

		return ( ! empty( $period ) ) ? $translated_periods[ $period ] : $translated_periods;
	}//end hf_get_subscription_period_strings()

	/**
	 * Get order with import key
	 *
	 * @global type $wpdb
	 * @param integer $id order id.
	 * @return type
	 */
	public static function wt_get_order_with_import_key( $id ) {
		global $wpdb;

		if ( self::$is_hpos_enabled ) {
			$order_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT po.id FROM {$wpdb->prefix}wc_orders AS po
				INNER JOIN {$wpdb->prefix}wc_orders_meta AS pm
				ON po.id = pm.order_id
				WHERE po.type = 'shop_order'
				AND pm.meta_key = '_wt_import_key'
				AND pm.meta_value = %d",
					$id
				)
			);

		} else {
			$order_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT po.ID FROM {$wpdb->posts} AS po
				INNER JOIN {$wpdb->postmeta} AS pm
				ON po.ID = pm.post_id
				WHERE po.post_type = 'shop_order'
				AND pm.meta_key = '_wt_import_key'
				AND pm.meta_value = %d",
					$id
				)
			);
		}
		return $order_id;
	}//end wt_get_order_with_import_key()


	/**
	 * Return an array of subscription status types, similar to @see wc_get_order_statuses()
	 *
	 * @return array
	 */
	public function hf_get_subscription_statuses() {
		$subscription_statuses = array(
			'wc-pending'        => _x( 'Pending', 'Subscription status', 'woocommerce-subscriptions' ),
			'wc-active'         => _x( 'Active', 'Subscription status', 'woocommerce-subscriptions' ),
			'wc-on-hold'        => _x( 'On hold', 'Subscription status', 'woocommerce-subscriptions' ),
			'wc-cancelled'      => _x( 'Cancelled', 'Subscription status', 'woocommerce-subscriptions' ),
			'wc-switched'       => _x( 'Switched', 'Subscription status', 'woocommerce-subscriptions' ),
			'wc-expired'        => _x( 'Expired', 'Subscription status', 'woocommerce-subscriptions' ),
			'wc-pending-cancel' => _x( 'Pending Cancellation', 'Subscription status', 'woocommerce-subscriptions' ),
		);
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array     $subscription_statuses  Subscription statuses.
		 */
		return apply_filters( 'hf_subscription_statuses', $subscription_statuses );
	}//end hf_get_subscription_statuses()


	/**
	 * Import tax lines
	 *
	 * @param WC_Subscription $subscription Subscription.
	 * @param array           $data Tax data.
	 */
	public static function add_taxes( $subscription, $data ) {
		global $wpdb;
		$tax_items          = explode( ';', $data['tax_items'] );
		$chosen_tax_rate_id = 0;
		if ( ! empty( $tax_items ) ) {
			foreach ( $tax_items as $tax_item ) {
				$tax_data = array();

				if ( false !== strpos( $tax_item, ':' ) ) {
					foreach ( explode( '|', $tax_item ) as $item ) {
						list( $name, $value )  = explode( ':', $item );
						$tax_data[ trim( $name ) ] = trim( $value );
					}
				} else if ( 1 == count( $tax_items ) ) {
					if ( is_numeric( $tax_item ) ) {
						$tax_data['rate_id'] = $tax_item;
					} else {
						$tax_data['code'] = $tax_item;
					}
				}

				if ( ! empty( $tax_data['rate_id'] ) ) {
					$tax_rate = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_id = %s", $tax_data['rate_id'] ) );
				} else if ( ! empty( $tax_data['code'] ) ) {
					$tax_rate = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_name = %s ORDER BY tax_rate_priority LIMIT 1", $tax_data['code'] ) );
				} else {
					/* translators:%s: Tax item */
					$result['warning'][] = sprintf( __( 'Missing tax code or ID from column: %s' ), $data['tax_items'] );
				}

				if ( ! empty( $tax_rate ) ) {
					$tax_rate = array_pop( $tax_rate );
					if ( WC()->version > '2.7.0' ) {
						foreach ( $data['post_meta'] as $key_main => $valuemain ) {
							if ( '_order_shipping_tax' == $valuemain['key'] ) {
								$temp_order_shipping_tax = $valuemain['value'];
							}

							if ( '_order_tax' == $valuemain['key'] ) {
								$temp_order_tax_total = $valuemain['value'];
							}
						}

						$tax = new WC_Order_Item_Tax();
						$tax->set_props(
							array(
								'rate_id'            => $tax_rate->tax_rate_id,
								'tax_total'          => ( ! empty( $temp_order_tax_total ) ? $temp_order_tax_total : 0 ),
								'shipping_tax_total' => ( ! empty( $temp_order_shipping_tax ) ? $temp_order_shipping_tax : 0 ),
							)
						);
						$tax->set_rate( $tax_rate->tax_rate_id );
						$tax->set_order_id( $subscription->get_id() );
						$tax->save();
						$subscription->add_item( $tax );
						$tax_id = $tax->get_id();
					} else {
						$tax_id = $subscription->add_tax( $tax_rate->tax_rate_id, ( ! empty( $data['order_shipping_tax'] ) ) ? $data['order_shipping_tax'] : 0, ( ! empty( $data['order_tax'] ) ) ? $data['order_tax'] : 0 );
					}//end if

					if ( ! $tax_id ) {
						$result['warning'][] = esc_html__( 'Tax line item could not properly be added to this subscription. Please review this subscription.' );
					} else {
						$chosen_tax_rate_id = $tax_rate->tax_rate_id;
					}
				} else {
					/* translators:%s: Tax code */
					$result['warning'][] = sprintf( __( 'The tax code "%s" could not be found in your store.' ), $tax_data['code'] );
				}//end if
			}//end foreach
		}//end if

		return $chosen_tax_rate_id;
	}//end add_taxes()


	/**
	 * Function to write in the woocommerce log file
	 *
	 * @param string $content Log context.
	 * @param string $data Log data.
	 */
	public function hf_log_data_change( $content = 'hf-subscription-csv-import', $data = '' ) {

		Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', $data );
	}//end hf_log_data_change()

	/**
	 * Delete existing post
	 */
	public function delete_existing() {

		$posts = new WP_Query(
			array(
				'post_type'      => $this->post_type,
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'post_status'    => array(
					'publish',
					'private',
					'draft',
					'pending',
					'future',
				),
				'meta_query'     => array(
					array(
						'key'     => '_wt_delete_existing',
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);

		foreach ( $posts->posts as $post ) {
			$this->import_results['detele_results'][ $post ] = wp_trash_post( $post );
		}

		$posts = new WP_Query(
			array(
				'post_type'      => $this->post_type,
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'post_status'    => array(
					'publish',
					'private',
					'draft',
					'pending',
					'future',
				),
				'meta_query'     => array(
					array(
						'key'     => '_wt_delete_existing',
						'compare' => 'EXISTS',
					),
				),
			)
		);
		foreach ( $posts->posts as $post ) {
			delete_post_meta( $post, '_wt_delete_existing' );
		}
	}//end delete_existing()

	/**
	 * Date validation
	 *
	 * @param array $args Query args.
	 *
	 * @param bool  $subscription_exist If suscription exist.
	 */
	public function data_validation( $args = array(), $subscription_exist = false ) {

		// validate the start_date field.
		if ( ! is_string( $args['start_date'] ) || false === $this->hf_is_datetime_mysql_format( $args['start_date'] ) ) {
			if ( ! $subscription_exist ) {
				return new WP_Error( 'woocommerce_subscription_invalid_start_date_format', _x( 'Invalid date. The date must be a string and of the format: "Y-m-d H:i:s".', 'Error message while creating a subscription', 'woocommerce-subscriptions' ) );
			}
		} else if ( strtotime( $args['start_date'] ) > time() ) {
			if ( ! $subscription_exist ) {
				return new WP_Error( 'woocommerce_subscription_invalid_start_date', _x( 'Subscription start date must be before current day.', 'Error message while creating a subscription', 'woocommerce-subscriptions' ) );
			}
		}
		// check customer id is set.
		if ( empty( $args['customer_id'] ) || ! is_numeric( $args['customer_id'] ) || $args['customer_id'] <= 0 ) {
			if ( ! $subscription_exist ) {
				return new WP_Error( 'woocommerce_subscription_invalid_customer_id', _x( 'Invalid subscription customer_id.', 'Error message while creating a subscription', 'woocommerce-subscriptions' ) );
			}
		}
		// check the billing period.
		if ( empty( $args['billing_period'] ) || ! in_array( strtolower( $args['billing_period'] ), array_keys( $this->hf_get_subscription_period_strings() ) ) ) {
			if ( ! $subscription_exist ) {
				return new WP_Error( 'woocommerce_subscription_invalid_billing_period', __( 'Invalid subscription billing period given.', 'woocommerce-subscriptions' ) );
			}
		}
		// check the billing interval.
		if ( empty( $args['billing_interval'] ) || ! is_numeric( $args['billing_interval'] ) || absint( $args['billing_interval'] ) <= 0 ) {
			if ( ! $subscription_exist ) {
				return new WP_Error( 'woocommerce_subscription_invalid_billing_interval', __( 'Invalid subscription billing interval given. Must be an integer greater than 0.', 'woocommerce-subscriptions' ) );
			}
		}
	}//end data_validation()
}//end class
