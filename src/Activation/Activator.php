<?php

/**
 * Fired during plugin activation
 *
 * @since      1.0.0
 * @package    WordPressPopularPosts
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WordPressPopularPosts
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts\Activation;

class Activator {
    /**
     * Fired when the plugin is activated.
     *
     * @since    1.0.0
     * @param    bool    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
     * @global   object  $wpdb
     */
    public static function activate($network_wide)
    {
        global $wpdb;

        if ( function_exists('is_multisite') && \is_multisite() ) {
            // run activation for each blog in the network
            if ( $network_wide ) {
                $original_blog_id = \get_current_blog_id();
                $blogs_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");

                foreach( $blogs_ids as $blog_id ) {
                    \switch_to_blog($blog_id);
                    self::plugin_activate();
                }

                // switch back to current blog
                \switch_to_blog($original_blog_id);

                return;
            }
        }

        self::plugin_activate();
    }

    /**
     * When a new MU site is added, generate its WPP DB tables.
     *
     * @since    1.0.0
     */
    public static function track_new_site()
    {
        self::plugin_activate();
    }

    /**
     * On plugin activation, checks that the WPP database tables are present.
     *
     * @since    1.0.0
     * @global   object   $wpdb
     */
    private static function plugin_activate()
    {
        $wpp_ver = \get_option('wpp_ver');

        if (
            ! $wpp_ver
            || version_compare($wpp_ver, WPP_VERSION, '<')
        ) {
            global $wpdb;

            $prefix = $wpdb->prefix . "popularposts";
            self::do_db_tables($prefix);
        }
    }

    /**
     * Creates/updates the database tables.
     *
     * @since    1.0.0
     * @param    string   $prefix
     * @global   object   $wpdb
     */
    private static function do_db_tables($prefix)
    {
        global $wpdb;
        $charset_collate = "";

        if ( !empty($wpdb->charset) )
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset} ";

        if ( !empty($wpdb->collate) )
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

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        \dbDelta($sql);

        \update_option('wpp_ver', WPP_VERSION);
    }
}
