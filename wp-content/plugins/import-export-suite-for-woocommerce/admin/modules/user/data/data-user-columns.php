<?php
/**
 * User post columns
 *
 * @link
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$columns = array(
	'ID'              => 'ID',
	'customer_id'     => 'customer_id',
	'user_login'      => 'user_login',
	'user_pass'       => 'user_pass',
	'user_nicename'   => 'user_nicename',
	'user_email'      => 'user_email',
	'user_url'        => 'user_url',
	'user_registered' => 'user_registered',
	'display_name'    => 'display_name',
	'first_name'      => 'first_name',
	'last_name'       => 'last_name',
	'user_status'     => 'user_status',
	'roles'           => 'roles',
);
// default meta.
$columns['nickname']            = 'nickname';
$columns['first_name']          = 'first_name';
$columns['last_name']           = 'last_name';
$columns['description']         = 'description';
$columns['rich_editing']        = 'rich_editing';
$columns['syntax_highlighting'] = 'syntax_highlighting';
$columns['admin_color']         = 'admin_color';
$columns['use_ssl'] = 'use_ssl';
$columns['show_admin_bar_front'] = 'show_admin_bar_front';
$columns['locale'] = 'locale';
$columns[ $wpdb->prefix . 'user_level' ] = $wpdb->prefix . 'user_level';
$columns['dismissed_wp_pointers']    = 'dismissed_wp_pointers';
$columns['show_welcome_panel']       = 'show_welcome_panel';
$columns['session_tokens']           = 'session_tokens';
$columns['last_update'] = 'last_update';
$columns['is_geuest_user'] = 'is_geuest_user';


if ( ! function_exists( 'is_plugin_active' ) ) {
	 include_once ABSPATH . '/wp-admin/includes/plugin.php';
}

if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) :
	$columns['total_spent']         = 'total_spent';
	$columns['billing_first_name']  = 'billing_first_name';
	$columns['billing_last_name']   = 'billing_last_name';
	$columns['billing_company']     = 'billing_company';
	$columns['billing_email']       = 'billing_email';
	$columns['billing_phone']       = 'billing_phone';
	$columns['billing_address_1']   = 'billing_address_1';
	$columns['billing_address_2']   = 'billing_address_2';
	$columns['billing_postcode']    = 'billing_postcode';
	$columns['billing_city']        = 'billing_city';
	$columns['billing_state']       = 'billing_state';
	$columns['billing_country']     = 'billing_country';
	$columns['shipping_first_name'] = 'shipping_first_name';
	$columns['shipping_last_name']  = 'shipping_last_name';
	$columns['shipping_company']    = 'shipping_company';
	$columns['shipping_phone']      = 'shipping_phone';
	$columns['shipping_address_1']  = 'shipping_address_1';
	$columns['shipping_address_2']  = 'shipping_address_2';
	$columns['shipping_postcode']   = 'shipping_postcode';
	$columns['shipping_city']       = 'shipping_city';
	$columns['shipping_state']      = 'shipping_state';
	$columns['shipping_country']    = 'shipping_country';
endif;

/**
 * Filter the query arguments for a request.
 *
 * Enables adding extra arguments or setting defaults for the request.
 *
 * @since 1.0.0
 *
 * @param array   $columns    Import columns.
 */
return apply_filters( 'hf_csv_customer_post_columns', $columns );
