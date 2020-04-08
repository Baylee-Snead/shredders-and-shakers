<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link        https://flothemes.com/
 * @since      1.0.0
 *
 * @package    Flo_Instagram
 * @subpackage Flo_Instagram/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Flo_Instagram
 * @subpackage Flo_Instagram/public
 * @author     Flothemes <support@flothemes.com>
 */
class Flo_Instagram_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->flo_gutenberg_block();

		include_once('partials/shcode.php');
		include_once('partials/flo-instagram-widget.php');
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Flo_Instagram_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Flo_Instagram_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
	 	*/

			// disable cache in dev environment
			if(defined('FLO_ENVIROMENT') && FLO_ENVIROMENT === 'DEV') {
				$ver = mt_rand(0,99999);
			} else {
				$ver = $this->version;
			}

			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/flo-instagram-public.css', array(), $ver, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Flo_Instagram_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Flo_Instagram_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

			// disable cache in dev environment
			// if(defined('FLO_ENVIROMENT') && FLO_ENVIROMENT === 'DEV') {
				$ver = mt_rand(0,99999);
			// } else {
			// 	$ver = $this->version;
			// }

			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/all.min.js', array( 'jquery' ), $ver, false );
			wp_localize_script( $this->plugin_name, 'floAjaxUrl', admin_url( 'admin-ajax.php' ) );

	}

	public function flo_gutenberg_block() {
		if ( function_exists('register_block_type') ) {

			register_block_type('flo/flo-instagram-feed', array(
	            'render_callback' => 'flo_render_instagram_shortcode',
	        )
	    );
	    function flo_render_instagram_shortcode( $attributes ){

				if ( (is_singular() || is_archive() || is_home()) && !empty($attributes['shortcode'])) {
					return '<div class="flo-gutenberg-instagram">'. do_shortcode($attributes['shortcode']) .'</div>';
				} else if ((is_singular() || is_archive() || is_home()) && !empty($attributes['content'])) {
					return '<div class="flo-gutenberg-instagram">'. do_shortcode($attributes['content']) .'</div>';
				}
	    }
		}
	}
}
