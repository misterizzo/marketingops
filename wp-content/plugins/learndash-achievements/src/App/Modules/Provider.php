<?php
/**
 * Modules provider class file.
 *
 * @since 2.0.0
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements\Modules;

use StellarWP\Learndash\lucatume\DI52\ContainerException;
use StellarWP\Learndash\lucatume\DI52\ServiceProvider;

/**
 * Modules service provider class.
 *
 * @since 2.0.0
 */
class Provider extends ServiceProvider {
	/**
	 * Register service providers.
	 *
	 * @since 2.0.0
	 *
	 * @throws ContainerException If the service provider is not registered.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->container->register( Privacy_Tools\Provider::class );

		$this->container->register( Payments\Provider::class );
	}
}
