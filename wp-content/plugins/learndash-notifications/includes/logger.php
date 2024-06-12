<?php
if ( ! defined( 'ABSPATH' ) ) exit();

add_action( 'learndash_notification_after_send_notification', function( $data ) {
    learndash_notifications_log_action( sprintf( __( 'Notification with these data: %s was sent.', 'learndash-notifications' ), learndash_notifications_parse_variable( $data ) ) );
} );

add_action( 'learndash_notifications_after_send_delayed_email', function( $data ) {
    learndash_notifications_log_action( sprintf( __( 'Scheduled notification with these data: %s was sent.', 'learndash-notifications' ), learndash_notifications_parse_variable( $data ) ) );
} );

add_action( 'learndash_notifications_insert_delayed_email', function( $data ) {
    learndash_notifications_log_action( sprintf( __( 'Scheduled notification for the timestamp: %d with these data: %s was stored in database.', 'learndash-notifications' ), $data['sent_on'], learndash_notifications_parse_variable( $data['shortcode_data'] ) ) );
} );

add_action( 'learndash_notifications_update_delayed_email', function( $data, $where, $count ) {
    learndash_notifications_log_action( sprintf( __( 'Scheduled notification with these data: %s was updated in database with these new data: %s. %d %s affected.', 'learndash-notifications' ), learndash_notifications_parse_variable( $where ), learndash_notifications_parse_variable( $data['shortcode_data'] ), $count, _n( 'row was', 'rows were', $count, 'learndash-notifications' ) ) );
}, 10, 3 );

add_action( 'learndash_notifications_empty_delayed_emails_table', function( $count ) {
    learndash_notifications_log_action( sprintf( __( 'Empty delayed emails table process was run. %d %s deleted from database.' ), $count, _n( 'row was', 'rows were', $count, 'learndash-notifications' ) ) );
} );

add_action( 'learndash_notifications_delete_delayed_emails', function( $count ) {
    if ( is_numeric( $count ) && $count > 0 ) {
        learndash_notifications_log_action( sprintf( __( 'Expired scheduled notifications were deleted. %d %s deleted from database.' ), $count, _n( 'row was', 'rows were', $count, 'learndash-notifications' ) ) );
    }
} );