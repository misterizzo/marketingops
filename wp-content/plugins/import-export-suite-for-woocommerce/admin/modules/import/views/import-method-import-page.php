<?php
/**
 * Import page method select
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wt_iew_import_main">
	<p>
	<?php
	// echo $this->step_description;.
	?>
	</p>
	<div class="wt_iew_warn wt_iew_method_import_wrn" style="display:none;">
		<?php esc_html_e( 'Please select an import template.', 'import-export-suite-for-woocommerce' ); ?>
	</div>
	<table class="form-table wt-iew-form-table">
		<tr>
			<th><label><?php esc_html_e( 'Import method', 'import-export-suite-for-woocommerce' ); ?></label></th>
			<td colspan="2" style="width:75%;">
				<div class="wt_iew_radio_block">
					<?php
					if ( empty( $this->mapping_templates ) ) {
						unset( $this->import_obj->import_methods['template'] );
					}
					foreach ( $this->import_obj->import_methods as $key => $value ) {
						?>
						<p>
							<input type="radio" value="<?php echo esc_html( $key ); ?>" id="wt_iew_import_<?php echo esc_html( $key ); ?>_import" name="wt_iew_import_method_import" <?php echo ( $this->import_method == $key ? 'checked="checked"' : '' ); ?>><b><label for="wt_iew_import_<?php echo esc_html( $key ); ?>_import"><?php echo esc_html( $value['title'] ); ?></label></b> <br />
							<span><label for="wt_iew_import_<?php echo esc_html( $key ); ?>_import"><?php echo esc_html( $value['description'] ); ?></label></span>
						</p>
						<?php
					}
					?>
				</div>
			</td>
		</tr>
		<tr><div id="user-required-field-message" class="updated" style="margin-left:0px;display: none;background: #dceff4;"><p><?php /* translators: 1: html b open. 2: html b close */ printf( esc_html__( 'Ensure the import file has the user email ID for a successful import. Use default column name %1$s user_email %2$s or map the column accordingly if you are using a custom column name.' ), '<b>', '</b>' ); ?></p></div></tr>
		<tr><div id="subscription-required-field-message" class="updated" style="margin-left:0px;display: none;background: #dceff4;"><p><?php /* translators: 1: html b open. 2: html b close */ printf( esc_html__( 'Ensure the import file has the user email ID for a successful import. Use default column name %1$s customer_email %2$s or map the column accordingly if you are using a custom column name.' ), '<b>', '</b>' ); ?></p></div></tr>
		<tr class="wt-iew-import-method-options wt-iew-import-method-options-template wt-iew-import-template-sele-tr" style="display:none;">
			<th><label><?php esc_html_e( 'Import template' ); ?></label></th>
			<td>
				<select class="wt-iew-import-template-sele">
					<option value="0">-- <?php esc_html_e( 'Select a template' ); ?> --</option>
					<?php
					foreach ( $this->mapping_templates as $mapping_template ) {
						?>
						<option value="<?php echo esc_html( $mapping_template['id'] ); ?>" <?php echo ( $form_data_import_template == $mapping_template['id'] ? ' selected="selected"' : '' ); ?>>
						<?php echo esc_html( $mapping_template['name'] ); ?>
						</option>
						<?php
					}
					?>
				</select>
			</td>
			<td>
			</td>
		</tr>
	</table>
	<form class="wt_iew_import_method_import_form">
		<table class="form-table wt-iew-form-table">
			<?php
			Wt_Import_Export_For_Woo_Common_Helper::field_generator( $method_import_screen_fields, $method_import_form_data );
			?>
		</table>
	</form>
</div>
<script type="text/javascript">
/* remote file modules can hook */
function wt_iew_set_file_from_fields(file_from)
{
	<?php
	/**
	 * Importer file from JS.
	 *
	 * Enables adding extra arguments or setting defaults for a request.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wt_iew_importer_file_from_js_fn' );
	?>
}

function wt_iew_set_validate_file_info(file_from)
{
	<?php
	/**
	 * Importer set file validate info.
	 *
	 * Enables adding extra arguments or setting defaults for a request.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wt_iew_importer_set_validate_file_info' );
	?>
}
</script>
