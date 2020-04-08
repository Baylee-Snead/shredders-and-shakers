/*
 * Live-update changed settings in real time in the Customizer preview.
 *
 */

( function( $ ) { "use strict";

	function castilo_replace_inline_style_setting( regex, subst, to ) {
		var inline_style_id = "castilo-style-inline-css";
		if ( $( "#" + inline_style_id ).length == 0 ) {
			$( "head" ).append( '<style id="' + inline_style_id + '" type="text/css"></style>' );
		}

		var inline_style = $( "#" + inline_style_id ).text();
		if ( inline_style.match( regex ) ) {
			$( "#" + inline_style_id ).text( inline_style.replace( regex, subst + to ) );
		} else {
			$( "#" + inline_style_id ).text( inline_style + subst + to + "; }" );
		}
	}

	// Site title
	wp.customize( "blogname", function( value ) {
		value.bind( function( to ) {
			$( ".site-branding-text h1 a" ).text( to );
		} );
	} );

	// Site tagline
	wp.customize( "blogdescription", function( value ) {
		value.bind( function( to ) {
			$( ".site-branding-text .site-description" ).text( to );
		} );
	} );

	// Page Background Color
	wp.customize( "background_color", function( value ) {
		value.bind( function( to ) {
			if ( to ) {
				castilo_replace_inline_style_setting( /body, body.colors-dark { background-color: #(?:[0-9a-f]{3}){1,2}/gm, "body { background-color: ", to );
			} else {
				castilo_replace_inline_style_setting( /body, body.colors-dark { background-color: #(?:[0-9a-f]{3}){1,2}/gm, "body { background-color: ", "initial" );
			}
		} );
	} );

	// Header Overlay Opacity
	wp.customize( "header_overlay_opacity", function( value ) {
		value.bind( function( to ) {
			castilo_replace_inline_style_setting( /.featured-content:after { opacity: (.*[0-9,.,[0-9])/gm, ".featured-content:after { opacity: ", ( to / 100 ) );
		} );
	} );

	// Footer Banner Overlay Opacity
	wp.customize( "footer_banner_overlay_opacity", function( value ) {
		value.bind( function( to ) {
			castilo_replace_inline_style_setting( /footer.sales-box:after { opacity: (.*[0-9,.,[0-9])/gm, "footer.sales-box:after { opacity: ", ( to / 100 ) );
		} );
	} );

} )( jQuery );
