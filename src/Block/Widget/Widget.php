<?php
namespace WordPressPopularPosts\Block\Widget;

use WordPressPopularPosts\Helper;
use WordPressPopularPosts\Query;
use WordPressPopularPosts\Output;
use WordPressPopularPosts\Block\Block;

class Widget extends Block
{

    /**
     * Administrative settings.
     *
     * @since   5.4.0
     * @var     array
     * @access  private
     */
    private $admin_options = [];

    /**
     * Image object.
     *
     * @since   5.4.0
     * @var     WordPressPopularPosts\Image
     * @access  private
     */
    private $thumbnail;

    /**
     * Output object.
     *
     * @since   5.4.0
     * @var     \WordPressPopularPosts\Output
     * @access  private
     */
    private $output;

    /**
     * Translate object.
     *
     * @since   5.4.0
     * @var     \WordPressPopularPosts\Translate    $translate
     * @access  private
     */
    private $translate;

    /**
     * Themer object.
     *
     * @since   5.4.0
     * @var     \WordPressPopularPosts\Themer       $themer
     * @access  private
     */
    private $themer;

    /**
     * Default attributes.
     *
     * @since   5.4.0
     * @var     array      $defaults
     * @access  private
     */
    private $defaults;

    /**
     * Construct.
     *
     * @since   5.4.0
     * @param   array                            $config
     * @param   \WordPressPopularPosts\Output    $output
     * @param   \WordPressPopularPosts\Image     $image
     * @param   \WordPressPopularPosts\Translate $translate
     * @param   \WordPressPopularPosts\Themer    $themer
     */
    public function __construct(array $config, \WordPressPopularPosts\Output $output, \WordPressPopularPosts\Image $thumbnail, \WordPressPopularPosts\Translate $translate, \WordPressPopularPosts\Themer $themer)
    {
        $this->admin_options = $config;
        $this->output = $output;
        $this->thumbnail = $thumbnail;
        $this->translate = $translate;
        $this->themer = $themer;

        $this->defaults = [
            'title' => '',
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
            'tax' => '',
            'term_id' => '',
            'author' => '',
            'title_length' => 0,
            'title_by_words' => 0,
            'excerpt_length' => 0,
            'excerpt_format' => 0,
            'excerpt_by_words' => 0,
            'thumbnail_width' => 0,
            'thumbnail_height' => 0,
            'thumbnail_build' => 'manual',
            'thumbnail_size' => '',
            'rating' => false,
            'stats_comments' => false,
            'stats_views' => true,
            'stats_author' => false,
            'stats_date' => false,
            'stats_date_format' => 'F j, Y',
            'stats_category' => false,
            'stats_taxonomy' => false,
            'custom_html' => false,
            'wpp_start' => '<ul class="wpp-list">',
            'wpp_end' => '</ul>',
            'header_start' => '<h2>',
            'header_end' => '</h2>',
            'post_html' => '',
            'theme' => ''
        ];
    }

    /**
     * 
     *
     * @since   5.4.0
     */
    public function register()
    {
        // Block editor is not available, bail.
        if ( ! function_exists('register_block_type') ) {
            return;
        }

        // Experimental feature, bail if disabled.
        if ( ! $this->admin_options['tools']['experimental'] )
            return;

        wp_enqueue_script(
            'block-wpp-widget-js',
            plugin_dir_url(dirname(dirname(dirname(__FILE__)))) . 'assets/js/blocks/block-wpp-widget.js',
            ['wp-blocks', 'wp-i18n', 'wp-element', 'wp-block-editor', 'wp-editor'],
            WPP_VERSION
        );

        wp_register_style(
            'block-wpp-editor-css',
            plugins_url('editor.css', __FILE__),
            [],
            filemtime(plugin_dir_path(__FILE__) . 'editor.css')
        );

        register_block_type(
            'wordpress-popular-posts/widget',
            [
                'editor_style'  => 'block-wpp-editor-css',
                'editor_script' => 'block-wpp-widget-js',
                'render_callback' => [$this, 'render'],
                'attributes' => [
                    '_editMode' => [
                        'type' => 'boolean',
                        'default' => true
                    ],
                    'title' => [
                        'type' => 'string',
                        'default' => ''
                    ],
                    'limit' => [
                        'type' =>'number',
                        'default' => 10
                    ],
                    'offset' => [
                        'type' => 'number',
                        'default' => 0
                    ],
                    'order_by' => [
                        'type' => 'string',
                        'default' => 'views'
                    ],
                    'range' => [
                        'type' => 'string',
                        'default' => 'last24hours'
                    ],
                    'time_quantity' => [
                        'type' => 'number',
                        'default' => 24
                    ],
                    'time_unit' => [
                        'type' => 'string',
                        'default' => 'hour'
                    ],
                    'freshness' => [
                        'type' => 'boolean',
                        'default' => false
                    ],
                    /* filters */
                    'post_type' => [
                        'type' => 'string',
                        'default' => 'post'
                    ],
                    'pid' => [
                        'type' => 'string',
                        'default' => ''
                    ],
                    'author' => [
                        'type' => 'string',
                        'default' => ''
                    ],
                    'tax' => [
                        'type' => 'string',
                        'default' => ''
                    ],
                    'term_id' => [
                        'type' => 'string',
                        'default' => ''
                    ],
                    /* post settings */
                    'shorten_title' => [
                        'type' => 'boolean',
                        'default' => false
                    ],
                    'title_length' => [
                        'type' =>'number',
                        'default' => 0
                    ],
                    'title_by_words' => [
                        'type' =>'number',
                        'default' => 0
                    ],
                    'display_post_excerpt' => [
                        'type' => 'boolean',
                        'default' => false
                    ],
                    'excerpt_format' => [
                        'type' => 'boolean',
                        'default' => false
                    ],
                    'excerpt_length' => [
                        'type' =>'number',
                        'default' => 0
                    ],
                    'excerpt_by_words' => [
                        'type' =>'number',
                        'default' => 0
                    ],
                    'display_post_thumbnail' => [
                        'type' => 'boolean',
                        'default' => false
                    ],
                    'thumbnail_width' => [
                        'type' =>'number',
                        'default' => 0
                    ],
                    'thumbnail_height' => [
                        'type' =>'number',
                        'default' => 0
                    ],
                    'thumbnail_build' => [
                        'type' => 'string',
                        'default' => 'manual'
                    ],
                    'thumbnail_size' => [
                        'type' => 'string',
                        'default' => ''
                    ],
                    /* stats tag settings */
                    'stats_comments' => [
                        'type' => 'boolean',
                        'default' => false
                    ],
                    'stats_views' => [
                        'type' => 'boolean',
                        'default' => true
                    ],
                    'stats_author' => [
                        'type' => 'boolean',
                        'default' => false
                    ],
                    'stats_date' => [
                        'type' => 'boolean',
                        'default' => false
                    ],
                    'stats_date_format' => [
                        'type' => 'string',
                        'default' => 'F j, Y'
                    ],
                    'stats_taxonomy' => [
                        'type' => 'boolean',
                        'default' => false
                    ],
                    'taxonomy' => [
                        'type' => 'string',
                        'default' => 'category'
                    ],
                    /* HTML markup settings */
                    'custom_html' => [
                        'type' => 'boolean',
                        'default' => false
                    ],
                    'header_start' => [
                        'type' => 'string',
                        'default' => '<h2>'
                    ],
                    'header_end' => [
                        'type' => 'string',
                        'default' => '</h2>'
                    ],
                    'wpp_start' => [
                        'type' => 'string',
                        'default' => '<ul class="wpp-list">'
                    ],
                    'wpp_end' => [
                        'type' => 'string',
                        'default' => '</ul>'
                    ],
                    'post_html' => [
                        'type' => 'string',
                        'default' => '<li>{thumb} {title} <span class="wpp-meta post-stats">{stats}</span></li>'
                    ],
                    'theme' => [
                        'type' => 'string',
                        'default' => ''
                    ],
                ]
            ]
        );
    }

    /**
     * Renders the block.
     *
     * @since   5.4.0
     * @param   array
     * @return  string
     */
    public function render(array $attributes)
    {
        extract($this->parse_attributes($attributes));

        $html = '<div class="widget popular-posts' . (( isset($attributes['className']) && $attributes['className'] ) ? ' '. esc_attr($attributes['className']) : '') . '">';

        // possible values for "Time Range" and "Order by"
        $time_units = ["minute", "hour", "day", "week", "month"];
        $range_values = ["daily", "last24hours", "weekly", "last7days", "monthly", "last30days", "all", "custom"];
        $order_by_values = ["comments", "views", "avg"];

        $theme_data = $this->themer->get_theme($theme);

        if ( ! isset($theme_data['json']) ) {
            $theme = '';
        }

        $query_args = [
            'title' => strip_tags($title),
            'limit' => ( ! empty($limit) && Helper::is_number($limit) && $limit > 0 ) ? $limit : 10,
            'offset' => ( ! empty($offset) && Helper::is_number($offset) && $offset >= 0 ) ? $offset : 0,
            'range' => ( in_array($range, $range_values) ) ? $range : 'daily',
            'time_quantity' => ( ! empty($time_quantity) && Helper::is_number($time_quantity) && $time_quantity > 0 ) ? $time_quantity : 24,
            'time_unit' => ( in_array($time_unit, $time_units) ) ? $time_unit : 'hour',
            'freshness' => empty($freshness) ? false : $freshness,
            'order_by' => ( in_array($order_by, $order_by_values) ) ? $order_by : 'views',
            'post_type' => empty($post_type) ? 'post' : $post_type,
            'pid' => rtrim(preg_replace('|[^0-9,]|', '', $pid), ","),
            'cat' => rtrim(preg_replace('|[^0-9,-]|', '', $cat), ","),
            'taxonomy' => empty($tax) ? 'category' : $tax,
            'term_id' => rtrim(preg_replace('|[^0-9,;-]|', '', $term_id), ","),
            'author' => rtrim(preg_replace('|[^0-9,]|', '', $author), ","),
            'shorten_title' => [
                'active' => ( ! empty($title_length) && Helper::is_number($title_length) && $title_length > 0 ),
                'length' => ( ! empty($title_length) && Helper::is_number($title_length) ) ? $title_length : 0,
                'words' => (( ! empty($title_by_words) && Helper::is_number($title_by_words) && $title_by_words > 0 )),
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
                'build' => 'predefined' == $thumbnail_build ? 'predefined' : 'manual',
                'size' => empty($thumbnail_size) ? '' : $thumbnail_size,
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
                'custom_html' => empty($custom_html) ? false : $custom_html,
                'wpp-start' => empty($wpp_start) ? '' : $wpp_start,
                'wpp-end' => empty($wpp_end) ? '' : $wpp_end,
                'title-start' => empty($header_start) ? '' : $header_start,
                'title-end' => empty($header_end) ? '' : $header_end,
                'post-html' => empty($post_html) ? '<li>{thumb} {title} <span class="wpp-meta post-stats">{stats}</span></li>' : $post_html
            ],
            'theme' => [
                'name' => empty($theme) ? '' : $theme
            ]
        ];

        // Post / Page / CTP filter
        $ids = array_filter(explode(",", $query_args['pid']), 'is_numeric');
        // Got no valid IDs, clear
        if ( empty($ids) ) {
            $query_args['pid'] = '';
        }

        // Category filter
        $ids = array_filter(explode(",", $query_args['cat']), 'is_numeric');
        // Got no valid IDs, clear
        if ( empty($ids) ) {
            $query_args['cat'] = '';
        }

        // Author filter
        $ids = array_filter(explode(",", $query_args['author']), 'is_numeric');
        // Got no valid IDs, clear
        if ( empty($ids) ) {
            $query_args['author'] = '';
        }

        // Has user set a title?
        if ( '' != $query_args['title'] ) {
            if ( ! $query_args['markup']['custom_html'] ) {
                $query_args['markup']['title-start'] = apply_filters('wpp_block_before_title', '<h2>');
                $query_args['markup']['title-end'] = apply_filters('wpp_block_after_title', '</h2>');
            }
        }

        // Return cached results
        if ( $this->admin_options['tools']['cache']['active'] ) {

            $key = md5(json_encode($query_args));
            $popular_posts = \WordPressPopularPosts\Cache::get($key);

            if ( false === $popular_posts ) {
                $popular_posts = new Query($query_args);

                $time_value = $this->admin_options['tools']['cache']['interval']['value']; // eg. 5
                $time_unit = $this->admin_options['tools']['cache']['interval']['time']; // eg. 'minute'

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

        } // Get popular posts
        else {
            $popular_posts = new Query($query_args);
        }

        $this->output->set_data($popular_posts->get_posts());
        $this->output->set_public_options($query_args);
        $this->output->build_output();

        $html .= $this->output->get_output();

        $html .= '</div>';

        return $html;
    }

    /**
     * Parses attributes.
     *
     * @since   5.4.0
     * @param   array
     * @return  array
     */
    private function parse_attributes($atts = [])
    {
        $out = array();

        foreach ( $this->defaults as $name => $default ) {
            $out[$name] = array_key_exists($name, $atts) ? trim($atts[$name]) : $default;
        }

        return $out;
    }
}