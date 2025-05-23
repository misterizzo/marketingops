<?php
/**
 * Copyright © 2019-2025 Rhubarb Tech Inc. All Rights Reserved.
 *
 * The Object Cache Pro Software and its related materials are property and confidential
 * information of Rhubarb Tech Inc. Any reproduction, use, distribution, or exploitation
 * of the Object Cache Pro Software and its related materials, in whole or in part,
 * is strictly forbidden unless prior permission is obtained from Rhubarb Tech Inc.
 *
 * In addition, any reproduction, use, distribution, or exploitation of the Object Cache Pro
 * Software and its related materials, in whole or in part, is subject to the End-User License
 * Agreement accessible in the included `LICENSE` file, or at: https://objectcache.pro/eula
 */

declare(strict_types=1);

namespace RedisCachePro\Plugin\Pages;

use const RedisCachePro\Version;

class Updates extends Page
{
    /**
     * The plugin update information.
     *
     * @var object|\WP_Error
     */
    protected $info;

    /**
     * Returns the page title.
     *
     * @return string
     */
    public function title()
    {
        return 'Updates';
    }

    /**
     * Whether this page is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->plugin->updatesEnabled();
    }

    /**
     * Boot the settings page and its components.
     *
     * @return void
     */
    public function boot()
    {
        if (! $this->isCurrent()) {
            $this->pluginInfo();

            return;
        }

        $this->setUpSettings();

        $this->enqueueScript();
        $this->enqueueOptionsScript();

        $forceCheck = ! empty($_GET['force-check']);

        $this->info = $this->setUpPluginInfo($forceCheck);

        if ($forceCheck) {
            wp_safe_redirect($this->url(), 302, 'Object Cache Pro');
            exit;
        }
    }

    /**
     * Render the settings page.
     *
     * @return void
     */
    public function render()
    {
        require __DIR__ . '/../templates/pages/updates.phtml';
    }

    /**
     * Registers the page's settings sections and fields.
     *
     * @return void
     */
    protected function setUpSettings()
    {
        add_settings_section(
            'update-channel',
            'Update Channel',
            [$this, 'printUpdateChannelNotice'],
            'objectcache'
        );

        add_settings_field(
            'channel',
            'Channel',
            [$this, 'printChannelField'],
            'objectcache',
            'update-channel',
            ['label_for' => 'objectcache_channel']
        );
    }

    /**
     * Set up the plugin information for the page and maybe
     * clear the WordPress plugin updates cache.
     *
     * @param  bool  $force
     * @return object|\WP_Error
     */
    protected function setUpPluginInfo($force)
    {
        $info = $this->pluginInfo($force);

        if (is_wp_error($info)) {
            add_settings_error('objectcache', (string) $info->get_error_code(), sprintf(
                'Unable to retrieve update information: %s [%s]',
                $info->get_error_message(),
                $info->get_error_code()
            ));

            return $info;
        }

        $updates = get_site_transient('update_plugins');

        $update = $updates->response[$this->plugin->basename()]
            ?? $updates->no_update[$this->plugin->basename()]
            ?? null;

        if (! $update || version_compare($info->version, $update->new_version, '>')) {
            wp_clean_plugins_cache();
        }

        $info->payload = (object) wp_parse_args($update ?? [], [
            'id' => $this->plugin->basename(),
            'slug' => $this->plugin->slug(),
            'plugin' => $this->plugin->basename(),
            'new_version' => Version,
            'url' => $this->plugin::Url,
            'package' => null,
            'icons' => [],
            'banners' => [],
            'banners_rtl' => [],
            'tested' => get_bloginfo('version'),
            'requires_php' => '7.2',
            'compatibility' => (object) [],
        ]);

        return $info;
    }

    /**
     * Returns the plugin update information for the page.
     *
     * @param  bool  $force
     * @return mixed
     */
    protected function pluginInfo($force = false)
    {
        if (! $force) {
            $transient = get_site_transient('objectcache_update');

            if ($transient !== false) {
                return $transient;
            }
        }

        $response = $this->plugin->pluginUpdateRequest();

        if (is_wp_error($response)) {
            return $response;
        }

        return get_site_transient('objectcache_update');
    }

    /**
     * Prints the `channel` field.
     *
     * @return void
     */
    public function printChannelField()
    {
        $license = $this->plugin->license();
        $channel = $this->plugin->option('channel');

        $accessibleStabilities = $license->accessibleStabilities();

        $html = '<select name="objectcache_options[channel]" id="objectcache_channel">';

        foreach ($license::Stabilities as $stability => $label) {
            $html .= sprintf(
                '<option value="%s" %s %s>%s</option>',
                esc_attr($stability),
                selected($stability, $channel, false),
                isset($accessibleStabilities[$stability]) ? '' : 'disabled',
                esc_html($label)
            );
        }

        $html .= '</select>';

        $html .= sprintf(
            '<p class="description">%s</p>',
            'Using an update channel other than <i>Stable</i> may break your site.'
        );

        echo $html;
    }

    /**
     * Print the `update-channel` section notice.
     *
     * @return void
     */
    public function printUpdateChannelNotice()
    {
        printf(
            '<p>%s</p>',
            'The channel acts as a "minimum stability", meaning that using the <i>Alpha</i> channel will also show the latest <i>Beta</i> releases and so on, whichever has the highest version number.'
        );
    }
}
