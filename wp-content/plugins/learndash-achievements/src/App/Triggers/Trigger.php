<?php
/**
 * Trigger base class file.
 *
 * @since 2.0.0
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements\Triggers;

/**
 * Trigger base class.
 *
 * @since 2.0.0
 */
abstract class Trigger {
	/**
	 * Returns trigger key.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	abstract public function get_key(): string;

	/**
	 * Returns trigger label.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	abstract public function get_label(): string;

	/**
	 * Returns trigger category.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	abstract public function get_category(): string;

	/**
	 * Registers hooks.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	abstract public function register_hooks(): void;

	/**
	 * Registers the trigger.
	 *
	 * This method is hooked to `learndash_achievements_triggers` filter hook.
	 *
	 * @since 2.0.0
	 *
	 * @param array<string, array<string, string>> $triggers Existing triggers.
	 *
	 * @return array<string, array<string, string>>
	 */
	public function register( array $triggers ): array {
		if ( ! isset( $triggers[ $this->get_category() ] ) ) {
			$triggers[ $this->get_category() ] = [];
		}

		$triggers[ $this->get_category() ][ $this->get_key() ] = $this->get_label();

		return $triggers;
	}
}
