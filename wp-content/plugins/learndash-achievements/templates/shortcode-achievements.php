<?php
/**
 * Shortcode output template for achievements.
 *
 * @var array{
 *      'user_id'         : int,
 *      'show_title'      : bool,
 *      'show_points'     : bool,
 *      'points_position' : string,
 *      'points_label'    : string
 * } $atts Shortcode attributes.
 *
 * TODO: Move this to a view folder in src/views and use StellarWP Template.
 *
 * @package LearnDash\Achievements
 */

use LearnDash\Achievements\Database;
use LearnDash\Achievements\Settings;
use LearnDash\Achievements\Utilities\Assets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="ld-achievements">
	<?php if ( $atts['show_title'] ) : ?>
		<h2><?php esc_html_e( 'My Achievements', 'learndash-achievements' ); ?></h2>
	<?php endif; ?>

	<?php
	$settings         = get_option( 'learndash_achievements_settings_badge', Settings::get_default_value() );
	$size             = isset( $settings['size'] ) ? absint( $settings['size'] ) : 40;
	$tooltip_fontsize = isset( $settings['tooltip_font_size'] ) ? absint( $settings['tooltip_font_size'] ) : 12;
	$points           = Database::get_user_points( $atts['user_id'] );
	if ( $tooltip_fontsize ) {
		?>
			<style type="text/css">
				.ld-achievement-tooltip .ld-achievement-tooltip-text{
					font-size: <?php echo absint( $tooltip_fontsize ); ?>px;
				}
			</style>
		<?php
	}

	if (
		$atts['show_points']
		&& $atts['points_position'] === 'before'
	) {
		include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'templates/achievements/points.php';
	}

	// TODO: Update this to use setup_postdata
	if ( ! empty( $achievements ) ) {
		echo '<div class="ld-achievement-items">';

		foreach ( $achievements as $achievement ) :
			$current_post = get_post( $achievement->post_id );
			if ( ! is_object( $current_post ) || 'publish' !== $current_post->post_status ) {
				continue;
			}
			$image   = Assets::achievement_icon_url( $achievement->post_id );
			$tooltip = $current_post->post_title;
			$content = $current_post->post_content;

			?>
			<span class="ld-achievement-image ld-achievement-tooltip">
				<span class="ld-achievement-tooltip-text">
					<?php echo esc_html( $tooltip ); ?>
				</span>
				<img width="<?php echo absint( $size ); ?>px" src="<?php echo esc_attr( $image ); ?>" alt="<?php echo esc_attr( $content ); ?>">
				<?php echo $achievement->c > 1 ? ' x' . esc_html( $achievement->c ) . '' : ''; ?>
			</span>
			<?php
		endforeach;

		echo '</div>';
	}

	if (
		$atts['show_points']
		&& $atts['points_position'] === 'after'
	) {
		include LEARNDASH_ACHIEVEMENTS_PLUGIN_PATH . 'templates/achievements/points.php';
	}

	echo '</div>';
