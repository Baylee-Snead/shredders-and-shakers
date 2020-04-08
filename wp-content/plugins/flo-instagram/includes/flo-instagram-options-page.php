<div class="floInstagram">
  <?php
    settings_fields( 'flo_instagram_settings' );
    do_settings_sections( 'flo_instagram_settings' );
    wp_nonce_field( 'flo_instagram_settings_security_nonce', 'security' );
    
    // logging messages enabled only in dev mode
    if(defined('FLO_ENVIROMENT') && FLO_ENVIROMENT === 'DEV') {
			echo '<script>window.IS_FLO_DEV = true</script>';
		}
  ?>
  <div id="floInstagram__root"></div>
</div>
