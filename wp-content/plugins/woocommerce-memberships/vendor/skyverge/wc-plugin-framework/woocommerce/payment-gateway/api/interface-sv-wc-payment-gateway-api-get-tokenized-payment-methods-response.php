<?php
/**
 * WooCommerce Payment Gateway Framework
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
 * @package   SkyVerge/WooCommerce/Payment-Gateway/API
 * @author    SkyVerge
 * @copyright Copyright (c) 2013-2024, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace SkyVerge\WooCommerce\PluginFramework\v5_15_8;

defined( 'ABSPATH' ) or exit;

if ( ! interface_exists( '\\SkyVerge\\WooCommerce\\PluginFramework\\v5_15_8\\SV_WC_API_Get_Tokenized_Payment_Methods_Response' ) ) :


/**
 * WooCommerce Direct Payment Gateway API Create Payment Token Response
 */
interface SV_WC_Payment_Gateway_API_Get_Tokenized_Payment_Methods_Response extends SV_WC_Payment_Gateway_API_Response {


	/**
	 * Returns any payment tokens.
	 *
	 * @since 1.0.0
	 *
	 * @return SV_WC_Payment_Gateway_Payment_Token[] array of SV_WC_Payment_Gateway_Payment_Token payment tokens, keyed by the token ID
	 */
	public function get_payment_tokens();


}


endif;
