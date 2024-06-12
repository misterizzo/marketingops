<?php
/**
 * Handles the coupon export.
 *
 * @package   ImportExportSuite\Admin\Modules\Coupon\Export
 * @version   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wt_Import_Export_For_Woo_Coupon_Export Class.
 */
class Wt_Import_Export_For_Woo_Coupon_Export {

	/**
	 * Parent module
	 *
	 * @var object
	 */
	public $parent_module = null;

	/**
	 * Constructor.
	 *
	 * @param object $parent_object parent module object.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $parent_object ) {

		$this->parent_module = $parent_object;
	}//end __construct()

	/**
	 * Prepare column headers in CSV format.
	 *
	 * @since 1.0.0
	 * @return array
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
		return apply_filters( 'hf_alter_coupon_csv_header', $export_columns );
	}//end prepare_header()

	/**
	 * Prepare data that will be exported.
	 *
	 * @param array   $form_data   submitted form data.
	 * @param integer $batch_offset  export batch offset.
	 *
	 * @since 1.0.0
	 */
	public function prepare_data_to_export( $form_data, $batch_offset ) {

		$export_statuses = ! empty( $form_data['filter_form_data']['wt_iew_statuses'] ) ? $form_data['filter_form_data']['wt_iew_statuses'] : array(
			'publish',
			'pending',
			'private',
			'draft',
			'future',
		);
		$cpn_categories = ! empty( $form_data['filter_form_data']['wt_iew_types'] ) ? $form_data['filter_form_data']['wt_iew_types'] : array_keys( wc_get_coupon_types() );
		$coupon_amount_from = ! empty( $form_data['filter_form_data']['wt_iew_coupon_amount_from'] ) ? $form_data['filter_form_data']['wt_iew_coupon_amount_from'] : 0;
		$coupon_amount_to = ! empty( $form_data['filter_form_data']['wt_iew_coupon_amount_to'] ) ? $form_data['filter_form_data']['wt_iew_coupon_amount_to'] : 0;
		$coupon_exp_date_from = ! empty( $form_data['filter_form_data']['wt_iew_coupon_exp_date_from'] ) ? $form_data['filter_form_data']['wt_iew_coupon_exp_date_from'] : '0000-00-00';
		$coupon_exp_date_to = ! empty( $form_data['filter_form_data']['wt_iew_coupon_exp_date_to'] ) ? $form_data['filter_form_data']['wt_iew_coupon_exp_date_to'] : '0000-00-00';

		$export_sortby = ! empty( $form_data['filter_form_data']['wt_iew_sort_columns'] ) ? implode( ' ', $form_data['filter_form_data']['wt_iew_sort_columns'] ) : 'ID';
		// get_post accept spaced string.
		$export_sort_order = ! empty( $form_data['filter_form_data']['wt_iew_order_by'] ) ? $form_data['filter_form_data']['wt_iew_order_by'] : 'ASC';

		$export_limit = ! empty( $form_data['filter_form_data']['wt_iew_limit'] ) ? intval( $form_data['filter_form_data']['wt_iew_limit'] ) : 999999999;
		// user limit.
		$current_offset = ! empty( $form_data['filter_form_data']['wt_iew_offset'] ) ? intval( $form_data['filter_form_data']['wt_iew_offset'] ) : 0;
		// user offset.
		$batch_count = ! empty( $form_data['advanced_form_data']['wt_iew_batch_count'] ) ? $form_data['advanced_form_data']['wt_iew_batch_count'] : Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings( 'default_export_batch' );

		$this->export_shortcodes = ( ! empty( $form_data['advanced_form_data']['wt_iew_export_shortcode_tohtml'] ) && 'Yes' == $form_data['advanced_form_data']['wt_iew_export_shortcode_tohtml'] ) ? true : false;

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
			$coupon_args = array(
				'post_status' => $export_statuses,
				'post_type' => 'shop_coupon',
				'order' => $export_sort_order,
				'orderby' => $export_sortby,
			);

			if ( ! empty( $cpn_categories ) ) {
				$coupon_args['meta_query'] = array(
					array(
						'key' => 'discount_type',
						'value' => $cpn_categories,
						'compare' => 'IN',
					),
				);
			}

			if ( 0 != $coupon_amount_from && 0 == $coupon_amount_to ) {
				$coupon_args['meta_query'] = array(
					array(
						'key' => 'coupon_amount',
						'value' => $coupon_amount_from,
						'compare' => '>=',
						'type' => 'NUMERIC',
					),
				);
			}

			if ( 0 != $coupon_amount_to && 0 == $coupon_amount_from ) {
				$coupon_args['meta_query'] = array(
					array(
						'key' => 'coupon_amount',
						'value' => $coupon_amount_to,
						'compare' => '<=',
						'type' => 'NUMERIC',
					),
				);
			}

			if ( 0 != $coupon_amount_to && 0 != $coupon_amount_from ) {
				$coupon_args['meta_query'] = array(
					array(
						'key' => 'coupon_amount',
						'value' => array(
							$coupon_amount_from,
							$coupon_amount_to,
						),
						'compare' => 'BETWEEN',
						'type' => 'NUMERIC',
					),
				);
			}

			if ( '0000-00-00' != $coupon_exp_date_from && '0000-00-00' == $coupon_exp_date_to ) {
				$coupon_args['meta_query'] = array(
					array(
						'key' => 'date_expires',
						'value' => strtotime( $coupon_exp_date_from ),
						'compare' => '>=',
					// 'type' => 'NUMERIC'
					),
				);
			}

			if ( '0000-00-00' != $coupon_exp_date_to && '0000-00-00' == $coupon_exp_date_from ) {
				$coupon_args['meta_query'] = array(
					array(
						'key' => 'date_expires',
						'value' => strtotime( $coupon_exp_date_to ),
						'compare' => '<=',
					// 'type' => 'NUMERIC'
					),
				);
			}

			if ( '0000-00-00' != $coupon_exp_date_to && '0000-00-00' != $coupon_exp_date_from ) {
				$coupon_args['meta_query'] = array(
					array(
						'key' => 'date_expires',
						'value' => array(
							strtotime( $coupon_exp_date_from ),
							strtotime( $coupon_exp_date_to ),
						),
						'compare' => 'BETWEEN',
					// 'type' => 'NUMERIC'
					),
				);
			}

			if ( ! empty( $selected_coupon_ids ) ) {
				$coupon_args['meta_query'] = array();
				$coupon_args['post__in'] = $selected_coupon_ids;
			}
			/**
			* Filter the query arguments for a request.
			*
			* Enables adding extra arguments or setting defaults for the request.
			*
			* @since 1.0.0
			*
			* @param array   $coupon_args    Query parameters.
			*/
			$coupon_args = apply_filters( 'coupon_csv_product_export_args', $coupon_args );
			$coupon_args['offset'] = $real_offset;
			$coupon_args['posts_per_page'] = $limit;

			$coupons = get_posts( $coupon_args );

			foreach ( $coupons as $coupon ) {
				if ( ! $coupons || is_wp_error( $coupons ) ) {
					break;
				}

				$data_array[] = $this->generate_row_data( $coupon );
			}

			/*
			 *   taking total records
			 */
			$total_records = 0;
			if ( 0 == $batch_offset ) {
				// first batch.
				$total_item_args = $coupon_args;
				$total_item_args['fields'] = 'ids';
				$total_item_args['posts_per_page'] = $export_limit;
				// user given limit.
				$total_item_args['offset'] = $current_offset;
				// user given offset.
				$coupons = get_posts( $total_item_args );
				$total_records = count( $coupons );
			}

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
	 * Take a coupon and generate row data from it for export.
	 *
	 * @param WC_Post $coupon WC_Post object.
	 *
	 * @return array
	 */
	public function generate_row_data( $coupon ) {
		$csv_columns = $this->parent_module->get_selected_column_names();
		$row = array();

		foreach ( $csv_columns as $column => $value ) {
			if ( isset( $coupon->$column ) ) {
				if ( is_array( $coupon->$column ) ) {
					$coupon->$column = implode( ',', $coupon->$column );
				}

				if ( 'product_ids' == $column ) {
					$hf_val = $coupon->$column;
					$sku = self::get_sku_from_id( $hf_val );
					$row[ $column ] = str_replace( ',', '|', $hf_val );
					continue;
				}

				if ( 'exclude_product_ids' == $column ) {
					$ex_val = $coupon->$column;
					$exsku = self::get_sku_from_id( $ex_val );
					$row[ $column ] = str_replace( ',', '|', $ex_val );
					continue;
				}

				if ( 'product_categories' == $column || 'exclude_product_categories' == $column ) {
					$cpn_product_category_ids = explode( ',', $coupon->$column );
					$cpn_product_category_name = array();
					foreach ( $cpn_product_category_ids as $cpn_product_category_id ) {
						$cpn_product_category_name[] = get_term( $cpn_product_category_id )->name;
					}

					$row[ $column ] = implode( ',', $cpn_product_category_name );
					continue;
				}

				if ( 'date_expires' == $column && ! empty( $coupon->$column ) ) {
					$row[ $column ] = gmdate( 'Y-m-d', $coupon->$column );
					continue;
				}

				$row[ $column ] = $coupon->$column;
				continue;
			} elseif ( isset( $coupon->$column ) && ! is_array( $coupon->$column ) ) {
				if ( 'post_title' === $column ) {
					$row[ $column ] = sanitize_text_field( $coupon->$column );
				} else {
					$row[ $column ] = $coupon->$column;
				}

				continue;
			} elseif ( 'product_SKUs' === $column ) {
				$row[ $column ] = ! empty( $sku ) ? $sku : '';
				unset( $sku );
				continue;
			} elseif ( 'exclude_product_SKUs' === $column ) {
				$row[ $column ] = ! empty( $exsku ) ? $exsku : '';
				unset( $exsku );
				continue;
			}//end if

			if ( $this->export_shortcodes && ( 'post_content' == $column || 'post_excerpt' == $column ) ) {
				// Convert Shortcodes to html for Description and Short Description.
				$row[ $column ] = do_shortcode( $coupon->$column );
				continue;
			}

			// Export meta data.
			if ( 'meta:' == substr( $column, 0, 5 ) ) {
				$meta = substr( $column, 5 );
				if ( isset( $coupon->$meta ) ) {
					$row[ $column ] = maybe_serialize( $coupon->$meta );
				} else {
					$row[ $column ] = '';
				}

				continue;
			}

			$row[ $column ] = '';
		}//end foreach
		/**
		* Filter the query arguments for a request.
		*
		* Enables adding extra arguments or setting defaults for the request.
		*
		* @since 1.0.0
		*
		* @param array   $row    Retrieved coupons.
		* @param array   $csv_columns    CSV columns.
		*/
		return apply_filters( 'hf_alter_coupon_csv_data', $row, $csv_columns );
	}//end generate_row_data()

	/**
	 * Get product SKU by product ID in WooCommerce.
	 *
	 * @param integer $val Product ID.
	 * @return string
	 */
	public static function get_sku_from_id( $val ) {
		$pro_id = explode( ',', $val );
		$sku_arr = array();
		if ( $pro_id ) {
			foreach ( $pro_id as $value ) {
				$product_exist = get_post_type( $value );
				if ( 'product' == $product_exist || 'product_variation' == $product_exist ) {
					$psku = get_post_meta( $value, '_sku', true );
					if ( ! empty( $psku ) ) {
						$sku_arr[] = $psku;
					}
				}
			}
		}

		$new_sku = implode( '|', $sku_arr );
		return $new_sku;
	}//end get_sku_from_id()
}//end class
