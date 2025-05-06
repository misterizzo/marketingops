<?php
/**
 * LearnDash Settings Section for PayPal Metabox.
 *
 * @since 1.0.6
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor\Settings;

use LearnDash_Settings_Section;

/**
 * Class LearnDash Settings Section for PayPal Metabox.
 *
 * @since 1.0.6
 */
class Section extends LearnDash_Settings_Section {
	/**
	 * Protected constructor for class
	 *
	 * @since 1.0.6
	 */
	protected function __construct() {
		$this->settings_page_id       = 'ld-elementor-settings';
		$this->setting_option_key     = 'learndash_elementor';
		$this->setting_field_prefix   = 'learndash_elementor';
		$this->settings_section_key   = 'learndash_elementor';
		$this->settings_section_label = esc_html__( 'Settings', 'learndash-elementor' );

		parent::__construct();
	}

	/**
	 * Initialize the metabox settings values.
	 *
	 * @since 1.0.6
	 *
	 * @return void
	 */
	public function load_settings_values(): void {
		parent::load_settings_values();
	}

	/**
	 * Initialize the metabox settings fields.
	 *
	 * @since 1.0.6
	 *
	 * @return void
	 */
	public function load_settings_fields(): void {
		$this->setting_option_fields = [
			'disable_widget_auto_insertion' => [
				'name'    => 'disable_widget_auto_insertion',
				'type'    => 'checkbox-switch',
				'label'   => esc_html__( 'Disable Widget Auto Insertion', 'learndash-elementor' ),
				'value'   => isset( $this->setting_option_values['disable_widget_auto_insertion'] )
					? $this->setting_option_values['disable_widget_auto_insertion']
					: '',
				'help_text' => __( 'By default, we automatically insert the LearnDash infobar, content and navigation widgets into a single course/lesson/topic that has Elementor edit mode enabled (not Elementor Pro global template). Enable this option to prevent that, note that you will need to ensure that you add the appropriate LearnDash widgets into your own page templates.', 'learndash-elementor' ),
				'default' => '',
				'options' => [
					'on' => '',
					''   => '',
				],
			],
		];

		/** This filter is documented in /wp-content/plugins/sfwd-lms/includes/settings/settings-metaboxes/class-ld-settings-metabox-course-access-settings.php */
		$this->setting_option_fields = apply_filters( 'learndash_settings_fields', $this->setting_option_fields, $this->settings_section_key );

		parent::load_settings_fields();
	}
}
