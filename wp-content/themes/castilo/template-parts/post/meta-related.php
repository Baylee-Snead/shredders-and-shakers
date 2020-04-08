<?php
/**
 * Template part for displaying related posts
 */

$prev_post_link = get_adjacent_post_link( '%link', '%title' );
$next_post_link = get_adjacent_post_link( '%link', '%title', false, '', false );
$prev_post_icon = 'left';
$next_post_icon = 'right';
if ( is_rtl() ) {
	$tmp_icon       = $prev_post_icon;
	$prev_post_icon = $next_post_icon;
	$next_post_icon = $tmp_icon;
}
if ( ! is_attachment() && ( $prev_post_link || $next_post_link ) ) : ?>

	<aside class="post-controls">
		<div class="row">
			<div class="prev-post col-12 col-lg-6 col-xl-5">
				<?php if ( $prev_post_link ) : ?>
					<?php echo get_adjacent_post_link( '%link', '<span class="zmdi mdi mdi-arrow-' . $prev_post_icon . '"></span> ' . esc_html__( 'Prev', 'castilo' ) ); ?>
					<h5><?php echo wp_kses_data( $prev_post_link ); ?></h5>
				<?php endif; ?>
			</div>
			<div class="next-post col-12 col-lg-6 col-xl-5 offset-xl-2">
				<?php echo get_adjacent_post_link( '%link', esc_html__( 'Next', 'castilo' ) . ' <span class="mdi mdi-arrow-' . $next_post_icon . '"></span>', false, '', false ); ?>
				<h5><?php echo wp_kses_data( $next_post_link ); ?></h5>
			</div>
		</div>
	</aside>

<?php endif; ?>
