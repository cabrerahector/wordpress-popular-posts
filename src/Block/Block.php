<?php
/**
 * Contract to build blocks.
 */

namespace WordPressPopularPosts\Block;

abstract class Block {

    /**
     * 
     */
    public function hooks()
    {
        add_action('init', [$this, 'register']);
    }

    /**
     * 
     */
    abstract function register();

    /**
     * 
     */
    abstract function render(array $attributes);
}
