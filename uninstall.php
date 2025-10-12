<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   WordpressPopularPosts
 * @author    Hector Cabrera <me@cabrerahector.com>
 * @license   GPL-2.0+
 * @link      https://cabrerahector.com
 * @copyright 2008-2022 Hector Cabrera
 */

// If uninstall is not called from WordPress, exit
if ( ! defined('WP_UNINSTALL_PLUGIN') ) {
    exit;
}

// Run uninstall for each blog in the network
if (
    function_exists('is_multisite') 
    && is_multisite()
) {
    global $wpdb;

    $blogs_table = "{$wpdb->blogs}";
    $original_blog_id = get_current_blog_id();
    $blogs_ids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM %i", $blogs_table)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

    foreach( $blogs_ids as $b_id ) {
        switch_to_blog($b_id);
        // delete tables and options
        wordpress_popular_posts_uninstall();
        // delete thumbnails cache and its directory
        wordpress_popular_posts_delete_thumb_cache();
    }

    // Switch back to current blog
    switch_to_blog($original_blog_id);
} else {
    // delete tables and options
    wordpress_popular_posts_uninstall();
    // delete thumbnails cache and its directory
    wordpress_popular_posts_delete_thumb_cache();
}

function wordpress_popular_posts_delete_thumb_cache() {
    $wp_upload_dir = wp_get_upload_dir();

    if ( is_dir($wp_upload_dir['basedir'] . '/wordpress-popular-posts') ) {
        $files = glob($wp_upload_dir['basedir'] . '/wordpress-popular-posts/*'); // get all file names

        if ( is_array($files) && ! empty($files) ) {
            foreach( $files as $file ){ // iterate files
                if ( is_file($file) ) {
                    @unlink($file); // delete file
                }
            }
        }

        // Finally, delete WPP's upload directory
        @rmdir($wp_upload_dir['basedir'] . '/wordpress-popular-posts');
    }
}

function wordpress_popular_posts_uninstall() {
    global $wpdb;

    // Delete plugin's options
    delete_option('wpp_ver');
    delete_option('wpp_update');
    delete_option('wpp_settings_config');
    delete_option('wpp_rand');
    delete_option('wpp_transients');
    delete_option('wpp_performance_nag');

    // Delete WPP's DB tables
    $prefix = $wpdb->prefix . 'popularposts';
    //phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
    $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %i;", "{$prefix}data"));
    $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %i;", "{$prefix}datacache"));
    $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %i;", "{$prefix}datacache_backup"));
    $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %i;", "{$prefix}log"));
    $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %i;", "{$prefix}summary"));
    $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %i;", "{$prefix}transients"));
    //phpcs:enable
}
