<?php
namespace WordPressPopularPosts\Block\Widget;

use WordPressPopularPosts\{ Helper, Image, Output, Themer, Translate };
use WordPressPopularPosts\Block\Block;
use WordPressPopularPosts\Traits\QueriesPosts;

class Widget extends Block
{

    use QueriesPosts;

    /**
     * Administrative settings.
     *
     * @since   5.4.0
     * @var     array
     * @access  private
     */
    private $config = [];

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
    public function __construct(array $config, Output $output, Image $thumbnail, Translate $translate, Themer $themer)
    {
        $this->config = $config;
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
     * Registers the block.
     *
     * @since   5.4.0
     */
    public function register()
    {
        // Block editor is not available, bail.
        if ( ! function_exists('register_block_type') ) {
            return;
        }

        $block_editor_support = apply_filters('wpp_block_editor_support', true);

        if ( ! $block_editor_support ) {
            return;
        }

        wp_register_script(
            'block-wpp-widget-js',
            plugin_dir_url(dirname(dirname(dirname(__FILE__)))) . 'assets/js/blocks/block-wpp-widget.js',
            ['wp-blocks', 'wp-i18n', 'wp-element', 'wp-block-editor', 'wp-server-side-render'],
            filemtime(plugin_dir_path(dirname(dirname(dirname(__FILE__)))) . 'assets/js/blocks/block-wpp-widget.js')
        );

        wp_localize_script(
            'block-wpp-widget-js',
            '_wordpress_popular_posts',
            [
                'can_show_rating' => function_exists('the_ratings_results')
            ]
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
                    '_isSelected' => [
                        'type' => 'boolean',
                        'default' => false
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
                    'rating' => [
                        'type' => 'boolean',
                        'default' => false
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

        $html = '<div class="widget popular-posts' . (( isset($attributes['className']) && $attributes['className'] ) ? ' ' . esc_attr($attributes['className']) : '') . '">';

        // possible values for "Time Range" and "Order by"
        $time_units = ['minute', 'hour', 'day', 'week', 'month'];
        $range_values = ['daily', 'last24hours', 'weekly', 'last7days', 'monthly', 'last30days', 'all', 'custom'];
        $order_by_values = ['comments', 'views', 'avg'];

        $theme_data = $this->themer->get_theme($theme);

        if ( ! isset($theme_data['json']) ) {
            $theme = '';
        }

        $query_args = [
            'title' => strip_tags($title), // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- We want the behavior of strip_tags
            'limit' => ( ! empty($limit) && Helper::is_number($limit) && $limit > 0 ) ? $limit : 10,
            'offset' => ( ! empty($offset) && Helper::is_number($offset) && $offset >= 0 ) ? $offset : 0,
            'range' => ( in_array($range, $range_values) ) ? $range : 'daily',
            'time_quantity' => ( ! empty($time_quantity) && Helper::is_number($time_quantity) && $time_quantity > 0 ) ? $time_quantity : 24,
            'time_unit' => ( in_array($time_unit, $time_units) ) ? $time_unit : 'hour',
            'freshness' => empty($freshness) ? false : $freshness,
            'order_by' => ( in_array($order_by, $order_by_values) ) ? $order_by : 'views',
            'post_type' => empty($post_type) ? 'post' : $post_type,
            'pid' => rtrim(preg_replace('|[^0-9,]|', '', $pid), ','),
            'taxonomy' => empty($tax) ? 'category' : $tax,
            'term_id' => rtrim(preg_replace('|[^0-9,;-]|', '', $term_id), ','),
            'author' => rtrim(preg_replace('|[^0-9,]|', '', $author), ','),
            'shorten_title' => [
                'active' => ( (bool) $attributes['shorten_title'] && ! empty($title_length) && Helper::is_number($title_length) && $title_length > 0 ),
                'length' => ( ! empty($title_length) && Helper::is_number($title_length) ) ? $title_length : 0,
                'words' => (( ! empty($title_by_words) && Helper::is_number($title_by_words) && $title_by_words > 0 )),
            ],
            'post-excerpt' => [
                'active' => ( (bool) $attributes['display_post_excerpt'] && ! empty($excerpt_length) && Helper::is_number($excerpt_length) && $excerpt_length > 0 ),
                'length' => ( ! empty($excerpt_length) && Helper::is_number($excerpt_length) ) ? $excerpt_length : 0,
                'keep_format' => ( ! empty($excerpt_format) && Helper::is_number($excerpt_format) && $excerpt_format > 0 ),
                'words' => ( ! empty($excerpt_by_words) && Helper::is_number($excerpt_by_words) && $excerpt_by_words > 0 ),
            ],
            'thumbnail' => [
                'active' => ( 'predefined' == $thumbnail_build && (bool) $attributes['display_post_thumbnail'] ) ? true : ( ! empty($thumbnail_width) && Helper::is_number($thumbnail_width) && $thumbnail_width > 0 ),
                'width' => ( ! empty($thumbnail_width) && Helper::is_number($thumbnail_width) && $thumbnail_width > 0 ) ? $thumbnail_width : 0,
                'height' => ( ! empty($thumbnail_height) && Helper::is_number($thumbnail_height) && $thumbnail_height > 0 ) ? $thumbnail_height : 0,
                'build' => 'predefined' == $thumbnail_build ? 'predefined' : 'manual',
                'size' => empty($thumbnail_size) ? '' : $thumbnail_size,
            ],
            'rating' => (bool) $attributes['rating'],
            'stats_tag' => [
                'comment_count' => (bool) $attributes['stats_comments'],
                'views' => (bool) $attributes['stats_views'],
                'author' => (bool) $attributes['stats_author'],
                'date' => [
                    'active' => (bool) $attributes['stats_date'],
                    'format' => empty($stats_date_format) ? 'F j, Y' : $stats_date_format
                ],
                'taxonomy' => [
                    'active' => (bool) $attributes['stats_taxonomy'],
                    'name' => empty($taxonomy) ? 'category' : $taxonomy,
                ]
            ],
            'markup' => [
                'custom_html' => (bool) $attributes['custom_html'],
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
        $ids = array_filter(explode(',', $query_args['pid']), 'is_numeric');
        // Got no valid IDs, clear
        if ( empty($ids) ) {
            $query_args['pid'] = '';
        }

        // Taxonomy filter
        $ids = array_filter(explode(',', $query_args['term_id']), 'is_numeric');
        // Got no valid term IDs, clear
        if ( empty($ids) ) {
            $query_args['term_id'] = '';
        }

        // Author filter
        $ids = array_filter(explode(',', $query_args['author']), 'is_numeric');
        // Got no valid IDs, clear
        if ( empty($ids) ) {
            $query_args['author'] = '';
        }

        // Has user set a title?
        if (
            ! empty($query_args['title'])
            && ! empty($query_args['markup']['title-start'])
            && ! empty($query_args['markup']['title-end'])
        ) {
            $html .= htmlspecialchars_decode($query_args['markup']['title-start'], ENT_QUOTES) . $query_args['title'] . htmlspecialchars_decode($query_args['markup']['title-end'], ENT_QUOTES);
            $html = Helper::sanitize_html($html, $query_args);
        }

        $isAdmin = isset($_GET['isSelected']) ? $_GET['isSelected'] : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- isSelected is a boolean from wp-admin

        if ( $this->config['tools']['ajax'] && ! is_customize_preview() && ! $isAdmin ) {
            $html .= '<script type="application/json">' . json_encode($query_args) . '</script>';
            $html .= '<div class="wpp-widget-block-placeholder"></div>';

            return $html . '</div>';
        }

        $popular_posts = $this->maybe_query($query_args);

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
    private function parse_attributes(array $atts = [])
    {
        $out = array();

        foreach ( $this->defaults as $name => $default ) {
            $out[$name] = array_key_exists($name, $atts) ? trim($atts[$name]) : $default;
        }

        return $out;
    }
}
