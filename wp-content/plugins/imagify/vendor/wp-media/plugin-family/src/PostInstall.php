<?php

namespace WPMedia\PluginFamily;

use Composer\Script\Event;

class PostInstall {

    /**
     * Array of files to update.
     *
     * @var array
     */
    private static $files = [
        'PluginFamily',
        'wp_media_plugins',
    ];

    /**
     * Updates domain text for package after composer update or install.
     *
     * @param Event $event Composer event.
     * @return void
     */
    public static function apply_text_domain( Event $event ) {

        $output = $event->getIO();
        $composer = $event->getComposer();
        $extra = $composer->getPackage()->getExtra();

        if ( ! isset( $extra['plugin_domain'] ) ) {
            $output->writeError( self::colorize( 'Plugin domain is not set in the composer extra configuration.', 'red' ) );
            return;
        }

        foreach ( self::$files as $file ) {
            // Construct file path.
            $path = __DIR__ . '/Model/' . $file . '.php';

            if ( ! file_exists( $path ) ) {
                $output->writeError( self::colorize( 'Could not find file: ' . $path . ', Does it exist?', 'red' ) );
                return;
            }

            // Get file contents.
            $content = file_get_contents( $path );

            if ( false === $content ) {
                $output->writeError( self::colorize( 'Failed to read the file: ' . $path, 'red' ) );
                return;
            }

            // Update file content.
            $updated_content = str_replace( '%domain%', $extra['plugin_domain'], $content );
            $result = file_put_contents( $path, $updated_content );

            if ( false === $result ) {
                $output->writeError( self::colorize( 'Failed to write the updated content to the file: ' . $path, 'red' ) );
                return;
            }
        }

        // Output success feed.
        $output->write( self::colorize( 'Text domain has been updated.', 'green' ) );

        // Path to this script.
        $script = __FILE__;

        // Delete script after execution.
        register_shutdown_function( function () use ( $script ) {
            if ( file_exists( $script ) ) {
                unlink( $script );
            }
        });
    }

    /**
     * This function colorizes a given string with a specified color for console output.
     *
     * @param string $message String message to pass.
     * @param string $color Color on the console.
     * @return string
     */
    private static function colorize( string $message, string $color ): string {
        $colors = [
            'red' => "\033[31m",
            'green' => "\033[32m",
            'reset' => "\033[0m",
        ];

        return $colors[$color] . $message . $colors['reset'];
    }
}