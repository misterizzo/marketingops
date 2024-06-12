/**
 * Cron main actions 
 */
var wt_iew_cron_js = ( function ( $ ) {
	// 'use strict';
	var wt_iew_cron_js = {
		Set: function () {
			this.reg_delete();
			this.reg_gen_url();
				this.bind_update_schedule();
			// this.subTab($('.wt_iew_cron_settings_page'));
		},
		reg_gen_url: function () {
			jQuery( '.wt_iew_cron_url' ).click(
				function () {
					  window.prompt( wt_iew_cron_params.msgs.use_url, jQuery( this ).attr( 'data-href' ) );
				}
			);
		},
		reg_delete: function () {
			jQuery( '.wt_iew_delete_cron' ).click(
				function () {
					if (confirm( wt_iew_cron_params.msgs.sure )) {
						window.location.href = jQuery( this ).attr( 'data-href' );
					}
				}
			);
		},
		bind_update_schedule:function () {
			jQuery( '.wt_iew_update_schedule' ).unbind( 'click' ).click(
				function () {

					/* prevent saving on multiple button click */
					/*
					if(wt_iew_cron.Onprg){
					return false;
					}
					*/

					var interval_vl = jQuery( '[name="wt_iew_cron_interval"]:checked' ).val();
					var date_vl     = jQuery( '[name="wt_iew_cron_interval_date"]' ).val();

							var start_time_hr   = jQuery( '[name="wt_iew_cron_start_val"]' ).val();
						start_time_hr           = parseInt( start_time_hr, 10 );
							var start_time_mnt  = jQuery( '[name="wt_iew_cron_start_val_min"]' ).val();
						start_time_mnt          = start_time_mnt.slice( -2 );
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

									   this.ajx_dta = {
											'_wpnonce': wt_iew_params.nonces.main,
											'action': "iew_schedule_ajax",
											'cron_id': jQuery( '[name="requested_cron_edit_id"]' ).val(),
											'iew_schedule_action': 'update_schedule',
					};
							var action_type         = jQuery( '[name="requested_cron_action_type"]' ).val();
					if (action_type == 'import') {
						wt_iew_import.prepare_form_data();
						this.ajx_dta['form_data'] = wt_iew_import.form_data;
					} else {
						wt_iew_export.prepare_form_data();
						this.ajx_dta['form_data'] = wt_iew_export.form_data;
					}

					this.ajx_dta['schedule_data'] = {'schedule_type':schedule_type, 'interval':interval_vl, 'date_vl':date_vl, 'start_time':start_time, 'custom_interval':custom_interval, 'day_vl':day_vl, 'file_name':file_name};
					this.ajx_dta['action']        = 'iew_schedule_ajax';
					this.ajx_dta['iew_schedule_action'] = 'update_schedule';
					wt_iew_cron_js.update_schedule( this.ajx_dta, schedule_type );
				}
			);
		},
		edit_schedule: function ( ajax_data ) {

			wt_iew_cron.hide_url_cron_fields();

			jQuery.ajax(
				{
					url: wt_iew_params.ajax_url,
					type: 'POST',
					data: ajax_data,
					dataType: "json",
					success: function ( response ) {

						if (response.data.id) {
							  var start_time = response.data.cron_data.start_time;

							  var start_time_ampm   = start_time.slice( -2 );
							  var start_time_hour   = start_time.split( '.' )[0]
							  var minute_with_ampm  = start_time.split( '.' )[1];
							  var start_time_minute = minute_with_ampm.split( ' ' )[0];

							jQuery( '#wt_iew_schedule_' + response.data.schedule_type ).prop( 'checked', true );
							jQuery( '#wt_iew_cron_interval_' + response.data.cron_data.interval ).prop( 'checked', true );

							jQuery( '[name="wt_iew_cron_interval_val"]' ).val( response.data.cron_data.custom_interval );

							jQuery( '[name="wt_iew_cron_interval_date"]' ).val( response.data.cron_data.date_vl );

							if (response.data.cron_data.day_vl) {
								jQuery( '[name="wt_iew_cron_day"]' ).prop( 'checked', false );
							}

							if (response.data.cron_data.day_vl) {
								jQuery( '#wt_iew_cron_day_' + response.data.cron_data.day_vl ).prop( 'checked', true );
							}

							if (start_time_hour) {
									jQuery( '[name="wt_iew_cron_start_val"]' ).val( start_time_hour );
							}

							if (start_time_minute) {
								jQuery( '[name="wt_iew_cron_start_val_min"]' ).val( start_time_minute );
							}

							if (start_time_ampm) {
								jQuery( '[name="wt_iew_cron_start_ampm_val"]' ).val( start_time_ampm );
							}

							jQuery( '#cron-advanced-details' ).html( response.advanced_form_edit_html );
							jQuery( '.wt_iew_ftp_profiles' ).hide();
							wt_iew_cron.toggle_interval_fields( jQuery( '[name="wt_iew_cron_interval"]:checked' ).val() );
							jQuery( '[name="wt_iew_cron_interval"]' ).unbind( 'click' ).click(
								function () {
										var vl = jQuery( this ).val();
										wt_iew_cron.toggle_interval_fields( vl );
								}
							);

							jQuery( '[name="wt_iew_schedule_type"]' ).unbind( 'click' ).click(
								function () {
									wt_iew_cron.hide_url_cron_fields();
								}
							);

							  wt_iew_cron_js.RegAdvanced();
							  wt_iew_form_toggler.Set();
							if (jQuery( '#wt_iew_file_into_ftp' ).is( ':checked' )) {
								jQuery( '[wt_iew_frm_tgl-id="wt_iew_file_into"]' ).show();
								jQuery( '[wt_iew_frm_tgl-id="wt_iew_file_into"]' ).find( 'th label' ).animate( {'margin-left':'25px'},1000 );
							}

							  wt_iew_popup.showPopup( jQuery( '.wt_iew_schedule_now' ) );
							  this.ajax_data = {
									'_wpnonce': wt_iew_params.nonces.main,
									'action': "iew_schedule_ajax",
									'cron_id': response.data.id,
									'iew_schedule_action': 'update_schedule',
							};
							  // wt_iew_cron.Onprg = false;
							  wt_iew_cron_js.bind_update_schedule( ajax_data );
						}//end if
					},
					error: function ( jqXHR, textStatus, errorThrown ) {
						wt_iew_cron.Onprg = false;
						jQuery( '.wt_iew_update_schedule' ).html( jQuery( '.wt_iew_update_schedule' ).attr( 'data-html' ) );
						wt_iew_notify_msg.error( wt_iew_params.msgs.error );
					}
				}
			);
		},
		update_schedule: function ( ajax_data, schedule_type ) {

			wt_iew_cron.hide_url_cron_fields();

			jQuery.ajax(
				{
					url: wt_iew_params.ajax_url,
					type: 'POST',
					data: ajax_data,
					dataType: "json",
					success: function ( data ) {
						wt_iew_cron.Onprg = false;
						jQuery( '.wt_iew_update_schedule' ).html( jQuery( '.wt_iew_update_schedule' ).attr( 'data-html' ) );
						if (data.response == true) {
							if (schedule_type == 'server_cron') {
								jQuery( '.wt_iew_schedule_now_trigger_url' ).show();
								jQuery( '[name="wt_iew_cron_url"]' ).val( data.cron_url ).select();
								wt_iew_cron_js.subTab( $( '.wt_iew_cron_settings_page' ) );
							} else {
								wt_iew_popup.hidePopup();
							}

							wt_iew_notify_msg.success( data.msg );

							/*
							wt_iew_cron_js.loadList();
							*/
							/*
							setTimeout( function () {
							location.reload();
							}, 4000 );
							 *
							 */
						} else {
								  wt_iew_notify_msg.error( data.msg );
						}//end if
					},
					error: function ( jqXHR, textStatus, errorThrown ) {
						wt_iew_cron.Onprg = false;
						jQuery( '.wt_iew_update_schedule' ).html( jQuery( '.wt_iew_update_schedule' ).attr( 'data-html' ) );
						wt_iew_notify_msg.error( wt_iew_params.msgs.error );
					}
				}
			);
		},
		loadList: function () {
			$( '.cron_list_wrapper' ).html( '<div class="wt_iew_ftp_loader">' + wt_iew_params.msgs.loading + '</div>' );
			$.ajax(
				{
					url: wt_iew_params.ajax_url,
					data: { 'action': 'iew_schedule_ajax', _wpnonce: wt_iew_params.nonces.main, 'iew_schedule_action': 'list_cron' },
					type: 'post',
					dataType: "html",
					success: function ( data ) {
						$( '.cron_list_wrapper' ).html( data );
						wt_iew_cron_js.Set();
					},
					error: function () {
						wt_iew_notify_msg.error( wt_iew_params.msgs.error );
						$( '.cron_list_wrapper' ).html( '<div class="wt_iew_ftp_loader">' + wt_iew_params.msgs.error + '</div>' );
					}
				}
			);
		},
		subTab:function (cron_settings_el) {
			cron_settings_el.find( '.wt_iew_sub_tab li' ).click(
				function () {
					var trgt = $( this ).attr( 'data-target' );
					var prnt = $( this ).parent( '.wt_iew_sub_tab' );
					var ctnr = prnt.siblings( '.wt_iew_sub_tab_container' );
					prnt.find( 'li a' ).css( {'color':'#0073aa','cursor':'pointer'} );
					$( this ).find( 'a' ).css( {'color':'#000','cursor':'default'} );
					ctnr.find( '.wt_iew_sub_tab_content' ).hide();
					ctnr.find( '.wt_iew_sub_tab_content[data-id="' + trgt + '"]' ).show();
				}
			);
			cron_settings_el.find( '.wt_iew_sub_tab' ).each(
				function () {
					var elm = $( this ).children( 'li' ).eq( 0 );
					elm.click();
				}
			);
		},
		RegAdvanced:function () {
			jQuery( '.wt_iew_field_group_hd .wt_iew_field_group_toggle_btn' ).each(
				function () {
					var group_id         = jQuery( this ).attr( 'data-id' );
					var group_content_dv = jQuery( this ).parents( 'tr' ).find( '.wt_iew_field_group_content' );
					var visibility       = jQuery( this ).attr( 'data-visibility' );
					jQuery( '.wt_iew_field_group_children[data-field-group="' + group_id + '"]' ).appendTo( group_content_dv.find( 'table' ) );
					if (visibility == 1) {
						 group_content_dv.show();
					}
				}
			);
			jQuery( '.wt_iew_field_group_hd' ).unbind( 'click' ).click(
				function () {
					var toggle_btn       = jQuery( this ).find( '.wt_iew_field_group_toggle_btn' );
					var visibility       = toggle_btn.attr( 'data-visibility' );
					var group_content_dv = toggle_btn.parents( 'tr' ).find( '.wt_iew_field_group_content' );
					if (visibility == 1) {
						toggle_btn.attr( 'data-visibility',0 );
						toggle_btn.find( '.dashicons' ).removeClass( 'dashicons-arrow-down' ).addClass( 'dashicons-arrow-right' );
						group_content_dv.hide();
					} else {
						toggle_btn.attr( 'data-visibility',1 );
						toggle_btn.find( '.dashicons' ).removeClass( 'dashicons-arrow-right' ).addClass( 'dashicons-arrow-down' );
						group_content_dv.show();
					}
				}
			);
		}
	}
	return wt_iew_cron_js;
} )( jQuery );

jQuery(
	function () {
		wt_iew_cron_js.Set();
		wt_iew_cron_js.RegAdvanced();
	}
);
