(function ( $ ) {
	'use strict';
	$(
		function () {}
	);
})( jQuery );

var wt_iew_cron = {
	Onprg:false,
	clockTmr:null,
	clockTimestamp:0,
	Set:function () {
		this.bind_clock();

	},
	toggle_schedule_btn:function (state) {
		if (state == 1) {
			jQuery( '.iew_export_btn' ).each(
				function () {
					if (jQuery( this ).siblings( 'button.iew_export_schedule_drp_btn' ).length > 0) {
						jQuery( this ).hide();
						jQuery( this ).siblings( 'button.iew_export_schedule_drp_btn' ).show(); /* show only the button not dropdown */
					}
				}
			);
		} else {
			jQuery( '.iew_export_schedule_drp_btn' ).hide();
			jQuery( '.iew_export_btn' ).show();
		}

	},
	schedule_now:function (ajx_dta, action, id) {
		var action_key = action.replace( 'schedule_', '' );

		/* ensure only allowed actions */
		if (jQuery.inArray( action_key, wt_iew_cron_params.action_types ) != -1) {
			wt_iew_popup.showPopup( jQuery( '.wt_iew_schedule_now' ) );
			this.hide_url_cron_fields();
			this.bind_form_toggle();
			this.bind_save_schedule( ajx_dta, action, id );

			/* show file name if user entered */
			jQuery( '[name="wt_iew_cron_file_name"]' ).val( jQuery( '[name="wt_iew_file_name"]' ).val() );

			/* show file extension near to file name field */
			if (jQuery( '[name="wt_iew_file_as"]' ).length > 0) {
				jQuery( '.wt_iew_cron_file_ext' ).html( '.' + jQuery( '[name="wt_iew_file_as"]' ).val() );
			} else {
				jQuery( '.wt_iew_cron_file_ext' ).html( '.csv' );
			}

			/* hiding cron url field when toggling cron type */
			jQuery( '[name="wt_iew_schedule_type"]' ).unbind( 'click' ).click(
				function () {
					wt_iew_cron.hide_url_cron_fields();
				}
			);
		}//end if

	},
	bind_clock:function () {
		if (this.clockTimestamp == 0) {
			this.clockTimestamp = Date.parse( wt_iew_cron_params.timestamp );
		}

		this.show_current_time();
		clearInterval( wt_iew_cron.clockTmr );
		this.clockTmr = setInterval(
			function () {
				wt_iew_cron.show_current_time();
			},
			1000
		);

	},
	show_current_time:function () {
		this.clockTimestamp += 1000;
		var d = new Date( wt_iew_cron.clockTimestamp );
		jQuery( '.wt_iew_cron_current_time span' ).html( d.toLocaleTimeString( [], {hour12: true} ) );

	},
	hide_url_cron_fields:function () {
		jQuery( '.wt_iew_schedule_now_trigger_url' ).hide();
		jQuery( '[name="wt_iew_cron_url"]' ).val( '' );

	},
	bind_save_schedule:function (ajx_dta, action, id) {
		jQuery( '.wt_iew_save_schedule' ).unbind( 'click' ).click(
			function () {

				/* prevent saving on multiple button click */
				if (wt_iew_cron.Onprg) {
					return false;
				}

				var interval_vl = jQuery( '[name="wt_iew_cron_interval"]:checked' ).val();
				var date_vl     = jQuery( '[name="wt_iew_cron_interval_date"]' ).val();

							var start_time_hr = jQuery( '[name="wt_iew_cron_start_val"]' ).val();
					start_time_hr = parseInt( start_time_hr, 10 );
							var start_time_mnt = jQuery( '[name="wt_iew_cron_start_val_min"]' ).val();
					start_time_mnt = start_time_mnt.slice( -2 );
							var start_time_ampm = jQuery( '[name="wt_iew_cron_start_ampm_val"]' ).val();

				if (isNaN( start_time_hr ) || start_time_hr < 1 || start_time_hr > 12) {
					 wt_iew_notify_msg.error( wt_iew_cron_params.msgs.invalid_time_hr );
					 jQuery( '[name="wt_iew_cron_start_val"]' ).focus();
					 return false;
				}

				if (isNaN( start_time_mnt ) || start_time_mnt < 0 || start_time_mnt > 59) {
					wt_iew_notify_msg.error( wt_iew_cron_params.msgs.invalid_time_mnt );
					jQuery( '[name="wt_iew_cron_start_val_min"]' ).focus();
					return false;
				}

							var start_time = start_time_hr + '.' + start_time_mnt + ' ' + start_time_ampm;

				var custom_interval = jQuery( '[name="wt_iew_cron_interval_val"]' ).val();
				var day_vl          = jQuery( '[name="wt_iew_cron_day"]:checked' ).val();
				var schedule_type   = jQuery( '[name="wt_iew_schedule_type"]:checked' ).val();
				var file_name       = jQuery.trim( jQuery( '[name="wt_iew_cron_file_name"]' ).val() );

				if (interval_vl == 'custom') {
					custom_interval = parseInt( custom_interval );
					if (isNaN( custom_interval ) || custom_interval == 0) {
						wt_iew_notify_msg.error( wt_iew_cron_params.msgs.invalid_custom_interval );
						jQuery( '[name="wt_iew_cron_interval_val"]' ).focus();
						return false;
					} else {
						jQuery( '[name="wt_iew_cron_interval_val"]' ).val( custom_interval );
					}
				}

				/*
				if(file_name=="" && action=='schedule_import')
				{
				wt_iew_notify_msg.error(wt_iew_cron_params.msgs.specify_file_name);
				return false;
				}
				*/

				wt_iew_cron.Onprg = true;
				var btn_txt_bck   = jQuery( this ).html();
				jQuery( this ).html( wt_iew_cron_params.msgs.saving ).attr( 'data-html', btn_txt_bck );

				var action_arr = action.split( '_' );
				ajx_dta['schedule_action'] = action_arr[1]; /* what action need to do in schedule Eg: export, import */
				ajx_dta['schedule_data']   = {'schedule_type':schedule_type, 'interval':interval_vl, 'date_vl':date_vl, 'start_time':start_time, 'custom_interval':custom_interval, 'day_vl':day_vl, 'file_name':file_name};

				ajx_dta['action'] = 'iew_schedule_ajax';
				ajx_dta['iew_schedule_action'] = 'save_schedule';
				wt_iew_cron.save_schedule( ajx_dta, action, id, schedule_type );
			}
		);

	},
	bind_form_toggle:function (ajx_dta, action, id) {
		wt_iew_cron.toggle_interval_fields( jQuery( '[name="wt_iew_cron_interval"]:checked' ).val() );
		jQuery( '[name="wt_iew_cron_interval"]' ).unbind( 'click' ).click(
			function () {
				var vl = jQuery( this ).val();
				wt_iew_cron.toggle_interval_fields( vl );
			}
		);

	},
	toggle_interval_fields:function (vl) {
		jQuery( '.wt_iew_schedule_day_block, .wt_iew_schedule_custom_interval_block, .wt_iew_schedule_starttime_block, .wt_iew_schedule_date_block' ).hide();
		if (vl == 'day') {
			jQuery( '.wt_iew_schedule_starttime_block' ).show();
		} else if (vl == 'custom') {
			jQuery( '.wt_iew_schedule_custom_interval_block, .wt_iew_schedule_starttime_block' ).show();
		} else if (vl == 'month') {
			jQuery( '.wt_iew_schedule_date_block, .wt_iew_schedule_starttime_block' ).show();
		} else {
			jQuery( '.wt_iew_schedule_day_block, .wt_iew_schedule_starttime_block' ).show();
		}

	},
	save_schedule:function (ajx_dta, action, id, schedule_type) {
		this.hide_url_cron_fields();
		jQuery.ajax(
			{
				url:wt_iew_params.ajax_url,
				type:'POST',
				data:ajx_dta,
				dataType:"json",
				success:function (data) {
					  wt_iew_cron.Onprg = false;
					  jQuery( '.wt_iew_save_schedule' ).html( jQuery( '.wt_iew_save_schedule' ).attr( 'data-html' ) );
					if (data.response == true) {
						if (schedule_type == 'server_cron') {
							jQuery( '.wt_iew_schedule_now_trigger_url' ).show();
							jQuery( '[name="wt_iew_cron_url"]' ).val( data.cron_url ).select();
						} else {
							  wt_iew_popup.hidePopup();
						}

						wt_iew_notify_msg.success( data.msg );
					} else {
						wt_iew_notify_msg.error( data.msg );
					}
				},
				error:function (jqXHR,textStatus,errorThrown) {
					wt_iew_cron.Onprg = false;
					jQuery( '.wt_iew_save_schedule' ).html( jQuery( '.wt_iew_save_schedule' ).attr( 'data-html' ) );
					wt_iew_notify_msg.error( wt_iew_params.msgs.error );
				}
			}
		);

	}
}
wt_iew_cron.Set();
