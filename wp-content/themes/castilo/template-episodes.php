<?php
/**
 * Template Name: Episodes
 *
 * The template for displaying episode posts.
 *
 */

get_header();

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1 );
$query_args = array(
	'post_type'   => array( 'episode' ),
	'post_status' => 'publish',
	'orderby'     => 'menu_order date',
	'paged'       => $paged,
);

/* Filter results by the selected base category */
$page_category = get_post_meta( get_the_ID(), 'page_category', true );
if ( isset( $page_category ) && ! empty( $page_category ) && $page_category > 0 ) {
	$query_args['cat'] = $page_category;
}
query_posts( $query_args );
?>

<main id="content" class="padding-top-bottom">
	<div class="container">
		<div class="row">
			<div class="col-12 col-md-8 col-lg-9">

				<?php if ( have_posts() ) : ?>

					<div class="episodes-listing">
						<?php
						while ( have_posts() ) :
							the_post();
							get_template_part( 'template-parts/post/listing-content', get_post_type() );
						endwhile;

						castilo_pagination_links();
						?>
					</div>

				<?php else :

					// No results, display a 'Not Found' message
					get_template_part( 'template-parts/post/content', 'none' );

				endif;
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
