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
     * WordPress hooks.
     *
     * @since   5.0.0
     */
    public function hooks()
    {
        add_action('plugins_loaded', [$this, 'load_plugin_textdomain']);
    }

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain()
    {
        $locale = apply_filters('plugin_locale', get_locale(), 'wordpress-popular-posts');
        load_textdomain('wordpress-popular-posts', WP_LANG_DIR . '/' . 'wordpress-popular-posts' . '/' . 'wordpress-popular-posts' . '-' . $locale . '.mo');
    }
}
