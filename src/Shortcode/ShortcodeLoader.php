<?php

namespace WordPressPopularPosts\Shortcode;

use WordPressPopularPosts\Output;

class ShortcodeLoader {

    /**
     * Shortcode array.
     *
     * @since  6.3.0
     * @var array
     * @access protected
     */
    protected $shortcodes;

    /**
     * Admin settings.
     *
     * @since   6.3.0
     * @var     array
     */
    private $admin_options = [];

    /**
     * Output object.
     *
     * @since  6.3.0
     * @var     \WordPressPopularPosts\Output       $output
     * @access  private
     */
    private $output;

    /**
     * Construct.
     *
     * @param   array                               $admin_options
     * @param   \WordPressPopularPosts\Output       $output         Output class.
     */
    public function __construct(array $admin_options, Output $output)
    {
        $this->admin_options = $admin_options;
        $this->output = $output;
        $this->shortcodes = [
            __NAMESPACE__ . '\Posts',
            __NAMESPACE__ . '\ViewsCount'
        ];
    }

    /**
     * Loads all registered shortcodes.
     *
     * @since  6.3.0
     */
    public function load() : void
    {
        if ( is_array($this->shortcodes) && ! empty($this->shortcodes) ) {
            foreach ($this->shortcodes as $shortcode) {
                $instance = new $shortcode($this->admin_options, $this->output);
                $instance->init();
            }
        }
    }
}
