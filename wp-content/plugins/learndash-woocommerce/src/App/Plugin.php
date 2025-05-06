<?php
/**
 * Plugin service provider class file.
 *
 * @since 2.0.0
 *
 * @package LearnDash\WooCommerce
 */

namespace LearnDash\WooCommerce;

use StellarWP\Learndash\lucatume\DI52\ContainerException;
use StellarWP\Learndash\lucatume\DI52\ServiceProvider;

/**
 * Plugin service provider class.
 *
 * @since 2.0.0
 */
class Plugin extends ServiceProvider {
	/**
	 * Registers service provider.
	 *
	 * @since 2.0.0
	 *
	 * @throws ContainerException If the service provider is not registered.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->container->register( Admin\Provider::class );
		$this->container->register( Controllers\Provider::class );
		$this->container->register( Modules\Provider::class );
	}
}
