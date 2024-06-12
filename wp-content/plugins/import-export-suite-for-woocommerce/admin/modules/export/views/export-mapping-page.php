<?php
/**
 * Export mapping page
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wt_iew_export_main">
	<p><?php echo esc_html( $step_info['description'] ); ?></p>
	<div class="meta_mapping_box">
		<div class="meta_mapping_box_hd_nil wt_iew_noselect">
			<?php esc_html_e( 'Default fields', 'import-export-suite-for-woocommerce' ); ?>
			<span class="meta_mapping_box_selected_count_box"><span class="meta_mapping_box_selected_count_box_num">0</span> <?php esc_html_e( ' columns(s) selected', 'import-export-suite-for-woocommerce' ); ?></span>
		</div>
		<div style="clear:both;"></div>
		<div class="meta_mapping_box_con" data-sortable="0" data-loaded="1" data-field-validated="0" data-key="" style="display:inline-block;">
			<table class="wt-iew-mapping-tb wt-iew-exporter-default-mapping-tb">
				<thead>
					<tr>
						<th>
							<input type="checkbox" name="" class="wt_iew_mapping_checkbox_main">
						</th>
						<th width="35%"><?php esc_html_e( 'Column', 'import-export-suite-for-woocommerce' ); ?></th>
						<th><?php esc_html_e( 'Column name', 'import-export-suite-for-woocommerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$draggable_tooltip = __( 'Drag to rearrange the columns', 'import-export-suite-for-woocommerce' );
				$tr_count          = 0;
				foreach ( $form_data_mapping_fields as $key => $val ) {
					if ( isset( $mapping_fields[ $key ] ) ) {
						$label = $mapping_fields[ $key ];
						include 'export-mapping-tr-html.php';
						unset( $mapping_fields[ $key ] );
						// remove the field from default list.
						$tr_count++;
					}
				}

				if ( count( $mapping_fields ) > 0 ) {
					foreach ( $mapping_fields as $key => $label ) {
						$val = array(
							$key,
							1,
						);
						// enable the field.
						include 'export-mapping-tr-html.php';
						$tr_count++;
					}
				}

				if ( 0 == $tr_count ) {
					?>
					<tr>
						<td colspan="3" style="text-align:center;">
					<?php esc_html_e( 'No fields found.', 'import-export-suite-for-woocommerce' ); ?>
						</td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>
		</div>
	</div>
	<div style="clear:both;"></div>
	<?php
	if ( $this->mapping_enabled_fields ) {
		foreach ( $this->mapping_enabled_fields as $mapping_enabled_field_key => $mapping_enabled_field ) {
			$mapping_enabled_field = ( ! is_array( $mapping_enabled_field ) ? array( $mapping_enabled_field, 0 ) : $mapping_enabled_field );

			if ( count( $form_data_mapping_enabled_fields ) > 0 ) {
				if ( in_array( $mapping_enabled_field_key, $form_data_mapping_enabled_fields ) ) {
					$mapping_enabled_field[1] = 1;
				} else {
					$mapping_enabled_field[1] = 0;
				}
			}
			?>
			<div class="meta_mapping_box">
				<div class="meta_mapping_box_hd wt_iew_noselect">
					<span class="dashicons dashicons-arrow-right"></span>
			<?php echo esc_html( $mapping_enabled_field[0] ); ?>
					<span class="meta_mapping_box_selected_count_box"><span class="meta_mapping_box_selected_count_box_num">0</span> <?php esc_html_e( ' columns(s) selected', 'import-export-suite-for-woocommerce' ); ?></span>
				</div>
				<div style="clear:both;"></div>
				<div class="meta_mapping_box_con" data-sortable="0" data-loaded="0" data-field-validated="0" data-key="<?php echo esc_html( $mapping_enabled_field_key ); ?>"></div>
			</div>
			<div style="clear:both;"></div>
			<?php
		}//end foreach
	}//end if
	?>
</div>
