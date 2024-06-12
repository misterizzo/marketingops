<?php
/**
 * Handles the user actions.
 *
 * @package   ImportExportSuite\Admin\Modules\User
 * @version   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_User Class.
 */
class Wt_Import_Export_For_Woo_User {

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
	public $module_base = 'user';
		/**
		 * Module ID
		 *
		 * @var string
		 */
	public $module_name = 'User Import Export for WooCommerce';
		/**
		 * Minimum `Import export plugin` required to run this add on plugin
		 *
		 * @var string
		 */
	public $min_base_version = '1.0.0';
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
	private $found_meta = array();
		/**
		 * Module ID
		 *
		 * @var string
		 */
	private $found_hidden_meta = array();
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

		$this->module_id = Wt_Import_Export_For_Woo::get_module_id( $this->module_base );

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

		add_filter( 'wt_iew_importer_steps', array( $this, 'importer_steps' ), 10, 2 );

		add_action( 'wt_user_addon_help_content', array( $this, 'wt_user_import_help_content' ) );
	}//end __construct()


	/**
	 * Altering advanced step description
	 *
	 * @param array  $steps Steps.
	 * @param string $base Base.
	 * @return type
	 */
	public function importer_steps( $steps, $base ) {
		if ( $this->module_base == $base ) {
			$steps['advanced']['description'] = __( 'Use advanced options from below to decide updates to existing customers, batch import count or schedule an import. You can also save the template file for future imports.', 'import-export-suite-for-woocommerce' );
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

		include plugin_dir_path( __FILE__ ) . 'import/class-wt-import-export-for-woo-user-import.php';
		$import = new Wt_Import_Export_For_Woo_User_Import( $this );

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

		include plugin_dir_path( __FILE__ ) . 'export/class-wt-import-export-for-woo-user-export.php';
		$export = new Wt_Import_Export_For_Woo_User_Export( $this );

		$header_row = $export->prepare_header();

		$data_row = $export->prepare_data_to_export( $form_data, $batch_offset );

		$export_data = array(
			'head_data' => $header_row,
			'body_data' => $data_row['data'],
		);

		if ( isset( $data_row['total'] ) && ! empty( $data_row['total'] ) ) {
			$export_data['total'] = $data_row['total'];
		}

		if ( isset( $data_row['no_post'] ) ) {
			$export_data['no_post'] = $data_row['no_post'];
		}

		return $export_data;
	}//end exporter_do_export()


	/**
	 * Setting default export columns for quick export
	 *
	 * @param array $form_data Form data.
	 */
	public function set_export_columns_for_quick_export( $form_data ) {

		$post_columns = self::get_user_post_columns();

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
	 * @param array $arr Post types.
	 */
	public function wt_iew_exporter_post_types( $arr ) {
		$arr['user'] = __( 'User/Customer' );
		return $arr;
	}//end wt_iew_exporter_post_types()

	/**
	 * Sort columns
	 *
	 * @return array
	 */
	public static function get_user_sort_columns() {
		$sort_columns = array(
			'ID'              => 'ID',
			'user_registered' => 'user_registered',
			'user_email'      => 'user_email',
			'user_login'      => 'user_login',
			'user_nicename'   => 'user_nicename',
			'user_url'        => 'user_url',
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
		return apply_filters( 'wt_iew_export_user_sort_columns', $sort_columns );
	}//end get_user_sort_columns()

	/**
	 * User roles
	 *
	 * @return array
	 */
	public static function get_user_roles() {
		global $wp_roles;
		$roles = array();
		foreach ( $wp_roles->role_names as $role => $name ) {
			$roles[ esc_attr( $role ) ] = esc_html( $name );
		}
		/**
		* Filter the query arguments for a request.
		*
		* Enables adding extra arguments or setting defaults for the request.
		*
		* @since 1.0.0
		*
		* @param array   $roles    User roles.
		*/
		return apply_filters( 'wt_iew_export_user_roles', $roles );
	}//end get_user_roles()

	/**
	 * Post columns
	 *
	 * @return array
	 */
	public static function get_user_post_columns() {
		return include plugin_dir_path( __FILE__ ) . 'data/data-user-columns.php';
	}//end get_user_post_columns()

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
		if ( $base != $this->module_base ) {
			return $mapping_enabled_fields;
		}

			$mapping_enabled_fields         = array();
			$mapping_enabled_fields['meta'] = array(
				__( 'Meta (custom fields)', 'import-export-suite-for-woocommerce' ),
				1,
			);
			$mapping_enabled_fields['hidden_meta'] = array(
				__( 'Hidden meta', 'import-export-suite-for-woocommerce' ),
				0,
			);

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
				case 'meta':
					$meta_attributes = array();
					$found_meta      = $this->wt_get_found_meta();

					foreach ( $found_meta as $meta ) {
						$fields[ $key ]['fields'][ 'meta:' . $meta ] = 'meta:' . $meta;
					}
					break;

				case 'hidden_meta':
					$found_hidden_meta = $this->wt_get_found_hidden_meta();
					foreach ( $found_hidden_meta as $meta ) {
						$fields[ $key ]['fields'][ 'meta:' . $meta ] = 'meta:' . $meta;
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
	 * User meta
	 *
	 * @return array
	 */
	public function wt_get_found_meta() {

		if ( ! empty( $this->found_meta ) ) {
			return $this->found_meta;
		}

		global $wpdb;
		// Loop products and load meta data.
		$found_meta = array();
		// Some of the values may not be usable (e.g. arrays of arrays) but the worse.
		// that can happen is we get an empty column.
		$all_meta_keys = $this->wt_get_all_meta_keys();

		$csv_columns = self::get_user_post_columns();

		foreach ( $all_meta_keys as $meta ) {
			if ( ! $meta || ( substr( (string) $meta, 0, 1 ) == '_' ) || in_array( $meta, array_keys( $csv_columns ) ) || in_array( 'meta:' . $meta, array_keys( $csv_columns ) ) || "{$wpdb->prefix}capabilities" == $meta ) {
				continue;
			}

			$found_meta[] = $meta;
		}

		$found_meta = array_diff( $found_meta, array_keys( $csv_columns ) );

		$this->found_meta = $found_meta;
		return $this->found_meta;
	}//end wt_get_found_meta()

	/**
	 * User meta keys
	 *
	 * @return array
	 */
	public function wt_get_all_meta_keys() {

		if ( ! empty( $this->all_meta_keys ) ) {
			return $this->all_meta_keys;
		}

		$all_meta_pkeys = self::get_all_metakeys();

		$this->all_meta_keys = $all_meta_pkeys;

		return $this->all_meta_keys;
	}//end wt_get_all_meta_keys()


	/**
	 * Get a list of all the meta keys for a post type. This includes all public, private,
	 * used, no-longer used etc. They will be sorted once fetched.
	 */
	public static function get_all_metakeys() {
		global $wpdb;

		$user_meta_keys = $wpdb->get_col( "SELECT distinct(meta_key) FROM $wpdb->usermeta LIMIT 2010" );
		/**
		* Filter the query arguments for a request.
		*
		* Enables adding extra arguments or setting defaults for the request.
		*
		* @since 1.0.0
		*
		* @param array   $user_meta_keys    User meta keys.
		*/
		return apply_filters( 'wt_alter_user_meta_data', $user_meta_keys );
	}//end get_all_metakeys()

	/**
	 * User hidden meta
	 *
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

		$csv_columns = self::get_user_post_columns();
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
		/**
		* Filter the query arguments for a request.
		*
		* Enables adding extra arguments or setting defaults for the request.
		*
		* @since 1.0.0
		*
		* @param array   $this->selected_column_names    Selected user columns.
		*/
		return apply_filters( 'wt_user_alter_csv_header', $this->selected_column_names );
	}//end get_selected_column_names()

	/**
	 * Export alter mapping fields
	 *
	 * @param array  $fields Mapping Enabled fields.
	 * @param string $base Base.
	 * @param array  $mapping_form_data Mapping Enabled fields.
	 * @return type
	 */
	public function exporter_alter_mapping_fields( $fields, $base, $mapping_form_data ) {
		if ( $base != $this->module_base ) {
			return $fields;
		}

		$fields = self::get_user_post_columns();
		return $fields;
	}//end exporter_alter_mapping_fields()

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
		$fields['limit']['label']      = __( 'Total number of users to export', 'import-export-suite-for-woocommerce' );
		$fields['limit']['help_text']  = __( 'Exports specified number of users. e.g. Entering 500 with a skip count of 10 will export users from 11th to 510th position.', 'import-export-suite-for-woocommerce' );
		$fields['offset']['label']     = __( 'Skip first <i>n</i> users', 'import-export-suite-for-woocommerce' );
		$fields['offset']['help_text'] = __( 'Skips specified number of users from the beginning of the database. e.g. Enter 10 to skip first 10 users from export.', 'import-export-suite-for-woocommerce' );

		$fields['roles'] = array(
			'label'           => __( 'User Roles', 'import-export-suite-for-woocommerce' ),
			'placeholder'     => __( 'All Roles', 'import-export-suite-for-woocommerce' ),
			'field_name'      => 'roles',
			'sele_vals'       => self::get_user_roles(),
			'help_text'       => __( 'Input specific roles to export information pertaining to all customers with the respective roles.', 'import-export-suite-for-woocommerce' ),
			'type'            => 'multi_select',
			'css_class'       => 'wc-enhanced-select',
			'validation_rule' => array( 'type' => 'text_arr' ),
		);

		$fields['email'] = array(
			'label'           => __( 'User Email', 'import-export-suite-for-woocommerce' ),
			'placeholder'     => __( 'All User', 'import-export-suite-for-woocommerce' ),
			'field_name'      => 'email',
			'sele_vals'       => '',
			'help_text'       => __( 'Input the customer emails separated by comma to export information pertaining to only these customers.', 'import-export-suite-for-woocommerce' ),
			'validation_rule' => array( 'type' => 'text_arr' ),
		);
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$fields['email']['help_text'] = __( 'Input the customer email to export information pertaining to only these customers.', 'import-export-suite-for-woocommerce' );
			$fields['email']['type']      = 'multi_select';
			$fields['email']['css_class'] = 'wc-customer-search';
		}

		$fields['date_from'] = array(
			'label'       => __( 'Date From', 'import-export-suite-for-woocommerce' ),
			'placeholder' => __( 'Date', 'import-export-suite-for-woocommerce' ),
			'field_name'  => 'date_from',
			'sele_vals'   => '',
			'help_text'   => __( 'Date on which the customer registered. Export customers registered on and after the specified date.', 'import-export-suite-for-woocommerce' ),
			'type'        => 'text',
			'css_class'   => 'wt_iew_datepicker',
		);

		$fields['date_to'] = array(
			'label'       => __( 'Date To', 'import-export-suite-for-woocommerce' ),
			'placeholder' => __( 'Date', 'import-export-suite-for-woocommerce' ),
			'field_name'  => 'date_to',
			'sele_vals'   => '',
			'help_text'   => __( 'Export customers registered upto the specified date.', 'import-export-suite-for-woocommerce' ),
			'type'        => 'text',
			'css_class'   => 'wt_iew_datepicker',
		);
		$fields['sort_columns'] = array(
			'label'           => __( 'Sort Columns', 'import-export-suite-for-woocommerce' ),
			'placeholder'     => __( 'user_login' ),
			'field_name'      => 'sort_columns',
			'sele_vals'       => self::get_user_sort_columns(),
			'help_text'       => __( 'Sort the exported data based on the selected columns in order specified. Defaulted to ascending order.', 'import-export-suite-for-woocommerce' ),
			'type'            => 'multi_select',
			'css_class'       => 'wc-enhanced-select',
			'validation_rule' => array( 'type' => 'text_arr' ),
		);

		$fields['order_by'] = array(
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
	 * Export alter advnaced fields
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

		unset( $fields['export_shortcode_tohtml'] );
		$out = array();
		$out['export_guest_user'] = array(
			'label'        => __( 'Export guest users', 'import-export-suite-for-woocommerce' ),
			'type'         => 'radio',
			'radio_fields' => array(
				'Yes' => __( 'Yes', 'import-export-suite-for-woocommerce' ),
				'No'  => __( 'No', 'import-export-suite-for-woocommerce' ),
			),
			'value'        => 'No',
			'field_name'   => 'export_guest_user',
			'help_text'    => __( 'Enable this option to export information related to guest users', 'import-export-suite-for-woocommerce' ),
		);

		foreach ( $fields as $fieldk => $fieldv ) {
			$out[ $fieldk ] = $fieldv;
		}

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
			'label'                 => __( 'Update Only', 'import-export-suite-for-woocommerce' ),
			'type'                  => 'radio',
			'radio_fields'          => array(
				'1' => __( 'Yes', 'import-export-suite-for-woocommerce' ),
				'0' => __( 'No', 'import-export-suite-for-woocommerce' ),
			),
			'value'                 => '0',
			'field_name'            => 'skip_new',
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
			'form_toggler'          => array(
				'type'   => 'parent',
				'target' => 'wt_iew_skip_new',
			),
		);

		$out['merge_with'] = array(
			'label'        => __( 'Match users by their', 'import-export-suite-for-woocommerce' ),
			'type'         => 'radio',
			'radio_fields' => array(
				'id'       => __( 'ID' ),
				'email'    => __( 'Email' ),
				'username' => __( 'Username', 'import-export-suite-for-woocommerce' ),
			),
			'value'        => 'email',
			'field_name'   => 'merge_with',
			'help_text'    => __( 'The users are either looked up based on their User ID/email/Username as per the selection.', 'import-export-suite-for-woocommerce' ),
		);

		$out['found_action_merge'] = array(
			'label'                 => __( 'Existing user', 'import-export-suite-for-woocommerce' ),
			'type'                  => 'radio',
			'radio_fields'          => array(
				'skip'   => __( 'Skip', 'import-export-suite-for-woocommerce' ),
				'update' => __( 'Update', 'import-export-suite-for-woocommerce' ),
			),
			'value'                 => 'skip',
			'field_name'            => 'found_action',
			'help_text_conditional' => array(
				array(
					'help_text' => __( 'Retains the user in the store as is and skips the matching user from the input file.', 'import-export-suite-for-woocommerce' ),
					'condition' => array(
						array(
							'field' => 'wt_iew_found_action',
							'value' => 'skip',
						),
					),
				),
				array(
					'help_text' => __( 'Update user as per data from the input file', 'import-export-suite-for-woocommerce' ),
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
		$out['send_mail']         = array(
			'label'        => __( 'Email new users', 'import-export-suite-for-woocommerce' ),
			'type'         => 'radio',
			'radio_fields' => array(
				'1' => __( 'Yes', 'import-export-suite-for-woocommerce' ),
				'0' => __( 'No', 'import-export-suite-for-woocommerce' ),
			),
			'value'        => '0',
			'field_name'   => 'send_mail',
			'help_text'    => __( 'Email all the new users upon successful import.', 'import-export-suite-for-woocommerce' ),
		);

		$out['skip_guest_user'] = array(
			'label' => __( 'Skip Guest User' ),
			'type' => 'checkbox',
			'merge_right' => true,
			'checkbox_fields' => array( 1 => __( 'Enable' ) ),
			'value' => 1,
			'field_name' => 'skip_guest_user',
			'help_text' => __( 'Exclude guest users from being imported as registered customers.' ),
		);

		foreach ( $fields as $fieldk => $fieldv ) {
			$out[ $fieldk ] = $fieldv;
		}

		return $out;
	}//end importer_alter_advanced_fields()

	/**
	 * Get item link
	 *
	 * @param type $id ID.
	 * @return type
	 */
	public function get_item_by_id( $id ) {

		if ( empty( $id ) ) {
			return;
		}

		$post['edit_url'] = get_edit_user_link( $id );
		$user_info        = get_userdata( $id );
		if ( $user_info ) {
			$post['title'] = $user_info->user_login;
		}

		return $post;
	}//end get_item_by_id()

	/**
	 *  Add user import help content to help section
	 */
	public function wt_user_import_help_content() {
		if ( defined( 'WT_IEW_PLUGIN_ID' ) ) {
			?>
			<li>
				<img src="<?php echo esc_url( WT_IEW_PLUGIN_URL ); ?>assets/images/sample-csv.png">
				<h3><?php esc_html_e( 'Sample User CSV', 'import-export-suite-for-woocommerce' ); ?></h3>
				<p><?php esc_html_e( 'Familiarize yourself with the sample CSV.', 'import-export-suite-for-woocommerce' ); ?></p>
				<a target="_blank" href="https://www.webtoffee.com/wp-content/uploads/2020/10/Sample_Users.csv" class="button button-primary">
				<?php esc_html_e( 'Get User CSV', 'import-export-suite-for-woocommerce' ); ?>        
				</a>
			</li>
			<?php
		}
	}//end wt_user_import_help_content()
}//end class


new Wt_Import_Export_For_Woo_User();

