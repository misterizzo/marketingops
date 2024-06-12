<?php
/**
 * Export page post type select
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wt_iew_export_main">
	<p><?php echo esc_html( $step_info['description'] ); ?></p>
		<?php if ( empty( $post_types ) ) { ?>
			<div class="wt_iew_warn wt_iew_post_type_wrn">
				<?php

				echo wp_kses_post(
					sprintf(
						/* translators: 1: html b. 2: html b close. 3: link to my-account. 4: link to admin */
						__(
							'Atleast one of the %1$s WebToffee add-ons(Product/Reviews, User, Order/Coupon/Subscription)%2$s should be activated to start exporting the respective post type. Go to <a href="%3$s" target="_blank">My accounts->API Downloads</a> to download and activate the add-on. If already installed activate the respective add-on plugin under <a href="%4$s" target="_blank">Plugins</a>.',
							'import-export-suite-for-woocommerce'
						),
						'<b>',
						'</b>',
						'https://www.webtoffee.com/my-account/my-api-downloads/',
						admin_url( 'plugins.php?s=webtoffee' )
					)
				);
				?>
			</div>
		<?php } ?>
		
	<table class="form-table wt-iew-form-table">
		<tr>
			<th><label><?php esc_html_e( 'Select a post type to export', 'import-export-suite-for-woocommerce' ); ?></label></th>
			<td>
				<select name="wt_iew_export_post_type">
					<option value="">-- <?php esc_html_e( 'Select post type', 'import-export-suite-for-woocommerce' ); ?> --</option>
					<?php
					foreach ( $post_types as $key => $value ) {
						?>
						<option value="<?php echo esc_html( $key ); ?>" <?php echo ( $item_type == $key ? 'selected' : '' ); ?>><?php echo esc_html( $value ); ?></option>
						<?php
					}
					?>
				</select>
			</td>
			<td></td>
		</tr>
	</table>
</div>
