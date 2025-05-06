<?php
/**
 * Provider for the LD30 Theme.
 *
 * @since 4.21.0
 *
 * @package LearnDash\Core
 */

namespace LearnDash\Core\Themes\LD30;

use LearnDash\Core\Themes\LD30\Modern\Features;
use LearnDash_Theme_Register;
use StellarWP\Learndash\lucatume\DI52\ServiceProvider;

/**
 * Class Provider for initializing theme implementations and hooks.
 *
 * @since 4.21.0
 */
class Provider extends ServiceProvider {
	/**
	 * Registers the service provider bindings.
	 *
	 * @since 4.21.0
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->should_load() ) {
			return;
		}

		$this->container->register( Quiz\Provider::class );
		$this->container->register( Modern\Provider::class );
	}

	/**
	 * Controls whether LD30-specific Providers should be loaded.
	 *
	 * @since 4.21.0
	 *
	 * @return bool
	 */
	private function should_load(): bool {
		return LearnDash_Theme_Register::get_active_theme_key() === 'ld30';
	}
}
