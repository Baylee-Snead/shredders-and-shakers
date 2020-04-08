<?php
/**
 * Template part for displaying posts in the Home template.
 */

$categories_list = get_the_category_list( '<span> </span>' );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry-post' ); ?>>

	<?php if ( $categories_list && castilo_categorized_blog() ) : ?>
		<div class="categories">
			<span class="screen-reader-text"><?php esc_html_e( 'Posted in:', 'castilo' ); ?></span>
			<?php echo wp_kses_post( $categories_list ); ?>
		</div>
	<?php endif; ?>

	<header class="entry-header">
		<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
		<div class="entry-meta">
			<?php castilo_posted_on(); ?>
		</div>
	</header>

	<div class="entry-content">
		<?php
		if ( ! post_password_required() ) {
			the_excerpt();
		}
		?>
	</div>

</article>
