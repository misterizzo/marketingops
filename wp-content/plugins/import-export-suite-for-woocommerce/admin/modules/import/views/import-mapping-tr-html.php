<?php
/**
 * Import mapping tr
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<tr id="columns_<?php echo esc_html( $key ); ?>">
	<td>
		<input type="checkbox" name="columns_key[]" class="columns_key wt_iew_mapping_checkbox_sub" value="<?php echo esc_html( $key ); ?>" <?php echo ( 1 == $checked ? 'checked' : '' ); ?>>
	</td>
	<td>
		<label class="wt_iew_mapping_column_label"><?php echo esc_html( $label ); ?></label>
	</td>
	<td>
		<input type="hidden" name="columns_val[]" class="columns_val" value="<?php echo esc_html( $val ); ?>" data-type="<?php echo esc_html( $mapping_field_type ); ?>">
		<span data-wt_iew_popover="1" data-title="" data-content-container=".wt_iew_mapping_field_editor_container" class="wt_iew_mapping_field_val"><?php echo esc_html( $val ); ?></span>        
	</td>
</tr>
