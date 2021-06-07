<?php
namespace WordPressPopularPosts\Rest;

class Controller {

    /**
     * Posts Endpoint.
     *
     * @var     \WordPressPopularPosts\Rest\PostsEndpoint
     * @access  private
     */
    private $posts_endpoint;

    /**
     * View Logger Endpoint.
     *
     * @var     \WordPressPopularPosts\Rest\ViewLoggerEndpoint
     * @access  private
     */
    private $view_logger_endpoint;

    /**
     * View Logger Endpoint.
     *
     * @var     \WordPressPopularPosts\Rest\WidgetEndpoint
     * @access  private
     */
    private $widget_endpoint;

    /**
     * Themes Endpoint.
     *
     * @var     \WordPressPopularPosts\Rest\ThemesEndpoint
     * @access  private
     */
    private $themes_endpoint;

    /**
     * Themes Endpoint.
     *
     * @var     \WordPressPopularPosts\Rest\ThumbnailsEndpoint
     * @access  private
     */
    private $thumbnails_endpoint;

    /**
     * Themes Endpoint.
     *
     * @var     \WordPressPopularPosts\Rest\TaxonomiesEndpoint
     * @access  private
     */
    private $taxonomies_endpoint;

    /**
     * Initialize class.
     *
     * @param   \WordPressPopularPosts\Rest\PostsEndpoint
     * @param   \WordPressPopularPosts\Rest\ViewLoggerEndpoint
     * @param   \WordPressPopularPosts\Rest\WidgetEndpoint
     * @param   \WordPressPopularPosts\Rest\ThemesEndpoint
     * @param   \WordPressPopularPosts\Rest\ThumbnailsEndpoint
     * @param   \WordPressPopularPosts\Rest\TaxonomiesEndpoint
     */
    public function __construct(\WordPressPopularPosts\Rest\PostsEndpoint $posts_endpoint, \WordPressPopularPosts\Rest\ViewLoggerEndpoint $view_logger_endpoint, \WordPressPopularPosts\Rest\WidgetEndpoint $widget_endpoint, \WordPressPopularPosts\Rest\ThemesEndpoint $themes_endpoint, \WordPressPopularPosts\Rest\ThumbnailsEndpoint $thumbnails_endpoint, \WordPressPopularPosts\Rest\TaxonomiesEndpoint $taxonomies_endpoint)
    {
        $this->posts_endpoint = $posts_endpoint;
        $this->view_logger_endpoint = $view_logger_endpoint;
        $this->widget_endpoint = $widget_endpoint;
        $this->themes_endpoint = $themes_endpoint;
        $this->thumbnails_endpoint = $thumbnails_endpoint;
        $this->taxonomies_endpoint = $taxonomies_endpoint;
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
        $this->posts_endpoint->register();
        $this->view_logger_endpoint->register();
        $this->widget_endpoint->register();
        $this->themes_endpoint->register();
        $this->thumbnails_endpoint->register();
        $this->taxonomies_endpoint->register();
    }
}
