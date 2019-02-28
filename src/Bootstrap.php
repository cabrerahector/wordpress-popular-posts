<?php
/**
 * Plugin bootstrap file.
 */
namespace WordPressPopularPosts;

/** Composer autoloder */
require __DIR__ . '/../vendor/autoload.php';

$container = new Container\Container();
$container->configure([
    new Container\WordPressPopularPostsConfiguration()
]);

$WordPressPopularPosts = $container['wpp'];
add_action('init', [$WordPressPopularPosts, 'init']);
