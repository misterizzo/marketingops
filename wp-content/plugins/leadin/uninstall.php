<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

$leadin_users = get_users( array( 'fields' => array( 'ID' ) ) );
foreach ( $leadin_users as $leadin_user ) {
	delete_user_meta( $leadin_user->ID, 'leadin_email' );
	delete_user_meta( $leadin_user->ID, 'leadin_skip_review' );
	delete_user_meta( $leadin_user->ID, 'leadin_review_banner_last_call' );
	delete_user_meta( $leadin_user->ID, 'leadin_has_min_contacts' );
	delete_user_meta( $leadin_user->ID, 'leadin_track_consent' );
}

delete_option( 'leadin_portalId' );
delete_option( 'leadin_account_name' );
delete_option( 'leadin_portal_domain' );
delete_option( 'leadin_hublet' );
delete_option( 'leadin_disable_internal_tracking' );
delete_option( 'leadin_business_unit_id' );
delete_option( 'leadin_access_token' );
delete_option( 'leadin_refresh_token' );
delete_option( 'leadin_expiry_time' );
delete_option( 'leadin_activation_time' );
delete_option( 'leadin_content_embed_ui_install' );
