<?php
/**
 * Main provider class file.
 *
 * @since 1.0.9
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor;

use StellarWP\Learndash\lucatume\DI52\ServiceProvider;

/**
 * Service provider class the plugin.
 *
 * @since 1.0.9
 */
class Plugin extends ServiceProvider {
	/**
	 * Register service providers.
	 *
	 * @since 1.0.5
	 *
	 * @return void
	 */
	public function register(): void {
		$this->container->register( Settings\Provider::class );
		$this->container->register( Admin\Provider::class );

		$this->container->singleton( Templates::class );
		$this->container->singleton( Utilities::class );
		$this->container->singleton( Elements::class );
		$this->container->singleton( Widgets::class );
		$this->container->singleton( Documents::class );
		$this->container->singleton( Step::class );
		$this->container->singleton( Editor::class );
		$this->container->singleton( Frontend::class );

		$this->hooks();
	}

	/**
	 * Hooks wrapper.
	 *
	 * @since 1.0.5
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'admin_footer', $this->container->callback( Templates::class, 'check_import_templates' ) );
		add_filter( 'learndash_template', $this->container->callback( Templates::class, 'filter_learndash_template' ), 100, 5 );

		add_filter( 'elementor_pro/utils/get_public_post_types', $this->container->callback( Utilities::class, 'get_public_post_types' ) );

		add_action( 'elementor/elements/categories_registered', $this->container->callback( Elements::class, 'register_categories' ), 1, 1 );

		add_action( 'elementor/widgets/register', $this->container->callback( Widgets::class, 'register' ), 100, 1 );
		add_filter( 'elementor/widget/render_content', $this->container->callback( Widgets::class, 'filter_render_content' ), 20, 2 );

		add_action( 'elementor/documents/register', $this->container->callback( Documents::class, 'register' ) );

		add_filter( 'learndash_previous_step_completed', $this->container->callback( Step::class, 'filter_previous_step_completed' ), 10, 3 );

		add_action( 'elementor/editor/before_enqueue_scripts', $this->container->callback( Editor::class, 'enqueue_scripts' ) );

		add_filter( 'elementor/frontend/builder_content_data', $this->container->callback( Frontend::class, 'filter_builder_content_data' ), 10, 2 );
		add_filter( 'elementor/frontend/the_content', $this->container->callback( Frontend::class, 'filter_content' ), 100 );

		// Compatibility.

		add_action(
			'elementor/editor/after_enqueue_scripts',
			$this->container->callback(
				Compatibility::class,
				'dequeue_template_script_on_editor_page'
			)
		);
	}
}
