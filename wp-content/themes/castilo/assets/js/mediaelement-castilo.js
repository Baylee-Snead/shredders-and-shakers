(function( window, $, undefined ) { "use strict";

	var settings      = window._wpmejsSettings || {};
	settings.features = settings.features || mejs.MepDefaults.features;
	settings.features.push( "castilo" );
	$.extend( mejs.MepDefaults, {
		hideVolumeOnTouchDevices: true,
		audioVolume: "vertical",
	} );
	MediaElementPlayer.prototype.buildcastilo = function( player, controls, layers, media ) {
		var player_container = player.container.addClass( "castilo-mejs-container" ),
			player_parent = player_container.parents( ".podcast-episode-player" );
		if ( player_parent.length > 0 ) {
			player_parent.addClass( "state-init" );

			media.addEventListener( "playing", function( e ) {
				player_parent.removeClass( "state-init state-paused state-ended" );
				player_parent.addClass( "state-playing" );
			}, false);

			media.addEventListener( "pause", function( e ) {
				player_parent.addClass( "state-paused" ).removeClass( "state-playing" );
			}, false);

			media.addEventListener( "ended", function( e ) {
				player_parent.addClass( "state-ended" ).removeClass( "state-paused state-playing" );
			}, false);

			// manually add duration to the player (as it is prefered to not preload any data, so we can properly collect statistics only when the user clicks the play button)
			var episode_duration = player_parent.data( "episode-duration" );
			if ( episode_duration ) {
				controls.find( "span.mejs-duration" ).html( episode_duration );
			}
			// add download button
			var download_file = player_parent.data( "episode-download" );
			if ( ! $( 'body' ).hasClass( "no-episode-download" ) && download_file ) {
				var download_button = $( '<div class="mejs-button mejs-download-button"><a href="' + download_file + '" title="' + player_parent.data( "episode-download-button" ) + '"><span class="screen-reader-text">' + player_parent.data( "episode-download-button" ) + '</span></a></div>' );
				download_button.appendTo( controls );
			}
			// add transcript button
			var transcript_file = player_parent.data( "episode-transcript" );
			if ( transcript_file ) {
				var transcript_button = $( '<div class="mejs-button mejs-transcript-button"><a href="' + transcript_file + '" title="' + player_parent.data( "episode-transcript-button" ) + '" target="_blank"><span class="screen-reader-text">' + player_parent.data( "episode-transcript-button" ) + '</span></a></div>' );
				transcript_button.appendTo( controls );
			}
		}
	};

	// Handle audio timeline jumping points for episodes.
	jQuery( ".podcast-episode-player .play-episode" ).on( "click.castilo", function( e ) {
		e.preventDefault();
		jQuery( this ).parent( ".podcast-episode-player" ).find( "video" ).get(0).player.play();
		jQuery( this ).hide();

		// mark a play event for external videos
		if ( jQuery( "body" ).hasClass( "episode-type-video-embed" ) ) {
			jQuery.post( podcast_ajax_object.ajax_url, {
				action: "podcast_helper_episode_start_play",
				episode_id: jQuery( this ).parent( ".podcast-episode-player" ).data( "episode-id" )
			});
		}
	});

	// Handle audio timeline jumping points for episodes.
	$( '.single-episode a.jump-point[href^="#"], .single-episode .wp-block-button.jump-point a[href^="#"]' ).on( 'click.castilo', function( e ) {
		if ( $( '.podcast-episode-player .castilo-mejs-container' ) ) {
			e.preventDefault();

			var jumping_point = $( this ).attr( 'href' ).substr( 1 ), player = $( '.podcast-episode-player .castilo-mejs-container audio' ).get( 0 ).player, jumping_point_seconds = 0, m = 1, p = jumping_point.split( ':' );
			while ( p.length > 0 ) {
				jumping_point_seconds += m * parseInt( p.pop(), 10 );
				m                     *= 60;
			}
			if ( jumping_point_seconds > 0 ) {
				if ( true == player.paused ) {
					player.play();
				}
				player.setCurrentTime( jumping_point_seconds );
			}
		}
	});

})( this, jQuery );
