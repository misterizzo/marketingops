<?php
/**
 * Schedule now
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style type="text/css">
.wt_iew_schedule_now{ width:600px; text-align:left; }
.wt_iew_schedule_now_box{ width:100%; padding:15px; box-sizing:border-box; }
.wt_iew_schedule_now_formrow{float:left; width:100%; margin-bottom:15px; padding-left:5px; box-sizing:border-box;}    
.wt_iew_schedule_now_interval_radio_block{float:left; width:100%; margin:0px; padding:0px; margin-top:2px; }        
.wt_iew_schedule_now_box label{ width:100%; float:left; text-align:left; font-weight:bold; }
.wt_iew_schedule_now_interval_radio_block label{width:auto; float:left; margin-right:10px; margin-bottom:5px; text-align:left; font-weight:normal; }    
.wt_iew_schedule_now_box select, .wt_iew_schedule_now_box input[type="text"]{ width:auto; text-align:left; }    
.wt_iew_schedule_now .wt_iew_popup_footer{ margin-top:10px; float:left; margin-bottom:20px; }
.wt_iew_schedule_type_desc{ margin-top:0px; padding-left:5px; margin-bottom:0px; }
.wt_iew_schedule_type_box_single{ float:left; margin-top:5px; margin-bottom:10px;}
.wt_iew_schedule_type_box_single label{ color:#666; }
.wt_iew_schedule_now_trigger_url, .wt_iew_schedule_day_block, .wt_iew_schedule_custom_interval_block, .wt_iew_schedule_starttime_block{ display:none; }
.wt_iew_schedule_now_interval_sub_block{ float:left; width:100%; margin-top:3px; }
.wt_iew_cron_current_time{float:right; width:auto;}
.wt_iew_cron_current_time span{ display:inline-block; width:85px; }
</style>
<div class="wt_iew_schedule_now wt_iew_popup">
	<div class="wt_iew_popup_hd">
		<span style="line-height:40px;" class="dashicons dashicons-clock"></span>
		<span class="wt_iew_popup_hd_label"><?php esc_html_e( 'Schedule now' ); ?></span>
		<div class="wt_iew_popup_close">X</div>
	</div>
	<div class="wt_iew_schedule_now_box">
		<div class="wt_iew_cron_current_time"><b><?php esc_html_e( 'Current server time:' ); ?></b> <span>--:--:-- --</span></div>

		<label><?php esc_html_e( 'Schedule type' ); ?></label>
		<div class="wt_iew_schedule_now_formrow">
			<div class="wt_iew_schedule_type_box_single" style="margin-bottom:0px;">
				<label for="wt_iew_schedule_wordpress_cron"><input type="radio" name="wt_iew_schedule_type" id="wt_iew_schedule_wordpress_cron" value="wordpress_cron" checked="checked"> <?php esc_html_e( 'Wordpress Cron' ); ?> </label>
				<p class="wt_iew_schedule_type_desc"><?php esc_html_e( 'Schedules the task at the specified time and the triggering depends on the website visits.' ); ?></p>
			</div>
			<div class="wt_iew_schedule_type_box_single">
				<label for="wt_iew_schedule_server_cron"><input type="radio" name="wt_iew_schedule_type" id="wt_iew_schedule_server_cron" value="server_cron"> <?php esc_html_e( 'Server Cron' ); ?> </label>
				<p class="wt_iew_schedule_type_desc">
					<?php esc_html_e( 'You can use this option if you have a separate system to trigger the scheduled events.' ); ?>                    
				</p>
			</div>
		</div>

		<?php
		if ( 'export' == $this->to_cron ) {
			?>
		<label><?php esc_html_e( 'File name' ); ?></label>
		<div class="wt_iew_schedule_now_formrow">
			<input type="text" name="wt_iew_cron_file_name" value="" /> <span class="wt_iew_cron_file_ext">.csv</span>
			<br />    
			<?php esc_html_e( 'If left blank the system generates a default name.' ); ?>    
		</div>
			<?php
		}
		?>

		<label><?php esc_html_e( 'Interval' ); ?></label>
		<div class="wt_iew_schedule_now_formrow">            
			<div class="wt_iew_schedule_now_interval_radio_block">
				<label for="wt_iew_cron_interval_day"><input type="radio" id="wt_iew_cron_interval_day" name="wt_iew_cron_interval" value="day" checked="checked"> <?php esc_html_e( 'Every day' ); ?></label>
				<label for="wt_iew_cron_interval_week"><input type="radio" id="wt_iew_cron_interval_week" name="wt_iew_cron_interval" value="week"> <?php esc_html_e( 'Every week' ); ?></label>
				<!-- <label for="wt_iew_cron_interval_biweek"><input type="radio" id="wt_iew_cron_interval_biweek" name="wt_iew_cron_interval" value="biweek"> <?php esc_html_e( 'Biweekly' ); ?></label> -->
				<label for="wt_iew_cron_interval_month"><input type="radio" id="wt_iew_cron_interval_month" name="wt_iew_cron_interval" value="month"> <?php esc_html_e( 'Every month' ); ?></label>
				<label for="wt_iew_cron_interval_custom"><input type="radio" id="wt_iew_cron_interval_custom" name="wt_iew_cron_interval" value="custom"> <?php esc_html_e( 'Custom' ); ?></label>
			</div>
			<div class="wt_iew_schedule_now_interval_sub_block wt_iew_schedule_custom_interval_block">
				<label><?php esc_html_e( 'Custom interval' ); ?></label>
				<input type="number" step="1" min="1" name="wt_iew_cron_interval_val" value="" placeholder="<?php esc_html_e( 'Interval in minutes.' ); ?>">
				<span class="wt-iew_form_help" style="margin-top:3px;"><?php esc_html_e( 'Recommended: Minimum 2 hour(120 minutes)' ); ?></span>
			</div>
			<div class="wt_iew_schedule_now_interval_sub_block wt_iew_schedule_day_block">
				<label><?php esc_html_e( 'Which day?' ); ?></label>
				<div class="wt_iew_schedule_now_interval_radio_block">                
					<?php
					$days = array(
						'Sun',
						'Mon',
						'Tue',
						'Wed',
						'Thu',
						'Fri',
						'Sat',
					);
					foreach ( $days as $day ) {
						$day_vl  = strtolower( $day );
						$checked = ( 'sun' == $day_vl ) ? ' checked="checked"' : '';
						?>
						<label for="wt_iew_cron_day_<?php echo esc_attr( $day_vl ); ?>"><input type="radio" value="<?php echo esc_attr( $day_vl ); ?>" id="wt_iew_cron_day_<?php echo esc_attr( $day_vl ); ?>" name="wt_iew_cron_day" <?php echo esc_attr( $checked ); ?>> <?php echo esc_attr( $day ); ?></label>
						<?php
					}
					?>
				</div>
			</div>
			<div class="wt_iew_schedule_now_interval_sub_block wt_iew_schedule_date_block">
				<label><?php esc_html_e( 'Day of the Month?' ); ?></label>
				<select name="wt_iew_cron_interval_date">
					<?php
					for ( $i = 1; $i <= 28; $i++ ) {
						?>
						<option value="<?php echo absint( $i ); ?>"><?php echo absint( $i ); ?></option>
						<?php
					}
					?>
					<option value="last_day"><?php esc_html_e( 'Last day' ); ?></option>
				</select>
			</div>
			<div class="wt_iew_schedule_now_interval_sub_block wt_iew_schedule_starttime_block">
				<label><?php esc_html_e( 'Start time' ); ?></label> 
								<div style="float:left">
									<input  type="number" step="1" min="1" max="12" name="wt_iew_cron_start_val" value="" />
									<span class="wt-iew_form_help" style="display:block; margin-top: 1px">Hour</span>
								</div>
								<div style="float:left">
									<span class="wt_iew_cron_start_val_min">:</span><input type="number" step="1" min="0" max="59" name="wt_iew_cron_start_val_min" value="" onchange="if(parseInt(this.value,10)<10)this.value='0'+this.value;" />
									<span class="wt-iew_form_help" style="display:block;  margin-top: 1px">Minute</span>
								</div>
								<div style="float:left">
									<select name="wt_iew_cron_start_ampm_val">
									<?php
									$am_pm = array(
										'AM',
										'PM',
									);
									foreach ( $am_pm as $apvl ) {
										?>
											<option><?php echo esc_attr( $apvl ); ?></option>
											<?php
									}
									?>
									</select>
								</div>
			</div>
		</div>

		<div class="wt_iew_schedule_now_trigger_url">
			<label><?php esc_html_e( 'Trigger URL' ); ?></label>
			<div class="wt_iew_schedule_now_formrow" style="margin-bottom:0px;">
				<input type="text" name="wt_iew_cron_url" value="" />
				<p style="color:red; margin:0px;"><?php esc_html_e( 'Use the generated URL to run cron.' ); ?></p>
				<!-- <p>Eg: */2 * * * * wget -O /dev/null url >/dev/null 2>&1  </p> -->
			</div>
		</div>

		<div class="wt_iew_popup_footer">
			<button type="button" name="" class="button-secondary wt_iew_popup_cancel">
				<?php esc_html_e( 'Cancel' ); ?> 
			</button>
			<button type="button" name="" class="button-primary wt_iew_save_schedule"><?php esc_html_e( 'Schedule now' ); ?></button>    
		</div>
	</div>
</div>
