<?php
/**
 * Reloads plugin's .mo file when Polylang is present.
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/Compatibility
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts\Compatibility\Polylang;

use WordPressPopularPosts\Compatibility\Compat;

class Polylang extends Compat
{
    /**
     * Admin settings.
     *
     * @since  7.2.0
     * @var array
     * @access protected
     */
    private $config;

    /**
     * Construct.
     *
     * @param array $settings
     */
    public function __construct($settings)
    {
        $this->config = $settings;
    }

    /**
     * Hooks into init to load current textdomain.
     */
    public function init()
    {
        if ( function_exists('PLL') ) {
            add_action('init', [$this, 'load_textdomain']);
        }
    }

    /**
     * Loads current textdomain.
     */
    public function load_textdomain() {
        // This is basically a "hack" and should be removed in the future
        // if/when we figure out why Polylang doesn't load WPP's mo files 
        // while WPML does automatically.

        $locale_file = WP_LANG_DIR . '/plugins/wordpress-popular-posts-' . get_locale() . '.mo';

        if (
            ! is_admin() 
            && ! $this->config['tools']['ajax']
            && file_exists($locale_file)
        ) {
            unload_textdomain('wordpress-popular-posts');
            load_textdomain('wordpress-popular-posts', $locale_file);
        }
    }
}
