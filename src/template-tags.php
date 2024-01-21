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
 * @param   bool|string     $number_format  Whether to format the number (eg. 9,999) or not (eg. 9999)
 * @return  string
 */
function wpp_get_views(int $id = null, $range = null, $number_format = true) /** @TODO: starting PHP 8.0 $range can be declared as mixed $range, $number_format as mixed or bool|string */
{
    // have we got an id?
    if ( empty($id) || is_null($id) || ! is_numeric($id) ) {
        return '-1';
    }

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

    // Get all-time views count
    if ( 'all' == $args['range'] ) {
        //phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table_name is safe to use
        $query = $wpdb->prepare( "SELECT pageviews FROM {$table_name}data WHERE postid = %d;",
            $args['_postID']
        );
        //phpcs:enable
    } // Get views count within time range
    else {
        $start_date = new \DateTime(
            \WordPressPopularPosts\Helper::now(),
            wp_timezone()
        );

        // Determine time range
        switch( $args['range'] ){
            case 'last24hours':
            case 'daily':
                $start_date = $start_date->sub(new \DateInterval('P1D'));
                $start_datetime = $start_date->format('Y-m-d H:i:s');
                $views_time_range = "view_datetime >= '{$start_datetime}'";
                break;
            case 'last7days':
            case 'weekly':
                $start_date = $start_date->sub(new \DateInterval('P6D'));
                $start_datetime = $start_date->format('Y-m-d');
                $views_time_range = "view_date >= '{$start_datetime}'";
                break;
            case 'last30days':
            case 'monthly':
                $start_date = $start_date->sub(new \DateInterval('P29D'));
                $start_datetime = $start_date->format('Y-m-d');
                $views_time_range = "view_date >= '{$start_datetime}'";
                break;
            case 'custom':
                $time_units = ['MINUTE', 'HOUR', 'DAY', 'WEEK', 'MONTH'];

                // Valid time unit
                if (
                    isset($args['time_unit'])
                    && in_array(strtoupper($args['time_unit']), $time_units)
                    && isset($args['time_quantity'])
                    && filter_var($args['time_quantity'], FILTER_VALIDATE_INT)
                    && $args['time_quantity'] > 0
                ) {
                    $time_quantity = $args['time_quantity'];
                    $time_unit = strtoupper($args['time_unit']);

                    if ( 'MINUTE' == $time_unit ) {
                        $start_date = $start_date->sub(new \DateInterval('PT' . (60 * $time_quantity) . 'S'));
                        $start_datetime = $start_date->format('Y-m-d H:i:s');
                        $views_time_range = "view_datetime >= '{$start_datetime}'";
                    } elseif ( 'HOUR' == $time_unit ) {
                        $start_date = $start_date->sub(new \DateInterval('PT' . ((60 * $time_quantity) - 1) . 'M59S'));
                        $start_datetime = $start_date->format('Y-m-d H:i:s');
                        $views_time_range = "view_datetime >= '{$start_datetime}'";
                    } elseif ( 'DAY' == $time_unit ) {
                        $start_date = $start_date->sub(new \DateInterval('P' . ($time_quantity - 1) . 'D'));
                        $start_datetime = $start_date->format('Y-m-d');
                        $views_time_range = "view_date >= '{$start_datetime}'";
                    } elseif ( 'WEEK' == $time_unit ) {
                        $start_date = $start_date->sub(new \DateInterval('P' . ((7 * $time_quantity) - 1) . 'D'));
                        $start_datetime = $start_date->format('Y-m-d');
                        $views_time_range = "view_date >= '{$start_datetime}'";
                    } else {
                        $start_date = $start_date->sub(new \DateInterval('P' . ((30 * $time_quantity) - 1) . 'D'));
                        $start_datetime = $start_date->format('Y-m-d');
                        $views_time_range = "view_date >= '{$start_datetime}'";
                    }
                } // Invalid time unit, default to last 24 hours
                else {
                    $start_date = $start_date->sub(new \DateInterval('P1D'));
                    $start_datetime = $start_date->format('Y-m-d H:i:s');
                    $views_time_range = "view_datetime >= '{$start_datetime}'";
                }

                break;
            default:
                $start_date = $start_date->sub(new \DateInterval('P1D'));
                $start_datetime = $start_date->format('Y-m-d H:i:s');
                $views_time_range = "view_datetime >= '{$start_datetime}'";
                break;
        }

        //phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $views_time_range is safe to use
        $query = $wpdb->prepare(
            "SELECT SUM(pageviews) AS pageviews FROM `{$wpdb->prefix}popularpostssummary` WHERE {$views_time_range} AND postid = %d;",
            $args['_postID']
        );
        //phpcs:enable
    }

    $results = $wpdb->get_var($query); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- We already prepared $query above

    if ( ! $results ) {
        return 0;
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
            foreach( $args as $key => $arg ){
                if (
                    is_array($arg)
                    && ('post_type' == $key || 'cat' == $key || 'term_id' == $key || 'pid' == $key || 'author' == $key)
                ) {
                    $arg = array_filter($arg, 'is_int');
                    $arg = join(',', $arg);
                }

                $atts .= ' ' . $key . '="' . htmlspecialchars($arg, ENT_QUOTES, $encoding = ini_get('default_charset'), false) . '"';
            }
        } else {
            $atts = trim(str_replace('&', ' ', $args));
        }

        $shortcode .= ' ' . $atts . ' php=true]';
    }

    echo do_shortcode($shortcode);
}
