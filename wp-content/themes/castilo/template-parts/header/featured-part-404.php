<?php
/**
 * Displays featured content part for pages
 */

?>

<header id="featured" class="featured-content padding-top-bottom">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-12">
				<h1 class="entry-title"><span class="d-none d-sm-inline-block"><?php esc_html_e( 'Error', 'castilo' ); ?></span> 4<em>0</em>4</h1>
				<p><?php esc_html_e( 'Apologies, but no results were found for the requested page.', 'castilo' ); ?></p>
				<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button button-small"><?php esc_html_e( 'Go Home', 'castilo' ); ?></a></p>
			</div>
		</div>
	</div>

	<?php do_action( 'castilo_featured_after' ); ?>
</header>
