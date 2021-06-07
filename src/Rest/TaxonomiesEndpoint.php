<?php
namespace WordPressPopularPosts\Rest;

class TaxonomiesEndpoint extends Endpoint {

    /**
     * Registers the endpoint(s).
     *
     * @since   5.4.0
     */
    public function register()
    {
        $version = '1';
        $namespace = 'wordpress-popular-posts/v' . $version;

        register_rest_route($namespace, '/taxonomies', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_items'],
                'permission_callback' => function() {
                    return current_user_can('edit_posts');
                }
            ]
        ]);
    }

    /**
     * Gets popular posts.
     *
     * @since   5.4.0
     * @param   \WP_REST_Request $request Full data about the request.
     * @return  \WP_REST_Response
     */
    public function get_items($request)
    {
        $taxonomies = get_taxonomies(['public' => true], 'objects');
        return new \WP_REST_Response($taxonomies, 200);
    }
}
