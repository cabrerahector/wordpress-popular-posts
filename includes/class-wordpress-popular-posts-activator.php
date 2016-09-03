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
					self::__activate();
				}

				// switch back to current blog
				switch_to_blog( $original_blog_id );

				return;

			}

		}

		self::__activate();		

	} // end activate
	
	/**
	 * When a new MU site is added, generate its WPP DB tables.
	 *
	 * @since    4.0.0
	 */
	public static function track_new_site() {
		self::__activate();
	} // end track_new_site
	
	/**
	 * On plugin activation, checks that the WPP database tables are present.
	 *
	 * @since    2.4.0
	 * @global   object   wpdb
	 */
	private static function __activate() {

		global $wpdb;

		// set table name
		$prefix = $wpdb->prefix . "popularposts";

		// fresh setup
		if ( $prefix != $wpdb->get_var("SHOW TABLES LIKE '{$prefix}data'") ) {
			self::do_db_tables( $prefix );
		}

	} // end __activate
	
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
				day datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				last_viewed datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				pageviews bigint(20) DEFAULT 1,
				PRIMARY KEY  (postid)
			) {$charset_collate} ENGINE=InnoDB;
			CREATE TABLE {$prefix}summary (
				ID bigint(20) NOT NULL AUTO_INCREMENT,
				postid bigint(20) NOT NULL,
				pageviews bigint(20) NOT NULL DEFAULT 1,
				view_date date NOT NULL DEFAULT '0000-00-00',
				last_viewed datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				PRIMARY KEY  (ID),
				UNIQUE KEY ID_date (postid,view_date),
				KEY postid (postid),
				KEY view_date (view_date),
				KEY last_viewed (last_viewed)
			) {$charset_collate} ENGINE=InnoDB;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

	} // end do_db_tables

} // end WPP_Activator class
