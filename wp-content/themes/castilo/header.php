<?php
/**
 * The template for displaying the header
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="profile" href="https://gmpg.org/xfn/11">

<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<?php 
	if ( function_exists( 'wp_body_open' ) ) {
		wp_body_open();
	}
	?>

	<?php do_action( 'castilo_header_before' ); ?>

	<header id="top" class="navbar">
		<div class="container">
			<div class="row align-items-center">
				<?php
				get_template_part( 'template-parts/header/site', 'branding' );
				get_template_part( 'template-parts/navigation/navigation', 'top' );
				get_template_part( 'template-parts/navigation/navigation', 'top-social' );
				get_template_part( 'template-parts/header/call-to-action' );
				get_template_part( 'template-parts/navigation/navigation', 'mobile' );
				?>
			</div>
		</div>
	</header>

	<?php get_template_part( 'template-parts/header/featured-content' ); ?>

	<?php do_action( 'castilo_header_after' ); ?>
