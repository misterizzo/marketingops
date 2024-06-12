<?php
/**
 * Main view file of import section
 *
 * @link
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
/**
 * Before setting from.
 *
 * Enables adding extra arguments or setting defaults for a request.
 *
 * @since 1.0.0
 */
do_action( 'wt_iew_importer_before_head' );
?>
<style type="text/css">
.wt_iew_import_step{ display:none; }
.wt_iew_import_step_loader{ width:100%; height:400px; text-align:center; line-height:400px; font-size:14px; }
.wt_iew_import_step_main{ float:left; box-sizing:border-box; padding:15px; padding-bottom:0px; width:95%; margin:30px 0.5%; background:#fff; box-shadow:0px 2px 2px #ccc; border:solid 1px #efefef; }
.wt_iew_import_main{ padding:20px 0px; }
</style>

<div class="wrap">
	<h2 class="wp-heading-inline">
	<?php esc_html_e( 'Import Export Suite' ); ?>
	</h2>

<div class="wt_iew_view_log wt_iew_popup" style="text-align:left">
	<div class="wt_iew_popup_hd">
		<span style="line-height:40px;" class="dashicons dashicons-media-text"></span>
		<span class="wt_iew_popup_hd_label"><?php esc_html_e( 'History Details' ); ?></span>
		<div class="wt_iew_popup_close">X</div>
	</div>
	<div class="wt_iew_log_container" style="padding:25px;">
		
	</div>
</div>
<?php
Wt_Iew_IE_Helper::debug_panel( $this->module_base );
?>
<?php require WT_IEW_PLUGIN_PATH . '/admin/views/save-template-popup.php'; ?>


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

<?php
if ( $requested_rerun_id > 0 && 0 == $this->rerun_id ) {
	?>
		<div class="wt_iew_warn wt_iew_rerun_warn">
	<?php esc_html_e( 'Unable to handle Re-Run request.' ); ?>
		</div>
	<?php
}
?>

<div class="wt_iew_loader_info_box"></div>
<div class="wt_iew_overlayed_loader"></div>

<div class="wt_iew_import_step_main">
	<?php
	foreach ( $this->steps as $stepk => $stepv ) {
		?>
		<div class="wt_iew_import_step wt_iew_import_step_<?php echo esc_html( $stepk ); ?>" data-loaded="0"></div>
		<?php
	}
	?>
</div>
<script type="text/javascript">
/* external modules can hook */
function wt_iew_importer_validate(action, action_type, is_previous_step)
{
	var is_continue=true;
	<?php
	/**
	 * Importer form validate.
	 *
	 * Enables adding extra arguments or setting defaults for a request.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wt_iew_importer_validate' );
	?>
	return is_continue;
}
function wt_iew_importer_reset_form_data()
{
	<?php
	/**
	 * Importer reset from data.
	 *
	 * Enables adding extra arguments or setting defaults for a request.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wt_iew_importer_reset_form_data' );
	?>
}
</script>
</div>
