jQuery( document ).ready( function ( e ) {

	jQuery( '#episode-selection.hidden select' ).attr( 'disabled', 'disabled' );

	jQuery( '.podcast-stats #content-filter-select').change( function() {
		var value = jQuery( this ).val();

		if( 'episode' == value ) {
			jQuery( '#episode-selection' ).removeClass( 'hidden' ).find( 'select' ).removeAttr( 'disabled' );
		} else {
			jQuery( '#episode-selection' ).addClass( 'hidden' ).find( 'select' ).attr( 'disabled', 'disabled' );
		}
	});

	jQuery( '.podcast-stats #stats-content-filter select').change( function() {
		jQuery( '.podcast-stats #content-filter-button' ).removeClass( 'hidden' );
	});

	jQuery( document ).on( 'click', '#podcast-privacy-notice .notice-dismiss', function( e ) {
		jQuery.ajax( ajaxurl,
			{
				type: 'POST',
				data: {
					action: 'podcast_dismissed_notice_handler',
					type: 'podcast-privacy',
				}
			}
		);
	});

	jQuery( '#clear-stats-button' ).on( 'click', function( e ) {
		if ( ! confirm( jQuery( this ).data( 'alert-text' ) ) ) {
			return false;
		}
	});
});