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
     * Constructor.
     *
     * @since   5.0.0
     * @param   \Rest\Controller  $rest
     */
    public function __construct(Rest\Controller $rest)
    {
        $this->rest = $rest;
    }

    /**
     * Initializes plugin.
     *
     * @since   5.0.0
     */
    public function init()
    {
        $this->rest->hooks();
    }
}
