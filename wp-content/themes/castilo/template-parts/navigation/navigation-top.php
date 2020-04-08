<?php
/**
 * Displays top navigation
 */

?>
<?php if ( has_nav_menu( 'top' ) ) : ?>
	<nav id="site-menu" class="col-12 col-lg order-3 order-sm-4 order-lg-2">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'top',
				'container'      => false,
			)
		);
		?>
	</nav>
<?php endif; ?>
