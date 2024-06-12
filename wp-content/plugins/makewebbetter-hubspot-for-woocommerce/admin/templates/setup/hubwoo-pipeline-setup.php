<?php
/**
 * Manage eCommerce Pipeline and Deal stages creation.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    makewebbetter-hubspot-for-woocommerce
 * @subpackage makewebbetter-hubspot-for-woocommerce/admin/templates/setup/
 */

global $hubwoo;
$deal_stages       = Hubwoo::get_all_deal_stages();
$fetch_pipeline    = get_option( 'hubwoo_potal_pipelines', true );
$selected_pipeline = get_option( 'hubwoo_ecomm_pipeline_id', true );
$deal_stage_id     = 'stageId';

if ( 'yes' == get_option( 'hubwoo_ecomm_pipeline_created', 'no' ) ) {
	$deal_stage_id = 'id';
}
?>

<div class="hubwoo-box">
	<div class="hubwoo-form-wizard-content hubwoo-deal-wrap-con" data-tab-content="map-deal-stage">
		<div class="mwb-heb-wlcm__title">			
			<h2 class="pipeline-setup-heading">Map Deal Stages with eCommerce pipeline </h2>
		</div>
		<div class="hubwoo-general-settings hubwoo-group-wrap__map_deal_stage hubwoo-settings-container hubwoo-deal-wrap-con__store">
			<div>
				<table class="hubwoo-pipeline-stages-conf-table form-table">
					<tr>
						<th class="hubwoo-pipeline-wrap-con__thead"><?php esc_html_e( 'Select Pipeline', 'makewebbetter-hubspot-for-woocommerce' ); ?></th>
						<td>
							<select class="hubwoo_selected_pipeline" name="hubwoo_selected_pipeline">
								<?php
								if ( ! empty( $fetch_pipeline ) ) {
									foreach ( $fetch_pipeline as $single_pipeline ) {

										if ( $single_pipeline['id'] === $selected_pipeline ) {
											?>
												<option value="<?php echo esc_attr( $single_pipeline['id'] ); ?>" selected=""><?php echo esc_html( $single_pipeline['label'] ); ?></option>
												<?php
										} else {
											?>
												<option value="<?php echo esc_attr( $single_pipeline['id'] ); ?>"><?php echo esc_html( $single_pipeline['label'] ); ?></option>
												<?php
										}
									}
								}
								?>
							</select>
							<a class="hubwoo_update_pipelines"><i class="fa fa-refresh" style="font-size:24px;"></i></a>
						</td>
					</tr>
				</table>
			</div>
			<form action="#" method="post" class="hubwoo_save_ecomm_mapping">
				<table class="hubwoo-deals-stages-conf-table form-table">
					<thead>
						<tr>
							<th class="hubwoo-deal-wrap-con__thead"><?php esc_html_e( 'WooCommerce Order Status', 'makewebbetter-hubspot-for-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Deal Stage', 'makewebbetter-hubspot-for-woocommerce' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php $all_order_statuses = wc_get_order_statuses(); ?>
						<?php
						foreach ( $all_order_statuses as $order_key => $order_label ) {
							$stage = Hubwoo::get_selected_deal_stage( $order_key );
							?>
								<tr>
									<th class="hubwoo-deal-wrap-con__thead">
										<?php echo esc_html( $order_label ); ?>
										<input type="hidden" name="hubwoo_woo_order_statuses[]" value="<?php echo esc_html( $order_key ); ?>">
									</th>
									<td>
										<select class="hubwoo_ecomm_mapping" name="hubwoo_deal_stages[]">
											<?php
											if ( ! empty( $deal_stages ) ) {
												foreach ( $deal_stages as $single_deal_stage ) {

													if ( $single_deal_stage[ $deal_stage_id ] === $stage ) {
														?>
															<option value="<?php echo esc_attr( $single_deal_stage[ $deal_stage_id ] ); ?>" selected=""><?php echo esc_html( $single_deal_stage['label'] ); ?></option>
															<?php
													} else {
														?>
															<option value="<?php echo esc_attr( $single_deal_stage[ $deal_stage_id ] ); ?>"><?php echo esc_html( $single_deal_stage['label'] ); ?></option>
															<?php
													}
												}
											}
											?>
										</select>
									</td>
								</tr>
							<?php
						}
						?>
					</tbody>
					<tfoot>
						<tr>
							<td>
								<a href="javascript:void(0);" id = "hubwoo-save-pipeline" class="hubwoo-save-pipeline hubwoo-btn--primary"><?php esc_html_e( 'Save', 'makewebbetter-hubspot-for-woocommerce' ); ?></a>	
							</td>
							<td>
								<button id="reset-deal-stages" class="hubwoo__btn hubwoo-btn--primary hubwoo-btn--dashboard"><?php esc_html_e( ' Reset to Default Mapping', 'makewebbetter-hubspot-for-woocommerce' ); ?>
								</button>
							</td>
						</tr>
					</tfoot>
				</table>
			</form>				
		</div>									
	</div>
</div>
