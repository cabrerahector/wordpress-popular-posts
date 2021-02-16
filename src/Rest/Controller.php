<?php
namespace WordPressPopularPosts\Rest;

use WordPressPopularPosts\Helper;
use WordPressPopularPosts\Query;

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
     * Initialize class.
     *
     * @param   \WordPressPopularPosts\Rest\PostsEndpoint
     * @param   \WordPressPopularPosts\Rest\ViewLoggerEndpoint
     * @param   \WordPressPopularPosts\Rest\WidgetEndpoint
     */
    public function __construct(\WordPressPopularPosts\Rest\PostsEndpoint $posts_endpoint, \WordPressPopularPosts\Rest\ViewLoggerEndpoint $view_logger_endpoint, \WordPressPopularPosts\Rest\WidgetEndpoint $widget_endpoint)
    {
        $this->posts_endpoint = $posts_endpoint;
        $this->view_logger_endpoint = $view_logger_endpoint;
        $this->widget_endpoint = $widget_endpoint;
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
    }
}
