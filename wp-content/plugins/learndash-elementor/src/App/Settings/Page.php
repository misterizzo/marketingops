<?php
/**
 * Settings page class file.
 *
 * @since 1.0.6
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor\Settings;

use LearnDash_Settings_Page;

/**
 * Settings page class.
 *
 * @since 1.0.6
 */
class Page extends LearnDash_Settings_Page {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->settings_page_id      = 'ld-elementor-settings';
		$this->parent_menu_page_url  = 'admin.php?page=' . $this->settings_page_id;
		$this->menu_page_capability  = LEARNDASH_ADMIN_CAPABILITY_CHECK;
		$this->settings_page_title   = __( 'LearnDash Elementor Settings', 'learndash-elementor' );
		$this->settings_tab_title    = __( 'Elementor', 'learndash-elementor' );
		$this->settings_tab_priority = 10;

		// Priority 190 is above the Help submenu.
		add_filter( 'learndash_submenu', array( $this, 'add_submenu' ), 190 );

		parent::__construct();
	}

	/**
	 * Add submenu.
	 *
	 * @since 1.0.6
	 *
	 * @param array<string, array<string, string>> $submenu Submenu item to check.
	 *
	 * @return array<string, array<string, string>>
	 */
	public function add_submenu( $submenu ): array {
		if ( ! isset( $submenu[ $this->settings_page_id ] ) ) {
			$submenu = array_merge(
				$submenu,
				array(
					$this->settings_page_id => array(
						'name'  => $this->settings_tab_title,
						'cap'   => $this->menu_page_capability,
						'link'  => $this->parent_menu_page_url,
						'class' => 'submenu-ld-elementor-settings',
					),
				)
			);
		}

		return $submenu;
	}
}


