<?php
/**
 * Main provider class file.
 *
 * @package LearnDash\Notifications
 */

namespace LearnDash\Notifications;

use StellarWP\Learndash\lucatume\DI52\ServiceProvider;

/**
 * Service provider class the plugin.
 *
 * @since 1.6.3
 */
class Provider extends ServiceProvider {
	/**
	 * Register service providers.
	 *
	 * @since 1.6.3
	 *
	 * @return void
	 */
	public function register(): void {
		$this->hooks();
	}

	/**
	 * Hooks wrapper.
	 *
	 * @since 1.6.3
	 *
	 * @return void
	 */
	public function hooks() {
		add_filter(
			'learndash_loggers',
			function ( array $loggers ): array {
				$logger = new Logger();
				$loggers[] = $logger;

				return $loggers;
			}
		);
	}
}
