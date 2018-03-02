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
                    'permission_callback' => array( $this, 'get_items_permissions_check' ),
                    'args'                => $this->get_collection_params(),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'update_views_count' ),
                    'args'                => $this->get_tracking_params(),
                ),
                'schema' => array( $this, 'get_public_item_schema' ),
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
     * Checks whether a given request has permission to get popular posts.
     *
     * @since 4.1.0
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_Error|bool True if the request has read access, WP_Error object otherwise.
     */
    public function get_items_permissions_check( $request ) {
		// TO-DO: Are there scenarios where this request should be rejected?
        return true;
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

            // Return cached results
            if ( $admin_options['tools']['cache']['active'] ) {

                $transient_name = md5( json_encode($instance) );
                $popular_posts = get_transient( $transient_name );

                if ( false === $popular_posts ) {

                    $popular_posts = new WPP_Query( $instance );

                    switch( $admin_options['tools']['cache']['interval']['time'] ){

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
                            $time = 60 * 60;
                        break;

                    }

                    $expiration = $time * $admin_options['tools']['cache']['interval']['value'];

                    // Store transient
                    set_transient( $transient_name, $popular_posts, $expiration );

                    // Store transient in WPP transients array for garbage collection
                    $wpp_transients = get_option('wpp_transients');

                    if ( !$wpp_transients ) {
                        $wpp_transients = array( $transient_name );
                        add_option( 'wpp_transients', $wpp_transients );
                    } else {
                        if ( !in_array($transient_name, $wpp_transients) ) {
                            $wpp_transients[] = $transient_name;
                            update_option( 'wpp_transients', $wpp_transients );
                        }
                    }

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

        $exec_time = 0;
        $start = WPP_helper::microtime_float();

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
                'default'           => 'post,page',
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
                'description'       => __( 'Return posts from a specified time range.' ),
                'type'              => 'string',
                'enum'                 => array( 'today', 'daily', 'last24hours', 'weekly', 'last7days', 'monthly', 'last30days', 'all', 'custom' ),
                'default'            => 'daily',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ),
            'time_unit'   => array(
                'description'       => __( 'Especifies the time unit of the custom time range.' ),
                'type'              => 'string',
                'enum'                 => array( 'minute', 'hour', 'day', 'week', 'month' ),
                'default'            => 'hour',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ),
            'time_quantity' => array(
                'description'       => __( 'Especifies the number of time units of the custom time range.' ),
                'type'              => 'integer',
                'default'           => 24,
                'minimum'           => 1,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ),
            'pid'   => array(
                'description'       => __( 'Post IDs to exclude.' ),
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
                'description'       => __( 'Author ID(s).' ),
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
                'description'       => __( 'The post / page ID.' ),
                'type'              => 'integer',
                'default'           => 0,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            )
        );
    }
}
