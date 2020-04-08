/* Determine if screen can handle touch events. */
if ( ! ( ( 'ontouchstart' in window ) || ( navigator.maxTouchPoints > 0 ) || ( navigator.msMaxTouchPoints > 0 ) ) ) {
	jQuery( 'html' ).addClass( 'no-touch' );
}

jQuery( function( $ ) { 'use strict';

	/* Handle collapsable inputs */
	$( '#theme-additional-fields .podcast-metabox-field-checkbox .collapse-container' ).on( 'change', function( e ) {
		var $container = jQuery( jQuery( this ).data( "container" ) );
		if ( $container.length > 0 ) {
			$container.toggle();
		}
	});

	/* Handle uploading episode media files */
	var media_frame_audio_video;
	$( document ).on( 'click', '#podcast-episode-fields .podcast-metabox-field-episode .set-media', function( e ) {
		e.preventDefault();

		var $uploadButton = $( this ), $input_parent = $uploadButton.closest( '.podcast-metabox-input' );
		if ( ! media_frame_audio_video ) {
			media_frame_audio_video = wp.media( {
				title    : $uploadButton.data( 'frame-title' ),
				library  : { type: [ 'audio', 'video' ] },
				multiple : false
			} );

			media_frame_audio_video.on( 'select', function() {
				var attachment = media_frame_audio_video.state().get( 'selection' ).first().toJSON();
				$input_parent.find( 'input.media_url' ).val( attachment.url );
				$input_parent.find( 'input.media_size' ).val( attachment.filesizeInBytes );
				$input_parent.find( 'input.media_duration' ).val( attachment.fileLength );
				if ( 'video' == attachment.type ) {
					$input_parent.parent( '.podcast-metabox-field' ).next( '.podcast-metabox-field' ).show();
				}
				if ( 'audio' == attachment.type ) {
					$input_parent.parent( '.podcast-metabox-field' ).next( '.podcast-metabox-field' ).hide();
				}
			} );
		}

		media_frame_audio_video.open();
	} );

	/* Handle uploading transcript files */
	var media_frame_transcript;
	$( document ).on( 'click', '#podcast-episode-fields .podcast-metabox-field-episode .set-transcript', function( e ) {
		e.preventDefault();

		var $uploadButton = $( this ), $input_parent = $uploadButton.closest( '.podcast-metabox-input' );
		if ( ! media_frame_transcript ) {
			media_frame_transcript = wp.media( {
				title    : $uploadButton.data( 'frame-title' ),
				multiple : false
			} );

			media_frame_transcript.on( 'select', function() {
				var attachment = media_frame_transcript.state().get( 'selection' ).first().toJSON();
				$input_parent.find( 'input.transcript_url' ).val( attachment.url );
			} );
		}

		media_frame_transcript.open();
	} );

	/* Handle uploading image files */
	var media_frame_image;
	$( document ).on( 'click', '#theme-additional-fields .set-image, #podcast-episode-fields .podcast-metabox-field-episode .set-image', function( e ) {
		e.preventDefault();

		var $uploadButton = $( this ), $input_parent = $uploadButton.closest( '.podcast-metabox-input' );
		if ( ! media_frame_image ) {
			media_frame_image = wp.media( {
				title    : $uploadButton.data( 'frame-title' ),
				library  : { type: 'image' },
				multiple : false
			} );

			media_frame_image.on( 'select', function() {
				var attachment = media_frame_image.state().get( 'selection' ).first().toJSON();
				$input_parent.find( 'input.image_url' ).val( attachment.url );
			} );
		}

		media_frame_image.open();
	} );
} );
