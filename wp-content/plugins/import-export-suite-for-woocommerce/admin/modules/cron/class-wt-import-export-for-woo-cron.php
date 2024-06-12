<?php
/**
 * Handles the scheduled actions.
 *
 * @package   ImportExportSuite\Admin\Modules\Cron
 * @version   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_Cron Class.
 */
class Wt_Import_Export_For_Woo_Cron {

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
	 *  Module
	 *
	 * @var string
	 */
	public $module_base = 'cron';
	/**
	 * Actions modules
	 *
	 * @var array
	 */
	public $action_modules = array(
		'export' => 'export',
		'import' => 'import',
	);
	/**
	 * Status
	 *
	 * @var array
	 */
	public static $status_arr = array();
	/**
	 * Status label
	 *
	 * @var array
	 */
	public static $status_label_arr = array();
	/**
	 * Status color
	 *
	 * @var array
	 */
	public static $status_color_arr = array();
	/**
	 * Import or Export
	 *
	 * @var string
	 */
	public $to_cron = '';
	/**
	 * Cron salt
	 *
	 * @var string
	 */
	private $cron_url_salt = 'Dyeb(DjCr<}P2c#s';
	/**
	 * Export object
	 *
	 * @var object
	 */
	protected $export_obj = null;
	/**
	 * Status label
	 *
	 * @var array
	 */
	public $step_description = '';
	/**
	 * Url cron enable
	 *
	 * @var array
	 */
	public static $url_cron_enabled = false;
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->module_id        = Wt_Import_Export_For_Woo::get_module_id( $this->module_base );
		self::$module_id_static = $this->module_id;
		self::$status_arr       = array(
			'not_started' => 0,
			// not started yet.
				'finished'    => 1,
			// at least one completed.
				'disabled'    => 2,
			// disabled.
				'running'     => 3,
			// cron on running, eg: at least one batch completed.
				'uploading'   => 4,
			// uploading exported file.
				'downloading' => 5,
		// downloading the file to import.
		);
		self::$status_label_arr = array(
			0 => __( 'Not started', 'import-export-suite-for-woocommerce' ),
			1 => __( 'Finished', 'import-export-suite-for-woocommerce' ),
			2 => __( 'Disabled', 'import-export-suite-for-woocommerce' ),
			3 => __( 'Running', 'import-export-suite-for-woocommerce' ),
			4 => __( 'Uploading', 'import-export-suite-for-woocommerce' ),
			5 => __( 'Downloading', 'import-export-suite-for-woocommerce' ),
		);
		self::$status_color_arr = array(
			0 => '#337ab7',
			// dark blue.
				1 => '#5cb85c',
			// green.
				2 => '#f0ad4e',
			// orange.
				3 => '#5bc0de',
			// light blue.
				4 => '#5bc0de',
			// light blue.
				5 => '#5bc0de',
		// light blue.
		);
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ), 10, 1 );

		// altering footer buttons.
		add_filter( 'wt_iew_exporter_alter_footer_btns', array( $this, 'exporter_alter_footer_btns' ), 10, 3 );
		add_filter( 'wt_iew_importer_alter_footer_btns', array( $this, 'importer_alter_footer_btns' ), 10, 3 );

		// toggling the Export, Export/Schedule button based on `Export to` option.
		add_action( 'wt_iew_toggle_schedule_btn', array( $this, 'toggle_schedule_btn' ), 10, 1 );

		// hook for `schedule now` JS action.
		add_action( 'wt_iew_custom_action', array( $this, 'schedule_now' ) );

		// schedule now popup html.
		add_action( 'wt_iew_exporter_before_head', array( $this, 'schedule_now_popup_export' ) );
		add_action( 'wt_iew_importer_before_head', array( $this, 'schedule_now_popup_import' ) );

		// schedule main ajax hook.
		add_action( 'wp_ajax_iew_schedule_ajax', array( $this, 'ajax_main' ) );

		// add interval time for cron.
		add_filter( 'cron_schedules', array( $this, 'set_cron_interval' ) );

		// Hook cron based on action types.
		$this->prepare_cron();

		// hook for scheduling cron.
		add_action( 'init', array( $this, 'schedule_cron' ) );

		/*
		 * Hook for URL cron (Server cron).
		 */
		add_action( 'init', array( $this, 'do_url_cron' ) );

		// Admin menu for cron listing.
		add_filter( 'wt_iew_admin_menu', array( $this, 'add_admin_pages' ), 10, 1 );

		add_action( 'init', array( $this, 'test_cron' ) );
	}//end __construct()

	/**
	 *     Test cron
	 */
	public function test_cron() {
		if ( defined( 'WT_IEW_DEBUG' ) && WT_IEW_DEBUG ) {
			$action_type  = ( isset( $_GET['action_type'] ) ? sanitize_text_field( wp_unslash( $_GET['action_type'] ) ) : '' );
			$trigger_cron = ( isset( $_GET['wt_iew_test_cron'] ) ? absint( $_GET['wt_iew_test_cron'] ) : 0 );
			if ( ( 'import' == $action_type || 'export' == $action_type ) && 1 == $trigger_cron ) {
				$this->do_cron( $action_type );
				exit();
			}
		}
	}//end test_cron()


	/**
	 *     Main ajax hook for all ajax actions.
	 */
	public function ajax_main() {
		$out = array(
			'response' => false,
			'out'      => array(),
			'msg'      => __( 'Error' ),
		);
		$schedule_action = ( isset( $_REQUEST['iew_schedule_action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['iew_schedule_action'] ) ) : '' );

		if ( Wt_Iew_Sh::check_write_access( WT_IEW_PLUGIN_ID ) ) {
			$json_actions    = array(
				'save_schedule',
				'update_schedule',
				'edit_schedule',
			);
			$allowed_actions = array(
				'save_schedule',
				'list_cron',
				'update_schedule',
				'edit_schedule',
			);
			if ( method_exists( $this, $schedule_action ) && in_array( $schedule_action, $allowed_actions ) ) {
				$out = $this->{$schedule_action}( $out );
			}
		}

		if ( in_array( $schedule_action, $json_actions ) ) {
			echo json_encode( $out );
		}

		exit();
	}//end ajax_main()


	/**
	 * Adding admin menus
	 *
	 * @param array $menus Menus.
	 */
	public function add_admin_pages( $menus ) {
		$menus[ $this->module_base ] = array(
			'submenu',
			WT_IEW_PLUGIN_ID,
			__( 'Scheduled actions', 'import-export-suite-for-woocommerce' ),
			__( 'Scheduled actions', 'import-export-suite-for-woocommerce' ),
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
		return $menus;
	}//end add_admin_pages()


	/**
	 * List cron schedules
	 */
	public function list_cron() {
		global $wpdb;
		$tb        = $wpdb->prefix . Wt_Import_Export_For_Woo::$cron_tb;
		$cron_list = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wt_iew_cron ORDER BY id DESC", ARRAY_A ); // @codingStandardsIgnoreLine.
		$cron_list = ( $cron_list ? $cron_list : array() );
		include plugin_dir_path( __FILE__ ) . 'views/schedule-list.php';
	}//end list_cron()


	/**
	 *  Schedule list page
	 */
	public function admin_settings_page() {
		if ( isset( $_GET['wt_iew_change_schedule_status'] ) || isset( $_GET['wt_iew_delete_schedule'] ) ) {
			if ( Wt_Iew_Sh::check_write_access( WT_IEW_PLUGIN_ID ) ) {
				$cron_id = isset( $_GET['wt_iew_cron_id'] ) ? absint( $_GET['wt_iew_cron_id'] ) : 0;
				if ( $cron_id > 0 ) {
					$cron_data = self::get_cron_by_id( $cron_id );
					if ( $cron_data ) {
						if ( isset( $_GET['wt_iew_delete_schedule'] ) ) {
							// delete schedule action.
							// deleting history entries.
							$history_arr = ( '' != $cron_data['history_id_list'] ? maybe_unserialize( $cron_data['history_id_list'] ) : array() );
							$history_arr = ( is_array( $history_arr ) ? $history_arr : array() );
							if ( count( $history_arr ) > 0 ) {
								   $history_module_obj = Wt_Import_Export_For_Woo::load_modules( 'history' );
								if ( ! is_null( $history_module_obj ) ) {
									$history_module_obj->delete_history_by_id( $history_arr );
								}
							}
							self::delete_cron_by_id( $cron_id );
						} else {
							$action = sanitize_text_field( wp_unslash( $_GET['wt_iew_change_schedule_status'] ) );
							if ( 'enable' == $action ) {
								// checking its disabled.
								if ( $cron_data['status'] == self::$status_arr['disabled'] ) {
										  $update_data      = array(
											  'status' => absint( $cron_data['old_status'] ),
										  );
										  $update_data_type = array( '%d' );
										  self::update_cron( $cron_id, $update_data, $update_data_type );
								}
								// Checking it is already not disabled.
							} elseif ( $cron_data['status'] != self::$status_arr['disabled'] ) {
									   $update_data      = array(
										   'status'     => self::$status_arr['disabled'],
										   'old_status' => $cron_data['status'],
									   );
									   $update_data_type = array(
										   '%d',
										   '%d',
									   );
									   self::update_cron( $cron_id, $update_data, $update_data_type );
							}//end if
						}//end if
					}//end if
				}//end if
			}//end if
		}//end if

		include plugin_dir_path( __FILE__ ) . 'views/settings.php';
	}//end admin_settings_page()


	/**
	 *  Delete cron entry from DB.
	 *
	 * @param integer $id Cron ID.
	 */
	public static function delete_cron_by_id( $id ) {

		global $wpdb;
		$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$cron_tb;
		if ( is_array( $id ) ) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wt_iew_cron WHERE id IN(" . implode( ',', array_fill( 0, count( $id ), '%d' ) ) . ')', $id ) );
		} else {
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wt_iew_cron WHERE id=%d", $id ) );
		}
	}//end delete_cron_by_id()


	/**
	 * Schedule cron on action types.
	 */
	public function schedule_cron() {
		foreach ( $this->action_modules as $key => $value ) {
			if ( $this->is_cron_scheduled( $key ) ) {
				// cron exists.
				if ( ! wp_next_scheduled( 'wt_iew_do_cron_' . $key ) ) {
					$start_time = strtotime( 'now +1 minutes' );
					wp_schedule_event( $start_time, 'wt_iew_cron_interval', 'wt_iew_do_cron_' . $key );
				}
			} elseif ( wp_next_scheduled( 'wt_iew_do_cron_' . $key ) ) {
					// its already scheduled then remove.
					wp_clear_scheduled_hook( 'wt_iew_do_cron_' . $key );
			}
		}
	}//end schedule_cron()


	/**
	 * Hook cron on action types. Declare action for cron
	 */
	public function prepare_cron() {
		foreach ( $this->action_modules as $key => $value ) {
			if ( $this->is_cron_scheduled( $key ) ) {
				// cron exists.
				$method_name = 'do_cron_' . $key;
				if ( method_exists( $this, $method_name ) ) {
					// method exists.
					add_action( 'wt_iew_do_cron_' . $key, array( $this, $method_name ) );
				}
			}
		}
	}//end prepare_cron()


	/**
	 *    Initiate import cron
	 */
	public function do_cron_import() {
		$this->do_cron( 'import' );
	}//end do_cron_import()


	/**
	 *    Initiate export cron
	 */
	public function do_cron_export() {
		$this->do_cron( 'export' );
	}//end do_cron_export()


	/**
	 *  Registering new time interval for cron
	 *
	 * @param array $schedules Cron intervals.
	 */
	public function set_cron_interval( $schedules ) {
		if ( $this->is_cron_scheduled() ) {
			// cron exists.
			$schedules['wt_iew_cron_interval'] = array(
				'interval' => ( 5 ),
				// 5 second.
					'display'  => __( 'Every 5 second' ),
			);
		}

		return $schedules;
	}//end set_cron_interval()


	/**
	 * Checks any cron is available in the database
	 *
	 * @param string $action_type Cron action type.
	 */
	private function is_cron_scheduled( $action_type = '' ) {
		global $wpdb;
		$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$cron_tb;
		$status_check_arr = self::$status_arr;
		unset( $status_check_arr['disabled'] );

		$db_data_arr = array_values( $status_check_arr );
		$db_data_arr_int = $db_data_arr;
		$db_data_arr[]  = 'wordpress_cron';

		if ( '' != $action_type ) {
			$db_data_arr[] = $action_type;
			// taking count of available crons.
			$cron_count_arr = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(id) AS ttl FROM {$wpdb->prefix}wt_iew_cron WHERE status IN(" . implode( ', ', array_fill( 0, count( $db_data_arr_int ), '%d' ) ) . ') AND schedule_type=%s  AND action_type=%s', $db_data_arr ), ARRAY_A );// @codingStandardsIgnoreLine.

		} else {
			// taking count of available crons.
			$cron_count_arr = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(id) AS ttl FROM {$wpdb->prefix}wt_iew_cron WHERE status IN(" . implode( ', ', array_fill( 0, count( $db_data_arr_int ), '%d' ) ) . ') AND schedule_type=%s', $db_data_arr ), ARRAY_A );// @codingStandardsIgnoreLine.
		}

		$cron_count     = 0;
		if ( ! is_wp_error( $cron_count_arr ) ) {
			$cron_count = intval( isset( $cron_count_arr['ttl'] ) ? $cron_count_arr['ttl'] : 0 );
		}

		return $cron_count;
	}//end is_cron_scheduled()


	/**
	 * Popup HTML for export.
	 */
	public function schedule_now_popup_export() {
		$this->to_cron = 'export';
		$this->schedule_now_popup();
	}//end schedule_now_popup_export()


	/**
	 * Popup HTML for export.
	 */
	public function schedule_now_popup_import() {
		$this->to_cron = 'import';
		$this->schedule_now_popup();
	}//end schedule_now_popup_import()


	/**
	 * Popup HTML for schedule now.
	 */
	public function schedule_now_popup() {
		if ( isset( $_REQUEST['wt_iew_cron_edit_id'] ) && absint( $_REQUEST['wt_iew_cron_edit_id'] ) > 0 ) {
						$requested_cron_edit_id = absint( $_REQUEST['wt_iew_cron_edit_id'] );
						$cron_module_obj        = Wt_Import_Export_For_Woo::load_modules( 'cron' );
			if ( ! is_null( $cron_module_obj ) ) {
				$cron_data = $cron_module_obj->get_cron_by_id( $requested_cron_edit_id );
				$cron_data = maybe_unserialize( $cron_data['cron_data'] );
				include plugin_dir_path( __FILE__ ) . 'views/schedule-update.php';
			}

				wp_enqueue_script( $this->module_id . '_js', plugin_dir_url( __FILE__ ) . 'assets/js/main.js', array( 'jquery' ), WT_IEW_VERSION, false );
		} else {
			include plugin_dir_path( __FILE__ ) . 'views/schedule-now.php';
		}
	}//end schedule_now_popup()

	/**
	 * Enqueue assets.
	 */
	public function enqueue_assets() {
		if ( isset( $_GET['page'] ) ) {

			$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : null;
			wp_enqueue_script( $this->module_id, plugin_dir_url( __FILE__ ) . 'assets/js/cron.js', array( 'jquery' ), WT_IEW_VERSION, false );

			$params = array(
				'msgs'         => array(
					'invalid_date'            => __( 'Chosen date is invalid' ),
					'date_selected_info'      => __( 'You have selected 30 as the date but this date is not available in all months. In that case, last date of the month will be taken. Proceed?' ),
					'specify_file_name'       => __( 'Please specify a file name.' ),
					'saving'                  => __( 'Saving' ),
					'sure'                    => __( 'Are you sure?' ),
					'invalid_custom_interval' => __( 'Please enter a valid interval.' ),
					'invalid_time_hr'         => __( 'Please enter a valid time in hours(1-12).' ),
					'invalid_time_mnt'        => __( 'Please enter a valid time in minutes(0-60).' ),
					'use_url'                 => __( 'Use the generated URL to run cron.' ),
				),
				'timestamp'    => gmdate( 'Y M d h:i:s A' ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				'action_types' => array_keys( $this->action_modules ),
			);
			wp_localize_script( $this->module_id, 'wt_iew_cron_params', $params );

			if ( $_GET['page'] == $this->module_id || 'cron' == $current_tab ) {
				wp_enqueue_script( $this->module_id . '_js', plugin_dir_url( __FILE__ ) . 'assets/js/main.js', array( 'jquery' ), WT_IEW_VERSION, false );
			}
		}//end if
	}//end enqueue_assets()

	/**
	 * Checks any cron is available in the database.
	 *
	 * @param string $step_btns Cron action type.
	 * @param string $step Cron action type.
	 * @param string $steps Cron action type.
	 */
	public function importer_alter_footer_btns( $step_btns, $step, $steps ) {
		if ( 'advanced' !== $step ) {
			// last step.
			return $step_btns;
		}

		$out = array();
		foreach ( $step_btns as $step_btnk => $step_btnv ) {
			if ( 'download' == $step_btnk ) {
				// in import download is the primary step before import.
				$out['import_schedule'] = array(
					'key'   => 'import_schedule',
					'icon'  => '',
					'type'  => 'dropdown_button',
					'class' => 'iew_import_schedule_drp_btn',
					'text'  => __( 'Import/Schedule' ),
					'items' => array(
						$step_btnk => $step_btnv,
						'schedule' => array(
							'key'  => 'schedule_import',
							'text' => __( 'Schedule' ),
				// popups.
						),
					),
				);
			} else {
				$out[ $step_btnk ] = $step_btnv;
			}
		}//end foreach

		return $out;
	}//end importer_alter_footer_btns()


	/**
	 *    Filter callback for schedule now/Export now button toggle.
	 *
	 * @param string $step_btns Cron action type.
	 * @param string $step Cron action type.
	 * @param string $steps Cron action type.
	 */
	public function exporter_alter_footer_btns( $step_btns, $step, $steps ) {
		if ( 'advanced' !== $step ) {
			// last step.
			return $step_btns;
		}

		$out = array();
		foreach ( $step_btns as $step_btnk => $step_btnv ) {
			$out[ $step_btnk ] = $step_btnv;
			if ( 'export' == $step_btnk ) {
				$out['export_schedule'] = array(
					'key'   => 'export_schedule',
					'icon'  => '',
					'type'  => 'dropdown_button',
					'class' => 'iew_export_schedule_drp_btn',
					'text'  => __( 'Export/Schedule' ),
					'items' => array(
						$step_btnk => $step_btnv,
						'schedule' => array(
							'key'  => 'schedule_export',
							'text' => __( 'Schedule' ),
				// popups.
						),
					),
				);
			}
		}

		return $out;
	}//end exporter_alter_footer_btns()


	/**
	 *    Javascript callback for schedule now/Export now button toggle.
	 */
	public function toggle_schedule_btn() {
		?>
		wt_iew_cron.toggle_schedule_btn(state);
		<?php
	}//end toggle_schedule_btn()


	/**
	 *    Javascript callback for schedule now.
	 */
	public function schedule_now() {
		?>
		wt_iew_cron.schedule_now(ajx_dta, action, id);
		<?php
	}//end schedule_now()


	/**
	 *  Do the cron
	 *
	 * @param string $action_type Cron action type.
	 * @param string $cron_id Cron action type.
	 */
	public function do_cron( $action_type, $cron_id = 0 ) {
		global $wpdb;
		if ( '' == $action_type ) {
			return '';
		}

		// modules associated with action types.
		$action_modules = $this->action_modules;

		// checking corresponding module exists.
		if ( ! isset( $action_modules[ $action_type ] ) ) {
			return '';
		}

		// checking corresponding module available/active.
		if ( ! Wt_Import_Export_For_Woo_Admin::module_exists( $action_modules[ $action_type ] ) ) {
			return;
		}

				/*
				 * @since 1.1.2
				 * To control the cron run
				 */
				$args = array(
					'action_type' => $action_type,
					'cron_id'     => $cron_id,
				);
				/**
				 * Cron enabled check.
				 *
				 * Enables adding extra arguments or setting defaults for the request.
				 *
				 * @since 1.0.0
				 *
				 * @param boolean   $run_cron    Cron enabled flag.
				 * @param array   $args    Cron arguments.
				 */
				if ( ! apply_filters( 'wt_iew_run_cron', true, $args ) ) {
					return;
				}

				$tb          = $wpdb->prefix . Wt_Import_Export_For_Woo::$cron_tb;
				$tme         = time();
				$is_parallel = 0;
				// allow parallel cron on single request.
				$limit_sql = ( 0 == $is_parallel ) ? ' LIMIT 1' : '';

				/*
				 *    taking cron details from db.
				 *    Takes all data that have status running
				 *    Takes data that have status not started/finshed will take based on the startime
				 *    If id given then take that record only with above condition
				 */
				if ( 0 == $cron_id ) {
					$cron_list = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT * FROM {$wpdb->prefix}wt_iew_cron WHERE ( ( (status= %d OR  status= %d) AND start_time <= %d ) OR status IN(%d, %d, %d) ) AND action_type=%s AND schedule_type=%s ORDER BY start_time ASC LIMIT 1", // @codingStandardsIgnoreLine.
							array(
								self::$status_arr['not_started'],
								self::$status_arr['finished'],
								$tme,
								self::$status_arr['running'],
								self::$status_arr['uploading'],
								self::$status_arr['downloading'],
								$action_type,
								'wordpress_cron',
							)
						),
						ARRAY_A
					);
				} else // cron id exists.
				{
					$cron_list = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT * FROM {$wpdb->prefix}wt_iew_cron WHERE ( ( (status= %d OR  status= %d) AND start_time <= %d ) OR status IN(%d, %d, %d) ) AND action_type=%s AND id=%d", // @codingStandardsIgnoreLine.
							array(
								self::$status_arr['not_started'],
								self::$status_arr['finished'],
								$tme,
								self::$status_arr['running'],
								self::$status_arr['uploading'],
								self::$status_arr['downloading'],
								$action_type,
								$cron_id,
							)
						),
						ARRAY_A
					);
				}//end if

				// taking list of available crons.

				// if cron found.
				if ( $cron_list ) {
					$action_module = Wt_Import_Export_For_Woo::load_modules( $action_modules[ $action_type ] );

					if ( ! defined( 'WT_IEW_CRON' ) ) {
						// cron is running, this is used in log module to add prefix to identify cron log.
						define( 'WT_IEW_CRON', true );
					}

					foreach ( $cron_list as $cron_listv ) {
						if ( defined( 'WT_IEW_DEBUG' ) && WT_IEW_DEBUG ) {
							// debug.
							echo '<pre>';
							  print_r( $cron_listv );
							  echo '</pre><br />';
						}

						if ( $cron_listv['history_id'] > 0 ) {
							// no need to send formdata. It will take from history table by `process_action` method.
							$form_data = array();
						} else {
							$form_data = maybe_unserialize( $cron_listv['data'] );
						}

						$cron_data = maybe_unserialize( $cron_listv['cron_data'] );
						$file_name = ( isset( $cron_data['file_name'] ) ? $cron_data['file_name'] : '' );

						if ( $cron_listv['status'] == self::$status_arr['finished'] || $cron_listv['status'] == self::$status_arr['not_started'] ) {
							if ( 'import' == $cron_listv['action_type'] ) {
								$out = $action_module->process_download( $form_data, 'download', $cron_listv['item_type'], $cron_listv['history_id'], $cron_listv['next_offset'] );
							} else {
								$out = $action_module->process_action( $form_data, $action_type, $cron_listv['item_type'], $file_name, $cron_listv['history_id'], $cron_listv['next_offset'] );
							}
						} else if ( $cron_listv['status'] == self::$status_arr['running'] ) {
										   $out = $action_module->process_action( $form_data, $action_type, $cron_listv['item_type'], $file_name, $cron_listv['history_id'], $cron_listv['next_offset'] );
						} else if ( $cron_listv['status'] == self::$status_arr['uploading'] ) {
							$out = $action_module->process_upload( 'upload', $cron_listv['history_id'], $cron_listv['item_type'] );
						} else if ( $cron_listv['status'] == self::$status_arr['downloading'] ) {
							$out = $action_module->process_download( $form_data, $action_type, $cron_listv['item_type'], $cron_listv['history_id'], $cron_listv['next_offset'] );
						}//end if

						/*
						 *     Prepare for next run
						 */
						$update_data      = array(
							'last_run'   => time(),
							'history_id' => $out['history_id'],
						);
						$update_data_type = array(
							'%d',
							'%d',
							'%d',
							'%d',
						);

						if ( false === $out['response'] ) {
							// An error. Skip this cron and prepare for next run.
							$this->prepare_for_next_run( $update_data, $update_data_type, $cron_listv, $out );
						} else {
							if ( isset( $out['finished'] ) && 1 == $out['finished'] ) {

								/**
								 * Finished the export/import batching.
								 *
								 * Enables adding extra arguments or setting defaults for the request.
								 *
								 * @since 1.0.0
								 *
								 * @param array   $out    Cron output.
								 */
								do_action( 'wt_ier_scheduled_action_finished', $out );
								$this->prepare_for_next_run( $update_data, $update_data_type, $cron_listv, $out );
							} else if ( isset( $out['finished'] ) && 2 == $out['finished'] ) {
								// finshed the export, now need uploading.
								// update the status and reset the offset.
								$update_data['status'] = self::$status_arr['uploading'];
								// upload the exported data.
								$update_data['next_offset'] = 0;
								// reset the offset.
							} else if ( isset( $out['finished'] ) && 3 == $out['finished'] ) {
								// starting the import, file to download and processing was done.
								// update the status and reset the offset.
								$update_data['status'] = self::$status_arr['running'];
								// do import.
								$update_data['next_offset'] = 0;
								// reset the offset.
							} else // not finished, more batches are pending.
							{
								if ( 'export' == $cron_listv['action_type'] ) {
									$new_status = self::$status_arr['running'];
								} elseif ( $cron_listv['status'] == self::$status_arr['running'] ) {
										$new_status = self::$status_arr['running'];
										// continue import.
								} else {
									$new_status = self::$status_arr['downloading'];
									// continue download.

								}

																				// update the status and reset the offset.
																				$update_data['status'] = $new_status;
																				// waiting for next batch.
																				$update_data['next_offset'] = $out['new_offset'];
																				// save the next offset.
							}//end if

							// first execution, then update the ID in history id list.
							if ( 0 == $cron_listv['history_id'] ) {
								$history_id_list   = ( '' != $cron_listv['history_id_list'] ? maybe_unserialize( $cron_listv['history_id_list'] ) : array() );
								$history_id_list   = ( ! is_array( $history_id_list ) ? array() : $history_id_list );
								$history_id_list[] = $out['history_id'];
								// history id from import/export module.
								$update_data['history_id_list'] = maybe_serialize( $history_id_list );
								$update_data_type[] = '%s';
							}
						}//end if

						if ( defined( 'WT_IEW_DEBUG' ) && WT_IEW_DEBUG ) {
							// debug.
							echo '<pre>';
							print_r( $out );
							echo '</pre><br />';
						}

						/*
						 *     Update cron DB entry
						 */
						$this->update_cron( $cron_listv['id'], $update_data, $update_data_type );
					}//end foreach
				}//end if
	}//end do_cron()


	/**
	 *    Prepare for next cron (Not batch).
	 *
	 * @param array $update_data       data to be updated in cron table.
	 * @param array $update_data_type  for data be updated in cron table.
	 * @param array $cron_listv        cron DB record.
	 * @param array $action_module_out output from action module Eg: export response from export module.
	 */
	private function prepare_for_next_run( &$update_data, &$update_data_type, $cron_listv, $action_module_out ) {
		// update the status and reset the offset.
		$update_data['status'] = self::$status_arr['finished'];
		// waiting for next run.
		$update_data['next_offset'] = 0;
		// reset the offset.
		// add next start time based on interval type.
		$cron_data       = maybe_unserialize( $cron_listv['cron_data'] );
		$prev_start_time = $cron_listv['start_time'];
		$update_data['start_time'] = self::prepare_start_time( $cron_data, $prev_start_time );
		$update_data_type[]        = '%d';

		$update_data['history_id'] = 0;
		// resetting the hostory id, Otherwise next cron will use same history entry.
		// clear formdata from history table to avoid data duplication.
		$history_update_data      = array( 'data' => '' );
		$history_update_data_type = array( '%s' );
		Wt_Import_Export_For_Woo_History::update_history_entry( $action_module_out['history_id'], $history_update_data, $history_update_data_type );
	}//end prepare_for_next_run()

	/**
	 * Get cron by id
	 *
	 * @param array $cron_id id of cron.
	 *
	 * @since 1.0.0
	 * @return integer
	 */
	public static function get_cron_by_id( $cron_id ) {
		global $wpdb;
		$tb  = $wpdb->prefix . Wt_Import_Export_For_Woo::$cron_tb;

		// taking cron data.
		$cron_arr = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_cron WHERE id=%d", array( $cron_id ) ), ARRAY_A );// @codingStandardsIgnoreLine.
		if ( ! is_wp_error( $cron_arr ) ) {
			return $cron_arr;
		} else {
			return false;
		}
	}//end get_cron_by_id()


	/**
	 *    Update the cron data when running.
	 *
	 * @param array $cron_id cron id.
	 * @param array $update_data form data.
	 * @param array $update_data_type update type.
	 * @since 1.0.0
	 * @return bool
	 */
	public static function update_cron( $cron_id, $update_data, $update_data_type ) {
		global $wpdb;
		$tb           = $wpdb->prefix . Wt_Import_Export_For_Woo::$cron_tb;
		$update_where = array( 'id' => $cron_id );
		$update_where_type = array( '%d' );
		if ( $wpdb->update( $tb, $update_data, $update_where, $update_data_type, $update_where_type ) !== false ) {
			return true;
		}

		return false;
	}//end update_cron()


	/**
	 *     Prepare start time timestamp.
	 *
	 * @param array $cron_data process result.
	 * @param array $last_start_time process result.
	 * @since 1.0.0
	 * @return array
	 */
	private static function prepare_start_time( $cron_data, $last_start_time = 0 ) {
		$time_vl = $cron_data['start_time'];
		$tme     = time();
		// $m=date('n');.
		$mon = gmdate( 'M' ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		$y = gmdate( 'Y' ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		$d = gmdate( 'd' ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		// $t=gmdate('t');.
		$out = 0;
		if ( 'month' == $cron_data['interval'] ) {
			if ( 'last_day' == $cron_data['date_vl'] ) {
				$time_stamp = strtotime( "$time_vl Last day of +0 Month" );
				if ( $time_stamp < $tme ) {
					$out = strtotime( "$time_vl Last day of +1 Month" );
				} else {
					$out = $time_stamp;
				}
			} else {
				$date_vl    = $cron_data['date_vl'];
				$time_stamp = strtotime( "$time_vl $y-$mon-$date_vl" );
				if ( $time_stamp < $tme ) {
					$out = strtotime( '+1 Month', $time_stamp );
				} else {
					$out = $time_stamp;
				}
			}//end if
		} else if ( 'week' == $cron_data['interval'] ) {
			$day_vl     = $cron_data['day_vl'];
			$time_stamp = strtotime( "This week $day_vl $time_vl" );
			if ( $time_stamp < $tme ) {
				$out = strtotime( "Next week $day_vl $time_vl" );
			} else {
				$out = $time_stamp;
			}
		} else if ( 'day' == $cron_data['interval'] ) {
			$time_stamp = strtotime( $time_vl );
			if ( $time_stamp < $tme ) {
				$out = strtotime( "+1 day $time_vl" );
			} else {
				$out = $time_stamp;
			}
		} else {
			$custom_interval = $cron_data['custom_interval'];
			// in minutes.
			$custom_interval_sec = ( $custom_interval * 60 );
			// in seconds.
			if ( 0 == $last_start_time ) {
				// first time.
				$time_stamp = strtotime( $time_vl );
				if ( $time_stamp < $tme ) {
									$out = strtotime( "+1 day $time_vl" );
				} else {
					$out = $time_stamp;
				}
			} else {
				$next_start_time = ( $last_start_time + $custom_interval_sec );
				if ( $next_start_time < $tme ) {
					$interval_diff = ( $tme - $next_start_time );
					$out           = ( $next_start_time + ( ( ceil( $interval_diff / $custom_interval_sec ) - 1 ) * $custom_interval_sec ) );
				} else {
					$out = $next_start_time;
				}
			}//end if
		}//end if

		return $out;
	}//end prepare_start_time()


	/**
	 *  Save the cron data
	 *
	 * @param array $out process result.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function save_schedule( $out ) {
		global $wpdb;
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}

		$cron_data = ( isset( $_POST['schedule_data'] ) ? map_deep( wp_unslash( $_POST['schedule_data'] ), 'sanitize_text_field' ) : null ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! $cron_data ) {
			return $out;
		}

		// sanitize the file name.
		$cron_data['file_name'] = ( isset( $cron_data['file_name'] ) ? sanitize_file_name( $cron_data['file_name'] ) : '' );

		$tb         = $wpdb->prefix . Wt_Import_Export_For_Woo::$cron_tb;
		$start_time = self::prepare_start_time( $cron_data );
		if ( 0 == $start_time ) {
			return $out;
		}

		$item_type   = isset( $_POST['item_type'] ) ? sanitize_text_field( wp_unslash( $_POST['item_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already handled in main function
		$action_type = isset( $_POST['schedule_action'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule_action'] ) ) : '';// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already handled in main function

		if ( ! isset( $this->action_modules[ $action_type ] ) ) {
			// not in the allowed action list.
			return $out;
		}

		// process form data.
		$form_data = ( isset( $_POST['form_data'] ) ? Wt_Import_Export_For_Woo_Common_Helper::process_formdata( maybe_unserialize( map_deep( wp_unslash( $_POST['form_data'] ), 'sanitize_text_field' ) ) ) : array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// loading export module class object.
		$this->module_obj = Wt_Import_Export_For_Woo::load_modules( $action_type );

		if ( ! is_null( $this->module_obj ) ) {
			// sanitize form data.
			$form_data = Wt_Iew_IE_Helper::sanitize_formdata( $form_data, $this->module_obj );
		}

		$insert_data      = array(
			'action_type'   => $action_type,
			'item_type'     => $item_type,
			'schedule_type' => $cron_data['schedule_type'],
			'data'          => maybe_serialize( $form_data ),
			'start_time'    => $start_time,
			// next cron start time.
				'cron_data'     => maybe_serialize( $cron_data ),
			// cron settings data Eg: Cron interval type.
				'last_run'      => 0,
			// first time, not started yet.
				'history_id'    => 0,
			// first time, not started yet, it will added on first run.
				'status'        => self::$status_arr['not_started'],
			// not started yet status.
				'next_offset'   => 0,
		);
		$insert_data_type = array(
			'%s',
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
			'%d',
			'%d',
			'%d',
			'%d',
		);

		if ( $wpdb->insert( $tb, $insert_data, $insert_data_type ) ) {
			// success.
			$cron_id = $wpdb->insert_id;
			$out     = array(
				'response' => true,
				'out'      => array(),
				'msg'      => __( 'Success' ),
			);
			if ( 'server_cron' == $cron_data['schedule_type'] ) {
				$out['cron_url'] = $this->generate_cron_url( $cron_id, $action_type, $item_type );
			}
		}

		return $out;
	}//end save_schedule()


	/**
	 *  Edit the cron data
	 *
	 * @param array $out process result.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function edit_schedule( $out ) {

		global $wpdb;
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}

		$cron_id = ( isset( $_POST['cron_id'] ) ? intval( wp_unslash( $_POST['cron_id'] ) ) : null );
		if ( ! $cron_id ) {
			return $out;
		}

		$cron_details = self::get_cron_by_id( $cron_id );
		if ( $cron_details ) {
			$cron_form_data = maybe_unserialize( $cron_details['data'] );
			// cron settings data Eg: Cron interval type.
			$advanced_form_data = $cron_form_data['advanced_form_data'];
			$action_type        = $cron_details['action_type'];
			$method_action_type_form_data_holder = "method_{$action_type}_form_data";
			$method_action_type_form_data        = $cron_form_data[ $method_action_type_form_data_holder ];
			$update_data = array(
				'id'                              => $cron_details['id'],
				'action_type'                     => $action_type,
				'item_type'                       => $cron_details['item_type'],
				'schedule_type'                   => $cron_details['schedule_type'],
				'cron_data'                       => maybe_unserialize( $cron_details['cron_data'] ),
				"method_{$action_type}_form_data" => $method_action_type_form_data,
				'advanced_form_data'              => $advanced_form_data,
			);

			$step_info = array(
				'title'       => ' ',
				'description' => ' ',
			);

			$action_type_base_holder = ucfirst( $action_type );
			$action_type_base_class  = "Wt_Import_Export_For_Woo_{$action_type_base_holder}";
			$action_type_base_object = new $action_type_base_class();

			if ( is_object( $action_type_base_object ) ) {
				if ( 'export' == $action_type ) {
					$action_type_base_object->to_export = $cron_details['item_type'];
				} else {
					$action_type_base_object->to_import = $cron_details['item_type'];
				}

				$advanced_screen_fields = $action_type_base_object->get_advanced_screen_fields( $advanced_form_data );

				ob_start();
				include_once dirname( plugin_dir_path( __FILE__ ) ) . "/{$action_type}/views/_{$action_type}_advanced_page.php";
				$advanced_form_edit = ob_get_clean();
				$out['advanced_form_edit_html'] = $advanced_form_edit;
				$out['data'] = $update_data;
			}
		}//end if

		return $out;
	}//end edit_schedule()


	/**
	 *  Update the cron data.
	 *
	 * @param array $out process result.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function update_schedule( $out ) {

		global $wpdb;
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		$cron_data = ( isset( $_POST['schedule_data'] ) ? map_deep( wp_unslash( $_POST['schedule_data'] ), 'sanitize_text_field' ) : null );
		if ( ! $cron_data ) {
			return $out;
		}

		$cron_id      = isset( $_POST['cron_id'] ) ? absint( $_POST['cron_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$cron_details = self::get_cron_by_id( $cron_id );
		if ( ! $cron_details ) {
			$out = array(
				'msg' => __( 'Couldn\'t find selected schedule.' ),
			);
			return $out;
		}

		// sanitize the file name.
		$cron_data['file_name'] = ( isset( $cron_data['file_name'] ) ? sanitize_file_name( $cron_data['file_name'] ) : '' );

		$tb         = $wpdb->prefix . Wt_Import_Export_For_Woo::$cron_tb;
		$start_time = self::prepare_start_time( $cron_data );

		if ( 0 == $start_time ) {
			return $out;
		}

		$item_type   = sanitize_text_field( wp_unslash( $cron_details['item_type'] ) );
		$action_type = sanitize_text_field( wp_unslash( $cron_details['action_type'] ) );

		if ( ! isset( $this->action_modules[ $action_type ] ) ) {
			// not in the allowed action list.
			return $out;
		}

		$cron_form_details = maybe_unserialize( $cron_details['data'] );

		// process form data.
		$form_data = ( isset( $_POST['form_data'] ) ? Wt_Import_Export_For_Woo_Common_Helper::process_formdata( maybe_unserialize( map_deep( wp_unslash( $_POST['form_data'] ), 'sanitize_text_field' ) ) ) : array() );

		// loading export module class object.
		$this->module_obj = Wt_Import_Export_For_Woo::load_modules( $action_type );

		if ( ! is_null( $this->module_obj ) ) {
			// sanitize form data.
			$form_data = Wt_Iew_IE_Helper::sanitize_formdata( $form_data, $this->module_obj );
		}

		if ( 'export' == $action_type ) {
			$method_from_data = $cron_form_details['method_export_form_data'];
			$form_data['method_export_form_data'] = $method_from_data;
		} else {
			$method_from_data = $cron_form_details['method_import_form_data'];
			$form_data['method_import_form_data'] = $method_from_data;
		}

		$update_data = array(
			'id'            => $cron_id,
			'schedule_type' => $cron_data['schedule_type'],
			'data'          => maybe_serialize( $form_data ),
			'start_time'    => $start_time,
			// next cron start time.
			'cron_data'     => maybe_serialize( $cron_data ),
		);

		$out = array(
			'response' => true,
			'out'      => array(),
			'msg'      => __( 'Schedule updated successfully' ),
		);
		if ( $wpdb->update( $tb, $update_data, array( 'id' => $cron_id ) ) ) {
			// success.
			if ( 'server_cron' == $cron_data['schedule_type'] ) {
				$out['cron_url'] = $this->generate_cron_url( $cron_id, $action_type, $item_type );
			}
		}

		return $out;
	}//end update_schedule()

	/**
	 *    Do URL cron.
	 *
	 * @since 1.0.0
	 */
	public function do_url_cron() {
		if ( isset( $_GET['wt_iew_url_cron'] ) ) {
			$cron_id     = absint( $_GET['wt_iew_url_cron'] );
			$action_type = ( isset( $_GET['a'] ) ? sanitize_text_field( wp_unslash( $_GET['a'] ) ) : '' );
			$item_type   = ( isset( $_GET['i'] ) ? sanitize_text_field( wp_unslash( $_GET['i'] ) ) : '' );
			$hash        = ( isset( $_GET['h'] ) ? sanitize_text_field( wp_unslash( $_GET['h'] ) ) : '' );
			$tme         = ( isset( $_GET['t'] ) ? absint( $_GET['t'] ) : '' );

			if ( $cron_id > 0 && '' != $action_type && '' != $item_type && '' != $hash && $tme > 0 ) {
				// check the hash is matching.
				$expected_hash = $this->generate_hash_for_url( $cron_id, $tme, $action_type );
				if ( $expected_hash == $hash ) {
					global $wpdb;
					$tb          = $wpdb->prefix . Wt_Import_Export_For_Woo::$cron_tb;
					$db_data_arr = array(
						self::$status_arr['disabled'],
						$cron_id,
						$action_type,
						$item_type,
					);
					// checking cron exists.
					$cron_count_arr = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}wt_iew_cron  WHERE status!=%d AND id=%d AND action_type=%s AND item_type=%s", $db_data_arr ), ARRAY_A );// @codingStandardsIgnoreLine.
					if ( ! is_wp_error( $cron_count_arr ) ) {
						self::$url_cron_enabled = true;
						$this->do_cron( $action_type, $cron_id );
					}
				}
			}//end if

			exit();
		}//end if
	}//end do_url_cron()


	/**
	 * Generate hash for URL cron.
	 *
	 * @param integer $id   cron id.
	 * @param string  $tme  time .
	 * @param string  $action_type  action type.
	 *
	 * @since 1.0.0
	 * @return string md5hash
	 */
	private function generate_hash_for_url( $id, $tme, $action_type ) {
		return md5( $tme . '_' . $this->cron_url_salt . '-' . $id . $action_type );
	}//end generate_hash_for_url()


	/**
	 *    Generate URL for URL cron.
	 *
	 * @param integer $id   cron id.
	 * @param string  $action_type  action type.
	 * @param sring   $item_type  item type.
	 *
	 * @since 1.0.0
	 * @return string URL
	 */
	private function generate_cron_url( $id, $action_type, $item_type ) {
		$tme  = time();
		$hash = $this->generate_hash_for_url( $id, $tme, $action_type );
		return site_url( '?wt_iew_url_cron=' . $id . '&a=' . $action_type . '&i=' . $item_type . '&h=' . $hash . '&t=' . $tme );
	}//end generate_cron_url()
}//end class

Wt_Import_Export_For_Woo::$loaded_modules['cron'] = new Wt_Import_Export_For_Woo_Cron();
