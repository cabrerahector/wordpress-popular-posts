<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   WordpressPopularPosts
 * @author    Hector Cabrera <hcabrerab@gmail.com>
 * @license   GPL-2.0+
 * @link      http://cabrerahector.com
 * @copyright 2013 Hector Cabrera
 */

// If uninstall, not called from WordPress, then exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Run uninstall for each blog in the network
if ( function_exists( 'is_multisite' ) && is_multisite() ) {
	
	global $wpdb;
	
	$original_blog_id = get_current_blog_id();
	$blogs_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

	foreach( $blogs_ids as $blog_id ) {
		
		switch_to_blog( $blog_id );
		
		// Delete plugin's options
		delete_site_option( 'wpp_ver' );
		delete_site_option( 'wpp_settings_config' );
		delete_site_option( 'wpp_rand' );
		delete_site_option( 'wpp_transients' );
		
		// delete tables
		uninstall();

	}

	// Switch back to current blog
	switch_to_blog( $original_blog_id );

} else {
	// Delete plugin's options
	delete_option( 'wpp_ver' );
	delete_option( 'wpp_settings_config' );
	delete_option( 'wpp_rand' );
	delete_option( 'wpp_transients' );
	
	// delete tables
	uninstall();
}

function uninstall(){
	
	global $wpdb;

	// Delete db tables
	$prefix = $wpdb->prefix . "popularposts";
	$wpdb->query( "DROP TABLE IF EXISTS {$prefix}data;" );
	$wpdb->query( "DROP TABLE IF EXISTS {$prefix}datacache;" );
	$wpdb->query( "DROP TABLE IF EXISTS {$prefix}datacache_backup;" );
	$wpdb->query( "DROP TABLE IF EXISTS {$prefix}log;" );
	$wpdb->query( "DROP TABLE IF EXISTS {$prefix}summary" );
	
}