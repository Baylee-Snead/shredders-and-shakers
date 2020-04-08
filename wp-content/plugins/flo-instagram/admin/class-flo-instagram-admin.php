<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link        https://flothemes.com/
 * @since      1.0.0
 *
 * @package    Flo_Instagram
 * @subpackage Flo_Instagram/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Flo_Instagram
 * @subpackage Flo_Instagram/admin
 * @author     Flothemes <support@flothemes.com>
 */
class Flo_Instagram_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// gutenberg block script
		add_action( 'enqueue_block_editor_assets', [ &$this, 'enqueue_gutenberg_scripts' ] );
		add_action( 'acf/load_field/type=acf_flo_instagram_selector', [ &$this, 'init_compatibility' ] );

	}

	public function init_compatibility($field) {
		add_filter( 'option_flo_instagram_accounts', [ &$this, 'flo_social_acf_data_structure_compatibility' ] );
		return $field;
	}

	public function flo_social_acf_data_structure_compatibility($option) {
		$new_option = [];
		foreach ($option as $key => $value) {
			if(!is_array($value)) {
				$new_option[] = ['username' => $value];
			}
		}
    return $new_option;
	}
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		// disable cache in dev environment
		if(defined('FLO_ENVIROMENT') && FLO_ENVIROMENT === 'DEV') {
			$ver = mt_rand(0,99999);
		} else {
			$ver = $this->version;
		}

		// icons
 		wp_enqueue_style( $this->plugin_name . '-icons', plugin_dir_url( __FILE__ ) . 'assets/icons/css/flo-social.css', array(), $ver, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/all.min.css', array(), $ver, 'all' );

	}

	public function enqueue_gutenberg_scripts() {
		wp_enqueue_script(
	    $this->plugin_name . '_gutenberg',
	    plugins_url( '/js/gutbg.min.js', __FILE__ ),
	    [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor', $this->plugin_name ],
	    filemtime( plugin_dir_path( __FILE__ ) . '/js/gutbg.min.js' )
    );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		// disable cache in dev environment
		if(defined('FLO_ENVIROMENT') && FLO_ENVIROMENT === 'DEV') {
			$ver = mt_rand(0,99999);
		} else {
			$ver = $this->version;
		}
		

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/all.min.js', [ 'jquery', 'jquery-ui-slider' ], $ver, false );

		if(!get_option('flo_instagram_accounts')) {
			$accounts = [];
		} else {
			$accounts = get_option('flo_instagram_accounts');
		}
		
		wp_localize_script( $this->plugin_name, 'flo_instagram_users_data', [
			'flo_instagram_accounts' => $accounts,
			'masterKey' => get_option('flo_social_masterKey'),
			'cacheTime' => get_option('flo_instagram_cache_time')
		]);
	}

}
