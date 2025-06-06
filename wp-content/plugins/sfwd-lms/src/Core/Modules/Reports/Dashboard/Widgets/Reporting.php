<?php
/**
 * Reports Dashboard Reporting Widget.
 *
 * @since 4.17.0
 *
 * @package LearnDash\Core
 */

namespace LearnDash\Core\Modules\Reports\Dashboard\Widgets;

use LearnDash_ProPanel;

/**
 * Reports Dashboard Reporting Widget.
 *
 * @since 4.17.0
 */
class Reporting extends Types\ProPanel2_Widget {
	/**
	 * Loads required data.
	 *
	 * @since 4.17.0
	 *
	 * @return void
	 */
	public function load_data(): void {
		$propanel_2 = LearnDash_ProPanel::get_instance();
		$widget     = $propanel_2->reporting_widget;

		$this->set_propanel2_widget( $widget );
	}
}
