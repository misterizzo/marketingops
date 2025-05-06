<?php
/**
 * Template for displaying the leaderboard table.
 *
 * @var array<string, mixed> $atts Shortcode attributes.
 *
 * @package LearnDash\Achievements
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="ld-achievements-leaderboard">
	<table>
		<thead>
		<tr>
			<th><?php esc_html_e( 'User', 'learndash-achievements' ); ?></th>
			<th><?php esc_html_e( 'Badges Earned', 'learndash-achievements' ); ?></th>
			<?php if ( true === boolval( $atts['show_points'] ) ) { ?>
				<th><?php esc_html_e( 'Points', 'learndash-achievements' ); ?></th>
			<?php } ?>
		</tr>
		</thead>
		<tbody>
		<?php
		use LearnDash\Achievements\Settings;
		use LearnDash\Achievements\Utilities\Assets;

		$learndash_achievements_settings         = get_option( 'learndash_achievements_settings_badge', Settings::get_default_value() );
		$learndash_achievements_tooltip_fontsize = isset( $learndash_achievements_settings['tooltip_font_size'] ) ? absint( $learndash_achievements_settings['tooltip_font_size'] ) : 12;
		if ( $learndash_achievements_tooltip_fontsize ) {
			?>
				<style type="text/css">
					.ld-achievement-tooltip .ld-achievement-tooltip-text{
						font-size: <?php echo absint( $learndash_achievements_tooltip_fontsize ); ?>px;
					}
				</style>
			<?php
		}

		if ( empty( $leaders ) ) :
			?>
			<tr style="">
				<td colspan="3">No data available</td>
			</tr>
		<?php else : ?>
			<?php foreach ( $leaders as $key => $leader ) : ?>
				<?php
				$user = get_user_by( 'id', $leader['user_id'] );
				if ( ! is_object( $user ) ) {
					continue;
				}
				?>
				<tr <?php echo 0 !== $key % 2 ? 'class="odd"' : null; ?>>
					<td><?php echo esc_html( $user->display_name ); ?></td>
					<td>
						<?php
						foreach ( $leader['post'] as $badge ) :
							$image = Assets::achievement_icon_url( $badge->ID );
							?>
						<span class="ld-achievement-image ld-achievement-tooltip">
							<span class="ld-achievement-tooltip-text">
							<?php
							echo esc_html( $badge->post_title );
							?>
							</span>
							<img width="40" src="<?php echo esc_url_raw( $image ); ?>" alt="<?php echo esc_attr( $badge->post_content ); ?>">
						</span>
						<?php endforeach; ?>
					</td>
					<?php
					if ( true === boolval( $atts['show_points'] ) ) {
						echo '<td>' . absint( $leader['total_points'] ) . '</td>';
					}
					?>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
</div>
