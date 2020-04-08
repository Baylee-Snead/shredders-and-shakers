<?php
/**
 * Template part for displaying author information in posts
 */

if ( ! get_the_author_meta( 'description' ) ) {
	return;
}
?>

<aside class="author-box<?php echo esc_attr( get_the_author_meta( 'description' ) ? ' has-description' : ' no-description' ); ?>">
	<div class="row align-items-lg-center">
		<figure class="col-4 col-md-3 col-xl-2 author-box-image">
			<?php
			echo get_avatar(
				get_the_author_meta( 'user_email' ), 160, '', '', array(
					'height' => 80,
					'width'  => 80,
				)
			);
			?>
		</figure>
		<div class="col-8 col-md-9 col-xl-10">
			<h5 class="author-box-title"><?php esc_html_e( 'Published by', 'castilo' ); ?> <?php the_author_link(); ?></h5>
			<div class="author-box-description">
				<p class="author-box-social-links">
					<?php if ( '' !== get_the_author_meta( 'facebook' ) ) : ?>
					<a href="<?php echo esc_url( get_the_author_meta( 'facebook' ) ); ?>" class="castiloicon icon-facebook" rel="nofollow" target="_blank"></a>
					<?php endif; ?>
					<?php if ( '' !== get_the_author_meta( 'twitter' ) ) : ?>
					<a href="<?php echo esc_url( get_the_author_meta( 'twitter' ) ); ?>" class="castiloicon icon-twitter" rel="nofollow" target="_blank"></a>
					<?php endif; ?>
					<?php if ( '' !== get_the_author_meta( 'googleplus' ) ) : ?>
					<a href="<?php echo esc_url( get_the_author_meta( 'googleplus' ) ); ?>" class="castiloicon icon-google-plus" rel="nofollow" target="_blank"></a>
					<?php endif; ?>
				</p>
				<?php the_author_meta( 'description' ); ?>
			</div>
		</div>
	</div>
</aside>
