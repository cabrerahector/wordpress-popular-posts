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
     * Widget class.
     * 
     * @var     Widget\Widget $widget
     * @access  private
     */
    private $widget;

    /**
     * Constructor.
     *
     * @since   5.0.0
     * @param   Rest\Controller $rest
     * @param   Admin\Admin     $admin
     * @param   Front\Front     $front
     * @param   Widget\Widget   $widget
     */
    public function __construct(Rest\Controller $rest, Admin\Admin $admin, Front\Front $front, Widget\Widget $widget)
    {
        $this->rest = $rest;
        $this->admin = $admin;
        $this->front = $front;
        $this->widget = $widget;
    }

    /**
     * Initializes plugin.
     *
     * @since   5.0.0
     */
    public function init()
    {
        $this->rest->hooks();
        $this->admin->hooks();
        $this->front->hooks();
        $this->widget->hooks();
    }
}
