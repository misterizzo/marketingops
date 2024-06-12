<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-deals-for-woocommerce-memberships
 * @subpackage hubspot-deals-for-woocommerce-memberships/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;
}

if ( !empty( $_POST["hubwoo-old-ms-sync"] ) ) {

	unset( $_POST["hubwoo-old-ms-sync"] );
	if ( empty( $_POST["hubwoo-ms-sync-enable"] ) ) {
		$_POST["hubwoo-memberships-sync-enable"] = "off";
	}
	update_option( "hubwoo-ms-sync-enable", $_POST["hubwoo-ms-sync-enable"] );
	update_option( "hubwoo-ms-since-date", $_POST["hubwoo-ms-since-date"] );
	update_option( "hubwoo-ms-upto-date", $_POST["hubwoo-ms-upto-date"] );
	update_option( "hubwoo-ms-sync-status", $_POST["hubwoo-ms-sync-status"] );
}

$message = __( 'Congratulations! Your old Memberships are ready to be converted as new HubSpot Deals. ', 'hubwoo' );

if ( 'on' == get_option( "hubwoo-ms-sync-enable", "off" ) ) {

	$memberships_count = Hubspot_Deals_For_Woocommerce_Memberships_Admin::hubwoo_ms_count_for_deal();
	$message .= '<a id="hubwoo-run-ms-sync" href="javascript:void(0)" class="button button-primary">'.__( 'Sync Now', 'hubwoo' ).'</a>';
	$message .= '<span class="hubwoo_ms_oauth_span"><label>' . __( "Total Memberships Count: ", "hubwoo" ) . $memberships_count . '</label></span>';
	Hubspot_Deals_For_Woocommerce_Memberships::hubwoo_ms_deals_notice( $message, 'update' );
}

?>
<h2><?php _e("Export your old Memberships as Deals to HubSpot","hubwoo")?></h2>
<div id="hubspot-ms-deals-setup-process" style="display:none;">
	<div class="popupwrap">
      <p> <?php _e('We are exporting your old memberships as HubSpot Deals. Please do not navigate or reload the page before our confirmation message.', 'hubwoo')  ?></p>
        <div class="hubspot-ms-deals-message-area">
        </div>
    </div>
</div>
<div class="hubwoo-ms-old-deals">
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="hubwoo-ms-sync-enable"><?php _e("Enable/Disable","hubwoo"); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
						$deal_sync_enable = get_option( "hubwoo-ms-sync-enable", 'off' ); 
						$desc = __('Enable this feature to export old memberships as HubSpot Deals.','hubwoo');
						echo wc_help_tip( $desc );
					?>
					<input type="checkbox" name="hubwoo-ms-sync-enable" <?php echo ( $deal_sync_enable == 'on' ) ? "checked='checked'" : ""?> >
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="hubwoo-ms-since-date"><?php _e("Memberships from date","hubwoo"); ?></label>
				</th>
				<td class="forminp forminp-text">
				<?php 
				
					$since_date = get_option( "hubwoo-ms-since-date", '' );

					if( empty( $since_date ) )
					{
						$since_date = date("d-m-Y");
					}
					$desc = __( 'From which date you want to sync memberships, select that. If left blank, current date will be used.','hubwoo');
					echo wc_help_tip( $desc );
				?>
					<input type="text" class="date-picker" name="hubwoo-ms-since-date" id="hubwoo-ms-since-date" placeholder="<?php _e("Select start date","hubwoo")?>" value="<?php echo $since_date ?>"/>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="hubwoo-ms-upto-date"><?php _e("Memberships upto date","hubwoo"); ?></label>
				</th>
				<td class="forminp forminp-text">
				<?php 
				
					$upto_date = get_option( "hubwoo-ms-upto-date", '' );

					if( empty( $upto_date ) ) {

						$upto_date = date("d-m-Y");
					}
					$desc = __('Upto which date you want to sync memberships, select that. If left blank, current date will be used.','hubwoo');
					echo wc_help_tip( $desc );
				?>
					<input type="text" class="date-picker" name="hubwoo-ms-upto-date" id="hubwoo-ms-upto-date" placeholder="<?php _e("Select end date","hubwoo")?>" value="<?php echo $upto_date ?>"/>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="hubwoo-ms-sync-status"><?php _e("Status","hubwoo"); ?></label>
				</th>
				<td class="forminp forminp-text">
				<?php
					$desc = __('Select the status for memberships which as to be sync as Deal','hubwoo');
					echo wc_help_tip( $desc );
				?>
					<select id="hubwoo-ms-sync-status" name="hubwoo-ms-sync-status">
						<?php

							$selected_status = get_option( "hubwoo-ms-sync-status", 'wcm-active' );
							$statuses = wc_memberships_get_user_membership_statuses();
							foreach ( $statuses as $key => $single_status ) {
								if( $selected_status == $key ) {
									?>
									<option selected value="<?php echo $key ?>"><?php echo $single_status["label"] ?></option>
									<?php
								}
								else {
									?>
									<option value="<?php echo $key ?>"><?php echo $single_status["label"] ?></option>
									<?php
								}
							}
						?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<p class="submit">
	<input name="hubwoo-old-ms-sync" class="button-primary woocommerce-save-button hubwoo-ms-save-button" type="submit" value="<?php _e( 'Save changes', 'hubwoo' ); ?>" />
</p>