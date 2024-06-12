<?php
/**
 * Handles the user import.
 *
 * @package   ImportExportSuite\Admin\Modules\User\Import
 * @version   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Import_Export_For_Woo_User_Import Class.
 */
class Wt_Import_Export_For_Woo_User_Import {

	/**
	 * Parent module object
	 *
	 * @var object
	 */
	public $parent_module = null;
	/**
	 * Parsed data
	 *
	 * @var array
	 */
	public $parsed_data = array();
	/**
	 * Is merge
	 *
	 * @var boolean
	 */
	public $merge;
	/**
	 * Is skip
	 *
	 * @var boolean
	 */
	public $skip_new;
	/**
	 * Merge with
	 *
	 * @var string
	 */
	public $merge_with = 'email';
	/**
	 * Found action
	 *
	 * @var string
	 */
	public $found_action = 'skip';
	/**
	 * ID conflict
	 *
	 * @var string
	 */
	public $id_conflict = 'skip';
	/**
	 * Is Exist
	 *
	 * @var boolean
	 */
	public $is_user_exist = false;
	/**
	 * Results data
	 *
	 * @var array
	 */
	public $import_results = array();
	/**
	 * Parsed data
	 *
	 * @var array
	 */
	public $row;
	/**
	 * Parsed data
	 *
	 * @var array
	 */
	public $post_defaults;
	/**
	 * Post meta data
	 *
	 * @var array
	 */
	public $postmeta_defaults;
	/**
	 * Post meta data
	 *
	 * @var array
	 */
	public $postmeta_allowed;
	/**
	 * Skip guest users
	 *
	 * @var boolean
	 */
	public $skip_guest_user = false;
	/**
	 * Constructor.
	 *
	 * @param object $parent_object Parent module object.
	 * @since 1.0.0
	 */
	public function __construct( $parent_object ) {

		$this->parent_module    = $parent_object;
		$this->user_all_fields  = include plugin_dir_path( __FILE__ ) . '../data/data-user-columns.php';
		$this->user_base_fields = array_slice( $this->user_all_fields, 0, 13 );
		$this->user_meta_fields = array_slice( $this->user_all_fields, 13 );
		global $wpdb;
		$meta_keys = $wpdb->get_col( "SELECT distinct(meta_key) FROM $wpdb->usermeta LIMIT 2010" );

		foreach ( $meta_keys as $meta_key ) {
			$this->user_meta_fields[ $meta_key ] = $meta_key;
		}
	}//end __construct()

	/**
	 * Log  data change
	 *
	 * @param string $content Log hook.
	 * @param array  $data User data.
	 */
	public function hf_log_data_change( $content = 'review-csv-import', $data = '' ) {
		Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', $data );
	}//end hf_log_data_change()

	/**
	 * User import
	 *
	 * @param array   $import_data Import data.
	 * @param array   $form_data Form data.
	 * @param integer $batch_offset Offset.
	 * @param bool    $is_last_batch Is last offset.
	 * @return int
	 */
	public function prepare_data_to_import( $import_data, $form_data, $batch_offset, $is_last_batch ) {

		$this->skip_new          = isset( $form_data['advanced_form_data']['wt_iew_skip_new'] ) ? $form_data['advanced_form_data']['wt_iew_skip_new'] : 0;
		$this->merge_with        = ! empty( $form_data['advanced_form_data']['wt_iew_merge_with'] ) ? $form_data['advanced_form_data']['wt_iew_merge_with'] : 'email';
		$this->found_action      = ! empty( $form_data['advanced_form_data']['wt_iew_found_action'] ) ? $form_data['advanced_form_data']['wt_iew_found_action'] : 'skip';
		$this->use_same_password = isset( $form_data['advanced_form_data']['wt_iew_use_same_password'] ) ? $form_data['advanced_form_data']['wt_iew_use_same_password'] : 1;

		$this->send_mail = isset( $form_data['advanced_form_data']['wt_iew_send_mail'] ) ? $form_data['advanced_form_data']['wt_iew_send_mail'] : 0;
		$this->skip_guest_user = ! empty( $form_data['advanced_form_data']['wt_iew_skip_guest_user'] ) ? $form_data['advanced_form_data']['wt_iew_skip_guest_user'] : false;

		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );
		wp_suspend_cache_invalidation( true );

		Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', 'Preparing for import.' );

		$success = 0;
		$failed  = 0;
		$msg     = 'User imported successfully.';
		foreach ( $import_data as $key => $data ) {
			$row = ( $batch_offset + $key + 1 );

			Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', "Row :$row - Parsing item." );
			$parsed_data = $this->parse_users( $data );

			if ( ! is_wp_error( $parsed_data ) ) {
				Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', "Row :$row - Processing item." );

				$result = $this->process_users( $parsed_data, $data );

				if ( ! is_wp_error( $result ) ) {
					if ( $this->is_user_exist ) {
						$msg = 'User updated successfully.';
					}

					$this->import_results[ $row ] = array(
						'row'     => $row,
						'message' => $msg,
						'status'  => true,
						'post_id' => $result['id'],
					);
					Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', "Row :$row - " . $msg );
					$success++;
				} else {
					$this->import_results[ $row ] = array(
						'row'     => $row,
						'message' => $result->get_error_message(),
						'status'  => false,
						'post_id' => '',
					);
					Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', "Row :$row - Processing failed. Reason: " . $result->get_error_message() );
					$failed++;
				}//end if
			} else {
				$this->import_results[ $row ] = array(
					'row'     => $row,
					'message' => $parsed_data->get_error_message(),
					'status'  => false,
					'post_id' => '',
				);
				Wt_Import_Export_For_Woo_Logwriter::write_log( $this->parent_module->module_base, 'import', "Row :$row - Parsing failed. Reason: " . $parsed_data->get_error_message() );
				$failed++;
			}//end if

			unset( $data, $parsed_data );
		}//end foreach

		wp_suspend_cache_invalidation( false );
		wp_defer_term_counting( false );
		wp_defer_comment_counting( false );

		$import_response = array(
			'total_success' => $success,
			'total_failed'  => $failed,
			'log_data'      => $this->import_results,
		);

		return $import_response;
	}//end prepare_data_to_import()


	/**
	 * Parse the data.
	 *
	 * @param array $data value.
	 * @throws Exception User already exist.
	 * @return array
	 */
	public function parse_users( $data ) {
		try {
			/**
			 * Filter the query arguments for a request.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param array           $data    User CSV data.
			 */
			$data = apply_filters( 'wt_user_importer_pre_parse_data', $data );

			if ( $this->skip_guest_user && ! empty( $data['mapping_fields']['is_geuest_user'] ) ) {
				$email = '';
				if ( isset( $data['mapping_fields']['user_email'] ) && ! empty( $data['mapping_fields']['user_email'] ) ) {
					$email = 'Email : ' . $data['mapping_fields']['user_email'];
				} elseif ( isset( $data['mapping_fields']['billing_email'] ) && ! empty( $data['mapping_fields']['billing_email'] ) ) {
					$email = 'Email : ' . $data['mapping_fields']['billing_email'];
				}
				throw new Exception( sprintf( 'Guest user skipped %s', $email ) );
			}
			$item = $data['mapping_fields'];
			foreach ( $data['meta_mapping_fields'] as $value ) {
				$item = array_merge( $item, $value );
			}

			global $wpdb;

			$this->is_user_exist = false;
			$this->merge         = false;

			$id = isset( $item['ID'] ) && ! empty( $item['ID'] ) ? absint( $item['ID'] ) : 0;
			$user_id = isset( $item['ID'] ) && ! empty( $item['ID'] ) ? absint( $item['ID'] ) : 0;
			$id_found_with_id = '';
			if ( $id && 'id' == $this->merge_with ) {
				$id_found_with_id = get_userdata( $id );
				if ( $id_found_with_id ) {
					$this->is_user_exist = true;
					$user_id = $id;
				}
			}

			$email = isset( $item['user_email'] ) && '' != $item['user_email'] ? trim( $item['user_email'] ) : '';
			$id_found_with_email = '';
			if ( ! empty( $email ) && 'email' == $this->merge_with ) {
				$check_user_email_exist = email_exists( $email );
				if ( is_email( $email ) && false !== $check_user_email_exist ) {
					$id_found_with_email = $check_user_email_exist;
					$this->is_user_exist = true;
					$user_id = $id_found_with_email;
				}
			}

			$username = isset( $item['user_login'] ) && '' != $item['user_login'] ? trim( $item['user_login'] ) : '';
			$id_found_with_username = '';
			if ( ! empty( $username ) && 'username' == $this->merge_with ) {
				$check_user_name_exist = username_exists( $username );
				if ( false !== $check_user_name_exist ) {
					$id_found_with_username = $check_user_name_exist;
					$this->is_user_exist    = true;
					$user_id = $id_found_with_username;
				}
			}

			if ( $this->is_user_exist ) {
				if ( 'skip' == $this->found_action ) {
					if ( $id && $id_found_with_id ) {
						throw new Exception( sprintf( 'User with same ID already exists. ID: %d', absint( $id ) ) );
					} else if ( $email && $id_found_with_email ) {
						throw new Exception( sprintf( 'User with same Email already exists. Email: %s', esc_html( $email ) ) );
					} else if ( $username && $id_found_with_username ) {
						throw new Exception( sprintf( 'User with same Username already exists. Username: %s', esc_html( $username ) ) );
					} else {
						throw new Exception( 'User already exists.' );
					}
				} else if ( 'update' == $this->found_action ) {
					$this->merge = true;
				}
			}

			if ( ! $this->is_user_exist && $this->skip_new ) {
				throw new Exception( 'Skipping new item' );
			}

			if ( ! $this->is_user_exist ) {
				/**
				 * Filter the query arguments for a request.
				 *
				 * Enables adding extra arguments or setting defaults for the request.
				 *
				 * @since 1.0.0
				 *
				 * @param boolean           false    Allow create user without email.
				 */
				$create_user_without_email = apply_filters( 'wt_create_user_without_email', false );
				// create user without email address.
				if ( empty( $email ) && false === $create_user_without_email ) {
									$this->hf_log_data_change( 'user-csv-import', __( '> skipped: cannot insert user without email.' ) );
								unset( $item );
								return new WP_Error( 'parse-error', __( '> skipped: cannot insert user without email.' ) );
				} else if ( ! is_email( $email ) && false === $create_user_without_email ) {
									$this->hf_log_data_change( 'user-csv-import', /* translators:%s: user email */ sprintf( __( '> skipped: Email is not valid. %s' ), esc_html( $item['user_email'] ) ) );
								unset( $item );
								return new WP_Error( 'parse-error', __( '>  skipped: Email is not valid.' ) );
				}
			}

			$user_meta = array();
			$user_details = array();

			$user_details['ID'] = $user_id;

			foreach ( $this->user_base_fields as $key => $value ) {
				if ( in_array( $key, array( 'ID' ) ) ) {
					// ID alreay set.
					continue;
				}

				if ( isset( $item[ $value ] ) ) {
					$user_details[ $key ] = trim( $item[ $value ] );
				}
			}

			// adding metas from csv to user meta fields.
			foreach ( $item as $key => $value ) {
				if ( strstr( $key, 'meta:' ) ) {
					$key_val = $key;
					$key     = trim( str_replace( 'meta:', '', $key ) );

					$this->user_meta_fields[ $key ] = $key_val;
				}
			}

			foreach ( $this->user_meta_fields as $key => $value ) {
				$mkey = 'meta:' . $key; // Add to process meta list only if its mapped.
				if ( 'wc_last_active' == $key || 'last_update' == $key ) {
					$date = isset( $item[ $value ] ) ? trim( $item[ $value ] ) : '';
					if ( is_string( $date ) ) {
						$date = strtotime( $date );
					}
					$item[ $value ] = $date;
				}
				$user_meta[] = array(
					'key'   => $key,
					'value' => isset( $item[ $value ] ) ? $item[ $value ] : '',
				);
			}

			// the $user_details array will now contain the necessary name-value pairs for the wp_users table, and also any meta data in the 'usermeta' array.
			$parsed_details = array();

			$parsed_details['user_details'] = $user_details;
			$parsed_details['user_meta']    = $user_meta;

			return $parsed_details;
		} catch ( Exception $e ) {
			return new WP_Error( 'woocommerce_product_importer_error', $e->getMessage(), array( 'status' => $e->getCode() ) );
		}//end try
	}//end parse_users()


	/**
	 * Process item
	 *
	 * @global object $wpdb
	 * @param array $post User data.
	 * @param array $parsed_item Parsed data.
	 * @return \WP_Error
	 */
	private function process_users( $post, $parsed_item ) {
		try {
			global $wpdb;

			$this->hf_log_data_change( 'user-csv-import', __( 'Processing users.' ) );

			$user_id = ! empty( $post['user_details']['ID'] ) && $post['user_details']['ID'] ? $post['user_details']['ID'] : 0;

			// user exists when importing &  merge not ticked.
			$new_added = false;
			if ( $user_id && $this->merge ) {
				$current_user = get_current_user_id();
				if ( $current_user == $user_id ) {
					$usr_msg = 'This user is currently logged in hence we cannot update.';
					$this->hf_log_data_change( 'user-csv-import', /* translators:%s: user id */ sprintf( __( '> &#8220;%s&#8221; This user is currently logged in hence we cannot update.' ), $user_id ), true );
					unset( $post );
					return new WP_Error( 'parse-error', /* translators:%s: user id */ sprintf( __( '> &#8220;%s&#8221; This user is currently logged in hence we cannot update.' ), $user_id ) );
				}

				$user_id = $this->hf_update_customer( $user_id, $post, $this->send_mail );
			} else {
				$user_id   = $this->hf_create_customer( $post, $this->send_mail );
				$new_added = true;
				if ( is_wp_error( $user_id ) ) {
					$this->hf_log_data_change( 'user-csv-import', /* translators: 1: number. 2: insert error */ sprintf( __( '> Error inserting %1$s: %2$s' ), 1, $user_id->get_error_message() ), true );
					unset( $post );
						return new WP_Error( 'parse-error', /* translators: 1: number. 2: insert error */ sprintf( __( '> Error inserting %1$s: %2$s' ), 1, $user_id->get_error_message() ) );
				} else if ( empty( $user_id ) ) {
					unset( $post );
					return new WP_Error( 'parse-error', __( 'An error occurred with the customer information provided.' ) );
				}
			}//end if

			if ( $this->merge && ! $new_added ) {
				$out_msg = "User updated successfully. ID:$user_id";
			} else {
				$out_msg = "User imported successfully. ID:$user_id";
			}

			$this->hf_log_data_change( 'user-csv-import', /* translators: 1: user id. 2: success message */ sprintf( __( '> &#8220;%1$s&#8221; %2$s' ), $user_id, $out_msg ), true );

			$this->hf_log_data_change( 'user-csv-import', /* translators:%s: user id */ sprintf( __( '> Finished importing user %s' ), $user_id ) );
			/**
			* Filter the query arguments for a request.
			*
			* Enables adding extra arguments or setting defaults for the request.
			*
			* @since 1.0.0
			*
			* @param array   $parsed_item    CSV user row data.
			* @param int $user_id User id.
			*/
			do_action( 'wt_customer_csv_import_data', $parsed_item, $user_id );
			unset( $post );
			$result = array(
				'id'      => $user_id,
				'updated' => $this->merge,
			);
			return $result;
		} catch ( Exception $e ) {
			return new WP_Error( 'woocommerce_product_importer_error', $e->getMessage(), array( 'status' => $e->getCode() ) );
		}//end try
	}//end process_users()

	/**
	 * Create customer
	 *
	 * @global object $wpdb
	 * @param array   $data User data.
	 * @param boolean $email_customer Is send Mail.
	 * @return type
	 */
	public function hf_create_customer( $data, $email_customer ) {
		$meta_array = array();
		$customer_email = ( ! empty( $data['user_details']['user_email'] ) ) ? $data['user_details']['user_email'] : '';
		$username       = ( ! empty( $data['user_details']['user_login'] ) ) ? $data['user_details']['user_login'] : '';
		$customer_id    = ( ! empty( $data['user_details']['customer_id'] ) ) ? $data['user_details']['customer_id'] : '';
		$insertion_id   = ( ! empty( $data['user_details']['ID'] ) ) ? $data['user_details']['ID'] : '';
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param boolean           false    Allow import empty value for a field.
		 */
		$merge_empty_cells = apply_filters( 'wt_user_import_empty_csv_column', false );

		if ( 'email' == $this->merge_with ) {
			$insertion_id = '';
		}
		if ( ! empty( $data['user_details']['user_pass'] ) ) {
			$is_wordpress_password = false;
			if ( 34 === strlen( $data['user_details']['user_pass'] ) && 0 === strpos( $data['user_details']['user_pass'], '$P$B' ) ) {
				$is_wordpress_password = true;
			}
			$password = ( $is_wordpress_password ) ? $data['user_details']['user_pass'] : wp_hash_password( str_replace( "'", "\'", $data['user_details']['user_pass'] ) );
			$password_generated = false;
		} else {
			$password           = wp_generate_password( 12, true );
			$password_generated = true;
		}

		$found_customer            = false;
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param boolean           false    Allow create user without email.
		 */
		$create_user_without_email = apply_filters( 'wt_create_user_without_email', false );
		// create user without email address.
		if ( is_email( $customer_email ) || true === $create_user_without_email ) {
			$maybe_username = $username;
			// Not in test mode, create a user account for this email.
			if ( empty( $username ) ) {
				$maybe_username = explode( '@', $customer_email );
				$maybe_username = sanitize_user( $maybe_username[0] );
			}

			$counter  = 1;
			$username = $maybe_username;
			while ( username_exists( $username ) ) {
				$username = $maybe_username . $counter;
				$counter++;
			}

			if ( ! $this->is_user_exist && $insertion_id ) {
				global $wpdb;
				$insertion_id   = (int) $insertion_id;
				$username       = sanitize_user( $username );
				$customer_email = sanitize_email( $customer_email );
				if ( $password_generated ) {
					$password = wp_hash_password( $password );
				}

				$result = $wpdb->insert(
					$wpdb->users,
					array(
						'ID' => $insertion_id,
						'user_login' => $username,
						'user_email' => $customer_email,
						'user_pass' => $password,
					)
				);
				if ( $result ) {
					$found_customer = $insertion_id;
				}
			}

			if ( ! $found_customer ) {
				$found_customer = wp_create_user( $username, $password, $customer_email );
			}

			$user_data = array(
				'ID'         => $found_customer,
				'user_login' => $username,
				'user_email' => $customer_email,
			);
			if ( ! $password_generated ) {
				$user_data['user_pass'] = $password;
			}

			if ( ! is_wp_error( $found_customer ) ) {
				wp_insert_user( $user_data );
				$wp_user_object = new WP_User( $found_customer );
				if ( ! function_exists( 'get_editable_roles' ) ) {
					include_once ABSPATH . 'wp-admin/includes/user.php';
				}

				$roles         = get_editable_roles();
				$new_roles_str   = isset( $data['user_details']['roles'] ) ? $data['user_details']['roles'] : '';

				if ( empty( $new_roles_str ) ) {
					$new_roles_str = get_option( 'default_role' );
				}

				$new_roles       = explode( ',', $new_roles_str );
				$new_roles       = array_map( 'trim', explode( ',', $new_roles_str ) );
				$roles_to_remove = array();
				$user_roles      = array_intersect( array_values( $wp_user_object->roles ), array_keys( $roles ) );
				if ( ! $new_roles ) {
					// If there are no roles, delete all of the user's roles.
					$roles_to_remove = $user_roles;
				} else {
					$roles_to_remove = array_diff( $user_roles, $new_roles );
				}

				array_push( $roles_to_remove, get_option( 'default_role' ) );
				if ( ! empty( $new_roles ) ) {
					foreach ( $roles_to_remove as $_role ) {
						$wp_user_object->remove_role( $_role );
						// remove the default role before adding new roles.
					}

					// Make sure that we don't call $wp_user_object->add_role() any more than it's necessary.
					$_new_roles = array_diff( $new_roles, array_intersect( array_values( $wp_user_object->roles ), array_keys( $roles ) ) );
					foreach ( $_new_roles as $_role ) {
						$wp_user_object->add_role( $_role );
					}
				}

				$meta_array = array();
				foreach ( $data['user_meta'] as $meta ) {
					$meta_array[ $meta['key'] ] = $meta['value'];
				}

				$user_nicename   = ( ! empty( $data['user_details']['user_nicename'] ) ) ? $data['user_details']['user_nicename'] : '';
				$website         = ( ! empty( $data['user_details']['user_url'] ) ) ? $data['user_details']['user_url'] : '';
				$user_registered = ( ! empty( $data['user_details']['user_registered'] ) ) ? $data['user_details']['user_registered'] : '';
				$display_name    = ( ! empty( $data['user_details']['display_name'] ) ) ? $data['user_details']['display_name'] : '';
				$first_name      = ( ! empty( $data['user_details']['first_name'] ) ) ? $data['user_details']['first_name'] : '';
				$last_name       = ( ! empty( $data['user_details']['last_name'] ) ) ? $data['user_details']['last_name'] : '';
				$user_status     = ( ! empty( $data['user_details']['user_status'] ) ) ? $data['user_details']['user_status'] : '';
				wp_update_user(
					array(
						'ID'              => $found_customer,
						'user_nicename'   => $user_nicename,
						'user_url'        => $website,
						'user_registered' => $user_registered,
						'display_name'    => $display_name,
						'first_name'      => $first_name,
						'last_name'       => $last_name,
						'user_status'     => $user_status,
					)
				);
				global $wpdb;
				$final_user_meta_string = '';
				foreach ( $this->user_meta_fields as $key => $meta ) {
					if ( ( $key == $wpdb->prefix . 'capabilities' ) || ( in_array( $key, $this->user_base_fields ) ) ) {
						continue;
					}

					$key = trim( str_replace( 'meta:', '', $key ) );
					$meta_value = ( ! empty( $meta_array[ $key ] ) ) ? maybe_unserialize( $meta_array[ $key ] ) : '';
					if ( $key == $wpdb->prefix . 'user_level' && '' == $meta_value ) {
						$meta_value = 0;
					}

					if ( 'session_tokens' == $key ) {
						if ( ! empty( $meta_array[ $key ] ) ) {
							$session_json = base64_decode( $meta_array[ $key ] );
							$meta_value   = json_decode( $session_json, true );
						}
					}

					if ( empty( $meta_value ) && ! $merge_empty_cells ) {
						continue;
					}

					update_user_meta( $found_customer, $key, $meta_value );
				}//end foreach

				// send user registration email if admin as chosen to do so.
				if ( $email_customer && function_exists( 'wp_new_user_notification' ) ) {
					$blog_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
					$to        = $customer_email;
					/* translators:%s: blog name */
					$subject   = sprintf( __( 'Your %s account has been created!' ), $blog_name );
					if ( 34 == strlen( $password ) && $is_wordpress_password ) {
						/* translators: 1: first_name. 2:blog_name. 3: username. 4:blog_name */
						$body = sprintf( __( 'Hi %1$s, Thanks for creating an account on %2$s. Your username is %3$s. You can access your account area to view orders, change your password. We look forward to seeing you soon.%4$s' ), $first_name, $blog_name, $username, $blog_name );
					} else if ( ! empty( $data['user_details']['user_pass'] ) && ! $is_wordpress_password ) {
						$new_password = $data['user_details']['user_pass'];
						/* translators: 1: first_name. 2:blog_name. 3: username. 4:new_password. 5:blog_name */
						$body         = sprintf( __( 'Hi %1$s,Thanks for creating an account on %2$s. Your username is %3$s. You can access your account area to view orders, change your password.Your password has been generated: %4$s We look forward to seeing you soon.%5$s' ), $first_name, $blog_name, $username, $new_password, $blog_name );
					} else {
						/* translators: 1: first_name. 2:blog_name. 3: username. 4:password. 5:blog_name */
						$body = sprintf( __( 'Hi %1$s,Thanks for creating an account on %2$s. Your username is %3$s. You can access your account area to view orders, change your password.Your password has been generated: %4$s We look forward to seeing you soon.%5$s' ), $first_name, $blog_name, $username, $password, $blog_name );
					}//end if

					$headers = '';
					$mail_attrs['to']       = $to;
					$mail_attrs['subject']  = $subject;
					$mail_attrs['body']     = $body;
					$mail_attrs['headers']  = $headers;
					$mail_attrs['password'] = ( isset( $new_password ) ) ? $new_password : $password;
					$mail_attrs['user_current_roles'] = $user_roles;
					$mail_attrs['user_new_roles']     = $new_roles;
					$mail_attrs['user_id']            = $found_customer;
					$mail_attrs['mail_already_sent']  = false;
					$mail_template = get_option( 'wt_' . WT_CUSTOMER_IMP_EXP_ID . '_mail_option', null );
					if ( isset( $mail_template['enable_email_templates'] ) && 1 == $mail_template['enable_email_templates'] ) {
						$fetch_user            = get_user_by( 'email', $mail_attrs['to'] );
						$mail_attrs['headers'] = 'Content-Type: text/html; charset=UTF-8';
						$mail_attrs['subject'] = $mail_template['subject_mail'];
						$blog_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
						$mail_body = $mail_template['body_mail'];
						$mail_body = str_replace( '###EMAIL###', $mail_attrs['to'], $mail_body );
						$mail_body = str_replace( '###USERNAME###', $fetch_user->user_login, $mail_body );
						$mail_body = str_replace( '###DISPLAYNAME###', $fetch_user->display_name, $mail_body );
						$mail_body          = str_replace( '###PASSWORD###', $mail_attrs['password'], $mail_body );
						$mail_body          = str_replace( '###SITENAME###', $blog_name, $mail_body );
						$mail_body          = str_replace( '###SITEURL###', home_url(), $mail_body );
						$mail_body          = str_replace( '###LOGINURL###', wp_login_url(), $mail_body );
						$mail_body          = str_replace( '###LOSTPASSWORDURL###', wp_lostpassword_url(), $mail_body );
						$mail_attrs['body'] = $mail_body;
					}
					/**
					 * Filter the query arguments for a request.
					 *
					 * Enables adding extra arguments or setting defaults for the request.
					 *
					 * @since 1.0.0
					 *
					 * @param array   $mail_attrs   Email details.
					 */
					$mail_attrs = apply_filters( 'wt_user_registration_email', $mail_attrs );
					// this filter can use for alter mail as well as send mail with other third party plugins.
					if ( ! isset( $mail_attrs['mail_already_sent'] ) || false == $mail_attrs['mail_already_sent'] ) {
						// this parameter used for determine whether the email already sent or not by any Custom mail.
						wp_mail( $mail_attrs['to'], $mail_attrs['subject'], $mail_attrs['body'], $mail_attrs['headers'] );
					}
				}//end if
			}//end if
		} else {
			$found_customer = new WP_Error( 'hf_invalid_customer', sprintf( __( 'User could not be created without Email.' ), $customer_id ) );
		}//end if
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param int           $found_customer    Found user id.
		 * @param string        $this->user_meta_fields    Meta fields.
		 * @param string        $meta_array    Metadata.
		 */
		return apply_filters( 'wt_user_impexp_alter_user_meta', $found_customer, $this->user_meta_fields, $meta_array );
	}//end hf_create_customer()

	/**
	 * Update customer
	 *
	 * @global object $wpdb
	 * @param WP_Error $found_customer Customer exist.
	 * @param array    $data User data.
	 * @param type     $email_customer Is send mail.
	 * @return integer
	 */
	public function hf_update_customer( $found_customer, $data, $email_customer ) {
		$meta_array = array();
		if ( $found_customer ) {
			$wp_user_object  = new WP_User( $found_customer );
			$roles           = get_editable_roles();

			$new_roles_str   = isset( $data['user_details']['roles'] ) ? $data['user_details']['roles'] : '';
			$new_roles       = array_map( 'trim', explode( ',', $new_roles_str ) );
			$new_roles       = array_intersect( $new_roles, array_keys( $roles ) );
			$roles_to_remove = array();
			$user_roles      = array_intersect( array_values( $wp_user_object->roles ), array_keys( $roles ) );
			/**
			 * Filter the query arguments for a request.
			 *
			 * Enables adding extra arguments or setting defaults for the request.
			 *
			 * @since 1.0.0
			 *
			 * @param boolean           false    Allow import empty value for a field.
			 */
			$merge_empty_cells = apply_filters( 'wt_user_import_empty_csv_column', false );

			if ( ! $new_roles ) {
				// If there are no roles, delete all of the user's roles.
				$roles_to_remove = $user_roles;
			} else {
				$roles_to_remove = array_diff( $user_roles, $new_roles );
			}

			if ( ! empty( $new_roles ) ) {
				foreach ( $roles_to_remove as $_role ) {
					$wp_user_object->remove_role( $_role );
					// remove the default role before adding new roles.
				}

				// Make sure that we don't call $wp_user_object->add_role() any more than it's necessary.
				$_new_roles = array_diff( $new_roles, array_intersect( array_values( $wp_user_object->roles ), array_keys( $roles ) ) );
				foreach ( $_new_roles as $_role ) {
					$wp_user_object->add_role( $_role );
				}
			}

			foreach ( $data['user_meta'] as $meta ) {
				$meta_array[ $meta['key'] ] = $meta['value'];
			}

			// update user meta data.
			global $wpdb;

			foreach ( $this->user_meta_fields as $key => $meta ) {
				if ( ( $key == $wpdb->prefix . 'capabilities' ) || ( in_array( $key, $this->user_base_fields ) ) ) {
					continue;
				}

				$key = trim( str_replace( 'meta:', '', $key ) );
				$meta_value = ( ! empty( $meta_array[ $key ] ) ) ? maybe_unserialize( $meta_array[ $key ] ) : '';
				if ( $key == $wpdb->prefix . 'user_level' && '' == $meta_value ) {
					$meta_value = 0;
				}

				if ( empty( $meta_value ) && ! $merge_empty_cells ) {
					continue;
				}

				update_user_meta( $found_customer, $key, $meta_value );
			}

			$user_data = array( 'ID' => $found_customer );
			if ( isset( $data['user_details']['user_nicename'] ) ) {
				$user_data['user_nicename'] = $data['user_details']['user_nicename'];
			}

			$email_updated = false;
			// added when implement merge with user id use for update email.
			if ( isset( $data['user_details']['user_email'] ) && ! empty( $data['user_details']['user_email'] ) ) {
				if ( $wp_user_object->user_email != $data['user_details']['user_email'] ) {
					$email_updated  = true;
					$customer_email = $data['user_details']['user_email'];
				}

				$user_data['user_email'] = $data['user_details']['user_email'];
			}

			if ( isset( $data['user_details']['user_url'] ) ) {
				$user_data['user_url'] = $data['user_details']['user_url'];
			}

			if ( isset( $data['user_details']['user_registered'] ) ) {
				$user_data['user_registered'] = $data['user_details']['user_registered'];
			}

			if ( isset( $data['user_details']['user_pass'] ) ) {
				$user_data['user_pass'] = str_replace( "'", "\'", $data['user_details']['user_pass'] );
			}

			if ( isset( $data['user_details']['display_name'] ) ) {
				$user_data['display_name'] = $data['user_details']['display_name'];
			}

			if ( isset( $data['user_details']['first_name'] ) ) {
				$user_data['first_name'] = $data['user_details']['first_name'];
			}

			if ( isset( $data['user_details']['last_name'] ) ) {
				$user_data['last_name'] = $data['user_details']['last_name'];
			}

			if ( isset( $data['user_details']['user_status'] ) ) {
				$user_data['user_status'] = $data['user_details']['user_status'];
			}

			add_filter( 'send_password_change_email', '__return_false' );
			// for preventing sending password change notification mail on by wp_update_user.
			if ( count( $user_data ) > 1 ) {
				wp_update_user( $user_data );
			}

			$is_wordpress_password = false;
			if ( 34 === strlen( $data['user_details']['user_pass'] ) && 0 === strpos( $data['user_details']['user_pass'], '$P$B' ) ) {
				$is_wordpress_password = true;
			}
			if ( $is_wordpress_password && isset( $data['user_details']['user_pass'] ) && ! empty( $data['user_details']['user_pass'] ) ) {
				$password = $data['user_details']['user_pass'];
				$user_data = array_merge(
					$user_data,
					array(
						'user_login' => $wp_user_object->user_login,
						'user_email' => ( $email_updated ) ? $customer_email : $wp_user_object->user_email,
					)
				);
				$user_data['user_pass'] = $password;
				wp_insert_user( $user_data );
			}

			// send user registration email if admin as chosen to do so.
			if ( $email_customer && function_exists( 'wp_new_user_notification' ) && isset( $user_data['user_pass'] ) ) {
				// added when implement merge with user id use to get to email id.
				$mail_sent_email = ( $email_updated ) ? $customer_email : $wp_user_object->user_email;
				unset( $email_updated );
				$blog_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
				// translators: Do not translate USERNAME, ADMIN_EMAIL, EMAIL, SITENAME, SITEURL: those are placeholders.
				$pass_change_text      = __(
					'Hi ###USERNAME###,

            This notice confirms that your password was changed on ###SITENAME### to ###PASSWORD###.


            This email has been sent to ###EMAIL###

            Regards,
            All at ###SITENAME###
            ###SITEURL###'
				);
				$mail_attrs            = array(
					'to'      => $mail_sent_email,
					// translators: User password change notification email subject. 1: Site name.
					'subject' => __( '[%s] Notice of Password Change' ),
					'message' => $pass_change_text,
					'headers' => '',
				);
				$mail_attrs['message'] = str_replace( '###USERNAME###', $wp_user_object->user_login, $mail_attrs['message'] );
				$mail_attrs['message'] = str_replace( '###EMAIL###', $wp_user_object->user_email, $mail_attrs['message'] );
				$mail_attrs['message'] = str_replace( '###SITENAME###', $blog_name, $mail_attrs['message'] );
				$mail_attrs['message'] = str_replace( '###SITEURL###', home_url(), $mail_attrs['message'] );
				$mail_attrs['message'] = str_replace( '###PASSWORD###', $user_data['user_pass'], $mail_attrs['message'] );
				$mail_attrs['user_current_roles'] = $user_roles;
				$mail_attrs['user_new_roles']     = $new_roles;
				$mail_attrs['user_id']            = $found_customer;
				$mail_attrs['password']           = $user_data['user_pass'];
				$mail_attrs['mail_already_sent']  = false;
				$mail_template = get_option( 'wt_' . WT_CUSTOMER_IMP_EXP_ID . '_mail_option', null );
				if ( 1 == $mail_template['enable_update_mail_template'] ) {
					$mail_attrs['headers'] = 'Content-Type: text/html; charset=UTF-8';
					$mail_attrs['subject'] = $mail_template['subject_mail_update'];
					$mail_body_update      = $mail_template['body_mail_update'];
					$mail_body_update      = str_replace( '###EMAIL###', $wp_user_object->user_email, $mail_body_update );
					$mail_body_update      = str_replace( '###USERNAME###', $wp_user_object->user_login, $mail_body_update );
					$mail_body_update      = str_replace( '###DISPLAYNAME###', $wp_user_object->display_name, $mail_body_update );
					// $mail_body = str_replace( "###NICENAME###", $fetch_user->user_nicename , $mail_body_update );.
					$mail_body_update = str_replace( '###PASSWORD###', $mail_attrs['password'], $mail_body_update );
					$mail_body_update = str_replace( '###SITENAME###', $blog_name, $mail_body_update );
					$mail_body_update = str_replace( '###SITEURL###', home_url(), $mail_body_update );
					$mail_body_update = str_replace( '###LOGINURL###', wp_login_url(), $mail_body_update );
					$mail_body_update = str_replace( '###LOSTPASSWORDURL###', wp_lostpassword_url(), $mail_body_update );

					$mail_attrs['message'] = $mail_body_update;
				}
				/**
				 * Filter the query arguments for a request.
				 *
				 * Enables adding extra arguments or setting defaults for the request.
				 *
				 * @since 1.0.0
				 *
				 * @param array   $mail_attrs   Email details.
				 */
				$mail_attrs = apply_filters( 'wt_user_password_change_email', $mail_attrs );
				// this filter can use for alter mail as well as send mail with other third party plugins.
				if ( ! isset( $mail_attrs['mail_already_sent'] ) || false == $mail_attrs['mail_already_sent'] ) {
					// this parameter used for determine whether the email already sent or not by any Custom mail.
					wp_mail( $mail_attrs['to'], sprintf( $mail_attrs['subject'], $blog_name ), $mail_attrs['message'], $mail_attrs['headers'] );
				}
			}//end if
		} else {
			$found_customer = new WP_Error( 'hf_invalid_customer', sprintf( __( 'User could not be found with given Email or username.' ), $customer_email ) );
		}//end if
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param int           $found_customer    Found user id.
		 * @param string        $this->user_meta_fields    Meta fields.
		 * @param string        $meta_array    Metadata.
		 */
		return apply_filters( 'wt_user_impexp_alter_user_meta', $found_customer, $this->user_meta_fields, $meta_array );
	}//end hf_update_customer()
}//end class
