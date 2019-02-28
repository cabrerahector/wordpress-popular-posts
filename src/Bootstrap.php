<?php
/**
 * Plugin bootstrap file.
 */
namespace WordPressPopularPosts;

/** Composer autoloder */
require __DIR__ . '/../vendor/autoload.php';

register_activation_hook($wpp_main_plugin_file, [__NAMESPACE__ . '\Activator', 'activate']);
register_deactivation_hook($wpp_main_plugin_file, [__NAMESPACE__ . '\Deactivator', 'deactivate']);

$container = new Container\Container();
$container->configure([
    new Container\WordPressPopularPostsConfiguration()
]);

$WordPressPopularPosts = $container['wpp'];
add_action('init', [$WordPressPopularPosts, 'init']);
