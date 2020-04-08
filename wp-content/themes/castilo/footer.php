<?php
/**
 * The template for displaying the footer.
 */
?>

	<?php do_action( 'castilo_footer_before' ); ?>

	<footer id="footer" class="padding-top-bottom">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<?php get_template_part( 'template-parts/footer/footer-widgets' ); ?>
				</div>
				<div class="col-12">
					<?php get_template_part( 'template-parts/footer/navigation-social' ); ?>
				</div>
				<div class="copyright col-12">
					<?php get_template_part( 'template-parts/footer/site-info' ); ?>
				</div>
			</div>
		</div>
	</footer>

	<?php do_action( 'castilo_footer_after' ); ?>

<?php wp_footer(); ?>

</body>
</html>
