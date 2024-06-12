<?php
/**
 * Export section of the plugin
 *
 * @link
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_Export_Ajax Class.
 */
class Wt_Import_Export_For_Woo_Export_Ajax {

	/**
	 * Step
	 *
	 * @var string
	 */
	public $step = '';
	/**
	 * Steps
	 *
	 * @var array
	 */
	public $steps = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $step_btns = array();
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
	public $to_export = '';
	/**
	 * Module ID
	 *
	 * @var string
	 */
	protected $step_title = '';
	/**
	 * Module ID
	 *
	 * @var string
	 */
	protected $step_keys = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	protected $current_step_index = 0;
	/**
	 * Module ID
	 *
	 * @var string
	 */
	protected $current_step_number = 1;
	/**
	 * Module ID
	 *
	 * @var string
	 */
	protected $last_page = false;
	/**
	 * Module ID
	 *
	 * @var string
	 */
	protected $total_steps = 0;
	/**
	 * Module ID
	 *
	 * @var string
	 */
	protected $step_summary = '';
	/**
	 * Module ID
	 *
	 * @var string
	 */
	protected $mapping_enabled_fields = array();
	/**
	 * Mapping
	 *
	 * @var array
	 */
	protected $mapping_templates = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	protected $selected_template = 0;
	/**
	 * Form data
	 *
	 * @var array
	 */
	protected $selected_template_form_data = array();
	/**
	 * This variable is using to store form_data of selected template or selected history entry
	 *
	 * @var object
	 */
	protected $export_obj = null;
	/**
	 * Rerun ID
	 *
	 * @var integer
	 */
	protected $rerun_id = 0;

	/**
	 * Constructor.
	 *
	 * @param   string  $export_obj Export object.
	 * @param   integer $to_export Export type.
	 * @param   string  $steps Steps.
	 * @param   integer $export_method Export method.
	 * @param   string  $selected_template Selected template.
	 * @param   string  $rerun_id Rerun ID.
	 * @since 1.0.0
	 */
	public function __construct( $export_obj, $to_export, $steps, $export_method, $selected_template, $rerun_id ) {
		$this->export_obj    = $export_obj;
		$this->to_export     = $to_export;
		$this->steps         = $steps;
		$this->export_method = $export_method;
		$this->selected_template = $selected_template;
		$this->rerun_id          = $rerun_id;
	}//end __construct()


	/**
	 *    Ajax main function to retrive steps HTML
	 *
	 * @param   array $out Output response.
	 * @since 1.0.0
	 */
	public function get_steps( $out ) {
		// sleep(3);.
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		$steps_arra = isset( $_POST['steps'] ) ? map_deep( wp_unslash( $_POST['steps'] ), 'sanitize_text_field' ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing
		$steps     = ( is_array( $steps_arra ) ? $steps_arra : array( $steps_arra ) );
		$steps     = Wt_Iew_Sh::sanitize_item( $steps, 'text_arr' );
		$page_html = array();

		if ( $this->selected_template > 0 ) {
			// taking selected tamplate form_data.
			$this->get_template_form_data( $this->selected_template );
		} else if ( $this->rerun_id > 0 ) {
			$this->selected_template_form_data = $this->export_obj->form_data;
		}

		foreach ( $steps as $step ) {
			$method_name = $step . '_page';
			if ( method_exists( $this, $method_name ) ) {
				$page_html[ $step ] = $this->{$method_name}();

				if ( 'method_export' == $step && ( $this->selected_template > 0 || $this->rerun_id > 0 ) ) {
					$out['template_data'] = $this->selected_template_form_data;
				}
			}
		}

		$out['status']    = 1;
		$out['page_html'] = $page_html;
		return $out;
	}//end get_steps()


	/**
	 *     Ajax function to retrive meta step data
	 *
	 * @param   array $out Output response.
	 * @since 1.0.0
	 */
	public function get_meta_mapping_fields( $out ) {
		if ( $this->selected_template > 0 ) {
			// taking selected tamplate form_data.
			$this->get_template_form_data( $this->selected_template );
		} else if ( $this->rerun_id > 0 ) {
			$this->selected_template_form_data = $this->export_obj->form_data;
		}

		$this->get_mapping_enabled_fields();

		$meta_mapping_screen_fields = array();
		foreach ( $this->mapping_enabled_fields as $field_key => $field_vl ) {
			$field_vl = ( ! is_array( $field_vl ) ? array( $field_vl, 0 ) : $field_vl );
			$meta_mapping_screen_fields[ $field_key ] = array(
				'title'   => '',
				'checked' => $field_vl[1],
				'fields'  => array(),
			);
		}

		// taking current page form data.
		$meta_step_form_data = ( isset( $this->selected_template_form_data['meta_step_form_data'] ) ? $this->selected_template_form_data['meta_step_form_data'] : array() );

		// form_data/template data of fields in mapping page.
		$form_data_meta_mapping_fields = isset( $meta_step_form_data['mapping_fields'] ) ? $meta_step_form_data['mapping_fields'] : array();
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a post
		 * collection request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $meta_mapping_screen_fields    Mapping fields.
		 * @param string          $this->to_export    Export post type.
		 * @param array           $form_data_meta_mapping_fields    Form Mapping fields.
		 */
		$meta_mapping_screen_fields = apply_filters( 'wt_iew_exporter_alter_meta_mapping_fields', $meta_mapping_screen_fields, $this->to_export, $form_data_meta_mapping_fields );

		if ( isset( $meta_mapping_screen_fields['meta']['fields'] ) ) {
			if ( count( $meta_mapping_screen_fields['meta']['fields'] ) > 500 ) {
				$large_meta_columns = true;
			}
		}

		$draggable_tooltip = __( 'Drag to rearrange the columns' );
		$module_url        = plugin_dir_url( __DIR__ );

		$meta_html = array();
		if ( $meta_mapping_screen_fields && is_array( $meta_mapping_screen_fields ) ) {
			// loop through mapping fields.
			foreach ( $meta_mapping_screen_fields as $meta_mapping_screen_field_key => $meta_mapping_screen_field_val ) {
				$current_meta_step_form_data = ( isset( $form_data_meta_mapping_fields[ $meta_mapping_screen_field_key ] ) ? $form_data_meta_mapping_fields[ $meta_mapping_screen_field_key ] : array() );
				ob_start();
				include dirname( plugin_dir_path( __FILE__ ) ) . '/views/export-meta-step-page.php';
				$meta_html[ $meta_mapping_screen_field_key ] = ob_get_clean();
			}
		}

		foreach ( $meta_html as $key => $value ) {
			$meta_html[ $key ] = Wt_Iew_IE_Helper::sanitize_and_minify_html( $value );
		}

		$out['status']    = 1;
		$out['meta_html'] = $meta_html;
		return $out;
	}//end get_meta_mapping_fields()

	/**
	 *     Ajax function to save template
	 *
	 * @param   array $out Output response.
	 * @since 1.0.0
	 */
	public function save_template( $out ) {
		return $this->do_save_template( 'save', $out );
	}//end save_template()

	/**
	 *     Ajax function to save template copy
	 *
	 * @param   array $out Output response.
	 * @since 1.0.0
	 */
	public function save_template_as( $out ) {
		return $this->do_save_template( 'save_as', $out );
	}//end save_template_as()

	/**
	 *     Ajax function to update template
	 *
	 * @param   array $out Output response.
	 * @since 1.0.0
	 */
	public function update_template( $out ) {
		return $this->do_save_template( 'update', $out );
	}//end update_template()


	/**
	 *     Ajax hook to upload the exported file.
	 *
	 * @param   array $out Output response.
	 * @since 1.0.0
	 */
	public function upload( $out ) {
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		$export_id = ( isset( $_POST['export_id'] ) ? intval( $_POST['export_id'] ) : 0 );// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$out       = $this->export_obj->process_upload( 'upload', $export_id, $this->to_export );
		if ( true === $out['response'] ) {
			$out['status'] = 1;
		} else {
			$out['status'] = 0;
		}

		return $out;
	}//end upload()


	/**
	 * Process the image export
	 *
	 * @param   array $out Output response.
	 * @return array
	 */
	public function export_image( $out ) {
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		$offset    = ( isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0 );// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$export_id = ( isset( $_POST['export_id'] ) ? intval( $_POST['export_id'] ) : 0 );// phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( 0 == $export_id ) {
			// first batch.
			// process form data.
			$form_data = ( isset( $_POST['form_data'] ) ? Wt_Import_Export_For_Woo_Common_Helper::process_formdata( maybe_unserialize( map_deep( wp_unslash( $_POST['form_data'] ), 'sanitize_text_field' ) ) ) : array() );

			// sanitize form data.
			$form_data = Wt_Iew_IE_Helper::sanitize_formdata( $form_data, $this->export_obj );
		} else {
			// no need to send form_data. It will take from history table by `process_image_export` method.
			$form_data = array();
		}

		// do the image export process.
		$out = $this->export_obj->process_image_export( $form_data, 'export_image', $this->to_export, '', $export_id, $offset );
		if ( true === $out['response'] ) {
			$out['status'] = 1;
		} else {
			$out['status'] = 0;
		}

		return $out;
	}//end export_image()


	/**
	 * Process the export data
	 *
	 * @param array $out Response.
	 * @return array
	 */
	public function export( $out ) {
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		$offset    = ( isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0 );// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$export_id = ( isset( $_POST['export_id'] ) ? intval( $_POST['export_id'] ) : 0 );// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$file_name = '';

		if ( 0 == $export_id ) {
			// first batch.
			// process form data.
			$form_data = ( isset( $_POST['form_data'] ) ? Wt_Import_Export_For_Woo_Common_Helper::process_formdata( maybe_unserialize( map_deep( wp_unslash( $_POST['form_data'] ), 'sanitize_text_field' ) ) ) : array() );
			if ( isset( $form_data['advanced_form_data']['wt_iew_delimiter_preset'] ) && 'tab' === $form_data['advanced_form_data']['wt_iew_delimiter_preset'] ) {
				$form_data['advanced_form_data']['wt_iew_delimiter']  = "\t";
			}

			// sanitize form data.
			$form_data = Wt_Iew_IE_Helper::sanitize_formdata( $form_data, $this->export_obj );

			// taking file name from user input. (If given).
			if ( isset( $form_data['advanced_form_data'] ) && isset( $form_data['advanced_form_data']['wt_iew_file_name'] ) && '' != $form_data['advanced_form_data']['wt_iew_file_name'] ) {
				$file_name = $form_data['advanced_form_data']['wt_iew_file_name'];
			}
		} else {
			// no need to send form_data. It will take from history table by `process_action` method.
			$form_data = array();
		}

		// do the export process.
		$out = $this->export_obj->process_action( $form_data, 'export', $this->to_export, $file_name, $export_id, $offset );
		if ( true === $out['response'] ) {
			$out['status'] = 1;
		} else {
			$out['status'] = 0;
		}

		return $out;
	}//end export()


	/**
	 * Save/Update template (Ajax sub function)
	 *
	 * @param  string $step current step.
	 * @param array  $out Response.
	 * @return array response status, name, id
	 */
	public function do_save_template( $step, $out ) {
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		$is_update = ( 'update' == $step ) ? true : false;

		// take template name from post data, if not then create from time stamp.
		$template_name = ( isset( $_POST['template_name'] ) ? sanitize_text_field( wp_unslash( $_POST['template_name'] ) ) : gmdate( 'd-M-Y h:i:s A' ) );// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.DateTime.RestrictedFunctions.date_date

		$template_name = stripslashes( $template_name );
		$out['name']   = $template_name;
		$out['id']     = 0;
		$out['status'] = 1;

		if ( '' != $this->to_export ) {
			global $wpdb;

			// checking: just saved and again click the button so shift the action as update.
			if ( 'save' == $step && $this->selected_template > 0 ) {
				$is_update = true;
			}

			// checking template with same name exists.
			$template_data = $this->get_mapping_template_by_name( $template_name );
			if ( $template_data ) {
				$is_throw_warn = false;
				if ( $is_update ) {
					if ( $template_data['id'] != $this->selected_template ) {
						$is_throw_warn = true;
					}
				} else {
					$is_throw_warn = true;
				}

				if ( $is_throw_warn ) {
					$out['status'] = 0;
					if ( 'save_as' == $step ) {
						$out['msg'] = __( 'Please enter a different name' );
					} else {
						$out['msg'] = __( 'Template with same name already exists' );
					}

					return $out;
				}
			}//end if

			$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$template_tb;

			// process form data.
			$form_data = ( isset( $_POST['form_data'] ) ? Wt_Import_Export_For_Woo_Common_Helper::process_formdata( maybe_unserialize( map_deep( wp_unslash( $_POST['form_data'] ), 'sanitize_text_field' ) ) ) : array() );

			// sanitize form data.
			$form_data = Wt_Iew_IE_Helper::sanitize_formdata( $form_data, $this->export_obj );

			// upadte the template.
			if ( $is_update ) {
				$update_data = array(
					'data' => maybe_serialize( $form_data ),
					'name' => $template_name,
				// may be a rename.
				);
				$update_data_type  = array(
					'%s',
					'%s',
				);
				$update_where      = array(
					'id' => $this->selected_template,
				);
				$update_where_type = array( '%d' );
				if ( $wpdb->update( $tb, $update_data, $update_where, $update_data_type, $update_where_type ) !== false ) {
					$out['id']   = $this->selected_template;
					$out['name'] = $template_name;
					$out['msg']  = __( 'Template updated successfully' );
					return $out;
				}
			} else {
				$insert_data      = array(
					'template_type' => 'export',
					'item_type'     => $this->to_export,
					'name'          => $template_name,
					'data'          => maybe_serialize( $form_data ),
				);
				$insert_data_type = array(
					'%s',
					'%s',
					'%s',
					'%s',
				);
				if ( $wpdb->insert( $tb, $insert_data, $insert_data_type ) ) {
					// success.
					$out['id']  = $wpdb->insert_id;
					$out['msg'] = __( 'Template saved successfully' );
					return $out;
				}
			}//end if
		}//end if

		$out['status'] = 0;
		return $out;
	}//end do_save_template()


	/**
	 *  Step 1 (Ajax sub function)
	 *  Built in steps, post type choosing page
	 */
	public function post_type_page() {
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a post
		 * collection request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $post_types    Post types.
		 */
		$post_types = apply_filters( 'wt_iew_exporter_post_types', array() );
		$post_types = ( ! is_array( $post_types ) ? array() : $post_types );
		$this->step = 'post_type';
		$step_info  = $this->steps[ $this->step ];
		$item_type  = $this->to_export;

		$this->prepare_step_summary();
		$this->prepare_footer_button_list();

		ob_start();
		$this->prepare_step_header_html();
		include_once dirname( plugin_dir_path( __FILE__ ) ) . '/views/export-post-type-page.php';
		$this->prepare_step_footer_html();
		return ob_get_clean();
	}//end post_type_page()


	/**
	 *  Step 2 (Ajax sub function)
	 * Built in steps, export method choosing page
	 */
	public function method_export_page() {
		$this->step = 'method_export';
		$step_info  = $this->steps[ $this->step ];
		if ( '' != $this->to_export ) {
			// setting a default export method.
			$this->export_method = ( '' == $this->export_method ? $this->export_obj->default_export_method : $this->export_method );
			$this->export_obj->export_method = $this->export_method;
			$this->steps = $this->export_obj->get_steps();

			$form_data_export_template = $this->selected_template;
			$form_data_mapping_enabled = array();
			if ( $this->rerun_id > 0 ) {
				if ( isset( $this->selected_template_form_data['method_export_form_data'] ) ) {
					if ( isset( $this->selected_template_form_data['method_export_form_data']['selected_template'] ) ) {
						// do not set this value to `$this->selected_template`.
						$form_data_export_template = $this->selected_template_form_data['method_export_form_data']['selected_template'];
					}

					if ( isset( $this->selected_template_form_data['method_export_form_data']['mapping_enabled_fields'] ) ) {
						$form_data_mapping_enabled = $this->selected_template_form_data['method_export_form_data']['mapping_enabled_fields'];
						$form_data_mapping_enabled = ( is_array( $form_data_mapping_enabled ) ? $form_data_mapping_enabled : array() );
					}
				}
			}

			$this->prepare_step_summary();
			$this->prepare_footer_button_list();

			// meta field list for quick export.
			$this->get_mapping_enabled_fields();

			// template list for template export.
			$this->get_mapping_templates();

			ob_start();
			$this->prepare_step_header_html();
			include_once dirname( plugin_dir_path( __FILE__ ) ) . '/views/export-method-export-page.php';
			$this->prepare_step_footer_html();
			return ob_get_clean();
		} else {
			return '';
		}//end if
	}//end method_export_page()


	/**
	 * Get step information
	 *
	 * @param string $step Step.
	 */
	public function get_step_info( $step ) {
		return isset( $this->steps[ $step ] ) ? $this->steps[ $step ] : array(
			'title'       => ' ',
			'description' => ' ',
		);
	}//end get_step_info()


	/**
	 *      Step 3 (Ajax sub function)
	 *     Built in steps, filter page
	 */
	public function filter_page() {
		$this->step = 'filter';
		$step_info  = $this->get_step_info( $this->step );
		if ( '' != $this->to_export ) {
			$this->prepare_step_summary();
			$this->prepare_footer_button_list();

			// taking current page form data.
			$filter_form_data = ( isset( $this->selected_template_form_data['filter_form_data'] ) ? $this->selected_template_form_data['filter_form_data'] : array() );

			if ( isset( $_REQUEST['rerun_id'] ) && absint( $_REQUEST['rerun_id'] ) > 0 ) {
				$requested_cron_edit_id = ( isset( $_REQUEST['rerun_id'] ) ? absint( $_REQUEST['rerun_id'] ) : 0 );
				$cron_module_obj        = Wt_Import_Export_For_Woo::load_modules( 'cron' );
				if ( ! is_null( $cron_module_obj ) ) {
					// check the cron entry is for export and also has form_data.
					$cron_data        = $cron_module_obj->get_cron_by_id( $requested_cron_edit_id );
					if ( isset( $cron_data['data'] ) ) {
						$form_data        = maybe_unserialize( $cron_data['data'] );
						$filter_form_data = $form_data['filter_form_data'];
					}
				}
			}

			$filter_screen_fields = $this->export_obj->get_filter_screen_fields( $filter_form_data );

			ob_start();
			$this->prepare_step_header_html();
			include_once dirname( plugin_dir_path( __FILE__ ) ) . '/views/export-filter-page.php';
			$this->prepare_step_footer_html();
			return ob_get_clean();
		} else {
			return '';
		}//end if
	}//end filter_page()


	/**
	 * Step 4 (Ajax sub function)
	 * Built in steps, mapping page
	 */
	public function mapping_page() {
		$this->step = 'mapping';
		$step_info  = $this->get_step_info( $this->step );
		if ( '' != $this->to_export ) {
			$this->prepare_step_summary();
			$this->prepare_footer_button_list();

			// taking current page form data.
			$mapping_form_data = ( isset( $this->selected_template_form_data['mapping_form_data'] ) ? $this->selected_template_form_data['mapping_form_data'] : array() );

			if ( isset( $_REQUEST['rerun_id'] ) && absint( $_REQUEST['rerun_id'] ) > 0 ) {
				$requested_cron_edit_id = ( isset( $_REQUEST['rerun_id'] ) ? absint( $_REQUEST['rerun_id'] ) : 0 );
				$cron_module_obj        = Wt_Import_Export_For_Woo::load_modules( 'cron' );
				if ( ! is_null( $cron_module_obj ) ) {
					// check the cron entry is for export and also has form_data.
					$cron_data         = $cron_module_obj->get_cron_by_id( $requested_cron_edit_id );
					$form_data         = maybe_unserialize( $cron_data['data'] );
					$mapping_form_data = $form_data['mapping_form_data'];
				}
			}

			// form_data/template data of fields in mapping page.
			$form_data_mapping_fields = isset( $mapping_form_data['mapping_fields'] ) ? $mapping_form_data['mapping_fields'] : array();

			// default mapping page fields.
			$mapping_fields = array();
			/**
			 * Filter the query arguments for a request.
			 *
			 * Enables adding extra arguments or setting defaults for a post
			 * collection request.
			 *
			 * @since 1.0.0
			 *
			 * @param array           $mapping_fields    Mapping fields.
			 * @param string          $this->to_export    Export post type.
			 * @param array           $form_data_mapping_fields    Form Mapping fields.
			 */
			$mapping_fields = apply_filters( 'wt_iew_exporter_alter_mapping_fields', $mapping_fields, $this->to_export, $form_data_mapping_fields );

			// meta fields list.
			$this->get_mapping_enabled_fields();

			// mapping enabled meta fields.
			$form_data_mapping_enabled_fields = ( isset( $mapping_form_data['mapping_enabled_fields'] ) ? $mapping_form_data['mapping_enabled_fields'] : array() );

			ob_start();
			$this->prepare_step_header_html();
			include_once dirname( plugin_dir_path( __FILE__ ) ) . '/views/export-mapping-page.php';
			$this->prepare_step_footer_html();
			return ob_get_clean();
		} else {
			return '';
		}//end if
	}//end mapping_page()


	/**
	 * Step 5 (Ajax sub function)
	 * Built in steps, advanced page
	 */
	public function advanced_page() {
		$this->step = 'advanced';
		$step_info  = $this->get_step_info( $this->step );
		if ( '' != $this->to_export ) {
			$this->prepare_step_summary();
			$this->prepare_footer_button_list();

			// taking current page form data.
			$advanced_form_data = ( isset( $this->selected_template_form_data['advanced_form_data'] ) ? $this->selected_template_form_data['advanced_form_data'] : array() );

			if ( isset( $_REQUEST['rerun_id'] ) && absint( $_REQUEST['rerun_id'] ) > 0 ) {
				$requested_cron_edit_id = ( isset( $_REQUEST['rerun_id'] ) ? absint( $_REQUEST['rerun_id'] ) : 0 );
				$cron_module_obj        = Wt_Import_Export_For_Woo::load_modules( 'cron' );
				if ( ! is_null( $cron_module_obj ) ) {
								// check the cron entry is for export and also has form_data.
								$cron_data          = $cron_module_obj->get_cron_by_id( $requested_cron_edit_id );
								$form_data          = maybe_unserialize( $cron_data['data'] );
								$advanced_form_data = $form_data['advanced_form_data'];
				}
			}

			$advanced_screen_fields = $this->export_obj->get_advanced_screen_fields( $advanced_form_data );

			ob_start();
			$this->prepare_step_header_html();
			include_once dirname( plugin_dir_path( __FILE__ ) ) . '/views/export-advanced-page.php';
			$this->prepare_step_footer_html();
			return ob_get_clean();
		} else {
			return '';
		}//end if
	}//end advanced_page()


	/**
	 * Get template form data
	 *
	 * @param integer $id ID.
	 */
	protected function get_template_form_data( $id ) {
		$template_data = $this->get_mapping_template_by_id( $id );
		if ( $template_data ) {
			$decoded_form_data = Wt_Import_Export_For_Woo_Common_Helper::process_formdata( maybe_unserialize( $template_data['data'] ) );
			$this->selected_template_form_data = ( ! is_array( $decoded_form_data ) ? array() : $decoded_form_data );
		}
	}//end get_template_form_data()


	/**
	 * Taking mapping template by Name
	 *
	 * @param string $name Template name.
	 */
	protected function get_mapping_template_by_name( $name ) {
		global $wpdb;
		$tb  = $wpdb->prefix . Wt_Import_Export_For_Woo::$template_tb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_mapping_template WHERE template_type=%s AND item_type=%s AND name=%s", array( 'export', $this->to_export, $name ) ), ARRAY_A );// @codingStandardsIgnoreLine.
	}//end get_mapping_template_by_name()


	/**
	 * Taking mapping template by ID
	 *
	 * @param integer $id Template id.
	 */
	protected function get_mapping_template_by_id( $id ) {
		global $wpdb;
		$tb  = $wpdb->prefix . Wt_Import_Export_For_Woo::$template_tb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_mapping_template WHERE template_type=%s AND item_type=%s AND id=%d", array( 'export', $this->to_export, $id ) ), ARRAY_A );// @codingStandardsIgnoreLine.
	}//end get_mapping_template_by_id()


	/**
	 * Taking all mapping templates
	 */
	protected function get_mapping_templates() {
		if ( '' == $this->to_export ) {
			return;
		}

		global $wpdb;
		$tb  = $wpdb->prefix . Wt_Import_Export_For_Woo::$template_tb;
		$val = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_mapping_template WHERE template_type='export' AND item_type=%s ORDER BY id DESC", $this->to_export ), ARRAY_A );// @codingStandardsIgnoreLine.

		// add a filter here for modules to alter the data.
		$this->mapping_templates = ( $val ? $val : array() );
	}//end get_mapping_templates()


	/**
	 * Get meta field list for mapping page
	 */
	protected function get_mapping_enabled_fields() {
		$mapping_enabled_fields       = array(
			'hidden_meta' => array(
				__( 'Hidden meta' ),
				0,
			),
			'meta'        => array(
				__( 'Meta' ),
				1,
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
		 * @param array           $mapping_enabled_fields    Mapping fields.
		 * @param string          $this->to_export    Export post type.
		 * @param array           $form_data_mapping_fields    Form Mapping fields.
		 */
		$this->mapping_enabled_fields = apply_filters( 'wt_iew_exporter_alter_mapping_enabled_fields', $mapping_enabled_fields, $this->to_export, array() );
	}//end get_mapping_enabled_fields()

	/**
	 * Get footer HTML
	 */
	protected function prepare_step_footer_html() {
		include dirname( plugin_dir_path( __FILE__ ) ) . '/views/export-footer.php';
	}//end prepare_step_footer_html()

	/**
	 * Get step summary
	 */
	protected function prepare_step_summary() {
		$step_info        = $this->get_step_info( $this->step );
		$this->step_title = $step_info['title'];
		$this->step_keys  = array_keys( $this->steps );
		$this->current_step_index  = array_search( $this->step, $this->step_keys );
		$this->current_step_number = ( $this->current_step_index + 1 );
		$this->last_page           = ( ! isset( $this->step_keys[ ( $this->current_step_index + 1 ) ] ) ? true : false );
		$this->total_steps         = count( $this->step_keys );
		/* translators: 1: current step number. 2: total steps */
		$this->step_summary        = sprintf( __( 'Step %1$d of %2$d', 'import-export-suite-for-woocommerce' ), $this->current_step_number, $this->total_steps );
	}//end prepare_step_summary()

	/**
	 * Get step header HTML
	 */
	protected function prepare_step_header_html() {
		include dirname( plugin_dir_path( __FILE__ ) ) . '/views/export-header.php';
	}//end prepare_step_header_html()

	/**
	 * Get step button list
	 */
	protected function prepare_footer_button_list() {
		$out           = array();
		$step_keys     = $this->step_keys;
		$current_index = $this->current_step_index;
		$last_page     = $this->last_page;
		if ( false !== $current_index ) {
			// step exists.
			if ( $current_index > 0 ) {
				// add back button.
				$out['back'] = array(
					'type'        => 'button',
					'action_type' => 'step',
					'key'         => $step_keys[ ( $current_index - 1 ) ],
					'text'        => '<span class="dashicons dashicons-arrow-left-alt2" style="line-height:27px;"></span> ' . __( 'Back', 'import-export-suite-for-woocommerce' ),
				);
			}

			if ( isset( $step_keys[ ( $current_index + 1 ) ] ) ) {
				// not last step.
				$next_number = ( $current_index + 2 );
				$next_key    = $step_keys[ ( $current_index + 1 ) ];
				$next_title  = $this->steps[ $next_key ]['title'];
				$out['next'] = array(
					'type'        => 'button',
					'action_type' => 'step',
					'key'         => $next_key,
					'text'        => __( 'Step', 'import-export-suite-for-woocommerce' ) . ' ' . $next_number . ': ' . $next_title . ' <span class="dashicons dashicons-arrow-right-alt2" style="line-height:27px;"></span>',
				);

				if ( 'quick' == $this->export_method || 'template' == $this->export_method ) {
					// Quick Or Template method.
					$out['or'] = array(
						'type' => 'text',
						'text' => __( 'Or', 'import-export-suite-for-woocommerce' ),
					);
				}
			} else {
				$last_page = true;
			}//end if

			if ( 'quick' == $this->export_method || 'template' == $this->export_method || $last_page ) {
				// template method, or last page, or quick export.
				if ( 'quick' != $this->export_method && $last_page ) {
					// last page and not quick export.
					if ( 'template' == $this->export_method ) {
						$out['save'] = array(
							'key'   => 'save',
							'icon'  => '',
							'type'  => 'dropdown_button',
							'text'  => __( 'Save template', 'import-export-suite-for-woocommerce' ),
							'items' => array(
								'update' => array(
									'key'  => 'update_template',
									'text' => __( 'Save', 'import-export-suite-for-woocommerce' ),
						// no prompt.
								),
								'save'   => array(
									'key'  => 'save_template_as',
									'text' => __( 'Save As', 'import-export-suite-for-woocommerce' ),
								 // prompt for name.
								),
							),
						);
					} else {
						$out['save'] = array(
							'key'  => 'save_template',
							'icon' => '',
							'type' => 'button',
							'text' => __( 'Save template', 'import-export-suite-for-woocommerce' ),
						// prompt for name.
						);
					}//end if
				}//end if

				if ( $last_page ) {
					$out['export_image'] = array(
						'key'   => 'export_image',
						'class' => 'iew_export_image_btn',
						'icon'  => '',
						'type'  => 'button',
						'text'  => __( 'Export images', 'import-export-suite-for-woocommerce' ),
					);
				}

				$out['export'] = array(
					'key'   => 'export',
					'class' => 'iew_export_btn',
					'icon'  => '',
					'type'  => 'button',
					'text'  => __( 'Export', 'import-export-suite-for-woocommerce' ),
				);
			}//end if
		}//end if
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a post
		 * collection request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $out    Result array.
		 * @param string          $this->step    Step.
		 * @param array           $this->steps    Steps.
		 */
		$this->step_btns = apply_filters( 'wt_iew_exporter_alter_footer_btns', $out, $this->step, $this->steps );
	}//end prepare_footer_button_list()
}//end class
