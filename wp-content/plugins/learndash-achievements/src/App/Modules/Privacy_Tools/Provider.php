<?php
/**
 * Privacy tools module provider class file.
 *
 * @since 2.0.0
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements\Modules\Privacy_Tools;

use StellarWP\Learndash\lucatume\DI52\ContainerException;
use StellarWP\Learndash\lucatume\DI52\ServiceProvider;

/**
 * Privacy tools module service provider class.
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
		$this->container->singleton( Controller::class );

		$this->hooks();
	}

	/**
	 * Hooks wrapper.
	 *
	 * @since 2.0.0
	 *
	 * @throws ContainerException If the service provider is not registered.
	 *
	 * @return void
	 */
	public function hooks() {
		add_filter( 'wp_privacy_personal_data_exporters', $this->container->callback( Controller::class, 'register_exporters' ) );
		add_filter( 'wp_privacy_personal_data_erasers', $this->container->callback( Controller::class, 'register_erasers' ) );
	}
}
