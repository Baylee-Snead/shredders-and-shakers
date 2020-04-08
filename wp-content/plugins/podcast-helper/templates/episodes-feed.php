<?php
/**
 * Podcast RSS feed template
 *
 * @package Podcast Helper
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Hide errors.
error_reporting( 0 );

// Get podcast details.
$title           = get_option( 'podcast_title', get_bloginfo( 'name' ) );
$description     = strip_tags( get_option( 'podcast_description' ) );
$language        = get_option( 'podcast_language', get_bloginfo( 'language' ) );
$copyright       = get_option( 'podcast_copyright' );
$subtitle        = get_option( 'podcast_subtitle', get_bloginfo( 'description' ) );
$author          = get_option( 'podcast_author', get_bloginfo( 'name' ) );
$owner_name      = get_option( 'podcast_owner_name', get_bloginfo( 'name' ) );
$owner_email     = get_option( 'podcast_owner_email', get_bloginfo( 'admin_email' ) );
$explicit_option = get_option( 'podcast_explicit' );
if ( $explicit_option && 'on' === $explicit_option ) {
	$itunes_explicit     = 'yes';
	$googleplay_explicit = 'Yes';
} else {
	$itunes_explicit     = 'clean';
	$googleplay_explicit = 'No';
}
$complete_option = get_option( 'podcast_complete' );
if ( $complete_option && 'on' === $complete_option ) {
	$complete = 'yes';
} else {
	$complete = '';
}
$cover_image       = get_option( 'podcast_cover' );
$itunes_type = get_option( 'podcast_consume_order' );

$category1_option = get_option( 'podcast_primary_category' );
$category1        = explode( '|', $category1_option );
$category2_option = get_option( 'podcast_secondary_category' );
$category2        = explode( '|', $category2_option );
$category3_option = get_option( 'podcast_tertiary_category' );
$category3        = explode( '|', $category3_option );

/* Filter episodes by a category, useful for multiple feeds or seasons */
if ( isset( $_GET[ 'category' ] ) && ! empty( $_GET[ 'category' ] ) ) {
	$feed_category     = strip_tags( (string) wp_unslash( $_GET[ 'category' ] ) );
	$feed_category_obj = get_term_by( 'slug', $feed_category, 'category' );
	if ( $feed_category_obj ) {
		$title = $title . ' - ' . $feed_category_obj->name;
	}
}

// Get stylehseet URL (filterable to allow custom RSS stylesheets)
$stylehseet_url = apply_filters( 'podcast_helper_rss_feed_stylesheet', '' );

// Set RSS content type and charset headers.
header( 'Content-Type: ' . feed_content_type( 'podcast' ) . '; charset=' . get_option( 'blog_charset' ), true );

// Use `echo` for first line to prevent any extra characters at start of document.
echo '<?xml version="1.0" encoding="' . esc_attr( get_option( 'blog_charset' ) ) . '"?>' . "\n";

// Include RSS stylesheet
if ( $stylehseet_url ) {
	echo '<?xml-stylesheet type="text/xsl" href="' . esc_url( $stylehseet_url ) . '"?>' . "\n";
}

?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
	xmlns:googleplay="http://www.google.com/schemas/play-podcasts/1.0"
	<?php do_action( 'rss2_ns' ); ?>
>
	<channel>
		<title><?php echo esc_html( $title ); ?></title>
		<atom:link href="<?php esc_url( self_link() ); ?>" rel="self" type="application/rss+xml" />
		<link><?php echo esc_url( apply_filters( 'podcast_helper_feed_home_url', trailingslashit( home_url() ) ) ); ?></link>
		<description><?php echo esc_html( $description ); ?></description>
		<lastBuildDate><?php echo esc_html( mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false ) ); ?></lastBuildDate>
		<language><?php echo esc_html( $language ); ?></language>
		<copyright><?php echo esc_html( $copyright ); ?></copyright>
		<itunes:subtitle><?php echo esc_html( $subtitle ); ?></itunes:subtitle>
		<itunes:author><?php echo esc_html( $author ); ?></itunes:author>
<?php if ( $itunes_type ) : ?>
		<itunes:type><?php echo esc_html( $itunes_type ); ?></itunes:type>
<?php endif; ?>
		<itunes:owner>
			<itunes:name><?php echo esc_html( $owner_name ); ?></itunes:name>
			<itunes:email><?php echo esc_html( $owner_email ); ?></itunes:email>
		</itunes:owner>
		<googleplay:author><?php echo esc_html( $author ); ?></googleplay:author>
		<googleplay:email><?php echo esc_html( $owner_email ); ?></googleplay:email>
		<itunes:summary><?php echo esc_html( $description ); ?></itunes:summary>
		<googleplay:description><?php echo esc_html( $description ); ?></googleplay:description>
		<itunes:explicit><?php echo esc_html( $itunes_explicit ); ?></itunes:explicit>
		<googleplay:explicit><?php echo esc_html( $googleplay_explicit ); ?></googleplay:explicit>
<?php if ( $complete ) : ?>
		<itunes:complete><?php echo esc_html( $complete ); ?></itunes:complete>
<?php endif; ?>
<?php if ( $cover_image ) : ?>
		<itunes:image href="<?php echo esc_url( $cover_image ); ?>"></itunes:image>
		<googleplay:image href="<?php echo esc_url( $cover_image ); ?>"></googleplay:image>
		<image>
			<url><?php echo esc_url( $cover_image ); ?></url>
			<title><?php echo esc_html( $title ); ?></title>
			<link><?php echo esc_url( apply_filters( 'podcast_helper_feed_home_url', trailingslashit( home_url() ) ) ); ?></link>
		</image>
<?php endif; ?>
<?php if ( $category1_option ) : ?>
		<itunes:category text="<?php echo esc_attr( $category1[0] ); ?>">
	<?php if ( count( $category1 ) > 1 ) : ?>
			<itunes:category text="<?php echo esc_attr( $category1[1] ); ?>"></itunes:category>
	<?php endif; ?>
		</itunes:category>
<?php endif; ?>
<?php if ( $category2_option ) : ?>
		<itunes:category text="<?php echo esc_attr( $category2[0] ); ?>">
	<?php if ( count( $category2 ) > 1 ) : ?>
			<itunes:category text="<?php echo esc_attr( $category2[1] ); ?>"></itunes:category>
	<?php endif; ?>
		</itunes:category>
<?php endif; ?>
<?php if ( $category3_option ) : ?>
		<itunes:category text="<?php echo esc_attr( $category3[0] ); ?>">
	<?php if ( count( $category3 ) > 1 ) : ?>
			<itunes:category text="<?php echo esc_attr( $category3[1] ); ?>"></itunes:category>
	<?php endif; ?>
		</itunes:category>
<?php endif; ?>

<?php
remove_action( 'rss2_head', 'rss2_site_icon' );
remove_action( 'rss2_head', 'the_generator' );

// Add RSS2 headers.
do_action( 'rss2_head' );

$default_args = array(
	'post_type'           => 'episode',
	'post_status'         => 'publish',
	'orderby'             => 'date',
	'posts_per_page'      => -1,
	'ignore_sticky_posts' => true,
);
if ( isset( $feed_category ) ) {
	$default_args['category_name'] = $feed_category;
}

$query_args = apply_filters( 'podcast_helper_feed_query_args', $default_args );

$qry = new WP_Query( $query_args );

if ( $qry->have_posts() ) {
	while ( $qry->have_posts() ) {
		$qry->the_post();

		if ( post_password_required( get_the_ID() ) ) {
			continue;
		}

		// Date recorded.
		$pub_date = esc_html( mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false ) );

		// Episode author.
		$author = esc_html( get_the_author() );

		// Episode content (with iframes removed).
		$content = get_the_content_feed( 'rss2' );
		$content = strip_shortcodes( $content );
		$content = preg_replace( '/<\/?iframe(.|\s)*?>/', '', $content );

		// Episode description.
		ob_start();
		the_excerpt_rss();
		$episode_description = ob_get_clean();

		// iTunes summary excludes HTML and must be shorter than 4000 characters
		$itunes_summary = wp_strip_all_tags( $content );
		$itunes_summary = mb_substr( $itunes_summary, 0, 3999 );

		// Google Play description is the same as iTunes summary, but must be shorter than 1000 characters
		$gp_description = mb_substr( $itunes_summary, 0, 999 );

		// iTunes subtitle excludes HTML and must be shorter than 255 characters
		$itunes_subtitle = wp_strip_all_tags( $episode_description );
		$itunes_subtitle = str_replace(
			array(
				'>',
				'<',
				'\'',
				'"',
				'`',
				'[andhellip;]',
				'[&hellip;]',
				'[&#8230;]',
			),
			array( '', '', '', '', '', '', '', '' ),
			$itunes_subtitle
		);
		$itunes_subtitle = mb_substr( $itunes_subtitle, 0, 254 );

		$episode_image = '';
		$image_id      = get_post_thumbnail_id( get_the_ID() );
		if ( $image_id ) {
			$image_att = wp_get_attachment_image_src( $image_id, 'full' );
			if ( $image_att ) {
				$episode_image = $image_att[0];
			}
		} else if ( $cover_image ) {
			$episode_image = $cover_image;
		}

		// Audio file.
		$audio_file     = apply_filters( 'podcast_helper_episode_media_url', get_the_ID(), 'feed' );
		$audio_file_raw = get_post_meta( get_the_ID(), 'episode_audio_file', true );

		// If there is no enclosure then go no further.
		if ( ! isset( $audio_file_raw ) || ! $audio_file_raw ) {
			continue;
		}

		$episode_type = apply_filters( 'podcast_helper_episode_type', 'audio', get_the_ID() );
		if ( false !== strpos( $episode_type, 'video-' ) ) {
			continue;
		}

		// File MIME type
		if ( 'audio' === $episode_type ) {
			$mime_type = 'audio/mpeg';
		} elseif ( 'video' === $episode_type ) {
			$mime_type = 'video/mp4';
		}
		$mime_type = apply_filters( 'podcast_helper_episode_mime_type', $mime_type, get_the_ID() );

		$duration = get_post_meta( get_the_ID(), 'episode_audio_file_duration', true );
		if ( ! $duration ) {
			$duration = '00:00';
		}
		$size = get_post_meta( get_the_ID(), 'episode_audio_file_size', true );
		if ( ! $size ) {
			$size = 1;
		}

		// Episode explicit flag.
		$ep_explicit = get_post_meta( get_the_ID(), 'episode_explicit', true );
		if ( ! empty( $ep_explicit ) ) {
			$itunes_explicit_flag     = 'yes';
			$googleplay_explicit_flag = 'Yes';
		} else {
			$itunes_explicit_flag     = 'clean';
			$googleplay_explicit_flag = 'No';
		}

		// Episode block flag.
		$ep_block = get_post_meta( get_the_ID(), 'episode_block', true );
		if ( ! empty( $ep_block ) ) {
			$block_flag = 'yes';
		} else {
			$block_flag = 'no';
		}

		// Tags/keywords
		$post_tags = get_the_tags( get_the_ID() );
		if ( $post_tags ) {
			$tags = array();
			foreach ( $post_tags as $tag ) {
				$tags[] = $tag->name;
			}
			if ( ! empty( $tags ) ) {
				$keywords = implode( $tags, ',' );
			}
		}

		// New iTunes WWDC 2017 Tags.
		$itunes_episode_type   = get_post_meta( get_the_ID(), 'episode_type', true );
		$itunes_title          = get_post_meta( get_the_ID(), 'episode_title', true );
		$itunes_episode_number = get_post_meta( get_the_ID(), 'episode_number', true );
		$itunes_season_number  = get_post_meta( get_the_ID(), 'episode_season_number', true );
		?>

		<item>
			<title><?php esc_html( the_title_rss() ); ?></title>
			<link><?php esc_url( the_permalink_rss() ); ?></link>
			<pubDate><?php echo esc_html( $pub_date ); ?></pubDate>
			<dc:creator><?php echo esc_html( $author ); ?></dc:creator>
			<guid isPermaLink="false"><?php esc_html( the_guid() ); ?></guid>
			<description><![CDATA[<?php echo $episode_description; ?>]]></description>
			<itunes:subtitle><![CDATA[<?php echo $itunes_subtitle; ?>]]></itunes:subtitle>
		<?php if ( $keywords ) : ?>
			<itunes:keywords><?php echo $keywords; ?></itunes:keywords>
		<?php endif; ?>
		<?php if ( $itunes_episode_type ) : ?>
			<itunes:episodeType><?php echo esc_html( $itunes_episode_type ); ?></itunes:episodeType>
		<?php endif; ?>
		<?php if ( $itunes_title ) : ?>
			<itunes:title><![CDATA[<?php echo esc_html( $itunes_title ); ?>]]></itunes:title>
		<?php endif; ?>
		<?php if ( $itunes_episode_number ) : ?>
			<itunes:episode><?php echo esc_html( $itunes_episode_number ); ?></itunes:episode>
		<?php endif; ?>
		<?php if ( $itunes_season_number ) : ?>
			<itunes:season><?php echo esc_html( $itunes_season_number ); ?></itunes:season>
		<?php endif; ?>
			<content:encoded><![CDATA[<?php echo $content; ?>]]></content:encoded>
			<itunes:summary><![CDATA[<?php echo $itunes_summary; ?>]]></itunes:summary>
			<googleplay:description><![CDATA[<?php echo $gp_description; ?>]]></googleplay:description>
		<?php if ( $episode_image ) : ?>
			<itunes:image href="<?php echo esc_url( $episode_image ); ?>"></itunes:image>
			<googleplay:image href="<?php echo esc_url( $episode_image ); ?>"></googleplay:image>
		<?php endif; ?>
			<enclosure url="<?php echo esc_url( $audio_file ); ?>" length="<?php echo esc_attr( $size ); ?>" type="<?php echo esc_attr( $mime_type ); ?>"></enclosure>
			<itunes:explicit><?php echo esc_html( $itunes_explicit_flag ); ?></itunes:explicit>
			<googleplay:explicit><?php echo esc_html( $googleplay_explicit_flag ); ?></googleplay:explicit>
			<itunes:block><?php echo esc_html( $block_flag ); ?></itunes:block>
			<googleplay:block><?php echo esc_html( $block_flag ); ?></googleplay:block>
			<itunes:duration><?php echo esc_html( $duration ); ?></itunes:duration>
			<itunes:author><?php echo esc_html( $author ); ?></itunes:author>
		</item>
		<?php
	} // end while
} // end if
?>

	</channel>
</rss>
