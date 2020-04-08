<?php
/**
 * Castilo theme functions and definitions
 *
 * Set up the theme and provide some helper functions.
 *
 * Note: Do NOT add any custom code here. Please use a child theme so that your customizations aren't lost during updates.
 */

if ( ! function_exists( 'castilo_theme_setup' ) ) :

	/**
	 * Castilo theme setup function.
	 */
	function castilo_theme_setup() {

		/*
		 * Make theme available for translation.
		 * Translations can be placed in the /languages/ directory.
		 */
		load_theme_textdomain( 'castilo', get_template_directory() . '/languages' );

		// This theme uses wp_nav_menu() in the header section.
		register_nav_menus( array(
			'top'    => esc_html__( 'Top Menu', 'castilo' ),
			'social' => esc_html__( 'Social Links Menu', 'castilo' ),
		) );

		/*
		 * Enable support for posts and comments RSS feed links.
		 */
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded title tag in the document head and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for custom logo.
		 */
		add_theme_support( 'custom-logo',
			array(
				'width'       => 100,
				'height'      => 20,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 */
		add_theme_support( 'post-thumbnails' );
		add_image_size( 'castilo-episode-image', 510, 510, true );
		add_image_size( 'castilo-featured-image', 2000, 1200, true );

		/*
		 * Enable admin bar personal CSS style.
		 */
		add_theme_support( 'admin-bar',
			array(
				'callback' => '__return_false',
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments to output valid HTML5.
		 */
		add_theme_support( 'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);

		/*
		 * Enable support for Post Formats.
		 */
		add_theme_support( 'post-formats',
			array(
				'aside',
				'chat',
				'gallery',
				'link',
				'image',
				'quote',
				'status',
				'video',
				'audio',
			)
		);

		/*
		 * Enable support for custom backgrounds.
		 */
		add_theme_support( 'custom-background', array(
			'wp-head-callback' => 'castilo_custom_background_callback',
		) );

		/*
		 * Enable support for custom header media.
		 */
		add_theme_support( 'custom-header',
			array(
				'default-image' => '%s/assets/images/header.jpg',
				'width'         => 2000,
				'height'        => 1200,
				'flex-height'   => true,
				'video'         => true,
				'header-text'   => false,
			)
		);
		register_default_headers(
			array(
				'default-image' => array(
					'url'           => '%s/assets/images/header.jpg',
					'thumbnail_url' => '%s/assets/images/header.jpg',
					'description'   => esc_html__( 'Default Header Image', 'castilo' ),
				),
			)
		);

		/*
		 * Enable support for selective refresh in the Customizer.
		 */
		add_theme_support( 'customize-selective-refresh-widgets' );

		/*
		 * Enable support for the block editor wide elements.
		 */
		add_theme_support( 'align-wide' );

		/*
		 * Enable support for podcast features.
		 */
		add_theme_support( 'podcast-rss-feed' );
		add_theme_support( 'podcast-statistics' );

		/*
		 * This theme styles the visual editor to resemble the theme style, specifically font, colors, icons, and column width.
		 */
		add_editor_style( array( 'assets/css/editor-style.css', castilo_fonts_url() ) );
	}

endif; // castilo_theme_setup.

add_action( 'after_setup_theme', 'castilo_theme_setup' );

/**
 * Sets the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 */
function castilo_content_width() {
	$content_width = 825;

	if ( is_page() && ! is_page_template( array( 'template-sidebar.php', 'template-episodes.php' ) ) ) {
		$content_width = 1110;
	}

	$GLOBALS['content_width'] = apply_filters( 'castilo_content_width', $content_width );
}
add_action( 'after_setup_theme', 'castilo_content_width', 0 );

/**
 * Default callback for displaying background image and color
 */
function castilo_custom_background_callback() {
	// It is empty because we add the proper CSS via the wp_enqueue_scripts in setup.php.
}

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function castilo_pingback_header() {
	if ( is_singular() && pings_open() && get_bloginfo( 'pingback_url' ) ) {
		printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'castilo_pingback_header' );

/**
 * Registers the default widget area.
 */
function castilo_widgets_init() {

	register_sidebar(
		array(
			'name'          => esc_html__( 'Page Sidebar', 'castilo' ),
			'id'            => 'page-sidebar',
			'description'   => esc_html__( 'Add widgets here to appear in your sidebar on pages like blogs, archives or episode listings.', 'castilo' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h5 class="widget-title add-separator"><span>',
			'after_title'   => '</span></h5>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Home Sidebar', 'castilo' ),
			'id'            => 'home-sidebar',
			'description'   => esc_html__( 'Add widgets here to appear in your sidebar on the Home template > Browse Episodes section.', 'castilo' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h5 class="widget-title add-separator"><span>',
			'after_title'   => '</span></h5>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer 1', 'castilo' ),
			'id'            => 'footer-1',
			'description'   => esc_html__( 'Add widgets here to appear in your footer.', 'castilo' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer 2', 'castilo' ),
			'id'            => 'footer-2',
			'description'   => esc_html__( 'Add widgets here to appear in your footer.', 'castilo' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);

}
add_action( 'widgets_init', 'castilo_widgets_init' );

/**
 * Add a 'Read more' link to the excerpt
 */
function castilo_excerpt_content( $excerpt ) {
	if ( is_admin() || 'episode' === get_post_type() ) {
		return $excerpt;
	}

	$post_link = get_permalink( );
	if ( 'link' === get_post_format() ) {
		$content = get_the_content();
		if ( preg_match( "/<a\s[^>]*href=([\"\']??)([^\\1 >]*?)\\1[^>]*>(.*)<\/a>/siU", $content, $matches ) ) {
			if ( isset( $matches[2] ) ) {
				$post_link = $matches[2];
			}
		}
	}

	$link = sprintf(
		'<a href="%1$s" class="read-more">%2$s <span class="mdi mdi-trending-neutral"></span></a>',
		esc_url( $post_link ),
		__( 'Read more', 'castilo' )
	);
	return $excerpt . $link;
}
add_filter( 'the_excerpt', 'castilo_excerpt_content' );

/**
 * Filter the excerpt length to 20 words.
 */
function castilo_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}
	return 20;
}
add_filter( 'excerpt_length', 'castilo_excerpt_length', 20 );

if ( ! function_exists( 'castilo_fonts_url' ) ) :

	/**
	 * Register Google fonts for the theme.
	 * Create your own castilo_fonts_url() function to override in a child theme.
	 */
	function castilo_fonts_url() {
		$fonts_url = '';

		/* translators: If there are characters in your language that are not supported by Oswald and Karla, translate this to 'off'. Do not translate into your own language. */
		if ( 'off' !== _x( 'on', 'Oswald and Karla fonts: on or off', 'castilo' ) ) {
			$font_families   = array();
			$font_families[] = 'Oswald:300,400';
			$font_families[] = 'Karla:400,400italic,700';
			$fonts_url       = add_query_arg( array(
				'family' => urlencode( implode( '|', $font_families ) ),
				'subset' => urlencode( 'latin,latin-ext' ),
			), 'https://fonts.googleapis.com/css' );
		}

		return esc_url_raw( $fonts_url );
	}

endif; // function_exists(castilo_fonts_url).

/**
 * Add preconnect for Google Fonts.
 */
function castilo_resource_hints( $urls, $relation_type ) {
	if ( wp_style_is( 'castilo-fonts', 'queue' ) && 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'castilo_resource_hints', 10, 2 );

/**
 * Enqueues scripts and styles.
 */
function castilo_scripts_styles() {

	// Add custom fonts, used in the main stylesheet.
	if ( true == get_theme_mod( 'load_theme_fonts', true ) ) {
		wp_enqueue_style( 'castilo-fonts', castilo_fonts_url(), array(), null );
	}

	// Add bootstrap CSS files.
	wp_enqueue_style( 'bootstrap-reboot', get_theme_file_uri( '/assets/css/bootstrap-reboot.css' ), array(), '4.1.3' );
	if ( is_rtl() ) {
		wp_enqueue_style( 'bootstrap-reboot-rtl', get_theme_file_uri( '/assets/css/bootstrap-reboot-rtl.css' ), array( 'bootstrap-reboot' ), '4.1.3' );
	}
	wp_enqueue_style( 'bootstrap-grid', get_theme_file_uri( '/assets/css/bootstrap-grid.css' ), array(), '4.1.3' );
	if ( is_rtl() ) {
		wp_enqueue_style( 'bootstrap-grid-rtl', get_theme_file_uri( '/assets/css/bootstrap-grid-rtl.css' ), array( 'bootstrap-grid' ), '4.1.3' );
	}

	// Add theme icons CSS file.
	wp_enqueue_style( 'castilo-icons', get_theme_file_uri( '/assets/css/materialdesignicons.css' ), array(), '4.5.95' );

	// Add theme main CSS file.
	wp_enqueue_style( 'castilo-style', get_stylesheet_uri(), array( 'bootstrap-reboot', 'bootstrap-grid', 'castilo-icons' ), null );

	// Add Modernizr library file (custom version).
	wp_enqueue_script( 'castilo-modernizr', get_template_directory_uri() . '/assets/js/modernizr-custom.js', array(), null, false );

	if ( is_home() || is_archive() || is_search() || is_page_template( 'template-posts.php' ) ) {
		wp_enqueue_script( 'imagesloaded', get_template_directory_uri() . '/assets/js/imagesloaded.pkgd.min.js', false, false, true );
		wp_enqueue_script( 'masonry', get_template_directory_uri() . '/assets/js/masonry.pkgd.min.js', false, false, true );
	}

	// Add theme main JS file.
	wp_enqueue_script( 'castilo', get_template_directory_uri() . '/assets/js/functions.js', array( 'jquery' ), null, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

}
add_action( 'wp_enqueue_scripts', 'castilo_scripts_styles' );

// Enqueue block editor style
function castilo_block_editor_styles() {
	wp_enqueue_style( 'castilo-fonts', castilo_fonts_url(), array(), null );
	wp_enqueue_style( 'castilo-block-editor-style', get_template_directory_uri() . '/assets/css/blocks-editor.css', false, null );
}
add_action( 'enqueue_block_editor_assets', 'castilo_block_editor_styles' );

// Set demo data directory & widget import file.
function castilo_demo_data_dir_path() {
	return dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'demo' . DIRECTORY_SEPARATOR;
}
add_filter( 'theme_demo_data_dir_path', 'castilo_demo_data_dir_path', 10 );

function castilo_demo_data_file_widgets() {
	if ( defined( 'MC4WP_VERSION' ) ) {
		return 'widgets-mc4wp.json';
	} else {
		return 'widgets.json';
	}
}
add_filter( 'theme_demo_data_file_widgets', 'castilo_demo_data_file_widgets', 20 );

// Setup everything after the the import process.
function castilo_demo_data_finish_import() {

	// set primary menu location.
	$top_menu    = get_term_by( 'name', 'Top Menu', 'nav_menu' );
	$social_menu = get_term_by( 'name', 'Social Links', 'nav_menu' );
	if ( $top_menu && $social_menu ) {
		set_theme_mod( 'nav_menu_locations', array(
			'top'    => $top_menu->term_id,
			'social' => $social_menu->term_id,
		) );
	}

	// set homepage as a static page.
	$pages      = get_pages();
	$home_found = false;
	$blog_found = false;
	foreach ( $pages as $page ) {
		if ( 'Home' === $page->post_title ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $page->ID );
			$home_found = true;
		}
		if ( 'From Our Blog' === $page->post_title ) {
			$blog_found = true;
			update_option( 'page_for_posts', $page->ID );
		}
		if ( $home_found && $blog_found ) {
			break;
		}
	}
}
add_action( 'theme_demo_data_finish_import', 'castilo_demo_data_finish_import' );

// Add standard theme functionality (filters, hooks).
require get_parent_theme_file_path( '/inc/setup.php' );

// Add theme options for the WordPress Customizer.
require get_parent_theme_file_path( '/inc/customizer.php' );

// Add WooCommerce integration.
if ( class_exists( 'WooCommerce' ) && file_exists( get_parent_theme_file_path( 'inc/woocommerce-config.php' ) ) ) {
	require get_parent_theme_file_path( '/inc/woocommerce-config.php' );
}
