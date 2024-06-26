<?php
/**
 * Copyright (c) 2020 PublishPress
 *
 * GNU General Public License, Free Software Foundation <https://www.gnu.org/licenses/gpl-3.0.html>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     PublishPress\WordpressVersionNotices
 * @category    Core
 * @author      PublishPress
 * @copyright   Copyright (c) 2020 PublishPress. All rights reserved.
 **/

namespace PublishPress\WordpressVersionNotices\Module\MenuLink;

use PublishPress\WordpressVersionNotices\Template\TemplateLoaderInterface;

/**
 * Class Module
 *
 * @package PublishPress\WordpressVersionNotices
 */
class Module
{
    const SETTINGS_FILTER = 'pp_version_notice_menu_link_settings';

    const STYLE_HANDLE = 'pp-version-notice-menu-link-style';

    const MENU_SLUG_SUFFIX = '-menu-upgrade-link';

    /**
     * @var TemplateLoaderInterface
     */
    private $templateLoader;

    /**
     * @var array
     */
    private $globalSettings = [];

    /**
     * @var array
     */
    private $urlsMap = [];

    public function __construct(TemplateLoaderInterface $templateLoader)
    {
        $this->templateLoader = $templateLoader;
    }

    public function init()
    {
        add_action('admin_head', [$this, 'adminHeadAddStyle']);
        add_action('init', [$this, 'collectTheSettings'], 50);
        add_action('admin_menu', [$this, 'addMenuLink'], 20);
        add_action('admin_print_scripts', [$this, 'setUpgradeMenuLink'], 9999);
    }

    public function collectTheSettings()
    {
        if (is_admin()) {
            $this->globalSettings = apply_filters(self::SETTINGS_FILTER, []);
        }
    }

    public function adminHeadAddStyle()
    {
        ?>
        <style>
            .pp-version-notice-upgrade-menu-item {
                font-weight: bold !important;
                color: #FEB123 !important;
                font-weight: bold;
            }

            .pp-version-notice-upgrade-menu-item-page {
                padding: 10px;
                width: calc(100% - 40px);
                margin-top: 20px;
                text-align: center;
            }

            .pp-version-notice-upgrade-menu-item-page .spin {
                -webkit-animation: spin 1000ms infinite linear;
                animation: spin 1000ms infinite linear;
                color: #635A93;
            }
            @-webkit-keyframes spin {
                0% {
                    -webkit-transform: rotate(0deg);
                    transform: rotate(0deg);
                }
                100% {
                    -webkit-transform: rotate(359deg);
                    transform: rotate(359deg);
                }
            }
            @keyframes spin {
                0% {
                    -webkit-transform: rotate(0deg);
                    transform: rotate(0deg);
                }
                100% {
                    -webkit-transform: rotate(359deg);
                    transform: rotate(359deg);
                }
            }
        </style>
        <?php
    }

    /**
     * @param array $settings
     *
     * @return string
     */
    private function getSubmenuSlug($settings)
    {
        return $settings['parent'] . self::MENU_SLUG_SUFFIX;
    }

    public function addMenuLink()
    {
        global $submenu;

        $templateLoader = $this->templateLoader;

        foreach ($this->globalSettings as $pluginName => $settings) {
            if (is_array($settings['parent'])) {
                foreach ($settings['parent'] as $parent) {
                    $menuPageURL = menu_page_url($parent, false);

                    if (!empty($menuPageURL)) {
                        $settings['parent'] = $parent;

                        break;
                    }
                }
            }

            if (!empty($settings['parent'])) {
                $submenuSlug = $this->getSubmenuSlug($settings);

                add_submenu_page(
                    $settings['parent'],
                    $settings['label'],
                    $settings['label'],
                    'read',
                    $submenuSlug,
                    function () use ($settings, $templateLoader) {
                        $context = [
                            'message' => __(
                                'Amazing! We are redirecting you to our site...',
                                'wordpress-version-notices'
                            ),
                            'link'    => $settings['link']
                        ];

                        $templateLoader->displayOutput('menu-link', 'redirect-page', $context);
                    },
                    9999
                );

                $this->urlsMap[$pluginName] = [
                    'slug'       => $submenuSlug,
                    'localUrl'   => menu_page_url($submenuSlug, false),
                    'redirectTo' => $settings['link'],
                ];

                // Add the CSS class to change the item color and add a reference to the respective URL.
                $newItemIndex = false;
                if (isset($submenu[$settings['parent']])) {
                    $newItemIndex = $this->getUpgradeMenuItemIndex($submenu[$settings['parent']], $settings);
                }

                if (false !== $newItemIndex) {
                    $submenu[$settings['parent']][$newItemIndex][4] = 'pp-version-notice-upgrade-menu-item ' . $pluginName;
                }
            }
        }
    }

    private function getUpgradeMenuItemIndex($submenuItems, $settings)
    {
        if (!is_array($submenuItems)) {
            return false;
        }

        foreach ($submenuItems as $index => $item) {
            if ($item[0] === $settings['label'] && $item[2] === $this->getSubmenuSlug($settings)) {
                return $index;
            }
        }

        return false;
    }

    public function setUpgradeMenuLink()
    {
        if (empty($this->urlsMap)) {
            return;
        }

        $convertedUrlsMap = [];

        foreach ($this->urlsMap as $pluginName => $urlData) {
            $urlData['pluginName'] = $pluginName;

            $convertedUrlsMap[] = $urlData;
        }

        $context = [
            'convertedUrlsMap' => $convertedUrlsMap,
        ];

        $this->templateLoader->displayOutput('menu-link', 'menu-link-script', $context);
    }
}
