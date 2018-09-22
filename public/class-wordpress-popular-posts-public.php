<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://cabrerahector.com/
 * @since      4.0.0
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks to
 * enqueue the public-specific stylesheet and JavaScript.
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/public
 * @author     Hector Cabrera <me@cabrerahector.com>
 */
class WPP_Public {

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
    private $admin_options = array();

    /**
     * Initialize the class and set its properties.
     *
     * @since    4.0.0
     * @param    string    $plugin_name       The name of the plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->admin_options = WPP_Settings::get( 'admin_options' );

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    4.0.0
     */
    public function enqueue_styles() {
        if ( $this->admin_options['tools']['css'] ) {
            $theme_file = get_stylesheet_directory() . '/wpp.css';

            if ( @is_file( $theme_file ) ) {
                wp_enqueue_style( 'wordpress-popular-posts-css', get_stylesheet_directory_uri() . "/wpp.css", array(), $this->version, 'all' );
            } // Load stock stylesheet
            else {
                wp_enqueue_style( 'wordpress-popular-posts-css', plugin_dir_url( __FILE__ ) . 'css/wpp.css', array(), $this->version, 'all' );
            }
        }
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    4.0.0
     */
    public function enqueue_scripts() {

        /**
         * Enqueue WPP's library.
         */

        $is_single = 0;

        if (
            ( 0 == $this->admin_options['tools']['log']['level'] && !is_user_logged_in() )
            || ( 1 == $this->admin_options['tools']['log']['level'] )
            || ( 2 == $this->admin_options['tools']['log']['level'] && is_user_logged_in() )
        ) {
            $is_single = WPP_Helper::is_single();
        }

        wp_register_script( 'wpp-js', plugin_dir_url( __FILE__ ) . 'js/wpp-4.2.0.min.js', array(), $this->version, false );

        $params = array(
            'sampling_active' => (int) $this->admin_options['tools']['sampling']['active'],
            'sampling_rate' => $this->admin_options['tools']['sampling']['rate'],
            'ajax_url' => esc_url_raw( rest_url( 'wordpress-popular-posts/v1/popular-posts/' ) ),
            'ID' => $is_single,
            'token' => wp_create_nonce( 'wp_rest' ),
            'debug' => WP_DEBUG
        );
        wp_localize_script( 'wpp-js', 'wpp_params', $params );

        wp_enqueue_script( 'wpp-js' );

    }

    /**
     * Updates views count on page load via AJAX.
     *
     * @since	2.0.0
     */
    public function update_views(){

        if ( !wp_verify_nonce($_POST['token'], 'wpp-token') || !WPP_helper::is_number($_POST['wpp_id']) ) {
            die( "WPP: Oops, invalid request!" );
        }

        $nonce = $_POST['token'];
        $post_ID = $_POST['wpp_id'];

        $exec_time = 0;

        $start = WPP_helper::microtime_float();
        $result = $this->update_views_count( $post_ID );
        $end = WPP_helper::microtime_float();

        $exec_time += round( $end - $start, 6 );

        if ( $result ) {
            die( "WPP: OK. Execution time: " . $exec_time . " seconds" );
        }

        die( "WPP: Oops, could not update the views count!" );

    } // end update_views_ajax

    /**
     * Updates views count.
     *
     * @since    1.4.0
     * @access   private
     * @global   object    $wpdb
     * @param    int       $post_ID
     * @return   bool|int  FALSE if query failed, TRUE on success
     */
    private function update_views_count( $post_ID ) {

        /*
        TODO:
        For WordPress Multisite, we must define the DIEONDBERROR constant for database errors to display like so:
        <?php define( 'DIEONDBERROR', true ); ?>
        */

        global $wpdb;
        $table = $wpdb->prefix . "popularposts";
        $wpdb->show_errors();

        // Get translated object ID
        $translate = WPP_translate::get_instance();
        $post_ID = $translate->get_object_id(
            $post_ID,
            get_post_type( $post_ID ),
            true,
            $translate->get_default_language()
        );

        $now = WPP_helper::now();
        $curdate = WPP_helper::curdate();
        $views = ( $this->admin_options['tools']['sampling']['active'] )
          ? $this->admin_options['tools']['sampling']['rate']
          : 1;

        // Allow WP themers / coders perform an action
        // before updating views count
        if ( has_action( 'wpp_pre_update_views' ) )
            do_action( 'wpp_pre_update_views', $post_ID, $views );

        // Update all-time table
        $result1 = $wpdb->query( $wpdb->prepare(
            "INSERT INTO {$table}data
            (postid, day, last_viewed, pageviews) VALUES (%d, %s, %s, %d)
            ON DUPLICATE KEY UPDATE pageviews = pageviews + %d, last_viewed = %s;",
            $post_ID,
            $now,
            $now,
            $views,
            $views,
            $now
        ));

        // Update range (summary) table
        $result2 = $wpdb->query( $wpdb->prepare(
            "INSERT INTO {$table}summary
            (postid, pageviews, view_date, view_datetime) VALUES (%d, %d, %s, %s)
            ON DUPLICATE KEY UPDATE pageviews = pageviews + %d, view_datetime = %s;",
            $post_ID,
            $views,
            $curdate,
            $now,
            $views,
            $now
        ));

        if ( !$result1 || !$result2 )
            return false;

        // Allow WP themers / coders perform an action
        // after updating views count
        if ( has_action( 'wpp_post_update_views' ) )
            do_action( 'wpp_post_update_views', $post_ID );

        return true;

    } // end update_views_count

    /**
     * WPP shortcode handler.
     *
     * @since    1.4.0
     * @param    array    $atts    User defined attributes in shortcode tag
     * @return   string
     */
    public function wpp_shortcode( $atts = null ) {
        /**
        * @var string $header
        * @var int $limit
        * @var int $offset
        * @var string $range
        * @var bool $freshness
        * @var string $order_by
        * @var string $post_type
        * @var string $pid
        * @var string $cat
        * @var string $author
        * @var int $title_length
        * @var int $title_by_words
        * @var int $excerpt_length
        * @var int $excerpt_format
        * @var int $excerpt_by_words
        * @var int $thumbnail_width
        * @var int $thumbnail_height
        * @var bool $rating
        * @var bool $stats_comments
        * @var bool $stats_views
        * @var bool $stats_author
        * @var bool $stats_date
        * @var string $stats_date_format
        * @var bool $stats_category
        * @var string $wpp_start
        * @var string $wpp_end
        * @var string $header_start
        * @var string $header_end
        * @var string $post_html
        * @var bool $php
        */
        extract( shortcode_atts( array(
            'header' => '',
            'limit' => 10,
            'offset' => 0,
            'range' => 'daily',
            'time_unit' => 'hour',
            'time_quantity' => 24,
            'freshness' => false,
            'order_by' => 'views',
            'post_type' => 'post',
            'pid' => '',
            'cat' => '',
            'taxonomy' => 'category',
            'term_id' => '',
            'author' => '',
            'title_length' => 0,
            'title_by_words' => 0,
            'excerpt_length' => 0,
            'excerpt_format' => 0,
            'excerpt_by_words' => 0,
            'thumbnail_width' => 0,
            'thumbnail_height' => 0,
            'rating' => false,
            'stats_comments' => false,
            'stats_views' => true,
            'stats_author' => false,
            'stats_date' => false,
            'stats_date_format' => 'F j, Y',
            'stats_category' => false,
            'stats_taxonomy' => false,
            'wpp_start' => '<ul class="wpp-list">',
            'wpp_end' => '</ul>',
            'header_start' => '<h2>',
            'header_end' => '</h2>',
            'post_html' => '',
            'php' => false
        ), $atts, 'wpp') );

        // possible values for "Time Range" and "Order by"
        $time_units = array( "minute", "hour", "day", "week", "month" );
        $range_values = array( "daily", "last24hours", "weekly", "last7days", "monthly", "last30days", "all", "custom" );
        $order_by_values = array( "comments", "views", "avg" );

        $shortcode_ops = array(
            'title' => strip_tags( $header ),
            'limit' => ( !empty( $limit ) && WPP_Helper::is_number( $limit ) && $limit > 0 ) ? $limit : 10,
            'offset' => ( !empty( $offset ) && WPP_Helper::is_number( $offset ) && $offset >= 0 ) ? $offset : 0,
            'range' => ( in_array($range, $range_values) ) ? $range : 'daily',
            'time_quantity' => ( !empty( $time_quantity ) && WPP_Helper::is_number( $time_quantity ) && $time_quantity > 0 ) ? $time_quantity : 24,
            'time_unit' => ( in_array($time_unit, $time_units) ) ? $time_unit : 'hour',
            'freshness' => empty( $freshness ) ? false : $freshness,
            'order_by' => ( in_array( $order_by, $order_by_values ) ) ? $order_by : 'views',
            'post_type' => empty( $post_type ) ? 'post,page' : $post_type,
            'pid' => rtrim( preg_replace( '|[^0-9,]|', '', $pid ), "," ),
            'cat' => rtrim( preg_replace( '|[^0-9,-]|', '', $cat ), "," ),
            'taxonomy' => empty( $taxonomy ) ? 'category' : $taxonomy,
            'term_id' => rtrim( preg_replace( '|[^0-9,-]|', '', $term_id ), "," ),
            'author' => rtrim( preg_replace( '|[^0-9,]|', '', $author ), "," ),
            'shorten_title' => array(
                'active' => ( !empty( $title_length ) && WPP_Helper::is_number( $title_length ) && $title_length > 0 ),
                'length' => ( !empty( $title_length ) && WPP_Helper::is_number( $title_length) ) ? $title_length : 0,
                'words' => ( !empty( $title_by_words ) && WPP_Helper::is_number( $title_by_words ) && $title_by_words > 0 ),
            ),
            'post-excerpt' => array(
                'active' => ( !empty( $excerpt_length ) && WPP_Helper::is_number( $excerpt_length ) && $excerpt_length > 0 ),
                'length' => ( !empty( $excerpt_length ) && WPP_Helper::is_number( $excerpt_length ) ) ? $excerpt_length : 0,
                'keep_format' => ( !empty( $excerpt_format ) && WPP_Helper::is_number( $excerpt_format ) && $excerpt_format > 0 ),
                'words' => ( !empty( $excerpt_by_words ) && WPP_Helper::is_number( $excerpt_by_words ) && $excerpt_by_words > 0 ),
            ),
            'thumbnail' => array(
                'active' => ( !empty( $thumbnail_width ) && WPP_Helper::is_number( $thumbnail_width ) && $thumbnail_width > 0 ),
                'width' => ( !empty( $thumbnail_width ) && WPP_Helper::is_number( $thumbnail_width ) && $thumbnail_width > 0 ) ? $thumbnail_width : 0,
                'height' => ( !empty( $thumbnail_height ) && WPP_Helper::is_number( $thumbnail_height ) && $thumbnail_height > 0 ) ? $thumbnail_height : 0,
            ),
            'rating' => empty( $rating ) ? false : $rating,
            'stats_tag' => array(
                'comment_count' => empty( $stats_comments ) ? false : $stats_comments,
                'views' => empty( $stats_views ) ? false : $stats_views,
                'author' => empty( $stats_author ) ? false : $stats_author,
                'date' => array(
                    'active' => empty( $stats_date ) ? false : $stats_date,
                    'format' => empty( $stats_date_format ) ? 'F j, Y' : $stats_date_format
                ),
                'category' => empty( $stats_category ) ? false : $stats_category,
                'taxonomy' => array(
                    'active' => empty( $stats_taxonomy ) ? false : $stats_taxonomy,
                    'name' => empty( $taxonomy ) ? 'category' : $taxonomy,
                )
            ),
            'markup' => array(
                'custom_html' => true,
                'wpp-start' => empty( $wpp_start ) ? '<ul class="wpp-list">' : $wpp_start,
                'wpp-end' => empty( $wpp_end) ? '</ul>' : $wpp_end,
                'title-start' => empty( $header_start ) ? '' : $header_start,
                'title-end' => empty( $header_end ) ? '' : $header_end,
                'post-html' => empty( $post_html ) ? '<li>{thumb} {title} <span class="wpp-meta post-stats">{stats}</span></li>' : $post_html
            )
        );

        // Post / Page / CTP filter
        $ids = array_filter( explode( ",", $shortcode_ops['pid'] ), 'is_numeric' );
        // Got no valid IDs, clear
        if ( empty( $ids ) ) {
            $shortcode_ops['pid'] = '';
        }

        // Category filter
        $ids = array_filter( explode( ",", $shortcode_ops['cat'] ), 'is_numeric' );
        // Got no valid IDs, clear
        if ( empty( $ids ) ) {
            $shortcode_ops['cat'] = '';
        }

        // Author filter
        $ids = array_filter( explode( ",", $shortcode_ops['author'] ), 'is_numeric' );
        // Got no valid IDs, clear
        if ( empty( $ids ) ) {
            $shortcode_ops['author'] = '';
        }

        $shortcode_content = '';

        // is there a title defined by user?
        if (
            !empty( $header )
            && !empty( $header_start )
            && !empty( $header_end )
        ) {
            $shortcode_content .= htmlspecialchars_decode( $header_start, ENT_QUOTES ) . apply_filters( 'widget_title', $header ) . htmlspecialchars_decode( $header_end, ENT_QUOTES );
        }

        $cached = false;

        // Return cached results
        if ( $this->admin_options['tools']['cache']['active'] ) {

            $key = md5( json_encode($shortcode_ops) );
            $popular_posts = WPP_Cache::get( $key );

            if ( false === $popular_posts ) {

                $popular_posts = new WPP_Query( $shortcode_ops );

                $time_value = $this->admin_options['tools']['cache']['interval']['value']; // eg. 5
                $time_unit = $this->admin_options['tools']['cache']['interval']['time']; // eg. 'minute'

                WPP_Cache::set(
                    $key,
                    $popular_posts,
                    $time_value,
                    $time_unit
                );

            }

            $cached = true;

        } // Get popular posts
        else {
            $popular_posts = new WPP_Query( $shortcode_ops );
        }

        $output = new WPP_Output( $popular_posts->get_posts(), $shortcode_ops );

        $shortcode_content .= $output->get_output();

        return $shortcode_content;

    }

	/**
     * Initiates the popular posts REST controller class.
     *
     * @since    4.0.13
     */
    public function init_rest_route() {
        $rest_controller = new WP_REST_Popular_Posts_Controller();
        $rest_controller->register_routes();
    }

} // End WPP_Public class
