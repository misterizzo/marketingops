<?php
/**
 * Handles the order export.
 *
 * @package   ImportExportSuite\Admin\Modules\Order\Export
 * @version   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_Order_Export Class.
 */
class Wt_Import_Export_For_Woo_Order_Export {

	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $parent_module = null;
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	private $line_items_max_count = 0;
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	private $export_to_separate_columns = false;
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	private $export_to_separate_rows = false;
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	private $line_item_meta;
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public static $is_sync;
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public static $table_name;
	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $is_hpos_enabled = false;
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
		if ( strpos( $hpos_data['table_name'], 'wc_orders' ) !== false || $hpos_data['sync'] ) {
			$this->is_hpos_enabled = true;
		}
	}//end __construct()

	/**
	 * Prepare CSV header
	 *
	 * @return type
	 */
	public function prepare_header() {

		$export_columns = $this->parent_module->get_selected_column_names();

		$this->line_item_meta = self::get_all_line_item_metakeys();

		$max_line_items = $this->line_items_max_count;

		for ( $i = 1; $i <= $max_line_items; $i++ ) {
			$export_columns[ "line_item_{$i}" ] = "line_item_{$i}";
		}

		if ( $this->export_to_separate_columns ) {
			/**
			 * Filter the query arguments for a request.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.2.3
			 *
			 * @param bool
			 */
			$export_line_item_meta = apply_filters( 'wt_orderimpexp_export_line_item_meta', false );
			for ( $i = 1; $i <= $max_line_items; $i++ ) {
				foreach ( $this->line_item_meta as $meta_value ) {
					$new_val = str_replace( '_', ' ', $meta_value );
					$export_columns[ "line_item_{$i}_name" ]       = "Product Item {$i} Name";
					$export_columns[ "line_item_{$i}_product_id" ] = "Product Item {$i} id";
					$export_columns[ "line_item_{$i}_sku" ]        = "Product Item {$i} SKU";
					$export_columns[ "line_item_{$i}_quantity" ]   = "Product Item {$i} Quantity";
					$export_columns[ "line_item_{$i}_total" ]      = "Product Item {$i} Total";
					$export_columns[ "line_item_{$i}_subtotal" ]   = "Product Item {$i} Subtotal";
					if ( $export_line_item_meta ) {
						if ( in_array( $meta_value, array( '_product_id', '_qty', '_variation_id', '_line_total', '_line_subtotal', '_tax_class', '_line_tax', '_line_tax_data', '_line_subtotal_tax' ) ) ) {
							continue;
						} else {

							$export_columns[ "line_item_{$i}_$meta_value" ] = "Product Item {$i} $new_val";
						}
					}
				}
			}
		}

		if ( $this->export_to_separate_rows ) {
			$export_columns = $this->wt_line_item_separate_row_csv_header( $export_columns );
		}
		/**
		* Filter the query arguments for a request.
		*
		* Enables adding extra arguments or setting defaults for the request.
		*
		* @since 1.0.0
		*
		* @param array   $export_columns    Export columns.
		*/
		return apply_filters( 'hf_alter_csv_header', $export_columns, $max_line_items );
	}//end prepare_header()

	/**
	 * Line item separate row header
	 *
	 * @param array $export_columns Export columns.
	 * @return type
	 */
	public function wt_line_item_separate_row_csv_header( $export_columns ) {

		foreach ( $export_columns as $s_key => $value ) {
			if ( strstr( $s_key, 'line_item_' ) ) {
				unset( $export_columns[ $s_key ] );
			}
		}

		$export_columns['line_item_product_id']   = 'item_product_id';
		$export_columns['line_item_name']         = 'item_name';
		$export_columns['line_item_sku']          = 'item_sku';
		$export_columns['line_item_quantity']     = 'item_quantity';
		$export_columns['line_item_subtotal']     = 'item_subtotal';
		$export_columns['line_item_subtotal_tax'] = 'item_subtotal_tax';
		$export_columns['line_item_total']        = 'item_total';
		$export_columns['line_item_total_tax']    = 'item_total_tax';
		$export_columns['item_refunded']          = 'item_refunded';
		$export_columns['item_refunded_qty']      = 'item_refunded_qty';
		$export_columns['item_meta'] = 'item_meta';
		return $export_columns;
	}//end wt_line_item_separate_row_csv_header()

	/**
	 * Line item separate row data
	 *
	 * @param array $order_export_data Export data.
	 * @param array $order_data_filter_args Default args.
	 * @return type
	 */
	public function wt_line_item_separate_row_csv_data( $order_export_data, $order_data_filter_args ) {
		$row = array();
		$order_id = $order_export_data['order_id'];
		$order    = wc_get_order( $order_id );

		if ( $order ) {
			foreach ( $order->get_items() as $item_key => $item ) {
				foreach ( $order_export_data as $key => $value ) {
					if ( strpos( $key, 'line_item_' ) !== false ) {
						continue;
					} else {
						$data1[ $key ] = $value;
					}
				}

				$item_data = $item->get_data();
				$product   = $item->get_product();

				$data1['line_item_product_id']   = ! empty( $item_data['product_id'] ) ? $item_data['product_id'] : '';
				$data1['line_item_name']         = ! empty( $item_data['name'] ) ? $item_data['name'] : '';
				$data1['line_item_sku']          = ! empty( $product ) ? $product->get_sku() : '';
				$data1['line_item_quantity']     = ! empty( $item_data['quantity'] ) ? $item_data['quantity'] : '';
				$data1['line_item_subtotal']     = ! empty( $item_data['subtotal'] ) ? $item_data['subtotal'] : 0;
				$data1['line_item_subtotal_tax'] = ! empty( $item_data['subtotal_tax'] ) ? $item_data['subtotal_tax'] : 0;
				$data1['line_item_total']        = ! empty( $item_data['total'] ) ? $item_data['total'] : 0;
				$data1['line_item_total_tax']    = ! empty( $item_data['total_tax'] ) ? $item_data['total_tax'] : 0;

				$data1['item_refunded']     = ! empty( $order->get_total_refunded_for_item( $item_key ) ) ? $order->get_total_refunded_for_item( $item_key ) : '';
				$data1['item_refunded_qty'] = ! empty( $order->get_qty_refunded_for_item( $item_key ) ) ? absint( $order->get_qty_refunded_for_item( $item_key ) ) : '';
				$data1['item_meta']         = ! empty( $item_data['meta_data'] ) ? json_encode( $item_data['meta_data'] ) : '';

				$row[] = $data1;
			}//end foreach

			return $row;
		}//end if
	}//end wt_line_item_separate_row_csv_data()

	/**
	 * Separate row CSV export
	 *
	 * @param array $data_array Data array.
	 * @return array
	 */
	public function wt_ier_alter_order_data_befor_export_for_separate_row( $data_array ) {
		$new_data_array = array();
		foreach ( $data_array as $key => $avalue ) {
			if ( is_array( $avalue ) ) {
				if ( count( $avalue ) == 1 ) {
					$new_data_array[] = $avalue[0];
				} else if ( count( $avalue ) > 1 ) {
					foreach ( $avalue as $arrkey => $arrvalue ) {
						$new_data_array[] = $arrvalue;
					}
				}
			}
		}

		return $new_data_array;
	}//end wt_ier_alter_order_data_befor_export_for_separate_row()


	/**
	 * Prepare data that will be exported.
	 *
	 * @param array   $form_data Form data.
	 * @param integer $batch_offset Offset.
	 * @return type
	 */
	public function prepare_data_to_export( $form_data, $batch_offset ) {
		$export_order_statuses = ! empty( $form_data['filter_form_data']['wt_iew_order_status'] ) ? $form_data['filter_form_data']['wt_iew_order_status'] : 'any';
		$products = ! empty( $form_data['filter_form_data']['wt_iew_products'] ) ? $form_data['filter_form_data']['wt_iew_products'] : '';
		$email    = ! empty( $form_data['filter_form_data']['wt_iew_email'] ) ? $form_data['filter_form_data']['wt_iew_email'] : array();
		// user email fields return user ids.
		$start_date = ! empty( $form_data['filter_form_data']['wt_iew_date_from'] ) ? $form_data['filter_form_data']['wt_iew_date_from'] . ' 00:00:00' : gmdate( 'Y-m-d 00:00:00', 0 );// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		$end_date   = ! empty( $form_data['filter_form_data']['wt_iew_date_to'] ) ? $form_data['filter_form_data']['wt_iew_date_to'] . ' 23:59:59.99' : gmdate( 'Y-m-d 23:59:59.99', current_time( 'timestamp' ) );// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		$coupons    = ! empty( $form_data['filter_form_data']['wt_iew_coupons'] ) ? array_filter( explode( ',', strtolower( $form_data['filter_form_data']['wt_iew_coupons'] ) ), 'trim' ) : array();
		$orders     = ! empty( $form_data['filter_form_data']['wt_iew_orders'] ) ? array_filter( explode( ',', strtolower( $form_data['filter_form_data']['wt_iew_orders'] ) ), 'trim' ) : array();

		$export_limit = ! empty( $form_data['filter_form_data']['wt_iew_limit'] ) ? intval( $form_data['filter_form_data']['wt_iew_limit'] ) : 999999999;
		// user limit.
		$current_offset = ! empty( $form_data['filter_form_data']['wt_iew_offset'] ) ? intval( $form_data['filter_form_data']['wt_iew_offset'] ) : 0;
		// user offset.
		$export_offset = $current_offset;
		$batch_count   = ! empty( $form_data['advanced_form_data']['wt_iew_batch_count'] ) ? $form_data['advanced_form_data']['wt_iew_batch_count'] : Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings( 'default_export_batch' );

		$exclude_already_exported = ( ! empty( $form_data['advanced_form_data']['wt_iew_exclude_already_exported'] ) && 'Yes' == $form_data['advanced_form_data']['wt_iew_exclude_already_exported'] ) ? true : false;
		if ( isset( $form_data['advanced_form_data']['wt_iew_export_mode'] ) ) {
			$this->export_to_separate_columns = ( isset( $form_data['advanced_form_data']['wt_iew_export_mode'] ) && ! empty( $form_data['advanced_form_data']['wt_iew_export_mode'] ) && 'columns' == $form_data['advanced_form_data']['wt_iew_export_mode'] ) ? true : false;
			$this->export_to_separate_rows    = ( isset( $form_data['advanced_form_data']['wt_iew_export_mode'] ) && ! empty( $form_data['advanced_form_data']['wt_iew_export_mode'] ) && 'rows' == $form_data['advanced_form_data']['wt_iew_export_mode'] ) ? true : false;
		} else {
			$this->export_to_separate_columns = ( isset( $form_data['advanced_form_data']['wt_iew_export_to_separate_columns'] ) && ! empty( $form_data['advanced_form_data']['wt_iew_export_to_separate_columns'] ) && 'Yes' == $form_data['advanced_form_data']['wt_iew_export_to_separate_columns'] ) ? true : false;
			$this->export_to_separate_rows    = ( isset( $form_data['advanced_form_data']['wt_iew_export_to_separate_rows'] ) && ! empty( $form_data['advanced_form_data']['wt_iew_export_to_separate_rows'] ) && 'Yes' == $form_data['advanced_form_data']['wt_iew_export_to_separate_rows'] ) ? true : false;
		}
		$real_offset = ( $current_offset + $batch_offset );

		if ( $batch_count <= $export_limit ) {
			if ( ( $batch_offset + $batch_count ) > $export_limit ) {
				// last offset.
				$limit = ( $export_limit - $batch_offset );
			} else {
				$limit = $batch_count;
			}
		} else {
			$limit = $export_limit;
		}

		$data_array = array();
		if ( $batch_offset < $export_limit ) {
			/*
			 *   taking total records
			 */
			$order_ids     = 0;
			$total_records = 0;
			global $wpdb;
			if ( $exclude_already_exported ) {
				$table_name = self::$table_name;
				$type = 'shop_order';
				if ( $wpdb->prefix . 'wc_orders' == $table_name ) {
					$exclude_orders = $wpdb->get_col( $wpdb->prepare( "SELECT orders.id AS order_id FROM {$wpdb->prefix}wc_orders AS orders LEFT JOIN {$wpdb->prefix}wc_orders_meta AS ordermeta_already_exported ON (ordermeta_already_exported.order_id = orders.id AND ordermeta_already_exported.meta_key = 'wf_order_exported_status') WHERE orders.type = %s  AND ordermeta_already_exported.meta_value IS NOT NULL", $type ) );

				} else {
					$exclude_orders = $wpdb->get_col( $wpdb->prepare( "SELECT orders.ID AS order_id FROM {$wpdb->posts} AS orders LEFT JOIN {$wpdb->postmeta} AS ordermeta_already_exported ON (ordermeta_already_exported.post_id = orders.ID AND ordermeta_already_exported.meta_key = 'wf_order_exported_status') WHERE orders.post_type = %s AND ordermeta_already_exported.meta_value IS NOT NULL", $type ) );

				}
			}

				// first batch.
			if ( ! empty( $email ) && empty( $products ) && empty( $coupons ) ) {

				$args = array(
					'customer_id' => $email,
					'paginate'    => true,
					'return'      => 'ids',
					'limit'       => $export_limit,
					// user given limit.
						'offset'      => $current_offset,
				// user given offset.
				);

				if ( $exclude_already_exported ) {
					$args['wt_meta_query'][] = ( array(
						'key'     => 'wf_order_exported_status',
						'value'   => false,
						'compare' => 'NOT EXISTS',
					) );
				}

				$ord_email = wc_get_orders( $args );
				$order_ids = $ord_email->orders;
			} else if ( ! empty( $products ) && empty( $coupons ) && empty( $email ) ) {
				$order_ids = self::hf_get_orders_of_products( $products, $export_order_statuses, $export_limit, $current_offset, $end_date, $start_date, $exclude_already_exported );
			} else if ( ! empty( $coupons ) && empty( $products ) && empty( $email ) ) {
				$order_ids = self::hf_get_orders_of_coupons( $coupons, $export_order_statuses, $export_limit, $current_offset, $end_date, $start_date, $exclude_already_exported );
			} else if ( ! empty( $coupons ) && ! empty( $products ) && empty( $email ) ) {
				$ord_prods = self::hf_get_orders_of_products( $products, $export_order_statuses, $export_limit, $current_offset, $end_date, $start_date, $exclude_already_exported );
				$ord_coups = self::hf_get_orders_of_coupons( $coupons, $export_order_statuses, $export_limit, $current_offset, $end_date, $start_date, $exclude_already_exported );
				$order_ids = array_intersect( $ord_prods, $ord_coups );
			} else if ( ! empty( $coupons ) && empty( $products ) && ! empty( $email ) ) {
				$ord_coups = self::hf_get_orders_of_coupons( $coupons, $export_order_statuses, $export_limit, $current_offset, $end_date, $start_date, $exclude_already_exported );
				$args      = array( 'customer_id' => $email );
				$ord_email = wc_get_orders( $args );
				foreach ( $ord_email as $id ) {
					$order_id[] = $id->get_id();
				}

				$order_ids = array_intersect( $order_id, $ord_coups );
			} else if ( empty( $coupons ) && ! empty( $products ) && ! empty( $email ) ) {
				$ord_prods = self::hf_get_orders_of_products( $products, $export_order_statuses, $export_limit, $current_offset, $end_date, $start_date, $exclude_already_exported );

				$args = array( 'customer_id' => $email );

				$ord_email = wc_get_orders( $args );
				foreach ( $ord_email as $id ) {
					$order_id[] = $id->get_id();
				}

				$order_ids = array_intersect( $ord_prods, $order_id );
			} else if ( ! empty( $coupons ) && ! empty( $products ) && ! empty( $email ) ) {
				$ord_prods = self::hf_get_orders_of_products( $products, $export_order_statuses, $export_limit, $current_offset, $end_date, $start_date, $exclude_already_exported );
				$ord_coups = self::hf_get_orders_of_coupons( $coupons, $export_order_statuses, $export_limit, $current_offset, $end_date, $start_date, $exclude_already_exported );

				$args = array( 'customer_id' => $email );
				$ord_email = wc_get_orders( $args );
				foreach ( $ord_email as $id ) {
					$order_id[] = $id->get_id();
				}

				$order_ids = array_intersect( $ord_prods, $ord_coups, $order_id );
			} elseif ( self::$table_name == $wpdb->prefix . 'posts' ) {
					$query_args = array(
						'fields'      => 'ids',
						'post_type'   => 'shop_order',
						'order'       => 'ASC',
						'orderby'     => 'ID',
						'post_status' => $export_order_statuses,
						'date_query'  => array(
							array(
								'before'    => $end_date,
								'after'     => $start_date,
								'inclusive' => true,
							),
						),
					);
					if ( ! empty( $orders ) ) {
						$query_args['post__in'] = $orders;
					}
					/**
					* Filter the query arguments for a request.
					*
					* Enables adding extra arguments or setting defaults for the request.
					*
					* @since 1.0.0
					*
					* @param array   $query_args    Query parameters.
					*/
					$query_args           = apply_filters( 'wt_orderimpexpcsv_export_query_args', $query_args );
					$query_args['offset'] = $current_offset;
					// user given offset.
					$query_args['posts_per_page'] = $export_limit;
					// user given limit.
					$query = new WP_Query( $query_args );

					$order_ids = $query->posts;
			} else {
				$query_args = array(
					'return'       => 'ids',
					'status'       => $export_order_statuses, // Array of order statuses to include.
					'type'         => 'shop_order',
					'date_created' => $start_date . '...' . $end_date,
					'limit'        => $export_limit, // Limit the number of orders.
					'offset'       => $current_offset,
				);
					/**
				* Filter the query arguments for a request.
				*
				* Enables adding extra arguments or setting defaults for the request.
				*
				* @since 1.0.0
				*
				* @param array   $query_args    Query parameters.
				*/
				$query_args = apply_filters( 'wt_orderimpexpcsv_export_query_args', $query_args );
				$order_ids = wc_get_orders( $query_args );
			}//end if
			if ( ! empty( $orders ) ) {
				$order_ids = array_intersect( $order_ids, $orders );
			}
			if ( ! empty( $exclude_orders ) ) {

				$order_ids = array_diff( $order_ids, $exclude_orders );
			}
				$total_records = count( $order_ids );

				$this->line_items_max_count = $this->get_max_line_items( $order_ids );
				update_option( 'wt_order_line_items_max_count', $this->line_items_max_count );

			if ( empty( $this->line_items_max_count ) ) {
				$this->line_items_max_count = get_option( 'wt_order_line_items_max_count' );
			}

			/**
			* Filter the query arguments for a request.
			*
			* Enables adding extra arguments or setting defaults for the request.
			*
			* @since 1.0.0
			*
			* @param array   $order_ids    Order IDs.
			*/
			$order_ids = apply_filters( 'wt_orderimpexpcsv_alter_order_ids', array_unique( $order_ids ) );

			$order_ids = array_slice( $order_ids, $batch_offset, $limit );

			foreach ( $order_ids as $order_id ) {
				$data_array[] = $this->generate_row_data( $order_id );
				// updating records with expoted status.
				update_post_meta( $order_id, 'wf_order_exported_status', true );
				$order = wc_get_order( $order_id );
				$order->update_meta_data( 'wf_order_exported_status', true );
				$order->save();
			}

			if ( $this->export_to_separate_rows ) {
				$data_array = $this->wt_ier_alter_order_data_befor_export_for_separate_row( $data_array );
			}
			/**
			* Filter the query arguments for a request.
			*
			* Enables adding extra arguments or setting defaults for the request.
			*
			* @since 1.0.0
			*
			* @param array   $data_array    Order data.
			*/
			$data_array = apply_filters( 'wt_ier_alter_order_data_befor_export', $data_array );
			$return = array(
				'total' => $total_records,
				'data' => $data_array,
			);
			if ( 0 == $batch_offset && 0 == $total_records ) {
				$return['no_post'] = __( 'Nothing to export under the selected criteria. Please check and try adjusting the filters.' );
			}

			return $return;
		}//end if
	}//end prepare_data_to_export()

	/**
	 * Generate CSV row
	 *
	 * @param inetger $order_id Order id.
	 * @return type
	 */
	public function generate_row_data( $order_id ) {

		// $csv_columns = $this->parent_module->get_selected_column_names();
		$csv_columns = $this->prepare_header();

		$found_meta = array();
		// $this->parent_module->wt_get_found_meta();
		foreach ( $csv_columns as $key => $value ) {
			if ( 'meta:' == substr( (string) $key, 0, 5 ) ) {
				$found_meta[ substr( (string) $key, 5 ) ] = $value;
				unset( $csv_columns[ $key ] );
			}
		}

		$row = array();
		// Get an instance of the WC_Order object.
		$order      = wc_get_order( $order_id );
		$line_items = array();
		$shipping_items = array();
		$fee_items = array();
		$tax_items = array();
		$coupon_items = array();
		$refund_items = array();

		// get line items.
		foreach ( $order->get_items() as $item_id => $item ) {
			// WC_Abstract_Legacy_Order::get_product_from_item() deprecated since version 4.4.0.
			$product = ( WC()->version < '4.4.0' ) ? $order->get_product_from_item( $item ) : $item->get_product();
			if ( ! is_object( $product ) ) {
				$product = new WC_Product( 0 );
			}

			// $item_meta = function_exists('wc_get_order_item_meta') ? wc_get_order_item_meta($item_id, '', false) : $order->get_item_meta($item_id);
			$item_meta = self::get_order_line_item_meta( $item_id );
			$prod_type = ( WC()->version < '3.0.0' ) ? $product->product_type : $product->get_type();
			$line_item = array(
				'name'       => html_entity_decode( ! empty( $item['name'] ) ? $item['name'] : $product->get_title(), ENT_NOQUOTES, 'UTF-8' ),
				'product_id' => ( WC()->version < '2.7.0' ) ? $product->id : ( ( 'variable' == $prod_type || 'variation' == $prod_type || 'subscription_variation' == $prod_type ) ? $product->get_parent_id() : $product->get_id() ),
				'sku'        => $product->get_sku(),
				'quantity'   => $item['qty'],
				'total'      => wc_format_decimal( $order->get_line_total( $item ), 2 ),
				'sub_total'  => wc_format_decimal( $order->get_line_subtotal( $item ), 2 ),
					// 'meta' => html_entity_decode($meta, ENT_NOQUOTES, 'UTF-8'),
			);

			// add line item tax.
			$line_tax_data = isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : array();
			$tax_data      = maybe_unserialize( $line_tax_data );
			$tax_detail    = isset( $tax_data['total'] ) ? wc_format_decimal( wc_round_tax_total( array_sum( (array) $tax_data['total'] ) ), 2 ) : '';
			if ( '0.00' != $tax_detail && ! empty( $tax_detail ) ) {
				$line_item['tax']      = $tax_detail;
				$line_tax_ser          = maybe_serialize( $line_tax_data );
				$line_item['tax_data'] = $line_tax_ser;
			}

			foreach ( $item_meta as $key => $value ) {
				switch ( $key ) {
					case '_qty':
					case '_variation_id':
					case '_product_id':
					case '_line_total':
					case '_line_subtotal':
					case '_tax_class':
					case '_line_tax':
					case '_line_tax_data':
					case '_line_subtotal_tax':
						break;

					default:
						if ( is_object( $value ) ) {
							$value = $value->meta_value;
						}

						if ( is_array( $value ) ) {
							$value = implode( ',', $value );
						}

						$line_item[ $key ] = $value;
						break;
				}//end switch
			}//end foreach

			$refunded = wc_format_decimal( $order->get_total_refunded_for_item( $item_id ), 2 );
			if ( '0.00' != $refunded ) {
				$line_item['refunded'] = $refunded;
			}

			if ( 'variable' === $prod_type || 'variation' === $prod_type || 'subscription_variation' === $prod_type ) {
				$line_item['_variation_id'] = ( WC()->version > '2.7' ) ? $product->get_id() : $product->variation_id;
			}

			$line_items[] = $line_item;
		}//end foreach

		/**
			Fforeach ($order->get_shipping_methods() as $_ => $shipping_item) {

			$shipping_items[] = implode('|', array(
			'method:' . $shipping_item['name'],
			'total:' . wc_format_decimal($shipping_item['cost'], 2),
			));
			}
		 */
		// shipping items is just product x qty under shipping method.
		$line_items_shipping = $order->get_items( 'shipping' );

		foreach ( $line_items_shipping as $item_id => $item ) {
			$item_meta = self::get_order_line_item_meta( $item_id );
			foreach ( $item_meta as $key => $value ) {
				switch ( $key ) {
					case 'Items':
					case 'method_id':
					case 'taxes':
						if ( is_object( $value ) ) {
							$value = $value->meta_value;
						}

						if ( is_array( $value ) ) {
							$value = implode( ',', $value );
						}

						$meta[ $key ] = $value;
						break;
					default:
						if ( is_object( $value ) ) {
							$value = $value->meta_value;
						}

						if ( is_array( $value ) ) {
							$value = implode( ',', $value );
						}

							$meta[ $key ] = $value;
						break;
				}
			}

			foreach ( $meta as $meta_name => $meta_value ) {
				$shipping_item[ $meta_name ] = $meta_name . ':' . $meta_value;
			}
			/**
			 * Filter the query arguments for a request.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param string   $separator  $shipping item separator.
			 */
			$shipping_items[] = implode( apply_filters( 'wt_change_item_separator', '|' ), $shipping_item );
		}//end foreach

		// get fee and total.
		$fee_total     = 0;
		$fee_tax_total = 0;

		foreach ( $order->get_fees() as $fee_id => $fee ) {
			$fee_items[]    = implode(
				'|',
				array(
					'name:' . html_entity_decode( $fee['name'], ENT_NOQUOTES, 'UTF-8' ),
					'total:' . wc_format_decimal( $fee['line_total'], 2 ),
					'tax:' . wc_format_decimal( $fee['line_tax'], 2 ),
					'tax_data:' . maybe_serialize( $fee['line_tax_data'] ),
				)
			);
			$fee_total += (float) $fee['line_total'];
			$fee_tax_total += (float) $fee['line_tax'];
		}

		$order_taxes = $order->get_taxes();
		if ( ! empty( $order_taxes ) ) {
			foreach ( $order_taxes as $tax_id => $tax_item ) {
				if ( ! empty( $tax_item->get_shipping_tax_total() ) ) {
					$total = ( $tax_item->get_tax_total() + $tax_item->get_shipping_tax_total() );
				} else {
					$total = $tax_item->get_tax_total();
				}

				$tax_items[] = implode(
					'|',
					array(
						'rate_id:' . $tax_item->get_rate_id(),
						'code:' . $tax_item->get_rate_code(),
						'total:' . wc_format_decimal( $tax_item->get_tax_total(), 2 ),
						'label:' . $tax_item->get_label(),
						'tax_rate_compound:' . $tax_item->get_compound(),
						'shipping_tax_amount:' . $tax_item->get_shipping_tax_total(),
						'rate_percent:' . $tax_item->get_rate_percent(),
					)
				);
			}
		}//end if

		// add coupons.
		if ( ( WC()->version < '4.4.0' ) ) {
			foreach ( $order->get_items( 'coupon' ) as $_ => $coupon_item ) {
				$discount_amount = ! empty( $coupon_item['discount_amount'] ) ? $coupon_item['discount_amount'] : 0;
				$coupon_items[]  = implode(
					'|',
					array(
						'code:' . $coupon_item['name'],
						'amount:' . wc_format_decimal( $discount_amount, 2 ),
					)
				);
			}
		} else {
			foreach ( $order->get_coupon_codes() as $_ => $coupon_code ) {
				$coupon_obj      = new WC_Coupon( $coupon_code );
				$discount_amount = ! empty( $coupon_obj->get_amount() ) ? $coupon_obj->get_amount() : 0;
				$coupon_items[]  = implode(
					'|',
					array(
						'code:' . $coupon_code,
						'amount:' . wc_format_decimal( $discount_amount, 2 ),
					)
				);
			}
		}//end if

		foreach ( $order->get_refunds() as $refunded_items ) {
			if ( ( WC()->version < '2.7.0' ) ) {
				$refund_items[] = implode(
					'|',
					array(
						'amount:' . $refunded_items->get_refund_amount(),
						'reason:' . $refunded_items->reason,
						'date:' . gmdate( 'Y-m-d H:i:s', strtotime( $refunded_items->date_created ) ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					)
				);
			} else {
				$refund_items[] = implode(
					'|',
					array(
						'amount:' . $refunded_items->get_amount(),
						'reason:' . $refunded_items->get_reason(),
						'date:' . gmdate( 'Y-m-d H:i:s', strtotime( $refunded_items->get_date_created() ) ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					)
				);
			}
		}//end foreach

		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			$paid_date  = get_post_meta( $order->id, '_date_paid' );
			$a_userdata = get_userdata( $order->get_user_id() );
			$order_data = array(
				'order_id'             => $order->id,
				'order_number'         => $order->get_order_number(),
				'order_date'           => gmdate( 'Y-m-d H:i:s', strtotime( get_post( $order->id )->post_date ) ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				'paid_date'            => isset( $paid_date ) ? gmdate( 'Y-m-d H:i:s', $paid_date ) : '', // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				'status'               => $order->get_status(),
				'shipping_total'       => $order->get_total_shipping(),
				'shipping_tax_total'   => wc_format_decimal( $order->get_shipping_tax(), 2 ),
				'fee_total'            => wc_format_decimal( $fee_total, 2 ),
				'fee_tax_total'        => wc_format_decimal( $fee_tax_total, 2 ),
				'tax_total'            => wc_format_decimal( $order->get_total_tax(), 2 ),
				'cart_discount'        => ( defined( 'WC_VERSION' ) && ( WC_VERSION >= 2.3 ) ) ? wc_format_decimal( $order->get_total_discount(), 2 ) : wc_format_decimal( $order->get_cart_discount(), 2 ),
				'order_discount'       => ( defined( 'WC_VERSION' ) && ( WC_VERSION >= 2.3 ) ) ? wc_format_decimal( $order->get_total_discount(), 2 ) : wc_format_decimal( $order->get_order_discount(), 2 ),
				'discount_total'       => wc_format_decimal( $order->get_discount_total(), 2 ),
				'order_total'          => wc_format_decimal( $order->get_total(), 2 ),
				'order_subtotal'       => '',

				'order_currency'       => $order->get_order_currency(),
				'payment_method'       => $order->payment_method,
				'payment_method_title' => $order->payment_method_title,
				'transaction_id'       => $order->transaction_id,
				'customer_ip_address'  => $order->customer_ip_address,
				'customer_user_agent'  => $order->customer_user_agent,
				'shipping_method'      => $order->get_shipping_method(),
				'customer_id'          => $order->get_user_id(),
				'customer_user'        => $order->get_user_id(),
				'customer_email'       => ( $a_userdata ) ? $a_userdata->user_email : '',
				'billing_first_name'   => $order->billing_first_name,
				'billing_last_name'    => $order->billing_last_name,
				'billing_company'      => $order->billing_company,
				'billing_email'        => $order->billing_email,
				'billing_phone'        => $order->billing_phone,
				'billing_address_1'    => $order->billing_address_1,
				'billing_address_2'    => $order->billing_address_2,
				'billing_postcode'     => $order->billing_postcode,
				'billing_city'         => $order->billing_city,
				'billing_state'        => $order->billing_state,
				'billing_country'      => $order->billing_country,
				'shipping_first_name'  => $order->shipping_first_name,
				'shipping_last_name'   => $order->shipping_last_name,
				'shipping_company'     => $order->shipping_company,
				'shipping_phone'       => isset( $order->shipping_phone ) ? $order->shipping_phone : '',
				'shipping_address_1'   => $order->shipping_address_1,
				'shipping_address_2'   => $order->shipping_address_2,
				'shipping_postcode'    => $order->shipping_postcode,
				'shipping_city'        => $order->shipping_city,
				'shipping_state'       => $order->shipping_state,
				'shipping_country'     => $order->shipping_country,
				'customer_note'        => $order->customer_note,
				'wt_import_key'        => $order->get_order_number(),
				'shipping_items'       => self::format_data( implode( ';', $shipping_items ) ),
				'fee_items'            => implode( '||', $fee_items ),
				'tax_items'            => implode( ';', $tax_items ),
				'coupon_items'         => implode( ';', $coupon_items ),
				'refund_items'         => implode( ';', $refund_items ),
				'order_notes'          => implode( '||', self::get_order_notes( $order ) ),
				'download_permissions' => $order->download_permissions_granted ? $order->download_permissions_granted : 0,
			);
		} else {
			$paid_date  = $order->get_date_paid();
			$a_userdata = get_userdata( $order->get_user_id() );
			$order_data = array(
				'order_id'             => $order->get_id(),
				'order_number'         => $order->get_order_number(),
				'order_date'           => gmdate( 'Y-m-d H:i:s', strtotime( get_post( $order->get_id() )->post_date ) ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				'paid_date'            => $paid_date,

				'status'               => $order->get_status(),
				'shipping_total'       => $order->get_total_shipping(),
				'shipping_tax_total'   => wc_format_decimal( $order->get_shipping_tax(), 2 ),
				'fee_total'            => wc_format_decimal( $fee_total, 2 ),
				'fee_tax_total'        => wc_format_decimal( $fee_tax_total, 2 ),
				'tax_total'            => wc_format_decimal( $order->get_total_tax(), 2 ),
				'cart_discount'        => ( defined( 'WC_VERSION' ) && ( WC_VERSION >= 2.3 ) ) ? wc_format_decimal( $order->get_total_discount(), 2 ) : wc_format_decimal( $order->get_cart_discount(), 2 ),
				'order_discount'       => ( defined( 'WC_VERSION' ) && ( WC_VERSION >= 2.3 ) ) ? wc_format_decimal( $order->get_total_discount(), 2 ) : wc_format_decimal( $order->get_order_discount(), 2 ),
				'discount_total'       => wc_format_decimal( $order->get_total_discount(), 2 ),
				'order_total'          => wc_format_decimal( $order->get_total(), 2 ),
				'order_subtotal'       => wc_format_decimal( $order->get_subtotal(), 2 ),
				// Get order subtotal.

				'order_currency'       => $order->get_currency(),
				'payment_method'       => $order->get_payment_method(),
				'payment_method_title' => $order->get_payment_method_title(),
				'transaction_id'       => $order->get_transaction_id(),
				'customer_ip_address'  => $order->get_customer_ip_address(),
				'customer_user_agent'  => $order->get_customer_user_agent(),
				'shipping_method'      => $order->get_shipping_method(),
				'customer_id'          => $order->get_user_id(),
				'customer_user'        => $order->get_user_id(),
				'customer_email'       => ( $a_userdata ) ? $a_userdata->user_email : '',
				'billing_first_name'   => $order->get_billing_first_name(),
				'billing_last_name'    => $order->get_billing_last_name(),
				'billing_company'      => $order->get_billing_company(),
				'billing_email'        => $order->get_billing_email(),
				'billing_phone'        => $order->get_billing_phone(),
				'billing_address_1'    => $order->get_billing_address_1(),
				'billing_address_2'    => $order->get_billing_address_2(),
				'billing_postcode'     => $order->get_billing_postcode(),
				'billing_city'         => $order->get_billing_city(),
				'billing_state'        => $order->get_billing_state(),
				'billing_country'      => $order->get_billing_country(),
				'shipping_first_name'  => $order->get_shipping_first_name(),
				'shipping_last_name'   => $order->get_shipping_last_name(),
				'shipping_company'     => $order->get_shipping_company(),
				'shipping_phone'       => ( version_compare( WC_VERSION, '5.6', '<' ) ) ? '' : $order->get_shipping_phone(),
				'shipping_address_1'   => $order->get_shipping_address_1(),
				'shipping_address_2'   => $order->get_shipping_address_2(),
				'shipping_postcode'    => $order->get_shipping_postcode(),
				'shipping_city'        => $order->get_shipping_city(),
				'shipping_state'       => $order->get_shipping_state(),
				'shipping_country'     => $order->get_shipping_country(),
				'customer_note'        => $order->get_customer_note(),
				'wt_import_key'        => $order->get_order_number(),
				'shipping_items'       => self::format_data( implode( ';', $shipping_items ) ),
				'fee_items'            => implode( '||', $fee_items ),
				'tax_items'            => implode( ';', $tax_items ),
				'coupon_items'         => implode( ';', $coupon_items ),
				'refund_items'         => implode( ';', $refund_items ),
				'order_notes'          => implode( '||', ( defined( 'WC_VERSION' ) && ( WC_VERSION >= 3.2 ) ) ? self::get_order_notes_new( $order ) : self::get_order_notes( $order ) ),
				'download_permissions' => $order->is_download_permitted() ? $order->is_download_permitted() : 0,
			);
		}//end if

		$order_export_data = array();
		foreach ( $csv_columns as $key => $value ) {
			if ( ! $order_data || array_key_exists( $key, $order_data ) ) {
				$order_export_data[ $key ] = $order_data[ $key ];
			}
		}

		if ( $found_meta ) {
			foreach ( $found_meta as $key => $value ) {
				if ( $this->is_hpos_enabled ) {
					$order_export_data[ $value ] = self::format_data( maybe_serialize( $order->get_meta( $key ) ) );
				} else {
					$order_export_data[ $value ] = self::format_data( maybe_serialize( get_post_meta( $order_data['order_id'], $key, true ) ) );
				}
			}
		}

		$li = 1;
		foreach ( $line_items as $line_item ) {
			foreach ( $line_item as $name => $value ) {
				$line_item[ $name ] = $name . ':' . $value;
			}
			/**
			 * Filter the query arguments for a request.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param string   $separator  Line item separator.
			 */
			$line_item = implode( apply_filters( 'wt_change_item_separator', '|' ), $line_item );
			$order_export_data[ "line_item_{$li}" ] = $line_item;
			$li++;
		}

		$max_line_items = $this->line_items_max_count;
		for ( $i = 1; $i <= $max_line_items; $i++ ) {
			$order_export_data[ "line_item_{$i}" ] = ! empty( $order_export_data[ "line_item_{$i}" ] ) ? self::format_data( $order_export_data[ "line_item_{$i}" ] ) : '';
		}

		if ( $this->export_to_separate_columns ) {
			$line_item_values     = self::get_all_metakeys_and_values( $order );
			$this->line_item_meta = self::get_all_line_item_metakeys();
			$max_line_items       = $this->line_items_max_count;
			/**
			 * Filter the query arguments for a request.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.2.3
			 *
			 * @param bool
			 */
			$export_line_item_meta = apply_filters( 'wt_orderimpexp_export_line_item_meta', false );

			for ( $i = 1; $i <= $max_line_items; $i++ ) {
				/**
				 * Filter the query arguments for a request.
				 *
				 * Enables adding extra arguments or setting defaults for the request.
				 *
				 * @since 1.0.0
				 *
				 * @param string   $separator  Line item separator.
				 */
				$line_item_array = explode( apply_filters( 'wt_change_item_separator', '|' ), $order_export_data[ "line_item_{$i}" ] );
				foreach ( $this->line_item_meta as $meta_val ) {
					$order_export_data[ "line_item_{$i}_name" ]       = ! empty( $line_item_array[0] ) ? substr( $line_item_array[0], ( strpos( $line_item_array[0], ':' ) + 1 ) ) : '';
					$order_export_data[ "line_item_{$i}_product_id" ] = ! empty( $line_item_array[1] ) ? substr( $line_item_array[1], ( strpos( $line_item_array[1], ':' ) + 1 ) ) : '';
					$order_export_data[ "line_item_{$i}_sku" ]        = ! empty( $line_item_array[2] ) ? substr( $line_item_array[2], ( strpos( $line_item_array[2], ':' ) + 1 ) ) : '';
					$order_export_data[ "line_item_{$i}_quantity" ]   = ! empty( $line_item_array[3] ) ? substr( $line_item_array[3], ( strpos( $line_item_array[3], ':' ) + 1 ) ) : '';
					$order_export_data[ "line_item_{$i}_total" ]      = ! empty( $line_item_array[4] ) ? substr( $line_item_array[4], ( strpos( $line_item_array[4], ':' ) + 1 ) ) : '';
					$order_export_data[ "line_item_{$i}_subtotal" ]   = ! empty( $line_item_array[5] ) ? substr( $line_item_array[5], ( strpos( $line_item_array[5], ':' ) + 1 ) ) : '';

					if ( $export_line_item_meta ) {
						if ( in_array( $meta_val, array( '_product_id', '_qty', '_variation_id', '_line_total', '_line_subtotal', '_tax_class', '_line_tax', '_line_tax_data', '_line_subtotal_tax' ) ) ) {
							continue;
						} else {
							$order_export_data[ "line_item_{$i}_$meta_val" ] = ! empty( $line_item_values[ $i ][ $meta_val ] ) ? $line_item_values[ $i ][ $meta_val ] : '';
						}
					}
				}
			}
		}//end if

		$order_data_filter_args = array( 'max_line_items' => $max_line_items );

		if ( $this->export_to_separate_rows ) {
			$order_export_data = $this->wt_line_item_separate_row_csv_data( $order_export_data, $order_data_filter_args );
		}
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array     $order_export_data  Order data.
		 * @param array     $order_data_filter_args    Export filter arguments action.
		 */
		return apply_filters( 'hf_alter_csv_order_data', $order_export_data, $order_data_filter_args );
	}//end generate_row_data()

	/**
	 * Get order of product
	 *
	 * @global object $wpdb
	 * @param array   $products Products.
	 * @param array   $export_order_statuses Export order status.
	 * @param integer $export_limit Export limit.
	 * @param integer $export_offset Offset.
	 * @param date    $end_date End date.
	 * @param date    $start_date Start date.
	 * @param boolean $exclude_already_exported Exclude.
	 * @param integer $retun_count Count of orders.
	 * @return type
	 */
	public static function hf_get_orders_of_products( $products, $export_order_statuses, $export_limit, $export_offset, $end_date, $start_date, $exclude_already_exported, $retun_count = false ) {
		global $wpdb;
		$where_products      = $wpdb->prepare( ' IN(' . implode( ',', array_fill( 0, count( $products ), '%d' ) ) . ')', $products );

		$export_order_statuses_qry = '';
		if ( 'any' != $export_order_statuses ) {
			// $export_order_statuses_qry = " AND po.post_status IN ( '" . implode( "','", $export_order_statuses ) . "' )"; // Needed to modify here - for selected products.
			// $export_order_statuses_qry = $wpdb->prepare( '  AND po.post_status IN(' . implode( ',', array_fill( 0, count( $export_order_statuses ), '%s' ) ) . ')', $export_order_statuses );// Needed to modify here - for selected products.
			$export_order_statuses_qry = '';
		}

		$exclude_already_exported_qry = '';
		if ( $exclude_already_exported ) {
			// $exclude_already_exported_qry = " AND pm.meta_key = 'wf_order_exported_status' AND pm.meta_value=1"; // Needed to modify here - for selected products.
			$exclude_already_exported_qry = '';
		}

		$limit_offset_qry = '';
		if ( false == $retun_count ) {
			$limit_offset_qry = ' LIMIT ' . intval( $export_limit ) . ' ' . ( ! empty( $export_offset ) ? 'OFFSET ' . intval( $export_offset ) : '' );
		}
		if ( self::$table_name == $wpdb->prefix . 'wc_orders' ) {
			$order_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT po.id FROM {$wpdb->prefix}wc_orders AS po
				LEFT JOIN {$wpdb->prefix}wc_orders_meta AS pm ON pm.order_id = po.id
				LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON oi.order_id = po.id
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om ON om.order_item_id = oi.order_item_id
				WHERE po.type = 'shop_order'
				AND oi.order_item_type = 'line_item'
				AND om.meta_key IN ('_product_id','_variation_id')
				AND om.meta_value %1s
				AND (po.date_created_gmt BETWEEN %s AND %s) %1s %1s %1s",
					$where_products,
					$start_date,
					$end_date,
					$export_order_statuses_qry,
					$exclude_already_exported_qry,
					$limit_offset_qry
				)
			); // @codingStandardsIgnoreLine.
		} else {
			$order_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT po.ID FROM {$wpdb->posts} AS po
				LEFT JOIN {$wpdb->postmeta} AS pm ON pm.post_id = po.ID
				LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON oi.order_id = po.ID
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om ON om.order_item_id = oi.order_item_id
				WHERE po.post_type = 'shop_order'
				AND oi.order_item_type = 'line_item'
				AND om.meta_key IN ('_product_id','_variation_id')
				AND om.meta_value %1s
				AND (po.post_date BETWEEN %s AND %s) %1s %1s %1s",
					$where_products,
					$start_date,
					$end_date,
					$export_order_statuses_qry,
					$exclude_already_exported_qry,
					$limit_offset_qry
				)
			); // @codingStandardsIgnoreLine.
		}

		if ( true == $retun_count ) {
			return count( $order_ids );
		}
		return $order_ids;
	}//end hf_get_orders_of_products()

	/**
	 * Get order of coupon
	 *
	 * @global object $wpdb
	 * @param array   $where_coupon Coupons.
	 * @param array   $export_order_statuses Order status.
	 * @param integer $export_limit Limit.
	 * @param integer $export_offset Offset.
	 * @param date    $end_date End date.
	 * @param date    $start_date Start date.
	 * @param boolean $exclude_already_exported Exclude.
	 * @param integer $retun_count Count.
	 * @return type
	 */
	public static function hf_get_orders_of_coupons( $where_coupon, $export_order_statuses, $export_limit, $export_offset, $end_date, $start_date, $exclude_already_exported, $retun_count = false ) {
		global $wpdb;

		$where_params[] = $start_date;
		$where_params[] = $end_date;
		foreach ( $where_coupon as $coupon_code ) {
			$where_params[] = $coupon_code;
		}
		if ( self::$table_name == $wpdb->prefix . 'wc_orders' ) {
			$order_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT po.id FROM {$wpdb->prefix}wc_orders AS po
				LEFT JOIN {$wpdb->prefix}wc_orders_meta AS pm ON pm.order_id = po.id
				LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON oi.order_id = po.id
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om ON om.order_item_id = oi.order_item_id
				WHERE po.type = 'shop_order'
				AND oi.order_item_type = 'coupon'			
				AND (po.date_created_gmt BETWEEN %s AND %s)
				AND oi.order_item_name IN(" . implode( ',', array_fill( 0, count( $where_coupon ), '%s' ) ) . ')',
					$where_params
				)
			);// @codingStandardsIgnoreLine.
		} else {
			$order_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT po.ID FROM {$wpdb->posts} AS po
				LEFT JOIN {$wpdb->postmeta} AS pm ON pm.post_id = po.ID
				LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON oi.order_id = po.ID
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om ON om.order_item_id = oi.order_item_id
				WHERE po.post_type = 'shop_order'
				AND oi.order_item_type = 'coupon'			
				AND (po.post_date BETWEEN %s AND %s)
				AND oi.order_item_name IN(" . implode( ',', array_fill( 0, count( $where_coupon ), '%s' ) ) . ')',
					$where_params
				)
			);// @codingStandardsIgnoreLine.
		}
		if ( true == $retun_count ) {
			return count( $order_ids );
		}
		return $order_ids;
	}//end hf_get_orders_of_coupons()

	/**
	 * Line item keys
	 *
	 * @global object $wpdb
	 * @return type
	 */
	public static function get_all_line_item_metakeys() {
		global $wpdb;
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array   $item_meta    Import columns.
		 */
		$filter_meta = apply_filters( 'wt_order_export_select_line_item_meta', array() );
		$filter_meta = ! empty( $filter_meta ) ? implode( "','", $filter_meta ) : '';
		$line_item = 'line_item';

		$filtered_meta_qry = '';
		if ( ! empty( $filter_meta ) ) {
			$filtered_meta_qry = " AND om.meta_key IN ('" . esc_sql( $filter_meta ) . "')";
		}

		$meta_keys = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT om.meta_key
            FROM {$wpdb->prefix}woocommerce_order_itemmeta AS om 
            INNER JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON om.order_item_id = oi.order_item_id
            WHERE oi.order_item_type = %s %1s",
				$line_item,
				$filtered_meta_qry
			)
		);// @codingStandardsIgnoreLine.
		return $meta_keys;
	}//end get_all_line_item_metakeys()

	/**
	 * Get order item meta
	 *
	 * @param object $item_id Order.
	 * @return type
	 */
	public static function get_order_line_item_meta( $item_id ) {
		global $wpdb;
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array   $item_meta    Import columns.
		 */
		$filtered_meta = apply_filters( 'wt_order_export_select_line_item_meta', array() );
		$filtered_meta = ! empty( $filtered_meta ) ? implode( "','", $filtered_meta ) : '';

		$filtered_meta_qry = '';
		if ( ! empty( $filtered_meta ) ) {
			$filtered_meta_qry = " AND meta_key IN ('" . esc_sql( $filtered_meta ) . "')";
		}

		$meta_keys = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key,meta_value
            FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = %d %1s",
				$item_id,
				$filtered_meta_qry
			),
			OBJECT_K
		);// @codingStandardsIgnoreLine.
		return $meta_keys;
	}//end get_order_line_item_meta()

	/**
	 * Get order notes
	 *
	 * @param object $order Order.
	 * @return type
	 */
	public static function get_order_notes( $order ) {
		$callback = array(
			'WC_Comments',
			'exclude_order_comments',
		);
		$args     = array(
			'post_id' => ( WC()->version < '2.7.0' ) ? $order->id : $order->get_id(),
			'approve' => 'approve',
			'type'    => 'order_note',
		);
		remove_filter( 'comments_clauses', $callback );
		$notes = get_comments( $args );
		add_filter( 'comments_clauses', $callback );
		$notes       = array_reverse( $notes );
		$order_notes = array();
		foreach ( $notes as $note ) {
			$date          = $note->comment_date;
			$customer_note = 0;
			if ( get_comment_meta( $note->comment_ID, 'is_customer_note', '1' ) ) {
				$customer_note = 1;
			}

			$order_notes[] = implode(
				/**
				 * Filter the query arguments for a request.
				 *
				 * Enables adding extra arguments or setting defaults for the request.
				 *
				 * @since 1.1.0
				 *
				 * @param string   $separator  Order note separator.
				 */
				apply_filters( 'wt_change_item_separator', '|' ),
				array(
					'content:' . str_replace( array( "\r", "\n" ), ' ', $note->comment_content ),
					'date:' . ( ! empty( $date ) ? $date : current_time( 'mysql' ) ),
					'customer:' . $customer_note,
					'added_by:' . $note->added_by,
				)
			);
		}

		return $order_notes;
	}//end get_order_notes()

	/**
	 * Get order notes
	 *
	 * @param object $order Order.
	 * @return type
	 */
	public static function get_order_notes_new( $order ) {
		$notes       = wc_get_order_notes(
			array(
				'order_id' => $order->get_id(),
				'order_by' => 'date_created',
				'order' => 'ASC',
			)
		);
		$order_notes = array();
		foreach ( $notes as $note ) {

			$order_notes[] = implode(
			/**
			 * Filter the query arguments for a request.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.1.0
			 *
			 * @param string   $separator  Order note separator.
			 */
				apply_filters( 'wt_change_item_separator', '|' ),
				array(
					'content:' . str_replace( array( "\r", "\n" ), ' ', $note->content ),
					'date:' . $note->date_created->date( 'Y-m-d H:i:s' ),
					'customer:' . $note->customer_note,
					'added_by:' . $note->added_by,
				)
			);
		}

		return $order_notes;
	}//end get_order_notes_new()

	/**
	 * Get all metakeys and values
	 *
	 * @param object $order Order.
	 * @return type
	 */
	public static function get_all_metakeys_and_values( $order = null ) {
		$in = 1;
		$line_item_values = array();
		foreach ( $order->get_items() as $item_id => $item ) {
			// $item_meta = function_exists('wc_get_order_item_meta') ? wc_get_order_item_meta($item_id, '', false) : $order->get_item_meta($item_id);
			$item_meta = self::get_order_line_item_meta( $item_id );
			foreach ( $item_meta as $key => $value ) {
				switch ( $key ) {
					case '_qty':
					case '_product_id':
					case '_line_total':
					case '_line_subtotal':
					case '_tax_class':
					case '_line_tax':
					case '_line_tax_data':
					case '_line_subtotal_tax':
						break;

					default:
						if ( is_object( $value ) ) {
							$value = $value->meta_value;
						}

						if ( is_array( $value ) ) {
							$value = implode( ',', $value );
						}

						$line_item_value[ $key ] = $value;
						break;
				}//end switch
			}//end foreach

			$line_item_values[ $in ] = ! empty( $line_item_value ) ? $line_item_value : '';
			$in++;
		}//end foreach

		return $line_item_values;
	}//end get_all_metakeys_and_values()


	/**
	 * Format the data if required
	 *
	 * @param  string $meta_value Meta value.
	 * @param  string $meta Name of meta key.
	 * @return string
	 */
	public static function format_export_meta( $meta_value, $meta ) {
		switch ( $meta ) {
			case '_sale_price_dates_from':
			case '_sale_price_dates_to':
				return $meta_value ? gmdate( 'Y-m-d', $meta_value ) : '';// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				break;
			case '_upsell_ids':
			case '_crosssell_ids':
				return implode( '|', array_filter( (array) json_decode( $meta_value ) ) );
				break;
			default:
				return $meta_value;
				break;
		}
	}//end format_export_meta()

	/**
	 * Format data
	 *
	 * @param string $data CSV data.
	 * @return type
	 */
	public static function format_data( $data ) {
		if ( ! is_array( $data ) ) {
			$data = (string) rawurldecode( $data );
		}

		$data = (string) urldecode( $data );
		// $enc = mb_detect_encoding($data, 'UTF-8, ISO-8859-1', true);
		$use_mb = function_exists( 'mb_detect_encoding' );
		$enc    = '';
		if ( $use_mb ) {
			$enc = mb_detect_encoding( $data, 'UTF-8, ISO-8859-1', true );
		}

		$data = ( 'UTF-8' == $enc ) ? $data : utf8_encode( $data );

		return $data;
	}//end format_data()

	/**
	 * Get max line item
	 *
	 * @param array $line_item_keys Order line items keys.
	 * @return type
	 */
	public static function highest_line_item_count( $line_item_keys ) {

		$all_items = array_count_values( array_column( $line_item_keys, 'order_id' ) );
		return $all_items ? max( $all_items ) : 0;
	}//end highest_line_item_count()


	/**
	 * Wrap a column in quotes for the CSV
	 *
	 * @param  string $data Data to wrap.
	 * @return string Wrapped data.
	 */
	public static function wrap_column( $data ) {
		return '"' . str_replace( '"', '""', $data ) . '"';
	}//end wrap_column()

	/**
	 * Get max line item
	 *
	 * @global object $wpdb
	 * @param array $order_ids Order ids.
	 * @return type
	 */
	public static function get_max_line_items( $order_ids ) {
		global $wpdb;
		$line_item = 'line_item';
		array_unshift( $order_ids, $line_item );
		$line_item_keys   = $wpdb->get_results( $wpdb->prepare( "SELECT p.order_id, p.order_item_type FROM {$wpdb->prefix}woocommerce_order_items AS p WHERE p.order_item_type = %s AND p.order_id IN (" . implode( ',', array_fill( 0, count( $order_ids ) - 1, '%d' ) ) . ')', $order_ids ), ARRAY_A );// @codingStandardsIgnoreLine.
		$max_line_items   = self::highest_line_item_count( $line_item_keys );
		return $max_line_items;
	}//end get_max_line_items()
}//end class



/*
 * https://github.com/woocommerce/woocommerce/wiki/wc_get_orders-and-WC_Order_Query#adding-custom-parameter-support
 * It is possible to add support for custom query variables in wc_get_orders and WC_Order_Query. To do this you need to filter the generated query.
 */
add_filter(
	'woocommerce_order_data_store_cpt_get_orders_query',
	function ( $query, $query_vars ) {
		if ( ! empty( $query_vars['wt_meta_query'] ) ) {
			foreach ( $query_vars['wt_meta_query'] as $meta_querys ) {
				foreach ( $meta_querys as $key => $value ) {
					$meta_query[ $key ] = $value;
				}

				if ( ! empty( $meta_query ) ) {
					$query['meta_query'][] = $meta_query;
				}
			}
		}

		return $query;
	},
	10,
	2
);
