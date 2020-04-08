<?php
/**
 * Displays featured content part for the home template
 */
?>

<header id="featured" class="featured-content padding-top-bottom">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-12 col-lg-8 col-xl-7">
				<div class="latest-episode">
					<p class="big text-uppercase opacity-50"><?php esc_html_e( 'Latest Episode', 'castilo' ); ?></p>
					<?php
					echo do_shortcode( '[podcast_episode id="latest" title="true" title_tag="h1" title_class="entry-title"]' );
					echo wpautop( do_shortcode( get_post_meta( $post->ID, 'featured_area_additional_text', true ) ) );
					?>
				</div>
			</div>
		</div>
	</div>

	<?php do_action( 'castilo_featured_after' ); ?>
</header>
