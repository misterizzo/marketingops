<?php
/**
 * Import page post type select
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wt_iew_import_main">
	<div class="wt-migrations-sequence-headsup" style="background: #E4F1FF;padding: 1px 12px;">
		<p>
			<?php
				echo wp_kses_post(
					sprintf(
						/* translators: 1: html b. 2: html b close.*/
						__(
							'For a complete store migration, we recommend you to import the files in the following sequence: %1$s User/Customer > Product > Product Review > Coupon > Order > Subscription %2$s ',
							'import-export-suite-for-woocommerce'
						),
						'<b>',
						'</b>'
					)
				);
				?>
		</p>
	</div>
	<p><?php echo esc_html( $this->step_description ); ?></p>
	<table class="form-table wt-iew-form-table">
		<tr>
			<th><label><?php esc_html_e( 'Select a post type to import', 'import-export-suite-for-woocommerce' ); ?></label></th>
			<td>
				<select name="wt_iew_import_post_type">
					<option value="">-- <?php esc_html_e( 'Select post type', 'import-export-suite-for-woocommerce' ); ?> --</option>
					<?php
					$item_type = isset( $item_type ) ? $item_type : '';
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
