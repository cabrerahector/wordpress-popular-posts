<?php
namespace WordPressPopularPosts\Rest;

use WordPressPopularPosts\Translate;
use WordPressPopularPosts\Traits\QueriesPosts;

class PostsEndpoint extends Endpoint {

    use QueriesPosts;

    /**
     * Post types we are allowed to query.
     *
     * @access  private
     * @param   array
     * @since   7.4.3
     */
    private array $allowed_post_types = [];

    /**
     * Initializes class.
     *
     * @param   array
     * @param   \WordPressPopularPosts\Translate
     */
    public function __construct(array $config, Translate $translate)
    {
        $this->config = $config;
        $this->translate = $translate;
        $this->allowed_post_types = get_post_types([
            'public' => true,
            'show_in_rest' => true
        ]);
    }

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
        // This endpoint doesn't have any use for the context param
        // so let's drop it if present in the request
        //
        // WP will then default to 'view' context which is
        // the expected behavior
        if ( $request->offsetExists('context') ) {
            $request->offsetUnset('context');
        }

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
        $posts_controller = new \WP_REST_Posts_Controller($wp_post->post_type);
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
                'description'       => 'Return popular posts from specified custom post type(s).',
                'type'              => 'string',
                'default'           => 'post',
                'sanitize_callback' => function($post_type) {
                    $post_type = implode(',', array_intersect(
                        $this->allowed_post_types,
                        array_filter(array_map('trim', explode(',', $post_type)))
                    ));

                    if ( ! $post_type ) {
                        $post_type = 'post'; // same as 'default'
                    }

                    return $post_type;
                },
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'limit' => [
                'description'       => 'The maximum number of popular posts to return.',
                'type'              => 'integer',
                'default'           => 10,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
                'minimum'           => 1,
            ],
            'freshness' => [
                'description'       => 'Retrieve the most popular entries published within the specified time range.',
                'type'              => 'string',
                'enum'              => ['0', '1'],
                'default'           => '0',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'offset' => [
                'description'       => 'An offset point for the collection.',
                'type'              => 'integer',
                'default'           => 0,
                'minimum'           => 0,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'order_by' => [
                'description'       => 'Set the sorting option of the popular posts.',
                'type'              => 'string',
                'enum'              => ['views', 'comments'],
                'default'           => 'views',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'range' => [
                'description'       => 'Return popular posts from a specified time range.',
                'type'              => 'string',
                'enum'              => ['last24hours', 'last7days', 'last30days', 'all', 'custom'],
                'default'           => 'last24hours',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'time_unit' => [
                'description'       => 'Specifies the time unit of the custom time range.',
                'type'              => 'string',
                'enum'              => ['minute', 'hour', 'day', 'week', 'month'],
                'default'           => 'hour',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'time_quantity' => [
                'description'       => 'Specifies the number of time units of the custom time range.',
                'type'              => 'integer',
                'default'           => 24,
                'minimum'           => 1,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'pid' => [
                'description'       => 'Post IDs to exclude from the listing.',
                'type'              => 'string',
                'sanitize_callback' => function($pid) {
                    return rtrim(preg_replace('|[^0-9,]|', '', $pid), ',');
                },
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'exclude' => [
                'description'       => 'Post IDs to exclude from the listing.',
                'type'              => 'string',
                'sanitize_callback' => function($exclude) {
                    return rtrim(preg_replace('|[^0-9,]|', '', $exclude), ',');
                },
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'taxonomy' => [
                'description'       => 'Include posts in a specified taxonomy.',
                'type'              => 'string',
                'sanitize_callback' => function($taxonomy) {
                    return empty($taxonomy) ? 'category' : $taxonomy;
                },
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'term_id' => [
                'description'       => 'Taxonomy IDs, separated by comma (prefix a minus sign to exclude).',
                'type'              => 'string',
                'sanitize_callback' => function($term_id) {
                    return rtrim(preg_replace('|[^0-9,;-]|', '', $term_id), ',');
                },
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'author' => [
                'description'       => 'Include popular posts from author ID(s).',
                'type'              => 'string',
                'sanitize_callback' => function($author) {
                    return rtrim(preg_replace('|[^0-9,]|', '', $author), ',');
                },
                'validate_callback' => 'rest_validate_request_arg',
            ],
        ];
    }
}
