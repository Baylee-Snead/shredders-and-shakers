<?php

/**
 * Fired during plugin deactivation
 *
 * @link        https://flothemes.com/
 * @since      1.0.0
 *
 * @package    Flo_Instagram
 * @subpackage Flo_Instagram/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Flo_Instagram
 * @subpackage Flo_Instagram/includes
 * @author     Flothemes <support@flothemes.com>
 */
class Flo_Instagram_Updater {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function update() {
    require_once plugin_dir_path( __FILE__ ) . './class-flo-compatibility.php';
    $floInstagram = new Flo_Instagram_Compatibility;
		$floInstagram->flo_update_data();
	}

}
