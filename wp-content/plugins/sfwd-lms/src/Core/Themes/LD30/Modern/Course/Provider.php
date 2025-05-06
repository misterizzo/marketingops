<?php
/**
 * Provider for LD30 Modern Course Page.
 *
 * @since 4.21.0
 *
 * @package LearnDash\Core
 */

namespace LearnDash\Core\Themes\LD30\Modern\Course;

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

		$this->hooks();
	}

	/**
	 * Register hooks for the provider.
	 *
	 * @since 4.21.0
	 *
	 * @return void
	 */
	private function hooks(): void {
		// Course template context.

		add_filter(
			'learndash_template_view_context',
			$this->container->callback(
				Template::class,
				'add_additional_context'
			),
			10,
			5
		);

		// Change the payment button label on the course page.

		add_filter(
			'learndash_payment_button_label_course',
			$this->container->callback(
				Template::class,
				'change_payment_button_label'
			),
			10,
			3
		);

		// Change payment button classes on the course page.

		add_filter(
			'learndash_payment_button_classes',
			$this->container->callback(
				Template::class,
				'change_payment_button_classes'
			),
		);

		// Change the free payment button on the course page.

		add_filter(
			'learndash_payment_button_free',
			$this->container->callback( Template::class, 'change_free_payment_button' ),
			10,
			2
		);
	}

	/**
	 * Controls whether the LD30 Modern Course Page functionality should be ran.
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

		return ! empty( $pages_enabled['course_enabled'] )
			&& $pages_enabled['course_enabled'] === 'yes';
	}
}
