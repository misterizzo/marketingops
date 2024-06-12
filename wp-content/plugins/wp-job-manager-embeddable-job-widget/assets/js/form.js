/* global embeddable_job_widget_form_args */

jQuery( document ).ready( function ( $ ) {
	const wpjm_ejw_select2_args = {
		minimumResultsForSearch: 10,
		width: '100%',
	};
	if ( 1 === parseInt( embeddable_job_widget_form_args.is_rtl, 10 ) ) {
		wpjm_ejw_select2_args.dir = 'rtl';
	}

	if ( $.isFunction( $.fn.select2 ) ) {
		$( '.job-manager-enhanced-select:visible' ).select2(
			wpjm_ejw_select2_args
		);
	} else if ( $.isFunction( $.fn.chosen ) ) {
		$( '.job-manager-enhanced-select:visible' ).chosen();
	}

	$( '#widget-get-code' ).click( function () {
		const keywords = $( '#widget_keyword' ).val();
		const location = $( '#widget_location' ).val();
		const per_page = $( '#widget_per_page' ).val();
		const pagination = $( '#widget_pagination' ).is( ':checked' ) ? 1 : 0;
		let categories = $( '#widget_categories' ).val();
		let job_types = $( '#widget_job_type' ).val();
		const featured = $( '#widget_featured' ).val();

		if ( categories ) {
			categories = categories.join();
		} else {
			categories = '';
		}

		if ( job_types ) {
			job_types = job_types.join();
		} else {
			job_types = '';
		}

		const embed_code = `<script type='text/javascript'>
var embeddable_job_widget_options = {
'script_url' : '${ embeddable_job_widget_form_args.script_url }',
'keywords'   : ${ stringLiteral( keywords ) },
'location'   : ${ stringLiteral( location ) },
'categories' : '${ categories }',
'job_types'  : '${ job_types }',
'featured'   : '${ featured }',
'per_page'   : '${ parseInt( per_page ) }',
'pagination' : '${ parseInt( pagination ) }'
};

</script>
${ embeddable_job_widget_form_args.css }
${ embeddable_job_widget_form_args.code }`;

		$( '#widget-code' ).val( embed_code ).focus().select();
		$( '#widget-code-preview iframe' ).remove();
		const iframe = document.createElement( 'iframe' );
		const html = `<!doctype html><html><head></head><body style="margin:0; padding: 0;">${ embed_code }</body></html>`;
		$( '#widget-code-preview' ).append( iframe );
		iframe.contentWindow.document.open();
		iframe.contentWindow.document.write( html );
		iframe.contentWindow.document.close();
		$( '#widget-code-wrapper' ).slideDown();
	} );

	function stringLiteral( str ) {
		return JSON.stringify( str.replace( /<\//g, "<'+'/" ) );
	}
} );
