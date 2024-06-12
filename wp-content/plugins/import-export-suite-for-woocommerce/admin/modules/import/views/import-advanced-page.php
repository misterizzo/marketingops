<?php
/**
 * Import advanced page
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wt_iew_import_main">
	<p><?php echo esc_html( $this->step_description ); ?></p>
	<form class="wt_iew_import_advanced_form">
		<table class="form-table wt-iew-form-table">
			<?php
			Wt_Import_Export_For_Woo_Common_Helper::field_generator( $advanced_screen_fields, $advanced_form_data );
			?>
		</table>
	</form>
</div>
<script type="text/javascript">
/* custom action: other than import, save, update. Eg: schedule */
function wt_iew_custom_action(ajx_dta, action, id)
{
	ajx_dta['item_type']=ajx_dta['to_import'];
	<?php
	/**
	 * Importer custom actions.
	 *
	 * Enables adding extra arguments or setting defaults for a request.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wt_iew_custom_action' );
	?>
}
</script>
