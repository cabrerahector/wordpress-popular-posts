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
    public static function is_number($number) /** @TODO: starting PHP 8.0 $number can be declared as mixed $number */
    {
        return ! empty($number) && is_numeric($number) && (intval($number) == floatval($number));
    }

    /**
     * Converts a number into a short version, eg: 1000 -> 1k
     *
     * @see     https://gist.github.com/RadGH/84edff0cc81e6326029c
     * @since   5.2.0
     * @param   int
     * @param   int
     * @return  mixed   string|bool
     */
    public static function prettify_number($number, $precision = 1) /** @TODO: starting PHP 8.0 $number can be declared as mixed $number */
    {
        if ( ! is_numeric($number) ) {
            return false;
        }

        if ( $number < 900 ) {
            // 0 - 900
            $n_format = number_format($number, $precision);
            $suffix = '';
        } elseif ( $number < 900000 ) {
            // 0.9k-850k
            $n_format = number_format($number / 1000, $precision);
            $suffix = 'k';
        } elseif ( $number < 900000000 ) {
            // 0.9m-850m
            $n_format = number_format($number / 1000000, $precision);
            $suffix = 'm';
        } elseif ( $number < 900000000000 ) {
            // 0.9b-850b
            $n_format = number_format($number / 1000000000, $precision);
            $suffix = 'b';
        } else {
            // 0.9t+
            $n_format = number_format($number / 1000000000000, $precision);
            $suffix = 't';
        }

        // Remove unnecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
        // Intentionally does not affect partials, eg "1.50" -> "1.50"
        if ( $precision > 0 ) {
            $dotzero = '.' . str_repeat('0', $precision);
            $n_format = str_replace($dotzero, '', $n_format);
        }

        return $n_format . $suffix;
    }

    /**
     * Checks for valid date.
     *
     * @since   4.0.0
     * @param   string   $date
     * @param   string   $format
     * @return  bool
     */
    public static function is_valid_date(?string $date, $format = 'Y-m-d')
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
    public static function get_date_range(string $start_date, string $end_date, string $format = 'Y-m-d')
    {
        if (
            self::is_valid_date($start_date, $format)
            && self::is_valid_date($end_date, $format)
        ) {
            $dates = [];

            $begin = new \DateTime($start_date, wp_timezone());
            $end = new \DateTime($end_date, wp_timezone());

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
        return current_datetime()->format('Y-m-d');
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
        return current_datetime()->format('Y-m-d H:i:s');
    }

    /**
     * Returns current timestamp.
     *
     * @since   5.0.2
     * @return  string
     */
    public static function timestamp()
    {
        return current_datetime()->getTimestamp();
    }

    /**
     * Checks whether a string is a valid timestamp.
     *
     * @since   5.2.0
     * @param   string  $string
     * @return  bool
     */
    public static function is_timestamp($string) /** @TODO: starting PHP 8.0 $string can be declared as mixed $string */
    {
        if (
            ( is_int($string) || ctype_digit($string) ) 
            && strtotime(date('Y-m-d H:i:s', $string)) === (int) $string
        ) {
            return true;
        }

        return false;
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
     * Truncates text.
     *
     * @since   4.0.0
     * @param   string   $text
     * @param   int      $length
     * @param   bool     $truncate_by_words
     * @return  string
     */
    public static function truncate(string $text = '', int $length = 25, bool $truncate_by_words = false, string $more = '...')
    {
        if ( '' !== $text ) {
            $charset = get_bloginfo('charset');

            // Truncate by words
            if ( $truncate_by_words ) {
                $words = explode(' ', $text, $length + 1);

                if ( count($words) > $length ) {
                    array_pop($words);
                    $text = rtrim(implode(' ', $words), ',.') . $more;
                }
            }
            // Truncate by characters
            elseif ( mb_strlen($text, $charset) > $length ) {
                $text = rtrim(mb_substr($text, 0, $length, $charset), ' ,.') . $more;
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
            && ! is_front_page() 
            && ! is_preview() 
            && ! is_trackback() 
            && ! is_feed() 
            && ! is_robots() 
            && ! is_customize_preview()
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
    public static function add_scheme(?string $url, string $scheme = 'https://')
    {
        $url_args = parse_url($url);

        if ( $url_args ) {
            // No need to do anything, URL is fine
            if ( isset($url_args['scheme']) ) {
                return $url;
            }
            // Return URL with scheme
            return $scheme . $url_args['host'] . $url_args['path'];
        }

        // Invalid/malformed URL
        return false;
    }

    /**
     * Checks whether an URL points to an actual image.
     *
     * This function used to live in src/Image, moved it here
     * on version 5.4.0 to use it where needed.
     *
     * @since   5.0.0
     * @access  private
     * @param   string
     * @return  array|bool
     */
    public static function is_image_url(string $url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        $encoded_path = array_map('urlencode', explode('/', $path));
        $parse_url = str_replace($path, implode('/', $encoded_path), $url);

        if ( ! filter_var($parse_url, FILTER_VALIDATE_URL) ) {
            return false;
        }

        // Check extension
        $file_name = basename($path);
        $file_name = sanitize_file_name($file_name);
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if ( ! in_array($ext, $allowed_ext) ) {
            return false;
        }

        // sanitize URL, just in case
        $image_url = esc_url($url);
        // remove querystring
        preg_match('/[^\?]+\.(jpg|jpeg|gif|png|webp)/i', $image_url, $matches);

        return ( is_array($matches) && ! empty($matches) ) ? $matches : false;
    }

    /**
     * Sanitizes HTML output.
     *
     * @since   6.3.3
     * @param   string  $html
     * @param   array   $options  Public options
     * @return  string  $html     The (sanitized) HTML code
     */
    public static function sanitize_html(string $html, array $options)
    {
        $allowed_tags = wp_kses_allowed_html('post');

        if ( isset($allowed_tags['form']) ) {
            unset($allowed_tags['form']);
        }

        if (
            isset($options['theme']['name'])
            && $options['theme']['name']
        ) {
            $allowed_tags['style'] = [
                'id' => 1,
                'nonce' => 1
            ];
        }

        $allowed_tags['img']['decoding'] = true;
        $allowed_tags['img']['srcset'] = true;

        return wp_kses($html, $allowed_tags);
    }
}
