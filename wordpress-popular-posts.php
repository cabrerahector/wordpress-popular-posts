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
 * Plugin Name:       WP Popular Posts
 * Plugin URI:        https://wordpress.org/plugins/wordpress-popular-posts/
 * Description:       A highly customizable plugin that displays your most popular posts.
 * Version:           7.3.4
 * Requires at least: 6.2
 * Requires PHP:      7.3
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

define('WPP_VERSION', '7.3.4');

$wpp_main_plugin_file = __FILE__;
// Load plugin bootstrap
require __DIR__ . '/src/Bootstrap.php';
