<?php
/**
 * Handles the import actions.
 *
 * @package   ImportExportSuite\Admin\Modules\Import
 * @version   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_Import Class.
 */
class Wt_Import_Export_For_Woo_Import {

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
	public $module_base = 'import';
	/**
	 * Import directory
	 *
	 * @var string
	 */
	public static $import_dir = WP_CONTENT_DIR . '/webtoffee_import';
	/**
	 * Import directory
	 *
	 * @var string
	 */
	public static $import_dir_name = '/webtoffee_import';
	/**
	 * Steps
	 *
	 * @var array
	 */
	public $steps = array();
	/**
	 * Allowed file types
	 *
	 * @var array
	 */
	public $allowed_import_file_type = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $max_import_file_size = 10;  // in MB.
	/**
	 * Module ID
	 *
	 * @var string
	 */
	private $to_import_id = '';
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $to_import = '';
	/**
	 * Module ID
	 *
	 * @var string
	 */
	private $rerun_id = 0;
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $import_method = '';
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $import_methods = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $selected_template = 0;
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $default_batch_count = 0; /* configure this value in `advanced_setting_fields` method */
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $selected_template_data = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $default_import_method = ''; /* configure this value in `advanced_setting_fields` method */
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $form_data = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $temp_import_file = '';
	/**
	 * Module ID
	 *
	 * @var string
	 */
	private $to_process = '';
	/**
	 * Module ID
	 *
	 * @var string
	 */
	private $skip_from_evaluation_array = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	private $decimal_columns = array();
	/**
	 * Allowed file types for the import
	 *
	 * @var array
	 */
	public $allowed_import_file_type_mime = array();
	/**
	 * Steps that needs pass through validation
	 *
	 * @var array
	 */
	public $step_need_validation_filter = array();

	/**
	 * Validation rules
	 *
	 * @var array
	 */
	public $validation_rule = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		 $this->module_id = Wt_Import_Export_For_Woo::get_module_id( $this->module_base );
		self::$module_id_static = $this->module_id;

		$this->max_import_file_size = wp_max_upload_size() / 1000000; // in MB.

		/* allowed file types */
		$this->allowed_import_file_type = array(
			'csv' => __( 'CSV' ),
			'xml' => __( 'XML' ),
			'txt' => __( 'TXT' ),
		);
		$this->allowed_import_file_type_mime = array(
			'csv' => 'text/csv',
			'xml' => 'text/xml',
			'txt' => 'text/plain',
		);

		/* default step list */
		$this->steps = array(
			'post_type' => array(
				'title' => __( 'Select a post type', 'import-export-suite-for-woocommerce' ),
				'description' => __( 'Import the respective post type from a CSV/XML. As a first step you need to choose the post type to start the import.', 'import-export-suite-for-woocommerce' ),
			),
			'method_import' => array(
				'title' => __( 'Select an import method', 'import-export-suite-for-woocommerce' ),
				'description' => __( 'Choose from the options below to continue with your import: quick import, based on a pre-saved template or a new import with advanced options.', 'import-export-suite-for-woocommerce' ),
			),
			'mapping' => array(
				'title' => __( 'Map import columns', 'import-export-suite-for-woocommerce' ),
				'description' => __( 'Map the standard/meta/attributes/taxonomies and hidden meta columns with your CSV/XML column names.', 'import-export-suite-for-woocommerce' ),
			),
			'advanced' => array(
				'title' => __( 'Advanced options/Batch import/Scheduling', 'import-export-suite-for-woocommerce' ),
				'description' => __( 'Use advanced options from below to decide on the delimiter options, updates to existing products, batch import count or schedule an import. You can also save the template file for future imports.', 'import-export-suite-for-woocommerce' ),
			),
		);

			$this->validation_rule = array(
				'post_type' => array(), /* no validation rule. So default sanitization text */
			);
			$this->step_need_validation_filter = array( 'method_import', 'mapping', 'advanced' );

			$this->import_methods = array(
				'quick' => array(
					'title' => __( 'Quick import', 'import-export-suite-for-woocommerce' ),
					'description' => __( 'Use this option primarily when your input file was exported using the same plugin.', 'import-export-suite-for-woocommerce' ),
				),
				'template' => array(
					'title' => __( 'Pre-saved template', 'import-export-suite-for-woocommerce' ),
					'description' => __( 'Using a pre-saved template retains the previous filter criteria and other column specifications as per the chosen file and imports data accordingly', 'import-export-suite-for-woocommerce' ),
				),
				'new' => array(
					'title' => __( 'Advanced Import', 'import-export-suite-for-woocommerce' ),
					'description' => __( 'This option will take you through the entire process of filtering/column selection/advanced options that may be required for your import. You can also save your selections as a template for future use.', 'import-export-suite-for-woocommerce' ),
				),
			);

			/* advanced plugin settings */
			add_filter( 'wt_iew_advanced_setting_fields', array( $this, 'advanced_setting_fields' ) );

			/* setting default values this method must be below of advanced setting filter */
			$this->get_defaults();

			/* main ajax hook. The callback function will decide which is to execute. */
			add_action( 'wp_ajax_iew_import_ajax', array( $this, 'ajax_main' ), 11 );

			/* Admin menu for import */
			add_filter( 'wt_iew_admin_menu', array( $this, 'add_admin_pages' ), 10, 1 );
	}
	/**
	 *   Default settings
	 */
	public function get_defaults() {
		$this->default_import_method = Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings( 'default_import_method' );
		$this->default_batch_count = Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings( 'default_import_batch' );
	}

	/**
	 *   Fields for advanced settings
	 *
	 * @param array $fields Fields.
	 */
	public function advanced_setting_fields( $fields ) {

				$fields['maximum_execution_time'] = array(
					'label' => __( 'Maximum execution time', 'import-export-suite-for-woocommerce' ),
					'type' => 'number',
					'value' => ini_get( 'max_execution_time' ), /* Default max_execution_time settings value */
					'field_name' => 'maximum_execution_time',
					'field_group' => 'advanced_field',
					'help_text' => __( 'The maximum execution time, in seconds(eg:- 300, 600, 1800, 3600). If set to zero, no time limit is imposed. Increasing this will reduce the chance of export/import timeouts.', 'import-export-suite-for-woocommerce' ),
					'validation_rule' => array( 'type' => 'int' ),
				);
				$fields['enable_import_log'] = array(
					'label' => __( 'Generate Import log', 'import-export-suite-for-woocommerce' ),
					'type' => 'radio',
					'radio_fields' => array(
						1 => __( 'Yes', 'import-export-suite-for-woocommerce' ),
						0 => __( 'No', 'import-export-suite-for-woocommerce' ),
					),
					'value' => 1,
					'field_name' => 'enable_import_log',
					'field_group' => 'advanced_field',
					'help_text' => __( 'Generate import log as text file and make it available in the log section for debugging purposes.', 'import-export-suite-for-woocommerce' ),
					'validation_rule' => array( 'type' => 'absint' ),
				);
				$import_methods = array_map(
					function ( $vl ) {
						return $vl['title'];
					},
					$this->import_methods
				);
				$fields['default_import_method'] = array(
					'label' => __( 'Default Import method', 'import-export-suite-for-woocommerce' ),
					'type' => 'select',
					'sele_vals' => $import_methods,
					'value' => 'new',
					'field_name' => 'default_import_method',
					'field_group' => 'advanced_field',
					'help_text' => __( 'Select the default method of import.', 'import-export-suite-for-woocommerce' ),
				);
				$fields['default_import_batch'] = array(
					'label' => __( 'Default Import batch count', 'import-export-suite-for-woocommerce' ),
					'type' => 'number',
					'value' => 10, /* If altering then please also change batch count field help text section */
					'field_name' => 'default_import_batch',
					'help_text' => __( 'Provide the default number of records to be imported in a batch.', 'import-export-suite-for-woocommerce' ),
					'validation_rule' => array( 'type' => 'absint' ),
				);

				return $fields;
	}

	/**
	 *   Fields for Import advanced step
	 *
	 * @param array $advanced_form_data Advanced form data.
	 */
	public function get_advanced_screen_fields( $advanced_form_data ) {
		$advanced_screen_fields = array(

			'advanced_field_head' => array(
				'type' => 'field_group_head', // field type.
				'head' => __( 'Advanced options', 'import-export-suite-for-woocommerce' ),
				'group_id' => 'advanced_field', // field group id.
				'show_on_default' => 0,
			),
			'batch_count' => array(
				'label' => __( 'Import in batches of', 'import-export-suite-for-woocommerce' ),
				'type' => 'text',
				'value' => $this->default_batch_count,
				'field_name' => 'batch_count',
				'help_text' => __( 'The number of records that the server will process for every iteration within the configured timeout interval.', 'import-export-suite-for-woocommerce' ),
				// '<span class="woocommerce-help-tip" data-tip="If the export fails due to timeout you can lower this number accordingly and try again."></span>',.
				'field_group' => 'advanced_field',
				'validation_rule' => array( 'type' => 'absint' ),                                 // 'before_form_field'=>'<span class="woocommerce-help-tip" data-tip="If the export fails due to timeout you can lower this number accordingly and try again."></span>',.
			),
		);

		/**
		 * Taking advanced fields from post type modules.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $advanced_screen_fields    Advanced screen options.
		 * @param string          $this->to_import    Current action.
		 * @param array           $advanced_form_data    Form data.
		 */
		$advanced_screen_fields = apply_filters( 'wt_iew_importer_alter_advanced_fields', $advanced_screen_fields, $this->to_import, $advanced_form_data );
		return $advanced_screen_fields;
	}

	/**
	 *   Fields for Import method step
	 *
	 * @param array $method_import_form_data Import form data.
	 */
	public function get_method_import_screen_fields( $method_import_form_data ) {
		$file_from_arr = array(
			'local' => __( 'Local' ),
			'url' => __( 'URL' ),
		);

		/* taking available remote adapters */
		$remote_adapter_names = array();
		/**
		 * Taking advanced fields from post type modules.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $remote_adapter_names    Remote adapter.
		 */
		$remote_adapter_names = apply_filters( 'wt_iew_importer_remote_adapter_names', $remote_adapter_names );
		if ( $remote_adapter_names && is_array( $remote_adapter_names ) ) {
			foreach ( $remote_adapter_names as $remote_adapter_key => $remote_adapter_vl ) {
				$file_from_arr[ $remote_adapter_key ] = $remote_adapter_vl;
			}
		}

		// prepare file from field type based on remote type adapters.
		$file_from_field_arr = array(
			'label' => __( 'Import from', 'import-export-suite-for-woocommerce' ) . ' [<a href"#" target="_blank" id="sample-csv-file">' . __( 'Sample CSV', 'import-export-suite-for-woocommerce' ) . '</a>]',
			'type' => 'select',
			'tr_class' => 'wt-iew-import-method-options wt-iew-import-method-options-quick wt-iew-import-method-options-new wt-iew-import-method-options-template',
			'sele_vals' => $file_from_arr,
			'field_name' => 'file_from',
			'default_value' => 'local',
			'form_toggler' => array(
				'type' => 'parent',
				'target' => 'wt_iew_file_from',
			),
		);
		if ( 1 == count( $file_from_arr ) ) {
			$file_from_field_arr['label'] = __( 'Enable FTP import?', 'import-export-suite-for-woocommerce' );
			$file_from_field_arr['type'] = 'radio';
			$file_from_field_arr['radio_fields'] = array(
				'local' => __( 'No' ),
			);
		} elseif ( 2 == count( $file_from_arr ) ) {
			$end_vl = end( $file_from_arr );
			$end_ky = key( $file_from_arr );

			$file_from_field_arr['label'] = /* translators:%s: option like remote */ sprintf( __( 'Enable %s import?' ), $end_vl );
			$file_from_field_arr['type'] = 'radio';
			$file_from_field_arr['radio_fields'] = array(
				'local' => __( 'No' ),
				$end_ky => __( 'Yes' ),
			);
		}

		$method_import_screen_fields = array(
			'file_from' => $file_from_field_arr,
			'local_file' => array(
				'label' => __( 'Select a file' ),
				'type' => 'dropzone',
				'merge_left' => true,
				'merge_right' => true,
				'tr_id' => 'local_file_tr',
				'tr_class' => $file_from_field_arr['tr_class'], // add tr class from parent.Because we need to toggle the tr when parent tr toggles.
				'field_name' => 'local_file',
				'html_id' => 'local_file',
				'form_toggler' => array(
					'type' => 'child',
					'id' => 'wt_iew_file_from',
					'val' => 'local',
				),
			),
			'url_file' => array(
				'label' => __( 'Enter file URL' ),
				'type' => 'text',
				'tr_id' => 'url_file_tr',
				'tr_class' => $file_from_field_arr['tr_class'], // add tr class from parent.Because we need to toggle the tr when parent tr toggles.
				'field_name' => 'url_file',
				'form_toggler' => array(
					'type' => 'child',
					'id' => 'wt_iew_file_from',
					'val' => 'url',
				),
			),
		);

		/**
		 * Taking import_method fields from other modules.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $method_import_screen_fields    Advanced screen options.
		 * @param string          $this->to_import    Current action.
		 * @param array           $method_import_form_data    Form data.
		 */
		$method_import_screen_fields = apply_filters( 'wt_iew_importer_alter_method_import_fields', $method_import_screen_fields, $this->to_import, $method_import_form_data );

		$method_import_screen_fields['delimiter'] = array(
			'label' => __( 'Delimiter', 'import-export-suite-for-woocommerce' ),
			'type' => 'select',
			'value' => ',',
			'css_class' => 'wt_iew_delimiter_preset',
			'tr_id' => 'delimiter_tr',
			'tr_class' => $file_from_field_arr['tr_class'], // add tr class from parent.Because we need to toggle the tr when parent tr toggles.
			'field_name' => 'delimiter_preset',
			'sele_vals' => Wt_Iew_IE_Helper::_get_csv_delimiters(),
			'help_text' => __( 'Only applicable for CSV imports in order to separate the columns in the CSV file. Takes comma(,) by default.', 'import-export-suite-for-woocommerce' ),
			'validation_rule' => array( 'type' => 'skip' ),
			'after_form_field' => '<input type="text" class="wt_iew_custom_delimiter" name="wt_iew_delimiter" value="' . ( ! empty( $method_import_form_data['wt_iew_delimiter'] ) ? $method_import_form_data['wt_iew_delimiter'] : ',' ) . '" />',
		);

		$method_import_screen_fields['date_format'] = array(
			'label' => __( 'Date format', 'import-export-suite-for-woocommerce' ),
			'type' => 'select',
			'value' => 'Y-m-d',
			'css_class' => 'wt_iew_date_format_preset',
			'tr_class' => $file_from_field_arr['tr_class'], // add tr class from parent.Because we need to toggle the tr when parent tr toggles.
			'field_name' => 'date_format',
			'sele_vals' => array(
				'Y-m-d H:i:s'   => array(
					'value' => 'Y-m-d H:i:s (' . gmdate( 'Y-m-d H:i:s' ) . ')', // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					'val' => 'Y-m-d H:i:s',
				),
				'd-m-Y H:i:s'   => array(
					'value' => 'd-m-Y H:i:s (' . gmdate( 'd-m-Y H:i:s' ) . ')', // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					'val' => 'd-m-Y H:i:s',
				),
				'd/m/y h:i:s A' => array(
					'value' => 'd/m/y h:i:s A (' . gmdate( 'd/m/y h:i:s A' ) . ')', // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					'val' => 'd/m/y h:i:s A',
				),
				'Y/m/d H:i:s'   => array(
					'value' => 'Y/m/d H:i:s (' . gmdate( 'Y/m/d H:i:s' ) . ')', // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					'val' => 'Y/m/d H:i:s',
				),
				'other'         => array(
					'value' => __( 'Other', 'import-export-suite-for-woocommerce' ),
					'val' => '',
				),
			),
			/* translators: 1: HTML a tag. 2: HTML a tag close */
			'help_text' => sprintf( __( 'Date format in the input file. Click %1$s here %2$s for more info about the date formats.', 'import-export-suite-for-woocommerce' ), '<a href="https://www.php.net/manual/en/function.date.php" target="_blank">', '</a>' ),
			'validation_rule' => array( 'type' => 'skip' ),
			'after_form_field' => '<input type="text" class="wt_iew_custom_date_format" name="wt_iew_date_format" value="' . ( ! empty( $method_import_form_data['wt_iew_date_format'][1] ) ? $method_import_form_data['wt_iew_date_format'][1] : 'Y-m-dd' ) . '" />',
		);
		return $method_import_screen_fields;
	}

	/**
	 * Adding admin menus
	 *
	 * @param array $menus Menus.
	 */
	public function add_admin_pages( $menus ) {
		$first = array_slice( $menus, 0, 3, true );
		$last = array_slice( $menus, 3, ( count( $menus ) - 1 ), true );

		$menu = array(
			$this->module_base => array(
				'submenu',
				WT_IEW_PLUGIN_ID,
				__( 'Import', 'import-export-suite-for-woocommerce' ),
				__( 'Import', 'import-export-suite-for-woocommerce' ),
				/**
				 * Capability for menus.
				 *
				 * Enables adding extra arguments or setting defaults for the request.
				 *
				 * @since 1.0.0
				 *
				 * @param string          $capability    Capability.
				 */
						apply_filters( 'wt_import_export_allowed_capability', 'import' ),
				$this->module_id,
				array( $this, 'admin_settings_page' ),
			),
		);

		$menus = array_merge( $first, $menu, $last );
		return $menus;
	}

	/**
	 *   Import page
	 */
	public function admin_settings_page() {
		if ( isset( $_GET['wt_iew_cron_edit_id'] ) && absint( $_GET['wt_iew_cron_edit_id'] ) > 0 ) {
			$requested_cron_edit_id = ( isset( $_GET['wt_iew_cron_edit_id'] ) ? absint( $_GET['wt_iew_cron_edit_id'] ) : 0 );
			$this->_process_edit_cron( $requested_cron_edit_id );
		}

		/**
		*   Check it is a rerun call
		*/

		$requested_rerun_id = ( isset( $_GET['wt_iew_rerun'] ) ? absint( $_GET['wt_iew_rerun'] ) : 0 );
		$this->_process_rerun( $requested_rerun_id );

		if ( $this->rerun_id > 0 ) {
			$response = $this->download_remote_file( $this->form_data );
			if ( $response['response'] ) {
				$this->temp_import_file = $response['file_name'];

				/* delete temp files other than the current temp file of same rerun id, if exists */
				$file_path = $this->get_file_path();
				$temp_files = glob( $file_path . '/rerun_' . $this->rerun_id . '_*' );
				if ( count( $temp_files ) > 1 ) {
					foreach ( $temp_files as $key => $temp_file ) {
						if ( basename( $temp_file ) != $this->temp_import_file ) {
							@unlink( $temp_file ); // delete it.
						}
					}
				}
			} else /* unable to create temp file, then abort the rerun request */
			{
				$this->rerun_id = 0;
				$this->form_data = array();
			}
		}
		$this->enqueue_assets();
		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : null;
		if ( 'import' == $active_tab ) {
			include plugin_dir_path( __FILE__ ) . 'views/main.php';
		}
	}


	/**
	 *   Validating and Processing rerun action
	 *
	 * @param integer $rerun_id Rerun ID.
	 */
	protected function _process_rerun( $rerun_id ) {
		if ( $rerun_id > 0 ) {
			/* check the history module is available */
			$history_module_obj = Wt_Import_Export_For_Woo::load_modules( 'history' );
			if ( ! is_null( $history_module_obj ) ) {

				/* check the history entry is for import and also has form_data */
				$history_data = $history_module_obj->get_history_entry_by_id( $rerun_id );
				if ( $history_data && $history_data['template_type'] == $this->module_base ) {
					$form_data = maybe_unserialize( $history_data['data'] );
					if ( $form_data && is_array( $form_data ) ) {
						$this->to_import = ( isset( $form_data['post_type_form_data'] ) && isset( $form_data['post_type_form_data']['item_type'] ) ? $form_data['post_type_form_data']['item_type'] : '' );
						if ( '' != $this->to_import ) {
							$this->import_method = ( isset( $form_data['method_import_form_data'] ) && isset( $form_data['method_import_form_data']['method_import'] ) && '' != $form_data['method_import_form_data']['method_import'] ? $form_data['method_import_form_data']['method_import'] : $this->default_import_method );
							$this->rerun_id = $rerun_id;
							$this->form_data = $form_data;
							// process steps based on the import method in the history entry.
							$this->get_steps();

							return true;
						}
					}
				}
			}
		}
		return false;
	}
	/**
	 *   Cron edit
	 *
	 * @param integer $rerun_id Rerun ID.
	 */
	protected function _process_edit_cron( $rerun_id ) {
		if ( $rerun_id > 0 ) {
			/* check the cron module is available */
			$cron_module_obj = Wt_Import_Export_For_Woo::load_modules( 'cron' );
			if ( ! is_null( $cron_module_obj ) ) {
				/* check the cron entry is for export and also has form_data */
								$cron_data = $cron_module_obj->get_cron_by_id( $rerun_id );

				if ( $cron_data && $cron_data['action_type'] == $this->module_base ) {
					$form_data = maybe_unserialize( $cron_data['data'] );
					if ( $form_data && is_array( $form_data ) ) {
						$this->to_import = ( isset( $form_data['post_type_form_data'] ) && isset( $form_data['post_type_form_data']['item_type'] ) ? $form_data['post_type_form_data']['item_type'] : '' );
						if ( '' != $this->to_import ) {
							$this->import_method = ( isset( $form_data['method_import_form_data'] ) && isset( $form_data['method_import_form_data']['method_import'] ) && '' != $form_data['method_import_form_data']['method_import'] ? $form_data['method_import_form_data']['method_import'] : $this->default_import_method );
							$this->rerun_id = $rerun_id;
							$this->form_data = $form_data;
							// process steps based on the export method in the history entry.
							$this->get_steps();
							return true;
						}
					}
				}
			}
		}
		return false;
	}
	/**
	 *   Enque assests
	 */
	protected function enqueue_assets() {
		if ( Wt_Import_Export_For_Woo_Common_Helper::wt_is_screen_allowed() ) {
			/* adding dropzone JS */
			wp_enqueue_script( WT_IEW_PLUGIN_ID . '-dropzone', WT_IEW_PLUGIN_URL . 'admin/js/dropzone.min.js', array( 'jquery' ), WT_IEW_VERSION );

			wp_enqueue_script( $this->module_id, plugin_dir_url( __FILE__ ) . 'assets/js/main.js', array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-datepicker' ), WT_IEW_VERSION );
			wp_enqueue_style( 'jquery-ui-datepicker' );
			// wp_enqueue_media();.

			wp_enqueue_style( WT_IEW_PLUGIN_ID . '-jquery-ui', WT_IEW_PLUGIN_URL . 'admin/css/jquery-ui.css', array(), WT_IEW_VERSION, 'all' );

			/* check the history module is available */
			$history_module_obj = Wt_Import_Export_For_Woo::load_modules( 'history' );
			if ( ! is_null( $history_module_obj ) ) {
				wp_enqueue_script( Wt_Import_Export_For_Woo::get_module_id( 'history' ), WT_IEW_PLUGIN_URL . 'admin/modules/history/assets/js/main.js', array( 'jquery' ), WT_IEW_VERSION, false );
			}

			$file_extensions = array_keys( $this->allowed_import_file_type_mime );
			$file_extensions = array_map(
				function ( $vl ) {
					return '.' . $vl;
				},
				$file_extensions
			);

			$params = array(
				'item_type' => '',
				'steps' => $this->steps,
				'rerun_id' => $this->rerun_id,
				'to_import' => $this->to_import,
				'import_method' => $this->import_method,
				'temp_import_file' => $this->temp_import_file,
				'allowed_import_file_type_mime' => $file_extensions,
				'max_import_file_size' => $this->max_import_file_size,
				'wt_iew_prefix' => Wt_Import_Export_For_Woo_Admin::$wt_iew_prefix,
				'msgs' => array(
					'choosed_template' => __( 'Choosed template: ', 'import-export-suite-for-woocommerce' ),
					'choose_import_method' => __( 'Please select an import method.', 'import-export-suite-for-woocommerce' ),
					'choose_template' => __( 'Please select an import template.', 'import-export-suite-for-woocommerce' ),
					'step' => __( 'Step', 'import-export-suite-for-woocommerce' ),
					'choose_ftp_profile' => __( 'Please select an FTP profile.', 'import-export-suite-for-woocommerce' ),
					'choose_import_from' => __( 'Please choose import from.', 'import-export-suite-for-woocommerce' ),
					'select_post_type' => __( 'Please select a post type.', 'import-export-suite-for-woocommerce' ),
					'choose_a_file' => __( 'Please choose an import file.', 'import-export-suite-for-woocommerce' ),
					'select_an_import_template' => __( 'Please select an import template.', 'import-export-suite-for-woocommerce' ),
					'validating_file' => __( 'Creating temp file and validating.', 'import-export-suite-for-woocommerce' ),
					'processing_file' => __( 'Processing input file...', 'import-export-suite-for-woocommerce' ),
					'column_not_in_the_list' => __( 'This column is not present in the import list. Please tick the checkbox to include.', 'import-export-suite-for-woocommerce' ),
					'uploading' => __( 'Uploading...', 'import-export-suite-for-woocommerce' ),
					'outdated' => __( 'You are using an outdated browser. Please upgarde your browser.', 'import-export-suite-for-woocommerce' ),
					'server_error' => __( 'An error occured.', 'import-export-suite-for-woocommerce' ),
					/* translators:%s: Allowed file types */
					'invalid_file' => sprintf( __( 'Invalid file type. Only %s are allowed', 'import-export-suite-for-woocommerce' ), implode( ', ', array_values( $this->allowed_import_file_type ) ) ),
					'drop_upload' => __( 'Drag and Drop or click to upload', 'import-export-suite-for-woocommerce' ),
					/* translators:%s: Progress count */
					'upload_done' => sprintf( __( '%s Done.', 'import-export-suite-for-woocommerce' ), '<span class="dashicons dashicons-yes-alt" style="color:#3fa847;"></span>' ),
					'remove' => __( 'Remove', 'import-export-suite-for-woocommerce' ),
				),

			);
			wp_localize_script( $this->module_id, 'wt_iew_import_params', $params );

			$this->add_select2_lib(); // adding select2 JS, It checks the availibility of woocommerce.
		}
	}

	/**
	 *
	 * Enqueue select2 library, if woocommerce available use that
	 */
	protected function add_select2_lib() {
		/* enqueue scripts */
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			wp_enqueue_script( 'wc-enhanced-select' );
			wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WT_IEW_VERSION );
		} else {
			wp_enqueue_style( WT_IEW_PLUGIN_ID . '-select2', WT_IEW_PLUGIN_URL . 'admin/css/select2.css', array(), WT_IEW_VERSION, 'all' );
			wp_enqueue_script( WT_IEW_PLUGIN_ID . '-select2', WT_IEW_PLUGIN_URL . 'admin/js/select2.js', array( 'jquery' ), WT_IEW_VERSION, false );
		}
	}

	/**
	 * Get steps
	 */
	public function get_steps() {
		if ( 'quick' == $this->import_method ) {
			$out = array(
				'post_type' => $this->steps['post_type'],
				'method_import' => $this->steps['method_import'],
				'advanced' => $this->steps['advanced'],
			);
			$this->steps = $out;
		}
		/**
		 * Taking steps.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $this->steps    Steps.
		 * @param string          $this->to_import    Current action.
		 */
		$this->steps = apply_filters( 'wt_iew_importer_steps', $this->steps, $this->to_import );
		return $this->steps;
	}

	/**
	 * Download and save file into web server
	 *
	 * @param array $form_data Form data.
	 */
	public function download_remote_file( $form_data ) {

		$out = array(
			'response' => false,
			'file_name' => '',
			'msg' => '',
		);

		$method_import_form_data = ( isset( $form_data['method_import_form_data'] ) ? $form_data['method_import_form_data'] : array() );
		$file_from = ( isset( $method_import_form_data['wt_iew_file_from'] ) ? Wt_Iew_Sh::sanitize_item( $method_import_form_data['wt_iew_file_from'] ) : '' );

		if ( '' == $file_from ) {
			return $out;
		}

		if ( 'local' == $file_from ) {

				$file_url = ( isset( $method_import_form_data['wt_iew_local_file'] ) ? Wt_Iew_Sh::sanitize_item( $method_import_form_data['wt_iew_local_file'], 'url' ) : '' );
				$local_file_path = Wt_Iew_IE_Helper::_get_local_file_path( $file_url );
			if ( ! $local_file_path ) {
						$file_url = '';
			}

			if ( '' != $file_url ) {
				if ( $this->is_extension_allowed( $file_url ) ) {
					$ext_arr = explode( '.', $file_url );
					$ext = end( $ext_arr );

					$file_name = $this->get_temp_file_name( $ext );
					$file_path = $this->get_file_path( $file_name );
					if ( $file_path ) {

						if ( @copy( $local_file_path, $file_path ) ) {
								$out = array(
									'response' => true,
									'file_name' => $file_name,
									'msg' => '',
								);
						} else {
								$out['msg'] = __( 'Unable to create temp file.' );
						}
					} else {
								$out['msg'] = __( 'Unable to create temp directory.' );
					}
				} else {
					$out['msg'] = __( 'File type not allowed.' );
				}
			} else {
				$out['msg'] = __( 'File not found.' );
			}
		} elseif ( 'url' == $file_from ) {

					$file_url = ( isset( $method_import_form_data['wt_iew_url_file'] ) ? Wt_Iew_Sh::sanitize_item( $method_import_form_data['wt_iew_url_file'], 'url' ) : '' );

			if ( '' != $file_url ) {

				$ext_arr = explode( '.', $file_url ); /* if extension specified */

				$ext = end( $ext_arr );

				if ( $ext && $this->is_extension_allowed( $file_url ) ) {
					$file_name = $this->get_temp_file_name( $ext );
					$file_path = $this->get_file_path( $file_name );

					if ( $file_path ) {
						$file_data = $this->remote_get( $file_url );

						if ( ! is_wp_error( $file_data ) && wp_remote_retrieve_response_code( $file_data ) == 200 ) {
								$file_data = wp_remote_retrieve_body( $file_data );
							if ( @file_put_contents( $file_path, $file_data ) ) {
										$out = array(
											'response' => true,
											'file_name' => $file_name,
											'msg' => '',
										);
							} else {
									$out['msg'] = __( 'Unable to create temp file.' );
							}
						} else {
								$out['msg'] = __( 'Unable to fetch file data.' );
						}
					} else {
							$out['msg'] = __( 'Unable to create temp directory.' );
					}
				} else {  // if extension not provided in the url eg: Gdrive.

					$file_path = '';
					$file_name = $this->get_temp_file_name( 'txt' );
					$local_file = $this->get_file_path( $file_name );

					// if (strpos(substr($file_url, 0, 7), 'ftp://') !== false) { // the given url is an ftp url
					// return Wt_Iew_IE_Helper::get_data_from_ftp_url($url);
					// }
					// To Do: Check and update all 3 functions below.
					$get_data_from_url = Wt_Iew_IE_Helper::wt_wpie_download_file_from_url( $file_url, $local_file );
					if ( 0 == $get_data_from_url['status'] ) {
						$out['msg'] = $get_data_from_url['error'];

						$get_data_from_url = Wt_Iew_IE_Helper::get_data_from_url_method_2( $file_url, $local_file );
						if ( 0 == @$get_data_from_url['status'] ) {
							$out['msg'] = $get_data_from_url['error'];
							/**
							 *
							$get_data_from_url = Wt_Iew_IE_Helper::get_data_from_url_method_3($file_url,$local_file);
							if(@$get_data_from_url['status']==0){
								$get_data_from_url =    Wt_Iew_IE_Helper::wt_wpie_download_file_from_url($file_url,$local_file);
								if ( is_wp_error( $get_data_from_url ) ) {
									$error = $get_data_from_url->get_error_message();
									$out['msg']=$error;
								}
								if($get_data_from_url['status']==0){
									$out['msg']=$get_data_from_url['error'];
								}else{
									$fileName = $get_data_from_url['file_name'];
									$file_path1 = $get_data_from_url['path'];
									$ext = pathinfo($fileName, PATHINFO_EXTENSION);
									if($ext && file_exists($file_path1)){
										$file_name = str_replace('.txt', '.'.$ext, $file_name);
										$new_name = str_replace('.txt', '.'.$ext, $file_path1);
										rename($file_path1, $new_name);
										$file_path1 = $new_name;
										$out=array(
											'response'=>true,
											'file_name'=>$file_name,
											'msg'=>'',
										);
									}
								}
							}else{
								$file_path = $get_data_from_url['path'];
							}
							*/
						} else {
							$file_path = $get_data_from_url['path'];
						}
					} else {
						$file_path = $get_data_from_url['path'];
					}

					if ( $file_path ) {

						$content_type = Wt_Iew_IE_Helper::wt_get_mime_content_type( $file_path );
						/**
						// if(in_array($content_type, array('text/html'))){  // if not getting valid data from the url
						// $out['msg']=__('The specified URL is not valid.');
						// $out['response']=false;
						// return $out;
						// }
						 */
						if ( in_array( $content_type, array( 'application/xml', 'text/xml' ) ) ) {
							$ext = 'xml';

						} else {
							$ext = 'csv';
						}

						if ( file_exists( $file_path ) ) {
							$file_name = str_replace( '.txt', '.' . $ext, $file_name );
							$new_name = str_replace( '.txt', '.' . $ext, $file_path );
							rename( $file_path, $new_name );
						}

						$out = array(
							'response' => true,
							'file_name' => $file_name,
							'msg' => '',
						);
					}
				}
			} else {
					$out['msg'] = __( 'The specified URL is not valid.' );
			}
		} else {
			$out['response'] = true;
			/**
			 * Taking form data.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param array           $out    Result data.
			 * @param string          $file_from    Local, FTP, URL.
			 * @param array           $method_import_form_data    Import from data method.
			 */
			$out = apply_filters( 'wt_iew_validate_file', $out, $file_from, $method_import_form_data );

			if ( is_array( $out ) && isset( $out['response'] ) && $out['response'] ) {
				$remote_adapter = Wt_Import_Export_For_Woo::get_remote_adapters( 'import', $file_from );

				if ( is_null( $remote_adapter ) ) {
					/* translators:%s: export option like remote, local */
					$out['msg'] = sprintf( __( 'Unable to initailize %s' ), $file_from );
					$out['response'] = false;
				} else {
					/* download the file */
					$out = $remote_adapter->download( $method_import_form_data, $out, $this );
				}
			}
		}
		if ( false !== $out['response'] ) {
			$file_path = self::get_file_path( $out['file_name'] );
			/**
			*   Filter to modify the import file before processing.
			*
			*   @since 1.0.0
			*
			*   @param  string $file_name name of the file.
			*   @param  string $file_path path of the file.
			*   @return  string $file_name name of the new altered file.
			*/
			$out['file_name'] = apply_filters( 'wt_iew_alter_import_file', $out['file_name'], $file_path );
		}

		return $out;
	}
	/**
	 * Remote get wrapper
	 *
	 * @param string $target_url URL.
	 */
	public function remote_get( $target_url ) {
		global $wp_version;

		$def_args = array(
			'timeout'     => 5,
			'redirection' => 5,
			'httpversion' => '1.0',
			'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
			'blocking'    => true,
			'headers'     => array(),
			'cookies'     => array(),
			'body'        => null,
			'compress'    => false,
			'decompress'  => true,
			'sslverify'   => false,
			'stream'      => false,
			'filename'    => null,
		);
				return wp_remote_get( $target_url, $def_args );
	}
	/**
	 * Get log file name
	 *
	 * @param integer $history_id History id.
	 */
	public function get_log_file_name( $history_id ) {
		return 'log_' . $history_id . '.log';
	}

	/**
	 * Get temp filename
	 *
	 * @param string $ext Extension.
	 */
	public function get_temp_file_name( $ext ) {
		/* adding rerun prefix is to easily identify rerun temp files */
		$rerun_prefix = ( $this->rerun_id > 0 ? 'rerun_' . $this->rerun_id . '_' : '' );
		return $rerun_prefix . 'temp_' . $this->to_import . '_' . time() . '.' . $ext;
	}

	/**
	 *   Get given file URL.
	 *   If file name is empty then URL will return.
	 *
	 * @param string $file_name Filename.
	 */
	public static function get_file_url( $file_name = '' ) {
		return WP_CONTENT_URL . self::$import_dir_name . '/' . $file_name;
	}

	/**
	 *   Checks the file extension is in allowed list
	 *
	 *   @param string $file_url File name/ URL.
	 *   @return boolean
	 */
	public function is_extension_allowed( $file_url ) {
		$ext_arr = explode( '.', $file_url );
		$ext = strtolower( end( $ext_arr ) );
		if ( isset( $this->allowed_import_file_type[ $ext ] ) ) {
			return true;
		}
		return false;
	}

	/**
	 *   Delete import file
	 *
	 *   @param string $file_url File path/ URL.
	 *   @return boolean
	 */
	public function delete_import_file( $file_url ) {
		$file_path_arr = explode( '/', $file_url );
		$file_name = end( $file_path_arr );
		$file_path = $this->get_file_path( $file_name );
		if ( file_exists( $file_path ) ) {
			if ( $this->is_extension_allowed( $file_url ) ) {
				@unlink( $file_path );
				return true;
			}
		}
		return false;
	}

	/**
	 *   Get given temp file path.
	 *   If file name is empty then file path will return
	 *
	 *   @param string $file_name File path/ URL.
	 *   @return string
	 */
	public static function get_file_path( $file_name = '' ) {
		if ( ! is_dir( self::$import_dir ) ) {
			if ( ! mkdir( self::$import_dir, 0700 ) ) {
				return false;
			} else {
				$files_to_create = array(
					'.htaccess' => 'deny from all',
					'index.php' => '<?php // Silence is golden',
				);
				foreach ( $files_to_create as $file => $file_content ) {
					if ( ! file_exists( self::$import_dir . '/' . $file ) ) {
						$fh = @fopen( self::$import_dir . '/' . $file, 'w' );
						if ( is_resource( $fh ) ) {
							fwrite( $fh, $file_content );
							fclose( $fh );
						}
					}
				}
			}
		}
		return self::$import_dir . '/' . $file_name;
	}

	/**
	 * Download and create a temp file. And create a history entry
	 *
	 * @param   array   $form_data Form data.
	 * @param   string  $step export step.
	 * @param   string  $to_process to export type.
	 * @param   integer $import_id id of export.
	 * @param   integer $offset offset.
	 *
	 * @return array
	 */
	public function process_download( $form_data, $step, $to_process, $import_id = 0, $offset = 0 ) {
		$out = array(
			'response' => false,
			'new_offset' => 0,
			'import_id' => 0,
			'history_id' => 0, // same as that of import id.
			'total_records' => 0,
			'finished' => 0,
			'msg' => '',
		);
		$this->to_import = $to_process;

		if ( $import_id > 0 ) {
			// take history data by import_id.
			$import_data = Wt_Import_Export_For_Woo_History::get_history_entry_by_id( $import_id );
			if ( is_null( $import_data ) ) {
				return $out;
			} else {
				$file_name = ( isset( $import_data['file_name'] ) ? $import_data['file_name'] : '' );
				$file_path = $this->get_file_path( $file_name );
				if ( $file_path && file_exists( $file_path ) ) {
					$this->temp_import_file = $file_name;
				} else {
					$msg = 'Error occurred while processing the file';
					Wt_Import_Export_For_Woo_History::record_failure( $import_id, $msg );
					$out['msg'] = __( 'Error occurred while processing the file' );
					return $out;
				}
			}
		} elseif ( 0 == $offset ) {
			if ( '' != $this->temp_import_file ) {
				$file_path = $this->get_file_path( $this->temp_import_file );
				if ( $file_path && file_exists( $file_path ) ) {
					if ( $this->is_extension_allowed( $this->temp_import_file ) ) {
						$import_id = Wt_Import_Export_For_Woo_History::create_history_entry( '', $form_data, $to_process, 'import' );
					} else {
						return $out;
					}
				} else {
					$out['msg'] = __( 'Temp file missing.' );
					return $out;
				}
			} else /* in scheduled import need to prepare the temp file */
				{
				$import_id = Wt_Import_Export_For_Woo_History::create_history_entry( '', $form_data, $to_process, 'import' );
				$response = $this->download_remote_file( $form_data );

				if ( ! $response['response'] ) {
					Wt_Import_Export_For_Woo_History::record_failure( $import_id, $response['msg'] );
					$out['msg'] = $response['msg'];
					return $out;
				} else {
					$file_path = $this->get_file_path( $response['file_name'] );
					$this->temp_import_file = $response['file_name'];
				}
			}
		}

		/**
		* In XML import we need to convert the file into CSV before processing
		* It may be a batched processing for larger files
		*/
		$ext_arr = explode( '.', $this->temp_import_file );
		if ( 'xml' == end( $ext_arr ) ) {
			include_once WT_IEW_PLUGIN_PATH . 'admin/classes/class-wt-import-export-for-woo-xmlreader.php';
			$reader = new Wt_Import_Export_For_Woo_Xmlreader();
			$xml_file = $this->get_file_path( $this->temp_import_file );
			$csv_file_name = str_replace( '.xml', '.csv', $this->temp_import_file );
			$csv_file = $this->get_file_path( $csv_file_name );
			$response = $reader->xml_to_csv( $xml_file, $csv_file, $offset );

			if ( 0 == $offset ) {
				$form_data = $this->_set_csv_delimiter( $form_data, $import_id );
			}

			if ( $response['response'] ) {
				$out['finished'] = $response['finished'];
				if ( 1 == $out['finished'] ) {
					/**
					*   Remove the XML file
					*   And set the CSV file as temp file
					*/
					@unlink( $xml_file );
					$this->temp_import_file = $csv_file_name;
					$out = $this->_set_import_file_processing_finished( $csv_file, $import_id );
				} else {
					/**
					*   Update the existing XML file name to DB. This is necessary for scheduled imports
					*/
					if ( 0 == $offset ) {
						$update_data = array(
							'file_name' => $this->temp_import_file,
						);
						$update_data_type = array(
							'%s',
						);
						Wt_Import_Export_For_Woo_History::update_history_entry( $import_id, $update_data, $update_data_type );
					}

					/**
					*   Prepare response for next batch processing
					*/
					$out = array(
						'response' => true,
						'finished' => 0,
						'new_offset' => $response['new_offset'],
						'import_id' => $import_id,
						'history_id' => $import_id, // same as that of import id.
						'total_records' => 0,
						'msg' => __( 'Processing input file...' ),
					);
				}
			} else {
				$msg = 'Error occurred while processing XML';
				Wt_Import_Export_For_Woo_History::record_failure( $import_id, $msg );
				$out['msg'] = __( 'Error occurred while processing XML' );
				return $out;
			}
		} else {
			$out = $this->_set_import_file_processing_finished( $file_path, $import_id );
		}
		return $out;
	}

	/**
	 *   If the file type is not CSV (Eg: XML) Then the delimiter must be ",".
	 *   Because we are converting XML to CSV
	 *
	 * @param array   $form_data Form data.
	 * @param integer $import_id Import ID.
	 */
	protected function _set_csv_delimiter( $form_data, $import_id ) {
		$form_data['method_import_form_data']['wt_iew_delimiter'] = ',';

		$update_data = array(
			'data' => maybe_serialize( $form_data ), // formadata.
		);
		$update_data_type = array(
			'%s',
		);
		Wt_Import_Export_For_Woo_History::update_history_entry( $import_id, $update_data, $update_data_type );

		return $form_data;
	}
	/**
	 * Import file processing finished
	 *
	 * @param string  $file_path File path.
	 * @param integer $import_id Import id.
	 * @return array
	 */
	protected function _set_import_file_processing_finished( $file_path, $import_id ) {
		/* update total records, temp file name in history table */
		$total_records = filesize( $file_path ); /* in this case we cannot count number of rows */
		$update_data = array(
			'total' => $total_records,
			'file_name' => $this->temp_import_file,
		);
		$update_data_type = array(
			'%d',
			'%s',
		);
		Wt_Import_Export_For_Woo_History::update_history_entry( $import_id, $update_data, $update_data_type );

		return array(
			'response' => true,
			'finished' => 3,
			'import_id' => $import_id,
			'history_id' => $import_id, // same as that of import id.
			'total_records' => $total_records,
			'temp_import_file' => $this->temp_import_file,
			/* translators:%d: Progress percent */
			'msg' => sprintf( __( 'Importing...(%d processed)' ), 0 ),
		);
	}


	/**
	 *   Do the import process
	 *
	 * @param   array   $form_data Form data.
	 * @param   string  $step export step.
	 * @param   string  $to_process to export type.
	 * @param   string  $file_name Filename.
	 * @param   integer $import_id id of export.
	 * @param   integer $offset offset.
	 *
	 * @return array
	 */
	public function process_action( $form_data, $step, $to_process, $file_name = '', $import_id = 0, $offset = 0 ) {

		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );

		$out = array(
			'response' => false,
			'new_offset' => 0,
			'import_id' => 0,
			'history_id' => 0, // same as that of import id.
			'total_records' => 0,
			'offset_count' => 0,
			'finished' => 0,
			'msg' => '',
			'total_success' => 0,
			'total_failed' => 0,
		);

		if ( ! wp_doing_cron() && ! ( Wt_Import_Export_For_Woo_Cron::$url_cron_enabled ) && ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
			return $out;
		}

		$this->to_import = $to_process;
		$this->to_process = $to_process;

		/* prepare formdata, If this was not first batch */
		if ( $import_id > 0 ) {
			// take history data by import_id.
			$import_data = Wt_Import_Export_For_Woo_History::get_history_entry_by_id( $import_id );
			if ( is_null( $import_data ) ) {
				return $out;
			}

			// processing form data.
			$form_data = ( isset( $import_data['data'] ) ? maybe_unserialize( $import_data['data'] ) : array() );

		} else // No import id so it may be an error.
		{
			return $out;
		}

		/* setting history_id in Log section */
		Wt_Import_Export_For_Woo_Log::$history_id = $import_id;

		$file_name = ( isset( $import_data['file_name'] ) ? $import_data['file_name'] : '' );
		$file_path = $this->get_file_path( $file_name );
		if ( $file_path ) {
			if ( ! file_exists( $file_path ) ) {
				$msg = 'Temp file missing';
				// no need to add translation function in message.
				Wt_Import_Export_For_Woo_History::record_failure( $import_id, $msg );
				$out['msg'] = __( 'Temp file missing' );
				return $out;
			}
		} else {
			$msg = 'Temp file missing';
			// no need to add translation function in message.
			Wt_Import_Export_For_Woo_History::record_failure( $import_id, $msg );
			$out['msg'] = __( 'Temp file missing' );
			return $out;
		}
		/**
		*   Alter import batch.
		*
		*   @since 1.0.0
		*
		*   @param  int   $this->default_batch_count  Default batch count
		*   @param  string  $to_process          Post type
		*   @param   array   $form_data Data of export.
		*   @return array   $export_data        Altered export data
		*/
		$default_batch_count = absint( apply_filters( 'wt_iew_importer_alter_default_batch_count', $this->default_batch_count, $to_process, $form_data ) );
		$default_batch_count = ( 0 == $default_batch_count ? $this->default_batch_count : $default_batch_count );

		$batch_count = $default_batch_count;
		$csv_delimiter = ',';
		$total_records = ( isset( $import_data['total'] ) ? $import_data['total'] : 0 );
		$file_ext_arr = explode( '.', $file_name );
		$file_type = strtolower( end( $file_ext_arr ) );
		$file_type = ( isset( $this->allowed_import_file_type[ $file_type ] ) ? $file_type : 'csv' );

		if ( isset( $form_data['advanced_form_data'] ) ) {
			$batch_count = ( isset( $form_data['advanced_form_data']['wt_iew_batch_count'] ) ? $form_data['advanced_form_data']['wt_iew_batch_count'] : $batch_count );
		}
		if ( isset( $form_data['method_import_form_data'] ) && ( 'csv' == $file_type || 'txt' === $file_type ) ) {
			$csv_delimiter = ( isset( $form_data['method_import_form_data']['wt_iew_delimiter'] ) ? $form_data['method_import_form_data']['wt_iew_delimiter'] : $csv_delimiter );
			$csv_delimiter = ( '' == $csv_delimiter ? ',' : $csv_delimiter );
		}

		if ( 'xml' == $file_type ) {
			include_once WT_IEW_PLUGIN_PATH . 'admin/classes/class-wt-import-export-for-woo-xmlreader.php';
			$reader = new Wt_Import_Export_For_Woo_Xmlreader();
		} else {
			include_once WT_IEW_PLUGIN_PATH . 'admin/classes/class-wt-import-export-for-woo-csvreader.php';
			$reader = new Wt_Import_Export_For_Woo_Csvreader( $csv_delimiter );
		}

		/* important: prepare deafult mapping formdata for quick import */
		$input_data = $reader->get_data_as_batch( $file_path, $offset, $batch_count, $this, $form_data );

		if ( empty( $input_data['data_arr'] ) ) {
			$out['msg'] = __( 'CSV is empty' );
			return $out;
		}

		if ( ! $input_data || ! is_array( $input_data ) ) {
			$msg = 'Unable to process the file';
			// no need to add translation function in message.
			Wt_Import_Export_For_Woo_History::record_failure( $import_id, $msg );
			$out['msg'] = __( 'Unable to process the file' );
			return $out;
		}

		/* checking action is finshed */
		$is_last_offset = false;
		$new_offset = $input_data['offset']; // increase the offset.
		if ( $new_offset >= $total_records ) {
			$is_last_offset = true;
		}

		/**
		*   In case of non schedule import. Offset row count.
		*   The real internal offset is in bytes, This offset is total row processed.
		*/
		$offset_count = 0;
		if ( ! wp_doing_cron() && ! ( Wt_Import_Export_For_Woo_Cron::$url_cron_enabled ) ) {
			$offset_count = ( isset( $_POST['offset_count'] ) ? absint( $_POST['offset_count'] ) : 0 );// phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		/**
		 * Taking form data.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $form_data    Form posted data.
		 * @param string          $to_process    Current action.
		 * @param string          $step    Step.
		 * @param array           $this->selected_template_data    Selected template data.
		 */
		$form_data = apply_filters( 'wt_iew_import_full_form_data', $form_data, $to_process, $step, $this->selected_template_data );

		/* in scheduled import. The import method will not available so we need to take it from formdata */
		$formdata_import_method = ( isset( $formdata['method_import_form_data'] ) && isset( $formdata['method_import_form_data']['method_import'] ) ? $formdata['method_import_form_data']['method_import'] : 'quick' );
		$this->import_method = ( '' == $this->import_method ? $formdata_import_method : $this->import_method );

				/**
				 *
				 No form data to process/Couldnt get any data from csv
				 */
		// if($this->import_method == 'quick'){.
		if ( empty( $form_data['mapping_form_data']['mapping_fields'] ) ) {
			$msg = 'Please verify the data/delimiter in the CSV and try again.';
			// no need to add translation function in message.
			Wt_Import_Export_For_Woo_History::record_failure( $import_id, $msg );
			$out['msg'] = __( 'Please verify the data/delimiter in the CSV and try again.' );
			return $out;

		}
		// }

		/**
		*   Import response format
		*/
		$import_response = array(
			'total_success' => $batch_count,
			'total_failed' => 0,
			'log_data' => array(
				array(
					'row' => $offset_count,
					'message' => '',
					'status' => true,
					'post_id' => '',
				),
			),
		);
		/**
		 * Taking import data.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $import_data    Export data.
		 * @param string          $to_process    Current action.
		 * @param string          $step    Step.
		 * @param array           $form_data    Form posted data.
		 * @param array           $this->selected_template_data    Selected template data.
		 * @param string          $this->export_method    Current action.
		 * @param string          $offset    Offset number.
		 * @param string          $is_last_offset    Is last offset number.
		 */
		$import_response = apply_filters( 'wt_iew_importer_do_import', $input_data['data_arr'], $to_process, $step, $form_data, $this->selected_template_data, $this->import_method, $offset_count, $is_last_offset );

		/**
		*   Writing import log to file
		*/
		if ( ! empty( $import_response ) && is_array( $import_response ) ) {
			$log_writer = new Wt_Import_Export_For_Woo_Logwriter();
			$log_file_name = $this->get_log_file_name( $import_id );
			$log_file_path = $this->get_file_path( $log_file_name );
			$log_data = ( isset( $import_response['log_data'] ) && is_array( $import_response['log_data'] ) ? $import_response['log_data'] : array() );
			$log_writer->write_import_log( $log_data, $log_file_path );
		}

		/* updating completed offset */
		$update_data = array(
			'offset' => $offset,
		);
		$update_data_type = array(
			'%d',
		);
		Wt_Import_Export_For_Woo_History::update_history_entry( $import_id, $update_data, $update_data_type );

		/* updating output parameters */
		$out['total_records'] = $total_records;
		$out['import_id'] = $import_id;
		$out['history_id'] = $import_id;
		$out['response'] = true;
		$total_success = 0;
		$total_failed = 0;
		/* In case of non schedule import. total success, totla failed count */
		if ( ! wp_doing_cron() && ! ( Wt_Import_Export_For_Woo_Cron::$url_cron_enabled ) ) {
			$total_success = ( isset( $_POST['total_success'] ) ? absint( $_POST['total_success'] ) : 0 );// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$total_failed = ( isset( $_POST['total_failed'] ) ? absint( $_POST['total_failed'] ) : 0 );// phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		$out['total_success'] = ( isset( $import_response['total_success'] ) ? $import_response['total_success'] : 0 ) + $total_success;
		$out['total_failed'] = ( isset( $import_response['total_failed'] ) ? $import_response['total_failed'] : 0 ) + $total_failed;

		/* updating action is finshed */
		if ( $is_last_offset ) {
			/**
			 * Filter after import done.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param string          $to_process    Current action.
			 * @param string          $step    Step.
			 * @param array           $form_data    Form posted data.
			 * @param array           $this->selected_template_data    Selected template data.
			 * @param string          $this->import_method    Current action.
			 */
			apply_filters( 'wt_iew_importer_done_import', $to_process, $step, $form_data, $this->selected_template_data, $this->import_method );

			/* delete the temp file */
			@unlink( $file_path );

			$log_summary_msg = $this->generate_log_summary( $out, $is_last_offset );

			$out['finished'] = 1; // finished.
			$out['msg'] = $log_summary_msg;

			/* updating finished status */
			$update_data = array(
				'status' => Wt_Import_Export_For_Woo_History::$status_arr['finished'],
				'status_text' => 'Finished', // translation function not needed.
			);
			$update_data_type = array(
				'%d',
				'%s',
			);
			Wt_Import_Export_For_Woo_History::update_history_entry( $import_id, $update_data, $update_data_type );
		} else {
			$rows_processed = $input_data['rows_processed'];
			$total_processed = $rows_processed + $offset_count;

			$out['offset_count'] = $total_processed;
			$out['new_offset'] = $new_offset;

			$log_summary_msg = $this->generate_log_summary( $out, $is_last_offset );

			$out['msg'] = $log_summary_msg;
		}

		return $out;
	}
	/**
	 * Generate log
	 *
	 * @param array $data Log data.
	 * @param type  $is_last_offset Is last offset.
	 * @return string
	 */
	protected function generate_log_summary( $data, $is_last_offset ) {
		if ( $is_last_offset ) {
			$msg = '<span class="wt_iew_info_box_title">' . __( 'Finished' ) . '</span>';
						$msg .= '<span class="wt_iew_popup_close" style="line-height:10px;width:auto" onclick="wt_iew_import.hide_import_info_box();">X</span>';
		} else {
			/* translators:%s: Progress percentage */
			$msg = '<span class="wt_iew_info_box_title">' . sprintf( __( 'Importing...(%d processed)' ), $data['offset_count'] ) . '</span>';
		}
		$msg .= '<br />' . __( 'Total success: ' ) . $data['total_success'] . '<br />' . __( 'Total failed: ' ) . $data['total_failed'];
		if ( $is_last_offset ) {
			$msg .= '<span class="wt_iew_info_box_finished_text" style="display:block">';
			if ( Wt_Import_Export_For_Woo_Admin::module_exists( 'history' ) ) {
								$msg .= '<a class="button button-secondary wt_iew_view_log_btn" style="margin-top:10px;" data-history-id="' . $data['history_id'] . '" onclick="wt_iew_import.hide_import_info_box();">' . __( 'View Details' ) . '</a></span>';
			}
		}
		return $msg;
	}

	/**
	 *   Main ajax hook to handle all import related requests
	 */
	public function ajax_main() {
		include_once plugin_dir_path( __FILE__ ) . 'classes/class-wt-import-export-for-woo-import-ajax.php';
		if ( Wt_Iew_Sh::check_write_access( WT_IEW_PLUGIN_ID ) ) {
			/**
			*   Check it is a rerun call
			*/
			// Nonce already verified on main function - sanitization thorugh helper functions.
			$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
			if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
					return false;
			}
			if ( ! $this->_process_rerun( ( isset( $_POST['rerun_id'] ) ? absint( $_POST['rerun_id'] ) : 0 ) ) ) {
				$this->import_method = ( isset( $_POST['import_method'] ) ? sanitize_text_field( wp_unslash( $_POST['import_method'] ) ) : '' );
				$this->to_import = ( isset( $_POST['to_import'] ) ? sanitize_text_field( wp_unslash( $_POST['to_import'] ) ) : '' );
				$this->selected_template = ( isset( $_POST['selected_template'] ) ? intval( wp_unslash( $_POST['selected_template'] ) ) : 0 );
			}

			$this->get_steps();

			$ajax_obj = new Wt_Import_Export_For_Woo_Import_Ajax( $this, $this->to_import, $this->steps, $this->import_method, $this->selected_template, $this->rerun_id );

			$import_action = isset( $_POST['import_action'] ) ? sanitize_text_field( wp_unslash( $_POST['import_action'] ) ) : '';
			$data_type = isset( $_POST['data_type'] ) ? sanitize_text_field( wp_unslash( $_POST['data_type'] ) ) : '';

			$allowed_ajax_actions = array( 'get_steps', 'validate_file', 'get_meta_mapping_fields', 'save_template', 'save_template_as', 'update_template', 'download', 'import', 'upload_import_file', 'delete_import_file' );

			$out = array(
				'status' => 0,
				'msg' => __( 'Error' ),
			);

			if ( method_exists( $ajax_obj, $import_action ) && in_array( $import_action, $allowed_ajax_actions ) ) {
				$out = $ajax_obj->{$import_action}( $out );
			}

			if ( 'json' == $data_type ) {
				echo json_encode( $out );
			}
		}
		exit();
	}
	/**
	 * Process column
	 *
	 * @param array $input_file_data_row Data row.
	 * @param array $form_data From data.
	 * @return array
	 */
	public function process_column_val( $input_file_data_row, $form_data ) {
		$out = array(
			'mapping_fields' => array(),
			'meta_mapping_fields' => array(),
		);

				$this->skip_from_evaluation_array = $this->get_skip_from_evaluation();
				$this->decimal_columns = $this->get_decimal_columns();

		/**
		*   Default columns
		*/
		$mapping_form_data = ( isset( $form_data['mapping_form_data'] ) ? $form_data['mapping_form_data'] : array() );
		$mapping_selected_fields = ( isset( $mapping_form_data['mapping_selected_fields'] ) ? $mapping_form_data['mapping_selected_fields'] : array() );
		$mapping_fields = ( isset( $mapping_form_data['mapping_fields'] ) ? $mapping_form_data['mapping_fields'] : array() );

		/**
		*   Input date format.
		*   This will be taken as the global date format for all date fields in the input file.
		*   If date format is specified in the evaluation section. Then this value will be overriden.
		*/
		$method_import_form_data = ( isset( $form_data['method_import_form_data'] ) ? $form_data['method_import_form_data'] : array() );
		$input_date_format = ( isset( $method_import_form_data['wt_iew_date_format'] ) ? $method_import_form_data['wt_iew_date_format'] : '' );

		foreach ( $mapping_selected_fields as $key => $value ) {
			$out['mapping_fields'][ $key ] = $this->evaluate_data( $key, $value, $input_file_data_row, $mapping_fields, $input_date_format );
		}
		$mapping_form_data = null;
		$mapping_fields = null;
		$mapping_selected_fields = null;
		unset( $mapping_form_data, $mapping_fields, $mapping_selected_fields );

		/**
		*   Meta columns
		*/
		$meta_step_form_data = ( isset( $form_data['meta_step_form_data'] ) ? $form_data['meta_step_form_data'] : array() );
		$mapping_selected_fields = ( isset( $meta_step_form_data['mapping_selected_fields'] ) ? $meta_step_form_data['mapping_selected_fields'] : array() );
		$mapping_fields = ( isset( $meta_step_form_data['mapping_fields'] ) ? $meta_step_form_data['mapping_fields'] : array() );
		foreach ( $mapping_selected_fields as $meta_key => $meta_val_arr ) {
			$out['meta_mapping_fields'][ $meta_key ] = array();
			$meta_fields_arr = ( isset( $mapping_fields[ $meta_key ] ) ? $mapping_fields[ $meta_key ] : array() );
			foreach ( $meta_val_arr as $key => $value ) {
				$out['meta_mapping_fields'][ $meta_key ][ $key ] = $this->evaluate_data( $key, $value, $input_file_data_row, $meta_fields_arr, $input_date_format );
			}
		}
		$meta_step_form_data = null;
		$mapping_fields = null;
		$mapping_selected_fields = null;
		$input_file_data_row = null;
		$form_data = null;
		unset( $meta_step_form_data, $mapping_fields, $mapping_selected_fields, $input_file_data_row, $form_data );

		return $out;
	}
	/**
	 * Evaluate date
	 *
	 * @param string $key Key.
	 * @param string $value Column value.
	 * @param array  $data_row Data row.
	 * @param array  $mapping_fields Mapping fields.
	 * @param string $input_date_format Date format.
	 * @return string
	 */
	protected function evaluate_data( $key, $value, $data_row, $mapping_fields, $input_date_format ) {

		if ( 1 == preg_match( '/{(.*?)}/', $value, $match ) ) {
				   $maping_key = $match[1] ? $match[1] : '';
		}

		$value = $this->add_input_file_data( $key, $value, $data_row, $mapping_fields, $input_date_format, true );

		if ( isset( $maping_key ) ) {
			$value = ! empty( $data_row[ $maping_key ] ) ? $this->do_arithmetic( $value ) : $value;
		}

		$wt_string_is_json = is_string( $value ) && is_array( json_decode( $value, true ) ) && ( json_last_error() == JSON_ERROR_NONE ) ? true : false;
		if ( ! $wt_string_is_json && ! is_serialized( $value ) ) {
			$value = $this->add_input_file_data( $key, $value, $data_row, $mapping_fields, $input_date_format );
		}

		$data_row = null;
		unset( $data_row );
		return $value;
	}
	/**
	 * Do arithmetic operations
	 *
	 * @param string $str Row data single column.
	 * @return string
	 */
	protected function do_arithmetic( $str ) {

		$re = '/\[([0-9()+\-*\/. ]+)\]/m';
		$matches = array();
		$find = array();
		$replace = array();
		if ( preg_match_all( $re, $str, $matches, PREG_SET_ORDER, 0 ) ) {
			include_once 'classes/class-wt-evaluator.php';
			foreach ( $matches as $key => $value ) {
				if ( is_array( $value ) && count( $value ) > 1 ) {
					$synatx = $this->validate_syntax( $value[1] );
					if ( $synatx ) {
						$evaluator = new Wt_Evaluator( $synatx );
						$replace[]  = $evaluator->evaluate();
						// @codingStandardsIgnoreLine
					} else {
						$replace[] = '';
					}
					$find[] = $value[0];
					unset( $synatx );
				}
			}
		}
		return str_replace( $find, $replace, $str );
	}
	/**
	 * Validate Syntax
	 *
	 * @param string $val Column value.
	 * @return string|bool
	 */
	protected function validate_syntax( $val ) {
		$open_bracket = substr_count( $val, '(' );
		$close_bracket = substr_count( $val, ')' );
		if ( $close_bracket != $open_bracket ) {
			return false; // invalid.
		}

		// remove whitespaces.
		$val = str_replace( ' ', '', $val );
		$re_after = '/\b[\+|*|\-|\/]([^0-9\+\-\(])/m';
		$re_before = '/([^0-9\+\-\)])[\+|*|\-|\/]/m';

		$match_after = array();
		$match_before = array();
		if ( preg_match_all( $re_after, $val, $match_after, PREG_SET_ORDER, 0 ) || preg_match_all( $re_before, $val, $match_before, PREG_SET_ORDER, 0 ) ) {
			return false; // invalid.
		}

		unset( $match_after, $match_before, $re_after, $re_before );

		/* process + and - symbols */
		$val = preg_replace( array( '/\+{2,}/m', '/\-{2,}/m' ), array( '+', '- -' ), $val );

		return $val;
	}
	/**
	 * Add input data to evaluation section
	 *
	 * @param string $key Key.
	 * @param string $str Value.
	 * @param array  $data_row Data Row.
	 * @param array  $mapping_fields Mapping fields.
	 * @param string $input_date_format date format.
	 * @param bool   $skip_from_evaluation Skip from evaluation.
	 * @return string
	 */
	protected function add_input_file_data( $key, $str, $data_row, $mapping_fields, $input_date_format, $skip_from_evaluation = false ) {
		// add_filter( 'http_request_timeout', array( $this, 'bump_request_timeout' ) );.
		@set_time_limit( 0 );
		// $mapping_arr = array(); //IER-198
		// foreach ( $mapping_fields as $map_key => $map_value ) {
		// if ( isset( $map_value[ 1 ] ) && $map_value[ 1 ] == 1 ) {
		// $mapping_arr[ $map_key ] = isset( $map_value[ 0 ] ) ? $map_value[ 0 ] : '';
		// }
		// }
		$re = '/\{([^}]+)\}/m';
		$matches = array();
		preg_match_all( $re, $str, $matches, PREG_SET_ORDER, 0 );
		$find = array();
		$replace = array();
		foreach ( $matches as $key => $value ) {
			if ( is_array( $value ) && count( $value ) > 1 ) {
				$data_key = trim( $value[1] );

				/* Check for date formatting */
				$data_key_arr = explode( Wt_Import_Export_For_Woo_Admin::$wt_iew_prefix . '@', $data_key );
				$data_format = '';
				if ( count( $data_key_arr ) == 2 ) {
					$data_key = $data_key_arr[0]; // first value is the field key.
					$data_format = $data_key_arr[1]; // second value will be the format.
				}

				/* Pre-defined date field */
				if ( isset( $mapping_fields[ $data_key ] ) && isset( $mapping_fields[ $data_key ][2] ) && 'date' == $mapping_fields[ $data_key ][2] ) {
					/**
					*   Always give preference to evaluation section
					*   If not specified in evaluation section. Use default format
					*/
					if ( '' == $data_format ) {
						$data_format = $input_date_format;
					}
				}

				$output_val = '';
				if ( isset( $data_row[ $data_key ] ) ) {
					// $output_val=sanitize_text_field($data_row[$data_key]);   sanitize_text_field stripping html content
					$output_val = ( $data_row[ $data_key ] );
				}

				/**
				*   This is a date field
				*/
				$data_format = ( is_array( $data_format ) ) ? $data_format[1] : $data_format;
				if ( '' != trim( $data_format ) && '' != trim( $output_val ) ) {
					if ( version_compare( PHP_VERSION, '5.6.0', '>=' ) ) {
						$date_obj = DateTime::createFromFormat( $data_format, $output_val );
						if ( $date_obj ) {
							$output_val = $date_obj->format( 'Y-m-d H:i:s' );
						}
					} else {
						$output_val = gmdate( 'Y-m-d H:i:s', strtotime( trim( str_replace( '/', '-', str_replace( '-', '', $output_val ) ) ) ) );// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					}
				}

								$is_need_to_replace = false;

				if ( $skip_from_evaluation ) {   /* check whether skip or not */

					if ( false !== strpos( $value[1], 'line_item_' ) ) {   /* line item content gets trimmed when ';' occurred in serialized data */
						$value[1] = 'line_item_';// substr($value[1], 10,10);.
					}

					if ( ! in_array( $value[1], $this->skip_from_evaluation_array ) ) {      /*  current item dont skip */
						$is_need_to_replace = true;
					}
				} else { /* no needed to skip, so add all items to find and replace list */
					$is_need_to_replace = true;
				}
				if ( is_serialized( $value[1] ) ) {
					$is_need_to_replace = false;
				}
				if ( $is_need_to_replace ) {
					if ( in_array( $value[1], $this->decimal_columns ) ) { /* check if it is a decimal column , if yes, format it */
						$output_val = Wt_Import_Export_For_Woo_Common_Helper::wt_format_decimal( $output_val );
					}
					$replace[] = $output_val;
					$find[] = $value[0];
				}
				unset( $data_key );
			}
		}
		$data_row = null;
		unset( $data_row );

		// if (isset($find[0]) && !empty($find[0]) && !empty($mapping_arr)) {//IER-198
		// if (in_array($find[0], $mapping_arr)) {
		// $str = str_replace($find, $replace, $str);
		// }
		// }
		// return $str;.
		return str_replace( $find, $replace, $str );
	}
	/**
	 * Skip from evaluation
	 *
	 * @return array
	 */
	public function get_skip_from_evaluation() {
		/**
		 * Skip fields from evaluation.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $fields    Form posted data.
		 */
		return apply_filters( 'wt_iew_importer_skip_from_evaluation', array( 'post_title', 'description', 'post_content', 'short_description', 'post_excerpt', 'line_item_', 'shipping_items', 'fee_items', 'customer_note', 'order_notes', 'meta:_eh_stripe_payment_balance', 'meta:_eh_stripe_payment_charge', 'meta:wc_productdata_options', 'order_items' ) );
	}
	/**
	 * Decimal columns
	 *
	 * @return array
	 */
	public function get_decimal_columns() {
		/**
		 * Decimal columns.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $fields    Form posted data.
		 */
		return apply_filters( 'wt_iew_importer_decimal_columns', array( 'price', 'regular_price', '_regular_price', 'sale_price', '_sale_price' ) );
	}
	/**
	 * Format decimal columns
	 *
	 * @param string $value Value.
	 * @param string $key  Key.
	 * @param array  $decimal_columns Decimal columns.
	 * @return type
	 */
	public function format_decimal_columns( $value, $key, $decimal_columns ) {
		if ( in_array( $key, $decimal_columns ) ) {
			return Wt_Import_Export_For_Woo_Common_Helper::wt_format_decimal( $value );
		}
		return $value;
	}

	/**
	 * Added to http_request_timeout filter to force timeout at 60 seconds during import
	 *
	 * @param integer $val Bump time out.
	 * @return int 60
	 */
	public function bump_request_timeout( $val ) {
		return 60;
	}
}
Wt_Import_Export_For_Woo::$loaded_modules['import'] = new Wt_Import_Export_For_Woo_Import();
