<?php
/**
 * Manage eCommerce Pipeline and Deals creation.
 *
 * @link       https://makewebbetter.com/
 * @since      1.4.0
 *
 * @package    makewebbetter-hubspot-for-woocommerce
 * @subpackage makewebbetter-hubspot-for-woocommerce/admin/templates/
 */

$log_enable = Hubwoo::is_log_enable();
?>
<div id="hubwoo-logs" class="hubwoo-content-wrap hubwoo-tabcontent">
	<div class="hubwoo-logs__header">
		<div class="hubwoo-logs__heading-wrap">
			<h2 class="hubwoo-section__heading">
				<?php esc_html_e( 'Sync Log', 'makewebbetter-hubspot-for-woocommerce' ); ?>	
			</h2>
		</div>
		<?php if ( $log_enable ) : ?>
		<ul class="hubwoo-logs__settings-list">
			<li class="hubwoo-logs__settings-list-item">
				<a id="hubwoo-clear-log" href="#" class="hubwoo-logs__setting-link">
					<?php esc_html_e( 'Clear Log', 'makewebbetter-hubspot-for-woocommerce' ); ?>	
				</a>
			</li>
			<li class="hubwoo-logs__settings-list-item">
				<a id="hubwoo-download-log" class="hubwoo-logs__setting-link">
					<?php esc_html_e( 'Download', 'makewebbetter-hubspot-for-woocommerce' ); ?>	
				</a>
			</li>
		</ul>
		<?php endif; ?>
	</div>
	<?php if ( $log_enable ) : ?>
	<div class="hubwoo-table__wrapper">
		<table id="hubwoo-table" width="100%" class="hubwoo-table dt-responsive">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Expand', 'makewebbetter-hubspot-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Feed', 'makewebbetter-hubspot-for-woocommerce' ); ?></th>
					<th>
					<?php
					echo esc_html( Hubwoo::get_current_crm_name() );
					esc_html_e( ' Object', 'integration-with-quickbooks' );
					?>
					</th>
					<th><?php esc_html_e( 'Time', 'makewebbetter-hubspot-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Request', 'makewebbetter-hubspot-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Response', 'makewebbetter-hubspot-for-woocommerce' ); ?></th>
				</tr>
			</thead>
		</table>
	</div>
	<?php else : ?>
	<div class="hubwoo-content-wrap">
		<?php esc_html_e( 'Please enable the log', 'makewebbetter-hubspot-for-woocommerce' ); ?>
	</div>
	<?php endif; ?>
</div>
