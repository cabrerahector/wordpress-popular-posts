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
 * Version:           6.4.1
 * Requires at least: 5.3
 * Requires PHP:      7.2
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

define('WPP_VERSION', '6.4.1');

$wpp_main_plugin_file = __FILE__;
// Load plugin bootstrap
require __DIR__ . '/src/Bootstrap.php';
