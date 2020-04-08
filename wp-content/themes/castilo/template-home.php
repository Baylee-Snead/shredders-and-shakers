<?php
/**
 * Template Name: Home
 *
 * The template for displaying a combination of episodes and posts for a homepage.
 *
 */

get_header();
?>

<main id="content" class="padding-top-bottom">
	<?php get_template_part( 'template-parts/page/content-page', 'home' ); ?>
</main>

<?php
get_footer();
