<?php
/**
 * Export header page
 *
 * @package ImportExportSuite
 */

$checked = is_array( $val ) ? $val[1] : 0;
$val     = ( is_array( $val ) ? $val[0] : $val );
?>
<tr id="columns_<?php echo esc_html( $key ); ?>">
	<td>
	<?php if ( ! isset( $large_meta_columns ) ) : ?>    
	<div class="wt_iew_sort_handle"><span class="dashicons dashicons-move"></span></div>
	<?php endif; ?>
	<input type="checkbox" name="columns_key[]" class="columns_key wt_iew_mapping_checkbox_sub" value="<?php echo esc_html( $key ); ?>" <?php echo ( 1 == $checked ? 'checked' : '' ); ?>></td>
	<td>
			<label class="wt_iew_mapping_column_label"><?php echo esc_html( $label ); ?></label>
	</td>
	<td>
		<input type="text" name="columns_val[]" class="columns_val" value="<?php echo esc_html( $val ); ?>">
	</td>
</tr>
