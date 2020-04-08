<?php
/**
 * The template for displaying all single posts
 */

get_header();
?>

<main id="content" class="padding-top-bottom">
	<div class="container">
		<div class="row">
			<div class="col-12 col-md-8 col-lg-9">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/post/content', 'episode' );
					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
				}
				?>
			</div>
			<div class="col-12 col-md-4 col-lg-3">
				<?php get_sidebar(); ?>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
