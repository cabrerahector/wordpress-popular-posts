<?php
namespace WordPressPopularPosts\Rest;

use WordPressPopularPosts\Helper;

class ViewLoggerEndpoint extends Endpoint {

    /**
     * Registers the endpoint(s).
     *
     * @since   5.3.0
     */
    public function register()
    {
        $version = '1';
        $namespace = 'wordpress-popular-posts/v' . $version;

        register_rest_route($namespace, '/popular-posts', [
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'update_views_count'],
                'permission_callback' => '__return_true',
                'args'                => $this->get_tracking_params(),
            ]
        ]);
    }

    /**
     * Updates the views count of a post / page.
     *
     * @since   4.1.0
     *
     * @param   \WP_REST_Request    $request Full details about the request.
     * @return  string
     */
    public function update_views_count($request){
        global $wpdb;

        $post_ID = $request->get_param('wpp_id');
        $sampling = $request->get_param('sampling');
        $sampling_rate = $request->get_param('sampling_rate');

        $table = $wpdb->prefix . "popularposts";
        $wpdb->show_errors();

        // Get translated object ID
        $post_ID = $this->translate->get_object_id(
            $post_ID,
            get_post_type($post_ID),
            true,
            $this->translate->get_default_language()
        );

        $now = Helper::now();
        $curdate = Helper::curdate();
        $views = ($sampling)
          ? $sampling_rate
          : 1;

        // Allow WP themers / coders perform an action
        // before updating views count
        if ( has_action('wpp_pre_update_views') )
            do_action('wpp_pre_update_views', $post_ID, $views);

        $result1 = $result2 = false;

        $exec_time = 0;
        $start = Helper::microtime_float();

        // Store views data in persistent object cache
        if (
            wp_using_ext_object_cache()
            && defined('WPP_CACHE_VIEWS')
            && WPP_CACHE_VIEWS
        ) {

            $now_datetime = new \DateTime($now, new \DateTimeZone(Helper::get_timezone()));
            $timestamp = $now_datetime->getTimestamp();
            $date_time = $now_datetime->format('Y-m-d H:i');
            $date_time_with_seconds = $now_datetime->format('Y-m-d H:i:s');
            $high_accuracy = false;

            $key = $high_accuracy ? $timestamp : $date_time;

            if ( ! $wpp_cache = wp_cache_get('_wpp_cache', 'transient') ) {
                $wpp_cache = [
                    'last_updated' => $date_time_with_seconds,
                    'data' => [
                        $post_ID => [
                            $key => 1
                        ]
                    ]
                ];
            } else {
                if ( ! isset($wpp_cache['data'][$post_ID][$key]) ) {
                    $wpp_cache['data'][$post_ID][$key] = 1;
                } else {
                    $wpp_cache['data'][$post_ID][$key] += 1;
                }
            }

            // Update cache
            wp_cache_set('_wpp_cache', $wpp_cache, 'transient', 0);

            // How long has it been since the last time we saved to the database?
            $last_update = $now_datetime->diff(new \DateTime($wpp_cache['last_updated'], new \DateTimeZone(Helper::get_timezone())));
            $diff_in_minutes = $last_update->days * 24 * 60;
            $diff_in_minutes += $last_update->h * 60;
            $diff_in_minutes += $last_update->i;

            // It's been more than 2 minutes, save everything to DB
            if ( $diff_in_minutes > 2 ) {

                $query_data = "INSERT INTO {$table}data (`postid`,`day`,`last_viewed`,`pageviews`) VALUES ";
                $query_summary = "INSERT INTO {$table}summary (`postid`,`pageviews`,`view_date`,`view_datetime`) VALUES ";

                foreach( $wpp_cache['data'] as $pid => $data ) {
                    $views_count = 0;

                    foreach( $data as $ts => $cached_views ){
                        $views_count += $cached_views;
                        $ts = Helper::is_timestamp($ts) ? $ts : strtotime($ts);

                        $query_summary .= $wpdb->prepare("(%d,%d,%s,%s),", [
                            $pid,
                            $cached_views,
                            date("Y-m-d", $ts),
                            date("Y-m-d H:i:s", $ts)
                        ]);
                    }

                    $query_data .= $wpdb->prepare( "(%d,%s,%s,%s),", [
                        $pid,
                        $date_time_with_seconds,
                        $date_time_with_seconds,
                        $views_count
                    ]);
                }

                $query_data = rtrim($query_data, ",") . " ON DUPLICATE KEY UPDATE pageviews=pageviews+VALUES(pageviews),last_viewed=VALUES(last_viewed);";
                $query_summary = rtrim($query_summary, ",") . ";";

                // Clear cache
                $wpp_cache['last_updated'] = $date_time_with_seconds;
                $wpp_cache['data'] = [];
                wp_cache_set('_wpp_cache', $wpp_cache, 'transient', 0);

                // Save
                $result1 = $wpdb->query($query_data);
                $result2 = $wpdb->query($query_summary);
            }
            else {
                $result1 = $result2 = true;
            }
        } // Live update to the DB
        else {
            // Update all-time table
            $result1 = $wpdb->query($wpdb->prepare(
                "INSERT INTO {$table}data
                (postid, day, last_viewed, pageviews) VALUES (%d, %s, %s, %d)
                ON DUPLICATE KEY UPDATE pageviews = pageviews + %d, last_viewed = %s;",
                $post_ID,
                $now,
                $now,
                $views,
                $views,
                $now
            ));

            // Update range (summary) table
            $result2 = $wpdb->query($wpdb->prepare(
                "INSERT INTO {$table}summary
                (postid, pageviews, view_date, view_datetime) VALUES (%d, %d, %s, %s)
                ON DUPLICATE KEY UPDATE pageviews = pageviews + %d, view_datetime = %s;",
                $post_ID,
                $views,
                $curdate,
                $now,
                $views,
                $now
            ));
        }

        $end = Helper::microtime_float();
        $exec_time += round($end - $start, 6);

        $response = ['results' => ''];

        if ( ! $result1 || ! $result2 ) {
            $response['results'] = 'WPP: failed to update views count!';
            return new \WP_REST_Response($response, 500);
        }

        // Allow WP themers / coders perform an action
        // after updating views count
        if ( has_action('wpp_post_update_views') )
            do_action('wpp_post_update_views', $post_ID);

        $response['results'] = "WPP: OK. Execution time: " . $exec_time . " seconds";
        return new \WP_REST_Response($response, 201);
    }

    /**
     * Retrieves the query params for tracking views count.
     *
     * @since 4.1.0
     *
     * @return array Query parameters for tracking views count.
     */
    public function get_tracking_params()
    {
        return [
            'token' => [
                'description'       => __('Security nonce.'),
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'wpp_id' => [
                'description'       => __('The post / page ID.'),
                'type'              => 'integer',
                'default'           => 0,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'sampling' => [
                'description'       => __('Enables Data Sampling.'),
                'type'              => 'integer',
                'default'           => 0,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'sampling_rate' => [
                'description'       => __('Sets the Sampling Rate.'),
                'type'              => 'integer',
                'default'           => 100,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ]
        ];
    }
}
