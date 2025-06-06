<?php
/**
 * Translation class file.
 *
 * @since 1.1.0
 *
 * @package LearnDash\Certificate_Builder
 */

namespace LearnDash\Certificate_Builder\Admin;

use LearnDash_Settings_Section;
use LearnDash_Translations;

/**
 * Translation class.
 *
 * @since 1.1.0
 */
class Translation extends LearnDash_Settings_Section {
	/**
	 * Project slug.
	 *
	 * Must match the plugin text domain.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	private $project_slug = 'learndash-certificate-builder';

	/**
	 * Flag if the translation has been registered.
	 *
	 * @since 1.1.0
	 *
	 * @var boolean
	 */
	private $registered = false;

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		$this->settings_page_id = 'learndash_lms_translations';

		$this->settings_section_key = 'settings_translations_' . $this->project_slug;

		$this->settings_section_label = __( 'LearnDash LMS - Certificate Builder', 'learndash-certificate-builder' );

		if (
			class_exists( 'LearnDash_Translations' )
			&& method_exists( 'LearnDash_Translations', 'register_translation_slug' )
		) {
			$this->registered = true;

			LearnDash_Translations::register_translation_slug(
				$this->project_slug,
				LEARNDASH_CERTIFICATE_BUILDER_DIR . 'languages'
			);
		}

		parent::__construct();
	}

	/**
	 * Add translation meta box.
	 *
	 * @since 1.1.0
	 *
	 * @param string $settings_screen_id LearnDash settings screen ID.
	 *
	 * @return void
	 */
	public function add_meta_boxes( $settings_screen_id = '' ): void {
		if (
			$settings_screen_id === $this->settings_screen_id
			&& $this->registered === true
		) {
			parent::add_meta_boxes( $settings_screen_id );
		}
	}

	/**
	 * Output meta box.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function show_meta_box(): void {
		$ld_translations = new Learndash_Translations( $this->project_slug );
		$ld_translations->show_meta_box();
	}
}
