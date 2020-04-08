<?php
/**
 * Displays footer site info
 */

$copyright_text = get_theme_mod( 'site_copyright', '&copy; {{year}} {{site-title}}. All Rights Reserved.' );
$search         = array( '{{year}}', '{{site-title}}' );
$replace        = array( date( 'Y' ), '<a href="' . home_url() . '">' . get_bloginfo( 'name', 'display' ) . '</a>' );
$copyright_text = str_replace( $search, $replace, $copyright_text );
$copyright_text = wptexturize( trim( $copyright_text ) );
$copyright_text = convert_chars( $copyright_text );

if ( $copyright_text ) {
	echo do_shortcode( $copyright_text );
	if ( function_exists( 'the_privacy_policy_link' ) ) {
		the_privacy_policy_link( '<span class="separator" role="separator" aria-hidden="true"></span>' );
	}
}
