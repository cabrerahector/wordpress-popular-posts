<?php
/**
 * WordPress Popular Posts template tags for use in themes.
 */

/**
 * Template tag - gets views count.
 *
 * @link    https://github.com/cabrerahector/wordpress-popular-posts/wiki/2.-Template-tags#wpp_get_views
 * @since   2.0.3
 * @param   int             $id             The Post ID.
 * @param   string|array    $range          Either an string (eg. 'last7days') or -since 5.3- an array (eg. ['range' => 'custom', 'time_unit' => 'day', 'time_quantity' => 7])
 * @param   bool            $number_format  Whether to format the number (eg. 9,999) or not (eg. 9999)
 * @return  string
 */
function wpp_get_views($id = NULL, $range = NULL, $number_format = true)
{
    // have we got an id?
    if ( empty($id) || is_null($id) || ! is_numeric($id) )
        return "-1";

    $args = [
        'range' => 'all',
        '_postID' => $id
    ];

    if (
        is_array($range)
        && isset($range['range'])
        && isset($range['time_unit'])
        && isset($range['time_quantity'])
    ) {
        $args['range'] = $range['range'];
        $args['time_unit'] = $range['time_unit'];
        $args['time_quantity'] = $range['time_quantity'];
    } else {
        $range = is_string($range) ? trim($range) : null;
        $args['range'] = ! $range ? 'all' : $range;
    }

    add_filter('wpp_query_fields', 'wpp_get_views_fields', 10, 2);
    add_filter('wpp_query_where', 'wpp_get_views_where', 10, 2);
    add_filter('wpp_query_group_by', 'wpp_get_views_group_by', 10, 2);
    add_filter('wpp_query_order_by', 'wpp_get_views_order_by', 10, 2);
    add_filter('wpp_query_limit', 'wpp_get_views_limit', 10, 2);
    $query = new \WordPressPopularPosts\Query($args);
    remove_filter('wpp_query_fields', 'wpp_get_views_fields', 10);
    remove_filter('wpp_query_where', 'wpp_get_views_where', 10);
    remove_filter('wpp_query_group_by', 'wpp_get_views_group_by', 10);
    remove_filter('wpp_query_order_by', 'wpp_get_views_order_by', 10);
    remove_filter('wpp_query_limit', 'wpp_get_views_limit', 10);

    $results = $query->get_posts();

    if ( empty($results) )
        return 0;

    return $number_format ? number_format_i18n(intval($results[0]->pageviews)) : $results[0]->pageviews;
}

function wpp_get_views_fields($fields, $options)
{
    return 'IFNULL(v.pageviews, 0) AS pageviews';
}

function wpp_get_views_where($where, $options)
{
    global $wpdb;
    return $wpdb->prepare($where . ' AND p.ID = %d ', $options['_postID']);
}

function wpp_get_views_group_by($groupby, $options)
{
    return '';
}

function wpp_get_views_order_by($orderby, $options)
{
    return '';
}

function wpp_get_views_limit($limit, $options)
{
    return '';
}

/**
 * Template tag - gets popular posts.
 *
 * @link    https://github.com/cabrerahector/wordpress-popular-posts/wiki/2.-Template-tags#wpp_get_mostpopular
 * @since   2.0.3
 * @param   mixed   $args
 */
function wpp_get_mostpopular($args = NULL)
{
    $shortcode = '[wpp';

    if ( is_null($args) ) {
        $shortcode .= ']';
    } else {
        if ( is_array($args) ) {
            $atts = '';
            foreach( $args as $key => $arg ){
                if (
                    is_array($arg)
                    && ('post_type' == $key || 'cat' == $key || 'term_id' == $key || 'pid' == $key || 'author' == $key)
                ) {
                    $arg = array_filter($arg, 'is_int');
                    $arg = join(',', $arg);
                }

                $atts .= ' ' . $key . '="' . htmlspecialchars($arg, ENT_QUOTES, $encoding = ini_get("default_charset"), false) . '"';
            }
        } else {
            $atts = trim(str_replace("&", " ", $args));
        }

        $shortcode .= ' ' . $atts . ' php=true]';
    }

    echo do_shortcode($shortcode);
}
