<?php

/**
 * Class used to access popular posts via the REST API.
 *
 * @since    4.1.0
 */
class WP_REST_Popular_Posts_Controller extends WP_REST_Controller {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->namespace = 'wordpress-popular-posts/v1';
        $this->rest_base = 'popular-posts';
    }

    /**
     * Registers the rest route.
     *
     * @since    4.1.0
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->rest_base, array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_items' ),
                    'args'                => $this->get_collection_params(),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'update_views_count' ),
                    'args'                => $this->get_tracking_params(),
                ),
            )
        );

        // Widget endpoint
        register_rest_route(
            $this->namespace, '/' . $this->rest_base . '/widget', array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_widget' ),
                    'args'                => $this->get_widget_params(),
                )
            )
        );
    }

    /**
     * Retrieves the popular posts.
     *
     * @since 4.1.0
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
     */
    public function get_items( $request ) {
		$rest_params = $request->get_params();

		// The REST params match up with the args accepted by WPP_Query.
        $wpp_query = new WPP_Query( $rest_params );
		$wpp_posts = $wpp_query->get_posts();

        $posts = array();

		if ( ! empty( $wpp_posts ) ) {
	        foreach ( $wpp_posts as $popular_post ) {
	            $posts[] = $this->prepare_item( $popular_post, $request );
	        }
		}

        return rest_ensure_response( $posts );
    }

    /**
     * Retrieves the popular post's WP_Post object and formats it for the REST response.
     *
     * @since 4.1.0
     *
     * @param object          $popular_post The popular post object.
     * @param WP_REST_Request $request Full details about the request.
     * @return object           The formatted WP_Post object.
     */
    private function prepare_item( $popular_post, $request ) {
        $wp_post = get_post( $popular_post->id );

        // Borrow prepare_item_for_response method from WP_REST_Posts_Controller.
        $posts_controller = new WP_REST_Posts_Controller( $wp_post->post_type, $request );
        $data = $posts_controller->prepare_item_for_response( $wp_post, $request );

        // Add pageviews from popular_post object to response.
        $data->data['pageviews'] = $popular_post->pageviews;

        return $this->prepare_response_for_collection( $data );
    }

    /**
     * Retrieves a popular posts widget for display.
     *
     * @since 4.1.0
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
     */
    public function get_widget( $request ) {

        $instance_id = $request->get_param( 'id' );
        $widget = get_option( 'widget_wpp' );

        // Valid instance
        if ( $widget && isset($widget[ $instance_id ]) ) {

            $instance = $widget[ $instance_id ];
            $admin_options = WPP_Settings::get( 'admin_options' );

            // Expose widget ID for customization
            if ( ! isset($instance['widget_id']) )
                $instance['widget_id'] = 'wpp-' . $instance_id;

            // Return cached results
            if ( $admin_options['tools']['cache']['active'] ) {

                $key = md5( json_encode($instance) );
                $popular_posts = WPP_Cache::get( $key );

                if ( false === $popular_posts ) {

                    $popular_posts = new WPP_Query( $instance );

                    $time_value = $admin_options['tools']['cache']['interval']['value']; // eg. 5
                    $time_unit = $admin_options['tools']['cache']['interval']['time']; // eg. 'minute'

                    WPP_Cache::set(
                        $key,
                        $popular_posts,
                        $time_value,
                        $time_unit
                    );

                }

            } // Get popular posts
            else {
                $popular_posts = new WPP_Query( $instance );
            }

            $output = new WPP_Output( $popular_posts->get_posts(), $instance );

            return rest_ensure_response( array(
                'widget' => ( $admin_options['tools']['cache']['active'] ? '<!-- cached -->' : '' ) . $output->get_output()
            ) );

        }

        return new WP_Error( 'no_widget', 'Invalid widget instance', array( 'status' => 404 ) );

    }

    /**
     * Updates the views count of a post / page.
     *
     * @since 4.1.0
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_Error|string response on failure/success, or WP_Error object on nonce failure.
     */
    public function update_views_count( $request ){

        $post_ID = $request->get_param( 'wpp_id' );
        $sampling = $request->get_param( 'sampling' );
        $sampling_rate = $request->get_param( 'sampling_rate' );

        global $wpdb;
        $table = $wpdb->prefix . "popularposts";
        $wpdb->show_errors();

        // Get translated object ID
        $translate = WPP_translate::get_instance();
        $post_ID = $translate->get_object_id(
            $post_ID,
            get_post_type( $post_ID ),
            true,
            $translate->get_default_language()
        );

        $now = WPP_helper::now();
        $curdate = WPP_helper::curdate();
        $views = ( $sampling )
          ? $sampling_rate
          : 1;

        // Allow WP themers / coders perform an action
        // before updating views count
        if ( has_action( 'wpp_pre_update_views' ) )
            do_action( 'wpp_pre_update_views', $post_ID, $views );

        $result1 = $result2 = false;

        $exec_time = 0;
        $start = WPP_helper::microtime_float();

        // Store views data in persistent object cache
        if (
            wp_using_ext_object_cache()
            && defined( 'WPP_CACHE_VIEWS' )
            && WPP_CACHE_VIEWS
        ) {

            $now = new DateTime( WPP_Helper::now() );
            $timestamp = $now->getTimestamp();

            if ( ! $wpp_cache = wp_cache_get( '_wpp_cache', 'transient' ) ) {

                $wpp_cache = array(
                    'last_updated' => $now->format('Y-m-d H:i:s'),
                    'data' => array(
                        $post_ID => array(
                            $timestamp => 1
                        )
                    )
                );

            } else {

                if ( ! isset( $wpp_cache['data'][$post_ID] ) ) {
                    $wpp_cache['data'][$post_ID][$timestamp] = 1;
                } else {

                    if ( isset($wpp_cache['data'][$post_ID][$timestamp]) ) {
                        $wpp_cache['data'][$post_ID][$timestamp] += 1;
                    } else {
                        $wpp_cache['data'][$post_ID][$timestamp] = 1;
                    }

                }

            }

            // Update cache
            wp_cache_set( '_wpp_cache', $wpp_cache, 'transient', 0 );

            // How long has it been since the last time we saved to the database?
            $last_update = $now->diff( new DateTime($wpp_cache['last_updated']) );
            $diff_in_minutes = $last_update->days * 24 * 60;
            $diff_in_minutes += $last_update->h * 60;
            $diff_in_minutes += $last_update->i;

            // It's been more than 5 minutes, save everything to DB
            if ( $diff_in_minutes > 2 ) {

                $query_data = "INSERT INTO {$table}data (`postid`,`day`,`last_viewed`,`pageviews`) VALUES ";
                $query_summary = "INSERT INTO {$table}summary (`postid`,`pageviews`,`view_date`,`view_datetime`) VALUES ";

                foreach( $wpp_cache['data'] as $pid => $data ) {

                    $views_count = 0;

                    foreach( $data as $ts => $cached_views ){
                        $views_count += $cached_views;

                        $query_summary .= $wpdb->prepare( "(%d,%d,%s,%s),", array(
                            $pid,
                            $cached_views,
                            date("Y-m-d", $ts),
                            date("Y-m-d H:i:s", $ts)
                        ));
                    }

                    $query_data .= $wpdb->prepare( "(%d,%s,%s,%s),", array(
                        $pid,
                        $now->format('Y-m-d H:i:s'),
                        $now->format('Y-m-d H:i:s'),
                        $views_count
                    ));

                }

                $query_data = rtrim( $query_data, ",") . " ON DUPLICATE KEY UPDATE pageviews=pageviews+VALUES(pageviews),last_viewed=VALUES(last_viewed);";
                $query_summary = rtrim( $query_summary, ",") . ";";

                // Clear cache
                $wpp_cache['last_updated'] = $now->format('Y-m-d H:i:s');
                $wpp_cache['data'] = array();
                wp_cache_set( '_wpp_cache', $wpp_cache, 'transient', 0 );

                // Save
                $result1 = $wpdb->query( $query_data );
                $result2 = $wpdb->query( $query_summary );

            }
            else {
                $result1 = $result2 = true;
            }

        } // Live update to the DB
        else {

            // Update all-time table
            $result1 = $wpdb->query( $wpdb->prepare(
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
            $result2 = $wpdb->query( $wpdb->prepare(
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

        $end = WPP_helper::microtime_float();
        $exec_time += round( $end - $start, 6 );

        if ( !$result1 || !$result2 ) {
            return 'WPP: failed to update views count!';
        }

        // Allow WP themers / coders perform an action
        // after updating views count
        if ( has_action( 'wpp_post_update_views' ) )
            do_action( 'wpp_post_update_views', $post_ID );

        return "WPP: OK. Execution time: " . $exec_time . " seconds";

    }

    /**
     * Retrieves the query params for the collections.
     *
     * @since 4.1.0
     *
     * @return array Query parameters for the collection.
     */
    public function get_collection_params() {
        return array(
            'post_type'     => array(
                'description'       => __( 'Return popular posts from specified custom post type(s).' ),
                'type'              => 'string',
                'default'           => 'post',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ),
            'limit'     => array(
                'description'       => __( 'The maximum number of popular posts to return.' ),
                'type'              => 'integer',
                'default'           => 10,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
                'minimum'           => 1,
            ),
            'freshness' => array(
                'description'       => __( 'Retrieve the most popular entries published within the specified time range.' ),
                'type'              => 'string',
                'enum'              => array( '0', '1' ),
                'default'           => '0',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ),
            'offset' => array(
                'description'       => __( 'An offset point for the collection.' ),
                'type'              => 'integer',
                'default'           => 0,
                'minimum'           => 0,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ),
            'order_by'   => array(
                'description'       => __( 'Set the sorting option of the popular posts.' ),
                'type'              => 'string',
                'enum'                 => array( 'views', 'comments' ),
                'default'            => 'views',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ),
            'range'   => array(
                'description'       => __( 'Return popular posts from a specified time range.' ),
                'type'              => 'string',
                'enum'                 => array( 'last24hours', 'last7days', 'last30days', 'all', 'custom' ),
                'default'            => 'last24hours',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ),
            'time_unit'   => array(
                'description'       => __( 'Specifies the time unit of the custom time range.' ),
                'type'              => 'string',
                'enum'                 => array( 'minute', 'hour', 'day', 'week', 'month' ),
                'default'            => 'hour',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ),
            'time_quantity' => array(
                'description'       => __( 'Specifies the number of time units of the custom time range.' ),
                'type'              => 'integer',
                'default'           => 24,
                'minimum'           => 1,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ),
            'pid'   => array(
                'description'       => __( 'Post IDs to exclude from the listing.' ),
                'type'              => 'string',
                'sanitize_callback' => function( $pid ) {
                    return rtrim( preg_replace( '|[^0-9,]|', '', $pid ), ',' );
                },
                'validate_callback' => 'rest_validate_request_arg',
            ),
            'taxonomy'   => array(
                'description'       => __( 'Include posts in a specified taxonomy.' ),
                'type'              => 'string',
                'sanitize_callback' => function( $taxonomy ) {
                    return empty( $taxonomy ) ? 'category' : $taxonomy;
                },
                'validate_callback' => 'rest_validate_request_arg',
            ),
            'term_id'   => array(
                'description'       => __( 'Taxonomy IDs, separated by comma (prefix a minus sign to exclude).' ),
                'type'              => 'string',
                'sanitize_callback' => function( $term_id ) {
                    return rtrim( preg_replace( '|[^0-9,-]|', '', $term_id ), ',' );
                },
                'validate_callback' => 'rest_validate_request_arg',
            ),
            'author'   => array(
                'description'       => __( 'Include popular posts from author ID(s).' ),
                'type'              => 'string',
                'sanitize_callback' => function( $author ) {
                    return rtrim( preg_replace( '|[^0-9,]|', '', $author ), ',' );
                },
                'validate_callback' => 'rest_validate_request_arg',
            ),
        );
    }

    /**
     * Retrieves the query params for tracking views count.
     *
     * @since 4.1.0
     *
     * @return array Query parameters for tracking views count.
     */
    public function get_tracking_params() {
        return array(
            'token'   => array(
                'description'       => __( 'Security nonce.' ),
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ),
            'wpp_id' => array(
                'description'       => __( 'The post / page ID.' ),
                'type'              => 'integer',
                'default'           => 0,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ),
            'sampling' => array(
                'description'       => __( 'Enables Data Sampling.' ),
                'type'              => 'integer',
                'default'           => 0,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ),
            'sampling_rate' => array(
                'description'       => __( 'Sets the Sampling Rate.' ),
                'type'              => 'integer',
                'default'           => 100,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            )
        );
    }

    /**
     * Retrieves the query params for getting a widget instance.
     *
     * @since 4.1.0
     *
     * @return array Query parameters for getting a widget instance.
     */
    public function get_widget_params() {
        return array(
            'id' => array(
                'description'       => __( 'Widget instance ID' ),
                'type'              => 'integer',
                'default'           => 0,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            )
        );
    }
}
