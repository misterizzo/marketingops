<?php
/**
 * Integration file for Elementor elements-related stuff.
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor;

use Elementor\Elements_Manager;

/**
 * Elements integration class.
 *
 * @since 1.0.5
 */
class Elements {
	/**
	 * Register LearnDash Elementor editor Widgets category.
	 *
	 * @since 1.0.5
	 *
	 * @param Elements_Manager $elements_manager Instance of ElementorElements_Manager class.
	 */
	public function register_categories( $elements_manager ): void {
		$elements_manager->add_category(
			'learndash',
			[
				'title'  => __( 'LearnDash', 'learndash-elementor' ),
				'active' => false,
			]
		);
	}
}
