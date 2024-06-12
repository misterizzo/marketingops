<?php

namespace ProfilePress\Libsodium\TWOFA;

class Shortcode extends AbstractClass
{
    const NOTICES_META_KEY = '_ppress_2fa_notices';

    public function __construct()
    {
        parent::__construct();

        add_shortcode('profilepress-2fa-setup', [$this, 'shortcode_handler']);
    }

    public function shortcode_handler()
    {
        if ( ! is_user_logged_in()) {
            ob_start();
            ppress_content_http_redirect(
                ppress_login_url(ppress_get_current_url_query_string())
            );

            return ob_get_clean();
        }

        return self::twofa_setup_page_content(get_current_user_id());
    }

    /**
     * @return self
     */
    public static function get_instance()
    {
        static $instance;

        if ( ! isset($instance)) {
            $instance = new self;
        }

        return $instance;
    }
}