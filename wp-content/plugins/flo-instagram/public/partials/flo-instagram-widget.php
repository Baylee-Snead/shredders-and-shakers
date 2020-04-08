<?php if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'widgets_init', 'flo_widget_instagram' );

function flo_widget_instagram() {
	register_widget( 'widget_flo_instagram' );
}

/**
 * Instagrm_Feed_Widget Class
 */
class widget_flo_instagram extends WP_Widget {
	/** constructor */
	function __construct() {
		$options = array( 'classname' => 'flo-instagram_widget', 'description' => __('Flo Social Widget' , 'flotheme' ) );
		parent::__construct( 'widget_flo_instagram' , __( 'Flo Social Widget' , 'flotheme' )  , $options );

	}
	/* WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		//get widget information to display on page
		$title = apply_filters( 'widget_title', $instance['title'] );

    $below_title = isset($instance[ 'below_title' ]) ? esc_attr( $instance[ 'below_title' ] ) : '';
    $uid_or_tag = isset($instance[ 'uid_or_tag' ]) ? esc_attr( $instance[ 'uid_or_tag' ] ) : 'user_id';
    $uid_tag = isset($instance[ 'uid_tag' ]) ? esc_attr( $instance[ 'uid_tag' ] ) : '';
    $picture_number = isset($instance[ 'picture_number' ]) ? esc_attr( $instance[ 'picture_number' ] ) : 6;
    $picture_number_mobile = isset($instance[ 'picture_number_mobile' ]) ? esc_attr( $instance[ 'picture_number_mobile' ] ) : 6;
    $padding_between = isset($instance[ 'padding_between' ]) ? esc_attr( $instance[ 'padding_between' ] ) : 10;
    $nr_columns = isset($instance[ 'nr_columns' ]) && is_numeric($instance[ 'nr_columns' ]) ? esc_attr( $instance[ 'nr_columns' ] ) : 6;
    $show_id_hashtag = isset($instance[ 'show_id_hashtag' ]) ? esc_attr( $instance[ 'show_id_hashtag' ] ) : false;
    $user_name = isset($instance[ 'user_name' ]) ? esc_attr( $instance[ 'user_name' ] ) : '';
    $user_id = isset($instance[ 'user_id' ]) ? esc_attr( $instance[ 'user_id' ] ) : '';
    $access_token = isset($instance[ 'access_token' ]) ? esc_attr( $instance[ 'access_token' ] ) : '';
    $users = isset($instance[ 'users' ]) ? esc_attr( $instance[ 'users' ] ) : '';
    $picture_size = isset($instance[ 'picture_size' ]) ? esc_attr( $instance[ 'picture_size' ] ) : '150x150_crop';
    $link_images = isset($instance[ 'link_images' ]) ? esc_attr( $instance[ 'link_images' ] ) : false;

		if ( is_numeric($user_id) && isset($access_token) && $access_token !=='' ) {
				$path = dirname(dirname(plugin_dir_path( __FILE__ ))) . '/includes/class-flo-compatibility.php';

				require_once $path;
				$comp = new Flo_Instagram_Compatibility();
				$legacyAccount = $comp->flo_convert_uid_uat($user_id, $access_token);

				if ( isset($legacyAccount['convertStatus']) && $legacyAccount['convertStatus']['response'] === 200 ) {
					$legacyAccountUser = $legacyAccount['convertStatus']['user'];

					$oldInstance = array();
					$oldInstance = array_merge($oldInstance, $instance);

					$user_name = $legacyAccountUser;
					$users = $legacyAccountUser;
					$instance[ 'users' ]= $legacyAccountUser;
					$instance[ 'user_name' ] = $legacyAccountUser;
					$instance[ 'user_id' ] = '';
					$instance[ 'access_token' ] = '';

					$this->update( $instance, $oldInstance );
				}
		}

		echo $before_widget;
		if ( $title ) {
			if (strlen($below_title)) {
				$below_title_content = '<div class="below-title-inst">'.$below_title.'</div>';
			}else{
				$below_title_content = '';
			}
			echo $before_title . $title . $below_title_content . $after_title;
		};
		$link_images = $link_images === 'on' ? 1 : '';
		$show_id_hashtag = $show_id_hashtag === 'on' ? 1 : '';

		if ( $show_id_hashtag ) {
			echo '<div class="insta-widget__wrapper">
							<li class="insta-profile-btn"><a href="http://instagram.com/'.$users.'" target="_blank" class="insta-profile">@'.$users.'</a></li>';

		}
		echo do_shortcode('[flo_instagram padding="'.$padding_between.'" mobile_images_row="'.$picture_number_mobile.'" use_pattern="" picture_sizes="'.$picture_size.'" link="'.$link_images.'" nr_columns="'.$nr_columns.'" limit="'.$picture_number.'" new_user_id="'.$users.'" user_id="" access_token="new" ]');
		if ( $show_id_hashtag ) {
			echo '</div>';
		}
		echo $after_widget;
	}

	/* WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//update setting with information form widget form
		if (
			isset($instance[ 'top_padding' ]) &&
			isset($instance[ 'right_padding' ]) &&
			isset($instance[ 'bottom_padding' ]) &&
			isset($instance[ 'left_padding' ])
		) {
			$paddingDefault = min($instance[ 'top_padding' ], $instance[ 'right_padding' ], $instance[ 'bottom_padding' ], $instance[ 'left_padding' ]);
		} else {
			$paddingDefault = 5;
		}

		$instance['title'] = strip_tags($new_instance['title']);
		$instance['below_title'] = strip_tags($new_instance['below_title']);
		$instance['uid_or_tag'] = strip_tags($new_instance['uid_or_tag']);
		$instance['uid_tag'] = strip_tags($new_instance['uid_tag']);
		$instance['picture_number'] = strip_tags($new_instance['picture_number']);
		$instance['picture_number_mobile'] = strip_tags($new_instance['picture_number_mobile']);
		$instance['padding_between'] = isset($new_instance['padding_between']) ? strip_tags($new_instance['padding_between']) : $paddingDefault;


		$instance['nr_columns'] = strip_tags($new_instance['nr_columns']);
		$instance['show_id_hashtag'] = strip_tags($new_instance['show_id_hashtag']);
		$instance['user_name'] = strip_tags($new_instance['user_name']);
		$instance['user_id'] = strip_tags($new_instance['user_id']);
		$instance['access_token'] = strip_tags($new_instance['access_token']);
		$instance['users'] = strip_tags($new_instance['users']);

		$instance['picture_size'] = strip_tags($new_instance['picture_size']);
		$instance['link_images'] = strip_tags($new_instance['link_images']);

		return $instance;
	}

	/* WP_Widget::form */
	function form( $instance ) {
		if ( $instance ) {
			if (
				isset($instance[ 'top_padding' ]) &&
				isset($instance[ 'right_padding' ]) &&
				isset($instance[ 'bottom_padding' ]) &&
				isset($instance[ 'left_padding' ])
			) {
				$paddingDefault = min($instance[ 'top_padding' ], $instance[ 'right_padding' ], $instance[ 'bottom_padding' ], $instance[ 'left_padding' ]);
			} else {
				$paddingDefault = 5;
			}

			$title = esc_attr( $instance[ 'title' ] );
      $below_title = isset($instance[ 'below_title' ]) ? esc_attr( $instance[ 'below_title' ] ) : '';
      $uid_or_tag = isset($instance[ 'uid_or_tag' ]) ? esc_attr( $instance[ 'uid_or_tag' ] ) : 'user_id';
      $uid_tag = isset($instance[ 'uid_tag' ]) ? esc_attr( $instance[ 'uid_tag' ] ) : '';
      $picture_number = isset($instance[ 'picture_number' ]) ? esc_attr( $instance[ 'picture_number' ] ) : 6;
      $picture_number_mobile = isset($instance[ 'picture_number_mobile' ]) ? esc_attr( $instance[ 'picture_number_mobile' ] ) : 6;
      $padding_between = isset($instance[ 'padding_between' ]) ? esc_attr( $instance[ 'padding_between' ] ) : $paddingDefault;
      $nr_columns = isset($instance[ 'nr_columns' ]) && is_numeric($instance[ 'nr_columns' ]) ? esc_attr( $instance[ 'nr_columns' ] ) : 6;
      $show_id_hashtag = isset($instance[ 'show_id_hashtag' ]) ? esc_attr( $instance[ 'show_id_hashtag' ] ) : false;
      $user_name = isset($instance[ 'user_name' ]) ? esc_attr( $instance[ 'user_name' ] ) : '';
      $user_id = isset($instance[ 'user_id' ]) ? esc_attr( $instance[ 'user_id' ] ) : '';
      $access_token = isset($instance[ 'access_token' ]) ? esc_attr( $instance[ 'access_token' ] ) : '';
      $users = isset($instance[ 'users' ]) ? esc_attr( $instance[ 'users' ] ) : '';
      $picture_size = isset($instance[ 'picture_size' ]) ? esc_attr( $instance[ 'picture_size' ] ) : '150x150_crop';
      $link_images = isset($instance[ 'link_images' ]) ? esc_attr( $instance[ 'link_images' ] ) : false;
		} else {
			$title = __( 'Follow Me', 'flotheme' );
			$below_title = 'on instagram';
			$uid_or_tag = 'user_id';

			$uid_tag = '';
			$username = __( 'Username', 'flotheme' );
			$picture_size = '150x150_crop';
			$picture_number = 6;
      $picture_number_mobile = 6;
      $padding_between = 5;
			$show_id_hashtag = false;
			$user_name = '';
			$nr_columns = 6;
			$link_images = false;
			$user_id = '';
			$access_token = '';
			$users = '';


		}

		$picture_sizes = array('150x150_crop' => 'Crop 150x150', '240x240_crop' => 'Crop 240x240', '320x320_crop' => 'Crop 320x320', '480x480_crop' => 'Crop 480x480', '640x640_crop' => 'Crop 640x640', 'standard_resolution' => 'Full Images');
		?>

  		<div class="flo_instagram_widget--fields-wrapper inline">
  			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','flotheme'); ?></label>
  			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
  		</div>

  		<div class="flo_instagram_widget--fields-wrapper inline">
  			<label for="<?php echo $this->get_field_id('below_title'); ?>"><?php _e('Below title label:','flotheme'); ?></label>
  			<input class="widefat" id="<?php echo $this->get_field_id('below_title'); ?>" name="<?php echo $this->get_field_name('below_title'); ?>" type="text" value="<?php echo $below_title; ?>" />
  		</div>

  		<div class="flo_instagram_widget--fields-wrapper inline">
  			<label for="<?php echo $this->get_field_id('padding_between'); ?>"><?php _e('Padding between images:','flotheme'); ?></label>
        <div class="flo_instagram_widget--slider-controler">
          <div class="range_slider" data-target="<?php echo $this->get_field_id('padding_between'); ?>" data-min="0" data-max="20"></div>
          <input id="<?php echo $this->get_field_id('padding_between'); ?>" type="text" name="<?php echo $this->get_field_name('padding_between'); ?>" val="<?php echo $padding_between; ?>">
        </div>
  		</div>

  		<div class="flo_instagram_widget--fields-wrapper inline">
  			<label for="<?php echo $this->get_field_id('nr_columns'); ?>"><?php _e('Images per row:','flotheme'); ?></label>
        <div class="flo_instagram_widget--slider-controler">
          <div class="range_slider" data-target="<?php echo $this->get_field_id('nr_columns'); ?>" data-min="1" data-max="12"></div>
          <input id="<?php echo $this->get_field_id('nr_columns'); ?>" type="text" name="<?php echo $this->get_field_name('nr_columns'); ?>" val="<?php echo $nr_columns; ?>">
        </div>
  		</div>

  		<div class="flo_instagram_widget--fields-wrapper inline">
  			<label for="<?php echo $this->get_field_id('picture_number'); ?>"><?php _e('Number of Images:','flotheme'); ?></label>
        <div class="flo_instagram_widget--slider-controler">
          <div class="range_slider" data-target="<?php echo $this->get_field_id('picture_number'); ?>" data-min="1" data-max="12"></div>
          <input id="<?php echo $this->get_field_id('picture_number'); ?>" type="text" name="<?php echo $this->get_field_name('picture_number'); ?>" val="<?php echo $picture_number; ?>">
        </div>
  		</div>

  		<div class="flo_instagram_widget--fields-wrapper">
  			<label for="<?php echo $this->get_field_id('picture_size'); ?>"><?php _e('Picture Size:','flotheme'); ?></label>
  			<select id="<?php echo $this->get_field_id('picture_size'); ?>" name="<?php echo $this->get_field_name('picture_size'); ?>">
  				<?php foreach($picture_sizes as $item => $val):?>
  					<option value="<?php echo $item;?>" <?php if ($item == $picture_size) {echo 'selected="selected"';};?>><?php echo $val;?></option>
  				<?php endforeach;?>
  			</select>
  		</div>

      <div class="flo_instagram_widget--fields-wrapper">
	        <?php if (!get_option('flo_instagram_accounts')) { ?>
	          <a class="flo_instagram_widget--error-wrapper" href="admin.php?page=flo_instagram" target="_blank">
	            <div class="flo_instagram_widget--error">
	              <?php echo sprintf(__('Please add at least one user from the settings page','flotheme'),'<a href="admin.php?page=flo_instagram">', '</a>'); ?>
	            </div>
	          </a>
	        <?php } else { $IGusers = get_option('flo_instagram_accounts'); ?>
	          <label for="<?php echo $this->get_field_id('users'); ?>"><?php _e('Users:','flotheme'); ?></label>
	          <select class="widefat" name="<?php echo $this->get_field_name('users'); ?>" id="<?php echo $this->get_field_id('users'); ?>" value="<?php echo get_option('flo_social_masterKey'); ?>" >
							<?php if ( is_numeric($user_id) && isset($access_token) && $access_token !=='' ) {
									$path = dirname(dirname(plugin_dir_path( __FILE__ ))) . '/includes/class-flo-compatibility.php';

									require_once $path;
									$comp = new Flo_Instagram_Compatibility();
									$legacyAccount = $comp->flo_convert_uid_uat($user_id, $access_token);
									if ( isset($legacyAccount['convertStatus']) && $legacyAccount['convertStatus']['response'] === 200 ) {
										$legacyAccountUser = $legacyAccount['convertStatus']['user'];

										$user_name = $legacyAccountUser;
										$users = $legacyAccountUser;
										$instance[ 'users' ]= $legacyAccountUser;
										$instance[ 'user_name' ] = $legacyAccountUser;
										$instance[ 'user_id' ] = '';
										$instance[ 'access_token' ] = '';

										array_push($IGusers, $users);
									}

							}
							foreach ($IGusers as $key => $user) { ?>
								<option value="<?php echo $user; ?>" <?php if ($user == $users) {echo 'selected="selected"';}; ?>> <?php echo $user; ?></option>
							<?php } ?>
	          </select>
          <?php } ?>
  		</div>

  		<div class="flo_instagram_widget--fields-wrapper inline">
  			<label for="<?php echo $this->get_field_id('picture_number_mobile'); ?>"><?php _e('Number of images for mobile devices:','flotheme'); ?></label>
        <div class="flo_instagram_widget--slider-controler">
          <div class="range_slider" data-target="<?php echo $this->get_field_id('picture_number_mobile'); ?>" data-min="1" data-max="12"></div>
          <input id="<?php echo $this->get_field_id('picture_number_mobile'); ?>" type="text" name="<?php echo $this->get_field_name('picture_number_mobile'); ?>" val="<?php echo $picture_number_mobile; ?>">
        </div>
  		</div>

  		<div class="flo_instagram_widget--fields-wrapper">
  			<label for="<?php echo $this->get_field_id('link_images'); ?>"><?php _e('Link images to full image:','flotheme'); ?></label>
  			<input class="" id="<?php echo $this->get_field_id('link_images'); ?>" name="<?php echo $this->get_field_name('link_images'); ?>" type="checkbox" <?php echo (($link_images)? "CHECKED":''); ?> />
  		</div>

			<div class="flo_instagram_widget--fields-wrapper">
  			<label for="<?php echo $this->get_field_id('show_id_hashtag'); ?>"><?php _e('Show profile link:','flotheme'); ?></label>
  			<input class="" id="<?php echo $this->get_field_id('show_id_hashtag'); ?>" name="<?php echo $this->get_field_name('show_id_hashtag'); ?>" type="checkbox" <?php echo (($show_id_hashtag)? "CHECKED":''); ?> />
  		</div>
		<?php }
} ?>
