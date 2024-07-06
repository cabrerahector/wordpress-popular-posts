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
            __NAMESPACE__ . '\Autoptimize\Autoptimize',
            __NAMESPACE__ . '\LiteSpeedCache\LiteSpeedCache',
            __NAMESPACE__ . '\SiteGroundOptimizer\SiteGroundOptimizer',
            __NAMESPACE__ . '\W3TotalCache\W3TotalCache',
            __NAMESPACE__ . '\WPRocket\WPRocket',
        ];
    }

    /**
     * Loads all registered compatibility scripts.
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
