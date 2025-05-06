<?php
/**
 * LearnDash compatibility class file.
 *
 * @since 1.0.9
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor;

/**
 * LearnDash compatibility class.
 *
 * @since 1.0.9
 */
class Compatibility {
	/**
	 * Dequeue the LearnDash template script on the Elementor edit page to fix its conflict
	 * with the Elementor editor.
	 *
	 * @since 1.0.9
	 *
	 * @return void
	 */
	public function dequeue_template_script_on_editor_page(): void {
		wp_dequeue_script( 'learndash_template_script_js' );
	}
}
