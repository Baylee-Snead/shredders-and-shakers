<?php
/**
 * Displays featured content part for episodes
 */
global $post;
$separate_meta   = '<span>' . esc_html__( ', ', 'castilo' ) . '</span>';
$tags_list       = get_the_tag_list( '', $separate_meta );
$categories_list = get_the_category_list( '<span> </span>' );
?>

<header id="featured" class="featured-content padding-top-bottom">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-12 col-lg-9 col-xl-8">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

				<div class="entry-meta">
					<?php if ( $categories_list && castilo_categorized_blog() ) : ?>
					<span class="posted-in">
						<span class="screen-reader-text"><?php esc_html_e( 'Posted in:', 'castilo' ); ?></span>
						<?php echo wp_kses_post( $categories_list ); ?>
					</span>
					<?php endif; ?>

					<?php castilo_posted_on(); ?>

					<?php if ( $tags_list && ! is_wp_error( $tags_list ) ) : ?>
						<span class="tags"><span class="screen-reader-text"><?php esc_html_e( 'Posted in:', 'castilo' ); ?></span> <?php echo wp_kses_post( $tags_list ); ?></span>
					<?php endif; ?>
				</div>

				<?php
				$episode_type          = apply_filters( 'podcast_helper_episode_type', 'audio', get_the_ID() );
				$episode_custom_player = get_post_meta( get_the_ID(), 'episode_custom_player', true );
				if ( ! post_password_required() && ! empty( $episode_custom_player ) ) :
					echo do_shortcode( $episode_custom_player );
				elseif ( ! post_password_required() && 'audio' == $episode_type ) :
					$episode_file          = apply_filters( 'podcast_helper_episode_media_url', get_the_ID(), 'player' );
					$episode_file_raw      = get_post_meta( get_the_ID(), 'episode_audio_file', true );
					$episode_file_download = apply_filters( 'podcast_helper_episode_media_url', get_the_ID(), 'download' );
					$episode_file_duration = get_post_meta( get_the_ID(), 'episode_audio_file_duration', true );
					$duration_digit_no           = substr_count( $episode_file_duration, ':' );
					if ( 2 == $duration_digit_no ) {
						$episode_file_duration_secs = strtotime( '1970-01-01 ' . $episode_file_duration . ' UTC' );
					} elseif ( 1 == $duration_digit_no ) {
						$episode_file_duration_secs = strtotime( '1970-01-01 00:' . $episode_file_duration . ' UTC' );
					} elseif ( 0 == $duration_digit_no ) {
						$episode_file_duration_secs = strtotime( '1970-01-01 00:00:' . $episode_file_duration . ' UTC' );
					} else {
						$episode_file_duration_secs = strtotime( $episode_file_duration ) - strtotime( 'TODAY' );
					}
					$episode_file_duration = gmdate( $episode_file_duration_secs >= 3600 ? 'G:i:s' : 'i:s', $episode_file_duration_secs );
					$episode_file_size     = get_post_meta( get_the_ID(), 'episode_audio_file_size', true );
					$episode_transcript          = get_post_meta( get_the_ID(), 'episode_transcript', true );
					if ( ! empty( $episode_file_raw ) ) :
						?>
						<div class="podcast-episode">
							<div class="podcast-episode-player" data-episode-id="<?php echo esc_attr( get_the_ID() ); ?>" data-episode-download="<?php echo esc_url( $episode_file_download ); ?>" data-episode-download-button="<?php echo esc_attr( sprintf( esc_html__( 'Download Episode%s', 'castilo' ), $episode_file_size ? ' (' . esc_attr( size_format( $episode_file_size, 1 ) ) . ')' : '' ) ); ?>" data-episode-duration="<?php echo esc_attr( $episode_file_duration ); ?>" data-episode-size="<?php echo esc_attr( size_format( $episode_file_size, 1 ) ); ?>" data-episode-transcript="<?php echo esc_url( $episode_transcript ); ?>" data-episode-transcript-button="<?php echo esc_attr( esc_html__( 'View Transcript', 'castilo' ) ); ?>">
								<?php
									echo apply_filters(
										'podcast_helper_audio_player_shortcode',
										wp_audio_shortcode(
											array(
												'src'     => esc_url( $episode_file ),
												'preload' => 'none',
												'class'   => 'wp-audio-shortcode podcast-episode-' . get_the_ID(),
											)
										),
										$episode_file
									);
								?>
							</div>
						</div>
						<?php
					endif;
				endif;

				echo wpautop( do_shortcode( get_post_meta( get_the_ID(), 'featured_area_additional_text', true ) ) );
				?>
			</div>
		</div>
	</div>

	<?php do_action( 'castilo_featured_after' ); ?>
</header>
