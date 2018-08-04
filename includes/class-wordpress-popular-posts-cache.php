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
class WPP_Cache {

    /**
     * Retrieves cached data.
     *
     * @since    4.1.2
     * @access   public
     * @param    string               $key              The name of the cached data.
     * @return   mixed
     */
    public static function get( $key ) {
        return get_transient( $key );
    }

    /**
     * Retrieves cached data.
     *
     * @since    4.1.2
     * @access   public
     * @param    string               $key              The name of the cached data.
     * @param    mixed                $data             The data being stored.
     */
    public static function set( $key = null, $data = array(), $time_value = 1, $time_unit = 'minute' ) {

        if ( !$key )
            return false;

        if (
            !is_int( $time_value )
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
        set_transient( $key, $data, $expiration );

        // Store transient in WPP transients array for garbage collection
        $wpp_transients = get_option( 'wpp_transients' );

        if ( !$wpp_transients ) {
            $wpp_transients = array( $key );
            add_option( 'wpp_transients', $wpp_transients );
        } else {
            if ( !in_array($key, $wpp_transients) ) {
                $wpp_transients[] = $key;
                update_option( 'wpp_transients', $wpp_transients );
            }
        }

    }

}
