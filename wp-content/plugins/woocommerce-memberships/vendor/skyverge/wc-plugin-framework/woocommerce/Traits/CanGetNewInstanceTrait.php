<?php
/**
 * WooCommerce Plugin Framework
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the plugin to newer
 * versions in the future. If you wish to customize the plugin for your
 * needs please refer to http://www.skyverge.com
 *
 * @package   SkyVerge/WooCommerce/Plugin/Classes
 * @author    SkyVerge
 * @copyright Copyright (c) 2013-2024, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace SkyVerge\WooCommerce\PluginFramework\v5_15_8\Traits;

defined('ABSPATH') or exit;

if (trait_exists('\\SkyVerge\\WooCommerce\\PluginFramework\\v5_15_8\\Traits\\CanGetNewInstanceTrait')) {
	return;
}

/**
 * A trait that allows a given class/object to get a new instance of itself.
 * For singletons {@see CanGetNewInstanceTrait} instead.
 */
trait CanGetNewInstanceTrait
{
	/**
	 * Creates and returns a new instance of the calling class.
	 *
	 * @return static
	 */
	public static function getNewInstance(...$args)
	{
		return new static(...$args);
	}
}
