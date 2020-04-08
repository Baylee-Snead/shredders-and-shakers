<?php
/**
 * Displays header call to action
 */


$call_to_action_content = get_theme_mod( 'header_call_to_action' );
if ( $call_to_action_content ) :
?>
	<div class="call-to-action col-12 col-sm-auto order-5 order-sm-2 order-lg-4">
		<?php echo wp_kses_post( $call_to_action_content ); ?>
	</div>

<?php endif;
