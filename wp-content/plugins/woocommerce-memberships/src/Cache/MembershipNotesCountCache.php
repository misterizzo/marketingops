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
 * @copyright Copyright (c) 2014-2024, SkyVerge, Inc. (info@skyverge.com)
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace SkyVerge\WooCommerce\Memberships\Cache;

defined( 'ABSPATH' ) or exit;

/**
 * A class to manage the cache for the number of membership notes.
 * @see \WC_Memberships_User_Memberships::get_user_membership_notes_count()
 *
 * @since 1.26.7
 */
class MembershipNotesCountCache
{
	protected static MembershipNotesCountCache $instance;

	/** @var int how long the cache lasts, in seconds */
	protected int $ttl;

	/** @var string name of the cache key */
	protected string $cacheKey;

	final public function __construct()
	{
		$this->ttl = 6 * HOUR_IN_SECONDS;
		$this->cacheKey = 'wc_memberships_number_notes';
	}

	/**
	 * Gets the single instance of the cache.
	 *
	 * @since 1.26.7
	 *
	 * @return MembershipNotesCountCache
	 */
	public static function getInstance(): MembershipNotesCountCache
	{
		if (! isset(static::$instance)) {
			static::$instance = new MembershipNotesCountCache();
		}

		return static::$instance;
	}

	/**
	 * Gets the cached value.
	 *
	 * @since 1.26.7
	 *
	 * @return int|null
	 */
	public function get(): ?int
	{
		$value = get_transient($this->cacheKey);

		if (is_numeric($value)) {
			return (int) $value;
		}

		return null;
	}

	/**
	 * Sets the cache value.
	 *
	 * @since 1.26.7
	 *
	 * @param int $value
	 * @return $this
	 */
	public function set(int $value): MembershipNotesCountCache
	{
		set_transient($this->cacheKey, $value, $this->ttl);

		return $this;
	}

	/**
	 * Deletes the cached value.
	 *
	 * @since 1.26.7
	 *
	 * @return void
	 */
	public function clear(): void
	{
		delete_transient($this->cacheKey);
	}
}
