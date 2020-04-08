<?php
/**
 * Displays footer widgets
 */

?>

<?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) ) : ?>

	<aside class="widget-area row">

		<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
		<div class="widget-column col-md footer-widgets-1">
			<?php dynamic_sidebar( 'footer-1' ); ?>
		</div>
		<?php endif; ?>

		<?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
		<div class="widget-column col-md footer-widgets-2">
			<?php dynamic_sidebar( 'footer-2' ); ?>
		</div>
		<?php endif; ?>

	</aside>

<?php endif; ?>
