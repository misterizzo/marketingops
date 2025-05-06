<?php
/**
 * Modules provider class file.
 *
 * @since 2.0.0
 *
 * @package LearnDash\WooCommerce
 */

namespace LearnDash\WooCommerce\Modules;

use StellarWP\Learndash\lucatume\DI52\ServiceProvider;

/**
 * Modules provider class.
 *
 * @since 2.0.0
 */
class Provider extends ServiceProvider {
	/**
	 * Register the service provider.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function register(): void {
		$this->container->register( Retroactive_Access_Tool\Provider::class );
	}
}
