<?php

if ( ! defined( 'ABSPATH' ) ) exit();

/**
 * Log a variable in log file
 * @param  mixed  $variable Any variable that needs to be debugged
 * @param  string $desc     Desciption of the log
 * @return void
 */
function learndash_notifications_debug( $variable, $desc = '' )
{
    $file = WP_CONTENT_DIR . '/uploads/learndash/learndash-notifications.log';
    $dirname = dirname( $file );

    if ( ! is_dir( $dirname ) ) {
        wp_mkdir_p( $dirname );
    }

    if ( is_bool( $variable ) ) {
        $variable = $variable ? 'true' : 'false';
    }

    $date = date( 'Y-m-d H:i:s' );

    $variable = print_r( $variable, true );

    error_log( "[{$date}] {$desc}: {$variable}" . PHP_EOL, 3, $file );
}

/**
 * Log a message in log file
 * @param  string $message Log message
 * @return void
 */
function learndash_notifications_log_action( $message )
{
    $file = WP_CONTENT_DIR . '/uploads/learndash/learndash-notifications-actions.log';
    $dirname = dirname( $file );

    if ( ! is_dir( $dirname ) ) {
        wp_mkdir_p( $dirname );
    }

    if ( file_exists( $file ) && filesize( $file ) > 51200 ) {
        $write_file = $dirname . 'learndash-notifications-log.temp';
        $write = new SplFileObject( $write_file , 'w' );
        $reading = new SplFileObject( $file, 'r' );

        foreach ( new LimitIterator( $reading, 1 ) as $line ) {
            $write->fwrite( $line );
        }

        $write = null;
        $reading = null;

        rename( $write_file, $file );
    }

    $date = date( 'Y-m-d H:i:s' );
    $message = preg_replace( '/(\r|\n|\r\n|\s{2,})/', ' ', $message );
    error_log( "[{$date}]: {$message}" . PHP_EOL, 3, $file );
}

function learndash_notifications_parse_variable( $var ) {
    if ( is_array( $var ) || is_object( $var ) ) {
        return json_encode( $var );
    } else {
        return $var;
    }
}