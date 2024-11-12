<?php

namespace ProfilePress\Core\Membership\Controllers;

use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Group\GroupFactory;
use ProfilePress\Core\Membership\Models\Plan\PlanFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;

class FrontendController extends BaseController
{
    public function __construct()
    {
        add_action('wp', [$this, 'prevent_caching'], 0);
        add_action('wp', [$this, 'change_plan_shim'], 0);
    }

    public function change_plan_shim()
    {
        if (ppress_is_checkout()) {

            if (is_user_logged_in() && isset($_GET['plan'])) {

                $plan_group_id = PlanFactory::fromId(intval($_GET['plan']))->get_group_id();

                if (is_int($plan_group_id)) {

                    $plan_group = GroupFactory::fromId($plan_group_id);

                    $customer = CustomerFactory::fromUserId(get_current_user_id());

                    foreach ($plan_group->get_plan_ids() as $plan_id) {
                        if ($sub = $customer->has_active_subscription($plan_id, true)) {
                            wp_safe_redirect(ppress_plan_checkout_url($sub->get_id(), true));
                            exit;
                        }
                    }
                }
            }

            if (isset($_GET['change_plan'])) {
                $sub = SubscriptionFactory::fromId(intval($_GET['change_plan']));
                if ($sub->exists()) {
                    $_GET['plan'] = $sub->get_plan_id();
                }
            }

            if (isset($_GET['group'])) {
                $group = GroupFactory::fromId(intval($_GET['group']));
                if ($group->exists()) {
                    $group_plan_ids = $group->get_plan_ids();
                    if ( ! empty($group_plan_ids)) {
                        $_GET['plan'] = $group->get_default_plan_id();
                    }
                }
            }
        }
    }

    /**
     * Prevent caching on dynamic pages.
     */
    public function prevent_caching()
    {
        if ( ! is_blog_installed()) {
            return;
        }

        $page_ids = array_filter([
            ppress_settings_by_key('set_login_url', false, true),
            ppress_settings_by_key('set_registration_url', false, true),
            ppress_settings_by_key('checkout_page_id', false, true),
            ppress_settings_by_key('payment_success_page_id', false, true),
            ppress_settings_by_key('edit_user_profile_url', false, true)
        ]);

        $do_not_cache = apply_filters('ppress_no_cache', ( ! empty($page_ids)) ? true : false);

        if (apply_filters('ppress_is_prevent_cache', $do_not_cache && is_page($page_ids))) {

            add_filter('nocache_headers', [$this, 'additional_nocache_headers'], 99);

            ppress_maybe_define_constant('DONOTCACHEPAGE', true);
            ppress_maybe_define_constant('DONOTCACHEOBJECT', true);
            ppress_maybe_define_constant('DONOTCACHEDB', true);
            nocache_headers();
            $this->exclude_page_from_wpe_server_cache();
            $this->exclude_page_from_pantheon_server_cache();
            remove_filter('nocache_headers', [$this, 'additional_nocache_headers'], 99);
        }
    }

    /**
     * Set additional nocache headers.
     *
     * @param array $headers {
     *     Header names and field values.
     *
     * @type string $Expires Expires header.
     * @type string $Cache -Control Cache-Control header.
     * }
     * @return array
     * @see wp_get_nocache_headers()
     *
     */
    public function additional_nocache_headers($headers)
    {
        // First tree are the default ones.
        $nocache_headers_cache_control = array(
            'no-cache',
            'must-revalidate',
            'max-age=0',
            'no-store',
        );

        if ( ! empty($headers['Cache-Control'])) {
            $original_headers_cache_control = array_map('trim', explode(',', $headers['Cache-Control']));
            // Merge original headers with our nocache headers.
            $nocache_headers_cache_control = array_merge($nocache_headers_cache_control, $original_headers_cache_control);
            // Avoid duplicates.
            $nocache_headers_cache_control = array_unique($nocache_headers_cache_control);
        }

        $headers['Cache-Control'] = implode(', ', $nocache_headers_cache_control);

        return $headers;

    }

    /**
     * Sets a browser cookie that tells WP Engine to exclude a page from server caching.
     *
     * @see https://wpengine.com/support/cache/#Default_Cache_Exclusions
     * @see https://wpengine.com/support/determining-wp-engine-environment/
     *
     * @return void
     */
    public function exclude_page_from_wpe_server_cache()
    {
        if (function_exists('is_wpe') && is_wpe()) {
            /*
             * If "Settings -> Permalinks" is "Plain", i.e. the `permalink_structure` option is '',
             * allow the entire site to be cached by WP Engine.
             * Note: This will prevent users from being able to successfully use the "Lost your password?" feature.
             */
            if (isset($GLOBALS['wp_rewrite']) && ! $GLOBALS['wp_rewrite']->using_permalinks()) {
                return;
            }

            $path          = wp_parse_url(get_permalink(), PHP_URL_PATH);
            $cookie_domain = ! defined('COOKIE_DOMAIN') ? false : COOKIE_DOMAIN;
            setcookie('wordpress_wpe_no_cache', '1', 0, $path, $cookie_domain, is_ssl(), true);
        }
    }

    /**
     * Sets a browser cookie that tells Pantheon to exclude a page from server caching.
     *
     * @see https://docs.pantheon.io/cookies#disable-caching-for-specific-pages
     *
     * @return void
     */
    public function exclude_page_from_pantheon_server_cache()
    {
        if (apply_filters('ppress_enable_pantheon_caching_exclusion', true) && !empty($_ENV['PANTHEON_ENVIRONMENT'])) {
            $domain = $_SERVER['HTTP_HOST'];
            $path   = wp_parse_url(get_permalink(), PHP_URL_PATH);
            setcookie('NO_CACHE', '1', time() + 0, $path, $domain);
        }
    }
}