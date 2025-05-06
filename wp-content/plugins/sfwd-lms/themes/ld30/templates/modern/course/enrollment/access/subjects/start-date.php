<?php
/**
 * View: Course Enrollment Access Subject - Start Date.
 *
 * @since 4.21.0
 * @version 4.21.0
 *
 * @var Product $product Product model.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Models\Product;

?>
<div class="ld-enrollment__subject ld-enrollment__subject--start-date">
	<?php
	printf(
		// translators: placeholder: %s = course start date.
		esc_html_x( 'Started %s', 'When a course has started', 'learndash' ),
		esc_html( learndash_adjust_date_time_display( (int) $product->get_start_date() ) )
	);
	?>
</div>
