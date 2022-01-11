<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://cabrerahector.com/
 * @since             4.0.0
 * @package           WordPressPopularPosts
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Popular Posts
 * Plugin URI:        https://wordpress.org/plugins/wordpress-popular-posts/
 * Description:       A highly customizable widget that displays the most popular posts on your blog.
 * Version:           5.5.1
 * Author:            Hector Cabrera
 * Author URI:        https://cabrerahector.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wordpress-popular-posts
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
    die();
}

define('WPP_VERSION', '5.5.1');
define('WPP_MIN_PHP_VERSION', '5.4');
define('WPP_MIN_WP_VERSION', '4.9');

/** Requirements check */
global $wp_version;

// We're good, continue!
if ( version_compare(PHP_VERSION, WPP_MIN_PHP_VERSION, '>=') && version_compare($wp_version, WPP_MIN_WP_VERSION, '>=') ) {
    $wpp_main_plugin_file = __FILE__;
    // Load plugin bootstrap
    require __DIR__ . '/src/Bootstrap.php';
} // Nope.
else {
    if ( isset($_GET['activate']) )
        unset($_GET['activate']);

    function wpp_render_min_requirements_notice() {
        global $wp_version;
        echo '<div class="notice notice-error"><p>' . sprintf(
            __('WordPress Popular Posts requires at least PHP %1$s and WordPress %2$s to function correctly. Your site uses PHP %3$s and WordPress %4$s.', 'wordpress-popular-posts'),
            WPP_MIN_PHP_VERSION,
            WPP_MIN_WP_VERSION,
            PHP_VERSION,
            $wp_version
        ) . '</p></div>';
    }
    add_action('admin_notices', 'wpp_render_min_requirements_notice');
}
