<?php

/**
 * Set / get plugin default options.
 *
 * @link       http://cabrerahector.com
 * @since      4.0.0
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/includes
 */

/**
 * Set / get plugin default options.
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/includes
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

class WPP_Settings {

    /**
     * Plugin uploads directory.
     *
     * @since   3.0.4
     * @var     array
     */
    static public $defaults = array(
        'widget_options' => array(
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
            'shorten_title' => array(
                'active' => false,
                'length' => 25,
                'words'	=> false
            ),
            'post-excerpt' => array(
                'active' => false,
                'length' => 55,
                'keep_format' => false,
                'words' => false
            ),
            'thumbnail' => array(
                'active' => false,
                'build' => 'manual',
                'width' => 75,
                'height' => 75,
                'crop' => true
            ),
            'rating' => false,
            'stats_tag' => array(
                'comment_count' => false,
                'views' => true,
                'author' => false,
                'date' => array(
                    'active' => false,
                    'format' => 'F j, Y'
                ),
                'category' => false,
                'taxonomy' => array(
                    'active' => false,
                    'name' => 'category'
                )
            ),
            'markup' => array(
                'custom_html' => false,
                'title-start' => '<h2>',
                'title-end' => '</h2>',
                'wpp-start' => '<ul class="wpp-list">',
                'wpp-end' => '</ul>',
                'post-html' => '<li>{thumb} {title} <span class="wpp-meta post-stats">{stats}</span></li>'

            )
        ),
        'admin_options' => array(
            'stats' => array(
                'range' => 'last7days',
                'time_unit' => 'hour',
                'time_quantity' => 24,
                'order_by' => 'views',
                'limit' => 10,
                'post_type' => 'post,page',
                'freshness' => false
            ),
            'tools' => array(
                'ajax' => false,
                'css' => true,
                'link' => array(
                    'target' => '_self'
                ),
                'thumbnail' => array(
                    'source' => 'featured',
                    'field' => '',
                    'resize' => false,
                    'default' => ''
                ),
                'log' => array(
                    'level' => 1,
                    'limit' => 0,
                    'expires_after' => 180
                ),
                'cache' => array(
                    'active' => true,
                    'interval' => array(
                        'time' => 'minute',
                        'value' => 1
                    )
                ),
                'sampling' => array(
                    'active' => false,
                    'rate' => 100
                )
            )
        )
    );

    /**
     * Returns plugin options.
     *
     * @since    4.0.0
     * @access   public
     * @param    string   $option_set
     * @return   array
     */
    public static function get( $option_set = null ){

        $options = self::$defaults;

        if ( 'widget_options' == $option_set ) {
            return $options[ 'widget_options' ];
        }

        if ( !$admin_options = get_option( 'wpp_settings_config' ) ) {
            $admin_options = $options[ 'admin_options' ];
            add_option( 'wpp_settings_config', $admin_options );
        }
        else {
            $options[ 'admin_options' ] = WPP_Helper::merge_array_r(
                $options[ 'admin_options' ],
                (array) $admin_options
            );
        }

        if ( 'admin_options' == $option_set ) {
            return $options[ 'admin_options' ];
        }

        return $options;

    }

} // End WPP_Settings class
