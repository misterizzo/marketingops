<?php
/**
 * History settings page
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style type="text/css">
.wt_iew_history_page{ padding:15px; }
.history_list_tb td, .history_list_tb th{ text-align:center; }
.history_list_tb tr th:first-child{ text-align:left; }
.wt_iew_delete_history, .wt_iew_delete_log{ cursor:pointer; }
.wt_iew_history_settings{  float:left; width:100%; padding:15px; background:#fff; border:solid 1px #ccd0d4; box-sizing:border-box; margin-bottom:15px; }
.wt_iew_history_settings_hd{ float:left; width:100%; font-weight:bold; font-size:13px; }
.wt_iew_history_settings_form_group_box{ float:left; width:100%; box-sizing:border-box; padding:10px; padding-bottom:0px; height:auto; font-size:12px; }
.wt_iew_history_settings_form_group{ float:left; width:auto; margin-right:3%; min-width:200px;}
.wt_iew_history_settings_form_group label{ font-size:12px; font-weight:bold; }
.wt_iew_history_settings_form_group select, .wt_iew_history_settings_form_group input[type="text"]{ height:20px; }
.wt_iew_history_no_records{float:left; width:100%; margin-bottom:55px; margin-top:20px; text-align:center; background:#fff; padding:15px 0px; border:solid 1px #ccd0d4;}
.wt_iew_bulk_action_box{ float:left; width:auto; margin:10px 0px; }
select.wt_iew_bulk_action{ float:left; width:auto; height:20px; margin-right:10px; }
.wt_iew_view_log_btn{ cursor:pointer; }
.wt_iew_view_log{  }
.wt_iew_log_loader{ width:100%; height:200px; text-align:center; line-height:150px; font-size:14px; font-style:italic; }
.wt_iew_log_container{ padding:25px; }
.wt_iew_raw_log{ text-align:left; font-size:14px; }
.log_view_tb th, .log_view_tb td{ text-align:center; }
.log_list_tb .log_file_name_col{ text-align:left; }
</style>
<div class="wrap">
	<h2 class="wp-heading-inline">
	<?php esc_html_e( 'Import Export Suite' ); ?>
	</h2>
<?php
$tab_now = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : null;
if ( 'logs' == $tab_now ) {
		$history_page = $this->module_id . '_log';
		$popup_hd_label = 'Log';
} else {
		$history_page = $this->module_id;
		$popup_hd_label = 'History';
}
?>

	<?php
	// Get the active tab from the $_GET param.
	$default_tab = null;
	$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : $default_tab;
	?>
	<nav class="nav-tab-wrapper">        
	  <a href="?page=wt_import_export_for_woo" class="nav-tab 
	  <?php
		if ( null === $current_tab ) :
			?>
			nav-tab-active
			<?php
	 endif;
		?>
	 ">Export</a>      
	  <a href="?page=wt_import_export_for_woo&tab=import" class="nav-tab 
	  <?php
		if ( 'import' === $current_tab ) :
			?>
			nav-tab-active
			<?php
	 endif;
		?>
	 ">Import</a>
	  <a href="?page=wt_import_export_for_woo&tab=history" class="nav-tab 
	  <?php
		if ( 'history' === $current_tab ) :
			?>
			nav-tab-active
			<?php
	 endif;
		?>
	 ">History</a>
	  <a href="?page=wt_import_export_for_woo&tab=cron" class="nav-tab 
	  <?php
		if ( 'cron' === $current_tab ) :
			?>
			nav-tab-active
			<?php
	 endif;
		?>
	 ">Scheduled actions</a>
	  <a href="?page=wt_import_export_for_woo&tab=logs" class="nav-tab 
	  <?php
		if ( 'logs' === $current_tab ) :
			?>
			nav-tab-active
			<?php
	 endif;
		?>
	 "><?php esc_html_e( 'Import logs', 'import-export-suite-for-woocommerce' ); ?></a>
	  <a href="?page=wt_import_export_for_woo&tab=settings" class="nav-tab 
	  <?php
		if ( 'settings' === $current_tab ) :
			?>
			nav-tab-active
			<?php
	 endif;
		?>
	 ">Settings</a>
	</nav>

<div class="wt_iew_view_log wt_iew_popup">
	<div class="wt_iew_popup_hd">
		<span style="line-height:40px;" class="dashicons dashicons-media-text"></span>
		<span class="wt_iew_popup_hd_label"><?php echo esc_html( $popup_hd_label ) . esc_html__( ' Details' ); ?></span>
		<div class="wt_iew_popup_close">X</div>
	</div>
	<div class="wt_iew_log_container">
		
	</div>
</div>
<?php
if ( $history_page == $this->module_id . '_log' ) {
	include plugin_dir_path( __FILE__ ) . '/log-list.php';
} else {
	include plugin_dir_path( __FILE__ ) . '/history-list.php';
}
?>

</div>
