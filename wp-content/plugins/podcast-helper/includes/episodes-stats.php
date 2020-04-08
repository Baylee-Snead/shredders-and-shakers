<?php
/**
 * Handle the Statistics page for the episodes custom post type.

 * @package Podcast Helper
 */

/**
 * Manage custom settings for episodes.
 */
function podcast_helper_add_stats_menu_item_custom_post() {
	add_submenu_page(
		'edit.php?post_type=episode',
		esc_html__( 'Podcast Statistics', 'podcast-helper' ),
		esc_html__( 'Podcast Statistics', 'podcast-helper' ),
		'manage_options',
		'podcast_statistics',
		'podcast_helper_custom_post_statistics_page'
	);
}
add_action( 'admin_menu', 'podcast_helper_add_stats_menu_item_custom_post' );

/**
 * Output statistics section.
 */
function podcast_helper_custom_post_statistics_page() {
	if ( isset( $_POST['clear_data'] ) ) {
		global $wpdb;
		// clear statistics data from database.
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}podcast_stats" );
	} else {
		podcast_helper_run_geo_location_updater();
	}

	if ( isset( $_GET['filter'] ) ) {
		$filter = wp_unslash( $_GET['filter'] );
	} else {
		$filter = false;
	}
	if ( isset( $_GET['episode'] ) && $filter ) {
		$episode = wp_unslash( $_GET['episode'] );
	} else {
		$episode = false;
	}

	$episode_class = ( 'episode' == $filter ? '' : 'hidden' );

	$locations_class = function_exists( 'geoip_detect2_get_info_from_ip' ) ? ' show-locations-details' : '';

	?>
	<div class="wrap podcast-stats<?php echo esc_attr( $locations_class ); ?>" id="podcast-stats-page">
		<h2><?php esc_html_e( 'Podcast Statistics', 'podcast-helper' ); ?></h2>

		<?php if ( podcast_helper_statistics_exist() ) : ?>
			<form action="<?php echo esc_url( admin_url( 'edit.php?post_type=episode&page=podcast_statistics' ) ); ?>" method="post" id="clear-stats">
				<input type="hidden" name="clear_data" value="1">
				<input type="submit" id="clear-stats-button" class="button button-primary button-large" value="<?php echo esc_attr( esc_html__( 'Clear Statistics', 'podcast-helper' ) ); ?>" data-alert-text="<?php echo esc_attr( esc_html__( 'Are you sure you want to clear all the statistics data? This will remove every information from this screen without any option of restoring it.', 'podcast-helper' ) ); ?>">
			</form>
		<?php endif; ?>

		<?php if ( false === get_option( 'dismissed-podcast-privacy' ) ) : ?>
		<div id="podcast-privacy-notice" class="notice notice-warning is-dismissible">
			<p><?php esc_html_e( 'The IP address and referrer are stored in the database each time any user downloads or listens to an episode.', 'podcast-helper' ); ?></p>
			<p><?php esc_html_e( 'These fields are not transmitted to any third-party servers or made public in any way, they are stored for internal personal use only.', 'podcast-helper' ); ?></p>
		</div>
	<?php endif; ?>

		<?php if ( ! get_option( 'permalink_structure' ) ) : ?>

		<div class="notice notice-info">
			<p><?php esc_html_e( 'You haven\'t set a valid permalink structure yet. It is important to do so, as permalinks help in collecting data for this page.', 'podcast-helper' ); ?></p>
			<p>
			<?php
				/* translators: 1 - link to permalink settings */
				printf( esc_html__( 'Please set a permalink structure in the %s admin section.', 'podcast-helper' ), '<a href="' . esc_url( get_admin_url( null, 'options-permalink.php' ) ) . '">' . esc_html__( 'Settings > Permalinks', 'podcast-helper' ) . '</a>' );
			?>
			</p>
		</div>

		<?php else : ?>

		<?php podcast_helper_statistics_update_database(); ?>

		<form action="" method="get" id="stats-content-filter">
			<input type="hidden" name="post_type" value="<?php echo esc_attr( $_GET['post_type'] ); ?>">
			<input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>">
			<label for="filter"><?php esc_html_e( 'View statistics for', 'podcast-helper' ); ?></label>
			<select name="filter" id="content-filter-select">
				<option value="" <?php echo selected( false, $filter, false ); ?>><?php esc_html_e( 'All episodes', 'podcast-helper' ); ?></option>
				<option value="episode" <?php echo selected( 'episode', $filter, false ); ?>><?php esc_html_e( 'An individual episode', 'podcast-helper' ); ?></option>
			</select>
			<span id="episode-selection" class="<?php echo esc_attr( $episode_class ); ?>">
				<span class="dashicons dashicons-arrow-right-alt2"></span>
				<select name="episode">
					<option value="0" <?php echo selected( false, $episode, false ); ?>><?php esc_html_e( 'Select episode', 'podcast-helper' ); ?></option>
					<?php
					$query_args = array(
						'post_type'      => 'episode',
						'post_status'    => 'publish',
						'orderby'        => 'menu_order date',
						'posts_per_page' => -1,
					);
					query_posts( $query_args );
					if ( have_posts() ) {
						while ( have_posts() ) {
							the_post();
							echo '<option value="' . esc_attr( get_the_ID() ) . '" ' . selected( $episode, get_the_ID(), false ) . '>' . wp_kses_post( get_the_title() ) . '</option>';
						}
					}
					?>
				</select>
			</span>
			<input type="submit" id="content-filter-button" class="hidden button" value="<?php echo esc_attr( esc_html__( 'Apply', 'podcast-helper' ) ); ?>">
		</form>

		<div id="podcast-episode-data" class="metabox-holder">
		<?php
		if ( podcast_helper_statistics_exist( $episode ) ) :
			$current_time    = current_time( 'timestamp' );
			$start_of_today  = strtotime( date( 'Y-m-d 00:00:00', $current_time ) );
			$yesterday_start = strtotime( '-1 day', $start_of_today );
			$boxes           = array(
				esc_html__( 'Today', 'podcast-helper' ) => array(
					'start' => $start_of_today,
					'end'   => $current_time,
				),
				esc_html__( 'Yesterday', 'podcast-helper' ) => array(
					'start' => $yesterday_start,
					'end'   => $start_of_today,
				),
				esc_html__( 'This week', 'podcast-helper' ) => array(
					'start' => strtotime( 'monday this week' ),
					'end'   => $current_time,
				),
				esc_html__( 'Last week', 'podcast-helper' ) => array(
					'start' => strtotime( 'monday last week' ),
					'end'   => strtotime( 'sunday last week' ),
				),
				esc_html__( 'This month', 'podcast-helper' ) => array(
					'start' => strtotime( 'first day of this month' ),
					'end'   => $current_time,
				),
				esc_html__( 'Last month', 'podcast-helper' ) => array(
					'start' => strtotime( 'first day of last month' ),
					'end'   => strtotime( 'last day of last month' ),
				),
				esc_html__( 'Total', 'podcast-helper' ) => array(
					'start' => null,
					'end'   => null,
				),
			);
			?>
			<?php foreach ( $boxes as $box_name => $box_filter ) : ?>
			<div class="postbox">
				<h2 class="hndle"><span><?php echo $box_name; ?></span></h2>
				<div class="inside">
					<?php
					global $wpdb;
					if ( $box_filter['start'] && $box_filter['end'] ) {
						$where_clause = ( $episode ? 'AND ( post_id = ' . $episode . ' )' : '' );
						$results      = $wpdb->get_results( $wpdb->prepare( "SELECT ip_address, referrer, details FROM {$wpdb->prefix}podcast_stats WHERE ( date BETWEEN %d AND %d ) $where_clause", $box_filter['start'], $box_filter['end'] ) );
					} else {
						$where_clause = ( $episode ? 'WHERE post_id = ' . $episode : '' );
						$results      = $wpdb->get_results( "SELECT ip_address, referrer, details FROM {$wpdb->prefix}podcast_stats $where_clause" );
					}
					$total_listens = count( $results );
					if ( $total_listens ) {
						echo '<p class="episode-stat-data total-downloads">' . esc_html__( 'Total listens', 'podcast-helper' ) . ': <strong>' . $total_listens . '</strong></p>';
						$listens      = array();
						$listeners    = array();
						$locations    = array();
						$source_names = array(
							'itunes'         => esc_html__( 'iTunes', 'podcast-helper' ),
							'stitcher'       => esc_html__( 'Stitcher', 'podcast-helper' ),
							'spotify'        => esc_html__( 'Spotify', 'podcast-helper' ),
							'overcast'       => esc_html__( 'Overcast', 'podcast-helper' ),
							'pocketcasts'    => esc_html__( 'Pocket Casts', 'podcast-helper' ),
							'google_podcast' => esc_html__( 'Google Podcast', 'podcast-helper' ),
							'podbean'        => esc_html__( 'Podbean', 'podcast-helper' ),
							'download'       => esc_html__( 'Downloads', 'podcast-helper' ),
							'player'         => esc_html__( 'Site Player', 'podcast-helper' ),
							'android'        => esc_html__( 'Android App', 'podcast-helper' ),
							'podcast_addict' => esc_html__( 'Podcast Addict', 'podcast-helper' ),
							'playerfm'       => esc_html__( 'Player FM', 'podcast-helper' ),
							'google_play'    => esc_html__( 'Google Play', 'podcast-helper' ),
							'other'          => esc_html__( 'Other', 'podcast-helper' ),
						);

						foreach ( $results as $result ) {
							if ( ! isset( $listeners[ $result->ip_address ] ) ) {
								$listeners[ $result->ip_address ] = $result->ip_address;
								if ( isset( $result->details ) && $result->details ) {
									$location = json_decode( $result->details );
									if ( isset( $locations[ $location->country ] ) ) {
										$locations[ $location->country ]['count']++;
									} else {
										$locations[ $location->country ] = array(
											'count'  => 1,
											'flag'   => $location->country_flag,
											'states' => array(),
										);
									}
									if ( 'United States' == $location->country && isset( $location->state ) ) {
										if ( isset( $locations[ $location->country ]['states'][ $location->state ] ) ) {
											$locations[ $location->country ]['states'][ $location->state ]++;
										} else {
											$locations[ $location->country ]['states'][ $location->state ] = 1;
										}
									}
								}
							}
							switch ( $result->referrer ) {
								case 'itunes':
								case 'stitcher':
								case 'spotify':
								case 'overcast':
								case 'pocketcasts':
								case 'google_podcast':
								case 'podbean':
								case 'download':
								case 'player':
								case 'android':
								case 'podcast_addict':
								case 'playerfm':
								case 'google_play':
									if ( isset( $listens[ $result->referrer ] ) ) {
										$listens[ $result->referrer ]++;
									} else {
										$listens[ $result->referrer ] = 1;
									}
									break;

								default:
									if ( isset( $listens['other'] ) ) {
										$listens['other']++;
									} else {
										$listens['other'] = 1;
									}
									break;
							}
						}
						$total_listeners = count( $listeners );
						echo '<p class="episode-stat-data total-listeners">' . esc_html__( 'Total listeners', 'podcast-helper' ) . ': <strong>' . $total_listeners . '</strong></p>';
						echo '<p class="episode-stat-data sources">' . esc_html__( 'Listening sources', 'podcast-helper' ) . ':</p>';
						echo '<ul class="sources-list">';
						arsort( $listens );
						foreach ( $listens as $key => $value ) {
							echo '<li class="' . esc_attr( $key ) . '">' . $source_names[ $key ] . ': <strong>' . $value . '</strong></li>';
						}
						echo '</ul>';
						if ( count( $locations ) > 0 && function_exists( 'geoip_detect2_get_info_from_ip' ) ) {
							echo '<p class="episode-stat-data locations">' . esc_html__( 'Listening locations', 'podcast-helper' ) . ':</p>';
							echo '<ul class="locations-list">';
							uasort( $locations, 'podcast_helper_custom_sort_location_by_count' );
							foreach ( $locations as $country_name => $country_val ) {
								echo '<li class="country"><span>' . $country_val['flag'] . '</span> ' . $country_name . ': <strong>' . $country_val['count'] . '</strong>';
								if ( isset( $country_val['states'] ) && count( $country_val['states'] ) > 0 ) {
									echo '<ul class="country-states">';
									arsort( $country_val['states'], SORT_NUMERIC );
									foreach ( $country_val['states'] as $state_name => $state_val ) {
										echo '<li class="state">' . $state_name . ': <strong>' . $state_val . '</strong></li>';
									}
									echo '</ul>';
								}
								echo '</li>';
							}
							echo '</ul>';
						}
					} else {
						echo '<div id="no-stats-container"><div class="no-activity">';
						echo '<p class="smiley"></p>';
						if ( 'Today' == $box_name || 'This week' == $box_name || 'This month' == $box_name ) {
							echo '<p>' . esc_html__( 'No activity yet!', 'podcast-helper' ) . '</p>';
						} else {
							echo '<p>' . esc_html__( 'No activity!', 'podcast-helper' ) . '</p>';
						}
						echo '</div></div>';
					}
					?>
				</div>
			</div>
			<?php endforeach; ?>
			<div class="clear"></div>

		<?php else : ?>
		<div class="postbox">
			<div class="inside">
				<div id="no-stats-container">
					<div class="no-activity">
						<p class="smiley" aria-hidden="true"></p>
						<p><?php esc_html_e( 'No activity yet!', 'podcast-helper' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<?php endif; ?>
		</div>

		<?php endif; ?>
	</div>
	<?php
}

/**
 * AJAX handler to store the state of dismissible notices.
 */
function podcast_ajax_notice_handler() {
	$type = filter_var( $_POST['type'], FILTER_SANITIZE_STRING );
	update_option( 'dismissed-' . $type, true );
}
add_action( 'wp_ajax_podcast_dismissed_notice_handler', 'podcast_ajax_notice_handler' );

function podcast_helper_stats_admin_enqueue_scripts( $hook = '' ) {
	if ( 'episode_page_podcast_statistics' == $hook || 'post.php' == $hook || 'index.php' == $hook ) {
		wp_enqueue_style( 'podcast-helper-admin', PODCAST_HELPER_PLUGIN_URL . 'assets/css/admin.css' );
		if ( 'episode_page_podcast_statistics' == $hook ) {
			wp_enqueue_script( 'podcast-helper-admin', PODCAST_HELPER_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ) );
		}
	}
}
add_action( 'admin_enqueue_scripts', 'podcast_helper_stats_admin_enqueue_scripts', 10, 1 );

function podcast_helper_add_dashboard_stats_widget() {
	add_meta_box( 'podcast_stats_dashboard_widget', esc_html__( 'Podcast Statistics', 'podcast-helper' ), 'podcast_helper_dashboard_stats_widget_callback', 'dashboard', 'normal', 'high' );
}
add_action( 'wp_dashboard_setup', 'podcast_helper_add_dashboard_stats_widget', 1 );

function podcast_helper_dashboard_stats_widget_callback() {
	if ( ! get_option( 'permalink_structure' ) ) :
		?>

		<p><?php esc_html_e( 'You haven\'t set a valid permalink structure yet. It is important to do so, as permalinks help in collecting data for this page.', 'podcast-helper' ); ?></p>
		<p>
		<?php
		/* translators: 1 - link to permalink settings */
		printf( esc_html__( 'Please set a permalink structure in the %s admin section.', 'podcast-helper' ), '<a href="' . esc_url( get_admin_url( null, 'options-permalink.php' ) ) . '">' . esc_html__( 'Settings > Permalinks', 'podcast-helper' ) . '</a>' );
		?>
		</p>

	<?php else : ?>

		<?php if ( podcast_helper_statistics_exist() ) : ?>
			<div class="podcast-stats">
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=episode&page=podcast_statistics' ) ); ?>" class="podcast-stats-link-details clearfix">
					<?php
					global $wpdb;
					$current_time = current_time( 'timestamp' );

					// Listens today
					$start_of_day  = strtotime( date( 'Y-m-d 00:00:00', $current_time ) );
					$listens_today = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$wpdb->prefix}podcast_stats WHERE date BETWEEN %d AND %d", $start_of_day, $current_time ) );

					// Listens this week
					$one_week_ago      = strtotime( '-1 week', $current_time );
					$listens_this_week = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$wpdb->prefix}podcast_stats WHERE date BETWEEN %d AND %d", strtotime( 'monday this week' ), $current_time ) );

					// Listens last week
					$two_weeks_ago     = strtotime( '-1 week', $one_week_ago );
					$listens_last_week = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$wpdb->prefix}podcast_stats WHERE date BETWEEN %d AND %d", strtotime( 'monday last week' ), strtotime( 'sunday last week' ) ) );
					?>
					<span class="overview-stat">
						<span class="stat-total"><?php echo $listens_today; ?></span>
						<span class="stat-description"><?php esc_html_e( 'Listens today', 'podcast-helper' ); ?></span>
					</span>
					<span class="overview-stat">
						<span class="stat-total"><?php echo $listens_this_week; ?></span>
						<span class="stat-description"><?php esc_html_e( 'Listens this week', 'podcast-helper' ); ?></span>
					</span>
					<span class="overview-stat">
						<span class="stat-total"><?php echo $listens_last_week; ?></span>
						<span class="stat-description"><?php esc_html_e( 'Listens last week', 'podcast-helper' ); ?></span>
					</span>
				</a>
			</div>
		<?php else : ?>

			<div id="no-stats-container">
				<div class="inside no-activity">
					<p class="smiley"></p>
					<p><?php esc_html_e( 'No activity yet!', 'podcast-helper' ); ?></p>
				</div>
			</div>

		<?php endif; ?>

	<?php
	endif;

}

/**
 * Update the database to latest schema for stats storage.
 */
function podcast_helper_statistics_update_database() {
	$current_db_version = get_option( 'podcast_stats_db', '0' );

	if ( '0' == $current_db_version ) {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		// Load database functions if necessary.
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		}

		// Setup SQL query for table creation.
		dbDelta( "CREATE TABLE {$wpdb->prefix}podcast_stats (
			id int(11) NOT NULL AUTO_INCREMENT,
			post_id int(11) DEFAULT NULL,
			ip_address varchar(255) DEFAULT NULL,
			referrer varchar(255) DEFAULT NULL,
			details varchar(255) DEFAULT NULL,
			date int(25) DEFAULT NULL,
			PRIMARY KEY (id)
		) $charset_collate;" );

		// Update database verion option.
		update_option( 'podcast_stats_db', '1.0' );
	} elseif ( '1.0' == $current_db_version ) {
		global $wpdb;
		$stat = $wpdb->get_row( sprintf( "SELECT * FROM %s LIMIT 1" , "{$wpdb->prefix}podcast_stats" ) );
		if ( ! isset( $stat->details ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}podcast_stats ADD COLUMN details varchar(255) DEFAULT NULL" );
		}
		// Update database version option.
		update_option( 'podcast_stats_db', '1.1' );
	}
}
add_action( 'plugins_loaded', 'podcast_helper_statistics_update_database' );

function podcast_helper_statistics_exist( $episode_ids = 0 ) {
	global $wpdb;
	$count_entries_sql = "SELECT COUNT(id) FROM {$wpdb->prefix}podcast_stats";
	if ( $episode_ids ) {
		$count_entries_sql = $count_entries_sql . ' WHERE post_id in (' . $episode_ids . ')';
	}
	$total_entries = $wpdb->get_var( $count_entries_sql );
	return $total_entries;
}

/**
 * Add the no. of listens column to the episode post type.
 */
function podcast_helper_statistics_manage_columns( $columns ) {
	$date = $columns['date'];
	unset( $columns['date'] );
	unset( $columns['comments'] );
	$columns['listens'] = esc_html__( 'Listens', 'podcast-helper' );
	$columns['date']    = $date;
	return $columns;
}
add_filter( 'manage_episode_posts_columns', 'podcast_helper_statistics_manage_columns' );

/**
 * Add the data to the listens custom column for the episode post type.
 */
function podcast_helper_statistics_listens_column( $column, $post_id ) {
	if ( 'listens' == $column ) {
		echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=episode&page=podcast_statistics&filter=episode&episode=' . $post_id ) ) . '">' . podcast_helper_statistics_exist( $post_id ) . '</a>';
	}
}
add_action( 'manage_episode_posts_custom_column', 'podcast_helper_statistics_listens_column', 10, 2 );

/**
 * Display statistics in the episode details metabox.
 */
function podcast_helper_statistics_episode_meta_box_add_content( $post ) {

	if ( ! isset( $post->ID ) ) {
		return;
	}

	global $wpdb;
	$results       = $wpdb->get_results( $wpdb->prepare( "SELECT ip_address, referrer FROM {$wpdb->prefix}podcast_stats WHERE post_id = %d", $post->ID ) );
	$total_listens = count( $results );
	echo '<p><em>' . esc_html__( 'Here is an overview of this episodes\'s statistics.', 'podcast-helper' ) . '</em></p>';
	if ( $total_listens ) {
		echo '<p class="episode-stat-data total-downloads">' . esc_html__( 'Total listens', 'podcast-helper' ) . ': <strong>' . $total_listens . '</strong></p>';
		$itunes         = 0;
		$stitcher       = 0;
		$spotify        = 0;
		$overcast       = 0;
		$pocketcasts    = 0;
		$google_podcast = 0;
		$podbean        = 0;
		$direct         = 0;
		$new_window     = 0;
		$player         = 0;
		$android        = 0;
		$podcast_addict = 0;
		$playerfm       = 0;
		$google_play    = 0;
		$other          = 0;
		$listeners      = array();

		foreach ( $results as $result ) {
			$listeners[ $result->ip_address ] = $result->ip_address;
			switch ( $result->referrer ) {
				case 'itunes':
					$itunes++;
					break;
				case 'stitcher':
					$stitcher++;
					break;
				case 'spotify':
					$spotify++;
					break;
				case 'overcast':
					$overcast++;
					break;
				case 'pocketcasts':
					$pocketcasts++;
					break;
				case 'google_podcast':
					$google_podcast++;
					break;
				case 'podbean':
					$podbean++;
					break;
				case 'download':
					$direct++;
					break;
				case 'player':
					$player++;
					break;
				case 'android':
					$android++;
					break;
				case 'podcast_addict':
					$podcast_addict++;
					break;
				case 'playerfm':
					$playerfm++;
					break;
				case 'google_play':
					$google_play++;
					break;
				default:
					$other++;
					break;
			}
		}
		$total_listeners = count( $listeners );
		echo '<p class="episode-stat-data total-listeners">' . esc_html__( 'Total listeners', 'podcast-helper' ) . ': <strong>' . $total_listeners . '</strong></p>';
		echo '<p class="episode-stat-data sources">' . esc_html__( 'Listening sources', 'podcast-helper' ) . ':</p>';
		echo '<ul class="sources-list">';
		if ( $itunes ) {
				echo '<li class="itunes">' . esc_html__( 'iTunes', 'podcast-helper' ) . ': <strong>' . $itunes . '</strong></li>';
		}
		if ( $stitcher ) {
			echo '<li class="stitcher">' . esc_html__( 'Stitcher', 'podcast-helper' ) . ': <strong>' . $stitcher . '</strong></li>';
		}
		if ( $spotify ) {
			echo '<li class="spotify">' . esc_html__( 'Spotify', 'podcast-helper' ) . ': <strong>' . $spotify . '</strong></li>';
		}
		if ( $overcast ) {
			echo '<li class="overcast">' . esc_html__( 'Overcast', 'podcast-helper' ) . ': <strong>' . $overcast . '</strong></li>';
		}
		if ( $pocketcasts ) {
			echo '<li class="pocketcasts">' . esc_html__( 'Pocket Casts', 'podcast-helper' ) . ': <strong>' . $pocketcasts . '</strong></li>';
		}
		if ( $google_podcast ) {
			echo '<li class="google_podcast">' . esc_html__( 'Google Podcast', 'podcast-helper' ) . ': <strong>' . $google_podcast . '</strong></li>';
		}
		if ( $podbean ) {
			echo '<li class="podbean">' . esc_html__( 'Podbean', 'podcast-helper' ) . ': <strong>' . $podbean . '</strong></li>';
		}
		if ( $direct ) {
			echo '<li class="download">' . esc_html__( 'Downloads', 'podcast-helper' ) . ': <strong>' . $direct . '</strong></li>';
		}
		if ( $player ) {
			echo '<li class="player">' . esc_html__( 'Site Player', 'podcast-helper' ) . ': <strong>' . $player . '</strong></li>';
		}
		if ( $android ) {
			echo '<li class="android">' . esc_html__( 'Android App', 'podcast-helper' ) . ': <strong>' . $android . '</strong></li>';
		}
		if ( $podcast_addict ) {
			echo '<li class="podcast_addict">' . esc_html__( 'Podcast Addict', 'podcast-helper' ) . ': <strong>' . $podcast_addict . '</strong></li>';
		}
		if ( $playerfm ) {
			echo '<li class="playerfm">' . esc_html__( 'Player FM', 'podcast-helper' ) . ': <strong>' . $playerfm . '</strong></li>';
		}
		if ( $google_play ) {
			echo '<li class="google_play">' . esc_html__( 'Google Play', 'podcast-helper' ) . ': <strong>' . $google_play . '</strong></li>';
		}
		if ( $other ) {
			echo '<li class="other">' . esc_html__( 'Other', 'podcast-helper' ) . ': <strong>' . $other . '</strong></li>';
		}
		echo '</ul>';
		echo '<p class="episode-stat-data more-details">' . sprintf( esc_html__( '%1$sSee more details%2$s', 'podcast-helper' ), '<a href="' . admin_url( 'edit.php?post_type=episode&page=podcast_statistics&filter=episode&episode=' . $post->ID ) . '">', '</a>' ) . '<p>';
	} else {
		echo '<div id="no-stats-container"><div class="inside no-activity">';
		echo '<p class="smiley"></p>';
		echo '<p>' . esc_html__( 'No activity yet!', 'podcast-helper' ) . '</p>';
		echo '</div></div>';
	}
}
add_action( 'podcast_helper_episode_meta_box_side_add_content', 'podcast_helper_statistics_episode_meta_box_add_content', 10, 1 );

function podcast_helper_episode_start_play_ajax_callback() {
	$episode_id = filter_var( $_POST['episode_id'], FILTER_SANITIZE_NUMBER_INT );
	if ( $episode_id && is_numeric( $episode_id ) ) {
		do_action( 'podcast_helper_file_download', 'ajax-play-external-video', $episode_id, 'player' );
	}
	wp_die();
}
add_action( 'wp_ajax_podcast_helper_episode_start_play', 'podcast_helper_episode_start_play_ajax_callback' );
add_action( 'wp_ajax_nopriv_podcast_helper_episode_start_play', 'podcast_helper_episode_start_play_ajax_callback' );

function podcast_helper_statistics_track_download( $file, $episode_id, $referrer ) {

	if ( ! $file || ! $episode_id ) {
		return;
	}

	session_start();

	// Get request user agent.
	$user_agent = (string) $_SERVER['HTTP_USER_AGENT'];

	// Include Crawler Detect library.
	require_once 'lib/CrawlerDetect/CrawlerDetect.php';

	// Check if this user agent is a crawler/bot to prevent false stats
	$detector = new CrawlerDetect();
	if ( $detector->isCrawler( $user_agent ) ) {
		return;
	}

	if ( false !== stripos( $user_agent, 'podcasts/' ) ) {
		// This conditional will prevent double tracking from iOS Podcasts
		return;
	}

	if ( false !== stripos( $user_agent, 'itunes' ) || false !== stripos( $user_agent, 'applecoremedia' ) ) {
		$referrer = 'itunes';
	} elseif ( false !== stripos( $user_agent, 'stitcher' ) || false !== stripos( $user_agent, 'stagefright' ) ) {
		$referrer = 'stitcher';
	} elseif ( false !== stripos( $user_agent, 'spotify' ) ) {
		$referrer = 'spotify';
	} elseif ( false !== stripos( $user_agent, 'overcast' ) ) {
		$referrer = 'overcast';
	} elseif ( false !== stripos( $user_agent, 'pocket casts' ) ) {
		$referrer = 'pocketcasts';
	} elseif ( false !== stripos( $user_agent, 'google-podcast' ) || false !== stripos( $user_agent, 'google podcast' ) ) {
		$referrer = 'google_podcast';
	} elseif ( false !== stripos( $user_agent, 'podbean' ) || false !== stripos( $user_agent, 'podbean.com' ) ) {
		$referrer = 'podbean';
	} elseif ( false !== stripos( $user_agent, 'podcastaddict' ) ) {
		$referrer = 'podcast_addict';
	} elseif ( false !== stripos( $user_agent, 'player fm' ) ) {
		$referrer = 'playerfm';
	} elseif ( false !== stripos( $user_agent, 'google-play' ) ) {
		$referrer = 'google_play';
	} elseif ( false !== stripos( $user_agent, 'android' ) ) {
		$referrer = 'android';
	}

	// Get remote client IP address.
	$ip_address = '';
	if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
		$ip_address = $_SERVER['HTTP_CF_CONNECTING_IP'];
	} elseif ( isset( $_SERVER['CF-Connecting-IP'] ) ) {
		$ip_address = $_SERVER['CF-Connecting-IP'];
	} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif ( isset( $_SERVER['X-Forwarded-For'] ) ) {
		$ip_address = $_SERVER['X-Forwarded-For'];
	} else {
		// We're not behind a reverse proxy, fallback to using REMOTE_ADDR.
		$ip_address = $_SERVER['REMOTE_ADDR'];
	}

	// Exit if there is no detectable IP address.
	if ( ! $ip_address ) {
		return;
	}

	if ( false !== strpos( $ip_address, ',' ) ) {
		$ip_addresses = explode( ',', $ip_address );
		if ( count( $ip_addresses ) > 0 ) {
			$ip_address = $ip_addresses[0];
		}
	}

	// Filter IP addresses.
	$ip_blacklist = apply_filters( 'podcast_helper_track_download_ip_blacklist', array() );

	if ( is_array( $ip_blacklist ) && in_array( $ip_address, $ip_blacklist ) ) {
		return;
	}

	// Create transient name from episode ID, IP address and referrer.
	$transient = 'podcast_helper_dl_' . $episode_id . '_' . str_replace( '.', '', $ip_address ) . '_' . $referrer;

	// Allow forced transient refresh.
	if ( isset( $_GET['force'] ) ) {
		delete_transient( $transient );
	}

	// Check transient to prevent excessive tracking.
	if ( get_transient( $transient ) ) {
		return;
	}

	// Anonymise the ip address (after the transient check)
	$ip_octets = explode( '.', $ip_address );
	if ( count ( $ip_octets ) > 4 ) {
		$ip_octets[3] = '0';
		$ip_address   = implode( '.', $ip_octets );
	}

	$fields        = array(
		'post_id'    => $episode_id,
		'ip_address' => $ip_address,
		'referrer'   => $referrer,
	);
	$field_formats = array(
		'%d',
		'%s',
		'%s',
	);
	if ( function_exists( 'geoip_detect2_get_info_from_ip' ) ) {
		$geoip_result = geoip_detect2_get_info_from_ip( $ip_address );
		$location     = array(
			'country'      => $geoip_result->country->name,
			'country_flag' => $geoip_result->extra->flag,
		);
		if ( 'United States' == $location['country'] && $geoip_result->mostSpecificSubdivision->name ) {
			$location['state'] = $geoip_result->mostSpecificSubdivision->name;
		}
		if ( $geoip_result->country->name ) {
			$fields['details'] = json_encode( $location );
			$field_formats[] = '%s';
		}
	}
	$current_time    = current_time( 'timestamp' );
	$fields['date']  = $current_time;
	$field_formats[] = '%d';

	// Insert data into database.
	global $wpdb;
	$insert_row = $wpdb->insert(
		"{$wpdb->prefix}podcast_stats",
		$fields,
		$field_formats
	);

	// Set transient to prevent excessive tracking.
	if ( $transient ) {
		set_transient( $transient, $current_time, apply_filters( 'podcast_helper_track_download_transient_expiration', 600 ) ); // MINUTE_IN_SECONDS * 10
	}
}
add_action( 'podcast_helper_file_download', 'podcast_helper_statistics_track_download', 10, 3 );

function podcast_helper_run_geo_location_updater() {
	global $wpdb;
	$stat = $wpdb->get_row( sprintf( "SELECT * FROM %s LIMIT 1" , "{$wpdb->prefix}podcast_stats" ) );
	if ( ! isset( $stat->details ) ) {
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}podcast_stats ADD COLUMN details varchar(255) DEFAULT NULL" );
	}
	if ( ! function_exists( 'geoip_detect2_get_info_from_ip' ) ) {
		return;
	}
	if ( ( $stat && NULL == $stat->details ) || isset( $_GET['force_location_update'] ) ) {
		$results = $wpdb->get_results( "SELECT id, ip_address FROM {$wpdb->prefix}podcast_stats" );
		$update_ids = array();
		$update_details = array();
		foreach ( $results as $result) {
			$geoip_result = geoip_detect2_get_info_from_ip( $result->ip_address );
			$location = array(
				'country'      => $geoip_result->country->name,
				'country_flag' => $geoip_result->extra->flag,
			);
			if ( 'United States' == $location['country'] && $geoip_result->mostSpecificSubdivision->name ) {
				$location['state'] = $geoip_result->mostSpecificSubdivision->name;
			}
			if ( $geoip_result->country->name ) {
				$wpdb->update(
					"{$wpdb->prefix}podcast_stats",
					array(
						'details' => json_encode( $location ),
					),
					array(
						'id' => $result->id
					)
				);
			} elseif ( isset( $_GET['force_location_update'] ) ) {
				$wpdb->update(
					"{$wpdb->prefix}podcast_stats",
					array(
						'details' => NULL,
					),
					array(
						'id' => $result->id
					)
				);
			}
		}
	}
}

function podcast_helper_custom_sort_location_by_count( $loc1, $loc2 ) {
	if ( $loc1['count'] == $loc2['count'] ) {
		return 0;
	}
	return ( $loc1['count'] < $loc2['count'] ) ? 1 : -1;
}
