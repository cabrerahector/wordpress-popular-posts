<?php
namespace WordPressPopularPosts\Rest;

use WordPressPopularPosts\{ Output, Translate };
use WordPressPopularPosts\Traits\QueriesPosts;

class WidgetEndpoint extends Endpoint {

    use QueriesPosts;

    /**
     * Output object.
     *
     * @var     \WordPressPopularPosts\Output       $output
     * @access  private
     */
    protected $output;

    /**
     * Initializes class.
     *
     * @param   array
     * @param   \WordPressPopularPosts\Translate
     * @param   \WordPressPopularPosts\Output
     */
    public function __construct(array $config, Translate $translate, Output $output)
    {
        $this->config = $config;
        $this->translate = $translate;
        $this->output = $output;
    }

    /**
     * Registers the endpoint(s).
     *
     * @since   5.3.0
     */
    public function register()
    {
        $version = '2';
        $namespace = 'wordpress-popular-posts/v' . $version;

        register_rest_route($namespace, '/widget/', [
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'get_widget_block'],
                'permission_callback' => '__return_true',
                'args'                => $this->get_widget_params(),
            ]
        ]);
    }

    /**
     * Retrieves a popular posts widget for display.
     *
     * @since 5.4.0
     *
     * @param \WP_REST_Request $request Full details about the request.
     * @return \WP_Error|\WP_REST_Response Response object on success, or WP_Error object on failure.
     */
    public function get_widget_block($request)
    {
        $instance = $request->get_params();

        $is_single = $request->get_param('is_single');
        $lang = $request->get_param('lang');

        // Multilang support
        $this->set_lang($lang);

        $popular_posts = $this->maybe_query($instance);

        if ( is_numeric($is_single) && $is_single > 0 ) {
            add_filter('wpp_is_single', function($id) use ($is_single) {
                return $is_single;
            });
        }

        $this->output->set_data($popular_posts->get_posts());
        $this->output->set_public_options($instance);
        $this->output->build_output();

        return [
            'widget' => ( $this->config['tools']['cache']['active'] ? '<!-- cached -->' : '' ) . $this->output->get_output()
        ];
    }

    /**
     * Retrieves the query params for getting a widget instance.
     *
     * @since 4.1.0
     *
     * @return array Query parameters for getting a widget instance.
     */
    public function get_widget_params()
    {
        return [
            'is_single' => [
                'type' => 'integer',
                'default' => null,
                'sanitize_callback' => 'absint'
            ],
            'lang' => [
                'type' => 'string',
                'default' => null,
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'post_type' => [
                'description'       => 'Return popular posts from specified custom post type(s).',
                'type'              => 'string',
                'default'           => 'post',
                'sanitize_callback' => function($post_type) {
                    $allowed_post_types = get_post_types([
                        'public' => true,
                    ]);

                    $post_type = implode(',', array_intersect(
                        $allowed_post_types,
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
                'maximum'           => 100,
            ],
            'freshness' => [
                'description'       => 'Retrieve the most popular entries published within the specified time range.',
                'type'              => ['string', 'boolean'],
                'enum'              => ['0', '1', false, true],
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
