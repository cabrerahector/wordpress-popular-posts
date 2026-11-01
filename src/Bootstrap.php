<?php
/**
 * Plugin bootstrap file.
 */
namespace WordPressPopularPosts;

if ( ! defined( 'ABSPATH' ) ) exit;

/** Composer autoloder */
require __DIR__ . '/../vendor/autoload.php';

register_activation_hook($wpp_main_plugin_file, [__NAMESPACE__ . '\Activation\Activator', 'activate']);
register_deactivation_hook($wpp_main_plugin_file, [__NAMESPACE__ . '\Activation\Deactivator', 'deactivate']);

$wpp_container = new Container\Container(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- It is prefixed
$wpp_container->configure([
    new Container\WordPressPopularPostsConfiguration()
]);

$WordPressPopularPosts = $wpp_container['wpp'];
add_action('plugins_loaded', [$WordPressPopularPosts, 'init']);

// WPP's template tags
require __DIR__ . '/template-tags.php';

// Deprecated functions/classes
require __DIR__ . '/deprecated.php';
