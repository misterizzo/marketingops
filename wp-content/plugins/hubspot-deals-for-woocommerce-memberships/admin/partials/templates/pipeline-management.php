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
 * @subpackage hubspot-deals-for-woocommerce-memberships/admin/partials/templates
 */
?>
<?php

if( isset( $_POST ) ) {

	if( isset( $_POST[ "hubwoo-ms-deals-save"] ) ) {

		unset( $_POST["hubwoo-ms-deals-save"] );
		woocommerce_update_options( Hubspot_Deals_For_Woocommerce_Memberships_Admin::hubwoo_ms_deals_settings() );
	}
}
?>

<?php
	add_thickbox();
?>

<div id="hubspot-ms-deals-pipeline-setup-process" style="display:none;">
	<div class="popupwrap">
      <p> <?php _e( 'We are setting up for new WooCommerce Memberships pipeline. Please do not navigate or reload the page before our confirmation message.', 'hubwoo')  ?></p>
       <div class="progress">
        <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width:0%">
        </div>
      </div>
        <div class="hubspot-ms-deals-message-area">
        </div>
    </div>
</div>

<div id="hubspot-ms-deals-setup-process" style="display:none;">
	<div class="popupwrap">
      <p> <?php _e('We are setting up new deal properties for Membership Deals. Please do not navigate or reload the page before our confirmation message', 'hubwoo')  ?></p>
       <div class="progress">
        <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width:0%">
        </div>
      </div>
        <div class="hubspot-ms-deals-message-area">
        </div>
    </div>
</div>

<?php
    
    $message = __( 'Congratulations! Your memberships are ready to be converted as HubSpot Deals. ', 'hubwoo' );

    if( Hubspot_Deals_For_Woocommerce_Memberships::hubwoo_ms_check_basic_setup() ) {

		if( 'yes' == get_option( "hubwoo_ms_deals_settings_enable", "no" ) ) {

			$pipeline_id = Hubspot_Deals_For_Woocommerce_Memberships::hubwoo_ms_deals_get_pipeline_id();

			if ( !Hubspot_Deals_For_Woocommerce_Memberships::is_pipeline_setup_completed() && empty( $pipeline_id ) ) {

				$message .= '<a id="hubwoo-ms-deals-run-pipeline-setup" href="javascript:void(0)" class="button button-primary">'.__( 'Setup Pipeline', 'hubwoo' ).'</a>';
			}
			if ( Hubspot_Deals_For_Woocommerce_Memberships::is_pipeline_setup_completed() && !Hubspot_Deals_For_Woocommerce_Memberships::is_field_setup_completed() ) {
				
				$message .= '<a id="hubwoo-ms-deals-run-setup" href="javascript:void(0)" class="button button-primary">'.__( 'Run Setup', 'hubwoo' ).'</a>';
			}

			Hubspot_Deals_For_Woocommerce_Memberships::hubwoo_ms_deals_notice( $message, 'update' );
		}
	}
?>

<?php
	woocommerce_admin_fields( Hubspot_Deals_For_Woocommerce_Memberships_Admin::hubwoo_ms_deals_settings() );
?>
<p class="submit">
	<input name="hubwoo-ms-deals-save" class="button-primary woocommerce-save-button hubwoo-ms-save-button" type="submit" value="<?php _e( 'Save changes', 'hubwoo' ); ?>" />
</p>