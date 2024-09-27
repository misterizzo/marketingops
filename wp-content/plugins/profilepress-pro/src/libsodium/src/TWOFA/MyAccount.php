<?php

namespace ProfilePress\Libsodium\TWOFA;

class MyAccount extends AbstractClass
{
    public function __construct()
    {
        parent::__construct();

        add_filter('ppmyac_account_settings_endpoint_content', [$this, 'myaccount_page'], 1);
    }

    public function myaccount_page($args)
    {
        if (Common::can_configure_2fa()) {

            $args[] = [
                'title'   => esc_html__('Two-Factor Authentication', 'profilepress-pro'),
                'content' => self::twofa_setup_page_content()
            ];
        }

        return $args;
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