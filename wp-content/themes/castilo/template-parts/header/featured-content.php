<?php
/**
 * Displays featured content section
 */

// Check if post has custom HTML set for the featured text.
global $wp_query;
if ( $wp_query->is_singular ) {
	$post_id = $wp_query->queried_object_id;
} elseif ( is_home() && ! is_front_page() ) {
	$post_id = get_option( 'page_for_posts' );
}

if ( class_exists( 'WooCommerce' ) && is_shop() ) {
	$temp_id = get_option( 'woocommerce_shop_page_id' );
	if ( $temp_id ) {
		$post_id = $temp_id;
	}
}

if ( isset( $post_id ) ) {
	$featured_area_custom_text = do_shortcode( get_post_meta( $post_id, 'featured_area_custom_text', true ) );
	if ( ! empty( $featured_area_custom_text ) ) {
		preg_match_all( '/<div.*?class=".*?col.*?">/', $featured_area_custom_text, $col_matches );
		if ( 0 == count( $col_matches[0] ) ) {
			$featured_area_custom_text = '<div class="col-12">' . $featured_area_custom_text . '</div>';
		}
		echo '<header id="featured" class="featured-content padding-top-bottom"><div class="container"><div class="row align-items-center">' . do_shortcode( $featured_area_custom_text ) . '</div></div>';
		do_action( 'castilo_featured_after' );
		echo '</header>';
		return; // return early.
	}
}

$template_part = 'page';
if ( is_home() || is_archive() || is_search() ) {
	if ( class_exists( 'WooCommerce' ) && ( is_shop() || is_product_category() || is_product_tag() ) ) {
		$template_part = 'page-shop';
	} else {
		$template_part = 'page';
	}
} elseif ( is_page() ) {
	$template_file = get_post_meta( $post->ID, '_wp_page_template', true );
	if ( false !== strpos( $template_file, 'template-home' ) ) {
		$template_part = 'page-home';
	} else {
		$template_part = 'page';
	}
} elseif ( is_single() ) {
	switch ( get_post_type() ) {
		case 'episode':
			$template_part = 'episode';
			break;

		case 'product':
			$template_part = 'product';
			break;

		default:
			$template_part = 'single';
			break;
	}
} elseif ( is_404() ) {
	$template_part = '404';
}

get_template_part( 'template-parts/header/featured-part', $template_part );
