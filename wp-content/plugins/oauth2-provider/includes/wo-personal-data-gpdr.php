<?php

/**
 * Class WO_Personal_Eraser
 *
 * Personal Data Eraser that taps into WP's data removal
 *
 * @link   https://developer.wordpress.org/plugins/privacy/adding-the-personal-data-eraser-to-your-plugin/
 * @author Justin Greer <justin@justin-greer.com>
 */

/**
 * Personal Data Erasers
 * GDPR Compliance
 */
add_filter( 'wp_privacy_personal_data_erasers', 'wo_register_wo_personal_data_eraser_request', 10 );
function wo_register_wo_personal_data_eraser_request( $erasers ) {
	$erasers['wo-personal-data-eraser'] = array(
		'exporter_friendly_name' => 'WP OAuth Server Data',
		'callback'               => 'wo_personal_data_erase_function',
	);

	return $erasers;
}

function wo_personal_data_erase_function( $email_address, $page = 1 ) {
	 $user = get_user_by( 'email', $email_address );

	$items_removed = 0;

	// Remove access tokens left over.
	global $wpdb;
	$items_removed = $wpdb->delete(
		$wpdb->prefix . 'oauth_access_tokens',
		array(
			'user_id' => $user->ID,
		)
	);

	return array(
		'items_removed'  => $items_removed,
		'items_retained' => false, // always false in this example
		'messages'       => array(), // no messages in this example
		'done'           => 1, // Simple since we are not looping
	);
}

/**
 * Personal Data Exporters
 * GDPR Compliance
 */
// add_filter( 'wp_privacy_personal_data_exporters', 'wo_register_wo_personal_data_export_request', 10 );
function wo_register_wo_personal_data_export_request( $exporters ) {
	$exporters['wo-personal-data-exporter'] = array(
		'exporter_friendly_name' => 'WP OAuth Server Data',
		'callback'               => 'wo_personal_data_export_function',
	);

	return $exporters;
}

function wo_personal_data_export_function( $email_address, $page = 1 ) {
	$user = get_user_by( 'email', $email_address );

	$items_removed = 0;

	// Remove access tokens left over.
	global $wpdb;
	$prepare = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}oauth_access_tokens WHERE user_id=%d ", array( $user->ID ) );
	$items   = $wpdb->get_results( $prepare );

	$export_items = array();
	$group_id     = 'wp_oauth_items';
	$group_label  = 'WP OAuth Server Items';

	foreach ( $items as $item ) {
		$item_data = array(
			array(
				'name'  => 'User ID',
				'value' => $user->ID,
			),
			array(
				'name'  => 'Access Token',
				'value' => $item->access_token,
			),
			array(
				'name'  => 'Client ID',
				'value' => $item->client_id,
			),
		);

		$export_items[] = array(
			'group_id'    => $group_id,
			'group_label' => $group_label,
			'item_id'     => $item->id,
			'data'        => $item_data,
		);
	}

	return array(
		'data' => $export_items,
		'done' => true,
	);
}

/**
 * Privacy Policy Content Generator
 * GDPR Compliance Suggestion
 */

function wo_plugin_get_default_privacy_content() {
	return '<h2>' . __( 'What personal data we collect and why we collect it' ) . '</h2>' .
	'<p>' . __( 'We collect the user id and issue an access token that can be used on behalf of a user account. Although this does not directly handle personal information, it is important to note that personal information can be obtained using a User ID and access token.' ) . '</p>';
}

// add_action( 'admin_init', 'wo_plugin_add_suggested_privacy_content', 20 );
function wo_plugin_add_suggested_privacy_content() {
	$content = wo_plugin_get_default_privacy_content();
	wp_add_privacy_policy_content( __( 'WP OAuth Server' ), $content );
}
