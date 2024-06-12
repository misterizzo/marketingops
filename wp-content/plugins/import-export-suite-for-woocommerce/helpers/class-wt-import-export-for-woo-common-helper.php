<?php
/**
 * Webtoffee common helpers
 *
 * @link
 *
 * @package ImportExportSuite\Helpers\CommonHelper
 */

use Automattic\WooCommerce\Utilities\OrderUtil;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
	/**
	 * Wt_Import_Export_For_Woo_Common_Helper Class.
	 */
class Wt_Import_Export_For_Woo_Common_Helper {


	/**
	 * Minimum version
	 *
	 * @var string
	 */
	public static $min_version_msg = '';

	/**
	 * Get File name by url
	 *
	 * @param string $file_url URL of the file.
	 * @return string the base name of the given URL (File name).
	 */
	public static function wt_wc_get_filename_from_url( $file_url ) {
		$parts = parse_url( $file_url );
		if ( isset( $parts['path'] ) ) {
			return basename( $parts['path'] );
		}
	}

	/**
	 * Get info like language code, parent product ID etc by product id.
	 *
	 * @param int $element_id Product ID.
	 * @return array/false.
	 */
	public static function wt_get_wpml_original_post_language_info( $element_id ) {
		$get_language_args = array(
			'element_id' => $element_id,
			'element_type' => 'post_product',
		);
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a post
		 * collection request.
		 *
		 * @since 1.0.0
		 *
		 * @param null          $original_post_language_info    WPML false.
		 * @param array           $get_language_args    WPML language arguments.
		 */
		$original_post_language_info = apply_filters( 'wpml_element_language_details', null, $get_language_args );
		return $original_post_language_info;
	}
	/**
	 * Get product ID by SKU
	 *
	 * @global type $wpdb
	 * @param string $sku SKU of the product.
	 * @return boolean
	 */
	public static function wt_get_product_id_by_sku( $sku ) {
		global $wpdb;
		$post_exists_sku = $wpdb->get_var(
			$wpdb->prepare(
				"
                   SELECT $wpdb->posts.ID
                   FROM $wpdb->posts
                   LEFT JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
                   WHERE $wpdb->posts.post_status IN ( 'publish', 'private', 'draft', 'pending', 'future' )
                   AND $wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value = %s
                   ",
				$sku
			)
		);
		if ( $post_exists_sku ) {
			return $post_exists_sku;
		}
		return false;
	}

	/**
	 * To strip the specific string from the array key as well as value.
	 *
	 * @param array  $array Walk array.
	 * @param string $data Data to walk.
	 * @return array.
	 */
	public static function wt_array_walk( $array, $data ) {
		$new_array = array();
		foreach ( $array as $key => $value ) {
			$new_array[ str_replace( $data, '', $key ) ] = str_replace( $data, '', $value );
		}
		return $new_array;
	}

	/**
	 * Check the minimum base version required for post type modules
	 *
	 * @param string $post_type Post type.
	 * @param string $post_type_title Post type title.
	 * @param string $min_version Minimum version.
	 * @return boolean
	 */
	public static function check_base_version( $post_type, $post_type_title, $min_version ) {
		$warn_icon = '<span class="dashicons dashicons-warning"></span>&nbsp;';
		if ( ! version_compare( WT_IEW_VERSION, $min_version, '>=' ) ) {
			/* translators: 1: current step number. 2: total steps. 3: total steps. 4: total steps */
			self::$min_version_msg .= $warn_icon . sprintf( __( 'The %1$s requires a minimum version of %2$s %3$s. Please upgrade the %4$s accordingly.' ), "<b>$post_type_title</b>", '<b>' . WT_IEW_PLUGIN_NAME . '</b>', "<b>v$min_version</b>", '<b>' . WT_IEW_PLUGIN_NAME . '</b>' ) . '<br />';
			add_action( 'admin_notices', array( __CLASS__, 'no_minimum_base_version' ) );
			return false;
		}
		return true;
	}

	/**
	 *
	 *   No minimum version error message
	 */
	public static function no_minimum_base_version() {
		?>
		<div class="notice notice-warning">
			<p>
				<?php
				echo wp_kses_post( self::$min_version_msg );
				?>
			</p>
		</div>
		<?php
	}

	/**
	 *   Decode the post data as normal array from json encoded from data.
	 *   If step key is specified, then it will return the data corresponds to the form key
	 *
	 *   @param array  $form_data Form data.
	 *   @param string $key Form element key.
	 */
	public static function process_formdata( $form_data, $key = '' ) {
		if ( '' != $key ) {
			if ( isset( $form_data[ $key ] ) ) {
				if ( is_array( $form_data[ $key ] ) ) {
					$form_data_vl = $form_data[ $key ];
				} else {
					$form_data_vl = json_decode( ( $form_data[ $key ] ), true );
				}
			} else {
				$form_data_vl = array();
			}
		} else {
			$form_data_vl = array();
			foreach ( $form_data as $form_datak => $form_datav ) {
				$form_data_vl[ $form_datak ] = self::process_formdata( $form_data, $form_datak );
			}
		}
		return ( is_array( $form_data_vl ) ? $form_data_vl : array() );
	}

	/**
	 *   Form field generator
	 *
	 * @param array $form_fields Form fields.
	 * @param array $form_data Form data.
	 */
	public static function field_generator( $form_fields, $form_data ) {
		include plugin_dir_path( __DIR__ ) . 'admin/partials/form-field-generator.php';
	}


	/**
	 *   Save advanced settings
	 *
	 *   @param  array $settings  array of setting values.
	 */
	public static function set_advanced_settings( $settings ) {
		update_option( 'wt_iew_advanced_settings', $settings );
	}

	/**
	 *
	 *   Extract validation rule from form field array
	 *
	 *   @param  array $fields   form field array.
	 */
	public static function extract_validation_rules( $fields ) {
		$out = array_map(
			function ( $r ) {
				return ( isset( $r['validation_rule'] ) ? $r['validation_rule'] : '' );
			},
			$fields
		);
		return array_filter( $out );
	}

	/**
	 *   Get advanced settings.
	 *
	 *   @param      string $key    key for specific setting (optional).
	 *   @return     mixed   if key provided then the value of key otherwise array of values
	 */
	public static function get_advanced_settings( $key = '' ) {
		$advanced_settings = get_option( 'wt_iew_advanced_settings' );
		$advanced_settings = ( $advanced_settings ? $advanced_settings : array() );
		if ( '' != $key ) {
			$key = ( substr( $key, 0, 8 ) !== 'wt_iew_' ? 'wt_iew_' : '' ) . $key;
			if ( isset( $advanced_settings[ $key ] ) ) {
				return $advanced_settings[ $key ];
			} else {
				$default_settings = self::get_advanced_settings_default();
				return ( isset( $default_settings[ $key ] ) ? $default_settings[ $key ] : '' );
			}
		} else {
			$default_settings = self::get_advanced_settings_default();
			$advanced_settings = wp_parse_args( $advanced_settings, $default_settings );
			return $advanced_settings;
		}
	}

	/**
	 *   Get default value of advanced settings
	 *
	 *   @return     array   array of default values
	 */
	public static function get_advanced_settings_default() {
		$out = array();
		$fields = self::get_advanced_settings_fields();
		foreach ( $fields as $key => $value ) {
			if ( isset( $value['value'] ) ) {
				$key = ( substr( $key, 0, 8 ) !== 'wt_iew_' ? 'wt_iew_' : '' ) . $key;
				$out[ $key ] = $value['value'];
			}
		}
		return $out;
	}

	/**
	 *   Get advanced fields
	 *
	 *   @return     array   array of fields
	 */
	public static function get_advanced_settings_fields() {
		$fields = array();
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a post
		 * collection request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $fields    Advanced fields.
		 */
		return apply_filters( 'wt_iew_advanced_setting_fields', $fields );
	}
	/**
	 * Allowed screens
	 *
	 * @return type
	 */
	public static function wt_allowed_screens() {
		$screens = array( 'wt_import_export_for_woo', 'wt_import_export_for_woo_export', 'wt_import_export_for_woo_import', 'wt_import_export_for_woo_history', 'wt_import_export_for_woo_history_log', 'wt_import_export_for_woo_cron' );
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a post
		 * collection request.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $screens    Allowed screens.
		 */
		return apply_filters( 'wt_iew_allowed_screens', $screens );
	}
	/**
	 * Get current page
	 *
	 * @return string
	 */
	public static function wt_get_current_page() {
		if ( isset( $_GET['page'] ) ) {
			return sanitize_text_field( wp_unslash( $_GET['page'] ) );
		}
		return '';
	}
	/**
	 * Is allowed screen
	 *
	 * @return boolean
	 */
	public static function wt_is_screen_allowed() {
		if ( in_array( self::wt_get_current_page(), self::wt_allowed_screens() ) ) {
			return true;
		} else {
			return false;
		}
	}
	 /**
	  * Get hpos related datas
	  *
	  * @return array
	  */
	public static function is_hpos_enabled() {
		global $wpdb;
		$sync = false;
		$table_name = $wpdb->prefix . 'posts';
		if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
				$order_table_name = $wpdb->prefix . 'wc_orders';
				$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wc_orders'" );

			if ( $table_exists === $order_table_name ) {
				require_once WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
				$sync = OrderUtil::is_custom_order_tables_in_sync();
				$table_name = OrderUtil::custom_orders_table_usage_is_enabled() ? $wpdb->prefix . 'wc_orders' : $wpdb->prefix . 'posts';
			}
		}
		return array(
			'sync' => $sync,
			'table_name' => $table_name,
		);
	}

	/**
	 * Is prior to WC version
	 *
	 * @param string $version Version to check.
	 * @return boolean
	 */
	public static function wt_iew_is_woocommerce_prior_to( $version ) {

		$woocommerce_is_pre_version = ( ! defined( 'WC_VERSION' ) || version_compare( WC_VERSION, $version, '<' ) ) ? true : false;
		return $woocommerce_is_pre_version;

		if ( WC()->version < $version ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Let to num
	 *
	 * @param string $size Size.
	 * @return int
	 */
	public static function wt_let_to_num( $size ) {
			$l   = substr( $size, -1 );
			$ret = (int) substr( $size, 0, -1 );
		switch ( strtoupper( $l ) ) {
			case 'P':
					$ret *= 1024;
					// No break.
			case 'T':
					$ret *= 1024;
					// No break.
			case 'G':
					$ret *= 1024;
					// No break.
			case 'M':
					$ret *= 1024;
					// No break.
			case 'K':
					$ret *= 1024;
					// No break.
		}
			return $ret;
	}
	/**
	 * UTF-8fy
	 *
	 * @param string $d String to utf8fy.
	 * @return type
	 */
	public static function wt_iew_utf8ize( $d ) {
		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a post
		 * collection request.
		 *
		 * @since 1.0.0
		 *
		 * @param boolean           $utf8_encode_needed    UTF-8 encode needed flag.
		 */
		$enable_utf8 = apply_filters( 'wt_ier_enable_string_utf8_encode', false );
		if ( $enable_utf8 ) {
			if ( is_array( $d ) ) {
				foreach ( $d as $k => $v ) {
					$d[ $k ] = wt_iew_utf8ize( $v );
				}
			} else if ( is_string( $d ) ) {
				return utf8_encode( $d );
			}
		}
		return $d;
	}

	/**
	 * Format to decimal
	 *
	 * @param string $number Number.
	 * @return type
	 */
	public static function wt_format_decimal( $number ) {
		$decimals = array( ',', '.', ':' );
		// Remove locale from string.
		if ( ! is_float( $number ) ) {
			$number = str_replace( $decimals, '.', $number );
			$number = preg_replace( '/\.(?![^.]+$)|[^0-9.-]/', '', sanitize_text_field( $number ) );
		}
		return $number;
	}
}
