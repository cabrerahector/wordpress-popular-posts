<?php

namespace WordPressPopularPosts\Shortcode;

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
     * Construct.
     */
    public function __construct()
    {
        $this->shortcodes = [
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
                $instance = new $shortcode();
                $instance->init();
            }
        }
    }
}
