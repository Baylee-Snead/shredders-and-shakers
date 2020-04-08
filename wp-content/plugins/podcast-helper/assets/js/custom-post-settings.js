jQuery( document ).ready( function( e ) {
	var frame;
	jQuery( '.podcast-settings-image-wrapper #upload_image' ).on( 'click', function( ev ){
		ev.preventDefault();
		if ( frame ) {
			frame.open();
			return;
		}

		frame = wp.media({
			title: jQuery( '.podcast-settings-image-wrapper' ).data( 'media-popup-title' ),
			library : { type : 'image' },
			multiple: false
		});

		frame.on( 'select', function() {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			jQuery( '.podcast-settings-image-wrapper #podcast_cover' ).val( attachment.url );
		});

		frame.open();
	});
});