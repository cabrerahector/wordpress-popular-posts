<?php
namespace WordPressPopularPosts\Rest;

class PostsEndpoint extends Endpoint {

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
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_items'],
                'permission_callback' => '__return_true',
                'args'                => $this->get_collection_params()
            ]
        ]);
    }

    /**
     * Gets popular posts.
     *
     * @since   5.3.0
     * @param   \WP_REST_Request $request Full data about the request.
     * @return  \WP_REST_Response
     */
    public function get_items($request)
    {
        $params = $request->get_params();
        $lang = isset($params['lang']) ? $params['lang'] : null;
        $popular_posts = [];

        // Multilang support
        $this->set_lang($lang);

        $query = $this->maybe_query($params);
        $results = $query->get_posts();

        if ( is_array($results) && ! empty($results) ) {
            foreach( $results as $popular_post ) {
                $popular_posts[] = $this->prepare_item($popular_post, $request);
            }
        }

        return new \WP_REST_Response($popular_posts, 200);
    }

    /**
     * Retrieves the popular post's WP_Post object and formats it for the REST response.
     *
     * @since 4.1.0
     *
     * @param   object             $popular_post The popular post object.
     * @param   \WP_REST_Request   $request Full details about the request.
     * @return  array|mixed        The formatted WP_Post object.
     */
    private function prepare_item($popular_post, $request)
    {
        if ( $request->get_param('lang') ) {
            $post_ID = $this->translate->get_object_id(
                $popular_post->id,
                get_post_type($popular_post->id)
            );
        } else {
            $post_ID = $popular_post->id;
        }

        $wp_post = get_post($post_ID);

        // Borrow prepare_item_for_response method from WP_REST_Posts_Controller.
        $posts_controller = new \WP_REST_Posts_Controller($wp_post->post_type, $request);
        $data = $posts_controller->prepare_item_for_response($wp_post, $request);

        // Add pageviews from popular_post object to response.
        $data->data['pageviews'] = $popular_post->pageviews;

        return $this->prepare_response_for_collection($data);
    }

    /**
     * Retrieves the query params for the collections.
     *
     * @since 4.1.0
     *
     * @return array Query parameters for the collection.
     */
    public function get_collection_params()
    {
        return [
            'post_type' => [
                'description'       => __('Return popular posts from specified custom post type(s).'),
                'type'              => 'string',
                'default'           => 'post',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'limit' => [
                'description'       => __('The maximum number of popular posts to return.'),
                'type'              => 'integer',
                'default'           => 10,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
                'minimum'           => 1,
            ],
            'freshness' => [
                'description'       => __('Retrieve the most popular entries published within the specified time range.'),
                'type'              => 'string',
                'enum'              => ['0', '1'],
                'default'           => '0',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'offset' => [
                'description'       => __('An offset point for the collection.'),
                'type'              => 'integer',
                'default'           => 0,
                'minimum'           => 0,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'order_by' => [
                'description'       => __('Set the sorting option of the popular posts.'),
                'type'              => 'string',
                'enum'              => ['views', 'comments'],
                'default'           => 'views',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'range' => [
                'description'       => __('Return popular posts from a specified time range.'),
                'type'              => 'string',
                'enum'              => ['last24hours', 'last7days', 'last30days', 'all', 'custom'],
                'default'           => 'last24hours',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'time_unit' => [
                'description'       => __('Specifies the time unit of the custom time range.'),
                'type'              => 'string',
                'enum'              => ['minute', 'hour', 'day', 'week', 'month'],
                'default'           => 'hour',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'time_quantity' => [
                'description'       => __('Specifies the number of time units of the custom time range.'),
                'type'              => 'integer',
                'default'           => 24,
                'minimum'           => 1,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'pid' => [
                'description'       => __('Post IDs to exclude from the listing.'),
                'type'              => 'string',
                'sanitize_callback' => function($pid) {
                    return rtrim(preg_replace('|[^0-9,]|', '', $pid), ',');
                },
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'taxonomy' => [
                'description'       => __('Include posts in a specified taxonomy.'),
                'type'              => 'string',
                'sanitize_callback' => function($taxonomy) {
                    return empty($taxonomy) ? 'category' : $taxonomy;
                },
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'term_id' => [
                'description'       => __('Taxonomy IDs, separated by comma (prefix a minus sign to exclude).'),
                'type'              => 'string',
                'sanitize_callback' => function($term_id) {
                    return rtrim(preg_replace('|[^0-9,;-]|', '', $term_id), ',');
                },
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'author' => [
                'description'       => __('Include popular posts from author ID(s).'),
                'type'              => 'string',
                'sanitize_callback' => function($author) {
                    return rtrim(preg_replace('|[^0-9,]|', '', $author), ',');
                },
                'validate_callback' => 'rest_validate_request_arg',
            ],
        ];
    }
}
