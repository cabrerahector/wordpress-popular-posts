<?php

/**
 * Helper class to store data in cache for a fixed amount of time.
 *
 * @link       https://cabrerahector.com
 * @since      4.1.2
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/includes
 */

/**
 * Helper class to store data in cache for a fixed amount of time.
 *
 * Stores data in cache via WordPress Transients (or any other available
 * method in the future) for a fixed amount of time to reduce the number
 * of database calls.
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/includes
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts;

class Cache {

    /**
     * Retrieves cached data.
     *
     * @since    4.1.2
     * @access   public
     * @param    string               $key              The name of the cached data.
     * @return   mixed
     */
    public static function get(string $key)
    {
        return get_transient($key);
    }

    /**
     * Retrieves cached data.
     *
     * @since    4.1.2
     * @access   public
     * @param    string               $key              The name of the cached data.
     * @param    mixed                $data             The data being stored.
     */
    public static function set(string $key = null, $data = [], int $time_value = 1, string $time_unit = 'minute') /** @TODO: starting PHP 8.0 $data can be declared as mixed $data */
    {
        if ( ! $key ) {
            return false;
        }

        if (
            false === filter_var($time_value, FILTER_VALIDATE_INT)
            || $time_value <= 0
        ) {
            $time_value = 1;
        }

        switch( $time_unit ){
            case 'minute':
                $time = 60;
                break;
            case 'hour':
                $time = 60 * 60;
                break;
            case 'day':
                $time = 60 * 60 * 24;
                break;
            case 'week':
                $time = 60 * 60 * 24 * 7;
                break;
            case 'month':
                $time = 60 * 60 * 24 * 30;
                break;
            case 'year':
                $time = 60 * 60 * 24 * 365;
                break;
            default:
                $time = 60;
                break;
        }

        $expiration = $time * $time_value;

        // Store transient
        set_transient($key, $data, $expiration);

        // Store transient keys in WPP's transients table for garbage collection
        global $wpdb;

        $now = Helper::now();

        //phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}popularpoststransients (tkey, tkey_date) VALUES (%s, %s) ON DUPLICATE KEY UPDATE tkey_date = %s;",
                [
                    $key,
                    $now,
                    $now
                ]
            )
        );
        //phpcs:disable
    }
}
