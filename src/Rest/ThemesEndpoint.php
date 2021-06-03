<?php
namespace WordPressPopularPosts\Rest;

class ThemesEndpoint extends Endpoint {

    /**
     * Themer object.
     *
     * @var     \WordPressPopularPosts\Themer       $themer
     * @access  private
     */
    private $themer;

    /**
     * Initializes class.
     *
     * @param   array
     * @param   \WordPressPopularPosts\Translate
     * @param   \WordPressPopularPosts\Themer
     */
    public function __construct(array $config, \WordPressPopularPosts\Translate $translate, \WordPressPopularPosts\Themer $themer)
    {
        $this->config = $config;
        $this->translate = $translate;
        $this->themer = $themer;
    }

    /**
     * Registers the endpoint(s).
     *
     * @since   5.4.0
     */
    public function register()
    {
        $version = '1';
        $namespace = 'wordpress-popular-posts/v' . $version;

        register_rest_route($namespace, '/themes', [
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
        $registered_themes = $this->themer->get_themes();
        ksort($registered_themes);

        return new \WP_REST_Response($registered_themes, 200);
    }
}
