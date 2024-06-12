<?php
/**
 * Handles the FTP/sFTP actions.
 *
 * @package   ImportExportSuite\Admin\Modules\Ftp
 * @version   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_FtpAdapter Class.
 */
class Wt_Import_Export_For_Woo_Ftp {

	/**
	 * To export location like ftp, local
	 *
	 * @var string
	 */
	private $to_export = '';
	/**
	 * To export ID
	 *
	 * @var string
	 */
	private $to_export_id = '';
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $module_id = '';
	/**
	 * Module
	 *
	 * @var string
	 */
	public static $module_id_static = '';
	/**
	 * Module base
	 *
	 * @var string
	 */
	public $module_base = 'ftp';
	/**
	 * Is the call from the popup or the page
	 *
	 * @var string
	 */
	public $popup_page = 0; // is ajax call from popup. May be it from export/import page.
	/**
	 * Step
	 *
	 * @var array
	 */
	public $lables = array(); // labels for translation.
	/**
	 * FTP form fields
	 *
	 * @var array
	 */
	public $ftp_form_fields = array();
	/**
	 * FTP form validation rules
	 *
	 * @var array
	 */
	public $ftp_form_validation_rule = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		 $this->module_id = Wt_Import_Export_For_Woo::get_module_id( $this->module_base );
		self::$module_id_static = $this->module_id;

		add_filter( 'wt_iew_plugin_settings_tabhead', array( __CLASS__, 'settings_tabhead' ) );
		add_action( 'wt_iew_plugin_out_settings_form', array( $this, 'out_settings_form' ) );

		add_filter( 'wt_iew_exporter_alter_advanced_fields', array( $this, 'exporter_alter_advanced_fields' ), 10, 3 );
		add_filter( 'wt_iew_importer_alter_method_import_fields', array( $this, 'importer_alter_method_import_fields' ), 10, 3 );

		/* Ajax hook to save FTP details */
		add_action( 'wp_ajax_iew_ftp_ajax', array( $this, 'ajax_main' ), 11 );
		add_action( 'wp_ajax_iew_sftp_download', array( $this, 'sftp_unpack_package' ), 11 );

		/* Add FTP adapter to remoter adapter list */
		add_filter( 'wt_iew_remote_adapters', array( $this, 'remote_adapter' ), 11, 3 );
		add_filter( 'wt_iew_exporter_remote_adapter_names', array( $this, 'remote_adapter_name' ) );
		add_filter( 'wt_iew_importer_remote_adapter_names', array( $this, 'remote_adapter_name' ) );

		add_filter( 'wt_iew_exporter_file_into_fields_row_id', array( $this, 'exporter_file_into_fields_row_id' ) );
		add_action( 'wt_iew_exporter_file_into_js_fn', array( $this, 'exporter_file_into_js_fn' ) );
		add_action( 'wt_iew_importer_file_from_js_fn', array( $this, 'importer_file_from_js_fn' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ), 10, 1 );
		add_action( 'wt_iew_exporter_before_head', array( $this, 'add_popup_crud_html' ), 10, 1 );
		add_action( 'wt_iew_importer_before_head', array( $this, 'add_popup_crud_html' ), 10, 1 );

		/* validate ftp entries before doing an action */
		add_action( 'wt_iew_exporter_validate', array( $this, 'exporter_validate' ) );

		/* reset the formdata. Needed when user changes the import method */
		add_action( 'wt_iew_importer_reset_form_data', array( $this, 'importer_reset_form_data' ) );

		/* validate ftp entries before doing an action */
		add_action( 'wt_iew_importer_validate', array( $this, 'importer_validate' ), 10, 1 );

		/* set the validated file info to varaiable. This for revalidating if any changes ocuured */
		add_action( 'wt_iew_importer_set_validate_file_info', array( $this, 'importer_set_validate_file_info' ), 10, 1 );

		// labels using in multiple places.
		$this->lables['select_one'] = __( 'Select atleast one.' );
		$this->lables['no_ftp_prfile_found'] = __( 'No FTP profiles found.' );

		/* When altering fields and validation rule, please check `save_ftp` method */
		$this->ftp_form_fields = array( 'wt_iew_profilename', 'wt_iew_hostname', 'wt_iew_ftpuser', 'wt_iew_ftppassword', 'wt_iew_ftpport', 'wt_iew_ftpexport_path', 'wt_iew_ftpimport_path', 'wt_iew_is_sftp', 'wt_iew_useftps', 'wt_iew_passivemode' );
		$this->ftp_form_validation_rule = array(
			'wt_iew_ftpport' => array( 'type' => 'absint' ),
			'wt_iew_is_sftp' => array( 'type' => 'int' ),
			'wt_iew_useftps' => array( 'type' => 'int' ),
			'wt_iew_passivemode' => array( 'type' => 'int' ),
		);
	}
	/**
	 * Enque assets.
	 */
	public function enqueue_assets() {
		if ( isset( $_GET['page'] ) && ( Wt_Import_Export_For_Woo::get_module_id( 'export' ) == $_GET['page'] || Wt_Import_Export_For_Woo::get_module_id( 'import' ) == $_GET['page'] || WT_IEW_PLUGIN_ID == $_GET['page'] ) ) {
			wp_enqueue_script( $this->module_id, plugin_dir_url( __FILE__ ) . 'assets/js/main.js', array( 'jquery' ), WT_IEW_VERSION );
			$params = array(
				'nonces' => array(
					'main' => wp_create_nonce( $this->module_id ),
				),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'msgs' => array(
					'add_new' => __( 'Add new' ),
					'add_new_hd' => __( 'Add new FTP profile' ),
					'edit' => __( 'Edit' ),
					'edit_hd' => __( 'Edit FTP profile' ),
					'mandatory' => __( 'All fields are mandatory' ),
					'sure' => __( 'Confirm? All import/export profiles associated with this FTP profile will not work. You can\'t undo this action.' ),
					'wait' => __( 'Please wait...' ),
					'delete' => __( 'Delete' ),
					'dowbload_sftp' => __( 'Download sFTP Addon' ),
					'aborted' => __( 'Aborted' ),
					'some_mandatory' => __( 'Please fill mandatory fields' ),
					'choose_a_profile' => __( 'Please choose an FTP profile' ),
					'enter_an_export_path' => __( 'Export path is mandatory.' ),
					'enter_an_import_path' => __( 'Import path is mandatory.' ),
					'enter_an_import_file' => __( 'Import file is mandatory.' ),
					'select_one' => $this->lables['select_one'],
					'no_ftp_prfile_found' => $this->lables['no_ftp_prfile_found'],
				),
			);
			wp_localize_script( $this->module_id, 'wt_iew_ftp_params', $params );

			wp_enqueue_style( $this->module_id, plugin_dir_url( __FILE__ ) . 'assets/css/main.css', array(), WT_IEW_VERSION, 'all' );
		}
	}

	/**
	 *   Add HTML for FTP popup
	 */
	public function add_popup_crud_html() {
		?>
		<div class="wt_iew_popup_ftp_crud wt_iew_popup">
			<div class="wt_iew_popup_hd">
				<span class="wt_iew_popup_hd_label"><?php esc_html_e( 'FTP profiles' ); ?></span>
				<div class="wt_iew_popup_close">X</div>
			</div>
			<div class="wt_iew_ftp_settings_page" style="padding:15px; text-align:left;">
				
			</div>
		</div>
		<?php
	}
	/**
	 *   Validate file
	 */
	public function importer_set_validate_file_info() {
		?>
		wt_iew_ftp.importer_set_validate_file_info(file_from);
		<?php
	}

	/**
	 *   Reset formdata of import
	 */
	public function importer_reset_form_data() {
		?>
		wt_iew_ftp.importer_reset_form_data();
		<?php
	}
	/**
	 *   Import options validate
	 */
	public function importer_validate() {
		?>
		if(is_continue)
		{
			is_continue=wt_iew_ftp.validate_import_ftp_fields(is_continue, action, action_type, is_previous_step);
		}
		<?php
	}

	/**
	 *   Export options validate
	 */
	public function exporter_validate() {
		?>
		if(is_continue)
		{
			is_continue=wt_iew_ftp.validate_export_ftp_fields(is_continue, action, action_type, is_previous_step);
		}
		<?php
	}
	/**
	 *   Import options when selecting FTP
	 */
	public function importer_file_from_js_fn() {
		?>
		if(file_from=='ftp')
		{
			wt_iew_ftp.toggle_ftp_path();
			wt_iew_ftp.popUpCrud('import');
		}
		<?php
	}

	/**
	 *   JS code to toggle FTP form fields
	 */
	public function exporter_file_into_js_fn() {
		?>
		if(file_into=='ftp')
		{
			wt_iew_ftp.toggle_ftp_path();
			wt_iew_toggle_schedule_btn(1); /* show cron btn, if exists */
			wt_iew_ftp.popUpCrud('export');
		}
		<?php
	}
	/**
	 *   Available remote adapters like FTP, sFTP
	 *
	 * @param array $adapters Remote adapters.
	 * @return array Adapters
	 */
	public function remote_adapter_name( $adapters ) {
		$adapters['ftp'] = __( 'FTP' );
		return $adapters;
	}

	/**
	 *   Add FTP adapter to remoter adapter list
	 *
	 * @param array  $adapters Access adapters.
	 * @param string $action Action.
	 * @param string $adapter Current adapter.
	 */
	public function remote_adapter( $adapters, $action, $adapter ) {
		if ( '' != $adapter ) {
			if ( 'ftp' == $adapter ) {
				$adapters['ftp'] = include plugin_dir_path( __FILE__ ) . 'classes/class-wt-import-export-for-woo-ftpadapter.php';
			}
		} else {
			$adapters['ftp'] = include plugin_dir_path( __FILE__ ) . 'classes/class-wt-import-export-for-woo-ftpadapter.php';
		}
		return $adapters;
	}

	/**
	 *   Tab head for module settings page
	 *
	 * @param array $arr Tab headers.
	 */
	public static function settings_tabhead( $arr ) {
		$out = array();
		foreach ( $arr as $key => $value ) {
			$out[ $key ] = $value;
			if ( 'wt-advanced' == $key ) {
				$out['wt-ftp'] = __( 'FTP settings' );
			}
		}
		if ( ! isset( $out['wt-ftp'] ) ) {
			$out['wt-ftp'] = __( 'FTP settings' );
		}
		return $out;
	}

	/**
	 * Main ajax hook for ajax actions.
	 */
	public function ajax_main() {
		$allowed_actions = array( 'save_ftp', 'delete_ftp', 'ftp_list', 'settings_page', 'test_ftp' );
		$action = ( isset( $_POST['iew_ftp_action'] ) ? sanitize_text_field( wp_unslash( $_POST['iew_ftp_action'] ) ) : '' );// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$out = array(
			'status' => true,
			'msg' => '',
		);
		if ( ! Wt_Iew_Sh::check_write_access( WT_IEW_PLUGIN_ID ) ) {
			$out['status'] = false;
			// Nonce already verified above.
			$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
			if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				$out['msg'] = __( 'Security check failed' );
				die( json_encode( $out ) );
			}
		} elseif ( in_array( $action, $allowed_actions ) ) {
			if ( method_exists( $this, $action ) ) {
				$out = $this->{$action}( $out ); // some methods will not retrive array.
			}
		}
		echo json_encode( $out );
		exit();
	}
	/**
	 *   Export profile column IDs
	 *
	 * @param array $arr Tab headers.
	 */
	public function exporter_file_into_fields_row_id( $arr ) {
		$arr = ( is_array( $arr ) ? $arr : array() );
		return array_merge( $arr, array( '#export_type_tr', '#cron_start_time_tr', '#cron_interval_tr', '#ftp_profile_tr', '#export_path_tr' ) );
	}

	/**
	 * Add FTP related fields to the importer method_import step
	 *
	 * @param array  $fields Access adapters.
	 * @param string $to_import Action.
	 * @param string $form_data Current adapter.
	 */
	public function importer_alter_method_import_fields( $fields, $to_import, $form_data ) {
		$out = array();
		foreach ( $fields as $fieldk => $fieldv ) {
			$out[ $fieldk ] = $fieldv;
			if ( 'file_from' == $fieldk ) {
				$label_ftp_profiles = __( 'View/Add FTP profiles' );
				$label_add_ftp_profile = __( 'Add new FTP profile' );
				$ftp_list = $this->get_ftp_profile_for_select( 'import' );
				$out['ftp_profile'] = array(
					'label' => __( 'Select an FTP profile' ),
					'type' => 'select',
					'tr_id' => 'ftp_profile_tr',
					'tr_class' => $fieldv['tr_class'], // add tr class from parent.Because we need to toggle the tr when parent tr toggles.
					'sele_vals' => $ftp_list,
					'field_name' => 'ftp_profile',
					'form_toggler' => array(
						'type' => 'child',
						'id' => 'wt_iew_file_from',
						'val' => 'ftp',
					),
					'validation_rule' => array( 'type' => 'int' ),
					'after_form_field_html' => '<a class="wt_iew_ftp_profiles" data-label-ftp-profiles="' . $label_ftp_profiles . '" data-label-add-ftp-profile="' . $label_add_ftp_profile . '" data-tab="' . ( count( $ftp_list ) > 1 ? 'ftp-profiles' : 'add-new-ftp' ) . '">' . ( count( $ftp_list ) > 1 ? $label_ftp_profiles : $label_add_ftp_profile ) . '</a>',
				);
				$out['use_default_path'] = array(
					'label' => __( 'Use default path' ),
					'type' => 'radio',
					'value' => 'Yes',
					'radio_fields' => array(
						'Yes' => __( 'Yes' ),
						'No' => __( 'No' ),
					),
					'tr_id' => 'use_default_path_tr',
					'field_name' => 'use_default_path',
					'form_toggler' => array(
						'type' => 'child',
						'id' => 'wt_iew_file_from',
						'val' => 'ftp',
					),
					'help_text' => __( 'Use import path from FTP profile.' ),
				);
				$out['import_path'] = array(
					'label' => __( 'Import path' ),
					'type' => 'text',
					'value' => '/',
					'tr_id' => 'import_path_tr',
					'css_class' => 'wt_iew_ftp_path',
					'tr_class' => $fieldv['tr_class'],
					'field_name' => 'import_path',
					'form_toggler' => array(
						'type' => 'child',
						'id' => 'wt_iew_file_from',
						'val' => 'ftp',
					),
				);
				$out['import_file'] = array(
					'label' => __( 'Import file' ),
					'type' => 'text',
					'value' => '',
					'tr_id' => 'import_file_tr',
					'tr_class' => $fieldv['tr_class'],
					'field_name' => 'import_file',
					'form_toggler' => array(
						'type' => 'child',
						'id' => 'wt_iew_file_from',
						'val' => 'ftp',
					),
				);

			}
		}

		return $out;
	}

	/**
	 * Add FTP related fields to the exporter advanced step
	 *
	 * @param array  $fields Access adapters.
	 * @param string $base Action.
	 * @param string $advanced_form_data Current adapter.
	 */
	public function exporter_alter_advanced_fields( $fields, $base, $advanced_form_data ) {
		$export_type_arr = array(
			'export_now' => __( 'Export now' ),
		);
		if ( Wt_Import_Export_For_Woo_Admin::module_exists( 'cron' ) ) {
			$export_type_arr['schedule_now'] = __( 'Schedule now' );
		}

		$out = array();
		foreach ( $fields as $fieldk => $fieldv ) {
			$out[ $fieldk ] = $fieldv;
			if ( 'file_into' == $fieldk ) {
				$label_ftp_profiles = __( 'View/Add FTP profiles' );
				$label_add_ftp_profile = __( 'Add new FTP profile' );
				$ftp_list = $this->get_ftp_profile_for_select( 'export' );
				$out['ftp_profile'] = array(
					'label' => __( 'Select an FTP profile' ),
					'type' => 'select',
					'tr_id' => 'ftp_profile_tr',
					'sele_vals' => $ftp_list,
					'field_name' => 'ftp_profile',
					'form_toggler' => array(
						'type' => 'child',
						'id' => 'wt_iew_file_into',
						'val' => 'ftp',
					),
					'validation_rule' => array( 'type' => 'int' ),
					'after_form_field_html' => '<a class="wt_iew_ftp_profiles" data-label-ftp-profiles="' . $label_ftp_profiles . '" data-label-add-ftp-profile="' . $label_add_ftp_profile . '" data-tab="' . ( count( $ftp_list ) > 1 ? 'ftp-profiles' : 'add-new-ftp' ) . '">' . ( count( $ftp_list ) > 1 ? $label_ftp_profiles : $label_add_ftp_profile ) . '</a>',
				);
				$out['use_default_path'] = array(
					'label' => __( 'Use default path' ),
					'type' => 'radio',
					'value' => 'Yes',
					'radio_fields' => array(
						'Yes' => __( 'Yes' ),
						'No' => __( 'No' ),
					),
					'tr_id' => 'use_default_path_tr',
					'field_name' => 'use_default_path',
					'form_toggler' => array(
						'type' => 'child',
						'id' => 'wt_iew_file_into',
						'val' => 'ftp',
					),
					'help_text' => __( 'Use export path from FTP profile.' ),
				);
				$out['export_path'] = array(
					'label' => __( 'Export path' ),
					'type' => 'text',
					'value' => '/',
					'tr_id' => 'export_path_tr',
					'css_class' => 'wt_iew_ftp_path',
					'field_name' => 'export_path',
					'form_toggler' => array(
						'type' => 'child',
						'id' => 'wt_iew_file_into',
						'val' => 'ftp',
					),
				);
			}
		}
		return $out;
	}

	/**
	 * Process ftp list for select boxes
	 *
	 * @param string $action Current action.
	 */
	public function get_ftp_profile_for_select( $action ) {
		$profiles = $this->get_ftp_data();
		$sele_arr = array();
		if ( $profiles && is_array( $profiles ) && count( $profiles ) > 0 ) {
			$sele_arr[0] = array(
				'value' => $this->lables['select_one'],
				'path' => '',
			);
			foreach ( $profiles as $profile ) {
				$path = ( 'export' == $action ? $profile['export_path'] : $profile['import_path'] );
				$sele_arr[ $profile['id'] ] = array(
					'value' => $profile['name'],
					'path' => $path,
				);
			}
		} else {
			$sele_arr[0] = array(
				'value' => $this->lables['no_ftp_prfile_found'],
				'path' => '',
			);
		}
		return $sele_arr;
	}

	/**
	 *   Test FTP connection
	 *
	 *   @param array $out output array sample.
	 */
	public function test_ftp( $out ) {
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		$test_ftp_fields = array(
			'wt_iew_hostname' => 'Host Name',
			'wt_iew_ftpuser' => 'Username',
			'wt_iew_ftppassword' => 'Password',
			'wt_iew_ftpport' => 'Port',
			'wt_iew_useftps' => 'FTPS',
			'wt_iew_passivemode' => 'Passive mode',
			'wt_iew_is_sftp' => 'SFTP',
		);
		$profile_data = array();
		foreach ( $test_ftp_fields as $ftp_form_field => $ftp_form_field_label ) {
			$val = ( isset( $_POST[ $ftp_form_field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $ftp_form_field ] ) ) : '' );// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Missing
			if ( '' === $val ) {
					/* translators: %s: FTP field label */
					$out['msg'] = sprintf( __( '%s is mandatory' ), $ftp_form_field_label );
				$out['status'] = false;
				break;
			} elseif ( 'wt_iew_ftpport' == $ftp_form_field && 0 === $val ) {
					/* translators: %s: FTP field label */
					$out['msg'] = sprintf( __( '%s is mandatory' ), $ftp_form_field_label );
					$out['status'] = false;
					break;
			}
			$profile_data[ $ftp_form_field ] = $val;
		}

		if ( $out['status'] ) {
			$ftp_profile = array(
				'server' => $profile_data['wt_iew_hostname'],
				'user_name' => $profile_data['wt_iew_ftpuser'],
				'password' => $profile_data['wt_iew_ftppassword'],
				'port' => $profile_data['wt_iew_ftpport'],
				'ftps' => $profile_data['wt_iew_useftps'],
				'passive_mode' => $profile_data['wt_iew_passivemode'],
				'is_sftp' => isset( $profile_data['wt_iew_is_sftp'] ) ? $profile_data['wt_iew_is_sftp'] : 0,
			);
			include_once plugin_dir_path( __FILE__ ) . 'classes/class-wt-import-export-for-woo-ftpadapter.php';
			$ftp_adapter = new Wt_Import_Export_For_Woo_FtpAdapter();
			$out = $ftp_adapter->test_ftp( 0, $ftp_profile );
		}

		return $out;
	}

	/**
	 * Delete FTP profile
	 *
	 * @param array $out output array sample.
	 */
	public function delete_ftp( $out ) {
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		$id = ( isset( $_POST['wt_iew_ftp_id'] ) ? intval( $_POST['wt_iew_ftp_id'] ) : 0 );// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already handled in ajax main function
		if ( $id > 0 ) {
			global $wpdb;
			$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$ftp_tb;
			$wpdb->delete( $tb, array( 'id' => $id ), array( '%d' ) );
		} else {
			$out['msg'] = __( 'Error' );
			$out['status'] = false;
		}
		return $out;
	}

	/**
	 * Ajax function to save FTP details
	 *
	 * @param array $out output array sample.
	 */
	private function save_ftp( $out ) {
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		$update_data = array();
		foreach ( $this->ftp_form_fields as $ftp_form_field ) {
			$val = isset( $_POST[ $ftp_form_field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $ftp_form_field ] ) ) : '';// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Missing -- nonce already handled in main function
			if ( '' === $val ) {
				$out['msg'] = __( 'All fields are mandatory' );
				$out['status'] = false;
				break;
			} elseif ( 'wt_iew_ftpport' == $ftp_form_field && 0 === $val ) {
					$out['msg'] = __( 'All fields are mandatory' );
					$out['status'] = false;
					break;
			}
			$update_data[ $ftp_form_field ] = $val;
		}

		$id = ( isset( $_POST['wt_iew_ftp_id'] ) ? intval( $_POST['wt_iew_ftp_id'] ) : 0 );// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already handled in ajax main function
		$name = stripslashes( $update_data['wt_iew_profilename'] );

		if ( $out['status'] ) {
			$ftp_data = $this->get_ftp_data_by_name( $name );
			if ( count( $ftp_data ) > 1 ) {
				$out['msg'] = __( 'FTP profile with same name already exists.' );
				$out['status'] = false;
			} elseif ( isset( $ftp_data[0]['id'] ) && $ftp_data[0]['id'] != $id ) {
					$out['msg'] = __( 'FTP profile with same name already exists.' );
					$out['status'] = false;
			}
		}

		if ( $out['status'] ) {
			$db_data = array(
				'name' => $name,
				'server' => $update_data['wt_iew_hostname'],
				'user_name' => $update_data['wt_iew_ftpuser'],
				'password' => $update_data['wt_iew_ftppassword'],
				'port' => $update_data['wt_iew_ftpport'],
				'export_path' => $update_data['wt_iew_ftpexport_path'],
				'import_path' => $update_data['wt_iew_ftpimport_path'],
				'ftps' => $update_data['wt_iew_useftps'],
				'passive_mode' => $update_data['wt_iew_passivemode'],
				'is_sftp' => isset( $update_data['wt_iew_is_sftp'] ) ? $update_data['wt_iew_is_sftp'] : 0,
			);
			$db_data_type = array( '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%d' );
			if ( $id > 0 ) {
				$out['id'] = $id;
				if ( ! $this->update_ftp_data( $id, $db_data, $db_data_type ) ) {
					$out['msg'] = __( 'Error' );
					$out['status'] = false;
				}
			} else {
				$id = $this->add_ftp_data( $db_data, $db_data_type );
				$out['id'] = $id;
				if ( 0 == $id ) {
					$out['msg'] = __( 'Error' );
					$out['status'] = false;
				}
			}
		}
		return $out;
	}

	/**
	 * Print Settings page HTML Ajax function
	 *
	 * @param array $out Output array.
	 */
	public function settings_page( $out ) {
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		$this->popup_page = ( isset( $_POST['popup_page'] ) ? intval( $_POST['popup_page'] ) : 0 );// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already handled in ajax main function
		include plugin_dir_path( __FILE__ ) . 'views/settings-page.php';
		exit(); // not return anything, prints html.
	}

	/**
	 * Print FTP list HTML
	 */
	private function get_ftplist_html() {
		$ftp_list = $this->get_ftp_data();
		include plugin_dir_path( __FILE__ ) . 'views/ftp-list.php';
	}

	/**
	 * Print FTP list HTML Ajax function
	 *
	 * @param array $out Output array.
	 */
	private function ftp_list( $out ) {
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		$this->popup_page = ( isset( $_POST['popup_page'] ) ? intval( $_POST['popup_page'] ) : 0 );// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already handled in ajax main function
		$this->get_ftplist_html();
		exit(); // not return anything, prints html.
	}

	/**
	 * Module settings form
	 *
	 * @param array $args output array sample.
	 */
	public function out_settings_form( $args ) {
		$this->enqueue_assets();
		$view_file = plugin_dir_path( __FILE__ ) . 'views/settings.php';

		$params = array();
		Wt_Import_Export_For_Woo_Admin::envelope_settings_tabcontent( 'wt-ftp', $view_file, '', $params, 0 );
	}

	/**
	 * Create FTP profile
	 *
	 * @param array $insert_data array of insert data.
	 * @param array $insert_data_type array of insert data format.
	 * @return array
	 */
	private function add_ftp_data( $insert_data, $insert_data_type ) {
		global $wpdb;
		$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$ftp_tb;
		if ( $wpdb->insert( $tb, $insert_data, $insert_data_type ) ) {
			return $wpdb->insert_id;
		}
		return 0;
	}

	/**
	 * Update FTP profile
	 *
	 * @param int   $id id of FTP profile.
	 * @param array $update_data array of update data.
	 * @param array $update_data_type array of update data format.
	 * @return array
	 */
	private function update_ftp_data( $id, $update_data, $update_data_type ) {
		global $wpdb;
		// updating the data.
		$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$ftp_tb;
		$update_where = array(
			'id' => $id,
		);
		$update_where_type = array(
			'%d',
		);
		if ( $wpdb->update( $tb, $update_data, $update_where, $update_data_type, $update_where_type ) !== false ) {
			return true;
		}
		return false;
	}

	/**
	 * Get FTP profile by name from DB
	 *
	 * @param string $name FTP profile.
	 * @return array
	 */
	private function get_ftp_data_by_name( $name ) {
		global $wpdb;
		$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$ftp_tb;
		$val = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_ftp WHERE name=%s", array( $name ) ), ARRAY_A );// @codingStandardsIgnoreLine.
		if ( $val ) {
			return $val;
		} else {
			return array();
		}
	}

	/**
	 * Get FTP profile by name from DB
	 *
	 * @param string $id FTP id.
	 * @return array
	 */
	public static function get_ftp_data_by_id( $id ) {
		global $wpdb;
		$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$ftp_tb;
		$val = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_ftp WHERE id=%d", array( $id ) ), ARRAY_A );// @codingStandardsIgnoreLine.
		if ( $val ) {
			return $val;
		} else {
			return false;
		}
	}

	/**
	 * Get FTP profile list from DB
	 *
	 * @return array list of FTP profiles.
	 */
	public static function get_ftp_data() {
		 global $wpdb;
		$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$ftp_tb;
		$val = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wt_iew_ftp ORDER BY id DESC", ARRAY_A );// @codingStandardsIgnoreLine.
		if ( $val ) {
			return $val;
		} else {
			return array();
		}
	}



	/**
	 * Unpack a compressed package file.
	 *
	 * @since 2.8.0
	 *
	 * @global WP_Filesystem_Base $wp_filesystem WordPress filesystem subclass.
	 */
	public function sftp_unpack_package() {

		$out = array();
		$out['msg'] = __( 'Download failed, please try again.' );
		$out['status'] = false;

		$package = 'https://www.webtoffee.com/wp-content/uploads/2022/04/wt-sftp-vendor.zip';
		$delete_package = false;

		// If the function it's not available, require it.
		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$download_file = file_put_contents( WP_CONTENT_DIR . '/wt-sftp-vendor.zip', fopen( $package, 'r' ), LOCK_EX );
		if ( false === $download_file ) {
			echo json_encode( $out );
			exit;
		}
		$zip = new ZipArchive();
		$res = $zip->open( WP_CONTENT_DIR . '/wt-sftp-vendor.zip' );
		if ( true === $res ) {
			$zip->extractTo( WP_CONTENT_DIR );
			$zip->close();
			$out['msg'] = __( 'Successfully downloaded the sFTP addon.' );
			$out['status'] = true;
			echo json_encode( $out );
			exit;
		} else {
			echo json_encode( $out );
			exit;
		}
		echo json_encode( $out );
		exit;
	}
}
new Wt_Import_Export_For_Woo_Ftp();
