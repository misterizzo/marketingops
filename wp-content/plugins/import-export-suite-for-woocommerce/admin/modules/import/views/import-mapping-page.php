<?php
/**
 * Import mapping page
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
	$click_to_use = __( 'Click to use', 'import-export-suite-for-woocommerce' );

	$heavy_meta = array( 'status' => false );
	$file_heading_meta_fields    = isset( $file_heading_meta_fields ) ? $file_heading_meta_fields : array();
	$file_heading_default_fields = isset( $file_heading_default_fields ) ? $file_heading_default_fields : array();
	$mapping_fields = isset( $mapping_fields ) ? $mapping_fields : array();
	$skip_from_evaluation_array = isset( $skip_from_evaluation_array ) ? $skip_from_evaluation_array : array();

if ( count( $file_heading_meta_fields ) > 2000 ) {
	$heavy_meta['status']     = true;
	$heavy_meta['message']    = __( 'Large number of meta data has been detected. If you choose to proceed, this action will import only 2000 meta columns ( current website meta + new meta from the input file, in sequence). Consider removing unnecessary columns from input file and try again.', 'import-export-suite-for-woocommerce' );
	$heavy_meta['count']      = count( $file_heading_meta_fields );
	$file_heading_meta_fields = array_slice( $file_heading_meta_fields, 0, 2000 );
}
?>
<script type="text/javascript">
	
	var wt_iew_file_head_default=<?php echo json_encode( Wt_Import_Export_For_Woo_Common_Helper::wt_iew_utf8ize( $file_heading_default_fields ) ); ?>;
	var wt_iew_file_head_meta=<?php echo json_encode( Wt_Import_Export_For_Woo_Common_Helper::wt_iew_utf8ize( $file_heading_meta_fields ) ); ?>;
	var wt_iew_skip_from_evaluation_array=<?php echo json_encode( Wt_Import_Export_For_Woo_Common_Helper::wt_iew_utf8ize( $skip_from_evaluation_array ) ); ?>;                
</script>

<!-- Mapping field editor popup -->
<div class="wt_iew_mapping_field_editor_container" data-title="<?php esc_html_e( 'Set value for column:', 'import-export-suite-for-woocommerce' ); ?> <span class='wt_iew_target_column'></span>" data-module="import">
	<div class="wt_iew_mapping_field_editor">    
		<p class="wt_iew_mapping_field_editor_info" style="margin-bottom:5px;"><?php esc_html_e( 'Select and map any column from the input file or compute values with expressions.', 'import-export-suite-for-woocommerce' ); ?></p>
		
		<label><?php esc_html_e( 'Expression', 'import-export-suite-for-woocommerce' ); ?></label>
		<p class="wt_iew_mapping_field_editor_info">
		<ul class="wt_evaluation_expression_points">
			<li><?php esc_html_e( 'Append operators like + * / - () & @ or string constants along with the column names to update the values on import.', 'import-export-suite-for-woocommerce' ); ?></li>
			<li><?php /* translators: 1: HTML open b. 2: HTML close b */ printf( esc_html__( 'Ensure to enclose the expressions in square brackets. E.g. To increase the stock quantity of %1$sall imported products%2$s by 5 units, input [{stock}+5] in the stock column.', 'import-export-suite-for-woocommerce' ), '<b>', '</b>' ); ?></li>    
		</ul>
		</p>
		<p class="wt_iew_mapping_field_editor_er"></p>
		<div class="wt_iew_mapping_field_editor_box">
			<textarea class="wt_iew_mapping_field_editor_expression"></textarea>
		</div>

		<label><?php esc_html_e( 'Input file columns', 'import-export-suite-for-woocommerce' ); ?></label>    
		<div class="wt_iew_mapping_field_editor_box">
			<input type="text" class="wt_iew_mapping_field_editor_column_search" placeholder="<?php esc_html_e( 'Type here to search', 'import-export-suite-for-woocommerce' ); ?>">
			<div class="wt_iew_mapping_field_selector_box">
				<ul class="wt_iew_mapping_field_selector">
					<?php
					foreach ( $file_heading_default_fields as $key => $value ) {
						?>
						<li title="<?php echo esc_html( $click_to_use ); ?>" data-val="<?php echo esc_html( $key ); ?>"><?php echo esc_html( $key ); ?></li>
						<?php
					}

					foreach ( $file_heading_meta_fields as $key => $value ) {
						?>
						<li title="<?php echo esc_html( $click_to_use ); ?>" data-val="<?php echo esc_html( $key ); ?>"><?php echo esc_html( $key ); ?></li>
						<?php
					}
					?>
				</ul>
				<div class="wt_iew_mapping_field_selector_no_column"><?php esc_html_e( 'No column found.', 'import-export-suite-for-woocommerce' ); ?></div>    
			</div>
		</div>

		<label><?php esc_html_e( 'Output' ); ?></label>
		<p class="wt_iew_mapping_field_editor_info">
			<?php esc_html_e( 'Sample value based on first record from input file. Columns that have no values in the input file may cause syntax errors if used in an expression as above.', 'import-export-suite-for-woocommerce' ); ?>
		</p>
		<div class="wt_iew_mapping_field_editor_box" style="max-height:80px; overflow:auto; margin-bottom:0px; border:dashed 1px #ccc; padding:5px;">
			<div class="wt_iew_mapping_field_editor_sample"></div>
		</div>        
	</div>
</div>
<!-- Mapping field editor popup -->

<div class="wt_iew_import_main">    
	<p><?php echo esc_html( $this->step_description ); ?></p>

	<p class="wt_iew_info_box wt_iew_info">
		<?php esc_html_e( 'The first row from your input file is considered as a header for mapping columns and hence will NOT BE imported. If the input file columns are not mapped automatically, please associate corresponding columns by selecting appropriately.', 'import-export-suite-for-woocommerce' ); ?>
		<!--<br />-->
				<?php
				// _e('Columns are mapped automatically only if a matching header name is found in the input file.', 'import-export-suite-for-woocommerce');
				?>
		<!--<br />-->
		<?php
		// _e('If your input file header does not have the default column names the corresponding columns in the below section will be empty. In this case you can simply click on the empty fields in the screen below and associate the corresponding column from your input file. Furthermore you can also assign expressions to these columns.', 'import-export-suite-for-woocommerce');
		?>
	</p>

	<div class="meta_mapping_box">
		<div class="meta_mapping_box_hd_nil wt_iew_noselect">
			<?php esc_html_e( 'Default fields', 'import-export-suite-for-woocommerce' ); ?>
			<span class="meta_mapping_box_selected_count_box"><span class="meta_mapping_box_selected_count_box_num">0</span> <?php esc_html_e( ' columns(s) selected', 'import-export-suite-for-woocommerce' ); ?></span>
		</div>
		<div style="clear:both;"></div>
		<div class="meta_mapping_box_con" data-sortable="0" data-loaded="1" data-field-validated="0" data-key="" style="display:inline-block;">
			<table class="wt-iew-mapping-tb wt-iew-importer-default-mapping-tb">
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
				foreach ( $form_data_mapping_fields as $key => $val_arr ) {
					// looping the template form data.
					$val = $val_arr[0];
					// normal column val.
					$checked = $val_arr[1];
					// import this column?
					if ( isset( $mapping_fields[ $key ] ) ) {
						// found in default field list.
						$label       = ( isset( $mapping_fields[ $key ]['title'] ) ? $mapping_fields[ $key ]['title'] : '' );
						$description = ( isset( $mapping_fields[ $key ]['description'] ) ? $mapping_fields[ $key ]['description'] : '' );
						$mapping_field_type        = ( isset( $mapping_fields[ $key ]['type'] ) ? $mapping_fields[ $key ]['type'] : '' );
						unset( $mapping_fields[ $key ] );
						// remove the field from default list.
						if ( isset( $file_heading_default_fields[ $key ] ) ) {
							// also found in file heading list.
							unset( $file_heading_default_fields[ $key ] );
							// remove the field from file heading list.
						}

						include 'import-mapping-tr-html.php';
						$tr_count++;
					} else if ( isset( $file_heading_default_fields[ $key ] ) ) {
						// found in file heading list.
						$label       = $key;
						$description = $key;
						$mapping_field_type        = '';
						unset( $file_heading_default_fields[ $key ] );
						// remove the field from file heading list.
						include 'import-mapping-tr-html.php';
						$tr_count++;
					} else if ( isset( $file_heading_meta_fields[ $key ] ) ) {
						// some meta items will show inside default field list, Eg: yoast.
						$label       = $key;
						$description = $key;
						$mapping_field_type        = '';
						unset( $file_heading_meta_fields[ $key ] );
						// remove the field from file heading list.
						include 'import-mapping-tr-html.php';
						$tr_count++;
					}//end if
				}//end foreach

				/*
				 *    ####Important####
				 *    The similar code also done in Default mapping preparation step for quick import.
				 *    If any updates done please update there also
				 *    Method _prepare_for_quick in import ajax  class
				 */

				if ( count( $mapping_fields ) > 0 ) {
										$array_keys_file_heading_default_fields = array_keys( $file_heading_default_fields );
					$allowed_field_types = array(
						'start_with',
						'end_with',
						'contain',
					);
					foreach ( $mapping_fields as $key => $val_arr ) {
						$label       = ( isset( $val_arr['title'] ) ? $val_arr['title'] : '' );
						$description = ( isset( $val_arr['description'] ) ? $val_arr['description'] : '' );
						$mapping_field_type        = ( isset( $val_arr['type'] ) ? $val_arr['type'] : '' );
						$val         = '';
						$checked     = 0;
						/**
						 *
						Import this column? */
						// if(isset($file_heading_default_fields[$key])).
						$case_key = preg_grep( "/^$key$/i", $array_keys_file_heading_default_fields );
						if ( $case_key ) {
							// preg_grep used escape from case sensitive check.
							$checked = 1;
							/**
							 *
							 Import this column? */
							// $val='{'.$key.'}';
							$val = '{' . array_shift( $case_key ) . '}';
							// preg_grep give an array with actual index and value.
							unset( $file_heading_default_fields[ $key ] );
							// remove the field from file heading list.
								unset( $array_keys_file_heading_default_fields[ $key ] );
							include 'import-mapping-tr-html.php';
							$tr_count++;
						} else if ( isset( $file_heading_meta_fields[ $key ] ) ) {
							// some meta items will show inside default field list, Eg: yoast.
							$checked = 1;
							// import this column?
							$val = '{' . $key . '}';
							unset( $file_heading_meta_fields[ $key ] );
							// remove the field from file heading list.
							include 'import-mapping-tr-html.php';
							$tr_count++;
						} else {
							$field_type = ( isset( $val_arr['field_type'] ) ? $val_arr['field_type'] : '' );
							if ( '' != $field_type && in_array( $field_type, $allowed_field_types ) ) {
								// it may be a different field type.
								foreach ( $file_heading_default_fields as $def_key => $def_val ) {
									$matched = false;
									if ( 'start_with' == $field_type && 0 === strpos( $def_key, $key ) ) {
											  $matched = true;
									} else if ( 'ends_with' == $field_type && strrpos( $def_key, $key ) === ( strlen( $def_key ) - strlen( $key ) ) ) {
										$matched = true;
									} else if ( 'contains' == $field_type && strpos( $def_key, $key ) !== false ) {
										$matched = true;
									}

									if ( $matched ) {
										$checked = 1;
										// import this column?
										$val        = '{' . $def_key . '}';
										$label      = $def_key;
										$key_backup = $key;
										$key        = $def_key;
										unset( $file_heading_default_fields[ $def_key ] );
										// remove the field from file heading list.
										include 'import-mapping-tr-html.php';
										$tr_count++;
										$key = $key_backup;
									}
								}//end foreach
							} else // unmatched keys.
							{
								$checked = 0;
								// import this column?
								$val = '';
								include 'import-mapping-tr-html.php';
								$tr_count++;
							}//end if
						}//end if
					}//end foreach
				}//end if

				// if ( count( $file_heading_default_fields ) > 0 ) {
					// show the remaining items.
					/**
						Fforeach($file_heading_default_fields as $key=>$sample_val)
						{
						$label=$key;
						$description=$key;
						$val='{'.$key.'}';
						$checked=1;
						include "import-mapping-tr-html.php";
						$tr_count++;
						}
					*/
				// }

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
<script type="text/javascript">
		var wt_iew_file_head_remaining_meta=<?php echo json_encode( Wt_Import_Export_For_Woo_Common_Helper::wt_iew_utf8ize( $file_heading_meta_fields ) ); ?>;
	var heavy_meta=<?php echo json_encode( $heavy_meta ); ?>;
</script>
