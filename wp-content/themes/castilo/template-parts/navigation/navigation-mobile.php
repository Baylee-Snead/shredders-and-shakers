<?php
/**
 * Displays top navigation toggle hamburger icon
 */

?>
<?php if ( has_nav_menu( 'top' ) ) : ?>
	<div class="site-menu-toggle col-auto order-2 order-sm-3">
		<a href="#site-menu">
			<span class="screen-reader-text"><?php esc_html_e( 'Toggle navigation', 'castilo' ); ?></span>
		</a>
	</div>
<?php endif; ?>
