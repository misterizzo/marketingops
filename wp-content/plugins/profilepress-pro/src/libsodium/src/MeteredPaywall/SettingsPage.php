<?php

namespace ProfilePress\Libsodium\MeteredPaywall;

use ProfilePress\Core\Admin\SettingsPages\AbstractSettingsPage;
use ProfilePress\Custom_Settings_Page_Api;

class SettingsPage
{
    public function __construct()
    {
        add_filter('ppress_membership_content_protection_settings_page_tabs', [$this, 'menu_tab']);

        add_action('ppress_admin_settings_page_metered-paywall', [$this, 'settings_page_function']);

        add_filter('ppress_content_protection_settings_page_title', [$this, 'admin_page_title']);

        add_action('admin_footer', [$this, 'js_script']);

        add_action('admin_init', [$this, 'clear_ip_log']);
    }

    /**
     * Add options page and settings
     */
    public function menu_tab($tabs)
    {
        $tabs[10] = [
            'id'    => 'metered-paywall',
            'url'   => add_query_arg('view', 'metered-paywall', PPRESS_CONTENT_PROTECTION_SETTINGS_PAGE),
            'label' => esc_html__('Metered Paywall', 'profilepress-pro')
        ];

        return $tabs;
    }

    public function admin_page_title($title = '')
    {
        if (ppressGET_var('view') == 'metered-paywall') {
            $title = esc_html__('Metered Paywall', 'profilepress-pro');
        }

        return $title;
    }

    public function settings_page_function()
    {
        $saved_data = get_option('ppress_limit_post_views', []);

        $settings = [
            [
                'section_title'            => '',
                'cookie'                   => [
                    'type'  => 'custom_field_block',
                    'label' => esc_html__('Reset limitation after', 'profilepress-pro'),
                    'data'  => (function ($saved_data) {
                        $cookie_interval = ppress_var($saved_data, 'cookie_interval', '');
                        ob_start();
                        ?>
                        <input type="number" class="small-text" name="ppress_limit_post_views[cookie_expiration]" value="<?php echo ppress_var($saved_data, 'cookie_expiration', ''); ?>">
                        <select id="cookie_expiration_interval" name="ppress_limit_post_views[cookie_interval]">
                            <option value="hour" <?php selected('hour', $cookie_interval); ?>><?php esc_attr_e('Hour(s)', 'profilepress-pro'); ?></option>
                            <option value="day" <?php selected('day', $cookie_interval); ?>><?php esc_attr_e('Day(s)', 'profilepress-pro'); ?></option>
                            <option value="week" <?php selected('week', $cookie_interval); ?>><?php esc_attr_e('Week(s)', 'profilepress-pro'); ?></option>
                            <option value="month" <?php selected('month', $cookie_interval); ?>><?php esc_attr_e('Month(s)', 'profilepress-pro'); ?></option>
                            <option value="year" <?php selected('year', $cookie_interval); ?>><?php esc_attr_e('Year(s)', 'profilepress-pro'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Choose a length of time when visitors can once again read your posts for free (up to the # of articles allowed) after exhausting their free view quota.', 'profilepress-pro'); ?></p>
                        <?php
                        return ob_get_clean();
                    })($saved_data)
                ],
                'restrictions'             => [
                    'type'        => 'custom_field_block',
                    'label'       => esc_html__('Restrictions', 'profilepress-pro'),
                    'description' => esc_html__('Please note that restrictions are processed from top to bottom.', 'profilepress-pro'),
                    'data'        => (function ($saved_data) {
                        $restrictions = ppress_var($saved_data, 'restriction', []);
                        ob_start();
                        ?>
                        <div class="ppress-metered-paywall-restriction-wrap ppress-table-repeater-wrap">
                            <table cellspacing="0" class="widefat">
                                <thead>
                                <tr>
                                    <th class="ppress-metered-paywall-table-post-type"><?php esc_html_e('Post Type', 'profilepress-pro') ?></th>
                                    <th class="ppress-metered-paywall-table-taxonomy"><?php esc_html_e('Taxonomy', 'profilepress-pro') ?></th>
                                    <th class="ppress-metered-paywall-table-views"><?php esc_html_e('# Free Views', 'profilepress-pro') ?></th>
                                    <th class="ppress-metered-paywall-table-actions"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($restrictions as $index => $restriction) : ?>
                                    <?php $this->restriction_row(
                                        $index,
                                        ppress_var($restriction, 'post_type', 'post'),
                                        ppress_var($restriction, 'tax'),
                                        ppress_var($restriction, 'count')
                                    ); ?>
                                <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="6">
                                        <a href="#" class="button button-secondary ppress_add_restriction"><?php esc_html_e('Add Restricted Content', 'profilepress-pro') ?></a>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                        <?php
                        return ob_get_clean();
                    })($saved_data)
                ],
                'combined_free_view_total' => [
                    'type'        => 'number',
                    'label'       => esc_html__('Total Free Views', 'profilepress-pro'),
                    'description' => esc_html__('Enter a number greater than zero to define the total number of free views allowed regardless of post type or taxonomy.', 'profilepress-pro'),
                ],
                'enable_ip_blocker'        => [
                    'type'           => 'checkbox',
                    'label'          => esc_html__('IP Blocker', 'profilepress-pro'),
                    'value'          => 'yes',
                    'checkbox_label' => esc_html__('Enable', 'profilepress-pro'),
                    'description'    => esc_html__('Enable to stop readers from bypassing paywalls using different browsers, incognito or private window.', 'profilepress-pro'),
                ],
                'clear_blocked_ips'        => [
                    'type'  => 'custom_field_block',
                    'label' => esc_html__('Blocked IP Addresses', 'profilepress-pro'),
                    'data'  => (function () {
                        $url = esc_url(add_query_arg(['ppress_action' => 'metered_paywall_clear_ips', 'csfr' => wp_create_nonce('ppress_metered_paywall_clear_ips')]));

                        $html = sprintf('<a class="ppress-confirm-delete button" href="%s">%s</a><br>', $url, esc_html__('Clear IP Log', 'profilepress-pro'));
                        $html .= sprintf(
                            '<textarea readonly style="margin-top:8px">%s</textarea>',
                            implode("\r\n", array_map(function ($item) {
                                    return inet_ntop(hex2bin($item->meta_value));
                                },
                                    IPBlocker::get_instance()->last_100()
                                )
                            )
                        );

                        return $html;
                    })()
                ],
            ],
            [
                'section_title'    => esc_html__('Countdown Slidebox', 'profilepress-pro'),
                'enable_countdown' => [
                    'type'           => 'checkbox',
                    'label'          => esc_html__('Enable Slidebox', 'profilepress-pro'),
                    'checkbox_label' => esc_html__('Enable', 'profilepress-pro')
                ],
                'message'          => [
                    'type'  => 'text',
                    'label' => esc_html__('Message', 'profilepress-pro'),
                    'value' => esc_html__('posts remaining', 'profilepress-pro')
                ],
                'button_text'      => [
                    'type'  => 'text',
                    'label' => esc_html__('Button Text', 'profilepress-pro'),
                    'value' => esc_html__('Subscribe Now', 'profilepress-pro')
                ],
                'button_link'      => [
                    'type'        => 'text',
                    'label'       => esc_html__('Button Link', 'profilepress-pro'),
                    'placeholder' => 'https://'
                ],
                'login_text'       => [
                    'type'  => 'text',
                    'label' => esc_html__('Login Text', 'profilepress-pro'),
                    'value' => esc_html__('Have an account? Login', 'profilepress-pro')
                ]
            ]
        ];

        add_action('wp_cspa_after_settings_tab', function () {
            echo '<div class="ppress-sub-header-notice"><p>' .
                 sprintf(
                     esc_html__('Metered Paywall addon lets you specify the post types and taxonomies to make available for visitors to access freely. Consequently, ensure the post types and taxonomies defined in Restrictions settings below are already protected in %sContent Protection%s otherwise everyone will still have access to the content after hitting their limit.', 'profilepress-pro'),
                     '<a target="_blank" href="' . PPRESS_CONTENT_PROTECTION_SETTINGS_PAGE . '">', '</a>') .
                 '</p></div>';
        });

        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name('ppress_limit_post_views');
        $instance->page_header($this->admin_page_title());
        $instance->main_content($settings);
        $instance->sidebar(AbstractSettingsPage::sidebar_args());
        $instance->remove_white_design();
        $instance->build();
    }

    protected function restriction_row($index = 0, $saved_post_type = 'post', $saved_tax = '', $count = '')
    {
        $exclude    = ['attachment', 'revision', 'nav_menu_item', 'custom_css'];
        $post_types = get_post_types(['public' => true], 'objects');
        $taxes      = get_object_taxonomies($saved_post_type, 'objects');
        ?>
        <tr data-row-index="<?= $index ?>">
            <td class="ppress-metered-paywall-table-post-type">
                <select name="ppress_limit_post_views[restriction][<?= $index ?>][post_type]">
                    <?php foreach ($post_types as $post_type) {
                        if ( ! in_array($post_type->name, $exclude, true)) {
                            echo '<option value="' . esc_attr($post_type->name) . '" ' . selected($post_type->name, $saved_post_type, false) . '>' . esc_html($post_type->labels->name) . '</option>';
                        }
                    } ?>
                </select>
            </td>
            <td class="ppress-metered-paywall-table-taxonomy">
                <select name="ppress_limit_post_views[restriction][<?= $index ?>][tax]">
                    <?php

                    printf('<option value="all" %s>%s</option>', selected('all', '', false), esc_html__('All', 'profilepress-pro'));

                    foreach ($taxes as $tax) {

                        if ( ! in_array($tax->name, ['post_format'], true)) {

                            echo '<optgroup label="' . esc_attr($tax->label) . '">';

                            $terms = get_terms([
                                'taxonomy'   => $tax->name,
                                'fields'     => 'id=>name',
                                'hide_empty' => false,
                            ]);

                            foreach ($terms as $term_id => $term_name) {
                                echo '<option value="' . esc_attr($term_id) . '" ' . selected($term_id, $saved_tax, false) . '>' . esc_html($term_name) . '</option>';
                            }
                        }

                        echo '</optgroup>';
                    }
                    ?>
                </select>
            </td>
            <td class="ppress-metered-paywall-table-views">
                <input type="number" class="small-text" name="ppress_limit_post_views[restriction][<?= $index ?>][count]" value="<?= $count ?>">
            </td>
            <td class="ppress-metered-paywall-table-actions">
                <span class="ppress-table-repeater-icon"><span class="dashicons dashicons-no-alt"></span></span>
            </td>
        </tr>
        <?php
    }

    public function clear_ip_log()
    {
        if (ppressGET_var('ppress_action') == 'metered_paywall_clear_ips') {

            check_admin_referer('ppress_metered_paywall_clear_ips', 'csfr');

            IPBlocker::get_instance()->clear_log();

            wp_safe_redirect(esc_url_raw(remove_query_arg(['ppress_action', 'csfr'])));
            exit;
        }
    }

    public function js_script()
    {
        echo '<script type="text/html" id="tmpl-ppress-metered-paywall-row">';
        $this->restriction_row('{{data.index}}');
        echo '</script>';
    }
}