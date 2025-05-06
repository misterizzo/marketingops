<?php
/**
 * Elementor editor-related integration class file.
 *
 * @since 1.0.5
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor;

use LearnDash\Elementor\Shortcodes\TinyMCE;

/**
 * Elementor editor-related integration class.
 *
 * @since 1.0.5
 */
class Editor {
	/**
	 * Enqueue editor scripts and styles.
	 *
	 * @since 1.0.5
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		$tinymce = new TinyMCE();

		$tinymce->load_admin_scripts();
		$tinymce->inline_editor_styles();
	}
}
