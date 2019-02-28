<?php
namespace WordPressPopularPosts\Rest;

use WordPressPopularPosts\Helper;
use WordPressPopularPosts\Query;

class Controller extends \WP_REST_Controller {

    /**
     * Plugin options.
     *
     * @var     array      $config
     * @access  private
     */
    private $config;

    /**
     * Translate object.
     *
     * @var     \WordPressPopularPosts\Translate    $translate
     * @access  private
     */
    private $translate;

    /**
     * Initialize class.
     *
     */
    public function __construct(array $config, \WordPressPopularPosts\Translate $translate)
    {
        $this->config = $config;
        $this->translate = $translate;
    }

    /**
     * WordPress hooks.
     *
     * @since   5.0.0
     */
    public function hooks()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Registers REST endpoints.
     */
    public function register_routes()
    {
        $version = '1';
        $namespace = 'wordpress-popular-posts/v' . $version;

        register_rest_route($namespace, '/popular-posts', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
                'args'                => $this->get_collection_params()
            ],
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'update_views_count'],
                'args'                => $this->get_tracking_params(),
            ]
        ]);
    }

    /**
     * Gets popular posts.
     *
     * @since   1.0.0
     * @param   \WP_REST_Request $request Full data about the request.
     * @return  \WP_REST_Response
     */
    public function get_items($request)
    {
        $params = $request->get_params();
        $popular_posts = [];
        $query = new Query($params);
        $results = $query->get_posts();

        if ( is_array($results) && ! empty($results) ) {
            foreach( $results as $popular_post ) {
                $popular_posts[] = $this->prepare_item($popular_post, $request);
            }
        }

        return new \WP_REST_Response($popular_posts, 200);
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

            $timestamp = $now->getTimestamp();

            if ( ! $wpp_cache = wp_cache_get('_wpp_cache', 'transient') ) {
                $wpp_cache = [
                    'last_updated' => $now->format('Y-m-d H:i:s'),
                    'data' => [
                        $post_ID => [
                            $timestamp => 1
                        ]
                    ]
                ];
            } else {
                if ( ! isset($wpp_cache['data'][$post_ID]) ) {
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
            wp_cache_set('_wpp_cache', $wpp_cache, 'transient', 0);

            // How long has it been since the last time we saved to the database?
            $last_update = $now->diff(new \DateTime($wpp_cache['last_updated']));
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

                        $query_summary .= $wpdb->prepare("(%d,%d,%s,%s),", [
                            $pid,
                            $cached_views,
                            date("Y-m-d", $ts),
                            date("Y-m-d H:i:s", $ts)
                        ]);
                    }

                    $query_data .= $wpdb->prepare( "(%d,%s,%s,%s),", [
                        $pid,
                        $now->format('Y-m-d H:i:s'),
                        $now->format('Y-m-d H:i:s'),
                        $views_count
                    ]);
                }

                $query_data = rtrim($query_data, ",") . " ON DUPLICATE KEY UPDATE pageviews=pageviews+VALUES(pageviews),last_viewed=VALUES(last_viewed);";
                $query_summary = rtrim($query_summary, ",") . ";";

                // Clear cache
                $wpp_cache['last_updated'] = $now->format('Y-m-d H:i:s');
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

        if ( !$result1 || !$result2 ) {
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
        $wp_post = get_post($popular_post->id);

        // Borrow prepare_item_for_response method from WP_REST_Posts_Controller.
        $posts_controller = new \WP_REST_Posts_Controller($wp_post->post_type, $request);
        $data = $posts_controller->prepare_item_for_response($wp_post, $request);

        // Add pageviews from popular_post object to response.
        $data->data['pageviews'] = $popular_post->pageviews;

        return $this->prepare_response_for_collection($data);
    }

    /**
     * Check if current user can read items.
     *
     * @since   1.0.0
     * @param   WP_REST_Request $request Full data about the request.
     * @return  bool
     */
    public function get_items_permissions_check($request)
    {
        return true;
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
                    return rtrim(preg_replace('|[^0-9,-]|', '', $term_id), ',');
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
