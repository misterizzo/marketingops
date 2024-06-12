<?php
/**
 * Handles the product export.
 *
 * @package   ImportExportSuite\Admin\Modules\Product\Export
 * @version   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_Product_Export Class.
 */
class Wt_Import_Export_For_Woo_Product_Export {
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
	}
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
		return apply_filters( 'hf_alter_product_export_csv_columns', $export_columns );
	}

	/**
	 * Prepare data that will be exported.
	 *
	 * @param array   $form_data Form data.
	 * @param integer $batch_offset Offset.
	 * @param string  $step Step.
	 * @return type
	 */
	public function prepare_data_to_export( $form_data, $batch_offset, $step ) {

		$include_products = ! empty( $form_data['filter_form_data']['wt_iew_product'] ) ? $form_data['filter_form_data']['wt_iew_product'] : '';
		$exclude_products = ! empty( $form_data['filter_form_data']['wt_iew_exclude_product'] ) ? $form_data['filter_form_data']['wt_iew_exclude_product'] : '';

		$prod_categories = ! empty( $form_data['filter_form_data']['wt_iew_product_categories'] ) ? $form_data['filter_form_data']['wt_iew_product_categories'] : array();
		$prod_tags = ! empty( $form_data['filter_form_data']['wt_iew_product_tags'] ) ? $form_data['filter_form_data']['wt_iew_product_tags'] : array();
		$prod_types = ! empty( $form_data['filter_form_data']['wt_iew_product_types'] ) ? $form_data['filter_form_data']['wt_iew_product_types'] : array();
		$prod_status = ! empty( $form_data['filter_form_data']['wt_iew_product_status'] ) ? $form_data['filter_form_data']['wt_iew_product_status'] : array();

		$export_sortby = ! empty( $form_data['filter_form_data']['wt_iew_sort_columns'] ) ? $form_data['filter_form_data']['wt_iew_sort_columns'] : 'ID';
		$export_sort_order = ! empty( $form_data['filter_form_data']['wt_iew_order_by'] ) ? $form_data['filter_form_data']['wt_iew_order_by'] : 'ASC';

		$export_limit = ! empty( $form_data['filter_form_data']['wt_iew_limit'] ) ? intval( $form_data['filter_form_data']['wt_iew_limit'] ) : 999999999; // user limit.
		$current_offset = ! empty( $form_data['filter_form_data']['wt_iew_offset'] ) ? intval( $form_data['filter_form_data']['wt_iew_offset'] ) : 0; // user offset.

		$batch_count = ! empty( $form_data['advanced_form_data']['wt_iew_batch_count'] ) ? $form_data['advanced_form_data']['wt_iew_batch_count'] : Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings( 'default_export_batch' );
		/**
		* Filter the query arguments for a request.
		*
		* Enables adding extra arguments or setting defaults for the request.
		*
		* @since 1.0.0
		*
		* @param int   $batch_count    Export batch count.
		*/
		$batch_count = apply_filters( 'wt_woocommerce_csv_export_limit_per_request', $batch_count ); // ajax batch limit.

		$this->export_children_sku = ( ! empty( $form_data['advanced_form_data']['wt_iew_export_children_sku'] ) && 'Yes' == $form_data['advanced_form_data']['wt_iew_export_children_sku'] ) ? true : false;

		$this->export_shortcodes = ( ! empty( $form_data['advanced_form_data']['wt_iew_export_shortcode_tohtml'] ) && 'Yes' == $form_data['advanced_form_data']['wt_iew_export_shortcode_tohtml'] ) ? true : false;

		$this->export_images_zip = ( ! empty( $form_data['advanced_form_data']['wt_iew_image_export'] ) && 'Yes' == $form_data['advanced_form_data']['wt_iew_image_export'] ) ? true : false;

		$real_offset = ( $current_offset + $batch_offset );

		if ( $batch_count <= $export_limit ) {
			if ( ( $batch_offset + $batch_count ) > $export_limit ) {
				$limit = $export_limit - $batch_offset;
			} else {
				$limit = $batch_count;
			}
		} else {
			$limit = $export_limit;
		}

		$product_array = array();
		if ( $batch_offset < $export_limit ) {

			$args = array(
				'status' => array( 'publish', 'private', 'draft', 'pending', 'future' ),
				'type' => array_merge( array_keys( wc_get_product_types() ) ),
				'limit' => $limit,
				'offset' => $real_offset,
				'orderby' => $export_sortby,
				'order' => $export_sort_order,          // 'return' => 'objects',
				'paginate' => true,
			);

			if ( 'export_image' == $step ) {
				$args['limit']   = $export_limit;
				$args['offset']   = $current_offset;
				$args['paginate']   = false;

				$image_array = array();
				$image_array_with_path = array();
			}

			$args['return']   = 'ids';

			if ( ! empty( $prod_status ) ) {
				$args['status'] = $prod_status;
			}

			if ( ! empty( $prod_types ) ) {
				$args['type'] = $prod_types;
			}

			if ( ! empty( $prod_categories ) ) {
				$args['category'] = $prod_categories;
			}

			if ( ! empty( $prod_tags ) ) {
				$args['tag'] = $prod_tags;
			}

			if ( ! empty( $include_products ) ) {
				$args['include'] = $include_products;
			}

			if ( ! empty( $exclude_products ) ) {
				$args['exclude'] = $exclude_products;
			}

			/**
			* Filter the query arguments for a request.
			*
			* Enables adding extra arguments or setting defaults for the request.
			*
			* @since 1.0.0
			*
			* @param array   $args    Query parameters.
			*/
			$args = apply_filters( 'woocommerce_csv_product_export_args', $args );
			$products = wc_get_products( $args );
			/**
			* Filter the query arguments for a request.
			*
			* Enables adding extra arguments or setting defaults for the request.
			*
			* @since 1.0.0
			*
			* @param array   $products    Products list.
			*/
			$products = apply_filters( 'wt_ier_product_ids_befor_export', $products );
			$total_products = 0;
			if ( 0 == $batch_offset ) {
				$total_item_args = $args;
				$total_item_args['limit'] = $export_limit; // user given limit.
				$total_item_args['offset'] = $current_offset; // user given offset.
				$total_products_count = wc_get_products( $total_item_args );
				$total_products = ( is_object( $total_products_count ) ) ? count( $total_products_count->products ) : count( $total_products_count );
			}

			if ( 'export_image' == $step ) {
				$products_ids = $products;
			} else {
				$products_ids = $products->products;
			}

			if ( 1 ) {
				foreach ( $products_ids as $key => $product_id ) {
					$product = wc_get_product( $product_id );
					if ( 'export_image' == $step ) {
						if ( $product_id ) {
							$image_array[] = self::get_product_images( $product_id );
						}
					} else {
						$product_array[] = $this->generate_row_data_wc_lower( $product );
					}

					if ( $product->is_type( 'variable' ) || $product->has_child() ) {
						$children_ids = $product->get_children();
						if ( ! empty( $children_ids ) ) {
							foreach ( $children_ids as $id ) {
								if ( ! in_array( $id, $products_ids ) ) {  // skipping if alredy processed in $products_ids.
									if ( 'export_image' == $step ) {
										if ( $id ) {
											$image_array[] = self::get_product_images( $id );
										}
									} else {
										$variation = wc_get_product( $id );
										if ( is_object( $variation ) ) {
											$product_array[] = $this->generate_row_data_wc_lower( $variation );
										}
									}
								}
							}
						}
					}
				}
			} else {
				foreach ( $products_ids as $product ) {
					$product_array[] = $this->generate_row_data( $product );
				}
			}
		}

		if ( 'export_image' == $step ) {

			if ( ! empty( $image_array ) ) {
				foreach ( $image_array as $value ) {
					if ( empty( $value ) ) {
						continue;
					}
					foreach ( $value as $val ) {
						$image_array_with_path[] = $val;
					}
				}
			}

			$image_array_with_path = array_unique( $image_array_with_path ); // Avoid dublication.

			return array(
				'total' => count( $image_array_with_path ),
				'images' => $image_array_with_path,
			);
		}

		$return_products = array(
			'total' => $total_products,
			'data' => $product_array,
		);
		if ( 0 == $batch_offset && 0 == $total_products ) {
			$return_products['no_post'] = __( 'Nothing to export under the selected criteria. Please check and try adjusting the filters.' );
		}
		return $return_products;
	}
	/**
	 * Product images
	 *
	 * @param integer $product Product id.
	 * @return type
	 */
	public static function get_product_images( $product ) {
		$image_file_names = array();
		$meta_data = get_post_custom( $product );

		// Featured image.
		$featured_image_id = get_post_thumbnail_id( $product );
		if ( $featured_image_id ) {

			$attached_file_path = wp_get_attachment_url( $featured_image_id ); // wp_get_attachment_image_url(); //get_attached_file($featured_image_id);.

			if ( ! empty( $attached_file_path ) ) {
				$image_file_names[] = $attached_file_path;
			}
		}

		// Images.
		$images = isset( $meta_data['_product_image_gallery'][0] ) ? explode( ',', maybe_unserialize( maybe_unserialize( $meta_data['_product_image_gallery'][0] ) ) ) : false;
		$results = array();
		if ( $images ) {
			foreach ( $images as $image_id ) {
				if ( $featured_image_id == $image_id ) {
					continue;
				}
				$attached_file_path = wp_get_attachment_url( $image_id ); // get_attached_file($image_id);.

				if ( ! empty( $attached_file_path ) ) {
					$image_file_names[] = $attached_file_path;
				}
			}
		}

		/* compatible with WooCommerce Additional Variation Images Gallery plugin */
		$woo_variation_gallery_images = isset( $meta_data['woo_variation_gallery_images'][0] ) ? maybe_unserialize( $meta_data['woo_variation_gallery_images'][0] ) : false;
		if ( $woo_variation_gallery_images ) {
			foreach ( $woo_variation_gallery_images as $image_id ) {
				if ( $featured_image_id == $image_id ) {
					continue;
				}
				$attached_file_path = wp_get_attachment_url( $image_id ); // get_attached_file($image_id);.

				if ( ! empty( $attached_file_path ) ) {
					$image_file_names[] = $attached_file_path;
				}
			}
		}

		return $image_file_names;
	}
	/**
	 * Generate CSV/XML row data for product
	 *
	 * @global object $sitepress WPML
	 * @param object $product_object Product.
	 * @return type
	 */
	protected function generate_row_data_wc_lower( $product_object ) {

		$export_columns = $this->parent_module->get_selected_column_names();

		$post_columns = Wt_Import_Export_For_Woo_Product::get_product_post_columns();
		$standard_meta_columns = array_keys( array_slice( $post_columns, 12 ) );

		$product = get_post( $product_object->get_id() );

		$csv_columns = $export_columns; // Wt_Import_Export_For_Woo_Product::wt_array_walk($export_columns,'meta:'); // Remove string 'meta:' from keys and values, YOAST support.

		$export_columns = ! empty( $csv_columns ) ? $csv_columns : array();

		$row = array();

		if ( 0 == $product->post_parent ) {
			$product->post_parent = '';
		}

		// Pre-process data.
		$meta_data = get_post_custom( $product->ID );

		$product->meta = new stdClass();
		$product->attributes = new stdClass();
		// Meta data.
		foreach ( $meta_data as $meta => $value ) {

			if ( ! $meta ) {
				continue;
			}

			$meta_value = maybe_unserialize( maybe_unserialize( $value[0] ) );

			if ( is_array( $meta_value ) ) {
				$meta_value = json_encode( $meta_value );
			}

			if ( strstr( $meta, 'attribute_pa_' ) ) {
				if ( $meta_value ) {
					$get_name_by_slug = get_term_by( 'slug', $meta_value, str_replace( 'attribute_', '', $meta ) );
					if ( $get_name_by_slug ) {
						$product->meta->$meta = self::format_export_meta( $get_name_by_slug->name, $meta );
					} else {
						$product->meta->$meta = self::format_export_meta( $meta_value, $meta );
					}
				} else {
					$product->meta->$meta = self::format_export_meta( $meta_value, $meta );
				}
			} else {

				$formatted_mdata = self::format_export_meta( $meta_value, $meta );
				$clean_meta_key = ltrim( $meta, '_' );
				if ( in_array( $clean_meta_key, $standard_meta_columns ) ) {
					$meta = $clean_meta_key;
				}
				$product->meta->$meta = $formatted_mdata;
			}
		}

		// Product attributes.
		if ( isset( $meta_data['_product_attributes'][0] ) ) {

			$attributes = maybe_unserialize( maybe_unserialize( $meta_data['_product_attributes'][0] ) );

			if ( ! empty( $attributes ) && is_array( $attributes ) ) {
				foreach ( $attributes as $key => $attribute ) {
					if ( ! $key ) {
						continue;
					}
					$key = rawurldecode( $key );

					$key_to_find_default_attribute = $key;
					if ( 1 == $attribute['is_taxonomy'] ) {
						$terms = wp_get_post_terms( $product->ID, $key, array( 'fields' => 'names' ) );
						if ( ! is_wp_error( $terms ) ) {
							$attribute_value = implode( '|', $terms );
						} else {
							$attribute_value = '';
						}
					} else {
						if ( empty( $attribute['name'] ) ) {
							continue;
						}
						$key = $attribute['name'];
						$attribute_value = $attribute['value'];
					}

					if ( ! isset( $attribute['position'] ) ) {
						$attribute['position'] = 0;
					}
					if ( ! isset( $attribute['is_visible'] ) ) {
						$attribute['is_visible'] = 0;
					}
					if ( ! isset( $attribute['is_variation'] ) ) {
						$attribute['is_variation'] = 0;
					}

					$attribute_data = $attribute['position'] . '|' . $attribute['is_visible'] . '|' . $attribute['is_variation'];
					$_default_attributes = isset( $meta_data['_default_attributes'][0] ) ? maybe_unserialize( maybe_unserialize( $meta_data['_default_attributes'][0] ) ) : '';

					if ( is_array( $_default_attributes ) ) {
						$default_attribute = isset( $_default_attributes[ $key_to_find_default_attribute ] ) ? $_default_attributes[ $key_to_find_default_attribute ] : '';
						$term = get_term_by( 'slug', $default_attribute, $key_to_find_default_attribute );
						$_default_attribute = ( ! is_wp_error( $term ) && ! empty( $term ) ? $term->name : $default_attribute );

					} else {
						$_default_attribute = '';
					}

					$product->attributes->$key = array(
						'value' => $attribute_value,
						'data' => $attribute_data,
						'default' => $_default_attribute,
					);
				}
			}
		}

		foreach ( $csv_columns as $column => $value ) {

			if ( ! $export_columns || in_array( $value, $export_columns ) || in_array( $column, $export_columns ) ) {
				if ( ( '_regular_price' == $column || 'regular_price' == $column ) && empty( $product->meta->$column ) ) {
					$column = '_price';
				}

				if ( ! Wt_Import_Export_For_Woo_Common_Helper::wt_iew_is_woocommerce_prior_to( '2.7' ) ) {
					if ( '_visibility' == $column || 'visibility' == $column ) {
						$product_terms = get_the_terms( $product->ID, 'product_visibility' );
						if ( ! empty( $product_terms ) ) {
							if ( ! is_wp_error( $product_terms ) ) {
								$term_slug = '';
								foreach ( $product_terms as $i => $term ) {
									$term_slug .= $term->slug . ( isset( $product_terms[ $i + 1 ] ) ? '|' : '' );
								}
								$row[ $column ] = $term_slug;
							}
						} else {
							$row[ $column ] = '';
						}
						continue;
					}
				}

				if ( 'Parent' == $column ) {
					if ( $product->post_parent ) {
						$post_parent_title = get_the_title( $product->post_parent );
						if ( $post_parent_title ) {
							$row[ $column ] = self::format_data( $post_parent_title );
						} else {
							$row[ $column ] = '';
						}
					} else {
						$row[ $column ] = '';
					}
					continue;
				}

				if ( 'parent_sku' == $column ) {
					if ( $product->post_parent ) {
						$row[ $column ] = get_post_meta( $product->post_parent, '_sku', true );
					} else {
						$row[ $column ] = '';
					}
					continue;
				}

				// Export images/gallery.
				if ( 'images' == $column ) {
					/**
					 * Filter the query arguments for a request.
					 *
					 * Enables adding extra arguments or setting defaults for the request.
					 *
					 * @since 1.0.0
					 *
					 * @param boolean   $image_meta_data_needed    Image metadata needed in export flag.
					 */
					$export_image_metadata = apply_filters( 'hf_export_image_metadata_flag', true ); // filter for disable export image meta datas such as alt,title,content,caption...
					$image_file_names = array();

					// Featured image.
					$featured_image_id = get_post_thumbnail_id( $product->ID );
					if ( $featured_image_id ) {
						$image_object = get_post( $featured_image_id );
						$img_url = wp_get_attachment_image_src( $featured_image_id, 'full' );

						$image_meta = '';
						if ( $image_object && $export_image_metadata ) {
							$image_metadata = get_post_meta( $featured_image_id );
							$image_meta = ' ! alt : ' . ( isset( $image_metadata['_wp_attachment_image_alt'][0] ) ? $image_metadata['_wp_attachment_image_alt'][0] : '' ) . ' ! title : ' . $image_object->post_title . ' ! desc : ' . $image_object->post_content . ' ! caption : ' . $image_object->post_excerpt;
						}

						if ( $image_object && $image_object->guid ) {
							if ( ! empty( $img_url ) ) {
								$temp_images_export_to_csv = ( $this->export_images_zip ? basename( $img_url[0] ) : $img_url[0] ) . ( $export_image_metadata ? $image_meta : '' );
							}
						}

						if ( ! empty( $temp_images_export_to_csv ) ) {
							$image_file_names[] = $temp_images_export_to_csv;
						}
					}

					// Images.
					$images = isset( $meta_data['_product_image_gallery'][0] ) ? explode( ',', maybe_unserialize( maybe_unserialize( $meta_data['_product_image_gallery'][0] ) ) ) : false;
					$results = array();
					if ( $images ) {
						foreach ( $images as $image_id ) {
							if ( $featured_image_id == $image_id ) {
								continue;
							}
							$temp_gallery_images_export_to_csv = '';
							$gallery_image_meta = '';
							$gallery_image_object = get_post( $image_id );
							$gallery_img_url = wp_get_attachment_image_src( $image_id, 'full' );

							if ( $gallery_image_object && $export_image_metadata ) {
								$gallery_image_metadata = get_post_meta( $image_id );
								$gallery_image_meta = ' ! alt : ' . ( isset( $gallery_image_metadata['_wp_attachment_image_alt'][0] ) ? $gallery_image_metadata['_wp_attachment_image_alt'][0] : '' ) . ' ! title : ' . $gallery_image_object->post_title . ' ! desc : ' . $gallery_image_object->post_content . ' ! caption : ' . $gallery_image_object->post_excerpt;
							}

							if ( $gallery_image_object && $gallery_image_object->guid ) {
								$temp_gallery_images_export_to_csv = ( $this->export_images_zip ? basename( $gallery_img_url[0] ) : $gallery_img_url[0] ) . ( $export_image_metadata ? $gallery_image_meta : '' );
							}
							if ( ! empty( $temp_gallery_images_export_to_csv ) ) {
								$image_file_names[] = $temp_gallery_images_export_to_csv;
							}
						}
					}

					if ( ! empty( $image_file_names ) ) {
						$row[ $column ] = implode( ' | ', $image_file_names );
					} else {
						$row[ $column ] = '';
					}
					continue;
				}

				// Downloadable files.
				if ( 'file_paths' == $column || 'downloadable_files' == $column ) {
					$file_paths_to_export = array();
					if ( ! function_exists( 'wc_get_filename_from_url' ) ) {
						$file_paths = maybe_unserialize( maybe_unserialize( $meta_data['_file_paths'][0] ) );

						if ( $file_paths ) {
							foreach ( $file_paths as $file_path ) {
								$file_paths_to_export[] = $file_path;
							}
						}

						$file_paths_to_export = implode( ' | ', $file_paths_to_export );
						$row[] = self::format_data( $file_paths_to_export );
					} elseif ( isset( $meta_data['_downloadable_files'][0] ) ) {
						$file_paths = maybe_unserialize( maybe_unserialize( $meta_data['_downloadable_files'][0] ) );

						if ( is_array( $file_paths ) || is_object( $file_paths ) ) {
							foreach ( $file_paths as $file_path ) {
								$file_paths_to_export[] = ( ! empty( $file_path['name'] ) ? $file_path['name'] : Wt_Import_Export_For_Woo_Common_Helper::wt_wc_get_filename_from_url( $file_path['file'] ) ) . '::' . $file_path['file'];
							}
						}
						$file_paths_to_export = implode( ' | ', $file_paths_to_export );
					}
					if ( ! empty( $file_paths_to_export ) ) {
						$row[ $column ] = ! empty( $file_paths_to_export ) ? self::format_data( $file_paths_to_export ) : '';
					} else {
						$row[ $column ] = '';
					}
					continue;
				}

				// Export taxonomies.
				// if ( 'taxonomies' == $column ) {.
				if ( 'tax:' == substr( $column, 0, 4 ) ) {

					$taxonomy_name = substr( $column, 4 );

					if ( is_taxonomy_hierarchical( $taxonomy_name ) ) {
						$terms = wp_get_post_terms( $product->ID, $taxonomy_name, array( 'fields' => 'all' ) );
						$formatted_terms = array();

						foreach ( $terms as $term ) {
							$ancestors = array_reverse( get_ancestors( $term->term_id, $taxonomy_name ) );
							$formatted_term = array();

							foreach ( $ancestors as $ancestor ) {
								$formatted_term[] = get_term( $ancestor, $taxonomy_name )->name;
							}

							$formatted_term[] = $term->name;

							$formatted_terms[] = implode( ' > ', $formatted_term );
						}

						$row[ $column ] = self::format_data( implode( '|', $formatted_terms ) );
					} else {
						if ( 'product_type' == $taxonomy_name ) {
							$terms = wp_get_post_terms( $product->ID, $taxonomy_name, array( 'fields' => 'slugs' ) );
						} else {
							$terms = wp_get_post_terms( $product->ID, $taxonomy_name, array( 'fields' => 'names' ) );
						}
						$row[ $column ] = self::format_data( implode( '|', $terms ) );

					}
					continue;
				}

				// Export meta data.
				// if ( 'meta' == $column ) {.
				if ( 'meta:' == substr( $column, 0, 5 ) ) {

					$product_meta = substr( $column, 5 );

					if ( isset( $product->meta->$product_meta ) ) {
						$row[ $column ] = self::format_data( $product->meta->$product_meta );
					} else {
						$row[ $column ] = '';
					}

					continue;
				}

				// Find and export attributes.
				// if ('attributes' == $column ) {.
				if ( strstr( $column, 'attribute' ) ) {
					if ( 'attribute:' == substr( $column, 0, 10 ) ) {
						$attribute = substr( $column, 10 );
						if ( isset( $product->attributes ) && isset( $product->attributes->$attribute ) ) {
							$values = $product->attributes->$attribute;
							$row[ $column ] = self::format_data( $values['value'] );
						} else {
							$row[ $column ] = '';
						}
						continue;
					}

					if ( 'attribute_data:' == substr( $column, 0, 15 ) ) {
						$attribute = substr( $column, 15 );
						if ( isset( $product->attributes ) && isset( $product->attributes->$attribute ) ) {
							$values = $product->attributes->$attribute;
							$row[ $column ] = self::format_data( $values['data'] );
						} else {
							$row[ $column ] = '';
						}
						continue;
					}

					if ( 'attribute_default:' == substr( $column, 0, 18 ) ) {
						$attribute = substr( $column, 18 );
						if ( isset( $product->attributes ) && isset( $product->attributes->$attribute ) ) {
							$values = $product->attributes->$attribute;
							$row[ $column ] = self::format_data( $values['default'] );
						} else {
							$row[ $column ] = '';
						}
						continue;
					}
				}
				// WF: Adding product permalink.
				if ( 'product_page_url' == $column ) {
					$product_page_url = '';
					if ( ! empty( $product->ID ) ) {
						$product_page_url = get_permalink( $product->ID );
					}
					if ( ! empty( $product->post_parent ) ) {
						$product_page_url = get_permalink( $product->post_parent );
					}
					$row[ $column ] = ! empty( $product_page_url ) ? $product_page_url : '';
					continue;
				}

				/**
				 * Filter the query arguments for a request.
				 *
				 * Enables adding extra arguments or setting defaults for the request.
				 *
				 * @since 1.0.0
				 *
				 * @param boolean   $is_completed_wpml_setup    Is completed WPML setup.
				 * @param string   $action    WPML setup action.
				 */
				if ( apply_filters( 'wpml_setting', false, 'setup_complete' ) ) {

					if ( in_array( $column, array( 'wpml:language_code', 'wpml:original_product_id', 'wpml:original_product_sku' ) ) ) {
						if ( 'wpml:language_code' == $column ) {
							$original_post_language_info = Wt_Import_Export_For_Woo_Common_Helper::wt_get_wpml_original_post_language_info( $product->ID );
							$row[ $column ] = ( isset( $original_post_language_info->language_code ) && ! empty( $original_post_language_info->language_code ) ? $original_post_language_info->language_code : '' );
							continue;
						}
						/**
						 *
						 * To get the ID of the original product post
						 * https://wpml.org/forums/topic/translated-product-get-id-of-original-lang-for-custom-fields/
						 */
						global $sitepress;
						$original_product_id = icl_object_id( $product->ID, 'product', false, $sitepress->get_default_language() );
						if ( 'wpml:original_product_id' == $column ) {
							$row[ $column ] = ( $original_product_id ? $original_product_id : '' );
							continue;
						}
						if ( 'wpml:original_product_sku' == $column ) {
							$sku = get_post_meta( $original_product_id, '_sku', true );
							$row[ $column ] = ( $sku ? $sku : '' );
							continue;
						}
					}
				}

				// handling default meta and other columns.

				if ( isset( $product->meta->$column ) ) {

					if ( in_array( $column, array( '_children', '_upsell_ids', '_crosssell_ids', 'children', 'upsell_ids', 'crosssell_ids' ) ) ) {
						if ( $this->export_children_sku ) {
							$children_sku = '';
							$children_id_array = str_replace( '"', '', explode( '|', trim( $product->meta->$column, '[]' ) ) );

							if ( ! empty( $children_id_array ) && '""' != $children_id_array[0] ) {
								foreach ( $children_id_array as $children_id_array_key => $children_id ) {
									$children_sku = ! empty( $children_sku ) ? "{$children_sku}|" . get_post_meta( $children_id, '_sku', true ) : get_post_meta( $children_id, '_sku', true );
								}
							}
							$row[ $column ] = ! empty( $children_sku ) ? $children_sku : '';
						} else {
							$row[ $column ] = str_replace( '"', '', implode( '|', explode( ',', trim( $product->meta->$column, '[]' ) ) ) );
						}
					} elseif ( '_stock_status' == $column || 'stock_status' == $column ) {
						$stock_status = self::format_data( $product->meta->$column );
						$product_type = ( WC()->version < '3.0' ) ? $product_object->product_type : $product_object->get_type();
						$row[ $column ] = ! empty( $stock_status ) ? $stock_status : ( ( 'variable' == $product_type || 'variable-subscription' == $product_type ) ? '' : 'instock' );
					} elseif ( ( '_sku' == $column || 'sku' == $column ) ) {
						$row[ $column ] = $product->meta->$column;
					} else {
						$row[ $column ] = self::format_data( $product->meta->$column );
					}
				} elseif ( isset( $product->$column ) && ! is_array( $product->$column ) ) {
					if ( $this->export_shortcodes && ( 'post_content' == $column || 'post_excerpt' == $column ) ) {
						// Convert Shortcodes to html for Description and Short Description.
						$row[ $column ] = do_shortcode( $product->$column );
					} elseif ( 'post_title' === $column ) {
						$row[ $column ] = sanitize_text_field( $product->$column );
					} else {
						$row[ $column ] = self::format_data( $product->$column );
					}
				} else {
					$row[ $column ] = '';
				}
			}
		}

		if ( isset( $row['_featured'] ) || isset( $row['featured'] ) ) {
			if ( isset( $row['tax:product_visibility'] ) && strstr( $row['tax:product_visibility'], 'featured' ) ) {
				$tax_product_visibility_array = explode( '|', $row['tax:product_visibility'] );
				$tax_product_visibility_key = array_search( 'featured', $tax_product_visibility_array );
				if ( ! empty( $tax_product_visibility_array[ $tax_product_visibility_key ] ) ) {
					unset( $tax_product_visibility_array[ $tax_product_visibility_key ] );
				}
				$row['tax:product_visibility'] = implode( '|', $tax_product_visibility_array );
				$row['featured'] = 1;
			}
		}
		/**
		* Filter the query arguments for a request.
		*
		* Enables adding extra arguments or setting defaults for the request.
		*
		* @since 1.0.0
		*
		* @param array   $row    CSV product row data.
		* @param object $product Product.
		*/
		return apply_filters( 'wt_batch_product_export_row_data', $row, $product );
	}

	/**
	 * Take a product and generate row data from it for export.
	 *
	 * @param WC_Product $product WC_Product object.
	 */
	protected function generate_row_data( $product ) {
		// $columns = $this->get_column_names();

		$export_columns = $this->get_selected_column_names();

		$row = array();
		foreach ( $export_columns as $column_id => $column_name ) {

			// $column_id = strstr($column_id, ':') ? current(explode(':', $column_id)) : $column_id;

			$column_id = ltrim( $column_id, '_' );

			$value = '';

			// Skip some columns if dynamically handled later or if we're being selective.
			// if (in_array($column_id, array('downloads', 'attributes', 'meta'), true) || !$this->is_column_exporting($column_id)) {
			// continue;
			// }
			// if (has_filter("woocommerce_product_export_{$this->export_type}_column_{$column_id}")) {
			// Filter for 3rd parties.
			// $value = apply_filters("woocommerce_product_export_{$this->export_type}_column_{$column_id}", '', $product, $column_id);
			// } else.
			if ( is_callable( array( $this, "get_column_value_{$column_id}" ) ) ) {
				// Handle special columns which don't map 1:1 to product data.
				$value = $this->{"get_column_value_{$column_id}"}( $product );
			} elseif ( is_callable( array( $product, "get_{$column_id}" ) ) ) {
				// Default and custom handling.
				$value = $product->{"get_{$column_id}"}( 'edit' );
			}

			if ( 'description' === $column_id || 'short_description' === $column_id ) {
				$value = $this->filter_description_field( $value );
			}

			$row[ $column_id ] = $value;
		}

		// $this->prepare_downloads_for_export($product, $row);
		// $this->prepare_attributes_for_export($product, $row);
		// $this->prepare_meta_for_export($product, $row);
		// return apply_filters('wt_woocommerce_product_export_row_data', $row, $product);
	}

	/**
	 * Get published value.
	 *
	 * @param WC_Product $product Product being exported.
	 *
	 * @since  3.1.0
	 * @return int
	 */
	protected function get_column_value_published( $product ) {
		$statuses = array(
			'draft' => -1,
			'private' => 0,
			'publish' => 1,
		);

		$status = $product->get_status( 'edit' );

		return isset( $statuses[ $status ] ) ? $statuses[ $status ] : -1;
	}

	/**
	 * Get formatted sale price.
	 *
	 * @param WC_Product $product Product being exported.
	 *
	 * @return string
	 */
	protected function get_column_value_sale_price( $product ) {
		return wc_format_localized_price( $product->get_sale_price( 'view' ) );
	}

	/**
	 * Get formatted regular price.
	 *
	 * @param WC_Product $product Product being exported.
	 *
	 * @return string
	 */
	protected function get_column_value_regular_price( $product ) {
		return wc_format_localized_price( $product->get_regular_price() );
	}

	/**
	 * Get product_cat value.
	 *
	 * @param WC_Product $product Product being exported.
	 *
	 * @since  3.1.0
	 * @return string
	 */
	protected function get_column_value_category_ids( $product ) {
		$term_ids = $product->get_category_ids( 'edit' );
		return $this->format_term_ids( $term_ids, 'product_cat' );
	}

	/**
	 * Get product_tag value.
	 *
	 * @param WC_Product $product Product being exported.
	 *
	 * @since  3.1.0
	 * @return string
	 */
	protected function get_column_value_tag_ids( $product ) {
		$term_ids = $product->get_tag_ids( 'edit' );
		return $this->format_term_ids( $term_ids, 'product_tag' );
	}

	/**
	 * Get product_shipping_class value.
	 *
	 * @param WC_Product $product Product being exported.
	 *
	 * @since  3.1.0
	 * @return string
	 */
	protected function get_column_value_shipping_class_id( $product ) {
		$term_ids = $product->get_shipping_class_id( 'edit' );
		return $this->format_term_ids( $term_ids, 'product_shipping_class' );
	}

	/**
	 * Get images value.
	 *
	 * @param WC_Product $product Product being exported.
	 *
	 * @since  3.1.0
	 * @return string
	 */
	protected function get_column_value_images( $product ) {
		$image_ids = array_merge( array( $product->get_image_id( 'edit' ) ), $product->get_gallery_image_ids( 'edit' ) );
		$images = array();

		foreach ( $image_ids as $image_id ) {
			$image = wp_get_attachment_image_src( $image_id, 'full' );

			if ( $image ) {
				$images[] = $image[0];
			}
		}

		return $this->implode_values( $images );
	}

	/**
	 * Format term ids to names.
	 *
	 * @since 3.1.0
	 * @param  array  $term_ids Term IDs to format.
	 * @param  string $taxonomy Taxonomy name.
	 * @return string
	 */
	public function format_term_ids( $term_ids, $taxonomy ) {
		$term_ids = wp_parse_id_list( $term_ids );

		if ( ! count( $term_ids ) ) {
			return '';
		}

		$formatted_terms = array();

		if ( is_taxonomy_hierarchical( $taxonomy ) ) {
			foreach ( $term_ids as $term_id ) {
				$formatted_term = array();
				$ancestor_ids = array_reverse( get_ancestors( $term_id, $taxonomy ) );

				foreach ( $ancestor_ids as $ancestor_id ) {
					$term = get_term( $ancestor_id, $taxonomy );
					if ( $term && ! is_wp_error( $term ) ) {
						$formatted_term[] = $term->name;
					}
				}

				$term = get_term( $term_id, $taxonomy );

				if ( $term && ! is_wp_error( $term ) ) {
					$formatted_term[] = $term->name;
				}

				$formatted_terms[] = implode( ' > ', $formatted_term );
			}
		} else {
			foreach ( $term_ids as $term_id ) {
				$term = get_term( $term_id, $taxonomy );

				if ( $term && ! is_wp_error( $term ) ) {
					$formatted_terms[] = $term->name;
				}
			}
		}

		return $this->implode_values( $formatted_terms );
	}

	/**
	 * Implode CSV cell values using commas by default, and wrapping values
	 * which contain the separator.
	 *
	 * @since  3.2.0
	 * @param  array $values Values to implode.
	 * @return string
	 */
	protected function implode_values( $values ) {
		$values_to_implode = array();

		foreach ( $values as $value ) {
			$value = (string) is_scalar( $value ) ? $value : '';
			$values_to_implode[] = str_replace( ',', '\\,', $value );
		}

		return implode( ', ', $values_to_implode );
	}

	/**
	 * Filter description field for export.
	 * Convert newlines to '\n'.
	 *
	 * @param string $description Product description text to filter.
	 *
	 * @since  3.5.4
	 * @return string
	 */
	protected function filter_description_field( $description ) {
		$description = str_replace( '\n', "\\\\n", $description );
		$description = str_replace( "\n", '\n', $description );
		return $description;
	}

	/**
	 * Export downloads.
	 *
	 * @param WC_Product $product Product being exported.
	 * @param array      $row     Row being exported.
	 *
	 * @since 3.1.0
	 */
	protected function prepare_downloads_for_export( $product, &$row ) {
		if ( $product->is_downloadable() && $this->is_column_exporting( 'downloads' ) ) {
			$downloads = $product->get_downloads( 'edit' );

			if ( $downloads ) {
				$i = 1;
				foreach ( $downloads as $download ) {
					/* translators: %s: download number */
					$this->column_names[ 'downloads:name' . $i ] = sprintf( __( 'Download %d name', 'woocommerce' ), $i );
					/* translators: %s: download number */
					$this->column_names[ 'downloads:url' . $i ] = sprintf( __( 'Download %d URL', 'woocommerce' ), $i );
					$row[ 'downloads:name' . $i ] = $download->get_name();
					$row[ 'downloads:url' . $i ] = $download->get_file();
					$i++;
				}
			}
		}
	}

	/**
	 * Export attributes data.
	 *
	 * @param WC_Product $product Product being exported.
	 * @param array      $row     Row being exported.
	 *
	 * @since 3.1.0
	 */
	protected function prepare_attributes_for_export( $product, &$row ) {
		if ( $this->is_column_exporting( 'attributes' ) ) {
			$attributes = $product->get_attributes();
			$default_attributes = $product->get_default_attributes();

			if ( count( $attributes ) ) {
				$i = 1;
				foreach ( $attributes as $attribute_name => $attribute ) {
					/* translators: %s: attribute number */
					$this->column_names[ 'attributes:name' . $i ] = sprintf( __( 'Attribute %d name', 'woocommerce' ), $i );
					/* translators: %s: attribute number */
					$this->column_names[ 'attributes:value' . $i ] = sprintf( __( 'Attribute %d value(s)', 'woocommerce' ), $i );
					/* translators: %s: attribute number */
					$this->column_names[ 'attributes:visible' . $i ] = sprintf( __( 'Attribute %d visible', 'woocommerce' ), $i );
					/* translators: %s: attribute number */
					$this->column_names[ 'attributes:taxonomy' . $i ] = sprintf( __( 'Attribute %d global', 'woocommerce' ), $i );

					if ( is_a( $attribute, 'WC_Product_Attribute' ) ) {
						$row[ 'attributes:name' . $i ] = wc_attribute_label( $attribute->get_name(), $product );

						if ( $attribute->is_taxonomy() ) {
							$terms = $attribute->get_terms();
							$values = array();

							foreach ( $terms as $term ) {
								$values[] = $term->name;
							}

							$row[ 'attributes:value' . $i ] = $this->implode_values( $values );
							$row[ 'attributes:taxonomy' . $i ] = 1;
						} else {
							$row[ 'attributes:value' . $i ] = $this->implode_values( $attribute->get_options() );
							$row[ 'attributes:taxonomy' . $i ] = 0;
						}

						$row[ 'attributes:visible' . $i ] = $attribute->get_visible();
					} else {
						$row[ 'attributes:name' . $i ] = wc_attribute_label( $attribute_name, $product );

						if ( 0 === strpos( $attribute_name, 'pa_' ) ) {
							$option_term = get_term_by( 'slug', $attribute, $attribute_name ); // @codingStandardsIgnoreLine.
							$row[ 'attributes:value' . $i ] = $option_term && ! is_wp_error( $option_term ) ? str_replace( ',', '\\,', $option_term->name ) : $attribute;
							$row[ 'attributes:taxonomy' . $i ] = 1;
						} else {
							$row[ 'attributes:value' . $i ] = $attribute;
							$row[ 'attributes:taxonomy' . $i ] = 0;
						}

						$row[ 'attributes:visible' . $i ] = '';
					}

					if ( $product->is_type( 'variable' ) && isset( $default_attributes[ sanitize_title( $attribute_name ) ] ) ) {
						/* translators: %s: attribute number */
						$this->column_names[ 'attributes:default' . $i ] = sprintf( __( 'Attribute %d default', 'woocommerce' ), $i );
						$default_value = $default_attributes[ sanitize_title( $attribute_name ) ];

						if ( 0 === strpos( $attribute_name, 'pa_' ) ) {
							$option_term = get_term_by( 'slug', $default_value, $attribute_name ); // @codingStandardsIgnoreLine.
							$row[ 'attributes:default' . $i ] = $option_term && ! is_wp_error( $option_term ) ? $option_term->name : $default_value;
						} else {
							$row[ 'attributes:default' . $i ] = $default_value;
						}
					}
					$i++;
				}
			}
		}
	}

	/**
	 * Export meta data.
	 *
	 * @param WC_Product $product Product being exported.
	 * @param array      $row Row data.
	 *
	 * @since 3.1.0
	 */
	protected function prepare_meta_for_export( $product, &$row ) {
		if ( $this->enable_meta_export ) {
			$meta_data = $product->get_meta_data();

			if ( count( $meta_data ) ) {
				/**
				 * Export meta data.
				 *
				 * @param array      $meta_keys Meta keys.
				 * @param WC_Product $product Product being exported.
				 *
				 * @since 1.0.0
				 */
				$meta_keys_to_skip = apply_filters( 'woocommerce_product_export_skip_meta_keys', array(), $product );

				$i = 1;
				foreach ( $meta_data as $meta ) {
					if ( in_array( $meta->key, $meta_keys_to_skip, true ) ) {
						continue;
					}

					/**
					 * Allow 3rd parties to process the meta, e.g. to transform non-scalar values to scalar.
					 *
					 * @param string      $meta_value Meta value.
					 * @param object      $meta Meta object.
					 * @param WC_Product $product Product being exported.
					 * @param array      $row CSV row.
					 *
					 * @since 1.0.0
					 */
					$meta_value = apply_filters( 'woocommerce_product_export_meta_value', $meta->value, $meta, $product, $row );

					if ( ! is_scalar( $meta_value ) ) {
						continue;
					}

					$column_key = 'meta:' . esc_attr( $meta->key );
					/* translators: %s: meta data name */
					$this->column_names[ $column_key ] = sprintf( __( 'Meta: %s', 'woocommerce' ), $meta->key );
					$row[ $column_key ] = $meta_value;
					$i++;
				}
			}
		}
	}


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
			case 'sale_price_dates_from':
			case 'sale_price_dates_to':
				$timestamp = is_numeric( $meta_value ) ? (int) $meta_value : strtotime( $meta_value );

				if ( false === $timestamp ) {
					return '';
				}

				return gmdate( 'Y-m-d', $timestamp ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				break;
			case '_upsell_ids':
			case '_crosssell_ids':
			case 'upsell_ids':
			case 'crosssell_ids':
				return implode( '|', array_filter( (array) json_decode( $meta_value ) ) );
				break;
			default:
				return $meta_value;
				break;
		}
	}
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
		// $enc = mb_detect_encoding($data, 'UTF-8, ISO-8859-1', true);
		$use_mb = function_exists( 'mb_detect_encoding' );
		$enc = '';
		if ( $use_mb ) {
			$enc = mb_detect_encoding( $data, 'UTF-8, ISO-8859-1', true );
		}
		$data = ( 'UTF-8' == $enc ) ? $data : utf8_encode( $data );

		return $data;
	}
}
