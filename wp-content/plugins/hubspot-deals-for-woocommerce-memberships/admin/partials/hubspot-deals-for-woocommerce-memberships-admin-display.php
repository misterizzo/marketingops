<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://makewebbetter.com
 * @since      1.0.0
 *
 * @package    hubspot-deals-for-woocommerce-memberships
 * @subpackage hubspot-deals-for-woocommerce-memberships/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] :'mapping-stages';

?>
<div style="display: none;" class="loading-style-bg" id="hubwoo_ms_loader">
	<img src="<?php echo HUBWOO_MS_DEAL_URL . 'admin/images/loader.gif'?>">
</div>
<div class="hubwoo_ms_deals_main_wrapper">
	<div class="wrap woocommerce hub-ms-deals">
		<form action="" method="post" id="hubwoo-ms-deals">
			<h1 class="hubwoo_ms_deals_plugin_title"><?php _e('HubSpot Deals for WooCommerce Memberships', 'hubwoo'); ?></h1>
			<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
				<a class="nav-tab <?php echo $active_tab == 'mapping-stages' ? 'nav-tab-active' : ''; ?>" href="?page=hubwoo_ms_deal&tab=mapping-stages"><?php _e('Map Deal Stages', 'hubwoo');?></a>
				
				<?php if( Hubspot_Deals_For_Woocommerce_Memberships::hubwoo_ms_deals_check_mapped_stages() ): ?>
					<a class="nav-tab <?php echo $active_tab == 'pipeline-creation' ? 'nav-tab-active' : ''; ?>" href="?page=hubwoo_ms_deal&tab=pipeline-creation"><?php _e( 'Pipeline Management', 'hubwoo' );?></a>
					<?php if( Hubspot_Deals_For_Woocommerce_Memberships::is_pipeline_setup_completed() && Hubspot_Deals_For_Woocommerce_Memberships::is_field_setup_completed() ):?>
						<a class="nav-tab <?php echo $active_tab == 'old-deals-sync' ? 'nav-tab-active' : ''; ?>" href="?page=hubwoo_ms_deal&tab=old-deals-sync"><?php _e( 'One-Click Sync', 'hubwoo' );?></a>
					<?php endif; ?>
				<?php endif; ?>
			</nav>
			<?php 

				if ( $active_tab == 'mapping-stages' ) {

					include_once 'templates/stage-mapping.php';
				}
				elseif ( $active_tab == 'pipeline-creation' ) {

		            include_once 'templates/pipeline-management.php';
		        }
		        elseif ( $active_tab == 'old-deals-sync' ) {

		        	include_once 'templates/old-deals-sync.php';
		        }
			?>
		</form>
	</div>
</div>