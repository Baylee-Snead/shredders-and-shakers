<?php
/**
 * Handle metabox options for the episodes custom post type.

 * @package Podcast Helper
 */

/**
 * Add a metabox for displaying additional podcast information or statistics.
 */
function podcast_helper_meta_box_setup() {
	add_meta_box(
		'podcast-episode-side',
		esc_html__( 'Episode Shortcode & Stats', 'podcast-helper' ),
		'podcast_helper_meta_box_side_content',
		'',
		'side',
		'low'
	);

	add_meta_box(
		'podcast-episode-fields',
		esc_html__( 'Episode Fields', 'podcast-helper' ),
		'podcast_helper_metabox_episode_fields',
		'',
		'normal',
		'high'
	);

	$instructions = '<p><strong>' . esc_html__( 'Episode File', 'podcast-helper' ) . '</strong> &mdash; ' . esc_html__( 'Select the primary file that will be displayed in the header area and use it in the podcast feed to include it for external services.', 'podcast-helper' ) . '</p>';
	$instructions .= '<p><strong>' . esc_html__( 'Episode Transcript', 'podcast-helper' ) . '</strong> &mdash; ' . esc_html__( 'Select the transcript file for this podcast episode.', 'podcast-helper' ) . '</p>';
	$instructions .= '<p><strong>' . esc_html__( 'iTunes Episode Type', 'podcast-helper' ) . '</strong> &mdash; ' . esc_html__( 'Select the type of this podcast episode (full for a normal podcast episode).', 'podcast-helper' ) . '</p>';
	$instructions .= '<p><strong>' . esc_html__( 'iTunes Episode Number', 'podcast-helper' ) . '</strong> &mdash; ' . esc_html__( 'Select the episode number (used in combination with the season number field).', 'podcast-helper' ) . '</p>';
	$instructions .= '<p><strong>' . esc_html__( 'iTunes Season Number', 'podcast-helper' ) . '</strong> &mdash; ' . esc_html__( 'Select the season number of the episode (used in combination with the episode number field).', 'podcast-helper' ) . '</p>';
	$instructions .= '<p><strong>' . esc_html__( 'iTunes Episode Title', 'podcast-helper' ) . '</strong> &mdash; ' . esc_html__( 'Select a clear, concise name for the episode (excluding any episode number, season number of main podcast title).', 'podcast-helper' ) . '</p>';
	$instructions .= '<p><strong>' . esc_html__( 'Episode Summary', 'podcast-helper' ) . '</strong> &mdash; ' . esc_html__( 'Select a summary for the episode (defaults to a stripped version of the episode content, if empty).', 'podcast-helper' ) . '</p>';
	$instructions .= '<p><strong>' . esc_html__( 'Explicit Episode', 'podcast-helper' ) . '</strong> &mdash; ' . esc_html__( 'Determines if an episode contains explicit language.', 'podcast-helper' ) . '</p>';
	$instructions .= '<p><strong>' . esc_html__( 'Block Episode', 'podcast-helper' ) . '</strong> &mdash; ' . esc_html__( 'Determines if an episode is hiiden from services like iTunes and Google Play.', 'podcast-helper' ) . '</p>';
	get_current_screen()->add_help_tab( array(
		'id'       => 'podcast-episode-fields',
		'title'    => esc_html__( 'Episode Fields', 'podcast-helper' ),
		'content'  => $instructions,
		'priority' => 80,
	) );

}
add_action( 'add_meta_boxes_episode', 'podcast_helper_meta_box_setup', 10, 1 );

function podcast_helper_additional_page_meta_box_options() {

	if ( apply_filters( 'theme_additional_page_meta_box_options_restrict_template', true ) ) {
		return;
	}

	$theme_options_callback = apply_filters( 'theme_additional_page_meta_box_options_callback', false );
	if ( $theme_options_callback ) {
		add_meta_box(
			'theme-additional-fields',
			esc_html__( 'Additional Page Options', 'podcast-helper' ),
			$theme_options_callback,
			apply_filters( 'theme_additional_page_meta_box_options_post_types', array( 'page', 'post', 'episode', 'product' ) ),
			'normal'
		);
	}

	$theme_options_instructions = apply_filters( 'theme_additional_page_meta_box_options_instructions', false );
	if ( $theme_options_instructions ) {
		get_current_screen()->add_help_tab( array(
			'id'       => 'theme-additional-fields',
			'title'    => esc_html__( 'Additional Options', 'podcast-helper' ),
			'content'  => $theme_options_instructions,
			'priority' => 70,
		) );
	}

}
add_action( 'add_meta_boxes', 'podcast_helper_additional_page_meta_box_options', 10, 1 );

/**
 * Add theme metabox scripts and style.
 */
function podcast_helper_metabox_admin_enqueue_scripts() {

	if ( 'post' !== get_current_screen()->base ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style( 'podcast-helper-metabox-fields', PODCAST_HELPER_PLUGIN_URL . '/assets/css/metabox-fields.css', null, false );
	wp_enqueue_script( 'podcast-helper-metabox-fields', PODCAST_HELPER_PLUGIN_URL . '/assets/js/metabox-fields.js', null, false, true );
}
add_action( 'admin_enqueue_scripts', 'podcast_helper_metabox_admin_enqueue_scripts' );

/**
 * Display shortcode and statistics for the metabox side.
 */
function podcast_helper_meta_box_side_content( $post ) {
	$shortcode_text = '[podcast_episode id="' . $post->ID . '"]';
?>

	<p><em><?php esc_html_e( 'Use the shortcode below to display the audio player of the episode in any context of your site (sidebar, widget, footer or custom page).', 'podcast-helper' ); ?></em></p>
	<p><input type="text" readonly class="large-text" id="episode_shortcode" value="<?php echo esc_attr( $shortcode_text ); ?>"></p>

<?php
	// Allow additional metabox content to be added (used for statistics).
	do_action( 'podcast_helper_episode_meta_box_side_add_content', $post );
}

/**
 * Display fields for the metabox options.
 */
function podcast_helper_metabox_episode_fields() {
	global $post;
	$metabox_id = 'podcast-episode-fields';
	wp_nonce_field( "save_{$metabox_id}", "nonce_{$metabox_id}" );

	$episode_media_file          = get_post_meta( $post->ID, 'episode_audio_file', true );
	$episode_media_file_size     = get_post_meta( $post->ID, 'episode_audio_file_size', true );
	$episode_media_file_duration = get_post_meta( $post->ID, 'episode_audio_file_duration', true );
	$episode_poster              = get_post_meta( $post->ID, 'episode_poster', true );
	$episode_transcript          = get_post_meta( $post->ID, 'episode_transcript', true );
	?>
	<div class="podcast-metabox-field podcast-metabox-field-episode">
		<div class="podcast-metabox-label">
			<label for="episode_media_file"><strong><?php esc_html_e( 'Episode File', 'podcast-helper' ); ?></strong></label>
		</div>
		<div class="podcast-metabox-input">
			<p>
				<input type="url" class="media_url regular-text" id="episode_media_file" name="episode_media_file" value="<?php echo esc_attr( $episode_media_file ); ?>"> <button class="button set-media" data-frame-title="<?php esc_html_e( 'Select Episode File', 'podcast-helper' ); ?>"><?php esc_html_e( 'Select File', 'podcast-helper' ); ?></button>
			</p>
			<p>
				<input type="number" class="media_size" id="episode_media_file_size" name="episode_media_file_size"  value="<?php echo esc_attr( $episode_media_file_size ); ?>"> <span><?php esc_html_e( 'Size (bytes)', 'podcast-helper' ); ?></span>
			</p>
			<p>
				</span> <input type="text" class="media_duration" id="episode_media_file_duration" name="episode_media_file_duration"  value="<?php echo esc_attr( $episode_media_file_duration ); ?>"> <span><?php esc_html_e( 'Duration (h:min:sec)', 'podcast-helper' ); ?></span>
			</p>
			<p class="description no-bottom"><?php esc_html_e( 'Upload the primary media file or paste an URL. The size and direction will be added automatically if the file is not remote, otherwise you should add them manually.', 'podcast-helper' ); ?></p>
		</div>
	</div>

	<div class="podcast-metabox-field podcast-metabox-field-episode"<?php if ( false === strpos( apply_filters( 'podcast_helper_episode_type', 'audio', get_the_ID() ), 'video' ) ) : ?> style="display:none"<?php endif; ?>>
		<div class="podcast-metabox-label">
			<label for="episode_poster"><strong><?php esc_html_e( 'Episode Poster', 'podcast-helper' ); ?></strong></label>
		</div>
		<div class="podcast-metabox-input">
			<p>
				<input type="url" class="image_url regular-text" id="episode_poster" name="episode_poster" value="<?php echo esc_attr( $episode_poster ); ?>"> <button class="button set-image" data-frame-title="<?php esc_html_e( 'Select Episode Poster Image', 'podcast-helper' ); ?>"><?php esc_html_e( 'Select Image', 'podcast-helper' ); ?></button>
			</p>
			<p class="description no-bottom"><?php esc_html_e( 'Upload the poster image used for this episode\'s video or paste an URL here (leave empty if this is not a video episode).', 'podcast-helper' ); ?></p>
		</div>
	</div>

	<div class="podcast-metabox-field podcast-metabox-field-episode">
		<div class="podcast-metabox-label">
			<label for="episode_transcript"><strong><?php esc_html_e( 'Episode Transcript', 'podcast-helper' ); ?></strong></label>
		</div>
		<div class="podcast-metabox-input">
			<p>
				<input type="url" class="transcript_url regular-text" id="episode_transcript" name="episode_transcript" value="<?php echo esc_attr( $episode_transcript ); ?>"> <button class="button set-transcript" data-frame-title="<?php esc_html_e( 'Select Episode Transcript File', 'podcast-helper' ); ?>"><?php esc_html_e( 'Select File', 'podcast-helper' ); ?></button>
			</p>
			<p class="description no-bottom"><?php esc_html_e( 'Upload the transcript file for this episode or paste an URL here.', 'podcast-helper' ); ?></p>
		</div>
	</div>

	<div class="podcast-metabox-field podcast-metabox-field-select">
		<div class="podcast-metabox-label">
			<label for="episode_type"><strong><?php esc_html_e( 'iTunes Episode Type', 'podcast-helper' ); ?></strong></label>
		</div>
		<div class="podcast-metabox-input">
			<?php $episode_type = get_post_meta( $post->ID, 'episode_type', true ); ?>
			<select name="episode_type" id="episode_type">
				<option value=""<?php if ( empty( $episode_type ) ) : ?> selected="selected"<?php endif; ?>></option>
				<option value="full"<?php if ( ! empty( $episode_type ) && 'full' === $episode_type ) : ?> selected="selected"<?php endif; ?>><?php esc_html_e( 'Full', 'podcast-helper' ); ?></option>
				<option value="trailer"<?php if ( ! empty( $episode_type ) && 'trailer' === $episode_type ) : ?> selected="selected"<?php endif; ?>><?php esc_html_e( 'Trailer', 'podcast-helper' ); ?></option>
				<option value="bonus"<?php if ( ! empty( $episode_type ) && 'bonus' === $episode_type ) : ?> selected="selected"<?php endif; ?>><?php esc_html_e( 'Bonus', 'podcast-helper' ); ?></option>
			</select>
		</div>
	</div>

	<div class="podcast-metabox-field podcast-metabox-field-input">
		<div class="podcast-metabox-label">
			<label for="episode_number"><strong><?php esc_html_e( 'iTunes Episode Number', 'podcast-helper' ); ?></strong></label>
		</div>
		<div class="podcast-metabox-input">
			<?php $episode_number = get_post_meta( $post->ID, 'episode_number', true ); ?>
			<input type="number" min="1" id="episode_number" name="episode_number" value="<?php echo esc_attr( $episode_number ); ?>">
			<p class="description"><?php esc_html_e( 'Non-zero integer representing the episode number. Use this tag to specify the recommended order for episodes within a season.', 'podcast-helper' ); ?></p>
		</div>
	</div>

	<div class="podcast-metabox-field podcast-metabox-field-input">
		<div class="podcast-metabox-label">
			<label for="episode_season_number"><strong><?php esc_html_e( 'iTunes Season Number', 'podcast-helper' ); ?></strong></label>
		</div>
		<div class="podcast-metabox-input">
			<?php $episode_season_number = get_post_meta( $post->ID, 'episode_season_number', true ); ?>
			<input type="number" min="1" id="episode_season_number" name="episode_season_number" value="<?php echo esc_attr( $episode_season_number ); ?>">
			<p class="description"><?php esc_html_e( 'Non-zero integer representing the season number for this episode.', 'podcast-helper' ); ?></p>
		</div>
	</div>

	<div class="podcast-metabox-field podcast-metabox-field-input">
		<div class="podcast-metabox-label">
			<label for="episode_title"><strong><?php esc_html_e( 'iTunes Episode Title', 'podcast-helper' ); ?></strong></label>
		</div>
		<div class="podcast-metabox-input">
			<?php $episode_title = get_post_meta( $post->ID, 'episode_title', true ); ?>
			<input type="text" class="regular-text" id="episode_title" name="episode_title" value="<?php echo esc_attr( $episode_title ); ?>">
			<p class="description"><?php esc_html_e( 'A string containing a clear, concise name for this episode. You don\'t have to specify your podcast title, episode number, or season number in this tag.', 'podcast-helper' ); ?></p>
		</div>
	</div>

	<div class="podcast-metabox-field podcast-metabox-field-input">
		<div class="podcast-metabox-label">
			<label for="episode_summary"><strong><?php esc_html_e( 'Episode Summary', 'podcast-helper' ); ?></strong></label>
		</div>
		<div class="podcast-metabox-input">
			<?php $episode_summary = get_post_meta( $post->ID, 'episode_summary', true ); ?>
			<textarea class="large-text" rows="3" id="episode_summary" name="episode_summary"><?php echo esc_textarea( $episode_summary ); ?></textarea>
			<p class="description"><?php esc_html_e( 'An optional string containing the summary of for this episode. The field defaults to a stripped version of the episode content (if it is left empty).', 'podcast-helper' ); ?></p>
		</div>
	</div>

	<?php $episode_explicit = get_post_meta( $post->ID, 'episode_explicit', true ); ?>
	<div class="podcast-metabox-field podcast-metabox-field-checkbox">
		<div class="podcast-metabox-label">
			<label for="episode_explicit"><strong><?php esc_html_e( 'Explicit Episode', 'podcast-helper' ); ?></strong></label>
		</div>
		<div class="podcast-metabox-input">
			<label for="episode_explicit" class="selectit"><input type="checkbox" name="episode_explicit" id="episode_explicit" value="1"<?php checked( ! empty( $episode_explicit ) ); ?>> <?php esc_html_e( 'Indicates whether this episode is explicit', 'podcast-helper' ); ?></label>
		</div>
	</div>

	<?php $episode_block = get_post_meta( $post->ID, 'episode_block', true ); ?>
	<div class="podcast-metabox-field podcast-metabox-field-checkbox">
		<div class="podcast-metabox-label">
			<label for="episode_block"><strong><?php esc_html_e( 'Block Episode', 'podcast-helper' ); ?></strong></label>
		</div>
		<div class="podcast-metabox-input">
			<label for="episode_block" class="selectit"><input type="checkbox" name="episode_block" id="episode_block" value="1"<?php checked( ! empty( $episode_block ) ); ?>> <?php esc_html_e( 'Block this episode from appearing in iTunes and Google Play', 'podcast-helper' ); ?></label>
		</div>
	</div>

	<div class="podcast-metabox-field podcast-metabox-field-wysiwyg">
		<div class="podcast-metabox-label">
			<label for="episode_custom_player"><strong><?php esc_html_e( 'Custom Player', 'podcast-helper' ); ?></strong></label>
		</div>
		<div class="podcast-metabox-input">
			<?php
			$episode_custom_player = get_post_meta( $post->ID, 'episode_custom_player', true );
			wp_editor( $episode_custom_player, 'episode_custom_player', array(
				'media_buttons' => false,
				'textarea_rows' => 3,
				'tinymce'       => false,
				'quicktags'     => false,
			) );
			?>
			<p class="description"><?php esc_html_e( 'Replace the default episode player with a custom embed (optional field, leave empty to display the default episode player).', 'podcast-helper' ); ?></p>
		</div>
	</div>

<?php
}

/**
 * Save fields for the theme metabox options.
 */
function podcast_helper_metabox_episode_fields_save_post( $post_id ) {
	$metabox_id = 'podcast-episode-fields';

	// check if nonce validates.
	if ( ! ( isset( $_POST[ "nonce_{$metabox_id}" ] ) && wp_verify_nonce( sanitize_key( $_POST[ "nonce_{$metabox_id}" ] ), "save_{$metabox_id}" ) ) ) {
		return;
	}

	// update episode_media_file fields.
	if ( isset( $_POST['episode_media_file'] ) ) {
		$episode_media_file = esc_url_raw( wp_unslash( $_POST['episode_media_file'] ) );
		if ( empty( $episode_media_file ) ) {
			delete_post_meta( $post_id, 'episode_audio_file' );
			delete_post_meta( $post_id, 'episode_audio_file_size' );
			delete_post_meta( $post_id, 'episode_audio_file_duration' );
		} else {
			update_post_meta( $post_id, 'episode_audio_file', $episode_media_file );

			if ( isset( $_POST['episode_media_file_size'] ) ) {
				$episode_media_file_size = sanitize_text_field( wp_unslash( $_POST['episode_media_file_size'] ) );
				if ( empty( $episode_media_file_size ) ) {
					delete_post_meta( $post_id, 'episode_audio_file_size' );
				} else {
					update_post_meta( $post_id, 'episode_audio_file_size', $episode_media_file_size );
				}
			}

			if ( isset( $_POST['episode_media_file_duration'] ) ) {
				$episode_media_file_duration = sanitize_text_field( wp_unslash( $_POST['episode_media_file_duration'] ) );
				if ( empty( $episode_media_file_duration ) ) {
					delete_post_meta( $post_id, 'episode_audio_file_duration' );
				} else {
					update_post_meta( $post_id, 'episode_audio_file_duration', $episode_media_file_duration );
				}
			}
		}
	}

	// update episode_poster field.
	if ( isset( $_POST['episode_poster'] ) ) {
		$episode_poster = esc_url_raw( wp_unslash( $_POST['episode_poster'] ) );
		if ( empty( $episode_poster ) ) {
			delete_post_meta( $post_id, 'episode_poster' );
		} else {
			update_post_meta( $post_id, 'episode_poster', $episode_poster );
		}
	}

	// update episode_transcript field.
	if ( isset( $_POST['episode_transcript'] ) ) {
		$episode_transcript = esc_url_raw( wp_unslash( $_POST['episode_transcript'] ) );
		if ( empty( $episode_transcript ) ) {
			delete_post_meta( $post_id, 'episode_transcript' );
		} else {
			update_post_meta( $post_id, 'episode_transcript', $episode_transcript );
		}
	}

	if ( isset( $_POST['episode_type'] ) ) {
		$episode_type = sanitize_text_field( wp_unslash( $_POST['episode_type'] ) );
		if ( empty( $episode_type ) ) {
			delete_post_meta( $post_id, 'episode_type' );
		} else {
			update_post_meta( $post_id, 'episode_type', $episode_type );
		}
	}

	if ( isset( $_POST['episode_number'] ) ) {
		$episode_number = sanitize_text_field( wp_unslash( $_POST['episode_number'] ) );
		if ( empty( $episode_number ) ) {
			delete_post_meta( $post_id, 'episode_number' );
		} else {
			update_post_meta( $post_id, 'episode_number', $episode_number );
		}
	}

	if ( isset( $_POST['episode_season_number'] ) ) {
		$episode_season_number = sanitize_text_field( wp_unslash( $_POST['episode_season_number'] ) );
		if ( empty( $episode_number ) ) {
			delete_post_meta( $post_id, 'episode_season_number' );
		} else {
			update_post_meta( $post_id, 'episode_season_number', $episode_season_number );
		}
	}

	if ( isset( $_POST['episode_title'] ) ) {
		$episode_title = sanitize_text_field( wp_unslash( $_POST['episode_title'] ) );
		if ( empty( $episode_number ) ) {
			delete_post_meta( $post_id, 'episode_title' );
		} else {
			update_post_meta( $post_id, 'episode_title', $episode_title );
		}
	}

	if ( isset( $_POST['episode_summary'] ) ) {
		$episode_summary = sanitize_text_field( wp_unslash( $_POST['episode_summary'] ) );
		if ( empty( $episode_summary ) ) {
			delete_post_meta( $post_id, 'episode_summary' );
		} else {
			update_post_meta( $post_id, 'episode_summary', $episode_summary );
		}
	}

	if ( isset( $_POST['episode_explicit'] ) ) {
		$episode_explicit = sanitize_text_field( wp_unslash( $_POST['episode_explicit'] ) );
		if ( ! empty( $episode_explicit ) ) {
			update_post_meta( $post_id, 'episode_explicit', true );
		} else {
			delete_post_meta( $post_id, 'episode_explicit' );
		}
	} else {
		delete_post_meta( $post_id, 'episode_explicit' );
	}

	if ( isset( $_POST['episode_block'] ) ) {
		$episode_block = sanitize_text_field( wp_unslash( $_POST['episode_block'] ) );
		if ( ! empty( $episode_block ) ) {
			update_post_meta( $post_id, 'episode_block', true );
		} else {
			delete_post_meta( $post_id, 'episode_block' );
		}
	} else {
		delete_post_meta( $post_id, 'episode_block' );
	}

	if ( isset( $_POST['episode_custom_player'] ) ) {
		$episode_custom_player = wp_unslash( $_POST['episode_custom_player'] );
		if ( empty( $episode_custom_player ) ) {
			delete_post_meta( $post_id, 'episode_custom_player' );
		} else {
			update_post_meta( $post_id, 'episode_custom_player', $episode_custom_player );
		}
	}

}
add_action( 'save_post', 'podcast_helper_metabox_episode_fields_save_post' );
