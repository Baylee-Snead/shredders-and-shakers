<?php

if (!function_exists('flo_instagram_shortcode')) {
  function flo_instagram_shortcode( $atts, $content = null ) {

    require_once dirname(plugin_dir_path( __DIR__ )) . '/includes/class-flo-compatibility.php';
  	$compatibility = new Flo_Instagram_Compatibility();

    if(!get_option('flo_instagram_comp_passed')) {
      $updateStatus = $compatibility->flo_update_data();
    }

    $defaults = [
      'padding' => '20',
      'use_pattern' => '',
      'crop' => 0,
      'picture_sizes' =>'standard_resolution',
      'link' => 0,
      'nr_columns' => 4,
      'hide_mobile' => 0,
      'limit' => 10,
      'mobile_images_row' => 'inherit',
      'user_id' => '',
      'new_user_id' => get_option('flo_instagram_masterKey'),
      'access_token' => '',
      'hashtag' => ''
    ];

    $shData = shortcode_atts( $defaults, $atts );
  	$compatibilityCheckResults = $compatibility->flo_check_shortcode_data($shData);
    $sh = $compatibilityCheckResults['shData'];

    if (is_singular() || is_archive() || is_home() || is_search() || is_404()) {
      $transient = get_transient('flo_instagram_' . $sh['new_user_id']);
      $nr_columns_class = (isset($sh['nr_columns'])) ? 'columns'.$sh['nr_columns'] : 'columns6';

      ob_start();
      ?>
        <div
          class="flo-shcode-instgm-container <?php echo $nr_columns_class; ?>"
          style="--col: <?php echo $sh['nr_columns']; ?>;"
          data-attr="<?php echo htmlentities(json_encode($sh)) ?>"
          <?php if($transient) { ?>
            data-transient="<?php echo htmlentities($transient); ?>"
          <?php } else {?>
            data-user="<?php echo $sh['new_user_id']; ?>"
            data-nonce="<?php echo wp_create_nonce( 'transient_nonce_' . $sh['new_user_id'] ); ?>"
          <?php } ?>
        ></div>
      <?php

      return ob_get_clean();
    }
  }
  add_shortcode('flo_instagram', 'flo_instagram_shortcode');
}

?>
