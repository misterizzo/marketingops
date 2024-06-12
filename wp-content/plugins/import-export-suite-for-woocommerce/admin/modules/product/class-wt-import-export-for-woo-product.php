<?php
/**
 * Handles the product actions.
 *
 * @package   ImportExportSuite\Admin\Modules\Product
 * @version   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_Product Class.
 */
class Wt_Import_Export_For_Woo_Product {

	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $module_id = '';
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public static $module_id_static = '';
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $module_base = 'product';
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $module_name = 'Product Import Export for WooCommerce';
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $min_base_version = '1.0.0';
	/**
	 * Module ID
	 *
	 * Minimum `Import export plugin` required to run this add on plugin.
	 *
	 * @var string
	 */
	private $importer = null;
	/**
	 * Module ID
	 *
	 * @var string
	 */
	private $exporter = null;
	/**
	 * Module ID
	 *
	 * @var string
	 */
	private $product_categories = null;
	/**
	 * Module ID
	 *
	 * @var string
	 */
	private $product_tags = null;
	/**
	 * Module ID
	 *
	 * @var string
	 */
	private $product_taxonomies = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	private $all_meta_keys = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	private $product_attributes = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	private $exclude_hidden_meta_columns = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	private $found_product_meta = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	private $found_product_hidden_meta = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	private $selected_column_names = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		/*
		 *   Checking the minimum required version of `Import export plugin` plugin available
		 */
		if ( ! Wt_Import_Export_For_Woo_Common_Helper::check_base_version( $this->module_base, $this->module_name, $this->min_base_version ) ) {
			return;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return;
		}

		$this->module_id        = Wt_Import_Export_For_Woo::get_module_id( $this->module_base );
		self::$module_id_static = $this->module_id;

		add_filter( 'wt_iew_exporter_post_types', array( $this, 'wt_iew_exporter_post_types' ), 10, 1 );
		add_filter( 'wt_iew_importer_post_types', array( $this, 'wt_iew_exporter_post_types' ), 10, 1 );

		add_filter( 'wt_iew_exporter_alter_filter_fields', array( $this, 'exporter_alter_filter_fields' ), 10, 3 );

		add_filter( 'wt_iew_exporter_alter_mapping_fields', array( $this, 'exporter_alter_mapping_fields' ), 10, 3 );
		add_filter( 'wt_iew_importer_alter_mapping_fields', array( $this, 'get_importer_post_columns' ), 10, 3 );

		add_filter( 'wt_iew_exporter_alter_advanced_fields', array( $this, 'exporter_alter_advanced_fields' ), 10, 3 );
		add_filter( 'wt_iew_importer_alter_advanced_fields', array( $this, 'importer_alter_advanced_fields' ), 10, 3 );

		add_filter( 'wt_iew_exporter_alter_meta_mapping_fields', array( $this, 'exporter_alter_meta_mapping_fields' ), 10, 3 );
		add_filter( 'wt_iew_importer_alter_meta_mapping_fields', array( $this, 'importer_alter_meta_mapping_fields' ), 10, 3 );

		add_filter( 'wt_iew_exporter_alter_mapping_enabled_fields', array( $this, 'exporter_alter_mapping_enabled_fields' ), 10, 3 );
		add_filter( 'wt_iew_importer_alter_mapping_enabled_fields', array( $this, 'exporter_alter_mapping_enabled_fields' ), 10, 3 );

		add_filter( 'wt_iew_exporter_do_export', array( $this, 'exporter_do_export' ), 10, 7 );
		add_filter( 'wt_iew_importer_do_import', array( $this, 'importer_do_import' ), 10, 8 );

		add_filter( 'wt_iew_exporter_do_image_export', array( $this, 'exporter_do_export' ), 10, 7 );

		add_filter( 'wt_iew_importer_steps', array( $this, 'importer_steps' ), 10, 2 );

		add_action( 'wt_product_addon_help_content', array( $this, 'wt_product_import_export_help_content' ) );
	}//end __construct()


	/**
	 *   Altering advanced step description
	 *
	 * @param array  $steps Steps.
	 * @param string $base Base class.
	 */
	public function importer_steps( $steps, $base ) {
		if ( $this->module_base == $base ) {
			$steps['advanced']['description'] = __( 'Use advanced options from below to decide updates to existing products, batch import count or schedule an import. You can also save the template file for future imports.', 'import-export-suite-for-woocommerce' );
		}

		return $steps;
	}//end importer_steps()

	/**
	 *   Do the import process
	 *
	 * @param   array   $import_data Form data.
	 * @param string  $base Base.
	 * @param   string  $step export step.
	 * @param   array   $form_data to export type.
	 * @param   string  $selected_template_data Template.
	 * @param   integer $method_import id of export.
	 * @param   integer $batch_offset offset.
	 * @param   bool    $is_last_batch Is last.
	 *
	 * @return array
	 */
	public function importer_do_import( $import_data, $base, $step, $form_data, $selected_template_data, $method_import, $batch_offset, $is_last_batch ) {
		if ( $this->module_base != $base ) {
			return $import_data;
		}

		if ( 0 == $batch_offset ) {
			$memory    = size_format( Wt_Import_Export_For_Woo_Common_Helper::wt_let_to_num( ini_get( 'memory_limit' ) ) );
			$wp_memory = size_format( Wt_Import_Export_For_Woo_Common_Helper::wt_let_to_num( WP_MEMORY_LIMIT ) );
			Wt_Import_Export_For_Woo_Logwriter::write_log( $this->module_base, 'import', '---[ New import started at ' . gmdate( 'Y-m-d H:i:s' ) . ' ] PHP Memory: ' . $memory . ', WP Memory: ' . $wp_memory );// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		}

		include plugin_dir_path( __FILE__ ) . 'import/class-wt-import-export-for-woo-product-import.php';
		$import = new Wt_Import_Export_For_Woo_Product_Import( $this );

		$response = $import->prepare_data_to_import( $import_data, $form_data, $batch_offset, $is_last_batch );

		if ( $is_last_batch ) {
			Wt_Import_Export_For_Woo_Logwriter::write_log( $this->module_base, 'import', '---[ Import ended at ' . gmdate( 'Y-m-d H:i:s' ) . ']---' );// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		}

		return $response;
	}//end importer_do_import()

	/**
	 * Export process initiate.
	 *
	 * @param array   $export_data Export data.
	 * @param string  $base Base.
	 * @param string  $step Step.
	 * @param array   $form_data Form data.
	 * @param array   $selected_template_data Template data.
	 * @param string  $method_export Method.
	 * @param integer $batch_offset Offset.
	 * @return type
	 */
	public function exporter_do_export( $export_data, $base, $step, $form_data, $selected_template_data, $method_export, $batch_offset ) {
		if ( $this->module_base != $base ) {
			return $export_data;
		}

		switch ( $method_export ) {
			case 'quick':
				$this->set_export_columns_for_quick_export( $form_data );
				break;

			case 'template':
			case 'new':
				$this->set_selected_column_names( $form_data );
				break;

			default:
				break;
		}

		include plugin_dir_path( __FILE__ ) . 'export/class-wt-import-export-for-woo-product-export.php';
		$export = new Wt_Import_Export_For_Woo_Product_Export( $this );

		$header_row = $export->prepare_header();

		$data_row = $export->prepare_data_to_export( $form_data, $batch_offset, $step );

		if ( 'export_image' == $step ) {
			$export_data = array(
				'total'  => $data_row['total'],
				'images' => $data_row['images'],
			);
		} else {
			$export_data = array(
				'head_data' => $header_row,
				'body_data' => $data_row['data'],
				'total'     => $data_row['total'],
			);

		}
		if ( isset( $data_row['no_post'] ) ) {
			$export_data['no_post'] = $data_row['no_post'];
		}

		return $export_data;
	}//end exporter_do_export()


	/**
	 * Adding current post type to export list
	 *
	 * @param array $arr Post types.
	 */
	public function wt_iew_exporter_post_types( $arr ) {
		$arr['product'] = __( 'Product' );
		return $arr;
	}//end wt_iew_exporter_post_types()


	/**
	 * Add/Remove steps in export section.
	 *
	 * @param  array  $steps array of built in steps.
	 * @param  string $base  product, order etc.
	 * @return array $steps
	 */
	public function wt_iew_exporter_steps( $steps, $base ) {
		if ( $base == $this->module_base ) {
			foreach ( $steps as $stepk => $stepv ) {
				$out[ $stepk ] = $stepv;
				/**
				 * Iif ( $stepk == 'filter' ) {

						$out['product']=array(
						'title'=>'Product',
						'description'=>'',
						);
				}*/
			}
		} else {
			$out = $steps;
		}

		return $out;
	}//end wt_iew_exporter_steps()


	/**
	 * Setting default export columns for quick export
	 *
	 * @param array $form_data Form data.
	 */
	public function set_export_columns_for_quick_export( $form_data ) {

		$post_columns = self::get_product_post_columns();

		$this->selected_column_names = array_combine( array_keys( $post_columns ), array_keys( $post_columns ) );

		if ( isset( $form_data['method_export_form_data']['mapping_enabled_fields'] ) && ! empty( $form_data['method_export_form_data']['mapping_enabled_fields'] ) ) {
			foreach ( $form_data['method_export_form_data']['mapping_enabled_fields'] as $value ) {
				$additional_quick_export_fields[ $value ] = array( 'fields' => array() );
			}

			$export_additional_columns = $this->exporter_alter_meta_mapping_fields( $additional_quick_export_fields, $this->module_base, array() );
			foreach ( $export_additional_columns as $value ) {
				$this->selected_column_names = array_merge( $this->selected_column_names, $value['fields'] );
			}
		}
	}//end set_export_columns_for_quick_export()


	/**
	 * Get product categories
	 *
	 * @return array $categories
	 */
	private function get_product_categories() {
		if ( ! is_null( $this->product_categories ) ) {
			return $this->product_categories;
		}

		$out = array();
		$product_categories = get_terms( 'product_cat' );
		if ( ! is_wp_error( $product_categories ) ) {
			$version = get_bloginfo( 'version' );
			foreach ( $product_categories as $category ) {
				$out[ $category->slug ] = ( ( $version < '4.8' ) ? $category->name : get_term_parents_list( $category->term_id, 'product_cat', array( 'separator' => ' -> ' ) ) );
			}
		}

		$this->product_categories = $out;
		return $out;
	}//end get_product_categories()

	/**
	 * Product tags
	 *
	 * @return array
	 */
	private function get_product_tags() {
		if ( ! is_null( $this->product_tags ) ) {
			return $this->product_tags;
		}

		$out          = array();
		$product_tags = get_terms( 'product_tag' );
		if ( ! is_wp_error( $product_tags ) ) {
			foreach ( $product_tags as $tag ) {
				$out[ $tag->slug ] = $tag->name;
			}
		}

		$this->product_tags = $out;
		return $out;
	}//end get_product_tags()

	/**
	 * Product types
	 *
	 * @return array
	 */
	public static function get_product_types() {
		// return include plugin_dir_path(__FILE__) . 'data/data-allowed-product-types.php';.
		$product_types = array();
		foreach ( wc_get_product_types() as $value => $label ) {
			$product_types[ esc_attr( $value ) ] = esc_html( $label );
		}

		return $product_types;
		return array_merge( $product_types, array( 'variation' => 'Product variations' ) );
	}//end get_product_types()

	/**
	 * Product status
	 *
	 * @return array
	 */
	public static function get_product_statuses() {
		$product_statuses = array(
			'publish',
			'private',
			'draft',
			'pending',
			'future',
		);
		/**
		 * Product status.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array   $statuses    Product statuses.
		 */
		return apply_filters( 'wt_iew_allowed_product_statuses', array_combine( $product_statuses, $product_statuses ) );
	}//end get_product_statuses()

	/**
	 * Sort columns
	 *
	 * @return array
	 */
	public static function get_product_sort_columns() {
		// $sort_columns = array('post_parent', 'ID', 'post_author', 'post_date', 'post_title', 'post_name', 'post_modified', 'menu_order', 'post_modified_gmt', 'rand', 'comment_count');
		$sort_columns = array(
			'ID'       => 'Product ID',
			'title'    => 'Product name',
			'type'     => 'Product type',
			'date'     => 'Created date',
			'modified' => 'Modified date',
		);
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array   $columns    Sort columns.
		 */
		return apply_filters( 'wt_iew_allowed_product_sort_columns', $sort_columns );
	}//end get_product_sort_columns()

	/**
	 * Post columns
	 *
	 * @return array
	 */
	public static function get_product_post_columns() {
		return include plugin_dir_path( __FILE__ ) . 'data/data-product-post-columns.php';
	}//end get_product_post_columns()

	/**
	 * Post columns
	 *
	 * @param array  $fields Fields.
	 * @param string $base Base.
	 * @param array  $step_page_form_data Form data.
	 * @return type
	 */
	public function get_importer_post_columns( $fields, $base, $step_page_form_data ) {
		if ( $base != $this->module_base ) {
			return $fields;
		}

		$colunm = include plugin_dir_path( __FILE__ ) . 'data/data/data-wf-reserved-fields-pair.php';
		// $colunm = array_map(function($vl){ return array('title'=>$vl, 'description'=>$vl); }, $arr);.
		return $colunm;
	}//end get_importer_post_columns()

	/**
	 * Mapping Enabled fields
	 *
	 * @param array  $mapping_enabled_fields Mapping Enabled fields.
	 * @param string $base Base.
	 * @param array  $form_data_mapping_enabled_fields Mapping Enabled fields.
	 * @return int
	 */
	public function exporter_alter_mapping_enabled_fields( $mapping_enabled_fields, $base, $form_data_mapping_enabled_fields ) {
		if ( $base == $this->module_base ) {
			$mapping_enabled_fields = array();
			$mapping_enabled_fields['taxonomies']  = array(
				__( 'Taxonomies (cat/tags/shipping-class)' ),
				1,
			);
			$mapping_enabled_fields['meta']        = array(
				__( 'Meta (custom fields)' ),
				1,
			);
			$mapping_enabled_fields['attributes']  = array(
				__( 'Attributes' ),
				1,
			);
			$mapping_enabled_fields['hidden_meta'] = array(
				__( 'Hidden meta' ),
				0,
			);
		}

		return $mapping_enabled_fields;
	}//end exporter_alter_mapping_enabled_fields()

	/**
	 * Mapping Enabled fields
	 *
	 * @param array  $fields Mapping Enabled fields.
	 * @param string $base Base.
	 * @param array  $step_page_form_data Mapping Enabled fields.
	 * @return string
	 */
	public function exporter_alter_meta_mapping_fields( $fields, $base, $step_page_form_data ) {
		if ( $base != $this->module_base ) {
			return $fields;
		}

		foreach ( $fields as $key => $value ) {
			switch ( $key ) {
				case 'taxonomies':
					$product_taxonomies = $this->wt_get_product_taxonomies();
					foreach ( $product_taxonomies as $taxonomy ) {
						if ( strstr( $taxonomy->name, 'pa_' ) ) {
							continue;
							// Skip attributes.
						}

						$fields[ $key ]['fields'][ 'tax:' . $taxonomy->name ] = 'tax:' . $taxonomy->name;
					}
					break;

				case 'meta':
					$meta_attributes    = array();
					$found_product_meta = $this->wt_get_found_product_meta();
					foreach ( $found_product_meta as $product_meta ) {
						if ( 'attribute_' == substr( $product_meta, 0, 10 ) ) {
							// Skipping attribute meta which will add on attribute section.
							$meta_attributes[] = $product_meta;
							continue;
						}

						$fields[ $key ]['fields'][ 'meta:' . $product_meta ] = 'meta:' . $product_meta;
					}
					break;

				case 'attributes':
					$found_attributes = $this->wt_get_product_attributes();

					if ( ! empty( $meta_attributes ) ) {
						// adding meta attributes.
						foreach ( $meta_attributes as $attribute_value ) {
							$fields[ $key ]['fields'][ 'meta:' . $attribute_value ] = 'meta:' . $attribute_value;
						}
					}

					foreach ( $found_attributes as $attribute ) {
						$fields[ $key ]['fields'][ 'attribute:' . $attribute ]         = 'attribute:' . $attribute;
						$fields[ $key ]['fields'][ 'attribute_data:' . $attribute ]    = 'attribute_data:' . $attribute;
						$fields[ $key ]['fields'][ 'attribute_default:' . $attribute ] = 'attribute_default:' . $attribute;
					}
					break;

				case 'hidden_meta':
					$found_product_hidden_meta = $this->wt_get_found_product_hidden_meta();
					foreach ( $found_product_hidden_meta as $product_meta ) {
						$fields[ $key ]['fields'][ 'meta:' . $product_meta ] = 'meta:' . $product_meta;
					}
					break;
				default:
					break;
			}//end switch
		}//end foreach

		return $fields;
	}//end exporter_alter_meta_mapping_fields()

	/**
	 * Mapping Enabled fields
	 *
	 * @param array  $fields Mapping Enabled fields.
	 * @param string $base Base.
	 * @param array  $step_page_form_data Mapping Enabled fields.
	 * @return type
	 */
	public function importer_alter_meta_mapping_fields( $fields, $base, $step_page_form_data ) {
		if ( $base != $this->module_base ) {
			return $fields;
		}

		$fields = $this->exporter_alter_meta_mapping_fields( $fields, $base, $step_page_form_data );
		$out    = array();
		foreach ( $fields as $key => $value ) {
			$value['fields'] = array_map(
				function ( $vl ) {
					return array(
						'title'       => $vl,
						'description' => $vl,
					);
				},
				$value['fields']
			);
			$out[ $key ]       = $value;
		}

		return $out;
	}//end importer_alter_meta_mapping_fields()

	/**
	 * Product taxonomies
	 *
	 * @return array
	 */
	public function wt_get_product_taxonomies() {

		if ( ! empty( $this->product_taxonomies ) ) {
			return $this->product_taxonomies;
		}

		$product_ptaxonomies = get_object_taxonomies( 'product', 'name' );
		$product_vtaxonomies = get_object_taxonomies( 'product_variation', 'name' );
		$product_taxonomies  = array_merge( $product_ptaxonomies, $product_vtaxonomies );

		$this->product_taxonomies = $product_taxonomies;
		return $this->product_taxonomies;
	}//end wt_get_product_taxonomies()

	/**
	 * Product meta
	 *
	 * @return array
	 */
	public function wt_get_found_product_meta() {

		if ( ! empty( $this->found_product_meta ) ) {
			return $this->found_product_meta;
		}

		// Loop products and load meta data.
		$found_product_meta = array();
		// Some of the values may not be usable (e.g. arrays of arrays) but the worse.
		// that can happen is we get an empty column.
		$all_meta_keys = $this->wt_get_all_meta_keys();
		$csv_columns   = self::get_product_post_columns();
		$exclude_hidden_meta_columns = $this->wt_get_exclude_hidden_meta_columns();
		foreach ( $all_meta_keys as $meta ) {
			if ( ! $meta || ( substr( (string) $meta, 0, 1 ) == '_' ) || in_array( $meta, $exclude_hidden_meta_columns ) || in_array( $meta, array_keys( $csv_columns ) ) || in_array( 'meta:' . $meta, array_keys( $csv_columns ) ) ) {
				continue;
			}

			$found_product_meta[] = $meta;
		}

		$found_product_meta = array_diff( $found_product_meta, array_keys( $csv_columns ) );

		$this->found_product_meta = $found_product_meta;
		return $this->found_product_meta;
	}//end wt_get_found_product_meta()

	/**
	 * Product hidden meta
	 *
	 * @return array
	 */
	public function wt_get_found_product_hidden_meta() {

		if ( ! empty( $this->found_product_hidden_meta ) ) {
			return $this->found_product_hidden_meta;
		}

		// Loop products and load meta data.
		$found_product_meta = array();
		// Some of the values may not be usable (e.g. arrays of arrays) but the worse.
		// that can happen is we get an empty column.
		$all_meta_keys = $this->wt_get_all_meta_keys();
		$csv_columns   = self::get_product_post_columns();
		// $this->get_selected_column_names();.
		$exclude_hidden_meta_columns = $this->wt_get_exclude_hidden_meta_columns();
		foreach ( $all_meta_keys as $meta ) {
			if ( ! $meta || ( substr( (string) $meta, 0, 1 ) != '_' ) || in_array( $meta, $exclude_hidden_meta_columns ) || in_array( $meta, array_keys( $csv_columns ) ) || in_array( 'meta:' . $meta, array_keys( $csv_columns ) ) ) {
				continue;
			}

			$found_product_meta[] = $meta;
		}

		$found_product_meta = array_diff( $found_product_meta, array_keys( $csv_columns ) );

		$this->found_product_hidden_meta = $found_product_meta;
		return $this->found_product_hidden_meta;
	}//end wt_get_found_product_hidden_meta()

	/**
	 * Product hidden meta columns
	 *
	 * @return array
	 */
	public function wt_get_exclude_hidden_meta_columns() {

		if ( ! empty( $this->exclude_hidden_meta_columns ) ) {
			return $this->exclude_hidden_meta_columns;
		}

		$exclude_hidden_meta_columns = include plugin_dir_path( __FILE__ ) . 'data/data-wf-hidden-meta-columns.php';

		$this->exclude_hidden_meta_columns = $exclude_hidden_meta_columns;
		return $this->exclude_hidden_meta_columns;
	}//end wt_get_exclude_hidden_meta_columns()

	/**
	 * All meta keys of products
	 *
	 * @return array
	 */
	public function wt_get_all_meta_keys() {

		if ( ! empty( $this->all_meta_keys ) ) {
			return $this->all_meta_keys;
		}

		$all_meta_keys = self::get_all_metakeys();

		$this->all_meta_keys = $all_meta_keys;
		return $this->all_meta_keys;
	}//end wt_get_all_meta_keys()


	/**
	 * Get a list of all the meta keys for a post type. This includes all public, private,
	 * used, no-longer used etc. They will be sorted once fetched.
	 */
	public static function get_all_metakeys() {
		global $wpdb;
		/**
		 * Exclude some internal and WooCommerce specific fields from being displayed in Hidden meta.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array   $exclude_meta_keys  Result data.
		 */
		$exclude_meta_keys = apply_filters(
			'wt_ier_product_exclude_meta_keys',
			array(
				// WP internals.
				'_edit_lock',
				'_edit_last',
				'_wp_old_date',
				// WC internals.
				'_downloadable_files',
				'_downloadable',
				'_sku',
				'_weight',
				'_width',
				'_height',
				'_length',
				'_file_path',
				'_file_paths',
				'_sale_price',
				'_regular_price',
				'_low_stock_amount',
				'_virtual',
				'_visibility',
				'_stock_status',
				'_tax_status',
				'_tax_class',
				'_sold_individually',
				'_stock',
				'_sale_price_dates_from',
				'_price',
				'_manage_stock',
			)
		);

		$meta = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT pm.meta_key
            FROM {$wpdb->postmeta} AS pm
            LEFT JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id
            WHERE p.post_type IN ( 'product', 'product_variation' ) 
			AND pm.meta_key NOT IN ( " . implode( ',', array_fill( 0, count( $exclude_meta_keys ), '%s' ) ) . ' )
			LIMIT 2000',
				$exclude_meta_keys
			)
		);

		sort( $meta );

		return $meta;
	}//end get_all_metakeys()

	/**
	 * Selected column names.
	 *
	 * @param array $full_form_data Form data.
	 * @return array
	 */
	public function set_selected_column_names( $full_form_data ) {
		if ( is_null( $this->selected_column_names ) ) {
			if ( isset( $full_form_data['mapping_form_data']['mapping_selected_fields'] ) && ! empty( $full_form_data['mapping_form_data']['mapping_selected_fields'] ) ) {
				$this->selected_column_names = $full_form_data['mapping_form_data']['mapping_selected_fields'];
			}

			if ( isset( $full_form_data['meta_step_form_data']['mapping_selected_fields'] ) && ! empty( $full_form_data['meta_step_form_data']['mapping_selected_fields'] ) ) {
				$export_additional_columns = $full_form_data['meta_step_form_data']['mapping_selected_fields'];
				foreach ( $export_additional_columns as $value ) {
					$this->selected_column_names = array_merge( $this->selected_column_names, $value );
				}
			}
		}

		return $full_form_data;
	}//end set_selected_column_names()

	/**
	 * Selected column names
	 *
	 * @return array
	 */
	public function get_selected_column_names() {

		return $this->selected_column_names;
	}//end get_selected_column_names()

	/**
	 * Get product attributes
	 *
	 * @return array
	 */
	public function wt_get_product_attributes() {
		if ( ! empty( $this->product_attributes ) ) {
			return $this->product_attributes;
		}

		$found_pattributes        = self::get_all_product_attributes( 'product' );
		$found_vattributes        = self::get_all_product_attributes( 'product_variation' );
		$found_attributes         = array_merge( $found_pattributes, $found_vattributes );
		$found_attributes         = array_unique( $found_attributes );
		$found_attributes         = array_map( 'rawurldecode', $found_attributes );
		$this->product_attributes = $found_attributes;
		return $this->product_attributes;
	}//end wt_get_product_attributes()


	/**
	 * Get a list of all the product attributes for a post type.
	 * These require a bit more digging into the values.
	 *
	 * @param string $post_type Post type.
	 */
	public static function get_all_product_attributes( $post_type = 'product' ) {

		global $wpdb;

		$results = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT pm.meta_value
            FROM {$wpdb->postmeta} AS pm
            LEFT JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id
            WHERE p.post_type = %s
            AND p.post_status IN ( 'publish', 'pending', 'private', 'draft' )
            AND pm.meta_key = '_product_attributes'",
				$post_type
			)
		);

		// Go through each result, and look at the attribute keys within them.
		$result = array();

		if ( ! empty( $results ) ) {
			foreach ( $results as $_product_attributes ) {
				$attributes = maybe_unserialize( maybe_unserialize( $_product_attributes ) );
				if ( ! empty( $attributes ) && is_array( $attributes ) ) {
					foreach ( $attributes as $key => $attribute ) {
						if ( ! $key ) {
							continue;
						}

						if ( ! strstr( $key, 'pa_' ) ) {
							if ( empty( $attribute['name'] ) ) {
								continue;
							}

							$key = $attribute['name'];
						}

						$result[ $key ] = $key;
					}
				}
			}
		}//end if

		sort( $result );

		return $result;
	}//end get_all_product_attributes()

	/**
	 * Export alter mapping fields
	 *
	 * @param array  $fields Mapping Enabled fields.
	 * @param string $base Base.
	 * @param array  $mapping_form_data Mapping Enabled fields.
	 * @return type
	 */
	public function exporter_alter_mapping_fields( $fields, $base, $mapping_form_data ) {
		if ( $base == $this->module_base ) {
			$fields = self::get_product_post_columns();
		}

		return $fields;
	}//end exporter_alter_mapping_fields()

	/**
	 * Export alter advanced fields
	 *
	 * @param array  $fields Mapping Enabled fields.
	 * @param string $base Base.
	 * @param array  $advanced_form_data Mapping Enabled fields.
	 * @return string
	 */
	public function exporter_alter_advanced_fields( $fields, $base, $advanced_form_data ) {

		if ( $this->module_base != $base ) {
			return $fields;
		}

		$out = array();
		$out['export_children_sku'] = array(
			'label'        => __( 'Export children SKU of grouped products', 'import-export-suite-for-woocommerce' ),
			'type'         => 'radio',
			'radio_fields' => array(
				'Yes' => __( 'Yes', 'import-export-suite-for-woocommerce' ),
				'No'  => __( 'No', 'import-export-suite-for-woocommerce' ),
			),
			'value'        => 'No',
			'field_name'   => 'export_children_sku',
			'help_text'    => __( "Option 'Yes' exports children of grouped products by their SKU. Default is Product ID.", 'import-export-suite-for-woocommerce' ),
		);
		foreach ( $fields as $fieldk => $fieldv ) {
			$out[ $fieldk ] = $fieldv;
		}

		// export images separately.
		$out['image_export'] = array(
			'label'       => __( 'Export images as zip file', 'import-export-suite-for-woocommerce' ),
			'type'        => 'image_export',
			'value'       => 'No',
			'field_name'  => 'image_export',
			'field_group' => 'advanced_field',
			/* translators: 1: HTML a tag open. 2: HTML a tag close */
			'help_text'   => sprintf( __( 'Downloads the product images in a separate zip file. Choose ‘Yes’, if you have to export huge data or if the process is slow. %1$sLearn More.%2$s', 'import-export-suite-for-woocommerce' ), '<a href="https://www.webtoffee.com/exporting-importing-woocommerce-products-images-with-zip-file/" target="_blank">', '</a>' ),
		);

		return $out;
	}//end exporter_alter_advanced_fields()

	/**
	 * Alter advanced fields
	 *
	 * @param array  $fields Mapping Enabled fields.
	 * @param string $base Base.
	 * @param array  $advanced_form_data Mapping Enabled fields.
	 * @return type
	 */
	public function importer_alter_advanced_fields( $fields, $base, $advanced_form_data ) {
		if ( $this->module_base != $base ) {
			return $fields;
		}

		$out = array();

		$out['skip_new'] = array(
			'label'                 => __( 'Skip import of new products', 'import-export-suite-for-woocommerce' ),
			'type'                  => 'radio',
			'radio_fields'          => array(
				'1' => __( 'Yes', 'import-export-suite-for-woocommerce' ),
				'0' => __( 'No', 'import-export-suite-for-woocommerce' ),
			),
			'value'                 => '0',
			'field_name'            => 'skip_new',
			'help_text_conditional' => array(
				array(
					'help_text' => __( 'Choosing ‘Yes’ will not import the new products from the input file.', 'import-export-suite-for-woocommerce' ),
					'condition' => array(
						array(
							'field' => 'wt_iew_skip_new',
							'value' => 1,
						),
					),
				),
				array(
					'help_text' => __( 'Choosing ‘No’ will import the new products from the input file.', 'import-export-suite-for-woocommerce' ),
					'condition' => array(
						array(
							'field' => 'wt_iew_skip_new',
							'value' => 0,
						),
					),
				),
			),
			'form_toggler'          => array(
				'type'   => 'parent',
				'target' => 'wt_iew_skip_new',
			),
		);

		$out['conflict_with_existing_post'] = array(
			'label'                 => __( 'If product ID conflicts with an existing Post ID', 'import-export-suite-for-woocommerce' ),
			'type'                  => 'radio',
			'radio_fields'          => array(
				'skip'   => __( 'Skip item', 'import-export-suite-for-woocommerce' ),
				'import' => __( 'Import as new item', 'import-export-suite-for-woocommerce' ),
			),
			'value'                 => 'skip',
			'field_name'            => 'id_conflict',
			// 'help_text' => __('Every post in the WooCommerce store is assigned a unique Post ID on creation. The post types could be: product, coupon, order, pages, media etc.', 'import-export-suite-for-woocommerce'),
			'help_text_conditional' => array(
				array(
					'help_text' => __( 'Skips the import of that particular product if there is a conflict in Post ID with an existing post.', 'import-export-suite-for-woocommerce' ),
					'condition' => array(
						array(
							'field' => 'wt_iew_id_conflict',
							'value' => 'skip',
						),
					),
				),
				array(
					'help_text' => __( 'This option will import the product with a new ID', 'import-export-suite-for-woocommerce' ),
					'condition' => array(
						array(
							'field' => 'wt_iew_id_conflict',
							'value' => 'import',
						),
					),
				),
			),
			'form_toggler'          => array(
				'type'  => 'child',
				'id'    => 'wt_iew_skip_new',
				'val'   => '0',
				'depth' => 1,
			// indicates the left margin of fields.
			),
		);

		$out['merge_with'] = array(
			'label'                 => __( 'Match products by their', 'import-export-suite-for-woocommerce' ),
			'type'                  => 'radio',
			'radio_fields'          => array(
				'id'  => __( 'ID' ),
				'sku' => __( 'SKU' ),
			),
			'value'                 => 'id',
			'field_name'            => 'merge_with',
			'help_text'             => __( 'The products are either looked up based on their ID or SKU as per the selection.', 'import-export-suite-for-woocommerce' ),
			'help_text_conditional' => array(
				array(
					'help_text' => __( 'Skips the product from being updated to the store, if the post ID of the imported product already exists for another post type.', 'import-export-suite-for-woocommerce' ),
					'condition' => array(
						array(
							'field' => 'wt_iew_merge_with',
							'value' => 'id',
						),
						'AND',
						array(
							'field' => 'wt_iew_skip_new',
							'value' => 1,
						),
					),
				),
				array(
					'help_text' => __( 'If the ID of a product in the input file is different from that of the product ID in site, then match products by SKU.', 'import-export-suite-for-woocommerce' ),
					'condition' => array(
						array(
							'field' => 'wt_iew_merge_with',
							'value' => 'sku',
						),
					),
				),
			),
		);

		$out['found_action_merge'] = array(
			'label'                 => __( 'If product exists in the store', 'import-export-suite-for-woocommerce' ),
			'type'                  => 'radio',
			'radio_fields'          => array(
				// 'import' => __('Import as new item'),
						'skip'   => __( 'Skip', 'import-export-suite-for-woocommerce' ),
				'update' => __( 'Update', 'import-export-suite-for-woocommerce' ),
			),
			'value'                 => 'skip',
			'field_name'            => 'found_action',
			'help_text_conditional' => array(
				array(
					'help_text' => __( 'This option will not update the existing products.', 'import-export-suite-for-woocommerce' ),
					'condition' => array(
						array(
							'field' => 'wt_iew_found_action',
							'value' => 'skip',
						),
					),
				),
				array(
					'help_text' => __( 'This option will update the existing products as per the data from the input file.', 'import-export-suite-for-woocommerce' ),
					'condition' => array(
						array(
							'field' => 'wt_iew_found_action',
							'value' => 'update',
						),
					),
				),
			),
			'form_toggler'          => array(
				'type'   => 'parent',
				'target' => 'wt_iew_found_action',
			),
		);

		$out['merge_empty_cells'] = array(
			'label'        => __( 'Update even if empty values', 'import-export-suite-for-woocommerce' ),
			'type'         => 'radio',
			'radio_fields' => array(
				'1' => __( 'Yes', 'import-export-suite-for-woocommerce' ),
				'0' => __( 'No', 'import-export-suite-for-woocommerce' ),
			),
			'value'        => '0',
			'field_name'   => 'merge_empty_cells',
			'help_text'    => __( 'Updates the product data respectively even if some of the columns in the input file contains empty value.', 'import-export-suite-for-woocommerce' ),
			'form_toggler' => array(
				'type' => 'child',
				'id'   => 'wt_iew_found_action',
				'val'  => 'update',
			),
		);

		$out['use_sku_upsell_crosssell'] = array(
			'label'        => __( 'Use SKU to link up-sells, cross-sells and grouped products', 'import-export-suite-for-woocommerce' ),
			'type'         => 'radio',
			'radio_fields' => array(
				'1' => __( 'Yes', 'import-export-suite-for-woocommerce' ),
				'0' => __( 'No', 'import-export-suite-for-woocommerce' ),
			),
			'value'        => '0',
			'field_name'   => 'use_sku_upsell_crosssell',
			'help_text'    => __( 'Select ‘Yes’ to import up-sells, cross-sells and grouped products using the product SKU.', 'import-export-suite-for-woocommerce' ),
		);

		$out['delete_existing'] = array(
			'label'        => __( 'Delete non-matching products from store', 'import-export-suite-for-woocommerce' ),
			'type'         => 'radio',
			'radio_fields' => array(
				'1' => __( 'Yes', 'import-export-suite-for-woocommerce' ),
				'0' => __( 'No', 'import-export-suite-for-woocommerce' ),
			),
			'value'        => '0',
			'field_name'   => 'delete_existing',
			'help_text'    => __( 'Select ‘Yes’ to remove products from your store which are not present in the input file.', 'import-export-suite-for-woocommerce' ),
		);

		foreach ( $fields as $fieldk => $fieldv ) {
			$out[ $fieldk ] = $fieldv;
		}

		return $out;
	}//end importer_alter_advanced_fields()


	/**
	 * Customize the items in filter export page
	 *
	 * @param array  $fields Fields.
	 * @param string $base Base.
	 * @param array  $filter_form_data Form data.
	 * @return string
	 */
	public function exporter_alter_filter_fields( $fields, $base, $filter_form_data ) {
		if ( $this->module_base != $base ) {
			return $fields;
		}

		// altering help text of default fields.
		$fields['limit']['label']      = __( 'Total number of products to export', 'import-export-suite-for-woocommerce' );
		$fields['limit']['help_text']  = __( 'Exports specified number of products. e.g. Entering 500 with a skip count of 10 will export products from 11th to 510th position.', 'import-export-suite-for-woocommerce' );
		$fields['offset']['label']     = __( 'Skip first <i>n</i> products', 'import-export-suite-for-woocommerce' );
		$fields['offset']['help_text'] = __( 'Skips specified number of products from the beginning of the database. e.g. Enter 10 to skip first 10 products from export.', 'import-export-suite-for-woocommerce' );

		$fields['product_types'] = array(
			'label'           => __( 'Product types', 'import-export-suite-for-woocommerce' ),
			'placeholder'     => __( 'All types', 'import-export-suite-for-woocommerce' ),
			'field_name'      => 'product_types',
			'sele_vals'       => self::get_product_types(),
			'help_text'       => __( 'Filter products by their type. You can export multiple types together.', 'import-export-suite-for-woocommerce' ),
			'type'            => 'multi_select',
			'css_class'       => 'wc-enhanced-select',
			'validation_rule' => array( 'type' => 'text_arr' ),
		);

		$fields['product'] = array(
			'label'           => __( 'Products', 'import-export-suite-for-woocommerce' ),
			'placeholder'     => __( 'All products', 'import-export-suite-for-woocommerce' ),
			'field_name'      => 'product',
			'sele_vals'       => array(),
			'help_text'       => __( 'Export specific products. Keyin the product names to export multiple products.', 'import-export-suite-for-woocommerce' ),
			'type'            => 'multi_select',
			'css_class'       => 'wc-product-search',
			'validation_rule' => array( 'type' => 'text_arr' ),
		);

		$fields['exclude_product'] = array(
			'label'           => __( 'Exclude Products', 'import-export-suite-for-woocommerce' ),
			'placeholder'     => __( 'Exclude Products', 'import-export-suite-for-woocommerce' ),
			'field_name'      => 'exclude_product',
			'sele_vals'       => array(),
			'help_text'       => __( 'Use this if you need to exclude a specific or multiple products from your export list.', 'import-export-suite-for-woocommerce' ),
			'type'            => 'multi_select',
			'css_class'       => 'wc-product-search',
			'validation_rule' => array( 'type' => 'text_arr' ),
		);

		$fields['product_categories'] = array(
			'label'           => __( 'Product categories', 'import-export-suite-for-woocommerce' ),
			'placeholder'     => __( 'Any category', 'import-export-suite-for-woocommerce' ),
			'field_name'      => 'product_categories',
			'sele_vals'       => $this->get_product_categories(),
			'help_text'       => __( 'Export products belonging to a particular or from multiple categories. Just select the respective categories.', 'import-export-suite-for-woocommerce' ),
			'type'            => 'multi_select',
			'css_class'       => 'wc-enhanced-select',
			'validation_rule' => array( 'type' => 'sanitize_title_with_dashes_arr' ),
		);

		$fields['product_tags'] = array(
			'label'           => __( 'Product tags', 'import-export-suite-for-woocommerce' ),
			'placeholder'     => __( 'Any tag', 'import-export-suite-for-woocommerce' ),
			'field_name'      => 'product_tags',
			'sele_vals'       => $this->get_product_tags(),
			'help_text'       => __( 'Enter the product tags to export only the respective products that have been tagged accordingly.', 'import-export-suite-for-woocommerce' ),
			'type'            => 'multi_select',
			'css_class'       => 'wc-enhanced-select',
			'validation_rule' => array( 'type' => 'sanitize_title_with_dashes_arr' ),
		);

		$fields['product_status'] = array(
			'label'           => __( 'Product status', 'import-export-suite-for-woocommerce' ),
			'placeholder'     => __( 'All status', 'import-export-suite-for-woocommerce' ),
			'field_name'      => 'product_status',
			'sele_vals'       => self::get_product_statuses(),
			'help_text'       => __( 'Filter products by their status.', 'import-export-suite-for-woocommerce' ),
			'type'            => 'multi_select',
			'css_class'       => 'wc-enhanced-select',
			'validation_rule' => array( 'type' => 'text_arr' ),
		);

		$sort_columns           = self::get_product_sort_columns();
		$fields['sort_columns'] = array(
			'label'           => __( 'Sort Columns', 'import-export-suite-for-woocommerce' ),
			'placeholder'     => __( 'ID' ),
			'field_name'      => 'sort_columns',
			'sele_vals'       => $sort_columns,
			'help_text'       => __( 'Sort the exported data based on the selected columns in order specified. Defaulted to ID.', 'import-export-suite-for-woocommerce' ),
			'type'            => 'select',
			'validation_rule' => array( 'type' => 'text_arr' ),
		);
		$fields['order_by']     = array(
			'label'       => __( 'Sort By', 'import-export-suite-for-woocommerce' ),
			'placeholder' => __( 'ASC' ),
			'field_name'  => 'order_by',
			'sele_vals'   => array(
				'ASC'  => 'Ascending',
				'DESC' => 'Descending',
			),
			'help_text'   => __( 'Defaulted to Ascending. Applicable to above selected columns in the order specified.', 'import-export-suite-for-woocommerce' ),
			'type'        => 'select',
		);

		return $fields;
	}//end exporter_alter_filter_fields()


	/**
	 * Get info like language code, parent product ID etc by product id.
	 *
	 * @param  int $element_id Product ID.
	 * @return array/false.
	 */
	public static function wt_get_wpml_original_post_language_info( $element_id ) {
		$get_language_args           = array(
			'element_id'   => $element_id,
			'element_type' => 'post_product',
		);
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param string  $language_details     WPML lang.
		 * @param array   $get_language_args    Get WPML language argument.
		 */
		$original_post_language_info = apply_filters( 'wpml_element_language_details', null, $get_language_args );
		return $original_post_language_info;
	}//end wt_get_wpml_original_post_language_info()

	/**
	 * Get product ID by sku
	 *
	 * @global type $wpdb
	 * @param string $sku SKU of the product.
	 * @return boolean
	 */
	public static function wt_get_product_id_by_sku( $sku ) {
		global $wpdb;
		$post_exists_sku = $wpdb->get_var(
			$wpdb->prepare(
				"
	    		SELECT $wpdb->posts.ID
	    		FROM $wpdb->posts
	    		LEFT JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
	    		WHERE $wpdb->posts.post_status IN ( 'publish', 'private', 'draft', 'pending', 'future' )
	    		AND $wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value = %s
	    		",
				$sku
			)
		);
		if ( $post_exists_sku ) {
			return $post_exists_sku;
		}

		return false;
	}//end wt_get_product_id_by_sku()


	/**
	 * To strip the specific string from the array key as well as value.
	 *
	 * @param  array  $array Walking array.
	 * @param  string $data Data array.
	 * @return array.
	 */
	public static function wt_array_walk( $array, $data ) {
		$new_array = array();
		foreach ( $array as $key => $value ) {
			$new_array[ str_replace( $data, '', $key ) ] = str_replace( $data, '', $value );
		}

		return $new_array;
	}//end wt_array_walk()

	/**
	 * Get item link
	 *
	 * @param type $id ID.
	 * @return type
	 */
	public function get_item_by_id( $id ) {
		$post['title']    = get_the_title( $id );
		$product = wc_get_product( $id );
		if ( is_object( $product ) && $product->is_type( 'variation' ) ) {
			$id = $product->get_parent_id();
		}
		$post['edit_url'] = get_edit_post_link( $id );

		return $post;
	}//end get_item_by_id()

	/**
	 *  Add product import help content to help section
	 */
	public function wt_product_import_export_help_content() {
		if ( defined( 'WT_IEW_PLUGIN_ID' ) ) {
			?>
			<li>
				<img src="<?php echo esc_url( WT_IEW_PLUGIN_URL ); ?>assets/images/sample-csv.png">
				<h3><?php esc_html_e( 'Sample Product CSV', 'import-export-suite-for-woocommerce' ); ?></h3>
				<p><?php esc_html_e( 'Familiarize yourself with the sample CSV.', 'import-export-suite-for-woocommerce' ); ?></p>
				<a target="_blank" href="https://www.webtoffee.com/wp-content/uploads/2021/04/Product_SampleCSV-.csv" class="button button-primary">
				<?php esc_html_e( 'Get Product CSV', 'import-export-suite-for-woocommerce' ); ?>        
				</a>
			</li>
			<?php
		}
	}//end wt_product_import_export_help_content()
}//end class


new Wt_Import_Export_For_Woo_Product();
