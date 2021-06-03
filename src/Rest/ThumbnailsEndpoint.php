<?php
namespace WordPressPopularPosts\Rest;

class ThumbnailsEndpoint extends Endpoint {

    /**
     * Registers the endpoint(s).
     *
     * @since   5.4.0
     */
    public function register()
    {
        $version = '1';
        $namespace = 'wordpress-popular-posts/v' . $version;

        register_rest_route($namespace, '/thumbnails', [
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
        global $_wp_additional_image_sizes;

        $available_sizes = [];
        $get_intermediate_image_sizes = get_intermediate_image_sizes();

        // Create the full array with sizes and crop info
        foreach( $get_intermediate_image_sizes as $_size ) {
            if ( in_array($_size, ['thumbnail', 'medium', 'large']) ) {
                $available_sizes[$_size]['width'] = get_option($_size . '_size_w');
                $available_sizes[$_size]['height'] = get_option($_size . '_size_h');
                $available_sizes[$_size]['crop'] = (bool) get_option($_size . '_crop');
            } elseif ( isset($_wp_additional_image_sizes[$_size]) ) {
                $available_sizes[$_size] = [
                    'width' => $_wp_additional_image_sizes[$_size]['width'],
                    'height' => $_wp_additional_image_sizes[$_size]['height'],
                    'crop' =>  $_wp_additional_image_sizes[$_size]['crop']
                ];
            }
        }

        return new \WP_REST_Response($available_sizes, 200);
    }
}
