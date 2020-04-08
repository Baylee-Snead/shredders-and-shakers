<?php
/**
 * Handle the Settings page for the episodes custom post type.

 * @package Podcast Helper
 */

/**
 * Manage custom settings for episodes.
 */
function podcast_helper_add_settings_menu_item_custom_post() {
	add_submenu_page(
		'edit.php?post_type=episode',
		__( 'Podcast Settings', 'podcast-helper' ),
		__( 'Podcast Settings', 'podcast-helper' ),
		'manage_options',
		'podcast_settings',
		'podcast_helper_custom_post_settings_page'
	);
}
add_action( 'admin_menu', 'podcast_helper_add_settings_menu_item_custom_post' );

/**
 * Output custom settings section.
 */
function podcast_helper_custom_post_settings_page() {
	?>
	<div class="wrap">
		<h2><?php esc_html_e( 'Podcast Settings', 'podcast-helper' ); ?></h2>
		<?php settings_errors(); ?>
		<form method="post" action="options.php">
			<?php
				settings_fields( 'podcast_helper' );
				do_settings_sections( 'podcast_helper_section_feed_details' );
				submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Add settings for the podcast feed.
 */
function podcast_helper_add_settings_custom_post_admin_init() {

	$settings = array(
		'podcast_title'              => array(
			'title' => __( 'Title', 'podcast-helper' ),
			'args'  => array(
				'type'              => 'string',
				'default'           => get_bloginfo( 'name' ),
				'sanitize_callback' => 'wp_strip_all_tags',
			),
		),
		'podcast_subtitle'           => array(
			'title' => __( 'Subtitle', 'podcast-helper' ),
			'args'  => array(
				'type'              => 'string',
				'default'           => get_bloginfo( 'description' ),
				'sanitize_callback' => 'wp_strip_all_tags',
			),
		),
		'podcast_author'             => array(
			'title' => __( 'Author', 'podcast-helper' ),
			'args'  => array(
				'type'              => 'string',
				'default'           => get_bloginfo( 'name' ),
				'sanitize_callback' => 'wp_strip_all_tags',
			),
		),
		'podcast_primary_category'   => array(
			'title' => __( 'Primary Category', 'podcast-helper' ),
			'args'  => array(
				'type'              => 'string',
				'sanitize_callback' => 'wp_strip_all_tags',
			),
		),
		'podcast_secondary_category' => array(
			'title' => __( 'Secondary Category', 'podcast-helper' ),
			'args'  => array(
				'type'              => 'string',
				'sanitize_callback' => 'wp_strip_all_tags',
			),
		),
		'podcast_tertiary_category'  => array(
			'title' => __( 'Tertiary Category', 'podcast-helper' ),
			'args'  => array(
				'type'              => 'string',
				'sanitize_callback' => 'wp_strip_all_tags',
			),
		),
		'podcast_description'        => array(
			'title' => __( 'Description', 'podcast-helper' ),
			'args'  => array(
				'type'              => 'string',
				'sanitize_callback' => 'wp_strip_all_tags',
			),
		),
		'podcast_cover'              => array(
			'title' => __( 'Cover Image', 'podcast-helper' ),
			'args'  => array(
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
			),
		),
		'podcast_owner_name'         => array(
			'title' => __( 'Owner Name', 'podcast-helper' ),
			'args'  => array(
				'type'              => 'string',
				'default'           => get_bloginfo( 'name' ),
				'sanitize_callback' => 'wp_strip_all_tags',
			),
		),
		'podcast_owner_email'        => array(
			'title' => __( 'Owner Email', 'podcast-helper' ),
			'args'  => array(
				'type'              => 'string',
				'default'           => get_bloginfo( 'admin_email' ),
				'sanitize_callback' => 'sanitize_email',
			),
		),
		'podcast_language'           => array(
			'title' => __( 'Language', 'podcast-helper' ),
			'args'  => array(
				'type'              => 'string',
				'default'           => get_bloginfo( 'language' ),
				'sanitize_callback' => 'wp_strip_all_tags',
			),
		),
		'podcast_copyright'          => array(
			'title' => __( 'Copyright', 'podcast-helper' ),
			'args'  => array(
				'type'              => 'string',
				/* translators: 1: year, 2: blog name */
				'default'           => sprintf( __( '&copy; %1$s %2$s. All Rights Reserved.', 'podcast-helper' ), date( 'Y' ), get_bloginfo( 'name' ) ),
				'sanitize_callback' => 'wp_strip_all_tags',
			),
		),
		'podcast_explicit'           => array(
			'title' => __( 'Explicit', 'podcast-helper' ),
			'args'  => array(
				'type'              => 'boolean',
				'sanitize_callback' => 'wp_strip_all_tags',
			),
		),
		'podcast_complete'           => array(
			'title' => __( 'Complete', 'podcast-helper' ),
			'args'  => array(
				'type'              => 'boolean',
				'sanitize_callback' => 'wp_strip_all_tags',
			),
		),
		'podcast_consume_order'      => array(
			'title' => __( 'Podcast Type', 'podcast-helper' ),
			'args'  => array(
				'type'              => 'string',
				'sanitize_callback' => 'wp_strip_all_tags',
			),
		),
	);

	add_settings_section(
		'podcast_helper_feed_details',
		__( 'Feed Details', 'podcast-helper' ),
		'podcast_helper_custom_post_settings_feed_details',
		'podcast_helper_section_feed_details'
	);

	foreach ( $settings as $key => $value ) {
		register_setting( 'podcast_helper', $key, $value['args'] );
		add_settings_field(
			$key,
			$value['title'],
			'podcast_helper_custom_post_settings_feed_details_' . str_replace( 'podcast_', '', $key ),
			'podcast_helper_section_feed_details',
			'podcast_helper_feed_details'
		);
	}

}
add_action( 'admin_init', 'podcast_helper_add_settings_custom_post_admin_init' );

/**
 * Display the description from the podcast settings section.
 */
function podcast_helper_custom_post_settings_feed_details() {
	echo esc_html__( 'The following data will be used in the feed for your podcast so your listeners will know more about it before they subscribe.', 'podcast-helper' );
	/* translators: podcast URL */
	echo '<p>' . sprintf( esc_html__( 'Use the %s URL to share and publish your feed on any podcasting service (including iTunes).', 'podcast-helper' ), '<code>' . esc_url( podcast_helper_get_feed_url() ) . '</code>' ) . '</p>';
	/* translators: permalinks settings url */
	echo '<p><a class="view-feed-link button" href="' . esc_url( podcast_helper_get_feed_url() ) . '" target="_blank">' . esc_html__( 'View RSS2 feed', 'podcast-helper' ) . ' <span class="dashicons dashicons-rss" style="font-size:16px;vertical-align: middle;"></span></a> ' . sprintf( esc_html__( '(refresh %s if you can\'t access the feed)', 'podcast-helper' ), '<a href="' . esc_url( get_admin_url( null, 'options-permalink.php' ) ) . '">' . esc_html__( 'permalinks', 'podcast-helper' ) . '</a>' ) . '</p>';

	wp_enqueue_script( 'podcast_helper-custom-post-settings', PODCAST_HELPER_PLUGIN_URL . 'assets/js/custom-post-settings.js', array( 'jquery' ) );
}

/**
 * Display the form elements for the title setting.
 */
function podcast_helper_custom_post_settings_feed_details_title() {
	echo '<input type="text" id="podcast_title" name="podcast_title" placeholder="' . esc_attr( get_bloginfo( 'name' ) ) . '" value="' . esc_attr( get_option( 'podcast_title', get_bloginfo( 'name' ) ) ) . '" class="regular-text">';
	echo '<p class="description"><label for="podcast_title">' . esc_html__( 'Your podcast title', 'podcast-helper' ) . '</label></p>';
}

/**
 * Display the form elements for the subtitle setting.
 */
function podcast_helper_custom_post_settings_feed_details_subtitle() {
	echo '<input type="text" id="podcast_subtitle" name="podcast_subtitle" placeholder="' . esc_attr( get_bloginfo( 'description' ) ) . '" value="' . esc_attr( get_option( 'podcast_subtitle', get_bloginfo( 'description' ) ) ) . '" class="regular-text">';
	echo '<p class="description"><label for="podcast_subtitle">' . esc_html__( 'Your podcast subtitle', 'podcast-helper' ) . '</label></p>';
}

/**
 * Display the form elements for the author setting.
 */
function podcast_helper_custom_post_settings_feed_details_author() {
	echo '<input type="text" id="podcast_author" name="podcast_author" placeholder="' . esc_attr( get_bloginfo( 'name' ) ) . '" value="' . esc_attr( get_option( 'podcast_author' ), get_bloginfo( 'name' ) ) . '" class="regular-text">';
	echo '<p class="description"><label for="podcast_author">' . esc_html__( 'Your podcast author', 'podcast-helper' ) . '</label></p>';
}

/**
 * Display the form elements for a category setting.
 *
 * @param int $id ID of the DOM element.
 */
function podcast_helper_custom_post_settings_category_selector( $id ) {
	$categories_options = array(
		''                           => array(
			'label' => __( 'None', 'podcast-helper' ),
		),
		'Arts'                       => array(
			'label'         => __( 'Arts', 'podcast-helper' ),
			'subcategories' => array(
				'Books'            => __( 'Books', 'podcast-helper' ),
				'Design'           => __( 'Design', 'podcast-helper' ),
				'Fashion & Beauty' => __( 'Fashion & Beauty', 'podcast-helper' ),
				'Food'             => __( 'Food', 'podcast-helper' ),
				'Performing Arts'  => __( 'Performing Arts', 'podcast-helper' ),
				'Visual Arts'      => __( 'Visual Arts', 'podcast-helper' ),
			),
		),
		'Business'                   => array(
			'label'         => __( 'Business', 'podcast-helper' ),
			'subcategories' => array(
				'Careers'                => __( 'Careers', 'podcast-helper' ),
				'Entrepreneurship'       => __( 'Entrepreneurship', 'podcast-helper' ),
				'Investing'              => __( 'Investing', 'podcast-helper' ),
				'Management'             => __( 'Management', 'podcast-helper' ),
				'Marketing'              => __( 'Marketing', 'podcast-helper' ),
				'Non-profit'             => __( 'Non-profit', 'podcast-helper' ),
			),
		),
		'Comedy'                     => array(
			'label' => __( 'Comedy', 'podcast-helper' ),
			'subcategories' => array(
				'Comedy Interviews' => __( 'Comedy Interviews', 'podcast-helper' ),
				'Improv'            => __( 'Improv', 'podcast-helper' ),
				'Standup'           => __( 'Standup', 'podcast-helper' ),
			),
		),
		'Education'                  => array(
			'label'         => __( 'Education', 'podcast-helper' ),
			'subcategories' => array(
				'Courses'           => __( 'Courses', 'podcast-helper' ),
				'How to'            => __( 'How to', 'podcast-helper' ),
				'Language Learning' => __( 'Language Learning', 'podcast-helper' ),
				'Self Improvement'  => __( 'Self Improvement', 'podcast-helper' ),
			),
		),
		'Fiction'                    => array(
			'label'         => __( 'Fiction', 'podcast-helper' ),
			'subcategories' => array(
				'Comedy Fiction'  => __( 'Comedy Fiction', 'podcast-helper' ),
				'Drama'           => __( 'Drama', 'podcast-helper' ),
				'Science Fiction' => __( 'Science Fiction', 'podcast-helper' ),
			),
		),
		'Government'                 => array(
			'label' => __( 'Government', 'podcast-helper' ),
		),
		'History'                    => array(
			'label' => __( 'History', 'podcast-helper' ),
		),
		'Health & Fitness'           => array(
			'label'         => __( 'Health & Fitness', 'podcast-helper' ),
			'subcategories' => array(
				'Alternative Health'  => __( 'Alternative Health', 'podcast-helper' ),
				'Fitness'             => __( 'Fitness', 'podcast-helper' ),
				'Medicine'            => __( 'Medicine', 'podcast-helper' ),
				'Mental Health'       => __( 'Mental Health', 'podcast-helper' ),
				'Nutrition'           => __( 'Nutrition', 'podcast-helper' ),
				'Sexuality'           => __( 'Sexuality', 'podcast-helper' ),
			),
		),
		'Kids & Family'              => array(
			'label'         => __( 'Kids & Family', 'podcast-helper' ),
			'subcategories' => array(
				'Education for Kids' => __( 'Education for Kids', 'podcast-helper' ),
				'Parenting'          => __( 'Parenting', 'podcast-helper' ),
				'Pets & Animals'     => __( 'Pets & Animals', 'podcast-helper' ),
				'Stories for Kids'   => __( 'Stories for Kids', 'podcast-helper' ),
			),
		),
		'Leisure'                    => array(
			'label'         => __( 'Leisure', 'podcast-helper' ),
			'subcategories' => array(
				'Animation & Manga' => __( 'Animation & Manga', 'podcast-helper' ),
				'Automotive'        => __( 'Automotive', 'podcast-helper' ),
				'Aviation'          => __( 'Aviation', 'podcast-helper' ),
				'Crafts'            => __( 'Crafts', 'podcast-helper' ),
				'Games'             => __( 'Games', 'podcast-helper' ),
				'Hobbies'           => __( 'Hobbies', 'podcast-helper' ),
				'Home & Garden'     => __( 'Home & Garden', 'podcast-helper' ),
				'Video Games'       => __( 'Video Games', 'podcast-helper' ),
			),
		),
		'Music'                      => array(
			'label'         => __( 'Music', 'podcast-helper' ),
			'subcategories' => array(
				'Music Commentary' => __( 'Music Commentary', 'podcast-helper' ),
				'Music History'    => __( 'Music History', 'podcast-helper' ),
				'Music Interviews' => __( 'Music Interviews', 'podcast-helper' ),
			),
		),
		'News'                       => array(
			'label'         => __( 'News', 'podcast-helper' ),
			'subcategories' => array(
				'Business News'      => __( 'Business News', 'podcast-helper' ),
				'Daily News'         => __( 'Daily News', 'podcast-helper' ),
				'Entertainment News' => __( 'Entertainment News', 'podcast-helper' ),
				'News Commentary'    => __( 'News Commentary', 'podcast-helper' ),
				'Politics'           => __( 'Politics', 'podcast-helper' ),
				'Sports News'        => __( 'Sports News', 'podcast-helper' ),
				'Tech News'          => __( 'Tech News', 'podcast-helper' ),
			),
		),
		'Religion & Spirituality'    => array(
			'label'         => __( 'Religion & Spirituality', 'podcast-helper' ),
			'subcategories' => array(
				'Buddhism'     => __( 'Buddhism', 'podcast-helper' ),
				'Christianity' => __( 'Christianity', 'podcast-helper' ),
				'Hinduism'     => __( 'Hinduism', 'podcast-helper' ),
				'Islam'        => __( 'Islam', 'podcast-helper' ),
				'Judaism'      => __( 'Judaism', 'podcast-helper' ),
				'Religion'     => __( 'Religion', 'podcast-helper' ),
				'Spirituality' => __( 'Spirituality', 'podcast-helper' ),
			),
		),
		'Science'                   => array(
			'label'         => __( 'Science', 'podcast-helper' ),
			'subcategories' => array(
				'Astronomy'        => __( 'Astronomy', 'podcast-helper' ),
				'Chemistry'        => __( 'Chemistry', 'podcast-helper' ),
				'Earth Sciences'   => __( 'Earth Sciences', 'podcast-helper' ),
				'Life Sciences'    => __( 'Life Sciences', 'podcast-helper' ),
				'Mathematics'      => __( 'Mathematics', 'podcast-helper' ),
				'Natural Sciences' => __( 'Natural Sciences', 'podcast-helper' ),
				'Nature'           => __( 'Nature', 'podcast-helper' ),
				'Physics'          => __( 'Physics', 'podcast-helper' ),
				'Social Sciences'  => __( 'Social Sciences', 'podcast-helper' ),
			),
		),
		'Society & Culture'          => array(
			'label'         => __( 'Society & Culture', 'podcast-helper' ),
			'subcategories' => array(
				'Documentary'       => __( 'Documentary', 'podcast-helper' ),
				'Personal Journals' => __( 'Personal Journals', 'podcast-helper' ),
				'Philosophy'        => __( 'Philosophy', 'podcast-helper' ),
				'Places & Travel'   => __( 'Places & Travel', 'podcast-helper' ),
				'Relationships'     => __( 'Relationships', 'podcast-helper' ),
			),
		),
		'Sports'                     => array(
			'label'         => __( 'Sports', 'podcast-helper' ),
			'subcategories' => array(
				'Baseball'       => __( 'Baseball', 'podcast-helper' ),
				'Basketball'     => __( 'Basketball', 'podcast-helper' ),
				'Cricket'        => __( 'Cricket', 'podcast-helper' ),
				'Fantasy Sports' => __( 'Fantasy Sports', 'podcast-helper' ),
				'Football'       => __( 'Football', 'podcast-helper' ),
				'Golf'           => __( 'Golf', 'podcast-helper' ),
				'Hockey'         => __( 'Hockey', 'podcast-helper' ),
				'Rugby'          => __( 'Rugby', 'podcast-helper' ),
				'Running'        => __( 'Running', 'podcast-helper' ),
				'Soccer'         => __( 'Soccer', 'podcast-helper' ),
				'Swimming'       => __( 'Swimming', 'podcast-helper' ),
				'Tennis'         => __( 'Tennis', 'podcast-helper' ),
				'Volleyball'     => __( 'Volleyball', 'podcast-helper' ),
				'Wilderness'     => __( 'Wilderness', 'podcast-helper' ),
				'Wrestling'      => __( 'Wrestling', 'podcast-helper' ),
			),
		),
		'Technology'                 => array(
			'label' => __( 'Technology', 'podcast-helper' ),
		),
		'True Crime'                 => array(
			'label' => __( 'True Crime', 'podcast-helper' ),
		),
		'TV & Film'                  => array(
			'label'         => __( 'TV & Film', 'podcast-helper' ),
			'subcategories' => array(
				'After Shows'     => __( 'After Shows', 'podcast-helper' ),
				'Film History'    => __( 'Film History', 'podcast-helper' ),
				'Film Interviews' => __( 'Film Interviews', 'podcast-helper' ),
				'Film Reviews'    => __( 'Film Reviews', 'podcast-helper' ),
				'TV Reviews'      => __( 'TV Reviews', 'podcast-helper' ),
			),
		),
	);

	$current_option = get_option( 'podcast_' . $id . '_category' );
	echo '<select id="podcast_' . esc_attr( $id ) . '_category" name="podcast_' . esc_attr( $id ) . '_category" class="regular-text">';
	foreach ( $categories_options as $category_key => $category_value ) {
		echo '<option value="' . esc_attr( $category_key ) . '"' . selected( $current_option, $category_key, false ) . '>' . esc_html( $category_value['label'] ) . '</option>';
		if ( isset( $category_value['subcategories'] ) ) {
			foreach ( $category_value['subcategories'] as $subcategory_key => $subcategory_value ) {
				$subcategory_key = $category_key . '|' . $subcategory_key;
				echo '<option value="' . esc_attr( $subcategory_key ) . '"' . selected( $current_option, $subcategory_key, false ) . '>&nbsp;&nbsp;&nbsp;' . esc_html( $subcategory_value ) . '</option>';
			}
		}
	}
	echo '</select>';
}

/**
 * Display the form elements for the primary category setting.
 */
function podcast_helper_custom_post_settings_feed_details_primary_category() {
	podcast_helper_custom_post_settings_category_selector( 'primary' );
	echo '<p class="description"><label for="podcast_primary_category">' . esc_html__( 'Your podcast\'s primary category and sub-category (if available)', 'podcast-helper' ) . '</label></p>';
}

/**
 * Display the form elements for the secondary category setting.
 */
function podcast_helper_custom_post_settings_feed_details_secondary_category() {
	podcast_helper_custom_post_settings_category_selector( 'secondary' );
	echo '<p class="description"><label for="podcast_secondary_category">' . esc_html__( 'Your podcast\'s secondary category and sub-category (if available)', 'podcast-helper' ) . '</label></p>';
}

/**
 * Display the form elements for the primary tertiary setting.
 */
function podcast_helper_custom_post_settings_feed_details_tertiary_category() {
	podcast_helper_custom_post_settings_category_selector( 'tertiary' );
	echo '<p class="description"><label for="podcast_tertiary_category">' . esc_html__( 'Your podcast\'s tertiary category and sub-category (if available)', 'podcast-helper' ) . '</label></p>';
}

/**
 * Display the form elements for the description setting.
 */
function podcast_helper_custom_post_settings_feed_details_description() {
	echo '<textarea id="podcast_description" name="podcast_description" rows="5" cols="80">' . esc_textarea( get_option( 'podcast_description' ) ) . '</textarea>';
	echo '<p class="description"><label for="podcast_description">' . esc_html__( 'A description/summary of your podcast - no HTML allowed', 'podcast-helper' ) . '</label></p>';
}

/**
 * Display the form elements for the cover setting.
 */
function podcast_helper_custom_post_settings_feed_details_cover() {
	wp_enqueue_media();
	echo '<div class="podcast-settings-image-wrapper hide-if-no-js" data-media-popup-title="' . esc_html__( 'Select Podcast Cover Image', 'podcast-helper' ) . '"><input type="url" name="podcast_cover" id="podcast_cover" value="' . esc_attr( get_option( 'podcast_cover' ) ) . '" class="regular-text"> <input id="upload_image" type="button" class="button" value="' . esc_html__( 'Select Image', 'podcast-helper' ) . '"></div>';
	echo '<p class="description"><label for="podcast_cover">' . esc_html__( 'Your podcast cover image - must have a minimum size of 1400 pixels square JPEG or PNG file and a recommended 3000 pixels square for iTunes', 'podcast-helper' ) . '</label></p>';
}

/**
 * Display the form elements for the owner name setting.
 */
function podcast_helper_custom_post_settings_feed_details_owner_name() {
	echo '<input type="text" id="podcast_owner_name" name="podcast_owner_name" placeholder="' . esc_attr( get_bloginfo( 'name' ) ) . '" value="' . esc_attr( get_option( 'podcast_owner_name', get_bloginfo( 'name' ) ) ) . '" class="regular-text">';
	echo '<p class="description"><label for="podcast_owner_name">' . esc_html__( 'Podcast owner\'s name', 'podcast-helper' ) . '</label></p>';
}

/**
 * Display the form elements for the owner email setting.
 */
function podcast_helper_custom_post_settings_feed_details_owner_email() {
	echo '<input type="email" id="podcast_owner_email" name="podcast_owner_email" placeholder="' . esc_attr( get_bloginfo( 'admin_email' ) ) . '" value="' . esc_attr( get_option( 'podcast_owner_email', get_bloginfo( 'admin_email' ) ) ) . '" class="regular-text">';
	echo '<p class="description"><label for="podcast_owner_email">' . esc_html__( 'Podcast owner\'s email address', 'podcast-helper' ) . '</label></p>';
}

/**
 * Display the form elements for the language setting.
 */
function podcast_helper_custom_post_settings_feed_details_language() {
	echo '<input type="text" id="podcast_language" name="podcast_language" placeholder="' . esc_attr( get_bloginfo( 'language' ) ) . '" value="' . esc_attr( get_option( 'podcast_language', get_bloginfo( 'language' ) ) ) . '" class="regular-text">';
	/* translators: language format link */
	echo '<p class="description"><label for="podcast_language">' . sprintf( esc_html__( 'Your podcast\'s language in %s format', 'podcast-helper' ), '<a href="http://www.loc.gov/standards/iso639-2/php/code_list.php" target="_blank">ISO-639-1</a>' ) . '</label></p>';
}

/**
 * Display the form elements for the copyright setting.
 */
function podcast_helper_custom_post_settings_feed_details_copyright() {
	/* translators: 1: year, 2: blog name */
	echo '<input type="text" id="podcast_copyright" name="podcast_copyright" placeholder="' . sprintf( esc_html__( '&copy; %1$s %2$s. All Rights Reserved.', 'podcast-helper' ), esc_html( date( 'Y' ) ), esc_html( get_bloginfo( 'name' ) ) ) . '" value="' . esc_attr( get_option( 'podcast_copyright', sprintf( __( '&copy; %1$s %2$s. All Rights Reserved.', 'podcast-helper' ), date( 'Y' ), get_bloginfo( 'name' ) ) ) ) . '" class="regular-text">';
	echo '<p class="description"><label for="podcast_copyright">' . esc_html__( 'Copyright line for your podcast', 'podcast-helper' ) . '</label></p>';
}

/**
 * Display the form elements for the explicit setting.
 */
function podcast_helper_custom_post_settings_feed_details_explicit() {
	/* translators: link to explicit option details */
	echo '<label for="podcast_explicit"><input type="checkbox" name="podcast_explicit" id="podcast_explicit" ' . checked( get_option( 'podcast_explicit' ), 'on', false ) . '> ' . sprintf( esc_html__( 'Mark this option if your podcast uses %s language', 'podcast-helper' ), '<a href="https://discussions.apple.com/thread/1079151" target="_blank">' . esc_html__( 'explicit', 'podcast-helper' ) . '</a>' ) . '</label>';
}

/**
 * Display the form elements for the complete setting.
 */
function podcast_helper_custom_post_settings_feed_details_complete() {
	echo '<label for="podcast_complete"><input type="checkbox" name="podcast_complete" id="podcast_complete" ' . checked( get_option( 'podcast_complete' ), 'on', false ) . '> ' . esc_html__( 'Mark this option if your podcast is complete', 'podcast-helper' ) . '</label>';
	echo '<p class="description"><label for="podcast_complete">' . esc_html__( 'Only do this if no more episodes are going to be added to this feed', 'podcast-helper' ) . '</label></p>';
}

/**
 * Display the form elements for the consume order setting.
 */
function podcast_helper_custom_post_settings_feed_details_consume_order() {
	echo '<select name="podcast_consume_order" id="podcast_consume_order" class="regular-text">';
	echo '<option value="episodic">' . esc_html__( 'Episodic', 'podcast-helper' ) . '</option>';
	echo '<option value="serial"' . selected( get_option( 'podcast_consume_order' ), 'serial', false ) . '>' . esc_html__( 'Serial', 'podcast-helper' ) . '</option>';
	echo '</select>';
	echo '<p class="description"><label for="podcast_consume_order">' . esc_html__( 'Determine podcast is either Episodic or Serial (avoiding subscribers to listen "out of order")', 'podcast-helper' ) . '</label></p>';
}
