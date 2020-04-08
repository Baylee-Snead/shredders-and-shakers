<?php
/**
 * Castilo Customizer functionality.
 */

/**
 * Adds Customizer settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 */
function castilo_customize_register( $wp_customize ) {

	// Add postMessage support for the site title and tagline.
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'background_color' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial( 'blogname', array(
			'selector'            => '.site-branding-text h1 a',
			'container_inclusive' => false,
			'render_callback'     => function() {
				return get_bloginfo( 'name', 'display' );
			},
		) );
		$wp_customize->selective_refresh->add_partial( 'blogdescription', array(
			'selector'            => '.site-branding-text .site-description',
			'container_inclusive' => false,
			'render_callback'     => function() {
				return get_bloginfo( 'description' );
			},
		) );
	}

	// Primary color.
	$wp_customize->add_setting( 'primary_color', array(
		'default'           => '#cc00aa',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'primary_color', array(
		'label'    => esc_html__( 'Primary Color', 'castilo' ),
		'section'  => 'colors',
		'priority' => 5,
	) ) );

	// Header overlay opacity.
	$wp_customize->add_setting( 'header_overlay_opacity', array(
		'default'           => '75',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'castilo_sanitize_opacity',
	) );
	$wp_customize->add_control( 'header_overlay_opacity', array(
		'label'       => esc_html__( 'Overlay Opacity', 'castilo' ),
		'description' => esc_html__( 'Control the overlay opacity (to make text easier to read).', 'castilo' ),
		'section'     => 'header_image',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 100,
			'step' => 5,
		),
		'active_callback' => 'has_custom_header',
	) );

	// The theme doesn't allow any background image.
	$wp_customize->remove_section( 'background_image' );

	/* Additional Theme Options section */
	$wp_customize->add_section( 'additional_theme_options', array(
		'capability' => 'edit_theme_options',
		'title'      => esc_html__( 'Additional Options', 'castilo' ),
		'priority'   => 160,
	) );

	// Add option to make the primary menu sticky.
	$wp_customize->add_setting( 'primary_menu_sticky', array(
		'capability'        => 'edit_theme_options',
		'default'           => 1,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'primary_menu_sticky', array(
		'label'    => esc_html__( 'Sticky Top Menu', 'castilo' ),
		'settings' => 'primary_menu_sticky',
		'section'  => 'additional_theme_options',
		'type'     => 'checkbox',
	) );

	// Add option to avoid the multiplication effect on some images.
	$wp_customize->add_setting( 'avoid_image_multiply', array(
		'capability'        => 'edit_theme_options',
		'default'           => 0,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'avoid_image_multiply', array(
		'label'    => esc_html__( 'Avoid Image Multiply Effect', 'castilo' ),
		'settings' => 'avoid_image_multiply',
		'section'  => 'additional_theme_options',
		'type'     => 'checkbox',
	) );

	// Add option to disable the download button for episodes.
	$wp_customize->add_setting( 'no_episode_download', array(
		'capability'        => 'edit_theme_options',
		'default'           => 0,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'no_episode_download', array(
		'label'    => esc_html__( 'Disable Episode Download Button', 'castilo' ),
		'settings' => 'no_episode_download',
		'section'  => 'additional_theme_options',
		'type'     => 'checkbox',
	) );

	// Add option to avoid loading the default fonts.
	$wp_customize->add_setting( 'load_theme_fonts', array(
		'capability'        => 'edit_theme_options',
		'default'           => 1,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'load_theme_fonts', array(
		'label'       => esc_html__( 'Load Default Theme Fonts', 'castilo' ),
		'description' => esc_html__( 'Uncheck if you want to use your own typography (maybe by using a specialized plugin).', 'castilo' ),
		'settings'    => 'load_theme_fonts',
		'section'     => 'additional_theme_options',
		'type'        => 'checkbox',
	) );

	// Header additional padding.
	$wp_customize->add_setting( 'featured_image_padding', array(
		'default'           => '0',
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'featured_image_padding', array(
		'label'       => esc_html__( 'Featured Image Padding', 'castilo' ),
		'description' => esc_html__( 'Additional top and bottom padding for the featured area.', 'castilo' ),
		'section'     => 'additional_theme_options',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 20,
			'step' => 1,
		),
	) );
	// Set header call to action information.
	$wp_customize->add_setting( 'header_call_to_action', array(
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'wp_kses_post',
		'transport'         => 'postMessage',
	) );
	$wp_customize->add_control( 'header_call_to_action', array(
		'label'    => esc_html__( 'Header Call to Action', 'castilo' ),
		'settings' => 'header_call_to_action',
		'section'  => 'additional_theme_options',
		'type'     => 'textarea',
	) );

	// Add option to make the footer sticky.
	$wp_customize->add_setting( 'footer_sticky', array(
		'capability'        => 'edit_theme_options',
		'default'           => 0,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'footer_sticky', array(
		'label'    => esc_html__( 'Sticky Footer', 'castilo' ),
		'settings' => 'footer_sticky',
		'section'  => 'additional_theme_options',
		'type'     => 'checkbox',
	) );

	// Set footer copyright information.
	$wp_customize->add_setting( 'site_copyright', array(
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'wp_kses_post',
		'default'           => '&copy; {{year}} {{site-title}}. All Rights Reserved.',
		'transport'         => 'postMessage',
	) );
	$wp_customize->add_control( 'site_copyright', array(
		'label'    => esc_html__( 'Footer Copyright', 'castilo' ),
		'settings' => 'site_copyright',
		'section'  => 'additional_theme_options',
		'type'     => 'textarea',
	) );

	/* Footer Call to action */
	$wp_customize->add_section( 'footer_banner', array(
		'capability' => 'edit_theme_options',
		'title'      => esc_html__( 'Footer Banner', 'castilo' ),
		'priority'   => 155,
	) );

	// Footer banner image
	$wp_customize->add_setting( 'footer_banner_image', array(
		'sanitize_callback' => 'esc_url',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'footer_banner_image', array(
		'label'   => esc_html__( 'Cover Image', 'castilo' ),
		'section' => 'footer_banner',
	) ) );

	// Set footer call to action text
	$wp_customize->add_setting( 'footer_banner_content', array(
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'footer_banner_content', array(
		'label'    => esc_html__( 'Content', 'castilo' ),
		'settings' => 'footer_banner_content',
		'section'  => 'footer_banner',
		'type'     => 'textarea',
	) );

	// Footer banner background image
	$wp_customize->add_setting( 'footer_banner_background_image', array(
		'sanitize_callback' => 'esc_url',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'footer_banner_background_image', array(
		'label'   => esc_html__( 'Background Image', 'castilo' ),
		'section' => 'footer_banner',
	) ) );

	// Header overlay opacity.
	$wp_customize->add_setting( 'footer_banner_overlay_opacity', array(
		'default'           => '80',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'castilo_sanitize_opacity',
	) );
	$wp_customize->add_control( 'footer_banner_overlay_opacity', array(
		'label'       => esc_html__( 'Background Overlay Opacity', 'castilo' ),
		'description' => esc_html__( 'Control the banner overlay opacity to make text easier to read.', 'castilo' ),
		'section'     => 'footer_banner',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 100,
			'step' => 5,
		),
	) );

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial( 'header_call_to_action', array(
			'selector'            => '#top .call-to-action',
			'container_inclusive' => true,
			'render_callback'     => 'castilo_customize_partial_header_call_to_action',
		) );
		$wp_customize->selective_refresh->add_partial( 'site_copyright', array(
			'selector'            => '#footer .site-info',
			'container_inclusive' => true,
			'render_callback'     => 'castilo_customize_partial_site_copyright',
		) );
	}

}
add_action( 'customize_register', 'castilo_customize_register', 11 );

/**
 * Render the site copyright for the selective refresh partial.
 */
function castilo_customize_partial_header_call_to_action() {
	get_template_part( 'template-parts/header/call-to-action' );
}

/**
 * Render the site copyright for the selective refresh partial.
 */
function castilo_customize_partial_site_copyright() {
	get_template_part( 'template-parts/footer/site-info' );
}

/**
 * Sanitize the header opacity.
 */
function castilo_sanitize_opacity( $input ) {
	return filter_var( $input, FILTER_VALIDATE_INT );
}

/**
 * Binds JS handlers to make the Customizer preview reload changes asynchronously.
 */
function castilo_customize_preview_js() {
	wp_enqueue_script( 'castilo-customize-preview', get_template_directory_uri() . '/assets/js/customize-preview.js', array( 'customize-preview' ), false, true );
}
add_action( 'customize_preview_init', 'castilo_customize_preview_js' );
