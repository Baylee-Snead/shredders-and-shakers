<?php
/**
 * Template part for displaying share links and categories
 */

$categories_list = get_the_category_list( '<span> </span>' );
?>

<aside class="share-entry">
	<div class="row align-items-lg-center">
		<div class="col-12 col-lg-auto">
			<?php
			/**
			 * Hook for additional entry footer content, like displaying social links
			 */
			do_action( 'theme_additional_entry_footer_content' );
			?>
		</div>

		<?php if ( $categories_list && castilo_categorized_blog() ) : ?>
			<div class="col-12 col-lg">
				<div class="categories">
					<span class="screen-reader-text"><?php esc_html_e( 'Posted in:', 'castilo' ); ?></span>
					<?php echo wp_kses_post( $categories_list ); ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</aside>
