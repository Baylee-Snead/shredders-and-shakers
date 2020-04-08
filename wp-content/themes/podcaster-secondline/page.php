<?php

/**
 *
 * @package slt
 * @since slt 1.0
 */
get_header(); ?>
	
	
	<?php if(!get_post_meta($post->ID, 'secondline_themes_disable_page_title', true)  ): ?>
	<div id="page-title-slt">
		<div class="width-container-slt">
			<div id="secondline-themes-page-title-container">
				<?php the_title( '<h1 class="page-title">', '</h1>' ); ?>
				<?php if(get_post_meta($post->ID, 'secondline_themes_sub_title', true)): ?><h4 class="secondline-sub-title"><?php echo wp_kses( get_post_meta($post->ID, 'secondline_themes_sub_title', true) , true); ?></h4><?php endif; ?>
			</div><!-- close #secondline-themes-page-title-container -->
			<div class="clearfix-slt"></div>
		</div><!-- close .width-container-slt -->			
	</div><!-- #page-title-slt -->
	<?php endif; ?>


	<div id="content-slt">
		<div class="width-container-slt<?php if(get_post_meta($post->ID, 'secondline_themes_page_sidebar', true) == 'left-sidebar' ) : ?> left-sidebar-slt<?php endif; ?>">


			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'template-parts/content', 'page' ); ?>
			<?php endwhile; ?>
			
			<div class="clearfix-slt"></div>
		</div><!-- close .width-container-slt -->
	</div><!-- #content-slt -->
	
<?php get_footer(); ?>