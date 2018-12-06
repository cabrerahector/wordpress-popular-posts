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
 * Version:           4.2.2
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

define( 'WPP_VER', '4.2.2' );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-popular-posts-admin-notices.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-popular-posts-activator.php';

// Can we run?
if ( $errors = WPP_Activator::check_requirements() ) {
    if ( isset($_GET['activate']) ) unset($_GET['activate']);

    // Display error message(s)
    new WPP_Message( $errors, 'notice-error' );
    // We're done here
    return;
}

/*
 * The code that runs during plugin activation.
 */
register_activation_hook( __FILE__, array('WPP_Activator', 'activate') );

/*
 * The code that runs during plugin activation.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-popular-posts-deactivator.php';
register_deactivation_hook( __FILE__, array('WPP_Deactivator', 'deactivate') );

/*
 * The core plugins class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-popular-posts.php';

/*
 * Begin execution of the plugin.
 */
$wordpress_popular_posts = new WordPressPopularPosts();
$wordpress_popular_posts->run();
