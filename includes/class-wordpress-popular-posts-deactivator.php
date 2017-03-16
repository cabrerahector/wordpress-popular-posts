<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://cabrerahector.com
 * @since      4.0.0
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      4.0.0
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/includes
 * @author     Hector Cabrera <me@cabrerahector.com>
 */
class WPP_Deactivator {

    /**
     * Fired when the plugin is deactivated.
     *
     * @since	1.0.0
     * @global	object	wpbd
     * @param	bool	network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
     */
    public static function deactivate( $network_wide ) {

        global $wpdb;

        if ( function_exists( 'is_multisite' ) && is_multisite() ) {

            // Run deactivation for each blog in the network
            if ( $network_wide ) {

                $original_blog_id = get_current_blog_id();
                $blogs_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

                foreach( $blogs_ids as $blog_id ) {
                    switch_to_blog( $blog_id );
                    self::plugin_deactivate();
                }

                // Switch back to current blog
                switch_to_blog( $original_blog_id );

                return;

            }

        }

        self::plugin_deactivate();

    } // end deactivate

    /**
     * On plugin deactivation, disables the shortcode and removes the scheduled task.
     *
     * @since	2.4.0
     */
    private static function plugin_deactivate() {

        remove_shortcode( 'wpp' );
        wp_clear_scheduled_hook( 'wpp_cache_event' );

    } // end plugin_deactivate

} // end WPP_Activator class
