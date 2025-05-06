<?php
/**
 * Admin provider class file.
 *
 * @since 1.0.9
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor\Admin;

use StellarWP\Learndash\lucatume\DI52\ServiceProvider;
use StellarWP\Learndash\lucatume\DI52\ContainerException;

/**
 * Admin service provider class.
 *
 * @since 1.0.9
 */
class Provider extends ServiceProvider {
	/**
	 * Register service providers.
	 *
	 * @since 1.0.9
	 *
	 * @throws ContainerException If the service provider is not registered.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->hooks();
	}

	/**
	 * Hooks wrapper.
	 *
	 * @since 1.0.9
	 *
	 * @throws ContainerException If the service provider is not registered.
	 *
	 * @return void
	 */
	public function hooks() {
		// Translation.

		add_action(
			'init',
			$this->container->callback(
				Translation::class,
				'add_section_instance'
			)
		);
	}
}
