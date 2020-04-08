<?php
/**
 * Displays featured content part for shop pages
 */

?>

<header id="featured" class="featured-content padding-top-bottom">
	<div class="container">
		<div class="row align-items-center">
			<?php if ( is_search() ) : ?>
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
			<?php else : ?>
				<div class="col-12 col-md">
					<h2 class="entry-title"><?php woocommerce_page_title(); ?></h2>
				</div>
				<div class="col-12 col-md-auto entry-description">
					<?php
					global $wp_query;
					if ( $wp_query->is_singular ) {
						$post_id = $wp_query->queried_object_id;
					}

					if ( is_shop() ) {
						$temp_id = get_option( 'woocommerce_shop_page_id' );
						if ( $temp_id ) {
							$post_id = $temp_id;
						}
					}

					if ( $post_id ) {
						echo wpautop( do_shortcode( get_post_meta( $post_id, 'featured_area_subtitle', true ) ) );
					}
					?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php do_action( 'castilo_featured_after' ); ?>
</header>
