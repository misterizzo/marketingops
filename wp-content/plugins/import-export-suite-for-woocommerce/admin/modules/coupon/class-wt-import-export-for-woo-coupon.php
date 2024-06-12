<?php
/**
 * Handles the coupon actions.
 *
 * @package   ImportExportSuite\Admin\Modules\Coupon
 * @version   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_Coupon Class.
 */
class Wt_Import_Export_For_Woo_Coupon {
	/**
	 * Module id - coupon
	 *
	 * @var string
	 */
	public $module_id = '';
	/**
	 * Module name - coupon
	 *
	 * @var string
	 */
	public static $module_id_static = '';
	/**
	 * Module id - coupon
	 *
	 * @var string
	 */
	public $module_base = 'coupon';
	/**
	 * Module id - coupon
	 *
	 * @var string
	 */
	public $module_name = 'Coupon Import Export for WooCommerce';
	/**
	 * Module id -  Minimum `Import export plugin` required to run this add on plugin.
	 *
	 * @var string
	 */
	public $min_base_version = '1.0.0';
	/**
	 * Module id - coupon
	 *
	 * @var string
	 */
	private $importer = null;
	/**
	 * Module id - coupon
	 *
	 * @var string
	 */
	private $exporter = null;
	/**
	 * Module id - coupon
	 *
	 * @var string
	 */
	private $all_meta_keys = array();
	/**
	 * Module id - coupon
	 *
	 * @var string
	 */
	private $found_meta = array();
	/**
	 * Module id - coupon
	 *
	 * @var string
	 */
	private $found_hidden_meta = array();
	/**
	 * Module id - coupon
	 *
	 * @var string
	 */
	private $selected_column_names = null;
	/**
	 * Constructor.
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

		$this->module_id = Wt_Import_Export_For_Woo::get_module_id( $this->module_base );
		self::$module_id_static = $this->module_id;

		add_filter( 'wt_iew_exporter_post_types', array( $this, 'wt_iew_exporter_post_types' ), 10, 1 );
		add_filter( 'wt_iew_importer_post_types', array( $this, 'wt_iew_exporter_post_types' ), 10, 1 );

		add_filter( 'wt_iew_exporter_alter_filter_fields', array( $this, 'exporter_alter_filter_fields' ), 10, 3 );

		add_filter( 'wt_iew_exporter_alter_mapping_fields', array( $this, 'exporter_alter_mapping_fields' ), 10, 3 );
		add_filter( 'wt_iew_importer_alter_mapping_fields', array( $this, 'get_importer_post_columns' ), 10, 3 );

		add_filter( 'wt_iew_importer_alter_advanced_fields', array( $this, 'importer_alter_advanced_fields' ), 10, 3 );

		add_filter( 'wt_iew_exporter_alter_meta_mapping_fields', array( $this, 'exporter_alter_meta_mapping_fields' ), 10, 3 );
		add_filter( 'wt_iew_importer_alter_meta_mapping_fields', array( $this, 'importer_alter_meta_mapping_fields' ), 10, 3 );

		add_filter( 'wt_iew_exporter_alter_mapping_enabled_fields', array( $this, 'exporter_alter_mapping_enabled_fields' ), 10, 3 );
		add_filter( 'wt_iew_importer_alter_mapping_enabled_fields', array( $this, 'exporter_alter_mapping_enabled_fields' ), 10, 3 );

		add_filter( 'wt_iew_exporter_do_export', array( $this, 'exporter_do_export' ), 10, 7 );
		add_filter( 'wt_iew_importer_do_import', array( $this, 'importer_do_import' ), 10, 8 );

		add_filter( 'wt_iew_importer_steps', array( $this, 'importer_steps' ), 10, 2 );

		add_action( 'wt_coupon_addon_help_content', array( $this, 'wt_coupon_import_export_help_content' ) );
	}//end __construct()

	/**
	 * Altering advanced step description
	 *
	 * @since 1.0.0
	 * @param string $steps Steps.
	 * @param object $base Module base.
	 */
	public function importer_steps( $steps, $base ) {
		if ( $this->module_base == $base ) {
			$steps['advanced']['description'] = __( 'Use advanced options from below to decide updates to existing coupons, batch import count or schedule an import. You can also save the template file for future imports.', 'import-export-suite-for-woocommerce' );
		}

		return $steps;
	}//end importer_steps()

	/**
	 * Importing action initiate prepare data
	 *
	 * @since 1.0.0
	 * @param array   $import_data Import data.
	 * @param object  $base Module base.
	 * @param string  $step Current step.
	 * @param array   $form_data Form data.
	 * @param array   $selected_template_data Selected template data.
	 * @param string  $method_import Import method.
	 * @param integer $batch_offset Batch offset.
	 * @param bool    $is_last_batch Is last batch.
	 */
	public function importer_do_import( $import_data, $base, $step, $form_data, $selected_template_data, $method_import, $batch_offset, $is_last_batch ) {
		if ( $this->module_base != $base ) {
			return $import_data;
		}

		if ( 0 == $batch_offset ) {
			$memory = size_format( Wt_Import_Export_For_Woo_Common_Helper::wt_let_to_num( ini_get( 'memory_limit' ) ) );
			$wp_memory = size_format( Wt_Import_Export_For_Woo_Common_Helper::wt_let_to_num( WP_MEMORY_LIMIT ) );
			Wt_Import_Export_For_Woo_Logwriter::write_log( $this->module_base, 'import', '---[ New import started at ' . gmdate( 'Y-m-d H:i:s' ) . ' ] PHP Memory: ' . $memory . ', WP Memory: ' . $wp_memory );
		}

		include plugin_dir_path( __FILE__ ) . 'import/class-wt-import-export-for-woo-coupon-import.php';
		$import = new Wt_Import_Export_For_Woo_Coupon_Import( $this );

		$response = $import->prepare_data_to_import( $import_data, $form_data, $batch_offset, $is_last_batch );

		if ( $is_last_batch ) {
			Wt_Import_Export_For_Woo_Logwriter::write_log( $this->module_base, 'import', '---[ Import ended at ' . gmdate( 'Y-m-d H:i:s' ) . ']---' );
		}

		return $response;
	}//end importer_do_import()
	/**
	 * Exporting action initiate prepare data
	 *
	 * @since 1.0.0
	 * @param array   $export_data Export data.
	 * @param object  $base Module base.
	 * @param string  $step Current step.
	 * @param array   $form_data Form data.
	 * @param array   $selected_template_data Selected template data.
	 * @param string  $method_export Export method.
	 * @param integer $batch_offset Batch offset.
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

		include plugin_dir_path( __FILE__ ) . 'export/class-wt-import-export-for-woo-coupon-export.php';
		$export = new Wt_Import_Export_For_Woo_Coupon_Export( $this );

		$header_row = $export->prepare_header();

		$data_row = $export->prepare_data_to_export( $form_data, $batch_offset );

		$export_data = array(
			'head_data' => $header_row,
			'body_data' => $data_row['data'],
			'total' => $data_row['total'],
		);

		if ( isset( $data_row['no_post'] ) ) {
			$export_data['no_post'] = $data_row['no_post'];
		}

		return $export_data;
	}//end exporter_do_export()

	/**
	 * Setting default export columns for quick export
	 *
	 * @since 1.0.0
	 * @param array $form_data Form data.
	 */
	public function set_export_columns_for_quick_export( $form_data ) {

		$post_columns = self::get_coupon_post_columns();

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
	 * Adding current post type to export list
	 *
	 * @since 1.0.0
	 * @param array $arr Post types.
	 */
	public function wt_iew_exporter_post_types( $arr ) {
		$arr['coupon'] = __( 'Coupon' );
		return $arr;
	}//end wt_iew_exporter_post_types()
	/**
	 * Get available coupon types.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function get_coupon_types() {
		$coupon_types = wc_get_coupon_types();
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array     $coupon_types  Coupon types.
		 */
		return apply_filters( 'wt_iew_export_coupon_types', $coupon_types );
	}//end get_coupon_types()
	/**
	 * Get available coupon statuses.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function get_coupon_statuses() {
		$statuses = array(
			'publish',
			'private',
			'draft',
			'pending',
			'future',
		);
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array     $statuses  Coupon statuses.
		 */
		return apply_filters( 'wt_iew_export_coupon_statuses', array_combine( $statuses, $statuses ) );
	}//end get_coupon_statuses()
	/**
	 * Get available sort columns.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function get_coupon_sort_columns() {
		$sort_columns = array(
			'ID',
			'post_parent',
			'post_title',
			'post_date',
			'post_modified',
			'post_author',
			'menu_order',
			'comment_count',
		);
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array   $sort_columns    Sort columns.
		 */
		return apply_filters( 'wt_iew_export_coupon_sort_columns', array_combine( $sort_columns, $sort_columns ) );
	}//end get_coupon_sort_columns()
	/**
	 * Get available coupon post columns.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function get_coupon_post_columns() {
		return include plugin_dir_path( __FILE__ ) . 'data/data-coupon-post-columns.php';
	}//end get_coupon_post_columns()

	/**
	 * Get available coupon post columns.
	 *
	 * @since 1.0.0
	 * @param array  $fields Post columns.
	 * @param object $base Base object.
	 * @param array  $step_page_form_data Form data.
	 * @return array
	 */
	public function get_importer_post_columns( $fields, $base, $step_page_form_data ) {
		if ( $base != $this->module_base ) {
			return $fields;
		}

		$colunm = include plugin_dir_path( __FILE__ ) . 'data/data/data-wf-reserved-fields-pair.php';
		// $colunm = array_map(function($vl){ return array('title'=>$vl, 'description'=>$vl); }, $arr);
		return $colunm;
	}//end get_importer_post_columns()

	/**
	 * Mapping enabled columns.
	 *
	 * @since 1.0.0
	 * @param array  $mapping_enabled_fields Mapping enabled columns.
	 * @param object $base Base object.
	 * @param array  $form_data_mapping_enabled_fields Form data mapping enabled columns.
	 * @return array
	 */
	public function exporter_alter_mapping_enabled_fields( $mapping_enabled_fields, $base, $form_data_mapping_enabled_fields ) {
		if ( $base == $this->module_base ) {
			$mapping_enabled_fields = array();
			$mapping_enabled_fields['meta'] = array(
				__( 'Additional meta', 'import-export-suite-for-woocommerce' ),
				1,
			);
			$mapping_enabled_fields['hidden_meta'] = array(
				__( 'Hidden meta', 'import-export-suite-for-woocommerce' ),
				0,
			);
		}

		return $mapping_enabled_fields;
	}//end exporter_alter_mapping_enabled_fields()

	/**
	 * Meta mapping fields.
	 *
	 * @since 1.0.0
	 * @param array  $fields Post columns.
	 * @param object $base Base object.
	 * @param array  $step_page_form_data Form data.
	 * @return array
	 */
	public function exporter_alter_meta_mapping_fields( $fields, $base, $step_page_form_data ) {
		if ( $base != $this->module_base ) {
			return $fields;
		}

		foreach ( $fields as $key => $value ) {
			switch ( $key ) {
				case 'meta':
					$meta_attributes = array();
					$found_meta = $this->wt_get_found_meta();
					foreach ( $found_meta as $meta ) {
						$fields[ $key ]['fields'][ 'meta:' . $meta ] = 'meta:' . $meta;
					}
					break;

				case 'hidden_meta':
					$found_hidden_meta = $this->wt_get_found_hidden_meta();
					foreach ( $found_hidden_meta as $hidden_meta ) {
						$fields[ $key ]['fields'][ 'meta:' . $hidden_meta ] = 'meta:' . $hidden_meta;
					}
					break;
				default:
					break;
			}
		}//end foreach

		return $fields;
	}//end exporter_alter_meta_mapping_fields()

	/**
	 * Meta mapping fields.
	 *
	 * @since 1.0.0
	 * @param array  $fields Post columns.
	 * @param object $base Base object.
	 * @param array  $step_page_form_data Form data.
	 * @return array
	 */
	public function importer_alter_meta_mapping_fields( $fields, $base, $step_page_form_data ) {
		if ( $base != $this->module_base ) {
			return $fields;
		}

		$fields = $this->exporter_alter_meta_mapping_fields( $fields, $base, $step_page_form_data );
		$out = array();
		foreach ( $fields as $key => $value ) {
			$value['fields'] = array_map(
				function ( $vl ) {
					return array(
						'title' => $vl,
						'description' => $vl,
					);
				},
				$value['fields']
			);
			$out[ $key ] = $value;
		}

		return $out;
	}//end importer_alter_meta_mapping_fields()

	/**
	 * Get meta fields.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function wt_get_found_meta() {

		if ( ! empty( $this->found_meta ) ) {
			return $this->found_meta;
		}

		// Loop products and load meta data.
		$found_meta = array();
		// Some of the values may not be usable (e.g. arrays of arrays) but the worse.
		// that can happen is we get an empty column.
		$all_meta_keys = $this->wt_get_all_meta_keys();

		$csv_columns = self::get_coupon_post_columns();

		foreach ( $all_meta_keys as $meta ) {
			if ( ! $meta || ( substr( (string) $meta, 0, 1 ) == '_' ) || in_array( $meta, array_keys( $csv_columns ) ) || in_array( 'meta:' . $meta, array_keys( $csv_columns ) ) ) {
				continue;
			}

			$found_meta[] = $meta;
		}

		$found_meta = array_diff( $found_meta, array_keys( $csv_columns ) );
		$this->found_meta = $found_meta;
		return $this->found_meta;
	}//end wt_get_found_meta()

	/**
	 * Get hidden meta fields.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function wt_get_found_hidden_meta() {

		if ( ! empty( $this->found_hidden_meta ) ) {
			return $this->found_hidden_meta;
		}

		// Loop products and load meta data.
		$found_hidden_meta = array();
		// Some of the values may not be usable (e.g. arrays of arrays) but the worse.
		// that can happen is we get an empty column.
		$all_meta_keys = $this->wt_get_all_meta_keys();
		$csv_columns = self::get_coupon_post_columns();
		foreach ( $all_meta_keys as $meta ) {
			if ( ! $meta || ( substr( (string) $meta, 0, 1 ) != '_' ) || in_array( $meta, array_keys( $csv_columns ) ) || in_array( 'meta:' . $meta, array_keys( $csv_columns ) ) ) {
				continue;
			}

			$found_hidden_meta[] = $meta;
		}

		$found_hidden_meta = array_diff( $found_hidden_meta, array_keys( $csv_columns ) );

		$this->found_hidden_meta = $found_hidden_meta;
		return $this->found_hidden_meta;
	}//end wt_get_found_hidden_meta()

	/**
	 * Get all meta keys.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function wt_get_all_meta_keys() {

		if ( ! empty( $this->all_meta_keys ) ) {
			return $this->all_meta_keys;
		}

		$all_meta_keys = self::get_all_metakeys( 'shop_coupon' );

		$this->all_meta_keys = $all_meta_keys;
		return $this->all_meta_keys;
	}//end wt_get_all_meta_keys()

	/**
	 * Get all meta fields.
	 *
	 * @since 1.0.0
	 * @param string $post_type coupon.
	 * @return array
	 */
	public static function get_all_metakeys( $post_type = 'shop_coupon' ) {
		global $wpdb;

		$meta = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT pm.meta_key
            FROM {$wpdb->postmeta} AS pm
            LEFT JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id
            WHERE p.post_type = %s
            AND p.post_status IN ( 'publish', 'pending', 'private', 'draft' ) LIMIT 2010",
				$post_type
			)
		);

		sort( $meta );

		return $meta;
	}//end get_all_metakeys()

	/**
	 * Get selected column names.
	 *
	 * @since 1.0.0
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
	 * Get selected column names.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_selected_column_names() {

		return $this->selected_column_names;
	}//end get_selected_column_names()

	/**
	 * Post mapping fields.
	 *
	 * @since 1.0.0
	 * @param array  $fields Post columns.
	 * @param object $base Base object.
	 * @param array  $mapping_form_data Form data.
	 * @return array
	 */
	public function exporter_alter_mapping_fields( $fields, $base, $mapping_form_data ) {
		if ( $base == $this->module_base ) {
			$fields = self::get_coupon_post_columns();
		}

		return $fields;
	}//end exporter_alter_mapping_fields()

	/**
	 * Post mapping advanced fields.
	 *
	 * @since 1.0.0
	 * @param array  $fields Post columns.
	 * @param object $base Base object.
	 * @param array  $advanced_form_data Form data.
	 * @return array
	 */
	public function importer_alter_advanced_fields( $fields, $base, $advanced_form_data ) {
		if ( $this->module_base != $base ) {
			return $fields;
		}

		$out = array();

		$out['skip_new'] = array(
			'label' => __( 'Update Only', 'import-export-suite-for-woocommerce' ),
			'type' => 'radio',
			'radio_fields' => array(
				'1' => __( 'Yes', 'import-export-suite-for-woocommerce' ),
				'0' => __( 'No', 'import-export-suite-for-woocommerce' ),
			),
			'value' => '0',
			'field_name' => 'skip_new',
			'help_text_conditional' => array(
				array(
					'help_text' => __( 'The store is updated with the data from the input file only for matching/existing records from the file.', 'import-export-suite-for-woocommerce' ),
					'condition' => array(
						array(
							'field' => 'wt_iew_skip_new',
							'value' => 1,
						),
					),
				),
				array(
					'help_text' => __( 'The entire data from the input file is processed for an update or insert as the case maybe.', 'import-export-suite-for-woocommerce' ),
					'condition' => array(
						array(
							'field' => 'wt_iew_skip_new',
							'value' => 0,
						),
					),
				),
			),
			'form_toggler' => array(
				'type' => 'parent',
				'target' => 'wt_iew_skip_new',
			),
		);

		$out['merge_with'] = array(
			'label' => __( 'Match coupons by their', 'import-export-suite-for-woocommerce' ),
			'type' => 'radio',
			'radio_fields' => array(
				'id' => __( 'ID' ),
				'code' => __( 'Coupon Code' ),
			),
			'value' => 'id',
			'field_name' => 'merge_with',
			// 'help_text' => __('The coupons are either looked up based on their ID or coupon code as per the selection.', 'import-export-suite-for-woocommerce'),
			'help_text_conditional' => array(
				array(
					'help_text' => __( 'If the post ID of the coupon being imported exists already for any of the other post types, skip the coupon from being inserted into the store.', 'import-export-suite-for-woocommerce' ),
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
					'help_text' => __( 'The coupons will be imported on the basis of their coupon code.', 'import-export-suite-for-woocommerce' ),
					'condition' => array(
						array(
							'field' => 'wt_iew_merge_with',
							'value' => 'code',
						),
						'AND',
						array(
							'field' => 'wt_iew_skip_new',
							'value' => 1,
						),
					),
				),
			),
		);

		$out['found_action_merge'] = array(
			'label' => __( 'If the coupon exists in the store', 'import-export-suite-for-woocommerce' ),
			'type' => 'radio',
			'radio_fields' => array(
				// 'import' => __('Import as new item'),
				'skip' => __( 'Skip', 'import-export-suite-for-woocommerce' ),
				'update' => __( 'Update', 'import-export-suite-for-woocommerce' ),
			),
			'value' => 'skip',
			'field_name' => 'found_action',
			'help_text_conditional' => array(
				array(
					'help_text' => __( 'Retains the coupon in the store as is and skips the matching coupon from the input file.', 'import-export-suite-for-woocommerce' ),
					'condition' => array(
						array(
							'field' => 'wt_iew_found_action',
							'value' => 'skip',
						),
					),
				),
				array(
					'help_text' => __( 'Update coupon as per data from the input file', 'import-export-suite-for-woocommerce' ),
					'condition' => array(
						array(
							'field' => 'wt_iew_found_action',
							'value' => 'update',
						),
					),
				),
			),
			'form_toggler' => array(
				'type' => 'parent',
				'target' => 'wt_iew_found_action',
			),
		);

		$out['merge_empty_cells'] = array(
			'label' => __( 'Update even if empty values', 'import-export-suite-for-woocommerce' ),
			'type' => 'radio',
			'radio_fields' => array(
				'1' => __( 'Yes', 'import-export-suite-for-woocommerce' ),
				'0' => __( 'No', 'import-export-suite-for-woocommerce' ),
			),
			'value' => '0',
			'field_name' => 'merge_empty_cells',
			'help_text' => __( 'Updates the coupon data respectively even if some of the columns in the input file contains empty value.', 'import-export-suite-for-woocommerce' ),
			'form_toggler' => array(
				'type' => 'child',
				'id' => 'wt_iew_found_action',
				'val' => 'update',
			),
		);

		$out['conflict_with_existing_post'] = array(
			'label' => __( 'If conflict with an existing Post ID', 'import-export-suite-for-woocommerce' ),
			'type' => 'radio',
			'radio_fields' => array(
				'skip' => __( 'Skip item', 'import-export-suite-for-woocommerce' ),
				'import' => __( 'Import as new item', 'import-export-suite-for-woocommerce' ),
			),
			'value' => 'skip',
			'field_name' => 'id_conflict',
			// 'help_text' => __('All the items within WooCommerce/WordPress are treated as posts and assigned a unique ID as and when they are created in the store. The post ID uniquely identifies an item irrespective of the post type be it coupon/product/pages/attachments/revisions etc.', 'import-export-suite-for-woocommerce'),
			'help_text_conditional' => array(
				array(
					'help_text' => __( 'If the post ID of the coupon being imported exists already(for any of the posts like coupon, order, user, pages, media etc) skip the coupon from being inserted into the store.', 'import-export-suite-for-woocommerce' ),
					'condition' => array(
						array(
							'field' => 'wt_iew_id_conflict',
							'value' => 'skip',
						),
					),
				),
				array(
					'help_text' => __( 'Insert the coupon into the store with a new coupon ID(next available post ID) different from the value in the input file.', 'import-export-suite-for-woocommerce' ),
					'condition' => array(
						array(
							'field' => 'wt_iew_id_conflict',
							'value' => 'import',
						),
					),
				),
			),
			'form_toggler' => array(
				'type' => 'child',
				'id' => 'wt_iew_skip_new',
				'val' => '0',
				'depth' => 0,
			),
		);

		$out['delete_existing'] = array(
			'label' => __( 'Delete non-matching coupons from store', 'import-export-suite-for-woocommerce' ),
			'type' => 'radio',
			'radio_fields' => array(
				'1' => __( 'Yes', 'import-export-suite-for-woocommerce' ),
				'0' => __( 'No', 'import-export-suite-for-woocommerce' ),
			),
			'value' => '0',
			'field_name' => 'delete_existing',
			'help_text' => __( 'Select ‘Yes’ if you need to remove the coupons from your store which are not present in the input file.', 'import-export-suite-for-woocommerce' ),
		);

		$out['use_sku'] = array(
			'label' => __( 'Use product SKU for coupon restriction settings', 'import-export-suite-for-woocommerce' ),
			'type' => 'radio',
			'radio_fields' => array(
				'1' => __( 'Yes', 'import-export-suite-for-woocommerce' ),
				'0' => __( 'No', 'import-export-suite-for-woocommerce' ),
			),
			'value' => '0',
			'field_name' => 'use_sku',
			'help_text_conditional' => array(
				array(
					/* translators: %s: html bold */
					'help_text' => sprintf( __( 'Link the products by their product IDs under coupon restrictions for the imported coupons.  This option is %1$snot recommended in cases of conflicting IDs%2$s.', 'import-export-suite-for-woocommerce' ), '<b>', '</b>' ),
					'condition' => array(
						array(
							'field' => 'wt_iew_use_sku',
							'value' => 1,
						),
					),
				),
				array(
					'help_text' => __( 'Link the products by their product IDs under coupon restrictions for the imported coupons. In case of a conflict with IDs of other existing post types the link will be empty.', 'import-export-suite-for-woocommerce' ),
					'condition' => array(
						array(
							'field' => 'wt_iew_use_sku',
							'value' => 0,
						),
					),
				),
			),
		);

		foreach ( $fields as $fieldk => $fieldv ) {
			$out[ $fieldk ] = $fieldv;
		}

		return $out;
	}//end importer_alter_advanced_fields()

	/**
	 * Post filter fields.
	 *
	 * @since 1.0.0
	 * @param array  $fields Post columns.
	 * @param object $base Base object.
	 * @param array  $filter_form_data Form data.
	 * @return array
	 */
	public function exporter_alter_filter_fields( $fields, $base, $filter_form_data ) {

		if ( $base == $this->module_base ) {
			// altering help text of default fields.
			$fields['limit']['label'] = __( 'Total number of coupons to export', 'import-export-suite-for-woocommerce' );
			$fields['limit']['help_text'] = __( 'Exports specified number of coupons. e.g. Entering 500 with a skip count of 10 will export coupons from 11th to 510th position.', 'import-export-suite-for-woocommerce' );
			$fields['offset']['label'] = __( 'Skip first <i>n</i> coupons', 'import-export-suite-for-woocommerce' );
			$fields['offset']['help_text'] = __( 'Skips specified number of coupons from the beginning of the database. e.g. Enter 10 to skip first 10 coupons from export.', 'import-export-suite-for-woocommerce' );

			$fields['statuses'] = array(
				'label' => __( 'Coupon Statuses', 'import-export-suite-for-woocommerce' ),
				'placeholder' => __( 'All Statuses', 'import-export-suite-for-woocommerce' ),
				'field_name' => 'statuses',
				'sele_vals' => self::get_coupon_statuses(),
				'help_text' => __( 'Export coupons by their status. You can specify more than one status if required.', 'import-export-suite-for-woocommerce' ),
				'type' => 'multi_select',
				'css_class' => 'wc-enhanced-select',
				'validation_rule' => array( 'type' => 'text_arr' ),
			);
			$fields['types'] = array(
				'label' => __( 'Coupon Type', 'import-export-suite-for-woocommerce' ),
				'placeholder' => __( 'All Types', 'import-export-suite-for-woocommerce' ),
				'field_name' => 'types',
				'sele_vals' => self::get_coupon_types(),
				'help_text' => __( 'Select the coupon type e.g, fixed cart, recurring etc to export only coupon of a specific type.', 'import-export-suite-for-woocommerce' ),
				'type' => 'multi_select',
				'css_class' => 'wc-enhanced-select',
				'validation_rule' => array( 'type' => 'text_arr' ),
			);

			$fields['coupon_amount_from'] = array(
				'label' => __( 'Coupon amount: From', 'import-export-suite-for-woocommerce' ),
				'placeholder' => __( 'From amount', 'import-export-suite-for-woocommerce' ),
				'type' => 'number',
				'value' => '',
				'attr' => array( 'min' => 0 ),
				'field_name' => 'coupon_amount_from',
				'help_text' => __( 'Export coupons by their discount amount. Specify the minimum discount amount for which the coupon was levied.', 'import-export-suite-for-woocommerce' ),
				'validation_rule' => array( 'type' => 'floatval' ),
			);

			$fields['coupon_amount_to'] = array(
				'label' => __( 'Coupon amount: To', 'import-export-suite-for-woocommerce' ),
				'placeholder' => __( 'To amount', 'import-export-suite-for-woocommerce' ),
				'type' => 'number',
				'value' => '',
				'attr' => array( 'min' => 0 ),
				'field_name' => 'coupon_amount_to',
				'help_text' => __( 'Export coupons by their discount amount. Specify the maximum discount amount for which the coupon was levied.', 'import-export-suite-for-woocommerce' ),
				'validation_rule' => array( 'type' => 'floatval' ),
			);

			$fields['coupon_exp_date_from'] = array(
				'label' => __( 'Coupon Expiry Date: From', 'import-export-suite-for-woocommerce' ),
				'placeholder' => __( 'From date', 'import-export-suite-for-woocommerce' ),
				'field_name' => 'coupon_exp_date_from',
				'sele_vals' => '',
				'help_text' => __( 'Date on which the coupon will expire. Export coupons with expiry date equal to or greater than the specified date.', 'import-export-suite-for-woocommerce' ),
				'type' => 'text',
				'css_class' => 'wt_iew_datepicker',
			);

			$fields['coupon_exp_date_to'] = array(
				'label' => __( 'Coupon Expiry Date: To', 'import-export-suite-for-woocommerce' ),
				'placeholder' => __( 'To date', 'import-export-suite-for-woocommerce' ),
				'field_name' => 'coupon_exp_date_to',
				'sele_vals' => '',
				'help_text' => __( 'Date on which the coupon will expire. Export coupons with expiry date equal to or less than the specified date.', 'import-export-suite-for-woocommerce' ),
				'type' => 'text',
				'css_class' => 'wt_iew_datepicker',
			);

			$fields['sort_columns'] = array(
				'label' => __( 'Sort columns', 'import-export-suite-for-woocommerce' ),
				'placeholder' => __( 'ID' ),
				'field_name' => 'sort_columns',
				'sele_vals' => self::get_coupon_sort_columns(),
				'help_text' => __( 'Sort the exported data based on the selected columns in order specified. Defaulted to ascending order.', 'import-export-suite-for-woocommerce' ),
				'type' => 'multi_select',
				'css_class' => 'wc-enhanced-select',
				'validation_rule' => array( 'type' => 'text_arr' ),
			);

			$fields['order_by'] = array(
				'label' => __( 'Sort By', 'import-export-suite-for-woocommerce' ),
				'placeholder' => __( 'ASC' ),
				'field_name' => 'order_by',
				'sele_vals' => array(
					'ASC' => 'Ascending',
					'DESC' => 'Descending',
				),
				'help_text' => __( 'Defaulted to Ascending. Applicable to above selected columns in the order specified.', 'import-export-suite-for-woocommerce' ),
				'type' => 'select',
			);
		}//end if

		return $fields;
	}//end exporter_alter_filter_fields()

	/**
	 * Item edit link and name.
	 *
	 * @since 1.0.0
	 * @param integer $id ID.
	 * @return array
	 */
	public function get_item_by_id( $id ) {
		$post['edit_url'] = get_edit_post_link( $id );
		$post['title'] = get_the_title( $id );
		return $post;
	}//end get_item_by_id()


	/**
	 * Coupon import help doc.
	 *
	 * @since 1.0.0
	 */
	public function wt_coupon_import_export_help_content() {
		if ( defined( 'WT_IEW_PLUGIN_ID' ) ) {
			?>
		<li>
			<img src="<?php echo esc_url( WT_IEW_PLUGIN_URL ); ?>assets/images/sample-csv.png">
			<h3><?php esc_html_e( 'Sample Coupon CSV', 'import-export-suite-for-woocommerce' ); ?></h3>
			<p><?php esc_html_e( 'Familiarize yourself with the sample CSV.', 'import-export-suite-for-woocommerce' ); ?></p>
			<a target="_blank" href="https://www.webtoffee.com/wp-content/uploads/2016/09/Coupon_Sample_CSV.csv" class="button button-primary">
			<?php esc_html_e( 'Get Coupon CSV', 'import-export-suite-for-woocommerce' ); ?>        
			</a>
		</li>
			<?php
		}
	}//end wt_coupon_import_export_help_content()
}//end class


new Wt_Import_Export_For_Woo_Coupon();



