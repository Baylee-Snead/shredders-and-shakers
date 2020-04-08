<?php
/**
 * Displays social links navigation in the footer
 */

?>
<?php if ( has_nav_menu( 'social' ) ) : ?>
	<nav>
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
