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

	if ( isset( $_POST[ "hubwoo-ms-deals-save-stages" ] ) ) {

		unset( $_POST["hubwoo-ms-deals-save-stages"] );

		$stages = array();
		
		if ( isset( $_POST[ "hubwoo_ms_deal_stageID" ] ) && isset( $_POST[ "hubwoo_ms_deal_label" ] ) && isset( $_POST[ "hubwoo_ms_deal_prob" ] ) ) {

			foreach ( $_POST[ "hubwoo_ms_deal_stageID" ] as $key => $deals_stage_ID ) {

				$deal_stage = array( "label" => isset( $_POST[ "hubwoo_ms_deal_label" ][ $key ] ) ? $_POST[ "hubwoo_ms_deal_label" ][ $key ] : "", "displayOrder" => $key, "probability" => isset( $_POST[ "hubwoo_ms_deal_prob" ][ $key ] ) ? $_POST[ "hubwoo_ms_deal_prob" ][ $key ][0] : "", "stageId" => $deals_stage_ID );

				$stages[] = $deal_stage;
			}
		
			update_option( "hubwoo_ms_deal_mapped_stages", $stages );
			exit( wp_redirect( admin_url( 'admin.php?page=hubwoo_ms_deal&tab=mapping-stages' ) ) );
		}
	}
?>

<div id="hubspot-ms-deal-stages-configuration">
	<div class="stages-popupwrap">
     	<p> <?php _e('Please configure and save your Membership statuses for HubSpot deal stages with their winning probability', 'hubwoo' )  ?></p>
	    <div class="hubspot-ms-deals-message-area">
	    	<form action="" method="post">
	    		<table class="hubwoo-ms-deals-stages-conf-table">
	    			<tr>
				   		<th><?php _e( "Status", "hubwoo" ) ?></th>
				    	<th><?php _e( "Deal Label", "hubwoo" ) ?></th>
				    	<th><?php _e( "Deal StageID", "hubwoo" ) ?></th>
				    	<th><?php _e( "Winning Probability", "hubwoo") ?></th>
				  	</tr>
				  	<?php $all_statuses = wc_memberships_get_user_membership_statuses(); ?>
				  	<?php
				  		foreach ( $all_statuses as $key => $status_label ) {

				  			$deal_label = Hubspot_Deals_For_Woocommerce_Memberships::get_the_deal_label( $key );

				  			if ( empty( $deal_label ) ) {

				  				$deal_label = $status_label["label"];
				  			}
				  			?>
				  				<tr>
					  				<td>
					  					<?php echo $status_label["label"] ?>
					  				</td> 
					  				<td>
					  					<input type="text" value="<?php echo $deal_label ?>" name="hubwoo_ms_deal_label[]"/>
					  				</td>
					  				<td>
					  					<?php echo $key ?>
					  				</td>
					  				<td>
					  					<?php print_r ( Hubspot_Deals_For_Woocommerce_Memberships::get_the_deal_probability( $key ) ); ?>
					  				</td>
					  				<input type="hidden" name="hubwoo_ms_deal_stageID[]" value="<?php echo $key ?>">
				  				</tr>
				  			<?php
				  		}
				  	?>
				</table>
				<?php if ( !Hubspot_Deals_For_Woocommerce_Memberships::is_pipeline_setup_completed() ): ?>
					<p class="submit" style="text-align: center;">
						<a style="margin-right: 20px;" id="hubwoo-ms-clear-stages" href="javascript:void(0)" class="button button-primary"><?php _e( 'Reset', 'hubwoo' ) ?></a>
						<input name="hubwoo-ms-deals-save-stages" class="button-primary woocommerce-save-button hubwoo-ms-save-button" type="submit" value="<?php _e( 'Save changes', 'hubwoo' ); ?>" />
					</p>
				<?php endif; ?>
	    	</form>
	    </div>
    </div>
</div>