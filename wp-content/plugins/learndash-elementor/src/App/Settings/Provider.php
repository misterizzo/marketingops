<?php
/**
 * Settings provider class file.
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor\Settings;

use StellarWP\Learndash\lucatume\DI52\ServiceProvider;

/**
 * Service provider class the plugin.
 *
 * @since 1.0.6
 */
class Provider extends ServiceProvider {
	/**
	 * Register service providers.
	 *
	 * @since 1.0.6
	 *
	 * @return void
	 */
	public function register(): void {
		$this->hooks();
	}

	/**
	 * Hooks wrapper.
	 *
	 * @since 1.0.6
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action(
			'learndash_settings_pages_init',
			function() {
				Page::add_page_instance();
			}
		);

		add_action(
			'learndash_settings_sections_init',
			function() {
				Section::add_section_instance();
			}
		);
	}
}


