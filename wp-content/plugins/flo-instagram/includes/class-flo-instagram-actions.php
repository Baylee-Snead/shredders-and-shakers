<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link        https://flothemes.com/
 * @since      1.0.0
 *
 * @package    Flo_Instagram
 * @subpackage Flo_Instagram/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Flo_Instagram
 * @subpackage Flo_Instagram/includes
 * @author     Flothemes <support@flothemes.com>
 */
class Flo_Instagram_Actions {

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
    $this->flo_register_actions();
	}

  public function flo_register_actions() {
    add_action('wp_ajax_flo_add_user', array(&$this, 'flo_add_user'));
    add_action('wp_ajax_flo_delete_user', array(&$this, 'flo_delete_user'));
		add_action('wp_ajax_flo_get_user_object', array(&$this, 'flo_get_user_object'));
		add_action('wp_ajax_flo_get_all_user_objects', array(&$this, 'flo_get_all_user_objects'));
		add_action('wp_ajax_flo_make_master', array(&$this, 'flo_make_master'));
		add_action('wp_ajax_flo_update_cache_time', array(&$this, 'flo_update_cache_time'));
		add_action('wp_ajax_flo_update_user_transient', array(&$this, 'flo_update_user_transient'));
		add_action('wp_ajax_flo_reset_all_instagram_data', array(&$this, 'flo_reset_all_instagram_data'));
		
		add_action('wp_ajax_nopriv_flo_update_user_transient', array(&$this, 'flo_update_user_transient'));
  }
	
	public function recursively_sanitize ($data = array()) {
		if (!is_array($data) || !count($data)) {
			return array();
		}
		foreach ($data as $k => $v) {
			if (!is_array($v) && !is_object($v)) {
				$data[$k] = sanitize_text_field(trim($v));
			}
			if (is_array($v)) {
				$data[$k] = $this->recursively_sanitize($v);
			}
		}
		return $data;
	}
	
  public function flo_add_user() {
		check_ajax_referer( 'flo_instagram_settings_security_nonce', 'security' );
		$usersList = get_option('flo_instagram_accounts');
		if(!$usersList) {
			$usersList = array();
		}
		
		$response = array();
		$newUser = sanitize_text_field($_POST['user']);
		
		$newUserObj = $this->recursively_sanitize($_POST['userObj']);
		$newUserObj = json_encode($newUserObj);
		
		$cacheTime = get_option('flo_instagram_cache_time');
		if($cacheTime === false || $cacheTime === '') {
			$cacheTime = 60;
		}

		$masterKey = get_option('flo_social_masterKey');
		if(!$masterKey) {
			update_option('flo_social_masterKey', $newUser);
			$response['masterUpdate']['message'] = 'no previous master found, setting ' . $newUser . ' user as master';
			$response['masterUpdate']['action'] = true;
		}

		if ( in_array($newUser, $usersList) ) {
			if ( get_transient('flo_instagram_'.$newUser) ) {
				$response[$newUser]['message'] = 'Account "'. $newUser .'" already exists';
				$response[$newUser]['action'] = false;
			} else {
				set_transient('flo_instagram_'.$newUser, $newUserObj, $cacheTime * 60);

				$response[$newUser]['message'] = $newUser . ' expired transient, refreshing';
				$response[$newUser]['action'] = true;
				$response[$newUser]['data'] = $this->recursively_sanitize($_POST['userObj']);
			}
		} else {

			$usersList[] = $newUser;
			update_option('flo_instagram_accounts', $usersList);
			set_transient('flo_instagram_'.$newUser, $newUserObj, $cacheTime * 60);

			$response[$newUser]['message'] = $newUser . ' added succesfully';
			$response[$newUser]['data'] = $this->recursively_sanitize($_POST['userObj']);
			$response[$newUser]['action'] = true;

		}

		echo json_encode($response);
    die();
  }

  public function flo_delete_user() {
		check_ajax_referer( 'flo_instagram_settings_security_nonce', 'security' );

		$usersList = get_option('flo_instagram_accounts');
		$response = array();
		$requestedUser = sanitize_text_field($_POST['user']);

		if (($key = array_search($requestedUser, $usersList)) !== false) {
	    unset($usersList[$key]);
			$usersList = array_values($usersList);

			if(sizeof($usersList) === 0) {
				update_option('flo_social_masterKey', false);

				$response['masterUpdated']['message'] = 'Deleting master account, no more accounts available';
				$response['masterUpdated']['action'] = false;
			} elseif($requestedUser === get_option('flo_social_masterKey')) {
				update_option('flo_social_masterKey', $usersList[0]);

				$response['masterUpdated']['data'] = $usersList[0];
				$response['masterUpdated']['message'] = 'Deleting master account, assigning next regular user as next master';
				$response['masterUpdated']['action'] = true;
			}
			
			if(function_exists('get_field')) {
				$footer_account = get_field('flo-instagram-footer-account', 'options');
				$masterKey = get_option('flo_social_masterKey');
				if(
					$footer_account !== false && 
					$footer_account === $requestedUser && 
					$masterKey !== false && 
					is_string($masterKey) && 
					strlen($masterKey)
				) {
					update_field('flo-instagram-footer-account', $masterKey, 'options');
					if(function_exists('flo_delete_theme_options_transient')) {
						flo_delete_theme_options_transient();
					}
				}	
			}
			
			
			update_option( 'flo_instagram_accounts', $usersList );
			delete_transient( 'flo_instagram_'.$requestedUser );
			$response['message'] = 'User removed succesfully';
			$response['action'] = true;
		} else {
			$response['message'] = 'This user doesn\'t exist';
			$response['action'] = false;
		}

		echo json_encode($response);
    die();
  }

	public function flo_get_user_object() {
		check_ajax_referer( 'flo_instagram_settings_security_nonce', 'security' );

		$response = array();
		$requestedUser = sanitize_text_field($_POST['user']);
		$object = get_transient( 'flo_instagram_'.$requestedUser );

		if (empty($object)) {
			$response['message'] = 'This user doesn\'t exist';
			$response['action'] = false;
		} else {
			$response['message'] = 'User found';
			$response['action'] = true;
			$response['object'] = $object;
		}

		echo json_encode($response);
		die();
	}

	public function flo_make_master() {
		check_ajax_referer( 'flo_instagram_settings_security_nonce', 'security' );
		$requestedUser = sanitize_text_field($_POST['user']);
		update_option('flo_social_masterKey', $requestedUser);
		echo json_encode([
			'message' => $requestedUser . ' marked as master',
			'action' => true
		]);
		die();
	}

	public function flo_get_all_user_objects() {
		$allAccounts = get_option('flo_instagram_accounts');
		$response = [];
		if($allAccounts !== false && sizeof($allAccounts)) {
			$data = [];
			foreach ($allAccounts as $account) {
				$data[$account] = get_transient('flo_instagram_' . $account);
			}
			$response['data'] = $data;
			$response['message'] = 'Account data fetched succesfully';
			$response['action'] = true;
		} else {
			$response['action'] = false;
			$response['message'] = 'No users';
		}

		echo json_encode($response);
		die();
	}

	public function flo_update_cache_time() {
		check_ajax_referer( 'flo_instagram_settings_security_nonce', 'security' );

		$newVal = intval(sanitize_text_field($_POST['cacheVal']));
		$newCache = update_option('flo_instagram_cache_time', $newVal);
		
		if($newCache) {
			$response = [
				'action' => true,
				'data' => $newVal,
				'message' => 'Cache time updated successfully!'
			];
		} else {
			if(!get_option('flo_instagram_cache_time')) {
				$response = [
					'action' => false,
					'message' => 'Something went wrong, cache time could not be updated'
				];
			} else {
				$response = [
					'action' => true,
					'data' => get_option('flo_instagram_cache_time'),
					'message' => 'Option not changed, value is the same'
				];
			}
		}

		echo json_encode($response);
		die();
	}

	public function flo_update_user_transient() {
		
		if(isset($_POST['security']) && isset($_POST['transient'])) {
			if(isset($_POST['fromFront']) && $_POST['fromFront'] == true) {
				$user = sanitize_text_field($_POST['user']);
				$referName = 'transient_nonce_' . $user;
			} else {
				$referName = 'flo_instagram_settings_security_nonce';
			}
			
			check_ajax_referer( $referName, 'security' );
			
			$transient = $this->recursively_sanitize($_POST['transient']);
			$username = $transient['username'];
			
			$cacheTime = get_option('flo_instagram_cache_time');
			if($cacheTime === false || $cacheTime === '') {
				$cacheTime = 60;
			} else {
				$cacheTime = intval($cacheTime);
			}
			
			$userObj = json_encode($transient);
			
			set_transient( 'flo_instagram_' . $username, $userObj, $cacheTime * 60);
			$response = [
				'action' => true,
				'data' => $userObj,
				'message' => 'Successfully updated transient for user ' . $username
			];
			
			echo json_encode($response);
	 		wp_die();
		}
		
		wp_die();
		
	}
	
	public function flo_reset_all_instagram_data() {
		$accounts = get_option('flo_instagram_accounts');
		
		if(is_array($accounts) && sizeof($accounts)) {
			foreach ($accounts as $account) {
				if(is_string($account)) {
					delete_transient('flo_instagram_' . $account);	
				}
			}
		}
		
		update_option('flo_instagram_accounts', []);
		update_option('flo_social_masterKey', '');
		update_option('flo_instagram_user_id', '');
		update_option('flo_instagram_access_token', '');
		update_option('flo_instagram_cache_time', 60);
		
		echo json_encode(['success' => true]);
		wp_die();
	}
	
}
