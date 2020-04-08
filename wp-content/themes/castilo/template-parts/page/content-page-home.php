<?php
/**
 * Template part for displaying content for the home template
 */

$post_id           = get_the_ID();
$episodes_per_page = get_post_meta( $post_id, 'episodes_per_page', true );
if ( ! $episodes_per_page ) {
	$episodes_per_page = 3;
}
$hide_news_section  = get_post_meta( $post_id, 'hide_news_section', true );
$instagram_username = get_post_meta( $post_id, 'instagram_username', true );
$episodes_sidebar   = is_active_sidebar( 'home-sidebar' );

/* Filter episodes by the selected category */
$episode_query_args = array(
	'post_type'           => array( 'episode' ),
	'post_status'         => 'publish',
	'orderby'             => 'menu_order date',
	'posts_per_page'      => $episodes_per_page,
	'ignore_sticky_posts' => true,
);
$episodes_category = get_post_meta( $post_id, 'episodes_category', true );
if ( isset( $episodes_category ) && ! empty( $episodes_category ) && $episodes_category > 0 ) {
	$episode_query_args['cat'] = $episodes_category;
}
$episode_query = new WP_Query( $episode_query_args );
?>
<div class="container<?php if ( ! empty( $hide_news_section ) && empty( $instagram_username ) ) echo esc_attr( ' margin-bottom' ); ?>">
	<div class="row">
		<div class="<?php echo esc_attr( $episodes_sidebar ? 'col-12 col-md-8 col-lg-9' : 'col-12' ); ?>">
			<div class="episodes-listing">
				<h3 class="add-separator"><span><?php printf( '%1$s %2$s',
					esc_html__( 'Browse', 'castilo' ),
					sprintf(
						'<em>%s</em>',
						esc_html__( 'Episodes', 'castilo' )
					)
				); ?></span></h3>

				<?php
					if ( $episode_query->have_posts() ) :
						while ( $episode_query->have_posts() ) {
							$episode_query->the_post();
							get_template_part( 'template-parts/post/home', 'episode' );
						}
						$episode_page_id = get_post_meta( $post_id, 'episodes_page', true );
						if ( isset( $episode_page_id ) && ! empty( $episode_page_id ) && $episode_page_id > 0 ) :
						?>
							<div class="pagination pagination-load-more">
								<a href="<?php echo esc_url( get_permalink( $episode_page_id ) ); ?>" class="button button-filled"><span class="mdi mdi-dots-horizontal"></span> <?php esc_html_e( 'Browse More', 'castilo' ); ?></a>
							</div>
						<?php
						endif;
					else :
						?>
						<strong><?php esc_html_e( 'No episodes were found in the selected category.', 'castilo' ); ?></strong>
						<?php
					endif;
					?>

			</div>
		</div>

		<?php if ( $episodes_sidebar ) : ?>
			<div class="col-12 col-md-4 col-lg-3">
				<aside id="sidebar" class="widget-area">
					<?php dynamic_sidebar( 'home-sidebar' ); ?>
				</aside>
			</div>
		<?php endif; ?>

	</div>
</div>

<?php
if ( empty( $hide_news_section ) ) {
	/* Filter news by the selected category */
	$news_query_args = array(
		'post_type'           => array( 'post' ),
		'post_status'         => 'publish',
		'orderby'             => 'menu_order date',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
	);
	$news_category = get_post_meta( $post_id, 'news_category', true );
	if ( isset( $news_category ) && ! empty( $news_category ) && $news_category > 0 ) {
		$news_query_args['cat'] = $news_category;
	}
	$news_query = new WP_Query( $news_query_args );
	?>
	<div class="latest-news margin-top-bottom invert-colors padding-top-bottom">
		<div class="container">
			<h3 class="add-separator"><span><?php printf( '%1$s %2$s',
				esc_html__( 'Latest', 'castilo' ),
				sprintf(
					'<em>%s</em>',
					esc_html__( 'News', 'castilo' )
				)
			); ?></span></h3>
			<?php if ( $news_query->have_posts() ) : ?>
				<div class="row">
					<?php
						while ( $news_query->have_posts() ) {
							?>
							<div class="col-12 col-md">
								<?php
								$news_query->the_post();
								get_template_part( 'template-parts/post/home', 'post' );
								?>
							</div>
							<?php
						}
						?>
				</div>
				<?php
				$news_page_id = get_post_meta( $post_id, 'news_page', true );
				if ( isset( $news_page_id ) && ! empty( $news_page_id ) && $news_page_id > 0 ) :
				?>
				<div class="pagination pagination-load-more">
					<a href="<?php echo esc_url( get_permalink( $news_page_id ) ); ?>" class="button button-white button-filled"><span class="mdi mdi-dots-horizontal"></span> <?php esc_html_e( 'Browse More', 'castilo' ); ?></a>
				</div>
				<?php endif; ?>
			<?php else : ?>
				<strong><?php esc_html_e( 'No posts were found in the selected category.', 'castilo' ); ?></strong>
			<?php endif; ?>
		</div>
	</div>
<?php } // empty( $hide_news_section ) ?>

<?php
wp_reset_query();
$page_content = get_post_field( 'post_content', $post_id );
if ( $page_content ) :
?>
	<div class="container margin-top-bottom">
		<div class="row">
			<div class="col-12">
				<article id="post-<?php echo esc_attr( $post_id ); ?>" <?php post_class( 'entry entry-page' ); ?>>

					<div class="entry-content">
						<?php
						the_content();
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
			</div>
		</div>
	</div>
<?php endif; ?>
