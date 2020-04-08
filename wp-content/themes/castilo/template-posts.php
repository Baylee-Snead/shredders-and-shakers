<?php
/**
 * Template Name: Posts
 *
 * The template for displaying blog posts.
 *
 */

get_header();

global $post;
$is_page = is_page();
if ( $is_page ) {
	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1 );
	$query_args = array(
		'post_type'   => array( 'post' ),
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
}
?>

<main id="content" class="padding-top-bottom">
	<div class="container">
		<div class="row">
			<div class="col-12 col-md-8 col-lg-9">

				<?php if ( have_posts() ) : ?>

					<div class="post-listing">
						<div class="row masonry-grid">
							<?php
							// Mark availability of query results
							$main_query_has_posts = true;

							// Obtain list of custom post types, excluding default post types
							$custom_types = array_keys(
								get_post_types( array(
									'_builtin' => false,
								) )
							);

							while ( have_posts() ) :
								the_post();
								?>

								<div class="col-12 col-lg-6 grid-item">
									<?php

									// Get current post type
									$current_post_type = get_post_type();

									// Check if this is a custom post type
									if ( $current_post_type && in_array( $current_post_type, $custom_types ) ) {
										// Show special template part for custom post type (can be added in child theme easily)
										get_template_part( 'template-parts/post/content', $current_post_type );
									} else {
										// Show default template part for built in post types
										get_template_part( 'template-parts/post/content', get_post_format() );
									}
									?>
								</div>

								<?php
							endwhile;
							?>

							<div class="col-12 col-lg-6 grid-sizer"></div>
						</div>

						<?php castilo_pagination_links(); ?>
					</div>

					<?php
				else :

					// No results, display a 'Not Found' message
					get_template_part( 'template-parts/post/content', 'none' );

				endif;
				?>

			</div>

			<?php
			if ( $is_page ) {
				wp_reset_query(); // done ONLY for custom template pages where query_posts is used (to avoid altering the main query)
			}
			?>

			<div class="col-12 col-md-4 col-lg-3">
				<?php get_sidebar(); ?>
			</div>

		</div>
	</div>
</main>

<?php
get_footer();