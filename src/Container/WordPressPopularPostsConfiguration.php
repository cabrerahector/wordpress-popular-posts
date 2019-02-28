<?php
namespace WordPressPopularPosts\Container;

use WordPressPopularPosts\Settings;

class WordPressPopularPostsConfiguration implements ContainerConfigurationInterface
{
    /**
     * Modifies the given dependency injection container.
     *
     * @since   5.0.0
     * @param   Container $container
     */
    public function modify(Container $container)
    {
        $container['rest'] = $container->service(function(Container $container) {
            return new \WordPressPopularPosts\Rest\Controller(Settings::get('admin_options'));
        });

        $container['wpp'] = $container->service(function(Container $container) {
            return new \WordPressPopularPosts\WordPressPopularPosts($container['rest']);
        });
    }
}
