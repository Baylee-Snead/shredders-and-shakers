<?php
/**
 * Import external podcast from an external rss url.
 *
 * @package Podcast Helper
 */

if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
	return;
}

/* Load Importer API */
require_once ABSPATH . 'wp-admin/includes/import.php';

if ( ! class_exists( 'WP_Importer' ) ) {
	$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
	if ( file_exists( $class_wp_importer ) ) {
		require_once $class_wp_importer;
	}
}

function podcast_feed_cache_lifetime( $seconds ) {
	// change the default feed cache recreation period to 60 seconds (1 min)
	return 60;
}

if ( class_exists( 'WP_Importer' ) && ! class_exists( 'Podcast_RSS_Import' ) ) {

	if ( ! defined( 'SIMPLEPIE_NAMESPACE_GOOGLE_PLAY' ) ) {
		define( 'SIMPLEPIE_NAMESPACE_GOOGLE_PLAY', 'http://www.google.com/schemas/play-podcasts/1.0' );
	}

	/**
	 * Podcast RSS Importer
	 *
	 * Will process a RSS feed for importing episodes into WordPress.
	 */
	class Podcast_RSS_Import extends WP_Importer {

		/**
		 * Dispatch the import process depending on th current step.
		 */
		public function dispatch() {
			if ( empty( $_GET['step'] ) ) {
				$step = 0;
			} else {
				$step = (int) $_GET['step'];
			}
			$this->header();
			switch ( $step ) {
				case 0:
					$this->greet();
					break;
				case 1:
					$this->import();
					break;
			}
			$this->footer();
		}

		/**
		 * Display header section.
		 */
		private function header() {
			echo '<div class="wrap">';
			echo '<h2>' . esc_html__( 'Import Podcast RSS', 'podcast-helper' ) . '</h2>';
		}

		/**
		 * Display header section.
		 */
		private function footer() {
			echo '</div>';
		}

		/**
		 * Display greating for the first import step.
		 */
		private function greet() {
			echo '<div class="narrow">';
			echo '<p>' . esc_html__( 'This importer allows you to extract episodes from an external RSS feed into your WordPress site. Paste the URL link into the field below, check the additional options and click Import RSS.', 'podcast-helper' ) . '</p>';
			echo '<form enctype="multipart/form-data" action="admin.php?import=podcast-rss&amp;step=1" method="post" name="import-podcast-feed">';
			wp_nonce_field( 'podcast-import-rss' );
			echo '<table class="form-table"><tbody>';
			echo '<tr><th scope="row">' . esc_html__( 'URL Link', 'podcast-helper' ) . '</th><td><input type="url" name="podcast_feed_url" id="podcast_feed_url" size="60" class="regular-text" required><p class="description"><label for="podcast_feed_url">' . esc_html__( 'URL link to external RSS feed.', 'podcast-helper' ) . '</label></p></td></tr>';
			echo '<tr><th scope="row">' . esc_html__( 'Category', 'podcast-helper' ) . '</th><td>';
			echo wp_dropdown_categories( array(
				'hide_empty'   => 0,
				'echo'         => 0,
				'name'         => 'podcast_import_category',
				'class'        => 'regular-text',
				'hierarchical' => 1,
				'orderby'      => 'name',
				'selected'     => get_option( 'default_category' ),
			) );
			echo '<p class="description"><label for="podcast_import_episode_category">' . esc_html__( 'Select the category assigned to the new imported episodes.', 'podcast-helper' ) . '</label></p></td></tr>';
			echo '<tr><th scope="row">' . esc_html__( 'Attachments', 'podcast-helper' ) . '</th><td><label for="podcast_import_attachments"><input name="podcast_import_attachments" type="checkbox" id="podcast_import_attachments" value="0"> ' . esc_html__( 'Download and import each episode image', 'podcast-helper' ) . '</label><p class="description"><label for="podcast_import_attachments">' . esc_html__( 'Have featured images attached to the new imported episodes (make sure you don\'t have limitations like memory_limit, max_execution_time, post_max_size or upload_max_filesize from your hosting provider)', 'podcast-helper' ) . '</label></p></td></tr>';
			echo '<tr><th scope="row">' . esc_html__( 'Podcast Settings', 'podcast-helper' ) . '</th><td><label for="podcast_import_settings"><input name="podcast_import_settings" type="checkbox" id="podcast_import_settings" value="0"> ' . esc_html__( 'Include podcast settings as well', 'podcast-helper' ) . '</label><p class="description"><label for="podcast_import_settings">' . esc_html__( 'Import podcast settings like title, author, description, cover, categories, language, etc.', 'podcast-helper' ) . '</label></p></td></tr>';
			echo '</tbody></table>';
			echo '<input type="submit" name="submit" id="submit" class="button button-primary" value="' . esc_html__( 'Import RSS', 'podcast-helper' ) . '">';
			echo '</form>';
			echo '</div>';
		}

		/**
		 * Process the actual import (second step of the import).
		 */
		public function import() {
			if ( ! check_admin_referer( 'podcast-import-rss' ) ) {
				wp_die( '<p>' . esc_html__( 'URL link is empty.', 'podcast-helper' ) . '<p>', '', array( 'back_link' => true ) );
				return false;
			}

			return self::run_import( sanitize_text_field( wp_unslash( $_POST['podcast_feed_url'] ) ), sanitize_text_field( $_POST['podcast_import_category'] ), isset( $_POST['podcast_import_attachments'] ), isset( $_POST['podcast_import_settings'] ), false, true );
		}

		private static function episode_exists( $title, $episode_media ) {
			global $wpdb;
			$post_title = wp_unslash( sanitize_post_field( 'post_title', $title, 0, 'db' ) );
			$args       = array();
			$query      = "SELECT p.ID FROM $wpdb->posts p";
			$query     .= " INNER JOIN $wpdb->postmeta pm ON (p.ID = pm.post_id)";
			$query     .= ' WHERE p.post_type = %s';
			$query     .= ' AND ( p.post_title = %s OR ( pm.meta_key = \'episode_audio_file\' AND pm.meta_value = %s ) )';
			$args[]     = 'episode';
			$args[]     = $post_title;
			$args[]     = $episode_media;
			return (int) $wpdb->get_var( $wpdb->prepare($query, $args) );
		}

		/**
		 * Run the import process (can be called by a cron job).
		 */
		public static function run_import( $feed_url, $import_category = 0, $import_attachments = false, $import_settings = false, $cron_job = true, $verbose = false ) {

			if ( $verbose ) {
				echo '<p>' . esc_html__( 'Fetching the external feed and parsing it...', 'podcast-helper' ) . '<p>';
			}

			/* temporary decrease the transient lifetime, instead of the default 12 hours */
			add_filter( 'wp_feed_cache_transient_lifetime' , 'podcast_feed_cache_lifetime' );
			/* use SimplePie and FeedCache for retrieval and parsing of a feed. */
			$feed = fetch_feed( esc_url( $feed_url ) );
			/* restore the default lifetime transient */
			remove_filter( 'wp_feed_cache_transient_lifetime' , 'podcast_feed_cache_lifetime' );

			if ( is_wp_error( $feed ) ) {
				if ( $verbose ) {
					/* translators: 1: error message */
					wp_die( '<p>' . sprintf( esc_html__( 'Error occurred importing the feed: %1$s.', 'podcast-helper' ), esc_html( $feed->get_error_message() ) ) . '<p>', '', array( 'back_link' => true ) );
				}
				return false;
			}

			/* Import podcast settings, if required. */
			if ( $import_settings ) {
				if ( $verbose ) {
					echo '<p>' . esc_html__( 'Import podcast settings...', 'podcast-helper' ) . '<p>';
				}
				// import title.
				$tmp_title = $feed->get_title();
				if ( ! empty( $tmp_title ) ) {
					update_option( 'podcast_title', $tmp_title );
				}
				// import subtitle (iTunes only).
				$podcast_subtitle = $feed->get_channel_tags( SIMPLEPIE_NAMESPACE_ITUNES, 'subtitle' );
				if ( ! empty( $podcast_subtitle ) && isset( $podcast_subtitle[0] ) && isset( $podcast_subtitle[0]['data'] ) ) {
					update_option( 'podcast_subtitle', $podcast_subtitle[0]['data'] );
				}
				// import author (iTunes only).
				$podcast_author = $feed->get_channel_tags( SIMPLEPIE_NAMESPACE_ITUNES, 'author' );
				if ( ! empty( $podcast_author ) && isset( $podcast_author[0] ) && isset( $podcast_author[0]['data'] ) ) {
					update_option( 'podcast_author', $podcast_author[0]['data'] );
				}
				// import description.
				$tmp_description = $feed->get_description();
				if ( ! empty( $tmp_description ) ) {
					update_option( 'podcast_description', $tmp_description );
				}
				// import cover image.
				if ( $feed->get_image_url() ) {
					$podcast_cover_image = $feed->get_image_url();
				} else {
					$podcast_cover = $feed->get_channel_tags( SIMPLEPIE_NAMESPACE_ITUNES, 'image' );
					if ( ! empty( $podcast_cover ) && isset( $podcast_cover[0] ) && isset( $podcast_cover[0]['data'] ) ) {
						$podcast_cover_image = $podcast_cover[0]['data'];
					} else {
						$podcast_cover = $feed->get_channel_tags( SIMPLEPIE_NAMESPACE_GOOGLE_PLAY, 'image' );
						if ( ! empty( $podcast_cover ) && isset( $podcast_cover[0] ) && isset( $podcast_cover[0]['data'] ) ) {
							$podcast_cover_image = $podcast_cover[0]['data'];
						}
					}
				}
				if ( isset( $podcast_cover_image ) ) {
					// download the image in the Media Library and use this as the new podcast cover.
					if ( $import_attachments ) {
						if ( ! function_exists( 'media_handle_sideload' ) ) {
							require_once ABSPATH . 'wp-admin/includes/image.php';
							require_once ABSPATH . 'wp-admin/includes/file.php';
							require_once ABSPATH . 'wp-admin/includes/media.php';
						}
						$file_array = array();
						preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $podcast_cover_image, $matches );
						$file_array['name'] = basename( $matches[0] );

						// check if attachment already exists.
						$attachment_args  = array(
							'posts_per_page' => 1,
							'post_type'      => 'attachment',
							'post_mime_type' => 'image',
							'name'           => basename( $matches[0], '.' . $matches[1] ),
						);
						$attachment_check = new Wp_Query( $attachment_args );
						if ( $attachment_check->have_posts() ) {
							if ( $verbose ) {
								echo '<p><em>' . esc_html( $file_array['name'] ) . '</em> already exists in the Media Library...</p>';
							}
							$podcast_cover_image = wp_get_attachment_url( $attachment_check->post->ID );
						} else {
							if ( $verbose ) {
								echo '<p>Downloading the <em>' . esc_html( $file_array['name'] ) . '</em> file...</p>';
							}
							$tmp                    = download_url( $podcast_cover_image );
							$file_array['tmp_name'] = $tmp;
							if ( is_wp_error( $tmp ) ) {
								if ( $verbose ) {
									/* translators: 1: file name, 2: error message */
									echo '<p>' . sprintf( esc_html__( 'There was an error downloading %1$s: %2$s...', 'podcast-helper' ), esc_html( $file_array['name'] ), esc_html( $tmp->get_error_message() ) ) , '</p>';
								}
								$file_array['tmp_name'] = '';
							} else {
								$attachment_id = media_handle_sideload( $file_array, 0, basename( $matches[0], '.' . $matches[1] ) );
								if ( is_wp_error( $attachment_id ) ) {
									/* translators: 1: file name, 2: error message */
									if ( $verbose ) {
										echo '<p>' . sprintf( esc_html__( 'There was an error uploading %1$s: %2$s...', 'podcast-helper' ), esc_html( $file_array['name'] ), esc_html( $attachment_id->get_error_message() ) ) , '</p>';
									}
									unlink( $file_array['tmp_name'] ); // We have to unlink the unwanted file, or else it will remain unused.
								} else {
									$podcast_cover_image = wp_get_attachment_url( $attachment_id );
								}
							}
						}
					}
					update_option( 'podcast_cover', $podcast_cover_image );
				}
				// import owner details.
				$podcast_owner = $feed->get_channel_tags( SIMPLEPIE_NAMESPACE_ITUNES, 'owner' );
				if ( ! empty( $podcast_owner ) && isset( $podcast_owner[0] ) && isset( $podcast_owner[0]['child'] ) && isset( $podcast_owner[0]['child'][ SIMPLEPIE_NAMESPACE_ITUNES ] ) ) {
					$podcast_owner = $podcast_owner[0]['child'][ SIMPLEPIE_NAMESPACE_ITUNES ];
					if ( isset( $podcast_owner['name'] ) && isset( $podcast_owner['name'][0] ) && isset( $podcast_owner['name'][0]['data'] ) ) {
						update_option( 'podcast_owner_name', $podcast_owner['name'][0]['data'] );
					}
					if ( isset( $podcast_owner['email'] ) && isset( $podcast_owner['email'][0] ) && isset( $podcast_owner['email'][0]['data'] ) ) {
						update_option( 'podcast_owner_email', $podcast_owner['email'][0]['data'] );
					}
				} else {
					$podcast_author = $feed->get_channel_tags( SIMPLEPIE_NAMESPACE_GOOGLE_PLAY, 'author' );
					if ( ! empty( $podcast_author ) && isset( $podcast_author[0] ) && isset( $podcast_author[0]['data'] ) ) {
						update_option( 'podcast_author', $podcast_author[0]['data'] );
					}
					$podcast_owner = $feed->get_channel_tags( SIMPLEPIE_NAMESPACE_GOOGLE_PLAY, 'email' );
					if ( ! empty( $podcast_owner ) && isset( $podcast_owner[0] ) && isset( $podcast_owner[0]['data'] ) ) {
						update_option( 'podcast_owner_email', $podcast_owner[0]['data'] );
					}
				}
				// import language.
				$tmp_lang = $feed->get_language();
				if ( ! empty( $tmp_lang ) ) {
					update_option( 'podcast_language', $tmp_lang );
				}
				// import copyright statement.
				$tmp_copyright = $feed->get_copyright();
				if ( ! empty( $tmp_copyright ) ) {
					update_option( 'podcast_copyright', $tmp_copyright );
				}
				// import explicit option.
				$podcast_explicit = $feed->get_channel_tags( SIMPLEPIE_NAMESPACE_ITUNES, 'explicit' );
				if ( ! empty( $podcast_explicit ) && isset( $podcast_explicit[0] ) && isset( $podcast_explicit[0]['data'] ) ) {
					if ( 'yes' === $podcast_explicit[0]['data'] ) {
						update_option( 'podcast_explicit', 'on' );
					} else {
						delete_option( 'podcast_explicit' );
					}
				} else {
					$podcast_explicit = $feed->get_channel_tags( SIMPLEPIE_NAMESPACE_GOOGLE_PLAY, 'explicit' );
					if ( ! empty( $podcast_explicit ) && isset( $podcast_explicit[0] ) && isset( $podcast_explicit[0]['data'] ) ) {
						if ( 'Yes' === $podcast_explicit[0]['data'] ) {
							update_option( 'podcast_explicit', 'on' );
						} else {
							delete_option( 'podcast_explicit' );
						}
					}
				}
				// import complete option.
				$podcast_complete = $feed->get_channel_tags( SIMPLEPIE_NAMESPACE_ITUNES, 'complete' );
				if ( ! empty( $podcast_complete ) && isset( $podcast_complete[0] ) && isset( $podcast_complete[0]['data'] ) ) {
					if ( 'yes' === $podcast_complete[0]['data'] ) {
						update_option( 'podcast_complete', 'on' );
					} else {
						delete_option( 'podcast_complete' );
					}
				}
				// import episode order option.
				$podcast_consume_order = $feed->get_channel_tags( SIMPLEPIE_NAMESPACE_ITUNES, 'type' );
				if ( ! empty( $podcast_consume_order ) && isset( $podcast_consume_order[0] ) && isset( $podcast_consume_order[0]['data'] ) ) {
					update_option( 'podcast_consume_order', $podcast_consume_order[0]['data'] );
				}
				// import iTunes categories.
				$podcast_categories = $feed->get_channel_tags( SIMPLEPIE_NAMESPACE_ITUNES, 'category' );
				if ( ! empty( $podcast_categories ) && isset( $podcast_categories[0] ) ) {
					$category_index = 0;
					foreach ( $podcast_categories as $category_data ) {
						$category_value = '';
						if ( isset( $category_data['attribs'] ) && count( $category_data['attribs'] ) > 0 ) {
							$attribs = array_values( $category_data['attribs'] )[0];
							if ( count( $attribs ) > 0 && isset( $attribs['text'] ) ) {
								$category_value = $attribs['text'];
								$category_index++;
							}
						}
						if ( ! empty( $category_value ) && isset( $category_data['child'] ) && count( $category_data['child'] ) > 0 ) {
							$child_data = array_values( $category_data['child'] )[0];
							if ( count( $child_data ) > 0 && isset( $child_data['category'] ) && count( $child_data['category'] ) > 0 ) {
								$subcategory_data = $child_data['category'][0];
								if ( isset( $subcategory_data['attribs'] ) && count( $subcategory_data['attribs'] ) > 0 ) {
									$sub_attribs = array_values( $subcategory_data['attribs'] )[0];
									if ( count( $sub_attribs ) > 0 && isset( $sub_attribs['text'] ) ) {
										$category_value = $category_value . '|' . $sub_attribs['text'];
									}
								}
							}
						}
						if ( ! empty( $category_value ) ) {
							switch ( $category_index ) {
								case 1:
									update_option( 'podcast_primary_category', $category_value );
									delete_option( 'podcast_secondary_category' );
									delete_option( 'podcast_tertiary_category' );
									break;

								case 2:
									update_option( 'podcast_secondary_category', $category_value );
									delete_option( 'podcast_tertiary_category' );
									break;

								case 3:
									update_option( 'podcast_tertiary_category', $category_value );
									break;
							}
						}
					}
				}
			}

			if ( $verbose && $feed->get_item_quantity() > 0 ) {
				/* translators: 1: no. of items */
				echo '<p>' . sprintf( _n( 'Checking %s RSS item...', 'Checking %s RSS items...', $feed->get_item_quantity(), 'podcast-helper' ), '<strong>' . esc_html( $feed->get_item_quantity() ) . '</strong>' ) . '<p>';
			}

			// store how many items are inserted, updated or skipped.
			$item_inserted_count = 0;
			$item_updated_count  = 0;
			$item_skipped_count  = 0;
			$items               = $feed->get_items();
			foreach ( $items as $item ) {
				$episode_audio_file = '';
				$enclosures = $item->get_enclosures();
				if ( sizeof( $enclosures ) > 1 ) {
					foreach ( $enclosures as $enclosure ) {
						$enclosure_type = $enclosure->get_type();
						if ( $enclosure_type && stripos( $enclosure_type, 'audio' ) !== false) {
							$episode_audio_file = $enclosure->get_link();
						}
					}
				} else {
					$enclosure          = $item->get_enclosure();
					$episode_audio_file = $enclosure->get_link();
				}
				$episode_audio_file = esc_sql( str_replace( '?ref=feed', '', $episode_audio_file ) );
				$post_title         = esc_sql( $item->get_title() );

				// ignore items that have no enclosure.
				if ( empty( $episode_audio_file ) ) {
					if ( $verbose ) {
						/* translators: 1: post title */
						echo '<p>' . sprintf( esc_html__( 'No enclosure tag found in %1$s...', 'podcast-helper' ), '<em>' . esc_html( $post_title ) . '</em>' ) . '<p>';
					}
					$item_skipped_count++;
					continue;
				}

				$meta_input = array( 'episode_audio_file' => $episode_audio_file );
				if ( ! empty( $enclosure->length ) ) {
					$meta_input['episode_audio_file_size'] = (int) $enclosure->length;
				}
				$file_duration_secs          = abs( round( $enclosure->duration ) );
				$hours                       = floor( $file_duration_secs / 3600 ) . ':';
				$minutes                     = substr( '00' . floor( ( $file_duration_secs / 60 ) % 60 ), -2 ) . ':';
				$seconds                     = substr( '00' . $file_duration_secs % 60, -2 );
				$episode_audio_file_duration = ltrim( $hours . $minutes . $seconds, '0:0' );
				if ( ! empty( $episode_audio_file_duration ) && '0' !== $episode_audio_file_duration ) {
					$meta_input['episode_audio_file_duration'] = $episode_audio_file_duration;
				}
				$episode_type = $item->get_item_tags( SIMPLEPIE_NAMESPACE_ITUNES, 'episodeType' );
				if ( ! empty( $episode_type ) && isset( $episode_type[0] ) && isset( $episode_type[0]['data'] ) ) {
					$meta_input['episode_type'] = $episode_type[0]['data'];
				}
				$episode_number = $item->get_item_tags( SIMPLEPIE_NAMESPACE_ITUNES, 'episode' );
				if ( ! empty( $episode_number ) && isset( $episode_number[0] ) && isset( $episode_number[0]['data'] ) ) {
					$meta_input['episode_number'] = $episode_number[0]['data'];
				}
				$episode_season_number = $item->get_item_tags( SIMPLEPIE_NAMESPACE_ITUNES, 'season' );
				if ( ! empty( $episode_season_number ) && isset( $episode_season_number[0] ) && isset( $episode_season_number[0]['data'] ) ) {
					$meta_input['episode_season_number'] = $episode_season_number[0]['data'];
				}
				$episode_title = $item->get_item_tags( SIMPLEPIE_NAMESPACE_ITUNES, 'title' );
				if ( ! empty( $episode_title ) && isset( $episode_title[0] ) && isset( $episode_title[0]['data'] ) ) {
					$meta_input['episode_title'] = $episode_title[0]['data'];
				}
				$episode_explicit = $item->get_item_tags( SIMPLEPIE_NAMESPACE_ITUNES, 'explicit' );
				if ( ! empty( $episode_explicit ) && isset( $episode_explicit[0] ) && isset( $episode_explicit[0]['data'] ) && 'yes' === $episode_explicit[0]['data'] ) {
					$meta_input['episode_explicit'] = 1;
				} else {
					$episode_explicit = $item->get_item_tags( SIMPLEPIE_NAMESPACE_GOOGLE_PLAY, 'explicit' );
					if ( ! empty( $episode_explicit ) && isset( $episode_explicit[0] ) && isset( $episode_explicit[0]['data'] ) && 'Yes' === $episode_explicit[0]['data'] ) {
						$meta_input['episode_explicit'] = 1;
					}
				}
				$episode_block = $item->get_item_tags( SIMPLEPIE_NAMESPACE_ITUNES, 'block' );
				if ( ! empty( $episode_block ) && isset( $episode_block[0] ) && isset( $episode_block[0]['data'] ) && 'yes' === $episode_block[0]['data'] ) {
					$meta_input['episode_block'] = 1;
				} else {
					$episode_block = $item->get_item_tags( SIMPLEPIE_NAMESPACE_GOOGLE_PLAY, 'explicit' );
					if ( ! empty( $episode_block ) && isset( $episode_block[0] ) && isset( $episode_block[0]['data'] ) && 'yes' === $episode_block[0]['data'] ) {
						$meta_input['episode_block'] = 1;
					}
				}

				$gm_date       = $item->get_gmdate();
				$post_date_gmt = strtotime( $gm_date );
				$post_date_gmt = gmdate( 'Y-m-d H:i:s', $post_date_gmt );
				$post_date     = get_date_from_gmt( $post_date_gmt );
				$post_content  = esc_sql( str_replace( "\n", '', $item->get_content() ) );
				$post_excerpt  = esc_sql( $item->get_description() );

				$post_id = self::episode_exists( $post_title, $episode_audio_file );
				if ( empty( $post_id ) ) {
					// insert new post.
					$guid        = substr( esc_sql( $item->get_id() ), 0, 250 );
					$post_status = 'publish';
					$post_type   = 'episode';
					if ( $import_category ) {
						if ( ! is_int( $import_category ) ) {
							$category_obj = get_category_by_slug( $import_category );
							if ( $category_obj ) {
								$import_category = $category_obj->term_id;
							}
						}
						$post_category = array( $import_category );
					}
					$post        = compact( 'post_title', 'post_date', 'post_date_gmt', 'post_content', 'post_excerpt', 'post_status', 'post_type', 'post_category', 'guid', 'meta_input' );
					$new_post_id = wp_insert_post( apply_filters( 'podcast_helper_import_before_insert_post', $post ), true );
					if ( $new_post_id && ! is_wp_error( $new_post_id ) ) {
						if ( $verbose ) {
							/* translators: 1: post title */
							echo '<p>' . sprintf( esc_html__( 'Adding the %1$s episode...', 'podcast-helper' ), '<em>' . esc_html( $post_title ) . '</em>' ) . '<p>';
						}
						$item_inserted_count++;
					} else {
						if ( $verbose ) {
							/* translators: 1: post title, 2: error message */
							echo '<p>' . sprintf( esc_html__( 'Error occured while inserting the %1$s episode: %2$s...', 'podcast-helper' ), '<em>' . esc_html( $post_title ) . '</em>', esc_html( $new_post_id->get_error_message() ) ) . '<p>';
						}
					}
				}
				// download attachment to the Media Library and assign it to the created/updated post.
				if ( isset( $new_post_id ) && ! is_wp_error( $new_post_id ) && $import_attachments ) {
					$item_image_data = $item->get_item_tags( SIMPLEPIE_NAMESPACE_ITUNES, 'image' );
					if ( ! empty( $item_image_data ) && isset( $item_image_data[0] ) && isset( $item_image_data[0]['attribs'] ) && isset( $item_image_data[0]['attribs'][''] ) && isset( $item_image_data[0]['attribs']['']['href'] ) ) {
						$item_image = $item_image_data[0]['attribs']['']['href'];
					} else {
						$item_image_data = $item->get_item_tags( SIMPLEPIE_NAMESPACE_GOOGLE_PLAY, 'image' );
						if ( ! empty( $item_image_data ) && isset( $item_image_data[0] ) && isset( $item_image_data[0]['attribs'] ) && isset( $item_image_data[0]['attribs'][''] ) && isset( $item_image_data[0]['attribs']['']['href'] ) ) {
							$item_image = $item_image_data[0]['attribs']['']['href'];
						}
					}
					if ( isset( $item_image ) ) {
						if ( ! function_exists( 'media_handle_sideload' ) ) {
							require_once( ABSPATH . 'wp-admin/includes/image.php' );
							require_once( ABSPATH . 'wp-admin/includes/file.php' );
							require_once( ABSPATH . 'wp-admin/includes/media.php' );
						}
						$file_array = array();
						preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $item_image, $matches );
						$file_array['name'] = basename( $matches[0] );
						// check if attachment already exists.
						$attachment_args  = array(
							'posts_per_page' => 1,
							'post_type'      => 'attachment',
							'post_mime_type' => 'image',
							'name'           => basename( $matches[0], '.' . $matches[1] ),
						);
						$attachment_check = new Wp_Query( $attachment_args );
						if ( $attachment_check->have_posts() ) {
							if ( $verbose ) {
								echo '<p><em>' . esc_html( $file_array['name'] ) . '</em> already exists in the Media Library...</p>';
							}
							set_post_thumbnail( $new_post_id, $attachment_check->post->ID );
							continue;
						} else {
							if ( $verbose ) {
								echo '<p>Downloading the <em>' . esc_html( $file_array['name'] ) . '</em> file...</p>';
							}
						}
						$tmp                    = download_url( $item_image );
						$file_array['tmp_name'] = $tmp;
						if ( is_wp_error( $tmp ) ) {
							if ( $verbose ) {
								/* translators: 1: filename, 2: error message */
								echo '<p>' . sprintf( esc_html__( 'There was an error downloading %1$s: %2$s...', 'podcast-helper' ), esc_html( $file_array['name'] ), esc_html( $tmp->get_error_message() ) ) , '</p>';
							}
							$file_array['tmp_name'] = '';
							continue;
						}
						$attachment_id = media_handle_sideload( $file_array, $new_post_id, basename( $matches[0], '.' . $matches[1] ) );
						if ( is_wp_error( $attachment_id ) ) {
							if ( $verbose ) {
								/* translators: 1: filename, 2: error message */
								echo '<p>' . sprintf( esc_html__( 'There was an error uploading %1$s: %2$s...', 'podcast-helper' ), esc_html( $file_array['name'] ), esc_html( $attachment_id->get_error_message() ) ) , '</p>';
							}
							unlink( $file_array['tmp_name'] ); // We have to unlink the unwanted file, or else it will remain unused.
							continue;
						}
						set_post_thumbnail( $new_post_id, $attachment_id );
					}
				}
			}
			$results = '';
			if ( $item_inserted_count > 0 ) {
				/* translators: no. of items inserted */
				$results .= sprintf( esc_html( _n( '%s episode', '%s episodes', $item_inserted_count, 'podcast-helper' ) ), '<strong>' . $item_inserted_count . '</strong>' ) . ' ' . __( 'added', 'podcast-helper' ) . ', ';
				wp_cache_flush();
			}
			if ( $item_updated_count > 0 ) {
				/* translators: no. of items updated */
				$results .= sprintf( esc_html( _n( '%s episode', '%s episodes', $item_updated_count, 'podcast-helper' ) ), '<strong>' . $item_updated_count . '</strong>' ) . ' ' . __( 'updated', 'podcast-helper' ) . ', ';
			}
			if ( $item_skipped_count > 0 ) {
				/* translators: no. of items skipped */
				$results .= sprintf( esc_html( _n( '%s episode', '%s episodes', $item_skipped_count, 'podcast-helper' ) ), '<strong>' . $item_skipped_count . '</strong>' ) . ' ' . __( 'skipped', 'podcast-helper' );
			}
			if ( empty( $results ) ) {
				$results = esc_html__( 'no episodes were found', 'podcast-helper' );
			}
			if ( $verbose ) {
				/* translators: result string */
				echo '<p>' . sprintf( esc_html__( 'Import finished: %s.', 'podcast-helper' ), rtrim( $results, ', ' ) ) . '<p>'; // sanitized above.
				echo '<p><a href="' . esc_url( admin_url( 'edit.php?post_type=episode' ) ) . '">' . esc_html__( 'Browse Episodes', 'podcast-helper' ) . '</a> <a href="' . esc_url( admin_url( 'edit.php?post_type=episode&page=podcast_settings' ) ) . '">' . esc_html__( 'Podcast Settings', 'podcast-helper' ) . '</a><p>';
			}
			return true;
		}

	}

	$podcast_rss_import = new Podcast_RSS_Import();

	register_importer( 'podcast-rss', __( 'Podcast RSS Feed', 'podcast-helper' ), __( 'Import podcast episodes and settings from an RSS feed.', 'podcast-helper' ), array( $podcast_rss_import, 'dispatch' ) );

} // class_exists( 'WP_Importer' )
