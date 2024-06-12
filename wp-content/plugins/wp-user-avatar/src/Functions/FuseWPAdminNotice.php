<?php

if ( ! class_exists('\FuseWPAdminNotice')) {

    class FuseWPAdminNotice
    {
        public function __construct()
        {
            add_action('admin_notices', array($this, 'admin_notice'));
            add_action('network_admin_notices', array($this, 'admin_notice'));

            add_action('admin_init', array($this, 'dismiss_admin_notice'));
        }

        public function dismiss_admin_notice()
        {
            if ( ! isset($_GET['fwp-adaction']) || $_GET['fwp-adaction'] != 'fwp_dismiss_fwpadnotice') {
                return;
            }

            if ( ! current_user_can('manage_options')) return;

            check_admin_referer('fwp_dismiss_fwpadnotice', 'csrf');

            $url = admin_url();
            update_option('fwp_dismiss_fwpadnotice', 'true');

            wp_redirect($url);
            exit;
        }

        public function admin_notice()
        {
            global $pagenow;

            if ($pagenow != 'index.php') return;

            if ( ! current_user_can('manage_options')) return;

            if (get_option('fwp_dismiss_fwpadnotice', 'false') == 'true') {
                return;
            }

            if ($this->is_plugin_installed() && $this->is_plugin_active()) {
                return;
            }

            $dismiss_url = esc_url_raw(
                add_query_arg([
                    'fwp-adaction' => 'fwp_dismiss_fwpadnotice',
                    'csrf'         => wp_create_nonce('fwp_dismiss_fwpadnotice')
                ], admin_url())
            );
            $this->notice_css();
            $install_url = wp_nonce_url(
                admin_url('update.php?action=install-plugin&plugin=fusewp'),
                'install-plugin_fusewp'
            );

            $activate_url = wp_nonce_url(admin_url('plugins.php?action=activate&plugin=fusewp%2Ffusewp.php'), 'activate-plugin_fusewp/fusewp.php');
            ?>
            <div class="fwp-admin-notice notice notice-success">
                <div class="fwp-notice-first-half">
                    <p>
                        <?php
                        printf(
                            __('Free WordPress automation plugin to sync your users, membership members and their profile updates with your email marketing list (in Mailchimp, Constant Contact etc.) automatically.', 'wp-user-avatar'),
                            '<span class="fwp-stylize"><strong>', '</strong></span>');
                        ?>
                    </p>
                    <p style="text-decoration: underline;font-size: 12px;">Recommended by ProfilePress plugin</p>
                </div>
                <div class="fwp-notice-other-half">
                    <?php if ( ! $this->is_plugin_installed()) : ?>
                        <a class="button button-primary button-hero" id="fwp-install-fusewp-plugin" href="<?php echo $install_url; ?>">
                            <?php _e('Install FuseWP Now for Free!', 'wp-user-avatar'); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($this->is_plugin_installed() && ! $this->is_plugin_active()) : ?>
                        <a class="button button-primary button-hero" id="fwp-activate-fusewp-plugin" href="<?php echo $activate_url; ?>">
                            <?php _e('Activate FuseWP Now!', 'wp-user-avatar'); ?>
                        </a>
                    <?php endif; ?>
                    <div class="fwp-notice-learn-more">
                        <a target="_blank" href="https://fusewp.com/">Learn more</a>
                    </div>
                </div>
                <a href="<?php echo $dismiss_url; ?>">
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text"><?php _e('Dismiss this notice', 'wp-user-avatar'); ?>.</span>
                    </button>
                </a>
            </div>
            <?php
        }

        public function current_admin_url()
        {
            $parts = parse_url(home_url());
            $uri   = $parts['scheme'] . '://' . $parts['host'];

            if (array_key_exists('port', $parts)) {
                $uri .= ':' . $parts['port'];
            }

            $uri .= add_query_arg(array());

            return $uri;
        }

        public function is_plugin_installed()
        {
            $installed_plugins = get_plugins();

            return isset($installed_plugins['fusewp/fusewp.php']);
        }

        public function is_plugin_active()
        {
            return is_plugin_active('fusewp/fusewp.php');
        }

        public function notice_css()
        {
            ?>
            <style type="text/css">
                .fwp-admin-notice {
                    background: #fff;
                    color: #000;
                    border-left-color: #46b450;
                    position: relative;
                }

                .fwp-admin-notice .notice-dismiss:before {
                    color: #72777c;
                }

                .fwp-admin-notice .fwp-stylize {
                    line-height: 2;
                }

                .fwp-admin-notice .button-primary {
                    background: #006799;
                    text-shadow: none;
                    border: 0;
                    box-shadow: none;
                }

                .fwp-notice-first-half {
                    width: 66%;
                    display: inline-block;
                    margin: 10px 0;
                }

                .fwp-notice-other-half {
                    width: 33%;
                    display: inline-block;
                    padding: 20px 0;
                    position: absolute;
                    text-align: center;
                }

                .fwp-notice-first-half p {
                    font-size: 14px;
                }

                .fwp-notice-learn-more a {
                    margin: 10px;
                }

                .fwp-notice-learn-more {
                    margin-top: 10px;
                }
            </style>
            <?php
        }

        public static function instance()
        {
            static $instance = null;

            if (is_null($instance)) {
                $instance = new self();
            }

            return $instance;
        }
    }

    FuseWPAdminNotice::instance();
}