<?php
/**
 * Template part for displaying categories, tags, sharing tools in posts
 */

if ( ! is_single() || post_password_required() ) {
	return;
}

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

<div class="entry-footer">
	<?php get_template_part( 'template-parts/post/meta', 'share-categories' ); ?>
	<?php get_template_part( 'template-parts/post/meta', 'author' ); ?>
	<?php get_template_part( 'template-parts/post/meta', 'related' ); ?>
</div>
