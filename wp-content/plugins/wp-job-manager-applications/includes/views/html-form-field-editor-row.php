<?php
/**
 * File containing the row for the application form editor.
 *
 * @package wp-job-manager-applications
 *
 * @version 3.1.0
 *
 * @var array $field
 * @var array $field_types
 * @var int   $index
 * @var array $field_rules
 * @var array $unique_rules
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<tr>
	<td class="sort-column">&nbsp;</td>
	<td>
		<input type="text" class="input-text" name="field_label[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $field['label'] ); ?>" />
	</td>
	<td class="field-type">
		<select name="field_type[<?php echo esc_attr( $index ); ?>]" class="field_type">
			<?php
			foreach ( $field_types as $key => $field_type ) {
				echo '<option value="' . esc_attr( $key ) . '" ' . selected( $field['type'], $key, false ) . '>' . esc_html( $field_type ) . '</option>';
			}
			?>
		</select>
	</td>
	<td>
		<input type="text" class="input-text" name="field_description[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( isset( $field['description'] ) ? $field['description'] : '' ); ?>" placeholder="<?php esc_attr_e( 'N/A', 'wp-job-manager-applications' ); ?>" />
	</td>
	<td class="field-options">
		<input type="text" class="input-text placeholder" name="field_placeholder[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( isset( $field['placeholder'] ) ? $field['placeholder'] : '' ); ?>" placeholder="<?php esc_attr_e( 'N/A', 'wp-job-manager-applications' ); ?>" />

		<input type="text" class="input-text options" name="field_options[<?php echo esc_attr( $index ); ?>]" placeholder="<?php esc_attr_e( 'Pipe (|) separate options.', 'wp-job-manager-applications' ); ?>" value="<?php echo esc_attr( isset( $field['options'] ) ? implode( ' | ', $field['options'] ) : '' ); ?>" />

		<div class="file-options">
			<label class="multiple-files"><input type="checkbox" class="input-text" name="field_multiple_files[<?php echo esc_attr( $index ); ?>]" value="1" <?php checked( ! empty( $field['multiple'] ), true ); ?> /> <?php esc_attr_e( 'Multiple Files?', 'wp-job-manager-applications' ); ?></label>
		</div>

		<span class="na">&ndash;</span>
	</td>

	<?php
	/**
	 * Output content for additional columns in the form field editor.
	 *
	 * @see job_application_form_field_editor_column_header to add the matching header.
	 *
	 * @since 3.1.0
	 *
	 * @param array  $field Field array.
	 * @param int    $index Index of the field.
	 *
	 * @return void
	 * @output HTML <td> tag for the extra column
	 */
	do_action( 'job_application_form_field_editor_column_row', $field, $index );

	?>
	<td class="field-rules">
		<div class="rules">
			<select name="field_rules[<?php echo esc_attr( $index ); ?>][]" multiple="multiple" style="width: 250px;">
				<?php
					$field['rules'] = array_filter( (array) ( isset( $field['rules'] ) ? $field['rules'] : [] ) );

				foreach ( $field_rules as $optgroup => $rules ) {
					echo '<optgroup label="' . esc_attr( $optgroup ) . '">';
					foreach ( $rules as $key => $rule ) {
						$unique = '';
						if ( 'required' === $key ) {
							$selected = selected( ! empty( $field['required'] ), true, false );
						} else {
							$selected = selected( in_array( $key, $field['rules'], true ), true, false );
						}
						if ( in_array( $key, $unique_rules, true ) ) {
							$unique = ' class="unique"';
						}
						//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped or static strings.
						echo '<option value="' . esc_attr( $key ) . '" ' . $selected . $unique . '>' . esc_html( $rule ) . '</option>';
					}
					echo '</optgroup>';
				}
				?>
			</select>
		</div>
		<span class="na">&ndash;</span>
	</td>
	<td class="field-actions">
		<a class="delete-field" href='#'>X</a>
	</td>
</tr>
