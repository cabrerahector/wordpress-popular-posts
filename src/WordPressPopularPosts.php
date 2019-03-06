<?php
/**
 * Plugin's main class.
 * 
 * Here everything gets initialized/loaded.
 */

namespace WordPressPopularPosts;

class WordPressPopularPosts {
    /**
     * REST controller class.
     * 
     * @var     Rest\Controller $rest
     * @access  private
     */
    private $rest;

    /**
     * Front class.
     * 
     * @var     Front\Front $front
     * @access  private
     */
    private $front;

    /**
     * Constructor.
     *
     * @since   5.0.0
     * @param   Rest\Controller  $rest
     * @param   Front\Front      $front
     */
    public function __construct(Rest\Controller $rest, Front\Front $front)
    {
        $this->rest = $rest;
        $this->front = $front;
    }

    /**
     * Initializes plugin.
     *
     * @since   5.0.0
     */
    public function init()
    {
        $this->rest->hooks();
        $this->front->hooks();
    }
}
