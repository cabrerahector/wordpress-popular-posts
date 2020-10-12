<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      4.0.0
 *
 * @package    WordPressPopularPosts
 */

namespace WordPressPopularPosts;

class I18N {
    /**
     * Plugin options.
     *
     * @var     array      $config
     * @access  private
     */
    private $config;

    /**
     * Construct.
     *
     * @since   5.3.0
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain()
    {
        // This is basically a "hack" and should be removed in the future
        // if/when we figure out why Polylang doesn't load WPP's mo files 
        // while WPML does that automatically.
        if ( ! is_admin() && ! $this->config['tools']['ajax'] && function_exists('PLL') ) {
            unload_textdomain('wordpress-popular-posts');
            load_textdomain('wordpress-popular-posts', WP_LANG_DIR . '/plugins/wordpress-popular-posts-' . get_locale() . '.mo');
        }
    }
}
