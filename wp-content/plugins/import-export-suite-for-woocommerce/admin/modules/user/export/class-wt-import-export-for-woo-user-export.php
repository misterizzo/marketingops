<?php
/**
 * Handles the user export.
 *
 * @package   ImportExportSuite\Admin\Modules\User\Export
 * @version   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wt_Import_Export_For_Woo_User_Export Class.
 */
class Wt_Import_Export_For_Woo_User_Export {

	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $parent_module = null;

	/**
	 * Constructor.
	 *
	 * @param object $parent_object Parent module object.
	 * @since 1.0.0
	 */
	public function __construct( $parent_object ) {

		$this->parent_module = $parent_object;
	}//end __construct()


	/**
	 * Prepare CSV header
	 *
	 * @return type
	 */
	public function prepare_header() {

		$export_columns = $this->parent_module->get_selected_column_names();
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array   $export_columns    Export columns.
		 */
		return apply_filters( 'wt_ier_csv_customer_post_columns', $export_columns );
	}//end prepare_header()


	/**
	 * Prepare data that will be exported.
	 *
	 * @param array   $form_data Form data.
	 * @param integer $batch_offset Offset.
	 * @return type
	 */
	public function prepare_data_to_export( $form_data, $batch_offset ) {
		$export_user_roles = ! empty( $form_data['filter_form_data']['wt_iew_roles'] ) ? $form_data['filter_form_data']['wt_iew_roles'] : array();
		$export_sortby = ! empty( $form_data['filter_form_data']['wt_iew_sort_columns'] ) ? $form_data['filter_form_data']['wt_iew_sort_columns'] : array( 'user_login' );
		$export_sort_order = ! empty( $form_data['filter_form_data']['wt_iew_order_by'] ) ? $form_data['filter_form_data']['wt_iew_order_by'] : 'ASC';
		$user_ids = ! empty( $form_data['filter_form_data']['wt_iew_email'] ) ? $form_data['filter_form_data']['wt_iew_email'] : array();
		$export_start_date = ! empty( $form_data['filter_form_data']['wt_iew_date_from'] ) ? $form_data['filter_form_data']['wt_iew_date_from'] : '';
		$export_end_date = ! empty( $form_data['filter_form_data']['wt_iew_date_to'] ) ? $form_data['filter_form_data']['wt_iew_date_to'] : '';

		$v_export_guest_user = ( ! empty( $form_data['advanced_form_data']['wt_iew_export_guest_user'] ) && 'Yes' == $form_data['advanced_form_data']['wt_iew_export_guest_user'] ) ? true : false;

		$export_limit = ! empty( $form_data['filter_form_data']['wt_iew_limit'] ) ? intval( $form_data['filter_form_data']['wt_iew_limit'] ) : 999999999;
		// user limit.
		$current_offset = ! empty( $form_data['filter_form_data']['wt_iew_offset'] ) ? intval( $form_data['filter_form_data']['wt_iew_offset'] ) : 0;
		// user offset.
		$batch_count = ! empty( $form_data['advanced_form_data']['wt_iew_batch_count'] ) ? $form_data['advanced_form_data']['wt_iew_batch_count'] : Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings( 'default_export_batch' );

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
			$sortby_check = array_intersect( $export_sortby, array( 'ID', 'user_registered', 'user_email', 'user_login', 'user_nicename' ) );
			if ( empty( $sortby_check ) ) {
				$wt_export_sortby = $export_sortby[0];
				$args = array(
					'fields' => 'ID',
					// exclude standard wp_users fields from get_users query -> get Only ID##.
					'role__in' => $export_user_roles,
					// An array of role names. Matched users must have at least one of these roles. Default empty array.
					'number' => $limit,
					'offset' => $real_offset,
					'orderby' => 'meta_value',
					'meta_key' => $wt_export_sortby,
					'order' => $export_sort_order,
					'date_query' => array(
						array(
							'after' => $export_start_date,
							'before' => $export_end_date,
							'inclusive' => true,
						),
					),
				);
			} else {
				$args = array(
					'fields' => 'ID',
					// exclude standard wp_users fields from get_users query -> get Only ID##.
					'role__in' => $export_user_roles,
					// An array of role names. Matched users must have at least one of these roles. Default empty array.
					'number' => $limit,
					'offset' => $real_offset,
					'orderby' => $export_sortby,
					'order' => $export_sort_order,
					'date_query' => array(
						array(
							'after' => $export_start_date,
							'before' => $export_end_date,
							'inclusive' => true,
						),
					),
				);
			}//end if
			// https://developer.wordpress.org/reference/hooks/update_meta_type_metadata_cache/ .
			// add_filter( 'update_user_metadata_cache', '__return_true' ); //Short-circuits updating the metadata cache.
			if ( ! empty( $user_ids ) ) {
				if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
					$ids = array();
					$user_emails = array_map( 'trim', explode( ',', $user_ids ) );
					if ( ! empty( $user_emails ) && is_array( $user_emails ) ) {
						foreach ( $user_emails as $email ) {
							$user = get_user_by( 'email', $email );
							if ( $user ) {
								$ids[] = $user->ID;
							}
						}

						$user_ids = $ids;
					}
				}

				$args['include'] = $user_ids;
			}
			/**
			* Filter the query arguments for filter the user.
			* Enables adding extra arguments .
			*
			* @since 1.2.7
			*
			* @param array   $args    Argument.
			* @param array  $form_data Form data.
			*/
			$args = apply_filters( 'wt_iew_user_export_args', $args, $form_data );

			$users = get_users( $args );

			/*
			 *   taking total records
			 */
			$total_records = 0;
			if ( 0 == $batch_offset ) {
				// first batch.
				$total_item_args = $args;
				$total_item_args['fields'] = 'ids';
				$total_item_args['number'] = $export_limit;
				// user given limit.
				$total_item_args['offset'] = $current_offset;
				// user given offset.
				$total_record_count = get_users( $total_item_args );

				if ( $v_export_guest_user ) {
					delete_option( 'wt_export_guest_user' );
					delete_option( 'wt_get_processed_guest_user_array' );
					$query_args = array(
						'fields' => 'ids',
						'post_type' => 'shop_order',
						'post_status' => 'any',
						'posts_per_page' => -1,
					);
					$query_args['meta_query'] = array(
						array(
							'key' => '_customer_user',
							'value' => 0,
							'compare' => '',
						),
					);
					$query = new WP_Query( $query_args );
					$order_ids = $query->posts;

					$guest_orders_ids = wc_get_orders(
						array(
							'type' => 'shop_order',
							'customer_id' => 0,
							'return' => 'ids',
						)
					);

					$order_ids = array_merge( $order_ids, $guest_orders_ids );

					add_option( 'wt_export_guest_user', $order_ids );
					$total_records = count( $total_record_count ) + count( $order_ids );
					set_transient( 'wt_pro_total_user_count', $total_records, 60 * 60 * 1 ); // 1 hour
					set_transient( 'wt_pro_total_real_user_count', count( $total_record_count ), 60 * 60 * 1 );
				} else {
					$total_records = count( $total_record_count );
					set_transient( 'wt_pro_total_user_count', $total_records, 60 * 60 * 1 ); // 1 hour
				}
			}

			// Loop users.
			foreach ( $users as $user ) {
				$data = self::get_customers_csv_row( $user );
				/**
				 * Filter the query arguments for a request - exclude admin.
				 *
				 * Enables adding extra arguments or setting defaults for the request.
				 *
				 * @since 1.0.0
				 *
				 * @param array   $data    Export users.
				 */
				$data_array[] = apply_filters( 'wt_ier_customer_csv_exclude_admin', $data );
			}

			$is_guest_offset = false;
			$last_batch_count = $real_offset + $batch_count;

			if ( ! $v_export_guest_user ) {
				if ( $last_batch_count >= get_transient( 'wt_pro_total_user_count' ) ) {
					delete_transient( 'wt_pro_total_user_count' );
				}
			} elseif ( $last_batch_count >= get_transient( 'wt_pro_total_real_user_count' ) ) {
					$is_guest_offset = true;
			}

			if ( $v_export_guest_user && $is_guest_offset ) {

				$order_ids = get_option( 'wt_export_guest_user', array() );
				$start = 0;
				$sliced_order_ids = array_slice( $order_ids, $start, $batch_count );
				$order_ids = array_diff( $order_ids, $sliced_order_ids );
				$guest_email_list = get_option( 'wt_get_processed_guest_user_array', array() );
				foreach ( $sliced_order_ids as $order_id ) {
					$order = new WC_Order( $order_id );
					$user = get_user_by( 'email', $order->get_billing_email() );
					if ( ! isset( $user->ID ) && ! empty( $order->get_billing_email() ) ) {
						/**
											 * Filter the query arguments for a request - exclude duplicate guest users.
											 *
											 * Enables adding extra arguments or setting defaults for the request.
											 *
											 * @since 1.0.7
											 *
											 * @param bool   $data   Allow.
											 */
						if ( ! apply_filters( 'wt_ier_keep_duplicate_guest_users', false ) ) {
							if ( ! in_array( $order->get_billing_email(), $guest_email_list ) ) {
								$data = self::get_guest_customers_csv_row( $order );
								/**
								* Filter the query arguments for a request - exclude admin.
								*
								* Enables adding extra arguments or setting defaults for the request.
								*
								* @since 1.0.7
								*
								* @param array   $data    Export users.
								*/
								$data_array[] = apply_filters( 'wt_ier_customer_csv_exclude_admin', $data );
								$guest_email_list[] = $order->get_billing_email();
							}
						} else {
							$data = self::get_guest_customers_csv_row( $order );
							/**
							 * Filter the query arguments for a request - exclude admin.
							 *
							 * Enables adding extra arguments or setting defaults for the request.
							 *
							 * @since 1.0.7
							 *
							 * @param array   $data    Export users.
							 */
							$data_array[] = apply_filters( 'wt_ier_customer_csv_exclude_admin', $data );
						}
					}
				}
				$order_ids = array_values( array_filter( $order_ids ) );
				/**
				 * Filter the query arguments for a request - keep duplicate guest users.
				 *
				 * Enables adding extra arguments or setting defaults for the request.
				 *
				 * @since 1.0.7
				 *
				 * @param bool   $data   Allow.
				 */
				if ( ! apply_filters( 'wt_ier_keep_duplicate_guest_users', true ) ) {
					update_option( 'wt_get_processed_guest_user_array', $guest_email_list );
				}
				update_option( 'wt_export_guest_user', $order_ids );
				if ( $last_batch_count >= get_transient( 'wt_pro_total_user_count' ) ) {  // finished.
					delete_transient( 'wt_pro_total_user_count' );
					delete_transient( 'wt_pro_total_real_user_count' );
					delete_option( 'wt_export_guest_user' );
					delete_option( 'wt_get_processed_guest_user_array' );
				}
			}//end if

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
	 * Generate CSV row
	 *
	 * @param inetger $id User id.
	 * @return type
	 */
	public function get_customers_csv_row( $id ) {
		global $wpdb;
		$csv_columns = $this->parent_module->get_selected_column_names();

		$user = get_user_by( 'id', $id );

		$customer_data = array();
		foreach ( $csv_columns as $key => $value ) {
			$key = trim( str_replace( 'meta:', '', $key ) );
			if ( 'roles' == $key ) {
				$user_roles = ( ! empty( $user->roles ) ) ? $user->roles : array();
				$customer_data['roles'] = implode( ', ', $user_roles );
				continue;
			}

			if ( 'customer_id' == $key ) {
				$customer_data[ $key ] = ! empty( $user->ID ) ? maybe_serialize( $user->ID ) : '';
				continue;
			}

			if ( 'session_tokens' == $key ) {
				$customer_data[ $key ] = ! empty( $user->{$key} ) ? base64_encode( json_encode( maybe_unserialize( $user->{$key} ) ) ) : '';
				continue;
			}

			if ( $key == $wpdb->prefix . 'user_level' ) {
				$customer_data[ $key ] = ( ! empty( $user->{$key} ) ) ? $user->{$key} : 0;
				continue;
			}

			if ( 'total_spent' === $key ) {
					$customer_data[ $key ] = ! empty( $user->ID ) ? wc_get_customer_total_spent( $user->ID ) : '';
					continue;
			}
			if ( 'last_update' == $key ) {
				$date_in_timestamp = ( ! empty( $user->{$key} ) ) ? $user->{$key} : 0;
				if ( 0 == $date_in_timestamp ) {
					$customer_data[ $key ] = '';
				} elseif ( false == strtotime( $date_in_timestamp ) ) {
					$customer_data[ $key ] = gmdate( 'Y-m-d H:i:s', $date_in_timestamp );
				} else {
					$customer_data[ $key ] = $date_in_timestamp;
				}
				continue;
			}
			if ( 'wc_last_active' == $key ) {
				$date_in_timestamp = ( ! empty( $user->{$key} ) ) ? $user->{$key} : 0;
				if ( 0 == $date_in_timestamp ) {
					$customer_data[ $key ] = '';
				} elseif ( false == strtotime( $date_in_timestamp ) ) {
					$customer_data[ $key ] = gmdate( 'Y-m-d', $date_in_timestamp );
				} else {
					$customer_data[ $key ] = $date_in_timestamp;
				}
				continue;
			}
			if ( 'is_geuest_user' == $key ) {
				$customer_data[ $key ] = 0;
				continue;
			}

			$customer_data[ $key ] = isset( $user->{$key} ) ? maybe_serialize( $user->{$key} ) : '';
		}//end foreach

		/**
		 * CSV Customer Export Row.
		 * Filter the individual row data for the customer export.
		 *
		 * @since 1.0.0
		 *
		 * @param array $customer_data Users details.
		 * @param array $csv_columns CSV columns.
		 */
		return apply_filters( 'wt_ier_customer_csv_export_data', $customer_data, $csv_columns );
	}//end get_customers_csv_row()


	/**
	 * Guest user details
	 *
	 * @param object $order Order.
	 * @return type
	 */
	public function get_guest_customers_csv_row( $order ) {
		$customer_data = array();
		$csv_columns = $this->parent_module->get_selected_column_names();
		$key_array = array(
			'user_email',
			'billing_first_name',
			'billing_last_name',
			'billing_company',
			'billing_email',
			'billing_phone',
			'billing_address_1',
			'billing_address_2',
			'billing_postcode',
			'billing_city',
			'billing_state',
			'billing_country',
			'shipping_first_name',
			'shipping_last_name',
			'shipping_company',
			'shipping_phone',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_postcode',
			'shipping_city',
			'shipping_state',
			'shipping_country',
			'shipping_method',
			'is_geuest_user',
			'roles',
		);
		foreach ( $csv_columns as $key ) {
			if ( in_array( $key, $key_array ) ) {
				if ( 'user_email' == $key ) {
					$customer_data[ $key ] = $order->get_billing_email();
					continue;
				}

				if ( 'is_geuest_user' == $key ) {
					$customer_data['is_geuest_user'] = 1;
					continue;
				}

				if ( 'roles' == $key ) {
					$customer_data['role'] = 'customer';
					continue;
				}

				$method_name = "get_{$key}";
				if ( method_exists( $order, $method_name ) ) {
					$data = $order->$method_name();
				}

				if ( ! empty( $data ) ) {
					$data = maybe_serialize( $data );
				} else {
					$data = '';
				}

				$customer_data[ $key ] = $data;
			} else {
				$customer_data[ $key ] = '';
			}//end if
		}//end foreach

		/**
		 * CSV Guest Customer Export Row.
		 * Filter the individual row data for the Guest customer export.
		 *
		 * @since 1.0.0
		 *
		 * @param array $customer_data Users details.
		 * @param array $csv_columns CSV columns.
		 */
		return apply_filters( 'wt_ier_customer_csv_export_data', $customer_data, $csv_columns );
	}//end get_guest_customers_csv_row()
}//end class
