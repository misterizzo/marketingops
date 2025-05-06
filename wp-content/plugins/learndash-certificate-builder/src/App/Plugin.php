<?php
/**
 * Plugin service provider class file.
 *
 * @since 1.1.0
 *
 * @package LearnDash\Certificate_Builder
 */

namespace LearnDash\Certificate_Builder;

use LearnDash_Certificate_Builder\Controller\Certificate_Builder;
use StellarWP\Learndash\lucatume\DI52\ServiceProvider;
use StellarWP\Learndash\lucatume\DI52\ContainerException;

/**
 * Plugin service provider class.
 *
 * @since 1.1.0
 */
class Plugin extends ServiceProvider {
	/**
	 * Register service provider.
	 *
	 * @since 1.1.0
	 *
	 * @throws ContainerException If the service provider is not registered.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->container->register( Admin\Provider::class );
		$this->container->register( Admin\Post\Edit\Provider::class );

		// Register legacy plugin entry class.
		$this->container->singleton( Certificate_Builder::class );
	}
}
