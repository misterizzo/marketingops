/**
 * jQuery admin custom script file.
 */
jQuery( document ).ready( function( $ ) {
	'use strict';

	// Localized variables.
	var ajaxurl           = PTS_Admin_JS_Obj.ajaxurl;
	var push_data_confirm = PTS_Admin_JS_Obj.push_data_confirm;
	var start_pushing     = PTS_Admin_JS_Obj.start_pushing;

	/**
	 * Do reload.
	 */
	$( document ).on( 'click', '.do-reload', function() {
		location.reload();
	} );

	/**
	 * Push to syncari.
	 */
	$( document ).on( 'click', '#push_users_to_syncari', function() {
		var confirm_push = confirm( push_data_confirm );

		// Return, if request declined.
		if ( false === confirm_push ) {
			return false;
		}

		var page     = 1;
		var per_page = 37;

		$( '#push_users_to_syncari' ).text( start_pushing );
		push_to_syncari( page, per_page );
	} );

	/**
	 * Push the data to syncari.
	 *
	 * @param {*} page 
	 * @param {*} per_page 
	 */
	function push_to_syncari( page, per_page ) {
		// Block the element.
		block_element( $( '#push_users_to_syncari' ) );

		// Send AJAX.
		$.ajax( {
			dataType: 'JSON',
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'push_users_to_syncari',
				page: page,
				per_page: per_page
			},
			success: function ( response ) {
				// If the sync is complete.
				if ( 'users-sync-completed' === response.data.code ) {
					unblock_element( $( '#push_users_to_syncari' ) ); // Unblock the element.

					// Change the button text.
					$( '#push_users_to_syncari' ).text( response.data.progress_note_text );
					$( '#push_users_to_syncari' ).addClass( 'do-reload' ).attr( 'id', 'push_users_to_syncari_old' );
				} else if ( 'users-sync-in-process' === response.data.code ) {
					// Change the progress note text.
					$( '#push_users_to_syncari' ).text( response.data.progress_note_text );

					/**
					 * Call self to import next set of products.
					 * This wait of 500ms is just to allow the script to change the button text.
					 */
					setTimeout( function() {
						page++;
						push_to_syncari( page, per_page );
					}, 500 );
				}
			},
		} );
	}

	/**
	 * Block element.
	 *
	 * @param {string} element
	 */
	function block_element( element ) {
		element.addClass( 'non-clickable' );
	}

	/**
	 * Unblock element.
	 *
	 * @param {string} element
	 */
	function unblock_element( element ) {
		element.removeClass( 'non-clickable' );
	}

	/**
	 * Check if a number is valid.
	 * 
	 * @param {number} data 
	 */
	function is_valid_number( data ) {
		if ( '' === data || undefined === data || isNaN( data ) || 0 === data ) {
			return -1;
		} else {
			return 1;
		}
	}

	/**
	 * Check if a string is valid.
	 *
	 * @param {string} $data
	 */
	function is_valid_string( data ) {
		if ( '' === data || undefined === data || ! isNaN( data ) || 0 === data ) {
			return -1;
		} else {
			return 1;
		}
	}
} );