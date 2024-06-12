<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-field-to-field-sync
 * @subpackage hubspot-field-to-field-sync/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    hubspot-field-to-field-sync
 * @subpackage hubspot-field-to-field-sync/admin
 * @author     MakeWebBetter <webmaster@makewebbetter.com>
 */
class Hubspot_Field_To_Field_Sync_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->hubwoo_ftf_admin_actions();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Hubspot_Field_To_Field_Sync_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Hubspot_Field_To_Field_Sync_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$screen = get_current_screen();

		if( isset( $screen->id ) && $screen->id == "woocommerce_page_hubwoo_field_to_field" ) {

			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/hubspot-field-to-field-sync-admin.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Hubspot_Field_To_Field_Sync_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Hubspot_Field_To_Field_Sync_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$screen = get_current_screen();

		if( isset( $screen->id ) && $screen->id == "woocommerce_page_hubwoo_field_to_field" ) {

			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/hubspot-field-to-field-sync-admin.js', array( 'jquery' ), $this->version, false );
		}
	}

	/**
	 * all admin actions.
	 * 
	 * @since 1.0.0
	 */
	public function hubwoo_ftf_admin_actions(){

		// add submenu hubspot coupons in woocommerce top menu.
		add_action( 'admin_menu', array( &$this, 'add_hubwoo_ftf_submenu' ) );
	}

	/**
	 * add hubspot field to field sync submenu in woocommerce menu.
	 *
	 * @since 1.0.0
	 */

	public function add_hubwoo_ftf_submenu(){

		add_submenu_page( 'woocommerce', __('HubSpot Field to Field Sync', 'hubwoo'), __('HubSpot Field to Field Sync', 'hubwoo'), 'manage_woocommerce', 'hubwoo_field_to_field', array(&$this, 'hubwoo_ftf_configuration') );
	}

	/**
	 * adding hubspot field to field display for admin
	 *
	 * @since 1.0.0
	 */

	public function hubwoo_ftf_configuration() {

		$hubwoo_ftf_callname_lic = Hubspot_Field_To_Field_Sync::$hubwoo_ftf_lic_callback_function;

		if( Hubspot_Field_To_Field_Sync::$hubwoo_ftf_callname_lic() ) {

			include_once 'partials/hubspot-field-to-field-sync-admin-display.php';
		}
		else {

			include_once 'partials/hubspot-field-to-field-sync-admin-license.php';
		}
	}


	/**
	 * creating and returning new row html for field mapping
	 *
	 * @since 1.0.0
	 */

	public function hubwoo_ftf_new_row() {
		
		global $hubwoo_ftf;
		
		$contact_properties = array();

		$user_fields = array();

		$contact_properties = get_option( "hubwoo_site_properties", array() );

		$user_fields = $hubwoo_ftf->hubwoo_ftf_get_all_user_fields();

		$prop_option = '';

		$wp_fields = '';

		if( count( $user_fields ) ) {

			foreach( $user_fields as $key => $value ) {

				$wp_fields .= '<option value="' . $value . '">' . $value . '</option>';
			}
		}

		if( count( $contact_properties ) ) {

			foreach( $contact_properties as $single_property ) {

				if( !$single_property->modificationMetadata->readOnlyValue ) {

					$prop_option .= '<option value="' . $single_property->name . '">' . $single_property->label . '</option>';
				}
			}
		}

		$html = '<tr class="hubwoo_coupon_new_rule" valign="top" data-id="' . $_POST["count"] . '">
				<td class="forminp forminp-text">
				<select class="hubwoo_map" name="hubwoo_selected_prop_fields['.$_POST["count"].']">
				<option value="select">'.__("--Select from HubSpot properties--","hubwoo").'</option>'.$prop_option.'</select></td>
				<td class="forminp forminp-text">
				<select class="hubwoo_map" name="hubwoo_selected_user_fields['.$_POST["count"].']">
				<option value="select">'.__(" -- Select from Wordpress User Custom fields -- ","hubwoo").'</option>'.$wp_fields.'</select></td><td><button data-id="'.$_POST["count"].'" class="hubwoo_remove_row">'.__("Delete","hubwoo").'</button></td><input type="hidden" name="hubwoo_count_rows['.$_POST['count'].']" value="'.$_POST["count"].'"></tr>';

		echo $html;

		wp_die();
	}


	/**
	 * updating users mapped properties 
	 *
	 * @since 1.0.0
	 */

	public function hubwoo_ftf_mapping_new_properties( $properties, $customer_id ) {

		$rows_added = get_option( "hubwoo_count_rows", array() );

		$hubwoo_selected_prop_fields = get_option( "hubwoo_selected_prop_fields", array() );

		$hubwoo_selected_user_fields = get_option( "hubwoo_selected_user_fields", array() );

		if( count( $rows_added ) ) {

			foreach( $rows_added as $single_row ) {

				if( isset( $hubwoo_selected_prop_fields[ $single_row ] ) && isset( $hubwoo_selected_user_fields[ $single_row ] ) ) {

					$property = $hubwoo_selected_prop_fields[ $single_row ];

					$field = $hubwoo_selected_user_fields[ $single_row ];

					if( $property == 'select' || $field == 'select' || $property == '' || $field == '' ) {

						continue;
					}

					$new_property = $this->hubwoo_fetching_new_properties( $customer_id, $property, $field );
					
					if( isset( $new_property[ 'property' ] ) && isset( $new_property[ 'value' ] ) ) {

						$flag = $this->hubwoo_check_for_duplicate_property( $properties, $property );

						if( $flag >= 0 ) {

							$properties[$flag] = $new_property;
						}
						elseif( $flag == -1 ) {

							$properties[] = $new_property;
						}
					}
				}
			}
		}

		if ( isset( $hubwoo_selected_prop_fields[0] ) && isset( $hubwoo_selected_user_fields[0] ) ) {

			$property = $hubwoo_selected_prop_fields[0];
			
			$field = $hubwoo_selected_user_fields[0];
			
			if( $property == 'select' || $field == 'select' || $property == '' || $field == '' ) {

				return $properties;
			}

			$new_property = $this->hubwoo_fetching_new_properties( $customer_id, $property, $field );

			if ( isset( $new_property[ 'property' ] ) && isset( $new_property[ 'value' ] ) ) {

				$flag = $this->hubwoo_check_for_duplicate_property( $properties, $property );

				if ( $flag >= 0 ) {

					$properties[$flag] = $new_property;
				}
				elseif ( $flag == -1 ) {

					$properties[] = $new_property;
				}
			}
		}

		return $properties;
	}


	/**
	 * checking for duplicate property of hubspot
	 *
	 * @since 1.0.0
	 */

	public function hubwoo_check_for_duplicate_property( $properties, $property ) {

		$flag = -1;

		if( count( $properties ) ) {

			foreach( $properties as $key => $single_property ) {

				if( $property == $single_property[ 'property' ] ) {

					$flag = $key;
					break;
				}
			}
		}

		return $flag;
	}

	/**
	 * getting new properties to mapped
	 * @since 		1.0.0
	 * @param 		customer_id 	id of customer
	 * @param 		property 		name of the property
	 * @param 		field 			user mapped field   
	 */

	public function hubwoo_fetching_new_properties( $customer_id, $property, $field ) {

		$values = array();

		$flag = true;

		if( !empty( $property ) && !empty( $field ) ) {

			$prop_value = !empty( get_user_meta( $customer_id, $field, true ) ) ? get_user_meta( $customer_id, $field, true ) : "";
			
			if( isset( $prop_value ) && $prop_value !== "" ) {

				$available_options = $this->hubwoo_fetch_property_options( $property );

				if( !empty( $available_options ) && is_array( $available_options ) ) {

					if( is_array( $prop_value ) ) {

						if( count( $prop_value ) ) {

							foreach( $prop_value as $single_value => $single_label ) {

								if( !in_array( $single_label, $available_options ) ) {

									$flag = false;
								}
								else {
									$prop_values[] = $single_label;
								}								
							}									
						}						
					}

					else {

						$bool = ( $prop_value == "1" || $prop_value == 1 ) ? "true" : "false";

						if( !in_array( $prop_value, $available_options ) ) {

							$flag = false;
						}
						if ( in_array( $bool, $available_options ) ) {

							$flag = true;
							$prop_value = $bool;
						}
					}
				}
			}
			else {

				$flag = false;
			}

			if ( $flag ) {

				$prop_type = $this->hubwoo_fetch_property_fieldtype( $property );

				if ( $prop_type == "enumeration" ) {

					if ( count( $prop_values ) ) {
						$prop_value = Hubspot_Field_To_Field_Sync_Admin::hubwoo_ftf_format_array( $prop_values );
					}
					else {
						$prop_value = Hubspot_Field_To_Field_Sync_Admin::hubwoo_ftf_format_array( $prop_value );
					}
				}
				elseif ( $prop_type == "string" ) {

					$prop_value = Hubspot_Field_To_Field_Sync_Admin::hubwoo_ftf_format_array( $prop_value );
				}
				elseif ( $prop_type == "date" || $prop_type == "datetime" ) {

					if ( is_numeric( $prop_value ) ) {

						$prop_value = $this->hubwoo_ftf_set_utc_midnight( $prop_value );
					}
				}
				elseif( $prop_type == "number" ) {

					if( is_numeric( $prop_value ) ) {
						
						$prop_value = floatval( $prop_value );
					} 
				}

				$values[ 'property' ] = $property;

				$values[ 'value' ] = $prop_value;
			}			
		}
		
		return $values;
	}

	/**
	 * getting options array of the specified property
	 * @since 		1.0.0
	 * @param 		property 		name of the property
	 */

	public function hubwoo_fetch_property_options( $property ) {

		$options = array();

		$contact_properties = get_option( "hubwoo_site_properties", array() );

		if( count( $contact_properties ) ) {

			foreach( $contact_properties as $single_property ) {

				if( $single_property->name == $property ) {

					$options = $single_property->options;
				}
			}
		}

		$filtered_options = array();

		if ( count( $options ) ) {

			foreach ( $options as $single_option ) {

				if ( isset( $single_option->value ) ) {

					$filtered_options[] = $single_option->value;
				}
			}
		}

		return $filtered_options;
	}

	/**
	 * getting fieldtype for the specified property
	 * @since 		1.0.0
	 * @param 		property 		name of the property
	 */

	public function hubwoo_fetch_property_fieldtype( $property ) {

		$fieldtype = array();

		$contact_properties = get_option( "hubwoo_site_properties", array() );

		if( count( $contact_properties ) ) {

			foreach( $contact_properties as $single_property ) {

				if( !$single_property->modificationMetadata->readOnlyValue ) {

					if( $single_property->name == $property ) {

						$fieldtype = $single_property->type;
					}
				}	
			}
		}

		return $fieldtype;
	}

	/**
	 * convert unix timestamp to hubwoo formatted midnight time.
	 * 
	 * @param  Unix timestamp    $unix_timestamp
	 * @return Unix midnight timestamp
	 * @since  1.0.0
	 */
	public function hubwoo_ftf_set_utc_midnight ( $unix_timestamp ) {

		$string = gmdate("Y-m-d H:i:s", $unix_timestamp );
		$date = new DateTime( $string );
		$date->modify( 'midnight' );
		return $date->getTimestamp() * 1000; // in miliseconds	
	}

	/**
	 * format an array in hubspot accepted enumeration value.
	 * 
	 * @param  array   $properties  Array of values
	 * @return string       formatted string.
	 * @since 1.0.0
	 */
	public function hubwoo_ftf_format_array( $properties ) {

		if( is_array( $properties ) ) {

			$properties = array_unique( $properties );

			$properties = implode( ';', $properties );
		}

		return $properties;
	}


	/**
	 * activating license on user request
	 * @since 		1.0.0
	 * @param 		property 		name of the property
	 */
	public function hubwoo_ftf_validate_license_key() {
			        	
		$hubwoo_ftf_purchase_code = sanitize_text_field( $_POST["purchase_code"] );

	 	$api_params = array(
            'slm_action' 		=> 'slm_activate',
            'secret_key' 		=> HUBWOO_FTF_SPECIAL_SECRET_KEY, 
            'license_key' 		=> $hubwoo_ftf_purchase_code,
            '_registered_domain' => $_SERVER['SERVER_NAME'],
            'item_reference' 	=> urlencode( HUBWOO_FTF_ITEM_REFERENCE ),
            'product_reference' => 'MWBPK-4087'
		);
   	 	
	 	$query = esc_url_raw( add_query_arg( $api_params, HUBWOO_FTF_LICENSE_SERVER_URL ) );

		$hubwoo_ftf_response = wp_remote_get( $query, array( 'timeout' => 20, 'sslverify' => false ) );

		if( is_wp_error( $hubwoo_ftf_response ) ) {

			echo json_encode( array( 'status' => false, 'msg' => __("An unexpected error occurred. Please try again later." ) ) );
		}
		else {

	        $hubwoo_ftf_license_data = json_decode( wp_remote_retrieve_body( $hubwoo_ftf_response ) );
	       
	        if( isset( $hubwoo_ftf_license_data->result ) && $hubwoo_ftf_license_data->result == 'success' ) {

	        	update_option( "hubwoo_ftf_license_check", true );
	        	update_option( "hubwoo_ftf_license_key", $hubwoo_ftf_purchase_code );
	            echo json_encode( array( 'status' => true, 'msg' => __('Successfully Verified. Please Wait.','hubwoo') ) );
	        }
	        else {

	        	delete_option( "hubwoo_ftf_license_check" );
	        	delete_option( "hubwoo_ftf_license_key" );
	    	 	echo json_encode( array( 'status' => false, 'msg' => $hubwoo_ftf_license_data->message ) );
	        }
	    }

   	 	wp_die();
	}


	/**
	 * checking license on each day
	 * @since 		1.0.0
	 * @param 		property 		name of the property
	 */
	public function hubwoo_ftf_check_licence_daily() {

		$user_license_key = get_option( "hubwoo_ftf_license_key", "" );

		$api_params = array(
            'slm_action' 		=> 'slm_check',
            'secret_key' 		=> HUBWOO_FTF_SPECIAL_SECRET_KEY,
            'license_key' 		=> $user_license_key,
            '_registered_domain' => $_SERVER['SERVER_NAME'],
            'item_reference' 	=> urlencode( HUBWOO_FTF_ITEM_REFERENCE ),
            'product_reference' => 'MWBPK-4087'
		);
	 	
	 	$query = esc_url_raw( add_query_arg( $api_params, HUBWOO_FTF_LICENSE_SERVER_URL ) );

		$mwb_response = wp_remote_get( $query, array( 'timeout' => 20, 'sslverify' => false ) );

        $license_data = json_decode( wp_remote_retrieve_body( $mwb_response ) );

        if( isset( $license_data->result ) && $license_data->result == 'success' ) {

        	if( isset( $license_data->status ) && $license_data->status == 'active' ) {

	        	update_option( "hubwoo_ftf_license_check", true );
	        }
	        else {

	        	delete_option( "hubwoo_ftf_license_check" );
	        }
	    }
	}
}