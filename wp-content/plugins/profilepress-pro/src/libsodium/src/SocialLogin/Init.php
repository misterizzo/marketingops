<?php

namespace ProfilePress\Libsodium\SocialLogin;

use Hybridauth\Hybridauth;
use ProfilePress\Core\Classes\ExtensionManager as EM;

class Init
{
    public static $instance_flag = false;

    public function __construct()
    {
        add_action('init', [$this, 'rewrite_rule']);

        add_filter('query_vars', [$this, 'add_query_vars']);

        add_action('wp', [$this, 'do_social_login'], -99);

        add_action('wp_logout', [$this, 'destroy_hybridauth_session']);

        add_filter('ppress_error_log_settings', [$this, 'register_logger']);

        add_filter('ppress_settings_page_args', [$this, 'settings_page']);

        add_action('wp_cspa_header', [$this, 'social_login_tab_headers'], 10);

        add_shortcode('facebook-login-url', array(__CLASS__, 'facebook_login_url'));
        add_shortcode('twitter-login-url', array(__CLASS__, 'twitter_login_url'));
        add_shortcode('x-login-url', array(__CLASS__, 'twitter_login_url'));
        add_shortcode('linkedin-login-url', array(__CLASS__, 'linkedin_login_url'));
        add_shortcode('github-login-url', array(__CLASS__, 'github_login_url'));
        add_shortcode('microsoft-login-url', array(__CLASS__, 'microsoft_login_url'));
        add_shortcode('wordpresscom-login-url', array(__CLASS__, 'wordpresscom_login_url'));
        add_shortcode('google-login-url', array(__CLASS__, 'google_login_url'));
        add_shortcode('yahoo-login-url', array(__CLASS__, 'yahoo_login_url'));
        add_shortcode('amazon-login-url', array(__CLASS__, 'amazon_login_url'));
        add_shortcode('vk-login-url', array(__CLASS__, 'vk_login_url'));

        add_shortcode('pp-social-login', array(__CLASS__, 'pp_social_login_buttons'));
        //backward compat below
        add_shortcode('pp-social-button', array(__CLASS__, 'pp_social_login_buttons'));
        add_shortcode('social-button', array(__CLASS__, 'pp_social_login_buttons'));
    }

    public static function social_login_base_url($provider, $current_url)
    {
        $arg = [
            'pp_social_login' => $provider,
            'pp_current_url'  => rawurlencode(ppress_get_current_url_query_string()),
        ];

        if ($current_url === false) {
            unset($arg['pp_current_url']);
        }

        return apply_filters('ppress_social_login_url', add_query_arg($arg, wp_login_url()), $provider);
    }

    /**
     * Facebook social login url
     *
     * @param bool $current_url
     *
     * @return string
     */
    public static function facebook_login_url($current_url = true)
    {
        return self::social_login_base_url('facebook', $current_url);
    }

    /**
     * X/Twitter social login url
     *
     * @param bool $current_url
     *
     * @return string
     */
    public static function twitter_login_url($current_url = true)
    {
        return self::social_login_base_url('twitter', $current_url);
    }

    /**
     * LinkedIn social login url
     *
     * @param bool $current_url
     *
     * @return string
     */
    public static function linkedin_login_url($current_url = true)
    {
        return self::social_login_base_url('linkedin', $current_url);
    }

    /**
     * Yahoo social login url
     *
     * @param bool $current_url
     *
     * @return string
     */
    public static function yahoo_login_url($current_url = true)
    {
        return self::social_login_base_url('yahoo', $current_url);
    }

    /**
     * Amazon social login url
     *
     * @param bool $current_url
     *
     * @return string
     */
    public static function amazon_login_url($current_url = true)
    {
        return self::social_login_base_url('amazon', $current_url);
    }

    /**
     * Microsoft social login url
     *
     * @param bool $current_url
     *
     * @return string
     */
    public static function microsoft_login_url($current_url = true)
    {
        return self::social_login_base_url('microsoft', $current_url);
    }

    /**
     * Github social login url
     *
     * @param bool $current_url
     *
     * @return string
     */
    public static function github_login_url($current_url = true)
    {
        return self::social_login_base_url('github', $current_url);
    }

    /**
     * WordPress.com social login url
     *
     * @param bool $current_url
     *
     * @return string
     */
    public static function wordpresscom_login_url($current_url = true)
    {
        return self::social_login_base_url('wordpresscom', $current_url);
    }

    /**
     * Google social login url
     *
     * @param bool $current_url
     *
     * @return string
     */
    public static function google_login_url($current_url = true)
    {
        return self::social_login_base_url('google', $current_url);
    }

    /**
     * VK.com social login url
     *
     * @param bool $current_url
     *
     * @return string
     */
    public static function vk_login_url($current_url = true)
    {
        return self::social_login_base_url('vk', $current_url);
    }

    /**
     * Callback for social login form
     *
     * @param array $att
     *
     * @param string $content
     *
     * @return string
     */
    public static function pp_social_login_buttons($att, $content)
    {
        $sc   = shortcode_atts(['type' => 'facebook', 'redirect' => ''], $att);
        $type = strtolower($sc['type']);

        $type = ! empty($type) ? $type : 'facebook';

        if ($type == 'x') $type = 'twitter';

        $val = ppress_get_setting($type . '_button_label');

        $network_label = ppress_var(ppress_social_login_networks(), $type);

        $default_text = ! empty($val) ? esc_html($val) : sprintf(esc_html__('Sign in with %s', 'profilepress-pro'), $network_label);
        $text         = ( ! empty($content)) ? $content : $default_text;
        $url          = call_user_func([__CLASS__, "{$type}_login_url"]);

        $url = ! empty($sc['redirect']) ?
            add_query_arg('redirect_to', urlencode(esc_url_raw($sc['redirect'])), remove_query_arg('pp_current_url', $url)) :
            $url;

        return sprintf(
            '<a href="%3$s" class="pp-button-social-login pp-button-social-login-%1$s"><span class="ppsc ppsc-%1$s"></span><span class="ppsc-text">%2$s</span></a>',
            $type,
            $text,
            $url
        );
    }

    public function rewrite_rule()
    {
        add_rewrite_rule('ppauth/?([^/]*)', 'index.php?pagename=pp-auth-handler&pp_hybridauth_provider=$matches[1]', 'top');
    }

    public function add_query_vars($vars)
    {
        $vars[] = 'pp_hybridauth_provider';

        return $vars;
    }

    public function config($provider = '')
    {
        $callback = home_url();

        if ( ! empty($provider)) {

            if ($provider == 'vkontakte') {
                $provider = 'vk';
            }

            $callback = home_url('/ppauth/' . strtolower($provider), 'https');
        }

        if (defined('W3GUY_LOCAL') && $provider == 'google') {
            $callback = 'https://profilepress.com/?localcallback=google';
        }

        $microsoft_tenant = ppress_settings_by_key('microsoft_tenant', 'common', true);
        if ($microsoft_tenant === 'custom_tenant') {
            $microsoft_tenant = ppress_settings_by_key('microsoft_custom_tenant', '');
        }

        $config = apply_filters('ppress_social_login_config', [
            "callback"   => $callback,
            "providers"  => [
                "google"       => [
                    "enabled"             => true,
                    "supportRequestState" => false,
                    "scope"               => apply_filters(
                        'ppress_social_login_google_scope',
                        "https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email"
                    ), // optional
                    "keys"                => [
                        "id"     => trim(ppress_get_setting('google_client_id')),
                        "secret" => trim(ppress_get_setting('google_client_secret')),
                    ],
                    "approval_prompt"     => "auto",     // optional
                    "access_type"         => "online",     // optional
                ],
                "facebook"     => [
                    "enabled"             => true,
                    "supportRequestState" => false,
                    "keys"                => [
                        "id"     => trim(ppress_get_setting('facebook_id')),
                        "secret" => trim(ppress_get_setting('facebook_secret')),
                    ],
                    "trustForwarded"      => true,
                    'photo_size'          => 1000,
                    "scope"               => apply_filters('ppress_social_login_facebook_scope', "email, public_profile"),
                ],
                "twitter"      => [
                    "enabled" => true,
                    "keys"    => [
                        "key"    => trim(ppress_get_setting('twitter_consumer_key')),
                        "secret" => trim(ppress_get_setting('twitter_consumer_secret')),
                    ]
                ],
                "linkedin"     => [
                    "enabled"             => true,
                    "supportRequestState" => false,
                    "keys"                => [
                        "id"     => trim(ppress_get_setting('linkedin_consumer_key')),
                        "secret" => trim(ppress_get_setting('linkedin_consumer_secret')),
                    ],
                    "scope"               => 'r_liteprofile r_emailaddress'
                ],
                "microsoft"    => [
                    "enabled" => true,
                    'adapter' => 'ProfilePress\Libsodium\SocialLogin\PPMicrosoft',
                    'tenant'  => $microsoft_tenant,
                    "keys"    => [
                        "id"     => trim(ppress_get_setting('microsoft_client_id')),
                        "secret" => trim(ppress_get_setting('microsoft_client_secret')),
                    ]
                ],
                "yahoo"        => [
                    "enabled"             => true,
                    "supportRequestState" => false,
                    "keys"                => [
                        "id"     => trim(ppress_get_setting('yahoo_client_id')),
                        "secret" => trim(ppress_get_setting('yahoo_client_secret')),
                    ],
                    "scope"               => 'profile email'
                ],
                "amazon"       => [
                    "enabled"             => true,
                    "supportRequestState" => false,
                    "keys"                => [
                        "id"     => trim(ppress_get_setting('amazon_client_id')),
                        "secret" => trim(ppress_get_setting('amazon_client_secret')),
                    ]
                ],
                "github"       => [
                    "enabled" => true,
                    "keys"    => [
                        "id"     => trim(ppress_get_setting('github_client_id')),
                        "secret" => trim(ppress_get_setting('github_client_secret')),
                    ]
                ],
                "wordpresscom" => [
                    "enabled" => true,
                    'adapter' => 'ProfilePress\Libsodium\SocialLogin\PPWordPressCom',
                    "keys"    => [
                        "id"     => trim(ppress_get_setting('wpcom_client_id')),
                        "secret" => trim(ppress_get_setting('wpcom_client_secret')),
                    ]
                ],
                "vkontakte"    => [
                    "enabled" => true,
                    'adapter' => 'ProfilePress\Libsodium\SocialLogin\PPVkontakte',
                    "keys"    => [
                        "id"     => trim(ppress_get_setting('vk_application_id')),
                        "secret" => trim(ppress_get_setting('vk_secure_key')),
                    ]
                ]
            ],
            // If you want to enable logging, set 'debug_mode' to true.
            // You can also set it to
            // - "error" To log only error messages. Useful in production
            // - "info" To log info and error messages (ignore debug messages)
            "debug_mode" => apply_filters('ppress_social_login_debug', false),
            // Path to file writable by the web server. Required if 'debug_mode' is not false
            "debug_file" => dirname(__FILE__) . "/error.log",
        ]);

        return $config;
    }

    public function do_social_login()
    {
        $oauth_handler_page = get_query_var('pagename');
        $oauth_provider     = get_query_var('pp_hybridauth_provider');

        if ( ! isset($_GET['pp_social_login']) && ('pp-auth-handler' != $oauth_handler_page || empty($oauth_provider))) return;

        try {

            $provider = $original_provider = isset($_GET['pp_social_login']) ? sanitize_text_field($_GET['pp_social_login']) : $oauth_provider;

            if ($provider == 'vk') {
                $provider = 'vkontakte';
            }

            if ( ! function_exists('mb_strtolower')) {
                throw new \Exception('The mbstring extension is missing');
            }

            $config = $this->config($provider);

            if ($provider == 'linkedin' && ppress_get_setting('linkedin_api_version') == 'openid') {

                $provider = 'linkedinopenid';

                $args = $config['providers']['linkedin'];
                unset($args['scope']);

                unset($config['providers']['linkedin']);
                $config['providers']['linkedinopenid'] = $args;
            }

            $hybridauth = new Hybridauth(
                $config,
                null,
                new PPSessionAdapter()
            );

            $adapter = $hybridauth->getAdapter($provider);

            do_action('ppress_before_social_login_init', $config, $hybridauth);

            if ( ! empty($_GET['redirect_to'])) {
                $adapter->getStorage()->set('ppsc_redirect_to', esc_url_raw($_GET['redirect_to']));
            }

            if ( ! empty($_GET['pp_current_url'])) {
                $adapter->getStorage()->set('ppsc_current_url', esc_url_raw($_GET['pp_current_url']));
            }

            $adapter = $hybridauth->authenticate($provider);

            if (is_object($adapter)) {

                $user_profile = $adapter->getUserProfile();

                do_action('ppress_before_social_login_process', $user_profile, $original_provider);

                $saved_redirect_to_url = $adapter->getStorage()->get('ppsc_redirect_to');
                $saved_current_url     = $adapter->getStorage()->get('ppsc_current_url');

                if ( ! empty($saved_redirect_to_url)) {
                    // shim to restore back the current URL
                    $_GET['redirect_to']     = esc_url_raw($saved_redirect_to_url);
                    $_REQUEST['redirect_to'] = esc_url_raw($saved_redirect_to_url);
                    // clear session
                    $adapter->getStorage()->set('ppsc_redirect_to', null);
                }

                if ( ! empty($saved_current_url)) {
                    // shim to restore back the current URL
                    $_GET['pp_current_url'] = esc_url_raw($saved_current_url);
                    // clear session
                    $adapter->getStorage()->set('ppsc_current_url', null);
                }

                $adapter->disconnect();

                new ProcessSocialLogin($user_profile, $original_provider);
            }

            throw new \Exception('Error in retrieving profile data from authenticated social service.');

        } catch (\Exception $e) {

            ppress_log_error($e->getMessage(), 'social-login');

            $this->destroy_hybridauth_session();

            wp_safe_redirect(add_query_arg('pp-sl-error', 'true', ppress_login_url()));
            exit;
        }
    }

    public function destroy_hybridauth_session()
    {
        try {
            // fixes fatal error when mbstring extension isn't enabled
            // PHP Fatal error: Uncaught Error: Call to undefined function Hybridauth\mb_strtolower() in
            if (function_exists('mb_strtolower')) {
                (new Hybridauth($this->config()))->disconnectAllAdapters();
            }
        } catch (\Exception $e) {
        }
    }

    public function social_login_tab_headers($args)
    {
        if (count(array_intersect(['facebook_id', 'facebook_button_label', 'social_login_integration_shortcode'], array_keys($args))) > 0) {
            ?>
            <div id="pp-sub-bar" style="margin-top: -10px;">
                <div class="pp-new-toolbar pp-clear" style="padding: 10px 0;min-height: 30px;border-right: 0;border-left: 0;border-top: 0;">
                    <ul class="pp-design-options" style="float: none;margin: 13px 0">
                        <li style="margin: -4px -1px;">
                            <a style="padding: 18px 15px" href="<?= esc_url_raw(add_query_arg('ppsc', 'settings')); ?>#social_login_settings" class="<?= empty($_GET['ppsc']) || $_GET['ppsc'] == 'settings' ? 'pp-type-active' : ''; ?>">
                                <?= esc_html__('Settings', 'profilepress-pro') ?>
                            </a>
                        </li>
                        <li style="margin: -4px -1px;">
                            <a style="padding: 18px 15px" href="<?= esc_url_raw(add_query_arg('ppsc', 'buttons')); ?>#social_login_settings" class="<?= isset($_GET['ppsc']) && $_GET['ppsc'] == 'buttons' ? 'pp-type-active' : ''; ?>">
                                <?= esc_html__('Buttons', 'profilepress-pro') ?>
                            </a>
                        </li>
                        <li style="margin: -4px -1px;">
                            <a style="padding: 18px 15px" href="<?= esc_url_raw(add_query_arg('ppsc', 'integrations')); ?>#social_login_settings" class="<?= isset($_GET['ppsc']) && $_GET['ppsc'] == 'integrations' ? 'pp-type-active' : ''; ?>">
                                <?= esc_html__('Integrations', 'profilepress-pro') ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <?php
        }
    }

    public function settings_page($settings)
    {
        if (empty($_GET['ppsc']) || $_GET['ppsc'] == 'settings') {
            $settings['social_login_settings'] = apply_filters('ppress_social_login_settings_page', [
                    'tab_title' => esc_html__('Social Login', 'profilepress-pro'),
                    'dashicon'  => 'dashicons-networking',
                    [
                        'section_title'   => esc_html__('Facebook Settings', 'profilepress-pro'),
                        'facebook_id'     => [
                            'type'  => 'text',
                            'label' => esc_html__('Facebook App ID', 'profilepress-pro')
                        ],
                        'facebook_secret' => [
                            'type'          => 'text',
                            'obfuscate_val' => true,
                            'label'         => esc_html__('Facebook App Secret', 'profilepress-pro')
                        ],
                    ],
                    [
                        'section_title'           => esc_html__('X/Twitter Settings', 'profilepress-pro'),
                        'twitter_consumer_key'    => [
                            'type'  => 'text',
                            'label' => esc_html__('API Key', 'profilepress-pro')
                        ],
                        'twitter_consumer_secret' => [
                            'type'          => 'text',
                            'obfuscate_val' => true,
                            'label'         => esc_html__('API Secret Key', 'profilepress-pro')
                        ]
                    ],
                    [
                        'section_title'        => esc_html__('Google Settings', 'profilepress-pro'),
                        'google_client_id'     => [
                            'type'  => 'text',
                            'label' => esc_html__('Client ID', 'profilepress-pro')
                        ],
                        'google_client_secret' => [
                            'type'          => 'text',
                            'obfuscate_val' => true,
                            'label'         => esc_html__('Client secret', 'profilepress-pro')
                        ]
                    ],
                    [
                        'section_title'            => esc_html__('LinkedIn Settings', 'profilepress-pro'),
                        'linkedin_consumer_key'    => [
                            'type'  => 'text',
                            'label' => esc_html__('Client ID', 'profilepress-pro')
                        ],
                        'linkedin_consumer_secret' => [
                            'type'          => 'text',
                            'obfuscate_val' => true,
                            'label'         => esc_html__('Client Secret', 'profilepress-pro')
                        ],
                        'linkedin_api_version'     => [
                            'type'    => 'select',
                            'options' => [
                                'openid'     => esc_html__('(New) Using OpenID', 'profilepress-pro'),
                                'deprecated' => esc_html__('Deprecated Version', 'profilepress-pro')
                            ],
                            'label'   => esc_html__('API Version', 'profilepress-pro')
                        ]
                    ],
                    [
                        'section_title'       => esc_html__('Yahoo Settings', 'profilepress-pro'),
                        'yahoo_client_id'     => [
                            'type'  => 'text',
                            'label' => esc_html__('Client ID', 'profilepress-pro')
                        ],
                        'yahoo_client_secret' => [
                            'type'          => 'text',
                            'obfuscate_val' => true,
                            'label'         => esc_html__('Client Secret', 'profilepress-pro')
                        ]
                    ],
                    [
                        'section_title'           => esc_html__('Microsoft Settings', 'profilepress-pro'),
                        'microsoft_client_id'     => [
                            'type'  => 'text',
                            'label' => esc_html__('Application (client) ID', 'profilepress-pro')
                        ],
                        'microsoft_client_secret' => [
                            'type'          => 'text',
                            'obfuscate_val' => true,
                            'label'         => esc_html__('Client Secret', 'profilepress-pro')
                        ],
                        'microsoft_tenant'        => [
                            'type'  => 'custom_field_block',
                            'label' => esc_html__('Audience', 'profilepress-pro'),
                            'data'  => (function () {

                                $tenant        = ppress_settings_by_key('microsoft_tenant', 'common', true);
                                $custom_tenant = ppress_settings_by_key('microsoft_custom_tenant', '');

                                ob_start();
                                ?>
                                <fieldset>
                                    <label>
                                        <input type="radio" name="ppress_settings_data[microsoft_tenant]" value="organizations" <?php checked($tenant, 'organizations') ?>>
                                        <span><?php _e('Accounts in any organizational directory (any work and school accounts)', 'profilepress-pro'); ?></span>
                                    </label><br> <label>
                                        <input type="radio" name="ppress_settings_data[microsoft_tenant]" value="common" <?php checked($tenant, 'common') ?>>
                                        <span><?php _e('Accounts in any organizational directory (any work and school accounts) or personal Microsoft accounts.', 'profilepress-pro'); ?></span>
                                    </label><br> <label>
                                        <input type="radio" name="ppress_settings_data[microsoft_tenant]" value="consumers" <?php checked($tenant, 'consumers') ?>>
                                        <span><?php _e('Personal Microsoft accounts only', 'profilepress-pro'); ?></span>
                                    </label><br> <label>
                                        <input type="radio" name="ppress_settings_data[microsoft_tenant]" value="custom_tenant" <?php checked($tenant, 'custom_tenant') ?>>
                                        <span><?php _e('Only users in an organizational directory from a particular Azure AD tenant. Enter the tenant ID or domain here.', 'profilepress-pro'); ?></span>
                                        <input name="ppress_settings_data[microsoft_custom_tenant]" type="text" value="<?php echo esc_attr($custom_tenant); ?>" class="regular-text">
                                    </label><br>
                                </fieldset>
                                <?php
                                return ob_get_clean();
                            })()
                        ]
                    ],
                    [
                        'section_title'        => esc_html__('Amazon Settings', 'profilepress-pro'),
                        'amazon_client_id'     => [
                            'type'  => 'text',
                            'label' => esc_html__('Client ID', 'profilepress-pro')
                        ],
                        'amazon_client_secret' => [
                            'type'          => 'text',
                            'obfuscate_val' => true,
                            'label'         => esc_html__('Client Secret', 'profilepress-pro')
                        ]
                    ],
                    [
                        'section_title'        => esc_html__('GitHub Settings', 'profilepress-pro'),
                        'github_client_id'     => [
                            'type'  => 'text',
                            'label' => esc_html__('Client ID', 'profilepress-pro')
                        ],
                        'github_client_secret' => [
                            'type'          => 'text',
                            'obfuscate_val' => true,
                            'label'         => esc_html__('Client secret', 'profilepress-pro')
                        ]
                    ],
                    [
                        'section_title'       => esc_html__('WordPress.com Settings', 'profilepress-pro'),
                        'wpcom_client_id'     => [
                            'type'  => 'text',
                            'label' => esc_html__('Client ID', 'profilepress-pro')
                        ],
                        'wpcom_client_secret' => [
                            'type'          => 'text',
                            'obfuscate_val' => true,
                            'label'         => esc_html__('Client secret', 'profilepress-pro')
                        ]
                    ],
                    [
                        'section_title'     => esc_html__('VK Settings', 'profilepress-pro'),
                        'vk_application_id' => [
                            'type'  => 'text',
                            'label' => esc_html__('Application ID', 'profilepress-pro')
                        ],
                        'vk_secure_key'     => [
                            'type'          => 'text',
                            'obfuscate_val' => true,
                            'label'         => esc_html__('Secure key', 'profilepress-pro')
                        ]
                    ]
                ]
            );
        }

        if (isset($_GET['ppsc']) && $_GET['ppsc'] == 'buttons') {
            $social_login_settings = [
                'tab_title'     => esc_html__('Social Login', 'profilepress-pro'),
                'section_title' => esc_html__('Button Texts', 'profilepress-pro')
            ];

            foreach (ppress_social_login_networks() as $network => $network_label) {

                $social_login_settings[sprintf('%s_button_label', $network)] = [
                    'type'  => 'text',
                    'label' => $network_label
                ];
            }

            $settings['social_login_settings'] = apply_filters('ppress_social_login_settings_page_button', $social_login_settings);
        }

        if (isset($_GET['ppsc']) && $_GET['ppsc'] == 'integrations') {

            $shortcode_value     = '';
            $adv_shortcode_value = '';
            $link_value          = '';

            foreach (ppress_social_login_networks() as $network_id => $network_label) {

                $shortcode_value .= sprintf('[pp-social-login type="%s"]', $network_id) . "\r\n\r\n";

                $adv_shortcode_value .= sprintf('[pp-social-login type="%s"]Log in with %s[/pp-social-login]', $network_id, $network_label) . "\r\n\r\n";

                $link_value .= sprintf('<a href="%s">Sign in with %s</a>', call_user_func([__CLASS__, "{$network_id}_login_url"], false), $network_label) . "\r\n\r\n";
            }

            $settings['social_login_settings'] = apply_filters('ppress_social_login_settings_page_integrations', [
                    'tab_title'                                  => esc_html__('Social Login', 'profilepress-pro'),
                    'section_title'                              => esc_html__('Integrations', 'profilepress-pro'),
                    'disable_submit_button'                      => true,
                    'social_login_integration_shortcode'         => [
                        'skip_name'   => true,
                        'type'        => 'codemirror',
                        'value'       => $shortcode_value,
                        'label'       => esc_html__('Shortcode', 'profilepress-pro'),
                        'description' => esc_html__('Use the "redirect" attribute to specify a url to redirect users to after social login.', 'profilepress-pro')
                    ],
                    'social_login_integration_advance_shortcode' => [
                        'skip_name'   => true,
                        'type'        => 'codemirror',
                        'value'       => $adv_shortcode_value,
                        'label'       => esc_html__('Advanced Shortcode', 'profilepress-pro'),
                        'description' => esc_html__('This is typically useful if you want to change the button text.', 'profilepress-pro')
                    ],
                    'social_login_integration_link'              => [
                        'skip_name'   => true,
                        'type'        => 'codemirror',
                        'value'       => $link_value,
                        'label'       => esc_html__('HTML Link', 'profilepress-pro'),
                        'description' => esc_html__('This is typically used if you want to change the button text.', 'profilepress-pro')
                    ],
                ]
            );
        }

        return $settings;
    }

    public function register_logger($loggers)
    {
        $social_login_log_content    = ppress_get_error_log('social-login');
        $delete_social_login_log_url = esc_url_raw(add_query_arg(['ppress-delete-log' => 'social-login', '_wpnonce' => ppress_create_nonce()]));

        $loggers[] = [
            'section_title'            => esc_html__('Social Login Error Log', 'profilepress-pro'),
            'disable_submit_button'    => true,
            'social_login_log_content' => [
                'type'        => 'arbitrary',
                'data'        => sprintf(
                    '<textarea class="ppress-error-log-textarea" disabled>%s</textarea>',
                    $social_login_log_content
                ),
                'description' => sprintf(
                    '<div style="margin-top: 10px"><a class="button pp-confirm-delete" href="%s">%s</a></div>', $delete_social_login_log_url,
                    esc_html__('Delete Log', 'profilepress-pro')
                )
            ]
        ];

        return $loggers;
    }

    /**
     * @return self|void
     */
    public static function get_instance()
    {
        self::$instance_flag = true;

        if ( ! defined('ProfilePress\Core\Classes\ExtensionManager::SOCIAL_LOGIN')) return;

        if ( ! EM::is_enabled(EM::SOCIAL_LOGIN)) return;

        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}