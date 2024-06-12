<?php
/**
 * Admin settings save button section
 *
 * @link
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$settings_button_title = isset( $settings_button_title ) ? $settings_button_title : __( 'Update Settings' );
$before_button_text = isset( $before_button_text ) ? $before_button_text : '';
$after_button_text = isset( $after_button_text ) ? $after_button_text : '';
?>
<div style="clear: both;"></div>
<div class="wt-iew-plugin-toolbar bottom">
	<div class="left">
	</div>
	<div class="right">
		<?php
		if ( 1 == $is_popup_page ) {
			echo '<span style="line-height:40px;"><input type="checkbox" id="wt_iew_add_and_use_ftp" name="wt_iew_add_and_use_ftp" value="1"> <label for="wt_iew_add_and_use_ftp">' . esc_html__( 'Use this profile and close the popup' ) . '</label></span>';}
		?>
		<input type="submit" name="wt_iew_update_admin_settings_form" value="<?php echo esc_html( $settings_button_title ); ?>" class="button button-primary" style="float:right;"/>
		<?php if ( '' != $after_button_text ) : ?>
			<?php echo '<input type="button" name="wt_iew_abort_test_ftp" value="' . esc_html__( 'Abort FTP testing' ) . '" class="button button-secondary wt_iew_abort_test_ftp" style="float:right; margin-right:10px; display:none;" />'; ?>
			<?php echo '<input type="button" name="wt_iew_test_ftp_form" value="' . esc_html__( 'Test FTP' ) . '" class="button button-secondary wt_iew_test_ftp_form" style="float:right; margin-right:10px;" />'; ?>
		<?php	endif; ?>
		<span class="spinner" style="margin-top:11px"></span>
	</div>
</div>
