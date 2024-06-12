<?php
/**
 * Admin settings help section
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
	<ul class="wt_iew_sub_tab">
		<li style="border-left:none; padding-left: 0px;" data-target="help-links"><a><?php esc_html_e( 'Help Links' ); ?></a></li>
		<li data-target="help-doc"><a><?php esc_html_e( 'Sample CSV' ); ?></a></li>
	</ul>
	<div class="wt_iew_sub_tab_container">		
		<div class="wt_iew_sub_tab_content" data-id="help-links" style="display:block;">
			<ul class="wf-help-links">
				<li>
					<img src="<?php echo esc_url( WT_IEW_PLUGIN_URL ); ?>assets/images/documentation.png">
					<h3><?php esc_html_e( 'Documentation' ); ?></h3>
					<p><?php esc_html_e( 'Refer to our documentation to set up and get started.' ); ?></p>
					<a target="_blank" href="https://woocommerce.com/document/import-export-suite-for-woocommerce/" class="button button-primary">
						<?php esc_html_e( 'Documentation' ); ?>        
					</a>
				</li>
				<li>
					<img src="<?php echo esc_url( WT_IEW_PLUGIN_URL ); ?>assets/images/support.png">
					<h3><?php esc_html_e( 'Help and Support' ); ?></h3>
					<p><?php esc_html_e( 'We would love to help you on any queries or issues.' ); ?></p>
					<a target="_blank" href="https://woocommerce.com/vendor/webtoffee/" class="button button-primary">
						<?php esc_html_e( 'Contact Us' ); ?>
					</a>
				</li>               
			</ul>
		</div>
		<div class="wt_iew_sub_tab_content" data-id="help-doc">
			<ul class="wf-help-links">
				<?php
				/**
				 * Addon help content.
				 *
				 * @since 1.0.0
				 */
				do_action( 'wt_user_addon_help_content' );
				?>
				<?php
				/**
				 * Addon help content.
				 *
				 * @since 1.0.0
				 */
				do_action( 'wt_order_addon_help_content' );
				?>
				<?php
				/**
				 * Addon help content.
				 *
				 * @since 1.0.0
				 */
				do_action( 'wt_product_addon_help_content' );
				?>
				<?php
				/**
				 * Addon help content.
				 *
				 * @since 1.0.0
				 */
				do_action( 'wt_coupon_addon_help_content' );
				?>
				<?php
				/**
				 * Addon help content.
				 *
				 * @since 1.0.0
				 */
				do_action( 'wt_subscription_addon_help_content' );
				?>
				<?php
				/**
				 * Addon help content.
				 *
				 * @since 1.0.0
				 */
				do_action( 'wt_review_addon_help_content' );
				?>
			</ul>
		</div>
	</div>
</div>
