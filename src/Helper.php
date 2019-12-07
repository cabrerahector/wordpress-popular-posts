<?php

namespace WordPressPopularPosts;

class Helper {

    /**
     * Checks for valid number.
     *
     * @since   2.1.6
     * @param   int     number
     * @return  bool
     */
    public static function is_number($number)
    {
        return !empty($number) && is_numeric($number) && (intval($number) == floatval($number));
    }

    /**
     * Checks for valid date.
     *
     * @since   4.0.0
     * @param   string   $date
     * @param   string   $format
     * @return  bool
     */
    public static function is_valid_date($date = null, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Returns an array of dates between two dates.
     *
     * @since   4.0.0
     * @param   string   $start_date
     * @param   string   $end_date
     * @param   string   $format
     * @return  array|bool
     */
    public static function get_date_range($start_date = null, $end_date = null, $format = 'Y-m-d')
    {
        if (
            self::is_valid_date($start_date, $format)
            && self::is_valid_date($end_date, $format)
        ) {
            $dates = [];

            $begin = new \DateTime($start_date, new \DateTimeZone(Helper::get_timezone()));
            $end = new \DateTime($end_date, new \DateTimeZone(Helper::get_timezone()));

            if ( $begin < $end ) {
                while( $begin <= $end ) {
                    $dates[] = $begin->format($format);
                    $begin->modify('+1 day');
                }
            }
            else {
                while( $begin >= $end ) {
                    $dates[] = $begin->format($format);
                    $begin->modify('-1 day');
                }
            }

            return $dates;
        }

        return false;
    }

    /**
     * Returns server date.
     *
     * @since    2.1.6
     * @access   private
     * @return   string
     */
    public static function curdate()
    {
        return current_time('Y-m-d', false);
    }

    /**
     * Returns mysql datetime.
     *
     * @since    2.1.6
     * @access   private
     * @return   string
     */
    public static function now()
    {
        return current_time('mysql');
    }

    /**
     * Returns current timestamp.
     *
     * @since   5.0.2
     * @return  string
     */
    public static function timestamp()
    {
        // current_datetime() is WP 5.3+
        return ( function_exists('current_datetime') ) ? current_datetime()->getTimestamp() : current_time('timestamp');
    }

    /**
     * Returns site's timezone.
     *
     * Code borrowed from Rarst's awesome WpDateTime class: https://github.com/Rarst/wpdatetime
     *
     * @since   5.0.0
     * @return  string
     */
    public static function get_timezone()
    {
        $timezone_string = get_option('timezone_string');

        if ( ! empty($timezone_string) ) {
            return $timezone_string;
        }

        $offset = get_option('gmt_offset');
        $sign = $offset < 0 ? '-' : '+';
        $hours = (int) $offset;
        $minutes = abs(($offset - (int) $offset) * 60);
        $offset = sprintf('%s%02d:%02d', $sign, abs($hours), $minutes);

        return $offset;
    }

    /**
     * Returns time.
     *
     * @since   2.3.0
     * @return  string
     */
    public static function microtime_float()
    {
        list($msec, $sec) = explode(' ', microtime());
        return (float) $msec + (float) $sec;
    }

    /**
     * Merges two associative arrays recursively.
     *
     * @since   2.3.4
     * @link    http://www.php.net/manual/en/function.array-merge-recursive.php#92195
     * @param   array   array1
     * @param   array   array2
     * @return  array
     */
    public static function merge_array_r(array $array1, array $array2)
    {
        $merged = $array1;

        foreach( $array2 as $key => &$value ) {
            if ( is_array($value) && isset($merged[$key]) && is_array($merged[$key]) ) {
                $merged[$key] = self::merge_array_r($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Debug function.
     *
     * @since   3.0.0
     * @param   mixed $v variable to display with var_dump()
     * @param   mixed $v,... unlimited optional number of variables to display with var_dump()
     */
    public static function debug($v)
    {
        if ( !defined('WPP_DEBUG') || !WPP_DEBUG )
            return;

        foreach( func_get_args() as $arg ) {
            print "<pre>";
            var_dump($arg);
            print "</pre>";
        }
    }

    /**
     * Truncates text.
     *
     * @since   4.0.0
     * @param   string   $text
     * @param   int      $length
     * @param   bool     $truncate_by_words
     * @return  string
     */
    public static function truncate($text = '', $length = 25, $truncate_by_words = false, $more = '...')
    {
        if ( '' !== $text ) {
            // Truncate by words
            if ( $truncate_by_words ) {
                $words = explode(" ", $text, $length + 1);

                if ( count($words) > $length ) {
                    array_pop($words);
                    $text = rtrim(implode(" ", $words), ",.") . " {$more}";
                }
            }
            // Truncate by characters
            elseif ( strlen($text) > $length ) {
                $text = rtrim(mb_substr($text, 0, $length , get_bloginfo('charset')), " ,.") . $more;
            }
        }

        return $text;
    }

    /**
     * Gets post/page ID if current page is singular
     *
     * @since   3.1.2
     */
    public static function is_single()
    {
        $trackable = [];
        $registered_post_types = get_post_types(['public' => true], 'names');

        foreach( $registered_post_types as $post_type ) {
            $trackable[] = $post_type;
        }

        $trackable = apply_filters('wpp_trackable_post_types', $trackable);

        if (
            is_singular($trackable) 
            && !is_front_page() 
            && !is_preview() 
            && !is_trackback() 
            && !is_feed() 
            && !is_robots() 
            && !is_customize_preview()
        ) {
            return get_queried_object_id();
        }

        return false;
    }

    /**
     * Adds scheme to a given URL.
     *
     * @since   5.0.0
     * @param   string      $url
     * @param   string      $scheme
     * @return  string|bool
     */
    static function add_scheme($url = null, $scheme = 'https://')
    {
        $url_args = parse_url($url);

        if ( $url_args ) {
            // No need to do anything, URL is fine
            if ( isset($url_args['scheme']) )
                return $url;
            // Return URL with scheme
            return $scheme . $url_args['host'] . $url_args['path'];
        }

        // Invalid/malformed URL
        return false;
    }
}
