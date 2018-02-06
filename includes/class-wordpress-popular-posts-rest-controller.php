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
        $this->namespace = 'wp/v2';
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
                'schema' => array( $this, 'get_public_item_schema' ),
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
     * Retrieves the query params for the collections.
     *
     * @since 4.1.0
     *
     * @return array Query parameters for the collection.
     */
    public function get_collection_params() {
        return array(
            'limit'     => array(
                'description'       => __( 'The maximum number of popular posts to return.' ),
                'type'              => 'integer',
                'default'           => 10,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
                'minimum'           => 1,
            ),
            'offset' => array(
                'description'       => __( 'An offset point for the collection.' ),
                'type'              => 'integer',
                'default'           => 0,
                'minimum'           => 0,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ),
            'range'   => array(
                'description'       => __( 'Return posts from a specified time range.' ),
                'type'              => 'string',
                'enum'                 => array( 'daily', 'last24hours', 'weekly', 'last7days', 'monthly', 'last30days', 'all', 'custom' ),
                'default'            => 'daily',
                'sanitize_callback' => 'sanitize_text_field',
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
}
