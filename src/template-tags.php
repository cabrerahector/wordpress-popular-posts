<?php
/**
 * WP Popular Posts template tags for use in themes.
 */

/**
 * Template tag - gets views count.
 *
 * @link    https://github.com/cabrerahector/wordpress-popular-posts/wiki/2.-Template-tags#wpp_get_views
 * @since   2.0.3
 * @param   int             $id             The Post ID.
 * @param   string|array    $range          Either an string (eg. 'last7days') or -since 5.3- an array (eg. ['range' => 'custom', 'time_unit' => 'day', 'time_quantity' => 7])
 * @param   bool|string     $number_format  Whether to format the number (eg. 9,999) or not (eg. 9999)
 * @param   bool            $cache          Whether to cache the views data to improve performance
 * @return  string
 */
function wpp_get_views(?int $id = null, $range = null, $number_format = true, $cache = false) /** @TODO: starting PHP 8.0 $range can be declared as mixed $range, $number_format as mixed or bool|string */
{
    // have we got an id?
    if ( empty($id) || is_null($id) || ! is_numeric($id) ) {
        return '-1';
    }

    $results = null;

    $id = absint($id);

    global $wpdb;
    $table_name = $wpdb->prefix . 'popularposts';
    $translate = new \WordPressPopularPosts\Translate();

    $id = $translate->get_object_id(
        $id,
        get_post_type($id),
        true,
        $translate->get_default_language()
    );

    $args = [
        'range' => 'all',
        'time_unit' => 'hour',
        'time_quantity' => 24,
        '_postID' => $id
    ];

    if (
        is_array($range)
    ) {
        $args = \WordPressPopularPosts\Helper::merge_array_r($args, $range);
    } else {
        $range = is_string($range) ? trim($range) : null;
        $args['range'] = ! $range ? 'all' : $range;
    }

    $args['range'] = strtolower($args['range']);

    $key = 'wpp_views_' . $id . '_' . md5(json_encode($args));

    if ( $cache ) {
        $results = \WordPressPopularPosts\Cache::get($key);
    }

    if ( ! $results ) {
        // Get all-time views count
        if ( 'all' == $args['range'] ) {
            $data_table = "{$table_name}data";

            //phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $query = $wpdb->prepare('SELECT pageviews FROM %i WHERE postid = %d;',
                $data_table,
                $args['_postID']
            );
            //phpcs:enable
        } // Get views count within time range
        else {
            list($dates, $datetimes, $include_timestamps) = \WordPressPopularPosts\Helper::get_dates(
                $args['range'],
                $args['time_unit'],
                $args['time_quantity']
            );

            list($start_date, $end_date) = $dates;
            list($start_datetime, $end_datetime) = $datetimes;

            if ( $include_timestamps ) {
                $views_time_range = "(view_datetime BETWEEN '{$start_datetime}' AND '{$end_datetime}')";
            } else {
                $views_time_range = "(view_date BETWEEN '{$start_date}' AND '{$end_date}')";
            }

            $summary_table = "{$wpdb->prefix}popularpostssummary";

            //phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- $views_time_range is safe to use
            $query = $wpdb->prepare(
                "SELECT SUM(pageviews) AS pageviews FROM %i WHERE {$views_time_range} AND postid = %d;",
                $summary_table,
                $args['_postID']
            );
            //phpcs:enable
        }

        $results = $wpdb->get_var($query); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- We already prepared $query above
    }

    if ( ! $results ) {
        return 0;
    }

    if ( $cache ) {
        \WordPressPopularPosts\Cache::set($key, $results);
    }

    if ( $number_format ) {
        $results = intval($results);

        if ( 'prettify' === $number_format ) {
            return \WordPressPopularPosts\Helper::prettify_number($results);
        }

        return number_format_i18n($results);
    }

    return $results;
}

/**
 * Template tag - gets popular posts.
 *
 * @link    https://github.com/cabrerahector/wordpress-popular-posts/wiki/2.-Template-tags#wpp_get_mostpopular
 * @since   2.0.3
 * @param   mixed   $args
 */
function wpp_get_mostpopular($args = null) /** @TODO: starting PHP 8.0 $args can be declared as mixed $args */
{
    $shortcode = '[wpp';

    if ( is_null($args) ) {
        $shortcode .= ']';
    } else {
        if ( is_array($args) ) {
            $atts = '';
            foreach( $args as $key => $arg ) {
                if (
                    is_array($arg)
                    && ('post_type' == $key || 'cat' == $key || 'term_id' == $key || 'pid' == $key || 'exclude' == $key || 'author' == $key)
                ) {
                    $arg = array_filter($arg, 'is_int');
                    $arg = join(',', $arg);
                }

                $arg = (null !== $arg) ? trim($arg) : '';

                if ( is_numeric($arg) ) {
                    $atts .= ' ' . $key . '=' . $arg . '';
                } else {
                    $atts .= ' ' . $key . '="' . htmlspecialchars($arg, ENT_QUOTES, ini_get('default_charset'), false) . '"';
                }
            }
        } else {
            $atts = trim(str_replace('&', ' ', $args));
        }

        $shortcode .= ' ' . $atts . ' php=true]';
    }

    echo do_shortcode($shortcode);
}

/**
 * Returns an array of popular posts IDs, or an empty array if
 * nothing is found.
 *
 * eg. $popular_post_ids = wpp_get_ids(['range' => 'last24hours', 'limit' => 5]);
 *
 * @since  7.3.0
 * @link https://github.com/cabrerahector/wordpress-popular-posts/wiki/2.-Template-tags#parameters
 * @param  array  $args  Popular Posts parameters
 * @return array
 */
function wpp_get_ids(array $args) {
    $ids = [];

    $wpp_query = new \WordPressPopularPosts\Query($args);
    $popular_posts_arr = $wpp_query->get_posts();

    if ( $popular_posts_arr ) {
        $ids = wp_list_pluck($popular_posts_arr, 'id');
    }

    return $ids;
}
