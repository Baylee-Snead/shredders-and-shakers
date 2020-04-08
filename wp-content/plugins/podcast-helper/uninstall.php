<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// remove statistics
global $wpdb;
delete_option( 'podcast_stats_db' );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}podcast_stats" );
