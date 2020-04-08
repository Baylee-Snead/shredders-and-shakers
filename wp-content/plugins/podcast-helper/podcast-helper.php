<?php
/**
 * Plugin Name: Podcast Helper
 * Plugin URI: http://themeforest.net/user/liviu_cerchez
 * Description: Helper plugin for the podcast theme (includes episode post type and other useful podcast features)
 * Version: 1.2
 * Author: liviu_cerchez
 * Author URI: http://themeforest.net/user/liviu_cerchez
 * Requires at least: 4.9
 * Tested up to: 5.3

 * Text Domain: podcast-helper
 * Domain Path: /languages/

 * @package Podcast Helper
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'PODCAST_HELPER_PLUGIN_URL' ) ) {
	define( 'PODCAST_HELPER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'PODCAST_HELPER_PLUGIN_PATH' ) ) {
	define( 'PODCAST_HELPER_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

/* Add one-click-import option */
if ( is_admin() ) {
	require_once 'includes/demo-importer/class-podcast-demo-data-import.php';
}

/* Add RSS importer */
if ( is_admin() ) {
	require_once 'includes/rss-importer/class-podcast-rss-import.php';
}

/**
 * Add custom episode post type and feed
 */
function podcast_helper_register_custom_posts() {

	if ( ! post_type_exists( 'episode' ) ) {

		$labels = array(
			'name'          => apply_filters( 'podcast_helper_episode_custom_type_name', esc_html__( 'Episodes', 'podcast-helper' ) ),
			'singular_name' => apply_filters( 'podcast_helper_episode_custom_type_singular_name', esc_html__( 'Episode', 'podcast-helper' ) ),
			'add_new_item'  => apply_filters( 'podcast_helper_episode_custom_type_add_new_item', esc_html__( 'Add New Episode', 'podcast-helper' ) ),
			'edit_item'     => apply_filters( 'podcast_helper_episode_custom_type_edit_item', esc_html__( 'Edit Episode', 'podcast-helper' ) ),
			'view_item'     => apply_filters( 'podcast_helper_episode_custom_type_view_item', esc_html__( 'View Episode', 'podcast-helper' ) ),
			'search_items'  => apply_filters( 'podcast_helper_episode_custom_type_search_items', esc_html__( 'Search Episodes', 'podcast-helper' ) ),
		);

		register_post_type( 'episode',
			array(
				'labels'        => $labels,
				'supports'      => array( 'title', 'excerpt', 'editor', 'page-attributes', 'thumbnail', 'custom-fields', 'comments', 'author', 'excerpt', 'revisions', 'publicize' ),
				'rewrite'       => array(
					'slug'  => apply_filters( 'podcast_helper_episode_custom_type_slug', 'episode' ),
					'feeds' => true,
				),
				'taxonomies'    => array( 'category', 'post_tag' ),
				'public'        => true,
				'query_var'     => true,
				'has_archive'   => true,
				'menu_position' => 22,
				'menu_icon'     => 'dashicons-microphone',
				'show_in_rest'  => true,
			)
		);
	}

	add_feed( apply_filters( 'podcast_helper_feed_slug', 'podcast' ), 'podcast_helper_episodes_render_feed' );

}
add_action( 'init', 'podcast_helper_register_custom_posts', 5 );

/**
 * Default function for returning the URL for each episode media file.
 *
 * This will be used with a filter 'podcast_helper_episode_media_url' to create a custom permalink for media files and offer the ability to collect statistics.
 */
function podcast_helper_episode_download_url( $post_id = 0, $referrer = '' ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$file = get_post_meta( $post_id, 'episode_audio_file', true );

	// Get download link based on permalink structure.
	if ( get_option( 'permalink_structure' ) && current_theme_supports( 'podcast-statistics' ) ) {
		$episode = get_post( $post_id );
		$ext     = substr( strrchr( $file, '.' ), 1 );
		if ( ! $ext ) {
			$episode_type = apply_filters( 'podcast_helper_episode_type', 'audio', $post_id );
			if ( 'audio' === $episode_type ) {
				$ext = 'mp3';
			} elseif ( 'video' === $episode_type ) {
				$ext = 'mp4';
			}
		}
		if ( 'player' == $referrer ) {
			$link = home_url() . '/episode-player/' . $post_id . '/' . $episode->post_name . '.' . $ext;
		} else {
			$link = home_url() . '/download-episode/' . $post_id . '/' . $episode->post_name . '.' . $ext;
		}
	} else {
		// serve direct file, as we don't have a choice (can't track statistics).
		$link = $file;
	}

	// Allow for dyamic referrer.
	$referrer = apply_filters( 'podcast_helper_download_referrer', $referrer, $post_id );
	if ( $referrer && 'player' != $referrer ) {
		$link = add_query_arg( array( 'ref' => $referrer ), $link );
	}

	return esc_url( $link );
}
add_filter( 'podcast_helper_episode_media_url', 'podcast_helper_episode_download_url', 10, 2 );

/**
 * Make sure we have a proper permalink structure.
 *
 * This option offers the advantage to hide the actual direct URL and track data for statistics.
 */
function podcast_helper_episodes_setup_permastruct() {
	// Episode download URL.
	add_rewrite_rule( '^download-episode/([^/]*)/([^/]*)/?', 'index.php?podcast_episode=$matches[1]&podcast_slug=$matches[2]', 'top' );
	add_rewrite_rule( '^episode-player/([^/]*)/([^/]*)/?', 'index.php?podcast_episode=$matches[1]&podcast_slug=$matches[2]&podcast_ref=player', 'top' );

	// Custom query variables
	add_rewrite_tag( '%podcast_episode%', '([^&]+)' );
	add_rewrite_tag( '%podcast_slug%', '([^&]+)' );
	add_rewrite_tag( '%podcast_ref%', '([^&]+)' );
}
add_action( 'init', 'podcast_helper_episodes_setup_permastruct', 10 );

/**
 * Flush rewrite rules when plugin is either activated or deactivated.
 */
function podcast_helper_flush_rewrites() {
	podcast_helper_register_custom_posts();
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'podcast_helper_flush_rewrites' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/**
 * Download podcast episode.
 */
function podcast_helper_episode_download_file() {
	global $wp_query;

	// check if we are downloading episode media file.
	$download   = false;
	$episode_id = false;
	if ( isset( $wp_query->query_vars['podcast_episode'] ) && $wp_query->query_vars['podcast_episode'] ) {
		$download   = true;
		$episode_id = intval( $wp_query->query_vars['podcast_episode'] );
	}
	$download = apply_filters( 'podcast_helper_is_episode_download', $download, $episode_id );

	if ( $download && isset( $episode_id ) && $episode_id ) {

		// Get file referrer.
		$referrer = '';
		if ( isset( $wp_query->query_vars['podcast_ref'] ) && $wp_query->query_vars['podcast_ref'] ) {
			$referrer = $wp_query->query_vars['podcast_ref'];
		} else {
			if ( isset( $_GET['ref'] ) ) {
				$referrer = esc_attr( $_GET['ref'] );
			}
		}

		// Get audio file for download.
		$file = get_post_meta( $episode_id, 'episode_audio_file', true );

		// Exit if no file is found.
		if ( ! $file ) {
			return;
		}

		// Allow other actions - functions hooked on here must not output any data
		do_action( 'podcast_helper_file_download', $file, $episode_id, $referrer );

		// Set necessary headers
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Robots: none' );

		// Check file referrer.
		if ( 'download' == $referrer ) {
			// Get episode slug.
			$episode_slug = '';
			if ( isset( $wp_query->query_vars['podcast_slug'] ) && $wp_query->query_vars['podcast_slug'] ) {
				$episode_slug = $wp_query->query_vars['podcast_slug'];
			} else {
				$episode      = get_post( $episode_id );
				$episode_slug = $episode->post_name;
			}
			$episode_ext = strrchr( $file, '.' );

			// Set size of file.
			$size          = get_post_meta( $episode_id, 'episode_audio_file_size', true );
			$attachment_id = podcast_helper_get_attachment_id_from_url( $file );
			if ( empty( $size ) && ! empty( $attachment_id ) ) {
				$size = filesize( get_attached_file( $attachment_id ) );
				update_post_meta( $episode_id, 'episode_audio_file_size', $size );
			}

			// Send Content-Length header.
			if ( ! empty( $size ) ) {
				header( 'Content-Length: ' . $size );
			}

			// Encode spaces in file names until this is fixed in core (https://core.trac.wordpress.org/ticket/36998)
			$file = str_replace( ' ', '%20', $file );

			// Force file download.
			header( 'Content-Type: application/force-download' );
			// Set other relevant headers
			header( 'Content-Description: File Transfer' );
			//header( 'Content-Disposition: attachment; filename="' . $episode_slug . $episode_ext . '";' );
			header( 'Content-Transfer-Encoding: binary' );
			podcast_helper_readfile_chunked( $file ); // header( 'Location: ' . $file );
		} else {
			// Encode spaces in file names until this is fixed in core (https://core.trac.wordpress.org/ticket/36998)
			$file = str_replace( ' ', '%20', $file );

			// For all other referrers redirect to the raw file.
			$redirect_status = 302;
			if ( ! podcast_helper_get_attachment_id_from_url( $file ) ) {
				$redirect_status = 301;
			}
			wp_redirect( $file, $redirect_status );
		}

		// Exit to prevent other processes running later on.
		exit;
	}
}
add_action( 'wp', 'podcast_helper_episode_download_file', 1 );

/**
 * Reads file in chunks so big downloads are possible without changing PHP.INI - http://codeigniter.com/wiki/Download_helper_for_large_files/
 */
function podcast_helper_readfile_chunked( $file, $retbytes = true ) {
	$chunksize = 1 * ( 1024 * 1024 );
	$cnt       = 0;
	$handle    = fopen( $file, 'r' );
	if ( false === $handle ) {
		return false;
	}

	while ( ! feof( $handle ) ) {
		$buffer = fread( $handle, $chunksize );
		echo $buffer;
		ob_flush();
		flush();
		if ( $retbytes ) {
			$cnt += strlen( $buffer );
		}
	}
	$status = fclose( $handle );
	if ( $retbytes && $status ) {
		return $cnt;
	}
	return $status;
}

function podcast_helper_get_local_file_path( $file ) {

	// Identify file by root path and not URL (required for getID3 class)
	$site_root = trailingslashit( ABSPATH );

	// Remove common dirs from the ends of site_url and site_root, so that file can be outside of the WordPress installation
	$root_chunks = explode( '/', $site_root );
	$url_chunks  = explode( '/', site_url() );

	end( $root_chunks );
	end( $url_chunks );

	while ( ! is_null( key( $root_chunks ) ) && ! is_null( key( $url_chunks ) ) && ( current( $root_chunks ) == current( $url_chunks ) ) ) {
		array_pop( $root_chunks );
		array_pop( $url_chunks );
		end( $root_chunks );
		end( $url_chunks );
	}

	$site_root = implode( '/', $root_chunks );
	$site_url  = implode( '/', $url_chunks );

	$file = str_replace( $site_url, $site_root, $file );

	return $file;
}

/**
 * Get the ID of an attachment from its URL.
 */
function podcast_helper_get_attachment_id_from_url( $url = '' ) {
	// Let's hash the URL to ensure that we don't get
	// any illegal chars that might break the cache.
	$key = md5( $url );

	// Do we have anything in the cache for this URL?
	$attachment_id = wp_cache_get( $key, 'attachment_id' );

	if ( false === $attachment_id ) {

		// If there is no url, return.
		if ( '' === $url ) {
			return false;
		}

		// Set the default
		$attachment_id = 0;

		$attachment_id = absint( attachment_url_to_postid( $url ) );
		if ( 0 !== $attachment_id ) {
			wp_cache_set( $key, $attachment_id, 'attachment_id', DAY_IN_SECONDS );
			return $attachment_id;
		}
	}

	return $attachment_id;
}

/**
 * Add episodes to the dashboard glance items.
 *
 * @param array $items The list of items available in the dashboard glance items section.
 * @return array
 */
function podcast_helper_custom_glance_items( $items = array() ) {
	$num_posts = wp_count_posts( 'episode' );
	if ( $num_posts ) {
		// Style only specific post type icon.
		echo "<style type='text/css'>#dashboard_right_now a.episode-count:before,#dashboard_right_now span.episode-count:before { content: '\\f482'; }</style>";

		$published = intval( $num_posts->publish );
		$post_type = get_post_type_object( 'episode' );

		/* translators: no. of published posts */
		$text = _n( '%s Episode', '%s Episodes', $published, 'podcast-helper' );
		$text = sprintf( $text, number_format_i18n( $published ) );

		if ( current_user_can( $post_type->cap->edit_posts ) ) {
				$items[] = sprintf( '<a class="%1$s-count" href="edit.php?post_type=%1$s">%2$s</a>', 'episode', $text ) . "\n";
		} else {
				$items[] = sprintf( '<span class="%1$s-count">%2$s</span>', $type, $text ) . "\n";
		}
	}
	return $items;
}
add_filter( 'dashboard_glance_items', 'podcast_helper_custom_glance_items', 10, 1 );

/**
 * Add feed in the header section of the website
 */
function podcast_helper_rss_meta_tag() {
	if ( current_theme_supports( 'podcast-rss-feed' ) ) {
		echo '<link rel="alternate" type="application/rss+xml" title="' . esc_attr( get_option( 'podcast_title', get_bloginfo( 'name' ) ) ) . ' &raquo; ' . esc_html__( 'RSS Feed', 'podcast-helper' ) . '" href="' . esc_url( podcast_helper_get_feed_url() ) . '" />' . "\n";
	}
}
add_action( 'wp_head', 'podcast_helper_rss_meta_tag' );

/**
 * Render the feed using our template.
 */
function podcast_helper_episodes_render_feed() {
	global $wp_query;
	$wp_query->is_404 = false; // Prevent 404 on feed.
	status_header( 200 );
	require 'templates/episodes-feed.php';
	exit;
}

/**
 * Obtain feed URL address.
 *
 * @return string
 */
function podcast_helper_get_feed_url() {
	$feed_url = trailingslashit( home_url() );
	if ( get_option( 'permalink_structure' ) ) {
		$feed_url .= 'feed/';
	} else {
		$feed_url .= '?feed=';
	}
	$feed_url .= apply_filters( 'podcast_helper_feed_slug', 'podcast' );
	return $feed_url;
}

/**
 * Alter the feed content type to be 'text/xml'.
 *
 * @param string $content_type The content type of the feed.
 * @param string $type         Type of the feed (unique identifier).
 *
 * @return string
 */
function podcast_helper_feed_content_type( $content_type = '', $type = '' ) {
	if ( apply_filters( 'podcast_helper_feed_slug', 'podcast' ) === $type ) {
		$content_type = 'text/xml';
	}
	return $content_type;
}
add_filter( 'feed_content_type', 'podcast_helper_feed_content_type', 10, 2 );

/**
 * Make sure the custom post is added to the main query.
 *
 * @param WP_Query $query The query for which the filter is applied.
 */
function podcast_helper_add_post_types_to_query( $query ) {
	if ( ! is_admin() && ! is_preview() && $query->is_main_query() && ( is_search() || is_category() || is_tag() ) ) {
		if ( empty( $query->query_vars['post_type'] ) ) {
			$query->query_vars['post_type'] = array( 'post', 'episode' );
		} elseif ( 'any' === $query->query_vars['post_type'] ) {
			return;
		} else {
			$query->query_vars['post_type']   = (array) $query->query_vars['post_type'];
			$query->query_vars['post_type'][] = 'episode';
		}
	}
}
add_action( 'pre_get_posts', 'podcast_helper_add_post_types_to_query' );

/**
 * Determine the type of an episode: "audio", "video" or other (false).
 */
function podcast_helper_get_episode_type( $type = '', $post_id ) {
	$episode_file_download = get_post_meta( $post_id, 'episode_audio_file', true );

	if ( ! empty( $episode_file_download ) ) {
		$audio_extensions = implode( '|', wp_get_audio_extensions() );
		if ( preg_match( '/^.*\.(' . $audio_extensions . ')$/i', $episode_file_download ) ) {
			return "audio";
		}
		$video_extensions = implode( '|', wp_get_video_extensions() );
		if ( preg_match( '/^.*\.(' . $video_extensions . ')$/i', $episode_file_download ) ) {
			return "video";
		}
		$yt_rx = '/^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$/';
		$vm_rx = '/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([‌​0-9]{6,11})[?]?.*/';
		if ( preg_match( $yt_rx, $episode_file_download, $yt_matches ) || preg_match($vm_rx, $episode_file_download, $vm_matches) ) {
			return "video-embed";
		}

		return "audio";
	}

	return false;
}
add_filter( 'podcast_helper_episode_type', 'podcast_helper_get_episode_type', 10, 2 );


function podcast_helper_episode_video_thumbnail( $thumb = '', $post_id ) {
	$episode_type   = apply_filters( 'podcast_helper_episode_type', 'audio', $post_id );
	$episode_poster = get_post_meta( $post_id, 'episode_poster', true );
	if ( $episode_poster ) {
		return esc_url( $episode_poster );
	} elseif ( false !== strpos( $episode_type, 'video-embed' ) ) {
		$episode_file = get_post_meta( $post_id, 'episode_audio_file', true );
		$yt_rx = '/^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$/';
		if ( preg_match( $yt_rx, $episode_file, $yt_matches ) ) {
			$video_id = esc_attr( $yt_matches[5] );
			return esc_url( 'https://img.youtube.com/vi/' . $video_id . '/sddefault.jpg' );
		}
		$vm_rx = '/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([‌​0-9]{6,11})[?]?.*/';
		if ( preg_match( $vm_rx, $episode_file, $vm_matches ) ) {
			$video_id    = esc_attr( $vm_matches[5] );
			$video_thumb = get_transient( 'podcast_helper_episode_vimeo_thumb_' . $video_id );
			if ( false === $video_thumb ) {
				$response = wp_remote_get( "http://vimeo.com/api/v2/video/$video_id.json", array( 'sslverify' => false, 'timeout' => 20 ) );
				if ( ! is_wp_error( $response ) && $response['response']['code'] == 200 ) {
					$video_results = json_decode( $response['body'], true );
					if ( $video_results && is_array( $video_results ) && count( $video_results ) > 0 && isset( $video_results[0]['thumbnail_large'] ) ) {
						$video_thumb = $video_results[0]['thumbnail_large'];
						set_transient( 'podcast_helper_episode_vimeo_thumb_' . $video_id, $video_thumb, WEEK_IN_SECONDS );
					}
				}
			}
			if ( $video_thumb ) {
				return esc_url( $video_thumb );
			}
		}
	}
	return false;
}
add_filter( 'podcast_helper_episode_video_thumbnail', 'podcast_helper_episode_video_thumbnail', 10, 2 );

function podcast_helper_display_social_icons() {
	?>
	<h5 class="share-title"><?php esc_html_e( 'Share it:', 'podcast-helper' ); ?></h5>
	<p class="share-links">
		<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()) ?>" target="_blank" title="<?php echo esc_attr( esc_html__( 'Share via Facebook', 'podcast-helper' ) ); ?>"><?php echo apply_filters( 'theme_additional_social_link_icon', '', 'facebook' ); ?> <span class="screen-reader-text"><?php esc_html_e( 'Facebook', 'podcast-helper' ) ?></span></a>
		<a href="https://twitter.com/intent/tweet?text=<?php echo urlencode( get_the_title() ); ?>&amp;url=<?php echo rawurlencode( get_permalink() ); ?>" target="_blank" title="<?php echo esc_attr( esc_html__( 'Share via Twitter', 'podcast-helper' ) ); ?>"><?php echo apply_filters( 'theme_additional_social_link_icon', '', 'twitter' ); ?> <span class="screen-reader-text"><?php esc_html_e( 'Twitter', 'podcast-helper' ) ?></span></a>
		<?php
		if ( has_post_thumbnail() ) :
			$post_image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
			?>
			<a href="http://pinterest.com/pin/create/button/?url=<?php echo rawurlencode( get_permalink() ); ?>&amp;media=<?php echo esc_attr( $post_image[0] ); ?>&amp;description=<?php echo esc_attr( urlencode( get_the_title() ) ); ?>" target="_blank" title="<?php echo esc_attr( esc_html__( 'Share via Pinterest', 'podcast-helper' ) ); ?>"><?php echo apply_filters( 'theme_additional_social_link_icon', '', 'pinterest' ); ?> <span class="screen-reader-text"><?php esc_html_e( 'Pinterest', 'podcast-helper' ) ?></span></a>
		<?php endif; ?>
	</p>
	<?php
}
add_action( 'theme_additional_entry_footer_content', 'podcast_helper_display_social_icons' );

/**
 * Shortcode for displaying a podcast episode.
 *
 * @param array $atts Attributes of the shortcode.
 * @return string
 */
function podcast_helper_episode_shortcode( $atts ) {
	$args = shortcode_atts( array(
		'id'           => 'latest',
		'title'        => false,
		'title_tag'    => 'h2',
		'title_link'   => true,
		'title_class'  => '',
		'title_prefix' => '',
	), $atts, 'podcast_episode' );

	$query_args = array(
		'post_type'      => 'episode',
		'post_status'    => 'publish',
		'orderby'        => 'menu_order date',
		'posts_per_page' => 1,
	);

	if ( filter_var( esc_attr( $args['id'] ), FILTER_VALIDATE_INT ) ) {
		$query_args['p'] = $args['id'];
	} elseif ( isset( $args['id'] ) && 'latest' !== $args['id'] ) {
		return '';
	}

	$query_args = apply_filters( 'podcast_helper_episode_shortcode_query_args', $query_args );

	$query = new WP_Query( $query_args );

	if ( $query->have_posts() ) {
		$query->the_post();
		$html = '';
		if ( filter_var( esc_attr( $args['title'] ), FILTER_VALIDATE_BOOLEAN ) ) {
			if ( $args['title_tag'] ) {
				$html .= '<' . esc_attr( $args['title_tag'] );
				if ( esc_attr( $args['title_class'] ) ) {
					$html .= ' class="' . esc_attr( $args['title_class'] ) . '"';
				}
				$html .= '>';
			}
			if ( filter_var( esc_attr( $args['title_link'] ), FILTER_VALIDATE_BOOLEAN ) ) {
				$html .= '<a href="' . esc_url( get_permalink() ) . '">';
			}
			if ( $args['title_prefix'] ) {
				$html .= do_shortcode( $args['title_prefix'] );
			}
			$html .= get_the_title();
			if ( filter_var( esc_attr( $args['title_link'] ), FILTER_VALIDATE_BOOLEAN ) ) {
				$html .= '</a>';
			}
			if ( esc_attr( $args['title_tag'] ) ) {
				$html .= '</' . esc_attr( $args['title_tag'] ) . '>';
			}
		}
		$episode_type          = apply_filters( 'podcast_helper_episode_type', 'audio', get_the_ID() );
		$episode_custom_player = get_post_meta( get_the_ID(), 'episode_custom_player', true );
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

		if ( 'video-embed' == $episode_type ) {
			$episode_file          = $episode_file_raw;
			$episode_file_download = $episode_file_raw;
			$episode_download_text = esc_html__( 'View Original Video', 'vipo' );
		} else {
			$episode_download_text = sprintf( esc_html__( 'Download Episode%s', 'vipo' ), $episode_file_size ? ' (' . esc_attr( size_format( $episode_file_size, 1 ) ) . ')' : '' );
		}

		if ( ! post_password_required() && ! empty( $episode_custom_player ) ) {
			$html .= do_shortcode( $episode_custom_player );
		} else if ( ! empty( $episode_file ) ) {
			$video_episode_class = '';
			if ( false !== strpos( $episode_type, 'video' ) ) {
				$video_episode_class = ' episode-type-video';
			}
			/* translators: 1 - file size */
			$html .= '<div class="podcast-episode-player' . esc_attr( $video_episode_class ) . '" data-episode-id="' . esc_attr( get_the_ID() ) . '" data-episode-download="' . esc_url( $episode_file_download ) . '" data-episode-download-button="' . esc_attr( $episode_download_text ) . '" data-episode-duration="' . esc_attr( $episode_file_duration ) . '" data-episode-size="' . esc_attr( size_format( $episode_file_size, 1 ) ) . '" data-episode-transcript="' . esc_url( $episode_transcript ) . '" data-episode-transcript-button="' . esc_attr( esc_html__( 'View Transcript', 'podcast-helper' ) ) . '">';
			if ( false !== strpos( $episode_type, 'video' ) ) {
				$episode_poster = apply_filters( 'podcast_helper_episode_video_thumbnail', '', get_the_ID() );
				$episode_poster_css = '';
				if ( $episode_poster ) {
					$episode_poster_css .= ' style="background-image:url(' . esc_url( $episode_poster ) . ')"';
				}
				$html .= '<a class="play-episode" href="' . esc_url( get_the_permalink() ) . '"' . $episode_poster_css . '><span>' . esc_html__( 'Play Episode', 'podcast-helper' ) . '</span></a>';
				$html .= apply_filters( 'podcast_helper_video_player_shortcode', wp_video_shortcode( array(
					'src'     => esc_url( $episode_file ),
					'preload' => 'none',
					'class'   => 'wp-video-shortcode podcast-episode-' . get_the_ID(),
				) ), $episode_file );
			} else {
				$html .= apply_filters( 'podcast_helper_audio_player_shortcode', wp_audio_shortcode( array(
					'src'     => esc_url( $episode_file ),
					'preload' => 'none',
					'class'   => 'wp-audio-shortcode podcast-episode-' . get_the_ID(),
				) ), $episode_file );
			}
			$html .= '</div>';
		}
		wp_reset_postdata();
		if ( $html ) {
			$html = '<div class="podcast-episode">' . apply_filters( 'podcast_helper_episode_shortcode_html', $html, esc_attr( $args['id'] ) ) . '</div>';
		}
		return $html;
	} else {
		return '';
	}
}
add_shortcode( 'podcast_episode', 'podcast_helper_episode_shortcode' );

/* Add metabox options */
if ( is_admin() ) {
	require_once 'includes/metabox-options.php';
}

function pocast_helper_additional_features_callback() {
	/* Add settings page for custom post type */
	if ( is_admin() && current_theme_supports( 'podcast-rss-feed' ) ) {
		require_once 'includes/episodes-settings.php';
	}

	/* Add statistics page for custom post type */
	if ( current_theme_supports( 'podcast-statistics' ) ) {
		require_once 'includes/episodes-stats.php';
	}
}
add_action( 'init', 'pocast_helper_additional_features_callback', 6 );
