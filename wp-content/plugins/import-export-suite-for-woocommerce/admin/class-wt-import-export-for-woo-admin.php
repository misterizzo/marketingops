<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.webtoffee.com/
 * @since      1.0.0
 *
 * @package    ImportExportSuite\Admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */
class Wt_Import_Export_For_Woo_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Module list, Module folder and main file must be same as that of module name
	 * Please check the `register_modules` method for more details
	 *
	 * @var      array    $modules    The current version of this plugin.
	 */
	public static $modules = array(
		'history',
		'export',
		'import',
		'ftp',
		'cron',
	);
	/**
	 * Module id - coupon
	 *
	 * @var string
	 */
	public static $existing_modules = array();
	/**
	 * Module id - coupon
	 *
	 * @var string
	 */
	public static $addon_modules = array(
		'product',
		'order',
		'product-review',
		'user',
		'coupon',
		'subscription',
	);

		/**
		 * WebToffee data identifier, this variable used for identify that the data is belongs to WebToffee Import/Export.
		 * Use1: used in evaluation operators prefix.
		 * Use2: We can use this for identify WebToffee operations (@[]/+-*) etc
		 * !!!important: Do not change this value frequently
		 *
		 * @var string
		 */
	public static $wt_iew_prefix = 'wt_iew';

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 * @param      string $plugin_name       The name of this plugin.
		 * @param      string $version    The version of this plugin.
		 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		if ( Wt_Import_Export_For_Woo_Common_Helper::wt_is_screen_allowed() ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wt-import-export-for-woo-admin.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if ( Wt_Import_Export_For_Woo_Common_Helper::wt_is_screen_allowed() ) {
			/* enqueue scripts */
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wt-import-export-for-woo-admin.js', array( 'jquery', 'jquery-tiptip' ), $this->version, false );
			} else {
				wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wt-import-export-for-woo-admin.js', array( 'jquery' ), $this->version, false );
				wp_enqueue_script( WT_IEW_PLUGIN_ID . '-tiptip', WT_IEW_PLUGIN_URL . 'admin/js/tiptip.js', array( 'jquery' ), WT_IEW_VERSION, false );
			}

			$params = array(
				'nonces' => array(
					'main' => wp_create_nonce( WT_IEW_PLUGIN_ID ),
				),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'plugin_id' => WT_IEW_PLUGIN_ID,
				'msgs' => array(
					'settings_success' => __( 'Settings updated', 'import-export-suite-for-woocommerce' ),
					'all_fields_mandatory' => __( 'All fields are mandatory', 'import-export-suite-for-woocommerce' ),
					'settings_error' => __( 'Unable to update settings', 'import-export-suite-for-woocommerce' ),
					'template_del_error' => __( 'Unable to delete template', 'import-export-suite-for-woocommerce' ),
					'template_del_loader' => __( 'Deleting template...', 'import-export-suite-for-woocommerce' ),
					'value_empty' => __( 'Value is empty', 'import-export-suite-for-woocommerce' ),
					/* translators: 1: HTML a open. 2: HTML a close */
					'error' => sprintf( __( 'An unknown error has occurred! Refer to our %1$stroubleshooting guide%2$s for assistance. You may also try increasing <b>maximum execution time</b> in advanced %3$ssettings%4$s.', 'import-export-suite-for-woocommerce' ), '<a href="' . WT_IEW_DEBUG_PRO_TROUBLESHOOT . '" target="_blank">', '</a>', '<a href="' . admin_url( 'admin.php?page=' . WT_IEW_PLUGIN_ID . '&tab=settings' ) . '" target="blank">', '</a>' ),
					'success' => __( 'Success', 'import-export-suite-for-woocommerce' ),
					'loading' => __( 'Loading...', 'import-export-suite-for-woocommerce' ),
					'sure' => __( 'Are you sure?', 'import-export-suite-for-woocommerce' ),
					'use_expression' => __( 'Use expression as value', 'import-export-suite-for-woocommerce' ),
					'cancel' => __( 'Cancel', 'import-export-suite-for-woocommerce' ),
				),
				'pro_plugins' => array(
					'order' => array(
						'sample_csv_url' => 'https://www.webtoffee.com/wp-content/uploads/2021/03/Order_SampleCSV.csv',
					),
					'coupon' => array(
						'sample_csv_url' => 'https://www.webtoffee.com/wp-content/uploads/2016/09/Coupon_Sample_CSV.csv',
					),
					'product' => array(
						'sample_csv_url' => 'https://www.webtoffee.com/wp-content/uploads/2021/04/Product_SampleCSV-.csv',
					),
					'product_review' => array(
						'sample_csv_url' => 'https://www.webtoffee.com/wp-content/uploads/2021/04/product_review_SampleCSV.csv',
					),
					'user' => array(
						'sample_csv_url' => 'https://www.webtoffee.com/wp-content/uploads/2020/10/Sample_Users.csv',
					),
					'subscription' => array(
						'sample_csv_url' => 'https://www.webtoffee.com/wp-content/uploads/2021/04/Subscription_Sample_CSV.csv',
					),
				),
			);
			wp_localize_script( $this->plugin_name, 'wt_iew_params', $params );
		}
	}

	/**
	 * Registers menu options
	 * Hooked into admin_menu
	 *
	 * @since    1.0.0
	 */
	public function admin_menu() {

		$menus = array(
			'general-settings' => array(
				'menu',
				__( 'General Settings' ),
				__( 'General Settings' ),
				/**
				* Filter the query arguments for a request.
				*
				* Enables adding extra arguments or setting defaults for a post
				* collection request.
		 *
		 * @since 1.0.0
		 *
				* @param string          $capability    Allowed capability.
				*/
					   apply_filters( 'wt_import_export_allowed_capability', 'import' ),
				WT_IEW_PLUGIN_ID,
				array( $this, 'admin_settings_page' ),
				'dashicons-controls-repeat',
				56,
			),
		);
				/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a post
		 * collection request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $menus    Menus.
		 */
		$menus = apply_filters( 'wt_iew_admin_menu', $menus );

			$menu_order = array( 'export', 'export-sub', 'import', 'history', 'history_log', 'cron' );
			$this->wt_menu_order_changer( $menus, $menu_order );

		$main_menu = reset( $menus ); // main menu must be first one.

		$parent_menu_key = $main_menu ? $main_menu[4] : WT_IEW_PLUGIN_ID;

		/* adding general settings menu */
		$menus['general-settings-sub'] = array(
			'submenu',
			$parent_menu_key,
			__( 'General Settings' ),
			__( 'General Settings' ),
			/**
			* Filter the query arguments for a request.
			*
			* Enables adding extra arguments or setting defaults for a post
			* collection request.
		 *
		 * @since 1.0.0
		 *
			* @param string          $capability    Allowed capability.
			*/
			   apply_filters( 'wt_import_export_allowed_capability', 'import' ),
			WT_IEW_PLUGIN_ID,
			array( $this, 'admin_settings_page' ),
		);
		if ( count( $menus ) > 0 ) {

						$i = 0;
			foreach ( $menus as $menu ) {
				if ( 'submenu' == $menu[0] && 1 == $i ) {
					/* currently we are only allowing one parent menu */
					add_submenu_page( 'woocommerce', $menu[2], 'Import Export Suite', $menu[4], 'wt_import_export_for_woo', $menu[6] );
				}
								$i++;
			}
		}
	}

	/**
	 * Menu order adjust
	 *
	 * @param array $arr Menus.
	 * @param array $index_arr Index.
	 */
	public function wt_menu_order_changer( &$arr, $index_arr ) {
		$arr_t = array();
		foreach ( $index_arr as $i => $v ) {
			foreach ( $arr as $k => $b ) {
				if ( $k == $v ) {
					$arr_t[ $k ] = $b;
				}
			}
		}
		$arr = $arr_t;
	}

	/**
	 * Admin settings page
	 */
	public function admin_settings_page() {
		include plugin_dir_path( __FILE__ ) . 'partials/wt-import-export-for-woo-admin-display.php';
	}

	/**
	 *   Save admin settings and module settings ajax hook
	 */
	public function save_settings() {
		$out = array(
			'status' => false,
			'msg' => __( 'Error' ),
		);
		if ( Wt_Iew_Sh::check_write_access( WT_IEW_PLUGIN_ID ) ) {
			// Nonce already verified on main function - sanitization thorugh helper functions.
			$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
			if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return false;
			}
			$advanced_settings = Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings();
			$advanced_fields = Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings_fields();
			$validation_rule = Wt_Import_Export_For_Woo_Common_Helper::extract_validation_rules( $advanced_fields );
			$new_advanced_settings = array();
			foreach ( $advanced_fields as $key => $value ) {
				$form_field_name = isset( $value['field_name'] ) ? $value['field_name'] : '';
				$field_name = ( substr( $form_field_name, 0, 8 ) !== 'wt_iew_' ? 'wt_iew_' : '' ) . $form_field_name;
				$validation_key = str_replace( 'wt_iew_', '', $field_name );
				if ( isset( $_POST[ $field_name ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					$new_advanced_settings[ $field_name ] = sanitize_text_field( wp_unslash( $_POST[ $field_name ] ) );
					// $new_advanced_settings[ $field_name ] = Wt_Iew_Sh::sanitize_data( wp_unslash( $_POST[ $field_name ] ), $validation_key, $validation_rule );// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				}
			}
			Wt_Import_Export_For_Woo_Common_Helper::set_advanced_settings( $new_advanced_settings );
			$out['status'] = true;
			$out['msg'] = __( 'Settings Updated' );
									/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a post
		 * collection request.
		 *
		 * @since 1.0.0
		 *
		 * @param array          $new_advanced_settings    Advanced settings options.
		 */
			do_action( 'wt_iew_after_advanced_setting_update', $new_advanced_settings );
		}
		echo json_encode( $out );
		exit();
	}

		/**
		 *   Delete pre-saved temaplates entry from DB - ajax hook
		 */
	public function delete_template() {
		$out = array(
			'status' => false,
			'msg' => __( 'Error' ),
		);
		if ( Wt_Iew_Sh::check_write_access( WT_IEW_PLUGIN_ID ) ) {
			// Nonce already verified on main function - sanitization thorugh helper functions.
			$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
			if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
			}
			if ( isset( $_POST['template_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

				global $wpdb;
				$template_id = absint( $_POST['template_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$template_tb;
				$where = '=%d';
				$where_data = array( $template_id );
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wt_iew_mapping_template WHERE id =%d", $where_data ) ); // @codingStandardsIgnoreLine
				$out['status'] = true;
				$out['msg'] = __( 'Template deleted successfully', 'import-export-suite-for-woocommerce' );
				$out['template_id'] = $template_id;
			}
		}
		wp_send_json( $out );
	}

		/**
	 Registers modules: admin
		 */
	public function admin_modules() {
		$wt_iew_admin_modules = get_option( 'wt_iew_admin_modules' );
		if ( false === $wt_iew_admin_modules ) {
			$wt_iew_admin_modules = array();
		}
		foreach ( self::$modules as $module ) {
			$is_active = 1;
			if ( isset( $wt_iew_admin_modules[ $module ] ) ) {
				$is_active = $wt_iew_admin_modules[ $module ]; // checking module status.
			} else {
				$wt_iew_admin_modules[ $module ] = 1; // default status is active.
			}
			$module_file = plugin_dir_path( __FILE__ ) . "modules/$module/class-wt-import-export-for-woo-$module.php";
			if ( file_exists( $module_file ) && 1 == $is_active ) {
				self::$existing_modules[] = $module; // this is for module_exits checking.
				require_once $module_file;
			} else {
				$wt_iew_admin_modules[ $module ] = 0;
			}
		}
		$out = array();
		foreach ( $wt_iew_admin_modules as $k => $m ) {
			if ( in_array( $k, self::$modules ) ) {
				$out[ $k ] = $m;
			}
		}
		update_option( 'wt_iew_admin_modules', $out );

		/**
		*   Add on modules
		*/
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		$module_path = 'import-export-suite-for-woocommerce';

		foreach ( self::$addon_modules as $module ) {
			$plugin_file = 'import-export-suite-for-woocommerce/import-export-suite-for-woocommerce.php';
			if ( is_plugin_active( $plugin_file ) ) {
				$module_file = WP_CONTENT_DIR . "/plugins/{$module_path}/admin/modules/$module/class-wt-import-export-for-woo-$module.php";
				if ( file_exists( $module_file ) ) {
					self::$existing_modules[] = $module;
					require_once $module_file;
				}
			}
		}
	}
	/**
	 * Module exist check
	 *
	 * @param string $module Module name.
	 * @return type
	 */
	public static function module_exists( $module ) {
		return in_array( $module, self::$existing_modules );
	}

	/**
	 * Envelope settings tab content with tab div.
	 * relative path is not acceptable in view file
	 *
	 * @param string  $target_id Target id.
	 * @param string  $view_file View file.
	 * @param string  $html Is html.
	 * @param array   $variables Variables.
	 * @param boolean $need_submit_btn Is submit button needed.
	 */
	public static function envelope_settings_tabcontent( $target_id, $view_file = '', $html = '', $variables = array(), $need_submit_btn = 0 ) {

		// extract( $variables );.
		?>
		<div class="wt-iew-tab-content" data-id="<?php echo esc_html( $target_id ); ?>">
			<?php
			if ( '' != $view_file && file_exists( $view_file ) ) {
				include_once $view_file;
			} else {
				echo wp_kses_post( $html );
			}
			?>
			<?php
			if ( 1 == $need_submit_btn ) {
				include WT_IEW_PLUGIN_PATH . 'admin/views/admin-settings-save-button.php';
			}
			?>
		</div>
		<?php
	}

	/**
	 *   Plugin page action links
	 *
	 * @param array $links Links.
	 */
	public function plugin_action_links( $links ) {
		$links[] = '<a href="' . admin_url( 'admin.php?page=' . WT_IEW_PLUGIN_ID ) . '">' . __( 'Export' ) . '</a>';
		$links[] = '<a href="' . admin_url( 'admin.php?page=' . WT_IEW_PLUGIN_ID ) . '&tab=import">' . __( 'Import' ) . '</a>';
		$links[] = '<a href="https://woocommerce.com/document/import-export-suite-for-woocommerce/" target="_blank">' . __( 'Documentation' ) . '</a>';
		$links[] = '<a href="https://woocommerce.com/my-account/create-a-ticket/" target="_blank">' . __( 'Support' ) . '</a>';
		return $links;
	}
}
