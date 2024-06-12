<?php
/**
 * Admin advanced settings section
 *
 * @link
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wt-iew-tab-content" data-id="<?php echo esc_html( $target_id ); ?>">
	<?php
	$fields = Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings_fields();

	$advanced_settings = Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings();
	?>
	<table class="form-table wt-iew-form-table">
		<?php
		Wt_Import_Export_For_Woo_Common_Helper::field_generator( $fields, $advanced_settings );
		?>
	</table>
		<?php
		include 'admin-settings-pre-saved-templates.php';
		?>
	<?php
	$is_popup_page = 0;
	include 'admin-settings-save-button.php';
	?>
</div>
