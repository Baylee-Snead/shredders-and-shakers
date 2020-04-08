<?php
/**
 * The sidebar containing the main widget area
 */

if ( ! is_active_sidebar( 'page-sidebar' ) ) {
	return;
}
?>

<aside id="sidebar" class="widget-area">
	<?php dynamic_sidebar( 'page-sidebar' ); ?>
</aside>
