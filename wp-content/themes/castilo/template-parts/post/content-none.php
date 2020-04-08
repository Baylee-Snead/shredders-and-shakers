<?php
/**
 * Template part for displaying a message that posts cannot be found
 */

global $post;
$page_template = '';
if ( $post && isset( $post ) ) {
	$page_template = get_page_template_slug( $post->ID );
}
?>

<section class="no-results not-found">
	<header class="entry-header">
		<h3 class="entry-title"><?php esc_html_e( 'Nothing Found', 'castilo' ); ?></h3>
	</header>

	<div class="entry-content">
		<?php
		if ( ( is_home() || 'template-episodes.php' === $page_template ) && current_user_can( 'publish_posts' ) ) :
			?>

			<p>
				<?php
				$additional_param = '';
				if ( 'template-episodes.php' === $page_template ) {
					$additional_param = '?post_type=episode';
				}
				printf( '%1$s <a href="%2$s">%3$s</a>.',
					esc_html__( 'Ready to publish your first entry?', 'castilo' ),
					esc_url( admin_url( 'post-new.php' . $additional_param ) ),
					esc_html__( 'Get started here', 'castilo' )
				);
				?>
			</p>

		<?php else : ?>

			<div class="alert"><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for.', 'castilo' ); ?></div>

			<?php
		endif;
?>
	</div>
</section>
