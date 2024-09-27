<?php

namespace ProfilePress\Libsodium\MeteredPaywall;

use ProfilePress\Core\ContentProtection\Frontend\PostContent;
use ProfilePressVendor\Carbon\CarbonImmutable;

class DoRestriction
{
    public $post_id = 0;

    public $total_count = 0;

    public function __construct()
    {
        add_filter('ppress_content_protection_is_blocked', [$this, 'do_content_restrictions'], 999);

        add_action('ppress_after_registration', function () {
            setcookie($this->get_cookie_name(), '', 1, '/');
        });

        add_action('wp_footer', [$this, 'countdown_slide_box']);

        // if rule action is redirect, template_redirect already runs without any output before header is sent, hence do nothing.
        // we are running the content protection rule for "show restricted message" type because we want to set cookie before header is sent
        add_action('wp', function () {
            PostContent::get_instance()->the_content('');

            add_filter('ppress_is_prevent_cache', function ($result) {
                if ($this->content_matches_restriction_rules()) $result = true;

                return $result;
            });

        }, -1);
    }

    public function prevent_page_caching($result)
    {
        if ($this->content_matches_restriction_rules()) {
            $result = true;
        }

        return $result;
    }

    public function do_content_restrictions($is_blocked)
    {
        static $cache = null;

        if (is_null($cache)) {

            if (
                ! is_user_logged_in() ||
                (apply_filters('ppress_metered_paywall_logged_in_user_check_support', false) && is_user_logged_in())
            ) {

                global $post;

                $this->post_id = isset($post->ID) && absint($post->ID) > 0 ? $post->ID : get_queried_object_id();

                if ($is_blocked === true) {

                    if ($this->content_matches_restriction_rules()) {

                        if ( ! $this->is_exceeded_free_view_limit()) {
                            $is_blocked = false;
                            $this->update_content_viewed_by_user();
                        } else {
                            do_action('ppress_metered_paywall_exceeded_free_view_limit', $this->post_id);
                        }

                    }
                }
            }

            $cache = $is_blocked;

        } else {
            $is_blocked = $cache;
        }

        return $is_blocked;
    }

    public function is_exceeded_free_view_limit()
    {
        $callback = function () {
            $viewed_content    = $this->get_content_viewed_by_user();
            $restrictions      = $this->get_restrictions();
            $content_post_type = get_post_type($this->post_id);

            $combined_restrictions = apply_filters('ppress_metered_paywall_combined_free_view_total', intval(self::get_settings('combined_free_view_total')));

            foreach ($restrictions as $restriction) {

                $tax = ! empty($restriction['tax']) ? $restriction['tax'] : 'all';

                $view_count = apply_filters('ppress_metered_paywall_view_count', absint($restriction['count']), $restriction);

                if ($restriction['post_type'] == $content_post_type) {

                    if ($combined_restrictions > 0) {

                        $allowed_value         = $combined_restrictions;
                        $number_already_viewed = isset($viewed_content[$content_post_type]) ? $this->get_total_content_viewed() : 0;

                    } else {

                        $allowed_value = $view_count;

                        if ($this->content_taxonomy_matches($tax)) {
                            $number_already_viewed = isset($viewed_content[$content_post_type]) ? $this->get_number_viewed_by_term($tax) : 0;
                        } else {
                            $number_already_viewed = isset($viewed_content[$content_post_type]) ? count($viewed_content[$content_post_type]) : 0;
                        }
                    }

                    $this->total_count = $allowed_value;

                    // If content already viewed, then let visitors view it.
                    if (isset($viewed_content[$content_post_type]) && in_array($this->post_id, array_keys($viewed_content[$content_post_type]))) {
                        return false;
                    }

                    if (0 == $allowed_value) return true;

                    if ( ! empty($viewed_content) && $number_already_viewed >= $allowed_value) {
                        return true;
                    }

                    $this->update_content_viewed_by_user();

                    return false;
                }
            }

            return true;
        };

        static $cache = null;

        if (is_null($cache)) {
            $cache = $callback();
        }

        return apply_filters('ppress_metered_paywall_is_exceeded_free_view_limit', $cache, $this->post_id);
    }

    public static function get_settings($key = '', $default = '')
    {
        $cache_key = sprintf('get_settings_%s', $key);

        static $cache = [];

        if ( ! isset($cache[$cache_key])) {

            $cache[$cache_key] = get_option('ppress_limit_post_views', []);

            if ( ! empty($key)) {
                $cache[$cache_key] = ppress_var($cache[$cache_key], $key, $default, true);
            }
        }

        return $cache[$cache_key];
    }

    protected function get_restrictions()
    {
        return self::get_settings('restriction');
    }

    public static function is_ip_blocker_enabled()
    {
        return self::get_settings('enable_ip_blocker') == 'yes';
    }

    public function content_matches_restriction_rules()
    {
        $callback = function () {

            if ( ! is_singular()) return false;

            if ($this->is_excluded_pages()) return false;

            if ($this->content_restricted_by_settings()) return true;

            return false;
        };

        static $cache = null;

        if (is_null($cache)) {
            $cache = $callback();
        }

        return $cache;
    }

    protected function is_excluded_pages()
    {
        $page_ids = array_filter([
            ppress_settings_by_key("set_login_url", false, true),
            ppress_settings_by_key("set_registration_url", false, true),
            ppress_settings_by_key("checkout_page_id", false, true),
            ppress_settings_by_key("payment_success_page_id", false, true),
            ppress_settings_by_key("edit_user_profile_url", false, true),
        ]);

        if (in_array($this->post_id, apply_filters('ppress_metered_paywall_excluded_pages', $page_ids))) {
            return true;
        }

        return apply_filters('ppress_metered_paywall_is_excluded_pages', false, $this->post_id);
    }

    protected function content_restricted_by_settings()
    {
        $restrictions = $this->get_restrictions();

        if (empty($restrictions)) return false;

        $content_post_type = get_post_type($this->post_id);

        foreach ($restrictions as $restriction) {

            $restriction_taxomony = isset($restriction['tax']) ? $restriction['tax'] : 'all';

            if ($restriction['post_type'] == $content_post_type && 'all' === $restriction_taxomony) {
                return true;
            }

            if ($restriction['post_type'] === $content_post_type && $this->content_taxonomy_matches($restriction_taxomony)) {
                return true;
            }
        }

        return false;
    }

    protected function content_taxonomy_matches($restricted_term_id)
    {
        $taxonomies = get_post_taxonomies($this->post_id);

        foreach ($taxonomies as $taxonomy) {

            $terms = get_the_terms($this->post_id, $taxonomy);

            if ($terms) {

                foreach ($terms as $term) {

                    if ($term->term_id == $restricted_term_id) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected function get_cookie_name()
    {
        $id = 'ppress_mv';

        if(isset($_ENV['PANTHEON_ENVIRONMENT'])) {
            $id = 'STYXKEY_' . $id;
        }

        if (is_multisite() && ! is_main_site()) $id = '_' . get_current_blog_id();

        return apply_filters('ppress_metered_paywall_cookie_name', $id);
    }

    protected function update_content_viewed_by_user()
    {
        static $cache = null;

        if (is_null($cache)) {
            $cache = false;

            $viewed_content = [];

            if ( ! empty($_COOKIE[$this->get_cookie_name()])) {

                $viewed_content = \json_decode(
                    \sanitize_text_field(
                        \wp_unslash($_COOKIE[$this->get_cookie_name()])
                    ),
                    true
                );
            }

            $expiration_time = $this->get_expiration_time();

            $restricted_post_type                                  = \get_post_type($this->post_id);
            $viewed_content[$restricted_post_type][$this->post_id] = $expiration_time;
            $json_viewed_content                                   = \wp_json_encode($viewed_content);

            setcookie($this->get_cookie_name(), $json_viewed_content, $expiration_time, '/');
            $_COOKIE[$this->get_cookie_name()] = $json_viewed_content;
        }
    }

    protected function get_expiration_time()
    {
        $value = self::get_settings('cookie_expiration');
        $unit  = self::get_settings('cookie_interval');

        $expiration = CarbonImmutable::now('UTC')->add($unit, $value)->getTimestamp();

        return apply_filters('ppress_metered_paywall_expiration_time', $expiration);
    }

    protected function get_content_viewed_by_user()
    {
        if ( ! empty($_COOKIE[$this->get_cookie_name()])) {
            $content_viewed = json_decode(sanitize_text_field(wp_unslash($_COOKIE[$this->get_cookie_name()])), true);
        } else {
            $content_viewed = array();
        }

        return apply_filters('ppress_metered_paywall_viewed_content', $content_viewed);
    }

    /**
     * Go through each content item viewed and see if its term matches any restrictions.
     *
     * @param int $term_id .
     */
    public function get_number_viewed_by_term($term_id)
    {
        $viewed_content = $this->get_content_viewed_by_user();

        $num = 0;

        foreach ($viewed_content as $items) {

            foreach ($items as $item) {

                if ('all' === $term_id) {
                    $num++;
                } elseif ($this->content_taxonomy_matches($term_id)) {
                    $num++;
                }
            }
        }

        return $num;
    }

    /**
     * Calculate all content items that have been viewed the current user
     *
     * @return int
     */
    protected function get_total_content_viewed()
    {
        $viewed_content = $this->get_content_viewed_by_user();

        $total_viewed = 0;

        foreach ($viewed_content as $content) {

            $total_viewed += count($content);
        }

        return $total_viewed;
    }

    public function countdown_slide_box()
    {
        if (self::get_settings('enable_countdown', false) != 'true') return;

        if ( ! $this->content_matches_restriction_rules()) return;

        if (apply_filters('ppress_metered_paywall_countdown_slide_box_is_exceeded_free_view_limit', $this->is_exceeded_free_view_limit())) return;

        if ($this->post_id <= 0) return;

        $message     = self::get_settings('message', esc_html__('posts remaining', 'profilepress-pro'));
        $button_text = self::get_settings('button_text', esc_html__('Subscribe Now', 'profilepress-pro'));
        $button_link = self::get_settings('button_link', '#');
        $login_text  = self::get_settings('login_text', esc_html__('Have an account? Login', 'profilepress-pro'));

        ob_start();
        ?>
        <style>
            .ppress-metered-paywall-countdown-wrap {
                display: none;
                position: fixed;
                z-index: 12000;
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                box-sizing: border-box;
                background: #fff;
                padding: 24px;
                max-width: 360px;
                width: 100%;
                /*border: 1px solid #dedede;*/
                border-left: 10px solid #00A5E3;
                box-shadow: 0 5px 35px rgb(0 0 0 / 17%);
                bottom: 25px;
            }

            .ppress-metered-paywall-bottom-right {
                right: 0;
            }

            .ppress-metered-paywall-bottom-left {
                left: 0;
            }

            .ppress-metered-paywall-countdown-content > :first-child {
                margin-top: 0;
                padding-top: 0;
            }

            .ppress-metered-paywall-countdown-headline {
                font-size: 1.5em;
                width: 90%;
                margin: 0 auto;
            }

            .ppress-metered-paywall-countdown-count {
                color: #A52A2A;
                float: left;
                font-size: 5em;
                margin: 0 .1em 0 0;
                line-height: 0.85;
            }

            .ppress-metered-paywall-countdown-close-icon {
                position: absolute;
                right: 0;
                top: 0;
                text-align: center;
                padding: 6px;
                cursor: pointer;
                -webkit-appearance: none;
                font-size: 28px;
                font-weight: 700;
                line-height: 20px;
                color: #000;
                opacity: .5;
            }

            .ppress-metered-paywall-countdown-body a.ppress-metered-paywall-countdown-content-button {
                background: #0073CC;
                padding: .5em;
                display: inline-block;
                border: 2px solid #0073CC;
                color: #ffffff;
                font-size: 1em;
                text-decoration: none;
                line-height: 100%;
                margin: 8px 0;
                box-shadow: 0px 0px 1px 0px rgb(0 0 0 / 40%);
                border-radius: 3px;
            }

            .ppress-metered-paywall-countdown-body a {
                color: #0073CC;
            }

            .ppress-metered-paywall-countdown-body .ppress-link {
                text-decoration: underline;
                display: block;
            }
        </style>
        <div class="ppress-metered-paywall-countdown-wrap ppress-metered-paywall-bottom-left">
            <div class="ppress-metered-paywall-countdown-inner">
                <div class="ppress-metered-paywall-countdown-content">
                    <div class="ppress-metered-paywall-countdown-count"><?php echo max($this->total_count - $this->get_total_content_viewed(), 0) ?></div>
                    <div class="ppress-metered-paywall-countdown-headline"><?php echo $message ?></div>
                    <div class="ppress-metered-paywall-countdown-body">
                        <a class="ppress-metered-paywall-countdown-content-button" href="<?php echo $button_link; ?>"><?php echo $button_text; ?></a>
                        <a class="ppress-link" href="<?php echo wp_login_url(ppress_get_current_url_query_string()) ?>"><?php echo $login_text; ?></a>
                    </div>
                </div>
            </div>
            <span class="ppress-metered-paywall-countdown-close-icon">×</span>
        </div>
        <script type="text/javascript">
            (function ($) {
                var init = function () {
                    $('.ppress-metered-paywall-countdown-wrap').fadeIn();
                    $(document).on('click', '.ppress-metered-paywall-countdown-close-icon', function () {
                        $(this).parents('.ppress-metered-paywall-countdown-wrap').fadeOut();
                    });
                };
                $(setTimeout(function () {
                    init();
                }, 2000));

            })(jQuery)
        </script>
        <?php

        $slide_in = ob_get_clean();

        echo $slide_in;
    }
}