<?php
/**
 * Import meta step page
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="wt-iew-mapping-tb wt-iew-importer-meta-mapping-tb" data-field-type="<?php echo esc_html( $meta_mapping_screen_field_key ); ?>">
	<thead>
		<tr>
			<th>
				<?php
				$is_checked   = $meta_mapping_screen_field_val['checked'];
				$checked_attr = ( 1 == $is_checked ? ' checked="checked"' : '' );
				?>
				<input type="checkbox" name="" class="wt_iew_mapping_checkbox_main" <?php echo esc_html( $checked_attr ); ?>>
			</th>
			<th width="35%"><?php esc_html_e( 'Column' ); ?></th>
			<th><?php esc_html_e( 'Column name' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$tr_count = 0;

		foreach ( $meta_mapping_screen_field_val['fields'] as $key => $val_arr ) {

			$label       = ( isset( $val_arr['label'] ) ? $val_arr['label'] : '' );
			$val      = ( isset( $val_arr['val'] ) ? $val_arr['val'] : '' );
			$checked      = ( isset( $val_arr['checked'] ) ? (bool) $val_arr['checked'] : 0 );
			$description = ( isset( $val_arr['description'] ) ? $val_arr['description'] : '' );
			$mapping_field_type        = ( isset( $val_arr['type'] ) ? $val_arr['type'] : '' );

			include 'import-mapping-tr-html.php';
			$tr_count++;
		}

		if ( 0 == $tr_count ) {
			?>
			<tr>
				<td colspan="3" style="text-align:center;">
			<?php esc_html_e( 'No fields found.' ); ?>
				</td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>
