(function( $ ) {
	'use strict';

	jQuery(document).ready(function(){

		jQuery('.hubwoo_delete_row').on("click",function(e){
    		e.preventDefault();
	    	var btn_id = $(this).data("id");
			jQuery("#hubwoo_ftf_table tbody tr[data-id='" + btn_id + "']").remove();
		});

		jQuery("#hubwoo_new_row").on('click',function(){
			jQuery('#hubwoo_ftf_loader').show();
			var count = jQuery("#hubwoo_ftf_table tbody tr:last").data('id');
			++count;
			jQuery.ajax({ 
				url: ajaxurl, 
				type: 'POST', 
				data:{action:'hubwoo_ftf_new_row',count:count},
				success:function(data)
			    {
			    	jQuery('#hubwoo_ftf_loader').hide();
			    	jQuery("#hubwoo_ftf_table tbody").append(data);
			    	jQuery('.hubwoo_remove_row').on("click",function(e){
			    		e.preventDefault();
				    	var btn_id = $(this).data("id");
						jQuery("#hubwoo_ftf_table tbody tr[data-id='" + btn_id + "']").remove();
		    		});
			    }
			});
		});

		jQuery('#hubwoo_ftf_license_key').on("click",function(e){
			jQuery('.hubwoo_ftf_license_activation_status').html(""); 
		});

		jQuery('form#hubwoo-ftf-license').on("submit",function(e){
			e.preventDefault();	
			var license_key =  jQuery('#hubwoo_ftf_license_key').val();
			jQuery("#hubwoo-ftf-lic-loader").removeClass('hubwoo_ftf_hide');
			jQuery("#hubwoo-ftf-lic-loader").addClass('hubwoo_ftf_show');
			hubwoo_ftf_send_license_request(license_key);		
		});

		function hubwoo_ftf_send_license_request(license_key)
		{
			$.ajax({
		        type:'POST',
		        dataType:'JSON',
		        url :ajaxurl,
		        data:{action:'hubwoo_ftf_validate_license_key',purchase_code:license_key},
		        success:function(data)
		        {
		        	if( data.status == true )
		        	{
		        		jQuery('.hubwoo_ftf_license_activation_status').html(data.msg);
		        		$("#hubwoo-ftf-lic-loader").removeClass('hubwoo_deals_show');
						$("#hubwoo-ftf-lic-loader").addClass('hubwoo_deals_hide');
						location.reload();
		        	}
		        	else
		        	{
		        		jQuery('.hubwoo_ftf_license_activation_status').html(data.msg);
		        		$("#hubwoo-ftf-lic-loader").removeClass('hubwoo_ftf_show');
						$("#hubwoo-ftf-lic-loader").addClass('hubwoo_ftf_hide');
		        		jQuery('#hubwoo_ftf_license_key').val("");
		        	}
		        }
			});
		}
	});

})( jQuery );
