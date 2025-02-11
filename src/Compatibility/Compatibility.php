<?php
/**
 * Loads various third-party compatibility scripts.
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/Compatibility
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts\Compatibility;

use WordPressPopularPosts\{Image, Themer};

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
     * Admin settings.
     *
     * @since  7.2.0
     * @var array
     * @access protected
     */
    protected $config;

    /**
     * Image object.
     *
     * @var     \WordPressPopularPosts\Image
     * @access  private
     */
    private $thumbnail;

    /**
     * Themer object.
     *
     * @var     \WordPressPopularPosts\Themer       $themer
     * @access  private
     */
    private $themer;

    /**
     * Construct.
     *
     * @param array $admin_settings
     * @param   \WordPressPopularPosts\Themer
     */
    public function __construct(array $admin_settings, Image $image, Themer $themer)
    {
        $this->compat = [
            __NAMESPACE__ . '\Autoptimize\Autoptimize',
            __NAMESPACE__ . '\Elementor\Elementor',
            __NAMESPACE__ . '\LiteSpeedCache\LiteSpeedCache',
            __NAMESPACE__ . '\Polylang\Polylang',
            __NAMESPACE__ . '\SiteGroundOptimizer\SiteGroundOptimizer',
            __NAMESPACE__ . '\W3TotalCache\W3TotalCache',
            __NAMESPACE__ . '\WPRocket\WPRocket',
        ];

        $this->config = $admin_settings;
        $this->thumbnail = $image;
        $this->themer = $themer;
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
                $instance = new $compat($this->config, $this->thumbnail, $this->themer);
                $instance->init();
            }
        }
    }
}
