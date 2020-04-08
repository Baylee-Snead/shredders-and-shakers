<?php
/**
 * Template for displaying search forms
 */

$unique_id = uniqid( 'search-form-' );
?>

<form role="search" method="get" class="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $unique_id ); ?>" class="screen-reader-text"><?php echo esc_html_x( 'Search for:', 'label', 'castilo' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $unique_id ); ?>" name="s" placeholder="<?php echo esc_attr_x( 'Search&hellip;', 'placeholder', 'castilo' ); ?>" value="<?php echo get_search_query(); ?>">
	<button type="submit"><span class="screen-reader-text"><?php echo esc_html_x( 'Search', 'submit button', 'castilo' ); ?></span><span class="mdi mdi-magnify"></span></button>
</form>
