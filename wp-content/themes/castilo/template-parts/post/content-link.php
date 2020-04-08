<?php
/**
 * Template part for displaying link posts
 */

$post_link = get_permalink();
if ( 'link' === get_post_format() ) {
	$content = get_the_content();
	if ( preg_match( "/<a\s[^>]*href=([\"\']??)([^\\1 >]*?)\\1[^>]*>(.*)<\/a>/siU", $content, $matches ) ) {
		if ( isset( $matches[2] ) ) {
			$post_link = $matches[2];
		}
	}
}
$categories_list = get_the_category_list( '<span> </span>' );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry-post' ); ?>>

	<?php if ( ! is_single() && $categories_list && castilo_categorized_blog() ) : ?>
		<div class="categories">
			<span class="screen-reader-text"><?php esc_html_e( 'Posted in:', 'castilo' ); ?></span>
			<?php echo wp_kses_post( $categories_list ); ?>
		</div>
	<?php endif; ?>

	<?php if ( ! is_single() && '' !== get_the_post_thumbnail() ) : ?>
		<div class="entry-media entry-image">
			<a href="<?php echo esc_url( $post_link ); ?>">
				<?php the_post_thumbnail( 'large' ); ?>
			</a>
		</div>
	<?php endif; ?>

	<?php if ( ! is_single() ) : ?>
		<header class="entry-header">
			<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( $post_link ) . '" rel="bookmark">', '</a></h2>' ); ?>
		</header>
	<?php endif; ?>

	<div class="entry-content">
		<?php
		if ( is_single() ) {
			the_content(
				sprintf(
					/* translators: 1 - page title */
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
			if ( ! post_password_required() ) {
				the_excerpt();
			}
		}
		?>
	</div>

	<?php get_template_part( 'template-parts/post/meta', 'footer' ); ?>

</article>
