<?php
/**
 * Admin display area
 *
 * @link
 *
 * @package ImportExportSuite\Admin\Partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wf_admin_view_path = WT_IEW_PLUGIN_PATH . 'admin/views/';
$wf_img_path = WT_IEW_PLUGIN_URL . 'images/';
?>
<div class="wrap" id="<?php echo esc_html( WT_IEW_PLUGIN_ID ); ?>">
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
	<div class="nav-tab-wrapper wp-clearfix wt-iew-tab-head">
		<?php
		$tab_head_arr = array(
			'wt-advanced' => __( 'General' ),
			'wt-help' => __( 'Help guide' ),
		);
		if ( isset( $_GET['debug'] ) ) {
			$tab_head_arr['wt-debug'] = 'Debug';
		}
		Wt_Import_Export_For_Woo::generate_settings_tabhead( $tab_head_arr );
		?>
	</div>
	<div class="wt-iew-tab-container">
		<?php
		// inside the settings form.
		$setting_views_a = array(
			'wt-advanced' => 'admin-settings-advanced.php',
		);

		// outside the settings form.
		$setting_views_b = array(
			'wt-help' => 'admin-settings-help.php',
		);
		if ( isset( $_GET['debug'] ) ) {
			$setting_views_b['wt-debug'] = 'admin-settings-debug.php';
		}
		?>
		<form method="post" class="wt_iew_settings_form">
			<?php
			// Set nonce:.
			if ( function_exists( 'wp_nonce_field' ) ) {
				wp_nonce_field( WT_IEW_PLUGIN_ID );
			}
			foreach ( $setting_views_a as $target_id => $value ) {
				$settings_view = $wf_admin_view_path . $value;
				if ( file_exists( $settings_view ) ) {
					include $settings_view;
				}
			}
			?>
			<?php
					/**
					 * Inside settings form.
					 *
					 * Enables adding extra arguments or setting defaults for a post
					 * collection request.
					 *
					 * @since 1.0.0
					 */
			do_action( 'wt_iew_plugin_settings_form' );
			?>
					   
		</form>
		<?php
		foreach ( $setting_views_b as $target_id => $value ) {
			$settings_view = $wf_admin_view_path . $value;
			if ( file_exists( $settings_view ) ) {
				include $settings_view;
			}
		}
		?>
		<?php
				/**
				 * After settings form.
				 *
				 * Enables adding extra arguments or setting defaults for a post
				 * collection request.
				 *
				 * @since 1.0.0
				 */
		do_action( 'wt_iew_plugin_out_settings_form' );
		?>
		 
	</div>
</div>
