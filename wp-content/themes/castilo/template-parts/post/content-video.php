<?php
/**
 * Template part for displaying video posts
 */

$categories_list = get_the_category_list( '<span> </span>' );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry-post' ); ?>>

	<?php if ( ! is_single() && $categories_list && castilo_categorized_blog() ) : ?>
		<div class="categories">
			<span class="screen-reader-text"><?php esc_html_e( 'Posted in:', 'castilo' ); ?></span>
			<?php echo wp_kses_post( $categories_list ); ?>
		</div>
	<?php endif; ?>

	<?php if ( ! is_single() ) : ?>
		<header class="entry-header">
			<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
		</header>
	<?php endif; ?>

	<?php
	$content = apply_filters( 'the_content', get_the_content() );
	$video   = false;
	if ( false === strpos( $content, 'wp-playlist-script' ) ) {
		$video = get_media_embedded_in_content( $content, array( 'video', 'object', 'embed', 'iframe' ) );
	}
	?>

	<?php if ( ! is_single() && ! empty( $video ) ) : ?>
		<?php foreach ( $video as $video_html ) : ?>
			<div class="entry-video">
				<?php echo do_shortcode( $video_html ); // already escaped by WP ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>

	<div class="entry-content">
		<?php
		if ( is_single() ) {
			the_content(
				sprintf(
					/* translators: %s: Name of current post */
					__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'castilo' ),
					get_the_title()
				)
			);

			wp_link_pages(
				array(
					'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages', 'castilo' ) . '</span>',
					'after'       => '</div>',
					'link_before' => '<span class="page-number">',
					'link_after'  => '</span>',
				)
			);
		} else {
			if ( ! post_password_required() && empty( $video ) ) {
				the_excerpt();
			}
		}
		?>
	</div>

	<?php get_template_part( 'template-parts/post/meta', 'footer' ); ?>

</article>
