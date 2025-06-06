<?php
/**
 * WooCommerce Memberships
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Memberships to newer
 * versions in the future. If you wish to customize WooCommerce Memberships for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-memberships/ for more information.
 *
 * @author    SkyVerge
 * @copyright Copyright (c) 2014-2025, SkyVerge, Inc. (info@skyverge.com)
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Starts output for the Member Directory.
 *
 * @type array $members associative array of user memberships IDS indexed by user ID
 *
 * @version 1.21.0
 * @since 1.21.0
 */

/**
 * Fires before outputting the Member Directory.
 *
 * @since 1.21.0
 *
 * @param array $members associative array of user memberships IDs indexed by user ID
*/
do_action( 'wc_memberships_before_member_directory', $members );

// note: the legacy `wcm` class here is to support an old template when the shortcode was part of a free plugin
?>
<div class="woocommerce-memberships wcm member-directory">
