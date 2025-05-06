<?php
/**
 * Plugin service provider class file.
 *
 * @since 2.0.0
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements;

use StellarWP\Learndash\lucatume\DI52\ServiceProvider;
use StellarWP\Learndash\lucatume\DI52\ContainerException;

/**
 * Plugin service provider class.
 *
 * @since 2.0.0
 */
class Plugin extends ServiceProvider {
	/**
	 * Register service provider.
	 *
	 * @since 2.0.0
	 *
	 * @throws ContainerException If the service provider is not registered.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->container->register( Admin\Provider::class );
		$this->container->register( Triggers\Provider::class );
		$this->container->register( Modules\Provider::class );
	}
}
