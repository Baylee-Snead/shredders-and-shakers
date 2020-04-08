<?php
/**
 * Template part for displaying audio posts
 */

$separate_meta   = '<span>' . esc_html__( ', ', 'castilo' ) . '</span>';
$categories_list = get_the_category_list( $separate_meta );
$tags_list       = get_the_tag_list( '', $separate_meta );
$episode_type    = apply_filters( 'podcast_helper_episode_type', 'audio', get_the_ID() );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry-episode episode-type-' . $episode_type ); ?>>
	<div class="row align-items-xl-center">

		<?php if ( '' !== get_the_post_thumbnail() ) : ?>
			<div class="col-12 col-lg-4">
				<div class="entry-media entry-image multiply-effect">
					<a href="<?php echo esc_url( get_the_permalink() ); ?>">
						<?php
						the_post_thumbnail( 'castilo-episode-image', array( 'class' => 'first' ) ); ?>
						<span class="second"><?php the_post_thumbnail( 'castilo-episode-image' ); ?></span>
						<span class="third"><?php the_post_thumbnail( 'castilo-episode-image' ); ?></span>
					</a>
				</div>
			</div>
		<?php elseif ( get_option( 'podcast_cover' ) ) : ?>
			<div class="col-12 col-lg-4">
				<div class="entry-media entry-image multiply-effect">
					<a href="<?php echo esc_url( get_the_permalink() ); ?>">
						<?php
						$podcast_cover_image = get_option( 'podcast_cover' );
						if ( $podcast_cover_image ) {
							$podcast_cover_image_id = attachment_url_to_postid( $podcast_cover_image );
							if ( $podcast_cover_image_id ) {
								echo wp_kses_post( wp_get_attachment_image( $podcast_cover_image_id , 'castilo-episode-image', false, array( 'class' => 'first' ) ) );
								echo '<span class="second">' . wp_kses_post( wp_get_attachment_image( $podcast_cover_image_id , 'castilo-episode-image', false ) ) . '</span>';
								echo '<span class="third">' . wp_kses_post( wp_get_attachment_image( $podcast_cover_image_id , 'castilo-episode-image', false ) ) . '</span>';
							} else {
								echo '<img class="first" src="' . esc_url( $podcast_cover_image ) . '">';
								echo '<span class="second"><img src="' . esc_url( $podcast_cover_image ) . '"></span>';
								echo '<span class="third"><img src="' . esc_url( $podcast_cover_image ) . '"></span>';
							}
						}
						?>
					</a>
				</div>
			</div>
		<?php endif; ?>

		<div class="col-12 col-lg-8">
			<header class="entry-header">
				<div class="entry-meta">
					<?php if ( $categories_list && castilo_categorized_blog() ) : ?>
					<span class="posted-in"><span class="screen-reader-text"><?php esc_html_e( 'Posted in:', 'castilo' ); ?></span> <?php echo wp_kses_post( $categories_list ); ?></span>
					<?php endif; ?>
					<?php castilo_posted_on(); ?>

					<?php if ( $tags_list && ! is_wp_error( $tags_list ) ) : ?>
						<span class="tags"><span class="screen-reader-text"><?php esc_html_e( 'Posted in:', 'castilo' ); ?></span> <?php echo wp_kses_post( $tags_list ); ?></span>
					<?php endif; ?>
				</div>
				<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
			</header>

			<?php
			$episode_custom_player = get_post_meta( get_the_ID(), 'episode_custom_player', true );
			if ( ! post_password_required() && ! empty( $episode_custom_player ) ) {
				echo do_shortcode( $episode_custom_player );
			} else if ( ! post_password_required() && 'audio' == $episode_type ) {
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

			<?php if ( ! post_password_required() ) : ?>
				<div class="entry-content">
					<?php the_excerpt(); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</article>
