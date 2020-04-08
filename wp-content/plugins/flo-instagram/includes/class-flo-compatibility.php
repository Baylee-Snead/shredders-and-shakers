<?php

/**
 * Fired during plugin activation
 *
 * @link        https://flothemes.com/
 * @since      1.0.0
 *
 * @package    Flo_Instagram
 * @subpackage Flo_Instagram/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Flo_Instagram
 * @subpackage Flo_Instagram/includes
 * @author     Flothemes <support@flothemes.com>
 */
class Flo_Instagram_Compatibility {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
  public function __construct() {

 	}

	public function flo_update_data() {

    $updateDataStatus = array();
    if ( !get_option('flo_social_masterKey') ) {
      $user_id = get_option('flo_instagram_user_id');

      // if ( $user_id !== false) {
      //   if(is_numeric($user_id)) {
      //     $updateDataStatus = $this->flo_convert_uid_uat($user_id);
      //   } elseif(is_string($user_id) && strlen($user_id)) {
      //     $updateDataStatus = $this->flo_update_options_object_structure();
      //   }
      // 
      //   update_option('flo_instagram_comp_passed', true);
      // }
      if ( $user_id !== false) {
        $accounts = get_option('flo_instagram_accounts');
        if(intval($user_id) > 0 && !$accounts ) {
          $updateDataStatus = $this->flo_convert_uid_uat($user_id);
        } elseif(is_string($user_id) && strlen($user_id)) {
          $updateDataStatus = $this->flo_update_options_object_structure();
        }

        update_option('flo_instagram_comp_passed', true);
      }

    }
    return $updateDataStatus;
	}

  public function flo_convert_uid_uat($user_id = null, $userToken = null) {

    $user_id = $user_id === null ? get_option('flo_instagram_user_id') : $user_id;
    $userToken = $userToken === null ? get_option('flo_instagram_access_token') : $userToken;
    $response = [ 'plugin_version' => '1.4.8' ];

    if(
      $user_id !== false &&
      $userToken !== false &&
  		$user_id !== '' &&
  		$userToken !== '' &&
  		intval($user_id) !== 0 &&
  		intval($user_id) !== false
  	) {
  		$newUserData = $this->convertUserID($user_id, $userToken);
      $response['convertStatus'] = $newUserData;
  		if(
        isset($newUserData['user']) &&
        isset($newUserData['response']) &&
        $newUserData['user'] !== false &&
        $newUserData['response'] === 200
      ) {
  			$addResponse = $this->flo_add_user_no_ajax($newUserData['user']);
        $response['userAddStatus'] = $addResponse;
  		}
  	}

    return $response;
  }

  public function flo_update_options_object_structure() {

    $currentOptions = get_option('flo_instagram_accounts');
    $newOptions = [];
    $flag = false;
    $response = [
      'usersOptionUpdated' => false,
      'usersAdded' => []
    ];

    if ( is_array($currentOptions) && sizeof($currentOptions) ) {
      foreach ($currentOptions as &$value) {
         if ( is_array($value) && isset($value['username'])) {
           array_push($newOptions, $value['username']);
           $response['usersAdded'][] = $value['username'];
           if ( $value['master'] ) {
             update_option('flo_social_masterKey', $value['username']);
             if(function_exists('update_field')) {
               update_field('flo-instagram-footer-account', $value['username'], 'options');
             }
             $response['masterUpdated'] = $value['username'];
           }
           if(!$flag) {
             $flag = true;
           }
         } else {
           if ( !in_array($value, $newOptions) && is_string($value) && strlen($value)) {
             array_push($newOptions, $value);
             $response['usersAdded'][] = $value;
           }
         }
      }
      if ( $flag ) {
        update_option('flo_instagram_accounts', $newOptions);
        $response['newData'] = $newOptions;
        $response['usersOptionUpdated'] = true;
      }
    }

    return $response;
  }

  public function flo_add_user_no_ajax($newUser) {
  	$usersList = get_option('flo_instagram_accounts');
  	if(!$usersList) {
  		$usersList = array();
  	}
    $response = array();

  	$masterKey = get_option('flo_social_masterKey');
  	if(!$masterKey) {
  		update_option('flo_social_masterKey', $newUser);
      if(function_exists('update_field')) {
        update_field('flo-instagram-footer-account', $newUser, 'options');
      }
      $response['masterUpdated'] = $newUser;
  	}

  	if ( !in_array($newUser, $usersList) ) {
  		$usersList[] = $newUser;
  		update_option('flo_instagram_accounts', $usersList);
  		$response['userAdded'] = $newUser;
  	} else {
      $response['userAdded'] = false;
      $response['userExists'] = true;
    }
  	return $response;
  }

  public function convertUserID ($legacyID, $legacyToken) {

    $result = array();

  	if(is_numeric(trim($legacyID))) {
      $apiurl = "https://api.instagram.com/v1/users/".trim($legacyID)."/media/recent/?count=1&access_token=".$legacyToken;
      $object = wp_remote_get($apiurl);
      $response = wp_remote_retrieve_response_code($object);

      $result['response'] = $response;
      if($response === 200) {
       $thisObj = preg_match('/(?<=username":)(.*?)(?=})/', $object['body'], $user);
       if($thisObj) {
      	 $user = str_replace('"', '', $user["0"]);
      	 $user = str_replace(" ", "", $user);
         $result['user'] = $user;
       }
      } else {
        $result['user'] = false;
      }
  	}
  	return $result;
  }


  // SHORTCODE UTILITIES
  public function flo_check_shortcode_data($data) {

    $response = [
      'status' => true,
      'message' => 'User doesn\'t need transformations'
    ];

    // USER ID CHECK
    if(
      is_numeric($data['user_id']) &&
      strlen($data['access_token']) &&
      $data['access_token'] !== 'new'
    ) {
      $addReport = $this->flo_convert_uid_uat($data['user_id'], $data['access_token']);
      if(
        isset($addReport['convertStatus']['user']) &&
        isset($addReport['convertStatus']['response']) &&
        $addReport['convertStatus']['user'] !== false &&
        $addReport['convertStatus']['response'] === 200
      ) {
        $data['new_user_id'] = $addReport['convertStatus']['user'];
        $data['user_id'] = '';
        $data['access_token'] = 'new';
        $response['status'] = true;
        $response['message'] = 'All Good, user transformed';
      } else {
        $response['status'] = false;
        $response['message'] = 'Could not get user';
      }
    } else {
      $masterKey = get_option('flo_social_masterKey');
      if(
        $data['user_id'] === '' &&
        ($data['access_token'] === '' || $data['access_token'] === 'new') &&
        $masterKey !== false &&
        ($data['new_user_id'] === false || $data['new_user_id'] === '' || is_numeric($data['new_user_id']))
      ) {
        $data['new_user_id'] = $masterKey;
        $data['access_token'] = 'new';
      }
      // else {
      //   $data['new_user_id'] = $masterKey;
      //   $data['access_token'] = 'new';
      // }
    }

    // transform inherit to numeric value
    if($data['mobile_images_row'] === 'inherit') {
      $data['mobile_images_row'] = $data['limit'];
    }

    // PARSE IMAGE SIZE TO PROPER FORMAT
    $picture_size = $data['picture_sizes'];
    switch ($picture_size) {
      case 'thumbnail':
        $picture_size = '150x150_crop';
        break;
      case 'low_resolution':
        $picture_size = '240x240_crop';
        break;
      case 'standard_resolution':
        $picture_size = 'imagesFull';
        break;
      default:
        $picture_size = $data['picture_sizes'];
        break;
    }
    $data['picture_sizes'] = $picture_size;

    $response['shData'] = $data;
    return $response;
  }

}
