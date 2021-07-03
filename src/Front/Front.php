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
use WordPressPopularPosts\Output;
use WordPressPopularPosts\Query;

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
     * Output object.
     *
     * @var     \WordPressPopularPosts\Output       $output
     * @access  private
     */
    private $output;

    /**
     * Construct.
     *
     * @since   5.0.0
     * @param   array                               $config     Admin settings.
     * @param   \WordPressPopularPosts\Translate    $translate  Translate class.
     */
    public function __construct(array $config, \WordPressPopularPosts\Translate $translate, \WordPressPopularPosts\Output $output)
    {
        $this->config = $config;
        $this->translate = $translate;
        $this->output = $output;
    }

    /**
     * WordPress public-facing hooks.
     *
     * @since   5.0.0
     */
    public function hooks()
    {
        add_shortcode('wpp', [$this, 'wpp_shortcode']);
        add_action('wp_head', [$this, 'inline_loading_css']);
        add_action('wp_ajax_update_views_ajax', [$this, 'update_views']);
        add_action('wp_ajax_nopriv_update_views_ajax', [$this, 'update_views']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('script_loader_tag', [$this, 'convert_inline_js_into_json'], 10, 3);
    }

    /**
     * 
     */
    public function inline_loading_css()
    {
        ?>
        <style>
            @-webkit-keyframes bgslide {
                from {
                    background-position-x: 0;
                }
                to {
                    background-position-x: -200%;
                }
            }

            @keyframes bgslide {
                    from {
                        background-position-x: 0;
                    }
                    to {
                        background-position-x: -200%;
                    }
            }

            .wpp-widget-placeholder {
                margin: 0 auto;
                width: 60px;
                height: 3px;
                background: #dd3737;
                background: -webkit-gradient(linear, left top, right top, from(#dd3737), color-stop(10%, #571313), to(#dd3737));
                background: linear-gradient(90deg, #dd3737 0%, #571313 10%, #dd3737 100%);
                background-size: 200% auto;
                border-radius: 3px;
                -webkit-animation: bgslide 1s infinite linear;
                animation: bgslide 1s infinite linear;
            }
        </style>
        <?php
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
            ( 0 == $this->config['tools']['log']['level'] && ! is_user_logged_in() )
            || ( 1 == $this->config['tools']['log']['level'] )
            || ( 2 == $this->config['tools']['log']['level'] && is_user_logged_in() )
        ) {
            $is_single = Helper::is_single();
        }

        wp_register_script('wpp-js', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/wpp.min.js', [], WPP_VERSION, false);
        $params = [
            'sampling_active' => (int) $this->config['tools']['sampling']['active'],
            'sampling_rate' => (int) $this->config['tools']['sampling']['rate'],
            'ajax_url' => esc_url_raw(rest_url('wordpress-popular-posts/v1/popular-posts')),
            'ID' => (int) $is_single,
            'token' => wp_create_nonce('wp_rest'),
            'lang' => function_exists('PLL') ? $this->translate->get_current_language() : 0,
            'debug' => (int) WP_DEBUG
        ];
        wp_enqueue_script('wpp-js');
        wp_add_inline_script('wpp-js', json_encode($params), 'before');
    }

    /**
     * Converts inline script tag into type=application/json.
     *
     * This function mods the original script tag as printed
     * by WordPress which contains the data for the wpp_params
     * object into a JSON script. This improves compatibility
     * with Content Security Policy (CSP).
     *
     * @since   5.2.0
     * @param   string  $tag
     * @param   string  $handle
     * @param   string  $src
     * @return  string  $tag
     */
    function convert_inline_js_into_json($tag, $handle, $src)
    {
        if ( 'wpp-js' === $handle ) {
            // id attribute found, replace it
            if ( false !== strpos($tag, 'wpp-js-js-before') ) {
                $tag = str_replace('wpp-js-js-before', 'wpp-json', $tag);
            } // id attribute missing, let's add it
            else {
                $pos = strpos($tag, '>');
                $tag = substr_replace($tag, ' id="wpp-json">', $pos, 1);
            }

            // type attribute found, replace it
            if ( false !== strpos($tag, 'type') ) {
                $pos = strpos($tag, 'text/javascript');

                if ( false !== $pos )
                    $tag = substr_replace($tag, 'application/json', $pos, strlen('text/javascript'));
            } // type attribute missing, let's add it
            else {
                $pos = strpos($tag, '>');
                $tag = substr_replace($tag, ' type="application/json">', $pos, 1);
            }
        }

        return $tag;
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

    /**
     * WPP shortcode handler.
     *
     * @since    1.4.0
     * @param    array    $atts    User defined attributes in shortcode tag
     * @return   string
     */
    public function wpp_shortcode($atts = null) {
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
        extract(shortcode_atts([
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
            'theme' => '',
            'php' => false
        ], $atts, 'wpp'));

        // possible values for "Time Range" and "Order by"
        $time_units = ["minute", "hour", "day", "week", "month"];
        $range_values = ["daily", "last24hours", "weekly", "last7days", "monthly", "last30days", "all", "custom"];
        $order_by_values = ["comments", "views", "avg"];

        $shortcode_ops = [
            'title' => strip_tags($header),
            'limit' => ( ! empty($limit ) && Helper::is_number($limit) && $limit > 0 ) ? $limit : 10,
            'offset' => ( ! empty($offset) && Helper::is_number($offset) && $offset >= 0 ) ? $offset : 0,
            'range' => ( in_array($range, $range_values) ) ? $range : 'daily',
            'time_quantity' => ( ! empty($time_quantity ) && Helper::is_number($time_quantity) && $time_quantity > 0 ) ? $time_quantity : 24,
            'time_unit' => ( in_array($time_unit, $time_units) ) ? $time_unit : 'hour',
            'freshness' => empty($freshness) ? false : $freshness,
            'order_by' => ( in_array($order_by, $order_by_values) ) ? $order_by : 'views',
            'post_type' => empty($post_type) ? 'post,page' : $post_type,
            'pid' => rtrim(preg_replace('|[^0-9,]|', '', $pid), ","),
            'cat' => rtrim(preg_replace('|[^0-9,-]|', '', $cat), ","),
            'taxonomy' => empty($taxonomy) ? 'category' : $taxonomy,
            'term_id' => rtrim(preg_replace('|[^0-9,;-]|', '', $term_id), ","),
            'author' => rtrim(preg_replace('|[^0-9,]|', '', $author), ","),
            'shorten_title' => [
                'active' => ( ! empty($title_length) && Helper::is_number($title_length) && $title_length > 0 ),
                'length' => ( ! empty($title_length) && Helper::is_number($title_length) ) ? $title_length : 0,
                'words' => ( ! empty($title_by_words) && Helper::is_number($title_by_words) && $title_by_words > 0 ),
            ],
            'post-excerpt' => [
                'active' => ( ! empty($excerpt_length) && Helper::is_number($excerpt_length) && $excerpt_length > 0 ),
                'length' => ( ! empty($excerpt_length) && Helper::is_number($excerpt_length) ) ? $excerpt_length : 0,
                'keep_format' => ( ! empty($excerpt_format) && Helper::is_number($excerpt_format) && $excerpt_format > 0 ),
                'words' => ( ! empty($excerpt_by_words) && Helper::is_number($excerpt_by_words) && $excerpt_by_words > 0 ),
            ],
            'thumbnail' => [
                'active' => ( ! empty($thumbnail_width) && Helper::is_number($thumbnail_width) && $thumbnail_width > 0 ),
                'width' => ( ! empty($thumbnail_width) && Helper::is_number($thumbnail_width) && $thumbnail_width > 0 ) ? $thumbnail_width : 0,
                'height' => ( ! empty($thumbnail_height) && Helper::is_number($thumbnail_height) && $thumbnail_height > 0 ) ? $thumbnail_height : 0,
            ],
            'rating' => empty($rating) ? false : $rating,
            'stats_tag' => [
                'comment_count' => empty($stats_comments) ? false : $stats_comments,
                'views' => empty($stats_views) ? false : $stats_views,
                'author' => empty($stats_author) ? false : $stats_author,
                'date' => [
                    'active' => empty($stats_date) ? false : $stats_date,
                    'format' => empty($stats_date_format) ? 'F j, Y' : $stats_date_format
                ],
                'category' => empty($stats_category) ? false : $stats_category,
                'taxonomy' => [
                    'active' => empty($stats_taxonomy) ? false : $stats_taxonomy,
                    'name' => empty($taxonomy) ? 'category' : $taxonomy,
                ]
            ],
            'markup' => [
                'custom_html' => true,
                'wpp-start' => empty($wpp_start) ? '' : $wpp_start,
                'wpp-end' => empty($wpp_end) ? '' : $wpp_end,
                'title-start' => empty($header_start) ? '' : $header_start,
                'title-end' => empty($header_end) ? '' : $header_end,
                'post-html' => empty($post_html) ? '<li>{thumb} {title} <span class="wpp-meta post-stats">{stats}</span></li>' : $post_html
            ],
            'theme' => [
                'name' => trim($theme)
            ]
        ];

        // Post / Page / CTP filter
        $ids = array_filter(explode(",", $shortcode_ops['pid']), 'is_numeric');
        // Got no valid IDs, clear
        if ( empty($ids) ) {
            $shortcode_ops['pid'] = '';
        }

        // Category filter
        $ids = array_filter(explode(",", $shortcode_ops['cat']), 'is_numeric');
        // Got no valid IDs, clear
        if ( empty($ids) ) {
            $shortcode_ops['cat'] = '';
        }

        // Author filter
        $ids = array_filter(explode( ",", $shortcode_ops['author']), 'is_numeric');
        // Got no valid IDs, clear
        if ( empty($ids) ) {
            $shortcode_ops['author'] = '';
        }

        $shortcode_content = '';
        $cached = false;

        // Return cached results
        if ( $this->config['tools']['cache']['active'] ) {

            $key = md5(json_encode($shortcode_ops));
            $popular_posts = \WordPressPopularPosts\Cache::get($key);

            if ( false === $popular_posts ) {
                $popular_posts = new Query($shortcode_ops);

                $time_value = $this->config['tools']['cache']['interval']['value']; // eg. 5
                $time_unit = $this->config['tools']['cache']['interval']['time']; // eg. 'minute'

                // No popular posts found, check again in 1 minute
                if ( ! $popular_posts->get_posts() ) {
                    $time_value = 1;
                    $time_unit = 'minute';
                }

                \WordPressPopularPosts\Cache::set(
                    $key,
                    $popular_posts,
                    $time_value,
                    $time_unit
                );
            }

            $cached = true;

        } // Get popular posts
        else {
            $popular_posts = new Query($shortcode_ops);
        }

        $this->output->set_data($popular_posts->get_posts());
        $this->output->set_public_options($shortcode_ops);
        $this->output->build_output();

        $shortcode_content .= $this->output->get_output();

        return $shortcode_content;
    }

}
