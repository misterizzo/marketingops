<?php
/**
 * Triggers service provider class file.
 *
 * @since 2.0.0
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements\Triggers;

use StellarWP\Learndash\lucatume\DI52\ContainerException;
use StellarWP\Learndash\lucatume\DI52\ServiceProvider;

/**
 * Triggers service provider class.
 *
 * @since 2.0.0
 */
class Provider extends ServiceProvider {
	/**
	 * Register service provider.
	 *
	 * @since 2.0.0
	 *
	 * @throws ContainerException If the container cannot be resolved.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->register_triggers();
	}

	/**
	 * Registers triggers.
	 *
	 * @since 2.0.0
	 *
	 * @throws ContainerException If the container cannot be resolved.
	 *
	 * @return void
	 */
	private function register_triggers(): void {
		/**
		 * Filters the list of trigger classes.
		 *
		 * @since 2.0.0
		 *
		 * @param string[] $trigger_classes List of trigger classes.
		 *
		 * @return string[]
		 */
		$trigger_classes = apply_filters(
			'learndash_achievements_trigger_classes',
			[
				// Add trigger classes here.
				Consecutive_Login::class,
				Complete_Courses_Groups_Count::class,
				Earn_Badges_Points_Count::class,
			]
		);

		foreach ( $trigger_classes as $trigger_class ) {
			// Check if the trigger class extends the Trigger base class before registering it.

			$class_parents = class_parents( $trigger_class );

			if (
				$class_parents === false
				|| ! in_array( Trigger::class, $class_parents, true )
			) {
				continue;
			}

			$this->container->singleton( $trigger_class );

			$trigger = $this->container->get( $trigger_class );

			if ( ! $trigger instanceof Trigger ) {
				continue;
			}

			/**
			 * Registers the trigger in the existing triggers list.
			 *
			 * This makes the trigger available in the triggers getter method `LearnDash\Achievements\Achievement::get_triggers()`.
			 */
			add_filter( 'learndash_achievements_triggers', $this->container->callback( $trigger_class, 'register' ) );

			$trigger->register_hooks();
		}
	}
}
