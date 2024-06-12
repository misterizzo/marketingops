<?php

namespace ProfilePress\Core\Membership\DigitalProducts;

class Init
{
    public function __construct()
    {
        UploadHandler::get_instance();
        DownloadHandler::get_instance();
    }

    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}