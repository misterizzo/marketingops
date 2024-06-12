<?php
/**
 * Handles the order import.
 *
 * @package   ImportExportSuite\Admin\Modules\Order\Import
 * @version   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Wt_Import_Export_For_Woo_Order_Import Class.
 */
class Wt_Import_Export_For_Woo_Order_Import {

	/**
	 * Post type
	 *
	 * @var string
	 */
	public $post_type = 'shop_order';
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $parent_module = null;
	/**
	 * Parsed data
	 *
	 * @var array
	 */
	public $parsed_data = array();
	/**
	 * Import columns
	 *
	 * @var array
	 */
	public $import_columns = array();
	/**
	 * Is merge
	 *
	 * @var boolean
	 */
	public $merge;
	/**
	 * Is skip new
	 *
	 * @var boolean
	 */
	public $skip_new;
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $order_id;
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $merge_empty_cells;
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $delete_existing;
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $ord_link_using_sku;
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $create_user;
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $notify_customer = false;
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $status_mail;
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $new_order_status;
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $allow_unknown_products = true;
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $item_data = array();
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $is_order_exist = false;
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $found_action = 'skip';
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $id_conflict = 'skip';
	/**
	 * Result
	 *
	 * @var array
	 */
	public $import_results = array();
	/**
	 * Is custome order table is enabled.
	 *
	 * @var bool
	 */
	public $is_hpos_enabled = false;
	/**
	 * Selected order table.
	 *
	 * @var string
	 */
	public $table_name;
	/**
	 * Is custome order table and post table is in sync.
	 *
	 * @var bool
	 */
	public $is_sync;

	/**
	 * Constructor.
	 *
	 * @param object $parent_object Parent module object.
	 * @since 1.0.0
	 */
	public function __construct( $parent_object ) {

		$this->parent_module = $parent_object;
		$hpos_data = Wt_Import_Export_For_Woo_Common_Helper::is_hpos_enabled();
		$this->table_name = $hpos_data['table_name'];
		$this->is_sync = $hpos_data['sync'];
		if ( strpos( $hpos_data['table_name'], 'wc_orders' ) !== false || $hpos_data['sync'] ) {
			$this->is_hpos_enabled = true;
		}
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

		$this->found_action      = ! empty( $form_data['advanced_form_data']['wt_iew_found_action'] ) ? $form_data['advanced_form_data']['wt_iew_found_action'] : 'skip';
		$this->id_conflict       = ! empty( $form_data['advanced_form_data']['wt_iew_id_conflict'] ) ? $form_data['advanced_form_data']['wt_iew_id_conflict'] : 'skip';
		$this->merge_empty_cells = isset( $form_data['advanced_form_data']['wt_iew_merge_empty_cells'] ) ? $form_data['advanced_form_data']['wt_iew_merge_empty_cells'] : 0;
		$this->skip_new          = isset( $form_data['advanced_form_data']['wt_iew_skip_new'] ) ? $form_data['advanced_form_data']['wt_iew_skip_new'] : 0;

		$this->delete_existing = isset( $form_data['advanced_form_data']['wt_iew_delete_existing'] ) ? $form_data['advanced_form_data']['wt_iew_delete_existing'] : 0;

		$this->ord_link_using_sku = isset( $form_data['advanced_form_data']['wt_iew_ord_link_using_sku'] ) ? $form_data['advanced_form_data']['wt_iew_ord_link_using_sku'] : 0;
		$this->create_user        = isset( $form_data['advanced_form_data']['wt_iew_create_user'] ) ? $form_data['advanced_form_data']['wt_iew_create_user'] : 0;
		$this->notify_customer    = isset( $form_data['advanced_form_data']['wt_iew_notify_customer'] ) ? $form_data['advanced_form_data']['wt_iew_notify_customer'] : 0;
		$this->status_mail        = isset( $form_data['advanced_form_data']['wt_iew_status_mail'] ) ? $form_data['advanced_form_data']['wt_iew_status_mail'] : 0;

		Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', 'Preparing for import.' );

		$success = 0;
		$failed  = 0;
		$msg     = 'Order imported successfully.';

		foreach ( $import_data as $key => $data ) {
			$row = ( $batch_offset + $key + 1 );
			Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', "Row :$row - Parsing item." );
			$parsed_data = $this->parse_data( $data );
			if ( ! is_wp_error( $parsed_data ) ) {
				Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', "Row :$row - Processing item." );
				$result = $this->process_item( $parsed_data );
				if ( ! is_wp_error( $result ) ) {
					if ( $this->is_order_exist ) {
						$msg = 'Order updated successfully.';
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
		}//end foreach

		if ( $is_last_batch && $this->delete_existing ) {
			$this->delete_existing();
		}

		$this->clean_after_import();

		$import_response = array(
			'total_success' => $success,
			'total_failed'  => $failed,
			'log_data'      => $this->import_results,
		);

		return $import_response;
	}//end prepare_data_to_import()

	/**
	 * Clean after import
	 *
	 * @global object $wpdb
	 */
	public function clean_after_import() {
		global $wpdb;
		$posts = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_status = %s AND post_type = %s ", 'importing', $this->post_type ) );// @codingStandardsIgnoreLine.
		if ( $posts ) {
			array_map( 'wp_delete_post', $posts );
		}
	}//end clean_after_import()

	/**
	 * Delete existing
	 */
	public function delete_existing() {

		$wc_order_statuses = array_keys( wc_get_order_statuses() );
		$posts = new WP_Query(
			array(
				'post_type'      => $this->post_type,
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'post_status'    => get_post_stati(),
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
				'post_status'    => $wc_order_statuses,
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
	 * Parse the data.
	 *
	 * @param array $data value.
	 *
	 * @return array
	 */
	public function parse_data( $data ) {
		try {
			/**
			 * Filter the query arguments for a request.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param array   $data  Order CSV data.
			 */
			$data = apply_filters( 'wt_woocommerce_order_importer_pre_parse_data', $data );

			$mapping_fields = $data['mapping_fields'];
			foreach ( $data['meta_mapping_fields'] as $value ) {
				$mapping_fields = array_merge( $mapping_fields, $value );
			}

			$this->item_data = array();
			// resetting WC default data before parsing new item to avoid merging last parsed item wp_parse_args.
			if ( isset( $mapping_fields['order_id'] ) && ! empty( $mapping_fields['order_id'] ) ) {
				$this->item_data['order_id'] = $this->wt_order_existance_check( $mapping_fields['order_id'] );
				// to determine wether merge or import.
			}

			if ( ! $this->merge ) {
				$default_data    = $this->get_default_data();
				$this->item_data = wp_parse_args( $this->item_data, $default_data );
				// $this->item_data = $default_data;
			}

			if ( $this->merge && ! $this->merge_empty_cells ) {
				$this->item_data = array();
				$this->item_data['order_id'] = $this->order_id;
				// $this->order_id set from wt_order_existance_check.
			}
			foreach ( $mapping_fields as $column => $value ) {
				if ( $this->merge && ! $this->merge_empty_cells && '' == $value ) {
					continue;
				}

				$column = strtolower( $column );

				if ( 'order_number' == $column ) {
					$this->item_data['order_number'] = $this->wt_parse_order_number_field( $value );
					continue;
				}

				if ( 'parent_id' == $column || 'post_parent' == $column ) {
					$this->item_data['parent_id'] = $this->wt_parse_int_field( $value );
					continue;
				}

				if ( 'date_created' == $column || 'post_date' == $column || 'order_date' == $column ) {
					$date = $this->wt_parse_date_field( $value, $column );
					$this->item_data['date_created'] = gmdate( 'Y-m-d H:i:s', $date );// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					continue;
				}

				if ( ( '_paid_date' == $column || 'paid_date' == $column ) && '' != $value ) {
					$date = $this->wt_parse_date_field( $value, $column );
					$this->item_data['date_paid'] = gmdate( 'Y-m-d H:i:s', $date );// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					continue;
				}

				if ( 'post_modified' == $column || 'date_modified' == $column || 'date_completed' == $column || '_completed_date' == $column || 'meta:_completed_date' == $column ) {
					$date = $this->wt_parse_date_field( $value, $column );
					$this->item_data['date_modified']  = gmdate( 'Y-m-d H:i:s', $date );// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					$this->item_data['date_completed'] = gmdate( 'Y-m-d H:i:s', $date );// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					continue;
				}

				if ( 'status' == $column || 'post_status' == $column ) {
					$this->item_data['status'] = $this->wt_parse_status_field( $value );
					continue;
				}

				if ( 'shipping_tax_total' == $column ) {
					$this->item_data['shipping_tax_total'] = wc_format_decimal( $value );
					$this->item_data['shipping_tax']       = wc_format_decimal( $value );
					continue;
				}

				if ( 'fee_total' == $column ) {
					$this->item_data['fee_total'] = wc_format_decimal( $value );
					continue;
				}

				if ( 'fee_tax_total' == $column ) {
					$this->item_data['fee_tax_total'] = wc_format_decimal( $value );
					continue;
				}

				if ( 'tax_total' == $column ) {
					$this->item_data['tax_total'] = wc_format_decimal( $value );
					continue;
				}

				if ( 'cart_discount' == $column ) {
					$this->item_data['cart_discount'] = wc_format_decimal( $value );
					$this->item_data['cart_tax']      = wc_format_decimal( $value );
					continue;
				}

				if ( 'order_discount' == $column ) {
					$this->item_data['order_discount'] = wc_format_decimal( $value );
					continue;
				}

				if ( 'discount_total' == $column ) {
					$this->item_data['discount_total'] = wc_format_decimal( $value );
					$this->item_data['discount_tax']   = wc_format_decimal( $value );
					continue;
				}

				if ( 'order_total' == $column ) {
					$this->item_data['order_total'] = wc_format_decimal( $value );
					$this->item_data['total']       = wc_format_decimal( $value );
					$this->item_data['total_tax']   = wc_format_decimal( $value );
					continue;
				}

				if ( 'order_currency' == $column ) {
					$this->item_data['currency'] = ( $value ) ? $value : get_woocommerce_currency();
					continue;
				}

				if ( 'payment_method' == $column ) {
					$this->item_data['payment_method'] = $this->wt_parse_payment_method_field( $value );
					// $this->item_data['payment_method_title'] = ($value);
					// $this->item_data['transaction_id'] = ($value);
					continue;
				}

				if ( '_payment_method_title' == $column || 'payment_method_title' == $column ) {
					$this->item_data['payment_method_title'] = $value;
					continue;
				}

				if ( 'transaction_id' == $column ) {
					$this->item_data['transaction_id'] = $value;
					continue;
				}

				if ( 'customer_ip_address' == $column ) {
					$this->item_data['customer_ip_address'] = $value;
					continue;
				}

				if ( 'customer_user_agent' == $column ) {
					$this->item_data['customer_user_agent'] = $value;
					continue;
				}

				if ( 'shipping_method' == $column ) {
					$this->item_data['shipping_method'] = $this->wt_parse_shipping_method_field( $value );
					continue;
				}

				if ( 'order_shipping' == $column || 'shipping_total' == $column ) {
					if ( 'shipping_total' == $column ) {
						$this->item_data['shipping_total'] = wc_format_decimal( $value );
					}

					$this->item_data['order_shipping'] = $this->wt_parse_order_shipping_field( $value, $column, $mapping_fields );
					// special case need to rewrite this concept.
					continue;
				}

				if ( 'customer_user' == $column || 'customer_email' == $column || 'customer_id' == $column ) {
					// $this->item_data['customer_id'] = $this->wt_parse_customer_id_field($value,$column,$mapping_fields);
					$this->wt_parse_customer_id_field( $value, $column, $mapping_fields );
					continue;
				}

				if ( 'billing_first_name' == $column ) {
					$this->item_data['billing']['first_name'] = ( $value );
					continue;
				}

				if ( 'billing_last_name' == $column ) {
					 $this->item_data['billing']['last_name'] = ( $value );
					continue;
				}

				if ( 'billing_company' == $column ) {
					 $this->item_data['billing']['company'] = ( $value );
					continue;
				}

				if ( 'billing_email' == $column ) {
					 $this->item_data['billing']['email'] = $this->wt_parse_email_field( $value );
					continue;
				}

				if ( 'billing_phone' == $column ) {
					 $this->item_data['billing']['phone'] = trim( $value, '\'' );
					continue;
				}

				if ( 'billing_address_1' == $column ) {
					 $this->item_data['billing']['address_1'] = ( $value );
					continue;
				}

				if ( 'billing_address_2' == $column ) {
					 $this->item_data['billing']['address_2'] = ( $value );
					continue;
				}

				if ( 'billing_postcode' == $column ) {
					 $this->item_data['billing']['postcode'] = ( $value );
					continue;
				}

				if ( 'billing_city' == $column ) {
					 $this->item_data['billing']['city'] = ( $value );
					continue;
				}

				if ( 'billing_state' == $column ) {
					 $this->item_data['billing']['state'] = ( $value );
					continue;
				}

				if ( 'billing_country' == $column ) {
					 $this->item_data['billing']['country'] = ( $value );
					continue;
				}

				if ( 'shipping_first_name' == $column ) {
					 $this->item_data['shipping']['first_name'] = ( $value );
					continue;
				}

				if ( 'shipping_last_name' == $column ) {
					 $this->item_data['shipping']['last_name'] = ( $value );
					continue;
				}

				if ( 'shipping_company' == $column ) {
					 $this->item_data['shipping']['company'] = ( $value );
					continue;
				}

				if ( 'shipping_phone' == $column ) {
					 $this->item_data['shipping']['phone'] = ( $value );
					continue;
				}

				if ( 'shipping_address_1' == $column ) {
					$this->item_data['shipping']['address_1'] = ( $value );
					continue;
				}

				if ( 'shipping_address_2' == $column ) {
					$this->item_data['shipping']['address_2'] = ( $value );
					continue;
				}

				if ( 'shipping_postcode' == $column ) {
					$this->item_data['shipping']['postcode'] = ( $value );
					continue;
				}

				if ( 'shipping_city' == $column ) {
					$this->item_data['shipping']['city'] = ( $value );
					continue;
				}

				if ( 'shipping_state' == $column ) {
					$this->item_data['shipping']['state'] = ( $value );
					continue;
				}

				if ( 'shipping_country' == $column ) {
					$this->item_data['shipping']['country'] = ( $value );
					continue;
				}

				if ( 'customer_note' == $column || 'post_excerpt' == $column ) {
					$this->item_data['customer_note'] = ( $value );
					continue;
				}

				if ( 'shipping_items' == $column ) {
					$this->item_data['shipping_items'] = $this->wt_parse_shipping_items_field( $value );
					continue;
				}

				if ( 'fee_items' == $column ) {
					$this->item_data['fee_items'] = $this->wt_parse_fee_items_field( $value );
					continue;
				}

				if ( 'tax_items' == $column ) {
					$this->item_data['tax_items'] = $this->wt_parse_tax_items_field( $value );
					continue;
				}

				if ( 'coupon_items' == $column ) {
					$this->item_data['coupon_items'] = $this->wt_parse_coupon_items_field( $value );
					continue;
				}

				if ( 'refund_items' == $column ) {
					$this->item_data['refund_items'] = $this->wt_parse_refund_items_field( $value );
					continue;
				}

				if ( 'order_notes' == $column ) {
					$this->item_data['order_notes'] = $this->wt_parse_order_notes_field( $value );
					continue;
				}

				if ( 'download_permissions' == $column ) {
					$this->item_data['meta_data'][] = array(
						'key'   => '_download_permissions_granted',
						'value' => $value,
					);
					$this->item_data['meta_data'][] = array(
						'key'   => '_download_permissions',
						'value' => $value,
					);
					continue;
				}

				if ( 'wt_import_key' == $column ) {
					$this->item_data['meta_data'][] = array(
						'key'   => '_wt_import_key',
						'value' => $value,
					);
					continue;
				}

				if ( strstr( $column, 'line_item_' ) ) {
					$this->item_data['order_items'][] = $this->wt_parse_line_item_field( $value, $column );
					continue;
				}

				if ( strstr( $column, 'meta:' ) ) {
					$this->item_data['meta_data'][] = $this->wt_parse_meta_field( $value, $column );
					continue;
				}
			}//end foreach

			if ( empty( $this->item_data['order_id'] ) ) {
				$this->item_data['order_id'] = $this->wt_parse_id_field( $mapping_fields, $this->item_data );
			}
			return $this->item_data;
		} catch ( Exception $e ) {
			return new WP_Error( 'woocommerce_product_importer_error', $e->getMessage(), array( 'status' => $e->getCode() ) );
		}//end try
	}//end parse_data()

	/**
	 * Order existence check
	 *
	 * @global object $wpdb
	 * @param integer $id Order id.
	 * @return type
	 * @throws Exception Already exist.
	 */
	public function wt_order_existance_check( $id ) {
		global $wpdb;
		$order_id = 0;
		$this->merge = false;
		$this->is_order_exist = false;
		$id = absint( $id );
		$id_found_with_id = '';
		if ( $id ) {
			$statuses = array_keys( wc_get_order_statuses() ) ? array_keys( wc_get_order_statuses() ) : array();
			$data_from_order_table = $wpdb->get_row( $wpdb->prepare( "SELECT id,status,type,date_created_gmt,parent_order_id,customer_note FROM {$wpdb->prefix}wc_orders WHERE id = %d;", $id ) );
			$data_from_post_table = $wpdb->get_row( $wpdb->prepare( "SELECT ID,post_status,post_type,post_date_gmt,post_parent,post_excerpt FROM {$wpdb->posts} WHERE ID = %d;", $id ) );
			if ( $data_from_order_table ) {
				$order_table_id = $data_from_order_table->id;
				$order_table_status = $data_from_order_table->status;
				$order_table_type = $data_from_order_table->type;
				$order_table_date_created_gmt = $data_from_order_table->date_created_gmt;
				$order_table_parent_order_id  = $data_from_order_table->parent_order_id;
				$order_table_customer_note = $data_from_order_table->customer_note;
			}
			if ( $data_from_post_table ) {
				$post_table_id = $data_from_post_table->ID;
				$post_table_status = $data_from_post_table->post_status;
				$post_table_type = $data_from_post_table->post_type;
				$post_table_post_date_gmt = $data_from_post_table->post_date_gmt;
				$post_table_post_parent = $data_from_post_table->post_parent;
				$post_table_post_excerpt = $data_from_post_table->post_excerpt;

			}

			if ( 1 == $this->is_sync ) {
				if ( ( $data_from_post_table && $post_table_type !== $this->post_type ) || ( $data_from_order_table && $order_table_type !== $this->post_type ) ) {
					$conflict_with_existing_post = true;
				}

				if ( $data_from_post_table && $data_from_order_table ) {
					if ( $this->post_type == $post_table_type && $this->post_type == $order_table_type ) {
						if ( in_array( $order_table_status, $statuses ) && in_array( $post_table_status, $statuses ) ) {
							$this->is_order_exist = true;
							$order_id = $post_table_id;
						}
					} else {
						$conflict_with_existing_post = true;
					}
				} else if ( $data_from_post_table && $post_table_type == $this->post_type && in_array( $post_table_status, $statuses ) && ! $data_from_order_table ) {

					$order_data = array(
						'id'               => $post_table_id,
						'date_created_gmt' => $post_table_post_date_gmt,
						'type'             => $this->post_type,
						'status'           => $order_table_status,
						'parent_order_id'  => ! empty( $post_table_post_parent ) ? $post_table_post_parent : 0,
						'customer_note'    => ! empty( $post_table_post_excerpt ) ? $post_table_post_excerpt : '',
					);

					$table_name = $wpdb->prefix . 'wc_orders';
					$insert_order = $wpdb->insert( $table_name, $order_data );
					if ( $insert_order ) {
						$this->is_order_exist = true;
						$order_id = $post_table_id;
					}
				} else if ( $data_from_order_table && $order_table_type == $this->post_type && in_array( $order_table_status, $statuses ) && ! $data_from_post_table ) {
					$postdata = array( // if not specifiying id (id is empty) or if not found by given id.
						'import_id'     => $order_table_id,
						'post_date'     => $order_table_date_created_gmt,
						'post_date_gmt' => $order_table_date_created_gmt,
						'post_type'     => $this->post_type,
						'post_status'   => $order_table_status,
						'ping_status'   => 'closed',
						'post_author'   => 1,
						'post_title'    => sprintf( 'Order &ndash; %s', strftime( '%b %d, %Y @ %I:%M %p', strtotime( $order_table_date_created_gmt ) ) ),
						'post_parent'   => $order_table_parent_order_id,
						'post_password' => wc_generate_order_key(),
						'post_excerpt'  => $order_table_customer_note,
					);

					$insert_post = wp_insert_post( $postdata, true );
					if ( $insert_post && ! is_wp_error( $insert_post ) ) {
						$this->is_order_exist = true;
						$order_id = $insert_post;
					}
				}
			} elseif ( $this->table_name == $wpdb->prefix . 'posts' ) {

				if ( $data_from_post_table ) {
					if ( $this->post_type == $post_table_type ) {
						if ( in_array( $post_table_status, $statuses ) ) {
							$this->is_order_exist = true;
							$order_id = $post_table_id;
						}
					} else {
						$conflict_with_existing_post = true;
					}
				}
			} else if ( $this->table_name == $wpdb->prefix . 'wc_orders' ) {

				if ( $data_from_post_table && $data_from_order_table ) {
					if ( $this->post_type == $order_table_type ) {
						if ( in_array( $order_table_status, $statuses ) ) {
							if ( 'shop_order_placehold' == $post_table_type && ( 'draft' == $post_table_status || in_array( $post_table_status, $statuses ) ) ) {
								$this->is_order_exist = true;
								$order_id = $post_table_id;
							} elseif ( $this->post_type == $post_table_type ) {
								if ( in_array( $post_table_status, $statuses ) ) {
									$this->is_order_exist = true;
									$order_id = $post_table_id;
								}
							} else {
								$conflict_with_existing_post = true;
							}
						}
					} else {
						$conflict_with_existing_post = true;
					}
				} else if ( ! $data_from_post_table && $data_from_order_table ) {
					if ( $data_from_order_table && $order_table_type !== $this->post_type ) {
						$conflict_with_existing_post = true;
					} else {
						$postdata = array( // if not specifiying id (id is empty) or if not found by given id.
							'import_id'     => $order_table_id,
							'post_date'     => $order_table_date_created_gmt,
							'post_date_gmt' => $order_table_date_created_gmt,
							'post_type'     => 'shop_order_placehold',
							'post_status'   => 'draft',
							'ping_status'   => 'closed',
							'post_author'   => 1,
							'post_title'    => sprintf( 'Order &ndash; %s', strftime( '%b %d, %Y @ %I:%M %p', strtotime( $order_table_date_created_gmt ) ) ),
							'post_parent'   => $order_table_parent_order_id,
							'post_password' => wc_generate_order_key(),
							'post_excerpt'  => $order_table_customer_note,
						);

						$insert_post = wp_insert_post( $postdata, true );
						if ( $insert_post && ! is_wp_error( $insert_post ) ) {
							$this->is_order_exist = true;
							$order_id = $insert_post;
						}
					}
				} else if ( $data_from_post_table && ! $data_from_order_table ) {
					if ( $data_from_order_table && ( $order_table_type !== $this->post_type || 'shope_order_placehold' == $post_table_type ) ) {
						$conflict_with_existing_post = true;
					} else {
						$order_data = array(
							'id'               => $post_table_id,
							'date_created_gmt' => $post_table_post_date_gmt,
							'type'             => $this->post_type,
							'status'           => 'pending',
							'parent_order_id'  => ! empty( $post_table_post_parent ) ? $post_table_post_parent : 0,
							'customer_note'    => ! empty( $post_table_post_excerpt ) ? $post_table_post_excerpt : '',
						);

								$table_name = $wpdb->prefix . 'wc_orders';
								$insert_order = $wpdb->insert( $table_name, $order_data );
						if ( $insert_order ) {
							$this->is_order_exist = true;
							$order_id = $post_table_id;
						}
					}
				}
			}

			if ( $this->is_order_exist ) {
				if ( 'skip' == $this->found_action ) {
					if ( $id && $order_id ) {
						throw new Exception( sprintf( 'Order with same ID already exists. ID: %d', absint( $id ) ) );
					}
				} elseif ( 'update' == $this->found_action ) {
					$this->merge = true;
					$this->order_id = $order_id;
					return $order_id;
				}
			}
			if ( $this->skip_new ) {
				throw new Exception( 'Skipping new item' );
			}
			if ( $id && isset( $conflict_with_existing_post ) && $conflict_with_existing_post && ! $this->is_order_exist && 'skip' == $this->id_conflict ) {
				throw new Exception( sprintf( 'Importing Order(ID) conflicts with an existing post. ID: %d', absint( $id ) ) );
			}
		}
	}//end wt_order_existance_check()


	/**
	 * Explode CSV cell values using commas by default, and handling escaped
	 * separators.
	 *
	 * @since  3.2.0
	 * @param  string $value     Value to explode.
	 * @param  string $separator Separator separating each value. Defaults to comma.
	 * @return array
	 */
	protected function wt_explode_values( $value, $separator = ',' ) {
		$value  = str_replace( '\\,', '::separator::', $value );
		$values = explode( $separator, $value );
		$values = array_map( array( $this, 'wt_explode_values_formatter' ), $values );

		return $values;
	}//end wt_explode_values()


	/**
	 * Remove formatting and trim each value.
	 *
	 * @since  3.2.0
	 * @param  string $value Value to format.
	 * @return string
	 */
	protected function wt_explode_values_formatter( $value ) {
		return trim( str_replace( '::separator::', ',', $value ) );
	}//end wt_explode_values_formatter()

	/**
	 * Parse order number
	 *
	 * @param string $value Column value.
	 * @return type
	 * @throws Exception Already exist.
	 */
	public function wt_parse_order_number_field( $value ) {
		$order_number_formatted = $this->order_id;
		$order_number           = ( ! empty( $value ) ? $value : ( is_numeric( $order_number_formatted ) ? $order_number_formatted : 0 ) );

		if ( $order_number_formatted ) {
			// verify that this order number isn't already in use.
			$query_args = array(
				'numberposts' => 1,
				/**
				* Filter the query arguments for a request.
				*
				* Enables adding extra arguments or setting defaults for the request.
				*
				* @since 1.0.0
				*
				* @param string   $meta_key    Formatted order number meta_key.
				*/
				'meta_key'    => apply_filters( 'woocommerce_order_number_formatted_meta_name', '_order_number_formatted' ),
				'meta_value'  => $order_number_formatted,
				'post_type'   => $this->post_type,
				'post_status' => array_keys( wc_get_order_statuses() ),
				'fields'      => 'ids',
			);

			$order_id = 0;
			$orders   = get_posts( $query_args );
			if ( ! empty( $orders ) ) {
				list( $order_id ) = get_posts( $query_args );
			}
			/**
			* Filter the query arguments for a request.
			*
			* Enables adding extra arguments or setting defaults for the request.
			*
			* @since 1.0.0
			* @param int $order_id Order ID.
			* @param string   $order_number_formatted    Formatted order number.
			*/
			$order_id = apply_filters( 'woocommerce_find_order_by_order_number', $order_id, $order_number_formatted );

			if ( $order_id ) {
				// skip if order ID already exist.
				throw new Exception( sprintf( 'Skipped. %s already exists.', esc_html( ucfirst( $this->parent_module->module_base ) ) ) );
			}
		}//end if

		if ( $order_number_formatted ) {
			$this->item_data['order_number_formatted'] = $order_number_formatted;
		}

		if ( ! is_null( $order_number ) ) {
			return $order_number;
			// optional order number, for convenience.
		}
	}//end wt_parse_order_number_field()

	/**
	 * Parse date field
	 *
	 * @param string $value Column value.
	 * @param string $column Column name.
	 * @return type
	 * @throws Exception Invalid date.
	 */
	public function wt_parse_date_field( $value, $column ) {

		$date_v = $value;

		if ( '' == $value ) {
			$date_v = gmdate( 'Y-m-d h:i:s' );// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		}
		$date = strtotime( $date_v );
		if ( false === ( $date ) ) {
			// invalid date format.
			throw new Exception( sprintf( 'Skipped. Invalid order date format %s in column %s.', esc_html( $value ), esc_html( $column ) ) );
		}

		return $date;
	}//end wt_parse_date_field()

	/**
	 * Customer ID.
	 *
	 * @param string $value Column value.
	 * @param string $column Column name.
	 * @param array  $data Order data.
	 * @return type
	 */
	public function wt_parse_customer_id_field( $value, $column, $data ) {
		if ( isset( $this->item_data['customer_id'] ) && ! empty( $this->item_data['customer_id'] ) ) {
			return $this->item_data['customer_id'];
		}

		if ( isset( $value ) && $value ) {
			// attempt to find the customer user.
			$value          = trim( $value );
			$found_customer = null;
			switch ( $column ) {
				case 'customer_id':
				case 'customer_user':
					$customer = get_user_by( 'id', $value );
					if ( $customer ) {
						$this->item_data['customer_id'] = $value;
					}

					break;

				case 'customer_email':
					// check by email.
					if ( is_email( $value ) ) {
						$found_customer = email_exists( $value );
						if ( $found_customer ) {
							$this->item_data['customer_id'] = $found_customer;
							break;
						} elseif ( $this->create_user && is_email( $value ) ) {
								$customer_email = $value;
								$username       = ( ! empty( $data['_customer_username'] ) ) ? $data['_customer_username'] : '';
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

							if ( ! empty( $data['_customer_password'] ) ) {
								$password = $data['_customer_password'];
							} else {
								$password = wp_generate_password( 12, true );
							}

								$found_customer = wp_create_user( $username, $password, $customer_email );
							if ( ! is_wp_error( $found_customer ) ) {
								$user_meta_fields = array(
									'billing_first_name',
									// Billing Address Info.
									'billing_last_name',
									'billing_company',
									'billing_address_1',
									'billing_address_2',
									'billing_city',
									'billing_state',
									'billing_postcode',
									'billing_country',
									'billing_email',
									'billing_phone',
									'shipping_first_name',
									// Shipping Address Info.
									'shipping_last_name',
									'shipping_company',
									'shipping_address_1',
									'shipping_address_2',
									'shipping_city',
									'shipping_state',
									'shipping_postcode',
									'shipping_country',
								);

								// update user meta data.
								foreach ( $user_meta_fields as $key ) {
									switch ( $key ) {
										case 'billing_email':
											// user billing email if set in csv otherwise use the user's account email.
											$meta_value = ( ! empty( $data[ $key ] ) ) ? $data[ $key ] : $customer_email;
											$key        = substr( $key, 1 );
											update_user_meta( $found_customer, $key, $meta_value );
											break;

										case 'billing_first_name':
											$meta_value = ( ! empty( $data[ $key ] ) ) ? $data[ $key ] : $username;
											$key        = substr( $key, 1 );
											update_user_meta( $found_customer, $key, $meta_value );
											update_user_meta( $found_customer, 'first_name', $meta_value );
											break;

										case 'billing_last_name':
											$meta_value = ( ! empty( $data[ $key ] ) ) ? $data[ $key ] : '';
											$key        = substr( $key, 1 );
											update_user_meta( $found_customer, $key, $meta_value );
											update_user_meta( $found_customer, 'last_name', $meta_value );
											break;

										case 'shipping_first_name':
										case 'shipping_last_name':
										case 'shipping_address_1':
										case 'shipping_address_2':
										case 'shipping_city':
										case 'shipping_postcode':
										case 'shipping_state':
										case 'shipping_country':
											// Set the shipping address fields to match the billing fields if not specified in CSV.
											$meta_value = ( ! empty( $data[ $key ] ) ) ? $data[ $key ] : '';

											if ( empty( $meta_value ) ) {
												$n_key      = str_replace( 'shipping', 'billing', $key );
												$meta_value = ( ! empty( $data[ $n_key ] ) ) ? $data[ $n_key ] : '';
											}

											$key = substr( $key, 1 );
											update_user_meta( $found_customer, $key, $meta_value );
											break;

										default:
											$meta_value = ( ! empty( $data[ $key ] ) ) ? $data[ $key ] : '';
											$key        = substr( $key, 1 );
											update_user_meta( $found_customer, $key, $meta_value );
									}//end switch
								}//end foreach

								$wp_user_object = new WP_User( $found_customer );
								$wp_user_object->set_role( 'customer' );
								// send user registration email if admin as chosen to do so.
								// @codingStandardsIgnoreStart.
								if ( $this->notify_customer && function_exists( 'wp_new_user_notification' ) ) {
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
								// @codingStandardsIgnoreEnd.
								$this->item_data['customer_id'] = $found_customer;
								break;
							}//end if
						}//end if
					}//end if
			}//end switch
		}//end if
	}//end wt_parse_customer_id_field()

	/**
	 * Product IDs
	 *
	 * @param string $value Column value.
	 * @return type
	 */
	public function wt_parse_product_ids_field( $value ) {
		return $value;
	}//end wt_parse_product_ids_field()

	/**
	 * Order meta
	 *
	 * @param string $value Column value.
	 * @param string $column Column name.
	 * @return type
	 */
	public function wt_parse_meta_field( $value, $column ) {
		$meta_key = trim( str_replace( 'meta:', '', $column ) );
		return array(
			'key'   => $meta_key,
			'value' => $value,
		);
	}//end wt_parse_meta_field()

	/**
	 * Order email
	 *
	 * @param string $value Column value.
	 * @return type
	 */
	public function wt_parse_email_field( $value ) {
		return is_email( $value ) ? $value : '';
	}//end wt_parse_email_field()

	/**
	 * Shipping
	 *
	 * @param string $value Column value.
	 * @param string $column Column name.
	 * @param array  $item Item.
	 * @return type
	 */
	public function wt_parse_order_shipping_field( $value, $column, $item ) {

		$available_methods = WC()->shipping()->load_shipping_methods();

		$order_shipping = $value;

		$order_shipping_methods = array();
		$_shipping_methods      = array();

		// pre WC 2.1 format of a single shipping method, left for backwards compatibility of import files.
		if ( isset( $item['shipping_method'] ) && $item['shipping_method'] ) {
			// collect the shipping method id/cost.
			$_shipping_methods[] = array(
				$item['shipping_method'],
				isset( $item['shipping_cost'] ) ? $item['shipping_cost'] : null,
			);
		}

		// collect any additional shipping methods.
		$i = null;
		if ( isset( $item['shipping_method_1'] ) ) {
			$i = 1;
		} else if ( isset( $item['shipping_method_2'] ) ) {
			$i = 2;
		}

		if ( ! is_null( $i ) ) {
			while ( ! empty( $item[ 'shipping_method_' . $i ] ) ) {
				$_shipping_methods[] = array(
					$item[ 'shipping_method_' . $i ],
					isset( $item[ 'shipping_cost_' . $i ] ) ? $item[ 'shipping_cost_' . $i ] : null,
				);
				$i++;
			}
		}

		// if the order shipping total wasn't set, calculate it.
		if ( ! isset( $order_shipping ) ) {
			$order_shipping = 0;
			foreach ( $_shipping_methods as $_shipping_method ) {
				$order_shipping += $_shipping_method[1];
			}

			$postmeta[] = array(
				'key'   => '_order_shipping' . $column,
				'value' => number_format( (float) $order_shipping, 2, '.', '' ),
			);
		} else if ( isset( $order_shipping ) && 1 == count( $_shipping_methods ) && is_null( $_shipping_methods[0][1] ) ) {
			// special case: if there was a total order shipping but no cost for the single shipping method, use the total shipping for the order shipping line item.
			$_shipping_methods[0][1] = $order_shipping;
		}

		foreach ( $_shipping_methods as $_shipping_method ) {
			// look up shipping method by id or title.
			$shipping_method = isset( $available_methods[ $_shipping_method[0] ] ) ? $_shipping_method[0] : null;

			if ( ! $shipping_method ) {
				// try by title.
				foreach ( $available_methods as $method ) {
					if ( 0 === strcasecmp( $method->title, $_shipping_method[0] ) ) {
						$shipping_method = $method->id;
						break;
						// go with the first one we find.
					}
				}
			}

			if ( $shipping_method ) {
				// known shipping method found.
				$order_shipping_methods[] = array(
					'cost'  => $_shipping_method[1],
					'title' => $available_methods[ $shipping_method ]->title,
				);
			} else if ( $_shipping_method[0] ) {
				// Standard format, shipping method but no title.
				$order_shipping_methods[] = array(
					'cost'  => $_shipping_method[1],
					'title' => '',
				);
			}
		}//end foreach

		return $order_shipping_methods;
	}//end wt_parse_order_shipping_field()

	/**
	 * Shipping method
	 *
	 * @param string $value Column value.
	 * @return type
	 */
	public function wt_parse_shipping_method_field( $value ) {

		$order_shipping_methods = array();
		$_shipping_methods      = array();

		$available_methods = WC()->shipping()->load_shipping_methods();
		// look up shipping method by id or title.
		$shipping_method_obj = isset( $available_methods[ $value ] ) ? $available_methods[ $value ] : $value;

		if ( ! $shipping_method_obj ) {
			// try by title.
			foreach ( $available_methods as $method ) {
				if ( 0 === strcasecmp( $method->title, $value ) ) {
					$shipping_method = $method->id;
					break;
					// go with the first one we find.
				}
			}

			$shipping_method_obj = isset( $available_methods[ $shipping_method ] ) ? $available_methods[ $shipping_method ] : $shipping_method;
		}

		return $shipping_method_obj;
	}//end wt_parse_shipping_method_field()

	/**
	 * Payment method
	 *
	 * @param string $value Column value.
	 * @return type
	 */
	public function wt_parse_payment_method_field( $value ) {
		$available_gateways = WC()->payment_gateways->payment_gateways();
		// look up shipping method by id or title.
		$payment_method_obj = isset( $available_gateways[ $value ] ) ? $available_gateways[ $value ] : $value;
		if ( ! $payment_method_obj ) {
			$payment_method = false;
			// try by title.
			foreach ( $available_gateways as $method ) {
				if ( 0 === strcasecmp( $method->title, $value ) ) {
					$payment_method = $method->id;
					break;
					// go with the first one we find.
				}
			}
			if ( $payment_method ) {
				$payment_method_obj = isset( $available_gateways[ $payment_method ] ) ? $available_gateways[ $payment_method ] : $payment_method;
			}
		}

		return $payment_method_obj;
	}//end wt_parse_payment_method_field()

	/**
	 * Shipping items
	 *
	 * @param string $value Column value.
	 * @return array
	 */
	public function wt_parse_shipping_items_field( $value ) {

		$shipping_items = array();

		if ( '' !== $value ) {
						/**
			 * Filter the query arguments for a request.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param string   $separator  shipping item separator.
			 */
			$shipping_line_items = explode( apply_filters( 'wt_change_item_separator', '|' ), $value );
			foreach ( $shipping_line_items as $pair ) {
				$split = strpos( $pair, ':' );
				$name  = substr( $pair, 0, $split );
				$value = substr( $pair, ( $split + 1 ) );
				$shipping_items[ $name ] = $value;
			}
		}
		if ( isset( $shipping_items['taxes'] ) ) {
			$tax_data = maybe_unserialize( $shipping_items['taxes'] );

			if ( isset( $tax_data['total'] ) ) {
				$new_tax_data = array();
				foreach ( $tax_data['total'] as $t_key => $t_value ) {
					if ( isset( $this->item_data['tax_items'][ $t_key ] ) ) {
						$new_tax_data ['total'][ $this->item_data['tax_items'][ $t_key ]['rate_id'] ] = $t_value;
					} else {
						$new_tax_data ['total'][ $t_key ] = $t_value;
					}
				}
			} else {
				$new_tax_data = $tax_data;
			}
			$shipping_items['taxes'] = $new_tax_data;
		}
		return $shipping_items;
	}//end wt_parse_shipping_items_field()

	/**
	 * Fee items
	 *
	 * @param string $value Column value.
	 * @return array
	 */
	public function wt_parse_fee_items_field( $value ) {
		$fee_items = array();
		if ( '' !== $value ) {
			$fee_line_items = explode( '||', $value );
			foreach ( $fee_line_items as $fee_line_item ) {
				$fee_item_meta = explode( '|', $fee_line_item );
				$name = array_shift( $fee_item_meta );
				$name = substr( $name, strpos( $name, ':' ) + 1 );
				$total = array_shift( $fee_item_meta );
				$total = substr( $total, strpos( $total, ':' ) + 1 );
				$tax = array_shift( $fee_item_meta );
				$tax = substr( $tax, strpos( $tax, ':' ) + 1 );
				$tax_data = array_shift( $fee_item_meta );
				$tax_data = substr( $tax_data, strpos( $tax_data, ':' ) + 1 );
				$tax_data = maybe_unserialize( $tax_data );
				$new_tax_data = array();
				if ( isset( $tax_data['total'] ) ) {
					foreach ( $tax_data['total'] as $t_key => $t_value ) {
						if ( isset( $this->item_data['tax_items'][ $t_key ] ) ) {
							$new_tax_data ['total'][ $this->item_data['tax_items'][ $t_key ]['rate_id'] ] = $t_value;
						} else {
							$new_tax_data ['total'][ $t_key ] = $t_value;
						}
					}
				} else {
					$new_tax_data = $tax_data;
				}
				$fee_items[] = array(
					'name' => $name,
					'total' => $total,
					'tax' => $tax,
					'tax_data' => $new_tax_data,
				);
			}
		}
		return $fee_items;// end if.
	}//end wt_parse_fee_items_field()

	/**
	 * Tax items
	 *
	 * @param string $value Column value.
	 * @return array
	 */
	public function wt_parse_tax_items_field( $value ) {
		global $wpdb;
		$tax_rates = array();

		foreach ( $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates" ) as $_row ) {
			$tax_rates[ $_row->tax_rate_id ] = $_row;
		}
		$tax_items = array();

			$tax_item = explode( ';', $value );
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
			if ( $tax_item_data['rate_id'] && ( ! isset( $tax_rates[ $tax_item_data['rate_id'] ] ) || 0 == $tax_item_data['rate_id'] ) ) {
				$tax_item_data['rate_id'] = 0;
			} else if ( isset( $tax_item_data['label'] ) && ( 0 !== strcasecmp( $tax_rates[ $tax_item_data['rate_id'] ]->tax_rate_name, $tax_item_data['label'] ) || $tax_rates[ $tax_item_data['rate_id'] ]->tax_rate != $tax_item_data['rate_percent'] ) ) {
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
		return $tax_items;
	}//end wt_parse_tax_items_field()

	/**
	 * Coupon items
	 *
	 * @param string $value Column value.
	 * @return type
	 */
	public function wt_parse_coupon_items_field( $value ) {

		// standard format.
		$coupon_item = array();

		if ( isset( $value ) && ! empty( $value ) ) {
			$coupon_item = explode( ';', $value );
		}

		return $coupon_item;
	}//end wt_parse_coupon_items_field()

	/**
	 * Refund items
	 *
	 * @param string $value Column value.
	 * @return type
	 */
	public function wt_parse_refund_items_field( $value ) {

		// added since refund not importing.
		$refund_item = array();
		if ( isset( $value ) && ! empty( $value ) ) {
			$refund_item = explode( ';', $value );
		}

		return $refund_item;
	}//end wt_parse_refund_items_field()

	/**
	 * Order notes
	 *
	 * @param string $value Column value.
	 * @return type
	 */
	public function wt_parse_order_notes_field( $value ) {

		$order_notes = array();
		if ( ! empty( $value ) ) {
			$order_notes = explode( '||', $value );
		}

		return $order_notes;
	}//end wt_parse_order_notes_field()

	/**
	 * Line item fields
	 *
	 * @global object $wpdb
	 * @param string $value Column value.
	 * @param string $column Column name.
	 * @return type
	 */
	public function wt_parse_line_item_field( $value, $column ) {
		if ( empty( $value ) ) {
			return array();
		}

			global $wpdb;
			$order_items = array();
			$variation   = false;

			$_item_meta = array();
		if ( $value && empty( $_item_meta ) ) {
			/**
			 * Filter the query arguments for a request.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param string   $separator  Line item separator.
			 */
			$_item_meta = explode( apply_filters( 'wt_change_item_separator', '|' ), $value );
		}

			// get any additional item meta.
			$item_meta = array();
		foreach ( $_item_meta as $pair ) {
			// replace any escaped pipes.
			$pair = str_replace( '\|', '|', $pair );

			// find the first ':' and split into name-value.
			$split = strpos( $pair, ':' );
			$name  = substr( $pair, 0, $split );
			$value = substr( $pair, ( $split + 1 ) );
			switch ( $name ) {
				case 'name':
					$unknown_product_name = $value;
					break;
				case 'product_id':
					$product_identifier_by_id = $value;
					break;
				case 'sku':
					$product_identifier_by_sku = $value;
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
			}//end switch
		}//end foreach

		if ( $this->ord_link_using_sku || ( empty( $product_identifier_by_id ) ) ) {
			$product_sku = ! empty( $product_identifier_by_sku ) ? $product_identifier_by_sku : '';
			if ( $product_sku ) {
				$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value=%s LIMIT 1", $product_sku ) );
				if ( ! empty( $product_id ) ) {
					if ( get_post_type( $product_id ) == 'product_variation' ) {
						$variation    = true;
						$variation_id = $product_id;
						$product_id   = wp_get_post_parent_id( $variation_id );
						$item_meta['_variation_id'] = $variation_id;
					}
				}
			} else {
				$product_id = '';
			}
		} elseif ( ! empty( $product_identifier_by_id ) ) {
				// product by product_id.
				$product_id = $product_identifier_by_id;

				// not a product.
			if ( ! in_array( get_post_type( $product_id ), array( 'product', 'product_variation' ) ) ) {
				$product_id = '';
			}
		} else {
			$product_id = '';
		}//end if

		if ( ! $this->allow_unknown_products && ! $product_id ) {
			// unknown product.
			return;
		}

			$order_items = array(
				'product_id'   => ! empty( $product_id ) ? $product_id : 0,
				'qty'          => ! empty( $qty ) ? $qty : 0,
				'total'        => ! empty( $total ) ? $total : 0,
				'sub_total'    => ! empty( $sub_total ) ? $sub_total : 0,
				'tax'          => ! empty( $tax ) ? $tax : 0,
				'meta'         => $item_meta,
				'product_name' => ! empty( $unknown_product_name ) ? $unknown_product_name : '',
			);

			if ( ! empty( $tax_data ) ) {
				$tax_data = maybe_unserialize( $tax_data );
				$new_tax_data = array();
				if ( isset( $tax_data['total'] ) ) {
					foreach ( $tax_data['total'] as $t_key => $t_value ) {
						if ( isset( $this->item_data['tax_items'][ $t_key ] ) ) {
							$new_tax_data ['total'][ $this->item_data['tax_items'][ $t_key ]['rate_id'] ] = $t_value;
						} else {
							$new_tax_data ['total'][ $t_key ] = $t_value;
						}
					}
				}
				if ( isset( $tax_data['subtotal'] ) ) {
					foreach ( $tax_data['subtotal'] as $st_key => $st_value ) {
						if ( isset( $this->item_data['tax_items'][ $st_key ] ) ) {
							$new_tax_data ['subtotal'][ $this->item_data['tax_items'][ $st_key ]['rate_id'] ] = $st_value;
						} else {
							$new_tax_data ['subtotal'][ $st_key ] = $st_value;
						}
					}
					$order_items['tax_data'] = serialize( $new_tax_data );
				}
			}

			return $order_items;
	}//end wt_parse_line_item_field()


	/**
	 * Parse relative field and return product ID.
	 *
	 * Handles `id:xx` and SKUs.
	 *
	 * If mapping to an id: and the product ID does not exist, this link is not
	 * valid.
	 *
	 * If mapping to a SKU and the product ID does not exist, a temporary object
	 * will be created so it can be updated later.
	 *
	 * @param string $value Field value.
	 *
	 * @return int|string
	 */
	public function wt_parse_relative_field( $value ) {
		global $wpdb;

		if ( empty( $value ) ) {
			return '';
		}

		// IDs are prefixed with id:.
		// @codingStandardsIgnoreStart.
		if ( preg_match( '/^id:(\d+)$/', $value, $matches ) ) {
			// @codingStandardsIgnoreEnd
			$id = intval( $matches[1] );

			// If original_id is found, use that instead of the given ID since a new placeholder must have been created already.
			$original_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_original_id' AND meta_value = %s;", $id ) );
			// WPCS: db call ok, cache ok.
			if ( $original_id ) {
				return absint( $original_id );
			}

			// See if the given ID maps to a valid product allready.
			$existing_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type IN ( 'product', 'product_variation' ) AND ID = %d;", $id ) );
			// WPCS: db call ok, cache ok.
			if ( $existing_id ) {
				return absint( $existing_id );
			}

			// If we're not updating existing posts, we may need a placeholder product to map to.
			if ( ! $this->params['update_existing'] ) {
				$product = wc_get_product_object( 'simple' );
				$product->set_name( 'Import placeholder for ' . $id );
				$product->set_status( 'importing' );
				$product->add_meta_data( '_original_id', $id, true );
				$id = $product->save();
			}

			return $id;
		}//end if

		$id = wc_get_product_id_by_sku( $value );

		if ( $id ) {
			return $id;
		}

		try {
			$product = wc_get_product_object( 'simple' );
			$product->set_name( 'Import placeholder for ' . $value );
			$product->set_status( 'importing' );
			$product->set_sku( $value );
			$id = $product->save();

			if ( $id && ! is_wp_error( $id ) ) {
				return $id;
			}
		} catch ( Exception $e ) {
			return '';
		}

		return '';
	}//end wt_parse_relative_field()


	/**
	 * Parse the ID field.
	 *
	 * If we're not doing an update, create a placeholder product so mapping works
	 * for rows following this one.
	 *
	 * @param array $data Order value.
	 * @param array $parsed_data Parsed value.
	 * @throws Exception Insert error.
	 * @return int
	 */
	public function wt_parse_id_field( $data, $parsed_data ) {

		if ( ! isset( $data['order_id'] ) ) {
			return 0;
		}

		$id = $this->wt_order_existance_check( $data['order_id'] );

		if ( $id ) {
			return $id;
		}

		if ( class_exists( 'HF_Subscription' ) ) {
			remove_all_actions( 'save_post' );
		}

		global $wpdb;
		if ( 1 == $this->is_sync ) {
			$date = ! empty( $parsed_data['date_created'] ) ? $parsed_data['date_created'] : gmdate( 'Y-m-d H:i:s', time() );
			$postdata = array( // if not specifiying id (id is empty) or if not found by given id.
				'post_date'     => $date,
				'post_date_gmt' => $date,
				'post_type'     => $this->post_type,
				'post_status'   => 'importing',
				'ping_status'   => 'closed',
				'post_author'   => 1,
				'post_title'    => sprintf( 'Order &ndash; %s', strftime( '%b %d, %Y @ %I:%M %p', strtotime( $date ) ) ),
				'post_password' => wc_generate_order_key(),
				'post_parent'   => ! empty( $parsed_data['parent_id'] ) ? $parsed_data['parent_id'] : 0,
				'post_excerpt'  => ! empty( $parsed_data['customer_note'] ) ? $parsed_data['customer_note'] : '',
			);
			if ( isset( $data['order_id'] ) && ! empty( $data['order_id'] ) ) {
				$postdata['import_id'] = $data['order_id'];
			}
			$post_id = wp_insert_post( $postdata, true );
			if ( $post_id ) {
				$order_data = array(
					'id'               => $post_id,
					'date_created_gmt' => $date,
					'type'             => $this->post_type,
					'status'           => 'importing',
					'parent_order_id'  => ! empty( $parsed_data['parent_id'] ) ? $parsed_data['parent_id'] : 0,
					'customer_note'    => ! empty( $parsed_data['customer_note'] ) ? $parsed_data['customer_note'] : '',
				);

				$table_name = $wpdb->prefix . 'wc_orders';
				$insert_order = $wpdb->insert( $table_name, $order_data );
			}
		} elseif ( $this->table_name == $wpdb->prefix . 'posts' ) {
				$date = ! empty( $parsed_data['date_created'] ) ? $parsed_data['date_created'] : gmdate( 'Y-m-d H:i:s', time() );
				$postdata = array( // if not specifiying id (id is empty) or if not found by given id.
					'post_date'     => $date,
					'post_date_gmt' => $date,
					'post_type'     => $this->post_type,
					'post_status'   => 'importing',
					'ping_status'   => 'closed',
					'post_author'   => 1,
					'post_title'    => sprintf( 'Order &ndash; %s', strftime( '%b %d, %Y @ %I:%M %p', strtotime( $date ) ) ),
					'post_password' => wc_generate_order_key(),
					'post_parent'   => ! empty( $parsed_data['parent_id'] ) ? $parsed_data['parent_id'] : 0,
					'post_excerpt'  => ! empty( $parsed_data['customer_note'] ) ? $parsed_data['customer_note'] : '',
				);
				if ( isset( $data['order_id'] ) && ! empty( $data['order_id'] ) ) {
					$postdata['import_id'] = $data['order_id'];
				}
				$post_id = wp_insert_post( $postdata, true );
		} else if ( $this->table_name == $wpdb->prefix . 'wc_orders' ) {
			$date = ! empty( $parsed_data['date_created'] ) ? $parsed_data['date_created'] : gmdate( 'Y-m-d H:i:s', time() );
			$postdata = array( // if not specifiying id (id is empty) or if not found by given id.
				'post_date'     => $date,
				'post_date_gmt' => $date,
				'post_type'     => 'shop_order_placehold',
				'post_status'   => 'draft',
				'ping_status'   => 'closed',
				'post_author'   => 1,
				'post_password' => wc_generate_order_key(),
			);
			if ( isset( $data['order_id'] ) && ! empty( $data['order_id'] ) ) {
				$postdata['import_id'] = $data['order_id'];
			}
			$post_id = wp_insert_post( $postdata, true );
			if ( $post_id ) {
				$order_data = array(
					'id'               => $post_id,
					'date_created_gmt' => $date,
					'type'             => $this->post_type,
					'status'           => 'importing',
					'parent_order_id'  => ! empty( $parsed_data['parent_id'] ) ? $parsed_data['parent_id'] : 0,
					'customer_note'    => ! empty( $parsed_data['customer_note'] ) ? $parsed_data['customer_note'] : '',
				);

				global $wpdb;
				$table_name = $wpdb->prefix . 'wc_orders';
				$insert_order = $wpdb->insert( $table_name, $order_data );
			}
		}
		if ( $post_id && ! is_wp_error( $post_id ) && ( ( isset( $insert_order ) && $insert_order ) || ! isset( $insert_order ) ) ) {
			Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', sprintf( 'Importing as new ' . ( $this->parent_module->module_base ) . ' ID:%d', $post_id ) );
			return $post_id;
		} elseif ( isset( $insert_order ) && ! $insert_order ) {
				throw new Exception( 'Error in creating entry in custom order table' );
		} elseif ( 0 == $post_id ) {
				throw new Exception( 'Error in creating entry in post table' );
		} elseif ( 0 === $post_id ) {
				throw new Exception( 'Error in creating entry in post table' );
		} else {
			throw new Exception( wp_kses_post( $post_id->get_error_message() ) );
		}
	}//end wt_parse_id_field()


	/**
	 * Parse relative comma-delineated field and return product ID.
	 *
	 * @param string $value Field value.
	 *
	 * @return array
	 */
	public function wt_parse_relative_comma_field( $value ) {
		if ( empty( $value ) ) {
			return array();
		}

		return array_filter( array_map( array( $this, 'wt_parse_relative_field' ), $this->wt_explode_values( $value ) ) );
	}//end wt_parse_relative_comma_field()


	/**
	 * Parse a comma-delineated field from a CSV.
	 *
	 * @param string $value Field value.
	 *
	 * @return array
	 */
	public function parse_comma_field( $value ) {
		if ( empty( $value ) && '0' !== $value ) {
			return array();
		}

		$value = $this->unescape_data( $value );
		return array_map( 'wc_clean', $this->wt_explode_values( $value ) );
	}//end parse_comma_field()


	/**
	 * Parse a field that is generally '1' or '0' but can be something else.
	 *
	 * @param string $value Field value.
	 *
	 * @return bool|string
	 */
	public function wt_parse_bool_field( $value ) {
		if ( '0' === $value ) {
			return false;
		}

		if ( '1' === $value ) {
			return true;
		}

		// Don't return explicit true or false for empty fields or values like 'notify'.
		return wc_clean( $value );
	}//end wt_parse_bool_field()


	/**
	 * Parse download file urls, we should allow shortcodes here.
	 *
	 * Allow shortcodes if present, othersiwe esc_url the value.
	 *
	 * @param string $value Field value.
	 *
	 * @return string
	 */
	public function wt_parse_download_file_field( $value ) {
		// Absolute file paths.
		if ( 0 === strpos( $value, 'http' ) ) {
			return esc_url_raw( $value );
		}

		// Relative and shortcode paths.
		return wc_clean( $value );
	}//end wt_parse_download_file_field()


	/**
	 * Parse an int value field
	 *
	 * @param int $value field value.
	 *
	 * @return int
	 */
	public function wt_parse_int_field( $value ) {
		// Remove the ' prepended to fields that start with - if needed.
		return intval( $value );
	}//end wt_parse_int_field()


	/**
	 * Parse the published field. 1 is published, 0 is private, -1 is draft.
	 * Alternatively, 'true' can be used for published and 'false' for draft.
	 *
	 * @param string $value Field value.
	 * @throws Exception Unknown status.
	 * @return float|string
	 */
	public function wt_parse_status_field( $value ) {

		if ( ! empty( $value ) ) {
			$shop_order_status = $this->wc_get_order_statuses_neat();

			$found_status = false;

			foreach ( $shop_order_status as $status_slug => $status_name ) {
				if ( 0 == strcasecmp( $status_slug, $value ) ) {
					$found_status = true;
				}
			}

			if ( $found_status ) {
				return $value;
			} else {
				throw new Exception( sprintf( 'Skipped. Unknown order status (%s).', esc_html( $value ) ) );
			}
		}
	}//end wt_parse_status_field()

	/**
	 * Order status
	 *
	 * @return type
	 */
	private function wc_get_order_statuses_neat() {
		$order_statuses = array();
		foreach ( wc_get_order_statuses() as $slug => $name ) {
			// @codingStandardsIgnoreStart
			$order_statuses[ preg_replace( '/^wc-/', '', $slug ) ] = $name;
			// @codingStandardsIgnoreEnd
		}

		return $order_statuses;
	}//end wc_get_order_statuses_neat()

	/**
	 * Get default order data
	 *
	 * @return type
	 */
	public function get_default_data() {

		return array(
			// Abstract order props.
			'parent_id'            => 0,
			'status'               => '',
			'currency'             => '',
			'version'              => '',
			'prices_include_tax'   => false,
			'date_created'         => null,
			'date_modified'        => null,
			'discount_total'       => 0,
			'discount_tax'         => 0,
			'shipping_total'       => 0,
			'shipping_tax'         => 0,
			'cart_tax'             => 0,
			'total'                => 0,
			'total_tax'            => 0,
			// Order props.
			'customer_id'          => null,
			'order_key'            => '',
			'billing'              => array(
				'first_name' => '',
				'last_name'  => '',
				'company'    => '',
				'address_1'  => '',
				'address_2'  => '',
				'city'       => '',
				'state'      => '',
				'postcode'   => '',
				'country'    => '',
				'email'      => '',
				'phone'      => '',
			),
			'shipping'             => array(
				'first_name' => '',
				'last_name'  => '',
				'company'    => '',
				'address_1'  => '',
				'address_2'  => '',
				'city'       => '',
				'state'      => '',
				'postcode'   => '',
				'country'    => '',
			),
			'payment_method'       => '',
			'payment_method_title' => '',
			'transaction_id'       => '',
			'customer_ip_address'  => '',
			'customer_user_agent'  => '',
			'created_via'          => '',
			'customer_note'        => '',
			'date_completed'       => null,
			'date_paid'            => null,
			'cart_hash'            => '',
		);
	}//end get_default_data()

	/**
	 * Process item
	 *
	 * @global object $wpdb
	 * @param arra $data Order data.
	 * @return \WP_Error
	 */
	public function process_item( $data ) {

		try {
			global $wpdb;
			/**
			 * Filter the query arguments for a request.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param array    $data  Order data.
			 */
			do_action( 'wt_woocommerce_order_import_before_process_item', $data );
			/**
			 * Filter the query arguments for a request.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param array      $data  Order data.
			 */
			$data = apply_filters( 'wt_woocommerce_order_import_process_item', $data );

			$post_id = $data['order_id'];
			$status = ( isset( $data['status'] ) && ! empty( $data['status'] ) ? $data['status'] : 'pending' );
			$data['status']  = 'wc-' . preg_replace( '/^wc-/', '', $status );
			if ( true != $this->status_mail ) {
				remove_all_actions( 'woocommerce_order_status_refunded_notification' );
				remove_all_actions( 'woocommerce_order_partially_refunded_notification' );
				remove_action( 'woocommerce_order_status_refunded', array( 'WC_Emails', 'send_transactional_email' ) );
				remove_action( 'woocommerce_order_partially_refunded', array( 'WC_Emails', 'send_transactional_email' ) );
				remove_action( 'woocommerce_order_fully_refunded', array( 'WC_Emails', 'send_transactional_email' ) );

				add_action( 'woocommerce_email', array( $this, 'wt_iew_order_import_unhook_woocommerce_email' ) );

			}

			$order = wc_create_order( $data );
			if ( is_wp_error( $order ) ) {
				return $order;
			}

			Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', 'Found order object. ID:' . $order->get_id() );
			$default_args = array(
				'status',
				'customer_id',
				'customer_note',
				'parent',
				'created_via',
				'cart_hash',
				'order_id',
				'shipping_items',
				'fee_items',
				'tax_items',
				'coupon_items',
				'refund_items',
				'order_items',
				'meta_data',
			);

			$order->set_props( array_diff_key( $data, array_flip( $default_args ) ) );

			if ( $order->get_id() ) {
				$order_id = $order->get_id();
			}

			$order->set_address( $data['billing'], 'billing' );
			$order->set_address( $data['shipping'], 'shipping' );

			// Store the concatenated version of billing/shipping address to make searches faster.
			if ( $this->is_hpos_enabled ) {
				  $order->update_meta_data( '_billing_address_index', implode( ' ', $order->get_address( 'billing' ) ) );
				  $order->update_meta_data( '_shipping_address_index', implode( ' ', $order->get_address( 'shipping' ) ) );
			} else {
				update_post_meta( $order->get_id(), '_billing_address_index', implode( ' ', $order->get_address( 'billing' ) ) );
				update_post_meta( $order->get_id(), '_shipping_address_index', implode( ' ', $order->get_address( 'shipping' ) ) );
			}
			$order->set_prices_include_tax( 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
			/**
			 * Filter the query arguments for a request.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param string      $order_key  Order key.
			 */
			$order_key = apply_filters( 'woocommerce_generate_order_key', uniqid( 'wc_order_' ) );

			if ( $this->is_hpos_enabled ) {
				$order->set_order_key( $order_key );
			} else {
				update_post_meta( $order->get_id(), '_order_key', $order_key );
			}

			// handle order items.
			$order_items     = array();
			$order_item_meta = null;
			if ( $this->merge && $this->is_order_exist && ! empty( $data['order_items'] ) ) {
				$wpdb->query( $wpdb->prepare( "DELETE items,itemmeta FROM {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items ON itemmeta.order_item_id = items.order_item_id WHERE items.order_id = %d and items.order_item_type = 'line_item'", $order_id ) );
			}

			if ( $this->merge && $this->is_order_exist && ! empty( $data['order_shipping'] ) ) {
				$wpdb->query( $wpdb->prepare( "DELETE items,itemmeta FROM {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items ON itemmeta.order_item_id = items.order_item_id WHERE items.order_id = %d and items.order_item_type = 'shipping'", $order_id ) );
			}

			$_order_item_meta = array();
			if ( ! empty( $data['order_items'] ) ) {
				foreach ( $data['order_items'] as $item ) {
					if ( empty( $item ) ) {
						continue;
						// special case need to rewrite this concept.  empty array returning from wt_parse_line_item_field.
					}

					$product = null;
					$variation_item_meta = array();
					$product_title       = __( 'Unknown Product' );
					if ( $item['product_id'] ) {
						$product = wc_get_product( $item['product_id'] );
						if ( $product ) {
							$product_title = ( $product->get_title() != '' ) ? $product->get_title() : __( 'Unknown Product' );
						}

						// handle variations.
						if ( $product && ( $product->is_type( 'variable' ) || $product->is_type( 'variation' ) || $product->is_type( 'subscription_variation' ) ) && method_exists( $product, 'get_variation_id' ) ) {
							foreach ( $product->get_variation_attributes() as $key => $value ) {
								$variation_item_meta[] = array(
									'meta_name'  => esc_attr( substr( $key, 10 ) ),
									'meta_value' => $value,
								);
								// remove the leading 'attribute_' from the name to get 'pa_color' for instance.
							}
						}
					}

					// order item.
					$order_items[] = array(
						// 'order_item_name' => $product ? $product->get_title() : (!empty($item['unknown_product_name']) ? $item['unknown_product_name'] : __('Unknown Product')),
						'order_item_name' => ! empty( $item['product_name'] ) ? $item['product_name'] : ( $product_title ),
						'order_item_type' => 'line_item',
					);
					$var_id        = 0;
					if ( $product ) {
						if ( WC()->version < '2.7.0' ) {
							$var_id = ( 'variation' === $product->product_type ) ? $product->variation_id : 0;
						} else {
							$var_id = $product->is_type( 'variation' ) ? $product->get_id() : 0;
						}
					}

					// standard order item meta.
					$_order_item_meta = array(
						'_qty'               => (int) $item['qty'],
						'_tax_class'         => '',
						// Tax class (adjusted by filters).
							'_product_id'        => $item['product_id'],
						'_variation_id'      => $var_id,
						'_line_subtotal'     => number_format( (float) $item['sub_total'], 2, '.', '' ),
						// Line subtotal (before discounts).
							'_line_subtotal_tax' => number_format( (float) $item['tax'], 2, '.', '' ),
						// Line tax (before discounts).
							'_line_total'        => number_format( (float) $item['total'], 2, '.', '' ),
						// Line total (after discounts).
							'_line_tax'          => number_format( (float) $item['tax'], 2, '.', '' ),
					// Line Tax (after discounts).
					);
					if ( ! empty( $item['tax_data'] ) ) {
						$_order_item_meta['_line_tax_data'] = $item['tax_data'];
					}

					// add any product variation meta.
					foreach ( $variation_item_meta as $meta ) {
						$_order_item_meta[ $meta['meta_name'] ] = $meta['meta_value'];
					}

					// include any arbitrary order item meta.
					$_order_item_meta  = array_merge( $_order_item_meta, $item['meta'] );
					$order_item_meta[] = $_order_item_meta;
				}//end foreach

				foreach ( $order_items as $key => $order_item ) {
					$order_item_id = wc_add_order_item( $order_id, $order_item );
					if ( $order_item_id ) {
						foreach ( $order_item_meta[ $key ] as $meta_key => $meta_value ) {
							wc_add_order_item_meta( $order_item_id, $meta_key, maybe_unserialize( $meta_value ) );
						}
					}
				}
			}//end if

			$shipping_tax = isset( $data['shipping_tax_total'] ) ? $data['shipping_tax_total'] : 0;
			// create the shipping order items.
			if ( ! empty( $data['order_shipping'] ) ) {
				foreach ( $data['order_shipping'] as $order_shipping ) {
					if ( empty( $order_shipping ) ) {
						continue;
						// special case need to rewrite this concept.  empty array returning from wt_parse_order_shipping_field.
					}

					$shipping_order_item    = array(
						'order_item_name' => ( $order_shipping['title'] ) ? $order_shipping['title'] : $data['shipping_method'],
						'order_item_type' => 'shipping',
					);
					$shipping_order_item_id = wc_add_order_item( $order_id, $shipping_order_item );
					if ( $shipping_order_item_id ) {
						wc_add_order_item_meta( $shipping_order_item_id, 'cost', $order_shipping['cost'] );
						wc_add_order_item_meta( $shipping_order_item_id, 'total_tax', $shipping_tax );
					}
				}
			}

			if ( ! empty( $data['shipping_items'] ) ) {
				foreach ( $data['shipping_items'] as $key => $value ) {
					if ( $shipping_order_item_id ) {
						wc_add_order_item_meta( $shipping_order_item_id, $key, $value );
					} else {
						$shipping_order_item_id = wc_add_order_item( $order_id, $shipping_order_item );
						wc_add_order_item_meta( $shipping_order_item_id, $key, $value );
					}
				}
			}

			// create the fee order items.
			if ( ! empty( $data['fee_items'] ) ) {
				if ( $this->merge && $this->is_order_exist ) {
					$fee_str = 'fee';
					$wpdb->query( $wpdb->prepare( "DELETE items,itemmeta FROM {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items WHERE itemmeta.order_item_id = items.order_item_id and items.order_id = %d and items.order_item_type = %s", $order_id, $fee_str ) );
				}

				foreach ( $data['fee_items'] as $key => $fee_item ) {
					$fee_order_item    = array(
						'order_item_name' => $fee_item['name'],
						'order_item_type' => 'fee',
					);
					$fee_order_item_id = wc_add_order_item( $order_id, $fee_order_item );
					if ( $fee_order_item_id ) {
						wc_add_order_item_meta( $fee_order_item_id, '_line_tax', $fee_item['tax'] );
						wc_add_order_item_meta( $fee_order_item_id, '_line_total', $fee_item['total'] );
						wc_add_order_item_meta( $fee_order_item_id, '_fee_amount', $fee_item['total'] );
						wc_add_order_item_meta( $fee_order_item_id, '_line_tax_data', $fee_item['tax_data'] );
					}
				}
			}

			// create the tax order items.
			if ( ! empty( $data['tax_items'] ) ) {
				if ( $this->merge && $this->is_order_exist ) {
					$tax_str = 'tax';
					$wpdb->query( $wpdb->prepare( "DELETE items,itemmeta FROM {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items WHERE itemmeta.order_item_id = items.order_item_id and items.order_id = %d and items.order_item_type = %s", $order_id, $tax_str ) );
				}
				foreach ( $data['tax_items'] as $tax_item ) {
					$tax_order_item    = array(
						'order_item_name' => $tax_item['title'],
						'order_item_type' => 'tax',
					);
					$tax_order_item_id = wc_add_order_item( $order_id, $tax_order_item );
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

			// importing coupon items.
			if ( ! empty( $data['coupon_items'] ) ) {
				if ( Wt_Import_Export_For_Woo_Common_Helper::wt_iew_is_woocommerce_prior_to( '2.7' ) ) {
					if ( $this->merge && $this->is_order_exist ) {
						$applied_coupons = $order->get_coupon_codes();
						if ( ! empty( $applied_coupons ) ) {
							$order->remove_order_items( 'coupon' );
						}
					}

					$coupon_item = array();
					foreach ( $data['coupon_items'] as $coupon ) {
						$_citem_meta = explode( '|', $coupon );
						$coupon_code = array_shift( $_citem_meta );
						$coupon_code = substr( $coupon_code, ( strpos( $coupon_code, ':' ) + 1 ) );

						$discount_amount = array_shift( $_citem_meta );
						$discount_amount = substr( $discount_amount, ( strpos( $discount_amount, ':' ) + 1 ) );

						$mypost = get_page_by_title( $coupon_code, '', 'shop_coupon' );
						$id     = ( isset( $mypost->ID ) ? $mypost->ID : '' );

						if ( $id && $this->merge && $this->is_order_exist ) {
							$order->add_coupon( $coupon_code, $discount_amount );
						} else {
							$coupon_item['order_item_name'] = $coupon_code;
							$coupon_item['order_item_type'] = 'coupon';
							$order_item_id = wc_add_order_item( $order_id, $coupon_item );
							wc_add_order_item_meta( $order_item_id, 'discount_amount', $discount_amount );
						}
					}//end foreach
				} else {
					$skip_remove_coupon = false;
					if ( $this->merge && $this->is_order_exist ) {
						$applied_coupons = $order->get_coupon_codes();
						if ( ! empty( $applied_coupons ) ) {
							foreach ( $applied_coupons as $coupon ) {
								foreach ( $order->get_items() as $item ) {
									if ( $item->get_product_id() <= 0 ) {
										$skip_remove_coupon = true;
										break;
									}
								}
								if ( ! $skip_remove_coupon ) {
									$order->remove_coupon( $coupon );
								}
							}
						}
					}

					$coupon_item        = array();
					$order_items_exists = 1;
					// ensuring products in the order is exists in the site before applying coupon, if any one of the product not exist it make error while applying coupon.
					foreach ( $order->get_items() as  $item_key => $item_values ) {
						$line_id = $item_values->get_product_id();
						if ( 0 == $line_id ) {
							$order_items_exists = 0;
							break;
						}
					}
					if ( ! $skip_remove_coupon ) {
						foreach ( $data['coupon_items'] as $coupon ) {
							$_citem_meta     = explode( '|', $coupon );
							$coupon_code     = array_shift( $_citem_meta );
							$coupon_code     = substr( $coupon_code, ( strpos( $coupon_code, ':' ) + 1 ) );
							$discount_amount = array_shift( $_citem_meta );
							$discount_amount = substr( $discount_amount, ( strpos( $discount_amount, ':' ) + 1 ) );

							$id = wc_get_coupon_id_by_code( $coupon_code );

							if ( $id && $this->merge && $this->is_order_exist && $order_items_exists ) {
								$order->apply_coupon( $coupon_code );
							} else {
								$coupon_item['order_item_name'] = $coupon_code;
								$coupon_item['order_item_type'] = 'coupon';
								$order_item_id = wc_add_order_item( $order_id, $coupon_item );
								wc_add_order_item_meta( $order_item_id, 'discount_amount', $discount_amount );
							}
						}
					}
				}//end if
			}//end if

			// importing refund items.
			if ( ! empty( $data['refund_items'] ) ) {
				if ( $this->merge && $this->is_order_exist ) {
					$refund = 'shop_order_refund';
					$wpdb->query( $wpdb->prepare( "DELETE po,pm FROM $wpdb->posts AS po INNER JOIN $wpdb->postmeta AS pm ON po.ID = pm.post_id WHERE post_parent = %d and post_type = %s", $order_id, $refund ) );
				}

				foreach ( $data['refund_items'] as $refund ) {
					$single_refund = explode( '|', $refund );
					$amount        = array_shift( $single_refund );
					$amount        = substr( $amount, ( strpos( $amount, ':' ) + 1 ) );
					$reason        = array_shift( $single_refund );
					$reason        = substr( $reason, ( strpos( $reason, ':' ) + 1 ) );
					$date          = array_shift( $single_refund );
					$date          = substr( $date, ( strpos( $date, ':' ) + 1 ) );

					$create_rf_line_item = array();

					foreach ( $order->get_items() as  $item_key => $item_values ) {
						$line_id = $item_values->get_product_id();
						if ( 0 != $line_id ) {
							$create_rf_line_item[ $line_id ] = array( 'refund_total' => $amount );
						}
					}

					$args = array(
						'amount'       => $amount,
						'reason'       => $reason,
						'date_created' => $date,
						'order_id'     => $order_id,
						'line_items'   => $create_rf_line_item,
					);

					$input_currency = isset( $data['currency'] ) ? $data['currency'] : $order->get_currency();
					remove_all_actions( 'woocommerce_order_status_refunded_notification' );
					remove_all_actions( 'woocommerce_order_partially_refunded_notification' );
					remove_action( 'woocommerce_order_status_refunded', array( 'WC_Emails', 'send_transactional_email' ) );
					remove_action( 'woocommerce_order_partially_refunded', array( 'WC_Emails', 'send_transactional_email' ) );
					remove_action( 'woocommerce_order_fully_refunded', array( 'WC_Emails', 'send_transactional_email' ) );
					$this->wt_create_refund( $input_currency, $args );
				}//end foreach
			}//end if

			// add order notes.
			if ( ! empty( $data['order_notes'] ) ) {
				add_filter( 'woocommerce_email_enabled_customer_note', '__return_false' );
				// if ($this->merge && $this->is_order_exist) { // commented for delete order all order notes.
					$wpdb->query( $wpdb->prepare( "DELETE comments,meta FROM {$wpdb->prefix}comments comments LEFT JOIN {$wpdb->prefix}commentmeta meta ON comments.comment_ID = meta.comment_id WHERE comments.comment_post_ID = %d", $order_id ) );
				// }
				foreach ( $data['order_notes'] as $order_note ) {
										/**
										* Filter the query arguments for a request.
										*
										* Enables adding extra arguments or setting defaults for the request.
										*
										* @since 1.1.0
										*
										* @param string   $separator  Order note separator.
										*/
					$note     = explode( apply_filters( 'wt_change_item_separator', '|' ), $order_note );
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
						$comment_id = $order->add_order_note( $con, 1, 1 );
					} else {
						$comment_id = $order->add_order_note( $con, 0, $added_by_user );
					}

					wp_update_comment(
						array(
							'comment_ID' => $comment_id,
							'comment_date' => $date,
						)
					);
				}//end foreach
			}//end if

			$this->set_meta_data( $order, $data );

			if ( isset( $data['order_number'] ) ) {
				if ( $this->is_hpos_enabled ) {
					$order->update_meta_data( '_order_number', $data['order_number'] );
				} else {
					update_post_meta( $order_id, '_order_number', $data['order_number'] );
				}
			}

			// was an original order number provided?
			if ( ! empty( $data['order_number_formatted'] ) ) {
				/**
				 * Provide custom order number functionality , also allow 3rd party plugins to provide their own custom order number facilities.
				 *
				 * Enables adding extra arguments or setting defaults for the request.
				 *
				 * @since 1.0.0
				 *
				 * @param array      $order Order.
				 * @param string     $order_number    Order number.
				 * @param string     $order_formatted_number   Order number formatted.
				 */
				do_action( 'woocommerce_set_order_number', $order, $data['order_number'], $data['order_number_formatted'] );
				/* translators:%s: order number */
				$order->add_order_note( sprintf( __( 'Original order #%s', 'wf_order_import_export' ), $data['order_number_formatted'] ) );
			}
			if ( ! empty( $data['tax_total'] ) ) {
				$shipping_tax_total = $data['shipping_tax'] ? $data['shipping_tax'] : 0;
				$order_tax_total    = ( ( $data['tax_total'] ) - ( $shipping_tax_total ) );
				$id = update_post_meta( $order_id, '_order_tax', $order_tax_total );
			}

			if ( $this->delete_existing ) {
				$order->update_meta_data( '_wt_delete_existing', 1 );
			}
			/**
			 * Pre insert order.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param object      $order Order.
			 * @param array     $data  Order CSV data.
			 */
			$order = apply_filters( 'wt_woocommerce_import_pre_insert_order_object', $order, $data );

			$order->save();
			/**
			 * Post insert order
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param object      $order Order.
			 * @param array     $data  Order CSV data.
			 */
			do_action( 'wt_woocommerce_order_import_inserted_object', $order, $data );

			$result = array(
				'id'      => $order->get_id(),
				// 'updated' => $$updating,
					'updated' => $this->merge,
			);
			return $result;
		} catch ( Exception $e ) {
			return new WP_Error( 'woocommerce_product_importer_error', $e->getMessage(), array( 'status' => $e->getCode() ) );
		}//end try
	}//end process_item()

	/**
	 * Refund process
	 *
	 * @param string $input_currency Currency.
	 * @param array  $args Args.
	 * @return \WP_Error|\WC_Order_Refund
	 * @throws Exception Invalid order.
	 */
	public function wt_create_refund( $input_currency, $args = array() ) {

		$default_args = array(
			'amount'         => 0,
			'reason'         => null,
			'order_id'       => 0,
			'refund_id'      => 0,
			'line_items'     => array(),
			'refund_payment' => false,
			'restock_items'  => false,
		);

		try {
			$args  = wp_parse_args( $args, $default_args );
			$order = wc_get_order( $args['order_id'] );

			if ( ! $order ) {
				   throw new Exception( __( 'Invalid order ID.', 'woocommerce' ) );
			}

			$remaining_refund_amount = $order->get_remaining_refund_amount();
			$remaining_refund_items  = $order->get_remaining_refund_items();
			$refund_item_count       = 0;
			$refund = new WC_Order_Refund( $args['refund_id'] );

			$refund->set_currency( $input_currency );
			$refund->set_amount( $args['amount'] );
			$refund->set_parent_id( absint( $args['order_id'] ) );
			$refund->set_refunded_by( get_current_user_id() ? get_current_user_id() : 1 );
			$refund->set_prices_include_tax( $order->get_prices_include_tax() );

			if ( ! is_null( $args['reason'] ) ) {
				$refund->set_reason( $args['reason'] );
			}

			// Negative line items.
			if ( count( $args['line_items'] ) > 0 ) {
				$items = $order->get_items( array( 'line_item', 'fee', 'shipping' ) );

				foreach ( $items as $item_id => $item ) {
					if ( ! isset( $args['line_items'][ $item_id ] ) ) {
							continue;
					}

					$qty          = isset( $args['line_items'][ $item_id ]['qty'] ) ? $args['line_items'][ $item_id ]['qty'] : 0;
					$refund_total = $args['line_items'][ $item_id ]['refund_total'];
					$refund_tax   = isset( $args['line_items'][ $item_id ]['refund_tax'] ) ? array_filter( (array) $args['line_items'][ $item_id ]['refund_tax'] ) : array();

					if ( empty( $qty ) && empty( $refund_total ) && empty( $args['line_items'][ $item_id ]['refund_tax'] ) ) {
						continue;
					}

					$class         = get_class( $item );
					$refunded_item = new $class( $item );
					$refunded_item->set_id( 0 );
					$refunded_item->add_meta_data( '_refunded_item_id', $item_id, true );
					$refunded_item->set_total( wc_format_refund_total( $refund_total ) );
					$refunded_item->set_taxes(
						array(
							'total'    => array_map( 'wc_format_refund_total', $refund_tax ),
							'subtotal' => array_map( 'wc_format_refund_total', $refund_tax ),
						)
					);

					if ( is_callable( array( $refunded_item, 'set_subtotal' ) ) ) {
							   $refunded_item->set_subtotal( wc_format_refund_total( $refund_total ) );
					}

					if ( is_callable( array( $refunded_item, 'set_quantity' ) ) ) {
							   $refunded_item->set_quantity( $qty * -1 );
					}

					$refund->add_item( $refunded_item );
					$refund_item_count += $qty;
				}//end foreach
			}//end if

			$refund->update_taxes();
			$refund->calculate_totals( false );
			$refund->set_total( ( $args['amount'] * -1 ) );

			// this should remain after update_taxes(), as this will save the order, and write the current date to the db.
			// so we must wait until the order is persisted to set the date.
			if ( isset( $args['date_created'] ) ) {
				$refund->set_date_created( $args['date_created'] );
			}

			/**
			 * Action hook to adjust refund before save.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param object    $refund Order refund object.
			 * @param array     $args  Order args.
			 */
			do_action( 'woocommerce_create_refund', $refund, $args );

			if ( $refund->save() ) {
				if ( $args['refund_payment'] ) {
					$result = wc_refund_payment( $order, $refund->get_amount(), $refund->get_reason() );

					if ( is_wp_error( $result ) ) {
							  $refund->delete();
							  return $result;
					}

					$refund->set_refunded_payment( true );
					$refund->save();
				}

				if ( $args['restock_items'] ) {
					wc_restock_refunded_items( $order, $args['line_items'] );
				}

				// Trigger notification emails.
				if ( ( $remaining_refund_amount - $args['amount'] ) > 0 || ( $order->has_free_item() && ( $remaining_refund_items - $refund_item_count ) > 0 ) ) {
					/**
					 * Action hook to adjust refund.
					 *
					 * Enables adding extra arguments or setting defaults for the request.
					 *
					 * @since 1.0.0
					 *
					 * @param int    $order_id Order id.
					 * @param int    $refund_id Refund id.
					 */
					 do_action( 'wt_woocommerce_order_partially_refunded', $order->get_id(), $refund->get_id() );
				} else {
					/**
					 * Fully refunded status.
					 *
					 * Enables adding extra arguments or setting defaults for the request.
					 *
					 * @since 1.0.0
					 *
					 * @param string $status Refunded status.
					 * @param int    $order_id Order id.
					 * @param int    $refund_id Refund id.
					 */
					$parent_status = apply_filters( 'woocommerce_order_fully_refunded_status', 'refunded', $order->get_id(), $refund->get_id() );

					if ( $parent_status ) {
						$order->update_status( $parent_status );
					}
				}
			}//end if
			/**
			 * Action hook to adjust refund.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param int    $refund_id Refund id.
			 * @param array    $args Refund args.
			 */
			do_action( 'woocommerce_refund_created', $refund->get_id(), $args );
			/**
			 * Action hook to adjust refund.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param int    $order_id Order id.
			 * @param int    $refund_id Refund id.
			 */
			do_action( 'woocommerce_order_refunded', $order->get_id(), $refund->get_id() );
		} catch ( Exception $e ) {
			if ( isset( $refund ) && is_a( $refund, 'WC_Order_Refund' ) ) {
				wp_delete_post( $refund->get_id(), true );
			}

			return new WP_Error( 'error', $e->getMessage() );
		}//end try

		return $refund;
	}//end wt_create_refund()

	/**
	 * Set order meta
	 *
	 * @param object $object Order.
	 * @param array  $data Order data.
	 */
	public function set_meta_data( &$object, $data ) {
		if ( isset( $data['meta_data'] ) ) {
			$order_id = $object->get_id();
			$add_download_permissions = false;
			foreach ( $data['meta_data'] as $meta ) {
				if ( ( 'Download Permissions Granted' == $meta['key'] || '_download_permissions_granted' == $meta['key'] ) && $meta['value'] ) {
					$add_download_permissions = true;
				}

				if ( '_wt_import_key' == $meta['key'] ) {
					/**
					 * Filter the query arguments for a request.
					 *
					 * Enables adding extra arguments or setting defaults for the request.
					 *
					 * @since 1.0.0
					 *
					 * @param string     $order_reference_key Order reference key
					 * @param array      $data  Order data.
					 */
					$object->update_meta_data( '_wt_import_key', apply_filters( 'wt_importing_order_reference_key', $meta['value'], $data ) );
					// for future reference, this holds the order number which in the csv.
					continue;
				}

				if ( is_serialized( $meta['value'] ) ) {
					// Don't attempt to unserialize data that wasn't serialized going in.
					$meta['value'] = ( maybe_unserialize( $meta['value'] ) );

					$meta_function = 'set_' . ltrim( $meta['key'], '_' );
					if ( is_callable( array( $object, $meta_function ) ) ) {
						$object->{$meta_function}( $meta['value'] );
					} else {
						$object->update_meta_data( $meta['key'], $meta['value'] );
					}
				}

				if ( '' === $meta['value'] && ! $this->merge_empty_cells ) {
					continue;
				}

				$object->update_meta_data( $meta['key'], $meta['value'] );
			}//end foreach

			// Grant downloadalbe product permissions.
			if ( $add_download_permissions ) {
				$object->save();
				/**
				 * Force update downloadable product permission.
				 *
				 * Enables adding extra arguments or setting defaults for the request.
				 *
				 * @since 1.0.0
				 *
				 * @param boolean      $force_update  Order data.
				 */
				$force = apply_filters( 'wt_force_update_downloadalbe_product_permissions', true );
				wc_downloadable_product_permissions( $order_id, $force );
			}
		}//end if
	}//end set_meta_data()

	/**
	 * Hook WC emails
	 *
	 * @param object $email_class Email classes.
	 */
	public function wt_iew_order_import_unhook_woocommerce_email( $email_class ) {

			// New order emails.
			remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_pending_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_failed_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_failed_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_failed_to_pending_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );

			// Processing  emails.
			remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_on-hold_to_processing_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_failed_to_processing_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_cancelled_to_processing_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_cancelled_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );

			// On-hold emails.
			remove_action( 'woocommerce_order_status_cancelled_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_cancelled_to_on-hold_notification', array( $email_class->emails['WC_Email_Customer_On_Hold_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $email_class->emails['WC_Email_Customer_On_Hold_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_Customer_On_Hold_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_processing_to_on-hold_notification', array( $email_class->emails['WC_Email_Customer_On_Hold_Order'], 'trigger' ) );

			// Cancelled emails.
			remove_action( 'woocommerce_order_status_on-hold_to_cancelled_notification', array( $email_class->emails['WC_Email_Cancelled_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_processing_to_cancelled_notification', array( $email_class->emails['WC_Email_Cancelled_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_failed_to_cancelled_notification', array( $email_class->emails['WC_Email_Cancelled_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_completed_to_cancelled_notification', array( $email_class->emails['WC_Email_Cancelled_Order'], 'trigger' ) );

			// Completed  emails.
			remove_action( 'woocommerce_order_status_completed_notification', array( $email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_processing_to_completed_notification', array( $email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_refunded_to_completed_notification', array( $email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_cancelled_to_completed_notification', array( $email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_cancelled_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );

			// Refund mails.
			remove_action( 'woocommerce_order_status_completed_to_refunded_notification', array( $email_class->emails['WC_Email_Customer_Refunded_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_processing_to_refunded_notification', array( $email_class->emails['WC_Email_Customer_Refunded_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_refunded', array( $email_class->emails['WC_Email_Customer_Refunded_Order'], 'trigger' ) );

			// Failed emails.
			remove_action( 'woocommerce_order_status_on-hold_to_failed_notification', array( $email_class->emails['WC_Email_Failed_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_pending_to_failed_notification', array( $email_class->emails['WC_Email_Failed_Order'], 'trigger' ) );
	}//end wt_iew_order_import_unhook_woocommerce_email()

	/**
	 * Hook WC emails
	 *
	 * @param object $email_class Email classes.
	 */
	public function wt_iew_order_import_hook_woocommerce_email( $email_class ) {

			// New order emails.
			add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			add_action( 'woocommerce_order_status_pending_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			add_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			add_action( 'woocommerce_order_status_failed_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			add_action( 'woocommerce_order_status_failed_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			add_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );

			// Processing order emails.
			add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
			add_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );

			// Completed order emails.
			add_action( 'woocommerce_order_status_completed_notification', array( $email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger' ) );
	}//end wt_iew_order_import_hook_woocommerce_email()
}//end class
