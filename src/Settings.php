<?php
/**
 * Set / get plugin default options.
 *
 * @link       http://cabrerahector.com
 * @since      4.0.0
 *
 * @package    WordPressPopularPosts
 */

/**
 * Set / get plugin default options.
 *
 * @package    WordPressPopularPosts
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts;

class Settings {

    /**
     * Plugin uploads directory.
     *
     * @since   3.0.4
     * @var     array
     */
    private static $defaults = [
        'widget_options' => [
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
            'author' => '',
            'cat' => '',
            'taxonomy' => 'category',
            'term_id' => '',
            'shorten_title' => [
                'active' => false,
                'length' => 25,
                'words'	=> false
            ],
            'post-excerpt' => [
                'active' => false,
                'length' => 55,
                'keep_format' => false,
                'words' => false
            ],
            'thumbnail' => [
                'active' => false,
                'build' => 'manual',
                'width' => 75,
                'height' => 75,
                'crop' => true
            ],
            'rating' => false,
            'stats_tag' => [
                'comment_count' => false,
                'views' => true,
                'author' => false,
                'date' => [
                    'active' => false,
                    'format' => 'F j, Y'
                ],
                'category' => false,
                'taxonomy' => [
                    'active' => false,
                    'name' => 'category'
                ]
            ],
            'markup' => [
                'custom_html' => false,
                'title-start' => '<h2>',
                'title-end' => '</h2>',
                'wpp-start' => '<ul class="wpp-list">',
                'wpp-end' => '</ul>',
                'post-html' => '<li>{thumb} {title} <span class="wpp-meta post-stats">{stats}</span></li>'
            ],
            'theme' => [
                'name' => '',
                'applied' => false
            ]
        ],
        'admin_options' => [
            'stats' => [
                'range' => 'last7days',
                'time_unit' => 'hour',
                'time_quantity' => 24,
                'order_by' => 'views',
                'limit' => 10,
                'post_type' => 'post,page',
                'freshness' => false
            ],
            'tools' => [
                'experimental' => false,
                'ajax' => true,
                'css' => true,
                'link' => [
                    'target' => '_self'
                ],
                'thumbnail' => [
                    'source' => 'featured',
                    'field' => '',
                    'resize' => false,
                    'default' => '',
                    'lazyload' => true
                ],
                'log' => [
                    'level' => 1,
                    'limit' => 0,
                    'expires_after' => 180
                ],
                'cache' => [
                    'active' => true,
                    'interval' => [
                        'time' => 'minute',
                        'value' => 1
                    ]
                ],
                'sampling' => [
                    'active' => false,
                    'rate' => 100
                ]
            ]
        ]
    ];

    /**
     * Returns plugin options.
     *
     * @since    4.0.0
     * @access   public
     * @param    string   $option_set
     * @return   array
     */
    public static function get($option_set = null)
    {
        $options = self::$defaults;

        if ( 'widget_options' == $option_set ) {
            return $options['widget_options'];
        }

        if ( ! $admin_options = get_option('wpp_settings_config') ) {
            $admin_options = $options['admin_options'];
            add_option('wpp_settings_config', $admin_options);
        }
        else {
            $options['admin_options'] = Helper::merge_array_r(
                $options['admin_options'],
                (array) $admin_options
            );
        }

        if ( 'admin_options' == $option_set ) {
            return $options['admin_options'];
        }

        return $options;
    }
}
