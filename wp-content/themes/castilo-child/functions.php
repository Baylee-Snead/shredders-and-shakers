<?php
/**
* Castilo Child functions and definitions
*
* @link http://codex.wordpress.org/Theme_Development
* @link http://codex.wordpress.org/Child_Themes
*
* @package WordPress
* @subpackage Castilo
* @since Castilo
*/



// Enqueue scripts and styles
function castilo_child_scripts(){
	wp_enqueue_style( 'castilo', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'castilo-style' ));
}
add_action( 'wp_enqueue_scripts', 'castilo_child_scripts' );

// Enqueue Google Fonts
function wpb_add_google_fonts() {
   wp_enqueue_style( 'wpb-google-fonts', 'https://fonts.googleapis.com/css?family=Karla|Rubik&display=swap', false );
   }

   add_action( 'wp_enqueue_scripts', 'wpb_add_google_fonts' );
?>
