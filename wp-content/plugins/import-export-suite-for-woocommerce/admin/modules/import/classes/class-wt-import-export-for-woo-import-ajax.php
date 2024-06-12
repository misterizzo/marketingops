<?php
/**
 * Ajax section of the Import module
 *
 * @link
 *
 * @package  ImportExportSuite\Admin\Modules\Import
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_Import_Ajax Class.
 */
class Wt_Import_Export_For_Woo_Import_Ajax {

	/**
	 * Module ID
	 *
	 * @var string
	 */
	public $step = '';
	/**
	 * Module ID
	 *
	 * @var string
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
	public $import_method = '';
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
	protected $step_description = '';
	/**
	 * Module ID
	 *
	 * @var string
	 */
	protected $mapping_enabled_fields = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	protected $mapping_templates = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	protected $selected_template = 0;
	/**
	 * Module ID
	 *
	 * @var string
	 */
	protected $selected_template_form_data = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	protected $import_obj = null;
	/**
	 * Module ID
	 *
	 * @var string
	 */
	protected $field_group_prefixes = array();
	/**
	 * Module ID
	 *
	 * @var string
	 */
	protected $rerun_id = 0;
	/**
	 * Constructor.
	 *
	 * @param type $import_obj Description.
	 * @param type $to_import Description.
	 * @param type $steps Description.
	 * @param type $import_method Description.
	 * @param type $selected_template Description.
	 * @param type $rerun_id Description.
	 * @since 1.0.0
	 */
	public function __construct( $import_obj, $to_import, $steps, $import_method, $selected_template, $rerun_id ) {
		$this->import_obj = $import_obj;
		$this->to_import = $to_import;
		$this->steps = $steps;
		$this->import_method = $import_method;
		$this->selected_template = $selected_template;
		$this->rerun_id = $rerun_id;

		/**
		*  This array is to group the fields in the input file that are not in the default list.
		*/
		$this->field_group_prefixes = array(
			'taxonomies' => array( 'tax' ),
			'meta' => array( 'meta' ),
			'attributes' => array( 'attribute', 'attribute_data', 'attribute_default', 'meta:attribute' ),
			'hidden_meta' => array( 'meta:_' ),
		);
	}

	/**
	 *   Ajax main function to retrive steps HTML
	 *
	 * @param array $out Description.
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
		$page_html = array();

		if ( $this->selected_template > 0 ) {
			$this->get_template_form_data( $this->selected_template );

		} elseif ( $this->rerun_id > 0 ) {
			$this->selected_template_form_data = $this->import_obj->form_data;
		}

		foreach ( $steps as $step ) {
			$method_name = $step . '_page';
			if ( method_exists( $this, $method_name ) ) {
				$page_html[ $step ] = $this->{$method_name}();

				if ( 'method_import' == $step && ( $this->selected_template > 0 || $this->rerun_id > 0 ) ) {
					$out['template_data'] = $this->selected_template_form_data;
				}
			}
		}
		$out['status'] = 1;
		$out['page_html'] = $page_html;
		return $out;
	}

	/**
	 *   Delete uploaded import file
	 *
	 * @param array $out Response array.
	 */
	public function delete_import_file( $out ) {
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		$file_url = ( isset( $_POST['file_url'] ) ? esc_url_raw( wp_unslash( $_POST['file_url'] ) ) : '' );
		$out['file_url'] = $file_url;
		if ( '' != $file_url && $this->import_obj->delete_import_file( $file_url ) ) {
			$out['status'] = 1;
			$out['msg'] = '';
		}
		return $out;
	}

	/**
	 *   Upload import file (Drag and drop  upload)
	 *
	 * @param array $out Response array.
	 */
	public function upload_import_file( $out ) {
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		if ( isset( $_FILES['wt_iew_import_file'] ) ) {

			$is_file_type_allowed = false;
			$upload_file_type = isset( $_FILES['wt_iew_import_file']['type'] ) ? sanitize_text_field( wp_unslash( $_FILES['wt_iew_import_file']['type'] ) ) : '';
			$upload_file_name = isset( $_FILES['wt_iew_import_file']['name'] ) ? sanitize_text_field( wp_unslash( $_FILES['wt_iew_import_file']['name'] ) ) : '';
			if ( ! in_array( $upload_file_type, $this->import_obj->allowed_import_file_type_mime ) ) {
				$ext = pathinfo( $upload_file_name, PATHINFO_EXTENSION );
				if ( isset( $this->import_obj->allowed_import_file_type_mime[ $ext ] ) ) {
					$is_file_type_allowed = true;
				}
			} else {
				$is_file_type_allowed = true;
			}

			if ( $is_file_type_allowed ) {

				@set_time_limit( 3600 ); // 1 hour.

				$max_bytes = ( $this->import_obj->max_import_file_size * 1000000 ); // convert to bytes.
				$upload_file_size = isset( $_FILES['wt_iew_import_file']['size'] ) ? sanitize_text_field( wp_unslash( $_FILES['wt_iew_import_file']['size'] ) ) : '';
				if ( $max_bytes >= $upload_file_size ) {
					$file_name = 'local_file_' . time() . '_' . sanitize_file_name( wp_unslash( $_FILES['wt_iew_import_file']['name'] ) ); // sanitize the file name, add a timestamp prefix to avoid conflict.
					$file_path = $this->import_obj->get_file_path( $file_name );
					$upload_file_temp = isset( $_FILES['wt_iew_import_file']['tmp_name'] ) ? sanitize_text_field( wp_unslash( $_FILES['wt_iew_import_file']['tmp_name'] ) ) : '';
					if ( @move_uploaded_file( $upload_file_temp, $file_path ) ) {
						$out['msg'] = '';
						$out['status'] = 1;
						$out['url'] = $this->import_obj->get_file_url( $file_name );

						/**
						*   Check old file exists, and delete it
						*/
						$file_url = ( isset( $_POST['file_url'] ) ? esc_url_raw( wp_unslash( $_POST['file_url'] ) ) : '' );// phpcs:ignore WordPress.Security.NonceVerification.Missing
						if ( '' != $file_url ) {
							$this->import_obj->delete_import_file( $file_url );
						}
					} else {
						$out['msg'] = __( 'Unable to upload file. Please check write permission of your `wp-content` folder.' );
					}
				} else {
					/* translators:%d: Upload limit */
					$out['msg'] = sprintf( __( 'File size exceeds the limit. %dMB max' ), $this->import_obj->max_import_file_size );
				}
			} else {
				/* translators:%s: file type */
				$out['msg'] = sprintf( __( 'Invalid file type. Only %s are allowed.' ), implode( ', ', array_values( $this->import_obj->allowed_import_file_type ) ) );
			}
		}

		return $out;
	}

	/**
	 *   Ajax hook to download the input file as temp file and validate its extension.
	 *
	 * @param array $out Output.
	 */
	public function validate_file( $out ) {

		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		/* process form data */
		$form_data = ( isset( $_POST['form_data'] ) ? Wt_Import_Export_For_Woo_Common_Helper::process_formdata( maybe_unserialize( map_deep( wp_unslash( $_POST['form_data'] ), 'sanitize_text_field' ) ) ) : array() );

		if ( 'tab' === $form_data['method_import_form_data']['wt_iew_delimiter_preset'] ) {
			$form_data['method_import_form_data']['wt_iew_delimiter']  = "\t";
		}
		if ( 'space' === $form_data['method_import_form_data']['wt_iew_delimiter_preset'] ) {
			$form_data['method_import_form_data']['wt_iew_delimiter']  = ' ';
		}

		$response = $this->import_obj->download_remote_file( $form_data );

		if ( $response['response'] ) {
			$temp_import_file = ( isset( $_POST['temp_import_file'] ) ? sanitize_file_name( wp_unslash( $_POST['temp_import_file'] ) ) : '' );// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing
			if ( '' != $temp_import_file ) {
				$file_path = $this->import_obj->get_file_path( $temp_import_file );
				if ( file_exists( $file_path ) ) {
					@unlink( $file_path );
				}
			}
		}

		$out['status'] = ( $response['response'] ? 1 : 0 );
		$out['msg'] = ( '' != $response['msg'] ) ? $response['msg'] : $out['msg'];
		$out['file_name'] = ( isset( $response['file_name'] ) ? $response['file_name'] : '' );

		return $out;
	}

	/**
	 *   Ajax function to retrive meta step data
	 *
	 * @param array $out Output.
	 */
	public function get_meta_mapping_fields( $out ) {
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		if ( $this->selected_template > 0 ) {
			$this->get_template_form_data( $this->selected_template );

		} elseif ( $this->rerun_id > 0 ) {
			$this->selected_template_form_data = $this->import_obj->form_data;
		}

		/* This is the sample data from input file */
		$file_heading_meta_fields = ( isset( $_POST['file_head_meta'] ) ? json_decode( map_deep( wp_unslash( $_POST['file_head_meta'] ), 'sanitize_text_field' ), true ) : array() );// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing

		// taking current page form data.
		$meta_step_form_data = ( isset( $this->selected_template_form_data['meta_step_form_data'] ) ? $this->selected_template_form_data['meta_step_form_data'] : array() );

		/* formdata/template data of fields in mapping page */
		$form_data_meta_mapping_fields = isset( $meta_step_form_data['mapping_fields'] ) ? $meta_step_form_data['mapping_fields'] : array();

		$meta_mapping_screen_fields = $this->_get_meta_mapping_screen_fields( $form_data_meta_mapping_fields );

		$draggable_tooltip = __( 'Drag to rearrange the columns' );
		$module_url = plugin_dir_url( __DIR__ );

		/* preparing meta fields. */
		$prepared_meta_fields = array();
		if ( $meta_mapping_screen_fields && is_array( $meta_mapping_screen_fields ) ) {
			/* loop through mapping fields */
			foreach ( $meta_mapping_screen_fields as $meta_mapping_screen_field_key => $meta_mapping_screen_field_val ) {
				/* decalaring an empty array*/
				$temp_arr = array();

				/* current field group(tax, meta) formdata */
				$current_meta_step_form_data = ( isset( $form_data_meta_mapping_fields[ $meta_mapping_screen_field_key ] ) ? $form_data_meta_mapping_fields[ $meta_mapping_screen_field_key ] : array() );

				/* default field list from post type module */
				$mapping_fields = ( ( isset( $meta_mapping_screen_field_val['fields'] ) && is_array( $meta_mapping_screen_field_val['fields'] ) ) ? $meta_mapping_screen_field_val['fields'] : array() );

				if ( count( $mapping_fields ) > 2000 ) {
					$heavy_meta['status'] = true;
					$heavy_meta['message']             = __( 'Large number of meta data has been detected. If you choose to proceed, this action will import only 2000 meta columns ( current website meta + new meta from the input file, in sequence). Consider removing unnecessary columns from input file and try again.' );
					$heavy_meta['count']           = count( $mapping_fields );
					$mapping_fields      = array_slice( $mapping_fields, 0, 2000 );
					$out['heavy_meta'] = $heavy_meta;
				}

				/* loop through form data */
				foreach ( $current_meta_step_form_data as $key => $val_arr ) {
					$val = $val_arr[0]; /* normal column val */
					$checked = $val_arr[1]; /* import this column? */

					if ( isset( $mapping_fields[ $key ] ) ) {
						$label = ( isset( $mapping_fields[ $key ]['title'] ) ? $mapping_fields[ $key ]['title'] : '' );
						$description = ( isset( $mapping_fields[ $key ]['description'] ) ? $mapping_fields[ $key ]['title'] : '' );
						$type = ( isset( $mapping_fields[ $key ]['type'] ) ? $mapping_fields[ $key ]['type'] : '' );
						unset( $mapping_fields[ $key ] ); // remove the field from default list.

						if ( isset( $file_heading_meta_fields[ $key ] ) ) {
							unset( $file_heading_meta_fields[ $key ] ); // remove the field from file heading list.
						}
						$temp_arr[ $key ] = array(
							'label' => $label,
							'description' => $description,
							'val' => $val,
							'checked' => $checked,
							'type' => $type,
						);
					} elseif ( isset( $file_heading_meta_fields[ $key ] ) ) {
						$label = $key;
						$description = $this->prepare_field_description( $key );
						$type = '';
						unset( $file_heading_meta_fields[ $key ] ); // remove the field from file heading list.
						$temp_arr[ $key ] = array(
							'label' => $label,
							'description' => $description,
							'val' => $val,
							'checked' => $checked,
							'type' => $type,
						);
					}
				}

				/* loop through mapping fields */
				if ( count( $mapping_fields ) > 0 ) {
					foreach ( $mapping_fields as $key => $val_arr ) {
						$label = ( isset( $val_arr['title'] ) ? $val_arr['title'] : '' );
						$description = ( isset( $val_arr['description'] ) ? $val_arr['description'] : '' );
						$type = ( isset( $val_arr['type'] ) ? $val_arr['type'] : '' );
						$val = '';
						$checked = 0; /* import this column? */
						if ( isset( $file_heading_meta_fields[ $key ] ) ) {
							$checked = 1; /* import this column? */
							$val = '{' . $key . '}';
							unset( $file_heading_meta_fields[ $key ] ); // remove the field from file heading list.
						}
						$temp_arr[ $key ] = array(
							'label' => $label,
							'description' => $description,
							'val' => $val,
							'checked' => $checked,
							'type' => $type,
						);
					}
				}

				if ( count( $file_heading_meta_fields ) > 0 ) {
					$current_field_group_prefix_arr = ( isset( $this->field_group_prefixes[ $meta_mapping_screen_field_key ] ) ? $this->field_group_prefixes[ $meta_mapping_screen_field_key ] : array() );
					foreach ( $file_heading_meta_fields as $key => $sample_val ) {
						$is_include = $this->_is_include_meta_in_this_group( $current_field_group_prefix_arr, $key );
						if ( 1 == $is_include ) {
							$label = Wt_Iew_Sh::sanitize_item( $key );
							$description = $this->prepare_field_description( $key );
							$type = '';
							$val = '{' . $key . '}';
							$checked = 1; /* import this column? */
							unset( $file_heading_meta_fields[ $key ] ); // remove the field from file heading list.
							$temp_arr[ $key ] = array(
								'label' => $label,
								'description' => $description,
								'val' => $val,
								'checked' => $checked,
								'type' => $type,
							);
						}
					}
				}

				/* adding value to main array */
				$prepared_meta_fields[ $meta_mapping_screen_field_key ] = array(
					'fields' => $temp_arr,
					'checked' => ( isset( $meta_mapping_screen_field_val['checked'] ) && 1 == $meta_mapping_screen_field_val['checked'] ? 1 : 0 ),
				);
			}

			/*
			 If any columns that not in the above list
			 *
			if ( count( $file_heading_meta_fields ) > 0 ) {
				// do something
			}
			 *
			 */
		}

		/* prepare HTML for meta mapping step */
		$meta_html = array();

		/* loop through prepared meta fields */
		foreach ( $prepared_meta_fields as $meta_mapping_screen_field_key => $meta_mapping_screen_field_val ) {
			ob_start();
			include dirname( plugin_dir_path( __FILE__ ) ) . '/views/import-meta-step-page.php';
			$meta_html[ $meta_mapping_screen_field_key ] = ob_get_clean();
		}

		foreach ( $meta_html as $key => $value ) {
			$meta_html[ $key ] = Wt_Iew_IE_Helper::sanitize_and_minify_html( $value );
		}

		$out['status'] = 1;
		$out['meta_html'] = $meta_html;
		return $out;
	}
	/**
	 *   Ajax function to retrive meta step data
	 *
	 * @param array $out Output.
	 */
	public function save_template( $out ) {
		return $this->do_save_template( 'save', $out );
	}
	/**
	 *   Ajax function to retrive meta step data
	 *
	 * @param array $out Output.
	 */
	public function save_template_as( $out ) {
		return $this->do_save_template( 'save_as', $out );
	}
	/**
	 *   Ajax function to retrive meta step data
	 *
	 * @param array $out Output.
	 */
	public function update_template( $out ) {
		return $this->do_save_template( 'update', $out );
	}

	/**
	 *   Download the input file and create history entry.
	 *   This is the primary step before Import
	 *   On XML import the file will be converted to CSV (Batch processing)
	 *
	 * @param array $out Output.
	 */
	public function download( $out ) {
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		$this->import_obj->temp_import_file = ( isset( $_POST['temp_import_file'] ) ? sanitize_file_name( wp_unslash( $_POST['temp_import_file'] ) ) : '' );

		$offset = ( isset( $_POST['offset'] ) ? floatval( $_POST['offset'] ) : 0 );// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing
		$import_id = ( isset( $_POST['import_id'] ) ? intval( $_POST['import_id'] ) : 0 );// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing
		$import_method = ( isset( $_POST['import_method'] ) ? sanitize_text_field( wp_unslash( $_POST['import_method'] ) ) : $this->import_obj->default_import_method );// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing

		if ( 0 == $offset ) {
			/* process form data */
			$form_data = ( isset( $_POST['form_data'] ) ? Wt_Import_Export_For_Woo_Common_Helper::process_formdata( maybe_unserialize( map_deep( wp_unslash( $_POST['form_data'] ), 'sanitize_text_field' ) ) ) : array() );
			if ( 'tab' === $form_data['method_import_form_data']['wt_iew_delimiter_preset'] ) {
					$form_data['method_import_form_data']['wt_iew_delimiter']  = "\t";
			}
			if ( 'space' === $form_data['method_import_form_data']['wt_iew_delimiter_preset'] ) {
					$form_data['method_import_form_data']['wt_iew_delimiter']  = ' ';
			}
			// sanitize form data.
			$form_data = Wt_Iew_IE_Helper::sanitize_formdata( $form_data, $this->import_obj );
		} else {
			/* no need to process the formdata steps other than first */
			$form_data = array();
		}

		$out = $this->import_obj->process_download( $form_data, 'download', $this->to_import, $import_id, $offset );
		if ( true === $out['response'] ) {
			$import_id = $out['import_id'];

			/**
			*   Prepare default mapping data for quick import
			*   After preparing update the Formdata in history table
			*/
			if ( 'quick' == $import_method && $import_id > 0 && 3 == $out['finished'] ) {
				$this->_prepare_for_quick( $import_id );
			}

			$out['status'] = 1;
		} else {
			$out['status'] = 0;
		}
		return $out;
	}

	/**
	 * Process the import
	 *
	 * @param array $out Output.
	 * @return array
	 */
	public function import( $out ) {
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		$offset = ( isset( $_POST['offset'] ) ? floatval( $_POST['offset'] ) : 0 );// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing
		$import_id = ( isset( $_POST['import_id'] ) ? intval( $_POST['import_id'] ) : 0 );// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing

		/* no need to send formdata. It will take from history table by `process_action` method */
		$form_data = array();

		/* do the export process */
		$out = $this->import_obj->process_action( $form_data, 'import', $this->to_import, '', $import_id, $offset );
		if ( true === $out['response'] ) {
			$out['status'] = 1;
		} else {
			$out['status'] = 0;
		}
		return $out;
	}

	/**
	 * Save/Update template (Ajax sub function)
	 *
	 * @param string $step Is update existing template or save as new.
	 * @param array  $out Output.
	 * @return array response status, name, id
	 */
	public function do_save_template( $step, $out ) {
		// Nonce already verified on main function - sanitization thorugh helper functions.
		$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
		if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
		}
		$is_update = ( 'update' == $step ) ? true : false;

		/* take template name from post data, if not then create from time stamp */
		$template_name_date = gmdate( 'd-M-Y h:i:s A' );// @codingStandardsIgnoreLine.
		$template_name = ( isset( $_POST['template_name'] ) ? sanitize_text_field( wp_unslash( $_POST['template_name'] ) ) : $template_name_date );// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing

		$template_name = stripslashes( $template_name );
		$out['name'] = $template_name;
		$out['id'] = 0;
		$out['status'] = 1;

		if ( '' != $this->to_import ) {
			global $wpdb;

			/* checking: just saved and again click the button so shift the action as update */
			if ( 'save' == $step && $this->selected_template > 0 ) {
				$is_update = true;
			}

			/* checking template with same name exists */
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
			}

			$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$template_tb;

			/* process form data */
			$form_data = ( isset( $_POST['form_data'] ) ? Wt_Import_Export_For_Woo_Common_Helper::process_formdata( maybe_unserialize( map_deep( wp_unslash( $_POST['form_data'] ), 'sanitize_text_field' ) ) ) : array() );

			// sanitize form data.
			$form_data = Wt_Iew_IE_Helper::sanitize_formdata( $form_data, $this->import_obj );

			/* upadte the template */
			if ( $is_update ) {

				$update_data = array(
					'data' => maybe_serialize( $form_data ),
					'name' => $template_name, // may be a rename.
				);
				$update_data_type = array(
					'%s',
					'%s',
				);
				$update_where = array(
					'id' => $this->selected_template,
				);
				$update_where_type = array(
					'%d',
				);
				if ( $wpdb->update( $tb, $update_data, $update_where, $update_data_type, $update_where_type ) !== false ) {
					$out['id'] = $this->selected_template;
					$out['name'] = $template_name;
					$out['msg'] = __( 'Template updated successfully' );
					return $out;
				}
			} else {
				$insert_data = array(
					'template_type' => 'import',
					'item_type' => $this->to_import,
					'name' => $template_name,
					'data' => maybe_serialize( $form_data ),
				);
				$insert_data_type = array(
					'%s',
					'%s',
					'%s',
					'%s',
				);
				if ( $wpdb->insert( $tb, $insert_data, $insert_data_type ) ) {
					$out['id'] = $wpdb->insert_id;
					$out['msg'] = __( 'Template saved successfully' );
					return $out;
				}
			}
		}
		$out['status'] = 0;
		return $out;
	}

	/**
	 * Get step information
	 *
	 * @param string $step Step.
	 */
	public function get_step_info( $step ) {
		return isset( $this->steps[ $step ] ) ? $this->steps[ $step ] : array(
			'title' => ' ',
			'description' => ' ',
		);
	}

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
		$post_types = apply_filters( 'wt_iew_importer_post_types', array() );
		$post_types = ( ! is_array( $post_types ) ? array() : $post_types );
		$this->step = 'post_type';

		$this->prepare_step_summary();
		$this->prepare_footer_button_list();

		ob_start();
		$this->prepare_step_header_html();
		include_once dirname( plugin_dir_path( __FILE__ ) ) . '/views/import-post-type-page.php';
		$this->prepare_step_footer_html();
		return ob_get_clean();
	}

	/**
	 *  Step 2 (Ajax sub function)
	 * Built in steps, import method choosing page
	 */
	public function method_import_page() {
		$this->step = 'method_import';
		if ( '' != $this->to_import ) {
			/* setting a default import method */
			$this->import_method = ( '' == $this->import_method ) ? $this->import_obj->default_import_method : $this->import_method;
			$this->import_obj->import_method = $this->import_method;
			$this->steps = $this->import_obj->get_steps();

			$this->prepare_step_summary();
			$this->prepare_footer_button_list();

			// taking current page form data.
			$method_import_form_data = ( isset( $this->selected_template_form_data['method_import_form_data'] ) ? $this->selected_template_form_data['method_import_form_data'] : array() );

			if ( isset( $_REQUEST['rerun_id'] ) && absint( $_REQUEST['rerun_id'] ) > 0 ) {
					$requested_cron_edit_id = ( isset( $_REQUEST['rerun_id'] ) ? absint( $_REQUEST['rerun_id'] ) : 0 );
					$cron_module_obj = Wt_Import_Export_For_Woo::load_modules( 'cron' );
				if ( ! is_null( $cron_module_obj ) ) {
							/* check the cron entry is for export and also has form_data */
							$cron_data = $cron_module_obj->get_cron_by_id( $requested_cron_edit_id );
					if ( empty( $cron_data ) ) {
						// take history data by import_id.
						$import_data = Wt_Import_Export_For_Woo_History::get_history_entry_by_id( $requested_cron_edit_id );
						// processing form data.
						$form_data = ( isset( $import_data['data'] ) ? maybe_unserialize( $import_data['data'] ) : array() );
					} else {
						$form_data = maybe_unserialize( $cron_data['data'] );
					}
							$method_import_form_data = $form_data['method_import_form_data'];
				}
			}

			$method_import_screen_fields = $this->import_obj->get_method_import_screen_fields( $method_import_form_data );

			$form_data_import_template = $this->selected_template;
			if ( $this->rerun_id > 0 ) {
				if ( isset( $method_import_form_data['selected_template'] ) ) {
					/* do not set this value to `$this->selected_template` */
					$form_data_import_template = $method_import_form_data['selected_template'];
				}
			}

			/* meta field list for quick import */
			$this->get_mapping_templates();

			ob_start();
			$this->prepare_step_header_html();
			include_once dirname( plugin_dir_path( __FILE__ ) ) . '/views/import-method-import-page.php';
			$this->prepare_step_footer_html();
			return ob_get_clean();
		} else {
			return '';
		}
	}

	/**
	 *  Step 3 (Ajax sub function)
	 * Built in steps, Import mapping page
	 */
	public function mapping_page() {
		$this->step = 'mapping';
		if ( '' != $this->to_import ) {
			// Nonce already verified on main function - sanitization thorugh helper functions.
			$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
			if ( ! ( wp_verify_nonce( $nonce, WT_IEW_PLUGIN_ID ) ) ) {
				return $out;
			}
			$this->prepare_step_summary();
			$this->prepare_footer_button_list();

			$temp_import_file = ( isset( $_POST['temp_import_file'] ) ? sanitize_file_name( wp_unslash( $_POST['temp_import_file'] ) ) : '' );// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing
			$file_path = $this->import_obj->get_file_path( $temp_import_file );
			if ( '' != $temp_import_file && file_exists( $file_path ) ) {
				$ext_arr = explode( '.', $temp_import_file );
				$ext = strtolower( end( $ext_arr ) );
				if ( isset( $this->import_obj->allowed_import_file_type[ $ext ] ) ) {
					$skip_from_evaluation_array = $this->import_obj->get_skip_from_evaluation();

					if ( 'xml' == $ext ) {
						include_once WT_IEW_PLUGIN_PATH . 'admin/classes/class-wt-import-export-for-woo-xmlreader.php';
						$reader = new Wt_Import_Export_For_Woo_Xmlreader();
					} else /* csv */
					{
						include_once WT_IEW_PLUGIN_PATH . 'admin/classes/class-wt-import-export-for-woo-csvreader.php';
						$delimiter = ( isset( $_POST['delimiter'] ) ? sanitize_text_field( wp_unslash( $_POST['delimiter'] ) ) : ',' ); // // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing

						$delimiter_preset = ( isset( $_POST['delimiter_preset'] ) ? sanitize_text_field( wp_unslash( $_POST['delimiter_preset'] ) ) : 'comma' );

						if ( 'tab' === $delimiter_preset ) {
							$delimiter  = "\t";
						}
						if ( 'space' === $delimiter_preset ) {
							$delimiter  = ' ';
						}
						$reader = new Wt_Import_Export_For_Woo_Csvreader( $delimiter );
					}

					/* take first two rows in csv and in xml takes column keys and a sample data */
					$sample_data = $reader->get_sample_data( $file_path, true );

					$file_heading_data = $this->process_file_heading_data( $sample_data );
					$file_heading_default_fields = $file_heading_data['default'];
					$file_heading_meta_fields = $file_heading_data['meta'];

					// taking current page form data.
					$mapping_form_data = ( isset( $this->selected_template_form_data['mapping_form_data'] ) ? $this->selected_template_form_data['mapping_form_data'] : array() );

					if ( isset( $_REQUEST['rerun_id'] ) && absint( $_REQUEST['rerun_id'] ) > 0 ) {
						$requested_cron_edit_id = ( isset( $_REQUEST['rerun_id'] ) ? absint( $_REQUEST['rerun_id'] ) : 0 );
						$cron_module_obj = Wt_Import_Export_For_Woo::load_modules( 'cron' );
						if ( ! is_null( $cron_module_obj ) ) {
							/* check the cron entry is for export and also has form_data */
							$cron_data = $cron_module_obj->get_cron_by_id( $requested_cron_edit_id );

							if ( empty( $cron_data ) ) {
								// take history data by import_id.
								$import_data = Wt_Import_Export_For_Woo_History::get_history_entry_by_id( $requested_cron_edit_id );
								// processing form data.
								$form_data = ( isset( $import_data['data'] ) ? maybe_unserialize( $import_data['data'] ) : array() );
							} else {
																			$form_data = maybe_unserialize( $cron_data['data'] );
							}

																	$mapping_form_data = $form_data['mapping_form_data'];
						}
					}

					/* formdata/template data of fields in mapping page */
					$form_data_mapping_fields = isset( $mapping_form_data['mapping_fields'] ) ? $mapping_form_data['mapping_fields'] : array();

					/**
					*   Default mapping page fields
					*   Format: 'field_key'=>array('title'=>'', 'description'=>'')
					*/
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
					 * @param string          $this->to_import    Import post type.
					 * @param array           $form_data_mapping_fields    Form Mapping fields.
					 */
					$mapping_fields = apply_filters( 'wt_iew_importer_alter_mapping_fields', $mapping_fields, $this->to_import, $form_data_mapping_fields );

					/* meta fields list */
					$this->get_mapping_enabled_fields();

					/* mapping enabled meta fields */
					$form_data_mapping_enabled_fields = ( isset( $mapping_form_data['mapping_enabled_fields'] ) ? $mapping_form_data['mapping_enabled_fields'] : array() );
				}
			}

			ob_start();
			$this->prepare_step_header_html();
			include_once dirname( plugin_dir_path( __FILE__ ) ) . '/views/import-mapping-page.php';
			$this->prepare_step_footer_html();
			return ob_get_clean();

		} else {
			return '';
		}
	}

	/**
	 *  Step 4 (Ajax sub function)
	 * Built in steps, Advanced settings page
	 */
	public function advanced_page() {
		$this->step = 'advanced';
		if ( '' != $this->to_import ) {
			$this->prepare_step_summary();
			$this->prepare_footer_button_list();

			// taking current page form data.
			$advanced_form_data = ( isset( $this->selected_template_form_data['advanced_form_data'] ) ? $this->selected_template_form_data['advanced_form_data'] : array() );

			if ( isset( $_REQUEST['rerun_id'] ) && absint( $_REQUEST['rerun_id'] ) > 0 ) {
				$requested_cron_edit_id = ( isset( $_REQUEST['rerun_id'] ) ? absint( $_REQUEST['rerun_id'] ) : 0 );
				$cron_module_obj = Wt_Import_Export_For_Woo::load_modules( 'cron' );
				if ( ! is_null( $cron_module_obj ) ) {
								/* check the cron entry is for export and also has form_data */
								$cron_data = $cron_module_obj->get_cron_by_id( $requested_cron_edit_id );
					if ( empty( $cron_data ) ) {
						// take history data by import_id.
						$import_data = Wt_Import_Export_For_Woo_History::get_history_entry_by_id( $requested_cron_edit_id );
						// processing form data.
						$form_data = ( isset( $import_data['data'] ) ? maybe_unserialize( $import_data['data'] ) : array() );
					} else {
						  $form_data = maybe_unserialize( $cron_data['data'] );
					}
								$advanced_form_data = $form_data['advanced_form_data'];
				}
			}

			$advanced_screen_fields = $this->import_obj->get_advanced_screen_fields( $advanced_form_data );

			ob_start();
			$this->prepare_step_header_html();
			include_once dirname( plugin_dir_path( __FILE__ ) ) . '/views/import-advanced-page.php';
			$this->prepare_step_footer_html();
			return ob_get_clean();

		} else {
			return '';
		}
	}

	/**
	 * Prepare description for mapping step fields
	 *
	 * @param string $key Taxonomy or Meta.
	 */
	protected function prepare_field_description( $key ) {
		$out = '';
		if ( false !== strpos( $key, 'tax:' ) ) {
			$column = trim( str_replace( 'tax:', '', $key ) );
			if ( 'pa_' !== substr( $column, 0, 3 ) ) {
				$out = __( 'Product taxonomies' );
			} else {
				$out = __( 'New taxonomy: ' ) . $column;
			}
		} elseif ( false !== strpos( $key, 'meta:' ) ) {
			$column = trim( str_replace( 'meta:', '', $key ) );
			$out = __( 'Custom Field: ' ) . $column;
		} elseif ( strpos( $key, 'attribute:' ) !== false ) {
			$column = trim( str_replace( 'attribute:', '', $key ) );
			if ( 'pa_' == substr( $column, 0, 3 ) ) {
				$out = __( 'Taxonomy attributes' );
			} else {
				$out = __( 'New attribute: ' ) . $column;
			}
		} elseif ( false !== strpos( $key, 'attribute_data:' ) ) {
			$column = trim( str_replace( 'attribute_data:', '', $key ) );
			$out = __( 'Attribute data: ' ) . $column;
		} elseif ( false !== strpos( $key, 'attribute_default:' ) ) {
			$column = trim( str_replace( 'attribute_default:', '', $key ) );
			$out = __( 'Attribute default value: ' ) . $column;
		}
		return $out;
	}

	/**
	 * Split default mapping fields and meta mapping fields
	 *
	 * @param array $arr Output.
	 */
	protected function process_file_heading_data( $arr ) {
				$decimal_columns = $this->import_obj->get_decimal_columns();

		$default = array();
		$meta = array();

		foreach ( $arr as $key => $v ) {
			if ( is_array( $v ) ) {
				foreach ( $v as $meta_key => $meta_value ) {
					$v[ $meta_key ] = $this->import_obj->format_decimal_columns( $meta_value, $meta_key, $decimal_columns );
				}
				$meta = array_merge( $meta, $v );
			} else {
						$v = $this->import_obj->format_decimal_columns( $v, $key, $decimal_columns );
				$default[ $key ] = $v;
			}
		}
		return array(
			'default' => $default,
			'meta' => $meta,
		);
	}

	/**
	 * Get template form data
	 *
	 * @param integer $id Template ID.
	 */
	protected function get_template_form_data( $id ) {
		$template_data = $this->get_mapping_template_by_id( $id );
		if ( $template_data ) {
			$decoded_form_data = Wt_Import_Export_For_Woo_Common_Helper::process_formdata( maybe_unserialize( $template_data['data'] ) );
			$this->selected_template_form_data = ( ! is_array( $decoded_form_data ) ? array() : $decoded_form_data );
		}
	}


	/**
	 * Taking mapping template by Name
	 *
	 * @param string $name Mapping Template name.
	 */
	protected function get_mapping_template_by_name( $name ) {
		global $wpdb;
		$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$template_tb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_mapping_template WHERE template_type=%s AND item_type=%s AND name=%s", array( 'import', $this->to_import, $name ) ), ARRAY_A );// @codingStandardsIgnoreLine.
	}


	/**
	 * Taking mapping template by ID
	 *
	 * @param integer $id Template ID.
	 */
	protected function get_mapping_template_by_id( $id ) {
		global $wpdb;
		$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$template_tb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_mapping_template WHERE template_type=%s AND item_type=%s AND id=%d", array( 'import', $this->to_import, $id ) ), ARRAY_A );// @codingStandardsIgnoreLine.
	}

	/**
	 * Taking all mapping templates
	 */
	protected function get_mapping_templates() {
		if ( '' == $this->to_import ) {
			return;
		}
		global $wpdb;
		$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$template_tb;
		$val = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wt_iew_mapping_template WHERE template_type='import' AND item_type=%s ORDER BY id DESC", $this->to_import ), ARRAY_A );// @codingStandardsIgnoreLine.

		// add a filter here for modules to alter the data.
		$this->mapping_templates = ( $val ? $val : array() );
	}


	/**
	 * Get meta field list for mapping page
	 */
	protected function get_mapping_enabled_fields() {
		$mapping_enabled_fields = array(
			'hidden_meta' => array( __( 'Hidden meta' ), 0 ),
			'meta' => array( __( 'Meta' ), 1 ),
		);
									/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a post
		 * collection request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $mapping_fields    Mapping fields.
		 * @param string          $this->to_import    Import post type.
		 * @param array           $form_data_mapping_fields    Form Mapping fields.
		 */
		$this->mapping_enabled_fields = apply_filters( 'wt_iew_importer_alter_mapping_enabled_fields', $mapping_enabled_fields, $this->to_import, array() );
	}
	/**
	 * Step summary html
	 */
	protected function prepare_step_summary() {
		 $step_info = $this->get_step_info( $this->step );
		$this->step_title = $step_info['title'];
		$this->step_keys = array_keys( $this->steps );
		$this->current_step_index = array_search( $this->step, $this->step_keys );
		$this->current_step_number = $this->current_step_index + 1;
		$this->last_page = ( ! isset( $this->step_keys[ $this->current_step_index + 1 ] ) ? true : false );
		$this->total_steps = count( $this->step_keys );
		/* translators: 1: current step number. 2: total steps */
		$this->step_summary = sprintf( __( 'Step %1$d of %2$d' ), $this->current_step_number, $this->total_steps );
		$this->step_description = $step_info['description'];
	}
	/**
	 * Footer step header html
	 */
	protected function prepare_step_header_html() {
		include dirname( plugin_dir_path( __FILE__ ) ) . '/views/import-header.php';
	}
	/**
	 * Footer step html
	 */
	protected function prepare_step_footer_html() {
		include dirname( plugin_dir_path( __FILE__ ) ) . '/views/import-footer.php';
	}
	/**
	 * Footer html
	 */
	protected function prepare_footer_button_list() {
		$out = array();
		$step_keys = $this->step_keys;
		$current_index = $this->current_step_index;
		$last_page = $this->last_page;
		if ( false !== $current_index ) {
			if ( $current_index > 0 ) {
				$out['back'] = array(
					'type' => 'button',
					'action_type' => 'step',
					'key' => $step_keys[ $current_index - 1 ],
					'text' => '<span class="dashicons dashicons-arrow-left-alt2" style="line-height:27px;"></span> ' . __( 'Back', 'import-export-suite-for-woocommerce' ),
				);
			}

			if ( isset( $step_keys[ $current_index + 1 ] ) ) {
				$next_number = $current_index + 2;
				$next_key = $step_keys[ $current_index + 1 ];
				$next_title = $this->steps[ $next_key ]['title'];
				$out['next'] = array(
					'type' => 'button',
					'action_type' => 'step',
					'key' => $next_key,
					'text' => __( 'Step' ) . ' ' . $next_number . ': ' . $next_title . ' <span class="dashicons dashicons-arrow-right-alt2" style="line-height:27px;"></span>',
				);

				if ( 'quick' == $this->import_method || 'template' == $this->import_method ) {
					$out['or'] = array(
						'type' => 'text',
						'text' => __( 'Or', 'import-export-suite-for-woocommerce' ),
					);
				}
			} else {
				$last_page = true;
			}

			if ( 'quick' == $this->import_method || 'template' == $this->import_method || $last_page ) {
				if ( $last_page && 'quick' != $this->import_method ) {
					if ( 'template' == $this->import_method ) {
						$out['save'] = array(
							'key' => 'save',
							'icon' => '',
							'type' => 'dropdown_button',
							'text' => __( 'Save template', 'import-export-suite-for-woocommerce' ),
							'items' => array(
								'update' => array(
									'key' => 'update_template',
									'text' => __( 'Save', 'import-export-suite-for-woocommerce' ),  // no prompt.
								),
								'save' => array(
									'key' => 'save_template_as',
									'text' => __( 'Save As', 'import-export-suite-for-woocommerce' ), // prompt for name.
								),
							),
						);
					} else {
						$out['save'] = array(
							'key' => 'save_template',
							'icon' => '',
							'type' => 'button',
							'text' => __( 'Save template', 'import-export-suite-for-woocommerce' ), // prompt for name.
						);
					}
				}
				$out['download'] = array(
					'key' => 'download', /* first step of import must be download the input file */
					'class' => 'iew_import_btn',
					'icon' => '',
					'type' => 'button',
					'text' => __( 'Import', 'import-export-suite-for-woocommerce' ),
				);
			}
		}
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
		$this->step_btns = apply_filters( 'wt_iew_importer_alter_footer_btns', $out, $this->step, $this->steps );
	}

	/**
	 *   Prepare default mapping data for quick import
	 *   After preparing update the Formdata in history table
	 *
	 * @param integer $import_id Import ID.
	 */
	protected function _prepare_for_quick( $import_id ) {
		// take history data by import_id.
		$import_data = Wt_Import_Export_For_Woo_History::get_history_entry_by_id( $import_id );

		// processing form data.
		$form_data = ( isset( $import_data['data'] ) ? maybe_unserialize( $import_data['data'] ) : array() );

		$ext_arr = explode( '.', $this->import_obj->temp_import_file );
		if ( strtolower( end( $ext_arr ) ) == 'xml' ) {
			include_once WT_IEW_PLUGIN_PATH . 'admin/classes/class-wt-import-export-for-woo-xmlreader.php';
			$reader = new Wt_Import_Export_For_Woo_Xmlreader();

		} else /* csv */
		{
			include_once WT_IEW_PLUGIN_PATH . 'admin/classes/class-wt-import-export-for-woo-csvreader.php';
			$delimiter = ( isset( $form_data['method_import_form_data']['wt_iew_delimiter'] ) ? ( $form_data['method_import_form_data']['wt_iew_delimiter'] ) : ',' );
			$reader = new Wt_Import_Export_For_Woo_Csvreader( $delimiter );
		}

		$file_path = $this->import_obj->get_file_path( $this->import_obj->temp_import_file );

		/* take first two rows in csv and in xml takes column keys and a sample data */
		$sample_data = $reader->get_sample_data( $file_path, true );

		$file_heading_data = $this->process_file_heading_data( $sample_data );
		$file_heading_default_fields = $file_heading_data['default'];
		$file_heading_meta_fields = $file_heading_data['meta'];

		$mapping_fields = array();
				/**
		*   Default mapping fields
			 *
		 * @since 1.0.0
		 *
		*   Format: 'field_key'=>array('title'=>'', 'description'=>'')
		*/
		$mapping_fields = apply_filters( 'wt_iew_importer_alter_mapping_fields', $mapping_fields, $this->to_import, array() );

				$array_keys_file_heading_default_fields = array_keys( $file_heading_default_fields );
		$mapping_form_data = array(
			'mapping_fields' => array(),
			'mapping_selected_fields' => array(),
		);
		$allowed_field_types = array( 'start_with', 'end_with', 'contain' );

		foreach ( $mapping_fields as $key => $val_arr ) {
			$val = '';
			$checked = 0; /* import this column? */
			$type = ( isset( $val_arr['type'] ) ? $val_arr['type'] : '' );
			// if(isset($file_heading_default_fields[$key])).
			$case_key = preg_grep( "/^$key$/i", $array_keys_file_heading_default_fields );
			if ( $case_key ) {
				$checked = 1;
				/**
				 *
				 * Import this column? */
				// $val='{'.$key.'}';.
					$val = '{' . array_shift( $case_key ) . '}';  // preg_grep give an array with actual index and value.
				unset( $file_heading_default_fields[ $key ] ); // remove the field from file heading list.
			} elseif ( isset( $file_heading_meta_fields[ $key ] ) ) {
				$checked = 1; /* import this column? */
				$val = '{' . $key . '}';
				unset( $file_heading_meta_fields[ $key ] ); // remove the field from file heading list.
			} else {
				$field_type = ( isset( $val_arr['field_type'] ) ? $val_arr['field_type'] : '' );
				if ( '' != $field_type && in_array( $field_type, $allowed_field_types ) ) {
					foreach ( $file_heading_default_fields as $def_key => $def_val ) {
						$matched = false;
						if ( 'start_with' == $field_type && strpos( $def_key, $key ) === 0 ) {
							$matched = true;
						} elseif ( 'ends_with' == $field_type && strrpos( $def_key, $key ) === ( strlen( $def_key ) - strlen( $key ) ) ) {
							$matched = true;
						} elseif ( 'contains' == $field_type && false !== strpos( $def_key, $key ) ) {
							$matched = true;
						}
						if ( $matched ) {
							$val = '{' . $def_key . '}';
							unset( $file_heading_default_fields[ $def_key ] ); // remove the field from file heading list.
							$mapping_form_data['mapping_selected_fields'][ $def_key ] = $val;
							$mapping_form_data['mapping_fields'][ $def_key ] = array( $val, 1, $type ); // value, enabled, type.
						}
					}
				} else /* unmatched keys */
				{
					$checked = 0;
					$val = '';
				}
			}
			if ( 1 == $checked ) {
				$mapping_form_data['mapping_selected_fields'][ $key ] = $val;
				$mapping_form_data['mapping_fields'][ $key ] = array( $val, 1, $type ); // value, enabled, type.
			}
		}

		/**
		*   Meta mapping fields
		*/
		$form_data_meta_mapping_fields = array(); // recheck the need of this variable in the below context.
		$meta_mapping_screen_fields = $this->_get_meta_mapping_screen_fields( $form_data_meta_mapping_fields );

		/* preparing meta fields. */
		$meta_mapping_form_data = array(
			'mapping_fields' => array(),
			'mapping_selected_fields' => array(),
		);
		if ( $meta_mapping_screen_fields && is_array( $meta_mapping_screen_fields ) ) {
			/* loop through mapping fields */
			foreach ( $meta_mapping_screen_fields as $meta_mapping_screen_field_key => $meta_mapping_screen_field_val ) {
				/* decalaring an empty array*/
				$temp_arr = array();
				$temp_fields_arr = array(); /* this is to store mapping field other details */

				/* default field list from post type module */
				$mapping_fields = ( ( isset( $meta_mapping_screen_field_val['fields'] ) && is_array( $meta_mapping_screen_field_val['fields'] ) ) ? $meta_mapping_screen_field_val['fields'] : array() );

				/* loop through mapping fields */
				if ( count( $mapping_fields ) > 0 ) {
					foreach ( $mapping_fields as $key => $val_arr ) {
						$val = '';
						$checked = 0; /* import this column? */
						$type = ( isset( $val_arr['type'] ) ? $val_arr['type'] : '' );

						if ( isset( $file_heading_meta_fields[ $key ] ) ) {
							$checked = 1; /* import this column? */
							$val = '{' . $key . '}';
							unset( $file_heading_meta_fields[ $key ] ); // remove the field from file heading list.
						}
						if ( 1 == $checked ) {
							$temp_arr[ $key ] = $val;
							$temp_fields_arr[ $key ] = array( $val, 1, $type );
						}
					}
				}
				if ( count( $file_heading_meta_fields ) > 0 ) {
					$current_field_group_prefix_arr = ( isset( $this->field_group_prefixes[ $meta_mapping_screen_field_key ] ) ? $this->field_group_prefixes[ $meta_mapping_screen_field_key ] : array() );
					foreach ( $file_heading_meta_fields as $key => $sample_val ) {
						$is_include = $this->_is_include_meta_in_this_group( $current_field_group_prefix_arr, $key );
						if ( 1 == $is_include ) {
							$val = '{' . $key . '}';
							$checked = 1; /* import this column? */
							unset( $file_heading_meta_fields[ $key ] ); // remove the field from file heading list.
							$temp_arr[ $key ] = $val;
							$temp_fields_arr[ $key ] = array( $val, 1, '' );
						}
					}
				}

				/* adding value to main array */
				$meta_mapping_form_data['mapping_selected_fields'][ $meta_mapping_screen_field_key ] = $temp_arr;
				$meta_mapping_form_data['mapping_fields'][ $meta_mapping_screen_field_key ] = $temp_fields_arr;
				$mapping_fields = null;
				$temp_arr = null;
				$temp_fields_arr = null;
				unset( $temp_arr, $temp_fields_arr, $mapping_fields );
			}

			/*
			If any columns that not in the above list
			if ( count( $file_heading_meta_fields ) > 0 ) {
				error_log( 'Has unmapped meta' );
			}
			 *
			 */
		}

		/**
		*    Update form data with prepared mapping form data.
		*/
		$form_data['mapping_form_data'] = $mapping_form_data;
		$form_data['meta_step_form_data'] = $meta_mapping_form_data;

		$update_data = array(
			'data' => maybe_serialize( $form_data ), // formadata.
		);
		$update_data_type = array(
			'%s',
		);

		Wt_Import_Export_For_Woo_History::update_history_entry( $import_id, $update_data, $update_data_type );

		$mapping_form_data = null;
		$meta_mapping_form_data = null;
		$form_data = null;
		unset( $mapping_form_data, $meta_mapping_form_data, $form_data );
	}
	/**
	 * Mapping screen fields for import
	 *
	 * @param array $form_data_meta_mapping_fields Fields.
	 * @return array
	 */
	protected function _get_meta_mapping_screen_fields( $form_data_meta_mapping_fields ) {
		$this->get_mapping_enabled_fields();
		$meta_mapping_screen_fields = array();
		foreach ( $this->mapping_enabled_fields as $field_key => $field_vl ) {
			$field_vl = ( ! is_array( $field_vl ) ? array( $field_vl, 0 ) : $field_vl );
			$meta_mapping_screen_fields[ $field_key ] = array(
				'title' => '',
				'checked' => $field_vl[1],
				'fields' => array(),
			);
		}

		/**
		*   Default mapping page fields.
			 *
		 * @since 1.0.0
		 *
		*   Format: 'field_key'=>array('title'=>'', 'description'=>'')
		*/
		return apply_filters( 'wt_iew_importer_alter_meta_mapping_fields', $meta_mapping_screen_fields, $this->to_import, $form_data_meta_mapping_fields );
	}
	/**
	 *  Is included in meta
	 *
	 * @param array  $current_field_group_prefix_arr Is included meta.
	 * @param string $key Current meta key in loop.
	 * @return int
	 */
	protected function _is_include_meta_in_this_group( $current_field_group_prefix_arr, $key ) {
		$is_include = 0;
		foreach ( $current_field_group_prefix_arr as $_prefix ) {
			if ( 0 === strpos( $key, $_prefix ) ) {
				if ( 'meta' == $_prefix ) {
					$name = str_replace( 'meta:', '', $key );
					if ( '_' != substr( $name, 0, 1 ) ) {
						if ( 0 !== strpos( $name, 'attribute' ) ) {
							$is_include = 1;
							break;
						}
					}
				} else {
					$is_include = 1;
					break;
				}
			}
		}
		return $is_include;
	}
}
