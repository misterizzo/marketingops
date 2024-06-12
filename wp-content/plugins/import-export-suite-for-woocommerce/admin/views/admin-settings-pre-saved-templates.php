<?php
/**
 * Admin settings pre-saved templates section
 *
 * @link
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$tb = $wpdb->prefix . Wt_Import_Export_For_Woo::$template_tb;
$val = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wt_iew_mapping_template ORDER BY id DESC", ARRAY_A ); // @codingStandardsIgnoreLine
$pre_saved_templates = ( $val ? $val : array() );
if ( ! empty( $pre_saved_templates ) ) :
	?>


	<style>
		.wt_ier_template_list_table {
			width: 50%;
			border-spacing: 0px;
			border-collapse: collapse;
			margin-top: 15px;
		}
		.wt_ier_template_list_table th {
			padding: 5px 5px;
			background: #f9f9f9;
			color: #333;
			text-align: center;
			border: solid 1px #e1e1e1;
			font-weight: bold;
		}
		.wt_ier_template_list_table td {
			padding: 5px 5px;
			background: #fff;
			color: #000;
			text-align: center;
			border: solid 1px #e1e1e1;
		}
	</style>
	<div class="wt-ier-import-export-templates">
		<h3><?php esc_html_e( 'Import export pre-saved templates', 'import-export-suite-for-woocommerce' ); ?></h3>
		<div class="wt_ier_template_list_table_data">
		<table class="wt_ier_template_list_table">
			<thead>
				<tr>
					<th style="width:50px;">#</th>
					<th><?php esc_html_e( 'Name', 'import-export-suite-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Item', 'import-export-suite-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Type', 'import-export-suite-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Action', 'import-export-suite-for-woocommerce' ); ?></th>                                
				</tr>
			</thead>
			<tbody>
				<?php
				$num = 1;
				foreach ( $pre_saved_templates as $key => $value ) :
					?>
					<tr data-row-id="<?php echo absint( $value['id'] ); ?>">
						<td><?php echo esc_html( $num ); ?></td>                                    
						<td><?php echo esc_html( $value['name'] ); ?></td>
						<td><?php echo esc_html( $value['item_type'] ); ?></td>
						<td><?php echo esc_html( $value['template_type'] ); ?></td>
						<td><button data-id="<?php echo absint( $value['id'] ); ?>" title="<?php esc_html_e( 'Delete', 'import-export-suite-for-woocommerce' ); ?>" class="button button-secondary wt_ier_delete_template"><span><?php esc_html_e( 'Delete', 'import-export-suite-for-woocommerce' ); ?></span></button></td>
					</tr>           
					<?php
					$num++;
				endforeach;
				?>
			</tbody>
		</table>
		</div>
	</div>
<?php endif; ?>
