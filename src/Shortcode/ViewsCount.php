<?php
namespace WordPressPopularPosts\Shortcode;

use WordPressPopularPosts\Helper;
use WordPressPopularPosts\Shortcode\Shortcode;

class ViewsCount extends Shortcode {

    /**
     * Construct.
     */
    public function __construct()
    {
        $this->tag = 'wpp_views_count';
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
        $views = '';

        if ( function_exists('wpp_get_views') ) {
            $attributes = shortcode_atts(
                [
                    'post_id' => null,
                    'range' => 'all',
                    'time_unit' => 'hour',
                    'time_quantity' => 24,
                    'number_format' => 1,
                    'include_views_text' => 1
                ],
                $attributes,
                $this->tag
            );

            if ( ! $attributes['post_id'] ) {
                if ( is_singular() ) {
                    $attributes['post_id'] = \get_queried_object_id();
                }
            }

            if ( $attributes['post_id'] && is_numeric($attributes['post_id']) ) {
                $valid_time_ranges = ['last24hours', 'last7days', 'last30days', 'all', 'custom'];

                if (
                    'all' === $attributes['range']
                    || ! in_array($attributes['range'], $valid_time_ranges)
                ) {
                    $views = wpp_get_views($attributes['post_id'], 'all', false);
                } elseif ( 'custom' !== $attributes['range'] ) {
                    $views = wpp_get_views($attributes['post_id'], $attributes['range'], false);
                } else {
                    $views = wpp_get_views(
                        $attributes['post_id'],
                        [
                            'range' => 'custom',
                            'time_unit' => $attributes['time_unit'],
                            'time_quantity' => $attributes['time_quantity']
                        ],
                        false
                    );
                }

                $views = (int) $views;
                $views_string = $views;

                if ( $views && $attributes['number_format'] ) {
                    $views_string = ( 'prettify' === $attributes['number_format'] ) ? Helper::prettify_number($views) : number_format_i18n($views);
                }

                if ( $attributes['include_views_text'] ) {
                    return sprintf(
                        _n('%s view', '%s views', $views, 'wordpress-popular-posts'),
                        $views_string
                    );
                }

                return $views_string;
            }
        }

        return $views;
    }
}
