<?php
/**
 * Plugin's main class.
 * 
 * Here everything gets initialized/loaded.
 */

namespace WordPressPopularPosts;

class WordPressPopularPosts {
    /**
     * I18N class.
     * 
     * @var     I18N $i18n
     * @access  private
     */
    private $i18n;

    /**
     * REST controller class.
     * 
     * @var     Rest\Controller $rest
     * @access  private
     */
    private $rest;

    /**
     * Admin class.
     * 
     * @var     Admin\Admin $front
     * @access  private
     */
    private $admin;

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
     * Block Widget class.
     *
     * @var     Block\Widget $widget
     * @access  private
     */
    private $block_widget;

    /**
     * ShortcodeLoader class.
     *
     * @var     Shortcode\ShortcodeLoader $shortcode_loader
     * @access  private
     */
    private $shortcode_loader;

    /**
     * Constructor.
     *
     * @since   5.0.0
     * @param   I18N            $i18n
     * @param   Rest\Controller $rest
     * @param   Admin\Admin     $admin
     * @param   Front\Front     $front
     * @param   Widget\Widget   $widget
     */
    public function __construct(I18N $i18n, Rest\Controller $rest, Admin\Admin $admin, Front\Front $front, Widget\Widget $widget, Block\Widget\Widget $block_widget, Shortcode\ShortcodeLoader $shortcode_loader)
    {
        $this->i18n = $i18n;
        $this->rest = $rest;
        $this->admin = $admin;
        $this->front = $front;
        $this->widget = $widget;
        $this->block_widget = $block_widget;
        $this->shortcode_loader = $shortcode_loader;
    }

    /**
     * Initializes plugin.
     *
     * @since   5.0.0
     */
    public function init()
    {
        $this->i18n->load_plugin_textdomain();
        $this->rest->hooks();
        $this->admin->hooks();
        $this->front->hooks();
        $this->widget->hooks();
        $this->block_widget->hooks();
        $this->shortcode_loader->load();
    }
}
