<?php
/**
 * Payments module provider class file.
 *
 * @since 2.0.2
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements\Modules\Payments;

use Learndash_Payment_Gateway;
use StellarWP\Learndash\lucatume\DI52\ContainerException;
use StellarWP\Learndash\lucatume\DI52\ServiceProvider;

/**
 * Payments module service provider class.
 *
 * @since 2.0.2
 */
class Provider extends ServiceProvider {
	/**
	 * Register service providers.
	 *
	 * @since 2.0.2
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
	 * @since 2.0.2
	 *
	 * @throws ContainerException If the service provider is not registered.
	 *
	 * @return void
	 */
	public function hooks() {
		add_filter( 'learndash_payment_gateways', $this->container->callback( __CLASS__, 'register_gateway' ) );
	}

	/**
	 *
	 * Registers payment gateway.
	 *
	 * @since 2.0.2
	 *
	 * @param Learndash_Payment_Gateway[] $gateways Existing payment gateways.
	 *
	 * @return Learndash_Payment_Gateway[]
	 */
	public function register_gateway( array $gateways ): array {
		$gateways[] = new Gateway();

		return $gateways;
	}
}
