<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   WordpressPopularPosts
 * @author    Hector Cabrera <me@cabrerahector.com>
 * @license   GPL-2.0+
 * @link      https://cabrerahector.com
 * @copyright 2008-2018 Hector Cabrera
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
        delete_option( 'wpp_ver' );
        delete_option( 'wpp_update' );
        delete_option( 'wpp_settings_config' );
        delete_option( 'wpp_rand' );
        delete_option( 'wpp_transients' );

        // delete tables
        uninstall();

        // delete thumbnails cache and its directory
        delete_thumb_cache();

    }

    // Switch back to current blog
    switch_to_blog( $original_blog_id );

} else {

    // Delete plugin's options
    delete_option( 'wpp_ver' );
    delete_option( 'wpp_update' );
    delete_option( 'wpp_settings_config' );
    delete_option( 'wpp_rand' );
    delete_option( 'wpp_transients' );

    // delete tables
    uninstall();

    // delete thumbnails cache and its directory
    delete_thumb_cache();

}

function delete_thumb_cache() {
    $wp_upload_dir = wp_get_upload_dir();

    if ( is_dir( $wp_upload_dir['basedir'] . "/wordpress-popular-posts" ) ) {
        $files = glob( $wp_upload_dir['basedir'] . "/wordpress-popular-posts/*" ); // get all file names

        if ( is_array($files) && !empty($files) ) {
            foreach($files as $file){ // iterate files
                if ( is_file($file) )
                    @unlink($file); // delete file
            }
        }

        // Finally, delete wpp's upload directory
        @rmdir( $wp_upload_dir['basedir'] . "/wordpress-popular-posts" );

    }
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