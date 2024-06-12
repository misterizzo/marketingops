<?php
/**
 * Handles the import history.
 *
 * @package   ImportExportSuite\Admin\Modules\History
 * @version   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_History Class.
 */
class Wt_Import_Export_For_Woo_History {

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
	public $module_base = 'history';
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public static $status_arr = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public static $status_label_arr = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public static $action_label_arr = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $max_records = 50;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->module_id        = Wt_Import_Export_For_Woo::get_module_id( $this->module_base );
		self::$module_id_static = $this->module_id;

		self::$status_arr = array(
			'pending'  => 0,
			// running...
				'finished' => 1,
			// completed.
				'failed'   => 2,
		// failed.
		);

		self::$status_label_arr = array(
			0 => __( 'Running/Incomplete', 'import-export-suite-for-woocommerce' ),
			1 => __( 'Finished', 'import-export-suite-for-woocommerce' ),
			2 => __( 'Failed', 'import-export-suite-for-woocommerce' ),
		);

		self::$action_label_arr = array(
			'export'       => __( 'Export', 'import-export-suite-for-woocommerce' ),
			'import'       => __( 'Import', 'import-export-suite-for-woocommerce' ),
			'export_image' => __( 'Image Export', 'import-export-suite-for-woocommerce' ),
		);

		// Admin menu for hostory listing.
		add_filter( 'wt_iew_admin_menu', array( $this, 'add_admin_pages' ), 10, 1 );

		// advanced plugin settings.
		add_filter( 'wt_iew_advanced_setting_fields', array( $this, 'advanced_setting_fields' ), 12 );

		// main ajax hook. The callback function will decide which action is to execute.
		add_action( 'wp_ajax_iew_history_ajax', array( $this, 'ajax_main' ), 11 );

		// Hook to perform actions after advanced settings was updated.
		add_action( 'wt_iew_after_advanced_setting_update', array( $this, 'after_advanced_setting_update' ), 11 );

		// Download log file via nonce URL.
		add_action( 'admin_init', array( $this, 'download_file' ), 11 );
	}//end __construct()


	/**
	 * Adding admin menus
	 *
	 * @param array $menus Menus.
	 */
	public function add_admin_pages( $menus ) {
		$menus[ $this->module_base ] = array(
			'submenu',
			WT_IEW_PLUGIN_ID,
			__( 'History', 'import-export-suite-for-woocommerce' ),
			__( 'History', 'import-export-suite-for-woocommerce' ),
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
			array(
				$this,
				'admin_settings_page',
			),
		);
		// if(Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings('enable_import_log')==1){.
		$menus[ $this->module_base . '_log' ] = array(
			'submenu',
			WT_IEW_PLUGIN_ID,
			__( 'Import Logs', 'import-export-suite-for-woocommerce' ),
			__( 'Import Logs', 'import-export-suite-for-woocommerce' ),
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
			$this->module_id . '_log',
			array(
				$this,
				'admin_log_page',
			),
		);
		// }.
		return $menus;
	}//end add_admin_pages()

	/**
	 * Ajax main function - all history ajax action nonce verification is done here
	 */
	public function ajax_main() {
		if ( Wt_Iew_Sh::check_write_access( WT_IEW_PLUGIN_ID ) ) {
			// Nonce already verified on main function - sanitization thorugh helper functions.
			$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
			if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
					return false;
			}
			$allowed_ajax_actions = array( 'view_log' );

			$out = array(
				'status' => 0,
				'msg'    => __( 'Error' ),
			);
			$history_action = isset( $_POST['history_action'] ) ? sanitize_text_field( wp_unslash( $_POST['history_action'] ) ) : '';// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$data_type = isset( $_POST['data_type'] ) ? sanitize_text_field( wp_unslash( $_POST['data_type'] ) ) : '';// phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( method_exists( $this, $history_action ) && in_array( $history_action, $allowed_ajax_actions ) ) {
				$out = $this->{$history_action}( $out );
			}

			if ( 'json' == $data_type ) {
				echo json_encode( $out );
			}
		}

		exit();
	}//end ajax_main()


	/**
	 *    Ajax sub function to display logs
	 *
	 * @param array $out Response array.
	 */
	private function view_log( $out ) {
			// Nonce already verified on main function - sanitization thorugh helper functions.
			$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		$history_id  = ( isset( $_POST['history_id'] ) ? absint( $_POST['history_id'] ) : 0 );// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$out['html'] = '';

		if ( $history_id > 0 ) {
			$offset            = ( isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0 );// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$out['offset']     = $offset;
			$out['history_id'] = $history_id;

			$history_item = $this->get_history_entry_by_id( $history_id );
			if ( $history_item ) {
				// history item exists.
				$action_type = $history_item['template_type'];
				if ( 'import' == $action_type && Wt_Import_Export_For_Woo_Admin::module_exists( $action_type ) ) {
					$action_module_obj = Wt_Import_Export_For_Woo::load_modules( $action_type );
					$log_file_name     = $action_module_obj->get_log_file_name( $history_item['id'] );
					$log_file_path     = $action_module_obj->get_file_path( $log_file_name );
					if ( file_exists( $log_file_path ) ) {
						include_once WT_IEW_PLUGIN_PATH . 'admin/classes/class-wt-import-export-for-woo-logreader.php';
						$reader   = new Wt_Import_Export_For_Woo_Logreader();
						$response = $reader->get_data_as_batch( $log_file_path, $offset );
						if ( $response['response'] ) {
							$log_list        = $response['data_arr'];
							$out['offset']   = $response['offset'];
							$out['status']   = 1;
							$out['finished'] = $response['finished'];

							$is_finished = $response['finished'];
							$new_offset  = $response['offset'];

							$show_item_details    = false;
							$item_type_module_obj = Wt_Import_Export_For_Woo::load_modules( $history_item['item_type'] );
							if ( ! is_null( $item_type_module_obj ) && method_exists( $item_type_module_obj, 'get_item_by_id' ) ) {
								   $show_item_details = true;
							}

							ob_start();
							include plugin_dir_path( __FILE__ ) . 'views/log-table.php';
							$out['html'] = ob_get_clean();
						}
					}//end if
				}//end if
			}//end if
		} else // raw log viewing.
		{
			$log_file_name = ( isset( $_POST['log_file'] ) ? sanitize_text_field( wp_unslash( $_POST['log_file'] ) ) : '' );// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( '' != $log_file_name ) {
				$ext_arr = explode( '.', $log_file_name );
				$ext     = end( $ext_arr );
				if ( 'log' == $ext ) {
					$log_file_path = Wt_Import_Export_For_Woo_Log::get_file_path( $log_file_name );
					if ( file_exists( $log_file_path ) ) {
						include_once WT_IEW_PLUGIN_PATH . 'admin/classes/class-wt-import-export-for-woo-logreader.php';
						$reader   = new Wt_Import_Export_For_Woo_Logreader();
						$response = $reader->get_full_data( $log_file_path );

						$out['status'] = 1;
						$out['html']   = '<div class="wt_iew_raw_log">' . nl2br( esc_html( $response['data_str'] ) ) . '</div>';
					}
				}
			}
		}//end if

		return $out;
	}//end view_log()


	/**
	 *    Fields for advanced settings
	 *
	 * @param array $fields Form fields.
	 */
	public function advanced_setting_fields( $fields ) {

		$fields['advanced_field_head']        = array(
			'type'            => 'field_group_head',
			// field type.
				'head'            => __( 'Advanced options', 'import-export-suite-for-woocommerce' ),
			'group_id'        => 'advanced_field',
			// field group id.
				'show_on_default' => 0,
		);
		$fields['enable_history_auto_delete'] = array(
			'label'           => __( 'Auto delete history', 'import-export-suite-for-woocommerce' ),
			'type'            => 'radio',
			'radio_fields'    => array(
				1 => __( 'Yes' ),
				0 => __( 'No' ),
			),
			'value'           => 1,
			'field_name'      => 'enable_history_auto_delete',
			'field_group'     => 'advanced_field',
			'help_text'       => __( 'Clicking ‘Yes’ will automatically delete the records in the history section', 'import-export-suite-for-woocommerce' ),
			'validation_rule' => array( 'type' => 'absint' ),
			'form_toggler'    => array(
				'type'   => 'parent',
				'target' => 'wt_iew_enable_history_auto_delete',
			),
		);

		$fields['auto_delete_history_count'] = array(
			'label'           => __( 'Maximum entries', 'import-export-suite-for-woocommerce' ),
			'type'            => 'number',
			'value'           => 100,
			'attr'            => array( 'style' => 'width:30%;' ),
			'field_name'      => 'auto_delete_history_count',
			'field_group'     => 'advanced_field',
			'help_text'       => __( 'Indicates the maximum records to retain in history. Limit the number of records with status ‘Finished’. E.g On giving an input of 50, the system will retain(not delete) the latest 50 records with status ‘Finished’. Any other record with a different status will not be retained.', 'import-export-suite-for-woocommerce' ),
			'validation_rule' => array( 'type' => 'absint' ),
			'form_toggler'    => array(
				'type' => 'child',
				'id'   => 'wt_iew_enable_history_auto_delete',
				'val'  => 1,
			),
		);

		return $fields;
	}//end advanced_setting_fields()

	/**
	 * Admin log page
	 */
	public function admin_log_page() {
		// delete action.
		if ( isset( $_GET['wt_iew_delete_log'] ) ) {

			if ( Wt_Iew_Sh::check_write_access( WT_IEW_PLUGIN_ID ) ) {

				$log_file_arr = isset( $_GET['wt_iew_log_file'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_GET['wt_iew_log_file'] ) ) ) : array();
				$log_file_arr = Wt_Iew_Sh::sanitize_item( $log_file_arr, 'text_arr' );

				if ( is_array( $log_file_arr ) ) {
					foreach ( $log_file_arr as $log_file_name ) {
						if ( '' !== $log_file_name ) {
							$ext_arr = explode( '.', $log_file_name );
							$ext = end( $ext_arr );
							if ( 'log' === $ext ) {
								$log_file_path = Wt_Import_Export_For_Woo_Log::get_file_path( $log_file_name );
								if ( file_exists( $log_file_path ) && is_file( $log_file_path ) ) {
									@unlink( $log_file_path );
								}
							}
						}
					}
				}
			}
		}

		$delete_url_params['wt_iew_delete_log'] = 1;
		$delete_url_params['wt_iew_log_file']   = '_log_file_';
		$delete_url_params['page']        = 'wt_import_export_for_woo';
		$delete_url_params['tab'] = 'logs';
		$delete_url = wp_nonce_url( admin_url( 'admin.php?' . http_build_query( $delete_url_params ) ), WT_IEW_PLUGIN_ID );

		$download_url = wp_nonce_url( admin_url( 'admin.php?wt_iew_log_download=1&file=_log_file_' ), WT_IEW_PLUGIN_ID );

		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : null;
		// enqueue script.
		if ( 'logs' == $tab ) {
			$this->enqueue_scripts( $delete_url );
		}

		include plugin_dir_path( __FILE__ ) . 'views/settings.php';
	}//end admin_log_page()


	/**
	 *  History list page
	 */
	public function admin_settings_page() {
		global $wpdb;

		// delete action.
		if ( isset( $_GET['wt_iew_delete_history'] ) ) {
			if ( Wt_Iew_Sh::check_write_access( WT_IEW_PLUGIN_ID ) ) {
				$history_id_arr = isset( $_GET['wt_iew_history_id'] ) ? map_deep( explode( ',', sanitize_text_field( wp_unslash( $_GET['wt_iew_history_id'] ) ) ), 'absint' ) : array();// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$history_id_arr = Wt_Iew_Sh::sanitize_item( $history_id_arr, 'absint_arr' );
				if ( count( $history_id_arr ) > 0 ) {
					self::delete_history_by_id( $history_id_arr );
				}
			}
		}

		/*
		 *    Lising page section
		 */
		$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$history_tb;

		$post_type_arr   = self::get_disticnt_items( 'item_type' );
		$action_type_arr = self::get_disticnt_items( 'template_type' );
		$status_arr      = self::get_disticnt_items( 'status' );
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $post_types    Importer post types.
		 */
		$importer_post_types = apply_filters( 'wt_iew_importer_post_types', array() );
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $post_types    Exporter post types.
		 */
		$exporter_post_types = apply_filters( 'wt_iew_exporter_post_types', array() );
		$post_type_label_arr = array_merge( $importer_post_types, $exporter_post_types );

		/*
		 *    Get history entries by Schedule ID
		 */
		$cron_id      = ( isset( $_GET['wt_iew_cron_id'] ) ? absint( $_GET['wt_iew_cron_id'] ) : 0 );
		$history_arr  = array();
		$list_by_cron = false;
		if ( $cron_id > 0 ) {
			$cron_module_obj = Wt_Import_Export_For_Woo::load_modules( 'cron' );
			if ( ! is_null( $cron_module_obj ) ) {
				$cron_data = $cron_module_obj->get_cron_by_id( $cron_id );
				if ( $cron_data ) {
					$history_id_arr = ( '' != $cron_data['history_id_list'] ? maybe_unserialize( $cron_data['history_id_list'] ) : array() );
					$history_id_arr = ( is_array( $history_id_arr ) ? $history_id_arr : array() );
					$list_by_cron   = true;
				} else {
					$cron_id = 0;
					// invalid cron id.
				}
			} else {
				$cron_id = 0;
				// cron module not enabled.
			}
		}

		/*
		 *    Filter by form fields
		 */
		$filter_by = array(
			'item_type'     => array(
				'label'        => __( 'Post type' ),
				'values'       => $post_type_arr,
				'val_labels'   => $post_type_label_arr,
				'val_type'     => '%s',
				'selected_val' => '',
			),
			'template_type' => array(
				'label'        => __( 'Action type' ),
				'values'       => $action_type_arr,
				'val_labels'   => self::$action_label_arr,
				'val_type'     => '%s',
				'selected_val' => '',
			),
			'status'        => array(
				'label'           => __( 'Status' ),
				'values'          => $status_arr,
				'val_labels'      => self::$status_label_arr,
				'validation_rule' => array( 'type' => 'absint' ),
				'val_type'        => '%d',
				'selected_val'    => '',
			),
		);

		if ( $list_by_cron ) {
			// no need of these filters in `cron by` listing.
			unset( $filter_by['item_type'] );
			unset( $filter_by['template_type'] );
		}

		/*
		 *    Order by field vals
		 */
		$order_by = array(
			'date_desc' => array(
				'label' => __( 'Date descending' ),
				'sql'   => 'created_at DESC',
			),
			'date_asc'  => array(
				'label' => __( 'Date ascending' ),
				'sql'   => 'created_at ASC',
			),
		);

		// just applying a text validation.
		$conf_arr           = isset( $_GET['wt_iew_history'] ) ? map_deep( wp_unslash( $_GET['wt_iew_history'] ), 'sanitize_text_field' ) : array();// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$url_params_allowed = array();
		// this array will only include the allowed $_GET params. This will use in pagination section.

		/*
		 *    Filter by block
		 */
		$where_qry_val_arr = array();
		// sql query WHERE clause val array.
		$where_qry_format_arr = array();
		$where_qry_val_pair = array();
		// sql query  WHERE clause val format array.
		if ( isset( $conf_arr['filter_by'] ) ) {
			// filter by GET var exists.
			$url_params_allowed['filter_by'] = array();
			// for pagination purpose.
			$filter_by_conf            = ( is_array( $conf_arr['filter_by'] ) ? $conf_arr['filter_by'] : array() );
			$filter_by_validation_rule = Wt_Import_Export_For_Woo_Common_Helper::extract_validation_rules( $filter_by );
			foreach ( $filter_by as $filter_key => $filter_val ) {
				if ( isset( $filter_by_conf[ $filter_key ] ) && trim( $filter_by_conf[ $filter_key ] ) != '' ) {
					// current filter applied.
					$where_qry_format_arr[] = $filter_key . '=' . $filter_val['val_type'];
					$filter_by[ $filter_key ]['selected_val'] = Wt_Iew_Sh::sanitize_data( $filter_by_conf[ $filter_key ], $filter_key, $filter_by_validation_rule );
					$where_qry_val_arr[] = $filter_by[ $filter_key ]['selected_val'];
					$where_qry_val_pair[ $filter_key ] = $filter_by[ $filter_key ]['selected_val'];
					$url_params_allowed['filter_by'][ $filter_key ] = $filter_by[ $filter_key ]['selected_val'];
					// for pagination purpose.
				}
			}
		}//end if

		/*
		 *    Order by block
		 */
		$default_order_by  = array_keys( $order_by )[0];
		$order_by_val      = $default_order_by;
		$order_qry_val_arr = array();
		// sql query ORDER clause val array.
		if ( isset( $conf_arr['order_by'] ) ) {
			// order by GET var exists.
			$order_by_val = ( is_array( $conf_arr['order_by'] ) ? $default_order_by : $conf_arr['order_by'] );
		}

		if ( isset( $order_by[ $order_by_val ] ) ) {
			$order_qry_val_arr[]            = $order_by[ $order_by_val ]['sql'];
			$url_params_allowed['order_by'] = $order_by_val;
			// for pagination purpose.
		}

		/*
		 *    Pagination block
		 */
		$max_data          = ( isset( $conf_arr['max_data'] ) ? absint( $conf_arr['max_data'] ) : $this->max_records );
		$this->max_records = ( $max_data > 0 ? $max_data : $this->max_records );

		$offset = ( isset( $_GET['offset'] ) ? absint( $_GET['offset'] ) : 0 );
		$url_params_allowed['max_data'] = $this->max_records;
		$pagination_url_params          = array(
			'wt_iew_history' => $url_params_allowed,
			'tab'            => 'history',
			'page'           => str_replace( '_history', '', $this->module_id ),
		);
		$offset_qry_str = " LIMIT $offset, " . $this->max_records;
		$no_records     = false;

		if ( $list_by_cron ) {
			// list by cron.
			$pagination_url_params['wt_iew_cron_id'] = $cron_id;
			// adding cron id to URL params.
			$total_history_ids = count( $history_id_arr );
			if ( $total_history_ids > 0 ) {
				$where_qry_format_arr[] = 'id IN(' . implode( ',', array_fill( 0, $total_history_ids, '%d' ) ) . ')';
				$where_qry_val_arr      = array_merge( $where_qry_val_arr, $history_id_arr );
			} else // reset all where, order by queries.
			{
				$where_qry_format_arr = array();
				$where_qry_val_arr    = array();
				// $order_qry_val_arr=array();.
				$no_records = true;
			}
		}

		$where_qry   = ( count( $where_qry_format_arr ) > 0 ? ' WHERE ' . implode( ' AND ', $where_qry_format_arr ) : '' );
		$orderby_qry = ( count( $order_qry_val_arr ) > 0 ? ' ORDER BY ' . implode( ', ', $order_qry_val_arr ) : '' );

		if ( $no_records ) {
			// in list_by cron, history IDs are not available.
			$total_records = 0;
			$history_list  = array();
		} else {

			if ( $list_by_cron && $total_history_ids > 0 ) {
				$total_records = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(id) AS total_records FROM {$wpdb->prefix}wt_iew_action_history WHERE id IN( " . implode( ',', array_fill( 0, count( $history_id_arr ), '%d' ) ) . ' )', $where_qry_val_arr ), ARRAY_A );// @codingStandardsIgnoreLine.
				$where_qry_val_arr[] = $offset;
				$where_qry_val_arr[] = $this->max_records;
				$history_list = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_action_history WHERE id IN( " . implode( ',', array_fill( 0, count( $history_id_arr ), '%d' ) ) . ' )  LIMIT %d, %d', $where_qry_val_arr ), ARRAY_A );// @codingStandardsIgnoreLine.
			} elseif ( ! empty( $where_qry_val_pair ) ) {
				if ( isset( $where_qry_val_pair['item_type'] ) && isset( $where_qry_val_pair['template_type'] ) && isset( $where_qry_val_pair['status'] ) ) {
					$history_list = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_action_history WHERE item_type=%s AND template_type=%s AND status=%d ORDER BY %1s LIMIT %d, %d", $where_qry_val_pair['item_type'], $where_qry_val_pair['template_type'], $where_qry_val_pair['status'], $order_qry_val_arr[0], $offset, $this->max_records ), ARRAY_A );// @codingStandardsIgnoreLine.
					$total_records = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(id) AS total_records FROM {$wpdb->prefix}wt_iew_action_history WHERE item_type=%s AND template_type=%s AND status=%d", $where_qry_val_pair['item_type'], $where_qry_val_pair['template_type'], $where_qry_val_pair['status'] ), ARRAY_A );// @codingStandardsIgnoreLine.
				} elseif ( isset( $where_qry_val_pair['item_type'] ) && isset( $where_qry_val_pair['template_type'] ) ) {
					$history_list = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_action_history WHERE item_type=%s AND template_type=%s ORDER BY %1s LIMIT %d, %d", $where_qry_val_pair['item_type'], $where_qry_val_pair['template_type'], $order_qry_val_arr[0], $offset, $this->max_records ), ARRAY_A );// @codingStandardsIgnoreLine.
					$total_records = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(id) AS total_records FROM {$wpdb->prefix}wt_iew_action_history WHERE item_type=%s AND template_type=%s", $where_qry_val_pair['item_type'], $where_qry_val_pair['template_type'] ), ARRAY_A );// @codingStandardsIgnoreLine.
				} elseif ( isset( $where_qry_val_pair['item_type'] ) && isset( $where_qry_val_pair['status'] ) ) {
					$history_list = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_action_history WHERE item_type=%s AND status=%d ORDER BY %1s LIMIT %d, %d", $where_qry_val_pair['item_type'], $where_qry_val_pair['status'], $order_qry_val_arr[0], $offset, $this->max_records ), ARRAY_A );// @codingStandardsIgnoreLine.
					$total_records = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(id) AS total_records FROM {$wpdb->prefix}wt_iew_action_history WHERE item_type=%s AND status=%d", $where_qry_val_pair['item_type'], $where_qry_val_pair['status'] ), ARRAY_A );// @codingStandardsIgnoreLine.
				} elseif ( isset( $where_qry_val_pair['template_type'] ) && isset( $where_qry_val_pair['status'] ) ) {
					$history_list = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_action_history WHERE template_type=%s AND status=%d ORDER BY %1s LIMIT %d, %d", $where_qry_val_pair['template_type'], $where_qry_val_pair['status'], $order_qry_val_arr[0], $offset, $this->max_records ), ARRAY_A );// @codingStandardsIgnoreLine.
					$total_records = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(id) AS total_records FROM {$wpdb->prefix}wt_iew_action_history WHERE template_type=%s AND status=%d", $where_qry_val_pair['template_type'], $where_qry_val_pair['status'] ), ARRAY_A );// @codingStandardsIgnoreLine.
				} elseif ( isset( $where_qry_val_pair['item_type'] ) ) {
					$history_list = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_action_history WHERE item_type=%s ORDER BY %1s LIMIT %d, %d", $where_qry_val_pair['item_type'], $order_qry_val_arr[0], $offset, $this->max_records ), ARRAY_A );// @codingStandardsIgnoreLine.
					$total_records = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(id) AS total_records FROM {$wpdb->prefix}wt_iew_action_history WHERE item_type=%s", $where_qry_val_pair['item_type'] ), ARRAY_A );// @codingStandardsIgnoreLine.
				} elseif ( isset( $where_qry_val_pair['template_type'] ) ) {
					$history_list = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_action_history WHERE template_type=%s ORDER BY %1s LIMIT %d, %d", $where_qry_val_pair['template_type'], $order_qry_val_arr[0], $offset, $this->max_records ), ARRAY_A );// @codingStandardsIgnoreLine.
					$total_records = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(id) AS total_records FROM {$wpdb->prefix}wt_iew_action_history WHERE template_type=%s", $where_qry_val_pair['template_type'] ), ARRAY_A );// @codingStandardsIgnoreLine.
				} elseif ( isset( $where_qry_val_pair['status'] ) ) {
					$history_list = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_action_history WHERE status=%d ORDER BY %1s LIMIT %d, %d", $where_qry_val_pair['status'], $order_qry_val_arr[0], $offset, $this->max_records ), ARRAY_A );// @codingStandardsIgnoreLine.
					$total_records = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(id) AS total_records FROM {$wpdb->prefix}wt_iew_action_history WHERE status=%d", $where_qry_val_pair['status'] ), ARRAY_A );// @codingStandardsIgnoreLine.
				}
			} else {
				$history_list = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_action_history  ORDER BY %1s LIMIT %d, %d", $order_qry_val_arr[0], $offset, $this->max_records ), ARRAY_A );// @codingStandardsIgnoreLine.
				$total_records = $wpdb->get_row( "SELECT COUNT(id) AS total_records FROM {$wpdb->prefix}wt_iew_action_history", ARRAY_A );// @codingStandardsIgnoreLine.

			}

			$total_records = ( $total_records && isset( $total_records['total_records'] ) ? $total_records['total_records'] : 0 );
			$history_list = ( $history_list ? $history_list : array() );
		}

		$delete_url_params = $pagination_url_params;
		$delete_url_params['wt_iew_delete_history'] = 1;
		$delete_url_params['wt_iew_history_id']     = '_history_id_';
		$delete_url_params['offset'] = $offset;
		$delete_url = wp_nonce_url( admin_url( 'admin.php?' . http_build_query( $delete_url_params ) ), WT_IEW_PLUGIN_ID );

		// enqueue script.
		if ( isset( $_GET['page'] ) && 'wt_import_export_for_woo' == $_GET['page'] ) {
			$this->enqueue_scripts( $delete_url );
		}

		include plugin_dir_path( __FILE__ ) . 'views/settings.php';
	}//end admin_settings_page()

	/**
	 * Enques scripts
	 *
	 * @param string $delete_url Delete URL.
	 */
	private function enqueue_scripts( $delete_url ) {
		if ( Wt_Import_Export_For_Woo_Common_Helper::wt_is_screen_allowed() ) {
			wp_enqueue_script( $this->module_id, plugin_dir_url( __FILE__ ) . 'assets/js/main.js', array( 'jquery' ), WT_IEW_VERSION, false );

			$params = array(
				'delete_url' => $delete_url,
				'msgs'       => array(
					'sure' => __( 'Are you sure?' ),
				),
			);
			wp_localize_script( $this->module_id, 'wt_iew_history_params', $params );
		}
	}//end enqueue_scripts()

	/**
	 * Record failure
	 *
	 * @param string $history_id Delete URL.
	 * @param string $msg Delete URL.
	 */
	public static function record_failure( $history_id, $msg ) {
		$update_data = array(
			'status'      => self::$status_arr['failed'],
			'status_text' => $msg,
		// no need to add translation function.
		);
		$update_data_type = array(
			'%d',
			'%s',
		);
		self::update_history_entry( $history_id, $update_data, $update_data_type );
	}//end record_failure()


	/**
	 *  Delete history entry from DB and also associated files (Export files only)
	 *
	 * @param array|int $id history entry IDs.
	 */
	public static function delete_history_by_id( $id ) {
		global $wpdb;
		$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$history_tb;
		if ( is_array( $id ) ) {
			$where      = ' IN(' . implode( ',', array_fill( 0, count( $id ), '%d' ) ) . ')';
			$where_data = $id;
		} else {
			$where      = '=%d';
			$where_data = array( $id );
		}

		// first remove files associated with it. give argument as array then no need to check the result array type.
		$allowed_ext_arr = array(
			'csv',
			'xml',
		);
		// please update this array if new file types introduced.
		$list = self::get_history_entry_by_id( $where_data );
		if ( $list ) {
			foreach ( $list as $listv ) {
				if ( 'export' == $listv['template_type'] ) {
					// history is for export action.
					if ( Wt_Import_Export_For_Woo_Admin::module_exists( 'export' ) ) {
						$ext_arr = explode( '.', $listv['file_name'] );
						$ext     = end( $ext_arr );
						if ( in_array( $ext, $allowed_ext_arr ) ) {
							// delete only allowed extensions.
							$file_path = Wt_Import_Export_For_Woo_Export::get_file_path( $listv['file_name'] );
							if ( $file_path && file_exists( $file_path ) ) {
								@unlink( $file_path );
							}
						}
					}
				} else if ( 'import' == $listv['template_type'] ) {
					$action_module_obj = Wt_Import_Export_For_Woo::load_modules( 'import' );

					$log_file_name = $action_module_obj->get_log_file_name( $listv['id'] );
					$log_file_path = $action_module_obj->get_file_path( $log_file_name );
					if ( file_exists( $log_file_path ) ) {
						@unlink( $log_file_path );
					}
					$log_path = Wt_Import_Export_For_Woo_Log::$log_dir;
					$wt_log_path = glob( $log_path . '/' . $listv['id'] . '_*.log' );
					if ( isset( $wt_log_path[0] ) && ! empty( $wt_log_path[0] ) && file_exists( $wt_log_path[0] ) ) {
						@unlink( $wt_log_path[0] );
					}
				}//end if
			}//end foreach
		}//end if

		if ( is_array( $id ) ) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wt_iew_action_history WHERE id IN( " . implode( ',', array_fill( 0, count( $id ), '%d' ) ) . ' )', $where_data ) );
		} else {
			$where      = '=%d';
			$where_data = array( $id );
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wt_iew_action_history WHERE id =%d", $where_data ) );
		}
	}//end delete_history_by_id()

	/**
	 *    Update history
	 *
	 * @param integer $history_id Advanced settings.
	 * @param array   $update_data Update data.
	 * @param string  $update_data_type Update type.
	 */
	public static function update_history_entry( $history_id, $update_data, $update_data_type ) {
		global $wpdb;
		// updating the data.
		$tb           = $wpdb->prefix . Wt_Import_Export_For_Woo::$history_tb;
		$update_where = array( 'id' => $history_id );
		$update_where_type = array( '%d' );
		if ( $wpdb->update( $tb, $update_data, $update_where, $update_data_type, $update_where_type ) !== false ) {
			return true;
		}

		return false;
	}//end update_history_entry()


	/**
	 *    Mathod perform actions after advanced settings was updated
	 *
	 * @param array $advanced_settings Advanced settings.
	 */
	public function after_advanced_setting_update( $advanced_settings ) {
		// Check auto deletion enabled.
		if ( isset( $advanced_settings['wt_iew_enable_history_auto_delete'] ) && 1 == $advanced_settings['wt_iew_enable_history_auto_delete'] ) {
			$record_count = ( isset( $advanced_settings['wt_iew_auto_delete_history_count'] ) ? absint( $advanced_settings['wt_iew_auto_delete_history_count'] ) : 0 );
			if ( $record_count > 0 ) {
				self::auto_delete_history_entry( $record_count );
			}
		}
	}//end after_advanced_setting_update()


	/**
	 *    Check and delete history entry. If auto deletion enabled
	 *
	 * @param integer $record_count Record count.
	 */
	public static function auto_delete_history_entry( $record_count = 0 ) {
		if ( 0 == $record_count ) {
			// this condition is for, some requests will come from create section or some from advanced settings section.
			if ( Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings( 'enable_history_auto_delete' ) == 1 ) {
				 $record_count = absint( Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings( 'auto_delete_history_count' ) );
			}
		}

		if ( $record_count >= 1 ) {
			global $wpdb;
			$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$history_tb;
			$limit_record_count = $record_count - 1;
			$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_action_history WHERE status = 1 AND id<(SELECT id FROM {$wpdb->prefix}wt_iew_action_history ORDER BY id DESC LIMIT %1s, 1 ) ", $limit_record_count ), ARRAY_A );// @codingStandardsIgnoreLine.
			if ( $data && is_array( $data ) ) {
				$id_arr = array_column( $data, 'id' );
				self::delete_history_by_id( $id_arr );
			}
		}
	}//end auto_delete_history_entry()

	/**
	 * Create a history entry before starting export/import
	 *
	 * @param  string  $file_name String export/import file name.
	 * @param  array   $form_data  Array export/import formdata.
	 * @param  string  $to_process  String export or import.
	 * @param integer $action  Int DB id if success otherwise zero.
	 * @return integer  0 or created id.
	 */
	public static function create_history_entry( $file_name, $form_data, $to_process, $action ) {
		global $wpdb;

		$tb          = $wpdb->prefix . Wt_Import_Export_For_Woo::$history_tb;
		$insert_data = array(
			'template_type' => $action,
			'item_type'     => $to_process,
			// item type Eg: product.
				'file_name'     => $file_name,
			// export/import file name.
				'created_at'    => time(),
			// craeted time.
				'data'          => maybe_serialize( $form_data ),
			// formadata.
				'status'        => self::$status_arr['pending'],
			// pending.
				'status_text'   => 'Pending',
			// pending, No need to add translate function. we can add this on printing page.
				'offset'        => 0,
			// current offset, its always 0 on start.
				'total'         => 0,
		// total records, not available now.
		);
		$insert_data_type = array(
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
			'%d',
			'%d',
			'%d',
		);

		$insert_response = $wpdb->insert( $tb, $insert_data, $insert_data_type );

		// check for auto delete.
		self::auto_delete_history_entry();

		if ( $insert_response ) {
			// success.
			return $wpdb->insert_id;
		}

		return 0;
	}//end create_history_entry()


	/**
	 *     Get distinct column values from history table
	 *
	 * @param  string $column table column name.
	 * @return array array of distinct column values.
	 */
	private static function get_disticnt_items( $column ) {
		global $wpdb;
		$tb   = $wpdb->prefix . Wt_Import_Export_For_Woo::$history_tb;
		// $data = $wpdb->get_results( "SELECT DISTINCT $column FROM $tb ORDER BY $column ASC", ARRAY_A ); // @codingStandardsIgnoreLine.
		$column1 = $column;
		$data = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT %1s FROM {$wpdb->prefix}wt_iew_action_history ORDER BY %s ASC", $column, $column ), ARRAY_A ); // @codingStandardsIgnoreLine.
		$data = is_array( $data ) ? $data : array();
		return array_column( $data, $column );
	}//end get_disticnt_items()
	/**
	 *     Taking history entry by ID
	 *
	 * @param integer $id History entry id.
	 */
	public static function get_history_entry_by_id( $id ) {
		global $wpdb;
		$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$history_tb;
		if ( is_array( $id ) ) {
			$where      = ' IN(' . implode( ',', array_fill( 0, count( $id ), '%d' ) ) . ')';
			$where_data = $id;
		} else {
			$where      = '=%d';
			$where_data = array( $id );
		}

		if ( is_array( $id ) ) {
			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_action_history WHERE id IN( " . implode( ',', array_fill( 0, count( $id ), '%d' ) ) . ' )', $where_data ), ARRAY_A );
		} else {
			$where      = '=%d';
			$where_data = array( $id );
			return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_action_history WHERE id =%d", $where_data ), ARRAY_A );
		}
	}//end get_history_entry_by_id()


	/**
	 *      Download log file via a nonce URL
	 */
	public function download_file() {
		if ( isset( $_GET['wt_iew_log_download'] ) ) {
			if ( Wt_Iew_Sh::check_write_access( WT_IEW_PLUGIN_ID ) ) {
				// check nonce and role.
				$file_name = ( isset( $_GET['file'] ) ? sanitize_text_field( wp_unslash( $_GET['file'] ) ) : '' );
				if ( '' != $file_name ) {
					$file_arr = explode( '.', $file_name );
					$file_ext = end( $file_arr );
					if ( 'log' == $file_ext ) {
						// Only allowed files.
						$file_path = Wt_Import_Export_For_Woo_Log::get_file_path( $file_name );
						if ( file_exists( $file_path ) && is_file( $file_path ) ) {
							header( 'Pragma: public' );
							header( 'Expires: 0' );
							header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
							header( 'Cache-Control: private', false );
							header( 'Content-Transfer-Encoding: binary' );
							header( 'Content-Disposition: attachment; filename="' . $file_name . '";' );
							header( 'Content-Description: File Transfer' );
							header( 'Content-Type: application/octet-stream' );
							// header('Content-Length: '.filesize($file_path));.
							$chunk_size = ( 1024 * 1024 );
							$handle     = @fopen( $file_path, 'rb' );
							while ( ! feof( $handle ) ) {
								$buffer = fread( $handle, $chunk_size );
								echo wp_kses_post( $buffer );// @codingStandardsIgnoreLine.
								ob_flush();
								flush();
							}

							fclose( $handle );
							exit();
						}//end if
					}//end if
				}//end if
			}//end if
		}//end if
	}//end download_file()


	/**
	 *     Generate pagination HTML
	 *
	 * @param type $total Description.
	 * @param type $limit Description.
	 * @param type $offset Description.
	 * @param type $url Description.
	 * @param type $url_params Description.
	 * @param type $mxnav Description.
	 */
	public static function gen_pagination_html( $total, $limit, $offset, $url, $url_params = array(), $mxnav = 6 ) {
		if ( $total <= 0 ) {
			return '';
		}

		// taking current page.
		$crpage = ( ( $offset + $limit ) / $limit );

		$limit = $limit <= 0 ? 1 : $limit;
		$ttpg  = ceil( $total / $limit );
		if ( $ttpg < $crpage ) {
			error_log( 'Total page less than current page' );
		}

		 // calculations.
		$mxnav = $ttpg < $mxnav ? $ttpg : $mxnav;

		$mxnav_mid  = floor( $mxnav / 2 );
		$pgstart    = $mxnav_mid >= $crpage ? 1 : ( $crpage - $mxnav_mid );
		$mxnav_mid += $mxnav_mid >= $crpage ? ( $mxnav_mid - $crpage ) : 0;
		// adjusting other half with first half balance.
		$pgend = ( $crpage + $mxnav_mid );
		if ( $pgend > $ttpg ) {
			$pgend = $ttpg;
		}

		$html = '<span class="wt_iew_pagination_total_info">' . $total . __( ' record(s)' ) . '</span>';
		$url_params_string = http_build_query( $url_params );
		$url_params_string = ( '' != $url_params_string ) ? '&' . $url_params_string : '';
		$url       = ( false !== strpos( $url, '?' ) ) ? $url . '&' : $url . '?';
		$href_attr = ' href="' . $url . 'offset={offset}' . $url_params_string . '"';

		$prev_onclick = '';
		if ( $crpage > 1 ) {
			$offset       = ( ( $crpage - 2 ) * $limit );
			$prev_onclick = str_replace( '{offset}', $offset, $href_attr );
		}

		$html .= '<a class="' . ( $crpage > 1 ? 'wt_iew_page' : 'wt_iew_pagedisabled' ) . '"' . $prev_onclick . '>‹</a>';
		for ( $i = $pgstart; $i <= $pgend; $i++ ) {
			$page_offset = '';
			$onclick     = '';
			$offset      = ( ( $i * $limit ) - $limit );
			if ( $i != $crpage ) {
				$onclick = str_replace( '{offset}', $offset, $href_attr );
			}

			$html .= '<a class="' . ( $i == $crpage ? 'wt_iew_pageactive' : 'wt_iew_page' ) . '" ' . $onclick . '>' . $i . '</a>';
		}

		$next_onclick = '';
		if ( $crpage < $ttpg ) {
			$offset       = ( $crpage * $limit );
			$next_onclick = str_replace( '{offset}', $offset, $href_attr );
		}

		$html .= '<a class="' . ( $crpage < $ttpg ? 'wt_iew_page' : 'wt_iew_pagedisabled' ) . '"' . $next_onclick . '>›</a>';
		return '<div class="wt_iew_pagination"><span>' . $html . '</div>';
	}//end gen_pagination_html()
}//end class

Wt_Import_Export_For_Woo::$loaded_modules['history'] = new Wt_Import_Export_For_Woo_History();
