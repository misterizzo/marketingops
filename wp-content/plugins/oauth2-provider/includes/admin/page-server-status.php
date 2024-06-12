<?php
/**
 * Server Status
 */
function wo_server_status_page() {
	wp_enqueue_style( 'wo_admin' );
	wp_enqueue_script( 'wo_admin' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	?>
	<div class="wrap">
		<h2><?php _e( 'Server Status', 'wp-oauth' ); ?></h2>
		<div class="section group">
			<div class="col span_6_of_6">
				<?php wo_display_settings_tabs(); ?>
			</div>
		</div>

	</div>
	<?php
}
