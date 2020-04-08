<?php
/**
 * Template part for displaying page content
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry-page' ); ?>>

	<div class="entry-content">
		<?php
		the_content();
		wp_link_pages(
			array(
				'before' => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages', 'castilo' ) . '</span>',
				'after'  => '</div>',
			)
		);
		edit_post_link(
			sprintf(
				/* translators: %s: Name of current post */
				__( 'Edit<span class="screen-reader-text"> "%s"</span>', 'castilo' ),
				get_the_title()
			),
			'<span class="edit-link">',
			'</span>',
			'',
			'post-edit-link button button-small'
		);
		?>
	</div>

</article>
