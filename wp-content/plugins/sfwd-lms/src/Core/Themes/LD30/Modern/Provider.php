<?php
/**
 * Provider for LD30 Modern Variations.
 *
 * @since 4.21.0
 *
 * @package LearnDash\Core
 */

namespace LearnDash\Core\Themes\LD30\Modern;

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
		$this->register_configuration_hooks();

		if ( ! $this->should_load() ) {
			return;
		}

		$this->container->register( Course\Provider::class );
		$this->container->register( Ajax\Provider::class );

		$this->hooks();
	}

	/**
	 * Hooks for configuration.
	 *
	 * @since 4.21.0
	 *
	 * @return void
	 */
	private function register_configuration_hooks(): void {
		// Hydrate for new install.
		add_action(
			'learndash_initialization_new_install',
			$this->container->callback( Features::class, 'action_set_new_install_appearance' )
		);

		// Migrate for an existing data set.
		add_action(
			'learndash_version_upgraded',
			$this->container->callback( Features::class, 'migrate_updated_appearance_field' )
		);
	}

	/**
	 * Register hooks for the provider.
	 *
	 * @since 4.21.0
	 *
	 * @return void
	 */
	private function hooks(): void {
		add_filter(
			'learndash_theme_supports_views',
			$this->container->callback(
				Features::class,
				'enable_view_support'
			),
			10,
			3
		);

		add_filter(
			'learndash_remove_template_content_filter',
			$this->container->callback(
				Features::class,
				'remove_template_content_filter'
			)
		);

		add_filter(
			'learndash_template_filename',
			$this->container->callback(
				Features::class,
				'load_modern_templates'
			),
			20, // learndash_30_template_filename() is filtered at 10.
			6
		);

		add_filter(
			'learndash_wrapper_class',
			$this->container->callback(
				Features::class,
				'update_wrapper_class'
			),
			10,
			2
		);

		add_action(
			'init',
			$this->container->callback(
				Assets::class,
				'register_scripts'
			)
		);
	}

	/**
	 * Controls whether the LD30 Modern functionality should be ran.
	 *
	 * @since 4.21.0
	 *
	 * @return bool
	 */
	private function should_load(): bool {
		/**
		 * We can't use the LearnDash_Settings_Section_General_Appearance::get_setting() here because it is
		 * not always initialized, and for purposes of this logic we need to see the option when it is not initialized.
		 *
		 * @var array{ course_enabled: string } $pages_enabled The modern page enabled settings.
		 */
		$pages_enabled = get_option( 'learndash_settings_appearance' );

		// In the future, this will check if one of many settings are enabled.
		return ! empty( $pages_enabled['course_enabled'] )
			&& $pages_enabled['course_enabled'] === 'yes';
	}
}
