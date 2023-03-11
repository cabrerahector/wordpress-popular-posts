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
    abstract public function register();

    /**
     * 
     */
    abstract public function render(array $attributes);
}
