<?php
/**
 * Import footer page
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wt-iew-plugin-toolbar bottom">
	<div style="float:left; padding-top:10px;" class="wt_iew_import_template_name"> </div>
	<div style="float:right;">
		<div style="float:right;">
			<?php
			$button_types    = array_column( array_values( $this->step_btns ), 'type' );
			$last_button_key = array_search( 'button', array_reverse( $button_types, true ) );
			$count           = 0;
			$button_standard_class = 'media-button';
			foreach ( $this->step_btns as $btnk => $btnv ) {
				$css_class   = ( isset( $btnv['class'] ) ? $btnv['class'] : '' );
				$action_type = ( isset( $btnv['action_type'] ) ? $btnv['action_type'] : 'non-step' );
				if ( $count == $last_button_key ) {
					   $button_standard_class = 'button-primary';
				}

				if ( 'button' == $btnv['type'] ) {
					?>
					<button class="button <?php echo esc_html( $button_standard_class ); ?> wt_iew_import_action_btn <?php echo esc_html( $css_class ); ?>" data-action-type="<?php echo esc_html( $action_type ); ?>" data-action="<?php echo esc_html( $btnv['key'] ); ?>" type="submit">
					<?php echo wp_kses_post( $btnv['text'] ); ?>            
					</button>
					<?php
				} else if ( 'dropdown_button' == $btnv['type'] ) {
					$btn_arr = ( isset( $btnv['items'] ) && is_array( $btnv['items'] ) ? $btnv['items'] : array() );
					?>
					<button type="button" class="button button-primary wt_iew_drp_menu <?php echo esc_html( $css_class ); ?>" data-target="wt_iew_<?php echo esc_html( $btnk ); ?>_drp">
					<?php echo wp_kses_post( $btnv['text'] ); ?> <span class="dashicons dashicons-arrow-down" style="line-height: 28px;"></span>
					</button>
					<ul class="wt_iew_dropdown <?php echo esc_html( $css_class ); ?>" data-id="wt_iew_<?php echo esc_html( $btnk ); ?>_drp">
					<?php
					foreach ( $btn_arr as $btnkk => $btnvv ) {
							  $field_attr  = ( isset( $btnvv['field_attr'] ) ? $btnvv['field_attr'] : '' );
							  $action_type = ( isset( $btnvv['action_type'] ) ? $btnvv['action_type'] : 'non-step' );
						?>
							<li class="wt_iew_import_action_btn" data-action-type="<?php echo esc_html( $action_type ); ?>"  data-action="<?php echo esc_html( $btnvv['key'] ); ?>" <?php echo esc_html( $field_attr ); ?> ><?php echo wp_kses_post( $btnvv['text'] ); ?></li>
							  <?php
					}
					?>
					</ul>
					<?php
				} else if ( 'hidden_button' == $btnv['type'] ) {
					?>
					<button style="display:none;" class="button button-primary wt_iew_import_action_btn <?php echo esc_html( $css_class ); ?>" data-action-type="<?php echo esc_html( $action_type ); ?>" data-action="<?php echo esc_html( $btnv['key'] ); ?>" type="submit">
					<?php echo wp_kses_post( $btnv['text'] ); ?>            
					</button>
					<?php
				} else if ( 'text' == $btnv['type'] ) {
					?>
					<span style="line-height:40px; font-weight:bold;" class="<?php echo esc_html( $css_class ); ?>"><?php echo wp_kses_post( $btnv['text'] ); ?></span>
					<?php
				}//end if

				$count++;
			}//end foreach
			?>
		</div>
	</div>
	<span class="spinner" style="margin-top:11px;"></span>
</div>
