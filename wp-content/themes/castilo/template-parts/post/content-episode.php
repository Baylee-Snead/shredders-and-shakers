<?php
/**
 * Template part for displaying audio posts
 */

$categories_list = get_the_category_list( '<span> </span>' );
$episode_type    = apply_filters( 'podcast_helper_episode_type', 'audio', get_the_ID() );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry-post episode-type-' . $episode_type ); ?>>

	<?php if ( ! is_single() && $categories_list && castilo_categorized_blog() ) : ?>
		<div class="categories">
			<span class="screen-reader-text"><?php esc_html_e( 'Posted in:', 'castilo' ); ?></span>
			<?php echo wp_kses_post( $categories_list ); ?>
		</div>
	<?php endif; ?>

	<?php if ( ! is_single() && '' !== get_the_post_thumbnail() ) : ?>
		<div class="entry-media entry-image">
			<a href="<?php echo esc_url( get_the_permalink() ); ?>">
				<?php the_post_thumbnail( 'large' ); ?>
			</a>
		</div>
	<?php endif; ?>

	<?php if ( ! is_single() ) : ?>
		<header class="entry-header">
			<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
		</header>
	<?php endif; ?>

	<?php
	$episode_file          = apply_filters( 'podcast_helper_episode_media_url', get_the_ID(), 'player' );
	$episode_file_raw      = get_post_meta( get_the_ID(), 'episode_audio_file', true );
	$episode_file_download = apply_filters( 'podcast_helper_episode_media_url', get_the_ID(), 'download' );
	$episode_file_duration = get_post_meta( get_the_ID(), 'episode_audio_file_duration', true );
	$duration_digit_no     = substr_count( $episode_file_duration, ':' );
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
	$episode_transcript    = get_post_meta( get_the_ID(), 'episode_transcript', true );
	$episode_type          = apply_filters( 'podcast_helper_episode_type', 'audio', get_the_ID() );
	$episode_custom_player = get_post_meta( get_the_ID(), 'episode_custom_player', true );
	if ( ! is_single() && ! post_password_required() && empty( $episode_custom_player ) && 'audio' == $episode_type ) {
		if ( ! empty( $episode_file_raw ) ) {
			?>
			<div class="entry-audio">
				<div class="podcast-episode-player" data-episode-id="<?php echo esc_attr( get_the_ID() ); ?>" data-episode-download="<?php echo esc_url( $episode_file_download ); ?>" data-episode-download-button="<?php echo esc_attr( sprintf( esc_html__( 'Download Episode (%s)', 'castilo' ), esc_attr( size_format( $episode_file_size, 1 ) ) ) ); ?>" data-episode-duration="<?php echo esc_attr( $episode_file_duration ); ?>" data-episode-size="<?php echo esc_attr( size_format( $episode_file_size, 1 ) ); ?>" data-episode-transcript="<?php echo esc_url( $episode_transcript ); ?>" data-episode-transcript-button="<?php echo esc_attr( esc_html__( 'View Transcript', 'castilo' ) ); ?>">
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
		} elseif ( ! is_single() ) {
			$content = apply_filters( 'the_content', get_the_content() );
			$audio   = false;
			if ( false === strpos( $content, 'wp-playlist-script' ) ) {
				$audio = get_media_embedded_in_content( $content, array( 'audio' ) );
			}
			if ( ! empty( $audio ) ) {
				foreach ( $audio as $audio_html ) :
					?>
					<div class="entry-audio">
						<?php echo do_shortcode( $audio_html ); // already escaped by WP ?>
					</div>
					<?php
				endforeach;
			}
		}
	}
	?>

	<div class="entry-content">
		<?php
		if ( is_single() ) {
			if ( ! post_password_required() && false !== strpos( $episode_type, 'video' ) ) {
				if ( 'video-embed' == $episode_type ) {
					$episode_file          = $episode_file_raw;
					$episode_file_download = $episode_file_raw;
					$episode_download_text = esc_html__( 'View Original Video', 'castilo' );
				} else {
					$episode_download_text = sprintf( esc_html__( 'Download Episode%s', 'castilo' ), $episode_file_size ? ' (' . esc_attr( size_format( $episode_file_size, 1 ) ) . ')' : '' );
				}
				?>
				<div class="entry-video">
					<div class="podcast-episode-player" data-episode-id="<?php echo esc_attr( get_the_ID() ); ?>" data-episode-download="<?php echo esc_url( $episode_file_download ); ?>" data-episode-download-button="<?php echo esc_attr( $episode_download_text ); ?>" data-episode-duration="<?php echo esc_attr( $episode_file_duration ); ?>" data-episode-size="<?php echo esc_attr( size_format( $episode_file_size, 1 ) ); ?>" data-episode-transcript="<?php echo esc_url( $episode_transcript ); ?>" data-episode-transcript-button="<?php echo esc_attr( esc_html__( 'View Transcript', 'castilo' ) ); ?>">
						<a class="play-episode" href="<?php echo esc_url( get_the_permalink() ); ?>"><span><?php esc_html_e( 'Play Episode', 'castilo' ); ?></span></a>
						<?php
							echo apply_filters(
								'podcast_helper_video_player_shortcode',
								wp_video_shortcode(
									array(
										'src'     => esc_url( $episode_file ),
										'preload' => 'none',
										'class'   => 'wp-video-shortcode podcast-episode-' . get_the_ID(),
									)
								),
								$episode_file
							);
						?>
					</div>
				</div>
				<?php
			}
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
