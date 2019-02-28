<?php
namespace WordPressPopularPosts\Rest;

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
     * Initialize class.
     *
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * WordPress hooks.
     *
     * @since   5.0.0
     */
    public function hooks()
    {
        //
    }
}
