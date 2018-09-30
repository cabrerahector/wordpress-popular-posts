<?php

/**
 * Fired during plugin activation
 *
 * @link       http://cabrerahector.com
 * @since      4.0.0
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      4.0.0
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/includes
 * @author     Hector Cabrera <me@cabrerahector.com>
 */
class WPP_Activator {

    /**
     * Fired when the plugin is activated.
     *
     * @since    1.0.0
     * @param    bool    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
     */
    public static function activate( $network_wide ) {

        global $wpdb;

        if ( function_exists( 'is_multisite' ) && is_multisite() ) {

            // run activation for each blog in the network
            if ( $network_wide ) {

                $original_blog_id = get_current_blog_id();
                $blogs_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

                foreach( $blogs_ids as $blog_id ) {
                    switch_to_blog( $blog_id );
                    self::plugin_activate();
                }

                // switch back to current blog
                switch_to_blog( $original_blog_id );

                return;

            }

        }

        self::plugin_activate();		

    } // end activate
    
    /**
     * When a new MU site is added, generate its WPP DB tables.
     *
     * @since    4.0.0
     */
    public static function track_new_site() {
        self::plugin_activate();
    } // end track_new_site
    
    /**
     * On plugin activation, checks that the WPP database tables are present.
     *
     * @since    2.4.0
     * @global   object   wpdb
     */
    private static function plugin_activate() {

        // Get WPP version
        $wpp_ver = get_option( 'wpp_ver' );

        if (
            !$wpp_ver
            || version_compare( $wpp_ver, WPP_VER, '<' )
        ) {
            global $wpdb;

            $prefix = $wpdb->prefix . "popularposts";
            self::do_db_tables( $prefix );
        }

    } // end plugin_activate
    
    /**
     * Creates/updates the WPP database tables.
     *
     * @since    2.4.0
     * @global   object   wpdb
     */
    private static function do_db_tables( $prefix ) {

        global $wpdb;

        $charset_collate = "";

        if ( !empty( $wpdb->charset ) )
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset} ";

        if ( !empty( $wpdb->collate ) )
            $charset_collate .= "COLLATE {$wpdb->collate}";

        $sql = "
            CREATE TABLE {$prefix}data (
                postid bigint(20) NOT NULL,
                day datetime NOT NULL,
                last_viewed datetime NOT NULL,
                pageviews bigint(20) DEFAULT 1,
                PRIMARY KEY  (postid)
            ) {$charset_collate} ENGINE=InnoDB;
            CREATE TABLE {$prefix}summary (
                ID bigint(20) NOT NULL AUTO_INCREMENT,
                postid bigint(20) NOT NULL,
                pageviews bigint(20) NOT NULL DEFAULT 1,
                view_date date NOT NULL,
                view_datetime datetime NOT NULL,
                PRIMARY KEY  (ID),
                KEY postid (postid),
                KEY view_date (view_date),
                KEY view_datetime (view_datetime)
            ) {$charset_collate} ENGINE=InnoDB;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

    } // end do_db_tables

    /**
     * Checks if the technical requirements are met.
     *
     * @since	2.4.0
     * @link	http://wordpress.stackexchange.com/questions/25910/uninstall-activate-deactivate-a-plugin-typical-features-how-to/25979#25979
     * @global	string $wp_version
     * @return	array
     */
    public static function check_requirements() {

        global $wp_version;

        $php_min_version = '5.3';
        $wp_min_version = '4.7';
        $php_current_version = phpversion();
        $errors = array();

        if ( version_compare( $php_min_version, $php_current_version, '>' ) ) {
            $errors[] = sprintf(
                __( 'Your PHP installation is too old. WordPress Popular Posts requires at least PHP version %1$s to function correctly. Please contact your hosting provider and ask them to upgrade PHP to %1$s or higher.', 'wordpress-popular-posts' ),
                $php_min_version
            );
        }

        if ( version_compare( $wp_min_version, $wp_version, '>' ) ) {
            $errors[] = sprintf(
                __( 'Your WordPress version is too old. WordPress Popular Posts requires at least WordPress version %1$s to function correctly. Please update your blog via Dashboard &gt; Update.', 'wordpress-popular-posts' ),
                $wp_min_version
            );
        }

        return $errors;

    } // end check_requirements

} // end WPP_Activator class
