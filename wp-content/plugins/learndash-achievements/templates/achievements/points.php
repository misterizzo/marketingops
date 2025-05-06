<?php
/**
 * Achievement points template.
 *
 * @since 2.0.0
 * @version 2.0.0
 *
 * @var array{
 *       'user_id'         : int,
 *       'show_title'      : bool,
 *       'show_points'     : bool,
 *       'points_position' : string,
 *       'points_label'    : string
 *  } $atts Shortcode attributes.
 * @var int $points User points.
 *
 * TODO: Move this to a view folder in src/views and use StellarWP Template.
 *
 * @package LearnDash\Achievements
 */

use LearnDash\Core\Utilities\Cast;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ld-achievements-points">
	<span class="ld-achievements-points-label">
		<?php echo esc_html( Cast::to_string( $atts['points_label'] ) ); ?>:
	</span>
	<span>
		<?php echo esc_html( Cast::to_string( $points ) ); ?>
	</span>
</div>
