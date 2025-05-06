<?php
/**
 * Admin provider class file.
 *
 * @since 1.1.0
 *
 * @package LearnDash\Certificate_Builder
 */

namespace LearnDash\Certificate_Builder\Admin;

use LearnDash_Settings_Section;
use StellarWP\Learndash\lucatume\DI52\ServiceProvider;
use StellarWP\Learndash\lucatume\DI52\ContainerException;

/**
 * Admin service provider class.
 *
 * @since 1.1.0
 */
class Provider extends ServiceProvider {
	/**
	 * Register service providers.
	 *
	 * @since 1.1.0
	 *
	 * @throws ContainerException If the service provider is not registered.
	 *
	 * @return void
	 */
	public function register(): void {
		// Ensure that the Instance is only created once, using Core's methods to create and return the instance.
		$this->container->singleton(
			Translation::class,
			function () {
				Translation::add_section_instance();

				return LearnDash_Settings_Section::get_section_instance( Translation::class );
			}
		);

		$this->hooks();
	}

	/**
	 * Hooks wrapper.
	 *
	 * @since 1.1.0
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
