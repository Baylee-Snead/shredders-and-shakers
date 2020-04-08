<?php
/**
 * Displays social links navigation in the header
 */

?>
<?php if ( has_nav_menu( 'social' ) ) : ?>
	<nav id="social-links-menu" class="col-12 col-md-auto order-4 order-md-1 order-lg-3">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'social',
				'menu_class'     => 'social-navigation',
				'container'      => false,
				'depth'          => 1,
				'link_before'    => '<span class="screen-reader-text">',
				'link_after'     => '</span>',
			)
		);
		?>
	</nav>
<?php endif; ?>
