<?php
/**
 * Post edit provider class file.
 *
 * @since 1.1.3
 *
 * @package LearnDash\Certificate_Builder
 */

namespace LearnDash\Certificate_Builder\Admin\Post\Edit;

use StellarWP\Learndash\lucatume\DI52\ServiceProvider;
use StellarWP\Learndash\lucatume\DI52\ContainerException;

/**
 * Post edit provider class.
 *
 * @since 1.1.3
 */
class Provider extends ServiceProvider {
	/**
	 * Register service providers.
	 *
	 * @since 1.1.3
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
	 * @since 1.1.3
	 *
	 * @throws ContainerException If the service provider is not registered.
	 *
	 * @return void
	 */
	public function hooks() {
		add_filter(
			'post_type_link',
			$this->container->callback(
				Certificate::class,
				'update_view_permalink'
			),
			10,
			2
		);
	}
}
