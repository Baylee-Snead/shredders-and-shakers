<?php
/**
 * The template for displaying comments
 *
 * This area of the page contains both current comments
 * and the comment form.
 *
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}

?>

<div id="comments">
	<?php if ( have_comments() ) : ?>

	<h5 class="comments-title">
		<?php
		/* translators: 1: number of comments */
		printf( esc_html__( 'Comments (%s)', 'castilo' ), intval( number_format_i18n( get_comments_number() ) ) );
		?>
	</h5>

	<ol class="comment-list<?php echo esc_attr( get_option( 'show_avatars', true ) ? '' : ' no-avatars' ); ?>">
		<?php
		wp_list_comments( array(
			'callback' => 'castilo_comments_callback',
		) );
		?>
	</ol>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through ?>
		<nav class="navigation comment-navigation">
			<h2 class="screen-reader-text"><?php esc_html_e( 'Comments navigation', 'castilo' ); ?></h2>
			<div class="nav-links">
				<div class="nav-previous">
					<?php next_comments_link( esc_html__( 'Newer Comments', 'castilo' ) ); ?>
				</div>
				<div class="nav-next">
					<?php previous_comments_link( esc_html__( 'Older Comments', 'castilo' ) ); ?>
				</div>
			</div>
		</nav>
		<?php endif; // check for comment navigation ?>

	<?php endif; // Check for have_comments(). ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>

		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'castilo' ); ?></p>

	<?php endif; ?>

	<?php
		comment_form( array(
			'title_reply_before' => '<h5 id="reply-title" class="comment-reply-title">',
			'title_reply_after'  => '</h5>',
		) );
	?>

</div>
