<?php
/**
 * LearnDash LD30 Displays a user group lists.
 * This template is called from the [user_groups] shortcode.
 *
 * @param array   $admin_groups     Array of admin group IDs.
 * @param array   $user_groups      Array of user group IDs.
 * @param boolean $has_admin_groups True if there are admin groups.
 * @param boolean $has_user_groups  True if there are user groups.
 *
 * @since 3.0.0
 * @version 4.21.2
 *
 * @package LearnDash\Templates\LD30
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="learndash-wrapper">
	<div class="ld-user-groups ld-item-list">

		<?php if ( $has_admin_groups ) : ?>
			<div class="ld-item-list-group-leader ld-group-list">
				<div class="ld-section-heading">
					<h2><?php esc_html_e( 'Group Leader', 'learndash' ); ?></h2>
				</div> <!--/.ld-section-heading-->
				<div class="ld-item-list-items">
					<?php
					foreach ( $admin_groups as $learndash_group_id ) :

						if ( empty( $learndash_group_id ) ) {
							continue;
						}

						$learndash_group = get_post( $learndash_group_id );

						if ( ! $learndash_group || ! is_a( $learndash_group, 'WP_POST' ) ) {
							continue;
						}

						learndash_get_template_part(
							'shortcodes/groups/row.php',
							array(
								'group'   => $learndash_group,
								'context' => 'admin-group',
							),
							true
						);

					endforeach;
					?>
				</div> <!--/.ld-table-list-items-->
			</div> <!--/.ld-table-list-->
			<?php
		endif;

		if ( $has_user_groups ) :
			?>
			<div class="ld-item-list-group-leader ld-group-list">
				<div class="ld-item-list-group-leader">
					<div class="ld-section-heading">
						<h2>
						<?php
						printf(
							// translators: group.
							esc_html_x( 'Assigned %s(s)', 'placeholder: group', 'learndash' ),
							learndash_get_custom_label( 'group' )
						)
						?>
						</h2>
					</div>
					<div class="ld-item-list-items">
						<?php
						foreach ( $user_groups as $learndash_group_id ) :

							if ( empty( $learndash_group_id ) ) {
								continue;
							}

							$learndash_group = get_post( $learndash_group_id );

							if ( ! $learndash_group || ! is_a( $learndash_group, 'WP_POST' ) ) {
								continue;
							}

							learndash_get_template_part(
								'shortcodes/groups/row.php',
								array(
									'group'   => $learndash_group,
									'context' => 'user-group',
								),
								true
							);

						endforeach;
						?>
					</div> <!--/.ld-table-list-items-->
				</div> <!--/.ld-table-list-->
			</div>
		<?php endif; ?>
	</div>
</div>
