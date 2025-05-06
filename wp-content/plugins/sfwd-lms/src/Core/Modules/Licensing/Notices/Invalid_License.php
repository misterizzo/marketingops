<?php
/**
 * Invalid License notice.
 *
 * @since 4.18.0
 *
 * @package LearnDash\Core
 */

namespace LearnDash\Core\Modules\Licensing\Notices;

use LearnDash\Core\Utilities\Cast;
use WP_Screen;

/**
 * Invalid License notice.
 *
 * @since 4.18.0
 */
class Invalid_License {
	/**
	 * Outputs a notice if the entered License Key is invalid.
	 *
	 * @since 4.18.0
	 *
	 * @return void
	 */
	public function display(): void {
		$current_screen = get_current_screen();

		/**
		 * Only display the an invalid license notice on LearnDash pages,
		 * with the exception of the Setup and Licensing pages.
		 */
		if (
			! $current_screen instanceof WP_Screen
			|| in_array(
				$current_screen->id,
				[
					'admin_page_learndash-setup',
					'admin_page_learndash_hub_licensing',
				],
				true
			)
			|| ! learndash_should_load_admin_assets()
			|| learndash_is_license_hub_valid()
		) {
			return;
		}

		printf(
			'<div class="%s" %s><p>%s</p></div>',
			esc_attr(
				learndash_get_license_class( 'notice notice-error is-dismissible learndash-license-is-dismissible' )
			),
			learndash_get_license_data_attrs( false ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded, escaped in function.
			wp_kses_post( learndash_get_license_message( 2 ) )
		);
	}
}
