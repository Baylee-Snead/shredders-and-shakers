<?php
/**
 * Displays featured content part for posts
 */
global $post;
$separate_meta = '<span>' . esc_html__( ', ', 'castilo' ) . '</span>';
$tags_list     = get_the_tag_list( '', $separate_meta );
?>

<header id="featured" class="featured-content padding-top-bottom">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-12 col-lg-10 offset-lg-1 text-center">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				<div class="entry-meta">
					<span class="posted-by"><span class="screen-reader-text"><?php esc_html_e( 'Posted by:', 'castilo' ); ?></span> <a href="<?php echo esc_url( get_author_posts_url( $post->post_author ) ); ?>"><?php echo get_avatar( $post->post_author, 80, '', '', array( 'height' => 40, 'width' => 40 ) ); the_author_meta( 'display_name', $post->post_author ); ?></a></span>

					<?php castilo_posted_on(); ?>

					<?php if ( $tags_list && ! is_wp_error( $tags_list ) ) : ?>
						<span class="tags"><span class="screen-reader-text"><?php esc_html_e( 'Posted in:', 'castilo' ); ?></span> <?php echo wp_kses_post( $tags_list ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<?php do_action( 'castilo_featured_after' ); ?>
</header>
