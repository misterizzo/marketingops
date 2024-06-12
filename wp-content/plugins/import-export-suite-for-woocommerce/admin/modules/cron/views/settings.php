<?php
/**
 * Schedule settings
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style type="text/css">
.wt_iew_cron_settings_page{ padding:15px; }
.cron_list_tb td, .cron_list_tb th{ text-align:center; vertical-align:middle; }
.wt_iew_delete_cron{ cursor:pointer; }

.wt_iew_cron_current_time{float:right; width:auto; font-size:12px; font-weight:normal;}
.wt_iew_cron_current_time span{ display:inline-block; width:85px; }
.cron_list_tb td a{ cursor:pointer; }
</style>
<div class="wrap">
	<h2 class="wp-heading-inline">
	<?php esc_html_e( 'Import Export Suite' ); ?>
	</h2>
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


<div class="wt_iew_cron_settings_page">
	<h2 class="wp-heading-inline"><?php esc_html_e( 'Scheduled actions', 'import-export-suite-for-woocommerce' ); ?> <div class="wt_iew_cron_current_time"><b><?php esc_html_e( 'Current server time:' ); ?></b> <span>--:--:-- --</span></div></h2>
	<p>
		<?php esc_html_e( 'Lists all the scheduled processes for import and export.', 'import-export-suite-for-woocommerce' ); ?><br />
		<?php esc_html_e( 'Disable or delete unwanted scheduled actions to reduce server load and reduce the chances for failure of actively scheduled actions.', 'import-export-suite-for-woocommerce' ); ?>
	</p>
	<?php
	Wt_Import_Export_For_Woo_Cron::list_cron();
	?>
</div>
	
</div>
