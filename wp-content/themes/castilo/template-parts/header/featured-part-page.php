<?php
/**
 * Displays featured content part for pages
 */

?>

<header id="featured" class="featured-content padding-top-bottom">
	<div class="container">
		<div class="row align-items-center">
			<?php if ( is_home() && ! is_front_page() ) : ?>
				<div class="col-12 col-md">
					<h2 class="entry-title"><?php single_post_title(); ?></h2>
				</div>
				<div class="col-12 col-md-auto entry-description">
					<?php echo wpautop( do_shortcode( get_post_meta( get_option( 'page_for_posts' ), 'featured_area_subtitle', true ) ) ); ?>
				</div>
			<?php else : ?>
				<?php
				if ( is_page() ) {
					?>
					<div class="col-12 col-md">
						<?php the_title( '<h2 class="entry-title">', '</h2>' ); ?>
					</div>
					<div class="col-12 col-md-auto entry-description">
						<?php echo wpautop( do_shortcode( get_post_meta( $post->ID, 'featured_area_subtitle', true ) ) ); ?>
					</div>
					<?php
				} elseif ( is_category() || is_tag() || is_author() || is_year() || is_month() || is_day() || is_tax( 'post_format' ) || is_post_type_archive() || is_tax() ) {
					?>
					<div class="col-12 col-md">
						<?php the_archive_title( '<h2 class="entry-title">', '</h2>' ); ?>
					</div>
					<div class="col-12 col-md-auto entry-description">
						<?php the_archive_description(); ?>
					</div>
					<?php
				} elseif ( is_search() ) {
				?>
					<div class="col-12 col-md">
						<h2 class="entry-title"><?php esc_html_e( 'Search Results', 'castilo' ); ?></h2>
					</div>
					<div class="col-12 col-md-auto entry-description">
						<p>
							<?php
							/* translators: 1: search term */
							printf( esc_html__( 'Looking for the %s term.', 'castilo' ), '<mark>' . get_search_query() . '</mark>' );
							?>
						</p>
					</div>
				<?php
				} else {
					?>
					<div class="col-12 col-md">
						<h2 class="entry-title"><?php esc_html_e( 'Latest Posts', 'castilo' ); ?></h2>
					</div>
					<?php if ( $paged && $paged > 1 ) : ?>
						<div class="col-12 col-md-auto entry-description">
							<p>
								<?php
								/* translators: 1: page number */
								printf( esc_html__( 'Page %s', 'castilo' ), $paged );
								?>
							</p>
						</div>
					<?php endif; ?>
				<?php } ?>
			<?php endif; ?>
		</div>
	</div>

	<?php do_action( 'castilo_featured_after' ); ?>
</header>
