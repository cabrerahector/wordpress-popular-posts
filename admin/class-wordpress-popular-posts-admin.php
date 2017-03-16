<?php

/**
 * The admin-facing functionality of the plugin.
 *
 * @link       http://cabrerahector.com/
 * @since      4.0.0
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/public
 * @author     Hector Cabrera <me@cabrerahector.com>
 */
class WPP_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    4.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    4.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;
    
    /**
     * Administrative settings.
     *
     * @since	2.3.3
     * @var		array
     */
    private $options = array();
    
    /**
     * Slug of the plugin screen.
     *
     * @since	3.0.0
     * @var		string
     */
    protected $plugin_screen_hook_suffix = NULL;

    /**
     * Initialize the class and set its properties.
     *
     * @since    4.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        // Get/Set admin options
        $defaults = WPP_Settings::$defaults[ 'admin_options' ];
        
        if ( !$this->options = get_site_option( 'wpp_settings_config' ) ) {
            add_site_option( 'wpp_settings_config', $defaults );
            $this->options = $defaults;
        } else {
            $this->options = WPP_Helper::merge_array_r(
                $defaults,
                $this->options
            );
        }
        
        // Delete old data on demand
        if ( 1 == $this->options['tools']['log']['limit'] ) {
            
            if ( !wp_next_scheduled( 'wpp_cache_event' ) ) {
                $tomorrow = time() + 86400;
                $midnight  = mktime(
                    0,
                    0,
                    0,
                    date( "m", $tomorrow ),
                    date( "d", $tomorrow ),
                    date( "Y", $tomorrow )
                );
                wp_schedule_event( $midnight, 'daily', 'wpp_cache_event' );
            }
            
        } else {
            // Remove the scheduled event if exists
            if ( $timestamp = wp_next_scheduled( 'wpp_cache_event' ) ) {
                wp_unschedule_event( $timestamp, 'wpp_cache_event' );
            }
            
        }
        
        // Allow WP themers / coders to override data sampling status (active/inactive)
        $this->options['tools']['sampling']['active'] = apply_filters( 'wpp_data_sampling', $this->options['tools']['sampling']['active'] );

    }
    
    /**
     * Fired when a new blog is activated on WP Multisite.
     *
     * @since    3.0.0
     * @param    int      blog_id    New blog ID
     */
    public function activate_new_site( $blog_id ){

        if ( 1 !== did_action( 'wpmu_new_blog' ) )
            return;

        // run activation for the new blog
        switch_to_blog( $blog_id );
        WPP_Activator::track_new_site();

        // switch back to current blog
        restore_current_blog();

    } // end activate_new_site
    
    /**
     * Fired when a blog is deleted on WP Multisite.
     *
     * @since    4.0.0
     * @param    array     $tables
     * @param    int       $blog_id
     * @return   array
     */
    public function delete_site_data( $tables, $blog_id ){

        global $wpdb;
        
        $tables[] = $wpdb->prefix . 'popularpostsdata';
        $tables[] = $wpdb->prefix . 'popularpostssummary';
        
        return $tables;

    } // end delete_site_data

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    4.0.0
     */
    public function enqueue_styles() {
        
        if ( !isset( $this->plugin_screen_hook_suffix ) ) {
            return;
        }

        $screen = get_current_screen();
        
        if ( $screen->id == $this->plugin_screen_hook_suffix ) {
            wp_enqueue_style( 'wordpress-popular-posts-admin-styles', plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), $this->version, 'all' );
        }
        
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    4.0.0
     */
    public function enqueue_scripts() {
        
        if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
            return;
        }

        $screen = get_current_screen();
        
        if ( $screen->id == $this->plugin_screen_hook_suffix ) {
            
            wp_enqueue_script( 'thickbox' );
            wp_enqueue_style( 'thickbox' );
            wp_enqueue_script( 'media-upload' );
            wp_enqueue_script( 'wordpress-popular-posts-admin-script', plugin_dir_url( __FILE__ ) . 'js/admin.js', array('jquery'), $this->version, true );
            
        }
        
    }
    
    /**
     * Hooks into getttext to change upload button text when uploader is called by WPP.
     *
     * @since	2.3.4
     */
    public function thickbox_setup() {

        global $pagenow;
        
        if ( 'media-upload.php' == $pagenow || 'async-upload.php' == $pagenow ) {
            add_filter( 'gettext', array( $this, 'replace_thickbox_text' ), 1, 3 );
        }

    } // end thickbox_setup

    /**
     * Replaces upload button text when uploader is called by WPP.
     *
     * @since	2.3.4
     * @param	string	translated_text
     * @param	string	text
     * @param	string	domain
     * @return	string
     */
    public function replace_thickbox_text( $translated_text, $text, $domain ) {

        if ( 'Insert into Post' == $text ) {
            $referer = strpos( wp_get_referer(), 'wpp_admin' );
            if ( $referer != '' ) {
                return __( 'Upload', 'wordpress-popular-posts' );
            }
        }

        return $translated_text;

    } // end replace_thickbox_text
    
    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {

        $this->plugin_screen_hook_suffix = add_options_page(
            'WordPress Popular Posts',
            'WordPress Popular Posts',
            'manage_options',
            'wordpress-popular-posts',
            array( $this, 'display_plugin_admin_page' )
        );

    }
    
    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        include_once( plugin_dir_path(__FILE__) . 'partials/admin.php' );
    }
    
    /**
     * Registers Settings link on plugin description.
     *
     * @since	2.3.3
     * @param	array	$links
     * @param	string	$file
     * @return	array
     */
    public function add_plugin_settings_link( $links, $file ){

        $plugin_file = 'wordpress-popular-posts/wordpress-popular-posts.php';

        if (
            is_plugin_active( $plugin_file ) 
            && $plugin_file == $file
        ) {
            $links[] = '<a href="' . admin_url( 'options-general.php?page=wordpress-popular-posts' ) . '">' . __( 'Settings' ) . '</a>';
        }

        return $links;

    }
    
    /**
     * Register the WPP widget.
     *
     * @since    4.0.0
     */
    public function register_widget() {
        register_widget( 'WPP_Widget' );
    }
    
    /**
     * Flushes post's cached thumbnail(s) when the image is changed.
     *
     * @since    3.3.4
     *
     * @param    integer    $meta_id       ID of the meta data field
     * @param    integer    $object_id     Object ID
     * @param    string     $meta_key      Name of meta field
     * @param    string     $meta_value    Value of meta field
     */
    public function flush_post_thumbnail( $meta_id, $object_id, $meta_key, $meta_value ) {

        // User changed the featured image
        if ( '_thumbnail_id' == $meta_key ) {

            $wpp_image = WPP_Image::get_instance();

            if ( $wpp_image->can_create_thumbnails() ) {
            
                $wpp_uploads_dir = $wpp_image->get_plugin_uploads_dir();
                
                if ( is_array($wpp_uploads_dir) && !empty($wpp_uploads_dir) ) {
            
                    $files = glob( "{$wpp_uploads_dir['basedir']}/{$object_id}-featured-*.*" ); // get all related images
                    
                    if ( is_array($files) && !empty($files) ) {

                        foreach( $files as $file ){ // iterate files
                            if ( is_file( $file ) ) {
                                @unlink( $file ); // delete file
                            }
                        }

                    }

                }

            }

        }

    }
    
    /**
     * Truncates thumbnails cache on demand.
     *
     * @since	2.0.0
     * @global	object	wpdb
     */
    public function clear_thumbnails() {
        
        $wpp_image = WPP_Image::get_instance();

        if ( $wpp_image->can_create_thumbnails() ) {
        
            $wpp_uploads_dir = $wpp_image->get_plugin_uploads_dir();
            
            if ( is_array($wpp_uploads_dir) && !empty($wpp_uploads_dir) ) {
                
                $token = isset( $_POST['token'] ) ? $_POST['token'] : null;
                $key = get_site_option( "wpp_rand" );				
                
                if (
                    current_user_can( 'manage_options' ) 
                    && ( $token === $key )
                ) {
                    
                    if ( is_dir( $wpp_uploads_dir['basedir'] ) ) {
                        
                        $files = glob( "{$wpp_uploads_dir['basedir']}/*" ); // get all related images
                    
                        if ( is_array($files) && !empty($files) ) {
    
                            foreach( $files as $file ){ // iterate files
                                if ( is_file( $file ) ) {
                                    @unlink( $file ); // delete file
                                }
                            }
                            
                            echo 1;
    
                        } else {
                            echo 2;
                        }
                        
                    } else {
                        echo 3;
                    }
                
                } else {
                    echo 4;
                }
                
            }
            
        } else {
            echo 3;
        }

        wp_die();

    }
    
    /**
     * Truncates data and cache on demand.
     *
     * @since	2.0.0
     * @global	object	wpdb
     */
    public function clear_data() {

        $token = $_POST['token'];
        $clear = isset( $_POST['clear'] ) ? $_POST['clear'] : null;
        $key = get_site_option( "wpp_rand" );

        if (
            current_user_can( 'manage_options' ) 
            && ( $token === $key ) 
            && $clear
        ) {
            
            global $wpdb;

            // set table name
            $prefix = $wpdb->prefix . "popularposts";

            if ( $clear == 'cache' ) {
                
                if ( $wpdb->get_var("SHOW TABLES LIKE '{$prefix}summary'") ) {
                    
                    $wpdb->query("TRUNCATE TABLE {$prefix}summary;");
                    $this->flush_transients();
                    
                    echo 1;
                    
                } else {
                    echo 2;
                }
                
            } elseif ( $clear == 'all' ) {
                
                if ( $wpdb->get_var("SHOW TABLES LIKE '{$prefix}data'") && $wpdb->get_var("SHOW TABLES LIKE '{$prefix}summary'") ) {
                    
                    $wpdb->query("TRUNCATE TABLE {$prefix}data;");
                    $wpdb->query("TRUNCATE TABLE {$prefix}summary;");
                    $this->flush_transients();
                    
                    echo 1;
                    
                } else {
                    echo 2;
                }
                
            } else {
                echo 3;
            }
        } else {
            echo 4;
        }

        wp_die();

    }
    
    /**
     * Deletes cached (transient) data.
     *
     * @since   3.0.0
     * @access  private
     */
    private function flush_transients() {

        $wpp_transients = get_site_option( 'wpp_transients' );

        if ( $wpp_transients && is_array( $wpp_transients ) && !empty( $wpp_transients ) ) {
            
            for ( $t=0; $t < count( $wpp_transients ); $t++ )
                delete_transient( $wpp_transients[$t] );

            update_site_option( 'wpp_transients', array() );
            
        }

    }
    
    /**
     * Purges post from data/summary tables.
     *
     * @since    3.3.0
     */
    public function purge_post_data() {

        if ( current_user_can( 'delete_posts' ) )
            add_action( 'delete_post', array( $this, 'purge_post' ) );

    }

    /**
     * Purges post from data/summary tables.
     *
     * @since    3.3.0
     * @global   object   $wpdb
     * @return   bool
     */
    public function purge_post( $post_ID ) {

        global $wpdb;

        if ( $wpdb->get_var( $wpdb->prepare( "SELECT postid FROM {$wpdb->prefix}popularpostsdata WHERE postid = %d", $post_ID ) ) ) {
            // Delete from data table
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}popularpostsdata WHERE postid = %d;", $post_ID ) );
            // Delete from summary table
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}popularpostssummary WHERE postid = %d;", $post_ID ) );				
        }

    }
    
    /**
     * Purges old post data from summary table.
     *
     * @since	2.0.0
     * @global	object	$wpdb
     */
    public function purge_data() {

        global $wpdb;

        $wpdb->query( "DELETE FROM {$wpdb->prefix}popularpostssummary WHERE view_date < DATE_SUB('" . WPP_Helper::curdate() . "', INTERVAL {$this->options['tools']['log']['expires_after']} DAY);" );

    } // end purge_data
    
    /**
     * Checks if an upgrade procedure is required.
     *
     * @since	2.4.0
     */
    public function upgrade_check(){

        // Get WPP version
        $wpp_ver = get_site_option( 'wpp_ver' );

        if ( !$wpp_ver ) {
            add_site_option( 'wpp_ver', $this->version );
        } elseif ( version_compare( $wpp_ver, $this->version, '<' ) ) {
            $this->upgrade();
        }

    } // end upgrade_check

    /**
     * On plugin upgrade, performs a number of actions: update WPP database tables structures (if needed),
     * run the setup wizard (if needed), and some other checks.
     *
     * @since	2.4.0
     * @access  private
     * @global	object	wpdb
     */
    private function upgrade() {

        // Keep the upgrade process from running too many times
        if ( get_site_option('wpp_update') )
            return;
        
        add_site_option( 'wpp_update', '1' );

        global $wpdb;

        // Set table name
        $prefix = $wpdb->prefix . "popularposts";

        // Validate the structure of the tables, create missing tables / fields if necessary
        WPP_Activator::track_new_site();

        // If summary is empty, import data from popularpostsdatacache
        if ( !$wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}summary" ) ) {

            // popularpostsdatacache table is still there
            if ( $wpdb->get_var( "SHOW TABLES LIKE '{$prefix}datacache'" ) ) {

                $sql = "
                INSERT INTO {$prefix}summary (postid, pageviews, view_date, last_viewed)
                SELECT id, pageviews, day_no_time, day
                FROM {$prefix}datacache
                GROUP BY day_no_time, id
                ORDER BY day_no_time DESC";

                $result = $wpdb->query( $sql );

            }

        }
        
        // Deletes old caching tables, if found
        $wpdb->query( "DROP TABLE IF EXISTS {$prefix}datacache, {$prefix}datacache_backup;" );

        // Check storage engine
        $storage_engine_data = $wpdb->get_var( "SELECT `ENGINE` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`='{$wpdb->dbname}' AND `TABLE_NAME`='{$prefix}data';" );
        
        if ( 'InnoDB' != $storage_engine_data ) {
            $wpdb->query( "ALTER TABLE {$prefix}data ENGINE=InnoDB;" );
        }
        
        $storage_engine_summary = $wpdb->get_var( "SELECT `ENGINE` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`='{$wpdb->dbname}' AND `TABLE_NAME`='{$prefix}summary';" );
        
        if ( 'InnoDB' != $storage_engine_summary ) {
            $wpdb->query( "ALTER TABLE {$prefix}summary ENGINE=InnoDB;" );
        }

        // Update WPP version
        update_site_option( 'wpp_ver', $this->version );
        
        // Remove upgrade flag
        delete_site_option( 'wpp_update' );

    } // end __upgrade
    
    /**
     * Checks if the technical requirements are met.
     *
     * @since	2.4.0
     * @access  private
     * @link	http://wordpress.stackexchange.com/questions/25910/uninstall-activate-deactivate-a-plugin-typical-features-how-to/25979#25979
     * @global	string $wp_version
     * @return	array
     */
    private function check_requirements() {

        global $wp_version;

        $php_min_version = '5.2';
        $wp_min_version = '3.8';
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
    
    /**
     * Outputs error messages to wp-admin.
     *
     * @since	2.4.0
     */
    public function check_admin_notices() {

        $errors = $this->check_requirements();

        if ( empty($errors) )
            return;

        if ( isset($_GET['activate']) )
            unset($_GET['activate']);

        printf(
            __('<div class="error"><p>%1$s</p><p><i>%2$s</i> has been <strong>deactivated</strong>.</p></div>', 'wordpress-popular-posts'),
            join( '</p><p>', $errors ),
            'WordPress Popular Posts'
        );

        $plugin_file = 'wordpress-popular-posts/wordpress-popular-posts.php';
        deactivate_plugins( $plugin_file );

    } // end check_admin_notices

} // End WPP_Admin class
