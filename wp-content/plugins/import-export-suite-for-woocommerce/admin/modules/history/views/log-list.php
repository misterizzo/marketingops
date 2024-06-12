<?php
/**
 * History list page
 *
 * @link
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// delete after redirect.
if ( isset( $_GET['wt_iew_delete_log'] ) ) {
	?>
	<script type="text/javascript">
		window.location.href='<?php echo esc_url( admin_url( 'admin.php?page=wt_import_export_for_woo' ) ) . '&tab=logs'; ?>';
	</script>
	<?php
}
?>
<div class="wt_iew_history_page">
	<h2 class="wp-heading-inline"><?php esc_html_e( 'Import logs', 'import-export-suite-for-woocommerce' ); ?></h2>
	<p>
		<?php esc_html_e( 'Lists developer logs mostly required for debugging purposes. Options to view detailed logs are available along with delete and download(that can be shared with the support team in case of issues).', 'import-export-suite-for-woocommerce' ); ?>
	</p>

	<?php
	$log_path  = Wt_Import_Export_For_Woo_Log::$log_dir;
	$log_files = glob( $log_path . '/*.log' );

	$max_list_count = 50;
	$total_records = count( $log_files );
	$offset = ( isset( $_GET['offset'] ) ? absint( $_GET['offset'] ) : 0 );
	$url_params_allowed = array();
	$url_params_allowed['max_data'] = $max_list_count;
	$pagination_url_params = array(
		'wt_iew_history_log' => $url_params_allowed,
		'page' => 'wt_import_export_for_woo',
		'tab' => 'logs',
	);

	if ( is_array( $log_files ) && count( $log_files ) > 0 ) {
		foreach ( $log_files as $key => $value ) {
			$date_time = str_replace( '.log', '', substr( $value, ( strrpos( $value, '_' ) + 1 ) ) );
			$d         = DateTime::createFromFormat( 'Y-m-d H i s A', $date_time );
			if ( false == $d ) {
				$index = $date_time;
			} else {
				$index = $d->getTimestamp();
			}

			$indexed_log_files[ $index ] = $value;
		}

		krsort( $indexed_log_files );
				$log_files = $indexed_log_files;

		?>
	
		<div class="wt_iew_bulk_action_box">
		<select class="wt_iew_bulk_action wt_iew_select">
			<option value=""><?php esc_html_e( 'Bulk Actions', 'import-export-suite-for-woocommerce' ); ?></option>
			<option value="delete"><?php esc_html_e( 'Delete', 'import-export-suite-for-woocommerce' ); ?></option>
		</select>
		<button class="button button-primary wt_iew_bulk_action_logs_btn" type="button" style="float:left;"><?php esc_html_e( 'Apply', 'import-export-suite-for-woocommerce' ); ?></button>
	</div>
		<?php
		$pagination_html = self::gen_pagination_html( $total_records, $max_list_count, $offset, 'admin.php', $pagination_url_params );
			echo wp_kses_post( $pagination_html );
		?>
	
		<table class="wp-list-table widefat fixed striped history_list_tb log_list_tb">
		<thead>
			<tr>
				<th width="100">
					<input type="checkbox" name="" class="wt_iew_history_checkbox_main">
					<?php esc_html_e( 'No.' ); ?>
				</th>				
				<th class="log_file_name_col"><?php esc_html_e( 'File', 'import-export-suite-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'import-export-suite-for-woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		$i = $offset;
		$log_files = array_slice( $log_files, $offset, $max_list_count );
		foreach ( $log_files as $log_file ) {
			$i++;
			$file_name = basename( $log_file );
			?>
			<tr>
				<th>
					<input type="checkbox" value="<?php echo esc_html( $file_name ); ?>" name="logfile_name[]" class="wt_iew_history_checkbox_sub">
					<?php echo absint( $i ); ?>						
				</td>				
				<td class="log_file_name_col"><a class="wt_iew_view_log_btn" data-log-file="<?php echo esc_html( $file_name ); ?>"><?php echo esc_html( $file_name ); ?></a></td>
				<td>
					<a class="wt_iew_delete_log" data-href="<?php echo esc_html( str_replace( '_log_file_', $file_name, $delete_url ) ); ?>"><?php esc_html_e( 'Delete', 'import-export-suite-for-woocommerce' ); ?></a>
					| <a class="wt_iew_view_log_btn" data-log-file="<?php echo esc_html( $file_name ); ?>"><?php esc_html_e( 'View', 'import-export-suite-for-woocommerce' ); ?></a>
					| <a class="wt_iew_download_log_btn" href="<?php echo esc_html( str_replace( '_log_file_', $file_name, $download_url ) ); ?>"><?php esc_html_e( 'Download', 'import-export-suite-for-woocommerce' ); ?></a>
				</td>
			</tr>
			<?php
		}
		?>
		</tbody>
		</table>
		<?php
	} else {
		?>
		<h4 class="wt_iew_history_no_records"><?php esc_html_e( 'No logs found.', 'import-export-suite-for-woocommerce' ); ?>
		<?php if ( 0 == Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings( 'enable_import_log' ) ) : ?>        
			<span> <?php esc_html_e( 'Please enable import log under', 'import-export-suite-for-woocommerce' ); ?> <a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wt_import_export_for_woo' ) ); ?>"><?php esc_html_e( 'settings', 'import-export-suite-for-woocommerce' ); ?></a></span>        
		<?php endif; ?>
		</h4>
		<?php
	}//end if
	?>
</div>
