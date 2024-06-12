<?php
/**
 * Handles the subscription export.
 *
 * @package   ImportExportSuite\Admin\Modules\Subscription\Export
 * @version   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_Subscription_Export Class.
 */
class Wt_Import_Export_For_Woo_Subscription_Export {

	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $parent_module = null;

	/**
	 * Line item maximum count.
	 *
	 * @var int
	 */
	public $line_items_max_count = 0;

	/**
	 * Line item meta array.
	 *
	 * @var array
	 */
	public $line_item_meta = array();

	/**
	 * Export to separate columns
	 *
	 * @var bool
	 */
	private $export_to_separate_columns = false;
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
	 * Order table is in sync.
	 *
	 * @var bool
	 */
	public static $is_sync;
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
		if ( false !== strpos( $hpos_data['table_name'], 'wc_orders' ) ) {
			self::$is_hpos_enabled = true;
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
		if ( $this->export_to_separate_columns ) {
			for ( $i = 1; $i <= $max_line_items; $i++ ) {
				$export_columns[ "line_item_{$i}_product_id" ] = "Product Item {$i} id";
					$export_columns[ "line_item_{$i}_variation_id" ] = "Variation Item {$i} id";
					$export_columns[ "line_item_{$i}_name" ]       = "Product Item {$i} Name";
					$export_columns[ "line_item_{$i}_sku" ]        = "Product Item {$i} SKU";
					$export_columns[ "line_item_{$i}_quantity" ]   = "Product Item {$i} Quantity";
					$export_columns[ "line_item_{$i}_total" ]      = "Product Item {$i} Total";
					$export_columns[ "line_item_{$i}_subtotal" ]   = "Product Item {$i} Subtotal";
				foreach ( $this->line_item_meta as $meta_value ) {
					$new_val = str_replace( '_', ' ', $meta_value );
					if ( in_array( $meta_value, array( '_product_id', '_qty', '_variation_id', '_line_total', '_line_subtotal' ) ) ) {
						continue;
					} else {
						$export_columns[ "line_item_{$i}_$meta_value" ] = "Product Item {$i} $new_val";
					}
				}
			}
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
		return apply_filters( 'wt_alter_subscription_csv_header', $export_columns, $max_line_items );
	}//end prepare_header()

	/**
	 * Prepare data that will be exported.
	 *
	 * @param array   $form_data Form data.
	 * @param integer $batch_offset Offset.
	 * @return type
	 */
	public function prepare_data_to_export( $form_data, $batch_offset ) {

		$export_statuses = ! empty( $form_data['filter_form_data']['wt_iew_statuses'] ) ? $form_data['filter_form_data']['wt_iew_statuses'] : 'any';
		$start_date      = ! empty( $form_data['filter_form_data']['wt_iew_start_date'] ) ? $form_data['filter_form_data']['wt_iew_start_date'] : '';

		$end_date = ! empty( $form_data['filter_form_data']['wt_iew_end_date'] ) ? $form_data['filter_form_data']['wt_iew_end_date'] . ' 23:59:59.99' : '';

		$next_pay_date = ! empty( $form_data['filter_form_data']['wt_iew_next_pay_date'] ) ? $form_data['filter_form_data']['wt_iew_next_pay_date'] : '';

		$payment_methods = ! empty( $form_data['filter_form_data']['wt_iew_payment_methods'] ) ? wc_clean( $form_data['filter_form_data']['wt_iew_payment_methods'] ) : array();
		$email           = ! empty( $form_data['filter_form_data']['wt_iew_email'] ) ? wc_clean( $form_data['filter_form_data']['wt_iew_email'] ) : array();
		$products        = ! empty( $form_data['filter_form_data']['wt_iew_products'] ) ? wc_clean( $form_data['filter_form_data']['wt_iew_products'] ) : array();
		$coupons         = ! empty( $form_data['filter_form_data']['wt_iew_coupons'] ) ? array_filter( explode( ',', strtolower( $form_data['filter_form_data']['wt_iew_coupons'] ) ), 'trim' ) : array();

		$export_sortby = ! empty( $form_data['filter_form_data']['wt_iew_sort_columns'] ) ? implode( ' ', $form_data['filter_form_data']['wt_iew_sort_columns'] ) : 'ID';
		// get_post accept spaced string.
		$export_sort_order = ! empty( $form_data['filter_form_data']['wt_iew_order_by'] ) ? $form_data['filter_form_data']['wt_iew_order_by'] : 'ASC';

		$export_limit = ! empty( $form_data['filter_form_data']['wt_iew_limit'] ) ? intval( $form_data['filter_form_data']['wt_iew_limit'] ) : 999999999;
		// user limit.
		$current_offset = ! empty( $form_data['filter_form_data']['wt_iew_offset'] ) ? intval( $form_data['filter_form_data']['wt_iew_offset'] ) : 0;
		// user offset.
		$batch_count = ! empty( $form_data['advanced_form_data']['wt_iew_batch_count'] ) ? $form_data['advanced_form_data']['wt_iew_batch_count'] : Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings( 'default_export_batch' );

		$this->export_to_separate_columns = ( ! empty( $form_data['advanced_form_data']['wt_iew_export_to_separate_columns'] ) && 'Yes' == $form_data['advanced_form_data']['wt_iew_export_to_separate_columns'] ) ? true : false;
		$subscription_plugin = 'WC';
		if ( class_exists( 'HF_Subscription' ) ) {
			$subscription_plugin = 'HF';
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
			if ( self::$is_hpos_enabled ) {
				global $wpdb;
				$format = array();
				$post_type = ( 'WC' == $subscription_plugin ) ? 'shop_subscription' : 'hf_shop_subscription';
				$subscription_post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT po.id FROM {$wpdb->prefix}wc_orders as po LEFT JOIN {$wpdb->prefix}wc_orders_meta AS pm ON po.id = pm.order_id WHERE type = %s ORDER BY id DESC LIMIT %d OFFSET %d", $post_type, $export_limit, $current_offset ) );
				if ( ! empty( $end_date ) ) {
					$subscription_post_ids_end_date = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT po.id FROM {$wpdb->prefix}wc_orders as po LEFT JOIN {$wpdb->prefix}wc_orders_meta AS pm ON po.id = pm.order_id WHERE type = %s AND date_created_gmt<=%s ORDER BY id DESC LIMIT %d OFFSET %d", $post_type, $end_date, $export_limit, $current_offset ) );
				} else {
					$subscription_post_ids_end_date = $subscription_post_ids;
				}

				if ( ! empty( $start_date ) ) {
					$subscription_post_ids_start_date = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT po.id FROM {$wpdb->prefix}wc_orders as po LEFT JOIN {$wpdb->prefix}wc_orders_meta AS pm ON po.id = pm.order_id WHERE type = %s AND  date_created_gmt>= %s ORDER BY id DESC LIMIT %d OFFSET %d", $post_type, $start_date, $export_limit, $current_offset ) );
				} else {
					$subscription_post_ids_start_date = $subscription_post_ids;
				}

				if ( 'any' !== $export_statuses ) {
					$subscription_post_ids_export_statuses = array();
					$statuses = $export_statuses;
					if ( ! empty( $statuses ) && is_array( $statuses ) ) {
						if ( ! in_array( $statuses, array( 'any', 'trash' ) ) ) {
							array_unshift( $statuses, $post_type );
							$statuses = array_merge( $statuses, array( $export_limit, $current_offset ) );
							$subscription_post_ids_export_statuses = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT po.id FROM {$wpdb->prefix}wc_orders as po LEFT JOIN {$wpdb->prefix}wc_orders_meta AS pm ON po.id = pm.order_id WHERE type = %s AND po.status IN (" . implode( ',', array_fill( 0, ( count( $statuses ) - 3 ), '%s' ) ) . ') ORDER BY id DESC LIMIT %d OFFSET %d', $statuses ) );
						}
					}
				} else {
					$subscription_post_ids_export_statuses = $subscription_post_ids;
				}

				if ( ! empty( $payment_methods ) ) {
					$subscription_post_ids_payment_methods = array();
					array_unshift( $statuses, $post_type );
					$payment_methods = array_merge( $payment_methods, array( $export_limit, $current_offset ) );
					$subscription_post_ids_payment_methods = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT po.id FROM {$wpdb->prefix}wc_orders as po LEFT JOIN {$wpdb->prefix}wc_orders_meta AS pm ON po.id = pm.order_id WHERE type = %s AND po.payment_method IN ( (" . implode( ',', array_fill( 0, ( count( $payment_methods ) - 3 ), '%s' ) ) . ') ORDER BY id DESC LIMIT %d OFFSET %d ORDER BY id DESC LIMIT %d OFFSET %d', $payment_methods ) );
				} else {
					$subscription_post_ids_payment_methods = $subscription_post_ids;

				}

				if ( ! empty( $next_pay_date ) ) {
					$subscription_post_ids_next_pay_date = array();
					$next_pay_date = '%' . $next_pay_date . '%';
					$subscription_post_ids_next_pay_date = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT po.id FROM {$wpdb->prefix}wc_orders as po LEFT JOIN {$wpdb->prefix}wc_orders_meta AS pm ON po.id = pm.order_id WHERE type = %s AND (pm.meta_key = '_schedule_next_payment' AND pm.meta_value LIKE %s) ORDER BY id DESC LIMIT %d OFFSET %d", $post_type, $next_pay_date, $export_limit, $current_offset ) );
				} else {
					$subscription_post_ids_next_pay_date = $subscription_post_ids;
				}
				$subscription_post_ids = array_intersect( $subscription_post_ids, $subscription_post_ids_next_pay_date, $subscription_post_ids_payment_methods, $subscription_post_ids_export_statuses, $subscription_post_ids_start_date, $subscription_post_ids_end_date );
			} else {
				$query_args = array(
					'fields' => 'ids',
					'post_type' => ( 'WC' == $subscription_plugin ) ? 'shop_subscription' : 'hf_shop_subscription',
					'post_status' => 'any',
					'orderby' => $export_sortby,
					'order' => $export_sort_order,
				);

				if ( $end_date || $start_date ) {
					$query_args['date_query'] = array(
						array(
							'before' => $end_date,
							'after' => $start_date,
							'inclusive' => true,
						),
					);
				}

				if ( ! empty( $export_statuses ) ) {
					$statuses = $export_statuses;
					if ( ! empty( $statuses ) && is_array( $statuses ) ) {
						$query_args['post_status'] = implode( ',', $statuses );
						if ( ! in_array( $query_args['post_status'], array( 'any', 'trash' ) ) ) {
							$query_args['post_status'] = self::hf_sanitize_subscription_status_keys( $query_args['post_status'] );
						}
					}
				}

				if ( ! empty( $payment_methods ) ) {
					$meta_query = array( 'relation' => 'OR' );
					foreach ( $payment_methods as $key => $value ) {
						$value = strtolower( $value );
						$meta_query[] = array(
							'key' => '_payment_method',
							'value' => $value,
						);
					}
					$query_args['meta_query'][] = $meta_query;
				}

				if ( ! empty( $next_pay_date ) ) {
					$query_args['meta_query'][]  = array(
						'key' => '_schedule_next_payment',
						'value' => $next_pay_date,
						'compare' => 'LIKE',
					);

				}
				$query_args['posts_per_page'] = $export_limit;
				$query_args['offset'] = $current_offset;

				/**
				* Filter the query arguments for a request.
				*
				* Enables adding extra arguments or setting defaults for the request.
				*
				* @since 1.0.0
				*
				* @param array   $query_args    Query parameters.
				*/
				$query_args = apply_filters( 'woocommerce_get_subscriptions_query_args', $query_args );
				$subscription_post_ids = get_posts( $query_args );
			}

			$subscription_order_ids = array();
			$prod_subscription_ids = array();
			$coupon_subscription_ids = array();
			if ( ! empty( $email ) ) {
				global $wpdb;
				$subscription_type = 'shop_subscription';
				if ( class_exists( 'HF_Subscription' ) ) {
					$subscription_type = 'hf_shop_subscription';
				}
				$email[] = $subscription_type;

				if ( self::$is_hpos_enabled && ! self::$is_sync ) {
					$subscription_order_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}wc_orders WHERE customer_id IN (" . implode( ',', array_fill( 0, count( $email ) - 1, '%d' ) ) . ') AND type = %s ', $email ) );
				} else {
					$subscription_order_ids = $wpdb->get_col( $wpdb->prepare( "SELECT p.ID FROM {$wpdb->prefix}posts AS p INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id  WHERE pm.meta_key = '_customer_user' AND pm.meta_value IN (" . implode( ',', array_fill( 0, count( $email ) - 1, '%d' ) ) . ') AND p.post_type = %s ', $email ) );
				}
			}

			if ( ! empty( $products ) ) {

				$prod_subscription_ids = array();
				if ( function_exists( 'wcs_get_subscriptions_for_product' ) ) {
					$prod_subscription_ids = wcs_get_subscriptions_for_product( $products );
				} elseif ( function_exists( 'hf_get_subscriptions_for_product' ) ) {
						$prod_subscription_ids = hf_get_subscriptions_for_product( $products );
				}
			}

			if ( ! empty( $coupons ) ) {

				$coupon_subscription_ids = array();
				$subscription_type = 'shop_subscription';
				if ( class_exists( 'HF_Subscription' ) ) {
					$subscription_type = 'hf_shop_subscription';
					;
				}
				$coupon_subscription_ids = self::wt_get_subscription_of_coupons( $coupons, $subscription_type );
			}

			/**
			*   Taking total records.
			*/
			$total_records = 0;
			if ( ! empty( $email ) ) {
				if ( ! empty( $subscription_order_ids ) ) {
					$subscription_post_ids = array_intersect( $subscription_order_ids, $subscription_post_ids );
				} else {
					$subscription_post_ids = array();
				}
			}
			if ( ! empty( $products ) ) {
				if ( ! empty( $prod_subscription_ids ) ) {
					$subscription_post_ids = array_intersect( $prod_subscription_ids, $subscription_post_ids );
				} else {
					$subscription_post_ids = array();
				}
			}
			if ( ! empty( $coupons ) ) {
				if ( ! empty( $coupon_subscription_ids ) ) {
					$subscription_post_ids = array_intersect( $coupon_subscription_ids, $subscription_post_ids );
				} else {
					$subscription_post_ids = array();
				}
			}

			foreach ( $subscription_post_ids as $key => $subscription_id ) {
				if ( ! $subscription_id ) {
					unset( $subscription_post_ids[ $key ] );
				}
			}

				$total_records = count( $subscription_post_ids );

			if ( 0 == $batch_offset ) {
				$this->line_items_max_count = $this->get_max_line_items( $subscription_post_ids );
				add_option( 'wt_subscription_order_line_items_max_count', $this->line_items_max_count );
			}
			if ( empty( $this->line_items_max_count ) ) {
				$this->line_items_max_count = get_option( 'wt_subscription_order_line_items_max_count' );
			}
			$subscription_post_ids = array_slice( $subscription_post_ids, $batch_offset, $limit );
			/**
			* Filter the query arguments for a request.
			*
			* Enables adding extra arguments or setting defaults for the request.
			*
			* @since 1.2.4
			*
			* @param array   $subscription_post_ids    Retrieved subscription ids.
			*/
			$subscription_post_ids = apply_filters( 'wt_subscription_export_retrieved_ids', $subscription_post_ids );
			$subscriptions = array();
			foreach ( $subscription_post_ids as $subscription_id ) {
				if ( ! $subscription_id ) {
					break;
				}
				$subscriptions[]  = self::hf_get_subscription( $subscription_id );
			}
			/**
			* Filter the query arguments for a request.
			*
			* Enables adding extra arguments or setting defaults for the request.
			*
			* @since 1.0.0
			*
			* @param array   $subscriptions    Retrieved subscriptions.
			*/
			$subscriptions = apply_filters( 'hf_retrieved_subscriptions', $subscriptions );

			if ( 'WC' == $subscription_plugin ) {
				// Loop orders.
				foreach ( $subscriptions as $subscription ) {

					$data_array[] = $this->get_subscriptions_csv_row( $subscription );

					// updating records with expoted status .
					update_post_meta( $subscription->get_id(), 'wt_subscription_exported_status', true );
				}
			} else {
				// Loop orders.
				foreach ( $subscriptions as $subscription ) {

					$data_array[] = $this->get_wt_subscriptions_csv_row( $subscription );

					// updating records with expoted status .
					update_post_meta( $subscription->get_id(), 'wt_subscription_exported_status', true );
				}
			}
			/**
			* Filter the query arguments for a request.
			*
			* Alter the subscription data.
			*
			* @since 1.2.2
			*
			* @param array   $data_array    Retrieved subscription data.
			*/
			$data_array = apply_filters( 'wt_ier_alter_subscriptions_data_befor_export', $data_array );
			$return = array(
				'total' => $total_records,
				'data' => $data_array,
			);
			if ( 0 == $batch_offset && 0 == $total_records ) {
				// nothing to export.
				$return['no_post'] = __( 'Nothing to export under the selected criteria. Please check and try adjusting the filters.' );
			}

			return $return;
		}//end if
	}//end prepare_data_to_export()

	/**
	 * Get subscription
	 *
	 * @param object $subscription Subscription.
	 * @return type
	 */
	public static function hf_get_subscription( $subscription ) {
		if ( is_object( $subscription ) && self::hf_is_subscription( $subscription ) ) {
			$subscription = $subscription->id;
		}

		$subscription_plugin = 'WC';
		if ( class_exists( 'HF_Subscription' ) ) {
			$subscription_plugin = 'HF';
		}

		if ( 'WC' == $subscription_plugin ) {
			if ( ! class_exists( 'WC_Subscription' ) ) :
				include WP_PLUGIN_DIR . '/woocommerce-subscriptions/wcs-functions.php';
				include WP_PLUGIN_DIR . '/woocommerce-subscriptions/includes/class-wc-subscription.php';
			endif;
			$subscription = new WC_Subscription( $subscription );
		} else {
			if ( ! class_exists( 'HF_Subscription' ) ) :
				include WP_PLUGIN_DIR . '/xa-woocommerce-subscriptions/includes/subscription-common-functions.php';
				include WP_PLUGIN_DIR . '/xa-woocommerce-subscriptions/includes/components/class-subscription.php';
			endif;
			$subscription = new HF_Subscription( $subscription );
		}

		if ( ! self::hf_is_subscription( $subscription ) ) {
			$subscription = false;
		}
		/**
		* Filter the query arguments for a request.
		*
		* Enables adding extra arguments or setting defaults for the request.
		*
		* @since 1.0.0
		*
		* @param object   $subscription    Retrieved subscription.
		*/
		return apply_filters( 'hf_get_subscription', $subscription );
	}//end hf_get_subscription()

	/**
	 * Is subscription
	 *
	 * @param object $subscription Subscription.
	 * @return type
	 */
	public static function hf_is_subscription( $subscription ) {
		if ( is_object( $subscription ) && ( is_a( $subscription, 'WC_Subscription' ) || is_a( $subscription, 'HF_Subscription' ) ) ) {
			$is_subscription = true;
		} else if ( is_numeric( $subscription ) && ( 'shop_subscription' == get_post_type( $subscription ) || 'hf_shop_subscription' == get_post_type( $subscription ) ) ) {
			$is_subscription = true;
		} else {
			$is_subscription = false;
		}
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param boolean  $is_subscription  Is a subscription.
		 * @param object   $subscription    Retrieved subscription.
		 */
		return apply_filters( 'hf_is_subscription', $is_subscription, $subscription );
	}//end hf_is_subscription()

	/**
	 * Get CSV row data
	 *
	 * @param object $subscription Subscription.
	 * @return type
	 */
	public function get_subscriptions_csv_row( $subscription ) {

		$csv_columns = $this->parent_module->get_selected_column_names();
		$row         = array();

		$fee_total = 0;
		$fee_tax_total = 0;
		$fee_items = array();
		$shipping_items = array();
		if ( 0 != count( array_intersect( array_keys( $csv_columns ), array( 'fee_total', 'fee_tax_total', 'fee_items' ) ) ) ) {
			foreach ( $subscription->get_fees() as $fee_id => $fee ) {
				$fee_items[]    = implode(
					'|',
					array(
						'name:' . html_entity_decode( $fee['name'], ENT_NOQUOTES, 'UTF-8' ),
						'total:' . wc_format_decimal( $fee['line_total'], 2 ),
						'tax:' . wc_format_decimal( $fee['line_tax'], 2 ),
						'tax_data:' . $fee['line_tax_data'],
						'tax_class:' . $fee['tax_class'],
					)
				);
				$fee_total     += (float) $fee['line_total'];
				$fee_tax_total += (float) $fee['line_tax'];
			}
		}

		$line_items_shipping = $subscription->get_items( 'shipping' );
		foreach ( $line_items_shipping as $item_id => $item ) {
			if ( is_object( $item ) ) {
				$meta_data = $item->get_formatted_meta_data( '' );
				if ( $meta_data ) :
					foreach ( $meta_data as $meta_id => $meta ) :
						if ( in_array( $meta->key, $line_items_shipping ) ) {
							continue;
						}

						// html entity decode is not working preoperly.
						$shipping_items[] = implode( '|', array( 'item:' . wp_kses_post( $meta->display_key ), 'value:' . str_replace( '&times;', 'X', strip_tags( $meta->display_value ) ) ) );
					endforeach;
				endif;
			}
		}

		if ( ! function_exists( 'get_user_by' ) ) {
			include ABSPATH . 'wp-includes/pluggable.php';
		}

		$user_values = get_user_by( 'ID', ( WC()->version < '2.7' ) ? $subscription->customer_user : $subscription->get_customer_id() );

		// Preparing data for export.
		foreach ( $csv_columns as $header_key => $_ ) {
			switch ( $header_key ) {
				case 'subscription_id':
					$value = ( WC()->version < '2.7' ) ? $subscription->id : $subscription->get_id();
					break;
				case 'subscription_status':
					$value = ( WC()->version < '2.7' ) ? $subscription->post_status : $subscription->get_status();
					break;
				case 'customer_id':
					$value = ( WC()->version < '2.7' ) ? $subscription->customer_user : $subscription->get_customer_id();
					break;
				case 'customer_username':
					$value = is_object( $user_values ) ? $user_values->user_login : '';
					break;
				case 'customer_email':
					$value = is_object( $user_values ) ? $user_values->user_email : '';
					break;
				case 'fee_total':
					$value = $fee_total;
					break;
				case 'fee_tax_total':
					$value = $fee_tax_total;
					break;
				case 'order_shipping_tax':
					$value = ( WC()->version < '2.7' ) ? ( empty( $subscription->order_shipping_tax ) ? 0 : $subscription->order_shipping_tax ) : $subscription->get_shipping_tax();
					break;
				case 'order_total':
					$value = ( WC()->version < '2.7' ) ? ( empty( $subscription->order_total ) ? 0 : $subscription->order_total ) : $subscription->get_total();
					break;
				case 'order_tax':
					$value = ( WC()->version < '2.7' ) ? ( empty( $subscription->order_tax ) ? 0 : $subscription->order_tax ) : $subscription->get_total_tax();
					break;
				case 'order_shipping':
					$value = ( WC()->version < '2.7' ) ? ( empty( $subscription->order_shipping ) ? 0 : $subscription->order_shipping ) : $subscription->get_total_shipping();
					break;
				case 'cart_discount_tax':
					$value = ( WC()->version < '2.7' ) ? ( empty( $subscription->cart_discount_tax ) ? 0 : $subscription->cart_discount_tax ) : $subscription->get_discount_tax();
					break;
				case 'cart_discount':
					$value = ( WC()->version < '2.7' ) ? ( empty( $subscription->cart_discount ) ? 0 : $subscription->cart_discount ) : $subscription->get_total_discount();
					break;
				case 'date_created':
					$value = ( WC()->version < '2.7' ) ? $subscription->date_created : $subscription->get_date( $header_key );
					break;
				case 'trial_end_date':
					$value = ( WC()->version < '2.7' ) ? $subscription->trial_end_date : $subscription->get_date( $header_key );
					break;
				case 'next_payment_date':
					$value = ( WC()->version < '2.7' ) ? $subscription->next_payment_date : $subscription->get_date( $header_key );
					break;
				case 'last_order_date_created':
					$value = ( WC()->version < '2.7' ) ? $subscription->last_order_date_created : $subscription->get_date( $header_key );
					break;
				case 'end_date':
					$value = ( WC()->version < '2.7' ) ? $subscription->end_date : $subscription->get_date( $header_key );
					break;
				case 'billing_period':
					$value = ( WC()->version < '2.7' ) ? $subscription->billing_period : $subscription->get_billing_period();
					break;
				case 'billing_interval':
					$value = ( WC()->version < '2.7' ) ? $subscription->billing_interval : $subscription->get_billing_interval();
					break;
				case 'payment_method':
					$value = ( WC()->version < '2.7' ) ? $subscription->payment_method : $subscription->get_payment_method();
					break;
				case 'payment_method_title':
					$value = ( WC()->version < '2.7' ) ? $subscription->payment_method_title : $subscription->get_payment_method_title();
					break;
				case 'billing_first_name':
					$value = ( WC()->version < '2.7' ) ? $subscription->billing_first_name : $subscription->get_billing_first_name();
					break;
				case 'billing_last_name':
					$value = ( WC()->version < '2.7' ) ? $subscription->billing_last_name : $subscription->get_billing_last_name();
					break;
				case 'billing_email':
					$value = ( WC()->version < '2.7' ) ? $subscription->billing_email : $subscription->get_billing_email();
					break;
				case 'billing_phone':
					$value = ( WC()->version < '2.7' ) ? $subscription->billing_phone : $subscription->get_billing_phone();
					break;
				case 'billing_address_1':
					$value = ( WC()->version < '2.7' ) ? $subscription->billing_address_1 : $subscription->get_billing_address_1();
					break;
				case 'billing_address_2':
					$value = ( WC()->version < '2.7' ) ? $subscription->billing_address_2 : $subscription->get_billing_address_2();
					break;
				case 'billing_postcode':
					$value = ( WC()->version < '2.7' ) ? $subscription->billing_postcode : $subscription->get_billing_postcode();
					break;
				case 'billing_city':
					$value = ( WC()->version < '2.7' ) ? $subscription->billing_city : $subscription->get_billing_city();
					break;
				case 'billing_state':
					$value = ( WC()->version < '2.7' ) ? $subscription->billing_state : $subscription->get_billing_state();
					break;
				case 'billing_country':
					$value = ( WC()->version < '2.7' ) ? $subscription->billing_country : $subscription->get_billing_country();
					break;
				case 'billing_company':
					$value = ( WC()->version < '2.7' ) ? $subscription->billing_company : $subscription->get_billing_company();
					break;
				case 'shipping_first_name':
					$value = ( WC()->version < '2.7' ) ? $subscription->shipping_first_name : $subscription->get_shipping_first_name();
					break;
				case 'shipping_last_name':
					$value = ( WC()->version < '2.7' ) ? $subscription->shipping_last_name : $subscription->get_shipping_last_name();
					break;
				case 'shipping_address_1':
					$value = ( WC()->version < '2.7' ) ? $subscription->shipping_address_1 : $subscription->get_shipping_address_1();
					break;
				case 'shipping_address_2':
					$value = ( WC()->version < '2.7' ) ? $subscription->shipping_address_2 : $subscription->get_shipping_address_2();
					break;
				case 'shipping_postcode':
					$value = ( WC()->version < '2.7' ) ? $subscription->shipping_postcode : $subscription->get_shipping_postcode();
					break;
				case 'shipping_city':
					$value = ( WC()->version < '2.7' ) ? $subscription->shipping_city : $subscription->get_shipping_city();
					break;
				case 'shipping_state':
					$value = ( WC()->version < '2.7' ) ? $subscription->shipping_state : $subscription->get_shipping_state();
					break;
				case 'shipping_country':
					$value = ( WC()->version < '2.7' ) ? $subscription->shipping_country : $subscription->get_shipping_country();
					break;
				case 'shipping_company':
					$value = ( WC()->version < '2.7' ) ? $subscription->shipping_company : $subscription->get_shipping_company();
					break;
				case 'shipping_phone':
					$value = ( version_compare( WC_VERSION, '5.6', '<' ) ) ? '' : $subscription->get_shipping_phone();
					break;
				case 'customer_note':
					$value = ( WC()->version < '2.7' ) ? $subscription->customer_note : $subscription->get_customer_note();
					break;
				case 'order_currency':
					$value = ( WC()->version < '2.7' ) ? $subscription->order_currency : $subscription->get_currency();
					break;
				case 'post_parent':
					if ( ! empty( $subscription->get_parent() ) ) {
						$value = $subscription->get_parent_id();
					} else {
						$value = 0;
					}
					break;
				case 'order_notes':
					$order_notes = implode( '||', ( defined( 'WC_VERSION' ) && ( WC_VERSION >= 3.2 ) ) ? self::get_order_notes_new( $subscription ) : self::get_order_notes( $subscription ) );

					if ( ! empty( $order_notes ) ) {
						$value = $order_notes;
					} else {
						$value = '';
					}
					break;
				case 'renewal_orders':
					$renewal_orders = $subscription->get_related_orders( 'ids', 'renewal' );
					if ( ! empty( $renewal_orders ) ) {
						$value = implode( '|', $renewal_orders );
					} else {
						$value = '';
					}
					break;
				case 'order_items':
					$value      = '';
					$line_items = array();
					foreach ( $subscription->get_items() as $item_id => $item ) {
						$product = $item->get_product();
						if ( ! is_object( $product ) ) {
							$product = new WC_Product( 0 );
						}
						$item_meta = self::get_order_line_item_meta( $item_id );
						$prod_type = ( WC()->version < '3.0.0' ) ? $product->product_type : $product->get_type();
						$line_item = array(
							'product_id' => ( WC()->version < '2.7.0' ) ? $product->id : ( ( 'variable' == $prod_type || 'variation' == $prod_type || 'subscription_variation' == $prod_type ) ? $product->get_parent_id() : $product->get_id() ),
							'name'       => html_entity_decode( $item['name'], ENT_NOQUOTES, 'UTF-8' ),
							'sku'        => $product->get_sku(),
							'quantity'   => $item['qty'],
							'total'      => wc_format_decimal( $subscription->get_line_total( $item ), 2 ),
							'sub_total'  => wc_format_decimal( $subscription->get_line_subtotal( $item ), 2 ),
						);

						// add line item tax.
						$line_tax_data = isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : array();
						$tax_data      = maybe_unserialize( $line_tax_data );
						$tax_detail    = isset( $tax_data['total'] ) ? wc_format_decimal( wc_round_tax_total( array_sum( (array) $tax_data['total'] ) ), 2 ) : '';
						if ( '0.00' != $tax_detail && ! empty( $tax_detail ) ) {
							$line_item['tax'] = $tax_detail;
						}

						$line_tax_ser          = maybe_serialize( $line_tax_data );
						$line_item['tax_data'] = $line_tax_ser;

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

						if ( 'variable' == $prod_type || 'variation' == $prod_type || 'subscription_variation' == $prod_type ) {
							$line_item['_variation_id'] = ( WC()->version > '2.7' ) ? $product->get_id() : $product->variation_id;
						}

						foreach ( $line_item as $name => $value ) {
							$line_item[ $name ] = $name . ':' . $value;
						}

						$line_item = implode( '|', $line_item );

						if ( $line_item ) {
							$line_items[] = $line_item;
						}
					}//end foreach

					if ( ! empty( $line_items ) ) {
						$value = implode( '||', $line_items );
					}
					break;
				case 'coupon_items':
					$coupon_items = array();
					foreach ( $subscription->get_items( 'coupon' ) as $_ => $coupon_item ) {
						$coupon         = new WC_Coupon( $coupon_item['name'] );
						$coupon_post    = get_post( $coupon->id );
						$coupon_items[] = implode(
							'|',
							array(
								'code:' . $coupon_item['name'],
								'description:' . ( is_object( $coupon_post ) ? $coupon_post->post_excerpt : '' ),
								'amount:' . wc_format_decimal( $coupon_item['discount_amount'], 2 ),
							)
						);
					}

					if ( ! empty( $coupon_items ) ) {
						$value = implode( ';', $coupon_items );
					} else {
						$value = '';
					}
					break;
				case 'download_permissions':
					$value = ( WC()->version < '2.7' ) ? ( $subscription->download_permissions_granted ? $subscription->download_permissions_granted : 0 ) : ( $subscription->is_download_permitted() );
					break;
				case 'shipping_method':
					$shipping_lines = array();
					foreach ( $subscription->get_shipping_methods() as $shipping_item_id => $shipping_item ) {
						$shipping_lines[] = implode(
							'|',
							array(
								'method_id:' . $shipping_item['method_id'],
								'method_title:' . $shipping_item['name'],
								'total:' . wc_format_decimal( $shipping_item['cost'], 2 ),
								'total_tax:' . wc_format_decimal( $shipping_item['total_tax'], 2 ),
								'taxes:' . maybe_serialize( $shipping_item['taxes'] ),
							)
						);
					}

					if ( ! empty( $shipping_lines ) ) {
						$value = implode( ';', $shipping_lines );
					} else {
						$value = '';
					}
					break;
				case 'fee_items':
					$value = implode( ';', $fee_items );
					break;
				case 'shipping_items':
					$value = implode( ';', $shipping_items );
					break;
				case 'tax_items':
					$tax_items = array();
					foreach ( $subscription->get_taxes() as $tax_code => $tax ) {
						$tax_items[] = implode(
							'|',
							array(
								'rate_id:' . $tax->get_rate_id(),
								'code:' . $tax->get_rate_code(),
								'total:' . wc_format_decimal( $tax->get_tax_total(), 2 ),
								'label:' . $tax->get_label(),
								'tax_rate_compound:' . $tax->get_compound(),
								'shipping_tax_amount:' . $tax->get_shipping_tax_total(),
								'rate_percent:' . $tax->get_rate_percent(),
							)
						);
					}

					if ( ! empty( $tax_items ) ) {
						$value = implode( ';', $tax_items );
					} else {
						$value = '';
					}
					break;
				default:
					if ( strstr( $header_key, 'meta:' ) ) {
						if ( 'meta:_payment_tokens' == $header_key ) {
							$value = $subscription->get_payment_tokens();
						} else {
							$value = maybe_serialize( $subscription->get_meta( str_replace( 'meta:', '', $header_key ), true ) );
						}
					} else {
						$value = '';
					}
			}//end switch

			$csv_row[ $header_key ] = $value;
		}//end foreach

		$data = array();
		foreach ( $csv_columns as $header_key => $_ ) {
			// Strict string comparison, as values like '0' are valid.
			$value = ( '' !== $csv_row[ $header_key ] ) ? $csv_row[ $header_key ] : '';
			$data[ $header_key ] = $value;
		}
		if ( $this->export_to_separate_columns ) {
			$line_item_values     = self::get_all_metakeys_and_values( $subscription );
			$this->line_item_meta = self::get_all_line_item_metakeys();
			$count = 1;
			foreach ( $subscription->get_items() as $item_id => $item ) {
				$product = $item->get_product();
				if ( ! is_object( $product ) ) {
					$product = new WC_Product( 0 );
				}
				$item_meta = self::get_order_line_item_meta( $item_id );
				$prod_type = ( WC()->version < '3.0.0' ) ? $product->product_type : $product->get_type();
				$is_varaition = ( 'variable' == $prod_type || 'variation' == $prod_type || 'subscription_variation' == $prod_type ) ? true : false;
				$data[ "line_item_{$count}_product_id" ] = ( WC()->version < '2.7.0' ) ? $product->id : ( ( 'variable' == $prod_type || 'variation' == $prod_type || 'subscription_variation' == $prod_type ) ? $product->get_parent_id() : $product->get_id() );
				$data[ "line_item_{$count}_variation_id" ] = $is_varaition ? ( ( WC()->version > '2.7' ) ? $product->get_id() : $product->get_variation_id() ) : '';
				$data[ "line_item_{$count}_name" ]        = html_entity_decode( $item['name'], ENT_NOQUOTES, 'UTF-8' );
				$data[ "line_item_{$count}_sku" ]     = $product->get_sku();
				$data[ "line_item_{$count}_quantity" ]    = $item['qty'];
				$data[ "line_item_{$count}_total" ]         = wc_format_decimal( $subscription->get_line_total( $item ), 2 );
				$data[ "line_item_{$count}_subtotal" ]  = wc_format_decimal( $subscription->get_line_subtotal( $item ), 2 );
				foreach ( $this->line_item_meta as $key ) {
					switch ( $key ) {
						case '_qty':
						case '_variation_id':
						case '_product_id':
						case '_line_total':
						case '_line_subtotal':
							break;

						default:
							if ( isset( $item_meta[ $key ] ) ) {
								if ( is_object( $item_meta[ $key ] ) ) {
									$value = $item_meta[ $key ]->meta_value;
								}

								if ( is_array( $item_meta[ $key ] ) ) {
									$value = implode( ',', $item_meta[ $key ] );
								}
							} else {
								$value = '';
							}

							$data[ "line_item_{$count}_" . $key ] = $value;
							break;
					}//end switch
				}//end foreach
				++$count;
			}
		}//end if

		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array     $data  Subscription data.
		 * @param array     $csv_columns   CSV columns.
		 */
		return apply_filters( 'hf_alter_subscription_data', $data, $csv_columns );
		// support for old customer.
	}//end get_subscriptions_csv_row()

	/**
	 * Get subscription row
	 *
	 * @param object $subscription Subscription.
	 * @return type
	 */
	public function get_wt_subscriptions_csv_row( $subscription ) {

		$csv_columns = $this->parent_module->get_selected_column_names();
		$row         = array();

		$fee_total = 0;
		$fee_tax_total = 0;
		$fee_items = array();
		$shipping_items = array();

		if ( 0 != count( array_intersect( array_keys( $csv_columns ), array( 'fee_total', 'fee_tax_total', 'fee_items' ) ) ) ) {
			foreach ( $subscription->get_fees() as $fee_id => $fee ) {
				$fee_items[] = implode(
					'|',
					array(
						'name:' . html_entity_decode( $fee['name'], ENT_NOQUOTES, 'UTF-8' ),
						'total:' . wc_format_decimal( $fee['line_total'], 2 ),
						'tax:' . wc_format_decimal( $fee['line_tax'], 2 ),
						'tax_class:' . $fee['tax_class'],
					)
				);

				$fee_total     += (float) $fee['line_total'];
				$fee_tax_total += (float) $fee['line_tax'];
			}
		}

		$line_items_shipping = $subscription->get_items( 'shipping' );
		foreach ( $line_items_shipping as $item_id => $item ) {
			if ( is_object( $item ) ) {
				$meta_data = $item->get_formatted_meta_data( '' );
				if ( $meta_data ) :
					foreach ( $meta_data as $meta_id => $meta ) :
						if ( in_array( $meta->key, $line_items_shipping ) ) {
							continue;
						}

						// html entity decode is not working preoperly.
						$shipping_items[] = implode( '|', array( 'item:' . wp_kses_post( $meta->display_key ), 'value:' . str_replace( '&times;', 'X', strip_tags( $meta->display_value ) ) ) );
					endforeach;
				endif;
			}
		}

		if ( ! function_exists( 'get_user_by' ) ) {
			include ABSPATH . 'wp-includes/pluggable.php';
		}

		$user_values = get_user_by( 'ID', ( WC()->version < '2.7' ) ? $subscription->customer_user : $subscription->get_customer_id() );

		// Preparing data for export.
		foreach ( $csv_columns as $header_key => $_ ) {
			switch ( $header_key ) {
				case 'subscription_id':
					$value = $subscription->get_id();
					break;
				case 'subscription_status':
					$value = $subscription->get_status();
					break;
				case 'customer_id':
					$value = is_object( $user_values ) ? $user_values->ID : '';
					break;
				case 'customer_username':
					$value = is_object( $user_values ) ? $user_values->user_login : '';
					break;
				case 'customer_email':
					$value = is_object( $user_values ) ? $user_values->user_email : '';
					break;
				case 'fee_total':
					$value = $fee_total;
					break;
				case 'fee_tax_total':
					$value = $fee_tax_total;
					break;
				case 'order_shipping':
					$value = ( WC()->version < '2.7' ) ? ( empty( $subscription->order_shipping ) ? 0 : $subscription->order_shipping ) : $subscription->get_total_shipping();
					break;
				case 'order_shipping_tax':
					$value = ( WC()->version < '2.7' ) ? ( empty( $subscription->order_shipping_tax ) ? 0 : $subscription->order_shipping_tax ) : $subscription->get_shipping_tax();
					break;
				case 'order_tax':
					$value = ( WC()->version < '2.7' ) ? ( empty( $subscription->order_tax ) ? 0 : $subscription->order_tax ) : $subscription->get_total_tax();
					break;
				case 'cart_discount':
					$value = ( WC()->version < '2.7' ) ? ( empty( $subscription->cart_discount ) ? 0 : $subscription->cart_discount ) : $subscription->get_total_discount();
					break;
				case 'cart_discount_tax':
					$value = ( WC()->version < '2.7' ) ? ( empty( $subscription->cart_discount_tax ) ? 0 : $subscription->cart_discount_tax ) : $subscription->get_discount_tax();
					break;
				case 'order_total':
					$value = empty( $subscription->get_total() ) ? 0 : $subscription->get_total();
					break;
				case 'date_created':
					$value = $subscription->get_date( 'date_created' );
					break;
				case 'trial_end_date':
					$value = $subscription->get_date( 'trial_end_date' );
					break;
				case 'next_payment_date':
					$value = $subscription->get_date( 'next_payment_date' );
					break;
				case 'last_order_date_created':
					$value = $subscription->get_date( 'last_order_date_created' );
					break;
				case 'end_date':
					$value = $subscription->get_date( 'end_date' );
					break;
				case 'order_currency':
					$value = ( WC()->version < '2.7' ) ? $subscription->order_currency : $subscription->get_currency();
					break;
				case 'billing_period':
				case 'billing_interval':
				case 'payment_method':
				case 'payment_method_title':
				case 'billing_first_name':
				case 'billing_last_name':
				case 'billing_email':
				case 'billing_phone':
				case 'billing_address_1':
				case 'billing_address_2':
				case 'billing_postcode':
				case 'billing_city':
				case 'billing_state':
				case 'billing_country':
				case 'billing_company':
				case 'shipping_first_name':
				case 'shipping_last_name':
				case 'shipping_address_1':
				case 'shipping_address_2':
				case 'shipping_postcode':
				case 'shipping_city':
				case 'shipping_state':
				case 'shipping_country':
				case 'shipping_company':
				case 'shipping_phone':
				case 'customer_note':
					$m_key = "get_$header_key";

					if ( method_exists( $subscription, $m_key ) ) {
						$value = $subscription->{$m_key}();
					} else {
						$value = $subscription->{$header_key};
					}
					break;
				case 'post_parent':
					$post  = get_post( $subscription->get_id() );
					$value = $post->post_parent;
					break;
				case 'order_notes':
					$order_notes = implode( '||', ( defined( 'WC_VERSION' ) && ( WC_VERSION >= 3.2 ) ) ? self::get_order_notes_new( $subscription ) : self::get_order_notes( $subscription ) );

					if ( ! empty( $order_notes ) ) {
						$value = $order_notes;
					} else {
						$value = '';
					}
					break;
				case 'renewal_orders':
					$renewal_orders = $subscription->get_related_orders( 'ids', 'renewal' );
					if ( ! empty( $renewal_orders ) ) {
						$value = implode( '|', $renewal_orders );
					} else {
						$value = '';
					}
					break;
				case 'order_items':
					$value      = '';
					$line_items = array();
					foreach ( $subscription->get_items() as $item_id => $item ) {
						$product = $item->get_product();
						if ( ! is_object( $product ) ) {
							$product = new WC_Product( 0 );
						}
						$item_meta = self::get_order_line_item_meta( $item_id );
						$prod_type = ( WC()->version < '3.0.0' ) ? $product->product_type : $product->get_type();
						$line_item = array(
							'product_id' => ( WC()->version < '2.7.0' ) ? $product->id : ( ( 'variable' == $prod_type || 'variation' == $prod_type || 'subscription_variation' == $prod_type ) ? $product->get_parent_id() : $product->get_id() ),
							'name'       => html_entity_decode( $item['name'], ENT_NOQUOTES, 'UTF-8' ),
							'sku'        => $product->get_sku(),
							'quantity'   => $item['qty'],
							'total'      => wc_format_decimal( $subscription->get_line_total( $item ), 2 ),
							'sub_total'  => wc_format_decimal( $subscription->get_line_subtotal( $item ), 2 ),
						);

						// add line item tax.
						$line_tax_data = isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : array();
						$tax_data      = maybe_unserialize( $line_tax_data );
						$tax_detail    = isset( $tax_data['total'] ) ? wc_format_decimal( wc_round_tax_total( array_sum( (array) $tax_data['total'] ) ), 2 ) : '';
						if ( '0.00' != $tax_detail && ! empty( $tax_detail ) ) {
							$line_item['tax'] = $tax_detail;
						}

						$line_tax_ser          = maybe_serialize( $line_tax_data );
						$line_item['tax_data'] = $line_tax_ser;

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

						if ( 'variable' == $prod_type || 'variation' == $prod_type || 'subscription_variation' == $prod_type ) {
							$line_item['_variation_id'] = ( WC()->version > '2.7' ) ? $product->get_id() : $product->variation_id;
						}

						foreach ( $line_item as $name => $value ) {
							$line_item[ $name ] = $name . ':' . $value;
						}

						$line_item = implode( '|', $line_item );

						if ( $line_item ) {
							$line_items[] = $line_item;
						}
					}//end foreach

					if ( ! empty( $line_items ) ) {
						$value = implode( '||', $line_items );
					}
					break;
				case 'coupon_items':
					$coupon_items = array();

					foreach ( $subscription->get_items( 'coupon' ) as $_ => $coupon_item ) {
						$coupon_name   = ( WC()->version < '4.3.9' ) ? $coupon_item['name'] : $coupon_item->get_name();
						$coupon_amount = ( WC()->version < '4.3.9' ) ? $coupon_item['discount_amount'] : $coupon_item->get_discount();

						$coupon         = new WC_Coupon( $coupon_name );
						$coupon_post    = get_post( ( ( WC()->version < '2.7' ) ? $coupon->id : $coupon->get_id() ) );
						$coupon_items[] = implode(
							'|',
							array(
								'code:' . $coupon_name,
								'description:' . ( is_object( $coupon_post ) ? $coupon_post->post_excerpt : '' ),
								'amount:' . wc_format_decimal( $coupon_amount, 2 ),
							)
						);
					}

					if ( ! empty( $coupon_items ) ) {
						$value = implode( ';', $coupon_items );
					} else {
						$value = '';
					}
					break;
				case 'download_permissions':
					$value = ( WC()->version < '2.7' ) ? ( $subscription->download_permissions_granted ? $subscription->download_permissions_granted : 0 ) : ( $subscription->is_download_permitted() );
					break;
				case 'shipping_method':
					$shipping_lines = array();
					foreach ( $subscription->get_shipping_methods() as $shipping_item_id => $shipping_item ) {
						$shipping_lines[] = implode(
							'|',
							array(
								'method_id:' . $shipping_item['method_id'],
								'method_title:' . $shipping_item['name'],
								'total:' . wc_format_decimal( $shipping_item['cost'], 2 ),
								'total_tax:' . wc_format_decimal( $shipping_item['total_tax'], 2 ),
								'taxes:' . maybe_serialize( $shipping_item['taxes'] ),
							)
						);
					}

					if ( ! empty( $shipping_lines ) ) {
						$value = implode( ';', $shipping_lines );
					} else {
						$value = '';
					}
					break;
				case 'fee_items':
					$value = implode( ';', $fee_items );
					break;
				case 'shipping_items':
					$value = implode( ';', $shipping_items );
					break;
				case 'tax_items':
					$tax_items = array();
					foreach ( $subscription->get_taxes() as $tax_code => $tax ) {
						$tax_items[] = implode(
							'|',
							array(
								'rate_id:' . $tax->get_rate_id(),
								'code:' . $tax->get_rate_code(),
								'total:' . wc_format_decimal( $tax->get_tax_total(), 2 ),
								'label:' . $tax->get_label(),
								'tax_rate_compound:' . $tax->get_compound(),
								'shipping_tax_amount:' . $tax->get_shipping_tax_total(),
								'rate_percent:' . $tax->get_rate_percent(),
							)
						);
					}

					if ( ! empty( $tax_items ) ) {
						$value = implode( ';', $tax_items );
					} else {
						$value = '';
					}
					break;
				default:
					if ( strstr( $header_key, 'meta:' ) ) {
						$value = maybe_serialize( $subscription->get_meta( str_replace( 'meta:', '', $header_key ), true ) );
					} else {
						$value = '';
					}
			}//end switch

			$csv_row[ $header_key ] = $value;
		}//end foreach
		$data = array();
		foreach ( $csv_columns as $header_key => $_ ) {
			// Strict string comparison, as values like '0' are valid.
			$value = ( '' !== $csv_row[ $header_key ] ) ? $csv_row[ $header_key ] : '';
			$data[ $header_key ] = $value;
		}
		if ( $this->export_to_separate_columns ) {
			$line_item_values     = self::get_all_metakeys_and_values( $subscription );
			$this->line_item_meta = self::get_all_line_item_metakeys();
			$count = 1;
			foreach ( $subscription->get_items() as $item_id => $item ) {
				$product = $item->get_product();
				if ( ! is_object( $product ) ) {
					$product = new WC_Product( 0 );
				}
				$item_meta = self::get_order_line_item_meta( $item_id );
				$prod_type = ( WC()->version < '3.0.0' ) ? $product->product_type : $product->get_type();
				$is_varaition = ( 'variable' == $prod_type || 'variation' == $prod_type || 'subscription_variation' == $prod_type ) ? true : false;
				$data[ "line_item_{$count}_product_id" ] = ( WC()->version < '2.7.0' ) ? $product->id : ( ( 'variable' == $prod_type || 'variation' == $prod_type || 'subscription_variation' == $prod_type ) ? $product->get_parent_id() : $product->get_id() );
				$data[ "line_item_{$count}_variation_id" ] = $is_varaition ? ( ( WC()->version > '2.7' ) ? $product->get_id() : $product->get_variation_id() ) : '';
				$data[ "line_item_{$count}_name" ]        = html_entity_decode( $item['name'], ENT_NOQUOTES, 'UTF-8' );
				$data[ "line_item_{$count}_sku" ]     = $product->get_sku();
				$data[ "line_item_{$count}_quantity" ]    = $item['qty'];
				$data[ "line_item_{$count}_total" ]         = wc_format_decimal( $subscription->get_line_total( $item ), 2 );
				$data[ "line_item_{$count}_subtotal" ]  = wc_format_decimal( $subscription->get_line_subtotal( $item ), 2 );
				foreach ( $this->line_item_meta as $key ) {
					switch ( $key ) {
						case '_qty':
						case '_variation_id':
						case '_product_id':
						case '_line_total':
						case '_line_subtotal':
							break;

						default:
							if ( isset( $item_meta[ $key ] ) ) {
								if ( is_object( $item_meta[ $key ] ) ) {
									$value = $item_meta[ $key ]->meta_value;
								}

								if ( is_array( $item_meta[ $key ] ) ) {
									$value = implode( ',', $item_meta[ $key ] );
								}
							} else {
								$value = '';
							}

							$data[ "line_item_{$count}_" . $key ] = $value;
							break;
					}//end switch
				}//end foreach
				++$count;
			}
		}//end if
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array     $data  Subscription data.
		 * @param array     $csv_columns   CSV columns.
		 */
		return apply_filters( 'hf_alter_subscription_data', $data, $csv_columns );
	}//end get_wt_subscriptions_csv_row()

	/**
	 * Safe subscription status
	 *
	 * @param string $status_key Subscription status.
	 * @return string
	 */
	public static function hf_sanitize_subscription_status_keys( $status_key ) {
		if ( ! is_string( $status_key ) || empty( $status_key ) ) {
			return '';
		}

		$status_key = ( 'wc-' === substr( $status_key, 0, 3 ) ) ? $status_key : sprintf( 'wc-%s', $status_key );
		return $status_key;
	}//end hf_sanitize_subscription_status_keys()

	/**
	 * Line item meta
	 *
	 * @global type $wpdb
	 * @param integer $item_id Line item id.
	 * @return type
	 */
	public static function get_order_line_item_meta( $item_id ) {
		global $wpdb;
		$filter_meta = array( '_product_id', '_qty', '_variation_id', '_line_total', '_line_subtotal' );
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array     $line_item_meta  Subscription item metadata.
		 */
		$filter_meta = apply_filters( 'wt_subscription_export_select_line_item_meta', $filter_meta );
		$filter_meta_keys = $filter_meta;
		array_unshift( $filter_meta, $item_id );
		$meta_keys = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key,meta_value
			FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = %d AND meta_key IN (" . implode( ',', array_fill( 0, count( $filter_meta_keys ), '%s' ) ) . ')',
				$filter_meta
			),
			OBJECT_K
		);
		return $meta_keys;
	}//end get_order_line_item_meta()

	/**
	 * Subscription of coupons
	 *
	 * @global type $wpdb
	 * @param array  $coupons Coupons.
	 * @param string $subscription_type Type of sub.
	 * @return array
	 */
	public static function wt_get_subscription_of_coupons( $coupons, $subscription_type = 'shop_subscription' ) {
		 global $wpdb;

		if ( is_array( $coupons ) ) {
			$where_coupon = implode( ',', $coupons );
		} else {
			$where_coupon = $coupons;
		}
		$subscription_ids = array();
		if ( self::$is_hpos_enabled ) {

			$order_id = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT po.id FROM  {$wpdb->prefix}wc_orders AS po
            LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON oi.order_id = po.id
            WHERE po.type = %s
            AND oi.order_item_type = 'coupon'
            AND oi.order_item_name IN (%s)",
					'shop_order',
					$where_coupon
				)
			); // @codingStandardsIgnoreLine.
			if ( ! empty( $order_id ) ) {
				$order_id[] = $subscription_type;
				$subscription_ids = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT DISTINCT id FROM {$wpdb->prefix}wc_orders WHERE parent_order_id IN ( " . implode( ',', array_fill( 0, count( $order_id ) - 1, '%s' ) ) . ' ) AND type=%s',
						$order_id
					)
				);
			}
		} else {
			$order_id = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT po.id FROM {$wpdb->posts} AS po
            LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON oi.order_id = po.ID
            WHERE po.post_type = %s
            AND oi.order_item_type = 'coupon'
            AND oi.order_item_name IN (%s)",
					'shop_order',
					$where_coupon
				)
			); // @codingStandardsIgnoreLine.

			if ( ! empty( $order_id ) ) {
				$order_id[] = $subscription_type;
				$subscription_ids = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT ID FROM {$wpdb->posts} WHERE post_parent IN ( " . implode( ',', array_fill( 0, count( $order_id ) - 1, '%s' ) ) . ' ) AND post_type=%s', $order_id, $subscription_type ) );
			}
		}
		return $subscription_ids;
	}//end wt_get_subscription_of_coupons()

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
				'|',
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
	 * Get maximum number of line item for an order in the database.
	 *
	 * @param array $subscription_post_ids Subscription ids.
	 *
	 * @return int Max line item number.
	 */
	public static function get_max_line_items( $subscription_post_ids ) {
		global $wpdb;
		if ( ! empty( $subscription_post_ids ) ) {
			$line_item_keys = $wpdb->get_col( $wpdb->prepare( "SELECT COUNT(p.order_id) AS ttal FROM {$wpdb->prefix}woocommerce_order_items AS p WHERE order_item_type ='line_item' AND p.order_id IN ( " . implode( ',', array_fill( 0, count( $subscription_post_ids ), '%s' ) ) . ' ) GROUP BY p.order_id ORDER BY ttal DESC LIMIT 1', $subscription_post_ids ) );
			$max_line_items = $line_item_keys[0];
		} else {
			$max_line_items = 0;
		}
		return $max_line_items;
	}//end get_max_line_items()

	/**
	 * Line item keys
	 *
	 * @global object $wpdb
	 * @return type
	 */
	public static function get_all_line_item_metakeys() {
		global $wpdb;
		$filter_meta = array( '_product_id', '_qty', '_variation_id', '_line_total', '_line_subtotal' );
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array   $item_meta    Import columns.
		 */
		$filter_meta = apply_filters( 'wt_subscription_export_select_line_item_meta', $filter_meta );
		$meta_keys = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT om.meta_key
            FROM {$wpdb->prefix}woocommerce_order_itemmeta AS om 
            INNER JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON om.order_item_id = oi.order_item_id
            WHERE oi.order_item_type = 'line_item' AND om.meta_key IN ( " . implode( ',', array_fill( 0, count( $filter_meta ), '%s' ) ) . ' )',
				$filter_meta
			)
		);// @codingStandardsIgnoreLine.
		return $meta_keys;
	}//end get_all_line_item_metakeys()

	/**
	 * Get all metakeys and values
	 *
	 * @param object $order Order.
	 * @return type
	 */
	public static function get_all_metakeys_and_values( $order = null ) {
		$in = 1;
		foreach ( $order->get_items() as $item_id => $item ) {
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
				'|',
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
}//end class
