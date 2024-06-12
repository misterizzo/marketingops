<?php
/**
 * Handles the export actions.
 *
 * @package   ImportExportSuite\Admin\Modules\Export
 * @version   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_Cron Class.
 */
class Wt_Import_Export_For_Woo_Export {
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
	 * Module base
	 *
	 * @var string
	 */
	public $module_base = 'export';
	/**
	 * Export path
	 *
	 * @var string
	 */
	public static $export_dir = WP_CONTENT_DIR . '/webtoffee_export';
	/**
	 * Export directory
	 *
	 * @var string
	 */
	public static $export_dir_name = '/webtoffee_export';
	/**
	 * Steps
	 *
	 * @var string
	 */
	public $steps = array();
	/**
	 * Allowed types
	 *
	 * @var string
	 */
	public $allowed_export_file_type = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $to_export = '';
	/**
	 * Module ID
	 *
	 * @var string
	 */
	private $to_export_id = '';
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
	public $export_method = '';
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $export_methods = array();
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
	 * Selected template data
	 *
	 * @var array
	 */
	public $selected_template_data = array();
	/**
	 * Default export method
	 *
	 * @var string
	 */
	public $default_export_method = '';  /* configure this value in `advanced_setting_fields` method */
	/**
	 * Form data
	 *
	 * @var array
	 */
	public $form_data = array();
	/**
	 * Steps that needs pass through validation
	 *
	 * @var array
	 */
	public $step_need_validation_filter = array();
	/**
	 * Validation rules for the step form fields
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

		/* allowed file types */
		$this->allowed_export_file_type = array(
			'csv' => __( 'CSV' ),
			'xml' => __( 'XML' ),
		);

		/* default step list */
		$this->steps = array(
			'post_type' => array(
				'title' => __( 'Select a post type', 'import-export-suite-for-woocommerce' ),
				'description' => __( 'Export and download the respective post type into a CSV or XML. This file can also be used to import data related to the specific post type back into your WooCommerce shop. As a first step you need to choose the post type to start the export.', 'import-export-suite-for-woocommerce' ),
			),
			'method_export' => array(
				'title' => __( 'Select an export method', 'import-export-suite-for-woocommerce' ),
				'description' => __( 'Choose from the options below to continue with your export: quick export from DB, based on a pre-saved template or a new export with advanced options.', 'import-export-suite-for-woocommerce' ),
			),
			'filter' => array(
				'title' => __( 'Filter data', 'import-export-suite-for-woocommerce' ),
				'description' => __( 'Filter data that needs to be exported as per the below criteria.', 'import-export-suite-for-woocommerce' ),
			),
			'mapping' => array(
				'title' => __( 'Map and reorder export columns', 'import-export-suite-for-woocommerce' ),
				'description' => __( 'The default export column names can be edited from the screen below, if required. If you have chosen a pre-saved template you can see the preferred names and choices that were last saved. You may also drag the columns accordingly to reorder them within the output file.', 'import-export-suite-for-woocommerce' ),
			),
			'advanced' => array(
				'title' => __( 'Advanced options/Batch export/Scheduling', 'import-export-suite-for-woocommerce' ),
				'description' => __( 'Use options from below to decide on the batch export count, schedule an export and/or export images separately. You can also save the template file for future exports.', 'import-export-suite-for-woocommerce' ),
			),
		);

		$this->validation_rule = array(
			'post_type' => array(), /* no validation rule. So default sanitization text */
			'method_export' => array(
				'mapping_enabled_fields' => array( 'type' => 'text_arr' ), // in case of quick export.
			),
		);

		$this->step_need_validation_filter = array( 'filter', 'advanced' );

		$this->export_methods = array(
			'quick' => array(
				'title' => __( 'Quick export', 'import-export-suite-for-woocommerce' ),
				'description' => __( 'Exports all the basic fields.', 'import-export-suite-for-woocommerce' ),
			),
			'template' => array(
				'title' => __( 'Pre-saved template', 'import-export-suite-for-woocommerce' ),
				'description' => __( 'Exports data as per the specifications(filters,selective column,mapping etc) from the previously saved file.', 'import-export-suite-for-woocommerce' ),
			),
			'new' => array(
				'title' => __( 'Advanced export', 'import-export-suite-for-woocommerce' ),
				'description' => __( 'Exports data after a detailed process of data filtering/column selection/advanced options that may be required for your export. You can also save the selections as a template for future use.', 'import-export-suite-for-woocommerce' ),
			),
		);

		/* advanced plugin settings */
		add_filter( 'wt_iew_advanced_setting_fields', array( $this, 'advanced_setting_fields' ), 11 );

		/* setting default values, this method must be below of advanced setting filter */
		$this->get_defaults();

		/* main ajax hook. The callback function will decide which is to execute. */
		add_action( 'wp_ajax_iew_export_ajax', array( $this, 'ajax_main' ), 11 );

		/* Admin menu for export */
		add_filter( 'wt_iew_admin_menu', array( $this, 'add_admin_pages' ), 10, 1 );

		/* Download export file via nonce URL */
		add_action( 'admin_init', array( $this, 'download_file' ), 11 );
	}

	/**
	 * Get default setting options.
	 *
	 * @since 1.0.0
	 */
	public function get_defaults() {
		$this->default_export_method = Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings( 'default_export_method' );
		$this->default_batch_count = Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings( 'default_export_batch' );
	}

	/**
	 *   Fields for advanced settings.
	 *
	 * @since 1.0.0
	 * @param array $fields Settings fields.
	 * @return array
	 */
	public function advanced_setting_fields( $fields ) {
		$export_methods = array_map(
			function ( $vl ) {
				return $vl['title'];
			},
			$this->export_methods
		);
		$fields['default_export_method'] = array(
			'label' => __( 'Default Export method', 'import-export-suite-for-woocommerce' ),
			'type' => 'select',
			'sele_vals' => $export_methods,
			'value' => 'new',
			'field_name' => 'default_export_method',
			'field_group' => 'advanced_field',
			'help_text' => __( 'Select the default method of export.', 'import-export-suite-for-woocommerce' ),
		);
		$fields['default_export_batch'] = array(
			'label' => __( 'Default Export batch count', 'import-export-suite-for-woocommerce' ),
			'type' => 'number',
			'value' => 100,
			'field_name' => 'default_export_batch',
			'help_text' => __( 'Provide the default count for the records to be exported in a batch.', 'import-export-suite-for-woocommerce' ),
			'validation_rule' => array( 'type' => 'absint' ),
		);
		$fields['enable_chatgpt'] = array(
			'label' => __( 'Enable ChatGPT' ),
			'value' => 0,
			'checkbox_fields' => array( 1 => __( 'Enable' ) ),
			'type' => 'checkbox',
			'field_name' => 'enable_chatgpt',
			'field_group' => 'advanced_field',
			'help_text' => __( 'Automatically generate product descriptions from product titles using ChatGPT API for products without descriptions in the importing CSV.' ),
			'form_toggler' => array(
				'type' => 'parent',
				'target' => 'wt_iew_enable_chatgpt',
			),
		);
		$fields['chatgpt_api_key'] = array(
			'label' => __( 'ChatGPT API key' ),
			'type' => 'text',
			'value' => '',
			'field_name' => 'chatgpt_api_key',
			'field_group' => 'advanced_field',
			/* translators: 1: opening <a> tag. 2: closing <a> tag. 3: opening <a> tag. 4: closing <a> tag */
			'help_text' => sprintf( __( 'Input the ChatGPT API key to enable the automatic generation of product descriptions. <br> %1$s Where do I get my API Keys? %2$s  %3$s How to check the usage limit? %4$s' ), '<a href="https://help.openai.com/en/articles/4936850-where-do-i-find-my-secret-api-key" target="_blank">', '</a>', '&nbsp;<a href="https://platform.openai.com/account/billing/limits" target="_blank">', '</a>' ),
			'validation_rule' => array( 'type' => 'text' ),
			'form_toggler' => array(
				'type' => 'child',
				'id' => 'wt_iew_enable_chatgpt',
				'val' => 1,
				'chk' => 'true',
			),
		);
		$select_tone = array(
			'formal' => 'Formal',
			'casual' => 'Casual',
			'flowery' => 'Flowery',
			'convincing' => 'Convincing',
		);
		$fields['chatgpt_tone'] = array(
			'label' => __( 'ChatGPT Tone' ),
			'type' => 'select',
			'sele_vals' => $select_tone,
			'value' => 'formal',
			'field_name' => 'chatgpt_tone',
			'field_group' => 'advanced_field',
			'help_text' => __( 'Select the tone for ChatGPT. Choose the tone that best suits your product descriptions.' ),
			'validation_rule' => array( 'type' => 'text' ),
			'form_toggler' => array(
				'type' => 'child',
				'id' => 'wt_iew_enable_chatgpt',
				'val' => 1,
				'chk' => 'true',
			),
		);
		return $fields;
	}

	/**
	 * Adding admin menus.
	 *
	 * @since 1.0.0
	 * @param array $menus Admin menus.
	 * @return array
	 */
	public function add_admin_pages( $menus ) {
		$menu_temp = array(
			$this->module_base => array(
				'menu',
				__( 'WebToffee Import Export', 'import-export-suite-for-woocommerce' ),
				__( 'WebToffee Import Export (Pro)', 'import-export-suite-for-woocommerce' ),
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
				'dashicons-controls-repeat',
				56,
			),
			$this->module_base . '-sub' => array(
				'submenu',
				$this->module_id,
				__( 'Import Export Suite', 'import-export-suite-for-woocommerce' ),
				__( 'Import Export Suite', 'import-export-suite-for-woocommerce' ),
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
		unset( $menus['general-settings'] );
		$menus = array_merge( $menu_temp, $menus );
		return $menus;
	}

	/**
	 *   Export page.
	 *
	 * @since 1.0.0
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

		$this->enqueue_assets();
				$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : null;

		switch ( $tab ) :
			case 'export':
				include plugin_dir_path( __FILE__ ) . 'views/main.php';// Put your HTML here.
				break;
			case 'import':
				$import = new Wt_Import_Export_For_Woo_Import();
					$import->admin_settings_page();
				break;
			case 'history':
				$import = new Wt_Import_Export_For_Woo_History();
					$import->admin_settings_page();
				break;
			case 'cron':
				$import = new Wt_Import_Export_For_Woo_Cron();
					$import->admin_settings_page();
				break;
			case 'logs':
				$import = new Wt_Import_Export_For_Woo_History();
					$import->admin_log_page();
				break;
			case 'settings':
				  $wtimportexport = new Wt_Import_Export_For_Woo();
					$import = new Wt_Import_Export_For_Woo_Admin( $wtimportexport->get_plugin_name(), $wtimportexport->get_version() );
					$import->admin_settings_page();
				break;
			default:
				include plugin_dir_path( __FILE__ ) . 'views/main.php';
				break;
	endswitch;
	}

	/**
	 *   Main ajax hook to handle all export related requests.
	 *
	 * @since 1.0.0
	 */
	public function ajax_main() {
		include_once plugin_dir_path( __FILE__ ) . 'classes/class-wt-import-export-for-woo-export-ajax.php';
		if ( Wt_Iew_Sh::check_write_access( WT_IEW_PLUGIN_ID ) ) {
			/**
			*   Check it is a rerun call
			*/
			// Nonce already verified on main function - sanitization thorugh helper functions.
			$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
			if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
					return false;
			}
			if ( ! $this->_process_rerun( ( isset( $_POST['rerun_id'] ) ? absint( $_POST['rerun_id'] ) : 0 ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$this->export_method = ( isset( $_POST['export_method'] ) ? sanitize_text_field( wp_unslash( $_POST['export_method'] ) ) : '' );// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing
				$this->to_export = ( isset( $_POST['to_export'] ) ? sanitize_text_field( wp_unslash( $_POST['to_export'] ) ) : '' );// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing
				$this->selected_template = ( isset( $_POST['selected_template'] ) ? intval( $_POST['selected_template'] ) : 0 );// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing
			}

			$this->get_steps();

			$ajax_obj = new Wt_Import_Export_For_Woo_Export_Ajax( $this, $this->to_export, $this->steps, $this->export_method, $this->selected_template, $this->rerun_id );

			$export_action = isset( $_POST['export_action'] ) ? sanitize_text_field( wp_unslash( $_POST['export_action'] ) ) : '';// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing
			$data_type = isset( $_POST['data_type'] ) ? sanitize_text_field( wp_unslash( $_POST['data_type'] ) ) : '';// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing

			$allowed_ajax_actions = array( 'get_steps', 'get_meta_mapping_fields', 'save_template', 'save_template_as', 'update_template', 'upload', 'export', 'export_image' );

			$out = array(
				'status' => 0,
				'msg' => __( 'Error' ),
			);

			if ( method_exists( $ajax_obj, $export_action ) && in_array( $export_action, $allowed_ajax_actions ) ) {
				$out = $ajax_obj->{$export_action}( $out );
			}

			if ( 'json' == $data_type ) {
				echo json_encode( $out );
			}
		}
		exit();
	}
	/**
	 * Export filter screen fields.
	 *
	 * @since 1.0.0
	 * @param array $filter_form_data Form data filters.
	 * @return array
	 */
	public function get_filter_screen_fields( $filter_form_data ) {
		$filter_screen_fields = array(
			'limit' => array(
				'label' => __( 'Limit', 'import-export-suite-for-woocommerce' ),
				'value' => '',
				'type' => 'number',
				'attr' => array(
					'step' => 1,
					'min' => 0,
				),
				'field_name' => 'limit',
				'placeholder' => 'Unlimited',
				'help_text' => __( 'The actual number of records you want to export. e.g. A limit of 500 with an offset 10 will export records from 11th to 510th position.', 'import-export-suite-for-woocommerce' ),
			),
			'offset' => array(
				'label' => __( 'Offset', 'import-export-suite-for-woocommerce' ),
				'value' => '',
				'field_name' => 'offset',
				'placeholder' => __( '0' ),
				'help_text' => __( 'Specify the number of records that should be skipped from the beginning of the database. e.g. An offset of 10 skips the first 10 records.', 'import-export-suite-for-woocommerce' ),
				'type' => 'number',
				'attr' => array(
					'step' => 1,
					'min' => 0,
				),
				'validation_rule' => array( 'type' => 'absint' ),
			),
		);
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $filter_screen_fields    Screen fields.
		 * @param string          $this->to_export    Current action.
		 * @param array           $filter_form_data    Form data.
		 */
		$filter_screen_fields = apply_filters( 'wt_iew_exporter_alter_filter_fields', $filter_screen_fields, $this->to_export, $filter_form_data );
		return $filter_screen_fields;
	}
	/**
	 * Export advanced screen fields.
	 *
	 * @since 1.0.0
	 * @param array $advanced_form_data Advanced data filters.
	 * @return array
	 */
	public function get_advanced_screen_fields( $advanced_form_data ) {
		$file_into_arr = array( 'local' => __( 'Local' ) );

		/* taking available remote adapters */
		$remote_adapter_names = array();
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $remote_adapter_names    Remote adapter.
		 */
		$remote_adapter_names = apply_filters( 'wt_iew_exporter_remote_adapter_names', $remote_adapter_names );
		if ( $remote_adapter_names && is_array( $remote_adapter_names ) ) {
			foreach ( $remote_adapter_names as $remote_adapter_key => $remote_adapter_vl ) {
				$file_into_arr[ $remote_adapter_key ] = $remote_adapter_vl;
			}
		}

		// prepare file into field type based on remote type adapters.
		$file_int_field_arr = array(
			'label' => __( 'Download the file into my', 'import-export-suite-for-woocommerce' ),
			'type' => 'select',
			'sele_vals' => $file_into_arr,
			'field_name' => 'file_into',
			'default_value' => 'local',
			'form_toggler' => array(
				'type' => 'parent',
				'target' => 'wt_iew_file_into',
			),
		);
		if ( count( $file_into_arr ) == 1 ) {
			$file_int_field_arr['label'] = __( 'Enable FTP export?', 'import-export-suite-for-woocommerce' );
			$file_int_field_arr['type'] = 'radio';
			$file_int_field_arr['radio_fields'] = array(
				'local' => __( 'No' ),
			);
		} elseif ( count( $file_into_arr ) == 2 ) {
			$end_vl = end( $file_into_arr );
			$end_ky = key( $file_into_arr );
			/* translators:%s: export option like remote, local */
			$file_int_field_arr['label'] = sprintf( __( 'Enable %s export?' ), ucfirst( $end_vl ) );
			$file_int_field_arr['type'] = 'radio';
			$file_int_field_arr['radio_fields'] = array(
				'local' => __( 'No', 'import-export-suite-for-woocommerce' ),
				$end_ky => __( 'Yes', 'import-export-suite-for-woocommerce' ),
			);
		}
		$delimiter_default = isset( $advanced_form_data['wt_iew_delimiter'] ) ? $advanced_form_data['wt_iew_delimiter'] : ',';
		/**
		 * Export file types.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.6
		 *
		 * @param array           $this->allowed_export_file_type  Allowed file types.
		 */
		$this->allowed_export_file_type = apply_filters( 'wt_ier_allowed_export_file_types', $this->allowed_export_file_type );
		// add `is_advanced` field to group it as advanced tab section.
		$advanced_screen_fields = array(
			'file_name' => array(
				'label' => __( 'Export file name' ),
				'type' => 'text',
				'field_name' => 'file_name',
				'help_text' => __( 'Specify a filename for the exported file. If left blank the system generates a default name.', 'import-export-suite-for-woocommerce' ),
				'after_form_field_html' => '<div class="wt_iew_file_ext_info"></div>',
				'td_class3' => 'wt_iew_file_ext_info_td',
				'validation_rule' => array( 'type' => 'file_name' ),
			),
			'file_as' => array(
				'label' => __( 'Export file format', 'import-export-suite-for-woocommerce' ),
				'type' => 'select',
				'sele_vals' => $this->allowed_export_file_type,
				'field_name' => 'file_as',
				'form_toggler' => array(
					'type' => 'parent',
					'target' => 'wt_iew_file_as',
				),
			),
			'delimiter' => array(
				'label' => __( 'Delimiter', 'import-export-suite-for-woocommerce' ),
				'type' => 'select',
				'value' => ',',
				'css_class' => 'wt_iew_delimiter_preset',
				'tr_id' => 'delimiter_tr',
				'field_name' => 'delimiter_preset',
				'sele_vals' => Wt_Iew_IE_Helper::_get_csv_delimiters(),
				'form_toggler' => array(
					'type' => 'child',
					'id' => 'wt_iew_file_as',
					'val' => 'csv',
				),
				'help_text' => __( 'Separator for differentiating the columns in the CSV file. Assumes ‘,’ by default.', 'import-export-suite-for-woocommerce' ),
				'validation_rule' => array( 'type' => 'skip' ),
				'after_form_field' => '<input type="text" class="wt_iew_custom_delimiter" name="wt_iew_delimiter" value="' . $delimiter_default . '" />',
			),
			'file_into' => $file_int_field_arr,
			'advanced_field_head' => array(
				'type' => 'field_group_head', // field type.
				'head' => __( 'Advanced options', 'import-export-suite-for-woocommerce' ),
				'group_id' => 'advanced_field', // field group id.
				'show_on_default' => 0,
			),
			'export_shortcode_tohtml' => array(
				'label' => __( 'Convert shortcodes to HTML', 'import-export-suite-for-woocommerce' ),
				'value' => 'No',
				'radio_fields' => array(
					'Yes' => __( 'Yes' ),
					'No' => __( 'No' ),
				),
				'type' => 'radio',
				'field_name' => 'export_shortcode_tohtml',
				'field_group' => 'advanced_field',
				'help_text' => __( "Option 'Yes' converts the shortcode to HTML within the exported CSV.", 'import-export-suite-for-woocommerce' ),
			),
			'batch_count' => array(
				'label' => __( 'Export in batches of', 'import-export-suite-for-woocommerce' ),
				'type' => 'text',
				'value' => $this->default_batch_count,
				'field_name' => 'batch_count',
				'is_advanced' => 1,
				'help_text' => __( 'The number of records that the server will process for every iteration within the configured timeout interval.', 'import-export-suite-for-woocommerce' ),
				'field_group' => 'advanced_field',
				'validation_rule' => array( 'type' => 'absint' ),
			),
		);
		/**
		 * Taking advanced fields from post type modules.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $advanced_screen_fields    Screen fields.
		 * @param string          $this->to_export    Current action.
		 * @param array           $advanced_form_data    Form data.
		 */
		$advanced_screen_fields = apply_filters( 'wt_iew_exporter_alter_advanced_fields', $advanced_screen_fields, $this->to_export, $advanced_form_data );
		return $advanced_screen_fields;
	}

	/**
	 * Get steps.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_steps() {
		if ( 'quick' == $this->export_method ) {
			$out = array(
				'post_type' => $this->steps['post_type'],
				'method_export' => $this->steps['method_export'],
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
		 * @param string          $this->to_export    Current action.
		 */
		$this->steps = apply_filters( 'wt_iew_exporter_steps', $this->steps, $this->to_export );
		return $this->steps;
	}

	/**
	 * Edit cron.
	 *
	 * @since 1.0.0
	 * @param integer $rerun_id cron id.
	 * @return bool
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
						$this->to_export = ( isset( $form_data['post_type_form_data'] ) && isset( $form_data['post_type_form_data']['item_type'] ) ? $form_data['post_type_form_data']['item_type'] : '' );
						if ( '' != $this->to_export ) {
							$this->export_method = ( '' != ( isset( $form_data['method_export_form_data'] ) && isset( $form_data['method_export_form_data']['method_export'] ) && $form_data['method_export_form_data']['method_export'] ) ? $form_data['method_export_form_data']['method_export'] : $this->default_export_method );
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
	 *   Validating and Processing rerun action.
	 *
	 * @since 1.0.0
	 * @param integer $rerun_id cron id.
	 * @return bool
	 */
	protected function _process_rerun( $rerun_id ) {
		if ( $rerun_id > 0 ) {
			/* check the history module is available */
			$history_module_obj = Wt_Import_Export_For_Woo::load_modules( 'history' );
			if ( ! is_null( $history_module_obj ) ) {
				/* check the history entry is for export and also has form_data */
				$history_data = $history_module_obj->get_history_entry_by_id( $rerun_id );
				if ( $history_data && $history_data['template_type'] == $this->module_base ) {
					$form_data = maybe_unserialize( $history_data['data'] );
					if ( $form_data && is_array( $form_data ) ) {
						$this->to_export = ( isset( $form_data['post_type_form_data'] ) && isset( $form_data['post_type_form_data']['item_type'] ) ? $form_data['post_type_form_data']['item_type'] : '' );
						if ( '' != $this->to_export ) {
							$this->export_method = ( '' != ( isset( $form_data['method_export_form_data'] ) && isset( $form_data['method_export_form_data']['method_export'] ) && $form_data['method_export_form_data']['method_export'] ) ? $form_data['method_export_form_data']['method_export'] : $this->default_export_method );
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
	 *   Assets loading.
	 *
	 * @since 1.0.0
	 */
	protected function enqueue_assets() {
		if ( Wt_Import_Export_For_Woo_Common_Helper::wt_is_screen_allowed() ) {
			$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : null;
			if ( 'settings' !== $tab ) {
				wp_enqueue_script( $this->module_id, plugin_dir_url( __FILE__ ) . 'assets/js/main.js', array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-datepicker' ), WT_IEW_VERSION );
			}
			wp_enqueue_style( 'jquery-ui-datepicker' );
			wp_enqueue_style( WT_IEW_PLUGIN_ID . '-jquery-ui', WT_IEW_PLUGIN_URL . 'admin/css/jquery-ui.css', array(), WT_IEW_VERSION, 'all' );
			$params = array(
				'item_type' => '',
				'steps' => $this->steps,
				'rerun_id' => $this->rerun_id,
				'to_export' => $this->to_export,
				'export_method' => $this->export_method,
				'msgs' => array(
					'select_post_type' => __( 'Please select a post type', 'import-export-suite-for-woocommerce' ),
					'choosed_template' => __( 'Choosed template: ', 'import-export-suite-for-woocommerce' ),
					'choose_export_method' => __( 'Please select an export method.', 'import-export-suite-for-woocommerce' ),
					'choose_template' => __( 'Please select an export template.', 'import-export-suite-for-woocommerce' ),
					'step' => __( 'Step', 'import-export-suite-for-woocommerce' ),
					'choose_ftp_profile' => __( 'Please select an FTP profile.', 'import-export-suite-for-woocommerce' ),
				),
			);
			wp_localize_script( $this->module_id, 'wt_iew_export_params', $params );

			$this->add_select2_lib(); // adding select2 JS, It checks the availibility of woocommerce.
		}
	}

	/**
	 *
	 * Enqueue select2 library, if woocommerce available use that.
	 *
	 * @since 1.0.0
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
	 * Upload data to the user choosed remote method (Eg: FTP).
	 *
	 * @param   string  $step the action to perform, here 'upload'.
	 * @param   integer $export_id export id.
	 * @param   string  $to_export to export type.
	 *
	 * @return array
	 */
	public function process_upload( $step, $export_id, $to_export ) {
		$out = array(
			'response' => false,
			'export_id' => 0,
			'history_id' => 0, // same as that of export id.
			'finished' => 0,
			'file_url' => '',
			'msg' => '',
		);

		if ( 0 == $export_id ) {
			return $out;
		}

		// take history data by export_id.
		$export_data = Wt_Import_Export_For_Woo_History::get_history_entry_by_id( $export_id );
		if ( is_null( $export_data ) ) {
			return $out;
		}

		$form_data = maybe_unserialize( $export_data['data'] );

		// taking file name.
		$file_name = ( isset( $export_data['file_name'] ) ? $export_data['file_name'] : '' );

		$file_path = $this->get_file_path( $file_name );
		if ( false === $file_path ) {
			$update_data = array(
				'status' => Wt_Import_Export_For_Woo_History::$status_arr['failed'],
				'status_text' => 'File not found.', // no need to add translation function.
			);
			$update_data_type = array(
				'%d',
				'%s',
			);
			Wt_Import_Export_For_Woo_History::update_history_entry( $export_id, $update_data, $update_data_type );

			return $out;
		}

		/* updating output parameters */
		$out['export_id'] = $export_id;
		$out['history_id'] = $export_id;
		$out['file_url'] = '';

		// check where to copy the files.
		$file_into = 'local';
		if ( isset( $form_data['advanced_form_data'] ) ) {
			$file_into = ( isset( $form_data['advanced_form_data']['wt_iew_file_into'] ) ? $form_data['advanced_form_data']['wt_iew_file_into'] : 'local' );
		}

		if ( 'local' != $file_into ) {
			$remote_adapter = Wt_Import_Export_For_Woo::get_remote_adapters( 'export', $file_into );
			if ( is_null( $remote_adapter ) ) {
				$msg = sprintf( 'Unable to initailize %s', $file_into );
				Wt_Import_Export_For_Woo_History::record_failure( $export_id, $msg );
				/* translators:%s: export option like remote, local */
				$out['msg'] = sprintf( __( 'Unable to initailize %s' ), $file_into );
				return $out;
			}

			/* upload the file */
			$upload_out_format = array(
				'response' => true,
				'msg' => '',
			);

			$advanced_form_data = ( isset( $form_data['advanced_form_data'] ) ? $form_data['advanced_form_data'] : array() );

			$upload_data = $remote_adapter->upload( $file_path, $file_name, $advanced_form_data, $upload_out_format );
			$out['response'] = ( isset( $upload_data['response'] ) ? $upload_data['response'] : false );
			$out['msg'] = ( isset( $upload_data['msg'] ) ? $upload_data['msg'] : __( 'Error' ) );
		} else {
			$out['response'] = true;
			$out['file_url'] = html_entity_decode( $this->get_file_url( $file_name ) );
		}

		$out['finished'] = 1;  // if any error then also its finished, but with errors.
		if ( true === $out['response'] ) {

			$msg         = __( '<div>File exported successfully to the selected FTP server.</div>' );
			$msg         .= '<span class="wt_iew_info_box_finished_text" style="font-size: 10px;">';
			$msg         .= '<a class="button button-secondary" style="margin-top:5px;" onclick="wt_iew_export.hide_export_info_box();">' . __( 'Close' ) . '</a>';
			$msg         .= '</span>';
			$out['msg']    = $msg;

			/* updating finished status */
			$update_data = array(
				'status' => 1,  // success.
			);
			$update_data_type = array(
				'%d',
			);
			Wt_Import_Export_For_Woo_History::update_history_entry( $export_id, $update_data, $update_data_type );

		} else // failed.
		{
			// no need to add translation function in message.
			Wt_Import_Export_For_Woo_History::record_failure( $export_id, 'Failed while uploading' );
		}
		return $out;
	}

	/**
	 *   Do the image export process
	 *
	 * @param   array   $form_data Form data.
	 * @param   string  $step export step.
	 * @param   string  $to_process to export type.
	 * @param   string  $file_name Filename.
	 * @param   integer $export_id id of export.
	 * @param   integer $offset offset.
	 *
	 * @return array
	 */
	public function process_image_export( $form_data, $step, $to_process, $file_name = '', $export_id = 0, $offset = 0 ) {
		$out = array(
			'response' => false,
			'new_offset' => 0,
			'export_id' => 0,
			'history_id' => 0, // same as that of export id.
			'total_records' => 0,
			'finished' => 0,
			'file_url' => '',
			'msg' => '',
		);

		/* prepare form_data, If this was not first batch */
		if ( $export_id > 0 ) {
			// take history data by export_id.
			$export_data = Wt_Import_Export_For_Woo_History::get_history_entry_by_id( $export_id );
			if ( is_null( $export_data ) ) {
				return $out;
			}

			// processing form data.
			$form_data = ( isset( $export_data['data'] ) ? maybe_unserialize( $export_data['data'] ) : array() );
		}
		$this->to_export = $to_process;
		$default_batch_count = $this->_get_default_batch_count( $form_data );
		$batch_count = $default_batch_count;
		$total_records = 0;

		if ( isset( $form_data['advanced_form_data'] ) ) {
			$batch_count = ( isset( $form_data['advanced_form_data']['wt_iew_batch_count'] ) && (int) $form_data['advanced_form_data']['wt_iew_batch_count'] > 0 ? $form_data['advanced_form_data']['wt_iew_batch_count'] : $batch_count );
		}

		if ( 0 == $export_id ) {
			$file_name = $this->to_export . '_export_image_' . gmdate( 'Y-m-d-h-i-s' ) . '.zip'; // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			$export_id = Wt_Import_Export_For_Woo_History::create_history_entry( $file_name, $form_data, $this->to_export, $step );
			$offset = 0;
		} else {
			// taking file name from export data.
			$file_name = ( isset( $export_data['file_name'] ) ? $export_data['file_name'] : '' );
			$total_records = ( isset( $export_data['total'] ) ? $export_data['total'] : 0 );
		}

		/* setting history_id in Log section */
		Wt_Import_Export_For_Woo_Log::$history_id = $export_id;

		$file_path = $this->get_file_path( $file_name );
		if ( false === $file_path ) {
			$msg = 'Unable to create backup directory. Please grant write permission for `wp-content` folder.';

			// no need to add translation function in message.
			Wt_Import_Export_For_Woo_History::record_failure( $export_id, $msg );

			$out['msg'] = __( 'Unable to create backup directory. Please grant write permission for `wp-content` folder.' );
			return $out;
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
		$form_data = apply_filters( 'wt_iew_export_full_form_data', $form_data, $to_process, $step, $this->selected_template_data );

		/* hook to get data from corresponding module. Eg: product, order */
		$export_data = array(
			'total' => 100,
			'images' => array(),
		);
		/**
		 * Taking export data.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $export_data    Export data.
		 * @param string          $to_process    Current action.
		 * @param string          $step    Step.
		 * @param array           $form_data    Form posted data.
		 * @param array           $this->selected_template_data    Selected template data.
		 * @param string          $this->export_method    Current action.
		 * @param string          $offset    Offset number.
		 */
		$export_data = apply_filters( 'wt_iew_exporter_do_image_export', $export_data, $to_process, $step, $form_data, $this->selected_template_data, $this->export_method, $offset );

		if ( 0 == $offset ) {
			$total_records = intval( isset( $export_data['total'] ) ? $export_data['total'] : 0 );
		}
		$this->_update_history_after_export( $export_id, $offset, $total_records, $export_data );

		/* checking action is finshed */
		$is_last_offset = false;
		$new_offset = $offset + $batch_count; // increase the offset.
		if ( $new_offset >= $total_records ) {
			$is_last_offset = true;
		}

		/* no data from corresponding module */
		if ( $export_data ) {
			$image_arr = ( isset( $export_data['images'] ) ? $export_data['images'] : array() );
			include_once WT_IEW_PLUGIN_PATH . 'admin/classes/class-wt-import-export-for-woo-zipwriter.php';
			Wt_Import_Export_For_Woo_Zipwriter::write_to_file( $file_path, $image_arr, $offset );

		}

		/* updating output parameters */
		$out['total_records'] = $total_records;
		$out['export_id'] = $export_id;
		$out['history_id'] = $export_id;
		$out['file_url'] = '';
		$out['response'] = true;

		/* updating action is finshed */
		if ( $is_last_offset ) {
			$out['file_url'] = html_entity_decode( $this->get_file_url( $file_name ) );
			$out['finished'] = 1; // finished.

			if ( $total_records > 0 ) {
				$msg = __( '<div>Images exported successfully as separate zip file.</div>' );
				$msg .= '<span class="wt_iew_info_box_finished_text" style="font-size: 10px;">';
				$msg .= '<a class="button button-secondary" style="margin-top:5px;" onclick="wt_iew_export.hide_export_info_box();">' . __( 'Close' ) . '</a>';
				$msg .= '<a class="button button-secondary" style="margin-top:5px;" onclick="wt_iew_export.hide_export_info_box();" target="_blank" href="' . $out['file_url'] . '" >' . __( 'Download file' ) . '</a></span>';
			} else {
				$msg = __( '<div>There is no images to export.</div>' );
				$msg .= '<span class="wt_iew_info_box_finished_text" style="font-size: 10px;">';
				$msg .= '<a class="button button-secondary" style="margin-top:5px;" onclick="wt_iew_export.hide_export_info_box();">' . __( 'Close' ) . '</a>';
				$msg .= '</span>';
			}

			$out['msg'] = $msg;

			/* updating finished status */
			$update_data = array(
				'status' => Wt_Import_Export_For_Woo_History::$status_arr['finished'],
				'status_text' => 'Finished', // translation function not needed.
			);
			$update_data_type = array(
				'%d',
				'%s',
			);
			Wt_Import_Export_For_Woo_History::update_history_entry( $export_id, $update_data, $update_data_type );

		} else {
			$out['new_offset'] = $new_offset;
			/* translators: 1: new offset. 2: total records */
			$out['msg'] = sprintf( __( 'Exporting...(%1$d out of %2$d)' ), $new_offset, $total_records );
		}
		return $out;
	}

	/**
	 *   Do the export process
	 *
	 * @param   array   $form_data Form data.
	 * @param   string  $step export step.
	 * @param   string  $to_process to export type.
	 * @param   string  $file_name Filename.
	 * @param   integer $export_id id of export.
	 * @param   integer $offset offset.
	 *
	 * @return array
	 */
	public function process_action( $form_data, $step, $to_process, $file_name = '', $export_id = 0, $offset = 0 ) {
		$out = array(
			'response' => false,
			'new_offset' => 0,
			'export_id' => 0,
			'history_id' => 0, // same as that of export id.
			'total_records' => 0,
			'finished' => 0,
			'file_url' => '',
			'msg' => '',
		);

		/* prepare form_data, If this was not first batch */
		if ( $export_id > 0 ) {
			// take history data by export_id.
			$export_data = Wt_Import_Export_For_Woo_History::get_history_entry_by_id( $export_id );
			if ( is_null( $export_data ) ) {
				return $out;
			}

			// processing form data.
			$form_data = ( isset( $export_data['data'] ) ? maybe_unserialize( $export_data['data'] ) : array() );
		}
		$this->to_export = $to_process;
		$default_batch_count = $this->_get_default_batch_count( $form_data );
		$batch_count = $default_batch_count;
		$file_as = 'csv';
		$csv_delimiter = ',';
		$total_records = 0;
		if ( isset( $form_data['advanced_form_data'] ) ) {
			$batch_count = ( isset( $form_data['advanced_form_data']['wt_iew_batch_count'] ) && (int) $form_data['advanced_form_data']['wt_iew_batch_count'] > 0 ? $form_data['advanced_form_data']['wt_iew_batch_count'] : $batch_count );
			$file_as = ( isset( $form_data['advanced_form_data']['wt_iew_file_as'] ) ? $form_data['advanced_form_data']['wt_iew_file_as'] : 'csv' );
			$csv_delimiter = ( isset( $form_data['advanced_form_data']['wt_iew_delimiter'] ) ? $form_data['advanced_form_data']['wt_iew_delimiter'] : ',' );
			$csv_delimiter = ( '' == $csv_delimiter ) ? ',' : $csv_delimiter;

		}

				// for Quick method exicuted from step 2(method of export).
		if ( empty( $form_data['advanced_form_data']['wt_iew_batch_count'] ) ) {
			$form_data['advanced_form_data']['wt_iew_batch_count'] = $batch_count;
		}
		/**
		 * Export file types.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.6
		 *
		 * @param array           $this->allowed_export_file_type  Allowed file types.
		 */
		$this->allowed_export_file_type = apply_filters( 'wt_ier_allowed_export_file_types', $this->allowed_export_file_type );
		$file_as = ( isset( $this->allowed_export_file_type[ $file_as ] ) ? $file_as : 'csv' );

		$generated_file_name = $this->to_export . '_export_' . gmdate( 'Y-m-d-h-i-s' ) . '.' . $file_as;// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

				$args = array(
					'file_name' => $file_name,
					'to_export' => $this->to_export,
					'step' => $step,
				);
				/**
				 * Export filename.
				 *
				 * Enables adding extra arguments or setting defaults for the request.
				 *
				 * @since 1.0.0
				 *
				 * @param string          $generated_file_name    File name.
				 * @param array           $args    Form posted data.
				 */
				$generated_file_name = apply_filters( 'wt_iew_export_filename', $generated_file_name, $args );

				if ( 0 == $export_id ) {
					$file_name = ( '' == $file_name ? $generated_file_name : sanitize_file_name( $file_name . '.' . $file_as ) );
					$export_id = Wt_Import_Export_For_Woo_History::create_history_entry( $file_name, $form_data, $this->to_export, $step );
					$offset = 0;
				} else {
					// taking file name from export data.
					$file_name = ( isset( $export_data['file_name'] ) ? $export_data['file_name'] : $generated_file_name );
					$total_records = ( isset( $export_data['total'] ) ? $export_data['total'] : 0 );
				}
				/* setting history_id in Log section */
				Wt_Import_Export_For_Woo_Log::$history_id = $export_id;

				$file_path = $this->get_file_path( $file_name );
				if ( false === $file_path ) {
					$msg = 'Unable to create backup directory. Please grant write permission for `wp-content` folder.';

					// no need to add translation function in message.
					Wt_Import_Export_For_Woo_History::record_failure( $export_id, $msg );

					$out['msg'] = __( 'Unable to create backup directory. Please grant write permission for `wp-content` folder.' );
					return $out;
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
				$form_data = apply_filters( 'wt_iew_export_full_form_data', $form_data, $to_process, $step, $this->selected_template_data );

				/* hook to get data from corresponding module. Eg: product, order */
				$export_data = array(
					'total' => 100,
					'head_data' => array(
						'abc' => 'hd1',
						'bcd' => 'hd2',
						'cde' => 'hd3',
						'def' => 'hd4',
					),
					'body_data' => array(
						array(
							'abc' => 'Abc1',
							'bcd' => 'Bcd1',
							'cde' => 'Cde1',
							'def' => 'Def1',
						),
						array(
							'abc' => 'Abc2',
							'bcd' => 'Bcd2',
							'cde' => 'Cde2',
							'def' => 'Def2',
						),
					),
				);

				/* in scheduled export. The export method will not available so we need to take it from form_data */
				$form_data_export_method = ( isset( $form_data['method_export_form_data'] ) && isset( $form_data['method_export_form_data']['method_export'] ) ? $form_data['method_export_form_data']['method_export'] : $this->default_export_method );
				$this->export_method = ( '' == $this->export_method ? $form_data_export_method : $this->export_method );
				/**
				 * Taking export data.
				 *
				 * Enables adding extra arguments or setting defaults for the request.
				 *
				 * @since 1.0.0
				 *
				 * @param array           $export_data    Export data.
				 * @param string          $to_process    Current action.
				 * @param string          $step    Step.
				 * @param array           $form_data    Form posted data.
				 * @param array           $this->selected_template_data    Selected template data.
				 * @param string          $this->export_method    Current action.
				 * @param string          $offset    Offset number.
				 */
				$export_data = apply_filters( 'wt_iew_exporter_do_export', $export_data, $to_process, $step, $form_data, $this->selected_template_data, $this->export_method, $offset );
				if ( 0 == $offset ) {
					$total_records = intval( isset( $export_data['total'] ) ? $export_data['total'] : 0 );
				}
				$this->_update_history_after_export( $export_id, $offset, $total_records, $export_data );

				/* checking action is finshed */
				$is_last_offset = false;
				$new_offset = $offset + $batch_count; // increase the offset.
				if ( $new_offset >= $total_records ) {
					$is_last_offset = true;
				}

				/* no data from corresponding module */
				if ( $export_data ) {

					if ( 'xml' == $file_as ) {
						include_once WT_IEW_PLUGIN_PATH . 'admin/classes/class-wt-import-export-for-woo-xmlwriter.php';
						$writer = new Wt_Import_Export_For_Woo_Xmlwriter( $file_path );
					} else {
						include_once WT_IEW_PLUGIN_PATH . 'admin/classes/class-wt-import-export-for-woo-csvwriter.php';
						$writer = new Wt_Import_Export_For_Woo_Csvwriter( $file_path, $offset, $csv_delimiter );
					}
						$args = array(
							'file_path' => $file_path,
							'offset' => $offset,
							'csv_delimiter' => $csv_delimiter,
						);
						/**
						 * Export file writer.
						 *
						 * Enables adding extra arguments or setting defaults for the request.
						 *
						 * @since 1.0.0
						 *
						 * @param string          $writer    File writer.
						 * @param array           $args    Form posted data.
						 */
						$writer = apply_filters( 'wt_iew_custom_file_writer', $writer, $args );

						/**
						*   Alter export data before writing to file.
						*
						*   @since 1.0.0
						*
						*   @param  array   $export_data        data to export
						*   @param  int     $offset             current offset
						*   @param  boolean $is_last_offset     is current offset is last one
						*   @param  string  $file_as            file type to write Eg: XML, CSV
						*   @param  string  $to_export          Post type
						*   @param  string  $csv_delimiter      CSV delimiter. In case of CSV export
						*   @return array   $export_data        Altered export data
						*/
						$export_data = apply_filters( 'wt_iew_alter_export_data', $export_data, $offset, $is_last_offset, $file_as, $this->to_export, $csv_delimiter );

						$writer->write_to_file( $export_data, $offset, $is_last_offset, $this->to_export );
				}

				/* updating output parameters */
				$out['total_records'] = $total_records;
				$out['export_id'] = $export_id;
				$out['history_id'] = $export_id;
				$out['file_url'] = '';
				$out['response'] = true;

				/* updating action is finshed */
				if ( $is_last_offset ) {
					// check where to copy the files.
					$file_into = 'local';
					if ( isset( $form_data['advanced_form_data'] ) ) {
						$file_into = ( isset( $form_data['advanced_form_data']['wt_iew_file_into'] ) ? $form_data['advanced_form_data']['wt_iew_file_into'] : 'local' );
					}
					if ( 'local' != $file_into ) {
						$out['finished'] = 2; // file created, next upload it.
						/* translators:%s: export option like remote, local */
						$out['msg'] = sprintf( __( 'Uploading to %s' ), $file_into );
						if ( 0 == $total_records && isset( $export_data['no_post'] ) ) {
							$out['no_post'] = true;
							$out['msg'] = $export_data['no_post'];
						}
					} else {
						$out['file_url'] = html_entity_decode( $this->get_file_url( $file_name ) );
						$out['finished'] = 1; // finished.
								$msg = __( 'Export file processing completed' );
								$msg .= '<span class="wt_iew_popup_close" style="line-height:10px;width:auto" onclick="wt_iew_export.hide_export_info_box();">X</span>';

								$msg .= '<span class="wt_iew_info_box_finished_text" style="font-size: 10px; display:block">';
						if ( Wt_Import_Export_For_Woo_Admin::module_exists( 'history' ) ) {
								$history_module_id = Wt_Import_Export_For_Woo::get_module_id( 'history' );
								$history_page_url = admin_url( 'admin.php?page=' . $history_module_id );
								$msg .= __( 'You can manage exports from History section.' );
							// $msg.=sprintf(__('You can manage exports from %s History %s section.'), '<a href="'.$history_page_url.'" target="_blank">', '</a>');
						}

								$msg .= '<a class="button button-secondary" style="margin-top:10px;" onclick="wt_iew_export.hide_export_info_box();" target="_blank" href="' . $out['file_url'] . '" >' . __( 'Download file' ) . '</a></span>';
						if ( 0 == $total_records && isset( $export_data['no_post'] ) ) {
							$out['no_post'] = true;
									$msg = $export_data['no_post'];
						}
						$out['msg'] = $msg;

						/* updating finished status */
						$update_data = array(
							'status' => Wt_Import_Export_For_Woo_History::$status_arr['finished'],
							'status_text' => 'Finished', // translation function not needed.
						);
						$update_data_type = array(
							'%d',
							'%s',
						);
						Wt_Import_Export_For_Woo_History::update_history_entry( $export_id, $update_data, $update_data_type );
					}
				} else {
							$out['new_offset'] = $new_offset;
							/* translators: 1: new offset. 2: total records */
							$out['msg'] = sprintf( __( 'Exporting...(%1$d out of %2$d)' ), $new_offset, $total_records );
				}
				return $out;
	}

	/**
	 *   Download file via a nonce URL
	 *
	 * @param string $file_name File name.
	 * @since 1.0.0
	 * @return string File path.
	 */
	public static function get_file_path( $file_name ) {
		if ( ! is_dir( self::$export_dir ) ) {
			if ( ! mkdir( self::$export_dir, 0700 ) ) {
				return false;
			} else {
				$files_to_create = array(
					'.htaccess' => 'deny from all',
					'index.php' => '<?php // Silence is golden',
				);
				foreach ( $files_to_create as $file => $file_content ) {
					if ( ! file_exists( self::$export_dir . '/' . $file ) ) {
						$fh = @fopen( self::$export_dir . '/' . $file, 'w' );
						if ( is_resource( $fh ) ) {
							fwrite( $fh, $file_content );
							fclose( $fh );
						}
					}
				}
			}
		}
		return self::$export_dir . '/' . $file_name;
	}

	/**
	 *   Download file via a nonce URL
	 */
	public function download_file() {
		if ( isset( $_GET['wt_iew_export_download'] ) ) {
			if ( Wt_Iew_Sh::check_write_access( WT_IEW_PLUGIN_ID ) ) {
				$file_name = ( isset( $_GET['file'] ) ? sanitize_file_name( wp_unslash( $_GET['file'] ) ) : '' );
				if ( '' != $file_name ) {
					$file_arr = explode( '.', $file_name );
					$file_ext = end( $file_arr );
					/**
					 * Export file types.
					 *
					 * Enables adding extra arguments or setting defaults for the request.
					 *
					 * @since 1.0.6
					 *
					 * @param array   $this->allowed_export_file_type  Allowed file types.
					 */
					$this->allowed_export_file_type = apply_filters( 'wt_ier_allowed_export_file_types', $this->allowed_export_file_type );
					if ( isset( $this->allowed_export_file_type[ $file_ext ] ) || 'zip' == $file_ext ) {
						$file_path = self::$export_dir . '/' . $file_name;
						if ( file_exists( $file_path ) && is_file( $file_path ) ) {
							header( 'Pragma: public' );
							header( 'Expires: 0' );
							header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
							header( 'Cache-Control: private', false );
							header( 'Content-Transfer-Encoding: binary' );
							header( 'Content-Disposition: attachment; filename="' . $file_name . '";' );
							header( 'Content-Description: File Transfer' );
							header( 'Content-Type: application/octet-stream' );
							header( 'Content-Length: ' . filesize( $file_path ) );
							readfile( $file_path );
							exit();

						}
					}
				}
			}
		}
	}
	/**
	 *   Update history after export
	 *
	 * @param   integer $export_id ID.
	 * @param   integer $offset Export offset.
	 * @param   integer $total_records Total records to export.
	 * @param   array   $export_data Data of export.
	 * @since 1.0.0
	 */
	private function _update_history_after_export( $export_id, $offset, $total_records, $export_data ) {
		/* we need to update total record count on first batch */
		if ( 0 == $offset ) {
			$update_data = array(
				'total' => $total_records,
			);
		} else {
			/* updating completed offset */
			$update_data = array(
				'offset' => $offset,
			);
		}
		$update_data_type = array(
			'%d',
		);
		Wt_Import_Export_For_Woo_History::update_history_entry( $export_id, $update_data, $update_data_type );
	}
	/**
	 * Get default batch count.
	 *
	 * @param   array $form_data Form data.
	 * @since 1.0.0
	 * @return integer
	 */
	private function _get_default_batch_count( $form_data ) {
		/**
		*   Alter export batch.
		*
		*   @since 1.0.0
		*
		*   @param  int   $this->default_batch_count  Default batch count
		*   @param  string  $this->to_export          Post type
		*   @param   array   $form_data Data of export.
		*   @return array   $export_data        Altered export data
		*/
		$default_batch_count = absint( apply_filters( 'wt_iew_exporter_alter_default_batch_count', $this->default_batch_count, $this->to_export, $form_data ) );
		$form_data = null;
		unset( $form_data );
		return ( 0 == $default_batch_count ? $this->default_batch_count : $default_batch_count );
	}

	/**
	 *   Generating downloadable URL for a file
	 *
	 * @param   string $file_name File name.
	 * @since 1.0.0
	 * @return string
	 */
	private function get_file_url( $file_name ) {
		return wp_nonce_url( admin_url( 'admin.php?wt_iew_export_download=true&file=' . $file_name ), WT_IEW_PLUGIN_ID );
	}
}
Wt_Import_Export_For_Woo::$loaded_modules['export'] = new Wt_Import_Export_For_Woo_Export();
