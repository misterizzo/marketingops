(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	var ajaxUrl 							= hubwooi18n.ajaxUrl;
	var hubwooWentWrong 					= hubwooi18n.hubwooWentWrong;
	var hubwooDealsPipelineSetupCompleted 	= hubwooi18n.hubwooDealsPipelineSetupCompleted;
	var hubwooSecurity 						= hubwooi18n.hubwooSecurity;
	var hubwooCreatingPipeline 				= hubwooi18n.hubwooCreatingPipeline;
	var hubwooCreatingGroup 				= hubwooi18n.hubwooCreatingGroup;
	var hubwooCreatingProperty 				= hubwooi18n.hubwooCreatingProperty;
	var hubwooSetupCompleted 				= hubwooi18n.hubwooSetupCompleted;
	var hubwooLicenseUpgrade 				= hubwooi18n.hubwooLicenseUpgrade;
	var hubwooDealsSyncComplete 			= hubwooi18n.hubwooDealsSyncComplete;
	var hubwooDealSynced 					= hubwooi18n.hubwooDealSynced;
	var hubwooNoObjectFound 				= hubwooi18n.hubwooNoObjectFound;
	var hubwooPipelineUpdated 				= hubwooi18n.hubwooPipelineUpdated;
	var hubwooPipelineUpdateFailed 			= hubwooi18n.hubwooPipelineUpdateFailed;

	jQuery(document).ready(function(){

	 	jQuery('.date-picker').datepicker( { dateFormat: "dd-mm-yy", maxDate: 0, changeMonth : true, changeYear : true } );
	 	jQuery('#hubwoo-ms-deals-run-pipeline-setup').on( 'click', function(){
			jQuery('#hubwoo_ms_loader').show();
			jQuery.post( ajaxUrl, { 'action' : 'hubwoo_ms_deals_check_oauth_access_token', 'hubwooSecurity' : hubwooSecurity }, function( response ){
				var oauth_response = jQuery.parseJSON( response );
				var oauth_status = oauth_response.status;
				if( oauth_status ) {
					jQuery('#hubwoo_ms_loader').hide();
					jQuery('#hubspot-ms-deals-pipeline-setup-process').show();
					jQuery.post( ajaxUrl, {'action' : 'hubwoo_ms_deals_get_pipeline', 'hubwooSecurity' : hubwooSecurity }, function( response ){
						if( response != null ) {
							var pipeline = jQuery.parseJSON(response);
							var pipeline_count = pipeline.length;
							var pipeline_progress = parseFloat(100/pipeline_count);
							var current_progress = 0;
							jQuery.each( pipeline, function( index, pipeline_details ) {
								var pipelineData = { 'action':'hubwoo_ms_deals_create_pipeline', 'pipelineDetails': pipeline_details };
								jQuery.ajax({ url: ajaxUrl, type: 'POST', data : pipelineData, async: false }).done( function( pipelineResponse ){
									var response = jQuery.parseJSON( pipelineResponse );
									var errors = response.errors;
									var hubwooMessage = "";
									if( !errors ) {
										var responseCode = response.status_code;
										var hubwooResponse = response.response;
										if ( responseCode == 200 ) {
											hubwooMessage += "<div class='notice updated'><p> "+ hubwooCreatingPipeline + "</p></div>";
											alert(hubwooDealsPipelineSetupCompleted);
											location.reload();
										}
										else {
											hubwooMessage += "<div class='notice error'><p> "+ hubwooResponse +"</p></div>";
											alert( hubwooWentWrong );
											location.reload();
										}
									}
									jQuery('.progress-bar').css( 'width', pipeline_progress + '%' );
									jQuery(".hubspot-ms-deals-message-area").append( hubwooMessage );
								});
							});
						}
					});
				}
			});
		});
		jQuery('#hubwoo-ms-deals-run-setup').on( 'click', function(){
			jQuery('#hubwoo_ms_loader').show();
			jQuery.post( ajaxUrl, { 'action' : 'hubwoo_ms_deals_check_oauth_access_token', 'hubwooSecurity' : hubwooSecurity }, function( response ) {
				var oauth_response = jQuery.parseJSON( response );
				var oauth_status = oauth_response.status;
				if( oauth_status ) {
					jQuery('#hubwoo_ms_loader').hide();
					jQuery('#hubspot-ms-deals-setup-process').show();
					jQuery.post( ajaxUrl, { 'action' : 'hubwoo_ms_deals_get_groups', 'hubwooSecurity' : hubwooSecurity }, function( response ) {
						if( response != null ) {
							// get all groups
							var groups = jQuery.parseJSON(response);
							var group_count = groups.length;
							var group_progress = parseFloat(100/group_count);
							var current_progress = 0;
							var allProperties_progress = 0;

							// loop all groups
							jQuery.each(groups, function( index, group_details ) {
								// group name
								var displayName = group_details.displayName;
								// post data to create groups.
								var groupData = {
									'action' : 'hubwoo_ms_deals_create_group',
									'createNow': 'group',
									'groupDetails': group_details,
									'hubwooSecurity' : hubwooSecurity
								};
								jQuery.ajax({ url: ajaxUrl, type: 'POST', data : groupData, async: false }).done(function( groupResponse ) {
									var response = jQuery.parseJSON( groupResponse );
									var errors = response.errors;
									var hubwooMessage = "";
									if( !errors ) {
										var responseCode = response.status_code;
										if( responseCode == 200 ) {
											hubwooMessage = "<div class='notice updated'><p> "+ hubwooCreatingGroup + " <strong>" + displayName +"</strong></p></div>";
										}
										else {
											var hubwooResponse = response.response;
											if( hubwooResponse != null && hubwooResponse != "" ) {
												hubwooResponse = jQuery.parseJSON( hubwooResponse );
												hubwooMessage = "<div class='notice error'><p> "+ hubwooResponse.message +"</p></div>";
											}
											else{
												hubwooMessage = "<div class='notice error'><p> "+ responseCode +"</p></div>";
											}
										}
									}
									else{
										hubwooMessage = "<div class='notice error'><p> "+ errors +"</p></div>";
									}
									
									jQuery(".hubspot-ms-deals-message-area").append( hubwooMessage );
									
									//let's create the group property.
									var getProperties = { action : 'hubwoo_ms_deals_get_group_properties', groupName: group_details.name, 'hubwooSecurity' : hubwooSecurity };
									
									jQuery.ajax({ url: ajaxUrl, type: 'POST', data : getProperties, async: false }).done(function( propResponse ){

										if( propResponse != null ){
											// parse all properties.
											var allProperties = jQuery.parseJSON( propResponse );
											var allProperties_count = allProperties.length;
											
											allProperties_progress = parseFloat(group_progress/allProperties_count);
											
											jQuery.each( allProperties, function( i, propertyDetails ) {
												current_progress+= allProperties_progress;
												jQuery('.progress-bar').css('width',current_progress+'%');
												var createProperties = { action : 'hubwoo_ms_deals_create_property', groupName: group_details.name, propertyDetails: propertyDetails, 'hubwooSecurity' : hubwooSecurity };
												jQuery.ajax({ url: ajaxUrl, type: 'POST', data : createProperties, async: false }).done(function( propertyResponse ){

													var proresponse = jQuery.parseJSON( propertyResponse );
													var proerrors = proresponse.errors;
													var prohubwooMessage = "";

													if( !proerrors ){

														var proresponseCode = proresponse.status_code;
														if( proresponseCode == 200 ){

															prohubwooMessage = "<div class='notice updated'><p> "+ hubwooCreatingProperty + " <strong>" + propertyDetails.name +"</strong></p></div>";
														}
														else{

															var prohubwooResponse = proresponse.response;
															if( prohubwooResponse != null && prohubwooResponse != "" ){

																prohubwooResponse = jQuery.parseJSON( prohubwooResponse );

																prohubwooMessage = "<div class='notice error'><p> "+ prohubwooResponse.message +"</p></div>";
															}
															else{

																prohubwooMessage = "<div class='notice error'><p> "+ proresponseCode +"</p></div>";
															}
														}
													}
													else{

														prohubwooMessage = "<div class='notice error'><p> "+ proerrors +"</p></div>";
													}
													
													jQuery(".hubspot-ms-deals-message-area").append( prohubwooMessage );
												});
											});
										}
									});	
								});
							});
						}
						else {
							// close the popup and show the error.
							alert( hubwooWentWrong );

							return false;
						}

						// mark the process as completed.
						jQuery.post(ajaxUrl, { 'action': 'hubwoo_ms_deals_setup_completed', 'hubwooSecurity' : hubwooSecurity}, function( response ){

							alert( hubwooSetupCompleted );

							location.reload();
						});
					});
				}
				else
				{
					// close the popup and show the error.
					alert( hubwooWentWrong );
					jQuery('#hubwoo_ms_loader').hide();
					return false;
				}
			});
		});
		jQuery('#hubwoo-run-ms-sync').on( 'click', function(){
			jQuery('#hubwoo_ms_loader').show();
			jQuery.post( ajaxUrl, { 'action' : 'hubwoo_ms_deals_check_oauth_access_token', 'hubwooSecurity' : hubwooSecurity }, function( response ) {
				var oauth_response = jQuery.parseJSON( response );
				var oauth_status = oauth_response.status;
				if( oauth_status ) {
					jQuery('#hubwoo_ms_loader').hide();
					jQuery('#hubspot-ms-deals-setup-process').show();
					jQuery.post( ajaxUrl, { 'action' : 'hubwoo_ms_get_count', 'hubwooSecurity' : hubwooSecurity }, function( count ) {

						if( count > 0 ) {

							var total_memerships = count;
							var offset = 0;
							var hubwooMessage = "";
							
							while( offset < total_memerships ) {
								
								jQuery.ajax({url:ajaxUrl,type:'POST',async: false, data:{'action' : 'hubwoo_ms_old_deals_sync', 'hubwooSecurity' : hubwooSecurity } }).done(function(message){
									message = jQuery.parseJSON(message);

									if( message != null ) {

										if( message.status_code == 200 ) {

											hubwooMessage = "<div class='notice updated'><p> "+ hubwooDealSynced + " </p></div>";
										}
										else {

											hubwooMessage = "<div class='notice error'><p> "+ message.response + " </p></div>";
										}
										jQuery(".hubspot-ms-deals-message-area").append( hubwooMessage );
									}
								});
								offset += 1;
							}
							alert( hubwooDealsSyncComplete );
							location.reload();
						}
						else {
							
							alert( hubwooNoObjectFound );
							location.reload();
						}
					});
				}
			});
		});
		jQuery('#hubwoo_ms_deals_license_key').on("click",function(e){
			jQuery('.hubwoo_ms_deals_license_activation_status').html("");
		});
		jQuery('form#hubwoo-ms-deals-license').on("submit",function(e){
			e.preventDefault();	
			var license_key =  jQuery('#hubwoo_ms_deals_license_key').val();
			jQuery("#hubwoo-ms-deals-lic-loader").removeClass('hubwoo_ms_deals_hide');
			jQuery("#hubwoo-ms-deals-lic-loader").addClass('hubwoo_ms_deals_show');
			hubwoo_ms_deals_send_license_request(license_key);		
		});
		function hubwoo_ms_deals_send_license_request( license_key ){

			$.ajax({
	        type:'POST',
	        dataType:'JSON',
	        url :ajaxUrl,
	        data:{ action:'hubwoo_ms_deals_validate_license_key', purchase_code:license_key },
		        success:function(data)
		        {
		        	if( data.status == true )
		        	{
		        		jQuery('.hubwoo_ms_deals_license_activation_status').html(data.msg);
		        		$("#hubwoo-ms-deals-lic-loader").removeClass('hubwoo_ms_deals_show');
						$("#hubwoo-ms-deals-lic-loader").addClass('hubwoo_ms_deals_hide');
						location.reload();
		        	}
		        	else
		        	{
		        		jQuery('.hubwoo_ms_deals_license_activation_status').html(data.msg);
		        		$("#hubwoo-ms-deals-lic-loader").removeClass('hubwoo_ms_deals_show');
						$("#hubwoo-ms-deals-lic-loader").addClass('hubwoo_ms_deals_hide');
		        		jQuery('#hubwoo_ms_deals_license_key').val("");
		        	}
		        }
			});
		}
	});

})( jQuery );
