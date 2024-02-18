<?php
namespace WordPressPopularPosts\Shortcode;

use WordPressPopularPosts\{ Helper, Output };
use WordPressPopularPosts\Shortcode\Shortcode;
use WordPressPopularPosts\Traits\QueriesPosts;

class Posts extends Shortcode {

    use QueriesPosts;

    /**
     * Admin settings.
     *
     * @since   6.3.0
     * @var     array
     */
    private $config = [];

    /**
     * Output object.
     *
     * @since  6.3.0
     * @var     \WordPressPopularPosts\Output       $output
     * @access  private
     */
    private $output;

    /**
     * Construct.
     *
     * @param   array                               $admin_options
     * @param   \WordPressPopularPosts\Output       $output         Output class.
     */
    public function __construct(array $admin_options, Output $output)
    {
        $this->config = $admin_options;
        $this->output = $output;
        $this->tag = 'wpp';
    }

    /**
     * Handles the HTML output of the shortcode.
     *
     * @since  6.3.0
     * @param  mixed  $attributes  Array of attributes passed to the shortcode, or an empty string if nothing is passed
     * @return string              Views count
     */
    public function handle($attributes = []) : string
    {
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
         * @var string $thumbnail_build
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
            'thumbnail_build' => 'manual',
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
            'ajaxify' => 1
        ], $attributes, 'wpp'));

        // possible values for "Time Range" and "Order by"
        $time_units = ['minute', 'hour', 'day', 'week', 'month'];
        $range_values = ['daily', 'last24hours', 'weekly', 'last7days', 'monthly', 'last30days', 'all', 'custom'];
        $order_by_values = ['comments', 'views', 'avg'];

        $shortcode_ops = [
            'title' => strip_tags($header), // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- We want the behavior of strip_tags
            'limit' => ( ! empty($limit ) && Helper::is_number($limit) && $limit > 0 ) ? $limit : 10,
            'offset' => ( ! empty($offset) && Helper::is_number($offset) && $offset >= 0 ) ? $offset : 0,
            'range' => ( in_array($range, $range_values) ) ? $range : 'daily',
            'time_quantity' => ( ! empty($time_quantity ) && Helper::is_number($time_quantity) && $time_quantity > 0 ) ? $time_quantity : 24,
            'time_unit' => ( in_array($time_unit, $time_units) ) ? $time_unit : 'hour',
            'freshness' => empty($freshness) ? false : $freshness,
            'order_by' => ( in_array($order_by, $order_by_values) ) ? $order_by : 'views',
            'post_type' => empty($post_type) ? 'post' : $post_type,
            'pid' => rtrim(preg_replace('|[^0-9,]|', '', $pid), ','),
            'cat' => rtrim(preg_replace('|[^0-9,-]|', '', $cat), ','),
            'taxonomy' => empty($taxonomy) ? 'category' : $taxonomy,
            'term_id' => rtrim(preg_replace('|[^0-9,;-]|', '', $term_id), ','),
            'author' => rtrim(preg_replace('|[^0-9,]|', '', $author), ','),
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
                'build' => 'predefined' === $thumbnail_build ? 'predefined' : 'manual',
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
        $ids = array_filter(explode(',', $shortcode_ops['pid']), 'is_numeric');
        // Got no valid IDs, clear
        if ( empty($ids) ) {
            $shortcode_ops['pid'] = '';
        }

        // Category filter
        $ids = array_filter(explode(',', $shortcode_ops['cat']), 'is_numeric');
        // Got no valid IDs, clear
        if ( empty($ids) ) {
            $shortcode_ops['cat'] = '';
        }

        // Author filter
        $ids = array_filter(explode( ',', $shortcode_ops['author']), 'is_numeric');
        // Got no valid IDs, clear
        if ( empty($ids) ) {
            $shortcode_ops['author'] = '';
        }

        $shortcode_content = '';
        $cached = false;

        // is there a title defined by user?
        if (
            ! empty($header)
            && ! empty($header_start)
            && ! empty($header_end)
        ) {
            $shortcode_content .= htmlspecialchars_decode($header_start, ENT_QUOTES) . $header . htmlspecialchars_decode($header_end, ENT_QUOTES);
            $shortcode_content = Helper::sanitize_html($shortcode_content, $shortcode_ops);
        }

        $isAdmin = isset($_GET['isSelected']) ? $_GET['isSelected'] : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- isSelected is a boolean from wp-admin

        $load_via_ajax = $this->config['tools']['ajax'];

        if ( isset($attributes['ajaxify']) && is_numeric($attributes['ajaxify']) ) {
            $load_via_ajax = (bool) absint($attributes['ajaxify']);
        }

        if ( $load_via_ajax && ! is_customize_preview() && ! $isAdmin ) {
            $shortcode_content .= '<div class="wpp-shortcode">';
            $shortcode_content .= '<script type="application/json">' . wp_json_encode($shortcode_ops) . '</script>';
            $shortcode_content .= '<div class="wpp-shortcode-placeholder"></div>';
            $shortcode_content .= '</div>';
        } else {
            $popular_posts = $this->maybe_query($shortcode_ops);

            $this->output->set_data($popular_posts->get_posts());
            $this->output->set_public_options($shortcode_ops);
            $this->output->build_output();

            $shortcode_content .= $this->output->get_output();
        }

        return $shortcode_content;
    }
}
