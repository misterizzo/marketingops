jQuery( function ( $ ) {
	( function ( $ ) {

		// When packing method changes, show/hide packaging options
		$( 'select#woocommerce_ups_packing_method' ).on( 'change', function () {
			if ( $( this ).val() === 'per_item' ) {
				$( '#woocommerce_ups_ups_packaging, .ups_boxes' ).parents( 'tr' ).hide();
			}
			if ( $( this ).val() === 'box_packing' ) {
				$( '#woocommerce_ups_ups_packaging, .ups_boxes' ).parents( 'tr' ).show();
			}
		} ).change();

	} )( jQuery );

	jQuery( '.ups_boxes .insert' ).click( function () {
		var dim_unit    = 'metric' === jQuery( '#woocommerce_ups_units' ).val() ? 'CM' : 'IN';
		var weight_unit = 'metric' === jQuery( '#woocommerce_ups_units' ).val() ? 'KGS' : 'LBS';

		var $tbody = jQuery( '.ups_boxes' ).find( 'tbody' );
		var size   = $tbody.find( 'tr' ).length;
		var code   = '<tr class="new">\
				<td class="check-column"><input type="checkbox" /></td>\
				<td><input type="text" size="5" name="boxes_outer_length[' + size + ']" />' + dim_unit + '</td>\
				<td><input type="text" size="5" name="boxes_outer_width[' + size + ']" />' + dim_unit + '</td>\
				<td><input type="text" size="5" name="boxes_outer_height[' + size + ']" />' + dim_unit + '</td>\
				<td><input type="text" size="5" name="boxes_inner_length[' + size + ']" />' + dim_unit + '</td>\
				<td><input type="text" size="5" name="boxes_inner_width[' + size + ']" />' + dim_unit + '</td>\
				<td><input type="text" size="5" name="boxes_inner_height[' + size + ']" />' + dim_unit + '</td>\
				<td><input type="text" size="5" name="boxes_box_weight[' + size + ']" />' + weight_unit + '</td>\
				<td><input type="text" size="5" name="boxes_max_weight[' + size + ']" />' + weight_unit + '</td>\
			</tr>';

		$tbody.append( code );

		return false;
	} );

	jQuery( '.ups_boxes .remove' ).click( function () {
		var $tbody = jQuery( '.ups_boxes' ).find( 'tbody' );

		$tbody.find( '.check-column input:checked' ).each( function () {
			jQuery( this ).closest( 'tr' ).hide().find( 'input' ).val( '' );
		} );

		return false;
	} );

	// Ordering
	jQuery( '.ups_services tbody' ).sortable( {
		items: 'tr',
		cursor: 'move',
		axis: 'y',
		handle: '.sort',
		scrollSensitivity: 40,
		forcePlaceholderSize: true,
		helper: 'clone',
		opacity: 0.65,
		placeholder: 'wc-metabox-sortable-placeholder',
		start: function ( event, ui ) {
			ui.item.css( 'baclbsround-color', '#F6F6F6' );
		},
		stop: function ( event, ui ) {
			ui.item.removeAttr( 'style' );
			ups_services_row_indexes();
		},
	} );

	function ups_services_row_indexes() {
		jQuery( '.ups_services tbody tr' ).each( function ( index, el ) {
			jQuery( 'input.order', el ).val( parseInt( jQuery( el ).index( '.ups_services tr' ) ) );
		} );
	}

	function ups_toggle_api_settings() {
		var api_type      = $( '#woocommerce_ups_api_type' ).val(),
		    api_type_attr = 'data-ups_api_type',
		    show_selector = '[' + api_type_attr + '="' + api_type + '"]',
		    rows_to_show  = $( show_selector ).closest( 'tr' ),
		    rows_to_hide  = $( '[' + api_type_attr + ']:not(' + show_selector + ')' ).closest( 'tr' );

		rows_to_show.show();
		rows_to_hide.hide();
	}

	ups_toggle_api_settings();

	$( document ).on( 'change', '#woocommerce_ups_api_type', function () {
		ups_toggle_api_settings();
	} );

} );
