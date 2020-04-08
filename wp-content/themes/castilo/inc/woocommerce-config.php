<?php
/*
 * Provide WooCommerce integration with the theme.
 */

/**
 * Declare theme compatibility with the plugin.
 */
function castilo_woo_support() {
	add_theme_support( 'woocommerce', array(
		'product_grid' => array(
			'default_rows'    => 4,
			'min_rows'        => 2,
			'max_rows'        => 8,
			'default_columns' => 2,
			'min_columns'     => 2,
			'max_columns'     => 4,
		),
	) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'castilo_woo_support' );

/**
 * Add specific sidebar widgets.
 */
function castilo_woo_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'WooCommerce Sidebar', 'castilo' ),
		'id'            => 'woo-sidebar',
		'description'   => esc_html__( 'Add widgets here to appear in your sidebar on WooCommerce shop pages.', 'castilo' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h5 class="widget-title add-separator"><span>',
		'after_title'   => '</span></h5>',
	) );
}
add_action( 'widgets_init', 'castilo_woo_widgets_init' );

/* Disable default plugin style in favor of theme style. */
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/**
 * Use own theme custom style.
 */
function castilo_woo_scripts_and_style() {
	wp_enqueue_style( 'castilo-woocommerce', get_template_directory_uri() . '/assets/css/woocommerce.css' );
	if ( is_rtl() ) {
		wp_enqueue_style( 'castilo-woocommerce-rtl', get_template_directory_uri() . '/assets/css/woocommerce-rtl.css', array( 'castilo-woocommerce' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'castilo_woo_scripts_and_style', 11 );

/**
 * Add additional link style declarations to the theme's specific CSS.
 */
function woo_castilo_add_link_style( $css, $link_color ) {
	$css .= ' #content #sidebar .widget_shopping_cart .remove, #content .shop_table .product-remove .remove, .no-touch #content .shop_table .product-remove .remove:hover { background-color: ' . $link_color . '; } #content .shop_table .product-remove .remove, .no-touch #content .shop_table .product-remove .remove:hover, #content .form-row.woocommerce-invalid input.input-text { border-color: ' . $link_color . '; } .no-touch #content .shop_table .product-remove .remove { border-color: #ccc; color: #bbb; background-color: #fff; } .no-touch #content .shop_table .product-remove .remove:hover { color: #fff; }';
	return $css;
}
add_filter( 'castilo_extra_primary_color_style', 'woo_castilo_add_link_style', 10, 2 );

/* Override layout with proper hooks. */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
remove_action( 'wp_footer', 'woocommerce_demo_store' );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

/**
 * Modify pagination arguments.
 */
function castilo_woo_pagination_args( $args ) {
	$args['type']               = 'plain';
	$args['mid_size']           = 1;
	$args['before_page_number'] = esc_html__( 'Page ', 'castilo' );
	$args['prev_text']          = '<em class="mdi mdi-chevron-' . ( is_rtl() ? 'right' : 'left' ) . '"></em> ' . esc_html__( 'Previous', 'castilo' );
	$args['next_text']          = esc_html__( 'Next', 'castilo' ) . ' <em class="mdi mdi-chevron-' . ( is_rtl() ? 'left' : 'right' ) . '"></em>';
	return $args;
}
add_filter( 'woocommerce_pagination_args', 'castilo_woo_pagination_args' );

/**
 * Remove page title at user's discretion.
 */
function castilo_woo_show_page_title() {
	return false;
}
add_filter( 'woocommerce_show_page_title', 'castilo_woo_show_page_title' );

/**
 * Wraps all WooCommerce content in wrappers which match the theme markup and add a left sidebar (if any).
 */
function castilo_woo_before_content() {
	$is_sidebar_active  = is_active_sidebar( 'woo-sidebar' );
	$is_sidebar_allowed = is_shop() || is_product_category() || is_product_tag();
	?>
	<main id="content" class="padding-top-bottom">
		<div class="container">
			<div class="row">
				<div class="<?php echo esc_attr( ( ( $is_sidebar_active && $is_sidebar_allowed ) ? "col-12 col-md-8 col-lg-9" : "col-12" ) ); ?>">
	<?php
	woocommerce_demo_store();
	if ( ! is_checkout() ) {
		echo wp_kses_post( do_shortcode( '[woocommerce_messages]' ) );
	}
}
add_action( 'woocommerce_before_main_content', 'castilo_woo_before_content', 10 );

/**
 * Closes the wrapping divs and add a right sidebar (if any).
 */
function castilo_woo_after_content() {
	$is_sidebar_active  = is_active_sidebar( 'woo-sidebar' );
	$is_sidebar_allowed = is_shop() || is_product_category() || is_product_tag();
	?>
				</div>
				<?php if ( $is_sidebar_active && $is_sidebar_allowed ) : ?>
				<div class="col-12 col-md-4 col-lg-3">
					<?php get_sidebar( 'woocommerce' ); ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</main>
	<?php
}
add_action( 'woocommerce_after_main_content', 'castilo_woo_after_content', 10 );

if ( ! function_exists( 'woocommerce_product_loop_start' ) ) {
	/**
	 * Output start markup for the product loop.
	 */
	function woocommerce_product_loop_start() {
		global $woocommerce_loop, $woocommerce;
		if ( version_compare( $woocommerce->version, '3.3', '<' ) ) {
			if ( empty( $woocommerce_loop['columns'] ) ) {
				$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', castilo_loop_columns() );
			}
		}
		echo '<ul class="products clearfix columns-' . esc_attr( $woocommerce_loop['columns'] ) . '">';
	}
}

if ( ! function_exists( 'woocommerce_product_loop_end' ) ) {
	/**
	 * Output end markup for the product loop.
	 */
	function woocommerce_product_loop_end() {
		echo '</ul>';
	}
}

if ( ! function_exists( 'woocommerce_template_loop_add_to_cart' ) ) {
	/**
	 * Output add to cart link in the product loop.
	 */
	function woocommerce_template_loop_add_to_cart() {
		global $product;
		if ( ! $product->is_in_stock() ) {
			echo '<span class="out-of-stock">' . esc_html__( 'Out of stock', 'castilo' ) . '</span>';
			return;
		}

		if ( $product ) {
			echo apply_filters( 'woocommerce_loop_add_to_cart_link',
				sprintf( '<a rel="nofollow" href="%s" data-quantity="1" data-product_id="%s" data-product_sku="%s" class="%s">%s</a>',
					esc_url( $product->add_to_cart_url() ),
					esc_attr( $product->get_id() ),
					esc_attr( $product->get_sku() ),
					esc_attr( implode( ' ', array_filter( array(
						'button',
						'product_type_' . WC_Product_Factory::get_product_type( $product->get_id() ),
						$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
						$product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
					) ) ) ),
					esc_html( $product->add_to_cart_text() )
				),
			$product );
		}
	}
}

function castilo_loop_columns() {
	global $woocommerce;
	if ( version_compare( $woocommerce->version, '3.3', '<' ) ) {
		return 2;
	} else {
		return get_option( 'woocommerce_catalog_columns', 2 );
	}
}

if ( class_exists( 'WooCommerce' ) ) {
	global $woocommerce;
	if ( version_compare( $woocommerce->version, '3.3', '<' ) ) {

		function castilo_woo_shop_per_page() {
			return 6;
		}
		add_filter( 'loop_shop_per_page', 'castilo_woo_shop_per_page' );
		add_filter( 'loop_shop_columns', 'castilo_loop_columns', 99 );

	}
}

/**
 * Set columns for the related products section.
 */
function castilo_woo_output_related_products_args() {
	global $woocommerce_loop;
	$woocommerce_loop['loop'] = 0;
	return array(
		'posts_per_page' => castilo_loop_columns(),
		'columns'        => castilo_loop_columns(),
		'orderby'        => 'rand',
	);
}
add_filter( 'woocommerce_output_related_products_args', 'castilo_woo_output_related_products_args' );

/**
 * Set columns for the upsell section.
 */
function castilo_woo_upsell_display_args() {
	global $woocommerce_loop;
	$woocommerce_loop['loop'] = 0;
	return array(
		'posts_per_page' => -1,
		'columns'        => castilo_loop_columns(),
		'orderby'        => 'rand',
	);
}
add_filter( 'woocommerce_upsell_display_args', 'castilo_woo_upsell_display_args' );

if ( ! function_exists( 'castilo_menu_cart_output' ) ) {
	function castilo_menu_cart_output() {
		global $woocommerce;
		$cart_items_total = '';
		if ( $woocommerce->cart->get_cart_contents_count() > 0 ) {
			$cart_items_total = '<span class="cart-total">' . $woocommerce->cart->get_cart_total() . '</span>';
		} else {
			return '<li id="castilo-menu-cart"></li>';
		}

		return '<li id="castilo-menu-cart"><a href="' . esc_url( wc_get_cart_url() ) . '" title="' . esc_html__( 'View Shopping Cart', 'castilo' ) . '"><i class="mdi mdi-shopping"></i><span class="cart-text">' . esc_html__( 'Shopping Cart', 'castilo' ) . '</span>' . $cart_items_total . '</a></li>';
	}
}

function castilo_nav_menu_add_cart( $items, $args ) {
	if ( ( has_nav_menu( 'top' ) && 'top' === $args->theme_location ) ) {
		return $items . castilo_menu_cart_output();
	} else {
		return $items;
	}
}
add_filter( 'wp_nav_menu_items', 'castilo_nav_menu_add_cart', 10, 2 );

/* Ensure cart contents update when products are added to the cart via AJAX */
function castilo_header_add_to_cart_fragment( $fragments ) {
	$fragments['#castilo-menu-cart'] = castilo_menu_cart_output();
	return $fragments;
}

add_filter( 'woocommerce_add_to_cart_fragments', 'castilo_header_add_to_cart_fragment' );

/* add container around product thumbnail */
function castilo_woo_before_shop_loop_item( $a ) {
	echo '<div class="thumb"><span class="photo">';
}
add_action( 'woocommerce_before_shop_loop_item', 'castilo_woo_before_shop_loop_item', 9, 1 );

function castilo_woo_before_shop_loop_item_title() {
	echo '</a></span><a href="' . esc_url( get_permalink() ) . '" class="product-title">';
}
add_action( 'woocommerce_before_shop_loop_item_title', 'castilo_woo_before_shop_loop_item_title', 20 );

function castilo_woo_after_shop_loop_item() {
	echo '</div>';
}
add_action( 'woocommerce_after_shop_loop_item', 'castilo_woo_after_shop_loop_item', 15 );

remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );

function castilo_woo_template_loop_product_title() {
	global $product;
	$price      = '';
	$price_html = $product->get_price_html();
	if ( ! empty( $price_html ) ) {
		$price = '<span class="price">' . $price_html . '</span>';
	}
	echo '<span class="title">' . get_the_title() . '</span>' . $price;
}
add_action( 'woocommerce_shop_loop_item_title', 'castilo_woo_template_loop_product_title', 10 );


/* set number of columns for the thumbnails in the product view */
function castilo_woo_product_thumbnails_columns() {
	return 3;
}
add_filter( 'woocommerce_product_thumbnails_columns', 'castilo_woo_product_thumbnails_columns' );

/* add second image for products, if any exists */
function castilo_woo_template_loop_second_product_thumbnail() {
	global $product, $woocommerce;
	$attachment_ids = $product->get_gallery_image_ids();
	if ( $attachment_ids && $product->is_in_stock() ) {
		$secondary_image_id      = $attachment_ids['0'];
		$secondary_image_attribs = array(
			'class' => 'secondary attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image',
		);
		echo wp_get_attachment_image( $secondary_image_id, 'woocommerce_thumbnail', '', $secondary_image_attribs );
	}
}
add_action( 'woocommerce_before_shop_loop_item_title', 'castilo_woo_template_loop_second_product_thumbnail', 15 );

function castilo_woo_composited_product_image_html( $output, $post_ID ) {
	return $output;
}
add_filter( 'woocommerce_composited_product_image_html', 'castilo_woo_composited_product_image_html', 10, 2 );

function castilo_woo_before_single_product_summary_before() {
	echo '<div class="row"><div class="col-12 col-md-5">';
}
add_action( 'woocommerce_before_single_product_summary', 'castilo_woo_before_single_product_summary_before', 5 );
function castilo_woo_before_single_product_summary_after() {
	echo '</div><div class="col-12 col-md-7">';
}
add_action( 'woocommerce_before_single_product_summary', 'castilo_woo_before_single_product_summary_after', 22 );
function castilo_woo_after_single_product_summary() {
	echo '</div></div>';
}
add_action( 'woocommerce_after_single_product_summary', 'castilo_woo_after_single_product_summary', 5 );

function castilo_woo_sale_flash( $message, $post, $product ) {

	$saving_amount = 0;
	// check if variable product
	if ( $product->has_child() ) {
		foreach ( $product->get_children() as $child_id ) {
			$regular_price = get_post_meta( $child_id, '_regular_price', true );
			$sale_price    = get_post_meta( $child_id, '_sale_price', true );
			if ( '' !== $regular_price && '' !== $sale_price && $regular_price > $sale_price ) {
				$new_saving_amount = $regular_price - $sale_price;
				if ( $new_saving_amount > $saving_amount ) {
					$saving_amount = $new_saving_amount;
				}
			}
		}
		$button_text = esc_html__( 'Save up to ', 'castilo' );
	} else {
		// Fetch prices for simple products
		$regular_price = get_post_meta( $product->get_id(), '_regular_price', true );
		$sale_price    = get_post_meta( $product->get_id(), '_sale_price', true );
		if ( '' !== $regular_price && '' !== $sale_price && $regular_price > $sale_price ) {
			$saving_amount = $regular_price - $sale_price;
		}
		$button_text = esc_html__( 'Save ', 'castilo' );
	}

	// Only modify badge if saving amount is larger than 0
	if ( $saving_amount > 0 ) {
		$saving_price = wc_price( $saving_amount );
		$message      = '<span class="onsale">' . $button_text . $saving_price . '</span>';
	}

	return $message;
}
add_filter( 'woocommerce_sale_flash', 'castilo_woo_sale_flash', 10, 3 );

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_show_product_sale_flash', 4 );

function castilo_woo_product_search_form( $echo ) {
	$form = '<form role="search" method="get" class="searchform woocommerce-product-search" action="' . esc_url( home_url( '/' ) ) . '"><input type="hidden" name="post_type" value="product"><label class="screen-reader-text">' . esc_html_x( 'Search for:', 'label', 'castilo' ) . '</label><input type="search" value="' . get_search_query() . '" name="s" placeholder="' . esc_html__( 'Search products&hellip;', 'castilo' ) . '"><button type="submit"><span class="screen-reader-text">' . esc_html__( 'Search', 'castilo' ) . '</span><span class="mdi mdi-magnify"></span></button></form>';
	if ( $echo ) {
		echo do_shortcode( $form ); // already eascaped above;
	} else {
		return $form;
	}
}
add_filter( 'get_product_search_form', 'castilo_woo_product_search_form', 10, 1 );

add_filter( 'woocommerce_product_tag_cloud_widget_args', 'castilo_widget_tag_cloud_args' );
