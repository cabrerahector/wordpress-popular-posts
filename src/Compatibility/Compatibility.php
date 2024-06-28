<?php
/**
 * Loads various third-party compatibility scripts.
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/Compatibility
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts\Compatibility;

class Compatibility
{
    /**
     * Compat array.
     *
     * @since  7.0.1
     * @var array
     * @access protected
     */
    protected $compat;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->compat = [
            __NAMESPACE__ . '\Autoptimize',
            __NAMESPACE__ . '\LiteSpeedCache',
            __NAMESPACE__ . '\SiteGroundOptimizer',
        ];
    }

    /**
     * Loads all registered shortcodes.
     *
     * @since  7.0.1
     */
    public function load() : void
    {
        if ( is_array($this->compat) && ! empty($this->compat) ) {
            foreach ($this->compat as $compat) {
                $instance = new $compat();
                $instance->init();
            }
        }
    }
}
