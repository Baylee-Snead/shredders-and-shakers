<?php
/**
 * Displays header site branding
 */

?>

<div class="site-title col col-lg-auto order-first">

	<?php if ( has_custom_logo() ) : ?>
		<h1 itemscope itemtype="<?php echo esc_url( 'https://schema.org/Brand' ); ?>"><?php the_custom_logo(); ?></h1>
		<?php else : ?>
		<h1 class="text"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
	<?php endif; ?>

	<?php
	$description = get_bloginfo( 'description', 'display' );
	if ( $description ) :
		?>
		<p class="site-description screen-reader-text"><?php echo wp_kses_post( $description ); ?></p>
	<?php endif; ?>

	<?php do_action( 'castilo_additional_site_branding' ); ?>

</div>
