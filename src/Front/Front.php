<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines hooks to enqueue the public-specific stylesheet and JavaScript.
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/Front
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts\Front;

use WordPressPopularPosts\Helper;

class Front {

    /**
     * Plugin options.
     *
     * @var     array      $config
     * @access  private
     */
    private $config;

    /**
     * Translate object.
     *
     * @var     \WordPressPopularPosts\Translate    $translate
     * @access  private
     */
    private $translate;

    /**
     * Construct.
     *
     * @since   5.0.0
     * @param   array                               $config     Admin settings.
     * @param   \WordPressPopularPosts\Translate    $translate  Translate class.
     */
    public function __construct(array $config, \WordPressPopularPosts\Translate $translate)
    {
        $this->config = $config;
        $this->translate = $translate;
    }

    /**
     * WordPress public-facing hooks.
     *
     * @since   5.0.0
     */
    public function hooks()
    {
        add_action('wp_ajax_update_views_ajax', [$this, 'update_views']);
        add_action('wp_ajax_nopriv_update_views_ajax', [$this, 'update_views']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Enqueues public facing assets.
     *
     * @since   5.0.0
     */
    public function enqueue_assets()
    {
        // Enqueue WPP's stylesheet.
        if ( $this->config['tools']['css'] ) {
            $theme_file = get_stylesheet_directory() . '/wpp.css';

            if ( @is_file($theme_file) ) {
                wp_enqueue_style('wordpress-popular-posts-css', get_stylesheet_directory_uri() . "/wpp.css", [], WPP_VERSION, 'all');
            } // Load stock stylesheet
            else {
                wp_enqueue_style('wordpress-popular-posts-css', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/css/wpp.css', [], WPP_VERSION, 'all');
            }
        }

        // Enqueue WPP's library.
        $is_single = 0;

        if (
            ( 0 == $this->config['tools']['log']['level'] && !is_user_logged_in() )
            || ( 1 == $this->config['tools']['log']['level'] )
            || ( 2 == $this->config['tools']['log']['level'] && is_user_logged_in() )
        ) {
            $is_single = Helper::is_single();
        }

        wp_register_script('wpp-js', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/wpp-4.2.0.min.js', [], WPP_VERSION, false);
        $params = [
            'sampling_active' => (int) $this->config['tools']['sampling']['active'],
            'sampling_rate' => $this->config['tools']['sampling']['rate'],
            'ajax_url' => esc_url_raw(rest_url('wordpress-popular-posts/v1/popular-posts')),
            'ID' => $is_single,
            'token' => wp_create_nonce('wp_rest'),
            'debug' => WP_DEBUG
        ];
        wp_localize_script('wpp-js', 'wpp_params', $params);
        wp_enqueue_script('wpp-js');
    }

    /**
     * Updates views count on page load via AJAX.
     *
     * @since   2.0.0
     */
    public function update_views()
    {
        if ( ! wp_verify_nonce($_POST['token'], 'wpp-token') || ! Helper::is_number($_POST['wpp_id']) ) {
            die( "WPP: Oops, invalid request!" );
        }

        $nonce = $_POST['token'];
        $post_ID = $_POST['wpp_id'];
        $exec_time = 0;

        $start = Helper::microtime_float();
        $result = $this->update_views_count($post_ID);
        $end = Helper::microtime_float();
        $exec_time += round($end - $start, 6);

        if ( $result ) {
            die("WPP: OK. Execution time: " . $exec_time . " seconds");
        }

        die("WPP: Oops, could not update the views count!");
    }

    /**
     * Updates views count.
     *
     * @since    1.4.0
     * @access   private
     * @global   object    $wpdb
     * @param    int       $post_ID
     * @return   bool|int  FALSE if query failed, TRUE on success
     */
    private function update_views_count($post_ID) {
        /*
        TODO:
        For WordPress Multisite, we must define the DIEONDBERROR constant for database errors to display like so:
        <?php define( 'DIEONDBERROR', true ); ?>
        */
        global $wpdb;
        $table = $wpdb->prefix . "popularposts";
        $wpdb->show_errors();

        // Get translated object ID
        $post_ID = $this->translate->get_object_id(
            $post_ID,
            get_post_type($post_ID),
            true,
            $this->translate->get_default_language()
        );
        $now = Helper::now();
        $curdate = Helper::curdate();
        $views = ( $this->config['tools']['sampling']['active'] )
          ? $this->config['tools']['sampling']['rate']
          : 1;

        // Allow WP themers / coders perform an action
        // before updating views count
        if ( has_action('wpp_pre_update_views') )
            do_action('wpp_pre_update_views', $post_ID, $views);

        // Update all-time table
        $result1 = $wpdb->query($wpdb->prepare(
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
        $result2 = $wpdb->query($wpdb->prepare(
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
        if ( has_action('wpp_post_update_views' ))
            do_action('wpp_post_update_views', $post_ID);

        return true;
    }
}
