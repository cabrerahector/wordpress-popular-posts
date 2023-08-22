<?php
namespace WordPressPopularPosts\Shortcode;

abstract class Shortcode {

    /**
     * Shortcode tag (eg. footag)
     *
     * @since  6.3.0
     * @var string
     * @access protected
     */
    protected $tag;

    /**
     * Initializes shortcode.
     *
     */
    public function init()
    {
        $this->register();
    }

    /**
     * Registers the shortcode
     *
     * @since  6.3.0
     */
    public function register() : void
    {
        if ( $this->tag ) {
            add_shortcode( $this->tag, [$this, 'handle'] );
        }
    }

    /**
     * Handles the HTML output of the shortcode.
     *
     * @since  6.3.0
     * @param  array  $attributes  Array of attributes passed to the shortcode
     * @return string $html        HTML output
     */
    abstract public function handle(array $attributes) : string;
}
