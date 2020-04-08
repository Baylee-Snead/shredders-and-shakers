<?php
/**
 * Setup main theme functions and hooks.
 */

if ( ! function_exists( 'castilo_posted_on' ) ) :
	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 */
	function castilo_posted_on() {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf(
			$time_string,
			get_the_date( DATE_W3C ),
			get_the_date(),
			get_the_modified_date( DATE_W3C ),
			get_the_modified_date()
		);

		echo '<span class="posted-on">' .
			'<span class="screen-reader-text">' .
			esc_html__( 'Posted on', 'castilo' ) .
			'</span> ' .
			'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>' .
			'</span>';
	}
endif;

/**
 * Returns true if a blog has more than 1 category.
 */
function castilo_categorized_blog() {
	$category_count = get_transient( 'castilo_categories' );

	if ( false === $category_count ) {
		// Create an array of all the categories that are attached to posts.
		$categories = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,
			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$category_count = count( $categories );

		set_transient( 'castilo_categories', $category_count );
	}

	// Allow viewing case of 0 or 1 categories in post preview.
	if ( is_preview() ) {
		return true;
	}

	return $category_count > 1;
}

/**
 * Flush out the transients used in castilo_categorized_blog.
 */
function castilo_category_transient_flusher() {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	delete_transient( 'castilo_categories' );
}
add_action( 'edit_category', 'castilo_category_transient_flusher' );
add_action( 'save_post', 'castilo_category_transient_flusher' );

/**
 * Add custom functionality to all episode players.
 *
 * If you plan to add an audio/video shortcode with an episode make sure the class includes a 'castilo-episode' class in order to obtain the same theme style.
 */
function castilo_wp_audio_shortcode( $html, $atts, $audio, $post_id, $library ) {
	if ( ! is_admin() ) {
		wp_enqueue_style( 'castilo-additional-mediaelement', get_theme_file_uri( '/assets/css/mediaelement-castilo.css' ), array( 'wp-mediaelement' ), false, 'all' );
		if ( is_rtl() ) {
			wp_enqueue_style( 'castilo-additional-mediaelement-rtl', get_theme_file_uri( '/assets/css/mediaelement-castilo-rtl.css' ), array( 'castilo-additional-mediaelement' ), '4.1.3' );
		}
		wp_enqueue_script( 'castilo-additional-mediaelement', get_template_directory_uri() . '/assets/js/mediaelement-castilo.js', array( 'wp-mediaelement' ), false, true );
		wp_localize_script( 'castilo-additional-mediaelement', 'podcast_ajax_object',
			array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}
	return $html;
}
add_filter( 'wp_audio_shortcode', 'castilo_wp_audio_shortcode', 10, 5 );
add_filter( 'wp_video_shortcode', 'castilo_wp_audio_shortcode', 10, 5 );

/* Fixes audio & video shortcodes used for embeds that have parameters in the src. See https://core.trac.wordpress.org/ticket/30377 */
$castilo_normal_file = wp_check_filetype( 'file.mp3', array( 'mp3' => 'audio/mpeg' ) );
$castilo_querys_file = wp_check_filetype( 'file.mp3?p=1', array( 'mp3' => 'audio/mpeg' ) );
if ( $castilo_normal_file['ext'] !== $castilo_querys_file['ext'] ) {
	function castilo_fix_media_shortcode_atts( $out, $pairs, $atts, $shortcode ) {
		$get_media_extensions = "wp_get_{$shortcode}_extensions";
		if ( ! function_exists( $get_media_extensions ) ) {
			return $out;
		}
		$default_types = $get_media_extensions();
		array_unshift( $default_types, 'src' );
		$fixes = array();
		foreach ( $default_types as $type ) {
			if ( empty( $out[ $type ] ) ) {
				continue;
			}
			if ( filter_var( $out[ $type ], FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED ) ) {
				$url = $out[ $type ];
				$ext = pathinfo( explode( '?', $url )[0], PATHINFO_EXTENSION ); 
				// Temporarily add the extension to the END so wp_check_file_type can match it.
				// This will be removed from the final output of the shortcode below.
				$out[ $type ] .= "&wp-check-file-type=.$ext";
				$fixes[] = $ext;
			}
		}
		if ( $fixes ) {
			add_filter( "wp_{$shortcode}_shortcode", function( $html ) use ( $fixes ) {
					foreach ( $fixes as $ext ) {
							$html = str_replace( "&#038;wp-check-file-type=.$ext", '', $html );
					}
					return $html;
			} );
		}
		return $out;
	}
	add_filter( 'shortcode_atts_audio', 'castilo_fix_media_shortcode_atts', 10, 4 );
}

/**
 * Display icons in sharing social links.
 */
function castilo_social_link_icon( $output, $icon ) {
	$output = '<span class="mdi mdi-' . $icon . '"></span>';
	return $output;
}
add_filter( 'theme_additional_social_link_icon', 'castilo_social_link_icon', 10, 2 );

function castilo_playlist_scripts( $type, $style ) {
	if ( ! is_admin() ) {
		wp_enqueue_style( 'castilo-additional-mediaelement', get_theme_file_uri( '/assets/css/mediaelement-castilo.css' ), array( 'wp-mediaelement' ), false, 'all' );
		wp_enqueue_script( 'castilo-additional-mediaelement', get_template_directory_uri() . '/assets/js/mediaelement-castilo.js', array( 'wp-playlist' ), false, true );
	}
}
add_action( 'wp_playlist_scripts', 'castilo_playlist_scripts', 10, 2 );

/**
 * Add video header to homepage featured area.
 */
function castilo_display_featured_video() {
	if ( is_header_video_active() && has_header_video() ) {
		the_custom_header_markup();
	}
}
add_action( 'castilo_featured_after', 'castilo_display_featured_video', 10 );

function castilo_display_sales_box() {
	$footer_banner_image    = get_theme_mod( 'footer_banner_image' );
	$footer_banner_image_id = attachment_url_to_postid( $footer_banner_image );
	$footer_banner_content  = get_theme_mod( 'footer_banner_content' );
	if ( $footer_banner_image && $footer_banner_content && ! is_404() ) :
		?>
		<footer class="sales-box padding-top-bottom">
			<div class="container">
				<div class="row align-items-center">
					<div class="col-12 col-md-6">
						<a class="cover-image">
							<?php if ( $footer_banner_image_id ) : ?>
								<?php echo wp_get_attachment_image( $footer_banner_image_id, 'castilo-episode-image' ); ?>
							<?php else : ?>
								<img src="<?php echo esc_url( $footer_banner_image ); ?>">
							<?php endif; ?>
						</a>
					</div>
					<div class="col-12 col-md-6">
						<?php echo do_shortcode( $footer_banner_content ); ?>
					</div>
				</div>
			</div>
		</footer>
	<?php
	endif;
}
add_action( 'castilo_footer_before', 'castilo_display_sales_box', 10 );

/**
 * Add toggle button for child menu functionality to the theme top menu.
 * Hide social link text making them screen-reader-text .
 */
function castilo_nav_menu_customize_items( $items, $args ) {
	if ( 'top' === $args->theme_location ) {
		return preg_replace(
			array(
				'<ul class="sub-menu">',
				'/>(\s|\n|\r)+</',
			),
			array(
				'a href="#" class="menu-expand"><span class="screen-reader-text">' . esc_html__( 'Toggle child menu', 'castilo' ) . '</span></a><ul class="sub-menu"',
				'><',
			), $items
		);
	}
	return $items;
}
add_filter( 'wp_nav_menu_items', 'castilo_nav_menu_customize_items', 12, 2 );

/**
 * Add some useful classes to the body DOM element.
 */
function castilo_body_classes( $classes ) {

	// Add class if sidebar is used.
	if ( ! has_nav_menu( 'social' ) ) {
		$classes[] = 'no-top-social-links';
	}

	if ( true == get_theme_mod( 'primary_menu_sticky', true ) ) {
		$classes[] = 'navbar-sticky';
	}

	if ( true == get_theme_mod( 'avoid_image_multiply', false ) ) {
		$classes[] = 'avoid-image-multiply';
	}

	if ( true == get_theme_mod( 'no_episode_download', false ) ) {
		$classes[] = 'no-episode-download';
	}

	if ( 1 === get_theme_mod( 'footer_sticky', 0 ) ) {
		$classes[] = 'footer-sticky';
	}

	if ( true == get_theme_mod( 'load_theme_fonts', true ) ) {
		$classes[] = 'default-theme-fonts';
	}

	if ( 'episode' === get_post_type() || is_page_template( 'template-home.php' ) ) {
		if ( 'episode' === get_post_type() ) {
			$lastest_episode_id = get_the_ID();
		} else {
			$query_args = array(
				'post_type'      => 'episode',
				'post_status'    => 'publish',
				'orderby'        => 'menu_order date',
				'posts_per_page' => 1,
			);
			$latest_episode = wp_get_recent_posts($query_args, OBJECT);
			if ( $latest_episode && count( $latest_episode ) > 0 ) {
				$lastest_episode_id = $latest_episode[0]->ID;
			}
		}
		$episode_type = apply_filters( 'podcast_helper_episode_type', 'audio', $lastest_episode_id );
		if ( false !== strpos( $episode_type, 'video-' ) ) {
			$classes[] = 'episode-type-video episode-type-' . esc_attr( $episode_type );
		} else {
			$classes[] = 'episode-type-' . esc_attr( $episode_type );
		}
	}

	// Add class if sidebar is used.
	if ( is_active_sidebar( 'page-sidebar' ) && ( is_home() || is_archive() || is_single() || is_page_template( array( 'template-sidebar.php', 'template-episodes.php', 'template-posts.php' ) ) || is_author() || is_category() || is_tag() || is_search() ) ) {
		$classes[] = 'has-sidebar';
	}

	if ( is_page_template( 'template-home.php' ) ) {
		$instagram_username = get_post_meta( get_the_ID(), 'instagram_username', true );
		if ( ! $instagram_username ) {
			$classes[] = 'no-instagram-section';
		}
	}
	

	return $classes;
}
add_filter( 'body_class', 'castilo_body_classes' );

/**
 * Modifies tag cloud widget arguments to have all tags in the widget same font size.
 */
function castilo_widget_tag_cloud_args( $args ) {
	$args['smallest'] = 1;
	$args['largest']  = 1;
	$args['unit']     = 'em';
	return $args;
}
add_filter( 'widget_tag_cloud_args', 'castilo_widget_tag_cloud_args' );

/**
 * Modifies default video setting for the header video.
 */
function castilo_header_video_settings( $settings ) {
	$settings['minWidth']  = 768;
	$settings['minHeight'] = 420;
	return $settings;
}
add_filter( 'header_video_settings', 'castilo_header_video_settings' );

/**
 * Display pagination links.
 */
function castilo_pagination_links() {
	global $wp_query;
	$max = $wp_query->max_num_pages;
	if ( $max > 1 ) {
		$pagination_style = get_theme_mod( 'pagination_style', 'regular' );
		if ( 'regular' === $pagination_style ) {
			?>
			<nav class="navigation posts-navigation" role="navigation">
				<h4 class="screen-reader-text"><?php esc_html_e( 'Posts navigation', 'castilo' ); ?></h4>
				<div class="nav-links">
					<div class="nav-previous">
						<?php next_posts_link( esc_html__( 'Older Entries', 'castilo' ) ); ?>
					</div>
					<div class="nav-next">
						<?php previous_posts_link( esc_html__( 'Newer Entries', 'castilo' ) ); ?>
					</div>
				</div>
			</nav>
			<?php
		} elseif ( 'pages' === $pagination_style ) {
			$arg                       = array();
			$big_no                    = 999999999;
			$arg['base']               = str_replace( $big_no, '%#%', esc_url( get_pagenum_link( $big_no ) ) );
			$arg['total']              = $max;
			$arg['current']            = max( 1, get_query_var( 'paged' ) );
			$arg['mid_size']           = 1;
			$arg['before_page_number'] = '<span class="screen-reader-text">' . esc_html__( 'Page ', 'castilo' ) . ' </span>';
			$arg['prev_text']          = '<span class="mdi mdi-chevron-' . ( is_rtl() ? 'right' : 'left' ) . '"></span><span class="screen-reader-text">' . esc_html__( 'Previous Page', 'castilo' ) . ' </span>';
			$arg['next_text']          = '<span class="screen-reader-text">' . esc_html__( 'Next Page', 'castilo' ) . ' </span><span class="mdi mdi-chevron-' . ( is_rtl() ? 'left' : 'right' ) . '"></span>';
			echo '<div class="pagination">' . wp_kses_post( paginate_links( $arg ) ) . '</div>';
		} else {
			echo '<div class="pagination pagination-' . esc_attr( get_theme_mod( 'pagination_style', 'load-more' ) ) . '">' . wp_kses_post( get_next_posts_link( '<span class="mdi mdi-dots-horizontal"></span> ' . esc_html__( 'Browse More', 'castilo' ) ) ) . '</div>';
		}
	}
}

/**
 * Add any additional CSS style specific to the theme.
 */
function castilo_inline_theme_style() {

	$custom_css         = '';
	$featured_image_css = '';

	// Get current post id.
	global $wp_query;
	if ( $wp_query->is_singular ) {
		$post_id = $wp_query->queried_object_id;
	} elseif ( is_home() && ! is_front_page() ) {
		$post_id = get_option( 'page_for_posts' );
	}

	if ( class_exists( 'WooCommerce' ) ) {
		if ( is_shop() ) {
			$temp_id = get_option( 'woocommerce_shop_page_id' );
			if ( $temp_id ) {
				$post_id = $temp_id;
			}
		} elseif ( is_product_category() ) {
			$cat = $wp_query->get_queried_object();
			$featured_image_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
		}
	}

	// Check if current post's featured image should override header image.
	if ( isset( $post_id ) ) {
		$avoid_featured_image = get_post_meta( $post_id, 'avoid_featured_image_in_header', true );
		if ( empty( $avoid_featured_image ) && has_post_thumbnail( $post_id ) ) {
			$featured_image_id = get_post_thumbnail_id( $post_id );
		}

		$template_file = get_post_meta( $post_id, '_wp_page_template', true );
		if ( false !== strpos( $template_file, 'template-home' ) ) {
			$use_latest_episode_image = get_post_meta( $post_id, 'use_latest_episode_image', true );
			if ( ! empty( $use_latest_episode_image ) ) {
				$latest_episode = wp_get_recent_posts(array(
					'post_type'      => 'episode',
					'post_status'    => 'publish',
					'orderby'        => 'menu_order date',
					'numberposts'    => 1,
					'posts_per_page' => 1
				));
				if ( $latest_episode ) {
					$featured_image_id = get_post_thumbnail_id( $latest_episode[0]['ID'] );
				}
			}
			$news_background_image = get_post_meta( $post_id, 'news_background_image', true );
			if ( $news_background_image ) {
				$custom_css .= ".latest-news { background-image: url('" . esc_url( $news_background_image ) . "'); }";
			}
			$news_background_overlay_opacity = intval( get_post_meta( $post_id, 'news_background_overlay_opacity', true ) );
			if ( $news_background_overlay_opacity && 75 !== $news_background_overlay_opacity ) {
				$custom_css .= ".latest-news:after { opacity: " . intval( $news_background_overlay_opacity ) / 100 . "; }";
			}
		}
	}

	// If no featured image can be displayed, use header image set in Customizer.
	if ( ! isset( $featured_image_id ) && get_header_image() ) {
		$header = get_custom_header();
		if ( isset( $header->attachment_id ) ) {
			$featured_image_id = $header->attachment_id;
		} elseif ( $header->video ) {
			$featured_image_css = ".featured-content { background-image: url('" . get_header_image() . "'); }";
			$custom_css .= $featured_image_css;
		}
	}

	if ( isset( $featured_image_id ) && ! is_404() ) {

		$image_url = wp_get_attachment_image_url( $featured_image_id, 'medium' );
		if ( $image_url ) {
			$featured_image_css .= ".featured-content { background-image: url('{$image_url}'); }";
		}
		$image_url = wp_get_attachment_image_url( $featured_image_id, 'post-thumbnail' );
		if ( $image_url ) {
			$featured_image_css = ".featured-content { background-image: url('{$image_url}'); }";
		}
		$image_url = wp_get_attachment_image_url( $featured_image_id, 'medium_large' );
		if ( $image_url ) {
			$featured_image_css .= " @media (min-width: 768px) { .featured-content { background-image: url('{$image_url}'); } }";
		}
		$image_url = wp_get_attachment_image_url( $featured_image_id, 'large' );
		if ( $image_url ) {
			$featured_image_css .= " @media (min-width: 992px) { .featured-content { background-image: url('{$image_url}'); } }";
		}
		$image_url = wp_get_attachment_image_url( $featured_image_id, 'full' );
		if ( $image_url ) {
			$featured_image_css .= " @media (min-width: 1200px) { .featured-content { background-image: url('{$image_url}'); } }";
		}
		if ( $featured_image_css ) {
			$custom_css .= $featured_image_css;
		} else {
			$image_url   = wp_get_attachment_image_url( $featured_image_id, 'full' );
			$custom_css .= ".featured-content { background-image: url('{$image_url}'); }";
		}
	}

	$header_overlay_opacity = get_theme_mod( 'header_overlay_opacity', '75' );
	if ( isset( $header_overlay_opacity ) && is_numeric( $header_overlay_opacity ) && '75' !== $header_overlay_opacity && ! is_404() ) {
		$custom_css .= ' .featured-content:after { opacity: ' . intval( $header_overlay_opacity ) / 100 . '; }';
	}

	$featured_image_padding = get_theme_mod( 'featured_image_padding', 0 );
	if ( isset( $featured_image_padding ) && is_numeric( $featured_image_padding ) && 0 !== $featured_image_padding && ! is_404() ) {
		$home_selector = is_front_page() ? '.home ' : '';
		$custom_css .= " {$home_selector}#featured.padding-top-bottom { padding-top: " . (3 + (int) $featured_image_padding ) . 'rem; padding-bottom: ' . (3 + (int) $featured_image_padding ) . "rem; } @media (min-width: 992px) { {$home_selector}#featured.padding-top-bottom { padding-top: " . (4 + (int) $featured_image_padding ) . 'rem; padding-bottom: ' . (4 + (int) $featured_image_padding ) . "rem; } } @media (min-width: 1200px) { {$home_selector}#featured.padding-top-bottom { padding-top: " . (5.3333 + (int) $featured_image_padding ) . 'rem; padding-bottom: ' . (5.3333 + (int) $featured_image_padding ) . 'rem; } }';
	}

	$background_color = get_theme_mod( 'background_color', get_theme_support( 'custom-background', 'default-color' ) );
	if ( $background_color && 'default-color' !== $background_color ) {
		$custom_css .= " body { background-color: #{$background_color}; }";
	}
	$primary_color = get_theme_mod( 'primary_color', '#cc00aa' );
	if ( $primary_color && '#cc00aa' !== $primary_color ) {
		$custom_css .= " a, a:hover, .add-separator em, .button-color, button.button-color, input[type=\"button\"].button-color, input[type=\"reset\"].button-color, input[type=\"submit\"].button-color, .widget ul a:hover, .widget ul a:focus, .widget ul .current-cat:before, .widget ul .current-cat > a, #site-menu .current-menu-item > a, .social-navigation a:hover, .social-navigation a:focus, .share-entry .share-links a:hover, .share-entry .share-links a:focus { color: {$primary_color}; }";
		$custom_css .= " .button-color, button.button-color, input[type=\"button\"].button-color, input[type=\"reset\"].button-color, input[type=\"submit\"].button-color, .button-color:hover, button.button-color:hover, input[type=\"button\"].button-color:hover, input[type=\"reset\"].button-color:hover, input[type=\"submit\"].button-color:hover, .button-color:focus, button.button-color:focus, input[type=\"button\"].button-color:focus, input[type=\"reset\"].button-color:focus, input[type=\"submit\"].button-color:focus, .button-color.button-filled, button.button-color.button-filled, input[type=\"button\"].button-color.button-filled, input[type=\"reset\"].button-color.button-filled, input[type=\"submit\"].button-color.button-filled, .button-color:focus,button.button-color:focus, input[type=\"button\"].button-color:focus, input[type=\"reset\"].button-color:focus, input[type=\"submit\"].button-color:focus,.pagination .page-numbers.current { border-color: {$primary_color}; }";
		$custom_css .= " .add-separator span:after, .button-color:hover, button.button-color:hover, input[type=\"button\"].button-color:hover, input[type=\"reset\"].button-color:hover, input[type=\"submit\"].button-color:hover, .button-color:focus, button.button-color:focus, input[type=\"button\"].button-color:focus, input[type=\"reset\"].button-color:focus, input[type=\"submit\"].button-color:focus, .button-color.button-filled, button.button-color.button-filled, input[type=\"button\"].button-color.button-filled, input[type=\"reset\"].button-color.button-filled, input[type=\"submit\"].button-color.button-filled, .button-color:focus,button.button-color:focus, input[type=\"button\"].button-color:focus, input[type=\"reset\"].button-color:focus, input[type=\"submit\"].button-color:focus,.pagination .page-numbers.current, .categories a { background-color: {$primary_color}; }";
		$custom_css .= " html body #content .castilo-mejs-container .mejs-playpause-button.mejs-pause > button, html body .featured-content .castilo-mejs-container .mejs-playpause-button > button { background: {$primary_color}; }";
		$custom_css .= " .mejs-video.castilo-mejs-container .mejs-captions-selected, .mejs-video.castilo-mejs-container .mejs-chapters-selected { color: {$primary_color}; }";
		$custom_css .= " @media (min-width: 992px) { .features .features-list li:hover .feature-icon { background-color: {$primary_color}; } }";
		$custom_css .= apply_filters( 'castilo_extra_primary_color_style', '', $primary_color );
	}

	if ( 'episode' === get_post_type() || is_page_template( 'template-home.php' ) ) {
		if ( 'episode' === get_post_type() ) {
			$lastest_episode_id = get_the_ID();
		} else {
			$query_args = array(
				'post_type'      => 'episode',
				'post_status'    => 'publish',
				'orderby'        => 'menu_order date',
				'posts_per_page' => 1,
			);
			$latest_episode = wp_get_recent_posts($query_args, OBJECT);
			if ( $latest_episode && count( $latest_episode ) > 0 ) {
				$lastest_episode_id = $latest_episode[0]->ID;
			}
		}
		$episode_poster = apply_filters( 'podcast_helper_episode_video_thumbnail', '', $lastest_episode_id );
		if ( $episode_poster ) {
			$custom_css .= ' .podcast-episode-player[data-episode-id="' . $lastest_episode_id . '"] .play-episode { background-image: url(' . esc_url( $episode_poster ) . ') }';
		}
	}

	$footer_banner_image   = get_theme_mod( 'footer_banner_image' );
	$footer_banner_content = get_theme_mod( 'footer_banner_content' );
	if ( $footer_banner_image && $footer_banner_content && ! is_404() ) {
		$footer_banner_background_image = get_theme_mod( 'footer_banner_background_image' );
		if ( $footer_banner_background_image ) {
			$custom_css .= ' footer.sales-box { background-image: url(' . $footer_banner_background_image . '); }';
		}

		$footer_banner_overlay_opacity = get_theme_mod( 'footer_banner_overlay_opacity', '80' );
		if ( ! empty( $footer_banner_overlay_opacity ) && '80' !== $footer_banner_overlay_opacity ) {
			$custom_css .= ' footer.sales-box:after { opacity: ' . intval( $footer_banner_overlay_opacity ) / 100 . '; }';
		}
	}

	if ( ! empty( $custom_css ) ) {
		// properly escape CSS allowing (only) direct child " > " CSS selector.

		if ( is_child_theme() ) {
			$theme_id = 'castilo-child-style';
		} else {
			$theme_id = 'castilo-style';
		}
		wp_add_inline_style( $theme_id, str_replace( ' &gt; ', ' > ', wp_kses( ltrim( $custom_css ), array( '\'', '\"' ) ) ) );
	}
}
add_action( 'wp_enqueue_scripts', 'castilo_inline_theme_style' );

/**
 * Add additional options specific to this theme
 */
function castilo_additional_meta_options_callback() {
	return 'castilo_add_additional_meta_options';
}
add_filter( 'theme_additional_page_meta_box_options_callback', 'castilo_additional_meta_options_callback', 10, 1 );

function castilo_page_meta_box_restrict_template() {
	return false;
}
add_filter( 'theme_additional_page_meta_box_options_restrict_template', 'castilo_page_meta_box_restrict_template', 10, 1 );

function castilo_additional_meta_options_instructions() {
	global $post;
	if ( ! $post ) {
		return '';
	}

	$template_file = get_post_meta( $post->ID, '_wp_page_template', true );

	$instructions = '';
	if ( false !== strpos( $template_file, 'template-episodes' ) || false !== strpos( $template_file, 'template-posts' ) ) {
		$instructions .= '<p><strong>' . esc_html__( 'Page Category', 'castilo' ) . '</strong> &mdash; ' . esc_html__( 'You can filter the posts displayed by this page using a specific category. This feature offers the possibility of creating multiple such pages; each one having different topics.', 'castilo' ) . '</p>';
	}
	$instructions .= '<p><strong>' . esc_html__( 'Use Featured Image', 'castilo' ) . '</strong> &mdash; ' . esc_html__( 'By default, the Featured Image set in the Customizer is displayed in the header section. You can choose to override it with the post\'s Featured Image field.', 'castilo' ) . '</p>';

	return $instructions;
}
add_filter( 'theme_additional_meta_box_options_instructions', 'castilo_additional_meta_options_instructions', 10, 1 );

function castilo_add_additional_meta_options() {
	global $post;
	$metabox_id = 'castilo-additional-fields';
	wp_nonce_field( "save_{$metabox_id}", "nonce_{$metabox_id}" );
	$template_file = get_post_meta( $post->ID, '_wp_page_template', true );
	?>

	<?php if ( false !== strpos( $template_file, 'template-episodes' ) || false !== strpos( $template_file, 'template-posts' ) ) : ?>
		<div class="podcast-metabox-field podcast-metabox-field-select">
			<div class="podcast-metabox-label">
				<label for="page_category"><strong><?php esc_html_e( 'Page Category', 'castilo' ); ?></strong></label>
			</div>
			<div class="podcast-metabox-input">
				<?php
					wp_dropdown_categories( array(
						'id'              => 'page_category',
						'name'            => 'page_category',
						'selected'        => get_post_meta( $post->ID, 'page_category', true ),
						'orderby'         => 'name',
						'hierarchical'    => true,
						'hide_empty'      => false,
						'show_option_all' => '&nbsp;',
					) );
				?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( false !== strpos( $template_file, 'template-home' ) ) : ?>
		<div class="podcast-metabox-field podcast-metabox-field-select">
			<div class="podcast-metabox-label">
				<label for="episodes_category"><strong><?php esc_html_e( 'Episodes Category', 'castilo' ); ?></strong></label>
			</div>
			<div class="podcast-metabox-input">
				<?php
					wp_dropdown_categories( array(
						'id'              => 'episodes_category',
						'name'            => 'episodes_category',
						'selected'        => get_post_meta( $post->ID, 'episodes_category', true ),
						'orderby'         => 'name',
						'hierarchical'    => true,
						'hide_empty'      => false,
						'show_option_all' => '&nbsp;',
					) );
				?>
				<p class="description"><?php esc_html_e( 'Filter the episodes displayed in the Browse Episodes section (using this parent category).', 'castilo' ); ?></p>
			</div>
		</div>

		<?php
		$episodes_per_page = get_post_meta( $post->ID, 'episodes_per_page', true );
		if ( ! $episodes_per_page ) {
			$episodes_per_page = 3;
		}
		?>
		<div class="podcast-metabox-field podcast-metabox-field-input">
			<div class="podcast-metabox-label">
				<label for="episodes_per_page"><strong><?php esc_html_e( 'Episodes Count', 'castilo' ); ?></strong></label>
			</div>
			<div class="podcast-metabox-input">
				<input type="number" id="episodes_per_page" name="episodes_per_page" min="0" max="50" step="1" value="<?php echo esc_attr( $episodes_per_page ); ?>">
				<p class="description"><?php esc_html_e( 'Set the number of episodes displayed in the Browse Episodes section (from the category selected above).', 'castilo' ); ?></p>
			</div>
		</div>

		<div class="podcast-metabox-field podcast-metabox-field-select">
			<div class="podcast-metabox-label">
				<label for="episodes_page"><strong><?php esc_html_e( 'Episodes Page', 'castilo' ); ?></strong></label>
			</div>
			<div class="podcast-metabox-input">
				<?php
					wp_dropdown_pages( array(
						'id'                => 'episodes_page',
						'name'              => 'episodes_page',
						'selected'          => get_post_meta( $post->ID, 'episodes_page', true ),
						'show_option_none'  => '&nbsp;',
						'option_none_value' => '0',
					) );
				?>
				<p class="description"><?php esc_html_e( 'Display a "Browse More" button linking to the Episodes page.', 'castilo' ); ?></p>
			</div>
		</div>

		<?php $hide_news_section = get_post_meta( $post->ID, 'hide_news_section', true ); ?>
		<div class="podcast-metabox-field podcast-metabox-field-checkbox">
			<div class="podcast-metabox-label">
				<label for="hide_news_section"><strong><?php esc_html_e( 'Hide News Section', 'castilo' ); ?></strong></label>
			</div>
			<div class="podcast-metabox-input">
				<label for="hide_news_section" class="selectit">
					<input type="checkbox" name="hide_news_section" id="hide_news_section" value="1"<?php checked( ! empty( $hide_news_section ) ); ?> class="collapse-container" data-container="#news-meta-container"> <?php esc_html_e( 'Check this option if you don\'t need to display the News section.', 'castilo' ); ?>
				</label>
			</div>
		</div>

		<div id="news-meta-container"<?php if ( ! empty( $hide_news_section ) ) : ?> style="display:none"<?php endif; ?>>
			<div class="podcast-metabox-field podcast-metabox-field-select">
				<div class="podcast-metabox-label">
					<label for="news_category"><strong><?php esc_html_e( 'News Category', 'castilo' ); ?></strong></label>
				</div>
				<div class="podcast-metabox-input">
					<?php
						wp_dropdown_categories( array(
							'id'              => 'news_category',
							'name'            => 'news_category',
							'selected'        => get_post_meta( $post->ID, 'news_category', true ),
							'orderby'         => 'name',
							'hierarchical'    => true,
							'hide_empty'      => false,
							'show_option_all' => '&nbsp;',
						) );
					?>
					<p class="description"><?php esc_html_e( 'Filter the posts displayed in the Latest News section (using this parent category).', 'castilo' ); ?></p>
				</div>
			</div>
			
			<?php $news_background_image = get_post_meta( $post->ID, 'news_background_image', true ); ?>
			<div class="podcast-metabox-field podcast-metabox-field-input">
				<div class="podcast-metabox-label">
					<label for="news_background_image"><strong><?php esc_html_e( 'News Background Image', 'castilo' ); ?></strong></label>
				</div>
				<div class="podcast-metabox-input">
					<input type="text" class="image_url regular-text" id="news_background_image" name="news_background_image" value="<?php echo esc_attr( $news_background_image ); ?>"> <button class="button set-image set-file" data-frame-title="<?php esc_html_e( 'Select Background Image File', 'castilo' ); ?>"><?php esc_html_e( 'Select File', 'castilo' ); ?></button>
				</div>
			</div>
			
			<?php $news_background_overlay_opacity = get_post_meta( $post->ID, 'news_background_overlay_opacity', true ); ?>
			<div class="podcast-metabox-field podcast-metabox-field-input">
				<div class="podcast-metabox-label">
					<label for="news_background_overlay_opacity"><strong><?php esc_html_e( 'News Background Overlay Opacity', 'castilo' ); ?></strong></label>
				</div>
				<div class="podcast-metabox-input">
					<input type="number" id="news_background_overlay_opacity" name="news_background_overlay_opacity" min="0" max="100" step="5" value="<?php echo esc_attr( $news_background_overlay_opacity ); ?>">
					<p class="description"><?php esc_html_e( 'Set overlay opacity for the above background image, for better text readability (default is 75%).', 'castilo' ); ?></p>
				</div>
			</div>
			
			<div class="podcast-metabox-field podcast-metabox-field-select">
				<div class="podcast-metabox-label">
					<label for="news_page"><strong><?php esc_html_e( 'News Page', 'castilo' ); ?></strong></label>
				</div>
				<div class="podcast-metabox-input">
					<?php
						wp_dropdown_pages( array(
							'id'                => 'news_page',
							'name'              => 'news_page',
							'selected'          => get_post_meta( $post->ID, 'news_page', true ),
							'show_option_none'  => '&nbsp;',
							'option_none_value' => '0',
						) );
					?>
					<p class="description"><?php esc_html_e( 'Display a "Browse More" button linking to the Blog page.', 'castilo' ); ?></p>
				</div>
			</div>
		</div>

		<?php if ( false ) : ?>
			<?php $instagram_username = get_post_meta( $post->ID, 'instagram_username', true ); ?>
			<div class="podcast-metabox-field podcast-metabox-field-input">
				<div class="podcast-metabox-label">
					<label for="instagram_username"><strong><?php esc_html_e( 'Instagram Username', 'castilo' ); ?></strong></label>
				</div>
				<div class="podcast-metabox-input">
					<input type="text" id="instagram_username" name="instagram_username" value="<?php echo esc_attr( $instagram_username ); ?>">
					<p class="description"><?php esc_html_e( 'Set the profile username for the "Latest Instagram" section (leave empty to hide it).', 'castilo' ); ?></p>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php $use_latest_episode_image = get_post_meta( $post->ID, 'use_latest_episode_image', true ); ?>
	<div class="podcast-metabox-field podcast-metabox-field-checkbox">
		<div class="podcast-metabox-label">
			<label for="use_latest_episode_image"><strong><?php esc_html_e( 'Use Latest Episode Featured Image', 'castilo' ); ?></strong></label>
		</div>
		<div class="podcast-metabox-input">
			<label for="use_latest_episode_image" class="selectit"><input type="checkbox" name="use_latest_episode_image" id="use_latest_episode_image" value="1"<?php checked( ! empty( $use_latest_episode_image ) ); ?>> <?php esc_html_e( 'Use the featured image of the latest episode for the featured area', 'castilo' ); ?></label>
		</div>
	</div>

	<?php $avoid_featured_image = get_post_meta( $post->ID, 'avoid_featured_image_in_header', true ); ?>
	<div class="podcast-metabox-field podcast-metabox-field-checkbox">
		<div class="podcast-metabox-label">
			<label for="avoid_featured_image_in_header"><strong><?php esc_html_e( 'Don\'t Use Featured Image', 'castilo' ); ?></strong></label>
		</div>
		<div class="podcast-metabox-input">
			<label for="avoid_featured_image_in_header" class="selectit"><input type="checkbox" name="avoid_featured_image_in_header" id="avoid_featured_image_in_header" value="1"<?php checked( ! empty( $avoid_featured_image ) ); ?>> <?php esc_html_e( 'Avoid using the featured image of this post for the featured area', 'castilo' ); ?></label>
		</div>
	</div>

	<?php if ( 'page' == $post->post_type && false === strpos( $template_file, 'template-home' ) ) : ?>
		<?php $featured_area_subtitle = get_post_meta( $post->ID, 'featured_area_subtitle', true ); ?>
		<div class="podcast-metabox-field podcast-metabox-field-wysiwyg">
			<div class="podcast-metabox-label">
				<label for="featured_area_subtitle"><strong><?php esc_html_e( 'Featured Area Subtitle', 'castilo' ); ?></strong></label>
			</div>
			<div class="podcast-metabox-input">
				<?php
					wp_editor( $featured_area_subtitle, 'featured_area_subtitle', array(
						'media_buttons' => false,
						'textarea_rows' => 1,
						'tinymce'       => false,
						'quicktags'     => array(
							'buttons' => 'strong,em,link,ins',
						),
					) );
				?>
				<p class="description"><?php esc_html_e( 'Optional text displayed on the right side of the featured area section.', 'castilo' ); ?></p>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( false !== strpos( $template_file, 'template-home' ) || 'episode' == get_post_type() ) : ?>
		<?php $featured_area_additional_text = get_post_meta( $post->ID, 'featured_area_additional_text', true ); ?>
		<div class="podcast-metabox-field podcast-metabox-field-wysiwyg">
			<div class="podcast-metabox-label">
				<label for="featured_area_additional_text"><strong><?php esc_html_e( 'Featured Area Additional Text', 'castilo' ); ?></strong></label>
			</div>
			<div class="podcast-metabox-input">
				<?php
					wp_editor( $featured_area_additional_text, 'featured_area_additional_text', array(
						'media_buttons' => false,
						'textarea_rows' => 5,
						'tinymce'       => false,
						'quicktags'     => array(
							'buttons' => 'strong,em,link,ins',
						),
					) );
				?>
				<p class="description"><?php esc_html_e( 'Optional text displayed after the header episode player.', 'castilo' ); ?></p>
			</div>
		</div>
	<?php endif; ?>

	<?php $featured_area_custom_text = get_post_meta( $post->ID, 'featured_area_custom_text', true ); ?>
	<div class="podcast-metabox-field podcast-metabox-field-wysiwyg">
		<div class="podcast-metabox-label">
			<label for="featured_area_custom_text"><strong><?php esc_html_e( 'Featured Area Custom Text', 'castilo' ); ?></strong></label>
		</div>
		<div class="podcast-metabox-input">
			<?php
				wp_editor( $featured_area_custom_text, 'featured_area_custom_text', array(
					'media_buttons' => false,
					'textarea_rows' => 6,
					'tinymce'       => false,
					'quicktags'     => array(
						'buttons' => 'strong,em,link,ins,ul,li',
					),
				) );
				?>
				<p class="description"><?php esc_html_e( 'Replace the featured area text with custom HTML/shortcode based code.', 'castilo' ); ?></p>
		</div>
	</div>

	<?php
}

/**
 * Save fields for the meta options.
 */
function castilo_additional_meta_fields_save_post( $post_id ) {
	$metabox_id = 'castilo-additional-fields';

	// check if nonce validates.
	if ( ! ( isset( $_POST[ "nonce_{$metabox_id}" ] ) && wp_verify_nonce( sanitize_key( $_POST[ "nonce_{$metabox_id}" ] ), "save_{$metabox_id}" ) ) ) {
		return;
	}

	// update page_category.
	if ( isset( $_POST['page_category'] ) ) {
		$page_category = sanitize_text_field( wp_unslash( $_POST['page_category'] ) );
		if ( empty( $page_category ) ) {
			delete_post_meta( $post_id, 'page_category' );
		} else {
			update_post_meta( $post_id, 'page_category', $page_category );
		}
	}

	// update episodes_category.
	if ( isset( $_POST['episodes_category'] ) ) {
		$episodes_category = sanitize_text_field( wp_unslash( $_POST['episodes_category'] ) );
		if ( empty( $episodes_category ) ) {
			delete_post_meta( $post_id, 'episodes_category' );
		} else {
			update_post_meta( $post_id, 'episodes_category', $episodes_category );
		}
	}

	// update episodes_per_page.
	if ( isset( $_POST['episodes_per_page'] ) ) {
		$episodes_per_page = sanitize_text_field( wp_unslash( $_POST['episodes_per_page'] ) );
		if ( empty( $episodes_per_page ) || "3" === $episodes_per_page  ) {
			delete_post_meta( $post_id, 'episodes_per_page' );
		} else {
			update_post_meta( $post_id, 'episodes_per_page', $episodes_per_page );
		}
	}

	// update episodes_page.
	if ( isset( $_POST['episodes_page'] ) ) {
		$episodes_page = sanitize_text_field( wp_unslash( $_POST['episodes_page'] ) );
		if ( empty( $episodes_page ) ) {
			delete_post_meta( $post_id, 'episodes_page' );
		} else {
			update_post_meta( $post_id, 'episodes_page', $episodes_page );
		}
	}

	// update hide_news_section.
	if ( isset( $_POST['hide_news_section'] ) ) {
		$hide_news_section = sanitize_text_field( wp_unslash( $_POST['hide_news_section'] ) );
		if ( ! empty( $hide_news_section ) ) {
			update_post_meta( $post_id, 'hide_news_section', true );
		} else {
			delete_post_meta( $post_id, 'hide_news_section' );
		}
	} else {
		delete_post_meta( $post_id, 'hide_news_section' );
	}

	// update news_category.
	if ( isset( $_POST['news_category'] ) ) {
		$news_category = sanitize_text_field( wp_unslash( $_POST['news_category'] ) );
		if ( empty( $news_category ) ) {
			delete_post_meta( $post_id, 'news_category' );
		} else {
			update_post_meta( $post_id, 'news_category', $news_category );
		}
	}

	// update news_background_image.
	if ( isset( $_POST['news_background_image'] ) ) {
		$news_background_image = sanitize_text_field( wp_unslash( $_POST['news_background_image'] ) );
		if ( empty( $news_background_image ) ) {
			delete_post_meta( $post_id, 'news_background_image' );
		} else {
			update_post_meta( $post_id, 'news_background_image', $news_background_image );
		}
	}

	// update news_background_overlay_opacity.
	if ( isset( $_POST['news_background_overlay_opacity'] ) ) {
		$news_background_overlay_opacity = sanitize_text_field( wp_unslash( $_POST['news_background_overlay_opacity'] ) );
		if ( empty( $news_background_overlay_opacity ) ) {
			delete_post_meta( $post_id, 'news_background_overlay_opacity' );
		} else {
			update_post_meta( $post_id, 'news_background_overlay_opacity', $news_background_overlay_opacity );
		}
	}

	// update news_page.
	if ( isset( $_POST['news_page'] ) ) {
		$news_page = sanitize_text_field( wp_unslash( $_POST['news_page'] ) );
		if ( empty( $news_page ) ) {
			delete_post_meta( $post_id, 'news_page' );
		} else {
			update_post_meta( $post_id, 'news_page', $news_page );
		}
	}

	// update instagram_username.
	if ( isset( $_POST['instagram_username'] ) ) {
		$instagram_username = sanitize_text_field( wp_unslash( $_POST['instagram_username'] ) );
		if ( empty( $instagram_username ) ) {
			delete_post_meta( $post_id, 'instagram_username' );
			delete_transient( 'castilo_instagram_data' );
		} else {
			$old_instagram_username = get_post_meta( $post_id, 'instagram_username', true );
			if ( $old_instagram_username && $instagram_username !== $old_instagram_username) {
				delete_transient( 'castilo_instagram_data' );
			}
			castilo_get_instagram_media( $instagram_username );
			update_post_meta( $post_id, 'instagram_username', $instagram_username );
		}
	}

	// update use_latest_episode_image.
	if ( isset( $_POST['use_latest_episode_image'] ) ) {
		$use_latest_episode_image = sanitize_text_field( wp_unslash( $_POST['use_latest_episode_image'] ) );
		if ( ! empty( $use_latest_episode_image ) ) {
			update_post_meta( $post_id, 'use_latest_episode_image', true );
		} else {
			delete_post_meta( $post_id, 'use_latest_episode_image' );
		}
	} else {
		delete_post_meta( $post_id, 'use_latest_episode_image' );
	}

	// update avoid_featured_image_in_header.
	if ( isset( $_POST['avoid_featured_image_in_header'] ) ) {
		$avoid_featured_image = sanitize_text_field( wp_unslash( $_POST['avoid_featured_image_in_header'] ) );
		if ( ! empty( $avoid_featured_image ) ) {
			update_post_meta( $post_id, 'avoid_featured_image_in_header', true );
		} else {
			delete_post_meta( $post_id, 'avoid_featured_image_in_header' );
		}
	} else {
		delete_post_meta( $post_id, 'avoid_featured_image_in_header' );
	}

	// update featured_area_subtitle.
	if ( isset( $_POST['featured_area_subtitle'] ) ) {
		$featured_area_subtitle = wp_unslash( $_POST['featured_area_subtitle'] );
		if ( empty( $featured_area_subtitle ) ) {
			delete_post_meta( $post_id, 'featured_area_subtitle' );
		} else {
			update_post_meta( $post_id, 'featured_area_subtitle', $featured_area_subtitle );
		}
	}

	// update featured_area_additional_text.
	if ( isset( $_POST['featured_area_additional_text'] ) ) {
		$featured_area_additional_text = wp_unslash( $_POST['featured_area_additional_text'] );
		if ( empty( $featured_area_additional_text ) ) {
			delete_post_meta( $post_id, 'featured_area_additional_text' );
		} else {
			update_post_meta( $post_id, 'featured_area_additional_text', $featured_area_additional_text );
		}
	}

	// update featured_area_custom_text.
	if ( isset( $_POST['featured_area_custom_text'] ) ) {
		$featured_area_custom_text = wp_unslash( $_POST['featured_area_custom_text'] );
		if ( empty( $featured_area_custom_text ) ) {
			delete_post_meta( $post_id, 'featured_area_custom_text' );
		} else {
			update_post_meta( $post_id, 'featured_area_custom_text', $featured_area_custom_text );
		}
	}

}
add_action( 'save_post', 'castilo_additional_meta_fields_save_post' );

if ( ! function_exists( 'castilo_comments_callback' ) ) {
	/**
	 * Theme comment item callback.
	 */
	function castilo_comments_callback( $comment, $args, $depth ) {
		switch ( $comment->comment_type ) {
			case 'pingback':
			case 'trackback': // Display trackbacks differently than normal comments.
				?>
			<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
					<p>
						<strong><?php esc_html_e( 'Pingback:', 'castilo' ); ?></strong>
						<?php comment_author_link(); ?>
						<?php edit_comment_link(); ?>
					</p>
				<?php
				break;

			default: // Proceed with normal comments.
				global $post;
				?>
				<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
					<article class="comment-body">
						<?php if ( get_option( 'show_avatars', true ) ) : ?>
						<div class="comment-author-avatar">
							<?php echo get_avatar( $comment, 100 ); ?>
						</div>
					<?php endif; ?>
						<div class="comment-content-wrapper">
							<footer class="comment-meta">
								<div class="comment-author">
									<h6><?php echo get_comment_author_link( $comment->comment_ID ); ?></h6>
								</div>
								<div class="comment-date">
									<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
									<?php
									/* translators: 1: date, 2: time */
									printf( esc_html__( '%1$s at %2$s', 'castilo' ), get_comment_date(), get_comment_time() );
									?>
									</a>
								</div>
							</footer>
							<div class="comment-content">
								<?php comment_text(); ?>
							</div>
							<div class="reply">
							<?php
							edit_comment_link( esc_html__( 'Edit', 'castilo' ) );
							comment_reply_link( array_merge( $args, array(
								'reply_text' => esc_html__( 'Reply', 'castilo' ),
								'depth'      => $depth,
								'max_depth'  => $args['max_depth'],
							) ) );
							?>
							</div>
						</div>
					</article>
				<?php
				break;
		} // End switch().
	}
}// End if().

if ( ! function_exists( 'castilo_get_instagram_media' ) ) :
	function castilo_get_instagram_media( $username ) {
		$insta_url  = 'https://www.instagram.com/' . $username . '/';
		$insta_data = get_transient( 'castilo_instagram_data' );
		if ( false === $insta_data ) {
			$response = wp_remote_get( $insta_url, array(
				'sslverify'  => false,
				'timeout'    => 20
			) );
			if ( ! is_wp_error( $response ) && $response['response']['code'] == 200 ) {
				$json = str_replace( 'window._sharedData = ', '', strstr( $response['body'], 'window._sharedData = ' ) );
				$json = strstr( $json, '</script>', true );
				$json = rtrim( $json, ';' );
				( $results = json_decode( $json, true ) ) && json_last_error() == JSON_ERROR_NONE;
				if ( $results && is_array( $results ) ) {
					$entry_data =  isset($results['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges']) ? $results['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'] : array();
					if ( empty( $entry_data ) ) {
						return false;
					} else {
						$insta_data = array();
						$insta_data_count = 0;
						foreach ( $entry_data as $current => $result ) {
							$insta_data_count++;
							$insta_data[$result['node']['shortcode']] = end($result['node']['thumbnail_resources']);
							if ( 5 <= $insta_data_count ) {
								break;
							}
						}
						set_transient( 'castilo_instagram_data', $insta_data, apply_filters( 'castilo_instagram_data_transient_expiration' , HOUR_IN_SECONDS * 6 ) );
						return $insta_data;
					}
				}
			}
		} else {
			return $insta_data;
		}

		return false;
	}
endif;

/* Admin functions for this specific theme. */
if ( is_admin() ) {
	/**
	 * Clear existent widgets during the import process.
	 */
	function castilo_import_widget_data( $data ) {
		update_option( 'sidebars_widgets', null );
		return $data;
	}
	add_action( 'podcast_theme_import_widget_data', 'castilo_import_widget_data' );

	/* Some theme page templates don't require the default editor */
	function castilo_edit_form_after_title( $post ) {
		if ( isset( $post ) && is_object( $post ) && isset( $post->ID ) ) {
			$template_file = get_post_meta( $post->ID, '_wp_page_template', true );
			if ( isset( $template_file ) && in_array( $template_file, array( 'template-episodes.php', 'template-posts.php' ) ) ) {
				global $_wp_post_type_features;
				unset( $_wp_post_type_features[ $post->post_type ]['editor'] );
			}
		}
	}
	add_action( 'edit_form_after_title', 'castilo_edit_form_after_title' );

	// Suggest plugins with the TGM Plugin Activation class.
	if ( file_exists( get_parent_theme_file_path( 'inc/class-tgm-plugin-activation.php' ) ) ) {
		include_once get_parent_theme_file_path( 'inc/class-tgm-plugin-activation.php' );

		/**
		 * Register theme required/recommanded plugins.
		 */
		function castilo_register_required_plugins() {
			$plugins = array(
				array(
					'name'        => 'Podcast Helper',
					'slug'        => 'podcast-helper',
					'source'      => get_template_directory() . '/inc/plugins/podcast-helper.zip',
					'version'     => '1.2',
					'required'    => true,
					'has_notices' => true,
				),
				array(
					'name'        => 'Contact Form 7',
					'slug'        => 'contact-form-7',
					'has_notices' => true,
				),
				array(
					'name'        => 'MailChimp for WordPress',
					'slug'        => 'mailchimp-for-wp',
					'has_notices' => true,
				),
				array(
					'name'        => 'Flo Social',
					'slug'        => 'flo-instagram',
					'has_notices' => true,
				),
				array(
					'name'        => 'WooCommerce',
					'slug'        => 'woocommerce',
					'has_notices' => false,
				),
			);
			tgmpa( $plugins );
		}
		add_action( 'tgmpa_register', 'castilo_register_required_plugins' );
	}
} // End if().
