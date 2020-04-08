<?php
/**
 * The sidebar containing the main widget area
 */

if ( ! is_active_sidebar( 'woo-sidebar' ) ) {
	return;
}
?>

<aside id="sidebar" class="widget-area">
	<?php dynamic_sidebar( 'woo-sidebar' ); ?>
</aside>
